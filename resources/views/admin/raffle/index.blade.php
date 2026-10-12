<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Sorteio</h1>
            <p class="text-sm text-gray-600">{{ $settings->name }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.raffle.settings') }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Configurações
            </a>
            <a href="{{ route('admin.raffle.acceptances') }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Aceites ({{ $stats['total_acceptances'] }})
            </a>
            <a href="{{ route('admin.raffle.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Adicionar Números
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="ml-3 text-sm text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Estatísticas -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Total de Números</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_numbers']) }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Números Ativos</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($stats['active_numbers']) }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Números Cancelados</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($stats['cancelled_numbers']) }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Participantes</p>
            <p class="text-2xl font-bold text-indigo-600">{{ number_format($stats['total_participants']) }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Aceites dos Termos</p>
            <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_acceptances']) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Lista de Números -->
        <div class="lg:col-span-2">
            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 p-4">
                    <form method="GET" class="flex gap-4">
                        <div class="flex-1">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por cliente ou número..."
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <select name="status" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos os status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativos</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelados</option>
                            <option value="winner" {{ request('status') === 'winner' ? 'selected' : '' }}>Ganhadores</option>
                        </select>
                        <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                            Filtrar
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Número</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Cliente</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Descrição</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Data</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($numbers as $number)
                                <tr class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="font-mono text-lg font-bold text-indigo-600">{{ $number->formatted_number }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.raffle.client', $number->client) }}" class="hover:text-indigo-600">
                                            <p class="font-medium text-gray-900">{{ $number->client->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $number->client->email }}</p>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $number->purchase_description ?: '-' }}
                                        @if($number->order_number)
                                            <br><span class="text-xs text-gray-400">Pedido: {{ $number->order_number }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        @if($number->status === 'active')
                                            <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">Ativo</span>
                                        @elseif($number->status === 'cancelled')
                                            <span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">Cancelado</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800">Ganhador</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                        {{ $number->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        @if($number->status === 'active')
                                            <button type="button" 
                                                    onclick="openCancelModal({{ $number->id }})"
                                                    class="text-sm text-red-600 hover:text-red-800">
                                                Cancelar
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        Nenhum número cadastrado ainda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($numbers->hasPages())
                    <div class="border-t border-gray-200 px-4 py-3">
                        {{ $numbers->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Top Participantes -->
        <div>
            <div class="rounded-lg bg-white p-4 shadow">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Top Participantes</h3>
                <div class="space-y-3">
                    @forelse($topParticipants as $index => $participant)
                        <a href="{{ route('admin.raffle.client', $participant->client) }}" 
                           class="flex items-center justify-between rounded-lg border border-gray-100 p-3 hover:bg-gray-50">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full {{ $index < 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600' }} text-sm font-bold">
                                    {{ $index + 1 }}º
                                </span>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $participant->client->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $participant->client->email }}</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-indigo-100 px-3 py-1 text-sm font-bold text-indigo-800">
                                {{ $participant->numbers_count }}
                            </span>
                        </a>
                    @empty
                        <p class="text-center text-gray-500">Nenhum participante ainda.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Cancelamento -->
    <div id="cancelModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeCancelModal()"></div>
            <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Cancelar Número</h3>
                <form id="cancelForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Motivo do cancelamento *</label>
                        <textarea name="reason" rows="3" required
                                  class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Ex: Pedido cancelado, estorno de pagamento..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeCancelModal()" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                            Voltar
                        </button>
                        <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                            Confirmar Cancelamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openCancelModal(numberId) {
            document.getElementById('cancelForm').action = `/admin/raffle/${numberId}/cancel`;
            document.getElementById('cancelModal').classList.remove('hidden');
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').classList.add('hidden');
        }
    </script>
</x-admin-layout>
