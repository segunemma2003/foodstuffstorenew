<?php

namespace App\Filament\Resources\ProductQuantityResource\Pages;

use App\Filament\Resources\ProductQuantityResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductQuantity extends ViewRecord
{
    protected static string $resource = ProductQuantityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
