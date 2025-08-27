<?php
namespace App\Services;

use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use App\Jobs\SendInvoiceEmailJob;
use LaravelDaily\Invoices\Classes\Party;

class InvoiceService
{
    public function createInvoice($customerDetails, $items, $user, $delivery)
    {
        // Define the buyer (customer) information
        $customer = new Buyer($customerDetails);

        $client = new Party([
            'name'          => 'FoodStuff store',
            'phone'         => '+234 906 828 0610',
            'custom_fields' => [
                'note'        => 'IDDQD',
                'business id' => 'RC1723748',
            ],
        ]);

        $notes = [
            'Account Name: Online FoodStuff Store Limited',
            'Bank Name: UBA',
            'Account Number: 1023748564',
        ];
        $notes = implode("<br>", $notes);

        $time = time();
        // Add invoice items
        $invoice = Invoice::make()
                ->buyer($customer)
                ->status(__('Pending'))
                ->logo(public_path('latest/image/FSSLOGO1-2.png'))
                ->currencyCode('NGN')
                ->seller($client)
                ->serialNumberFormat('{SEQUENCE}/{SERIES}')
                ->date(now()->subWeeks(3))
                ->dateFormat('m/d/Y')
                ->payUntilDays(14)
                ->currencySymbol('₦')
                ->filename($time)
                // ->shipping($delivery)
                ->currencyFormat('{SYMBOL}{VALUE}')
                ->currencyThousandsSeparator(',');
                if ($delivery > 0) {
                    $invoice->shipping($delivery);
                }
        $invoiceItems = [];
        foreach ($items as $item) {
            array_push($invoiceItems, (new InvoiceItem())
                ->title($item['title'])
                ->pricePerUnit($item['price'])
                ->units($item['units'])
                ->quantity($item['quantity']));
        }
        $invoice->addItems($invoiceItems)->notes($notes)->save('cloudinary');

        // Save the invoice as a PDF and send it via email
        SendInvoiceEmailJob::dispatch($invoice->url(), $user);

        // Return the invoice object for further processing if needed
        return $invoice;
    }
}
