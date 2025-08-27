<?php

namespace App\Http\Controllers;

use App\Models\DebtPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user()->customerMember;

        if (!$customer) {
            return response()->json(['message' => 'Customer membership not found'], 404);
        }

        return response()->json([
            'data' => [
                'total_debt' => $customer->total_debt,
                'payments' => $customer->debtPayments()->orderBy('created_at', 'desc')->get()
            ]
        ]);
    }

    public function createPaymentLink(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100'
        ]);

        $customer = $request->user()->customerMember;

        if (!$customer) {
            return response()->json(['message' => 'Customer membership not found'], 404);
        }

        if ($request->amount > $customer->total_debt) {
            return response()->json([
                'message' => 'Payment amount exceeds total debt',
                'errors' => ['amount' => 'You cannot pay more than your total debt.']
            ], 422);
        }

        // Create payment record
        $payment = DebtPayment::create([
            'customer_member_id' => $customer->id,
            'amount' => $request->amount,
            'status' => 'pending',
            'reference' => 'DP-' . strtoupper(uniqid())
        ]);

        // Generate payment link (this would integrate with your payment gateway)
        $paymentLink = $this->generatePaymentLink($payment);

        return response()->json([
            'payment_link' => $paymentLink,
            'payment_reference' => $payment->reference
        ]);
    }

    public function webhook(Request $request)
    {
        // Validate webhook request (this would be specific to your payment gateway)
        $valid = $this->validateWebhook($request);

        if (!$valid) {
            return response()->json(['message' => 'Invalid webhook'], 400);
        }

        $reference = $request->reference;
        $payment = DebtPayment::where('reference', $reference)->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        if ($payment->status === 'completed') {
            return response()->json(['message' => 'Payment already processed']);
        }

        // Update payment status
        $payment->update([
            'status' => 'completed',
            'paid_at' => now(),
            'gateway_response' => json_encode($request->all())
        ]);

        // Update customer debt
        $customer = $payment->customerMember;
        $customer->debts()->create([
            'amount' => -$payment->amount,
            'balance' => $customer->total_debt - $payment->amount,
            'description' => 'Debt payment - ' . $payment->reference
        ]);

        // Send notification
        $this->sendPaymentConfirmation($customer, $payment);

        return response()->json(['message' => 'Webhook processed']);
    }

    private function generatePaymentLink($payment)
    {
        // This would integrate with your payment gateway
        // For example, with Paystack:
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.paystack.co/transaction/initialize', [
            'email' => $payment->customerMember->contact_person_email,
            'amount' => $payment->amount * 100, // Paystack uses kobo
            'reference' => $payment->reference,
            'callback_url' => config('app.url') . '/api/debt/payment/callback',
            'metadata' => [
                'payment_id' => $payment->id,
                'customer_id' => $payment->customer_member_id
            ]
        ]);

        $data = $response->json();
        return $data['data']['authorization_url'];
    }

    private function validateWebhook($request)
    {
        // Implement webhook validation for your payment gateway
        return true;
    }

    private function sendPaymentConfirmation($customer, $payment)
    {
        // Send SMS/email confirmation
    }
}
