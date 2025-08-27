<?php

namespace App\Http\Controllers;

use App\Http\Requests\CombinedCustomerRegisterRequest;
use App\Http\Requests\CustomerRegisterRequest;
use App\Models\BusinessCustomer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    public function register(CombinedCustomerRegisterRequest $request)
    {
        $names = explode(' ',  $request->contact_person_name, 2); // Split into max 2 parts

        $first_name = $names[0] ?? ''; // First part
        $last_name = $names[1] ?? '';
        $user = User::create([
            'fname' => $first_name,
            'lname' => $last_name,
            'phone' => $request->phone,
            'name' =>  $request->contact_person_name,
            'email' =>  $request->contact_person_email,
            'password' => Hash::make($request->password),
            'user_type' => 'customer'
        ]);

        $customerMember = BusinessCustomer::create([
            'user_id' => $user->id,
            'member_id' => 'CUST-' . strtoupper(uniqid()),
            'company_name' => $request->company_name,
            'phone' => $request->phone,
            'email' => $request->company_email,
            'address' => $request->address,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_email' => $request->contact_person_email,
            'contact_person_phone' => $request->contact_person_phone,
            'monthly_quota_amount' => 0,
            'status' => 'pending'
        ]);


        return response()->json([
            'message' => 'Registration and membership application submitted successfully',
            'data' => [
                'user' => $user,
                'customer_member' => $customerMember,
                'token' => $user->createToken('customer-token')->plainTextToken
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)
            ->where('user_type', 'customer')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $user->createToken('customer-token')->plainTextToken,
                'customer'=>$user->customerMember
            ]
        ]);
    }
}
