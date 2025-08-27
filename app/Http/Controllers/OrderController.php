<?php

namespace App\Http\Controllers;

use App\Models\BotOrders;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Employee;
use App\Models\EmployeePayment;
use App\Models\MonthEmployee;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\StoreEmployee;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Services\InvoiceService;
use App\Services\ReceiptService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Hash;

class OrderController extends Controller
{

    protected $invoiceService;
    protected $receiptService;

    public function __construct(InvoiceService $invoiceService, ReceiptService $receiptService)
    {
        $this->invoiceService = $invoiceService;
        $this->receiptService = $receiptService;
    }
    /**
 * @OA\Post(
 *     path="/orders",
 *     summary="Create a new order",
 *     tags={"Orders"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="delivery_address", type="string", example="123 Main St, Springfield", description="Delivery address for the order."),
 *             @OA\Property(property="address_id", type="integer", example=1, description="ID of the address."),
 *             @OA\Property(property="products", type="array", @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="product_id", type="integer", example=1, description="ID of the product."),
 *                 @OA\Property(property="quantity", type="integer", example=2, description="Quantity of the product."),
 *                 @OA\Property(property="price", type="number", format="float", example=10.50, description="Price of the product.")
 *             )),
 *             @OA\Property(property="payment_method", type="string", example="credit_card", description="Payment method used for the order."),
 *             @OA\Property(property="payment_status", type="string", enum={"paid", "pending"}, example="paid", description="Payment status of the order."),
 *             @OA\Property(property="tax", type="number", format="float", example=1.50, description="Tax amount for the order.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Order created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Order created successfully"),
 *             @OA\Property(property="order", type="object", description="Details of the created order")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Bad request")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Order creation failed. Please try again later.")
 *         )
 *     )
 * )
 */

