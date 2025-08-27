<?php

// Fixed migration for performance optimizations
// Create: php artisan make:migration add_performance_indexes_to_blogs_and_comments_fixed

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add composite indexes for common query patterns
        Schema::table('blogs', function (Blueprint $table) {
            // For popular posts queries
            $table->index(['views_count', 'comments_count', 'published_at'], 'popularity_idx');

            // For status and published queries
            $table->index(['status', 'published_at', 'views_count'], 'status_published_views_idx');
        });

        Schema::table('comments', function (Blueprint $table) {
            // For admin moderation queries
            $table->index(['status', 'created_at', 'blog_id'], 'moderation_idx');

            // For spam detection by IP
            $table->index(['ip_address', 'created_at'], 'ip_created_idx');
        });

        // Add full-text search indexes for better search performance
        if (config('database.default') === 'mysql') {
            try {
                DB::statement('ALTER TABLE blogs ADD FULLTEXT search_idx (title, excerpt, content)');
            } catch (\Exception $e) {
                // Ignore if fulltext index already exists
            }
        }
    }

    public function down()
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropIndex(['views_count', 'comments_count', 'published_at']);
            $table->dropIndex(['status', 'published_at', 'views_count']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at', 'blog_id']);
            $table->dropIndex(['ip_address', 'created_at']);
        });

        if (config('database.default') === 'mysql') {
            try {
                DB::statement('ALTER TABLE blogs DROP INDEX search_idx');
            } catch (\Exception $e) {
                // Ignore if index doesn't exist
            }
        }
    }
};
