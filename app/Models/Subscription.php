<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

   // Relationship with User
   public function user()
   {
       return $this->belongsTo(User::class);
   }

   // Relationship with MembershipPlan
   public function membershipPlan()
   {
       return $this->belongsTo(MembershipPlan::class);
   }

   // Scope to get active subscriptions
   public function scopeActive($query)
   {
       return $query->where('is_active', true);
   }
}
