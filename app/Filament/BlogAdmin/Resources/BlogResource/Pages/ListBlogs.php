<?php

namespace App\Filament\BlogAdmin\Resources\BlogResource\Pages;

use App\Filament\BlogAdmin\Resources\BlogResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Cache;

class ListBlogs extends ListRecords
{
    protected static string $resource = BlogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // Add a "Clear Cache" action button
            Actions\Action::make('clear_cache')
                ->label('Clear Cache')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    // Clear all blog caches
                    for ($i = 1; $i <= 50; $i++) {
                        Cache::forget("blogs.index.page.{$i}");
                    }
                    Cache::forget('blogs.published.latest');
                    Cache::forget('blogs.popular');
                    Cache::forget('blogs.recent');
                    Cache::forget('dashboard_stats');
                    Cache::flush();
                    Notification::make()
            ->title('All blog caches cleared successfully!')
            ->success()
            ->body('The blog post has been created and caches cleared.')
            ->send();

                })
                ->requiresConfirmation()
                ->modalHeading('Clear All Blog Caches')
                ->modalDescription('This will clear all cached blog data. New changes will be visible immediately.')
                ->modalSubmitActionLabel('Clear Cache'),
        ];
    }
}
