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
    <meta property="og:image" content="mlatest/image/landing-page-bg.png">
    <meta property="og:image:width" content="540">
    <meta property="og:image:height" content="540">
    <meta property="og:url" content="https://foodstuff.store">

    <!-- Twitter Card Meta Tags for Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Foodstuff Store">
    <meta name="twitter:description" content="Procurement and supply of raw foodstuff products to bridge the gap in food supply chain in Africa.">
    <meta name="twitter:image" content="{{asset('mlatest/image/landing-page-bg.png')}}">

    <!-- Standard Meta Tags for Older Platforms -->
    <meta property="image" content="{{asset('mlatest/image/landing-page-bg.png')}}">
    <meta name="thumbnail" content="{{asset('mlatest/image/landing-page-bg.png')}}">

    <!-- favicon -->
    <link rel="shortcut icon" href="{{asset('mlatest/image/logo-head.png')}}" type="img/x-icon" />

    <!-- Bootstrap & icons -->
    <link rel="stylesheet" href="{{asset('mlatest/css/bootstrap-montserrat-font.css')}}" />
    <link rel="stylesheet" href="{{asset('mlatest/fonts/fontawesome-5/css/all.css')}}" />

    <!-- Google webfont -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;900&display=swap" rel="stylesheet">

    <!-- Plugin'stylesheets -->
    <link rel="stylesheet" href="{{asset('mlatest/plugins/slick/slick.min.css')}}" />
    <link rel="stylesheet" href="{{asset('mlatest/plugins/fancybox/jquery.fancybox.min.css')}}" />
    <link rel="stylesheet" href="{{asset('mlatest/plugins/aos/aos.min.css')}}" />
    {{-- <link rel="stylesheet" href="{{asset('mlatest/plugins/nice-select/nice-select.min.css')}}" /> --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    {{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/nice-select2/1.0.0/css/nice-select.css" rel="stylesheet" /> --}}


    <!-- TrustBox script -->
    <script type="text/javascript" src="//widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js" async></script>
    <!-- End TrustBox script -->

    <!-- Vendor stylesheets -->
    <link rel="stylesheet" href="{{asset('mlatest/css/main.css')}}" />

    <!-- Custom stylesheets -->
    <link rel="stylesheet" href="{{asset('mlatest/css/custom.css')}}" />
    <!-- Add this in your layout file or Blade template -->
    @yield('link')

    <style>
      @font-face {
        font-family: 'Made Gentle';
        src: url('{{asset('mlatest/fonts/made-gentle/madegentle.otf')}}') format('opentype');
      }
      @font-face {
        font-family: 'Urbanist Regular';
        src: url('{{asset('mlatest/fonts/Urbanist/Urbanist-Regular.otf')}}') format('opentype');
      }

      .body-style {
        background-image: url('{{asset('mlatest/image/splash-bg.png')}}');
        background-repeat: repeat; /* Ensures the image repeats */
        background-size: auto; /* Default size for repeated background */
        background-position: top left; /* Starts tiling from the top-left corner */
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
  <body class="coodiv-clouds-theme body-style" style="padding-top: 80px;">
    <!-- DELETE THIS IF YOU WANT TO REMOVE PAGELOADER ANIMATION -->
    <div class="preloader">
      <div class="preloader-container">
        <div class="lds-ring">
          <div></div>
          <div></div>
          <div></div>
          <div></div>
        </div>
        <span data-i18n="[html]genaral.loading" class="coodiv-text-10">Loading ...</span>
      </div>
    </div>
    <!-- DELETE THIS IF YOU WANT TO REMOVE PAGELOADER ANIMATION -->
    @yield('slot')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Vendor Scripts -->
    <script src="{{asset('mlatest/js/vendor.min.js')}}"></script>
    <!-- Plugin's Scripts -->
    <script src="{{asset('mlatest/plugins/fancybox/jquery.fancybox.min.js')}}"></script>

    {{-- <script src="{{asset('mlatest/plugins/nice-select/jquery.nice-select.min.js')}}"></script> --}}
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/nice-select2/1.0.0/js/nice-select.min.js"></script> --}}
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}
    <script src="{{asset('mlatest/plugins/aos/aos.min.js')}}"></script>
    <script src="{{asset('mlatest/plugins/slick/slick.min.js')}}"></script>
    <script src="{{asset('mlatest/plugins/typed/typed.min.js')}}"></script>
    <script src="{{asset('mlatest/plugins/waypoints/jquery.waypoints.min.js')}}"></script>
    <script src="{{asset('mlatest/plugins/particles/particles-code.js')}}"></script>
    <script src="{{asset('mlatest/plugins/particles/particles.js')}}"></script>
    <script src="{{asset('mlatest/plugins/parallax/parallax.js')}}"></script>
    <script src="{{asset('mlatest/plugins/smoothscroll/smoothscroll.js')}}"></script>
    <script src="{{asset('mlatest/plugins/lordicon/lord-icon-2.1.0.min.js')}}"></script>

    <!-- Translate Scripts -->
    <script src="{{asset('mlatest/plugins/i18next/i18next.min.js')}}"></script>
    <script src="{{asset('mlatest/plugins/i18next/i18nextXHRBackend.min.js')}}"></script>
    <script src="{{asset('mlatest/plugins/i18next/jquery-i18next.min.js')}}"></script>

    <!-- Activation Script -->
    <script src="{{ asset('mlatest/js/custom.js') }}?v={{ time() }}"></script>

    <script>
    //   function showLoader(button) {
    //     // Hide button text
    //     const buttonText = button.querySelector(".button-text");
    //     buttonText.style.visibility = "hidden";
    //     buttonText.innerText = "";

    //     // Show loader
    //     const loader = button.querySelector(".spinner-border");
    //     loader.style.display = "block";

    //     // Simulate a delay for demonstration purposes (e.g., API call)
    //     setTimeout(() => {
    //       buttonText.style.visibility = "visible";
    //       buttonText.innerText = "Continue";
    //       loader.style.display = "none";
    //     }, 3000); // Adjust delay as needed
    //   }
    function showLoader(button) {
    // Hide button text
    const buttonText = button.querySelector(".button-text");
    buttonText.style.visibility = "hidden";
    buttonText.innerText = "";

    // Show loader
    const loader = button.querySelector(".spinner-border");
    loader.style.display = "block";

    // Disable the button to prevent multiple clicks
    button.disabled = true;

    // Allow the form to submit normally
    const form = button.closest("form");
    form.submit();
}





    </script>
    @yield('script')
  </body>
</html>
