@extends('layouts.custom-guest')

@section('slot')
    <div class="container login-container">
        <div class="mx-auto card" style="padding: 32px;">
          <div class="card-body" style="padding: 0px;">
            <div class="row">
              <div class="align-item-left">
                <img src="mlatest/image/FSSLOGO1-2.png" alt="Logo" style="width:146px; height:36px; margin-bottom:16px; padding:0px">
              </div>
            </div>
            <h3 class="card-title login-header" style="margin-bottom:8px;">Collect user's details</h3>
            <p style="font-size:14px">Validate user and apply discount with the provided form below</p>
            <form onsubmit="showLoader(this)">
              <div class="form-group" style="margin-top: 40px;">
                <label for="email" class="body-texts" style="margin-bottom:8px;">Enter user's email address</label>
                <input type="email" class="form-control" id="email" placeholder="User's email address" style="height:50px;">
              </div>
              <div class="form-group" style="margin-top: 20px;">
                <label for="amount" class="body-texts" style="margin-bottom:8px;">Enter bill amount</label>
                <input type="number" step=".01" inputmode="numeric" class="form-control" id="amount" placeholder="Amount" style="height:50px;">
              </div>
              <button id="loadingButton" type="button" class="text-white btn btn-block"style="height:50px; margin-top:16px;"
                >
                <span class="button-text">Continue</span>
                <div class="spinner-border text-light" role="status" style="display: none;">
                  <span class="sr-only">Loading...</span>
                </div>
              </button>

            </form>
          </div>
        </div>
      </div>
@endsection
