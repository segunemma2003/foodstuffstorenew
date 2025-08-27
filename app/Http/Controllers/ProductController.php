<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use App\Models\ProductQuantity;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{

    /**
     * @OA\Get(
     *     path="/products",
     *     summary="Get a list of all products",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="A list of products",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 properties={
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Product Name"),
     *                     @OA\Property(property="category_id", type="integer", example=1),
     *                     @OA\Property(property="size_id", type="integer", example=1),
     *                     @OA\Property(property="quantities", type="array", @OA\Items(type="integer")),
     *                     @OA\Property(property="reviews", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="images", type="array", @OA\Items(type="string"))
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to fetch products. Please try again later.")
     *         )
     *     )
     * )
     */
    // public function index(Request $request): JsonResponse
    //     {
    //         try {
    //             // Fetch query parameters for filtering and searching
    //             $category = $request->query('category', '');
    //             $searchTerm = $request->query('search', '');
    //             $locationId = $request->query('location_id', null);
    //             // Get the authenticated user

    //             // Initialize the query with eager loading of related models
    //             $query = Product::with(['category', 'size', 'quantities.location', 'reviews.user', 'reviews.images']);

    //             // Filter by category if a valid category is provided
    //             if ($category && strtolower($category) !== 'all') {
    //                 $query->whereHas('category', function ($q) use ($category) {
    //                     $q->where('name', $category);
    //                 });
    //             }

    //             // Search by product name if a search term is provided
    //             if ($searchTerm) {
    //                 $query->where('name', 'like', '%' . $searchTerm . '%');
    //             }
    //             $use_r = Auth::user();
    //             $user = User::whereId($use_r->id)->first();
    //             // Execute the query and get the results
    //             $products = $query->get()->map(function($product) use ($locationId, $user) {
    //                 // Check if location_id is provided
    //                 if ($locationId) {
    //                     // Get price for the selected location
    //                     $price = $product->getPriceByLocation($locationId);
    //                 } else {
    //                     // Get price from the first available location if no location is selected
    //                     $firstQuantity = $product->quantities->first();
    //                     $price = $firstQuantity ? $firstQuantity->price : null;
    //                 }

    //                 // Add price to product object
    //                 $product->price = $price;

    //                 if ($user) {
    //                     $isFavorite = $user->favoriteProducts()
    //                         ->where('product_id', $product->id)
    //                         ->whereNull('favorites.deleted_at') // Explicitly reference 'favorites.deleted_at'
    //                         ->exists();
    //                 } else {
    //                     $isFavorite = false;
    //                 }

    //                 // Add is_favorite to product object
    //                 $product->is_favorite = $isFavorite;
    //                 return $product;
    //             });

    //             return response()->json($products, 200);
    //         } catch (\Exception $e) {
    //             Log::error('Failed to fetch products: ' . $e->getMessage());

    //             return response()->json([
    //                 'error' => 'Failed to fetch products. Please try again later.',
    //             ], 500);
    //         }
    //     }

    public function index(Request $request): JsonResponse
    {
        try {
            // Fetch query parameters for filtering and searching
            $category = $request->query('category', '');
            $searchTerm = $request->query('search', '');
            $locationId = $request->query('location_id', 2); // Default location_id to 2

            $user = Auth::user(); // Get the authenticated user, if any

            // Build the query
            $query = Product::with(['category', 'size', 'reviews.user', 'reviews.images'])
                ->select('products.*')
                ->addSelect([
                    'price' => ProductQuantity::select('price')
                        ->whereColumn('product_id', 'products.id')
                        ->where('location_id', $locationId)
                        ->limit(1),
                ]);

            // Add the 'is_favorite' condition
            $query->addSelect([
                'is_favorite' => $user
                    ? DB::table('favorites')
                        ->selectRaw('EXISTS (SELECT 1 FROM favorites WHERE favorites.product_id = products.id AND favorites.user_id = ? AND favorites.deleted_at IS NULL)', [$user->id])
                        ->limit(1)
                    : DB::raw('false'), // Return false if not authenticated
            ]);

            // Filter by category if a valid category is provided
            if ($category && strtolower($category) !== 'all') {
                $query->whereHas('category', function ($q) use ($category) {
                    $q->where('name', $category);
                });
            }

            // Search by product name if a search term is provided
            if ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%');
            }

            // Paginate the products by 50 per page
            $products = $query->paginate(50);

            return response()->json($products, 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch products: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to fetch products. Please try again later.',
            ], 500);
        }
    }

