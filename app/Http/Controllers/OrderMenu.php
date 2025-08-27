<?php

namespace App\Http\Controllers;

use App\Models\BotOrders;
use App\Models\GuestOrder;
use App\Models\Order;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class OrderMenu extends Controller
{

    public function trackOrder(){
        return view('auth.track.order');
    }

    public function trackStore(Request $request)
{
    $botOrder = BotOrders::whereSlug($request->slug)->first();

    if ($botOrder) {
        $order = Order::whereId($botOrder->order_id)->first();
        return redirect()->back()->with([
            'order' => $order,
            'slug' => $request->slug
        ]);
    }

    // When the slug is not found in both tables
    return redirect()->back()->withErrors([
        'error' => 'Order not found for the provided reference number.'
    ]);
}
    public function showQr(){
        $url = route('caterer');

        // Use the correct path to the image in the public folder
        $qrCode = QrCode::size(400)
                        ->mergeString(public_path('images/logo-head.png'), 0.3)  // Use public_path() to get the correct image path
                        ->generate($url);

        return view('auth.qr', compact('qrCode'));
    }
    public function order(){
        $tables = [
            'Table 1' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 2' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 3' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 4' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 5' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 6' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 7' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 8' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 9' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 10' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 11' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 12' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G'],
            'Table 13' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 14' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 15' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 16' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 17' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 18' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 19' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 20' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 21' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 22' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 23' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 24' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 25' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 26' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 27' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 28' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 29' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H'],
            'Table 30' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H', 'Chair I'],
            'Table 31' => ['Chair A', 'Chair B', 'Chair C', 'Chair D', 'Chair E', 'Chair F', 'Chair G', 'Chair H', 'Chair I'],
        ];
        return view('auth.caterer', compact('tables'));
    }

    public function store(Request $request){
        $request->validate([
            'table_number'=>'required',
            'starter'=>'required',
            'main'=>'required',
            'protein'=>'required',
            'sides'=>'required',
            'swallow'=>'required',
        ]);

        $order = GuestOrder::create($request->all());
        $user = User::whereEmail('caterer@admin.com')->first();
        Notification::make()
        ->title('A new Order made')
        ->sendToDatabase($user)
        ->broadcast($user);
        return redirect()->route('thankyou');
    }


    public function storeApi(Request $request){
        $request->validate([
            'table_number'=>'required',
            'starter'=>'required',
            'main'=>'required',
            'protein'=>'required',
            'sides'=>'required',
            'swallow'=>'required',
        ]);

        $order = GuestOrder::create($request->all());
        $user = User::whereEmail('caterer@admin.com')->first();
        Notification::make()
        ->title('A new Order made')
        ->sendToDatabase($user)
        ->broadcast($user);
        return response()->json([
            "status"=>"success"
        ], 201);
    }
}
