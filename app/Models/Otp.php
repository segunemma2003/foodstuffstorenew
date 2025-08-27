<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = ['identifier', 'otp', 'expires_at', 'verified'];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified' => 'boolean',
    ];

    public static function generateOtp($identifier)
    {
        // Generate a 6-digit numeric OTP
        $otp = mt_rand(100000, 999999);

        return self::create([
            'identifier' => $identifier, // This could be an email or phone number
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10), // OTP expires in 10 minutes
            'verified' => false,
        ]);
    }

    public static function getPendingOtp($identifier)
    {
        return self::where('identifier', $identifier)
                    ->where('expires_at', '>', now())
                    ->where('verified', false)
                    ->latest()
                    ->first();
    }

    public function isExpired()
    {
        return $this->expires_at->lessThan(Carbon::now());
    }

    public function markAsVerified()
    {
        $this->update(['verified' => true]);
    }

}
