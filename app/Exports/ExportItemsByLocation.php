<?php

namespace App\Exports;

use App\Models\ProductQuantity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportItemsByLocation implements FromQuery, WithHeadings
{

    protected $locationId;

    public function __construct($locationId)
    {
        $this->locationId = $locationId;
    }

    public function query()
    {
        return ProductQuantity::query()
            ->where('location_id', $this->locationId)
            ->with('product')
            ->select([
                'product_quantities.id',
                'products.name as product_name',
                'product_quantities.price'
            ])
            ->join('products', 'products.id', '=', 'product_quantities.product_id');
    }

    public function headings(): array
    {
        return ['ID', 'Product Name', 'Price'];
    }
}
