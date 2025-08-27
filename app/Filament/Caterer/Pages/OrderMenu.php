<?php

namespace App\Filament\Caterer\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Page;

class OrderMenu extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.caterer.pages.order-menu';
    protected static ?string $navigationLabel = null;

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return false;
    }

    public function mount(): void
    {


        Filament::getPanel()->navigation(false);
    }
}
