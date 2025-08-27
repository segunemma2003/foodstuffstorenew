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
        <h1 class="mb-4 text-xl font-semibold text-gray-800">Invoice for Your Recent Order</h1>

        <p class="mb-6 text-gray-700">Hello {{ $customer->fname}},</p>
        <p class="mb-4 text-gray-700">
            Thank you for your recent purchase from {{ config('app.name') }}! Attached to this email is the invoice for the products you ordered.
        </p>

        <p class="mb-4 text-gray-700">
            If you have any questions, feel free to reach us at support@foodstuff.store.
        </p>

        <p class="text-gray-700">
            Best regards, <br>
            The {{ config('app.name') }} Team
        </p>
    </div>
</body>
</html>
