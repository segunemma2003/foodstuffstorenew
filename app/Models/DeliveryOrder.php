<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $table = 'deliveries_orders';
    protected $fillable = [
        'fare_estimate_id',
        'order_reference',
        'kwik_job_id',
        'kwik_job_token',
        'pickup_tracking_link',
        'delivery_tracking_link',
        'pickup_hash',
        'delivery_hash',
        'special_instructions',
        'status',
        'job_status',
        'kwik_response',
        'last_status_update'
    ];

    protected $casts = [
        'kwik_response' => 'array',
        'last_status_update' => 'datetime'
    ];

    /**
     * Get the fare estimate that owns this delivery order.
     */
    public function fareEstimate()
    {
        return $this->belongsTo(DeliveryFareEstimate::class, 'fare_estimate_id');
    }

    /**
     * Get status text from status code
     */
    public function getStatusTextAttribute()
    {
        $statuses = [
            0 => 'upcoming',
            1 => 'started',
            2 => 'completed',
            3 => 'failed',
            4 => 'arrived',
            6 => 'unassigned',
            7 => 'accepted',
            8 => 'declined',
            9 => 'cancelled',
            10 => 'deleted'
        ];

        return $statuses[$this->job_status] ?? 'unknown';
    }

    /**
     * Check if delivery is completed
     */
    public function isCompleted()
    {
        return $this->job_status == 2;
    }

    /**
     * Check if delivery is in progress
     */
    public function isInProgress()
    {
        return in_array($this->job_status, [0, 1, 4, 7]); // upcoming, started, arrived, accepted
    }

    /**
     * Check if delivery is cancelled or failed
     */
    public function isCancelledOrFailed()
    {
        return in_array($this->job_status, [3, 8, 9, 10]); // failed, declined, cancelled, deleted
    }

    /**
     * Scope to get orders by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get orders by job status
     */
    public function scopeByJobStatus($query, $jobStatus)
    {
        return $query->where('job_status', $jobStatus);
    }
}
