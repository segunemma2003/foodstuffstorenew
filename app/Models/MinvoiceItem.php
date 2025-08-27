<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TomatoPHP\FilamentInvoices\Models\InvoicesItem;

class MinvoiceItem extends InvoicesItem
{
    use HasFactory;

    // protected $guarded = ['id'];
    protected $fillable = [
        'invoice_id',
        'type',
        'item_type',
        'item_id',
        'item',
        'description',
        'note',
        'qty',
        'price',
        'discount',
        'vat',
        'total',
        'returned_qty',
        'returned',
        'is_free',
        'is_returned',
        'options',
        'created_at',
        'product_id',
        'size',
        'updated_at'
    ];


    protected $table="invoices_items";

    public function invoice()
    {
        return $this->belongsTo(Minvoice::class, 'invoice_id');
    }
}
