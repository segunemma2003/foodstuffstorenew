<?php

use App\Exports\EmployeeReportExport;
use App\Filament\Caterer\Pages\OrderMenu as PagesOrderMenu;
use App\Http\Controllers\BenefitController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderMenu as ControllersOrderMenu;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VirtualAccountController;
use App\Http\Controllers\WebhookController;
use App\Livewire\OrderMenu;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/privacy', function () {
    return view('privacy');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::match(['get', 'post'], 'company/promo', [HotelController::class, 'store'])->middleware('auth');
Route::match(['get', 'post'],
    'webhook/callback',[WebhookController::class, 'handleWebhook']);
    Route::post('/wema/webhook/', [VirtualAccountController::class, 'handleWebhook']);


    Route::match(['get', 'post'],
    'paystack/callback',[PaymentController::class, 'handlePaymentGateway']);

Route::get('uploadcsv', [UserController::class, 'importUsersFromCsv']);


Route::get('/export-employee-report/{month}/{year}', function ($month, $year) {
    return Excel::download(new EmployeeReportExport($month, $year), 'employee_report.xlsx');
})->name('employee.report.download');


Route::get('/complete-order/{slug}', [OrderController::class, 'showComplete']);
Route::post('/complete-order/{slug}', [OrderController::class, 'addUserToOrder']);



Route::get('/restaurant/complete-order/{slug}', [RestaurantController::class, 'showComplete']);
Route::post('/restaurant/complete-order/{slug}', [RestaurantController::class, 'addUserToOrder']);

Route::get('/benefit-interest', [BenefitController::class, 'create'])->name('benefit.create');
Route::post('/benefit-interest', [BenefitController::class, 'store'])->name('benefit.store');

Route::get('thank-you', function(){
    return view('caterer.thankyou');
})->name('thankyou');

Route::get('create-caterer-order',[ControllersOrderMenu::class, 'order'])->name('caterer');
Route::get('qr',[ControllersOrderMenu::class, 'showQr'])->name('showQr');
Route::post('create-caterer-order',[ControllersOrderMenu::class, 'store'])->name('caterer-store');
Route::get('/track-order', [ControllersOrderMenu::class, 'trackOrder'])->name('track.order');
Route::post('/track-order', [ControllersOrderMenu::class, 'trackStore'])->name('track.store');
Route::get('/sample', function(){
    return view('sample.restaurant');
});



Route::prefix('blogs')->name('blogs.')->group(function () {
    // Blog listing page
    Route::get('/', [BlogController::class, 'index'])->name('index');

    // Individual blog post
    Route::get('/{slug}', [BlogController::class, 'show'])
        ->where('slug', '[a-z0-9\-]+')
        ->name('show');

    // Comment submission (no auth required)
    Route::post('/{slug}/comments', [BlogController::class, 'storeComment'])
        ->where('slug', '[a-z0-9\-]+')
        ->name('comments.store');
});
require __DIR__.'/auth.php';
