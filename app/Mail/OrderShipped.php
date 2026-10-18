<?php

namespace App\Mail;

use App\Models\ClientOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderShipped extends Mailable implements ShouldQueue
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
            subject: 'Seu Pedido Foi Enviado! - #' . $this->order->order_number . ' - RDV Discos',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-shipped',
            with: [
                'order' => $this->order,
                'items' => $this->order->items,
                'address' => $this->order->shippingAddress,
                'trackingUrl' => $this->getTrackingUrl(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    protected function getTrackingUrl(): ?string
    {
        if (!$this->order->tracking_code) {
            return null;
        }

        $carrier = strtolower($this->order->shipping_carrier ?? '');
        $code = $this->order->tracking_code;

        // URLs de rastreio por transportadora
        return match (true) {
            str_contains($carrier, 'correios') => "https://rastreamento.correios.com.br/app/index.php?objetos={$code}",
            str_contains($carrier, 'jadlog') => "https://www.jadlog.com.br/siteInstitucional/tracking.jad?cte={$code}",
            str_contains($carrier, 'sedex') => "https://rastreamento.correios.com.br/app/index.php?objetos={$code}",
            str_contains($carrier, 'pac') => "https://rastreamento.correios.com.br/app/index.php?objetos={$code}",
            default => null,
        };
    }
}
