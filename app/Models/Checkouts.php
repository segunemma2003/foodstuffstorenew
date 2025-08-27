<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checkouts extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
    ];

}
