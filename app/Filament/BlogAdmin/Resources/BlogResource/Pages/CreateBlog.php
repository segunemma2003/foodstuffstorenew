<?php

namespace App\Filament\BlogAdmin\Resources\BlogResource\Pages;

use App\Filament\BlogAdmin\Resources\BlogResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;

class CreateBlog extends CreateRecord
{
    protected static string $resource = BlogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['author_id'] = auth()->id();

        if ($data['status'] === 'published' && !$data['published_at']) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Clear all blog-related caches immediately after creating
        $this->clearAllBlogCaches();

        // Show success notification
        Notification::make()
            ->title('Blog Created Successfully')
            ->success()
            ->body('The blog post has been created and caches cleared.')
            ->send();
    }

    private function clearAllBlogCaches(): void
    {
        // Clear blog listing caches (all pages)
        for ($i = 1; $i <= 50; $i++) {
            Cache::forget("blogs.index.page.{$i}");
        }

        // Clear other blog caches
        Cache::forget('blogs.published.latest');
        Cache::forget('blogs.popular');
        Cache::forget('blogs.recent');
        Cache::forget('dashboard_stats');

        // Clear Laravel application caches
        Cache::flush();
    }
}
