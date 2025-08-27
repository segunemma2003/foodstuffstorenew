<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - {{ $company ?? config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .status-card {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
        }
        .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }
        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            stroke-width: 4;
            stroke: #fff;
            stroke-miterlimit: 10;
            margin: 0 auto;
            box-shadow: 0 0 0 rgba(68, 183, 123, 0.4);
            animation: pulse 2s ease-in-out infinite;
        }
        .cross {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            stroke-width: 4;
            stroke: #fff;
            stroke-miterlimit: 10;
            margin: 0 auto;
            box-shadow: 0 0 0 rgba(239, 68, 68, 0.4);
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); }
            50% { transform: scale(1.05); }
            100% { transform: scale(0.95); }
        }
        .checkmark-circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 4;
            stroke-miterlimit: 10;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }
        .checkmark-check {
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }
        .cross-circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 4;
            stroke-miterlimit: 10;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }
        .cross-line {
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }
        @keyframes stroke {
            100% { stroke-dashoffset: 0; }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm mx-auto">
        <!-- Company Name -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">{{ $company ?? config('app.name') }}</h1>
            <p class="text-gray-500 mt-1">Payment Status</p>
        </div>

        <!-- Status Card -->
        <div class="status-card bg-white rounded-2xl p-8 text-center">
            @if($status === 'success')
                <!-- Success State -->
                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none" style="stroke: #10b981"/>
                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" style="stroke: #10b981"/>
                </svg>
                <h2 class="text-2xl font-bold text-gray-800 mt-6 mb-2">Order Completed</h2>
                <p class="text-gray-600 mb-6">Thank you for your purchase!</p>
                <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-3 text-sm text-emerald-700">
                    Transaction ID: {{ $reference ?? 'N/A' }}
                </div>
            @else
                <!-- Failed State -->
                <svg class="cross" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="cross-circle" cx="26" cy="26" r="25" fill="none" style="stroke: #ef4444"/>
                    <path class="cross-line" fill="none" d="M16 16 36 36" style="stroke: #ef4444"/>
                    <path class="cross-line" fill="none" d="M16 36 36 16" style="stroke: #ef4444"/>
                </svg>
                <h2 class="text-2xl font-bold text-gray-800 mt-6 mb-2">Order Failed</h2>
                <p class="text-gray-600 mb-6">Payment could not be processed</p>
                <div class="bg-red-50 border border-red-100 rounded-lg p-3 text-sm text-red-700">
                    {{ $paystackData['message'] ?? 'Please try again later' }}
                </div>
            @endif

            <!-- Action Button -->

        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-sm text-gray-500">
            Need help? <a href="mailto:support@foodstuff.store" class="text-blue-600 hover:underline">Contact us</a>
        </div>
    </div>
</body>
</html>
