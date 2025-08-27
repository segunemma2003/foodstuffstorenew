@extends('layouts.custom-guest')

@section('slot')
<div class="container" style="text-align: center;">
    <h1>Order QR Code</h1>
    <p>Scan the QR code below to view order details:</p>

    <!-- Container for QR Code -->
    <div class="qr-container" style="display: inline-block; text-align: center;">
        {!! $qrCode !!}
    </div>

    <!-- Print button below the QR Code -->
    <div class="print-btn-container" style="margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">
            Print QR Code
        </button>
    </div>
</div>

<style>
    /* Center the QR code container */
    .container {
        text-align: center;
        margin-top: 50px;
    }

    /* Styling for the QR code container */
    .qr-container {
        display: inline-block;
        text-align: center;
    }

    .print-btn-container {
        margin-top: 20px;
    }

    /* Media query for print */
    @media print {
        /* Hide the button when printing */
        .print-btn-container {
            display: none;
        }

        /* Ensure the QR code is centered in the printed page */
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;  /* Stack elements vertically */
            height: 100vh; /* Center content vertically on the page */
            margin: 0; /* Remove default margin */
        }

        /* Ensure QR Code appears correctly on the printed page */
        .qr-container {
            width: 300px; /* Adjust the size of the QR code */
            height: 300px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    }
</style>
@endsection
