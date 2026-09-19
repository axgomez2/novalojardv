<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.vinyls.create') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar à Busca
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Cadastro Manual de Disco</h1>
        <p class="mt-1 text-sm text-gray-600">Cadastre um disco que não está no Discogs</p>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Erro ao cadastrar</h3>
                    <ul class="mt-2 list-disc list-inside text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.vinyls.store.manual') }}" enctype="multipart/form-data" class="space-y-6" x-data="manualVinylForm()">
        @csrf

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Cover Image -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 rounded-lg bg-white p-4 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Imagem de Capa</h3>
                    
                    <div class="aspect-square overflow-hidden rounded-lg bg-gray-100 mb-4" x-ref="previewContainer">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" alt="Preview" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!imagePreview">
                            <div class="flex h-full items-center justify-center">
                                <svg class="h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload de Imagem *</label>
                        <input type="file" name="cover_image" accept="image/*" required
                               @change="handleImageUpload($event)"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG ou WebP. Máx 4 MB. Recomendado 600×600.</p>
                    </div>
                </div>
            </div>

            <!-- Main Info -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Basic Info -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Informações Básicas</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="title" class="block text-sm font-medium text-gray-700">Título *</label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="release_year" class="block text-sm font-medium text-gray-700">Ano de Lançamento</label>
                            <input type="number" name="release_year" id="release_year" value="{{ old('release_year') }}" min="1900" max="{{ date('Y') + 1 }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700">País</label>
                            <input type="text" name="country" id="country" value="{{ old('country', 'Brazil') }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="description" id="description" rows="4"
                                      class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Artist Selection -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Artista</h3>
                    
                    <div class="space-y-4">
                        <!-- Search existing artist -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar artista existente</label>
                            <div class="relative">
                                <input type="text" 
                                       x-model="artistSearch"
                                       @input.debounce.300ms="searchArtists()"
                                       @focus="showArtistDropdown = true"
                                       placeholder="Digite o nome do artista..."
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                
                                <!-- Dropdown de resultados -->
                                <div x-show="showArtistDropdown && artistResults.length > 0" 
                                     x-cloak
                                     @click.away="showArtistDropdown = false"
                                     class="absolute z-10 mt-1 w-full rounded-lg bg-white shadow-lg border border-gray-200 max-h-60 overflow-auto">
                                    <template x-for="artist in artistResults" :key="artist.id">
                                        <button type="button"
                                                @click="selectArtist(artist)"
                                                class="w-full px-4 py-2 text-left text-sm hover:bg-indigo-50 flex items-center gap-2">
                                            <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <span x-text="artist.name"></span>
                                            <span class="text-xs text-gray-400">(já cadastrado)</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Selected artist or new artist input -->
                        <div x-show="selectedArtist" x-cloak class="rounded-lg border border-green-200 bg-green-50 p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-200">
                                        <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900" x-text="selectedArtist?.name"></p>
                                        <p class="text-xs text-green-600">Artista já cadastrado no sistema</p>
                                    </div>
                                </div>
                                <button type="button" @click="clearArtist()" class="text-sm text-red-600 hover:text-red-800">
                                    Remover
                                </button>
                            </div>
                            <input type="hidden" name="artist_id" :value="selectedArtist?.id">
                        </div>

                        <!-- New artist options -->
                        <div x-show="!selectedArtist" class="space-y-4">
                            <div class="border-t border-gray-200 pt-4">
                                <p class="text-sm font-medium text-gray-700 mb-3">Ou cadastre um novo artista:</p>
                                
                                <!-- Option 1: Search Discogs for artist -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-600 mb-2">Buscar no Discogs</label>
                                    <div class="flex gap-2">
                                        <input type="text" 
                                               x-model="discogsArtistSearch"
                                               placeholder="Nome do artista no Discogs..."
                                               class="flex-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <button type="button" 
                                                @click="searchDiscogsArtist()"
                                                :disabled="loadingDiscogs || !discogsArtistSearch.trim()"
                                                class="inline-flex items-center gap-1 rounded-lg bg-gray-600 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 disabled:opacity-50">
                                            <svg x-show="loadingDiscogs" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            <span x-text="loadingDiscogs ? 'Buscando...' : 'Buscar'"></span>
                                        </button>
                                    </div>
                                    
                                    <!-- Discogs artist results -->
                                    <div x-show="discogsArtistResults.length > 0" x-cloak class="mt-2 rounded-lg border border-gray-200 divide-y divide-gray-200">
                                        <template x-for="artist in discogsArtistResults" :key="artist.id">
                                            <button type="button"
                                                    @click="selectDiscogsArtist(artist)"
                                                    class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-3">
                                                <img x-show="artist.thumb" :src="artist.thumb" class="h-8 w-8 rounded-full object-cover bg-gray-100">
                                                <div x-show="!artist.thumb" class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                </div>
                                                <span x-text="artist.title"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Option 2: Manual artist name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-2">Ou digite o nome manualmente</label>
                                    <input type="text" 
                                           name="artist_name"
                                           x-model="manualArtistName"
                                           placeholder="Nome do artista..."
                                           class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <p class="mt-1 text-xs text-gray-500">O artista será criado automaticamente se não existir.</p>
                                </div>
                                
                                <!-- Hidden field for discogs artist -->
                                <input type="hidden" name="artist_discogs_id" :value="selectedDiscogsArtist?.id">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Record Label -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Gravadora</h3>
                    <div>
                        <label for="record_label_id" class="block text-sm font-medium text-gray-700">Selecione a gravadora</label>
                        <select name="record_label_id" id="record_label_id"
                                class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecione...</option>
                            @foreach($recordLabels as $label)
                                <option value="{{ $label->id }}" {{ old('record_label_id') == $label->id ? 'selected' : '' }}>{{ $label->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Se a gravadora não existir, cadastre-a primeiro em Gravadoras.</p>
                    </div>
                </div>

                <!-- Genres & Styles -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Gêneros e Estilos</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="genres" class="block text-sm font-medium text-gray-700">Gêneros</label>
                            <input type="text" name="genres" id="genres" value="{{ old('genres') }}"
                                   placeholder="Ex: Electronic, House, Techno"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Separe por vírgula</p>
                        </div>
                        <div>
                            <label for="styles" class="block text-sm font-medium text-gray-700">Estilos</label>
                            <input type="text" name="styles" id="styles" value="{{ old('styles') }}"
                                   placeholder="Ex: Deep House, Minimal"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Separe por vírgula</p>
                        </div>
                    </div>
                </div>

                <!-- Note about tracks -->
                <div class="rounded-lg bg-blue-50 border border-blue-200 p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-blue-800">Sobre as faixas</h4>
                            <p class="mt-1 text-sm text-blue-700">As faixas podem ser adicionadas posteriormente no gerenciador de faixas, após o cadastro do disco.</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('admin.vinyls.create') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Próximo: Estoque e Preços
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        function manualVinylForm() {
            return {
                imagePreview: null,
                artistSearch: '',
                artistResults: [],
                showArtistDropdown: false,
                selectedArtist: null,
                discogsArtistSearch: '',
                discogsArtistResults: [],
                loadingDiscogs: false,
                selectedDiscogsArtist: null,
                manualArtistName: '',

                handleImageUpload(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.imagePreview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                async searchArtists() {
                    if (this.artistSearch.length < 2) {
                        this.artistResults = [];
                        return;
                    }
                    try {
                        const response = await fetch(`{{ route('admin.artists.search') }}?q=${encodeURIComponent(this.artistSearch)}`);
                        const data = await response.json();
                        this.artistResults = data.artists || [];
                    } catch (e) {
                        console.error('Erro ao buscar artistas:', e);
                        this.artistResults = [];
                    }
                },

                selectArtist(artist) {
                    this.selectedArtist = artist;
                    this.showArtistDropdown = false;
                    this.artistSearch = '';
                    this.artistResults = [];
                    this.manualArtistName = '';
                    this.selectedDiscogsArtist = null;
                },

                clearArtist() {
                    this.selectedArtist = null;
                },

                async searchDiscogsArtist() {
                    if (!this.discogsArtistSearch.trim()) return;
                    
                    this.loadingDiscogs = true;
                    this.discogsArtistResults = [];
                    
                    try {
                        const response = await fetch(`{{ route('admin.vinyls.discogs.search') }}?query=${encodeURIComponent(this.discogsArtistSearch)}&type=artist`);
                        const data = await response.json();
                        this.discogsArtistResults = data.results || [];
                    } catch (e) {
                        console.error('Erro ao buscar no Discogs:', e);
                    } finally {
                        this.loadingDiscogs = false;
                    }
                },

                selectDiscogsArtist(artist) {
                    this.selectedDiscogsArtist = artist;
                    this.manualArtistName = artist.title;
                    this.discogsArtistResults = [];
                    this.discogsArtistSearch = '';
                }
            }
        }
    </script>
</x-admin-layout>
