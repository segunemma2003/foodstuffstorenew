@extends('layouts.custom-guest')
@section('slot')
    <!-- DELETE THIS IF YOU WANT TO REMOVE PAGELOADER ANIMATION -->
    <div class="container login-container">
        <div class="mx-auto card" style="margin-top: 40px;">
            <div class="card-body">
                <div class="mx-auto">
                    <img src="{{ asset('latest/image/logo-head.png') }}" alt="Logo">
                </div>
                <h3 class="card-title login-header">Complete Order</h3>
                <p style="font-size:14px">Complete Order</p>
                <form method="post" id="registration-form">
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
                        <label for="fname" class="body-texts">Enter  Name:</label>
                        <input type="text" name="fname" required value="{{ $user->fname ?? '' }}" class="form-control" id="first_name" placeholder="Enter Name">
                        @error('fname')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- <div class="form-group" style="margin-top: 40px;">
                        <label for="lname" class="body-texts">Enter Last Name:</label>
                        <input type="text" name="lname" required class="form-control" id="last_name" value="{{ $user->lname ?? '' }}" placeholder="Enter Last Name">
                        @error('lname')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div> --}}

                    <div class="form-group" style="margin-top: 40px;">
                        <label for="email" class="body-texts">Enter Email Address:</label>
                        <input type="email" name="email" value="{{ $user->email ?? '' }}" required class="form-control" id="email" placeholder="Email Address">
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <input type="hidden" name="order_id" value="{{ $botOrder->order_id}}" />

                    <div class="form-group" style="margin-top: 40px;">
                        <label for="phone" class="body-texts">Enter Phone Number:</label>
                        <input type="tel" name="phone" value="{{ $botOrder->phone }}" required class="form-control" id="phone" placeholder="Phone Number" readonly>
                        @error('phone')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-top: 40px;">
                        <label for="address" class="body-texts">Enter Address:</label>
                        <input type="text" name="address" required class="form-control" id="address" placeholder="Address" value="{{ $user->residential ?? '' }}">
                        @error('address')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- <div class="form-group" style="margin-top: 40px;">
                        <label for="password" class="body-texts">Enter Password:</label>
                        <input type="password" name="password" required class="form-control" id="password" placeholder="Password">
                        @error('password')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div> --}}

                    <div class="form-group" style="margin-top: 40px;">
                        <label for="total_amount" class="body-texts">Total Amount of Order:</label>
                        <input type="number" name="total_amount" readonly class="form-control" id="total_amount" value="{{ $botOrder->total_amount ?? '' }}">
                    </div>

                    <button type="submit" class="text-white btn btn-block">Continue</button>
                </form>
            </div>
        </div>
    </div>


    @endsection