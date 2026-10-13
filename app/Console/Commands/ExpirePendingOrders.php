<?php

namespace App\Console\Commands;

use App\Models\ClientOrder;
use App\Models\OrderStatusHistory;
use App\Services\MercadoPagoService;
use Illuminate\Console\Command;

class ExpirePendingOrders extends Command
{
    protected $signature = 'orders:expire-pending
                            {--grace=10 : Minutos de tolerância após a expiração do PIX}
                            {--dry-run : Apenas lista os pedidos que seriam cancelados}';

    protected $description = 'Cancela pedidos online (PIX / Checkout Pro) cujo pagamento expirou ou foi recusado.';

    public function handle(MercadoPagoService $mercadoPago): int
    {
        $grace = (int) $this->option('grace');
        $dryRun = (bool) $this->option('dry-run');
        $cancelled = 0;

        $orders = ClientOrder::with('payments')
            ->where('status', 'pending')
            ->where(fn ($q) => $q->whereNull('source')->orWhere('source', '!=', 'pdv'))
            ->whereHas('payments')
            ->where('created_at', '<', now()->subMinutes(MercadoPagoService::PIX_EXPIRATION_MINUTES + $grace))
            ->get();

        foreach ($orders as $order) {
            if (!$this->isExpired($order, $grace)) {
                continue;
            }

            if ($dryRun) {
                $this->line("[dry-run] {$order->order_number}");
                $cancelled++;
                continue;
            }

            // Última checagem: o pagamento pode ter sido aprovado sem o webhook chegar
            $mercadoPago->syncOrderPayment($order);
            $order->refresh();

            if ($order->status !== 'pending') {
                continue;
            }

            // Estoque não é reservado em pedidos pendentes (a baixa só ocorre no pagamento),
            // então não usamos $order->cancel(), que devolveria estoque.
            $order->update(['status' => 'cancelled']);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => 'pending',
                'to_status' => 'cancelled',
                'notes' => MercadoPagoService::AUTO_EXPIRED_NOTE,
            ]);

            $this->line("Cancelado: {$order->order_number}");
            $cancelled++;
        }

        $this->info("{$cancelled} pedido(s) " . ($dryRun ? 'seriam cancelados' : 'cancelados') . '.');

        return self::SUCCESS;
    }

    protected function isExpired(ClientOrder $order, int $grace): bool
    {
        $payment = $order->payments->sortByDesc('id')->first();

        return match ($payment->payment_method) {
            'pix' => $payment->pix_expiration?->lt(now()->subMinutes($grace)) ?? false,

            // Checkout Pro: preferência vale 24h. Pagamentos reais pendentes
            // (boleto, cartão em análise) não são cancelados aqui.
            'whatsapp' => false,
            default => !in_array($payment->mercado_pago_status, ['pending', 'in_process', 'authorized'])
                && $order->created_at->lt(now()->subHours(MercadoPagoService::CHECKOUT_PRO_EXPIRATION_HOURS)->subMinutes($grace)),
        };
    }
}
