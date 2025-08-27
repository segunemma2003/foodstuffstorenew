<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\VirtualAccount;
use App\Models\Transaction;
use Exception;

class PaystackService
{
    protected $baseUrl;
    protected $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('paystack.baseUrl');
        $this->secretKey = config('paystack.secretKey');
    }

    /**
     * Create a dedicated virtual account for a customer
     *
     * @param array $data Customer data
     * @return array Response with success status and data/error message
     */
    public function createDedicatedVirtualAccount(array $data)
    {
        try {
            // First create or verify the customer on Paystack
            $customer = $this->createCustomer($data);

            if (!isset($customer['status']) || !$customer['status']) {
                return [
                    'status' => false,
                    'message' => $customer['message'] ?? 'Failed to create customer',
                    'code' => 422
                ];
            }

            $customerId = $customer['data']['customer_code'];

            // Create the dedicated virtual account
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/dedicated_account', [
                'customer' => $customerId,
                'phone'=>$data['phone'],
                'preferred_bank' => 'wema-bank', // Specify Wema Bank
            ]);

            $responseData = $response->json();

            if (!$response->successful()) {
                Log::error('Paystack API error: ' . json_encode($responseData));
                return [
                    'status' => false,
                    'message' => $responseData['message'] ?? 'Failed to create virtual account',
                    'code' => $response->status()
                ];
            }

            if (!isset($responseData['status']) || !$responseData['status']) {
                return [
                    'status' => false,
                    'message' => $responseData['message'] ?? 'Failed to create virtual account',
                    'code' => 422
                ];
            }
// var_dump($responseData['data']);
            // Store virtual account details in database
            $virtualAccount = VirtualAccount::create([
                'email' => $data['email'],
                'phone' => $data['phone'],
                'business_name' => $data['business_name'] ?? ($data['first_name'] . ' ' . $data['last_name']),
                'account_name' => $responseData['data']['account_name'] ?? null,
                'account_number' => $responseData['data']['account_number'] ?? null,
                'bank_name' => $responseData['data']['bank']['name'] ?? 'Wema Bank',
                'customer_code' => $customerId,
                'reference' => $responseData['data']['customer']['customer_code'] ?? null,
                'assignment_reference' => $responseData['data']['assignment']['assignment_reference'] ?? null,
                'status' => 'active',
                'meta' => json_encode($responseData['data']),
            ]);

            return [
                'status' => true,
                'data' => [
                    'id' => $virtualAccount->id,
                    'account_name' => $virtualAccount->account_name,
                    'account_number' => $virtualAccount->account_number,
                    'bank_name' => $virtualAccount->bank_name,
                    'reference' => $virtualAccount->reference,
                ],
                'code' => 201
            ];
        } catch (Exception $e) {
            Log::error('Error creating virtual account: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Create or verify a customer on Paystack
     *
     * @param array $data Customer data
     * @return array Response data
     */
    protected function createCustomer(array $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/customer', [
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return [
                    'status' => true,
                    'data' => $responseData['data']
                ];
            }

            Log::error('Failed to create customer', [
                'status_code' => $response->status(),
                'response' => $responseData
            ]);

            return [
                'status' => false,
                'message' => $responseData['message'] ?? 'Failed to create customer'
            ];
        } catch (Exception $e) {
            Log::error('Error creating customer: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }

    /**
     * List virtual accounts
     *
     * @param string|null $email Filter by email
     * @return array
     */
    public function listVirtualAccounts($email = null)
    {
        try {
            $query = VirtualAccount::query();

            if ($email) {
                $query->where('email', $email);
            }

            $accounts = $query->orderBy('created_at', 'desc')->get();

            return [
                'status' => true,
                'data' => $accounts,
                'code' => 200
            ];
        } catch (Exception $e) {
            Log::error('Error listing virtual accounts: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Get a specific virtual account
     *
     * @param int $accountId
     * @return array
     */
    public function getVirtualAccount($accountId)
    {
        try {
            $account = VirtualAccount::find($accountId);

            if (!$account) {
                return [
                    'status' => false,
                    'message' => 'Virtual account not found',
                    'code' => 404
                ];
            }

            return [
                'status' => true,
                'data' => $account,
                'code' => 200
            ];
        } catch (Exception $e) {
            Log::error('Error getting virtual account: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Handle webhook notification for virtual account payments
     *
     * @param array $payload Webhook payload
     * @return array
     */
    public function handleWebhook(array $payload)
    {
        try {
            // Verify this is a deposit event
            if ($payload['event'] !== 'charge.success' ||
                !isset($payload['data']['channel']) ||
                $payload['data']['channel'] !== 'dedicated_nuban') {
                return [
                    'status' => true, // Return success to acknowledge receipt
                    'message' => 'Event ignored - not a virtual account deposit',
                    'code' => 200
                ];
            }

            $data = $payload['data'];
            $accountNumber = $data['authorization']['receiver_bank_account_number'] ?? null;

            if (!$accountNumber) {
                Log::error('Account number not found in webhook payload');
                return [
                    'status' => false,
                    'message' => 'Account number not found in payload',
                    'code' => 400
                ];
            }

            // Find associated virtual account
            $virtualAccount = VirtualAccount::where('account_number', $accountNumber)->first();

            if (!$virtualAccount) {
                Log::error('Virtual account not found for account number: ' . $accountNumber);
                return [
                    'status' => false,
                    'message' => 'Virtual account not found',
                    'code' => 404
                ];
            }

            // Check if transaction already exists
            $existingTransaction = Transaction::where('reference', $data['reference'])->first();
            if ($existingTransaction) {
                return [
                    'status' => true,
                    'message' => 'Transaction already processed',
                    'code' => 200
                ];
            }

            // Create transaction record
            $transaction = Transaction::create([
                'virtual_account_id' => $virtualAccount->id,
                'reference' => $data['reference'],
                'amount' => $data['amount'] / 100, // Convert from kobo to naira
                'currency' => $data['currency'],
                'status' => $data['status'],
                'payment_channel' => $data['channel'],
                'payer_name' => $data['customer']['email'] ?? null,
                'meta' => json_encode($data),
                'processed_at' => now(),
            ]);

            // Dispatch event for real-time notification
            event(new \App\Events\VirtualAccountPaymentReceived($transaction));

            return [
                'status' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'reference' => $transaction->reference,
                ],
                'code' => 200
            ];
        } catch (Exception $e) {
            Log::error('Error processing webhook: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }
}
