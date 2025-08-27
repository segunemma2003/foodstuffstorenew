<?php

namespace App\Http\Controllers;

use App\Models\BusinessInvoice;
use App\Models\BusinessOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);

        $order = BusinessOrder::with(['customerMember', 'items.product'])->find($request->order_id);

        // Check if invoice already exists
        $existingInvoice = BusinessInvoice::where('order_id', $order->id)->first();
        if ($existingInvoice) {
            return response()->json([
                'message' => 'Invoice already exists',
                'data' => $existingInvoice
            ]);
        }

        // Generate PDF
        $pdf = Pdf::loadView('invoices.order', compact('order'));
        $filename = 'invoices/' . $order->business_id . '/INV-' . $order->order_number . '.pdf';

        // Store PDF in S3
        Storage::disk('s3')->put($filename, $pdf->output());

        // Create invoice record
        $invoice = BusinessInvoice::create([
            'order_id' => $order->id,
            'invoice_number' => 'INV-' . $order->order_number,
            'amount' => $order->total_amount,
            'file_path' => $filename,
            'storage_disk' => 's3',
            'status' => 'generated'
        ]);

        return response()->json([
            'message' => 'Invoice generated successfully',
            'data' => [
                'invoice' => $invoice,
                'download_url' => Storage::disk('s3')->url($filename)
            ]
        ]);
    }

    public function send(Request $request, $id)
    {
        $invoice = BusinessInvoice::findOrFail($id);
        $order = $invoice->order;

        // Get the file from S3
        $file = Storage::disk('s3')->get($invoice->file_path);

        // Send to FoodStuff store emails
        $emails = ['support@foodstuff.store', 'foodstuffstore33@gmail.com'];

        foreach ($emails as $email) {
            Mail::send('emails.invoice', ['order' => $order], function ($message) use ($email, $invoice, $file) {
                $message->to($email)
                    ->subject('Invoice #' . $invoice->invoice_number)
                    ->attachData($file, $invoice->invoice_number . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
            });
        }

        $invoice->update(['status' => 'sent']);

        return response()->json([
            'message' => 'Invoice sent successfully'
        ]);
    }

    public function show($id)
    {
        $invoice = BusinessInvoice::findOrFail($id);

        if ($invoice->storage_disk !== 's3') {
            return response()->json(['message' => 'Invoice not stored in S3'], 400);
        }

        $file = Storage::disk('s3')->get($invoice->file_path);

        return response($file, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $invoice->invoice_number . '.pdf"'
        ]);
    }
}
