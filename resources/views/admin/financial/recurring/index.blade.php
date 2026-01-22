<x-admin-layout>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pagamentos Recorrentes</h1>
            <p class="mt-1 text-sm text-gray-600">Gerencie despesas e receitas que se repetem</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.financial.recurring.create', ['type' => 'payable']) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Despesa Recorrente
            </a>
            <a href="{{ route('admin.financial.recurring.create', ['type' => 'receivable']) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Receita Recorrente
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 rounded-lg bg-white p-4 shadow">
        <form method="GET" action="{{ route('admin.financial.recurring.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-32">
                <label class="block text-xs font-medium text-gray-500">Tipo</label>
                <select name="type" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todos</option>
                    <option value="payable" {{ request('type') == 'payable' ? 'selected' : '' }}>A Pagar</option>
                    <option value="receivable" {{ request('type') == 'receivable' ? 'selected' : '' }}>A Receber</option>
                </select>
            </div>
            <div class="w-32">
                <label class="block text-xs font-medium text-gray-500">Status</label>
                <select name="active" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todos</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Ativos</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inativos</option>
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filtrar</button>
            @if(request()->hasAny(['type', 'active']))
                <a href="{{ route('admin.financial.recurring.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Limpar</a>
            @endif
        </form>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        @if($payments->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Nome</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Frequência</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Próx. Vencimento</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Valor</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-2 w-2 rounded-full {{ $payment->type === 'payable' ? 'bg-red-500' : 'bg-green-500' }}"></span>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $payment->name }}</p>
                                        @if($payment->category)
                                            <p class="text-xs text-gray-500">{{ $payment->category->name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $payment->frequency_name }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $payment->next_due_date?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $payment->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $payment->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold {{ $payment->type === 'payable' ? 'text-red-600' : 'text-green-600' }}">
                                R$ {{ number_format($payment->amount, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if($payment->is_active)
                                        <form method="POST" action="{{ route('admin.financial.recurring.generate', $payment) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="rounded p-1 text-blue-600 hover:bg-blue-50" title="Gerar transação">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.financial.recurring.toggle', $payment) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="rounded p-1 {{ $payment->is_active ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50' }}" 
                                                title="{{ $payment->is_active ? 'Desativar' : 'Ativar' }}">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $payment->is_active ? 'M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z' : 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
                                            </svg>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.financial.recurring.edit', $payment) }}" class="rounded p-1 text-indigo-600 hover:bg-indigo-50" title="Editar">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.financial.recurring.destroy', $payment) }}" class="inline" onsubmit="return confirm('Excluir este pagamento recorrente?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded p-1 text-red-600 hover:bg-red-50" title="Excluir">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $payments->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhum pagamento recorrente</h3>
                <p class="mt-2 text-sm text-gray-500">Configure pagamentos que se repetem automaticamente.</p>
            </div>
        @endif
    </div>
</x-admin-layout>
