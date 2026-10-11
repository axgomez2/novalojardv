<?php

namespace App\Mail;

use App\Models\ClientOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ClientOrder $order;

    public function __construct(ClientOrder $order)
    {
        $this->order = $order->load(['items.vinylStock.vinylMaster', 'shippingAddress']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pedido Confirmado - #' . $this->order->order_number . ' - RDV Discos',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-confirmation',
            with: [
                'order' => $this->order,
                'items' => $this->order->items,
                'address' => $this->order->shippingAddress,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
