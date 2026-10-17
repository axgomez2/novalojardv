<x-admin-layout>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pedidos</h1>
            <p class="mt-1 text-sm text-gray-600">Gerencie os pedidos da loja</p>
        </div>
        <a href="{{ route('admin.orders.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Novo Pedido (PDV)
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 p-4 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <!-- Estatísticas -->
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-lg bg-white p-4 shadow">
            <div class="text-sm font-medium text-gray-500">Total de Pedidos</div>
            <div class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="rounded-lg bg-yellow-50 p-4 shadow">
            <div class="text-sm font-medium text-yellow-600">Aguardando Pagamento</div>
            <div class="mt-1 text-2xl font-bold text-yellow-700">{{ number_format($stats['pending']) }}</div>
        </div>
        <div class="rounded-lg bg-blue-50 p-4 shadow">
            <div class="text-sm font-medium text-blue-600">Em Processamento</div>
            <div class="mt-1 text-2xl font-bold text-blue-700">{{ number_format($stats['processing']) }}</div>
        </div>
        <div class="rounded-lg bg-purple-50 p-4 shadow">
            <div class="text-sm font-medium text-purple-600">Enviados</div>
            <div class="mt-1 text-2xl font-bold text-purple-700">{{ number_format($stats['shipped']) }}</div>
        </div>
        <div class="rounded-lg bg-green-50 p-4 shadow">
            <div class="text-sm font-medium text-green-600">Receita Hoje</div>
            <div class="mt-1 text-2xl font-bold text-green-700">R$ {{ number_format($stats['today_revenue'], 2, ',', '.') }}</div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="mb-6 rounded-lg bg-white p-4 shadow">
        <form method="GET" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nº pedido, cliente..."
                       class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todos</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Aguardando Pagamento</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Pago</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Em Processamento</option>
                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Enviado</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Entregue</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Origem</label>
                <select name="source" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todas</option>
                    <option value="online" {{ request('source') === 'online' ? 'selected' : '' }}>Online</option>
                    <option value="pdv" {{ request('source') === 'pdv' ? 'selected' : '' }}>PDV</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Data Início</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Data Fim</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Filtrar
                </button>
                <a href="{{ route('admin.orders.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de Pedidos -->
    <form id="bulk-form" method="POST" action="{{ route('admin.orders.bulk-destroy') }}">
        @csrf
        
        <!-- Ações em massa -->
        <div id="bulk-actions" class="mb-4 hidden rounded-lg bg-red-50 p-4 border border-red-200">
            <div class="flex items-center justify-between">
                <span class="text-sm text-red-800">
                    <strong id="selected-count">0</strong> pedido(s) selecionado(s)
                </span>
                <button type="submit" onclick="return confirm('Tem certeza que deseja excluir os pedidos selecionados? Esta ação não pode ser desfeita.')"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Excluir Selecionados
                </button>
            </div>
        </div>

    <div class="rounded-lg bg-white shadow">
        @if($orders->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Pedido</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Itens</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Origem</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Data</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-4 py-4">
                                    <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-indigo-600 hover:text-indigo-900">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $order->customer_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $order->customer_email ?? '-' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ $order->items->count() }} {{ $order->items->count() === 1 ? 'item' : 'itens' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ $order->formatted_total }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium
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
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $order->source === 'pdv' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $order->source_label }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900">
                                        Ver detalhes
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $orders->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhum pedido encontrado</h3>
                <p class="mt-2 text-sm text-gray-500">
                    @if(request()->hasAny(['search', 'status', 'source', 'date_from', 'date_to']))
                        Tente ajustar os filtros de busca.
                    @else
                        Os pedidos aparecerão aqui quando forem realizados.
                    @endif
                </p>
            </div>
        @endif
    </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.order-checkbox');
            const bulkActions = document.getElementById('bulk-actions');
            const selectedCount = document.getElementById('selected-count');

            function updateBulkActions() {
                const checked = document.querySelectorAll('.order-checkbox:checked').length;
                selectedCount.textContent = checked;
                bulkActions.classList.toggle('hidden', checked === 0);
            }

            selectAll?.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateBulkActions();
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkActions);
            });
        });
    </script>
</x-admin-layout>
