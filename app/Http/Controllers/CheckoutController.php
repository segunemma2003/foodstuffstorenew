<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Checkouts;
use App\Services\PaymentService;

class CheckoutController extends Controller
{
    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
            'callback_url' => 'required|url',
            'email' => 'required|email',
            'reference' => 'nullable|string|max:100',
            'metadata' => 'nullable|array',
            'amount' => 'required|numeric|min:100',
        ]);

        DB::beginTransaction();

        try {
            // Save checkout
            $checkout = Checkouts::create([
                'email' => $validated['email'],
                'callback_url' => $validated['callback_url'],
                'reference' => $validated['reference'],
                'metadata' => json_encode($validated['metadata']),
                'status' => 'pending',
                'amount' => $validated['amount'],
            ]);

            // Prepare metadata for Paystack
            $validated['metadata']['from'] = "fss";
            $mdata = [
                "amount"    => $validated['amount'],
                "orderID" => $checkout->id,
                "reference" => $validated['reference'],
                "email"     => $validated['email'],
                "metadata" => $validated['metadata'],
            ];

            // Redirect to Paystack
            $paystackPayment = PaymentService::redirectToGatewayTwo($mdata);
            // var_dump( $paystackPayment);
            if ($paystackPayment["status"] === true) {
                DB::commit();
                return response()->json([
                    'message' => 'Checkout initialized successfully.',
                    'payment_url' => $paystackPayment["data"],
                    'reference' => $validated['reference'],
                ], 201);
            }

            DB::rollBack();
            return response()->json(['error' => 'Unable to initiate payment.'], 500);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
