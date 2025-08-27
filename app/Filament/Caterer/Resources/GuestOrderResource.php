<?php

namespace App\Filament\Caterer\Resources;

use App\Filament\Caterer\Resources\GuestOrderResource\Pages;
use App\Filament\Caterer\Resources\GuestOrderResource\RelationManagers;
use App\Models\GuestOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GuestOrderResource extends Resource
{
    protected static ?string $model = GuestOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->striped()
        ->deferLoading()
        ->poll('10s')
            ->columns([
                TextColumn::make('table_number')->sortable()->searchable(),
                TextColumn::make('starter')->sortable()->searchable()->label('Soup'),
                TextColumn::make('main')->sortable()->searchable(),
                TextColumn::make('protein')->sortable()->searchable(),
                TextColumn::make('sides')->sortable()->searchable(),
                TextColumn::make('swallow')->sortable()->searchable(),
                ToggleColumn::make('status')->label('Served')
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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

    public static function  infolist(Infolist $infolist):Infolist{
        return $infolist
        ->schema([
            TextEntry::make('table_number'),
            TextEntry::make('starter')->label('Soup'),
            TextEntry::make('main'),
            TextEntry::make('protein'),
            TextEntry::make('sides'),
            TextEntry::make('swallow')
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuestOrders::route('/'),
            'create' => Pages\CreateGuestOrder::route('/create'),
            'view' => Pages\ViewGuestOrder::route('/{record}'),
            'edit' => Pages\EditGuestOrder::route('/{record}/edit'),
        ];
    }
}
