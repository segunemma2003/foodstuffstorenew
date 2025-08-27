<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('unique_code')
                    ->required(),
                Forms\Components\TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('payment_method'),
                Forms\Components\TextInput::make('tax')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('payment_status')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        "pending" => "pending",
                        "in_transit" => "in_transit",
                        "in_process" => "in_process",
                        "completed" => "completed",
                    ])
                    ->required(),
                Forms\Components\TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('delivery_address')
                    ->required(),
                Forms\Components\TextInput::make('delivery_user_id')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('estimated_delivery_time'),
                Forms\Components\Textarea::make('delivery_issues')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->poll('10s')
        ->deferLoading()
        ->query(fn () => Order::query()->with('cartItems.product'))
        ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('unique_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tax')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'in_transit',
                        'primary' => 'in_process',
                        'success' => 'completed',
                    ])
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('estimated_delivery_time')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('View Cart')
                ->icon('heroicon-o-shopping-cart') // Icon for the button
                ->modalHeading('Cart Items')
                ->modalDescription('Here are all the items in this order')
                ->modalContent(fn ($record) => view('filament.modals.view-cart', [
                    'cartItems' => $record->cartItems, // cartItems already loaded with products
                ]))
                ->button(),
                Tables\Actions\Action::make('Update Status')
                ->form([
                    Forms\Components\Select::make('status')
                        ->options([
                            "pending" => "pending",
                            "in_transit" => "in_transit",
                            "in_process" => "in_process",
                            "completed" => "completed",
                        ])
                        ->default(fn ($record) => $record->status) // Set default to current status
                        ->required(),

                    Forms\Components\DateTimePicker::make('estimated_delivery_time')
                        ->label('Estimated Delivery Time')
                        ->default(fn ($record) => $record->estimated_delivery_time) // Set default to current value
                        ->required(),
                ])
                ->action(function ($record, $data) {
                    $record->update([
                        'status' => $data['status'],
                        'estimated_delivery_time' => $data['estimated_delivery_time'],
                    ]);
                })
                ->icon('heroicon-o-arrow-path') // Corrected icon
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
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
