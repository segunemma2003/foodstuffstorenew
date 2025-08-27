<?php

namespace App\Filament\Resources\ProductQuantityResource\Pages;

use App\Filament\Resources\ProductQuantityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductQuantity extends EditRecord
{
    protected static string $resource = ProductQuantityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
