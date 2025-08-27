<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    // Define the relationship to Product
    public function reviewable()
    {
        return $this->morphTo();
    }

    // Define the relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship to ReviewImage
    public function images()
    {
        return $this->hasMany(ReviewImage::class);
    }
}
