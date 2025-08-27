<?php

namespace App\Filament\Support\Resources\CustomInvoiceResource\Pages;

use App\Filament\Support\Resources\CustomInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use TomatoPHP\FilamentInvoices\Filament\Resources\InvoiceResource\Pages\EditInvoice;

class EditCustomInvoice extends EditInvoice
{
    protected static string $resource = CustomInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
