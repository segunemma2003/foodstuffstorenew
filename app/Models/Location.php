<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function productQuantities()
    {
        return $this->hasMany(ProductQuantity::class);
    }

    // Define a method to get products with prices for the location
    public function products()
    {
        return $this->hasMany(ProductQuantity::class)
                    ->with('product')
                    ->select('product_id', 'price')
                    ->groupBy('product_id', 'price');
    }
}
