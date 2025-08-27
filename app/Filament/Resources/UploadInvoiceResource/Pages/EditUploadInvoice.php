<?php

namespace App\Filament\Resources\UploadInvoiceResource\Pages;

use App\Filament\Resources\UploadInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUploadInvoice extends EditRecord
{
    protected static string $resource = UploadInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
