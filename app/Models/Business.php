<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cacDocument()
    {
        return $this->hasOne(CacDocument::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(BusinessOrder::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(BusinessProduct::class);
    }
}
