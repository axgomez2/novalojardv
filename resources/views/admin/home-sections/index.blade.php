<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestão da Home</h1>
            <p class="mt-1 text-sm text-gray-600">
                Gerencie as seções de discos exibidas na página inicial.
            </p>
        </div>
        <a href="{{ route('admin.home-sections.create') }}" 
           class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nova Seção
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div class="rounded-lg bg-white shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Ordem</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Seção</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tipo</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Discos</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white" id="sections-list">
                    @forelse($sections as $section)
                        <tr data-id="{{ $section->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 font-semibold text-sm">
                                    {{ $section->sort_order }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $section->name }}</div>
                                @if($section->title)
                                    <div class="text-sm text-gray-500">{{ $section->title }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    @if($section->type === 'discos_novos') bg-green-100 text-green-800
                                    @elseif($section->type === 'pre_venda') bg-purple-100 text-purple-800
                                    @elseif($section->type === 'discos_usados') bg-orange-100 text-orange-800
                                    @elseif($section->type === 'discos_nacionais') bg-blue-100 text-blue-800
                                    @elseif($section->type === 'ofertas') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $section->type_name }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="font-semibold {{ $section->items->count() >= $section->max_items ? 'text-green-600' : 'text-gray-600' }}">
                                    {{ $section->items->count() }}/{{ $section->max_items }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <button type="button" 
                                        onclick="toggleSection({{ $section->id }})"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium cursor-pointer
                                            {{ $section->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                                        id="status-{{ $section->id }}">
                                    {{ $section->is_active ? 'Ativo' : 'Inativo' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.home-sections.edit', $section) }}" 
                                       class="inline-flex items-center gap-1 rounded bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ route('admin.home-sections.destroy', $section) }}" 
                                          onsubmit="return confirm('Excluir esta seção? Os discos serão desvinculados.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 rounded bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Excluir
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">Nenhuma seção cadastrada.</p>
                                <a href="{{ route('admin.home-sections.create') }}" class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Criar primeira seção
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($sections->count() > 0)
        <div class="mt-6 rounded-lg bg-blue-50 border border-blue-200 p-4">
            <h3 class="text-sm font-medium text-blue-800">Dicas de uso:</h3>
            <ul class="mt-2 text-sm text-blue-700 list-disc list-inside space-y-1">
                <li>Clique em <strong>Editar</strong> para adicionar ou remover discos de cada seção</li>
                <li>Clique no status <strong>Ativo/Inativo</strong> para alternar a visibilidade</li>
                <li>Seções inativas não aparecem na home do site</li>
            </ul>
        </div>
    @endif

    @push('scripts')
    <script>
        async function toggleSection(id) {
            try {
                const response = await fetch(`/admin/home-sections/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                const data = await response.json();
                if (data.success) {
                    const btn = document.getElementById(`status-${id}`);
                    if (data.is_active) {
                        btn.textContent = 'Ativo';
                        btn.classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                        btn.classList.add('bg-green-100', 'text-green-800', 'hover:bg-green-200');
                    } else {
                        btn.textContent = 'Inativo';
                        btn.classList.remove('bg-green-100', 'text-green-800', 'hover:bg-green-200');
                        btn.classList.add('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                    }
                }
            } catch (e) {
                console.error(e);
            }
        }
    </script>
    @endpush
</x-admin-layout>
