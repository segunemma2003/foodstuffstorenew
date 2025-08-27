<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class BusinessOrder extends Model
{

    protected $guarded =['id'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function customerMember(): BelongsTo
    {
        return $this->belongsTo(BusinessCustomer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BusinessOrderItem::class);
    }
    public function invoice()
    {
        return $this->hasOne(BusinessInvoice::class);
    }
}
