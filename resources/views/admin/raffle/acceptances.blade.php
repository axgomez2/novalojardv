<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.raffle.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Aceites dos Termos</h1>
        <p class="text-sm text-gray-600">Clientes que aceitaram os termos da promoção</p>
    </div>

    <!-- Busca -->
    <div class="mb-6">
        <form method="GET" class="flex gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nome ou email..."
                   class="flex-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                Buscar
            </button>
        </form>
    </div>

    <!-- Lista -->
    <div class="rounded-lg bg-white shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Data do Aceite</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">IP</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Números</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($acceptances as $acceptance)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $acceptance->client->name }}</p>
                                <p class="text-sm text-gray-500">{{ $acceptance->client->email }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">
                                {{ $acceptance->accepted_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 font-mono">
                                {{ $acceptance->ip_address }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @php
                                    $numbersCount = \App\Models\RaffleNumber::where('client_user_id', $acceptance->client_user_id)->active()->count();
                                @endphp
                                <span class="rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-800">
                                    {{ $numbersCount }} número(s)
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('admin.raffle.client', $acceptance->client) }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                                    Ver números
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                Nenhum aceite registrado ainda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($acceptances->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $acceptances->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
