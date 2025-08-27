<?php

// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use PhpOffice\PhpSpreadsheet\IOFactory;
// use App\Models\Product;
// use App\Models\ProductQuantity;
// use Illuminate\Support\Facades\DB;
// use Maatwebsite\Excel\Facades\Excel;
// use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// class ImportProductQuantitiesFromXlsx extends Command
// {
//     protected $signature = 'import:product-quantities-xlsx';
//     protected $description = 'Import product quantities and update prices from XLSX file';

//     protected $sizeMappings = [
//         'carton' => 1, 'pack' => 4, 'CTN' => 1, 'PCS' => 6, 'PXS' => 6, 'Big size' => 8,
//         'Small size' => 9, 'cup' => 10, 'pck' => 4, 'Mudu' => 5, 'bottle' => 11,
//         'btl' => 11, 'bttl' => 11, 'roll' => 12, 'tin' => 13, '5LTRS' => 14, '10LTRS' => 16,
//         '25LTRS' => 17, 'bag' => 7, 'portion' => 18, 'DUSTBIN BSK' => 19,
//         'DUST BIN BASKET' => 19, 'MEDUIM BASKET' => 20, 'BSKT' => 21, 'TUBER' => 22,
//         'SET(5TUBERS)' => 23, 'KG' => 2, 'g' => 3, 'CUP' => 10, 'grams' => 3, 'CRATE' => 24
//     ];


//     private function getCellValue(Worksheet $sheet, $cellAddress)
//     {
//         $cell = $sheet->getCell($cellAddress);
//         return $cell->getCalculatedValue(); // Get evaluated value
//     }

//     public function handle()
//     {
//         $filePath = public_path('items_abujatoday.xlsx');
//         $failedProducts = [];

//         DB::beginTransaction();

//         try {
//             // $spreadsheet = Excel::toArray([], $filePath)[0]; // Load the first sheet
//             // $workbook = Excel::load($filePath);
//             // $sheet = $workbook->getActiveSheet();

//             $spreadsheet = IOFactory::load($filePath);
//             $sheet = $spreadsheet->getActiveSheet();

//             foreach ($sheet->getRowIterator() as $rowIndex => $row) {
//                 if ($rowIndex < 367) continue; // Skip headers (first two rows)

//                 $itemName = trim($sheet->getCell("A" . $rowIndex)->getValue());
//                 $uom = trim($sheet->getCell("B" . $rowIndex)->getValue());
//                 $unitCost = $sheet->getCell("E" . $rowIndex)->getCalculatedValue(); // Evaluated formula
//                 $category = $sheet->getCell("H" . $rowIndex)->getValue();
//             // foreach ($spreadsheet as $index => $row) {
//             //     if ($index < 2) continue; // Skip header rows

//                 // $itemName = $row[0] ?? '';
//                 // $uom = trim($row[1] ?? '');
//                 // $unitCost = trim($row[4] ?? 0);
//                 // $unitCost = $this->getCellValue($sheet, "E" . ($index + 1));
//                 $image = "https://res.cloudinary.com/iamdevmaniac/image/upload/";
//                 // $category = $row[7];
//                 echo $category . " ". $unitCost;

//                 if (empty($itemName)) {
//                     $failedProducts[] = ['item' => 'Unknown Item', 'reason' => 'Missing ITEM in row ' . ($rowIndex + 1)];
//                     continue;
//                 }

//                 // if (preg_match('/^[A-T]/i', $itemName) && $category == 1) {
//                 //     continue; // Skip this row
//                 // }

//                 $product = $this->findOrCreateProduct($itemName, $uom, $image, $category);
//                 if (!$product) {
//                     $failedProducts[] = ['item' => $itemName, 'reason' => 'Failed to create product'];
//                     continue;
//                 }

//                 $this->updateOrCreateProductQuantity($product, $unitCost, 2, $failedProducts, $itemName);
//             }

//             DB::commit();
//             $this->info('Product quantities and prices updated successfully.');

//             if ($failedProducts) {
//                 $this->warn('The following products could not be processed:');
//                 foreach ($failedProducts as $failedProduct) {
//                     $this->warn("Item: {$failedProduct['item']} - Reason: {$failedProduct['reason']}");
//                 }
//             }
//         } catch (\Exception $e) {
//             DB::rollBack();
//             $this->error('Error importing product quantities: ' . $e->getMessage());
//         }
//     }


//     private function extractUnitCost($value)
//     {
//         return is_numeric($value) ? floatval($value) : 0;
//     }

