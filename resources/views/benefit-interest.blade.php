@extends('layouts.custom-guest')
@section('slot')
    <!-- DELETE THIS IF YOU WANT TO REMOVE PAGELOADER ANIMATION -->
    <div class="container login-container">
        <div class="mx-auto card" style="padding: 32px;">
            <div class="card-body" style="padding: 0px;">
              <div class="row">
                <div class="align-item-left">
                  <img src="{{asset('mlatest/image/FSSLOGO1-2.png')}}" alt="Logo" style="width:146px; height:36px; margin-bottom:16px; padding:0px">
                </div>
              </div>
                <h3 class="card-title login-header">Submit Your Employee Benefit  Interest Information</h3>
                <p style="font-size:14px">Please fill out the details below.</p>
                <form method="post"  onsubmit="showLoader(this)" id="benefit-form">
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

                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="form-group" style="margin-top: 40px;">
                        <label style="margin-bottom:8px;" for="company_name" class="body-texts" style="margin-bottom:8px;">Company Name:</label>
                        <input style="height:50px;" type="text" name="company_name" value="{{ old('company_name') }}" class="form-control" id="company_name" placeholder="Enter Company Name" required>
                        @error('company_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-top: 40px;">
                        <label style="margin-bottom:8px;" for="company_email" class="body-texts">Company Email:</label>
                        <input style="height:50px;" type="email" name="company_email" value="{{ old('company_email') }}" class="form-control" id="company_email" placeholder="Email Address">
                        @error('company_email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-top: 40px;">
                        <label style="margin-bottom:8px;" for="company_phone" class="body-texts">Company Phone:</label>
                        <input style="height:50px;" type="tel" name="company_phone" value="{{ old('company_phone') }}" class="form-control" id="company_phone" placeholder="Phone Number">
                        @error('company_phone')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-top: 40px;">
                        <label style="margin-bottom:8px;" for="number_of_staff" class="body-texts">Number of Staff:</label>
                        <input style="height:50px;" type="number" name="number_of_staff" value="{{ old('number_of_staff') }}" class="form-control" id="number_of_staff" placeholder="Staff Count">
                        @error('number_of_staff')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-top: 40px;">
                        <label style="margin-bottom:8px;" for="address" class="body-texts">Company Address:</label>
                        <input style="height:50px;" type="text" name="address" value="{{ old('address') }}" class="form-control" id="address" placeholder="Enter Address">
                        @error('address')
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
