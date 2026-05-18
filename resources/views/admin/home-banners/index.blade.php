<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Banners da Home</h1>
            <p class="mt-1 text-sm text-gray-600">
                Gerencie até <strong>{{ $max }}</strong> banners exibidos no topo da home.
                Atualmente: <strong>{{ $banners->count() }}/{{ $max }}</strong> cadastrados,
                <strong>{{ $activeCount }}</strong> ativos.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-800">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Form de novo banner --}}
        <div class="lg:col-span-1">
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Novo banner</h2>

                @if(!$canCreate)
                    <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-3 text-sm text-yellow-800">
                        Limite de {{ $max }} banners atingido. Exclua um para adicionar outro.
                    </div>
                @else
                    <form method="POST" action="{{ route('admin.home-banners.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Imagem *</label>
                            <input type="file" name="image" accept="image/*" required
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="mt-1 text-xs text-gray-500">JPG, PNG ou WebP. Máx 4 MB. Recomendado 1920×600 ou 1600×500.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Título <span class="text-gray-400">(opcional)</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" maxlength="120"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Subtítulo <span class="text-gray-400">(opcional)</span></label>
                            <input type="text" name="subtitle" value="{{ old('subtitle') }}" maxlength="200"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Link de ação</label>
                            <input type="url" name="link_url" value="{{ old('link_url') }}" placeholder="https://..."
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Para onde o banner leva quando clicado.</p>
                        </div>

                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="open_in_new_tab" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            Abrir em nova aba
                        </label>

                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            Ativar imediatamente
                        </label>

                        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            Adicionar banner
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Lista --}}
        <div class="lg:col-span-2 space-y-4">
            @forelse($banners as $banner)
                <div class="rounded-lg bg-white shadow overflow-hidden">
                    <form method="POST" action="{{ route('admin.home-banners.update', $banner) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid md:grid-cols-3 gap-0">
                            <div class="relative bg-gray-100">
                                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-full h-full object-cover aspect-[16/9] md:aspect-auto">
                                <span class="absolute left-2 top-2 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $banner->is_active ? 'bg-green-600 text-white' : 'bg-gray-500 text-white' }}">
                                    {{ $banner->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                                <span class="absolute right-2 top-2 rounded-full bg-black/70 px-2 py-0.5 text-xs font-semibold text-white">#{{ $banner->sort_order }}</span>
                            </div>

                            <div class="md:col-span-2 p-4 space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600">Título</label>
                                        <input type="text" name="title" value="{{ $banner->title }}" maxlength="120"
                                               class="mt-1 block w-full rounded border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600">Subtítulo</label>
                                        <input type="text" name="subtitle" value="{{ $banner->subtitle }}" maxlength="200"
                                               class="mt-1 block w-full rounded border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-600">Link de ação</label>
                                    <input type="url" name="link_url" value="{{ $banner->link_url }}" placeholder="https://..."
                                           class="mt-1 block w-full rounded border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-600">Substituir imagem (opcional)</label>
                                    <input type="file" name="image" accept="image/*"
                                           class="mt-1 block w-full text-xs text-gray-500 file:mr-3 file:rounded file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>

                                <div class="flex flex-wrap items-center gap-4 pt-1">
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="open_in_new_tab" value="1" {{ $banner->open_in_new_tab ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        Nova aba
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="is_active" value="1" {{ $banner->is_active ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        Ativo
                                    </label>
                                </div>

                                <div class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-gray-100">
                                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">
                                        Salvar alterações
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="flex items-center justify-end gap-2 border-t border-gray-100 bg-gray-50 px-4 py-2">
                        <form method="POST" action="{{ route('admin.home-banners.toggle', $banner) }}">
                            @csrf
                            <button type="submit" class="rounded px-3 py-1 text-xs font-medium {{ $banner->is_active ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' : 'bg-green-100 text-green-800 hover:bg-green-200' }}">
                                {{ $banner->is_active ? 'Desativar' : 'Ativar' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.home-banners.destroy', $banner) }}"
                              onsubmit="return confirm('Excluir este banner? Esta ação não pode ser desfeita.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded bg-red-100 px-3 py-1 text-xs font-medium text-red-800 hover:bg-red-200">
                                Excluir
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Nenhum banner cadastrado ainda.</p>
                    <p class="text-xs text-gray-400">Adicione o primeiro usando o formulário ao lado.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-admin-layout>
