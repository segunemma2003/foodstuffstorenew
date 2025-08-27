<?php

namespace App\Filament\Exports;

use App\Models\Employee;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class EmployeeExporter extends Exporter
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('user.name'),
            ExportColumn::make('company.name'),
            ExportColumn::make('salary'),
            ExportColumn::make('credit_limit'),
            ExportColumn::make('debit_limit'),
            ExportColumn::make('amount_paid'),
            ExportColumn::make('balance'),
            ExportColumn::make('status'),
            ExportColumn::make('created_at')->label('Date Created'),
            // ExportColumn::make('updated_at'),
            ExportColumn::make('card_number'),
            // ExportColumn::make('pin_code'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your employee export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
