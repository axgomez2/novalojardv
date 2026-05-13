<x-admin-layout>
    @php
        $color = $preOrder->status->color();
        $signalOver = $preOrder->isSignalOverdue();
        $balanceOver = $preOrder->isBalanceOverdue();
        $whatsappPhone = preg_replace('/\D/', '', $preOrder->client?->phone ?? '');
    @endphp

    <div x-data="{ cancelOpen: false }" class="mb-6 flex items-start justify-between gap-4">
        <div>
            <a href="{{ route('admin.pre-orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Voltar</a>
            <h1 class="mt-1 text-2xl font-bold text-gray-900 font-mono">{{ $preOrder->code }}</h1>
            <div class="mt-1 flex items-center gap-2">
                <span class="inline-flex items-center rounded-full bg-{{ $color }}-100 px-3 py-1 text-sm font-medium text-{{ $color }}-800">
                    {{ $preOrder->status->label() }}
                </span>
                @if($signalOver)<span class="rounded bg-red-600 px-2 py-0.5 text-xs font-bold text-white">SINAL VENCIDO</span>@endif
                @if($balanceOver)<span class="rounded bg-red-600 px-2 py-0.5 text-xs font-bold text-white">SALDO VENCIDO</span>@endif
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($whatsappPhone)
                <a href="https://wa.me/55{{ $whatsappPhone }}?text={{ urlencode('Olá ' . ($preOrder->client?->name ?? '') . ', sobre sua pré-venda ' . $preOrder->code . '...') }}"
                   target="_blank"
                   class="inline-flex items-center gap-1 rounded-md bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WhatsApp
                </a>
            @endif
            @if(!$preOrder->status->isFinal())
                <button type="button" @click="cancelOpen = true"
                        class="inline-flex items-center gap-1 rounded-md bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-100">
                    Cancelar pré-venda
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Coluna Esquerda: dados --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Resumo --}}
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Resumo</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Cliente</p>
                        <p class="text-sm font-medium text-gray-900">{{ $preOrder->client?->name }}</p>
                        <p class="text-xs text-gray-600">{{ $preOrder->client?->email }}</p>
                        @if($preOrder->client?->phone)
                            <p class="text-xs text-gray-600">{{ $preOrder->client->phone }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Disco</p>
                        <p class="text-sm font-medium text-gray-900">{{ $preOrder->vinylStock?->vinylMaster?->title }}</p>
                        <p class="text-xs text-gray-600">{{ $preOrder->vinylStock?->vinylMaster?->artist_names }}</p>
                        <p class="text-xs text-gray-500">Cód interno: {{ $preOrder->vinylStock?->internal_code }}</p>
                    </div>
                    <div class="sm:col-span-2 grid grid-cols-3 gap-4 border-t pt-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Total</p>
                            <p class="text-lg font-bold text-gray-900">{{ $preOrder->formatted_total }}</p>
                            <p class="text-xs text-gray-500">{{ $preOrder->quantity }}× R$ {{ number_format($preOrder->unit_price, 2, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Sinal ({{ $preOrder->signal_percentage }}%)</p>
                            <p class="text-lg font-bold {{ $preOrder->signal_paid_at ? 'text-green-700' : 'text-yellow-700' }}">{{ $preOrder->formatted_signal }}</p>
                            @if($preOrder->signal_paid_at)
                                <p class="text-xs text-green-600">✓ pago {{ $preOrder->signal_paid_at->format('d/m/Y H:i') }} ({{ $preOrder->signal_payment_method }})</p>
                            @elseif($preOrder->signal_due_date)
                                <p class="text-xs {{ $signalOver ? 'text-red-600 font-bold' : 'text-gray-500' }}">vence {{ $preOrder->signal_due_date->format('d/m/Y') }}</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Saldo</p>
                            <p class="text-lg font-bold {{ $preOrder->balance_paid_at ? 'text-green-700' : 'text-orange-700' }}">{{ $preOrder->formatted_balance }}</p>
                            @if($preOrder->balance_paid_at)
                                <p class="text-xs text-green-600">✓ pago {{ $preOrder->balance_paid_at->format('d/m/Y H:i') }} ({{ $preOrder->balance_payment_method }})</p>
                            @elseif($preOrder->balance_due_date)
                                <p class="text-xs {{ $balanceOver ? 'text-red-600 font-bold' : 'text-gray-500' }}">vence {{ $preOrder->balance_due_date->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Datas e Notas (edit) --}}
            <form method="POST" action="{{ route('admin.pre-orders.update', $preOrder) }}" class="rounded-lg bg-white p-6 shadow">
                @csrf @method('PUT')
                <h3 class="mb-4 text-lg font-medium text-gray-900">Editar dados</h3>
                @if($preOrder->status === \App\Enums\PreOrderStatus::AwaitingSignal)
                    <div class="mb-4 grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Qtde</label>
                            <input type="number" name="quantity" value="{{ $preOrder->quantity }}" min="1" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Preço unit.</label>
                            <input type="number" step="0.01" name="unit_price" value="{{ $preOrder->unit_price }}" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Sinal</label>
                            <input type="number" step="0.01" name="signal_amount" value="{{ $preOrder->signal_amount }}" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm">
                        </div>
                    </div>
                @endif
                <div class="mb-4 grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Vencimento sinal</label>
                        <input type="date" name="signal_due_date" value="{{ $preOrder->signal_due_date?->format('Y-m-d') }}" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Chegada prevista</label>
                        <input type="date" name="expected_arrival_date" value="{{ $preOrder->expected_arrival_date?->format('Y-m-d') }}" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Vencimento saldo</label>
                        <input type="date" name="balance_due_date" value="{{ $preOrder->balance_due_date?->format('Y-m-d') }}" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Observações (cliente)</label>
                        <textarea name="customer_notes" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm">{{ $preOrder->customer_notes }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Notas internas</label>
                        <textarea name="admin_notes" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm">{{ $preOrder->admin_notes }}</textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">Salvar</button>
                </div>
            </form>

            {{-- Histórico --}}
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Linha do tempo</h3>
                <ol class="relative border-l border-gray-200 pl-4">
                    @foreach($preOrder->statusHistories as $h)
                        @php $hcolor = \App\Enums\PreOrderStatus::tryFrom($h->to_status)?->color() ?? 'gray'; @endphp
                        <li class="mb-4 ml-2">
                            <span class="absolute -left-1.5 h-3 w-3 rounded-full bg-{{ $hcolor }}-500"></span>
                            <p class="text-sm font-medium text-gray-900">
                                @if($h->from_status)
                                    {{ \App\Enums\PreOrderStatus::tryFrom($h->from_status)?->label() }} →
                                @endif
                                {{ \App\Enums\PreOrderStatus::tryFrom($h->to_status)?->label() }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $h->created_at->format('d/m/Y H:i') }}
                                @if($h->adminUser) · por {{ $h->adminUser->name }}@endif
                                · {{ $h->triggered_by }}
                            </p>
                            @if($h->note)<p class="mt-1 text-sm text-gray-700">{{ $h->note }}</p>@endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        {{-- Coluna Direita: ações --}}
        <div class="space-y-4">
            @if($preOrder->status === \App\Enums\PreOrderStatus::AwaitingSignal)
                <form method="POST" action="{{ route('admin.pre-orders.mark-signal-paid', $preOrder) }}" class="rounded-lg bg-yellow-50 border border-yellow-200 p-4">
                    @csrf
                    <h4 class="font-medium text-yellow-900">Confirmar sinal (PIX manual)</h4>
                    <p class="mt-1 text-xs text-yellow-700">Use quando cliente pagou por fora do gateway.</p>
                    <textarea name="note" rows="2" placeholder="Observação (opcional)" class="mt-2 w-full rounded-lg border-yellow-300 text-sm shadow-sm"></textarea>
                    <button type="submit" class="mt-2 w-full rounded-md bg-yellow-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-yellow-700">Marcar sinal como pago</button>
                </form>
            @endif

            @if($preOrder->status === \App\Enums\PreOrderStatus::AwaitingBalance)
                <form method="POST" action="{{ route('admin.pre-orders.mark-balance-paid', $preOrder) }}" class="rounded-lg bg-orange-50 border border-orange-200 p-4">
                    @csrf
                    <h4 class="font-medium text-orange-900">Confirmar saldo (PIX manual)</h4>
                    <textarea name="note" rows="2" placeholder="Observação (opcional)" class="mt-2 w-full rounded-lg border-orange-300 text-sm shadow-sm"></textarea>
                    <button type="submit" class="mt-2 w-full rounded-md bg-orange-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-orange-700">Marcar saldo como pago</button>
                </form>
            @endif

            @if(!empty($allowedTransitions))
                <form method="POST" action="{{ route('admin.pre-orders.status', $preOrder) }}" class="rounded-lg bg-white p-4 shadow">
                    @csrf
                    <h4 class="font-medium text-gray-900">Avançar status</h4>
                    <select name="status" class="mt-2 w-full rounded-lg border-gray-300 text-sm shadow-sm">
                        @foreach($allowedTransitions as $t)
                            <option value="{{ $t->value }}">{{ $t->label() }}</option>
                        @endforeach
                    </select>
                    <textarea name="note" rows="2" placeholder="Observação (opcional)" class="mt-2 w-full rounded-lg border-gray-300 text-sm shadow-sm"></textarea>
                    <button type="submit" class="mt-2 w-full rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">Atualizar status</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Modal cancelar --}}
    <div x-show="cancelOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <form method="POST" action="{{ route('admin.pre-orders.cancel', $preOrder) }}" class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            @csrf
            <h3 class="text-lg font-medium text-gray-900">Cancelar pré-venda</h3>
            <p class="mt-1 text-sm text-gray-600">Esta ação não pode ser desfeita.</p>
            <label class="mt-4 block text-sm font-medium text-gray-700">Motivo *</label>
            <textarea name="cancellation_reason" rows="3" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm"></textarea>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" @click="cancelOpen = false" class="rounded-md px-4 py-1.5 text-sm text-gray-700 hover:bg-gray-100">Voltar</button>
                <button type="submit" class="rounded-md bg-red-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-red-700">Confirmar cancelamento</button>
            </div>
        </form>
    </div>
    </div>
</x-admin-layout>
