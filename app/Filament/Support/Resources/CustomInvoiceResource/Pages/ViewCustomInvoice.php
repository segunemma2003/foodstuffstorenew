<?php

namespace App\Filament\Support\Resources\CustomInvoiceResource\Pages;

use App\Filament\Support\Resources\CustomInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use TomatoPHP\FilamentInvoices\Filament\Resources\InvoiceResource\Pages\ViewInvoice;

class ViewCustomInvoice extends ViewInvoice
{
    protected static string $resource = CustomInvoiceResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\EditAction::make(),
    //     ];
    // }
}
