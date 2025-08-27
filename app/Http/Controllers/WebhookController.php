<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    public function handlePaystackWebhook(Request $request)
    {
        Log::info('=== PAYSTACK WEBHOOK STARTED ===');
        Log::info('Webhook received', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'body_size' => strlen($request->getContent())
        ]);

        // Verify the webhook signature
        Log::info('Verifying Paystack signature...');
        if (!$this->verifyPaystackSignature($request)) {
            Log::error('❌ Invalid Paystack webhook signature');
            Log::info('=== PAYSTACK WEBHOOK ENDED (SIGNATURE FAILED) ===');
            return response()->json(['error' => 'Invalid signature'], 400);
        }
        Log::info('✅ Paystack signature verified successfully');

        $event = $request->input('event');
        $data = $request->input('data');

        Log::info('Processing webhook event', [
            'event' => $event,
            'data_keys' => array_keys($data ?? []),
            'data_size' => strlen(json_encode($data))
        ]);

        switch ($event) {
            // Transfer Events
            case 'transfer.paid':
                Log::info('🔄 Processing transfer.paid event');
                $result = $this->handleTransferPaid($data);
                Log::info('✅ transfer.paid event processed');
                return $result;

            case 'transfer.success':
                Log::info('🔄 Processing transfer.success event');
                $result = $this->handleTransferSuccess($data);
                Log::info('✅ transfer.success event processed');
                return $result;

            case 'transfer.failed':
                Log::info('🔄 Processing transfer.failed event');
                $result = $this->handleTransferFailed($data);
                Log::info('✅ transfer.failed event processed');
                return $result;

            default:
                Log::warning('⚠️ Unhandled Paystack event', ['event' => $event]);
                Log::info('=== PAYSTACK WEBHOOK ENDED (UNHANDLED EVENT) ===');
                return response()->json(['status' => 'ignored']);
        }
    }

    private function handleTransferPaid($data)
    {
        Log::info('=== TRANSFER PAID PROCESSING STARTED ===');

        try {
            Log::info('Extracting transfer data...');
            $transferId = $data['id'];
            $amount = $data['amount'];
            $recipient = $data['recipient'];
            $status = $data['status'];

            Log::info('Transfer data extracted', [
                'transfer_id' => $transferId,
                'amount' => $amount,
                'status' => $status,
                'recipient_id' => $recipient['id'] ?? 'N/A'
            ]);

            // Extract po_number from metadata or reference
            Log::info('Extracting PO number...');
            $poNumber = $this->extractPoNumber($data);
            Log::info('✅ PO number extracted', ['po_number' => $poNumber]);

            Log::info('Transfer paid successfully', [
                'transfer_id' => $transferId,
                'amount' => $amount,
                'recipient' => $recipient,
                'status' => $status,
                'po_number' => $poNumber
            ]);

            // Call payment status update API
            Log::info('🔄 Calling payment status update API...');
            $this->updatePaymentStatus($poNumber, 'success', $transferId);
            Log::info('✅ Payment status update API called');

            Log::info('=== TRANSFER PAID PROCESSING COMPLETED ===');
            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('❌ Error handling transfer.paid webhook', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data' => $data
            ]);
            Log::info('=== TRANSFER PAID PROCESSING FAILED ===');
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    private function handleTransferSuccess($data)
    {
        Log::info('=== TRANSFER SUCCESS PROCESSING STARTED ===');

        try {
            Log::info('Extracting transfer data...');
            $transferId = $data['id'];
            $amount = $data['amount'];
            $recipient = $data['recipient'];
            $status = $data['status'];
            $reference = $data['reference'];

            Log::info('Transfer data extracted', [
                'transfer_id' => $transferId,
                'amount' => $amount,
                'status' => $status,
                'reference' => $reference,
                'recipient_id' => $recipient['id'] ?? 'N/A'
            ]);

            // Extract po_number from metadata or reference
            Log::info('Extracting PO number...');
            $poNumber = $this->extractPoNumber($data);
            Log::info('✅ PO number extracted', ['po_number' => $poNumber]);

            Log::info('Transfer successful', [
                'transfer_id' => $transferId,
                'amount' => $amount,
                'recipient' => $recipient,
                'status' => $status,
                'po_number' => $poNumber,
                'reference' => $reference
            ]);

            // Call payment status update API
            Log::info('🔄 Calling payment status update API...');
            $this->updatePaymentStatus($poNumber, 'success', $transferId);
            Log::info('✅ Payment status update API called');

            Log::info('=== TRANSFER SUCCESS PROCESSING COMPLETED ===');
            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('❌ Error handling transfer.success webhook', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data' => $data
            ]);
            Log::info('=== TRANSFER SUCCESS PROCESSING FAILED ===');
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    private function handleTransferFailed($data)
    {
        Log::info('=== TRANSFER FAILED PROCESSING STARTED ===');

        try {
            Log::info('Extracting transfer data...');
            $transferId = $data['id'];
            $amount = $data['amount'];
            $recipient = $data['recipient'];
            $status = $data['status'];
            $failure_reason = $data['failure_reason'] ?? 'Unknown';

            Log::info('Transfer data extracted', [
                'transfer_id' => $transferId,
                'amount' => $amount,
                'status' => $status,
                'failure_reason' => $failure_reason,
                'recipient_id' => $recipient['id'] ?? 'N/A'
            ]);

            // Extract po_number from metadata or reference
            Log::info('Extracting PO number...');
            $poNumber = $this->extractPoNumber($data);
            Log::info('✅ PO number extracted', ['po_number' => $poNumber]);

            Log::error('Transfer failed', [
                'transfer_id' => $transferId,
                'amount' => $amount,
                'recipient' => $recipient,
                'status' => $status,
                'failure_reason' => $failure_reason,
                'po_number' => $poNumber
            ]);

            // Call payment status update API
            Log::info('🔄 Calling payment status update API...');
            $this->updatePaymentStatus($poNumber, 'failed', $transferId, $failure_reason);
            Log::info('✅ Payment status update API called');

            Log::info('=== TRANSFER FAILED PROCESSING COMPLETED ===');
            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('❌ Error handling transfer.failed webhook', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data' => $data
            ]);
            Log::info('=== TRANSFER FAILED PROCESSING FAILED ===');
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    private function extractPoNumber($data)
    {
        Log::info('=== PO NUMBER EXTRACTION STARTED ===');
        Log::info('Searching for PO number in transfer data...');

        // Try to extract po_number from different possible locations
        $poNumber = null;
        $searchLocations = [];

        // Check reference field first (primary location based on your format)
        if (isset($data['reference'])) {
            $reference = $data['reference'];
            Log::info('Found reference field', ['reference' => $reference]);

            // Extract PO number from format: 'PO_VENDOR_' . $po->po_number . '_' . time()
            if (preg_match('/^PO_VENDOR_(.+)_\d+$/', $reference, $matches)) {
                $poNumber = $matches[1];
                $searchLocations[] = 'reference (extracted from PO_VENDOR_ format)';
                Log::info('✅ PO number extracted from reference using PO_VENDOR_ format', [
                    'reference' => $reference,
                    'po_number' => $poNumber,
                    'regex_match' => $matches
                ]);
            } else {
                Log::info('Reference found but does not match PO_VENDOR_ format', [
                    'reference' => $reference,
                    'expected_format' => 'PO_VENDOR_{po_number}_{timestamp}'
                ]);
            }
        }

        // Fallback to other locations if reference extraction failed
        if (!$poNumber) {
            // Check metadata
            if (isset($data['metadata']['po_number'])) {
                $poNumber = $data['metadata']['po_number'];
                $searchLocations[] = 'metadata.po_number';
                Log::info('✅ PO number found in metadata.po_number', ['po_number' => $poNumber]);
            }
            // Check custom fields
            elseif (isset($data['custom_fields']['po_number'])) {
                $poNumber = $data['custom_fields']['po_number'];
                $searchLocations[] = 'custom_fields.po_number';
                Log::info('✅ PO number found in custom_fields.po_number', ['po_number' => $poNumber]);
            }
            // Check transfer metadata
            elseif (isset($data['transfer']['metadata']['po_number'])) {
                $poNumber = $data['transfer']['metadata']['po_number'];
                $searchLocations[] = 'transfer.metadata.po_number';
                Log::info('✅ PO number found in transfer.metadata.po_number', ['po_number' => $poNumber]);
            }
        }

        if (!$poNumber) {
            Log::warning('❌ No po_number found in transfer data', [
                'searched_locations' => $searchLocations,
                'available_keys' => array_keys($data),
                'reference_value' => $data['reference'] ?? 'not_set',
                'data_sample' => array_slice($data, 0, 3) // Log first 3 items for debugging
            ]);
            Log::info('=== PO NUMBER EXTRACTION FAILED ===');
            throw new \Exception('PO number not found in transfer data');
        }

        Log::info('=== PO NUMBER EXTRACTION COMPLETED ===', [
            'po_number' => $poNumber,
            'found_in' => $searchLocations[0] ?? 'unknown',
            'extraction_method' => 'reference_regex' // Indicates we used the regex method
        ]);
        return $poNumber;
    }

    private function updatePaymentStatus($poNumber, $status, $transferId, $failureReason = null)
    {
        Log::info('=== PAYMENT STATUS UPDATE STARTED ===');

        try {
            Log::info('Preparing API request...');

            // Map Paystack status to API payment_status
            $paymentStatus = $status === 'success' ? 'fully_paid' : 'unpaid';
            Log::info('Status mapping', [
                'paystack_status' => $status,
                'mapped_status' => $paymentStatus
            ]);

            $payload = [
                'po_number' => $poNumber,
                'payment_status' => $paymentStatus
            ];

            Log::info('API request payload prepared', [
                'url' => 'https://nodopayapi-419811b691e7.herokuapp.com/api/payment-status/update',
                'payload' => $payload
            ]);

            Log::info('🔄 Making HTTP request to external API...');
            $startTime = microtime(true);

            $response = Http::timeout(30)->post('https://nodopayapi-419811b691e7.herokuapp.com/api/payment-status/update', $payload);

            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds

            Log::info('HTTP request completed', [
                'response_time_ms' => $responseTime,
                'status_code' => $response->status(),
                'response_size' => strlen($response->body()),
                'response_headers' => $response->headers()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('✅ Payment status updated successfully', [
                    'po_number' => $poNumber,
                    'payment_status' => $paymentStatus,
                    'response' => $responseData,
                    'response_time_ms' => $responseTime,
                    'api_url_called' => 'https://nodopayapi-419811b691e7.herokuapp.com/api/payment-status/update'
                ]);
            } else {
                $errorData = $response->json();
                Log::error('❌ Failed to update payment status', [
                    'po_number' => $poNumber,
                    'payment_status' => $paymentStatus,
                    'response' => $errorData,
                    'status_code' => $response->status(),
                    'response_time_ms' => $responseTime,
                    'api_url_called' => 'https://nodopayapi-419811b691e7.herokuapp.com/api/payment-status/update',
                    'raw_response' => $response->body()
                ]);
            }

            Log::info('=== PAYMENT STATUS UPDATE COMPLETED ===');

        } catch (\Exception $e) {
            Log::error('❌ Error calling payment status update API', [
                'po_number' => $poNumber,
                'payment_status' => $paymentStatus ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'api_url_called' => 'https://nodopayapi-419811b691e7.herokuapp.com/api/payment-status/update'
            ]);
            Log::info('=== PAYMENT STATUS UPDATE FAILED ===');
        }
    }

    private function verifyPaystackSignature(Request $request)
    {
        Log::info('=== SIGNATURE VERIFICATION STARTED ===');

        $signature = $request->header('X-Paystack-Signature');
        $payload = $request->getContent();
        $secret = config('services.paystack.secret_key');

        Log::info('Signature verification details', [
            'signature_header' => $signature ? 'present' : 'missing',
            'payload_length' => strlen($payload),
            'secret_key_configured' => !empty($secret) ? 'yes' : 'no'
        ]);

        if (empty($secret)) {
            Log::error('❌ Paystack secret key not configured');
            Log::info('=== SIGNATURE VERIFICATION FAILED (NO SECRET) ===');
            return false;
        }

        $expectedSignature = hash_hmac('sha512', $payload, $secret);
        $signatureValid = hash_equals($expectedSignature, $signature);

        Log::info('Signature verification result', [
            'expected_signature' => substr($expectedSignature, 0, 10) . '...',
            'received_signature' => $signature ? (substr($signature, 0, 10) . '...') : 'missing',
            'signature_valid' => $signatureValid ? 'yes' : 'no'
        ]);

        Log::info('=== SIGNATURE VERIFICATION COMPLETED ===');
        return $signatureValid;
    }
}
