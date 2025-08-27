<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory;
    protected $guarded = ['id'];


    public static function boot()
    {
        parent::boot();

        // Generate a unique 8-character alphanumeric code when a new company is created
        static::creating(function ($company) {
            do {
                // Generate a random 8-character alphanumeric string
                $uniqueCode = Str::random(8);
            } while (self::where('unique_code', $uniqueCode)->exists()); // Ensure it's unique

            $company->unique_code = $uniqueCode;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

}
