<?php

namespace App\Filament\Resources;

use App\Exports\ExportItemsByLocation;
use App\Filament\Resources\ProductQuantityResource\Pages;
use App\Filament\Resources\ProductQuantityResource\RelationManagers;
use App\Imports\UpdatePricesImport;
use App\Models\ProductQuantity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Product;
use App\Models\Location;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProductQuantityResource extends Resource
{
    protected static ?string $model = ProductQuantity::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('id')->disabled(),
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->options(Product::all()->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('location_id')
                    ->label('Location')
                    ->options(Location::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('discount')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('₦'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.size.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                // ->prefix('₦')
                    ->money("NGN")
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location')
                ->options(Location::all()->pluck('name', 'id')->toArray())
                ->preload()
                ->searchable()
                ->attribute('location_id'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

            ])
            ->headerActions([  // Moved here!
                Action::make('export')
                    ->label('Export to Excel')
                    ->icon('heroicon-o-arrow-down-tray') // Updated icon
                    ->form([
                        Forms\Components\Select::make('location_id')
                            ->label('Select Location')
                            ->options(Location::all()->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $location = Location::find($data['location_id']);
                        $filename = 'items_' . str_replace(' ', '_', strtolower($location->name)) . '.xlsx';
                        return Excel::download(new ExportItemsByLocation($data['location_id']), $filename);
                    }),


                    Action::make('import')
                    ->label('Import Excel to Update Prices')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->disk('local') // This is the disk where the file will be stored
                            ->label('Upload Excel File')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        // Get the file path stored on the 'local' disk
                        $filePath = Storage::disk('local')->path($data['file']); // Get the actual file path

                        // Import the file using Laravel Excel
                        Excel::import(new UpdatePricesImport, $filePath); // Use the correct file path

                        // Optionally delete the file after import (if needed)
                        Storage::disk('local')->delete($data['file']);

                        // Send a notification
                        Notification::make()
                            ->title('Prices Updated Successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->extremePaginationLinks();
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
            'index' => Pages\ListProductQuantities::route('/'),
            'create' => Pages\CreateProductQuantity::route('/create'),
            'view' => Pages\ViewProductQuantity::route('/{record}'),
            'edit' => Pages\EditProductQuantity::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
