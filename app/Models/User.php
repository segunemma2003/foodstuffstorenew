<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;


    public function canAccessPanel(Panel $panel): bool
    {
        // if ($panel->getId() === 'admin') {

        //     return $this->email == "myadmin@admin.com";
        // }
        // else if($panel->getId()=="caterer"){
        //     return $this->email == "caterer@admin.com";
        // }
        // else if($panel->getId()=="blog-admin"){
        //     return $this->email == "blog@foodstuff.store";
        // }
        return true;
        // return str_ends_with($this->email, 'myadmin@admin.com') && $this->hasVerifiedEmail();
    }


    public function customerMember()
    {
        return $this->hasOne(BusinessCustomer::class);
    }

    public function business()
    {
        return $this->hasOne(Business::class);
    }

    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    protected $guarded = ['id'];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // Get the active subscription
    public function activeSubscription()
    {
        return $this->subscriptions()->active()->first();
    }

    // Relationship with Card
    public function cards()
    {
        return $this->hasMany(Card::class);
    }



    // Get the default card
    public function defaultCard()
    {
        return $this->cards()->where('is_default', true)->first();
    }

    public function shoppingLists()
    {
        return $this->hasMany(ShoppingList::class);
    }

    public function favoriteProducts()
    {
        return $this->belongsToMany(Product::class, 'favorites');
    }


    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            $user->referral_code = self::generateUniqueReferralCode();
        });
    }

    private static function generateUniqueReferralCode()
    {
        do {
            // Generate a random 6-digit number
            $code = 'FSS' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('referral_code', $code)->exists()); // Ensure uniqueness

        return $code;
    }

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}
