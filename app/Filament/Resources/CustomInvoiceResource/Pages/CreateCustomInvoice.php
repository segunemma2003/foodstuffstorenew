<?php

namespace App\Filament\Resources\CustomInvoiceResource\Pages;

use App\Filament\Resources\CustomInvoiceResource;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceService;
use Filament\Actions;
use Illuminate\Support\Str;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use TomatoPHP\FilamentInvoices\Filament\Resources\InvoiceResource\Pages\CreateInvoice;

class CreateCustomInvoice extends CreateInvoice
{
    protected static string $resource = CustomInvoiceResource::class;


    public function afterCreate()
    {
        $data = [];
        Log::info( $this->getRecord());
        $data['discount'] = collect($this->getRecord()->invoicesItems)->sum(function ($item) {
            return $item->discount * $item->qty;
        });
        $data['vat'] = collect($this->getRecord()->invoicesItems)->sum(function ($item) {
            return $item->vat * $item->qty;
        });
        $data['total'] = collect($this->getRecord()->invoicesItems)->sum('total');

        $this->getRecord()->update($data);
        // Log::info( $this->getRecord());
        $invoiceService = app(InvoiceService::class);
        $this->sendMail($invoiceService);

        $this->getRecord()->invoiceLogs()->create([
            'log' => "Invoice created By: " . Auth::user()->name,
            'type' => 'created'
        ]);
    }

    protected function sendMail(InvoiceService $invoiceService){
        $customer = User::whereId($this->getRecord()->for_id)->first();
        $items = $this->getRecord()->invoicesItems;

        $unique_code = $this->getRecord()->uuid;

        $customerDetails = [
            'name'          => $customer->name,
            'address'       =>  $customer->residential,
            'code'          => $unique_code,
            'custom_fields' => [
                'order number' => $unique_code,
            ],
        ];

        $items = [];
        foreach ($this->getRecord()->invoicesItems as $product) {
            // $prod = Product::whereId($product['invoice_id'])->first();
            // Log::info( $product['size']);
            // Log::info($prod);
            array_push($items, [
                'title'=> $product['item'],
                'units'=> $product['size'],
                'description'=> $product['description'],
                'price'=> $product['price'],
            //    'size'=>$product['size'],
                'quantity'=>$product['qty']
            ]);
        }
        // Log::info($items);
        // $invoiceService->createInvoice($customerDetails, $items, $customer, $this->getRecord()->shipping);

    }
}
