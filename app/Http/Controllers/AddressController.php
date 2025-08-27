<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Info(title="Foodstuff API", version="1.0")
 */

class AddressController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/addresses",
     *     summary="Get all addresses for the authenticated user",
     *     tags={"Addresses"},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=500, description="Failed to retrieve addresses")
     * )
     */
    public function index()
    {
        try {

            $addresses = Address::where('user_id', Auth::id())->get();
            return response()->json($addresses);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve addresses.'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/addresses",
     *     summary="Create a new address for the authenticated user",
     *     tags={"Addresses"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="set_default", type="boolean"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="residential_address", type="string"),
     *             @OA\Property(property="phone", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Address created successfully"),
     *     @OA\Response(response=500, description="Failed to create address")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:255',
            'set_default' => 'boolean',
            'name'=>'required',
            'state'=> 'required',
            'phone' => 'required'
        ]);

        try {
            DB::beginTransaction();

            $user_id = Auth::id();
            $user = User::whereId($user_id)->first();

            if ($request->set_default) {
                $user->addresses()->update(['set_default' => false]);
            }

            $address = $user->addresses()->create([
                'address' => $request->address,
                'residential_address'=>$request->address,
                'set_default' => $request->set_default ?? true,
                'name'=> $request->name,
                'state'=>  $request->state,
                'phone' => $request->phone
            ]);

            DB::commit();
            return response()->json($address, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create address.', 'msg'=>$e->getMessage()], 500);
        }
    }

   /**
     * @OA\Get(
     *     path="/api/addresses/{address}",
     *     summary="Get a specific address by ID",
     *     tags={"Addresses"},
     *     @OA\Parameter(
     *         name="address",
     *         in="path",
     *         description="Address ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=500, description="Failed to retrieve address")
     * )
     */
    public function show(Address $address)
    {
        try {
            return response()->json($address);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve address.'], 500);
        }
    }


    /**
     * @OA\Put(
     *     path="/api/addresses/{address}",
     *     summary="Update an existing address",
     *     tags={"Addresses"},
     *     @OA\Parameter(
     *         name="address",
     *         in="path",
     *         description="Address ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="set_default", type="boolean"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="residential_address", type="string"),
     *             @OA\Property(property="phone", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Address updated successfully"),
     *     @OA\Response(response=500, description="Failed to update address")
     * )
     */
    public function update(Request $request, Address $address)
    {
        $request->validate([
            'address' => 'sometimes|string|max:255',
            'set_default' => 'sometimes|boolean',
            'name' => 'sometimes|string|max:255',
            'residential_address' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // $this->authorize('update', $address);

            $user_id = Auth::id();
            $user = User::whereId($user_id)->first();

            if ($request->set_default) {
                $user->addresses()->update(['set_default' => false]);
            }

            $address->update($request->only([
                'address',
                'set_default',
                'name',
                'residential_address',
                'phone'
            ]));

            DB::commit();
            return response()->json($address);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update address.'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/addresses/{address}",
     *     summary="Delete an address",
     *     tags={"Addresses"},
     *     @OA\Parameter(
     *         name="address",
     *         in="path",
     *         description="Address ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Address deleted successfully"),
     *     @OA\Response(response=500, description="Failed to delete address")
     * )
     */
    public function destroy(Address $address)
    {
        try {
            // $this->authorize('delete', $address);
            $address->delete();
            return response()->json(['message' => 'Address deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete address.'], 500);
        }
    }
  /**
     * @OA\Get(
     *     path="/api/addresses/default",
     *     summary="Get the default address for the authenticated user",
     *     tags={"Addresses"},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="No default address found"),
     *     @OA\Response(response=500, description="Failed to retrieve default address")
     * )
     */
    public function getDefaultAddress()
    {
        try {
            $user_id = Auth::id();
            $address = Address::where('user_id',$user_id)->where('set_default', 1)->first();

            if (!$address) {
                return response()->json(['message' => 'No default address found.'], 408);
            }

            return response()->json($address);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve default address.'], 500);
        }
    }
}
