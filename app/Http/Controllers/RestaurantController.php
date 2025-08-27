<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\RestaurantOrder;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RestaurantController extends Controller
{

    public function showComplete($slug)
    {

       $restaurant = RestaurantOrder::where('order_id', $slug)->first();

        $order_id = $slug;
        $sub_amount = $restaurant->sub_amount;
         $service_charge = 150;
        $delivery_fee = 1500;
        $total_amount = $service_charge + $delivery_fee + $sub_amount;
        $restaurant_phone = $restaurant->restaurant_name->phone;

        $restaurant_id = $restaurant->restaurant_id;

        // Return the view with the botOrder and user data
        return view('complete-restaurant', compact('total_amount', 'delivery_fee','service_charge','sub_amount','order_id', 'restaurant', 'restaurant_phone','restaurant_id'));
    }

public function addUserToOrder(Request $request, $slug)
{


    try{
        $validatedData = $request->validate([
             'fname' => 'required|string',
            //  'lname' => 'required|string',
            'email' => 'required|string|email',
            'phone' => 'required|string',
            'order_id' => 'required',
            'phone' => 'required',
            'address'=> 'required'
        ]);



        $restaurantOrder = RestaurantOrder::where('order_id', $slug)->first();




        if ($restaurantOrder) {
            // dd($restaurantOrder->payment_status);
            if($restaurantOrder->payment_status != 'pending'){
                return redirect()->back()->with([
                    "error"=> "Order has already been paid for"
                    ]);
            }
            $restaurantOrder->update([
                'delivery_fee'=> 1500
            ]);
            $names = explode(' ', $request->fname, 2); // Split into max 2 parts

            $first_name = $names[0] ?? ''; // First part
            $last_name = $names[1] ?? '';
            // Update the existing record
            $restaurantOrder->update([
                "fname" => $first_name,
                "lname" => $last_name,
                "email" => $request->email,
                "phone" => $request->phone,
                "address" => $request->address,
            ]);

        } else {

            // Handle case where the record does not exist, if needed
            // return response()->json(['message' => 'Order not found'], 404);
            return redirect()->back()->with([
                "error"=> "Order not found"
                ]);
        }


        $paymentData = [
            "amount" => $request->total_amount,
            "reference" => $restaurantOrder->order_id,
            "email" => $restaurantOrder->email,
            "orderID" => $restaurantOrder->order_id,
            "slug"=> $slug,
            "from"=>"bot",
            "order_type"=>'restaurant'
        ];

        // Payment handling (e.g. Paystack)


    $paystackPayment = PaymentService::redirectToGateway($paymentData);

        Log::info($paystackPayment);
        if ($paystackPayment["status"] === true) {
            return  ($paystackPayment["data"])->redirectNow();

        } else {
            return redirect()->back()->with([
            "error"=> "Order complete failes"
            ]);
        }

    }catch(\Exception $e){
        Log::info($e->getMessage());
        return redirect()->back()->with([
            "error"=> "Order complete failes"
        ]);
    }
}


}
