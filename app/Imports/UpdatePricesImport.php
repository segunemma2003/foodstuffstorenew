<?php

namespace App\Imports;

use App\Models\ProductQuantity;
use Maatwebsite\Excel\Concerns\ToModel;

class UpdatePricesImport implements ToModel
{
    public function model(array $row)
    {
        
        $product = ProductQuantity::find($row['ID']);
        if ($product) {
            $product->update([
                'price' => $row['Price'],
            ]);
        }
        return $product;
    }
}
