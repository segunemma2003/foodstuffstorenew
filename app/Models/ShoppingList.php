<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShoppingList extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

 // Relationship with User
 public function user()
 {
     return $this->belongsTo(User::class);
 }

 // Relationship with Products
 public function products()
 {
     return $this->belongsToMany(Product::class, 'shopping_list_product')
                ->withPivot('quantity')
                ->with(['quantities'])
                 ->withTimestamps(); // Assuming a pivot table named shopping_list_product
 }
}
