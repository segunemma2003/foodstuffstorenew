<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Unicodeveloper\Paystack\Facades\Paystack;

class PaymentController extends Controller
{
    public function redirectToGateway(Request $request)
    {
        try{
            $data = array(
                "amount" => 700 * 100,
                "reference" => '4g4g5485g8545jg8gj',
                "email" => 'user@mail.com',
                "currency" => "NGN",
                "orderID" => 23456,
            );
            return Paystack::getAuthorizationUrl($data);
        }catch(\Exception $e) {
            return Redirect::back()->withMessage(['msg'=>'The paystack token has expired. Please refresh the page and try again.', 'type'=>'error']);
        }
    }

    public function handlePaymentGateway(Request $request){
        // Log::info($request->all());
        $paymentDetails = Paystack::getPaymentData();
        Log::info($paymentDetails);
        // dd($paymentDetails);
        $company = "FoodStuff Store";
        if ($paymentDetails['status']) {
            $transaction = $paymentDetails['data'];
$sender = $transaction['metadata']['sender'];
            if(isset($sender) && $sender=='fss_app'){
                $company = 'FoodStuff Inventory';
            }else if(isset($sender) && $sender=='ordaly'){
                $company = 'Ordaly';
            }
            if ($transaction['status'] === 'success') {
                return view('paystack-status', [
                    'status' => 'success',
                    'company'=>$company,
                    'amount' => $transaction['amount'],
                    'paystackData' => $transaction,
                ]);
            }
        }

        return view('paystack-status', [
            'status' => 'failed',
            'company'=>$company,
            'paystackData' => $paymentDetails['data'] ?? null,
        ]);
    }
}
