<?php
namespace App\Services;

use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use App\Jobs\SendReceiptEmailJob;

class ReceiptService
{
    public function createReceipt($customerDetails, $items, $email, $order)
    {
        // Define the buyer (customer) information
        $customer = new Buyer($customerDetails);

        // Add receipt items
        $receipt = Invoice::make()->buyer($customer);

        foreach ($items as $item) {
            $receiptItem = (new InvoiceItem())
                ->title($item['title'])
                ->units($item['units'])
                ->pricePerUnit($item['pricePerUnit'])
                ->quantity($item['quantity']);

            $receipt->addItem($receiptItem)->status(__('invoices::invoice.paid'))
            ->save('cloudinary');
        }
        $link = $receipt->url();
        // Save the receipt as a PDF and send it via email
        SendReceiptEmailJob::dispatch($receipt, $email);

        // Return the receipt object for further processing if needed
        return $receipt;
    }
}
