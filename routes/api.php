<?php

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use \Lomkit\Rest\Facades\Rest;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OtpController;
use Orion\Facades\Orion;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrdalyController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\ShoppingListProductController;
use App\Http\Controllers\OrderMenu as ControllersOrderMenu;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\FoodDeliveryController;
use App\Http\Controllers\Api\VirtualAccountsController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\BusinessAuthController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BusinessOrderController;
use App\Http\Controllers\BusinessPaymentController;
use App\Http\Controllers\BusinessProductController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerMemberController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Main\WebhookController;
use App\Http\Controllers\PartnersController;
use App\Http\Controllers\QuotaController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\VirtualAccountController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//authentication
Route::post('/send-otp', [OtpController::class, 'sendOtp']);
Route::post('/verify-otp', [OtpController::class, 'verifyOtp']);

Route::post('/ordaly/send-otp', [OrdalyController::class, 'sendOtp'])->name('sendOrdalyOtp');
Route::post('/fss/send-sms', [OrdalyController::class, 'sendSms'])->name('sendOrdalyOtp');
Route::post('/estimates', [DeliveryController::class, 'getEstimates']);
Route::post('/create', [DeliveryController::class, 'createDelivery']);
Route::post('/track', [DeliveryController::class, 'trackDelivery']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/locations', [LocationController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('virtual/account')->group(function () {
        Route::post('/create', [VirtualAccountController::class, 'createVirtualAccount']);
        Route::get('/list', [VirtualAccountController::class, 'listVirtualAccounts']);
        Route::get('/{accountNumber}', [VirtualAccountController::class, 'getVirtualAccountDetails']);
        Route::get('/{accountNumber}/transactions', [VirtualAccountController::class, 'getTransactionHistory']);
        Route::put('/{accountNumber}/disable', [VirtualAccountController::class, 'disableVirtualAccount']);
    });
    Route::delete('/favorites', [FavoriteController::class, 'removeFromFavorites']);
    Route::patch('/user/update-image', [UserController::class, 'updateImage']);
    Route::post('/favorites', [FavoriteController::class, 'addToFavorites']);
    Route::get('/favorites', [FavoriteController::class, 'getFavorites']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
    Route::get('/user', [UserController::class, 'show']);
    Route::post('/user/activate-2fa', [UserController::class, 'activateTwoStepVerification']);
    Route::post('/user/update-details', [UserController::class, 'updateUserDetails']);

    Route::post('/orders', [OrderController::class, 'create']);
    Route::get('/orders', [OrderController::class, 'getUserOrders']);
    Route::delete('/user/delete', [UserController::class, 'deleteAccount']);

    Route::post('/user/update-phone', [UserController::class, 'updatePhoneNumber'])->middleware('auth:sanctum');
    Route::get('/categories/{categoryId}/products', [ProductController::class, 'getProductsByCategory']);
    Route::get('/products/{productId}', [ProductController::class, 'getProductDetails']);
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::get('/addresses/{address}', [AddressController::class, 'show']);
    Route::put('/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);
    Route::get('/address/default', [AddressController::class, 'getDefaultAddress']);
    Route::post('/{type}/{id}/reviews', [ReviewController::class, 'store']);
    Route::delete('/reviews/{reviewId}', [ReviewController::class, 'destroy']);
    Route::put('/cancel-subscription/{subscriptionId}', [SubscriptionController::class, 'cancelSubscription']);
    // Shopping List routes
    Route::get('/shopping-lists', [ShoppingListController::class, 'index']);
    Route::post('/shopping-lists', [ShoppingListController::class, 'store']);
    Route::get('/shopping-lists/{id}', [ShoppingListController::class, 'show']);
    Route::put('/shopping-lists/{id}', [ShoppingListController::class, 'update']);
    Route::delete('/shopping-lists/{id}', [ShoppingListController::class, 'destroy']);

    Route::post('/shopping-lists/products', [ShoppingListProductController::class, 'addProductToList']); // Add product to shopping list
    Route::put('/shopping-lists/{shoppingListId}/products/{productId}', [ShoppingListProductController::class, 'updateProductQuantity']); // Update product quantity in shopping list
    Route::delete('/shopping-lists/{shoppingListId}/products/{productId}', [ShoppingListProductController::class, 'removeProductFromList']); // Remove product from shopping list
    Route::get('/shopping-lists/{shoppingListId}/products', [ShoppingListProductController::class, 'getProductsByShoppingListId']);


    Route::prefix('cards')->group(function () {
        Route::post('/', [CardController::class, 'store']); // Create card
        Route::get('/{id}', [CardController::class, 'show']); // Retrieve card
        Route::put('/{id}', [CardController::class, 'update']); // Update card
        Route::delete('/{id}', [CardController::class, 'destroy']); // Delete card
        Route::get('/user/{userId}', [CardController::class, 'getUserCards']); // Get cards for a user
    });
});



Route::post('/forgot-password', [OtpController::class, 'forgotPassword']);
Route::post('/reset-password', [OtpController::class, 'resetPassword']);



