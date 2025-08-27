<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Promo;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HotelController extends Controller
{

    public function store(Request $request){
        if(!Auth::check()){
            return redirect('/auth/login');
        }
        $users = Auth::user();
        $usersss= User::whereId($users->id)->first();
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                // 'unique_code' => 'required|string|exists:hotels,unique_code',
                'email' => 'required|string|email|max:255',
                'amount' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return back()->withErrors(['email' => 'User not eligible for discount']);
            }

            // $hotel = Hotel::where('unique_code', $request->unique_code)->first();
            // if (!$hotel) {
            //     return back()->withErrors(['unique_code' => 'Invalid unique code']);
            // }

            $company = Company::where('user_id',$users->id)->first();
            if (!$company) {
                return back()->withErrors(['error' => 'Contact admin to upgrade  account to company account']);
            }
            $discountPercent = $company->discount;
            $amount = floatval($request->amount);
            $discountAmount = $amount * ($discountPercent / 100);
            $currentAmount = $amount - $discountAmount;
            $mcode =$this->generateUniqueCode();
            $percent_amount = floatval($request->amount) * (0.5 /100);
            $mdata = [
                "code" => $mcode,
                'user_id' => $user->id,
                "unique_code"=> $company->unique_code,
                "amount" => $amount,
                "discount_percent" => $discountPercent,
                "discount_amount" => $discountAmount,
                "current_amount" => $currentAmount,
                'company_id' => $company->id,
            ];
            Promo::create($mdata);
            $usert = User::whereId($users->id)->first();
            $usert->points += $percent_amount;
            $usert->save();
            // Promo::create($mdata);
            $user->points += $percent_amount;
            $user->save();

            $data = [
                "code" => $mcode,
                "username"=> $user->fname,
                'user_id' => $user->id,
                "unique_code"=> $company->unique_code ,
                "amount" => $amount,
                "discount_percent" => $discountPercent,
                "discount_amount" => $discountAmount,
                "current_amount" => $currentAmount,
                'company_id' => $company->id,
            ];



            return view('company.receipt', compact('data', 'user'));
        }

        return view('company.verify', ['users'=>$usersss]);
    }


    private function generateUniqueCode()
    {
        $letters = Str::upper(Str::random(5)); // Generate 5 random uppercase letters
        $numbers = random_int(10, 99); // Generate a 2-digit random number

        return $letters . $numbers;
    }

}