//     public function index(Request $request): JsonResponse
// {
//     try {
//         // Fetch query parameters for filtering and searching
//         $category = $request->query('category', '');
//         $searchTerm = $request->query('search', '');

//         $locationId = $request->query('location_id', 2); // Default location_id to 2

//         $user = Auth::user(); // Get the authenticated user

//         // Build the query
//         $query = Product::with(['category', 'size', 'reviews.user', 'reviews.images'])
//             ->select('products.*')
//             ->addSelect([
//                 'price' => ProductQuantity::select('price')
//                     ->whereColumn('product_id', 'products.id')
//                     ->where('location_id', $locationId)
//                     ->limit(1),
//                'is_favorite' => DB::table('favorites')
//                     ->selectRaw('EXISTS (SELECT 1 FROM favorites WHERE favorites.product_id = products.id AND favorites.user_id = ? AND favorites.deleted_at IS NULL)', [$user->id])
//                     ->limit(1),
//             ]);

//         // Filter by category if a valid category is provided
//         if ($category && strtolower($category) !== 'all') {
//             $query->whereHas('category', function ($q) use ($category) {
//                 $q->where('name', $category);
//             });
//         }

//         // Search by product name if a search term is provided
//         if ($searchTerm) {
//             $query->where('name', 'like', '%' . $searchTerm . '%');
//         }

//         // Fetch the products
//         $products = $query->get();

//         return response()->json($products, 200);
//     } catch (\Exception $e) {
//         Log::error('Failed to fetch products: ' . $e->getMessage());

//         return response()->json([
//             'error' => 'Failed to fetch products. Please try again later.',
//         ], 500);
//     }
// }


// public function index(Request $request): JsonResponse
// {
//     try {
//         // Fetch query parameters for filtering and searching
//         $category = $request->query('category', '');
//         $searchTerm = $request->query('search', '');
//         $locationId = $request->query('location_id', 2); // Default location_id to 2

//         $user = Auth::user(); // Get the authenticated user

//         // Number of items per page
//         $perPage = $request->query('per_page', 20); // Default to 10 items per page

//         // Build the query
//         $query = Product::with(['category', 'size', 'reviews.user', 'reviews.images'])
//             ->select('products.*')
//             ->addSelect([
//                 'price' => ProductQuantity::select('price')
//                     ->whereColumn('product_id', 'products.id')
//                     ->where('location_id', $locationId)
//                     ->limit(1),
//                'is_favorite' => DB::table('favorites')
//                     ->selectRaw('EXISTS (SELECT 1 FROM favorites WHERE favorites.product_id = products.id AND favorites.user_id = ? AND favorites.deleted_at IS NULL)', [$user->id])
//                     ->limit(1),
//             ]);

//         // Filter by category if a valid category is provided
//         if ($category && strtolower($category) !== 'all') {
//             $query->whereHas('category', function ($q) use ($category) {
//                 $q->where('name', $category);
//             });
//         }

//         // Search by product name if a search term is provided
//         if ($searchTerm) {
//             $query->where('name', 'like', '%' . $searchTerm . '%');
//         }

//         // Fetch the paginated products
//         $products = $query->paginate($perPage);

//         return response()->json($products, 200);
//     } catch (\Exception $e) {
//         Log::error('Failed to fetch products: ' . $e->getMessage());

//         return response()->json([
//             'error' => 'Failed to fetch products. Please try again later.',
//         ], 500);
//     }
// }



