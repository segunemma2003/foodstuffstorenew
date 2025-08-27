@component('mail::message')
# New Invoice Generated

A new invoice has been generated for order #{{ $order->order_number }}.

**Customer:** {{ $order->customerMember->company_name }}
**Business:** {{ $order->business->name }}
**Total Amount:** ₦{{ number_format($order->total_amount, 2) }}

@component('mail::button', ['url' => route('invoices.show', $order->invoice->id)])
View Invoice
@endcomponent

Thanks,
FoodStuff Store Team
@endcomponent
