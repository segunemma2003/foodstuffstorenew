<?php

namespace App\Http\Controllers;

use App\Models\BusinessCustomer;
use App\Models\BusinessOrder;
use App\Models\BusinessProduct;
use App\Models\DebtPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function orderStatistics(Request $request)
    {
        $user = $request->user();
        $query = BusinessOrder::query();

        if ($user->type === 'business') {
            $query->where('business_id', $user->business->id);
        } elseif ($user->type === 'customer') {
            $query->where('customer_member_id', $user->customerMember->id);
        }

        // Last 30 days statistics
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        $dailyStats = $query->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $statusStats = $query->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return response()->json([
            'daily_stats' => $dailyStats,
            'status_stats' => $statusStats
        ]);
    }

    public function paymentStatistics(Request $request)
    {
        $user = $request->user();
        $query = DebtPayment::query();

        if ($user->type === 'customer') {
            $query->where('customer_member_id', $user->customerMember->id);
        }

        // Last 30 days statistics
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        $dailyStats = $query->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $statusStats = $query->selectRaw('status, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('status')
            ->get();

        return response()->json([
            'daily_stats' => $dailyStats,
            'status_stats' => $statusStats
        ]);
    }

    public function customerStatistics(Request $request)
    {
        $query = BusinessCustomer::query();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $stats = [
            'total' => $query->count(),
            'active' => BusinessCustomer::where('status', 'approved')->count(),
            'pending' => BusinessCustomer::where('status', 'pending')->count(),
            'rejected' => BusinessCustomer::where('status', 'rejected')->count(),
        ];

        return response()->json([
            'data' => $stats
        ]);
    }

    public function productStatistics(Request $request)
    {
        $user = $request->user();
        $query = BusinessProduct::query();

        if ($user->type === 'business') {
            $query->where('business_id', $user->business->id);
        }

        $stats = [
            'total' => $query->count(),
            'active' => $query->where('is_active', true)->count(),
            'inactive' => $query->where('is_active', false)->count(),
        ];

        return response()->json([
            'data' => $stats
        ]);
    }
}
