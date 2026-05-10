<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.vinyls.create') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar à Busca
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Novo Disco de Vinil</h1>
        <p class="mt-1 text-sm text-gray-600">Etapa 2: Revisar e Confirmar Dados</p>
    </div>

    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center">
            <div class="flex items-center">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500 text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="ml-3 text-sm font-medium text-green-600">Buscar no Discogs</span>
            </div>
            <div class="mx-4 h-0.5 flex-1 bg-indigo-600"></div>
            <div class="flex items-center">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white">
                    <span class="text-sm font-medium">2</span>
                </div>
                <span class="ml-3 text-sm font-medium text-indigo-600">Dados do Disco</span>
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

    <form method="POST" action="{{ route('admin.vinyls.store') }}" class="space-y-6">
        @csrf

        <!-- Hidden fields -->
        <input type="hidden" name="discogs_release_id" value="{{ $data['discogs_release_id'] }}">
        <input type="hidden" name="discogs_master_id" value="{{ $data['discogs_master_id'] }}">
        <input type="hidden" name="discogs_url" value="{{ $data['discogs_url'] }}">
        <input type="hidden" name="cover_image" value="{{ $data['cover_image'] }}">

        @foreach($data['images'] ?? [] as $index => $image)
            <input type="hidden" name="images[{{ $index }}][type]" value="{{ $image['type'] }}">
            <input type="hidden" name="images[{{ $index }}][uri]" value="{{ $image['uri'] }}">
        @endforeach

        {{-- Artists fields moved to editable section below --}}

        @foreach($data['labels'] ?? [] as $index => $label)
            <input type="hidden" name="labels[{{ $index }}][discogs_id]" value="{{ $label['discogs_id'] }}">
            <input type="hidden" name="labels[{{ $index }}][name]" value="{{ $label['name'] }}">
        @endforeach

        {{-- Tracklist fields moved to editable section below --}}

        @foreach($data['genres'] ?? [] as $index => $genre)
            <input type="hidden" name="genres[]" value="{{ $genre }}">
        @endforeach

        @foreach($data['styles'] ?? [] as $index => $style)
            <input type="hidden" name="styles[]" value="{{ $style }}">
        @endforeach

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Cover Image -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 rounded-lg bg-white p-4 shadow">
                    <div class="aspect-square overflow-hidden rounded-lg bg-gray-100">
                        @if($data['cover_image'])
                            <img src="{{ $data['cover_image'] }}" alt="{{ $data['title'] }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center">
                                <svg class="h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    @if($data['discogs_url'])
                        <a href="{{ $data['discogs_url'] }}" target="_blank" class="mt-4 flex items-center justify-center gap-2 text-sm text-indigo-600 hover:text-indigo-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Ver no Discogs
                        </a>
                    @endif
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
                            <input type="text" name="title" id="title" value="{{ $data['title'] }}" required
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="release_year" class="block text-sm font-medium text-gray-700">Ano de Lançamento</label>
                            <input type="number" name="release_year" id="release_year" value="{{ $data['year'] }}" min="1900" max="{{ date('Y') + 1 }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700">País</label>
                            <input type="text" name="country" id="country" value="{{ $data['country'] }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="sm:col-span-2" x-data="aiDescription()">
                            <div class="flex items-center justify-between">
                                <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="generate('translate')" :disabled="loading"
                                            class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-100 disabled:opacity-50">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                                        Traduzir/Reescrever
                                    </button>
                                    <button type="button" @click="generate('create')" :disabled="loading"
                                            class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100 disabled:opacity-50">
                                        <svg x-show="!loading" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                        <svg x-show="loading" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        <span x-text="loading ? 'Gerando...' : 'Gerar com IA'"></span>
                                    </button>
                                </div>
                            </div>
                            <textarea name="description" id="description" rows="5" x-ref="textarea"
                                      class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $data['notes'] ?? '' }}</textarea>
                            <p x-show="error" x-cloak class="mt-1 text-xs text-red-600" x-text="error"></p>
                            <p class="mt-1 text-xs text-gray-500">Use "Traduzir/Reescrever" se já houver texto em inglês no campo, ou "Gerar com IA" para criar do zero.</p>

                            <script>
                                function aiDescription() {
                                    return {
                                        loading: false,
                                        error: '',
                                        async generate(mode) {
                                            this.loading = true;
                                            this.error = '';
                                            try {
                                                const payload = {
                                                    title: document.getElementById('title')?.value || @json($data['title'] ?? ''),
                                                    artists: @json(collect($data['artists'] ?? [])->pluck('name')->implode(', ')),
                                                    year: document.getElementById('release_year')?.value || @json((string) ($data['year'] ?? '')),
                                                    country: document.getElementById('country')?.value || @json($data['country'] ?? ''),
                                                    label: @json(collect($data['labels'] ?? [])->pluck('name')->implode(', ')),
                                                    genres: @json(implode(', ', $data['genres'] ?? [])),
                                                    styles: @json(implode(', ', $data['styles'] ?? [])),
                                                    notes: mode === 'translate' ? this.$refs.textarea.value : '',
                                                };
                                                const res = await fetch('{{ route('admin.vinyls.ai.description') }}', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'Accept': 'application/json',
                                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                    },
                                                    body: JSON.stringify(payload),
                                                });
                                                const data = await res.json();
                                                if (!res.ok) {
                                                    this.error = data.error || 'Erro ao gerar descrição.';
                                                    return;
                                                }
                                                this.$refs.textarea.value = data.description || '';
                                            } catch (e) {
                                                this.error = 'Erro de rede: ' + e.message;
                                            } finally {
                                                this.loading = false;
                                            }
                                        }
                                    }
                                }
                            </script>
                        </div>
                    </div>
                </div>

                <!-- Artists (Editable) -->
                @if(!empty($data['artists']))
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Artistas</h3>
                        <span class="text-sm text-gray-500">Você pode editar os nomes (remover aspas, parênteses, etc.)</span>
                    </div>
                    <div class="space-y-3">
                        @foreach($data['artists'] as $index => $artist)
                            @php
                                $existingArtist = $existingArtists[$artist['discogs_id']] ?? null;
                            @endphp
                            <div class="flex items-center gap-3 rounded-lg border {{ $existingArtist ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50' }} p-3">
                                <input type="hidden" name="artists[{{ $index }}][discogs_id]" value="{{ $artist['discogs_id'] }}">
                                <input type="hidden" name="artists[{{ $index }}][role]" value="{{ $artist['role'] ?? 'main' }}">
                                
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full {{ $existingArtist ? 'bg-green-200' : 'bg-gray-200' }}">
                                    <svg class="h-5 w-5 {{ $existingArtist ? 'text-green-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                
                                <div class="flex-1">
                                    <input type="text" 
                                           name="artists[{{ $index }}][name]" 
                                           value="{{ $existingArtist ? $existingArtist['name'] : $artist['name'] }}"
                                           class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           placeholder="Nome do artista">
                                    @if($existingArtist)
                                        <p class="mt-1 text-xs text-green-600">
                                            <svg class="inline h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Artista já cadastrado no sistema
                                        </p>
                                    @else
                                        <p class="mt-1 text-xs text-gray-500">Novo artista - será criado automaticamente</p>
                                    @endif
                                </div>
                                
                                <span class="rounded-full bg-gray-200 px-2 py-1 text-xs text-gray-600">
                                    {{ $artist['role'] ?? 'main' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Label -->
                @if(!empty($data['labels']))
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Gravadora</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($data['labels'] as $label)
                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                {{ $label['name'] }}
                                @if(!empty($label['catno']))
                                    <span class="text-gray-500">({{ $label['catno'] }})</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Genres & Styles -->
                @if(!empty($data['genres']) || !empty($data['styles']))
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Gêneros e Estilos</h3>
                    @if(!empty($data['genres']))
                        <div class="mb-3">
                            <span class="text-sm font-medium text-gray-500">Gêneros:</span>
                            <div class="mt-1 flex flex-wrap gap-2">
                                @foreach($data['genres'] as $genre)
                                    <span class="rounded-full bg-purple-100 px-3 py-1 text-sm text-purple-800">{{ $genre }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if(!empty($data['styles']))
                        <div>
                            <span class="text-sm font-medium text-gray-500">Estilos:</span>
                            <div class="mt-1 flex flex-wrap gap-2">
                                @foreach($data['styles'] as $style)
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-800">{{ $style }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                @endif

                <!-- Tracklist (Editable) -->
                @if(!empty($data['tracklist']))
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Faixas</h3>
                        <span class="text-sm text-gray-500">Edite nomes, duração e adicione links do YouTube</span>
                    </div>
                    <div class="space-y-3">
                        @foreach($data['tracklist'] as $index => $track)
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <input type="hidden" name="tracklist[{{ $index }}][position]" value="{{ $track['position'] }}">
                                <input type="hidden" name="tracklist[{{ $index }}][sort_order]" value="{{ $track['sort_order'] }}">
                                
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded bg-gray-200 text-sm font-medium text-gray-600">
                                        {{ $track['position'] ?: ($index + 1) }}
                                    </span>
                                    <input type="text" 
                                           name="tracklist[{{ $index }}][name]" 
                                           value="{{ $track['name'] }}"
                                           class="flex-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           placeholder="Nome da faixa">
                                    <input type="text" 
                                           name="tracklist[{{ $index }}][duration]" 
                                           value="{{ $track['duration'] }}"
                                           class="w-20 rounded-lg border-gray-300 text-center text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           placeholder="0:00">
                                </div>
                                <div class="mt-2 flex items-center gap-3 pl-11">
                                    <svg class="h-4 w-4 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                    </svg>
                                    <input type="url" 
                                           name="tracklist[{{ $index }}][youtube_url]" 
                                           value=""
                                           class="flex-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           placeholder="https://www.youtube.com/watch?v=...">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

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
</x-admin-layout>
