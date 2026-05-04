<x-admin-layout>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Clientes</h1>
            <p class="mt-1 text-sm text-gray-600">Gerencie os clientes da loja</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 p-4 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="mb-6 rounded-lg bg-white p-4 shadow">
        <form method="GET" action="{{ route('admin.clients.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Buscar por nome ou email..."
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <select name="is_dj" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todos</option>
                <option value="1" {{ request('is_dj') === '1' ? 'selected' : '' }}>Apenas DJs</option>
                <option value="0" {{ request('is_dj') === '0' ? 'selected' : '' }}>Não DJs</option>
            </select>
            <select name="is_active" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todos os status</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Ativos</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inativos</option>
            </select>
            <button type="submit"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                Filtrar
            </button>
            @if(request()->hasAny(['search', 'is_dj', 'is_active']))
                <a href="{{ route('admin.clients.index') }}"
                   class="rounded-lg px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">
                    Limpar
                </a>
            @endif
        </form>
    </div>

    <!-- Clients Table -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Cliente
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Telefone
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                        DJ
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Cadastro
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($clients as $client)
                    <tr>
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full {{ $client->is_dj ? 'bg-purple-200' : 'bg-gray-200' }}">
                                    @if($client->is_dj)
                                        <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                        </svg>
                                    @else
                                        <span class="text-sm font-medium text-gray-600">
                                            {{ strtoupper(substr($client->name, 0, 2)) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $client->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $client->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ $client->formatted_phone ?? '-' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-center">
                            <form method="POST" action="{{ route('admin.clients.toggle-dj', $client) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 {{ $client->is_dj ? 'bg-purple-600' : 'bg-gray-200' }}"
                                        title="{{ $client->is_dj ? 'Desativar DJ' : 'Ativar DJ' }}">
                                    <span class="sr-only">Toggle DJ</span>
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $client->is_dj ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </form>
                            @if($client->is_dj && $client->djPlaylist)
                                <a href="{{ route('admin.music.dj-playlists.edit', $client->djPlaylist) }}" 
                                   class="ml-2 text-xs text-purple-600 hover:text-purple-800"
                                   title="Ver Playlist">
                                    <svg class="inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                    </svg>
                                </a>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            @if ($client->is_active)
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                    Ativo
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800">
                                    Inativo
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ $client->created_at->format('d/m/Y') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.clients.show', $client) }}"
                                   class="text-gray-400 hover:text-gray-600"
                                   title="Ver detalhes">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('admin.clients.toggle-active', $client) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="{{ $client->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }}"
                                            title="{{ $client->is_active ? 'Desativar' : 'Ativar' }}">
                                        @if ($client->is_active)
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            Nenhum cliente encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($clients->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
