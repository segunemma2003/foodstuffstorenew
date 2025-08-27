<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ReviewController extends Controller
{
    /**
 * @OA\Post(
 *     path="/{type}/{id}/reviews",
 *     summary="Create a review for a product or order",
 *     tags={"Reviews"},
 *     @OA\Parameter(
 *         name="type",
 *         in="path",
 *         required=true,
 *         description="Type of the reviewable entity (product or order)",
 *         @OA\Schema(type="string", enum={"product", "order"})
 *     ),
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the product or order",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             properties={
 *                 @OA\Property(property="review_text", type="string", example="Great product!"),
 *                 @OA\Property(property="rating", type="integer", example=5),
 *                 @OA\Property(property="images", type="array", @OA\Items(type="string", format="binary"))
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Review created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             properties={
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="user_id", type="integer", example=1),
 *                 @OA\Property(property="review_text", type="string", example="Great product!"),
 *                 @OA\Property(property="rating", type="integer", example=5),
 *                 @OA\Property(property="images", type="array", @OA\Items(type="string")),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z")
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to create review",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Failed to add review.")
 *         )
 *     )
 * )
 */

 public function store(Request $request, $type, $id)
 {
     $request->validate([
         'review_text' => 'nullable|string',
         'rating' => 'required|integer|min:1|max:5',
         'images' => 'nullable|array',
         'images.*' => 'image|max:2048',
     ]);

     try {
         // Determine the model class based on the type
         $modelClass = $type === 'product' ? \App\Models\Product::class : \App\Models\Order::class;

         // Find the related model instance (either Product or Order)
         $modelInstance = $modelClass::findOrFail($id);

         // Create the review and associate it with the model instance
         $review = $modelInstance->reviews()->create([
             'user_id' => Auth::id(),
             'review_text' => $request->review_text,
             'rating' => $request->rating,
         ]);

         // If there are images, associate them with the review
         if ($request->has('images')) {
             foreach ($request->images as $image) {
                 $review->images()->create(['image' => $image]);
             }
         }

         return response()->json($review->load('user', 'images'), 201);
     } catch (\Exception $e) {
         return response()->json(['error' => 'Failed to add review.'], 500);
     }
 }


    /**
     * @OA\Delete(
     *     path="/reviews/{reviewId}",
     *     summary="Delete a review",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="reviewId",
     *         in="path",
     *         required=true,
     *         description="ID of the review to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Review deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden action",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="You can only delete your own reviews.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to delete review",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to delete review.")
     *         )
     *     )
     * )
     */
    public function destroy(Review $review)
    {
        try {
            if (Auth::id() !== $review->user_id) {
                return response()->json(['error' => 'You can only delete your own reviews.'], 403);
            }

            $review->delete();
            return response()->json(['message' => 'Review deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete review.'], 500);
        }
    }
}
