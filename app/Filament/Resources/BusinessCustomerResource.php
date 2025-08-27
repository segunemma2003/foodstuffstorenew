<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessCustomerResource\Pages;
use App\Filament\Resources\BusinessCustomerResource\RelationManagers;
use App\Models\BusinessCustomer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BusinessCustomerResource extends Resource
{
    protected static ?string $model = BusinessCustomer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('member_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('address')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('contact_person_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('contact_person_email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('contact_person_phone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('monthly_quota_amount')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('available_quota')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('status')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('member_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_person_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_person_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_person_phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('monthly_quota_amount')
                    ->numeric()
                    ->sortable()
                    ->money('NGN'),
                Tables\Columns\TextColumn::make('available_quota')
                    ->numeric()
                    ->sortable()
                    ->money('NGN'),
                    Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
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
                Tables\Filters\SelectFilter::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('updateStatus')
                ->form([
                    Forms\Components\Radio::make('status')
                        ->options([
                            'approved' => 'Approve',
                            'rejected' => 'Reject',
                        ])
                        ->required()
                        ->inline(),

                    Forms\Components\TextInput::make('monthly_quota_amount')
                        ->numeric()
                        ->required()
                        ->visible(fn (Forms\Get $get): bool => $get('status') === 'approved')
                        ->label('Monthly Quota Amount'),

                    Forms\Components\TextInput::make('available_quota')
                        ->numeric()
                        ->required()
                        ->visible(fn (Forms\Get $get): bool => $get('status') === 'approved')
                        ->label('Available Quota'),
                ])
                ->action(function (Model $record, array $data) {
                    $updateData = ['status' => $data['status']];

                    if ($data['status'] === 'approved') {
                        $updateData['monthly_quota_amount'] = $data['monthly_quota_amount'];
                        $updateData['available_quota'] = $data['available_quota'];
                    } else {
                        // When rejecting, you might want to set quotas to 0
                        $updateData['monthly_quota_amount'] = 0;
                        $updateData['available_quota'] = 0;
                    }

                    $record->update($updateData);
                })
                ->modalHeading('Update Application Status')
                ->modalSubmitActionLabel('Update')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('primary')
                ->visible(fn (Model $record): bool => $record->status === 'pending'),
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
            'index' => Pages\ListBusinessCustomers::route('/'),
            'create' => Pages\CreateBusinessCustomer::route('/create'),
            'view' => Pages\ViewBusinessCustomer::route('/{record}'),
            'edit' => Pages\EditBusinessCustomer::route('/{record}/edit'),
        ];
    }
}
