<?php

namespace App\Filament\Resources\UploadInvoiceResource\Pages;

use App\Filament\Resources\UploadInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUploadInvoices extends ListRecords
{
    protected static string $resource = UploadInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
