<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = 12;

        // Cache the blog listing for 15 minutes
        $cacheKey = "blogs.index.page.{$page}";

        $blogs = Cache::remember($cacheKey, 900, function () use ($perPage) {
            return Blog::published()
                ->with(['author:id,name'])
                ->select(['id', 'title', 'slug', 'excerpt', 'featured_image', 'tags', 'published_at', 'author_id', 'views_count', 'comments_count'])
                ->orderBy('published_at', 'desc')
                ->paginate($perPage);
        });

        return view('blogs.index', compact('blogs'));
    }

    public function show(Request $request, $slug)
    {
        // Cache individual blog posts for 1 hour
        $blog = Cache::remember("blog.{$slug}", 3600, function () use ($slug) {
            return Blog::published()
                ->with(['author:id,name'])
                ->where('slug', $slug)
                ->first();
        });

        if (!$blog) {
            abort(404);
        }

        // Increment views asynchronously to avoid blocking
        $this->incrementViewsAsync($blog);

        // Get approved comments with pagination for performance
        $comments = $blog->approvedComments()
            ->select(['id', 'name', 'email', 'comment', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get related blogs
        $relatedBlogs = $blog->getRelatedBlogs(3);

        return view('blogs.show', compact('blog', 'comments', 'relatedBlogs'));
    }

    public function storeComment(Request $request, $slug)
    {
        // Rate limiting: 3 comments per minute per IP
        $rateLimitKey = 'comment_' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return redirect()->back()
                ->with('error', "Too many comments. Please wait {$seconds} seconds before commenting again.");
        }

        $blog = Blog::published()
            ->where('slug', $slug)
            ->first();

        if (!$blog) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|min:2',
            'email' => 'required|email|max:150',
            'comment' => 'required|string|max:1000|min:10',
        ], [
            'name.required' => 'Please enter your name.',
            'name.min' => 'Name must be at least 2 characters.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'comment.required' => 'Please enter your comment.',
            'comment.min' => 'Comment must be at least 10 characters.',
            'comment.max' => 'Comment cannot exceed 1000 characters.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please check your input and try again.');
        }

        // Additional spam prevention
        $comment = trim($request->comment);

        // Check for suspicious content
        if ($this->isSpamComment($comment, $request->email)) {
            Log::warning('Potential spam comment detected', [
                'ip' => $request->ip(),
                'email' => $request->email,
                'comment' => $comment
            ]);

            return redirect()->back()
                ->with('error', 'Your comment could not be posted. Please try again.');
        }

        try {
            Comment::create([
                'blog_id' => $blog->id,
                'name' => strip_tags($request->name),
                'email' => $request->email,
                'comment' => $comment,
                'status' => 'pending',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Apply rate limiting
            RateLimiter::hit($rateLimitKey, 60);

            // Clear cache for this blog's comments
            Cache::forget("blog.{$slug}");

            return redirect()->back()
                ->with('success', 'Thank you! Your comment has been submitted and will be reviewed before being published.');

        } catch (\Exception $e) {
            Log::error('Error saving comment', [
                'error' => $e->getMessage(),
                'blog_id' => $blog->id,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->with('error', 'Something went wrong. Please try again later.');
        }
    }

    /**
     * Increment blog views asynchronously
     */
    private function incrementViewsAsync(Blog $blog)
    {
        // Use a job queue in production for better performance
        // For now, we'll use a simple increment with try-catch
        try {
            $blog->incrementViews();
        } catch (\Exception $e) {
            // Log but don't block the user
            Log::warning('Failed to increment blog views', [
                'blog_id' => $blog->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Basic spam detection
     */
    private function isSpamComment(string $comment, string $email): bool
    {
        // Check for suspicious patterns
        $spamPatterns = [
            '/\b(viagra|cialis|casino|poker|loan|mortgage)\b/i',
            '/http[s]?:\/\/[^\s]+/i', // URLs in comments
            '/\b[A-Z]{10,}\b/', // Too many capital letters
            '/(.)\1{4,}/', // Repeated characters
        ];

        foreach ($spamPatterns as $pattern) {
            if (preg_match($pattern, $comment)) {
                return true;
            }
        }

        // Check for disposable email domains
        $disposableEmailDomains = [
            '10minutemail.com', 'tempmail.org', 'guerrillamail.com',
            'mailinator.com', 'throwaway.email'
        ];

        $emailDomain = substr(strrchr($email, "@"), 1);
        if (in_array($emailDomain, $disposableEmailDomains)) {
            return true;
        }

        return false;
    }
}
