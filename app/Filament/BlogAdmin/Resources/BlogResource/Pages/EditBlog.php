<?php

namespace App\Filament\BlogAdmin\Resources\BlogResource\Pages;

use App\Filament\BlogAdmin\Resources\BlogResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditBlog extends EditRecord
{
    protected static string $resource = BlogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['status'] === 'published' && !$data['published_at']) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Clear all blog-related caches immediately after updating
        $this->clearAllBlogCaches();

        // Clear specific blog cache
        if ($this->record->slug) {
            Cache::forget("blog.{$this->record->slug}");
        }

        // Show success notification
        Notification::make()
            ->title('Blog Updated Successfully')
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
