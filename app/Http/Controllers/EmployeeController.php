<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Laravel\Facades\Image;

class EmployeeController extends Controller
{

    public function getBalanceInfo()
    {
        try {
            $employee = Employee::where('user_id', Auth::id())->first();

            if (!$employee) {
                return response()->json(['message' => 'Employee record not found'], 404);
            }

            $availableBalance = $employee->credit_limit;
            $currentBalance = $employee->balance;
            $amountOwed = $employee->credit_limit - $employee->balance;

            return response()->json([
                'card_number' => $employee->card_number,
                'available_balance' => $currentBalance,
                'current_balance' => $employee->credit_limit,
                'amount_paid' => $employee->amount_paid,
                'amount_owed' => $amountOwed,
                'pin_set'=> !is_null($employee->pin_code),
                'username'=> $employee->user->name,
                'pin_code'=>$employee->pin_code,
                'company_name'=> $employee->company->name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve balance information',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function setPinCode(Request $request)
    {
        try {
            $request->validate([
                'pin_code' => 'required|digits:4',
            ]);

            $employee = Employee::where('user_id', Auth::id())->firstOrFail();

            $employee->pin_code = $request->pin_code;
            $employee->save();

            return response()->json(['message' => 'PIN code set successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to set PIN code',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getApplicationStatus()
    {
        try {
            $employee = Employee::where('user_id', Auth::id())->first();

            if (!$employee) {
                return response()->json(['message' => 'No application found'], 404);
            }

            return response()->json([
                'status' => $employee->status,
                'message' => 'Application status retrieved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve application status',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function applyForCard(Request $request)
    {
        try {
            $request->validate([
                'company_id' => 'required|exists:companies,id',
                'salary' => 'required|numeric',
            ]);

            // Calculate credit limit and debit limit based on salary
            $salary = $request->salary;
            $creditLimit = $salary / 2; // 50% of salary
            $debitLimit = $salary * 0.1; // 10% of salary

            $employee = Employee::create([
                'user_id' => Auth::id(),
                'company_id' => $request->company_id,
                'salary' => $salary,
                'credit_limit' => $creditLimit,
                'debit_limit' => $debitLimit,
                'status' => 'pending',
            ]);

            return response()->json([
                'message' => 'Employee card application submitted successfully',
                'employee' => $employee,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to submit employee card application',
                'message' => $e->getMessage(),
            ], 500);
        }



    }

    public function downloadAtmCard(Employee $employee)
    {
        // Define card dimensions
        $width = 600;
        $height = 350;

        // Create a black background image
        $image = Image::canvas($width, $height, '#000000');

        // Add card details with white text
        $image->text("Card Number: " . implode(' ', str_split($employee->card_number, 4)), 30, 100, function($font) {
            $font->size(24);
            $font->color('#FFFFFF');
        });
        $image->text("Employee Name: " . $employee->user->name, 30, 150, function($font) {
            $font->size(24);
            $font->color('#FFFFFF');
        });
        $image->text("Company: " . $employee->company->name, 30, 200, function($font) {
            $font->size(24);
            $font->color('#FFFFFF');
        });
        $image->text("Date Created: " . $employee->created_at->format('Y-m-d'), 30, 250, function($font) {
            $font->size(24);
            $font->color('#FFFFFF');
        });

        // Return the image as a downloadable response
        return $image->response('png');
    }
}
