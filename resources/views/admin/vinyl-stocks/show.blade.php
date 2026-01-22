<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.vinyl-stocks.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar
        </a>
        <div class="mt-2 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">{{ $vinylStock->vinylMaster->full_title }}</h1>
            <a href="{{ route('admin.vinyl-stocks.edit', $vinylStock) }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Editar
            </a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Left Column -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Main Info -->
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex gap-6">
                    <div class="h-32 w-32 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100">
                        @if($vinylStock->vinylMaster->cover_url)
                            <img src="{{ $vinylStock->vinylMaster->cover_url }}" alt="{{ $vinylStock->vinylMaster->title }}" class="h-full w-full object-cover">
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium {{ $vinylStock->is_new ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                {{ $vinylStock->condition_label }}
                            </span>
                            <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium {{ $vinylStock->store_section == 'dj' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $vinylStock->store_section == 'dj' ? 'DJ' : 'Álbum' }}
                            </span>
                            <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium bg-{{ $vinylStock->availability_color }}-100 text-{{ $vinylStock->availability_color }}-800">
                                {{ $vinylStock->availability_label }}
                            </span>
                            @if($vinylStock->isOnPromotion())
                                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-800">Em Promoção</span>
                            @endif
                        </div>
                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            @if($vinylStock->internal_code)
                                <div>
                                    <dt class="text-sm text-gray-500">Código Interno</dt>
                                    <dd class="font-medium text-gray-900">{{ $vinylStock->internal_code }}</dd>
                                </div>
                            @endif
                            @if($vinylStock->barcode)
                                <div>
                                    <dt class="text-sm text-gray-500">Código de Barras</dt>
                                    <dd class="font-medium text-gray-900">{{ $vinylStock->barcode }}</dd>
                                </div>
                            @endif
                            @if($vinylStock->format)
                                <div>
                                    <dt class="text-sm text-gray-500">Formato</dt>
                                    <dd class="font-medium text-gray-900">{{ $vinylStock->format }} {{ $vinylStock->speed ? "/ {$vinylStock->speed}" : '' }}</dd>
                                </div>
                            @endif
                            @if($vinylStock->color)
                                <div>
                                    <dt class="text-sm text-gray-500">Cor</dt>
                                    <dd class="font-medium text-gray-900">{{ $vinylStock->color }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Prices -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Preços</h3>
                <div class="grid gap-4 sm:grid-cols-4">
                    <div class="rounded-lg bg-gray-50 p-4 text-center">
                        <p class="text-sm text-gray-500">Custo</p>
                        <p class="mt-1 text-xl font-bold text-gray-900">{{ $vinylStock->formatted_cost_price }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4 text-center">
                        <p class="text-sm text-gray-500">Venda</p>
                        <p class="mt-1 text-xl font-bold text-gray-900">{{ $vinylStock->formatted_sell_price }}</p>
                    </div>
                    <div class="rounded-lg {{ $vinylStock->isOnPromotion() ? 'bg-red-50' : 'bg-gray-50' }} p-4 text-center">
                        <p class="text-sm text-gray-500">Promocional</p>
                        <p class="mt-1 text-xl font-bold {{ $vinylStock->isOnPromotion() ? 'text-red-600' : 'text-gray-400' }}">
                            {{ $vinylStock->promotional_price ? 'R$ ' . number_format($vinylStock->promotional_price, 2, ',', '.') : '-' }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-green-50 p-4 text-center">
                        <p class="text-sm text-gray-500">Margem</p>
                        <p class="mt-1 text-xl font-bold text-green-600">
                            {{ $vinylStock->profit_margin ? number_format($vinylStock->profit_margin, 1) . '%' : '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Categories -->
            @if($vinylStock->categories->count() > 0)
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Categorias</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($vinylStock->categories as $category)
                            <span class="inline-flex items-center gap-1 rounded-full {{ $category->pivot->is_primary ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800' }} px-3 py-1 text-sm">
                                {{ $category->full_path }}
                                @if($category->pivot->is_primary)
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Stock Movements -->
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Histórico de Movimentações</h3>
                </div>
                @if($vinylStock->stockMovements->count() > 0)
                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Data</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Tipo</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Qtd</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Valor Unit.</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Usuário</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($vinylStock->stockMovements->take(20) as $movement)
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-{{ $movement->type_color }}-100 text-{{ $movement->type_color }}-800">
                                                {{ $movement->type_label }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-center text-sm font-medium {{ $movement->isEntry() ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $movement->isEntry() ? '+' : '' }}{{ $movement->quantity }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-500">{{ $movement->formatted_unit_price }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-gray-900">{{ $movement->formatted_total_price }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $movement->user?->name ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Nenhuma movimentação registrada.</p>
                @endif
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Stock Card -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Estoque</h3>
                <div class="text-center">
                    <p class="text-5xl font-bold {{ $vinylStock->isOutOfStock() ? 'text-red-600' : ($vinylStock->isLowStock() ? 'text-yellow-600' : 'text-green-600') }}">
                        {{ $vinylStock->stock }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500">unidades</p>
                    @if($vinylStock->stock_min > 0)
                        <p class="mt-2 text-xs text-gray-400">Mínimo: {{ $vinylStock->stock_min }}</p>
                    @endif
                </div>

                <!-- Add Stock Form -->
                <form method="POST" action="{{ route('admin.vinyl-stocks.add-stock', $vinylStock) }}" class="mt-6 space-y-3 border-t border-gray-200 pt-4">
                    @csrf
                    <p class="text-sm font-medium text-gray-700">Adicionar Estoque</p>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="sr-only">Quantidade</label>
                            <input type="number" name="quantity" value="1" min="1" placeholder="Qtd" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="sr-only">Valor Unitário</label>
                            <input type="number" name="unit_price" step="0.01" min="0" placeholder="R$ Unit." class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                    </div>
                    <input type="text" name="reference" placeholder="Referência (NF, etc)" class="w-full rounded-lg border-gray-300 text-sm">
                    <button type="submit" class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                        Adicionar
                    </button>
                </form>

                <!-- Adjust Stock Form -->
                <form method="POST" action="{{ route('admin.vinyl-stocks.adjust-stock', $vinylStock) }}" class="mt-4 space-y-3 border-t border-gray-200 pt-4">
                    @csrf
                    <p class="text-sm font-medium text-gray-700">Ajustar Estoque</p>
                    <input type="number" name="new_stock" value="{{ $vinylStock->stock }}" min="0" class="w-full rounded-lg border-gray-300 text-sm">
                    <input type="text" name="notes" placeholder="Motivo do ajuste" class="w-full rounded-lg border-gray-300 text-sm">
                    <button type="submit" class="w-full rounded-lg bg-yellow-600 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-700">
                        Ajustar
                    </button>
                </form>
            </div>

            <!-- Stock Value -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Valor em Estoque</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Valor (custo)</span>
                        <span class="font-medium text-gray-900">R$ {{ number_format($vinylStock->stock_value, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Valor (venda)</span>
                        <span class="font-medium text-gray-900">R$ {{ number_format($vinylStock->stock * $vinylStock->sell_price, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-3">
                        <span class="text-sm text-gray-500">Custo médio</span>
                        <span class="font-medium text-gray-900">
                            {{ $vinylStock->average_cost_price ? 'R$ ' . number_format($vinylStock->average_cost_price, 2, ',', '.') : '-' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Detalhes</h3>
                <dl class="space-y-3 text-sm">
                    @if($vinylStock->mediaStatus)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Estado Mídia</dt>
                            <dd class="font-medium text-gray-900">{{ $vinylStock->mediaStatus->name }}</dd>
                        </div>
                    @endif
                    @if($vinylStock->coverStatus)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Estado Capa</dt>
                            <dd class="font-medium text-gray-900">{{ $vinylStock->coverStatus->name }}</dd>
                        </div>
                    @endif
                    @if($vinylStock->supplier)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Fornecedor</dt>
                            <dd class="font-medium text-gray-900">{{ $vinylStock->supplier->name }}</dd>
                        </div>
                    @endif
                    @if($vinylStock->weight)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Peso</dt>
                            <dd class="font-medium text-gray-900">{{ $vinylStock->weight->name }}</dd>
                        </div>
                    @endif
                    @if($vinylStock->edition)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Edição</dt>
                            <dd class="font-medium text-gray-900">{{ $vinylStock->edition }}</dd>
                        </div>
                    @endif
                    @if($vinylStock->num_discs > 1)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Nº Discos</dt>
                            <dd class="font-medium text-gray-900">{{ $vinylStock->num_discs }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            @if($vinylStock->notes)
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Observações</h3>
                    <p class="text-sm text-gray-600">{{ $vinylStock->notes }}</p>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
