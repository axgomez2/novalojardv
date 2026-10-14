<?php

namespace App\Services;

use App\Models\ClientOrder;
use App\Models\OrderInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class OrderInvoiceService
{
    /**
     * Dados do remetente (loja)
     */
    protected array $senderData;

    public function __construct()
    {
        $this->senderData = [
            'name' => config('app.store_name', 'RDV Discos'),
            'legal_name' => config('app.store_legal_name', 'Silmara Aparecida de Oliveira'),
            'cpf_cnpj' => config('app.store_cnpj', '61.850.546/0001-26'),
            'ie' => config('app.store_ie', '154951218114'),
            'address' => config('app.store_address', 'Rua Montevideu, 174 - Santo André/SP'),
            'phone' => config('app.store_phone', '(11) 94715-9293'),
            'email' => config('app.store_email', 'rdvdiscos@gmail.com'),
        ];
    }

    /**
     * Gerar invoice do pedido
     */
    public function generateInvoice(ClientOrder $order): OrderInvoice
    {
        $order->load(['items.vinylStock.vinylMaster', 'clientUser', 'shippingAddress']);

        // Preparar dados do destinatário
        $recipientData = $this->getRecipientData($order);

        // Preparar itens
        $items = $this->prepareItems($order);

        // Criar invoice
        $invoice = OrderInvoice::create([
            'order_id' => $order->id,
            'type' => 'content_declaration',
            'sender_name' => $this->senderData['name'],
            'sender_legal_name' => $this->senderData['legal_name'],
            'sender_cpf_cnpj' => $this->senderData['cpf_cnpj'],
            'sender_ie' => $this->senderData['ie'],
            'sender_address' => $this->senderData['address'],
            'sender_phone' => $this->senderData['phone'],
            'sender_email' => $this->senderData['email'],
            'recipient_name' => $recipientData['name'],
            'recipient_cpf_cnpj' => $recipientData['cpf_cnpj'],
            'recipient_address' => $recipientData['address'],
            'recipient_phone' => $recipientData['phone'],
            'total_value' => $order->subtotal,
            'shipping_value' => $order->shipping_cost,
            'items' => $items,
        ]);

        // Gerar PDF
        $pdfPath = $this->generatePdf($invoice, $order);
        $invoice->update(['pdf_path' => $pdfPath]);

        // Atualizar pedido
        $order->update([
            'invoice_number' => $invoice->invoice_number,
            'invoice_generated_at' => now(),
        ]);

        return $invoice;
    }

    /**
     * Obter dados do destinatário
     */
    protected function getRecipientData(ClientOrder $order): array
    {
        if ($order->clientUser) {
            $address = $order->shippingAddress?->full_address ?? '';
            return [
                'name' => $order->clientUser->name,
                'cpf_cnpj' => $order->clientUser->cpf,
                'address' => $address,
                'phone' => $order->customer_phone,
            ];
        }

        // Cliente não cadastrado (guest)
        $addressData = $order->shipping_address_data ?? [];
        $address = implode(', ', array_filter([
            ($addressData['street'] ?? '') . ', ' . ($addressData['number'] ?? ''),
            $addressData['complement'] ?? null,
            $addressData['neighborhood'] ?? null,
            ($addressData['city'] ?? '') . '/' . ($addressData['state'] ?? ''),
            $addressData['zip_code'] ?? null,
        ]));

        return [
            'name' => $order->guest_name ?? 'Não informado',
            'cpf_cnpj' => $order->guest_cpf,
            'address' => $address,
            'phone' => $order->guest_phone,
        ];
    }

    /**
     * Preparar lista de itens
     */
    protected function prepareItems(ClientOrder $order): array
    {
        $items = [];

        foreach ($order->items as $item) {
            $vinyl = $item->vinylStock?->vinylMaster;
            $artist = $vinyl?->artist?->name;
            $title = $vinyl?->title;

            $description = trim(collect([$artist, $title])->filter()->implode(' - '));

            $items[] = [
                'description' => $description !== '' ? $description : 'Disco de Vinil',
                'quantity' => $item->quantity,
                'unit_value' => (float) $item->unit_price,
                'total_value' => (float) $item->total_price,
            ];
        }

        return $items;
    }

    /**
     * Gerar PDF da invoice
     */
    protected function generatePdf(OrderInvoice $invoice, ClientOrder $order): string
    {
        $data = [
            'invoice' => $invoice,
            'order' => $order,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = "invoices/{$invoice->invoice_number}.pdf";
        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }

    /**
     * Regenerar PDF de uma invoice existente
     */
    public function regeneratePdf(OrderInvoice $invoice): string
    {
        $order = $invoice->order;
        $order->load(['items.vinylStock.vinylMaster', 'clientUser', 'shippingAddress']);

        return $this->generatePdf($invoice, $order);
    }
}
