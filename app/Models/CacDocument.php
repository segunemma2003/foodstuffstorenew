<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CacDocument extends Model
{
    protected $guarded =['id'];

    public function getUrlAttribute()
    {
        return Storage::disk('s3_private')->temporaryUrl($this->path, now()->addMinutes(30));
    }

}
