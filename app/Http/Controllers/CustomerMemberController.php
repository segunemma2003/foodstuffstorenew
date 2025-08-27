<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessMemberApplicationRequest;
use App\Models\BusinessCustomer;
use App\Models\CustomerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CustomerMemberController extends Controller
{
    public function apply(BusinessMemberApplicationRequest $request)
    {
        $user = $request->user();

        if ($user->customerMember) {
            return response()->json([
                'message' => 'You already have a membership application'
            ], 400);
        }

        $member = DB::transaction(function () use ($request, $user) {
            $member = BusinessCustomer::create([
                'user_id' => $user->id,
                'member_id' => 'CUST-' . strtoupper(uniqid()),
                'company_name' => $request->company_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'contact_person_name' => $request->contact_person_name,
                'contact_person_email' => $request->contact_person_email,
                'contact_person_phone' => $request->contact_person_phone,
                'monthly_quota_amount' => $request->monthly_quota_amount,
                'status' => 'pending'
            ]);

            return $member;
        });

        return response()->json([
            'message' => 'Membership application submitted successfully',
            'data' => $member
        ], 201);
    }

    public function pendingApplications(Request $request)
    {
        $applications = BusinessCustomer::where('status', 'pending')
            ->paginate(10);

        return response()->json([
            'data' => $applications
        ]);
    }

    public function approve(Request $request, $id)
    {
        $member = BusinessCustomer::findOrFail($id);

        if ($member->status !== 'pending') {
            return response()->json([
                'message' => 'Application is not pending approval'
            ], 400);
        }

        $member->update([
            'status' => 'approved',
            'available_quota' => $member->monthly_quota_amount
        ]);

        return response()->json([
            'message' => 'Membership application approved',
            'data' => $member
        ]);
    }

    public function reject(Request $request, $id)
    {
        $member = BusinessCustomer::findOrFail($id);

        if ($member->status !== 'pending') {
            return response()->json([
                'message' => 'Application is not pending approval'
            ], 400);
        }

        $member->update([
            'status' => 'rejected'
        ]);

        return response()->json([
            'message' => 'Membership application rejected',
            'data' => $member
        ]);
    }

    public function getQuota(Request $request)
    {
        $member = $request->user()->customerMember;

        if (!$member) {
            return response()->json([
                'message' => 'Customer membership not found'
            ], 404);
        }

        return response()->json([
            'data' => [
                'monthly_quota' => $member->monthly_quota_amount,
                'available_quota' => $member->available_quota,
                'history' => $member->quotaHistory()->orderBy('created_at', 'desc')->get()
            ]
        ]);
    }
}
