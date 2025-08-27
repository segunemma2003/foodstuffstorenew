<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BusinessInvoice extends Model
{
    protected $guarded =['id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Accessor for download URL
    public function getDownloadUrlAttribute()
    {
        return Storage::disk($this->storage_disk ?: 's3')->url($this->file_path);
    }

}
