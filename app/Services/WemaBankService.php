<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\VirtualAccount;
use App\Models\Transaction;

class WemaBankService
{
    protected $baseUrl;
    protected $apiKey;
    protected $secretKey;
    protected $callbackUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.wema.base_url');
        $this->apiKey = config('services.wema.api_key');
        $this->secretKey = config('services.wema.secret_key');
        $this->callbackUrl = config('services.wema.callback_url');
    }

    /**
     * Create a virtual account for a customer
     *
     * @param array $customerData
     * @return array
     */
    public function createVirtualAccount(array $customerData): array
    {
        try {
            // Validate required fields
            $requiredFields = ['first_name', 'last_name', 'email', 'phone_number', 'bvn'];
            foreach ($requiredFields as $field) {
                if (empty($customerData[$field])) {
                    return [
                        'success' => false,
                        'message' => "Missing required field: {$field}"
                    ];
                }
            }

            // Prepare request data
            $requestData = [
                'first_name' => $customerData['first_name'],
                'last_name' => $customerData['last_name'],
                'email' => $customerData['email'],
                'phone_number' => $customerData['phone_number'],
                'bvn' => $customerData['bvn'],
                'customer_reference' => $customerData['reference'] ?? $this->generateReference(),
                'account_name' => $customerData['account_name'] ?? $customerData['first_name'] . ' ' . $customerData['last_name'],
                'callback_url' => $this->callbackUrl,
            ];

            // Make API request to Wema Bank
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/accounts/create', $requestData);

            // Process response
            $responseData = $response->json();

            if ($response->successful() && ($responseData['status'] === 'success' || $responseData['status'] === true)) {
                // Save virtual account details to database
                $virtualAccount = VirtualAccount::create([
                    'user_id' => $customerData['user_id'] ?? null,
                    'account_number' => $responseData['data']['account_number'],
                    'account_name' => $responseData['data']['account_name'],
                    'bank_name' => 'Wema Bank',
                    'reference' => $requestData['customer_reference'],
                    'status' => 'active',
                    'meta_data' => json_encode($responseData['data']),
                ]);

                return [
                    'success' => true,
                    'message' => 'Virtual account created successfully',
                    'data' => [
                        'account_number' => $responseData['data']['account_number'],
                        'account_name' => $responseData['data']['account_name'],
                        'bank_name' => 'Wema Bank',
                        'reference' => $requestData['customer_reference'],
                    ]
                ];
            } else {
                // Log error for debugging
                Log::error('Wema Bank API Error: ' . json_encode($responseData));

                return [
                    'success' => false,
                    'message' => $responseData['message'] ?? 'Failed to create virtual account',
                    'error' => $responseData['error'] ?? $responseData['message'] ?? 'Unknown error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Wema Bank Service Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred while creating the virtual account',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get virtual account details
     *
     * @param string $accountNumber
     * @return array
     */
    public function getVirtualAccountDetails(string $accountNumber): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/accounts/' . $accountNumber);

            $responseData = $response->json();

            if ($response->successful() && ($responseData['status'] === 'success' || $responseData['status'] === true)) {
                return [
                    'success' => true,
                    'data' => $responseData['data']
                ];
            } else {
                Log::error('Wema Bank API Error: ' . json_encode($responseData));

                return [
                    'success' => false,
                    'message' => $responseData['message'] ?? 'Failed to retrieve virtual account details',
                    'error' => $responseData['error'] ?? $responseData['message'] ?? 'Unknown error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Wema Bank Service Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred while retrieving virtual account details',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Disable a virtual account
     *
     * @param string $accountNumber
     * @return array
     */
    public function disableVirtualAccount(string $accountNumber): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->put($this->baseUrl . '/accounts/' . $accountNumber . '/disable');

            $responseData = $response->json();

            if ($response->successful() && ($responseData['status'] === 'success' || $responseData['status'] === true)) {
                // Update virtual account status in database
                VirtualAccount::where('account_number', $accountNumber)->update(['status' => 'disabled']);

                return [
                    'success' => true,
                    'message' => 'Virtual account disabled successfully'
                ];
            } else {
                Log::error('Wema Bank API Error: ' . json_encode($responseData));

                return [
                    'success' => false,
                    'message' => $responseData['message'] ?? 'Failed to disable virtual account',
                    'error' => $responseData['error'] ?? $responseData['message'] ?? 'Unknown error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Wema Bank Service Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred while disabling the virtual account',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process webhook payload
     *
     * @param array $payload
     * @return array
     */
    public function processWebhook(array $payload): array
    {
        try {
            // Validate webhook signature
            if (!$this->validateWebhookSignature($payload)) {
                Log::warning('Invalid webhook signature: ' . json_encode($payload));
                return [
                    'success' => false,
                    'message' => 'Invalid webhook signature'
                ];
            }

            // Extract transaction details
            $transactionData = $payload['data'] ?? [];
            $accountNumber = $transactionData['account_number'] ?? null;
            $amount = $transactionData['amount'] ?? 0;
            $reference = $transactionData['reference'] ?? null;
            $transactionType = $transactionData['transaction_type'] ?? 'credit';
            $narration = $transactionData['narration'] ?? '';
            $senderName = $transactionData['sender_name'] ?? '';
            $senderBank = $transactionData['sender_bank'] ?? '';

            // Find the virtual account
            $virtualAccount = VirtualAccount::where('account_number', $accountNumber)->first();

            if (!$virtualAccount) {
                Log::warning('Virtual account not found: ' . $accountNumber);
                return [
                    'success' => false,
                    'message' => 'Virtual account not found'
                ];
            }

            // Record the transaction
            $transaction = Transaction::create([
                'user_id' => $virtualAccount->user_id,
                'virtual_account_id' => $virtualAccount->id,
                'amount' => $amount,
                'reference' => $reference,
                'transaction_type' => $transactionType,
                'narration' => $narration,
                'sender_name' => $senderName,
                'sender_bank' => $senderBank,
                'status' => 'completed',
                'response_data' => json_encode($payload),
            ]);

            // Trigger any additional business logic here
            // e.g., update user balance, send notification, etc.

            return [
                'success' => true,
                'message' => 'Webhook processed successfully',
                'transaction_id' => $transaction->id
            ];
        } catch (\Exception $e) {
            Log::error('Webhook Processing Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred while processing the webhook',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate a unique reference
     *
     * @return string
     */
    protected function generateReference(): string
    {
        return 'VA_' . time() . '_' . uniqid();
    }

    /**
     * Validate webhook signature
     *
     * @param array $payload
     * @return bool
     */
    protected function validateWebhookSignature(array $payload): bool
    {
        // Get the signature from the request headers
        $requestSignature = request()->header('X-Wema-Signature');

        if (empty($requestSignature)) {
            return false;
        }

        // Get the raw payload
        $rawPayload = request()->getContent();

        // Generate a signature using the secret key
        $expectedSignature = hash_hmac('sha256', $rawPayload, $this->secretKey);

        // Compare signatures
        return hash_equals($expectedSignature, $requestSignature);
    }
}
