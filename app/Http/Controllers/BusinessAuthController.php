<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessRegisterRequest;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class BusinessAuthController extends Controller
{
    //
        public function register(BusinessRegisterRequest $request)
        {
            $names = explode(' ', $request->contact_person_name, 2); // Split into max 2 parts

        $first_name = $names[0] ?? ''; // First part
        $last_name = $names[1] ?? '';
        $user = User::create([
            'fname' => $first_name,
            'lname' => $last_name,
            'phone' => $request->phone,
            'name' => $request->contact_person_name,
            'email' =>  $request->contact_person_email,
            'password' => Hash::make($request->password),
            'user_type' => 'business'
        ]);

        $business = Business::create([
            'user_id' => $user->id,
            'name' => $request->company_name,
            'phone' => $request->phone,
            'email' => $request->company_email,
            'address' => $request->address,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_email' => $request->contact_person_email,
            'contact_person_phone' => $request->contact_person_phone,
            'status' => 'pending'
        ]);


            return response()->json([
                'message' => 'Business registered successfully',
                'data' => [
                    'user' => $user,
                    'token' => $user->createToken('business-token')->plainTextToken
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
                ->where('user_type', 'business')
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
                    'token' => $user->createToken('business-token')->plainTextToken
                ]
            ]);
        }

}
