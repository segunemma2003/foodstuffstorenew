<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Schema(
 *     schema="Favorite",
 *     type="object",
 *     title="Favorite",
 *     properties={
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="user_id", type="integer", example=1),
 *         @OA\Property(property="product_id", type="integer", example=1),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2023-09-05T14:48:00.000Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2023-09-05T14:48:00.000Z")
 *     }
 * )
 */


class FavoriteController extends Controller
{
    /**
     * @OA\Post(
     *     path="/favorites",
     *     tags={"Favorites"},
     *     summary="Add product to favorites",
     *     description="Adds a product to the user's list of favorite products.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="product_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added to favorites",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product added to favorites"),
     *             @OA\Property(property="favorite", type="object", ref="#/components/schemas/Favorite")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to add product to favorites",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to add product to favorites")
     *         )
     *     )
     * )
     */
    public function addToFavorites(Request $request): JsonResponse
    {
        try {
            // Check if the favorite already exists, including soft deleted records
            $favorite = Favorite::withTrashed()->where('user_id', Auth::id())
                                ->where('product_id', $request->product_id)
                                ->first();

            if ($favorite) {
                if ($favorite->trashed()) {
                    // Restore the soft deleted favorite
                    $favorite->restore();
                } else {
                    return response()->json(['status'=>'failed','message' => 'Product is already in favorites'], 409);
                }
            } else {
                // Create a new favorite
                $favorite = Favorite::create([
                    'user_id' => Auth::id(),
                    'product_id' => $request->product_id,
                ]);
            }

            return response()->json(['status'=>'success', 'message' => 'Product added to favorites', 'favorite' => $favorite], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status'=>'failed','error' => 'Failed to add product to favorites'], 500);
        }
    }



    /**
     * @OA\Get(
     *     path="/favorites",
     *     tags={"Favorites"},
     *     summary="Get favorite products",
     *     description="Retrieves a list of the user's favorite products.",
     *     @OA\Response(
     *         response=200,
     *         description="List of favorite products",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Favorite")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve favorite products",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to retrieve favorite products")
     *         )
     *     )
     * )
     */
    public function getFavorites(): JsonResponse
    {
        try {
            // Fetch the authenticated user and their active address
            $user = User::whereId(Auth::id())->first();
            $activeAddress = $user->addresses()->where('set_default', true)->first();

            // Get the user's active address location ID, or default to null if no active address
            $locationId = $activeAddress ? $activeAddress->location_id : null;

            // Fetch favorites with related product data
            $favorites = Favorite::with(['product.category', 'product.size', 'product.quantities'])
                ->where('user_id', Auth::id())
                ->get();

            // Attach price to each product based on location
            $favorites->each(function ($favorite) use ($locationId) {
                $product = $favorite->product;

                // Check if the product has any quantities
                if ($product && $product->quantities()->exists()) {
                    // Use getPriceByLocation method to fetch the price based on the location
                    $productQuantity = $product->quantities()->exists() ? $product->quantities()->first() : null;

                    // Set the price based on location or fallback to the first available price
                    if ($productQuantity) {
                        $favorite->product->price = $product->getPriceByLocation($locationId) ?: $productQuantity->price;
                    } else {
                        $favorite->product->price = 0.00; // Default price if no quantities are available
                    }
                }
            });

            Log::info($favorites);
            return response()->json($favorites, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching favorites: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve favorite products'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/favorites",
     *     tags={"Favorites"},
     *     summary="Remove product from favorites",
     *     description="Removes a product from the user's list of favorite products.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="product_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product removed from favorites",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product removed from favorites")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found in favorites",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product not found in favorites")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to remove product from favorites",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to remove product from favorites")
     *         )
     *     )
     * )
     */

     public function removeFromFavorites(Request $request): JsonResponse
{
    try {
        $favorite = Favorite::where('user_id', Auth::id())
                            ->where('product_id', $request->product_id)
                            ->first();

        if (!$favorite) {
            return response()->json(['message' => 'Product not found in favorites'], 404);
        }

        // No need to detach, just delete the favorite
        $favorite->delete();

        return response()->json(['message' => 'Product removed from favorites'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to remove product from favorites'], 500);
    }
}


}
