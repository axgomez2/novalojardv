<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Selos em Destaque</h1>
            <p class="mt-1 text-sm text-gray-600">
                Gerencie até <strong>{{ $max }}</strong> gravadoras em destaque na home.
                Atualmente: <strong>{{ $featuredLabels->count() }}/{{ $max }}</strong> cadastrados.
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
        {{-- Form de novo selo --}}
        <div class="lg:col-span-1">
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Adicionar Selo</h2>

                @if(!$canCreate)
                    <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-3 text-sm text-yellow-800">
                        Limite de {{ $max }} selos atingido. Remova um para adicionar outro.
                    </div>
                @elseif($availableLabels->isEmpty())
                    <div class="rounded-lg bg-blue-50 border border-blue-200 p-3 text-sm text-blue-800">
                        Todas as gravadoras ativas já estão em destaque.
                    </div>
                @else
                    <form method="POST" action="{{ route('admin.featured-labels.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Gravadora *</label>
                            <select name="record_label_id" required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                @foreach($availableLabels as $label)
                                    <option value="{{ $label->id }}">{{ $label->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Logo Customizado <span class="text-gray-400">(opcional)</span></label>
                            <input type="file" name="custom_logo" accept="image/*"
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="mt-1 text-xs text-gray-500">JPG, PNG, WebP ou SVG. Máx 2 MB. Recomendado 200×200.</p>
                            <p class="text-xs text-gray-400">Se não enviar, usará o logo cadastrado na gravadora.</p>
                        </div>

                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            Ativar imediatamente
                        </label>

                        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            Adicionar selo
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Lista --}}
        <div class="lg:col-span-2">
            <div class="rounded-lg bg-white shadow overflow-hidden">
                @if($featuredLabels->isEmpty())
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Nenhum selo em destaque.</p>
                        <p class="text-xs text-gray-400">Adicione o primeiro usando o formulário ao lado.</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4">
                        @foreach($featuredLabels as $featured)
                            <div class="relative group rounded-lg border {{ $featured->is_active ? 'border-green-200 bg-green-50/50' : 'border-gray-200 bg-gray-50' }} p-4 text-center">
                                {{-- Badge de ordem --}}
                                <span class="absolute left-2 top-2 rounded-full bg-gray-800 px-2 py-0.5 text-xs font-semibold text-white">#{{ $featured->sort_order }}</span>
                                
                                {{-- Badge de status --}}
                                <span class="absolute right-2 top-2 rounded-full px-2 py-0.5 text-xs font-semibold {{ $featured->is_active ? 'bg-green-600 text-white' : 'bg-gray-400 text-white' }}">
                                    {{ $featured->is_active ? 'Ativo' : 'Inativo' }}
                                </span>

                                {{-- Logo --}}
                                <div class="w-20 h-20 mx-auto mt-4 mb-3 rounded-lg bg-white border border-gray-200 flex items-center justify-center overflow-hidden">
                                    @if($featured->logo_url)
                                        <img src="{{ $featured->logo_url }}" alt="{{ $featured->recordLabel->name }}" class="max-w-full max-h-full object-contain">
                                    @else
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    @endif
                                </div>

                                {{-- Nome --}}
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $featured->recordLabel->name }}</p>

                                {{-- Ações --}}
                                <div class="mt-3 flex items-center justify-center gap-1">
                                    <form method="POST" action="{{ route('admin.featured-labels.toggle', $featured) }}">
                                        @csrf
                                        <button type="submit" class="rounded px-2 py-1 text-xs font-medium {{ $featured->is_active ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' : 'bg-green-100 text-green-800 hover:bg-green-200' }}">
                                            {{ $featured->is_active ? 'Desativar' : 'Ativar' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.featured-labels.destroy', $featured) }}"
                                          onsubmit="return confirm('Remover este selo dos destaques?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-800 hover:bg-red-200">
                                            Remover
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Dica de ordenação --}}
                    <div class="border-t border-gray-100 bg-gray-50 px-4 py-3 text-center text-xs text-gray-500">
                        A ordem de exibição segue a ordem de cadastro. Para reordenar, remova e adicione novamente.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
