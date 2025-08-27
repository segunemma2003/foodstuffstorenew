<?php

namespace App\Filament\Caterer\Resources\GuestOrderResource\Pages;

use App\Filament\Caterer\Resources\GuestOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGuestOrder extends ViewRecord
{
    protected static string $resource = GuestOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
