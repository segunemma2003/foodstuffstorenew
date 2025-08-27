<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VirtualAccountPaymentReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Get the channels the event should broadcast on.
     * Using a public channel since we don't require authentication
     */
    public function broadcastOn()
    {
        // Using a channel identified by the virtual account ID
        return new Channel('virtual-account.' . $this->transaction->virtual_account_id);
    }

    public function broadcastAs()
    {
        return 'payment.received';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->transaction->id,
            'virtual_account_id' => $this->transaction->virtual_account_id,
            'amount' => $this->transaction->amount,
            'currency' => $this->transaction->currency,
            'reference' => $this->transaction->reference,
            'payer_name' => $this->transaction->payer_name,
            'date' => $this->transaction->created_at->format('Y-m-d H:i:s'),
            'account_number' => $this->transaction->virtualAccount->account_number,
        ];
    }
}
