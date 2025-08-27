<?php

namespace App\Http\Controllers;

use App\Services\WemaBankService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\VirtualAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class VirtualAccountController extends Controller
{
    protected $wemaBankService;

    public function __construct(WemaBankService $wemaBankService)
    {
        $this->wemaBankService = $wemaBankService;
    }

    /**
     * Create a virtual account for a customer
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createVirtualAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100',
            'phone_number' => 'required|string|max:15',
            'bvn' => 'required|string|size:11',
            'reference' => 'nullable|string|max:100',
            'account_name' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Add user_id to the data if authenticated
        $data = $request->all();
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        $result = $this->wemaBankService->createVirtualAccount($data);

        if ($result['success']) {
            return response()->json($result, 201);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Get virtual account details
     *
     * @param string $accountNumber
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVirtualAccountDetails($accountNumber)
    {
        // Check if the account exists in our database
        $virtualAccount = VirtualAccount::where('account_number', $accountNumber)->first();

        if (!$virtualAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Virtual account not found'
            ], 404);
        }

        // Get latest details from Wema Bank
        $result = $this->wemaBankService->getVirtualAccountDetails($accountNumber);

        if ($result['success']) {
            return response()->json($result);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * List all virtual accounts for the authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listVirtualAccounts()
    {
        $userId = Auth::id();
        $user = User::whereId($userId)->first();
        $virtualAccounts = VirtualAccount::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $virtualAccounts
        ]);
    }

    /**
     * Disable a virtual account
     *
     * @param string $accountNumber
     * @return \Illuminate\Http\JsonResponse
     */
    public function disableVirtualAccount($accountNumber)
    {
        // Check if the account exists in our database
        $virtualAccount = VirtualAccount::where('account_number', $accountNumber)->first();

        if (!$virtualAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Virtual account not found'
            ], 404);
        }

        // Disable the account
        $result = $this->wemaBankService->disableVirtualAccount($accountNumber);

        if ($result['success']) {
            return response()->json($result);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Handle webhook notifications
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        // Log the webhook for debugging
        Log::info('Webhook received: ' . $request->getContent());

        $payload = $request->all();

        // Process the webhook
        $result = $this->wemaBankService->processWebhook($payload);

        if ($result['success']) {
            // Return a 200 OK response to acknowledge receipt
            return response()->json([
                'status' => 'success',
                'message' => 'Webhook received and processed successfully'
            ]);
        } else {
            // Log the error but still return a 200 to the bank
            Log::error('Webhook processing failed: ' . ($result['error'] ?? 'Unknown error'));

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook received but processing failed'
            ]);
        }
    }

    /**
     * Get transaction history for a virtual account
     *
     * @param string $accountNumber
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransactionHistory($accountNumber)
    {
        // Check if the account exists in our database
        $virtualAccount = VirtualAccount::where('account_number', $accountNumber)
            ->with(['transactions' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->first();

        if (!$virtualAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Virtual account not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'virtual_account' => $virtualAccount->only(['id', 'account_number', 'account_name', 'bank_name', 'status']),
                'transactions' => $virtualAccount->transactions
            ]
        ]);
    }
}
