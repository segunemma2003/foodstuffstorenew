<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Get authenticated user details",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object")
     *         )
     *     )
     * )
     */
    public function show(Request $request)
    {

        // Get the authenticated user
        $user = Auth::user();
// var_dump($user);
        // Return user details as JSON
        return response()->json(['user' => $user]);
    }

    /**
     * @OA\Put(
     *     path="/api/user/phone",
     *     summary="Update user's phone number",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="phone", type="string", example="+1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Phone number updated successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to update phone number",
     *     )
     * )
     */
    public function updatePhoneNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|unique:users,phone,' . Auth::id(),
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $user_id = Auth::id();
            $user = User::where('user_id', $user_id)->first();
            $user->phone = $request->input('phone');
            $user->save();

            return response()->json(['message' => 'Phone number updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update phone number'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/user",
     *     summary="Delete user account",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Account deleted successfully",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to delete account",
     *     )
     * )
     */
    public function deleteAccount(Request $request)
    {
        try {
            $user_id = Auth::id();
            $user = User::where('id', $user_id)->first();
            $user->delete();

            return response()->json(['message' => 'Account deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete account'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/user/two-step-verification",
     *     summary="Activate two-step verification",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Two-step verification activated successfully",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to activate two-step verification",
     *     )
     * )
     */
    public function activateTwoStepVerification(Request $request)
    {
        $user_id = Auth::user();

        // Assuming you have a field to store whether 2FA is enabled
        try {
            $user = User::where('user_id', $user_id)->first();
            $user->two_factor_enabled = true;
            $user->save();

            return response()->json(['message' => 'Two-step verification activated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to activate two-step verification'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/user",
     *     summary="Update user details",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="fname", type="string", example="John"),
     *             @OA\Property(property="lname", type="string", example="Doe"),
     *             @OA\Property(property="current_location", type="string", example="New York"),
     *             @OA\Property(property="residential", type="string", example="123 Main St")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details updated successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to update user details",
     *     )
     * )
     */
    public function updateUserDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fname' => 'sometimes|string',
            'lname' => 'sometimes|string',
            'current_location' => 'sometimes|string',
            'residential' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $user_id = Auth::id();
            $user = User::where('user_id', $user_id)->first();
            $user->update($request->all());

            return response()->json(['message' => 'User details updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update user details'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/user/image",
     *     summary="Update user image",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="image", type="string", example="base64encodedimage")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image updated successfully",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to update image",
     *     )
     * )
     */
    public function updateImage(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        try {
            $user_id = Auth::id();
            $user = User::where('user_id', $user_id)->first();
            $user->image = $request->image;
            $user->save();

            return response()->json(['user' => $user, 'message' => 'Image updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update image', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/user/add-money",
     *     summary="Add money to wallet",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="amount", type="number", format="float", example="50.00"),
     *             @OA\Property(property="payment_method", type="string", example="credit_card")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Money added to wallet successfully",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to add money to wallet",
     *     )
     * )
     */
    public function addMoney(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
        ]);

        try {
            $user_id = Auth::id();
            $user = User::where('user_id', $user_id)->first();

            // Update user wallet balance
            $user->wallet_balance += $request->amount;
            $user->save();

            // Create a transaction record
            $transaction = Transaction::create([
                'unique_code' => strtoupper(Str::random(10)),
                'user_id' => $user_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'status' => 'success',
                'type' => 'credit',
            ]);

            return response()->json(['message' => 'Money added to wallet successfully', 'transaction' => $transaction], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add money to wallet', 'message' => $e->getMessage()], 500);
        }
    }



    public function importUsersFromCsv(Request $request)
    {
        $filePath = public_path('users.csv');
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $failedRecords = [];
        $processedRecords = 0;
        $batchSize = 100;  // Set the size for each batch
        $counter =  700;  // Get the current batch from request

        DB::beginTransaction();

        try {
            foreach ($csv as $index => $record) {
                if ($index < $counter) {
                    continue; // Skip processed rows
                }

                // Process each record as before
                $email = $record['UserEmail'];
                $phone = $record['Phone'];
                if (substr($phone, 0, 1) === '0') {
                    $phone = '+234' . substr($phone, 1);
                }
                if (User::where('email', $email)->orWhere('phone',$phone )->exists()) {
                    continue;
                }

                // Split username to get fname and lname
                $nameParts = explode(' ', $record['Username']);
                $fname = $nameParts[0];
                $lname = $nameParts[1] ?? '';

                // Format the phone number


                // Create new user
                $user = new User();
                $user->fname = $fname;
                $user->lname = $lname;
                $user->name = $fname . ' ' . $lname;
                $user->email = $email;
                $user->image = $record['Image'] ?? "https://w7.pngwing.com/pngs/613/636/png-transparent-computer-icons-user-profile-male-avatar-avatar-heroes-logo-black-thumbnail.png";
                $user->phone = $phone;
                $user->residential = 'Abuja';  // Default to 'Abuja'
                $user->wallet_balance = 0.00;
                $user->points = 0.00;
                $user->password = $record['Passphrase'];  // No need to hash
                $user->user_type = strtolower($record['AccountType']) === 'regular' ? 'regular' : 'company';

                $user->save();

                $processedRecords++;

                if ($processedRecords >= $batchSize) {
                    // Break after processing the batch size
                    break;
                }
            }

            DB::commit();

            // Check if the end of the file is reached
            if ($counter + $processedRecords >= count($csv)) {
                return response()->json(['message' => 'All records successfully added.'], 200);
            }

            // Return progress update and next batch start point
            return response()->json([
                'message' => "Processed $processedRecords records",
                'next_batch_start' => $counter + $processedRecords
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error during processing.', 'error'=>$e->getMessage()], 500);
        }
    }
}
// ["96","110","137"]
