<?php

namespace App\Rest\Controllers;

use App\Rest\Controller as RestController;
use \Lomkit\Rest\Http\Requests\RestRequest;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;

class OrderController extends RestController
{
    /**
     * The resource the controller corresponds to.
     *
     * @var class-string<\Lomkit\Rest\Http\Resource>
     */
    public static $resource = \App\Rest\Resources\OrderResource::class;


    public function mutateQuery(RestRequest $request, \Illuminate\Contracts\Database\Eloquent\Builder $query) {
        $requestData = $request->validated();

        $order = Order::create([
            'unique_code' => $requestData['unique_code'],
            'subtotal' => $requestData['subtotal'],
            'payment_method' => $requestData['payment_method'],
            'tax' => $requestData['tax'],
            'user_id' => Auth::user()->id,
            'payment_status' => $requestData['payment_status'],
            // 'status' => $requestData['status'],
            'total_amount' => $requestData['total_amount'],
            'delivery_address' => $requestData['delivery_address'],
            'delivery_user_id' => $requestData['delivery_user_id'],
            'estimated_delivery_time' => $requestData['estimated_delivery_time'],
            'delivery_issues' => $requestData['delivery_issues'],
        ]);

        // Create associated cart items
        foreach ($requestData['cart_items'] as $item) {
            Cart::create([
                'product_id' => $item['product_id'],
                'order_id' => $order->id,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['total'],
            ]);
        }


        Transaction::create([
            'transactionable_id' => $order->id,
            'transactionable_type' => Order::class,
            'unique_code' => $requestData['transaction_unique_code'],
            'user_id' => $requestData['user_id'],
            'type' => $requestData['transaction_type'],
            'payment_method' => $requestData['transaction_payment_method'],
            'amount' => $order->total_amount,
            'status' => $requestData['transaction_status'],
            'payment_source' => $requestData['transaction_payment_source'],
            'description' => $requestData['transaction_description'],
        ]);

        return $query;


        // return $query;
    }
}
