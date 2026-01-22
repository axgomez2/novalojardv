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
            'name' => config('app.store_name', 'Vinil Store'),
            'cpf_cnpj' => config('app.store_cnpj', '00.000.000/0001-00'),
            'address' => config('app.store_address', 'Endereço da Loja'),
        ];
    }

    /**
     * Gerar declaração de conteúdo para o pedido
     */
    public function generateContentDeclaration(ClientOrder $order): OrderInvoice
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
            'sender_cpf_cnpj' => $this->senderData['cpf_cnpj'],
            'sender_address' => $this->senderData['address'],
            'recipient_name' => $recipientData['name'],
            'recipient_cpf_cnpj' => $recipientData['cpf_cnpj'],
            'recipient_address' => $recipientData['address'],
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
            
            $items[] = [
                'description' => $vinyl ? "{$vinyl->artist?->name} - {$vinyl->title}" : 'Disco de Vinil',
                'quantity' => $item->quantity,
                'unit_value' => (float) $item->unit_price,
                'total_value' => (float) $item->total_price,
            ];
        }

        return $items;
    }

    /**
     * Gerar PDF da declaração
     */
    protected function generatePdf(OrderInvoice $invoice, ClientOrder $order): string
    {
        $data = [
            'invoice' => $invoice,
            'order' => $order,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.content-declaration', $data);
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