public function autogonIndexId(Request $request, $id): JsonResponse
{
    try {
        // Fetch query parameters for filtering and searching
        $category = $request->query('category', '');
        $searchTerm = $request->query('search', '');
        $locationId = $request->query('location_id', 2); // Default location_id to 2

        // Build the query with necessary relations
        $query = Product::with([
            'category',
            'size',
            'reviews.user',
            'reviews.images',
        ])
        ->select('products.*')
        ->addSelect([
            // Subquery for price based on location
            'price' => ProductQuantity::select('price')
                ->whereColumn('product_id', 'products.id')
                ->where('location_id', $locationId)
                ->limit(1),
        ])
        ->where('id', $id);

        // Filter by category if a valid category is provided
        if ($category && strtolower($category) !== 'all') {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }

        // Search by product name if a search term is provided
        if ($searchTerm) {
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        // Fetch the first matching product
        $product = $query->first();

        if (!$product) {
            return response()->json([
                'error' => 'Product not found.',
            ], 404);
        }

        return response()->json($product, 200);
    } catch (\Exception $e) {
        Log::error('Failed to fetch product details: ' . $e->getMessage());

        return response()->json([
            'error' => 'Failed to fetch product details. Please try again later.',
        ], 500);
    }
}

public function autogonIndex(Request $request): JsonResponse
{
    try {
        // Fetch query parameters for filtering and searching
        $category = $request->query('category', '');
        $searchTerm = $request->query('search', '');
        $locationId = $request->query('location_id', 2); // Default location_id to 2

        // Build the query to fetch required fields
        $query = Product::query()
            ->with('size') // Load size relationship with only id and name
            ->select([
                'products.id',
                'products.name',
                'products.size_id',
                // Subquery for price based on location
                'price' => ProductQuantity::select('price')
                    ->whereColumn('product_id', 'products.id')
                    ->where('location_id', $locationId)
                    ->limit(1),
            ]);

        // Filter by category if a valid category is provided
        if ($category && strtolower($category) !== 'all') {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }

        // Search by product name if a search term is provided
        if ($searchTerm) {
            $query->where('products.name', 'like', '%' . $searchTerm . '%');
        }

        // $products = $query->get();
        // Fetch all matching products
        $products = $query->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'uom' => $product->size->name ?? null, // Map size.name to uom
                'price' => $product->price,
            ];
        });

        return response()->json($products, 200);
    } catch (\Exception $e) {
        Log::error('Failed to fetch products: ' . $e->getMessage());

        return response()->json([
            'error' => 'Failed to fetch products. Please try again later.',
        ], 500);
    }
}




    /**
     * @OA\Get(
     *     path="/products/{productId}",
     *     summary="Get details of a specific product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         required=true,
     *         description="ID of the product to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Product Name"),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="size_id", type="integer", example=1),
     *                 @OA\Property(property="quantities", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="reviews", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="images", type="array", @OA\Items(type="string"))
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Product not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to retrieve product details")
     *         )
     *     )
     * )
     */
    public function getProductDetails($productId)
    {
        try {
            // Load the product with its reviews, users, and images
            $product = Product::with(['reviews.user', 'reviews.images'])->find($productId);

            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            return response()->json($product, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve product details'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/products/category/{categoryId}",
     *     summary="Get products by category",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="categoryId",
     *         in="path",
     *         required=true,
     *         description="ID of the category to filter products by",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of products in the specified category",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 properties={
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Product Name"),
     *                     @OA\Property(property="category_id", type="integer", example=1),
     *                     @OA\Property(property="size_id", type="integer", example=1),
     *                     @OA\Property(property="quantities", type="array", @OA\Items(type="integer")),
     *                     @OA\Property(property="reviews", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="images", type="array", @OA\Items(type="string"))
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No products found for the specified category",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="No products found for this category")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to retrieve products")
     *         )
     *     )
     * )
     */
    public function getProductsByCategory($categoryId)
    {
        try {
            // Load products by category with their reviews, users, and images
            $products = Product::with(['reviews.user', 'reviews.images'])
                                ->where('category_id', $categoryId)
                                ->get();

            if ($products->isEmpty()) {
                return response()->json(['message' => 'No products found for this category'], 404);
            }

            return response()->json($products, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve products'], 500);
        }
    }

}
