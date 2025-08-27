<?php

namespace App\Http\Controllers;

use App\Mail\OtpOrdalyMail;
use App\Notifications\SendSmsNotification;
use App\Services\JusibeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class OrdalyController extends Controller
{
    protected $jusibeService;

    public function __construct(JusibeService $jusibeService)
    {
        $this->jusibeService = $jusibeService;
    }


    public function sendOtp(Request $request)
    {

        // Validate request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'username' => 'required|string',
            'otp' => 'required|numeric|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 400);
        }

        $email = $request->email;
        $username = $request->username;
        $otp = $request->otp;

        try {
            // Send email
            Mail::to($email)->queue(new OtpOrdalyMail($username, $otp));

            return response()->json([
                'message' => 'OTP sent successfully',
                'email' => $email
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send email',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function sendSms(Request $request)
    {

        // Validate request
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 400);
        }

        $phone = $request->phone;
        $message = $request->message;


        try {
            // Send email


            $response = $this->jusibeService->sendSingleSMSTermii($phone, 'FSSTORE', $message);
            return response()->json([
                'message' => 'Sent Message successfully',
                'phone' => $phone
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send message',
                'details' => $e->getMessage()
            ], 500);
        }

}
}
