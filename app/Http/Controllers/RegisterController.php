<?php

namespace App\Http\Controllers;

use App\Mail\CompanyRegistrationEmail;
use App\Mail\IndividualRegistrationEmail;
use App\Models\Company;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        try{

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:15|unique:users',
            'address' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            // 'phone' => ['required','unique:users', 'string', new Phone('NG')],
        ];

        // dd($request->all());

        if ($request->user_type === 'company') {
            $rules['company_name'] = 'required|string|max:255';
            $rules['company_address'] = 'required|string|max:255';
            $rules['company_state'] = 'required|string|max:255';
            $rules['discount'] = 'required';
        }

        if(User::where('email', $request->email)->exists()){
            return redirect()->back()->withErrors("Email already exists");
        }
        $request->validate($rules);
        // ,[
        //     'phone.required' => 'Phone number is required.',
        //     'phone.string' => 'Phone number must be a string.',
        //     'phone.phone' => 'The phone number is incorrect.',
        // ]);
        $username = explode('@', $request->email)[0];

        $user = User::create([
            'fname'=> $request->first_name,
            'lname'=>$request->last_name,
            'name' => $request->first_name. " ". $request->last_name,
            'email' => $request->email,
            'image'=> "https://w7.pngwing.com/pngs/613/636/png-transparent-computer-icons-user-profile-male-avatar-avatar-heroes-logo-black-thumbnail.png",
            'phone' => $request->phone,
            // 'current_location'=> $request->address,
            'residential' => $request->address,
            'wallet_balance' => 0.00,
            'points' => 0.00,
            'password' => Hash::make($request->password),
            'user_type'=>$request->user_type != "company"? "regular": "company"
        ]);

        $otp = rand(100000, 999999);

        if ($request->user_type === 'company') {
            $company = Company::create([
                'user_id' => $user->id,
                'name' => $request->company_name,
                'address' => $request->company_address,
                'state' => $request->company_state,
                'discount'=>$request->discount,
                'ceo_name'=> $request->first_name
            ]);
            $unique_code = $company->unique_code;
            Mail::to($request->email)->queue(new CompanyRegistrationEmail($unique_code, $otp));
         }else {
            // Send email to individual user
            Mail::to($request->email)->queue(new IndividualRegistrationEmail($otp));
        }

        $request->session()->put('otp', $otp);
        $request->session()->put('phone', $request->phone);
        $expiresAt = now()->addMinutes(10);
        Log::info($otp);
        $otp = Otp::getPendingOtp($request->phone);
        return redirect()->route('verify.otp.form');
    }catch(\Exception $e){
        return redirect()->back()->with(["errors"=>['failed registration']]);
    }

    }


    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|integer',
        ]);

        $sessionOtp = $request->session()->get('otp');
        $phone =$request->session()->get('phone');
        $otp = $request->input('otp');
        if ($sessionOtp == $otp) {
            $user = User::where('phone', $phone)->first();
            $user->email_verified_at = Carbon::now();
            $user->save();

            Auth::login($user);

            return redirect('/')->with(['status'=>'account verified']);

        }
        return redirect()->back()->withErrors(["code" => "Invalid code "]);

    }


    public function login(Request $request){
        $rules = [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ];

        $request->validate($rules);
        $data = [
            'email'=>$request->email,
            'password'=> $request->password
        ];
        if(Auth::attempt($data)){
            $users = Auth::user();
            $user = User::whereId($users->id)->first();
            if ($user->companies()->exists()) {
                return redirect('company/promo');
            }
        return redirect('/');



        }else{
            return redirect()->back()->withErrors(["email" => "Invalid login credentials"]);
        }
    }

}
