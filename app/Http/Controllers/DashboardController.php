<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function customerDashboard(Request $request)
    {
        $customer = $request->user()->customerMember;

        if (!$customer) {
            return response()->json(['message' => 'Customer membership not found'], 404);
        }

        $stats = [
            'available_quota' => $customer->available_quota,
            'total_debt' => $customer->total_debt,
            'total_orders' => $customer->orders()->count(),
            'pending_orders' => $customer->orders()->where('status', 'pending')->count(),
            'completed_orders' => $customer->orders()->where('status', 'completed')->count(),
        ];

        $recentOrders = $customer->orders()
            ->with(['business', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_orders' => $recentOrders
        ]);
    }

    public function businessDashboard(Request $request)
    {
        $business = $request->user()->business;

        if (!$business) {
            return response()->json(['message' => 'Business not found'], 404);
        }

        $stats = [
            'total_orders' => $business->orders()->count(),
            'pending_orders' => $business->orders()->where('status', 'pending')->count(),
            'completed_orders' => $business->orders()->where('status', 'completed')->count(),
            'total_revenue' => $business->orders()->sum('total_amount'),
            'active_products' => $business->products()->where('is_active', true)->count(),
        ];

        $recentOrders = $business->orders()
            ->with(['customerMember', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_orders' => $recentOrders
        ]);
    }
}
