<?php

namespace App\Filament\Caterer\Resources\GuestOrderResource\Pages;

use App\Filament\Caterer\Resources\GuestOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuestOrder extends EditRecord
{
    protected static string $resource = GuestOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
