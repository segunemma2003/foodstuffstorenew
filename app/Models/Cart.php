<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    // Relationship to Product
    public function product()
    {
        return $this->belongsTo(Product::class); // Ensure 'product_id' exists in cart_items table
    }
    // Relationship to Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
