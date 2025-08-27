<?php

namespace App\Filament\CompanyUser\Resources\EmployeeResource\Pages;

use App\Filament\CompanyUser\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    // public static function mutateBeforeSave($record)
    // {
    //     // Generate a unique 16-digit card number if status is "approved"
    //     // if ($record->status === 'approved' && is_null($record->card_number)) {
    //         $record->card_number = self::generateUniqueCardNumber();
    //     // }
    // }


    protected function mutateFormDataBeforeCreate(array $data): array
    {

         $data['card_number']=  self::generateUniqueCardNumber();
         $data['balance'] = $data['credit_limit'] ?? 0;
        return $data;
    }

    private static function generateUniqueCardNumber()
    {
        do {
            $cardNumber = mt_rand(1000000000000000, 9999999999999999); // 16 digits
        } while (Employee::where('card_number', $cardNumber)->exists());

        return $cardNumber;
    }
}