// Rest::resource('user', \App\Rest\Controllers\UsersController::class)->withSoftDeletes();
Rest::resource('categorie', \App\Rest\Controllers\CategoryController::class)->withSoftDeletes();
// Rest::resource('location', \App\Rest\Controllers\LocationController::class)->withSoftDeletes();
Rest::resource('product', \App\Rest\Controllers\ProductController::class)->withSoftDeletes();
Rest::resource('products-quantity', \App\Rest\Controllers\ProductQuantityController::class)->withSoftDeletes();
Rest::resource('review-image', \App\Rest\Controllers\ReviewImageController::class)->withSoftDeletes();
Rest::resource('size', \App\Rest\Controllers\SizeController::class)->withSoftDeletes();
Rest::resource('review', \App\Rest\Controllers\ReviewController::class)->withSoftDeletes();
Rest::resource('card', \App\Rest\Controllers\CardController::class)->withSoftDeletes();
Rest::resource('order', \App\Rest\Controllers\OrderController::class)->withSoftDeletes();
// Laravel 8 & 9
Route::post('/pay', [\App\Http\Controllers\PaymentController::class, 'redirectToGateway'])->name('pay');

Route::post('/cart/bot/create', [OrderController::class, 'orderFromApi']);

Route::post('/ordaly/cart/bot/create', [OrderController::class, 'orderRestaurantFromApi']);

// \Lomkit\Rest\Facades\Rest::resource('users', \App\Rest\Controllers\UsersController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/apply-card', [EmployeeController::class, 'applyForCard']);
    Route::get('/application-status', [EmployeeController::class, 'getApplicationStatus']);
    Route::post('/set-pin', [EmployeeController::class, 'setPinCode']);
    Route::get('/balance-info', [EmployeeController::class, 'getBalanceInfo']);
});

Route::get('/autogon/products/{id}', [ProductController::class, 'autogonIndexId']);
Route::get('/autogon/products', [ProductController::class, 'autogonIndex']);

Route::post('create-caterer',[ControllersOrderMenu::class, 'storeApi'])->name('caterer-store-api');

Route::post('/delivery/estimate', [DeliveryController::class, 'estimateDelivery']);




Route::post('/customer/register', [CustomerAuthController::class, 'register']);
Route::post('/customer/login', [CustomerAuthController::class, 'login']);

// Business specific
Route::post('/business/register', [BusinessAuthController::class, 'register']);
Route::post('/business/login', [BusinessAuthController::class, 'login']);



Route::middleware('auth:sanctum')->group(function () {
    // Customer membership application
    Route::post('/customer/membership/apply', [CustomerMemberController::class, 'apply']);

    // Admin approval
    Route::middleware('admin')->group(function () {
        Route::get('/admin/customer/membership/pending', [CustomerMemberController::class, 'pendingApplications']);
        Route::post('/admin/customer/membership/approve/{id}', [CustomerMemberController::class, 'approve']);
        Route::post('/admin/customer/membership/reject/{id}', [CustomerMemberController::class, 'reject']);
    });

    // Customer quota management
    Route::get('/customer/quota', [CustomerMemberController::class, 'getQuota']);
});


Route::middleware('auth:sanctum')->group(function () {
    // Business registration
    // Route::post('/business/register', [BusinessController::class, 'register']);

    // CAC document upload
    Route::post('/business/upload-cac', [BusinessController::class, 'uploadCac']);

    // Admin approval
    Route::middleware('admin')->group(function () {
        Route::get('/admin/business/pending', [BusinessController::class, 'pendingApplications']);
        Route::post('/admin/business/approve/{id}', [BusinessController::class, 'approve']);
        Route::post('/admin/business/reject/{id}', [BusinessController::class, 'reject']);
    });
});



Route::middleware(['auth:sanctum'])->group(function () {
    // Product CRUD
    Route::post('/business/products', [BusinessProductController::class, 'store']);
    Route::put('/business/products/{id}', [BusinessProductController::class, 'update']);
    Route::delete('/business/products/{id}', [BusinessProductController::class, 'destroy']);
});

// Public product listings
Route::middleware(\App\Http\Middleware\VerifyApiKey::class)->group(function () {
Route::get('/business/products', [BusinessProductController::class, 'index']);
Route::post('/business/pay', [BusinessPaymentController::class, 'processPayment']);
});
Route::get('/business/products/{id}', [BusinessProductController::class, 'show']);


Route::middleware(['auth:sanctum'])->group(function () {
    // Create order with validation
    Route::post('/business/orders', [PartnersController::class, 'store']);

    // Order status updates
    Route::post('/orders/{id}/status', [PartnersController::class, 'updateStatus']);

    // Order tracking
    Route::get('/orders/{id}', [PartnersController::class, 'show']);
    Route::get('/customer/orders', [PartnersController::class, 'customerOrders'])->middleware('customer');
    Route::get('/business/orders', [PartnersController::class, 'businessOrders'])->middleware('business');
});





