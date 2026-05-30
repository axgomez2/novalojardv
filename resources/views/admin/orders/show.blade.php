<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar para Pedidos
        </a>
        <div class="mt-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pedido #{{ $order->order_number }}</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Criado em {{ $order->created_at->format('d/m/Y H:i') }}
                    @if($order->is_pdv)
                        <span class="ml-2 inline-flex rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-800">PDV</span>
                        @if($order->createdBy)
                            por {{ $order->createdBy->name }}
                        @endif
                    @endif
                </p>
            </div>
            <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium
                @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                @elseif($order->status === 'paid') bg-blue-100 text-blue-800
                @elseif($order->status === 'processing') bg-indigo-100 text-indigo-800
                @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                @elseif($order->status === 'delivered') bg-green-100 text-green-800
                @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ $order->status_label }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-6 rounded-lg bg-red-50 p-4 text-red-800">{{ session('error') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Coluna Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Itens do Pedido -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Itens do Pedido</h2>
                <div class="divide-y divide-gray-200">
                    @foreach($order->items as $item)
                        <div class="flex items-center gap-4 py-4">
                            @php
                                $vinyl = $item->vinylStock?->vinylMaster;
                                $image = $vinyl?->vinylImages?->first();
                            @endphp
                            <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                @if($image)
                                    <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $vinyl->title }}" class="h-full w-full object-cover">
                                @elseif($vinyl?->cover_url)
                                    <img src="{{ $vinyl->cover_url }}" alt="{{ $vinyl->title }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-gray-900">{{ $vinyl?->title ?? 'Produto não encontrado' }}</h3>
                                <p class="text-sm text-gray-500">{{ $vinyl?->artist_names ?? 'Artista desconhecido' }}</p>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500">{{ $item->quantity }}x R$ {{ number_format($item->unit_price, 2, ',', '.') }}</div>
                                <div class="font-medium text-gray-900">R$ {{ number_format($item->total_price, 2, ',', '.') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 border-t border-gray-200 pt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="text-gray-900">R$ {{ number_format($order->subtotal, 2, ',', '.') }}</span>
                    </div>
                    @if($order->discount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Desconto</span>
                            <span class="text-red-600">- R$ {{ number_format($order->discount, 2, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Frete</span>
                        <span class="text-gray-900">R$ {{ number_format($order->shipping_cost, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-2 text-lg font-bold">
                        <span>Total</span>
                        <span>{{ $order->formatted_total }}</span>
                    </div>
                </div>
            </div>

            <!-- Carrinho do Cliente (sincronização) -->
            @if($order->client_user_id && $matchingCartItems > 0)
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-6">
                    <div class="flex items-start gap-3">
                        <svg class="h-6 w-6 flex-shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-amber-900">Atenção: itens duplicados no carrinho do cliente</h3>
                            <p class="mt-1 text-sm text-amber-800">
                                O cliente <strong>{{ $order->clientUser->name }}</strong> ainda tem
                                <strong>{{ $matchingCartItems }}</strong> item(ns) no carrinho que correspondem a este pedido.
                                Como o estoque já foi decrementado ao gerar este pedido, esses itens ficarão "fantasma"
                                (esgotados) no carrinho dele se não forem removidos.
                            </p>
                            <form method="POST" action="{{ route('admin.orders.clear-client-cart', $order) }}" class="mt-4"
                                  onsubmit="return confirm('Remover do carrinho do cliente os itens já incluídos neste pedido?');">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-3 py-2 text-sm font-medium text-white hover:bg-amber-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                                    </svg>
                                    Limpar carrinho do cliente
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Atualizar Status -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Atualizar Status</h2>
                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Novo Status</label>
                            <select name="status" id="status-select" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Aguardando Pagamento</option>
                                <option value="paid" {{ $order->status === 'paid' ? 'selected' : '' }}>Pago</option>
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Em Processamento</option>
                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Enviado</option>
                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Entregue</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                <option value="refunded" {{ $order->status === 'refunded' ? 'selected' : '' }}>Reembolsado</option>
                            </select>
                        </div>
                        <div id="tracking-fields" class="hidden">
                            <label class="block text-sm font-medium text-gray-700">Código de Rastreio</label>
                            <input type="text" name="tracking_code" value="{{ $order->tracking_code }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div id="carrier-field" class="hidden">
                            <label class="block text-sm font-medium text-gray-700">Transportadora</label>
                            <input type="text" name="shipping_carrier" value="{{ $order->shipping_carrier }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Observação</label>
                            <textarea name="notes" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            Atualizar Status
                        </button>
                    </div>
                </form>
            </div>

            <!-- Histórico de Status -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Histórico</h2>
                @if($order->statusHistory->count() > 0)
                    <div class="flow-root">
                        <ul class="-mb-8">
                            @foreach($order->statusHistory->sortByDesc('created_at') as $history)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 ring-8 ring-white">
                                                    <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-gray-900">
                                                        @if($history->from_status)
                                                            {{ $history->from_status_label }} → 
                                                        @endif
                                                        <span class="font-medium">{{ $history->to_status_label }}</span>
                                                    </p>
                                                    @if($history->notes)
                                                        <p class="mt-1 text-sm text-gray-500">{{ $history->notes }}</p>
                                                    @endif
                                                    @if($history->changedBy)
                                                        <p class="text-xs text-gray-400">por {{ $history->changedBy->name }}</p>
                                                    @endif
                                                </div>
                                                <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                    {{ $history->created_at->format('d/m/Y H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Nenhum histórico registrado.</p>
                @endif
            </div>

            <!-- Notificações Enviadas -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Notificações</h2>
                @if($order->notifications->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500">Canal</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500">Tipo</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500">Destinatário</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500">Status</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500">Data</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($order->notifications->sortByDesc('created_at') as $notification)
                                    <tr>
                                        <td class="px-3 py-2">{{ $notification->channel_label }}</td>
                                        <td class="px-3 py-2">{{ $notification->type_label }}</td>
                                        <td class="px-3 py-2 text-gray-500">{{ $notification->recipient }}</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-{{ $notification->status_color }}-100 text-{{ $notification->status_color }}-800">
                                                {{ $notification->status_label }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-gray-500">{{ $notification->created_at->format('d/m H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Nenhuma notificação enviada.</p>
                @endif
            </div>
        </div>

        <!-- Coluna Lateral -->
        <div class="space-y-6">
            <!-- Cliente -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Cliente</h2>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-500">Nome</span>
                        <p class="font-medium text-gray-900">{{ $order->customer_name }}</p>
                    </div>
                    @if($order->customer_email)
                        <div>
                            <span class="text-sm text-gray-500">E-mail</span>
                            <p class="text-gray-900">{{ $order->customer_email }}</p>
                        </div>
                    @endif
                    @if($order->customer_phone)
                        <div>
                            <span class="text-sm text-gray-500">Telefone</span>
                            <p class="text-gray-900">{{ $order->customer_phone }}</p>
                        </div>
                    @endif
                    @if($order->is_guest)
                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">Cliente não cadastrado</span>
                    @endif
                </div>
            </div>

            <!-- Endereço de Entrega -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Entrega</h2>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-500">Método</span>
                        <p class="font-medium text-gray-900">{{ $order->shipping_method ?? 'Não informado' }}</p>
                    </div>
                    @if($order->shipping_address_formatted)
                        <div>
                            <span class="text-sm text-gray-500">Endereço</span>
                            <p class="text-gray-900">{{ $order->shipping_address_formatted }}</p>
                        </div>
                    @endif
                    @if($order->tracking_code)
                        <div>
                            <span class="text-sm text-gray-500">Rastreio</span>
                            <p class="font-medium text-indigo-600">{{ $order->tracking_code }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pagamento -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Pagamento</h2>
                @if($order->lastPayment)
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-gray-500">Método</span>
                            <p class="font-medium text-gray-900">
                                {{ match($order->lastPayment->payment_method) {
                                    'pix' => 'PIX',
                                    'credit_card' => 'Cartão de Crédito',
                                    'debit_card' => 'Cartão de Débito',
                                    'boleto' => 'Boleto',
                                    'bank_transfer' => 'Transferência',
                                    'cash' => 'Dinheiro',
                                    default => $order->lastPayment->payment_method
                                } }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Status</span>
                            <p class="font-medium text-gray-900">
                                {{ match($order->lastPayment->status) {
                                    'pending' => 'Pendente',
                                    'processing' => 'Processando',
                                    'approved' => 'Aprovado',
                                    'declined' => 'Recusado',
                                    'cancelled' => 'Cancelado',
                                    'refunded' => 'Reembolsado',
                                    default => $order->lastPayment->status
                                } }}
                            </p>
                        </div>
                        @if($order->lastPayment->paid_at)
                            <div>
                                <span class="text-sm text-gray-500">Pago em</span>
                                <p class="text-gray-900">{{ $order->lastPayment->paid_at->format('d/m/Y H:i') }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-gray-500">Nenhum pagamento registrado.</p>
                @endif
            </div>

            <!-- Declaração de Conteúdo -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Declaração de Conteúdo</h2>
                @if($order->invoice_number)
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-gray-500">Número</span>
                            <p class="font-medium text-gray-900">{{ $order->invoice_number }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Gerada em</span>
                            <p class="text-gray-900">{{ $order->invoice_generated_at?->format('d/m/Y H:i') }}</p>
                        </div>
                        <a href="{{ route('admin.orders.download-invoice', $order) }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Download PDF
                        </a>
                    </div>
                @else
                    <p class="mb-4 text-sm text-gray-500">Declaração não gerada.</p>
                    <form method="POST" action="{{ route('admin.orders.generate-invoice', $order) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Gerar Declaração
                        </button>
                    </form>
                @endif
            </div>

            <!-- Notas Administrativas -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Notas Administrativas</h2>
                @if($order->admin_notes)
                    <div class="mb-4 whitespace-pre-wrap rounded-lg bg-gray-50 p-3 text-sm text-gray-700">{{ $order->admin_notes }}</div>
                @endif
                <form method="POST" action="{{ route('admin.orders.add-note', $order) }}">
                    @csrf
                    <textarea name="admin_notes" rows="2" placeholder="Adicionar nota..." class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    <button type="submit" class="mt-2 rounded-lg bg-gray-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-700">
                        Adicionar Nota
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('status-select').addEventListener('change', function() {
            const trackingFields = document.getElementById('tracking-fields');
            const carrierField = document.getElementById('carrier-field');
            if (this.value === 'shipped') {
                trackingFields.classList.remove('hidden');
                carrierField.classList.remove('hidden');
            } else {
                trackingFields.classList.add('hidden');
                carrierField.classList.add('hidden');
            }
        });
    </script>
</x-admin-layout>
