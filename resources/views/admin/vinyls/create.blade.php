<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.vinyls.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Novo Disco de Vinil</h1>
        <p class="mt-1 text-sm text-gray-600">Etapa 1: Buscar no Discogs</p>
    </div>

    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center">
            <div class="flex items-center">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white">
                    <span class="text-sm font-medium">1</span>
                </div>
                <span class="ml-3 text-sm font-medium text-indigo-600">Buscar no Discogs</span>
            </div>
            <div class="mx-4 h-0.5 flex-1 bg-gray-200"></div>
            <div class="flex items-center">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-gray-500">
                    <span class="text-sm font-medium">2</span>
                </div>
                <span class="ml-3 text-sm font-medium text-gray-500">Dados do Disco</span>
            </div>
            <div class="mx-4 h-0.5 flex-1 bg-gray-200"></div>
            <div class="flex items-center">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-gray-500">
                    <span class="text-sm font-medium">3</span>
                </div>
                <span class="ml-3 text-sm font-medium text-gray-500">Estoque e Preços</span>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Atenção</h3>
                    <p class="mt-1 text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(!$isDiscogsConfigured)
        <div class="mb-6 rounded-lg bg-yellow-50 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Discogs não configurado</h3>
                    <p class="mt-1 text-sm text-yellow-700">
                        Para buscar discos no Discogs, adicione seu token no arquivo <code class="rounded bg-yellow-100 px-1">.env</code>:
                        <br><code class="mt-1 block rounded bg-yellow-100 px-2 py-1">DISCOGS_TOKEN=seu_token_aqui</code>
                    </p>
                    <p class="mt-2 text-sm text-yellow-700">
                        <a href="https://www.discogs.com/settings/developers" target="_blank" class="font-medium underline">Obter token no Discogs</a>
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div x-data="discogsSearch()" class="space-y-6">
        <!-- Search Box -->
        <div class="rounded-lg bg-white p-6 shadow">
            <label for="search" class="block text-sm font-medium text-gray-700">Buscar Disco</label>
            <div class="mt-2 flex gap-4">
                <div class="relative flex-1">
                    <input type="text"
                           id="search"
                           x-model="query"
                           @keydown.enter.prevent="search()"
                           placeholder="Digite o nome do artista ou álbum..."
                           class="block w-full rounded-lg border-gray-300 pr-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           {{ !$isDiscogsConfigured ? 'disabled' : '' }}>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
                <button type="button"
                        @click="search()"
                        :disabled="loading || !query.trim()"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg x-show="loading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? 'Buscando...' : 'Buscar'"></span>
                </button>
            </div>

            {{-- Filtros adicionais --}}
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div>
                    <label for="filter-year" class="block text-xs font-medium text-gray-600">Ano</label>
                    <input type="text" id="filter-year" x-model="filters.year"
                           @keydown.enter.prevent="search()"
                           placeholder="Ex: 1985 ou 1980-1989"
                           class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="filter-label" class="block text-xs font-medium text-gray-600">Gravadora</label>
                    <input type="text" id="filter-label" x-model="filters.label"
                           @keydown.enter.prevent="search()"
                           placeholder="Ex: Blue Note"
                           class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="filter-country" class="block text-xs font-medium text-gray-600">País</label>
                    <input type="text" id="filter-country" x-model="filters.country"
                           @keydown.enter.prevent="search()"
                           placeholder="Ex: Brazil, US, UK"
                           class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div x-show="filters.year || filters.label || filters.country" class="mt-2">
                <button type="button" @click="clearFilters()" class="text-xs text-gray-500 hover:text-gray-700 underline">
                    Limpar filtros
                </button>
            </div>
        </div>

        <!-- Error Message -->
        <div x-show="error" x-cloak class="rounded-lg bg-red-50 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="ml-3 text-sm text-red-700" x-text="error"></p>
            </div>
        </div>

        <!-- Search Results -->
        <div x-show="results.length > 0" x-cloak class="rounded-lg bg-white shadow">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900">Resultados da Busca</h3>
                <p class="mt-1 text-sm text-gray-500">Selecione o disco que deseja cadastrar</p>
            </div>
            <ul class="divide-y divide-gray-200">
                <template x-for="result in results" :key="result.id">
                    <li class="flex items-center gap-4 p-4 hover:bg-gray-50">
                        <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded bg-gray-100">
                            <img x-show="result.cover_image" :src="result.cover_image" :alt="result.title" class="h-full w-full object-cover">
                            <div x-show="!result.cover_image" class="flex h-full items-center justify-center">
                                <svg class="h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                </svg>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-gray-900" x-text="result.title"></p>
                            <p class="mt-1 text-sm text-gray-500">
                                <span x-text="result.year || 'Ano desconhecido'"></span>
                                <span x-show="result.country"> • <span x-text="result.country"></span></span>
                                <span x-show="result.label && result.label[0]"> • <span x-text="result.label[0]"></span></span>
                            </p>
                            <p class="mt-1 text-xs text-gray-400">
                                <span x-show="result.format" x-text="result.format.join(', ')"></span>
                            </p>
                        </div>
                        <a :href="'{{ route('admin.vinyls.create.step2') }}?release_id=' + result.id"
                           class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-100">
                            Selecionar
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </li>
                </template>
            </ul>
            <div x-show="pagination.pages > 1" class="border-t border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <button @click="prevPage()" :disabled="pagination.page <= 1" class="text-sm text-gray-600 hover:text-gray-900 disabled:opacity-50">
                        &larr; Anterior
                    </button>
                    <span class="text-sm text-gray-500">Página <span x-text="pagination.page"></span> de <span x-text="pagination.pages"></span></span>
                    <button @click="nextPage()" :disabled="pagination.page >= pagination.pages" class="text-sm text-gray-600 hover:text-gray-900 disabled:opacity-50">
                        Próxima &rarr;
                    </button>
                </div>
            </div>
        </div>

        <!-- No Results -->
        <div x-show="searched && results.length === 0 && !loading" x-cloak class="rounded-lg bg-white p-8 text-center shadow">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhum resultado encontrado</h3>
            <p class="mt-2 text-sm text-gray-500">Tente buscar com outros termos.</p>
        </div>
    </div>

    <script>
        function discogsSearch() {
            return {
                query: '',
                filters: { year: '', label: '', country: '' },
                results: [],
                loading: false,
                error: null,
                searched: false,
                pagination: {
                    page: 1,
                    pages: 1,
                    items: 0
                },

                clearFilters() {
                    this.filters = { year: '', label: '', country: '' };
                },

                async search(page = 1) {
                    if (!this.query.trim()) return;

                    this.loading = true;
                    this.error = null;
                    this.pagination.page = page;

                    try {
                        const params = new URLSearchParams({
                            query: this.query,
                            page: page,
                        });
                        if (this.filters.year) params.append('year', this.filters.year);
                        if (this.filters.label) params.append('label', this.filters.label);
                        if (this.filters.country) params.append('country', this.filters.country);

                        const response = await fetch(`{{ route('admin.vinyls.discogs.search') }}?${params.toString()}`);
                        const data = await response.json();

                        if (data.error) {
                            this.error = data.error;
                            this.results = [];
                        } else {
                            this.results = data.results || [];
                            this.pagination = {
                                page: data.pagination?.page || 1,
                                pages: data.pagination?.pages || 1,
                                items: data.pagination?.items || 0
                            };
                        }
                        this.searched = true;
                    } catch (e) {
                        this.error = 'Erro ao buscar no Discogs. Tente novamente.';
                        this.results = [];
                    } finally {
                        this.loading = false;
                    }
                },

                prevPage() {
                    if (this.pagination.page > 1) {
                        this.search(this.pagination.page - 1);
                    }
                },

                nextPage() {
                    if (this.pagination.page < this.pagination.pages) {
                        this.search(this.pagination.page + 1);
                    }
                }
            }
        }
    </script>
</x-admin-layout>
