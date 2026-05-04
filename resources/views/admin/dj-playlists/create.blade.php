<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.music.dj-playlists.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar para Playlists
        </a>
        <h1 class="mt-4 text-2xl font-bold text-gray-900">Nova Playlist de DJ</h1>
        <p class="mt-1 text-sm text-gray-600">Crie uma nova playlist de um DJ parceiro</p>
    </div>

    <div class="rounded-lg bg-white p-6 shadow">
        <form method="POST" action="{{ route('admin.music.dj-playlists.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Vincular a um Cliente DJ -->
                @if($availableDjs->count() > 0 || $selectedClient)
                <div class="md:col-span-2 rounded-lg bg-purple-50 p-4 border border-purple-200">
                    <h3 class="text-lg font-medium text-purple-900 mb-2">
                        <svg class="inline h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Vincular a um Cliente DJ
                    </h3>
                    <p class="text-sm text-purple-700 mb-3">Vincule esta playlist a um cliente com permissão de DJ. O cliente poderá editar os discos da playlist pelo frontend.</p>
                    
                    <select name="client_user_id" id="client_user_id" 
                            class="block w-full rounded-lg border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                            onchange="fillDjInfo(this)">
                        <option value="">-- Não vincular (playlist independente) --</option>
                        @foreach($availableDjs as $dj)
                            <option value="{{ $dj->id }}" 
                                    data-name="{{ $dj->name }}" 
                                    data-email="{{ $dj->email }}"
                                    {{ (old('client_user_id') == $dj->id || ($selectedClient && $selectedClient->id == $dj->id)) ? 'selected' : '' }}>
                                {{ $dj->name }} ({{ $dj->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Informações da Playlist -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informações da Playlist</h3>
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Título da Playlist *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                           placeholder="Ex: Top 10 House Music"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="dj_name" class="block text-sm font-medium text-gray-700">Nome do DJ *</label>
                    <input type="text" name="dj_name" id="dj_name" value="{{ old('dj_name', $selectedClient?->name) }}" required
                           placeholder="Ex: DJ Alex"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('dj_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="dj_description" class="block text-sm font-medium text-gray-700">Descrição do DJ</label>
                    <textarea name="dj_description" id="dj_description" rows="3"
                              placeholder="Uma breve descrição sobre o DJ..."
                              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('dj_description') }}</textarea>
                    @error('dj_description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="dj_image" class="block text-sm font-medium text-gray-700">Foto do DJ</label>
                    <input type="file" name="dj_image" id="dj_image" accept="image/*"
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    @error('dj_image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Redes Sociais -->
                <div class="md:col-span-2 border-t border-gray-200 pt-6 mt-2">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Redes Sociais</h3>
                </div>

                <div>
                    <label for="instagram" class="block text-sm font-medium text-gray-700">Instagram</label>
                    <input type="url" name="instagram" id="instagram" value="{{ old('instagram') }}"
                           placeholder="https://instagram.com/dj_alex"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="facebook" class="block text-sm font-medium text-gray-700">Facebook</label>
                    <input type="url" name="facebook" id="facebook" value="{{ old('facebook') }}"
                           placeholder="https://facebook.com/djalex"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="twitter" class="block text-sm font-medium text-gray-700">Twitter/X</label>
                    <input type="url" name="twitter" id="twitter" value="{{ old('twitter') }}"
                           placeholder="https://twitter.com/dj_alex"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="soundcloud" class="block text-sm font-medium text-gray-700">SoundCloud</label>
                    <input type="url" name="soundcloud" id="soundcloud" value="{{ old('soundcloud') }}"
                           placeholder="https://soundcloud.com/djalex"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="spotify" class="block text-sm font-medium text-gray-700">Spotify</label>
                    <input type="url" name="spotify" id="spotify" value="{{ old('spotify') }}"
                           placeholder="https://open.spotify.com/artist/..."
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="youtube" class="block text-sm font-medium text-gray-700">YouTube</label>
                    <input type="url" name="youtube" id="youtube" value="{{ old('youtube') }}"
                           placeholder="https://youtube.com/@djalex"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700">Website</label>
                    <input type="url" name="website" id="website" value="{{ old('website') }}"
                           placeholder="https://djalex.com"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Configurações -->
                <div class="md:col-span-2 border-t border-gray-200 pt-6 mt-2">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Configurações</h3>
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700">Ordem de Exibição</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-gray-700">Playlist Ativa</span>
                    </label>

                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-gray-700">Destaque</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-4">
                <a href="{{ route('admin.music.dj-playlists.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Criar Playlist
                </button>
            </div>
        </form>
    </div>

    <script>
        function fillDjInfo(select) {
            const option = select.options[select.selectedIndex];
            const djNameInput = document.getElementById('dj_name');
            
            if (option.value && option.dataset.name) {
                djNameInput.value = option.dataset.name;
            }
        }

        // Preencher ao carregar se já tiver selecionado
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('client_user_id');
            if (select && select.value) {
                fillDjInfo(select);
            }
        });
    </script>
</x-admin-layout>
