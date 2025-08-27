<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class ProductQuantity extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

     // Define the relationship to Product
     public function product()
     {
         return $this->belongsTo(Product::class);
     }

     // Define the relationship to Location
     public function location()
     {
         return $this->belongsTo(Location::class);
     }
}
