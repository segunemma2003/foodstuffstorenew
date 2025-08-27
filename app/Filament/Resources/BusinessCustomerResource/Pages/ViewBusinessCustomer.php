<?php

namespace App\Filament\Resources\BusinessCustomerResource\Pages;

use App\Filament\Resources\BusinessCustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBusinessCustomer extends ViewRecord
{
    protected static string $resource = BusinessCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
