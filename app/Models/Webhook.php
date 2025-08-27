<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;

    protected $guarded=['id'];

    protected $casts = [
        'events' => 'array',
        'allowed_ips' => 'array',
        'data_filters' => 'array',
        'strict_ip' => 'boolean',
    ];




    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function shouldReceiveEvent($eventType, $eventData)
    {
        // Check if subscribed to this event type
        if ($this->events && !in_array($eventType, $this->events)) {
            return false;
        }

        // Apply data filters if present
        if ($this->data_filters) {
            foreach ($this->data_filters as $field => $value) {
                if (!array_key_exists($field, $eventData) || $eventData[$field] != $value) {
                    return false;
                }
            }
        }

        return true;
    }
}
