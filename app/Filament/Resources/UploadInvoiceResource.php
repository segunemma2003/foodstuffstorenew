<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UploadInvoiceResource\Pages;
use App\Filament\Resources\UploadInvoiceResource\RelationManagers;
use App\Models\Company;
use App\Models\UploadInvoice;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class UploadInvoiceResource extends Resource
{
    protected static ?string $model = UploadInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('company_id')
                    ->required()
                    ->label('Company')
                    ->options(
                        Company::all()->pluck('name','id')
                    )->searchable(),
                FileUpload::make('invoice_url')
                ->openable()
                    ->required()
                    ->directory('images')
                    ->disk('s3')->visibility('public')
                    ->downloadable()
                   ,
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('discount')
                    // ->required()
                    ->numeric()
                    ->default(0.00),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->sortable()
                    ->label('Company Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make("preview")
    ->label('Preview PDF')
    ->url(fn ($record) => Storage::disk('s3')->url($record->invoice_url))
    ->openUrlInNewTab()
    ->icon('heroicon-o-eye')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUploadInvoices::route('/'),
            'create' => Pages\CreateUploadInvoice::route('/create'),
            'view' => Pages\ViewUploadInvoice::route('/{record}'),
            'edit' => Pages\EditUploadInvoice::route('/{record}/edit'),
        ];
    }
}
