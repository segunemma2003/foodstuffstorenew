<?php

namespace App\Filament\BlogAdmin\Widgets;
use App\Models\Blog;
use App\Models\Comment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BlogChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Blog Activity Over Time';
    protected static string $color = 'info';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Get data for the last 7 days
        $data = collect(range(6, 0))->map(function ($daysBack) {
            $date = now()->subDays($daysBack);

            return [
                'date' => $date->format('M j'),
                'blogs' => Blog::whereDate('created_at', $date)->count(),
                'comments' => Comment::whereDate('created_at', $date)->count(),
                'views' => Blog::whereDate('created_at', '<=', $date)->sum('views_count'),
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'New Blogs',
                    'data' => $data->pluck('blogs')->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'New Comments',
                    'data' => $data->pluck('comments')->toArray(),
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
