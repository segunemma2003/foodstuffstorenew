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
        $disk = $this->storage_disk ?: 's3_private';
        if ($disk === 's3_private') {
            return Storage::disk($disk)->temporaryUrl($this->file_path, now()->addMinutes(30));
        }
        return Storage::disk($disk)->url($this->file_path);
    }

}
