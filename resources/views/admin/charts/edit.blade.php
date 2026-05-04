<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.music.charts.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar para Charts
        </a>
        <h1 class="mt-4 text-2xl font-bold text-gray-900">Editar Chart: {{ $chart->title }}</h1>
        <p class="mt-1 text-sm text-gray-600">Atualize as informações e gerencie os discos do chart</p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 p-4 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-lg bg-red-50 p-4 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Formulário de Edição -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Informações do Chart</h2>
            <form method="POST" action="{{ route('admin.music.charts.update', $chart) }}">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Título *</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $chart->title) }}" required
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Tipo *</label>
                        <select name="type" id="type" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type', $chart->type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Categoria</label>
                        <select name="category_id" id="category_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecione...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $chart->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="max_tracks" class="block text-sm font-medium text-gray-700">Máximo de Discos *</label>
                        <input type="number" name="max_tracks" id="max_tracks" value="{{ old('max_tracks', $chart->max_tracks) }}" min="1" max="100" required
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $chart->description) }}</textarea>
                    </div>

                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700">Ordem de Exibição</label>
                        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $chart->sort_order) }}"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $chart->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700">Chart Ativo</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>

        <!-- Gerenciamento de Discos -->
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Discos do Chart</h2>
                <span class="text-sm text-gray-500">{{ $chart->vinyls->count() }}/{{ $chart->max_tracks }}</span>
            </div>

            <!-- Adicionar Disco -->
            @if($chart->vinyls->count() < $chart->max_tracks)
                <div class="mb-4" x-data="vinylSearch()">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Adicionar Disco</label>
                    <div class="relative">
                        <input type="text" x-model="query" @input.debounce.300ms="search()" placeholder="Buscar por título, artista ou catálogo..."
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        
                        <div x-show="results.length > 0" x-cloak class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="vinyl in results" :key="vinyl.id">
                                <form method="POST" action="{{ route('admin.music.charts.add-vinyl', $chart) }}" class="block">
                                    @csrf
                                    <input type="hidden" name="vinyl_id" :value="vinyl.id">
                                    <button type="submit" class="w-full px-4 py-2 text-left hover:bg-gray-50 border-b border-gray-100 last:border-0 flex items-center gap-3">
                                        <img :src="vinyl.cover_url || '/images/placeholder-vinyl.png'" class="w-10 h-10 rounded object-cover" :alt="vinyl.title">
                                        <div>
                                            <div class="font-medium text-gray-900" x-text="vinyl.title"></div>
                                            <div class="text-sm text-gray-500" x-text="vinyl.artist"></div>
                                        </div>
                                    </button>
                                </form>
                            </template>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Lista de Discos -->
            @if($chart->vinyls->count() > 0)
                <div class="space-y-2" id="vinyl-list">
                    @foreach($chart->vinyls as $vinyl)
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-600">
                                    {{ $vinyl->pivot->position }}
                                </span>
                                <img src="{{ $vinyl->cover_url ?? '/images/placeholder-vinyl.png' }}" alt="{{ $vinyl->title }}" class="w-12 h-12 rounded object-cover">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $vinyl->title }}</div>
                                    <div class="text-sm text-gray-500">{{ $vinyl->artist_names ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.music.charts.remove-vinyl', [$chart, $vinyl]) }}" onsubmit="return confirm('Remover este disco?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke-width="2" fill="none"/>
                        <circle cx="12" cy="12" r="3" stroke-width="2" fill="none"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Nenhum disco adicionado ainda</p>
                    <p class="text-xs text-gray-400">Use a busca acima para adicionar discos</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        function vinylSearch() {
            return {
                query: '',
                results: [],
                loading: false,
                async search() {
                    if (this.query.length < 2) {
                        this.results = [];
                        return;
                    }
                    this.loading = true;
                    try {
                        const url = `{{ route('admin.music.charts.search-vinyls') }}?q=${encodeURIComponent(this.query)}`;
                        console.log('Fetching:', url);
                        const response = await fetch(url, {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        console.log('Response status:', response.status);
                        const data = await response.json();
                        console.log('Data:', data);
                        if (response.ok) {
                            this.results = Array.isArray(data) ? data : [];
                        } else {
                            console.error('Search failed:', response.status, data);
                            this.results = [];
                        }
                    } catch (e) {
                        console.error('Fetch error:', e);
                        this.results = [];
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</x-admin-layout>
