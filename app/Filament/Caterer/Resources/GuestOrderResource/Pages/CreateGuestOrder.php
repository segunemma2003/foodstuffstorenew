<?php

namespace App\Filament\Caterer\Resources\GuestOrderResource\Pages;

use App\Filament\Caterer\Resources\GuestOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGuestOrder extends CreateRecord
{
    protected static string $resource = GuestOrderResource::class;
}
