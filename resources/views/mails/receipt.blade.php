<!DOCTYPE html>
<html>
<head>
    <style>
        /* Import Tailwind CSS */
        @import url('https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css');
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-xl p-6 mx-auto bg-white rounded-md shadow-md">
        <h1 class="mb-4 text-xl font-semibold text-gray-800">Receipt for Your Recent Purchase</h1>

        <p class="mb-6 text-gray-700">Hello {{ $customer->fname }},</p>

        <p class="mb-4 text-gray-700">
            Thank you for shopping with {{ config('app.name') }}! Attached to this email, you will find the receipt for your recent purchase.
        </p>

        <p class="mb-4 text-gray-700">
            If you have any questions regarding your purchase or the receipt, feel free to reach out to us at support@foodstuff.store.
        </p>

        <p class="text-gray-700">
            We appreciate your business and look forward to serving you again soon.
        </p>

        <p class="mt-6 text-gray-700">Best regards,</p>
        <p class="text-gray-700">{{ config('app.name') }} Team</p>
    </div>
</body>
</html>
