@extends('layouts.custom-guest')
@section('link')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <!-- jQuery -->
    <style>
    #select2-selection {
        height: 45px; /* Adjust the height */
        font-size: 18px; /* Adjust the font size */
        padding: 10px; /* Adjust padding for better spacing */
      }
      </style>
@endsection

@section('slot')
    <div class="container login-container">
        <div class="mx-auto card" style="padding: 32px;">
          <div class="card-body" style="padding: 0px;">
            <div class="row">
              <div class="align-item-left">
                <img src="{{asset('mlatest/image/FSSLOGO1-2.png')}}" alt="Logo" style="width:146px; height:36px; margin-bottom:16px; padding:0px">
              </div>
            </div>
            <h3 class="card-title login-header" style="margin-bottom:8px;">Welcome to FoodStuff Store Dinner Party</h3>
            {{-- <p style="font-size:14px">Menu</p> --}}


                <div class="text-center card-body">
                    <h5 class="card-title">Menu</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Starters</strong></li>
                        <li class="list-group-item">Samosa</li>
                        <li class="list-group-item">Spring Roll</li>
                        <li class="list-group-item">Puff</li>
                        <li class="list-group-item">Chicken Wings</li>
                        <li class="list-group-item">Bread Rolls</li>
                        <li class="list-group-item">Chicken Peppersoup</li>

                        <li class="list-group-item"><strong>Soups</strong></li>
                        <li class="list-group-item">Native Soup</li>
                        <li class="list-group-item">Vegetable Soup</li>
                        <li class="list-group-item">Egusi Soup</li>
                        <li class="list-group-item">Coconut Punky Rice</li>
                        <li class="list-group-item">Jollof Rice</li>
                        <li class="list-group-item">Fiesta Fried Rice</li>

                        <li class="list-group-item"><strong>Main</strong></li>
                        <li class="list-group-item">Ofada Rice served with Ofada Sauce</li>
                        <li class="list-group-item">Yam Porridge</li>

                        <li class="list-group-item"><strong>Protein</strong></li>
                        <li class="list-group-item">BBQ Fish</li>
                        <li class="list-group-item">Grilled Chicken</li>
                        <li class="list-group-item">Spicy Beef</li>

                        <li class="list-group-item"><strong>Sides</strong></li>
                        <li class="list-group-item">Cole Slaw</li>
                        <li class="list-group-item">Dodo</li>
                        <li class="list-group-item">Moi-Moi</li>

                        <li class="list-group-item"><strong>Swallow</strong></li>
                        <li class="list-group-item">Eba</li>
                        <li class="list-group-item">Semo</li>
                    </ul>
                </div>


            <form method="POST" action="{{ route('caterer-store') }}"  onsubmit="showLoader(this)">
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
                <label for="table_number" class="body-texts" style="margin-bottom:8px;">Table Number</label>

                <!-- Replace the input with a select element for select2 -->
                <select name="table_number" id="select2" class="form-control" style="height:50px;">
                    <option value="">Select a table and chair</option>
                    @foreach ($tables as $table => $chairs)
                        @foreach ($chairs as $chair)
                            <option value="{{ $table }} {{ $chair }}">{{ $table }} - {{ $chair }}</option>
                        @endforeach
                    @endforeach

                </select>

                @error('table_number')
                    <span class="text-error">
                        {{$message}}
                    </span>
                @enderror
            </div>



            <div class="form-group" style="margin-top: 40px;">
                <label for="main" class="body-texts" style="margin-bottom:8px;">Main Dish</label>

                <!-- Replace the input with a select element for select2 -->
                <select name="main" id="select4"  class="form-control" style="height:50px;">
                    <option value="">-- Select a Main Dish --</option>
                    <option value="Coconut Punky Rice">Coconut Punky Rice</option>
                    <option value="Jollof Rice">Jollof Rice</option>
                    <option value="Fiesta Fried Rice">Fiesta Fried Rice</option>
                    <option value="Ofada Rice">Ofada Rice served with Ofada Sauce</option>
                    <option value="Yam Porridge">Yam Porridge</option>
                </select>

                @error('main')
                    <span class="text-error">
                        {{$message}}
                    </span>
                @enderror
            </div>


            <div class="form-group" style="margin-top: 40px;">
                <label for="starter" class="body-texts" style="margin-bottom:8px;">Soup</label>

                <!-- Replace the input with a select element for select2 -->
                <select name="starter" id="select3"  class="form-control" style="height:50px;">
                    <option value="">-- Select  Soup --</option>
                    <option value="Native Soup">Native Soup</option>
                    <option value="Vegetable Soup">Vegetable Soup</option>
                    <option value="Egusi Soup">Egusi Soup</option>
                    </select>

                @error('starter')
                    <span class="text-error">
                        {{$message}}
                    </span>
                @enderror
            </div>


            <div class="form-group" style="margin-top: 40px;">
                <label for="protein" class="body-texts" style="margin-bottom:8px;">Protein</label>

                <!-- Replace the input with a select element for select2 -->
                <select name="protein" id="select5"  class="form-control" style="height:50px;">
                    <option value="">-- Select a Protein --</option>
                    <option value="BBQ Fish">BBQ Fish</option>
                    <option value="Grilled Chicken">Grilled Chicken</option>
                    <option value="Spicy Beef">Spicy Beef</option>
                </select>

                @error('protein')
                    <span class="text-error">
                        {{$message}}
                    </span>
                @enderror
            </div>
            <div class="form-group" style="margin-top: 40px;">
                <label for="sides" class="body-texts" style="margin-bottom:8px;">Sides</label>

                <!-- Replace the input with a select element for select2 -->
                <select name="sides" id="select6" class="form-control" style="height:50px;">
                    <option value="">-- Select a Side --</option>
                    <option value="Cole Slaw">Cole Slaw</option>
                    <option value="Dodo">Dodo</option>
                    <option value="Moi-Moi">Moi-Moi</option>
                </select>

                @error('sides')
                    <span class="text-error">
                        {{$message}}
                    </span>
                @enderror
            </div>

            <div class="form-group" style="margin-top: 40px;">
                <label for="swallow" class="body-texts" style="margin-bottom:8px;">Swallow</label>

                <!-- Replace the input with a select element for select2 -->
                <select name="swallow"  id="select7" class=" form-control" style="height:50px;">
                    <option value="">-- Select a Swallow --</option>
                    <option value="Eba">Eba</option>
                    <option value="Semo">Semo</option>
                </select>

                @error('swallow')
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
          </div>
        </div>
      </div>
@section('script')

      <!-- Select2 JS -->
      <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>

$(document).ready(function() {
    const select2Elements = $('#select2');
    select2Elements.select2();
    $('#select3').select2();
    $('#select4').select2();
    $('#select5').select2();
    $('#select6').select2();
    $('#select7').select2();
});
</script>
@endsection
@endsection
