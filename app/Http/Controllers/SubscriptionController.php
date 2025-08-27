<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

class SubscriptionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/subscriptions",
     *     summary="Subscribe to a membership plan",
     *     description="Create a new subscription and record the transaction.",
     *     tags={"Subscription"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="membership_plan_id", type="integer", example=2),
     *             @OA\Property(property="payment_method", type="string", example="credit_card")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Subscription successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Subscription successful"),
     *             @OA\Property(property="subscription", type="object"),
     *             @OA\Property(property="transaction", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to subscribe",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to subscribe")
     *         )
     *     )
     * )
     */
    public function subscribe(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'membership_plan_id' => 'required|exists:membership_plans,id',
                'payment_method' => 'required|string', // Add payment method to the validation
            ]);

            $membershipPlan = MembershipPlan::findOrFail($validated['membership_plan_id']);

            // Create subscription
            $subscription = Subscription::create([
                'user_id' => $validated['user_id'],
                'membership_plan_id' => $validated['membership_plan_id'],
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'is_active' => true,
            ]);

            // Create transaction with polymorphic relation
            $transaction = Transaction::create([
                'user_id' => $validated['user_id'],
                'transactionable_id' => $subscription->id,
                'transactionable_type' => Subscription::class,
                'type' => 'debit', // Assuming it's a debit transaction
                'payment_method' => $validated['payment_method'],
                'amount' => $membershipPlan->price,
                'status' => 'completed', // or pending, failed, etc.
                'description' => 'Subscription to ' . $membershipPlan->name,
            ]);

            return response()->json([
                'message' => 'Subscription successful',
                'subscription' => $subscription,
                'transaction' => $transaction,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to subscribe: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to subscribe'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/subscriptions/{subscriptionId}/cancel",
     *     summary="Cancel a subscription",
     *     description="Cancel an active subscription and record the transaction.",
     *     tags={"Subscription"},
     *     @OA\Parameter(
     *         name="subscriptionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription canceled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Subscription canceled successfully"),
     *             @OA\Property(property="subscription", type="object"),
     *             @OA\Property(property="transaction", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Subscription is already inactive",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Subscription is already inactive")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to cancel subscription",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to cancel subscription")
     *         )
     *     )
     * )
     */
    public function cancelSubscription(Request $request, $subscriptionId)
    {
        try {
            $subscription = Subscription::findOrFail($subscriptionId);

            if (!$subscription->is_active) {
                return response()->json(['message' => 'Subscription is already inactive'], 400);
            }

            // Update subscription to inactive
            $subscription->is_active = false;
            $subscription->end_date = now();
            $subscription->save();

            // Create a transaction for the cancellation
            $transaction = Transaction::create([
                'user_id' => $subscription->user_id,
                'transactionable_id' => $subscription->id,
                'transactionable_type' => Subscription::class,
                'type' => 'credit', // Assuming it's a credit transaction for refund
                'amount' => 0, // Assuming no refund, or you can calculate a prorated refund amount
                'status' => 'completed',
                'description' => 'Cancellation of subscription ' . $subscription->membershipPlan->name,
            ]);

            return response()->json([
                'message' => 'Subscription canceled successfully',
                'subscription' => $subscription,
                'transaction' => $transaction,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to cancel subscription: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to cancel subscription'], 500);
        }
    }
}
