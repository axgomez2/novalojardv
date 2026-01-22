<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.vinyls.show', $vinyl) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar para o disco
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Imagens do Disco</h1>
        <p class="mt-1 text-sm text-gray-600">{{ $vinyl->full_title }}</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Upload de Imagem Local -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Upload de Imagem</h2>
            <form method="POST" action="{{ route('admin.vinyls.images.store', $vinyl) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Selecionar Imagem *</label>
                    <input type="file" name="image" id="image" accept="image/*" required
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-500">JPG, PNG, GIF ou WebP. Máximo 5MB.</p>
                    @error('image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="alt_text" class="block text-sm font-medium text-gray-700">Texto Alternativo</label>
                    <input type="text" name="alt_text" id="alt_text" value="{{ old('alt_text') }}" placeholder="Descrição da imagem"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_primary" id="is_primary" value="1" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_primary" class="ml-2 block text-sm text-gray-700">Definir como imagem principal</label>
                </div>
                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Enviar Imagem
                </button>
            </form>
        </div>

        <!-- Imagens do Discogs -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Imagens do Discogs</h2>
            @if(count($discogsImages) > 0)
                <div class="grid grid-cols-3 gap-3">
                    @foreach($discogsImages as $img)
                        @php
                            $imgUrl = is_array($img) ? ($img['uri'] ?? $img['uri150'] ?? '') : $img;
                            $thumbUrl = is_array($img) ? ($img['uri150'] ?? $imgUrl) : $img;
                            $alreadyImported = $vinyl->vinylImages->where('url', $imgUrl)->count() > 0;
                        @endphp
                        <div class="group relative aspect-square overflow-hidden rounded-lg bg-gray-100">
                            <img src="{{ $thumbUrl }}" alt="Discogs Image" class="h-full w-full object-cover">
                            @if($alreadyImported)
                                <div class="absolute inset-0 flex items-center justify-center bg-black/50">
                                    <span class="rounded bg-green-500 px-2 py-1 text-xs font-medium text-white">Importada</span>
                                </div>
                            @else
                                <div class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 transition-opacity group-hover:opacity-100">
                                    <form method="POST" action="{{ route('admin.vinyls.images.import-discogs', $vinyl) }}">
                                        @csrf
                                        <input type="hidden" name="url" value="{{ $imgUrl }}">
                                        <button type="submit" class="rounded bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
                                            Importar
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Nenhuma imagem disponível no Discogs</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Imagens Salvas -->
    <div class="mt-6 rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-semibold text-gray-900">Imagens Salvas ({{ $vinyl->vinylImages->count() }})</h2>
        
        @if($vinyl->vinylImages->count() > 0)
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                @foreach($vinyl->vinylImages as $image)
                    <div class="group relative aspect-square overflow-hidden rounded-lg bg-gray-100 ring-2 {{ $image->is_primary ? 'ring-indigo-500' : 'ring-transparent' }}">
                        <img src="{{ $image->full_url }}" alt="{{ $image->alt_text }}" class="h-full w-full object-cover">
                        
                        @if($image->is_primary)
                            <div class="absolute left-2 top-2">
                                <span class="rounded bg-indigo-600 px-2 py-0.5 text-xs font-medium text-white">Principal</span>
                            </div>
                        @endif
                        
                        <div class="absolute right-2 top-2">
                            <span class="rounded bg-gray-900/70 px-2 py-0.5 text-xs text-white">
                                {{ $image->type === 'local' ? 'Local' : 'Discogs' }}
                            </span>
                        </div>
                        
                        <!-- Actions overlay -->
                        <div class="absolute inset-x-0 bottom-0 flex items-center justify-center gap-2 bg-gradient-to-t from-black/70 to-transparent p-3 opacity-0 transition-opacity group-hover:opacity-100">
                            @if(!$image->is_primary)
                                <form method="POST" action="{{ route('admin.vinyls.images.set-primary', [$vinyl, $image]) }}">
                                    @csrf
                                    <button type="submit" class="rounded bg-indigo-600 p-1.5 text-white hover:bg-indigo-700" title="Definir como principal">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.vinyls.images.destroy', [$vinyl, $image]) }}" onsubmit="return confirm('Tem certeza que deseja excluir esta imagem?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded bg-red-600 p-1.5 text-white hover:bg-red-700" title="Excluir">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhuma imagem salva</h3>
                <p class="mt-2 text-sm text-gray-500">Faça upload de uma imagem ou importe do Discogs.</p>
            </div>
        @endif
    </div>
</x-admin-layout>
