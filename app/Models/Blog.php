<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'tags',
        'status',
        'published_at',
        'author_id',
        'views_count',
        'comments_count',
    ];

    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Automatically generate slug from title and clear caches
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($blog) {
            if (empty($blog->slug)) {
                $blog->slug = Str::slug($blog->title);
            }
        });

        static::updating(function ($blog) {
            if ($blog->isDirty('title') && empty($blog->slug)) {
                $blog->slug = Str::slug($blog->title);
            }
        });

        // Clear cache immediately when blog is created, updated, or deleted
        static::created(function ($blog) {
            self::clearAllBlogCaches();
        });

        static::updated(function ($blog) {
            self::clearAllBlogCaches();
            // Also clear the specific blog cache
            Cache::forget("blog.{$blog->slug}");
        });

        static::deleted(function ($blog) {
            self::clearAllBlogCaches();
            Cache::forget("blog.{$blog->slug}");
        });
    }

    /**
     * Clear all blog-related caches
     */
    private static function clearAllBlogCaches()
    {
        // Clear blog listing caches (multiple pages)
        for ($i = 1; $i <= 50; $i++) {
            Cache::forget("blogs.index.page.{$i}");
        }

        // Clear specific blog caches
        Cache::forget('blogs.published.latest');
        Cache::forget('blogs.popular');
        Cache::forget('blogs.recent');
        Cache::forget('dashboard_stats');

        // Clear all cached blog keys
        $blogs = self::all();
        foreach ($blogs as $blog) {
            Cache::forget("blog.{$blog->slug}");
            Cache::forget("blog.{$blog->id}.related");
        }

        // Clear application cache
        try {
            \Artisan::call('cache:clear');
        } catch (\Exception $e) {
            // Ignore if artisan command fails
        }
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->hasMany(Comment::class)
                   ->where('status', 'approved')
                   ->orderBy('created_at', 'desc');
    }

    // Optimized scope for published blogs
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at');
                    // ->where('published_at', '<=', now());
    }

    // Scope for popular blogs (high views/comments)
    public function scopePopular($query, $limit = 5)
    {
        return $query->published()
                    ->orderBy('views_count', 'desc')
                    ->orderBy('comments_count', 'desc')
                    ->limit($limit);
    }

    // Scope for recent blogs
    public function scopeRecent($query, $limit = 10)
    {
        return $query->published()
                    ->orderBy('published_at', 'desc')
                    ->limit($limit);
    }

    // Get featured image URL with S3 support
    public function getFeaturedImageUrlAttribute()
    {
        if (!$this->featured_image) {
            return null;
        }

        // Check if it's already a full URL (for S3)
        if (filter_var($this->featured_image, FILTER_VALIDATE_URL)) {
            return $this->featured_image;
        }

        // For S3 storage
        if (config('filesystems.default') === 's3') {
            return Storage::disk('s3')->url($this->featured_image);
        }

        // For local storage
        return Storage::url($this->featured_image);
    }

    // Get formatted published date
    public function getFormattedPublishedDateAttribute()
    {
        return $this->published_at ? $this->published_at->format('M d, Y') : null;
    }

    // Get reading time estimate
    public function getReadingTimeAttribute()
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $minutes = ceil($wordCount / 200); // Average reading speed
        return $minutes . ' min read';
    }

    // Get excerpt with fallback
    public function getExcerptAttribute($value)
    {
        if ($value) {
            return $value;
        }

        // Auto-generate excerpt from content if not provided
        $stripped = strip_tags($this->content);
        return Str::limit($stripped, 160);
    }

    // Increment views count efficiently
    public function incrementViews()
    {
        // Use increment to avoid race conditions
        $this->increment('views_count');

        // Clear cache
        Cache::forget("blog.{$this->slug}");
    }

    // Get related blogs based on tags
    public function getRelatedBlogs($limit = 3)
    {
        if (!$this->tags || empty($this->tags)) {
            return self::published()
                      ->where('id', '!=', $this->id)
                      ->orderBy('published_at', 'desc')
                      ->limit($limit)
                      ->get();
        }

        return Cache::remember("blog.{$this->id}.related", 3600, function () use ($limit) {
            return self::published()
                      ->where('id', '!=', $this->id)
                      ->where(function ($query) {
                          foreach ($this->tags as $tag) {
                              $query->orWhereJsonContains('tags', $tag);
                          }
                      })
                      ->orderBy('views_count', 'desc')
                      ->limit($limit)
                      ->get();
        });
    }
}
