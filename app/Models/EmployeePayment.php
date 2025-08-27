<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePayment extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

}