//     private function findOrCreateProduct($itemName, $uom, $image, $category)
//     {

//         $products = Product::all();
//         $matchedProduct = null;
//         $maxSimilarity = 0;

//         foreach ($products as $product) {
//             similar_text(strtolower($itemName), strtolower($product->name), $percent);
//             if ($percent >= 95 && $percent > $maxSimilarity) {
//                 $matchedProduct = $product;
//                 $maxSimilarity = $percent;
//             }
//         }

//         if (!$matchedProduct) {
//             $product = new Product();
//             $product->name = $itemName;
//             $product->image = $image;
//             $product->category_id = $category;
//             $product->about = "This is $itemName";
//             $product->size_id = $this->getSizeIdFromUom($uom);
//             $product->save();

//             return $product;
//         }

//         return $matchedProduct;
//     }

//     private function updateOrCreateProductQuantity($product, $unitCost, $locationId, &$failedProducts, $itemName)
//     {
//         try {
//             $productQuantity = ProductQuantity::firstOrNew([
//                 'product_id' => $product->id,
//                 'location_id' => $locationId
//             ]);

//             $productQuantity->quantity = $productQuantity->quantity ?? 1;
//             $productQuantity->discount = $productQuantity->discount ?? 0;
//             $productQuantity->price = $unitCost;
//             $productQuantity->save();

//             $this->info("Updated product ID {$product->id} - {$product->name} with price: $unitCost");
//         } catch (\Exception $e) {
//             $failedProducts[] = ['item' => $itemName, 'reason' => $e->getMessage()];
//         }
//     }

