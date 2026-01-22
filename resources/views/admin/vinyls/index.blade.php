<x-admin-layout>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Discos de Vinil</h1>
            <p class="mt-1 text-sm text-gray-600">Gerencie o catálogo de discos</p>
        </div>
        <a href="{{ route('admin.vinyls.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Novo Disco
        </a>
    </div>

    <!-- Filters -->
    <div class="mb-6 rounded-lg bg-white p-4 shadow">
        <form method="GET" action="{{ route('admin.vinyls.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[200px] flex-1">
                <label class="block text-xs font-medium text-gray-500">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Título ou artista..."
                       class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="w-24">
                <label class="block text-xs font-medium text-gray-500">Ano</label>
                <input type="number" name="year" value="{{ request('year') }}" placeholder="Ano" min="1900" max="{{ date('Y') + 1 }}"
                       class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-500">Categoria</label>
                <select name="category" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todas</option>
                    @foreach($parentCategories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-500">Disponibilidade</label>
                <select name="availability" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todas</option>
                    <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Disponível</option>
                    <option value="unavailable" {{ request('availability') == 'unavailable' ? 'selected' : '' }}>Indisponível</option>
                    <option value="featured" {{ request('availability') == 'featured' ? 'selected' : '' }}>Destaque</option>
                    <option value="preorder" {{ request('availability') == 'preorder' ? 'selected' : '' }}>Pré-venda</option>
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filtrar</button>
            @if(request()->hasAny(['search', 'year', 'category', 'availability']))
                <a href="{{ route('admin.vinyls.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">Limpar</a>
            @endif
        </form>
    </div>

    <!-- Vinyl List -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
        @if($vinyls->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Disco</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Ano</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Custo</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Venda</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Promo</th>
                            <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Estoque</th>
                            <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($vinyls as $vinyl)
                            @php
                                $stock = $vinyl->stocks->first();
                                $isPreorder = $stock && $stock->availability === 'preorder';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded bg-gray-100">
                                            @if($vinyl->cover_url)
                                                <img src="{{ $vinyl->cover_url }}" alt="{{ $vinyl->title }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full items-center justify-center">
                                                    <svg class="h-6 w-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.vinyls.show', $vinyl) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                                {{ Str::limit($vinyl->title, 35) }}
                                            </a>
                                            <p class="text-sm text-indigo-600">{{ Str::limit($vinyl->artist_names ?: 'Artista Desconhecido', 30) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                    {{ $vinyl->release_year ?: '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-500">
                                    @if($stock && $stock->cost_price)
                                        R$ {{ number_format($stock->cost_price, 2, ',', '.') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-gray-900">
                                    @if($stock && $stock->sell_price)
                                        R$ {{ number_format($stock->sell_price, 2, ',', '.') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                    @if($stock && $stock->promotional_price && $stock->is_promotional)
                                        <span class="font-medium text-green-600">R$ {{ number_format($stock->promotional_price, 2, ',', '.') }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-center text-sm">
                                    @if($stock)
                                        <span class="font-medium {{ $stock->stock > 0 ? ($stock->stock <= $stock->stock_min ? 'text-yellow-600' : 'text-gray-900') : 'text-red-600' }}">
                                            {{ $stock->stock }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-center">
                                    @if($stock)
                                        @if($isPreorder)
                                            <div class="flex flex-col items-center">
                                                <span class="inline-flex rounded-full bg-purple-100 px-2 py-0.5 text-xs font-semibold text-purple-800">Pré-venda</span>
                                                @if($stock->release_date)
                                                    <span class="mt-0.5 text-xs text-gray-500">{{ $stock->release_date->format('d/m/Y') }}</span>
                                                @endif
                                            </div>
                                        @elseif($stock->availability === 'featured')
                                            <span class="inline-flex rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-800">Destaque</span>
                                        @elseif($stock->availability === 'available')
                                            <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">Disponível</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800">Indisponível</span>
                                        @endif
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">Sem estoque</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.vinyls.tracks.index', $vinyl) }}" class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="Faixas">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.vinyls.images.index', $vinyl) }}" class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="Imagens">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.vinyls.show', $vinyl) }}" class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="Ver">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.vinyls.edit', $vinyl) }}" class="rounded p-1 text-indigo-600 hover:bg-indigo-50 hover:text-indigo-900" title="Editar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.vinyls.destroy', $vinyl) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este disco?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded p-1 text-red-600 hover:bg-red-50 hover:text-red-900" title="Excluir">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhum disco cadastrado</h3>
                <p class="mt-2 text-sm text-gray-500">Comece adicionando seu primeiro disco de vinil.</p>
                <a href="{{ route('admin.vinyls.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Adicionar Disco
                </a>
            </div>
        @endif
    </div>

    @if ($vinyls->hasPages())
        <div class="mt-6">{{ $vinyls->links() }}</div>
    @endif
</x-admin-layout>
