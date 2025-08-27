<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class VirtualAccount extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'array',
    ];


    public function creditUserWallet(float $amount): bool
    {
        try {
            $wallet = $this->user->wallet;

            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $this->user_id,
                    'balance' => $amount,
                    'currency' => $this->currency ?? 'NGN'
                ]);
            } else {
                $wallet->balance += $amount;
                $wallet->save();
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error crediting user wallet: ' . $e->getMessage());
            return false;
        }
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
