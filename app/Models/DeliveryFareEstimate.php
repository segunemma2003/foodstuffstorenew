<?php

// Model 1: DeliveryFareEstimate
// File: app/Models/DeliveryFareEstimate.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryFareEstimate extends Model
{
    use HasFactory;

    protected $fillable = [
        'pickup_address',
        'pickup_latitude',
        'pickup_longitude',
        'pickup_phone',
        'pickup_email',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_phone',
        'delivery_email',
        'restaurant_name',
        'customer_name',
        'vehicle_id',
        'kwik_response',
        'fare_amount',
        'service_charge',
        'insurance_amount',
        'total_tasks',
        'is_used',
        'expires_at'
    ];

    protected $casts = [
        'pickup_latitude' => 'decimal:8',
        'pickup_longitude' => 'decimal:8',
        'delivery_latitude' => 'decimal:8',
        'delivery_longitude' => 'decimal:8',
        'fare_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'insurance_amount' => 'decimal:2',
        'is_used' => 'boolean',
        'expires_at' => 'datetime',
        'kwik_response' => 'array'
    ];

    /**
     * Get the delivery order associated with this fare estimate.
     */
    public function deliveryOrder()
    {
        return $this->hasOne(DeliveryOrder::class, 'fare_estimate_id');
    }

    /**
     * Check if the fare estimate has expired
     */
    public function hasExpired()
    {
        return $this->expires_at < now();
    }

    /**
     * Scope to get unexpired and unused estimates
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_used', false)
                    ->where('expires_at', '>', now());
    }
}