    public function create(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Validate the incoming request
            $validatedData = $request->validate([
                'delivery_address' => 'required|string',
                'address_id'=>'required',
                "delivery_amount"=> "required",
                'products' => 'required|array',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.price' => 'required|numeric|min:0.01',
                'payment_method' => 'nullable|string',
                'payment_status' => 'required|string|in:paid,pending',
                'tax' => 'required|numeric|min:0',
            ]);

            // Calculate subtotal and total amount
            $subtotal = collect($validatedData['products'])->sum(function ($product) {
                return $product['quantity'] * $product['price'];
            });
            $totalAmount = $subtotal + $validatedData['tax'] + $validatedData["delivery_amount"];
            $user = User::whereId(Auth::id())->first();
            // Create the order
            $order = Order::create([
                // 'unique_code' => strtoupper(Str::random(6)),
                'user_id' => Auth::user()->id,
                'delivery_address' => $validatedData['delivery_address'],
                'payment_method' => $validatedData['payment_method'],
                'payment_status' => $validatedData['payment_status'],
                'tax' => $validatedData['tax'],
                'subtotal' => $subtotal,
                'delivery_amount'=>$validatedData['delivery_amount'],
                'address_id'=> $validatedData['address_id'],
                'total_amount' => $totalAmount,
            ]);
            $items = [];
            // Create associated carts
            foreach ($validatedData['products'] as $product) {
                Cart::create([
                    'order_id' => $order->id,
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'price' => $product['price'],
                    'total' => $product['quantity'] * $product['price'],
                ]);
                $prod = Product::whereId($product['product_id'])->first();
                array_push($items, [
                    'title'=> $prod->name,
                    'units'=> $prod->size->name,
                    'price'=>$product['price'],
                    // 'description'=> Str::limit($prod->about, 20, '...'),
                    'pricePerUnit'=> $product['price'],
                    'quantity'=>$product['quantity']
                ]);
            }

            // Create a transaction if payment status is paid
            if ($validatedData['payment_method'] === 'points') {
                $employee = Employee::where('user_id', Auth::id())->first();

                $employee->balance -= $totalAmount;
                $employee->save();
                StoreEmployee::create([
                    'employee_id' => $employee->id,
                    'date' => now()->toDateString(),
                    'amount' => $totalAmount,
                ]);



                $currentMonth = now()->format('m');
                $currentYear = now()->format('Y');

                // Check if a MonthEmployee record for the current month already exists
                $monthEmployee = MonthEmployee::where('employee_id', $employee->id)
                    ->whereYear('created_at', $currentYear)
                    ->whereMonth('created_at', $currentMonth)
                    ->first();

                    if ($monthEmployee) {
                        // If it exists, update the existing record
                        $monthEmployee->total_bought += $totalAmount; // Accumulate total bought
                        $monthEmployee->credit_balance = $employee->balance; // Update credit balance
                        $monthEmployee->total_owed += $totalAmount;
                        $monthEmployee->save();
                    } else {
                MonthEmployee::create([
                    'employee_id' => $employee->id,
                    'total_bought' => $totalAmount,
                    'total_owed' => $totalAmount,
                    'credit_balance' => $employee->balance,
                    'monthly_due' => $employee->debit_limit ?? 0,
                ]);
            }

                EmployeePayment::create([
                    'employee_id' => $employee->id,
                    'payment_date' => now()->toDateString(),
                    'amount' => $totalAmount,
                    'updated_by' => Auth::id(), // Assuming the user creating the order is the one updating
                ]);


                Transaction::create([
                    'transactionable_id' => $order->id,
                    'transactionable_type' => Order::class,
                    'unique_code' => $order->unique_code,
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'payment_method' => $validatedData['payment_method'],
                    'amount' => $totalAmount,
                    'status' => 'completed',
                    'description' => 'Payment for Order ' . $order->unique_code,
                ]);
            }
            if($validatedData["payment_method"]=="paystack"){
                $mdata =[
                    "amount"=>$totalAmount,
                    "reference"=> $order->unique_code,
                    "email"=>Auth::user()->email,
                    "orderID"=> $order->id,
                     "from"=>"mobile"
                ];
                $paystackPayment = PaymentService::redirectToGateway($mdata);
                if($paystackPayment["status"]== true){
                    DB::commit();
                    $email = Auth::user()->email;

                    $customerDetails = [
                        'name'          => Auth::user()->name,
                        'address'       => Auth::user()->residential,
                        'code'          => $order->unique_code,
                        'custom_fields' => [
                            'order number' => $order->unique_code,
                        ],
                    ];
            $this->invoiceService->createInvoice($customerDetails, $items,  $user, $validatedData["delivery_amount"]);
            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load('cartItems'),
                "payment_url"=>$paystackPayment["data"]
            ], 201);
                }else{
                    DB::rollBack();
                    Log::error('Payment creation failed: ' );

                    return response()->json([
                        'error' => "Payment url failed ",
                        'data'=>$paystackPayment
                    ], 500);
                }
            }
            DB::commit();
            $email = Auth::user()->email;
            $customerDetails = [
                'name'          => Auth::user()->name,
                'address'       => Auth::user()->residential,
                'code'          => $order->unique_code,
                'custom_fields' => [
                    'order number' => $order->unique_code,
                ],
            ];
            $this->invoiceService->createInvoice($customerDetails, $items,  $user, $validatedData["delivery_amount"]);
            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load('cartItems'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());

            return response()->json([
                'error' => 'Order creation failed. Please try again later.'. $e->getMessage()
            ], 500);
        }
    }


