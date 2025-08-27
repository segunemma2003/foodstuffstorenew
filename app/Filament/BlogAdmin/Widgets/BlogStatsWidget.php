<?php

namespace App\Filament\BlogAdmin\Widgets;

use App\Models\Blog;
use App\Models\Comment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BlogStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        // Get current period stats
        $totalBlogs = Blog::count();
        $publishedBlogs = Blog::where('status', 'published')->count();
        $totalViews = Blog::sum('views_count');
        $totalComments = Comment::where('status', 'approved')->count();
        $pendingComments = Comment::where('status', 'pending')->count();

        // Get previous period for comparison
        $lastMonth = now()->subMonth();
        $blogsLastMonth = Blog::where('created_at', '>=', $lastMonth)->count();
        $viewsLastMonth = Blog::where('created_at', '>=', $lastMonth)->sum('views_count');
        $commentsLastMonth = Comment::where('created_at', '>=', $lastMonth)->where('status', 'approved')->count();

        // Calculate trends
        $blogsTrend = $blogsLastMonth > 0 ? (($totalBlogs - $blogsLastMonth) / $blogsLastMonth) * 100 : 0;
        $viewsTrend = $viewsLastMonth > 0 ? (($totalViews - $viewsLastMonth) / $viewsLastMonth) * 100 : 0;
        $commentsTrend = $commentsLastMonth > 0 ? (($totalComments - $commentsLastMonth) / $commentsLastMonth) * 100 : 0;

        return [
            Stat::make('Total Blogs', $totalBlogs)
                ->description($blogsLastMonth . ' new this month')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary')
                ->chart([7, 12, 18, 25, 32, 28, $totalBlogs]),

            Stat::make('Published Blogs', $publishedBlogs)
                ->description($publishedBlogs . ' / ' . $totalBlogs . ' published')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success')
                ->chart([5, 8, 15, 20, 25, 22, $publishedBlogs]),

            Stat::make('Total Views', number_format($totalViews))
                ->description(number_format($viewsLastMonth) . ' this month')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning')
                ->chart([100, 200, 350, 500, 750, 900, $totalViews]),

            Stat::make('Approved Comments', $totalComments)
                ->description($pendingComments . ' pending approval')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('info')
                ->chart([2, 5, 8, 12, 18, 15, $totalComments]),
        ];
    }
}
