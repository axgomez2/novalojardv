<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.music.charts.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar para Charts
        </a>
        <h1 class="mt-4 text-2xl font-bold text-gray-900">Editar Chart: {{ $chart->title }}</h1>
        <p class="mt-1 text-sm text-gray-600">Atualize as informações e gerencie as faixas do chart</p>
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
                        <label for="max_tracks" class="block text-sm font-medium text-gray-700">Máximo de Faixas *</label>
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

        <!-- Gerenciamento de Faixas -->
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Faixas do Chart</h2>
                <span class="text-sm text-gray-500">{{ $chart->tracks->count() }}/{{ $chart->max_tracks }}</span>
            </div>

            <!-- Adicionar Faixa -->
            @if($chart->tracks->count() < $chart->max_tracks)
                <div class="mb-4" x-data="trackSearch()">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Adicionar Faixa</label>
                    <div class="relative">
                        <input type="text" x-model="query" @input.debounce.300ms="search()" placeholder="Buscar por nome da faixa, disco ou artista..."
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        
                        <div x-show="results.length > 0" x-cloak class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="track in results" :key="track.id">
                                <form method="POST" action="{{ route('admin.music.charts.add-track', $chart) }}" class="block">
                                    @csrf
                                    <input type="hidden" name="track_id" :value="track.id">
                                    <button type="submit" class="w-full px-4 py-2 text-left hover:bg-gray-50 border-b border-gray-100 last:border-0">
                                        <div class="font-medium text-gray-900" x-text="track.name"></div>
                                        <div class="text-sm text-gray-500">
                                            <span x-text="track.artist"></span> - <span x-text="track.vinyl_title"></span>
                                            <span class="text-gray-400" x-text="track.duration ? '(' + track.duration + ')' : ''"></span>
                                        </div>
                                    </button>
                                </form>
                            </template>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Lista de Faixas -->
            @if($chart->tracks->count() > 0)
                <div class="space-y-2" id="track-list">
                    @foreach($chart->tracks as $track)
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-600">
                                    {{ $track->pivot->position }}
                                </span>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $track->name }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $track->vinylMaster?->artist?->name ?? 'N/A' }} - {{ $track->vinylMaster?->title ?? 'N/A' }}
                                        @if($track->duration)
                                            <span class="text-gray-400">({{ $track->duration }})</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.music.charts.remove-track', [$chart, $track]) }}" onsubmit="return confirm('Remover esta faixa?')">
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Nenhuma faixa adicionada ainda</p>
                    <p class="text-xs text-gray-400">Use a busca acima para adicionar faixas</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        function trackSearch() {
            return {
                query: '',
                results: [],
                async search() {
                    if (this.query.length < 2) {
                        this.results = [];
                        return;
                    }
                    try {
                        const response = await fetch(`{{ route('admin.music.charts.search-tracks') }}?q=${encodeURIComponent(this.query)}`);
                        this.results = await response.json();
                    } catch (e) {
                        console.error(e);
                    }
                }
            }
        }
    </script>
</x-admin-layout>
