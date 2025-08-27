<?php

namespace App\Filament\Support\Resources\BenefitResource\Pages;

use App\Filament\Support\Resources\BenefitResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBenefit extends ViewRecord
{
    protected static string $resource = BenefitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
