<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ordaly - Complete Your Order</title>
    <meta name="description" content="Ordaly order checkout form" />
    <meta name="author" content="Ordaly" />

    <meta property="og:title" content="Ordaly - Complete Your Order" />
    <meta property="og:description" content="Complete your order with Ordaly" />
    <meta property="og:type" content="website" />
    <meta property="og:image" content="mlatest/image/landing-page-bg.png" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:site" content="@ordaly" />
    <meta name="twitter:image" content="mlatest/image/landing-page-bg.png" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- OpenStreetMap CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet-geosearch@3.0.0/dist/geosearch.css"/>

    <!-- OpenStreetMap JS -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet-geosearch@3.0.0/dist/geosearch.umd.js"></script>

    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              sans: ['Inter', 'sans-serif'],
              serif: ['Playfair Display', 'serif'],
            },
            animation: {
              'float': 'float 3s ease-in-out infinite',
              'pulse-soft': 'pulse-soft 4s ease-in-out infinite',
              'fade-in': 'fade-in 0.8s ease-out',
              'fade-in-delayed': 'fade-in 0.8s ease-out 0.3s forwards',
              'fade-in-delayed-more': 'fade-in 0.8s ease-out 0.6s forwards',
              'bounce-soft': 'bounce-soft 3s ease-in-out infinite',
              'shimmer': 'shimmer 2.5s linear infinite',
              'scale-in': 'scale-in 0.3s ease-out forwards',
              'border-glow': 'border-glow 2s ease-in-out infinite',
              'bg-shift': 'bg-shift 15s ease infinite alternate',
            },
            keyframes: {
              'float': {
                '0%, 100%': { transform: 'translateY(0)' },
                '50%': { transform: 'translateY(-10px)' }
              },
              'pulse-soft': {
                '0%, 100%': { opacity: '1', transform: 'scale(1)' },
                '50%': { opacity: '0.85', transform: 'scale(0.98)' }
              },
              'bounce-soft': {
                '0%, 100%': { transform: 'translateY(0)' },
                '50%': { transform: 'translateY(-15px)' }
              },
              'fade-in': {
                '0%': { opacity: '0', transform: 'translateY(20px)' },
                '100%': { opacity: '1', transform: 'translateY(0)' }
              },
              'scale-in': {
                '0%': { opacity: '0', transform: 'scale(0.95)' },
                '100%': { opacity: '1', transform: 'scale(1)' }
              },
              'shimmer': {
                '0%': { backgroundPosition: '-468px 0' },
                '100%': { backgroundPosition: '468px 0' }
              },
              'border-glow': {
                '0%, 100%': { boxShadow: '0 0 2px rgba(255, 105, 180, 0.4), 0 0 8px rgba(255, 105, 180, 0.1)' },
                '50%': { boxShadow: '0 0 10px rgba(255, 105, 180, 0.8), 0 0 20px rgba(255, 105, 180, 0.4)' }
              },
              'bg-shift': {
                '0%': { backgroundPosition: '0% 50%' },
                '50%': { backgroundPosition: '100% 50%' },
                '100%': { backgroundPosition: '0% 50%' }
              }
            },
            backgroundImage: {
              'gradient-primary': 'linear-gradient(45deg, #FF6B6B, #FFE66D, #4ECDC4, #556270)',
              'gradient-radial': 'radial-gradient(circle, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.6) 100%)',
              'gradient-card': 'linear-gradient(135deg, rgba(255,255,255,0.7) 0%, rgba(255,255,255,0.4) 100%)',
              'gradient-button': 'linear-gradient(90deg, #FF6B6B, #FF8E53, #FFD700)',
            },
          }
        }
      }
    </script>

    <style>
      .glass-morphism {
        background: rgba(255, 255, 255, 0.65);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      }

      .glass-panel {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      }

      .btn-gradient {
        background-size: 200% 200%;
        animation: gradient-shift 3s ease infinite;
      }

      @keyframes gradient-shift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
      }

      .shimmer-effect {
        background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.5) 50%, rgba(255,255,255,0) 100%);
        background-size: 200% 100%;
        animation: shimmer 2s infinite;
      }

      @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
      }

      .input-glow:focus {
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        transform: translateY(-2px);
      }

      .rainbow-text {
        background: linear-gradient(45deg, #f72585, #7209b7, #3a0ca3, #4361ee, #4cc9f0);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        background-size: 400% 400%;
        animation: rainbow 15s ease infinite;
      }

      @keyframes rainbow {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
      }
    </style>
  </head>

  <body style="background-image: url('https://foodstuff.store/mlatest/image/splash-bg.png'); background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="min-h-screen flex flex-col items-center justify-center p-4 md:p-6 overflow-hidden relative">
      <!-- Animated floating particles -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 h-16 w-16 rounded-full bg-pink-500/10 animate-bounce-soft" style="animation-delay: 0s;"></div>
        <div class="absolute top-1/3 right-1/3 h-12 w-12 rounded-full bg-yellow-500/10 animate-bounce-soft" style="animation-delay: 0.5s;"></div>
        <div class="absolute bottom-1/4 right-1/4 h-20 w-20 rounded-full bg-blue-500/10 animate-bounce-soft" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-1/3 left-1/3 h-14 w-14 rounded-full bg-green-500/10 animate-bounce-soft" style="animation-delay: 1.5s;"></div>
      </div>

      <div class="w-full max-w-3xl z-10">
        <!-- Logo with more animation -->
        <div class="flex justify-center mb-8 animate-fade-in">
          <div class="relative">
            <div class="absolute -inset-2 bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 rounded-full blur opacity-30 animate-pulse-soft"></div>
            <div class="relative flex items-center justify-center bg-white/80 backdrop-blur-sm rounded-2xl p-4 animate-float border-2 border-white shadow-lg">
              <img src="https://www.ordaly.ai/assets/orderly_logo-DFbKATeC.png" alt="Ordaly Logo" class="h-16" />
            </div>
          </div>
        </div>

        <!-- Form card with enhanced glass effect -->
        <div class="glass-morphism rounded-2xl p-6 md:p-8 shadow-xl opacity-0 animate-fade-in-delayed">
          <div class="text-center mb-8">
            <h1 class="text-3xl font-bold font-serif bg-gradient-to-br from-gray-900 to-gray-600 bg-clip-text text-transparent animate-fade-in-delayed">Complete Your Order</h1>
          </div>

          <form id="orderForm" class="space-y-6" method="post">
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

            @if (session('error'))
                        <div class="alert alert-danger">
                            <ul>
                                <li>{{ session('error') }}</li>
                            </ul>
                        </div>
                        @endif

            <!-- Personal Information -->
            <div class="space-y-4">
              <div class="relative">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                  Full Name
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                  <input
                    type="text"
                    id="fname"
                    name="fname"
                    value="{{ old('fname') }}"
                    class="transition-all duration-300 pl-10 w-full py-3 border border-gray-300 rounded-xl bg-white/50 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 focus:scale-[1.01] shadow-sm hover:shadow-md"
                    required
                  />
                  @error('fname')
                  <div class="text-danger">{{ $message }}</div>
              @enderror
                </div>
              </div>
              <input type="hidden" name="order_id" value="{{ $order_id}}" />
              <input type="hidden" name="restaurant_id" value="{{ $restaurant->id}}" />

              <div class="relative">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                  Email Address
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="transition-all duration-300 pl-10 w-full py-3 border border-gray-300 rounded-xl bg-white/50 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 focus:scale-[1.01] shadow-sm hover:shadow-md"
                    required
                  />
                  @error('email')
                  <div class="text-danger">{{ $message }}</div>
              @enderror
                </div>
              </div>

              <div class="relative">
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                  Phone Number
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                  </div>
                  <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="{{ $restaurant->phone }}"
                    class="pl-10 w-full py-3 border border-gray-300 rounded-xl bg-gray-100 text-gray-500 shadow-sm"
                    readonly
                  />
                  @error('phone')
                  <div class="text-danger">{{ $message }}</div>
              @enderror
                </div>
              </div>

              <div class="relative">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                  Delivery Address
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  </div>
                  <input
                    type="text"
                    id="address"
                    name="address"
                    placeholder="Enter your address"
                    class="transition-all duration-300 pl-10 w-full py-3 border border-gray-300 rounded-xl bg-white/50 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 focus:scale-[1.01] shadow-sm hover:shadow-md"
                    required
                    value="{{ old('address') }}"
                  />
                  @error('address')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
                  <div id="search-results" class="hidden absolute z-10 mt-1 w-full bg-white rounded-xl shadow-lg max-h-60 overflow-auto border border-gray-200"></div>
                </div>
              </div>
            </div>

            <!-- Order Summary with enhanced design -->
            <div class="mt-8 pt-6 border-t border-gray-200 rounded-xl overflow-hidden">
              <div class="bg-gradient-to-r from-indigo-50 via-purple-50 to-pink-50 p-5 rounded-xl shadow-inner">
                <h3 class="text-lg font-bold bg-gradient-to-r from-indigo-700 via-purple-700 to-pink-700 bg-clip-text text-transparent mb-4 flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                  </svg>
                  Order Summary
                </h3>
                <div class="space-y-3">
                  <div class="flex items-center justify-between">
                    <label for="subtotal" class="flex items-center text-sm text-gray-600">
                      <span>Subtotal</span>
                      <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                        Products
                      </span>
                    </label>
                    <div class="relative w-36">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 font-medium">₦</span>
                      </div>
                      <input
                        type="text"
                         id="sub_amount"
                        name="sub_amount"
                        value="{{ $sub_amount ?? '' }}"
                        class="pl-8 w-full py-2 border border-gray-300 rounded-lg bg-gray-100 text-right pr-3 font-medium text-gray-700"
                        readonly
                      />
                    </div>
                  </div>

                  <div class="flex items-center justify-between">
                    <label for="deliveryFee" class="flex items-center text-sm text-gray-600">
                      <span>Delivery Fee</span>
                      <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Fast
                      </span>
                    </label>
                    <div class="relative w-36">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 font-medium">₦</span>
                      </div>
                      <input
                        type="text"
                        id="deliveryFee"
                        name="delivery_fee"
                        value="1,500.00"
                        class="pl-8 w-full py-2 border border-gray-300 rounded-lg bg-gray-100 text-right pr-3 font-medium text-gray-700"
                        readonly
                      />
                    </div>
                  </div>

                  <div class="flex items-center justify-between">
                    <label for="serviceCharge" class="flex items-center text-sm text-gray-600">
                      <span>Service Charge</span>
                      <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Processing
                      </span>
                    </label>
                    <div class="relative w-36">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 font-medium">₦</span>
                      </div>
                      <input
                        type="text"
                        id="service_charge"
                        name="service_charge"
                        value="150.00"
                        class="pl-8 w-full py-2 border border-gray-300 rounded-lg bg-gray-100 text-right pr-3 font-medium text-gray-700"
                        readonly
                      />
                    </div>
                  </div>

                  <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                    <label for="total" class="block font-bold text-gray-800 flex items-center">
                      <span>Total Amount</span>
                      <div class="ml-2 relative">
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-pink-500 to-indigo-500 rounded-full blur opacity-30 animate-pulse-soft"></div>
                        <div class="relative px-2 py-0.5 rounded-full bg-white/80 text-xs font-medium text-pink-600">Final</div>
                      </div>
                    </label>
                    <div class="relative w-36">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-indigo-700 font-bold">₦</span>
                      </div>
                      <input
                        type="text"
                        id="total"
                        name="total_amount"
                       value="{{ $total_amount ?? '' }}"
                        class="pl-8 w-full py-3 bg-gradient-to-r from-indigo-100 to-purple-100 border border-indigo-200 rounded-lg text-right pr-3 font-bold text-indigo-700 shadow-inner"
                        readonly
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Submit button with enhanced design -->
            <div class="pt-6">
              <button
                type="submit"
                id="submitBtn"
                class="w-full relative overflow-hidden group py-4 px-6 rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white font-bold transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl"
                style="background-size: 200% 200%; animation: bg-shift 5s ease infinite;"
              >
                <div class="absolute top-0 right-0 bottom-0 left-0 opacity-0 group-hover:opacity-20 transition-opacity duration-300">
                  <div class="absolute inset-0 bg-white shimmer-effect"></div>
                </div>

                <span class="relative z-10 flex items-center justify-center tracking-wide">
                  <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  COMPLETE ORDER
                </span>
              </button>
            </div>
          </form>
        </div>

        <!-- Enhanced security badges -->
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4 opacity-0 animate-fade-in-delayed-more">
          <div class="flex items-center px-4 py-2 rounded-full glass-panel shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Secure Checkout</span>
          </div>

          <div class="flex items-center px-4 py-2 rounded-full glass-panel shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Fast Delivery</span>
          </div>

          <div class="flex items-center px-4 py-2 rounded-full glass-panel shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-pink-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
            </svg>
            <span class="text-sm font-medium text-gray-700">100% Satisfaction</span>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.gpteng.co/gptengineer.js" type="module"></script>

    <script>
      // Initialize OpenStreetMap search
      const provider = new GeoSearch.OpenStreetMapProvider();
      const search = document.getElementById('address');
      const results = document.getElementById('search-results');
      const form = document.getElementById('orderForm');

      // Handle form submission
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Show animation and loading
        const submitBtn = document.getElementById('submitBtn');
        const originalBtnContent = submitBtn.innerHTML;

        // Add loading animation
        submitBtn.innerHTML = `
          <span class="flex items-center justify-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            PROCESSING ORDER...
          </span>
        `;

        // Disable the submit button to prevent double submission
        submitBtn.disabled = true;

        // Submit the form
        form.submit();
      });

      // Address autocomplete functionality
      let timeoutId;
      search.addEventListener('input', function(e) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(async () => {
          if (e.target.value.length > 2) {
            try {
              const query = await provider.search({ query: e.target.value });
              displayResults(query);
            } catch (error) {
              console.error('Search failed:', error);
            }
          } else {
            results.innerHTML = '';
            results.classList.add('hidden');
          }
        }, 300);
      });

      function displayResults(addresses) {
        results.innerHTML = '';
        addresses.forEach(address => {
          const div = document.createElement('div');
          div.className = 'px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 cursor-pointer transition-colors duration-150 flex items-center';
          div.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="truncate">${address.label}</span>
          `;
          div.addEventListener('click', () => {
            search.value = address.label;
            results.innerHTML = '';
            results.classList.add('hidden');
            // Store coordinates if needed
            if (document.getElementById('latitude')) {
              document.getElementById('latitude').value = address.y;
            }
            if (document.getElementById('longitude')) {
              document.getElementById('longitude').value = address.x;
            }
          });
          results.appendChild(div);
        });
        results.classList.remove('hidden');
      }

      // Close results when clicking outside
      document.addEventListener('click', function(e) {
        if (!search.contains(e.target) && !results.contains(e.target)) {
          results.classList.add('hidden');
        }
      });

      // Add floating label animation to inputs
      const inputs = document.querySelectorAll('input:not([readonly])');
      inputs.forEach(input => {
        input.addEventListener('focus', function() {
          const label = document.querySelector(`label[for="${this.id}"]`);
          if (label) {
            label.classList.add('text-indigo-600', 'scale-105', 'origin-left', 'transition-transform');
          }
        });

        input.addEventListener('blur', function() {
          const label = document.querySelector(`label[for="${this.id}"]`);
          if (label && !this.value) {
            label.classList.remove('text-indigo-600', 'scale-105');
          }
        });
      });
    </script>
  </body>
</html>
