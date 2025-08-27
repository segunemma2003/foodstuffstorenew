<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"fname", "lname", "phone", "email", "password"},
     *             @OA\Property(property="fname", type="string", example="John"),
     *             @OA\Property(property="lname", type="string", example="Doe"),
     *             @OA\Property(property="current_location", type="string", example="New York"),
     *             @OA\Property(property="residential", type="string", example="123 Main St"),
     *             @OA\Property(property="phone", type="string", example="1234567890"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(response=422, description="Validation errors"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function register(Request $request)
    {
         // Validate the request data
    $validator = Validator::make($request->all(), [
        'fname' => 'required|string',
        'lname' => 'required|string',
        'current_location' => 'nullable|string',
        'residential' => 'nullable|string',
        'phone' => 'required|string|unique:users,phone',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|confirmed',
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    try {
        // Create the user
        $user = User::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'name' => $request->fname . ' ' . $request->lname,
            'phone' => $request->phone,
            'email' => $request->email,
            'current_location' => $request->current_location,
            'residential' => $request->residential,
            'password' => Hash::make($request->password),
            'wallet_balance' => 0.00,
            'points' => 0.00,
        ]);

        $token = $user->createToken('authToken')->plainTextToken;

        $defaultCardNumber = '000000000000000';
        $defaultAvailableBalance = 0.00;
        $defaultCurrentBalance = 0.00; // Change this according to your logic
        $defaultOwedAmount = 0.00;
        $defaultStatusEmployee = false; // Assuming not approved yet


        $user->addresses()->create([
            'address' => $request->residential,
            'residential_address'=>$request->residential,
            'set_default' => $request->set_default ?? true,
            'name'=> "Home Address",
            'state'=>  "1",
            'phone' => $request->phone
        ]);

        // Return success response with employee defaults
        return response()->json([
            'user' => $user,
            'token' => $token,
            'employee_card_number' => $defaultCardNumber,
            'available_balance' => $defaultAvailableBalance,
            'current_balance' => $defaultCurrentBalance,
            'owed_amount' => $defaultOwedAmount,
            'pin_code'=> "0000",
            'status_employee' => $defaultStatusEmployee,
            'message' => 'User registered successfully'
        ], 201);
        } catch (\Exception $e) {
            // Log the exception and return error response
            Log::error('User registration failed: ' . $e->getMessage());
            return response()->json(['error' => 'User registration failed. Please try again later.'], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Log in a user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone", "password"},
     *             @OA\Property(property="phone", type="string", example="1234567890"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful"),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }


        $employee = $user->employee; // Assuming User has a one-to-one relationship with Employee

        // Prepare card number, balances, and approval status
        $cardNumber = $employee ? $employee->card_number : '0000000000000000';
        $availableBalance = $employee ? $employee->balance : 0.00; // Assuming balance is stored in Employee
        $currentBalance = $employee ? $employee->credit_limit : 0.00; // Replace with actual current balance logic
        $owedAmount = $employee ? $employee->amount_paid : 0.00; // Assuming this is how owed amount is defined
        $statusEmployee = $employee && $employee->status === 'approved'; // Check if approved
        $pinCode =  $employee ? $employee->pin_code : "0000";

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
            'employee_card_number' => $cardNumber,
            'available_balance' => $availableBalance,
            'current_balance' => $currentBalance,
            'owed_amount' => $owedAmount,
            'pin_code'=> $pinCode,
            'status_employee' => $statusEmployee,
        ], 200);
    }
/**
     * @OA\Post(
     *     path="/api/change-password",
     *     summary="Change the password of the authenticated user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "new_password"},
     *             @OA\Property(property="current_password", type="string", example="oldpassword123"),
     *             @OA\Property(property="new_password", type="string", example="newpassword123"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password changed successfully"),
     *     @OA\Response(response=400, description="Current password is incorrect"),
     *     @OA\Response(response=422, description="Validation errors"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */

public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|confirmed',
        ]);

        $user = User::whereId(Auth::user()->id)->first();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json(['message' => 'Password changed successfully'], 200);
    }
}
