<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory,  SoftDeletes;


    protected $guarded = ['id'];

     // Define the relationship to Category
     public function category()
     {
         return $this->belongsTo(Category::class);
     }

     // Define the relationship to Size
     public function size()
     {
         return $this->belongsTo(Size::class);
     }

     // Define the relationship to ProductQuantity
     public function quantities()
     {
         return $this->hasMany(ProductQuantity::class);
     }

     // Define the relationship to Review
     public function reviews()
     {
        return $this->morphMany(Review::class, 'reviewable');
     }

     public function shoppingLists()
    {
        return $this->belongsToMany(ShoppingList::class, 'shopping_list_product')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function getPriceByLocation($locationId)
    {
        $productQuantity = $this->quantities()->where('location_id', $locationId)->first();

        // Return the price if a matching location is found
        return $productQuantity ? $productQuantity->price : null;
    }
}