//     private function getSizeIdFromUom($uom)
//     {
//         $uom = strtolower($uom);
//         foreach ($this->sizeMappings as $key => $value) {
//             if (strpos($uom, strtolower($key)) !== false) {
//                 return $value;
//             }
//         }
//         return null;
//     }
// }

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductQuantity;
use App\Models\Location;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ImportProductQuantitiesFromXlsx extends Command
{
    protected $signature = 'import:product-quantities-xlsx';
    protected $description = 'Import product quantities and update prices from XLSX file';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'app:import-product-quantities-from-xlsx';

    // /**
    //  * The console command description.
    //  *
    //  * @var string
    //  */
    // protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    // //lagos


    protected $sizeMappings = [
        'carton' => 1,
        'pack' => 4,
        'CTN' => 1,
        'PCS' => 6,
        'PXS' => 6,
        'Big size' => 8,
        'Small size' => 9,
        'cup' => 10,
        'pck' => 4,
        'Mudu'=>5,
        'bottle' => 11,
        'btl' => 11,
        'bttl' => 11,
        'roll' => 12,
        'tin' => 13,
        '5LTRS'=>14,
        '10LTRS'=>16,
        '25LTRS'=>17,
        'bag' => 7,
        'portion'=>18,
        'DUSTBIN BSK'=>19,
        'DUST BIN BASKET'=>19,
        'MEDUIM BASKET'=>20,
        'BSKT'=>21,
        'TUBER'=>22,
        'SET(5TUBERS)'=>23,
        'KG'=>2,
        'g'=>3,
        'CUP'=>10,
        'grams'=>3,
        'CRATE'=>24
    ];


    public function handle()
    {
        // Path to the new XLSX file
        $filePath = public_path('productq.xlsx'); // Replace with the correct file path

        // Start a transaction
        DB::beginTransaction();

        $failedProducts = []; // To store failed products and reasons

        try {
            // Load the XLSX file
            $spreadsheet = Excel::toArray([], $filePath)[0]; // Get the first sheet

            // Loop through the rows
            foreach ($spreadsheet as $index => $row) {
                if ($index < 1) {
                    // Skip header row
                    continue;
                }

                // Get the data from the row
                $itemId = trim($row[0] ?? '');
                $itemName = trim($row[1] ?? '');  // Column 'ITEMS' is at index 0
                $uom = trim($row[2] ?? 1);   // Column 4 ('UNIT COST') is at index 3, default to 0 if empty
                $unitCost = trim($row[3] ?? 1000);
                // $image = "https://res.cloudinary.com/iamdevmaniac/image/upload/";
                // If 'ITEMS' or 'UNIT COST' doesn't exist, skip
                $this->info($unitCost);
                $this->info($itemId);


                // $productQuantity = ProductQuantity::where('id', $itemName)->first();
                // $productQuantity->update([
                //     'price' => $unitCost,
                // ]);

            //    $prod = ProductQuantity::create([
            //             "product_id"=>$itemId,
            //             "location_id"=>1,
            //             "quantity"=>1,
            //             "discount"=>0.0,
            //             "uom"=> $uom,
            //             'price' => $unitCost
            //     ]);
                ProductQuantity::create([
                                'product_id' => $itemId,
                                'location_id' => 2,
                                'quantity' => 1,
                                'discount' => 0,
                                'price' => $unitCost,
                            ]);
                            $this->info("Created new ProductQuantity for product ID {$itemId} with price {$unitCost}");

            }

                // return;
                // if (empty($itemName)) {
                //     $failedProducts[] = [
                //         'item' => 'Unknown Item',
                //         'reason' => 'Missing ITEM in row ' . ($index + 1). ''.$itemName,
                //     ];
                //     continue;
                // }

                // // Fetch all products and find the most similar one based on 70% similarity
                // $products = Product::all();
                // $matchedProduct = null;
                // $maxSimilarity = 0;

                // foreach ($products as $product) {
                //     similar_text(strtolower($itemName), strtolower($product->name), $percent);

                //     if ($percent >= 95 && $percent > $maxSimilarity) {
                //         $matchedProduct = $product;
                //         $maxSimilarity = $percent;
                //     }
                // }

                // // If no product matched with 70% similarity, log failure and skip
                // if (!$matchedProduct) {
                //     $product = new Product();
                //     $product->name = $itemName;
                //     $product->image = $image;
                //     $product->about = "this is $itemName";
                //     $product->image = $image;
                //     $product->size_id = $this->getSizeIdFromUom($uom);
                //     $product->save();
                //     $matchedProduct = $product;
                //     $failedProducts[] = [
                //         'item' => $itemName,
                //         'reason' => 'Added new item',
                //     ];
                //     continue;
                // }

                // $productId = $matchedProduct->id;
                // $expectedSizeId = $this->getSizeIdFromUom($uom);

                // if (!$expectedSizeId) {
                //     $failedProducts[] = [
                //         'item' => $itemName,
                //         'reason' => 'Size not found for ' . $uom,
                //     ];
                //     continue;
                // }

                // if ($matchedProduct->size_id !== $expectedSizeId) {
                //     // If current size_id doesn't match expected, update it
                //     $matchedProduct->update([
                //         'size_id' => $expectedSizeId,
                //     ]);
                //     $this->info("Updated size_id for product: {$matchedProduct->name} (ID: {$productId}) to $expectedSizeId");
                // }
                // // Check if ProductQuantity exists for this product and location_id 2
                // try {
                //     $productQuantity = ProductQuantity::where('product_id', $productId)
                //         ->where('location_id', 2)
                //         ->first();

                //     if ($productQuantity) {
                //         // If ProductQuantity exists, update the price
                //         $productQuantity->update([
                //             'price' => $unitCost,
                //         ]);
                //         $this->info("Updated price for product ID {$productId} to {$unitCost}");
                //     } else {
                //         // If ProductQuantity doesn't exist, create a new entry
                //         ProductQuantity::create([
                //             'product_id' => $productId,
                //             'location_id' => 2,
                //             'quantity' => 1,
                //             'discount' => 0,
                //             'price' => $unitCost,
                //         ]);
                //         $this->info("Created new ProductQuantity for product ID {$productId} with price {$unitCost}");
                //     }

                //     // Output the matched product details
                //     $this->info("Product ID {$matchedProduct->id} - {$matchedProduct->name} updated or created with price: $unitCost");

            //      catch (\Exception $e) {
            //         // Log failure if updating or creating product quantity failed
            //         $failedProducts[] = [
            //             'item' => $itemName,
            //             'reason' => 'Failed to update or create ProductQuantity: ' . $e->getMessage(),
            //         ];
            //     }
            // }

            // Commit the transaction
            DB::commit();

            // // Output success and failed records
            $this->info('Product quantities and prices updated successfully for location_id 2.');
    }
            // // Check for failed records
            // if (!empty($failedProducts)) {
            //     $this->warn('The following products could not be updated:');
            //     foreach ($failedProducts as $failedProduct) {
            //         $this->warn("Item: {$failedProduct['item']} - Reason: {$failedProduct['reason']}");
            //     }
            // }

         catch (\Exception $e) {
            // Rollback on error
            DB::rollBack();
            $this->error('Error importing product quantities: ' . $e->getMessage());
        }
    }

    private function getSizeIdFromUom($size)
    {
        // Convert the size to lowercase to improve matching
        $size = strtolower($size);

        // Look for a match in the UOM mappings
        foreach ($this->sizeMappings as $key => $value) {
            if (strpos($size, strtolower($key)) !== false) {
                return $value;
            }
        }

        return null; // Return null if no match is found
    }


    //  public function handle()
    // {
    //     // Start a transaction
    //     DB::beginTransaction();

    //     $failedProducts = []; // To store failed products and reasons

    //     try {
    //         // Get all products
    //         $products = Product::all();

    //         foreach ($products as $product) {
    //             $productId = $product->id;

    //             // Check ProductQuantity for location 1
    //             $productQuantityLocation1 = ProductQuantity::where('product_id', $productId)
    //                 ->where('location_id', 1)
    //                 ->first();

    //             // Check ProductQuantity for location 2
    //             $productQuantityLocation2 = ProductQuantity::where('product_id', $productId)
    //                 ->where('location_id', 2)
    //                 ->first();

    //             // Check ProductQuantity for location 3
    //             $productQuantityLocation3 = ProductQuantity::where('product_id', $productId)
    //                 ->where('location_id', 3)
    //                 ->first();

    //             // Step 1: Create or update for location 1
    //             if (!$productQuantityLocation1) {
    //                 // Create with price 0 if it doesn't exist
    //                 $productQuantityLocation1 = ProductQuantity::create([
    //                     'product_id' => $productId,
    //                     'location_id' => 1,
    //                     'quantity' => 1,
    //                     'discount' => 0,
    //                     'price' => 0, // Default price
    //                 ]);
    //             }

    //             // Step 2: Create or update for location 2
    //             if (!$productQuantityLocation2) {
    //                 // Create with price from location 1 or default price if it doesn't exist
    //                 $price = $productQuantityLocation1 ? $productQuantityLocation1->price : 0;
    //                 $productQuantityLocation2 = ProductQuantity::create([
    //                     'product_id' => $productId,
    //                     'location_id' => 2,
    //                     'quantity' => 1,
    //                     'discount' => 0,
    //                     'price' => $price, // Use location 1 price or 0
    //                 ]);
    //             }

    //             // Step 3: If location 3 doesn't exist, create it
    //             if (!$productQuantityLocation3) {
    //                 // Randomly choose price between location 1 or 2
    //                 $randomPrice = $productQuantityLocation1->price ?? $productQuantityLocation2->price ?? 0;
    //                 if ($productQuantityLocation2 && $productQuantityLocation1) {
    //                     $randomPrice = rand(0, 1) ? $productQuantityLocation1->price : $productQuantityLocation2->price;
    //                 }

    //                 // Create for location 3
    //                 ProductQuantity::create([
    //                     'product_id' => $productId,
    //                     'location_id' => 3,
    //                     'quantity' => 1,
    //                     'discount' => 0,
    //                     'price' => $randomPrice,
    //                 ]);
    //             }

    //             // Step 4: Update if price needs syncing between location 1 and location 2
    //             if ($productQuantityLocation1 && $productQuantityLocation2) {
    //                 if ($productQuantityLocation1->price !== $productQuantityLocation2->price) {
    //                     // Sync prices, choose either one randomly if they differ
    //                     $randomSyncPrice = rand(0, 1) ? $productQuantityLocation1->price : $productQuantityLocation2->price;
    //                     $productQuantityLocation1->update(['price' => $randomSyncPrice]);
    //                     $productQuantityLocation2->update(['price' => $randomSyncPrice]);
    //                 }
    //             }

    //             // Output the matched product details
    //             $this->info("Product ID {$product->id} - {$product->name} processed across all locations.");
    //         }

    //         // Commit the transaction
    //         DB::commit();

    //         // Output success and failed records
    //         $this->info('Product quantities and prices synced successfully for locations 1, 2, and 3.');

    //         // Check for failed records
    //         if (!empty($failedProducts)) {
    //             $this->warn('The following products could not be updated:');
    //             foreach ($failedProducts as $failedProduct) {
    //                 $this->warn("Item: {$failedProduct['item']} - Reason: {$failedProduct['reason']}");
    //             }
    //         }

    //     } catch (\Exception $e) {
    //         // Rollback on error
    //         DB::rollBack();
    //         $this->error('Error syncing product quantities: ' . $e->getMessage());
    //     }
    // }


}
