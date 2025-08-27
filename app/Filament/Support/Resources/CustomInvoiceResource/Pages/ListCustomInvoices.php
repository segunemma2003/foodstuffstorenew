<?php

namespace App\Filament\Support\Resources\CustomInvoiceResource\Pages;

use App\Filament\Support\Resources\CustomInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use TomatoPHP\FilamentInvoices\Filament\Resources\InvoiceResource\Pages\ListInvoices;

class ListCustomInvoices extends ListInvoices
{
    protected static string $resource = CustomInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
