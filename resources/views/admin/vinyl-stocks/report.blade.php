<x-admin-layout>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Relatório de Estoque</h1>
            <p class="mt-1 text-sm text-gray-600">Análise completa de estoque e valores</p>
        </div>
        <a href="{{ route('admin.vinyl-stocks.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar
        </a>
    </div>

    <!-- Period Filter -->
    <div class="mb-6 rounded-lg bg-white p-4 shadow">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Data Inicial</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="mt-1 rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Data Final</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="mt-1 rounded-lg border-gray-300 text-sm">
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Filtrar</button>
        </form>
    </div>

    <!-- General Stats -->
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg bg-white p-6 shadow">
            <p class="text-sm text-gray-500">Total de Itens</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($stats['total_items']) }}</p>
            <div class="mt-2 flex gap-4 text-xs">
                <span class="text-green-600">{{ $stats['new_items'] }} novos</span>
                <span class="text-orange-600">{{ $stats['used_items'] }} usados</span>
            </div>
        </div>
        <div class="rounded-lg bg-white p-6 shadow">
            <p class="text-sm text-gray-500">Unidades em Estoque</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($stats['total_stock']) }}</p>
            <div class="mt-2 flex gap-4 text-xs">
                <span class="text-yellow-600">{{ $stats['low_stock_count'] }} baixo</span>
                <span class="text-red-600">{{ $stats['out_of_stock'] }} zerado</span>
            </div>
        </div>
        <div class="rounded-lg bg-white p-6 shadow">
            <p class="text-sm text-gray-500">Valor em Estoque (Custo)</p>
            <p class="mt-2 text-3xl font-bold text-blue-600">R$ {{ number_format($stats['total_stock_value'], 2, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-white p-6 shadow">
            <p class="text-sm text-gray-500">Valor em Estoque (Venda)</p>
            <p class="mt-2 text-3xl font-bold text-green-600">R$ {{ number_format($stats['total_sell_value'], 2, ',', '.') }}</p>
            <p class="mt-2 text-xs text-gray-500">Lucro potencial: <span class="font-medium text-green-600">R$ {{ number_format($stats['potential_profit'], 2, ',', '.') }}</span></p>
        </div>
    </div>

    <!-- Price Stats -->
    <div class="mb-6 rounded-lg bg-white p-6 shadow">
        <h3 class="mb-4 text-lg font-medium text-gray-900">Estatísticas de Preços</h3>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-lg bg-gray-50 p-4 text-center">
                <p class="text-sm text-gray-500">Custo Médio</p>
                <p class="mt-1 text-xl font-bold text-gray-900">R$ {{ number_format($priceStats['avg_cost_price'], 2, ',', '.') }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-4 text-center">
                <p class="text-sm text-gray-500">Venda Média</p>
                <p class="mt-1 text-xl font-bold text-gray-900">R$ {{ number_format($priceStats['avg_sell_price'], 2, ',', '.') }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-4 text-center">
                <p class="text-sm text-gray-500">Menor Preço</p>
                <p class="mt-1 text-xl font-bold text-gray-900">R$ {{ number_format($priceStats['min_sell_price'], 2, ',', '.') }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-4 text-center">
                <p class="text-sm text-gray-500">Maior Preço</p>
                <p class="mt-1 text-xl font-bold text-gray-900">R$ {{ number_format($priceStats['max_sell_price'], 2, ',', '.') }}</p>
            </div>
            <div class="rounded-lg bg-green-50 p-4 text-center">
                <p class="text-sm text-gray-500">Margem Média</p>
                <p class="mt-1 text-xl font-bold text-green-600">{{ number_format($priceStats['avg_margin'], 1) }}%</p>
            </div>
        </div>
    </div>

    <!-- Movement Stats -->
    <div class="mb-6 rounded-lg bg-white p-6 shadow">
        <h3 class="mb-4 text-lg font-medium text-gray-900">Movimentações no Período</h3>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-green-200 bg-green-50 p-4">
                <p class="text-sm text-green-700">Compras (Entradas)</p>
                <p class="mt-1 text-2xl font-bold text-green-800">+{{ number_format($movementStats['purchases']) }}</p>
                <p class="mt-1 text-sm text-green-600">R$ {{ number_format($movementStats['purchases_value'], 2, ',', '.') }}</p>
            </div>
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                <p class="text-sm text-blue-700">Vendas (Saídas)</p>
                <p class="mt-1 text-2xl font-bold text-blue-800">-{{ number_format($movementStats['sales']) }}</p>
                <p class="mt-1 text-sm text-blue-600">R$ {{ number_format($movementStats['sales_value'], 2, ',', '.') }}</p>
            </div>
            <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                <p class="text-sm text-yellow-700">Ajustes</p>
                <p class="mt-1 text-2xl font-bold text-yellow-800">{{ number_format($movementStats['adjustments']) }}</p>
            </div>
            <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                <p class="text-sm text-red-700">Perdas</p>
                <p class="mt-1 text-2xl font-bold text-red-800">-{{ number_format($movementStats['losses']) }}</p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Top by Value -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Top 10 por Valor em Estoque</h3>
            @if($topByValue->count() > 0)
                <div class="space-y-3">
                    @foreach($topByValue as $item)
                        <div class="flex items-center justify-between rounded-lg border border-gray-100 p-3">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900">{{ $item->vinylMaster->title }}</p>
                                <p class="text-xs text-gray-500">{{ $item->stock }} un. × R$ {{ number_format($item->cost_price ?? $item->sell_price, 2, ',', '.') }}</p>
                            </div>
                            <p class="ml-4 font-bold text-green-600">R$ {{ number_format($item->stock_value, 2, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">Nenhum item encontrado.</p>
            @endif
        </div>

        <!-- Low Stock -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Itens com Estoque Baixo</h3>
            @if($lowStockItems->count() > 0)
                <div class="space-y-3">
                    @foreach($lowStockItems as $item)
                        <div class="flex items-center justify-between rounded-lg border border-yellow-200 bg-yellow-50 p-3">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900">{{ $item->vinylMaster->title }}</p>
                                <p class="text-xs text-gray-500">Mínimo: {{ $item->stock_min }}</p>
                            </div>
                            <p class="ml-4 text-lg font-bold {{ $item->stock == 0 ? 'text-red-600' : 'text-yellow-600' }}">{{ $item->stock }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">Nenhum item com estoque baixo.</p>
            @endif
        </div>
    </div>

    <!-- By Supplier -->
    @if($bySupplier->count() > 0)
        <div class="mt-6 rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Por Fornecedor</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Fornecedor</th>
                            <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Itens</th>
                            <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Estoque</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($bySupplier as $row)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $row->supplier?->name ?? 'Sem fornecedor' }}</td>
                                <td class="px-4 py-3 text-center text-sm text-gray-500">{{ $row->count }}</td>
                                <td class="px-4 py-3 text-center text-sm text-gray-500">{{ number_format($row->total_stock) }}</td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">R$ {{ number_format($row->total_value, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Recent Movements -->
    @if($movements->count() > 0)
        <div class="mt-6 rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Movimentações Recentes</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Data</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Disco</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Tipo</th>
                            <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Qtd</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Valor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Usuário</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($movements->take(20) as $movement)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ Str::limit($movement->vinylStock->vinylMaster->title, 40) }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-{{ $movement->type_color }}-100 text-{{ $movement->type_color }}-800">
                                        {{ $movement->type_label }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-center text-sm font-medium {{ $movement->isEntry() ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $movement->isEntry() ? '+' : '' }}{{ $movement->quantity }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-500">{{ $movement->formatted_total_price }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $movement->user?->name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-admin-layout>
