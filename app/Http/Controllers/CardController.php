<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CardController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/cards",
     *     summary="Create a new card",
     *     tags={"Cards"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "card_number", "card_holder_name", "expiry_month", "expiry_year", "cvv"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="card_number", type="string", example="4111111111111111"),
     *             @OA\Property(property="card_holder_name", type="string", example="John Doe"),
     *             @OA\Property(property="expiry_month", type="string", example="12"),
     *             @OA\Property(property="expiry_year", type="string", example="2025"),
     *             @OA\Property(property="cvv", type="string", example="123"),
     *             @OA\Property(property="card_type", type="string", example="Visa"),
     *             @OA\Property(property="is_default", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Card created successfully"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'card_number' => 'required|string',
                'card_holder_name' => 'required|string',
                'expiry_month' => 'required|string',
                'expiry_year' => 'required|string',
                'cvv' => 'required|string',
                'card_type' => 'nullable|string',
                'is_default' => 'boolean',
            ]);

            $card = Card::create($validated);

            return response()->json($card, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create card: '.$e->getMessage());
            return response()->json(['error' => 'Failed to create card'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/cards/{id}",
     *     summary="Retrieve a card by ID",
     *     tags={"Cards"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Card retrieved successfully"),
     *     @OA\Response(response=404, description="Card not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function show($id)
    {
        try {
            $card = Card::find($id);

            if (!$card) {
                return response()->json(['error' => 'Card not found'], 404);
            }

            return response()->json($card, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve card: '.$e->getMessage());
            return response()->json(['error' => 'Failed to retrieve card'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/cards/{id}",
     *     summary="Update a card",
     *     tags={"Cards"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="card_number", type="string", example="4111111111111111"),
     *             @OA\Property(property="card_holder_name", type="string", example="John Doe"),
     *             @OA\Property(property="expiry_month", type="string", example="12"),
     *             @OA\Property(property="expiry_year", type="string", example="2025"),
     *             @OA\Property(property="cvv", type="string", example="123"),
     *             @OA\Property(property="card_type", type="string", example="Visa"),
     *             @OA\Property(property="is_default", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Card updated successfully"),
     *     @OA\Response(response=404, description="Card not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'card_number' => 'nullable|string',
                'card_holder_name' => 'nullable|string',
                'expiry_month' => 'nullable|string',
                'expiry_year' => 'nullable|string',
                'cvv' => 'nullable|string',
                'card_type' => 'nullable|string',
                'is_default' => 'nullable|boolean',
            ]);

            $card = Card::find($id);

            if (!$card) {
                return response()->json(['error' => 'Card not found'], 404);
            }

            $card->update($validated);

            return response()->json($card, 200);
        } catch (\Exception $e) {
            Log::error('Failed to update card: '.$e->getMessage());
            return response()->json(['error' => 'Failed to update card'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/cards/{id}",
     *     summary="Delete a card",
     *     tags={"Cards"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Card deleted successfully"),
     *     @OA\Response(response=404, description="Card not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function destroy($id)
    {
        try {
            $card = Card::find($id);

            if (!$card) {
                return response()->json(['error' => 'Card not found'], 404);
            }

            $card->delete();

            return response()->json(['message' => 'Card deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete card: '.$e->getMessage());
            return response()->json(['error' => 'Failed to delete card'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/cards/user/{userId}",
     *     summary="Get cards for a user",
     *     tags={"Cards"},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Cards retrieved successfully"),
     *     @OA\Response(response=404, description="No cards found for this user"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function getUserCards($userId)
    {
        try {
            $cards = Card::where('user_id', $userId)->get();

            if ($cards->isEmpty()) {
                return response()->json(['message' => 'No cards found for this user'], 404);
            }

            return response()->json($cards, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user cards: '.$e->getMessage());
            return response()->json(['error' => 'Failed to retrieve user cards'], 500);
        }
    }
}
