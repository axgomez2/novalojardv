<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.music.dj-playlists.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar para Playlists
        </a>
        <h1 class="mt-4 text-2xl font-bold text-gray-900">Editar: {{ $djPlaylist->dj_name }} - {{ $djPlaylist->title }}</h1>
        <p class="mt-1 text-sm text-gray-600">Atualize as informações e gerencie as faixas da playlist</p>
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

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Formulário de Edição -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Informações do DJ</h2>
                <form method="POST" action="{{ route('admin.music.dj-playlists.update', $djPlaylist) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Título da Playlist *</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $djPlaylist->title) }}" required
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="dj_name" class="block text-sm font-medium text-gray-700">Nome do DJ *</label>
                            <input type="text" name="dj_name" id="dj_name" value="{{ old('dj_name', $djPlaylist->dj_name) }}" required
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-2">
                            <label for="dj_description" class="block text-sm font-medium text-gray-700">Descrição do DJ</label>
                            <textarea name="dj_description" id="dj_description" rows="3"
                                      class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('dj_description', $djPlaylist->dj_description) }}</textarea>
                        </div>

                        <!-- Imagem atual -->
                        @if($djPlaylist->dj_image)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Imagem Atual</label>
                                <div class="flex items-center gap-4">
                                    <img src="{{ $djPlaylist->dj_image_url }}" alt="{{ $djPlaylist->dj_name }}" class="h-20 w-20 rounded-lg object-cover">
                                    <form method="POST" action="{{ route('admin.music.dj-playlists.remove-image', $djPlaylist) }}" onsubmit="return confirm('Remover imagem?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">Remover imagem</button>
                                    </form>
                                </div>
                            </div>
                        @endif

                        <div class="md:col-span-2">
                            <label for="dj_image" class="block text-sm font-medium text-gray-700">{{ $djPlaylist->dj_image ? 'Trocar Foto' : 'Foto do DJ' }}</label>
                            <input type="file" name="dj_image" id="dj_image" accept="image/*"
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>

                        <!-- Redes Sociais -->
                        <div class="md:col-span-2 border-t border-gray-200 pt-4 mt-2">
                            <h3 class="text-sm font-medium text-gray-900 mb-3">Redes Sociais</h3>
                        </div>

                        <div>
                            <label for="instagram" class="block text-sm font-medium text-gray-700">Instagram</label>
                            <input type="url" name="instagram" id="instagram" value="{{ old('instagram', $djPlaylist->instagram) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="soundcloud" class="block text-sm font-medium text-gray-700">SoundCloud</label>
                            <input type="url" name="soundcloud" id="soundcloud" value="{{ old('soundcloud', $djPlaylist->soundcloud) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="spotify" class="block text-sm font-medium text-gray-700">Spotify</label>
                            <input type="url" name="spotify" id="spotify" value="{{ old('spotify', $djPlaylist->spotify) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="youtube" class="block text-sm font-medium text-gray-700">YouTube</label>
                            <input type="url" name="youtube" id="youtube" value="{{ old('youtube', $djPlaylist->youtube) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="facebook" class="block text-sm font-medium text-gray-700">Facebook</label>
                            <input type="url" name="facebook" id="facebook" value="{{ old('facebook', $djPlaylist->facebook) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="twitter" class="block text-sm font-medium text-gray-700">Twitter/X</label>
                            <input type="url" name="twitter" id="twitter" value="{{ old('twitter', $djPlaylist->twitter) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="website" class="block text-sm font-medium text-gray-700">Website</label>
                            <input type="url" name="website" id="website" value="{{ old('website', $djPlaylist->website) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700">Ordem</label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $djPlaylist->sort_order) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $djPlaylist->is_active) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">Ativa</span>
                            </label>

                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $djPlaylist->is_featured) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">Destaque</span>
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
        </div>

        <!-- Gerenciamento de Faixas -->
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Faixas</h2>
                <span class="text-sm text-gray-500">{{ $djPlaylist->tracks->count() }}/10</span>
            </div>

            <!-- Adicionar Faixa -->
            @if($djPlaylist->tracks->count() < 10)
                <div class="mb-4" x-data="trackSearch()">
                    <div class="relative">
                        <input type="text" x-model="query" @input.debounce.300ms="search()" placeholder="Buscar faixa..."
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        
                        <div x-show="results.length > 0" x-cloak class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="track in results" :key="track.id">
                                <form method="POST" action="{{ route('admin.music.dj-playlists.add-track', $djPlaylist) }}" class="block">
                                    @csrf
                                    <input type="hidden" name="track_id" :value="track.id">
                                    <button type="submit" class="w-full px-3 py-2 text-left hover:bg-gray-50 border-b border-gray-100 last:border-0">
                                        <div class="font-medium text-gray-900 text-sm" x-text="track.name"></div>
                                        <div class="text-xs text-gray-500" x-text="track.artist + ' - ' + track.vinyl_title"></div>
                                    </button>
                                </form>
                            </template>
                        </div>
                    </div>
                </div>
            @else
                <div class="mb-4 rounded-lg bg-yellow-50 p-3 text-sm text-yellow-800">
                    Limite de 10 faixas atingido
                </div>
            @endif

            <!-- Lista de Faixas -->
            @if($djPlaylist->tracks->count() > 0)
                <div class="space-y-2">
                    @foreach($djPlaylist->tracks as $track)
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600">
                                    {{ $track->pivot->position }}
                                </span>
                                <div class="min-w-0">
                                    <div class="font-medium text-gray-900 text-sm truncate">{{ $track->name }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ $track->vinylMaster?->artist?->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.music.dj-playlists.remove-track', [$djPlaylist, $track]) }}" onsubmit="return confirm('Remover?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 p-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-lg border-2 border-dashed border-gray-300 p-6 text-center">
                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Nenhuma faixa</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        function trackSearch() {
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
                        const url = `{{ route('admin.music.dj-playlists.search-tracks') }}?q=${encodeURIComponent(this.query)}`;
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