/**
 * @OA\Get(
 *     path="/orders",
 *     summary="Get orders for the authenticated user",
 *     tags={"Orders"},
 *     @OA\Response(
 *         response=200,
 *         description="Successfully retrieved orders",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="orders", type="array", @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="user_id", type="integer", example=1),
 *                 @OA\Property(property="delivery_address", type="string", example="123 Main St, Springfield"),
 *                 @OA\Property(property="payment_method", type="string", example="credit_card"),
 *                 @OA\Property(property="payment_status", type="string", example="paid"),
 *                 @OA\Property(property="tax", type="number", format="float", example=1.50),
 *                 @OA\Property(property="subtotal", type="number", format="float", example=20.00),
 *                 @OA\Property(property="total_amount", type="number", format="float", example=21.50),
 *                 @OA\Property(property="carts", type="array", @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="product_id", type="integer", example=1),
 *                     @OA\Property(property="quantity", type="integer", example=2),
 *                     @OA\Property(property="price", type="number", format="float", example=10.50),
 *                     @OA\Property(property="total", type="number", format="float", example=21.00)
 *                 ))
 *             ))
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to retrieve orders",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Failed to retrieve orders"),
 *             @OA\Property(property="error", type="string", example="Internal server error.")
 *         )
 *     )
 * )
 */
    public function getUserOrders(Request $request)
    {
        try {
            // Get authenticated user's orders with cart details and associated product details
            $userId = Auth::id();
            $orders = Order::where('user_id', $userId)
                ->with(['cartItems.product'])->latest()
                ->get();

            return response()->json([
                'success' => true,
                'orders' => $orders
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }


/**
     * @OA\Post(
     *     path="/api/cart/bot/create",
     *     tags={"Order"},
     *     summary="Create an order from external cart data",
     *     description="This API allows external bodies to send cart data to create an order. It handles both existing and new users.",
     *     operationId="createOrder",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="John Doe", description="Name of the user"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com", description="Email of the user"),
     *             @OA\Property(property="phone", type="string", example="+1234567890", description="Phone number of the user"),
     *             @OA\Property(property="delivery_address", type="string", example="123 Main St", description="Delivery address for the order"),
     *             @OA\Property(property="address_id", type="integer", example=1, description="Address ID from the user's saved addresses"),
     *             @OA\Property(property="delivery_amount", type="number", example=10.50, description="Cost of delivery"),
     *             @OA\Property(property="products", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="product_id", type="integer", example=1, description="Product ID"),
     *                     @OA\Property(property="quantity", type="integer", example=2, description="Quantity of the product"),
     *                     @OA\Property(property="price", type="number", example=15.99, description="Price of the product")
     *                 )
     *             ),
     *             @OA\Property(property="payment_method", type="string", example="paystack", description="Payment method used"),
     *             @OA\Property(property="payment_status", type="string", example="pending", enum={"paid", "pending"}, description="Payment status of the order"),
     *             @OA\Property(property="tax", type="number", example=2.00, description="Applicable tax amount")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Order created successfully"),
     *             @OA\Property(property="order", type="object", description="Details of the created order"),
     *             @OA\Property(property="payment_url", type="string", example="https://paystack.com/xyz", description="URL for payment redirection")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Order creation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Order creation failed. Please try again later.")
     *         )
     *     )
     * )
     */
    // public function orderFromApi(Request $request): JsonResponse
    // {
    //     DB::beginTransaction();

    //     try {
    //         // Validate the incoming request
    //         $validatedData = $request->validate([
    //             'delivery_address' => 'required|string',
    //             'products' => 'required|array',
    //             'products.*.product_id' => 'required|exists:products,id',
    //             'products.*.quantity' => 'required|integer|min:1',
    //             'products.*.price' => 'required|numeric|min:0.01',
    //             'tax' => 'required|numeric|min:0',
    //             'phone'=>'required'
    //         ]);


    //         // Calculate subtotal and total amount
    //         $subtotal = collect($validatedData['products'])->sum(function ($product) {
    //             return $product['quantity'] * $product['price'];
    //         });
    //         $totalAmount = $subtotal + $validatedData['tax'] + 2000;

    //         // Create the order
    //         $order = Order::create([
    //             // 'user_id' => $user->id,
    //             'delivery_address' => $validatedData['delivery_address'],
    //             'payment_method' => "paystack",
    //             'payment_status' => "pending",
    //             'tax' => $validatedData['tax'],
    //             'subtotal' => $subtotal,
    //             'delivery_amount' => 2000,
    //             // 'address_id' => $validatedData['address_id'],
    //             'total_amount' => $totalAmount,
    //         ]);

    //         $cartItems = [];
    //         foreach ($validatedData['products'] as $product) {
    //             $cartItems[] = new Cart([
    //                 'product_id' => $product['product_id'],
    //                 'quantity' => $product['quantity'],
    //                 'price' => $product['price'],
    //                 'total' => $product['quantity'] * $product['price'],
    //             ]);
    //         }
    //         $order->cartItems()->saveMany($cartItems);

    //         $slug = time()."_".uniqid();

    //         BotOrders::create([
    //             'phone'=> $request->phone,
    //             'slug'=> $slug,
    //             'status'=> true,
    //             'total_amount'=> $totalAmount,
    //             'order_id'=> $order->id
    //         ]);
    //         DB::commit();

    //         // Return success response
    //         return response()->json([
    //             'message' => 'Order created successfully',
    //             'order' => $order->load('cartItems'),
    //             "complete_url"=> URL("/complete-order/".$slug)
    //         ], 201);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Order creation failed: ' . $e->getMessage());

    //         return response()->json(['error' => 'Order creation failed. Please try again later.'], 500);
    //     }
    // }

    public function orderRestaurantFromApi(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validate([
                // 'delivery_address' => 'required|string',
                'slug'=>'required',
                'products' => 'required|array',
                'delivery_method' => 'required',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.price' => 'required|numeric|min:0.01',
                // 'tax' => 'required|numeric|min:0',
                'phone' => 'required',
                'restaurant_id'=>"required",
                // "restaurant_phone"=>"required"
            ]);


            $slug = $validatedData['slug'];
            $service_charge = 1500;

            $order = RestaurantOrder::where('order_id', $slug)->first();


            if ($order) {
                // Check if order is already paid
                if ($order->payment_status != 'pending') {
                    return response()->json([
                        'error' => 'This order has already been paid for and cannot be modified.'
                    ], 400);
                }
            }
            if (!$order) {
                $restaurant = Restaurant::firstOrCreate(
                    ['restaurant_id' => $validatedData['restaurant_id']],
                    ['phone' => $validatedData['restaurant_id']]
                );

                 // Create new order
            $order = RestaurantOrder::create([
                'delivery_address' => "pending address",
                'payment_method' => "paystack",
                'payment_status' => "pending",
                'phone' => $validatedData['phone'],
                'restaurant_id' => $restaurant->id,
                'order_id' => $slug,
                'delivery_method' => $validatedData['delivery_method'],
                'sub_amount' => 0,
                'service_charge' => $service_charge,
                'delivery_fee' => 0,
                'total_amount' => 0,
            ]);
            }

             // Update or insert products in the cart
        foreach ($validatedData['products'] as $product) {
            $cartItem = RestaurantOrderItem::where('restaurant_order_id', $order->id)
                ->where('product_id', $product['product_id'])
                ->first();

            if ($cartItem) {
                // Update quantity and price if the product is already in cart
                $cartItem->quantity += $product['quantity'];
                $cartItem->price = $product['price'];
                $cartItem->total = $cartItem->quantity * $cartItem->price;
                $cartItem->updated_at = now();
                $cartItem->save();
            } else {
                // Add new product to the cart
                RestaurantOrderItem::create([
                    'restaurant_order_id' => $order->id,
                    'order_id' => $slug,
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'price' => $product['price'],
                    'total' => $product['quantity'] * $product['price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $subtotal = RestaurantOrderItem::where('restaurant_order_id', $order->id)->sum('total');
        $totalAmount = $subtotal + $service_charge;

        // Update order totals
        $order->update([
            'sub_amount' => $subtotal,
            'total_amount' => $totalAmount,
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Order created/updated successfully',
            'order' => $order->load('cartItems'),
            'service_charge' => $service_charge,
            'sub_amount' => $subtotal,
            'delivery_fee' => 0,
            'delivery_method' => $validatedData['delivery_method'],
            'total_amount' => $totalAmount,
            'complete_url' => URL("/restaurant/complete-order/" . $slug),
        ], 201);






        //     $subtotal = collect($validatedData['products'])->sum(function ($product) {
        //         return $product['quantity'] * $product['price'];
        //     });
        //     $service_charge = 1500;
        //     $totalAmount = $subtotal + $service_charge ;
        //     $slug = $request->slug;

        //     $restuarant = Restaurant::create([
        //         "phone"=> $validatedData['restaurant_id'],
        //         "restaurant_id"=> $validatedData['restaurant_id']
        //     ]);

        //     $order = RestaurantOrder::where('order_id', $slug)->first();
        //     if(is_null($order) || empty($order) ){


        //     $order = RestaurantOrder::create([
        //         'delivery_address' => "pending address",
        //         'payment_method' => "paystack",
        //         'payment_status' => "pending",
        //         "phone" =>$validatedData['phone'],
        //         'restaurant_id'=> $restuarant->id,
        //         'order_id' => $slug,
        //         'delivery_method'=>$request->delivery_method,
        //         "sub_amount"=>$subtotal,
        //         "service_charge"=>$service_charge,
        //         "delivery_fee"=>0,
        //         "total_amount"=>$totalAmount,
        //     ]);

        // }
        //     $cartItems = collect($validatedData['products'])->map(function ($product) use ($order,$slug) {
        //         return [
        //             'restaurant_order_id' => $order->id,
        //             'order_id'=>$slug,
        //             'product_id' => $product['product_id'],
        //             'quantity' => $product['quantity'],
        //             'price' => $product['price'],
        //             'total' => $product['quantity'] * $product['price'],
        //             'created_at' => now(),
        //             'updated_at' => now(),
        //         ];
        //     })->toArray();
        //     RestaurantOrderItem::insert($cartItems);


        //     $slug = $request->slug;



        //     DB::commit();

        //     return response()->json([
        //         'message' => 'Order created successfully',
        //         'order' => $order->load('cartItems'),
        //         'service_charge'=>$service_charge,
        //         "sub_amount"=>$subtotal,
        //         "delivery_fee"=>0,
        //         "delivery_method"=>$request->delivery_method,
        //         "total_amount"=>$totalAmount,
        //         // 'more'=>$bot,
        //         'complete_url' => URL("/restaurant/complete-order/" . $slug),
        //     ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Order creation failed. Please try again later.', "msg"=>$e->getMessage()], 500);
        }
    }


    public function orderFromApi(Request $request): JsonResponse
        {
            DB::beginTransaction();

            try {
                $validatedData = $request->validate([
                    // 'delivery_address' => 'required|string',
                    'slug'=>'required',
                    'products' => 'required|array',
                    'products.*.product_id' => 'required|exists:products,id',
                    'products.*.quantity' => 'required|integer|min:1',
                    'products.*.price' => 'required|numeric|min:0.01',
                    'tax' => 'required|numeric|min:0',
                    'phone' => 'required',
                ]);

                $subtotal = collect($validatedData['products'])->sum(function ($product) {
                    return $product['quantity'] * $product['price'];
                });
                $totalAmount = $subtotal + $validatedData['tax'] + 2000;

                $order = Order::create([
                    'delivery_address' => "pending address",
                    'payment_method' => "paystack",
                    'payment_status' => "pending",
                    'tax' => $validatedData['tax'],
                    'subtotal' => $subtotal,
                    'delivery_amount' => 2000,
                    'total_amount' => $totalAmount,
                ]);

                $cartItems = collect($validatedData['products'])->map(function ($product) use ($order) {
                    return [
                        'order_id' => $order->id,
                        'product_id' => $product['product_id'],
                        'quantity' => $product['quantity'],
                        'price' => $product['price'],
                        'total' => $product['quantity'] * $product['price'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();
                Cart::insert($cartItems);

                // $slug = time() . "_" . uniqid();
                $slug = $request->slug;

              $bot =  DB::table('bot_orders')->insert([
                    'phone' => $request->phone,
                    'slug' => $slug,
                    'status' => true,
                    'total_amount' => $totalAmount,
                    'order_id' => $order->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Order created successfully',
                    'order' => $order->load('cartItems'),
                    'more'=>$bot,
                    'complete_url' => URL("/complete-order/" . $slug),
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Order creation failed: ' . $e->getMessage());
                return response()->json(['error' => 'Order creation failed. Please try again later.'], 500);
            }
        }





        public function showComplete($slug)
        {
            // Correct syntax for multiple conditions in where clause
            $botOrder = BotOrders::where('slug', $slug)
                ->where('status', true) // Add an additional condition for status
                ->first();

            // Check if the botOrder exists before accessing its properties
            if (!$botOrder) {
                abort(404, 'Bot order not found.');
            }

            // Fetch the user based on the botOrder's phone number
            $user = User::where('phone', $botOrder->phone)->first();


            // Return the view with the botOrder and user data
            return view('complete_orders', compact('botOrder', 'user'));
        }

    public function addUserToOrder(Request $request, $slug)
    {

        try{
            $validatedData = $request->validate([
                 'fname' => 'required|string',
                //  'lname' => 'required|string',
                'email' => 'required|string|email',
                'phone' => 'required|string',
                // 'password' => 'required',
                'order_id' => 'required',
                'phone' => 'required',
                'address'=> 'required'
            ]);




            // Check if user already exists by phone or email
            $user = User::where('email', $validatedData['email'])
                        ->orWhere('phone', $validatedData['phone'])
                        ->first();

            $orderId = $request->order_id;
            $phone = $request->phone;
            $order = Order::whereId($orderId)->first();



            if (!$user) {
                $names = explode(' ', $request->fname, 2); // Split into max 2 parts

                $first_name = $names[0] ?? ''; // First part
                $last_name = $names[1] ?? '';
                $user = User::create([
                    'name' =>$request->fname,
                    'fname' => $first_name,
                    'lname' => $last_name,
                    'email' => $validatedData['email'],
                    'phone' => $validatedData['phone'],
                    'password'=> Hash::make("Password@123"),
                    'residential' => $validatedData['address'], // Assuming 'residential' as the address field
                ]);
            }

            $order->user_id = $user->id;
            $order->delivery_address = $validatedData['address'];
            $order->save();


            $paymentData = [
                "amount" => $request->total_amount,
                "reference" => $order->id,
                "email" => $user->email,
                "orderID" => $order->id,
                "slug"=> $slug,
                "from"=>"bot"
            ];

            // Payment handling (e.g. Paystack)


        $paystackPayment = PaymentService::redirectToGateway($paymentData);

            Log::info($paystackPayment);
            if ($paystackPayment["status"] === true) {

                $botOrder = BotOrders::where('slug', $slug)->first();
                $botOrder->status = false;
                $botOrder->save();

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
