<?php

namespace App\Filament\CompanyUser\Resources;

use App\Exports\EmployeesExport;
use App\Filament\CompanyUser\Pages\EmployeeReport;
use App\Filament\CompanyUser\Resources\EmployeePaymentsResource\RelationManagers\EmployeePaymentsRelationManager;
use App\Filament\CompanyUser\Resources\EmployeeResource\Pages;
use App\Filament\CompanyUser\Resources\EmployeeResource\RelationManagers;
use App\Filament\Exports\EmployeeExporter;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Filament\Actions\Action;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\MediaType;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([

                Forms\Components\Select::make('user_id')
                ->label('Employee User')
                ->options(User::all()->pluck('name', 'id'))
                ->required()
                ->searchable(),
                Forms\Components\Select::make('company_id')
            ->label('Company')
            ->options(function () {
                // Retrieve the authenticated user
                $user = Auth::user();

                // Ensure user is available and has companies
                return $user && $user->companies ? $user->companies->pluck('name', 'id') : [];
            })
            ->preload()
            ->required()
            ->searchable(),

                Forms\Components\TextInput::make('salary')
                    ->label('Salary')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('credit_limit')
                    ->label('Credit Limit')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('debit_limit')
                    ->label('Debit Limit')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('amount_paid')
                    ->label('Amount Paid')
                    ->numeric()
                    ->default(0)
                   ,
                Forms\Components\TextInput::make('balance')
                    ->label('Balance')
                    ->numeric()
                    ->default(0)
                   ,
                Forms\Components\Select::make('status')
                    ->label('Approval Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending'),
                    Forms\Components\TextInput::make('card_number')
                ->label('Card Number')
                ->disabled()
                ->visible(fn ($get) => $get('status') === 'approved'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Employee Name')->searchable(),
                Tables\Columns\TextColumn::make('company.name')->label('Company')->searchable(),
                Tables\Columns\TextColumn::make('salary')->label('Salary')->money('NGN')->searchable(),
                Tables\Columns\TextColumn::make('credit_limit')->label('Credit Limit')->money('NGN')->searchable(),
                Tables\Columns\TextColumn::make('balance')->label('Current Balance')->money('NGN')->searchable(),
                Tables\Columns\TextColumn::make('amount_paid')->label('Amount Paid')->money('NGN')->searchable(),
                Tables\Columns\TextColumn::make('card_number')->label('Card Number')->formatStateUsing(fn ($state) => implode(' ', str_split($state, 4))) // Splits into groups of 4
                ->copyable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->color(fn (string $state): string => match ($state) {
                       'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('downloadCard')
    ->label('Download Employee Card')
    ->action(function (Employee $record) {
        // Set dimensions
        $width = 600;
        $height = 350;


        $fontPath = public_path('latest/fonts/urbanist/Urbanist-Regular.otf');
        // Create a blank canvas
        $image = Image::create($width, $height)->fill('#000000');

        $image->text("Foodstuff Store Employee Card", $width / 2, 30, function($font) {
            $font->file(public_path('latest/fonts/urbanist/Urbanist-Regular.otf'));
            $font->size(24); // Adjust the size as needed
            $font->color('#FFFFFF');
            $font->align('center');
            $font->valign('top');
        });


        // Add text elements
        $image->text("Card Number: " . implode(' ', str_split($record->card_number, 4)), 30, 100, function($font) use($fontPath) {
            $font->file($fontPath); // Use the Urbanist font
            $font->size(16);
            $font->color('#FFFFFF');
        });
        $image->text("Employee Name: " . $record->user->name, 30, 150, function($font)  use($fontPath){
            $font->file($fontPath); // Use the Urbanist font
            $font->size(16);
            $font->color('#FFFFFF');
        });
        $image->text("Company: " . $record->company->name, 30, 200, function($font) use($fontPath) {
            $font->file($fontPath); // Use the Urbanist font
            $font->size(16);
            $font->color('#FFFFFF');
        });
        $image->text("Date Created: " . $record->created_at->format('Y-m-d'), 30, 250, function($font) use($fontPath) {
            $font->file($fontPath); // Use the Urbanist font
            $font->size(16);
            $font->color('#FFFFFF');
        });

        $logoPath = public_path('latest/image/FSSLOGO1-2.png'); // Update the path to your logo
        $logo = Image::read($logoPath);

        // Resize the logo if necessary
        $logo->resize(200, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        // Get the logo dimensions
        $logoWidth = $logo->width();
        $logoHeight = $logo->height();


        $image->place($logo, 'bottom', 0, 10);
        $imageData =  $image->toPng();



        $cloudinaryPath = 'cards/' . $record->id .uniqid(). '_card.png';

        // Upload the encoded image data to Cloudinary
        try {
            Storage::disk('s3')->put($cloudinaryPath, (string) $imageData, 'public');

            // Get the URL to download the image from Cloudinary
            $url = Storage::disk('s3')->url($cloudinaryPath);
            Log::info($url);
            // Return a download response directly from Cloudinary
            return Storage::disk('s3')->download($cloudinaryPath);
        } catch (\Exception $e) {
            // Log the error if upload fails
            Log::error('Cloudinary upload failed: ' . $e->getMessage());
            return back()->withErrors('Failed to download the employee card. Please try again later.');
        }
    })
    // ->openUrlInNewTab()
    ->requiresConfirmation()
    ->color('primary')
    ->icon('heroicon-o-arrow-down'),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->action(function (Employee $record) {
                        $record->status = 'approved';
                        $record->save();
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Employee $record) => $record->status !== 'approved')
                    ->color('success'),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->action(function (Employee $record) {
                        $record->status = 'rejected';
                        $record->save();
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Employee $record) => $record->status === 'approved')
                    ->color('danger'),
                Tables\Actions\Action::make('updatePayment')
                    ->label('Update Payment')
                    ->action(function (Employee $record, array $data) {
                        // Store who updated the payment
                        $id = Auth::id();
                        $user = User::whereId($id)->first();

                        // Create payment entry
                        $record->employeePayments()->create([
                            'amount' => $data['amount_paid'],
                            'payment_date' => now(),
                            'updated_by' => $user->id, // Track the user who updated the payment
                        ]);

                        // Update the balance and payment
                        $record->balance -= $data['amount_paid'];
                        $record->amount_paid += $data['amount_paid'];
                        $record->save();
                    })
                    ->form([
                        Forms\Components\TextInput::make('amount_paid')
                            ->label('Amount Paid')
                            ->numeric()
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->color('primary'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),

            ])->headerActions([

                Tables\Actions\Action::make('downloadEmployeeData')
                    ->label('Download Employee Data')
                    ->action(function () {
                        return Excel::download(new EmployeesExport, 'employees_data.xlsx');
                    })
                    ->color('primary')
                    ->icon('heroicon-o-arrow-down'),
                // ExportAction::make()
                // ->exporter(EmployeeExporter::class)->formats([
                //     ExportFormat::Xlsx,
                // ])
                // ->filedisk('s3')->visibility('public'),
               Tables\Actions\Action::make("download")
               ->url(fn () => route('employee.report.download',[
                    'month' => Carbon::now()->month,
                    'year' => Carbon::now()->year,
                ] ))
                ->openUrlInNewTab()
               ->label('Download Excel Report'),

               Tables\Actions\Action::make("view")
               ->url(fn () =>
                EmployeeReport::getUrl([
                    'month' => Carbon::now()->month,
                    'year' => Carbon::now()->year
                ])
               )
                // ->openUrlInNewTab()
               ->label('View Report'),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            EmployeePaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
