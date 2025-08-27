@extends('layouts.custom-guest')
@section('slot')
    <div class="container login-container">
        <div class="mx-auto card" style="padding: 32px;">
          <div class="card-body" style="padding: 0px;">
            <div class="row">
              <div class="align-item-left">
                <img src="{{asset('mlatest/image/FSSLOGO1-2.png')}}" alt="Logo" style="width:146px; height:36px; margin-bottom:16px; padding:0px">
              </div>
            </div>
            <h3 class="card-title login-header" style="margin-bottom:8px;">Track Your Order Here</h3>
            {{-- <p style="font-size:14px">Menu</p> --}}

            <form method="POST" action="{{ route('track.store') }}"  onsubmit="showLoader(this)">
                @csrf
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                    <div class="form-group" style="margin-top: 40px;">
                        <label for="" class="body-texts" style="margin-bottom:8px;">Enter Order Tracking Reference Number</label>
                        <input type="slug" name="slug" class="form-control" id="slug"  style="height:50px;">
                        @error('slug')
                            <span class="text-error">
                                {{$message}}
                            </span>
                        @enderror
                    </div>

                        <button id="loadingButton" type="submit" class="text-white btn btn-block"style="height:50px; margin-top:16px;"

                                >
                                <span class="button-text">Continue</span>
                                <div class="spinner-border text-light" role="status" style="display: none;">
                                    <span class="sr-only">Loading...</span>
                                </div>
                        </button>
                 </form>

                 <hr />
                 @if(session('order'))
                 <div class="mb-4 w-100">
                     <div class="text-white card-header">
                         <h5>Order Details</h5>
                         <p>{{ session('slug') }}</p>
                     </div>
                     <div class="card-body">
                         <p><strong>Order Date:</strong> {{ session('order')->created_at->format('F d, Y') }}</p>
                         <p><strong>Current Status:</strong> {{ session('order')->status }}</p>
                         <h6 class="mt-3">Items Ordered:</h6>
                         <ul class="mb-3 list-group">
                             @foreach(session('order')->cartItems as $cart)
                                 <li class="list-group-item">{{ $cart->product->name }} - NGN {{ number_format($cart->price, 2) }}</li>
                             @endforeach
                         </ul>
                         <h6>Total Amount:</h6>
                         <p>NGN {{ number_format(session('order')->total_amount, 2) }}</p>
                         <h6>Estimated Delivery Time:</h6>
                         <p>{{ session('order')->estimated_delivery_time }}</p>
                         <h6>Delivery Address:</h6>
                         <p>{{ session('order')->delivery_address }}</p>
                         <h6>Contact Number:</h6>
                         <p>+2349068280610</p>
                     </div>
                 </div>
             @endif

          </div>
        </div>
      </div>

@endsection
