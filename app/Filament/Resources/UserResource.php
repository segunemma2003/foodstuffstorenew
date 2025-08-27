<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('fname')
                ->label("First Name")
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('lname')
            ->label("Last Name")
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('current_location')
                ->maxLength(255),
            Forms\Components\TextInput::make('residential')
                ->maxLength(255),
            Forms\Components\TextInput::make('phone')
                ->tel()
                ->unique(table: User::class)
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('email')
                ->email()
                ->unique(table: User::class)
                ->required()
                ->maxLength(255),
            Forms\Components\FileUpload::make('image')
            ->directory('images')
            ->disk('s3')->visibility('public'),

            Forms\Components\TextInput::make('password')
                ->password()
                ->required()
                // ->hiddenOn('edit')
                ->maxLength(255),

            Forms\Components\Select::make('user_type')
                ->required()
                 ->options([
                    "user" => "user",
                    "staff"=>"staff",
                    "company" => "company"
                ])

                ->default('user'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fname')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lname')
                    ->searchable(),
                    Tables\Columns\TextColumn::make('user_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('wallet_balance')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('referral_code')
                    ->searchable(),
            ])
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
            ]) ->headerActions([
                Action::make('Export to Excel')
                    ->icon('heroicon-s-document')
                    ->action(fn () => static::exportToExcel()),
            ]);
    }


    public static function exportToExcel()
{
    return Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
        public function collection()
        {
            return \App\Models\User::all(['name', 'phone', 'email']);
        }

        public function headings(): array
        {
            return ['Name', 'Phone', 'Email'];
        }
    }, 'users.xlsx');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
