<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.home-sections.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Editar: {{ $homeSection->name }}</h1>
        <p class="mt-1 text-sm text-gray-600">
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                @if($homeSection->type === 'discos_novos') bg-green-100 text-green-800
                @elseif($homeSection->type === 'pre_venda') bg-purple-100 text-purple-800
                @elseif($homeSection->type === 'discos_usados') bg-orange-100 text-orange-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ $homeSection->type_name }}
            </span>
            &bull; {{ $homeSection->items->count() }}/{{ $homeSection->max_items }} discos
        </p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-800">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Configurações da Seção --}}
        <div class="lg:col-span-1">
            <form method="POST" action="{{ route('admin.home-sections.update', $homeSection) }}" class="rounded-lg bg-white p-6 shadow space-y-4">
                @csrf
                @method('PUT')

                <h2 class="text-lg font-semibold text-gray-900 border-b pb-2">Configurações</h2>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nome *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $homeSection->name) }}" required
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $homeSection->title) }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="subtitle" class="block text-sm font-medium text-gray-700">Subtítulo</label>
                    <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle', $homeSection->subtitle) }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Tipo *</label>
                    <select name="type" id="type" required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" {{ old('type', $homeSection->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="max_items" class="block text-sm font-medium text-gray-700">Máx. Discos *</label>
                    <input type="number" name="max_items" id="max_items" value="{{ old('max_items', $homeSection->max_items) }}" min="1" max="50" required
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="view_all_link" class="block text-sm font-medium text-gray-700">Link "Ver Todos"</label>
                    <input type="text" name="view_all_link" id="view_all_link" value="{{ old('view_all_link', $homeSection->view_all_link) }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" {{ $homeSection->is_active ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Seção ativa
                </label>

                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Salvar Configurações
                </button>
            </form>
        </div>

        {{-- Discos da Seção --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Busca de Discos --}}
            <div class="rounded-lg bg-white p-4 shadow">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Adicionar Discos</h2>
                <div class="relative">
                    <input type="text" id="vinyl-search" placeholder="Buscar por título ou artista..."
                           class="block w-full rounded-lg border-gray-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <div id="search-results" class="mt-3 max-h-64 overflow-y-auto hidden">
                    {{-- Resultados via JS --}}
                </div>
            </div>

            {{-- Lista de Discos Adicionados --}}
            <div class="rounded-lg bg-white shadow overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Discos na Seção</h2>
                    <span class="text-sm text-gray-500" id="items-count">{{ $homeSection->items->count() }}/{{ $homeSection->max_items }}</span>
                </div>
                
                <div id="section-items" class="divide-y divide-gray-200">
                    @forelse($homeSection->items as $item)
                        <div class="flex items-center gap-4 p-4 hover:bg-gray-50" data-item-id="{{ $item->id }}">
                            <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 text-sm font-medium">
                                {{ $item->position }}
                            </span>
                            <img src="{{ $item->vinyl->vinylMaster->cover_url ?? '/placeholder.jpg' }}" 
                                 alt="" class="w-12 h-12 rounded object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 truncate">{{ $item->vinyl->vinylMaster->title ?? 'Sem título' }}</p>
                                <p class="text-sm text-gray-500 truncate">{{ $item->vinyl->vinylMaster->artist_names ?? 'Sem artista' }}</p>
                            </div>
                            <span class="text-sm font-medium text-gray-900">
                                R$ {{ number_format($item->vinyl->current_price, 2, ',', '.') }}
                            </span>
                            <button type="button" onclick="removeItem({{ $item->id }})"
                                    class="flex-shrink-0 p-1.5 rounded-full text-red-600 hover:bg-red-50">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @empty
                        <div class="p-8 text-center" id="empty-message">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Nenhum disco adicionado ainda.</p>
                            <p class="text-xs text-gray-400">Use a busca acima para adicionar discos.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const sectionId = {{ $homeSection->id }};
        const sectionType = '{{ $homeSection->type }}';
        const maxItems = {{ $homeSection->max_items }};
        let currentItems = {{ $homeSection->items->count() }};
        let searchTimeout;

        const searchInput = document.getElementById('vinyl-search');
        const searchResults = document.getElementById('search-results');
        const sectionItems = document.getElementById('section-items');
        const itemsCount = document.getElementById('items-count');
        const emptyMessage = document.getElementById('empty-message');

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchResults.classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(() => searchVinyls(query), 300);
        });

        async function searchVinyls(query) {
            try {
                const response = await fetch(`/admin/home-sections/search-vinyls?q=${encodeURIComponent(query)}&section_id=${sectionId}&type=${sectionType}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const vinyls = await response.json();
                
                if (vinyls.length === 0) {
                    searchResults.innerHTML = '<p class="p-3 text-sm text-gray-500">Nenhum disco encontrado.</p>';
                } else {
                    searchResults.innerHTML = vinyls.map(v => `
                        <div class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0" onclick="addItem(${v.id})">
                            <img src="${v.cover || '/placeholder.jpg'}" alt="" class="w-10 h-10 rounded object-cover">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 text-sm truncate">${v.title}</p>
                                <p class="text-xs text-gray-500 truncate">${v.artist}</p>
                            </div>
                            <span class="text-sm font-medium text-gray-700">${v.price}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full ${v.is_new ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'}">
                                ${v.is_new ? 'Novo' : 'Usado'}
                            </span>
                        </div>
                    `).join('');
                }
                searchResults.classList.remove('hidden');
            } catch (e) {
                console.error(e);
            }
        }

        async function addItem(vinylStockId) {
            if (currentItems >= maxItems) {
                alert('Limite de discos atingido!');
                return;
            }

            try {
                const response = await fetch(`/admin/home-sections/${sectionId}/items`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ vinyl_stock_id: vinylStockId })
                });
                const data = await response.json();
                
                if (data.success) {
                    // Remove empty message if exists
                    if (emptyMessage) emptyMessage.remove();
                    
                    // Add new item to list
                    const item = data.item;
                    const html = `
                        <div class="flex items-center gap-4 p-4 hover:bg-gray-50" data-item-id="${item.id}">
                            <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 text-sm font-medium">
                                ${item.position}
                            </span>
                            <img src="${item.cover || '/placeholder.jpg'}" alt="" class="w-12 h-12 rounded object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 truncate">${item.title}</p>
                                <p class="text-sm text-gray-500 truncate">${item.artist}</p>
                            </div>
                            <span class="text-sm font-medium text-gray-900">${item.price}</span>
                            <button type="button" onclick="removeItem(${item.id})"
                                    class="flex-shrink-0 p-1.5 rounded-full text-red-600 hover:bg-red-50">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    `;
                    sectionItems.insertAdjacentHTML('beforeend', html);
                    
                    currentItems++;
                    itemsCount.textContent = `${currentItems}/${maxItems}`;
                    
                    // Clear search
                    searchInput.value = '';
                    searchResults.classList.add('hidden');
                } else if (data.error) {
                    alert(data.error);
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function removeItem(itemId) {
            if (!confirm('Remover este disco da seção?')) return;

            try {
                const response = await fetch(`/admin/home-sections/${sectionId}/items/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    document.querySelector(`[data-item-id="${itemId}"]`).remove();
                    currentItems--;
                    itemsCount.textContent = `${currentItems}/${maxItems}`;
                    
                    // Show empty message if no items
                    if (currentItems === 0) {
                        sectionItems.innerHTML = `
                            <div class="p-8 text-center" id="empty-message">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">Nenhum disco adicionado ainda.</p>
                            </div>
                        `;
                    }
                }
            } catch (e) {
                console.error(e);
            }
        }
    </script>
    @endpush
</x-admin-layout>
