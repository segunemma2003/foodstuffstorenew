<?php

namespace App\Http\Controllers;

use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ShoppingListController extends Controller
{
    /**
 * @OA\Get(
 *     path="/shopping-lists",
 *     summary="Get all shopping lists for the authenticated user",
 *     tags={"Shopping Lists"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of shopping lists",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 properties={
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="user_id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Groceries"),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z")
 *                 }
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to fetch shopping lists",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Failed to fetch shopping lists")
 *         )
 *     )
 * )
 */
public function index()
{
    try {
        $user = User::whereId(Auth::id())->first();
        $activeAddress = $user->addresses()->where('set_default', true)->first();

        // Get the user's active address location ID, or default to null if no active address
        $locationId = $activeAddress ? $activeAddress->location_id : 2;

        $shoppingLists = ShoppingList::with([ 'products' => function ($query) use ($locationId) {
            $query->with([
                'size',
                'category',
                'quantities' => function ($q) use ($locationId) {
                    $q->where('location_id', $locationId)->select('product_id', 'price');
                }
            ])
            ->select('products.id', 'products.name', 'products.category_id', 'products.size_id');
        }])
        ->where('user_id', $user->id)
        ->get();

        // Attach price to each product based on location
        $shoppingLists->each(function ($shoppingList) {
            $shoppingList->products->each(function ($product) {
                // Set price from the quantities relation, or default to 0.00
                $product->price = $product->quantities->first()->price ?? 0.00;
            });
        });

        return response()->json($shoppingLists, 200);
    } catch (\Exception $e) {
        Log::error('Failed to fetch shopping lists: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to fetch shopping lists'], 500);
    }
}


// public function index()
// {
//     try {
//         $user = User::whereId(Auth::id())->first();
//         $activeAddress = $user->addresses()->where('set_default', true)->first();

//         // Get the user's active address location ID, or default to null if no active address
//         $locationId = $activeAddress ? $activeAddress->location_id : null;

//         // Fetch shopping lists with optimized eager loading
//         $shoppingLists = ShoppingList::with([
//             'products' => function ($query) use ($locationId) {
//                 $query->with([
//                     'quantities' => function ($query) use ($locationId) {
//                         // Fetch only necessary fields and prioritize the location_id match
//                         $query->select(['id', 'product_id', 'location_id', 'price'])
//                             ->where(function ($query) use ($locationId) {
//                                 $query->where('location_id', $locationId)
//                                       ->orWhereNull('location_id'); // Fallback to any available location
//                             })
//                             ->orderByRaw('CASE WHEN location_id IS NULL THEN 1 ELSE 0 END'); // Prioritize non-null location
//                     },
//                     'size:id,name',
//                     'category:id,name',
//                 ]);
//             }
//         ])
//         ->where('user_id', Auth::id())
//         ->select(['id', 'user_id']) // Fetch only required fields
//         ->get();

//         // Attach price to each product based on location
//         $shoppingLists->each(function ($shoppingList) use ($locationId) {
//             $shoppingList->products->each(function ($product) use ($locationId) {
//                 // Get the first quantity matching location_id or fallback
//                 $product->price = optional($product->quantities->first())->price ?? 0.00;
//             });
//         });

//         return response()->json($shoppingLists, 200);
//     } catch (\Exception $e) {
//         Log::error('Failed to fetch shopping lists: ' . $e->getMessage());
//         return response()->json(['error' => 'Failed to fetch shopping lists'], 500);
//     }
// }

    /**
 * @OA\Post(
 *     path="/shopping-lists",
 *     summary="Create a new shopping list",
 *     tags={"Shopping Lists"},
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             properties={
 *                 @OA\Property(property="name", type="string", example="Groceries")
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Shopping list created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             properties={
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="user_id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Groceries"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z")
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to create shopping list",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Failed to create shopping list")
 *         )
 *     )
 * )
 */
    public function store(Request $request)
    {
        try {
            $shoppingList = ShoppingList::create([
                'user_id' => Auth::id(),
                'name' => $request->input('name'),
            ]);
            $response = [
                "status"=>"success",
                "data"=>$shoppingList
            ];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create shopping list: ' . $e->getMessage());
            return response()->json(['status'=>'error','error' => 'Failed to create shopping list'], 500);
        }
    }

    /**
 * @OA\Get(
 *     path="/shopping-lists/{id}",
 *     summary="Get a specific shopping list by ID",
 *     tags={"Shopping Lists"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the shopping list",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Shopping list retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             properties={
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="user_id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Groceries"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z")
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to fetch shopping list",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Failed to fetch shopping list")
 *         )
 *     )
 * )
 */


    public function show($id)
    {
        try {
            $shoppingList = ShoppingList::where('user_id', Auth::id())->findOrFail($id);
            return response()->json($shoppingList, 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch shopping list: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch shopping list'], 500);
        }
    }

    /**
 * @OA\Put(
 *     path="/shopping-lists/{id}",
 *     summary="Update a specific shopping list by ID",
 *     tags={"Shopping Lists"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the shopping list",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             properties={
 *                 @OA\Property(property="name", type="string", example="Groceries Updated")
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Shopping list updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             properties={
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="user_id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Groceries Updated"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z")
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to update shopping list",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Failed to update shopping list")
 *         )
 *     )
 * )
 */


    public function update(Request $request, $id)
    {
        try {
            $shoppingList = ShoppingList::where('user_id', Auth::id())->findOrFail($id);

            $shoppingList->update($request->only(['name']));

            return response()->json($shoppingList, 200);
        } catch (\Exception $e) {
            Log::error('Failed to update shopping list: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update shopping list'], 500);
        }
    }


    /**
 * @OA\Delete(
 *     path="/shopping-lists/{id}",
 *     summary="Delete a specific shopping list by ID",
 *     tags={"Shopping Lists"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the shopping list",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Shopping list deleted successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Shopping list deleted successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to delete shopping list",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Failed to delete shopping list")
 *         )
 *     )
 * )
 */
    public function destroy($id)
    {
        try {
            $shoppingList = ShoppingList::where('user_id', Auth::id())->findOrFail($id);
            $shoppingList->delete();

            return response()->json(['message' => 'Shopping list deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete shopping list: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete shopping list'], 500);
        }
    }
}
