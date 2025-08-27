<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessPaymentRequest;
use App\Models\BusinessCustomer;
use App\Models\BusinessOrder;
use App\Models\BusinessProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessPaymentController extends Controller
{
    public function processPayment(ProcessPaymentRequest $request)
    {
        // Validate customer membership
        $customer = BusinessCustomer::where('member_id', $request->customer_member_id)
            ->where('status', 'approved')
            ->first();

        if (!$customer) {
            return response()->json([
                'message' => 'Invalid or unapproved customer membership ID',
                'errors' => ['customer_member_id' => 'The provided customer member ID is invalid or not approved.']
            ], 422);
        }

        // Get the business
        $business = $request->attributes->get('api_business');
        if (!$business) {
            return response()->json([
                'message' => 'Business not found',
                'errors' => ['business' => 'The authenticated user is not associated with a business.']
            ], 422);
        }

        // Validate items and calculate total
        $items = $request->items;
        $calculatedTotal = 0;
        $orderItems = [];

        // Validate all items before processing
        foreach ($items as $item) {
            $product = BusinessProduct::where('id', $item['product_id'])
                ->where('business_id', $business->id)
                ->first();

            if (!$product) {
                return response()->json([
                    'message' => 'Invalid product ID',
                    'errors' => ['product_id' => 'Product with ID ' . $item['product_id'] . ' not found or not owned by your business.']
                ], 422);
            }

            if ($product->price != $item['unit_price']) {
                return response()->json([
                    'message' => 'Price mismatch',
                    'errors' => ['unit_price' => 'The price for product ' . $product->name . ' does not match.']
                ], 422);
            }

            $itemTotal = $product->price * $item['quantity'];
            $calculatedTotal += $itemTotal;

            $orderItems[] = [
                'business_product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'total_price' => $itemTotal
            ];
        }

        if ($calculatedTotal > $request->amount) {
            return response()->json([
                'message' => 'Price mismatch',
                'errors' => ['error in calculations']
            ], 422);
        }

        // Check available quota
        if ($customer->available_quota < $request->amount) {
            return response()->json([
                'message' => 'Insufficient quota',
                'errors' => ['quota' => 'The customer does not have enough available quota for this purchase.']
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Deduct from quota
            $customer->decrement('available_quota', $request->amount);

            // Record quota usage
            $customer->quotaHistory()->create([
                'amount' => -$request->amount,
                'balance' => $customer->available_quota,
                'description' => 'Payment for order'
            ]);

            // Record debt
            $customer->debts()->create([
                'amount' => $request->amount,
                'balance' => $customer->debts()->sum('amount') + $request->amount,
                'description' => 'Payment for order'
            ]);

            // Create order
            $order = BusinessOrder::create([
                'business_customer_id' => $customer->id,
                'business_id' => $business->id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'total_amount' => $request->amount,
                'status' => 'completed'
            ]);

            // Add order items
            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            DB::commit();

            return response()->json([
                'message' => 'Payment and order processed successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    // 'remaining_quota' => $customer->available_quota,
                    // 'total_debt' => $customer->total_debt
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Payment processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
