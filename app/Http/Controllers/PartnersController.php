<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\BusinessCustomer;
use App\Models\BusinessOrder;
use App\Models\BusinessProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PartnersController extends Controller
{
    public function store(StoreOrderRequest $request)
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
        $business = $request->user()->business;
        if (!$business) {
            return response()->json([
                'message' => 'Business not found',
                'errors' => ['business' => 'The authenticated user is not associated with a business.']
            ], 422);
        }

        // Check available quota
        $totalAmount = collect($request->items)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        if ($customer->available_quota < $totalAmount) {
            return response()->json([
                'message' => 'Insufficient quota',
                'errors' => ['quota' => 'The customer does not have enough available quota for this purchase.']
            ], 422);
        }

        // Validate items and calculate total
        $items = $request->items;
        $calculatedTotal = 0;
        $orderItems = [];

        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                $product = BusinessProduct::where('id', $item['product_id'])
                    ->where('business_id', $business->id)
                    ->first();

                if (!$product) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Invalid product ID',
                        'errors' => ['product_id' => 'Product with ID ' . $item['product_id'] . ' not found or not owned by your business.']
                    ], 422);
                }

                if ($product->price != $item['price']) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Price mismatch',
                        'errors' => ['price' => 'The price for product ' . $product->name . ' does not match.']
                    ], 422);
                }

                $calculatedTotal += $product->price * $item['quantity'];
                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total_price' => $product->price * $item['quantity']
                ];
            }

            if ($calculatedTotal != $request->total_amount) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Total amount mismatch',
                    'errors' => ['total_amount' => 'The calculated total does not match the provided total amount.']
                ], 422);
            }

            // Create the order
            $order = BusinessOrder::create([
                'customer_member_id' => $customer->id,
                'business_id' => $business->id,
                'order_number' => 'ORD-' . Str::upper(Str::random(8)),
                'total_amount' => $calculatedTotal,
                'status' => 'pending'
            ]);

            // Add order items
            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            // Deduct from quota
            $customer->decrement('available_quota', $calculatedTotal);

            // Record quota usage
            $customer->quotaHistory()->create([
                'amount' => -$calculatedTotal,
                'balance' => $customer->available_quota,
                'description' => 'Purchase for order ' . $order->order_number
            ]);

            // Record debt
            $customer->debts()->create([
                'amount' => $calculatedTotal,
                'balance' => $customer->debts()->sum('amount') + $calculatedTotal,
                'description' => 'Purchase for order ' . $order->order_number
            ]);

            // Send SMS notification
            $this->sendSmsNotification($customer, $order, $calculatedTotal);

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'request_id' => Str::uuid(),
                    'remaining_quota' => $customer->available_quota
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Order processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $business = $request->user()->business;
        $order = BusinessOrder::where('id', $id)
            ->where('business_id', $business->id)
            ->firstOrFail();

        $order->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Order status updated',
            'data' => $order
        ]);
    }

    public function show($id)
    {
        $order = BusinessOrder::with(['customerMember', 'items.product', 'business'])
            ->findOrFail($id);

        return response()->json([
            'data' => $order
        ]);
    }

    public function customerOrders(Request $request)
    {
        $customer = $request->user()->customerMember;

        if (!$customer) {
            return response()->json(['message' => 'Customer membership not found'], 404);
        }

        $orders = $customer->orders()
            ->with(['business', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $orders
        ]);
    }

    public function businessOrders(Request $request)
    {
        $business = $request->user()->business;

        if (!$business) {
            return response()->json(['message' => 'Business not found'], 404);
        }

        $orders = $business->orders()
            ->with(['customerMember', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $orders
        ]);
    }

    private function sendSmsNotification($customer, $order, $amount)
    {
        // Implement SMS sending logic here
        // This is a placeholder for actual SMS integration
        $message = "Hello {$customer->contact_person_name}, your order #{$order->order_number} for ₦{$amount} has been processed. Remaining quota: ₦{$customer->available_quota}.";

        // Actual SMS sending code would go here
    }
}
