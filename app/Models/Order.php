<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded =['id'];


    protected static function boot()
    {
        parent::boot();

        // Automatically generate a unique 6-character code
        static::creating(function ($model) {
            $model->unique_code = self::generateUniqueCode();
        });

    }


    public static function generateUniqueCode()
    {
        $code = Str::upper(Str::random(6));

        // Ensure the code is unique
        while (self::where('unique_code', $code)->exists()) {
            $code = Str::upper(Str::random(6));
        }

        return $code;
    }

    // Relationship to User (the user who made the order)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship to User (the user making the delivery, nullable)
    public function deliveryUser()
    {
        return $this->belongsTo(User::class, 'delivery_user_id');
    }

    // Relationship to Cart
    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }


    public function allCartItems()
    {
        return $this->hasMany(Cart::class)->with('product'); // Ensure 'product' is eager-loaded
    }

    // Polymorphic relationship to Reviews
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

}
