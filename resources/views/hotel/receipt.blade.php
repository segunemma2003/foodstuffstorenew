<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />
    <meta name="description" content="Procurement and supply of raw foodstuff products to bridge the gap in food supply chain in Africa." />
    <meta name="author" content="Jedi Labs" />
    <title>Foodstuff Store | Procurement and supply of raw foodstuff products to bridge the gap in food supply chain in Africa.</title>

    <!-- Open Graph Meta Tags for Facebook and WhatsApp -->
    <meta property="og:title" content="Foodstuff Store">
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Foodstuff Store" />
    <meta property="og:description" content="Procurement and supply of raw foodstuff products to bridge the gap in food supply chain in Africa.">
    <meta property="og:image" content="latest/image/landing-page-bg.png">
    <meta property="og:image:width" content="540">
    <meta property="og:image:height" content="540">
    <meta property="og:url" content="https://foodstuff.store">

    <!-- Twitter Card Meta Tags for Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Foodstuff Store">
    <meta name="twitter:description" content="Procurement and supply of raw foodstuff products to bridge the gap in food supply chain in Africa.">
    <meta name="twitter:image" content="latest/image/landing-page-bg.png">

    <!-- Standard Meta Tags for Older Platforms -->
    <meta property="image" content="latest/image/landing-page-bg.png">
    <meta name="thumbnail" content="latest/image/landing-page-bg.png">

    <!-- favicon -->
    <link rel="shortcut icon" href="{{asset('latest/image/logo-head.png')}}" type="img/x-icon" />

    <!-- Bootstrap & icons -->
    <link rel="stylesheet" href="{{asset('latest/css/bootstrap-montserrat-font.css')}}" />
    <link rel="stylesheet" href="{{asset('latest/fonts/fontawesome-5/css/all.css')}}" />

    <!-- Google webfont -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;900&display=swap" rel="stylesheet">

    <!-- Plugin'stylesheets -->
    <link rel="stylesheet" href="{{asset('latest/plugins/slick/slick.min.css')}}" />
    <link rel="stylesheet" href="{{asset('latest/plugins/fancybox/jquery.fancybox.min.css')}}" />
    <link rel="stylesheet" href="{{asset('latest/plugins/aos/aos.min.css')}}" />
    <link rel="stylesheet" href="{{asset('latest/plugins/nice-select/nice-select.min.css')}}" />

    <!-- TrustBox script -->
    <script type="text/javascript" src="//widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js" async></script>
    <!-- End TrustBox script -->

    <!-- Vendor stylesheets -->
    <link rel="stylesheet" href="{{asset('latest/css/main.css')}}" />

    <!-- Custom stylesheets -->
    <link rel="stylesheet" href="{{asset('latest/css/custom.css')}}" />
    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
        }
        .body-texts {
        font-family: 'Urbanist Regular', sans-serif;
        margin-bottom: 20px;
        }
        .card {
            width: 100%;
            max-width: 500px;
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-header {
          background: url("{{asset('latest/image/foodstuff-store-header-bg.png')}}") no-repeat center center;
            background-size: cover;
            color: white;
            text-align: right;
            height: 115px;
            align-items: center;
        }
        .card-footer {
            background-color: white;
            border: none;
            text-align: center;
        }
        .discount-badge {
            background-color: #FFF9F9;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .amount {
            font-weight: bold;
            color: green;
        }
        .link {
            color: blue;
            text-decoration: none;
        }
        .link:hover {
            text-decoration: underline;
        }
        @media (max-width: 767px) {
        .card-body {
          padding: 32px;
        }
        .card-footer {
          padding: 32px;
        }
      }
      @media (min-width: 768px) {
        .card-body {
          padding: 64px;
        }
        .card-footer {
          padding-bottom: 64px;
        }
      }
    </style>
  </head>
  <body class="coodiv-clouds-theme">
    <!-- DELETE THIS IF YOU WANT TO REMOVE PAGELOADER ANIMATION -->
    <div class="preloader">
      <div class="preloader-container">
        <div class="lds-ring">
          <div></div>
          <div></div>
          <div></div>
          <div></div>
        </div>
        <span data-i18n="[html]genaral.loading" class="coodiv-text-10 color-blackish-blue">Loading ...</span>
      </div>
    </div>
    <!-- DELETE THIS IF YOU WANT TO REMOVE PAGELOADER ANIMATION -->
    <div class="card">
          <div class="card-header">
            <div class="row">
                <div class="col-6 text-left" style="padding-top:20px; padding-left: 44px;"><img src="{{asset('latest/image/logo-head-2.png')}}" alt="Logo" height="48"></div>
                <div class="col-6 text-right" style="padding-top:30px; padding-right: 44px;"><span style="font-size:18px">15 June, 2024</span></div>
            </div>
          </div>
        <div class="card-body">
            <h5 class="card-title body-texts" style="font-size:25px">Bill Receipt Confirmed!</h5>
            <div class="discount-badge body-texts" style="font-size:18px">{{ $data['discount_percent'] }}% discount off</div>
            <div class="row">
                <div class="col-6 text-left body-texts" style="font-size:18px">User's code:</div>
                <div class="col-6 text-right body-texts" style="font-size:18px"><strong>{{$data['code']}}</strong></div>
            </div>
            <div class="row">
                <div class="col-6 text-left body-texts" style="font-size:18px">User's name:</div>
                <div class="col-6 text-right body-texts" style="font-size:18px"><strong>{{$data['username']}}</strong></div>
            </div>
            <div class="row">
                <div class="col-6 text-left body-texts" style="font-size:18px">Initial Bill:</div>
                <div class="col-6 text-right body-texts" style="font-size:18px"><strong>NGN {{number_format($data['amount'], 2, '.', ',')}} </strong></div>
            </div>
            <!-- <div class="row">
                <div class="col-6 text-left body-texts" style="font-size:18px">After discount::</div>
                <div class="col-6 text-right body-texts" style="font-size:18px"><strong>SD43576KI</strong></div>
            </div> -->
            <div class="row">
                <div class="col-6 text-left body-texts" style="font-size:18px">Discounted Amount:</div>
                <div class="col-6 text-right body-texts" style="font-size:18px"><strong>NGN {{number_format($data['discount_amount'], 2, '.', ',')}}</strong></div>
            </div>
            <div class="row">
                <div class="col-6 text-left body-texts" style="font-size:18px">{{$data['username']}} should pay:</div>
                <div class="col-6 text-right body-texts" style="font-size:25px"><span class="amount">NGN {{number_format($data['current_amount'], 2, '.', ',')}}</span></div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{url('/home/hotel')}}" class="link body-text">Got it, Done</a>
        </div>
    </div>

    <!-- Vendor Scripts -->
    <script src="{{asset('latest/js/vendor.min.js')}}"></script>
    <!-- Plugin's Scripts -->
    <script src="{{asset('latest/plugins/fancybox/jquery.fancybox.min.js')}}"></script>
    <script src="{{asset('latest/plugins/nice-select/jquery.nice-select.min.js')}}"></script>
    <script src="{{asset('latest/plugins/aos/aos.min.js')}}"></script>
    <script src="{{asset('latest/plugins/slick/slick.min.js')}}"></script>
    <script src="{{asset('latest/plugins/typed/typed.min.js')}}"></script>
    <script src="{{asset('latest/plugins/waypoints/jquery.waypoints.min.js')}}"></script>
    <script src="{{asset('latest/plugins/particles/particles-code.js')}}"></script>
    <script src="{{asset('latest/plugins/particles/particles.js')}}"></script>
    <script src="{{asset('latest/plugins/parallax/parallax.js')}}"></script>
    <script src="{{asset('latest/plugins/smoothscroll/smoothscroll.js')}}"></script>
    <script src="{{asset('latest/plugins/lordicon/lord-icon-2.1.0.min.js')}}"></script>

    <!-- Translate Scripts -->
    <script src="{{asset('latest/plugins/i18next/i18next.min.js')}}"></script>
    <script src="{{asset('latest/plugins/i18next/i18nextXHRBackend.min.js')}}"></script>
    <script src="{{asset('latest/plugins/i18next/jquery-i18next.min.js')}}"></script>

    <!-- Activation Script -->
    <script src="{{asset('latest/js/custom.js')}}"></script>
  </body>
</html>
