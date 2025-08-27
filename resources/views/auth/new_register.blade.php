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
        <h3 class="card-title login-header">Registration</h3>
        <p style="font-size:14px">Registration</p>
        <form method="post" action="{{route('auth_register_store')}}" id="registration-form" onsubmit="showLoader(this)">
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
          <!-- <div class="form-group" style="margin-top: 40px;">
            <label style="margin-bottom:8px;" for="unique_code" class="body-texts">Enter Company Unique Code</label>
            <input style="height:50px;" type="text" name="unique_code" required class="form-control" id="unique_code" placeholder="Enter Company Unique Code">
            @error('unique_code')
                          <div class="text-danger">{{ $message }}</div>
                      @enderror
          </div> -->
          <input style="height:50px;" type="hidden" name="user_type" id="user_type" value="individual">
          <div class="form-group" style="margin-top: 40px;">
            <label style="margin-bottom:8px;" for="first_name" class="body-texts">Enter First Name:</label>
            <input style="height:50px;" type="first_name" name="first_name" required class="form-control" id="first_name" placeholder="Enter First Name">
            @error('first_name')
                          <div class="text-danger">{{ $message }}</div>
                      @enderror
          </div>

          <div class="form-group" style="margin-top: 40px;">
            <label style="margin-bottom:8px;" for="last_name" class="body-texts">Enter Last Name:</label>
            <input style="height:50px;" type="last_name" name="last_name" required class="form-control" id="last_name" placeholder="Enter Last Name">
            @error('last_name')
                          <div class="text-danger">{{ $message }}</div>
                      @enderror
          </div>

          <div class="form-group" style="margin-top: 40px;">
            <label style="margin-bottom:8px;" for="email" class="body-texts">Enter  email address</label>
            <input style="height:50px;" type="email" name="email" required class="form-control" id="email" placeholder="email address">
            @error('email')
                          <div class="text-danger">{{ $message }}</div>
                      @enderror
          </div>

          <div class="form-group" style="margin-top: 40px;">
            <label style="margin-bottom:8px;" for="phone" class="body-texts">Enter  Phone Number</label>
            <input style="height:50px;" type="tel" name="phone" required class="form-control" id="phone" placeholder="phone no">
            @error('phone')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
        </div>

          <!-- <div class="form-group" style="margin-top: 40px;">
            <label style="margin-bottom:8px;" for="phone" class="body-texts">Enter  Phone Number</label>
            <input style="height:50px;" type="number" name="phone" required class="form-control" id="phone" placeholder="Phone number">
            @error('phone')
                          <div class="text-danger">{{ $message }}</div>
                      @enderror
          </div> -->

          <!-- <input style="height:50px;" type="hidden" name="phone" value="{{ session('phone') }}"> -->

          <div class="form-group" style="margin-top: 40px;">
            <label style="margin-bottom:8px;" for="address" class="body-texts">Enter  Address:</label>
            <input style="height:50px;" type="text" name="address" required class="form-control" id="address" placeholder="Address">
            @error('address')
                          <div class="text-danger">{{ $message }}</div>
                      @enderror
          </div>
          <div class="form-group" style="margin-top: 40px;">
            <label style="margin-bottom:8px;" for="password" class="body-texts">Enter  Password:</label>
            <input style="height:50px;" type="password" name="password" required class="form-control" id="password" placeholder="password">
            @error('password')
                          <div class="text-danger">{{ $message }}</div>
                      @enderror
          </div>
          <div class="form-group" style="margin-top: 40px;">
            <label style="margin-bottom:8px;" for="password_confirmation" class="body-texts">Confirm Password:</label>
            <input style="height:50px;" type="password" name="password_confirmation" required class="form-control" id="password_confirmation" placeholder="Confirm Password">
            @error('password_confirmation')
                          <div class="text-danger">{{ $message }}</div>
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
      </div>
    </div>
@endsection
