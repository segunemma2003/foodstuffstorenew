<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessCustomer extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(BusinessOrder::class);
    }

    public function quotaHistory()
    {
        return $this->hasMany(QuotaHistory::class);
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }

    public function debtPayments()
    {
        return $this->hasMany(DebtPayment::class);
    }

    // Accessor for total debt
    public function getTotalDebtAttribute()
    {
        return $this->debts()->sum('amount');
    }
}
