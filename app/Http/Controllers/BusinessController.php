<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessApplicationRequest;
use App\Models\Business;
use App\Models\CacDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessController extends Controller
{
    public function register(BusinessApplicationRequest $request)
    {
        $user = $request->user();

        if ($user->business) {
            return response()->json([
                'message' => 'You already have a business registration'
            ], 400);
        }

        $business = Business::create([
            'user_id' => $user->id,
            'name' => $request->company_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_email' => $request->contact_person_email,
            'contact_person_phone' => $request->contact_person_phone,
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Business registration submitted successfully',
            'data' => $business
        ], 201);
    }

    public function uploadCac(Request $request)
    {
        $request->validate([
            'cac_file' => 'required|file|mimes:pdf,jpeg,png|max:5120',
        ]);

        $business = $request->user()->business;

        if (!$business) {
            return response()->json(['message' => 'Business not found'], 404);
        }

        // Delete old CAC file if exists
        if ($business->cacDocument) {
            Storage::disk('s3')->delete($business->cacDocument->path);
            $business->cacDocument->delete();
        }

        // Store in S3
        $path = $request->file('cac_file')->store(
            'business/cac/' . $business->id,
            's3'
        );

        // Create CAC document record
        $cacDocument = CacDocument::create([
            'business_id' => $business->id,
            'original_name' => $request->file('cac_file')->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $request->file('cac_file')->getClientMimeType(),
            'size' => $request->file('cac_file')->getSize(),
        ]);

        return response()->json([
            'message' => 'CAC document uploaded successfully',
            'data' => [
                'document_id' => $cacDocument->id,
                'url' => Storage::disk('s3')->url($path)
            ]
        ]);
    }

    public function pendingApplications(Request $request)
    {
        $applications = Business::where('status', 'pending')
            ->paginate(10);

        return response()->json([
            'data' => $applications
        ]);
    }

    public function approve(Request $request, $id)
    {
        $business = Business::findOrFail($id);

        if ($business->status !== 'pending') {
            return response()->json([
                'message' => 'Application is not pending approval'
            ], 400);
        }

        $business->update([
            'status' => 'approved'
        ]);

        return response()->json([
            'message' => 'Business application approved',
            'data' => $business
        ]);
    }

    public function reject(Request $request, $id)
    {
        $business = Business::findOrFail($id);

        if ($business->status !== 'pending') {
            return response()->json([
                'message' => 'Application is not pending approval'
            ], 400);
        }

        $business->update([
            'status' => 'rejected'
        ]);

        return response()->json([
            'message' => 'Business application rejected',
            'data' => $business
        ]);
    }
}
