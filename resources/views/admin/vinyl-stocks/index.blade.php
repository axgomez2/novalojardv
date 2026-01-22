<x-admin-layout>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Estoque de Discos</h1>
            <p class="mt-1 text-sm text-gray-600">Gerencie o estoque e preços dos discos</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.vinyl-stocks.report') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Relatório
            </a>
            <a href="{{ route('admin.vinyl-stocks.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Novo Estoque
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Total de Itens</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($stats['total_items']) }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Unidades em Estoque</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($stats['total_stock']) }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Valor em Estoque</p>
            <p class="mt-1 text-2xl font-bold text-green-600">R$ {{ number_format($stats['total_value'], 2, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Estoque Baixo</p>
            <p class="mt-1 text-2xl font-bold text-yellow-600">{{ number_format($stats['low_stock_count']) }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Sem Estoque</p>
            <p class="mt-1 text-2xl font-bold text-red-600">{{ number_format($stats['out_of_stock']) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 rounded-lg bg-white p-4 shadow">
        <form method="GET" action="{{ route('admin.vinyl-stocks.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por título, código ou barcode..."
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <select name="availability" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Disponibilidade</option>
                <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Disponível</option>
                <option value="unavailable" {{ request('availability') == 'unavailable' ? 'selected' : '' }}>Indisponível</option>
                <option value="featured" {{ request('availability') == 'featured' ? 'selected' : '' }}>Destaque</option>
                <option value="preorder" {{ request('availability') == 'preorder' ? 'selected' : '' }}>Pré-venda</option>
            </select>
            <select name="is_new" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Condição</option>
                <option value="1" {{ request('is_new') === '1' ? 'selected' : '' }}>Novo</option>
                <option value="0" {{ request('is_new') === '0' ? 'selected' : '' }}>Usado</option>
            </select>
            <select name="store_section" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tipo de Disco</option>
                <option value="dj" {{ request('store_section') == 'dj' ? 'selected' : '' }}>DJ</option>
                <option value="albums" {{ request('store_section') == 'albums' ? 'selected' : '' }}>Álbum</option>
            </select>
            <select name="supplier_id" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Fornecedor</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                <span class="text-sm text-gray-700">Estoque baixo</span>
            </label>
            <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filtrar</button>
            @if(request()->hasAny(['search', 'availability', 'is_new', 'store_section', 'supplier_id', 'low_stock']))
                <a href="{{ route('admin.vinyl-stocks.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">Limpar</a>
            @endif
        </form>
    </div>

    <!-- Stock List -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
        @if($stocks->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Disco</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Condição</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Estoque</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Custo</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Venda</th>
                        <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Adicionar</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white" x-data>
                    @foreach ($stocks as $stock)
                        <tr class="hover:bg-gray-50 {{ $stock->isLowStock() ? 'bg-yellow-50' : '' }} {{ $stock->isOutOfStock() ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded bg-gray-100">
                                        @if($stock->vinylMaster->cover_url)
                                            <img src="{{ $stock->vinylMaster->cover_url }}" alt="{{ $stock->vinylMaster->title }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full items-center justify-center">
                                                <svg class="h-6 w-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.vinyl-stocks.show', $stock) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                            {{ $stock->vinylMaster->title }}
                                        </a>
                                        <p class="text-sm text-indigo-600">{{ $stock->vinylMaster->artist_names ?: 'Artista Desconhecido' }}</p>
                                        @if($stock->internal_code)
                                            <p class="text-xs text-gray-400">{{ $stock->internal_code }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $stock->store_section == 'dj' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $stock->store_section == 'dj' ? 'DJ' : 'Álbum' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $stock->is_new ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                    {{ $stock->condition_label }}
                                </span>
                                @if($stock->mediaStatus)
                                    <p class="mt-1 text-xs text-gray-500">{{ $stock->mediaStatus->name }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-{{ $stock->availability_color }}-100 text-{{ $stock->availability_color }}-800">
                                    {{ $stock->availability_label }}
                                </span>
                                @if($stock->isOnPromotion())
                                    <span class="mt-1 block text-xs text-red-600 font-medium">Em promoção</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <span class="text-lg font-bold {{ $stock->isOutOfStock() ? 'text-red-600' : ($stock->isLowStock() ? 'text-yellow-600' : 'text-gray-900') }}">
                                    {{ $stock->stock }}
                                </span>
                                @if($stock->stock_min > 0)
                                    <p class="text-xs text-gray-400">min: {{ $stock->stock_min }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-500">
                                {{ $stock->formatted_cost_price }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                @if($stock->isOnPromotion())
                                    <span class="text-sm text-gray-400 line-through">{{ $stock->formatted_sell_price }}</span>
                                    <span class="block text-sm font-bold text-red-600">R$ {{ number_format($stock->promotional_price, 2, ',', '.') }}</span>
                                @else
                                    <span class="text-sm font-medium text-gray-900">{{ $stock->formatted_sell_price }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <button type="button" 
                                        @click="$dispatch('open-quick-add', { stockId: {{ $stock->id }}, stockTitle: '{{ addslashes($stock->vinylMaster->title) }}', currentStock: {{ $stock->stock }}, costPrice: {{ $stock->cost_price ?? 0 }}, supplierId: {{ $stock->supplier_id ?? 'null' }} })"
                                        class="inline-flex items-center gap-1 rounded bg-green-100 px-3 py-1.5 text-sm font-medium text-green-700 hover:bg-green-200" title="Entrada Rápida">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Entrada
                                </button>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.vinyl-stocks.show', $stock) }}" class="text-gray-400 hover:text-gray-600" title="Ver">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.vinyl-stocks.edit', $stock) }}" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhum estoque cadastrado</h3>
                <p class="mt-2 text-sm text-gray-500">Comece adicionando estoque para seus discos.</p>
                <a href="{{ route('admin.vinyl-stocks.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Adicionar Estoque
                </a>
            </div>
        @endif
    </div>

    @if ($stocks->hasPages())
        <div class="mt-6">{{ $stocks->links() }}</div>
    @endif

    <!-- Quick Add Stock Modal -->
    <div x-data="{ 
            open: false, 
            stockId: null, 
            stockTitle: '', 
            currentStock: 0,
            costPrice: 0,
            supplierId: null,
            quantity: 1,
            totalValue: 0,
            updateTotal() {
                this.totalValue = (this.quantity * this.costPrice).toFixed(2);
            }
        }" 
        @open-quick-add.window="
            open = true; 
            stockId = $event.detail.stockId; 
            stockTitle = $event.detail.stockTitle;
            currentStock = $event.detail.currentStock;
            costPrice = $event.detail.costPrice;
            supplierId = $event.detail.supplierId;
            quantity = 1;
            updateTotal();
        "
        x-show="open" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto">
        
        <div class="flex min-h-screen items-center justify-center p-4">
            <!-- Backdrop -->
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 @click="open = false" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

            <!-- Modal -->
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative w-full max-w-lg transform rounded-lg bg-white shadow-xl">
                
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Entrada Rápida de Estoque</h3>
                    <p class="mt-1 text-sm text-gray-500" x-text="stockTitle"></p>
                </div>

                <form :action="'/admin/vinyl-stocks/' + stockId + '/quick-add-stock'" method="POST" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <!-- Current Stock Info -->
                        <div class="rounded-lg bg-gray-50 p-3">
                            <p class="text-sm text-gray-600">Estoque atual: <span class="font-semibold text-gray-900" x-text="currentStock"></span> unidades</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <!-- Quantity -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Quantidade *</label>
                                <input type="number" name="quantity" x-model="quantity" @input="updateTotal()" min="1" required
                                       class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Cost Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Valor Unitário (R$) *</label>
                                <input type="number" name="cost_price" x-model="costPrice" @input="updateTotal()" step="0.01" min="0" required
                                       class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- Total Value Display -->
                        <div class="rounded-lg bg-green-50 p-3">
                            <p class="text-sm text-green-700">Valor total da entrada: <span class="font-bold text-green-800">R$ <span x-text="totalValue"></span></span></p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <!-- Supplier -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fornecedor</label>
                                <select name="supplier_id" x-model="supplierId"
                                        class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Selecione...</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Purchase Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Data da Compra</label>
                                <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}"
                                       class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- Invoice Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nº Nota Fiscal / Referência</label>
                            <input type="text" name="invoice_number" placeholder="Ex: NF-12345"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Observações</label>
                            <textarea name="notes" rows="2" placeholder="Observações sobre a compra..."
                                      class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="open = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                            Registrar Entrada
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
