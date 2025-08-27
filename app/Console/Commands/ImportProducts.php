<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class ImportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

     protected $signature = 'import:products';
     protected $description = 'Import products from CSV';
    // protected $signature = 'app:import-products';

    // /**
    //  * The console command description.
    //  *
    //  * @var string
    //  */
    // protected $description = 'Command description';


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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Path to the CSV file
        $filePath = public_path('excel/pricelistnewabuja.xslx');

        // Reading the CSV
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0); // Assuming the first row contains headers

        $failedRecords = []; // To store failed record IDs

        // Start a transaction
        DB::beginTransaction();

        try {
            foreach ($csv as $record) {
                $name = $record['Name'];
                $category = $record['Category'];
                $image = $record['Image'];
                $description = $record['Description'];

                // Check for a similar name with fuzzy matching
                $existingProducts = Product::all();
                $similarProduct = null;

                foreach ($existingProducts as $existingProduct) {
                    $similarity = 0;
                    similar_text(strtolower($existingProduct->name), strtolower($name), $similarity);
                    if ($similarity > 80) { // Threshold for similarity (adjust as needed)
                        $similarProduct = $existingProduct;
                        break;
                    }
                }

                if ($similarProduct) {
                    // If similar name found, log and skip
                    $failedRecords[] = [
                        'id' => $similarProduct->id,
                        'name' => $similarProduct->name
                    ];
                    continue;
                }

                // Category mapping
                $categoryId = match (strtolower($category)) {
                    'others', 'fluid', 'seasoning' => 1,
                    'grains', 'nuts', 'seeds', 'processed' => 3,
                    'live stock', 'protein' => 2,
                    'vegetable', 'fruit' => 4,
                    default => 1 // Default to category 1 if none matches
                };

                // Adjust the image URL if necessary
                if (str_starts_with($image, 'https://res.cloudinary.com/iamdevmaniac/image/upload/')) {
                    $image = str_replace('https://res.cloudinary.com/iamdevmaniac/image/upload/', '', $image);
                }

                // Create the new product
                $product = new Product();
                $product->name = $name;
                $product->size_id = 1; // Default size_id
                $product->category_id = $categoryId;
                $product->about = $description;
                $product->image = $image;

                // Save the product
                $product->save();
            }

            // Commit the transaction
            DB::commit();

            // Return a report on failed records
            if (empty($failedRecords)) {
                $this->info('All records successfully added.');
            } else {
                $this->warn('Some products were skipped due to similar names:');
                foreach ($failedRecords as $failed) {
                    $this->warn("ID: {$failed['id']}, Name: {$failed['name']}");
                }
            }

        } catch (\Exception $e) {
            // Rollback on error
            DB::rollBack();
            $this->error('Error importing products: ' . $e->getMessage());
        }
    }
}
