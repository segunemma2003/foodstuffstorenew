<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CreateVirtualAccountRequest;
use App\Services\PaystackService;
use Exception;
use Illuminate\Http\JsonResponse;

class VirtualAccountsController extends Controller
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }

    /**
     * List all virtual accounts, optionally filtered by email
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $email = $request->query('email');
        $result = $this->paystackService->listVirtualAccounts($email);

        return response()->json([
            'status' => $result['status'],
            'message' => $result['status'] ? 'Virtual accounts retrieved successfully' : $result['message'],
            'data' => $result['status'] ? $result['data'] : null
        ], $result['code']);
    }

    /**
     * Create a new virtual account
     *
     * @param CreateVirtualAccountRequest $request
     * @return JsonResponse
     */
    public function store(CreateVirtualAccountRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->paystackService->createDedicatedVirtualAccount($data);

        return response()->json([
            'status' => $result['status'],
            'message' => $result['status'] ? 'Virtual account created successfully' : $result['message'],
            'data' => $result['status'] ? $result['data'] : null
        ], $result['code']);
    }

    /**
     * Get a specific virtual account
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $result = $this->paystackService->getVirtualAccount($id);

        return response()->json([
            'status' => $result['status'],
            'message' => $result['status'] ? 'Virtual account retrieved successfully' : $result['message'],
            'data' => $result['status'] ? $result['data'] : null
        ], $result['code']);
    }

    /**
     * Get transactions for a specific virtual account
     *
     * @param int $id
     * @return JsonResponse
     */
    public function transactions($id): JsonResponse
    {
        try {
            $account = \App\Models\VirtualAccount::find($id);

            if (!$account) {
                return response()->json([
                    'status' => false,
                    'message' => 'Virtual account not found',
                    'data' => null
                ], 404);
            }

            $transactions = $account->transactions()
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Transactions retrieved successfully',
                'data' => $transactions
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
