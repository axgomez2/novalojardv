<?php

namespace App\Http\Controllers\Api;

use App\Enums\PreOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\PreOrder;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreOrderClientController extends Controller
{
    public function __construct(protected MercadoPagoService $mercadoPago) {}

    /**
     * Lista as pré-vendas do cliente autenticado.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = PreOrder::with(['vinylStock.vinylMaster.mainArtists'])
            ->where('client_user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($po) => $this->serialize($po));

        return response()->json(['data' => $items]);
    }

    /**
     * Detalhe de uma pré-venda.
     */
    public function show(Request $request, string $code): JsonResponse
    {
        $preOrder = PreOrder::with([
                'vinylStock.vinylMaster.mainArtists',
                'vinylStock.vinylMaster.recordLabel',
                'statusHistories',
            ])
            ->where('client_user_id', $request->user()->id)
            ->where('code', $code)
            ->firstOrFail();

        return response()->json([
            'data' => array_merge(
                $this->serialize($preOrder, true),
                [
                    'history' => $preOrder->statusHistories->map(fn ($h) => [
                        'from_status' => $h->from_status,
                        'from_label' => $h->from_status ? PreOrderStatus::tryFrom($h->from_status)?->label() : null,
                        'to_status' => $h->to_status,
                        'to_label' => PreOrderStatus::tryFrom($h->to_status)?->label(),
                        'note' => $h->note,
                        'created_at' => $h->created_at,
                    ]),
                ]
            ),
        ]);
    }

    /**
     * Inicia pagamento do sinal via Mercado Pago.
     */
    public function paySignal(Request $request, string $code): JsonResponse
    {
        $preOrder = $this->findOwned($request, $code);

        if ($preOrder->status !== PreOrderStatus::AwaitingSignal) {
            return response()->json(['error' => 'Pré-venda não está aguardando sinal.'], 422);
        }

        $result = $this->mercadoPago->createGenericPreference(
            title: "Sinal — Pré-venda {$preOrder->code}",
            amount: (float) $preOrder->signal_amount,
            externalReference: "PREORDER-{$preOrder->id}-SIGNAL",
            payer: [
                'name' => $preOrder->client?->name,
                'email' => $preOrder->client?->email,
            ],
            returnPath: "/minhas-pre-vendas/{$preOrder->code}",
        );

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 502);
        }

        return response()->json($result);
    }

    /**
     * Inicia pagamento do saldo via Mercado Pago.
     */
    public function payBalance(Request $request, string $code): JsonResponse
    {
        $preOrder = $this->findOwned($request, $code);

        if ($preOrder->status !== PreOrderStatus::AwaitingBalance) {
            return response()->json(['error' => 'Pré-venda não está aguardando saldo.'], 422);
        }

        $result = $this->mercadoPago->createGenericPreference(
            title: "Saldo — Pré-venda {$preOrder->code}",
            amount: $preOrder->balance_amount,
            externalReference: "PREORDER-{$preOrder->id}-BALANCE",
            payer: [
                'name' => $preOrder->client?->name,
                'email' => $preOrder->client?->email,
            ],
            returnPath: "/minhas-pre-vendas/{$preOrder->code}",
        );

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 502);
        }

        return response()->json($result);
    }

    /**
     * Solicita PIX manual: registra observação na pré-venda.
     * Cliente recebe a chave PIX da loja em outro fluxo (ex: WhatsApp).
     */
    public function requestManualPix(Request $request, string $code): JsonResponse
    {
        $preOrder = $this->findOwned($request, $code);
        $kind = $request->validate(['kind' => 'required|in:signal,balance'])['kind'];

        $expected = $kind === 'signal' ? PreOrderStatus::AwaitingSignal : PreOrderStatus::AwaitingBalance;
        if ($preOrder->status !== $expected) {
            return response()->json(['error' => 'Pré-venda não está no status correto para esta solicitação.'], 422);
        }

        $note = "Cliente solicitou pagar o " . ($kind === 'signal' ? 'sinal' : 'saldo') . " via PIX manual em " . now()->format('d/m/Y H:i');

        $preOrder->statusHistories()->create([
            'from_status' => $preOrder->status->value,
            'to_status' => $preOrder->status->value,
            'admin_user_id' => null,
            'triggered_by' => 'customer',
            'note' => $note,
        ]);

        $preOrder->customer_notes = trim(($preOrder->customer_notes ?? '') . "\n" . $note);
        $preOrder->save();

        return response()->json([
            'message' => 'Solicitação registrada. A loja entrará em contato para combinar o PIX.',
        ]);
    }

    private function findOwned(Request $request, string $code): PreOrder
    {
        return PreOrder::where('client_user_id', $request->user()->id)
            ->where('code', $code)
            ->firstOrFail();
    }

    private function serialize(PreOrder $po, bool $detailed = false): array
    {
        $data = [
            'code' => $po->code,
            'status' => $po->status->value,
            'status_label' => $po->status->label(),
            'status_color' => $po->status->color(),
            'is_final' => $po->status->isFinal(),
            'requires_signal_payment' => $po->status->requiresSignalPayment(),
            'requires_balance_payment' => $po->status->requiresBalancePayment(),
            'is_signal_overdue' => $po->isSignalOverdue(),
            'is_balance_overdue' => $po->isBalanceOverdue(),

            'quantity' => $po->quantity,
            'unit_price' => (float) $po->unit_price,
            'total_amount' => (float) $po->total_amount,
            'signal_amount' => (float) $po->signal_amount,
            'balance_amount' => $po->balance_amount,
            'signal_percentage' => $po->signal_percentage ? (float) $po->signal_percentage : null,

            'expected_arrival_date' => $po->expected_arrival_date?->format('Y-m-d'),
            'signal_due_date' => $po->signal_due_date?->format('Y-m-d'),
            'balance_due_date' => $po->balance_due_date?->format('Y-m-d'),
            'signal_paid_at' => $po->signal_paid_at,
            'balance_paid_at' => $po->balance_paid_at,
            'arrived_at' => $po->arrived_at,
            'shipped_at' => $po->shipped_at,
            'delivered_at' => $po->delivered_at,

            'created_at' => $po->created_at,

            'vinyl' => [
                'title' => $po->vinylStock?->vinylMaster?->title,
                'artist' => $po->vinylStock?->vinylMaster?->artist_names ?? null,
                'cover_url' => $po->vinylStock?->vinylMaster?->cover_url ?? null,
                'format' => $po->vinylStock?->format,
            ],
        ];

        if ($detailed) {
            $data['customer_notes'] = $po->customer_notes;
            $data['shipping_address'] = $po->shipping_address;
            $data['shipping_cost'] = $po->shipping_cost ? (float) $po->shipping_cost : null;
            $data['record_label'] = $po->vinylStock?->vinylMaster?->recordLabel?->name;
        }

        return $data;
    }
}
