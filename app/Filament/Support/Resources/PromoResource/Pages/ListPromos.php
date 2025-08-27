<?php

namespace App\Filament\Support\Resources\PromoResource\Pages;

use App\Filament\Support\Resources\PromoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromos extends ListRecords
{
    protected static string $resource = PromoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
