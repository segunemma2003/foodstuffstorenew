<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentStatusController extends Controller
{
    public function updatePaymentStatus(Request $request)
    {
        try {
            $request->validate([
                'po_number' => 'required|string',
                'payment_status' => 'required|in:unpaid,partially_paid,fully_paid'
            ]);

            $poNumber = $request->po_number;
            $paymentStatus = $request->payment_status;

            Log::info('Payment status update received', [
                'po_number' => $poNumber,
                'payment_status' => $paymentStatus
            ]);

            // Update your database here
            // Example:
            // $order = Order::where('po_number', $poNumber)->first();
            // if ($order) {
            //     $order->update([
            //         'payment_status' => $paymentStatus,
            //         'updated_at' => now()
            //     ]);
            // }

            // Mock response matching the external API format
            $response = [
                'success' => true,
                'message' => 'Payment status updated successfully',
                'data' => [
                    'po_number' => $poNumber,
                    'po_id' => rand(100, 999), // Mock ID
                    'payment_status' => $paymentStatus,
                    'total_paid_amount' => $paymentStatus === 'fully_paid' ? 50000.00 : 0.00,
                    'outstanding_amount' => $paymentStatus === 'fully_paid' ? 0.00 : 50000.00,
                    'net_amount' => 50000.00,
                    'updated_at' => now()->toISOString()
                ]
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Error updating payment status', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status'
            ], 500);
        }
    }
}
