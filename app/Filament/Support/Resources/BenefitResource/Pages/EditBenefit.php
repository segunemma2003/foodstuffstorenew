<?php

namespace App\Filament\Support\Resources\BenefitResource\Pages;

use App\Filament\Support\Resources\BenefitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBenefit extends EditRecord
{
    protected static string $resource = BenefitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
