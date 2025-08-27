<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;
    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
        'processed_at' => 'datetime'
    ];

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        // Automatically generate a unique 10-character code
        static::creating(function ($model) {
            $model->unique_code = self::generateUniqueCode();
        });
    }

    public static function generateUniqueCode()
    {
        $code = Str::upper(Str::random(10));

        // Ensure the code is unique
        while (self::where('unique_code', $code)->exists()) {
            $code = Str::upper(Str::random(10));
        }

        return $code;
    }


     // Polymorphic relationship
     public function transactionable()
     {
         return $this->morphTo();
     }

     // Relationship to User
     public function user()
     {
         return $this->belongsTo(User::class);
     }


     public function virtualAccount()
        {
            return $this->belongsTo(VirtualAccount::class);
        }

        public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }
      /**
     * Scope a query to only include credit transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    /**
     * Scope a query to only include completed transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include transactions by payment method.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $method
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }
}
