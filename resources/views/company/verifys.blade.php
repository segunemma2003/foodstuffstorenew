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
    <meta property="og:image" content="{{asset('latest/image/landing-page-bg.png')}}">
    <meta property="og:image:width" content="540">
    <meta property="og:image:height" content="540">
    <meta property="og:url" content="https://foodstuff.store">

    <!-- Twitter Card Meta Tags for Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Foodstuff Store">
    <meta name="twitter:description" content="Procurement and supply of raw foodstuff products to bridge the gap in food supply chain in Africa.">
    <meta name="twitter:image" content="{{asset('latest/image/landing-page-bg.png')}}">

    <!-- Standard Meta Tags for Older Platforms -->
    <meta property="image" content="{{asset('latest/image/landing-page-bg.png')}}">
    <meta name="thumbnail" content="{{asset('latest/image/landing-page-bg.png')}}">

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
      @font-face {
        font-family: 'Made Gentle';
        src: url("{{asset('latest/fonts/made-gentle/madegentle.otf')}}") format('opentype');
      }
      @font-face {
        font-family: 'Urbanist Regular';
        src: url("{{asset('latest/fonts/Urbanist/Urbanist-Regular.otf')}}") format('opentype');
      }

      .login-header {
        font-family: 'Made Gentle', sans-serif;
        margin-bottom: 20px;
      }
      .body-texts {
        font-family: 'Urbanist Regular', sans-serif;
        margin-bottom: 20px;
      }
      .card {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        background-color: #ffffff;
        border-width: 0px;
      }
      .card-body {
        text-align: left;
      }
      .mx-auto img {
        display: block;
        margin-left: auto;
        margin-right: auto;
        height: 48px;
        width: 48px;
        margin-bottom: 64px;
        margin-top: 64px;
      }
      .form-group {
        margin-top: 20px;
      }
      .btn {
        background-color: #C31222;
        margin-top: 32px;
        margin-bottom: 32px;
      }
      @media (max-width: 767px) {
        .card {
          width: 100%;
          padding: 32px;
        }
      }
      @media (min-width: 768px) {
        .card {
          width: 50%;
          padding: 64px;
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
    <div class="container login-container">
      <div class="mx-auto card" style="margin-top: 40px;">
        <div class="card-body">
          <div class="mx-auto">
            <img src="{{asset('latest/image/logo-head.png')}}"  width="100" height="100" alt="Logo">
            @auth
            <div>
             <h3><b>Your Balance is :</b> {{ $users->points}}</h3>
            </div>
            @endauth
          </div>
          <h4 class="card-title login-header">Collect user's details</h4>
          <a href="/my_company">Click to check Dashboard</a>
          <p style="font-size:14px">Validate user and apply discount with the provided form below</p>
          <form method="post">
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
              <label for="unique_code" class="body-texts">Enter Company Unique Code</label>
              <input type="text" name="unique_code" required class="form-control" id="unique_code" placeholder="Enter Company Unique Code">
              @error('unique_code')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
            </div> -->
            <div class="form-group" style="margin-top: 40px;">
              <label for="email" class="body-texts">Enter Customer's email address</label>
              <input type="email" name="email" required class="form-control" id="email" placeholder="User's email address">
              @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
            </div>
            <div class="form-group" style="margin-top: 20px;">
              <label for="amount" class="body-texts">Enter bill amount</label>
              <input type="number" step=".01" required name="amount" inputmode="numeric" class="form-control" id="amount" placeholder="Amount">
              @error('amount')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
            </div>
            <button type="submit" class="text-white btn btn-block">Continue</button>
          </form>
        </div>
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