Route::middleware(['auth:sanctum', 'api.key'])->group(function () {
    // Payment processing


    // Quota management
    Route::get('/quota', [QuotaController::class, 'index']);
    Route::get('/quota/history', [QuotaController::class, 'history']);

    // Debt repayment
    Route::get('/debt', [DebtController::class, 'index']);
    Route::post('/debt/pay', [DebtController::class, 'createPaymentLink']);
    Route::post('/debt/webhook', [DebtController::class, 'webhook'])->withoutMiddleware(['auth:sanctum', 'api.key']);
});


Route::middleware(['auth:sanctum', 'api.key'])->group(function () {
    Route::post('/invoices/generate', [InvoiceController::class, 'generate']);
    Route::post('/invoices/send/{id}', [InvoiceController::class, 'send']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
});


Route::middleware(['auth:sanctum'])->group(function () {
    // Customer dashboard
    Route::get('/customer/dashboard', [DashboardController::class, 'customerDashboard'])->middleware('customer');

    // Business dashboard
    Route::get('/business/dashboard', [DashboardController::class, 'businessDashboard'])->middleware('business');

    // Admin dashboard
    // Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])->middleware('admin');

    // Statistics
    Route::get('/statistics/orders', [StatisticsController::class, 'orderStatistics']);
    Route::get('/statistics/payments', [StatisticsController::class, 'paymentStatistics']);
    Route::get('/statistics/customers', [StatisticsController::class, 'customerStatistics']);
    Route::get('/statistics/products', [StatisticsController::class, 'productStatistics']);
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/api-keys', [ApiKeyController::class, 'index']);
    Route::post('/api-keys', [ApiKeyController::class, 'store']);
    Route::delete('/api-keys/{id}', [ApiKeyController::class, 'destroy']);
});

Route::group(['namespace'=>'App\Http\Api'], function(){

});
Route::get('virtual-accounts', [VirtualAccountsController::class, 'index']);
Route::post('virtual-accounts', [VirtualAccountsController::class, 'store']);
Route::get('virtual-accounts/{id}', [VirtualAccountsController::class, 'show']);
Route::get('virtual-accounts/{id}/transactions', [VirtualAccountsController::class, 'transactions']);



Route::prefix('webhooks')->group(function () {
    // Public registration with IP flexibility option
    Route::post('/register', [WebhookController::class, 'register'])
        ->middleware('throttle:10,1440');

    // IP management (authenticated)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/{webhook}/add-ips', [WebhookController::class, 'addAllowedIps']);
        Route::post('/{webhook}/remove-ips', [WebhookController::class, 'removeAllowedIps']);

        // Other management routes
        Route::get('/', [WebhookController::class, 'index']);
        Route::delete('/{webhook}', [WebhookController::class, 'delete']);
    });

    // Webhook receiving endpoint (protected by IP whitelist middleware)
    Route::post('/receive', [WebhookController::class, 'receive'])
        ->middleware('whitelist');
});

// Paystack webhook - dedicated route with no middleware conflicts
Route::post('/paystack/webhook', [App\Http\Controllers\WebhookController::class, 'handlePaystackWebhook'])
    ->withoutMiddleware(['web', 'api', 'throttle:api'])
    ->name('paystack.webhook');

// Test route for webhook verification
Route::get('/paystack/webhook/test', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'Paystack webhook endpoint is accessible',
        'timestamp' => now()->toISOString()
    ]);
})->withoutMiddleware(['web', 'api', 'throttle:api']);

// Test webhook controller
Route::get('/webhook/test', [App\Http\Controllers\WebhookController::class, 'testWebhook'])
    ->withoutMiddleware(['web', 'api', 'throttle:api']);

Route::post('/checkout/initiate', [CheckoutController::class, 'initiate']);

Route::post('/payment-status/update', [App\Http\Controllers\Api\PaymentStatusController::class, 'updatePaymentStatus']);


Route::prefix('food-delivery')->group(function () {

    // API 1: Get Estimated Fare
    Route::post('/estimate-fare', [FoodDeliveryController::class, 'getEstimatedFare'])
         ->name('food-delivery.estimate-fare');

    // API 2: Create Delivery Order
    Route::post('/create-delivery', [FoodDeliveryController::class, 'createDelivery'])
         ->name('food-delivery.create-delivery');

    // API 3: Track Delivery Status
    Route::post('/track-delivery', [FoodDeliveryController::class, 'trackDelivery'])
         ->name('food-delivery.track-delivery');

    // Additional utility routes (optional)
    Route::get('/track-delivery/{delivery_id}', [FoodDeliveryController::class, 'trackDelivery'])
         ->name('food-delivery.track-delivery.get');

    Route::get('/track-by-reference/{order_reference}', function($order_reference) {
        return app(FoodDeliveryController::class)->trackDelivery(
            request()->merge(['order_reference' => $order_reference])
        );
    })->name('food-delivery.track-by-reference');
});

// Health check route
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'Food Delivery API is running',
        'timestamp' => now()->toISOString()
    ]);
});
