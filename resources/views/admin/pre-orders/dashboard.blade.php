<x-admin-layout>
    @php
        // Templates de mensagem WhatsApp por tipo de alerta
        $waTemplate = function ($po, $type) {
            $name = $po->client?->name ?? '';
            $first = explode(' ', trim($name))[0];
            $code = $po->code;
            $title = $po->vinylStock?->vinylMaster?->title ?? 'seu disco';
            return match ($type) {
                'signal_overdue' => "Olá {$first}, tudo bem? Passando pra lembrar do sinal da sua pré-venda {$code} ({$title}) que venceu em " . ($po->signal_due_date?->format('d/m/Y') ?? '') . ". Consegue confirmar o pagamento? Abraço!",
                'signal_due_soon' => "Olá {$first}! Lembrete: o sinal da pré-venda {$code} ({$title}) vence em " . ($po->signal_due_date?->format('d/m/Y') ?? '') . ". Qualquer dúvida me chama!",
                'balance_overdue' => "Olá {$first}, sobre a pré-venda {$code} ({$title}): o saldo está em aberto desde " . ($po->balance_due_date?->format('d/m/Y') ?? '') . ". Pode acertar pra gente liberar o envio?",
                'balance_due_soon' => "Olá {$first}! O saldo da pré-venda {$code} ({$title}) vence em " . ($po->balance_due_date?->format('d/m/Y') ?? '') . ". Aguardo seu retorno!",
                'arrived' => "Olá {$first}! Seu disco {$title} (pré-venda {$code}) chegou! Para liberar o envio, falta apenas o pagamento do saldo: " . ($po->formatted_balance ?? '') . ". Quando puder acertar me avisa!",
                default => "Olá {$first}, sobre sua pré-venda {$code}...",
            };
        };

        $waLink = function ($po, $type) use ($waTemplate) {
            $phone = preg_replace('/\D/', '', $po->client?->phone ?? '');
            if (!$phone) return null;
            return 'https://wa.me/55' . $phone . '?text=' . urlencode($waTemplate($po, $type));
        };
    @endphp

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Painel de Pré-vendas</h1>
            <p class="mt-1 text-sm text-gray-600">Monitoramento e alertas de encomendas com sinal.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.pre-orders.index') }}" class="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow hover:bg-gray-50">Ver todas</a>
            <a href="{{ route('admin.pre-orders.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Nova pré-venda</a>
        </div>
    </div>

    {{-- KPIs principais --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg bg-white p-5 shadow">
            <p class="text-xs text-gray-500 uppercase">Pré-vendas ativas</p>
            <p class="mt-1 text-3xl font-bold text-gray-900">{{ $stats['active'] }}</p>
            <p class="mt-1 text-xs text-gray-500">Valor total: R$ {{ number_format($stats['total_open'], 2, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-yellow-50 p-5 shadow">
            <p class="text-xs text-yellow-700 uppercase">Sinais a receber</p>
            <p class="mt-1 text-3xl font-bold text-yellow-900">R$ {{ number_format($stats['signal_pending'], 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-yellow-700">Soma dos sinais em aberto</p>
        </div>
        <div class="rounded-lg bg-orange-50 p-5 shadow">
            <p class="text-xs text-orange-700 uppercase">Saldos a receber</p>
            <p class="mt-1 text-3xl font-bold text-orange-900">R$ {{ number_format($stats['balance_pending'], 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-orange-700">Soma dos saldos em aberto</p>
        </div>
        <div class="rounded-lg bg-green-50 p-5 shadow">
            <p class="text-xs text-green-700 uppercase">Recebido no mês</p>
            <p class="mt-1 text-3xl font-bold text-green-900">R$ {{ number_format($stats['this_month_signal_paid'] + $stats['this_month_balance_paid'], 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-green-700">Sinais + saldos pagos este mês</p>
        </div>
    </div>

    @if($signalOverdue->isEmpty() && $balanceOverdue->isEmpty() && $signalDueSoon->isEmpty() && $balanceDueSoon->isEmpty() && $arrivedWaitingAction->isEmpty())
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="mt-2 text-sm font-medium text-green-900">Tudo em ordem! Nenhum alerta pendente.</p>
        </div>
    @endif

    @php
        $alertCard = function ($title, $items, $type, $color, $dateAccessor, $dateLabel) use ($waLink) { return compact('title','items','type','color','dateAccessor','dateLabel'); };
        $alerts = [
            ['title' => '🔴 Sinais VENCIDOS (ação urgente)', 'items' => $signalOverdue, 'type' => 'signal_overdue', 'color' => 'red', 'date_key' => 'signal_due_date', 'date_label' => 'Venceu em'],
            ['title' => '🔴 Saldos VENCIDOS (ação urgente)', 'items' => $balanceOverdue, 'type' => 'balance_overdue', 'color' => 'red', 'date_key' => 'balance_due_date', 'date_label' => 'Venceu em'],
            ['title' => '📦 Chegou, aguardando cobrança do saldo', 'items' => $arrivedWaitingAction, 'type' => 'arrived', 'color' => 'purple', 'date_key' => 'arrived_at', 'date_label' => 'Chegou em'],
            ['title' => '⏰ Sinais vencendo em 7 dias', 'items' => $signalDueSoon, 'type' => 'signal_due_soon', 'color' => 'yellow', 'date_key' => 'signal_due_date', 'date_label' => 'Vence em'],
            ['title' => '⏰ Saldos vencendo em 7 dias', 'items' => $balanceDueSoon, 'type' => 'balance_due_soon', 'color' => 'orange', 'date_key' => 'balance_due_date', 'date_label' => 'Vence em'],
        ];
    @endphp

    <div class="grid gap-6 lg:grid-cols-2">
        @foreach($alerts as $alert)
            @if($alert['items']->isNotEmpty())
                <div class="rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-4 py-3 bg-{{ $alert['color'] }}-50">
                        <h3 class="font-medium text-{{ $alert['color'] }}-900">{{ $alert['title'] }} ({{ $alert['items']->count() }})</h3>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @foreach($alert['items'] as $po)
                            @php
                                $date = $po->{$alert['date_key']};
                                $link = $waLink($po, $alert['type']);
                            @endphp
                            <li class="flex items-center gap-3 px-4 py-3">
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('admin.pre-orders.show', $po) }}" class="font-mono text-xs font-medium text-indigo-600 hover:underline">{{ $po->code }}</a>
                                    <p class="truncate text-sm text-gray-900">{{ $po->client?->name }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ $po->vinylStock?->vinylMaster?->title }}</p>
                                </div>
                                <div class="text-right text-xs">
                                    <p class="text-gray-500">{{ $alert['date_label'] }}</p>
                                    <p class="font-medium text-{{ $alert['color'] }}-700">{{ $date?->format('d/m/Y') }}</p>
                                </div>
                                <div class="flex flex-col gap-1">
                                    @if($link)
                                        <a href="{{ $link }}" target="_blank" title="Enviar WhatsApp"
                                           class="rounded bg-green-600 p-1.5 text-white hover:bg-green-700">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach

        {{-- Chegadas previstas --}}
        @if($arrivingSoon->isNotEmpty())
            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 bg-blue-50 px-4 py-3">
                    <h3 class="font-medium text-blue-900">🚚 Chegadas previstas (15 dias) ({{ $arrivingSoon->count() }})</h3>
                </div>
                <ul class="divide-y divide-gray-100">
                    @foreach($arrivingSoon as $po)
                        <li class="flex items-center gap-3 px-4 py-3">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('admin.pre-orders.show', $po) }}" class="font-mono text-xs font-medium text-indigo-600 hover:underline">{{ $po->code }}</a>
                                <p class="truncate text-sm text-gray-900">{{ $po->vinylStock?->vinylMaster?->title }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $po->client?->name }}</p>
                            </div>
                            <div class="text-right text-xs">
                                <p class="text-gray-500">Prevista</p>
                                <p class="font-medium text-blue-700">{{ $po->expected_arrival_date?->format('d/m/Y') }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Distribuição por status --}}
        @if($distribution->isNotEmpty())
            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-4 py-3">
                    <h3 class="font-medium text-gray-900">Distribuição por status</h3>
                </div>
                <ul class="divide-y divide-gray-100">
                    @foreach($distribution as $statusValue => $row)
                        @php $s = \App\Enums\PreOrderStatus::tryFrom($statusValue); @endphp
                        @if($s)
                            <li class="flex items-center gap-3 px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-{{ $s->color() }}-100 px-2 py-0.5 text-xs font-medium text-{{ $s->color() }}-800">
                                    {{ $s->label() }}
                                </span>
                                <div class="flex-1 text-right">
                                    <span class="text-sm font-bold text-gray-900">{{ $row->total }}</span>
                                    <span class="ml-2 text-xs text-gray-500">R$ {{ number_format($row->amount, 2, ',', '.') }}</span>
                                </div>
                                <a href="{{ route('admin.pre-orders.index', ['status' => $statusValue]) }}" class="text-xs text-indigo-600 hover:underline">ver →</a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Atividade recente --}}
        @if($recentActivity->isNotEmpty())
            <div class="rounded-lg bg-white shadow lg:col-span-2">
                <div class="border-b border-gray-200 px-4 py-3">
                    <h3 class="font-medium text-gray-900">Atividade recente</h3>
                </div>
                <ul class="divide-y divide-gray-100">
                    @foreach($recentActivity as $h)
                        @php
                            $from = \App\Enums\PreOrderStatus::tryFrom($h->from_status);
                            $to = \App\Enums\PreOrderStatus::tryFrom($h->to_status);
                        @endphp
                        <li class="flex items-center gap-3 px-4 py-2 text-sm">
                            <span class="text-xs text-gray-400 w-28">{{ $h->created_at->format('d/m H:i') }}</span>
                            @if($h->preOrder)
                                <a href="{{ route('admin.pre-orders.show', $h->preOrder) }}" class="font-mono text-xs text-indigo-600 hover:underline">{{ $h->preOrder->code }}</a>
                            @endif
                            <span class="text-gray-600 text-xs">
                                @if($from){{ $from->label() }} → @endif<strong class="text-gray-900">{{ $to?->label() }}</strong>
                            </span>
                            <span class="flex-1 text-xs text-gray-500 truncate">
                                @if($h->preOrder?->client) · {{ $h->preOrder->client->name }} @endif
                                @if($h->adminUser) · por {{ $h->adminUser->name }} @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-admin-layout>
