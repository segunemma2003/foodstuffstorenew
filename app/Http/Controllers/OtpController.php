<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Jobs\SendOtpSmsJob;
use App\Jobs\SendOtpEmailJob;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;






class OtpController extends Controller
{



    /**
 * @OA\Post(
 *     path="/send-otp",
 *     summary="Send an OTP to a user's phone",
 *     tags={"OTP"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="phone", type="string", example="08012345678", description="Phone number to send the OTP to")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="OTP sent successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="OTP sent successfully"),
 *             @OA\Property(property="OTP", type="object", description="Details of the OTP")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="object", description="Validation errors")
 *         )
 *     ),
 *     @OA\Response(
 *         response=429,
 *         description="Too many requests",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="You are sending OTP requests too frequently. Please try again later.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Failed to send OTP. Please try again later.")
 *         )
 *     )
 * )
 */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|unique:users,phone',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $phone = $request->input('phone');

        $rateLimitKey = 'otp_rate_limit_' . $phone;
        if (Cache::has($rateLimitKey)) {
            return response()->json(['error' => 'You are sending OTP requests too frequently. Please try again later.'], 429);
        }
        $userQuery = User::query();
        $userQuery->where('phone', $phone);

        $userExists = $userQuery->exists();

        if ($userExists) {
            return response()->json(['error' => 'User already exists'], 409);
        }



        $otp = Otp::getPendingOtp($phone);



        if (!$otp) {
            $otp = Otp::generateOtp($phone);
        }

        try {
            SendOtpSmsJob::dispatch($otp->identifier, $otp->otp);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch OTP SMS job: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send OTP. Please try again later.'], 500);
        }

        // Set rate limit cache
        Cache::put($rateLimitKey, true, 60); // 1 minute cache

        return response()->json(['message' => 'OTP sent successfully', 'OTP'=> $otp]);
    }


/**
 * @OA\Post(
 *     path="/verify-otp",
 *     summary="Verify a sent OTP",
 *     tags={"OTP"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="phone", type="string", example="08012345678", description="Phone number associated with the OTP"),
 *             @OA\Property(property="otp", type="string", example="123456", description="OTP to verify")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="OTP verified successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="OTP verified successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="object", description="Validation errors")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid or expired OTP",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Invalid or expired OTP")
 *         )
 *     )
 * )
 */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|unique:users,phone',
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $phone = $request->input('phone');
        $otpValue = $request->input('otp');

        $otp = Otp::where('identifier', $phone)
                    ->where('otp', $otpValue)
                    ->first();

        if (!$otp || $otp->isExpired()) {
            return response()->json(['error' => 'Invalid or expired OTP'], 400);
        }

        $otp->markAsVerified();

        return response()->json(['message' => 'OTP verified successfully']);
    }



/**
 * @OA\Post(
 *     path="/forgot-password",
 *     summary="Verify a sent OTP",
 *     tags={"OTP"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="phone", type="string", example="08012345678", description="Phone number associated with the OTP"),
 *             @OA\Property(property="otp", type="string", example="123456", description="OTP to verify")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="OTP verified successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="OTP verified successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="object", description="Validation errors")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid or expired OTP",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Invalid or expired OTP")
 *         )
 *     )
 * )
 */

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required', // can be either phone or email
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $identifier = $request->input('identifier');

        $rateLimitKey = 'otp_rate_limit_' . $identifier;
        if (Cache::has($rateLimitKey)) {
            return response()->json(['error' => 'You are sending OTP requests too frequently. Please try again later.'], 429);
        }

        $user = User::where('phone', $identifier)->orWhere('email', $identifier)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $otp = Otp::getPendingOtp($identifier);

        if (!$otp) {
            $otp = Otp::generateOtp($identifier);
        }

        try {
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                // Send OTP via Email if the identifier is an email
                SendOtpEmailJob::dispatch($identifier, $otp->otp);
            } else {
                // Send OTP via SMS if the identifier is a phone number
                SendOtpSmsJob::dispatch($identifier, $otp->otp);
            }
        } catch (\Exception $e) {
            Log::error('Failed to dispatch OTP job: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send OTP. Please try again later.'], 500);
        }

        // Set rate limit cache
        Cache::put($rateLimitKey, true, 60); // 1 minute cache

        return response()->json(['message' => 'OTP sent successfully']);
    }



/**
 * @OA\Post(
 *     path="/reset-password",
 *     summary="Reset password using OTP",
 *     tags={"OTP"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="identifier", type="string", example="08012345678", description="Phone or email associated with the OTP"),
 *             @OA\Property(property="otp", type="string", example="123456", description="OTP to verify"),
 *             @OA\Property(property="new_password", type="string", example="newSecurePassword", description="New password for the user")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Password reset successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Password reset successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="object", description="Validation errors")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid or expired OTP",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Invalid or expired OTP")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="User not found")
 *         )
 *     )
 * )
 */

    // Verify OTP and Reset Password
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required', // can be either phone or email
            'otp' => 'required',
            'new_password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $identifier = $request->input('identifier');
        $otpValue = $request->input('otp');
        $newPassword = $request->input('new_password');

        $otp = Otp::where('identifier', $identifier)
                    ->where('otp', $otpValue)
                    ->first();

        if (!$otp || $otp->isExpired()) {
            return response()->json(['error' => 'Invalid or expired OTP'], 400);
        }

        $user = User::where('phone', $identifier)->orWhere('email', $identifier)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->update(['password' => Hash::make($newPassword)]);
        $otp->markAsVerified();

        return response()->json(['message' => 'Password reset successfully']);
    }
}
