<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantOrder extends Model
{

    use HasFactory;
    protected $guarded = ['id'];


    public function cartItems()
    {
        return $this->hasMany(RestaurantOrderItem::class, 'restaurant_order_id');
    }

    public function restaurant_name()
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }
}
