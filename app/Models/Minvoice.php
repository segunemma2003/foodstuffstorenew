<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TomatoPHP\FilamentInvoices\Models\Invoice;
use TomatoPHP\FilamentInvoices\Models\InvoiceLog;
use TomatoPHP\FilamentInvoices\Models\InvoiceMeta;
use TomatoPHP\FilamentTypes\Models\Type;

class Minvoice extends Invoice
{
    use HasFactory;

    protected $table = "invoices";
    protected $guarded = ['id'];


    public function invoicesItems()
    {
        return $this->hasMany(MinvoiceItem::class, 'invoice_id');
    }
    public function invoiceMetas()
    {
        return $this->hasMany(InvoiceMeta::class, 'invoice_id');

    }

    public function category()
    {
        return $this->belongsTo(Type::class, 'invoice_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'invoice_id');
    }

    public function invoiceLogs()
    {
        return $this->hasMany(InvoiceLog::class, 'invoice_id');
    }

}
