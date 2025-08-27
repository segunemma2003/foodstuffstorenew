<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Card extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Set card as default
    public function setAsDefault()
    {
        $this->is_default = true;
        $this->save();
    }

    // Remove default status from all other cards for the user
    public static function removeDefaultFromOthers($userId)
    {
        self::where('user_id', $userId)
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }

    // Method to encrypt the CVV code
    public function setCvvAttribute($value)
    {
        $this->attributes['cvv'] = encrypt($value);
    }

    // Method to decrypt the CVV code
    public function getCvvAttribute($value)
    {
        return decrypt($value);
    }
}
