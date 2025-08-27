<?php

namespace App\Filament\Support\Resources\PromoResource\Pages;

use App\Filament\Support\Resources\PromoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPromo extends ViewRecord
{
    protected static string $resource = PromoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
