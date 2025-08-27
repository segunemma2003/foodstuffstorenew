<?php

namespace App\Filament\Resources\UploadInvoiceResource\Pages;

use App\Filament\Resources\UploadInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUploadInvoice extends ViewRecord
{
    protected static string $resource = UploadInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
