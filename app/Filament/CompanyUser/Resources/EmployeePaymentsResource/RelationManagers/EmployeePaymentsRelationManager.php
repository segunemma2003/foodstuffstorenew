<?php

namespace App\Filament\CompanyUser\Resources\EmployeePaymentsResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeePaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'employeePayments';

    protected static ?string $recordTitleAttribute = 'amount';



    public  function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')->label('Payment Date'),
                Tables\Columns\TextColumn::make('amount')->label('Amount Paid'),
                Tables\Columns\TextColumn::make('updatedBy.name')->label('Updated By'),
            ])
            ->filters([
                //
            ]);
    }

}
