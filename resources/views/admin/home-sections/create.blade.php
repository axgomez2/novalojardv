<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.home-sections.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Nova Seção da Home</h1>
        <p class="mt-1 text-sm text-gray-600">Crie uma nova seção para exibir discos na página inicial.</p>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-800">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.home-sections.store') }}" class="rounded-lg bg-white p-6 shadow space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nome da Seção *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       placeholder="Ex: Novidades da Semana"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-gray-500">Nome interno para identificação no painel.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Título (exibido no site)</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}"
                           placeholder="Ex: Novidades"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="subtitle" class="block text-sm font-medium text-gray-700">Subtítulo</label>
                    <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle') }}"
                           placeholder="Ex: Discos recém-chegados"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Tipo de Seção *</label>
                    <select name="type" id="type" required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Define quais discos podem ser adicionados.</p>
                </div>
                <div>
                    <label for="max_items" class="block text-sm font-medium text-gray-700">Máximo de Discos *</label>
                    <input type="number" name="max_items" id="max_items" value="{{ old('max_items', 20) }}" min="1" max="50" required
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label for="view_all_link" class="block text-sm font-medium text-gray-700">Link "Ver Todos"</label>
                <input type="text" name="view_all_link" id="view_all_link" value="{{ old('view_all_link') }}"
                       placeholder="Ex: /discos-novos"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-gray-500">Caminho para a página de listagem completa.</p>
            </div>

            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" checked
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Ativar seção imediatamente
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.home-sections.index') }}" 
                   class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" 
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Criar Seção
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
