@extends('layouts.custom-guest')

@section('slot')
<div class="card">
    <div class="card-header">
      <div class="row">
          <div class="text-left col-6" style="padding-top:20px; padding-left: 44px;"><img src="{{asset('latest/image/logo-head-2.png')}}" alt="Logo" height="48"></div>
          <div class="text-right col-6" style="padding-top:30px; padding-right: 44px;"><span style="font-size:18px">15 June, 2024</span></div>
      </div>
    </div>
  <div class="card-body">
      <h5 class="card-title body-texts" style="font-size:25px">Bill Receipt Confirmed!</h5>
      <div class="discount-badge body-texts" style="font-size:18px">{{ $data['discount_percent'] }}% discount off</div>
      <div class="row">
          <div class="text-left col-6 body-texts" style="font-size:18px">User's code:</div>
          <div class="text-right col-6 body-texts" style="font-size:18px"><strong>{{$data['code']}}</strong></div>
      </div>
      <div class="row">
          <div class="text-left col-6 body-texts" style="font-size:18px">User's name:</div>
          <div class="text-right col-6 body-texts" style="font-size:18px"><strong>{{$data['username']}}</strong></div>
      </div>
      <div class="row">
          <div class="text-left col-6 body-texts" style="font-size:18px">Initial Bill:</div>
          <div class="text-right col-6 body-texts" style="font-size:18px"><strong>NGN {{number_format($data['amount'], 2, '.', ',')}} </strong></div>
      </div>
      <!-- <div class="row">
          <div class="text-left col-6 body-texts" style="font-size:18px">After discount::</div>
          <div class="text-right col-6 body-texts" style="font-size:18px"><strong>SD43576KI</strong></div>
      </div> -->
      <div class="row">
          <div class="text-left col-6 body-texts" style="font-size:18px">Discounted Amount:</div>
          <div class="text-right col-6 body-texts" style="font-size:18px"><strong>NGN {{number_format($data['discount_amount'], 2, '.', ',')}}</strong></div>
      </div>
      <div class="row">
          <div class="text-left col-6 body-texts" style="font-size:18px">{{$data['username']}} should pay:</div>
          <div class="text-right col-6 body-texts" style="font-size:25px"><span class="amount">NGN {{number_format($data['current_amount'], 2, '.', ',')}}</span></div>
      </div>
  </div>
  <div class="card-footer">
      <a href="{{url('/company/promo')}}" class="link body-text">Got it, Done</a>
  </div>
@endsection
