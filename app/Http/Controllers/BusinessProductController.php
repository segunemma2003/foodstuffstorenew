<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\BusinessProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessProductController extends Controller
{
    public function index(Request $request)
    {
        $business = $request->attributes->get('api_business');

        $products = BusinessProduct::where('business_id', $business->id)
            ->where('is_active', true)
            ->paginate(10);

        return response()->json([
            'data' => $products
        ]);
    }

    public function show(Request $request, $id)
    {
        $business = $request->attributes->get('api_business');

        $product = BusinessProduct::where('id', $id)
            ->where('business_id', $business->id)
            ->firstOrFail();

        return response()->json([
            'data' => $product
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        // var_dump($request->user());
        $business = $request->user()->business;

        if (!$business) {
            return response()->json(['message' => 'Business not found'], 404);
        }

        $data = $request->validated();
        $data['business_id'] = $business->id;

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store(
                'products/' . $business->id,
                's3'
            );
            $data['image_path'] = $path;
        }

        $product = BusinessProduct::create($data);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    public function update(StoreProductRequest $request, $id)
    {
        $business = $request->user()->business;
        $product = BusinessProduct::where('id', $id)
            ->where('business_id', $business->id)
            ->firstOrFail();

        $data = $request->validated();

        // Handle image update
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image_path) {
                Storage::disk('s3')->delete($product->image_path);
            }

            $path = $request->file('image')->store(
                'products/' . $business->id,
                's3'
            );
            $data['image_path'] = $path;
        }

        $product->update($data);

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    public function destroy($id)
    {
        $business = request()->user()->business;
        $product = BusinessProduct::where('id', $id)
            ->where('business_id', $business->id)
            ->firstOrFail();

        // Soft delete the product
        $product->update(['is_active' => false]);

        return response()->json([
            'message' => 'Product deactivated successfully'
        ]);
    }
}
