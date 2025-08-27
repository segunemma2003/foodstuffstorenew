<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'blog_id',
        'name',
        'email',
        'comment',
        'status',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Disable Laravel's automatic timestamps for performance
    const UPDATED_AT = null;

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    // Scope for approved comments with optimized query
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved')
                    ->orderBy('created_at', 'desc');
    }

    // Scope for recent approved comments
    public function scopeRecentApproved($query, $limit = 10)
    {
        return $query->approved()
                    ->limit($limit)
                    ->select(['id', 'blog_id', 'name', 'comment', 'created_at']);
    }

    // Get formatted created date
    public function getFormattedDateAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Get avatar initials
    public function getAvatarInitialsAttribute()
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
            if (strlen($initials) >= 2) break;
        }
        return $initials ?: strtoupper(substr($this->name, 0, 2));
    }

    // Get gravatar URL
    public function getGravatarUrlAttribute()
    {
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=40";
    }

    // Automatically update blog comment count when comment status changes
    protected static function boot()
    {
        parent::boot();

        static::created(function ($comment) {
            if ($comment->status === 'approved') {
                $comment->blog()->increment('comments_count');
            }
            // Clear caches when new comment is created
            self::clearCommentCaches($comment->blog_id);
        });

        static::updated(function ($comment) {
            if ($comment->isDirty('status')) {
                $blog = $comment->blog;
                $approvedCount = $blog->comments()->where('status', 'approved')->count();
                $blog->update(['comments_count' => $approvedCount]);
            }
            // Clear caches when comment is updated
            self::clearCommentCaches($comment->blog_id);
        });

        static::deleted(function ($comment) {
            if ($comment->status === 'approved') {
                $comment->blog()->decrement('comments_count');
            }
            // Clear caches when comment is deleted
            self::clearCommentCaches($comment->blog_id);
        });
    }

    /**
     * Clear comment-related caches
     */
    private static function clearCommentCaches($blogId)
    {
        // Get the blog to clear its cache
        $blog = \App\Models\Blog::find($blogId);
        if ($blog) {
            Cache::forget("blog.{$blog->slug}");
        }

        // Clear blog listing caches
        for ($i = 1; $i <= 20; $i++) {
            Cache::forget("blogs.index.page.{$i}");
        }

        // Clear dashboard stats
        Cache::forget('dashboard_stats');
    }
}
