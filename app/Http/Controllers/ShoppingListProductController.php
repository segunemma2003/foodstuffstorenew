<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShoppingList;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;


/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     description="Product model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Product ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the product",
 *         example="Milk"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         description="Price of the product",
 *         example=2.99
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Product description",
 *         example="A fresh bottle of milk"
 *     ),
 *     @OA\Property(
 *         property="quantity",
 *         type="integer",
 *         description="Quantity available",
 *         example=10
 *     )
 * )
 */

class ShoppingListProductController extends Controller
{
    /**
     * @OA\Post(
     *     path="/shopping-lists/products",
     *     summary="Add a product to a shopping list",
     *     tags={"Shopping List"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"shopping_list_id", "product_id", "quantity"},
     *             @OA\Property(property="shopping_list_id", type="integer", example=1),
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added to shopping list",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product added to shopping list")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Shopping List or Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Shopping List not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to add product to shopping list",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to add product to shopping list")
     *         )
     *     )
     * )
     */
    public function addProductToList(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'shopping_list_id' => 'required|exists:shopping_lists,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $shoppingList = ShoppingList::find($validated['shopping_list_id']);
            if (!$shoppingList) {
                return response()->json(['error' => 'Shopping List not found'], 404);
            }

            $product = Product::find($validated['product_id']);
            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            $shoppingList->products()->syncWithoutDetaching([
                $product->id => ['quantity' => $validated['quantity']]
            ]);

            $shoppingList =  ShoppingList::with('products')->find($request->shopping_list_id);
            return response()->json(['message' => 'Product added to shopping list', 'data'=>$shoppingList], 200);
        } catch (\Exception $e) {
            Log::error('Failed to add product to shopping list: '.$e->getMessage());
            return response()->json(['error' => 'Failed to add product to shopping list'.$e->getMessage()], 500);
        }
    }

     /**
     * @OA\Put(
     *     path="/shopping-lists/{shoppingListId}/products/{productId}",
     *     summary="Update product quantity in a shopping list",
     *     tags={"Shopping List"},
     *     @OA\Parameter(
     *         name="shoppingListId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product quantity updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product quantity updated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Shopping List or Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Shopping List not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to update product quantity",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to update product quantity")
     *         )
     *     )
     * )
     */

    public function updateProductQuantity(Request $request, $shoppingListId, $productId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $shoppingList = ShoppingList::find($shoppingListId);
            if (!$shoppingList) {
                return response()->json(['error' => 'Shopping List not found'], 404);
            }

            $product = Product::find($productId);
            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            $shoppingList->products()->updateExistingPivot($productId, ['quantity' => $validated['quantity']]);

            return response()->json(['message' => 'Product quantity updated'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to update product quantity: '.$e->getMessage());
            return response()->json(['error' => 'Failed to update product quantity'], 500);
        }
    }


     /**
     * @OA\Delete(
     *     path="/shopping-lists/{shoppingListId}/products/{productId}",
     *     summary="Remove a product from a shopping list",
     *     tags={"Shopping List"},
     *     @OA\Parameter(
     *         name="shoppingListId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product removed from shopping list",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product removed from shopping list")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Shopping List or Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Shopping List not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to remove product from shopping list",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to remove product from shopping list")
     *         )
     *     )
     * )
     */
    public function removeProductFromList($shoppingListId, $productId): JsonResponse
    {
        try {
            $shoppingList = ShoppingList::find($shoppingListId);
            if (!$shoppingList) {
                return response()->json(['error' => 'Shopping List not found'], 404);
            }

            $product = Product::find($productId);
            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            $shoppingList->products()->detach($product);

            return response()->json(['message' => 'Product removed from shopping list'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to remove product from shopping list: '.$e->getMessage());
            return response()->json(['error' => 'Failed to remove product from shopping list'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/shopping-lists/{shoppingListId}/products",
     *     summary="Get products by shopping list ID",
     *     tags={"Shopping List"},
     *     @OA\Parameter(
     *         name="shoppingListId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of products in the shopping list",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Product")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Shopping List not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Shopping List not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve products by shopping list ID",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to retrieve products by shopping list ID")
     *         )
     *     )
     * )
     */

    public function getProductsByShoppingListId($shoppingListId): JsonResponse
    {
        try {
            $shoppingList = ShoppingList::with('products')->find($shoppingListId);

            if (!$shoppingList) {
                return response()->json(['error' => 'Shopping List not found'], 404);
            }

            $products = $shoppingList->products()->get();

            return response()->json($products, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve products by shopping list ID: '.$e->getMessage());
            return response()->json(['error' => 'Failed to retrieve products by shopping list ID'], 500);
        }
    }
}
