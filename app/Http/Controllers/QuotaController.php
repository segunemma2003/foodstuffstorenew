<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuotaController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user()->customerMember;

        if (!$customer) {
            return response()->json(['message' => 'Customer membership not found'], 404);
        }

        return response()->json([
            'data' => [
                'monthly_quota' => $customer->monthly_quota_amount,
                'available_quota' => $customer->available_quota
            ]
        ]);
    }

    public function history(Request $request)
    {
        $customer = $request->user()->customerMember;

        if (!$customer) {
            return response()->json(['message' => 'Customer membership not found'], 404);
        }

        $history = $customer->quotaHistory()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $history
        ]);
    }
}
