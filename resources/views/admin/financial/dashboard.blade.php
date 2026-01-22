<x-admin-layout>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Financeiro</h1>
            <p class="mt-1 text-sm text-gray-600">Visão geral das finanças</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.financial.transactions.create', ['type' => 'payable']) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nova Despesa
            </a>
            <a href="{{ route('admin.financial.transactions.create', ['type' => 'receivable']) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nova Receita
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- A Pagar -->
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">A Pagar (Mês)</p>
                    <p class="mt-1 text-2xl font-bold text-red-600">R$ {{ number_format($totalPayable, 2, ',', '.') }}</p>
                </div>
                <div class="rounded-full bg-red-100 p-3">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">Pago: R$ {{ number_format($totalPaid, 2, ',', '.') }}</p>
        </div>

        <!-- A Receber -->
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">A Receber (Mês)</p>
                    <p class="mt-1 text-2xl font-bold text-green-600">R$ {{ number_format($totalReceivable, 2, ',', '.') }}</p>
                </div>
                <div class="rounded-full bg-green-100 p-3">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">Recebido: R$ {{ number_format($totalReceived, 2, ',', '.') }}</p>
        </div>

        <!-- Vencidos -->
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Vencidos</p>
                    <p class="mt-1 text-2xl font-bold text-yellow-600">R$ {{ number_format($overduePayable + $overdueReceivable, 2, ',', '.') }}</p>
                </div>
                <div class="rounded-full bg-yellow-100 p-3">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">{{ $overdueCount }} transações vencidas</p>
        </div>

        <!-- Saldo -->
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Saldo do Mês</p>
                    @php $balance = $totalReceived - $totalPaid; @endphp
                    <p class="mt-1 text-2xl font-bold {{ $balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        R$ {{ number_format(abs($balance), 2, ',', '.') }}
                        {{ $balance < 0 ? '(-)' : '' }}
                    </p>
                </div>
                <div class="rounded-full {{ $balance >= 0 ? 'bg-green-100' : 'bg-red-100' }} p-3">
                    <svg class="h-6 w-6 {{ $balance >= 0 ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">Receitas - Despesas</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Próximos Vencimentos -->
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Próximos Vencimentos</h2>
                <a href="{{ route('admin.financial.transactions.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Ver todos</a>
            </div>
            @if($dueSoon->count() > 0)
                <div class="space-y-3">
                    @foreach($dueSoon as $transaction)
                        <div class="flex items-center justify-between rounded-lg border p-3 {{ $transaction->is_overdue ? 'border-red-200 bg-red-50' : 'border-gray-200' }}">
                            <div class="flex items-center gap-3">
                                <div class="rounded-full p-2 {{ $transaction->type === 'payable' ? 'bg-red-100' : 'bg-green-100' }}">
                                    <svg class="h-4 w-4 {{ $transaction->type === 'payable' ? 'text-red-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $transaction->type === 'payable' ? 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' : 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ Str::limit($transaction->description, 30) }}</p>
                                    <p class="text-xs text-gray-500">{{ $transaction->due_date->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold {{ $transaction->type === 'payable' ? 'text-red-600' : 'text-green-600' }}">
                                R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-sm text-gray-500 py-8">Nenhum vencimento próximo</p>
            @endif
        </div>

        <!-- Pagamentos Recorrentes -->
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Pagamentos Recorrentes</h2>
                <a href="{{ route('admin.financial.recurring.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Ver todos</a>
            </div>
            @if($recurringDueSoon->count() > 0)
                <div class="space-y-3">
                    @foreach($recurringDueSoon as $recurring)
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3">
                            <div class="flex items-center gap-3">
                                <div class="rounded-full p-2 {{ $recurring->type === 'payable' ? 'bg-red-100' : 'bg-green-100' }}">
                                    <svg class="h-4 w-4 {{ $recurring->type === 'payable' ? 'text-red-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ Str::limit($recurring->name, 30) }}</p>
                                    <p class="text-xs text-gray-500">{{ $recurring->frequency_name }} - Próx: {{ $recurring->next_due_date?->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold {{ $recurring->type === 'payable' ? 'text-red-600' : 'text-green-600' }}">
                                R$ {{ number_format($recurring->amount, 2, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-sm text-gray-500 py-8">Nenhum pagamento recorrente configurado</p>
            @endif
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="mt-6 rounded-lg bg-white p-6 shadow">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Transações Recentes</h2>
            <a href="{{ route('admin.financial.transactions.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Ver todas</a>
        </div>
        @if($recentTransactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Descrição</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Categoria</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Vencimento</th>
                            <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($recentTransactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-2 w-2 rounded-full {{ $transaction->type === 'payable' ? 'bg-red-500' : 'bg-green-500' }}"></span>
                                        <span class="text-sm font-medium text-gray-900">{{ Str::limit($transaction->description, 40) }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $transaction->category?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $transaction->due_date->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                        {{ $transaction->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $transaction->status === 'overdue' || $transaction->is_overdue ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $transaction->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ $transaction->is_overdue && $transaction->status === 'pending' ? 'Vencido' : $transaction->status_name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-semibold {{ $transaction->type === 'payable' ? 'text-red-600' : 'text-green-600' }}">
                                    R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center text-sm text-gray-500 py-8">Nenhuma transação registrada</p>
        @endif
    </div>
</x-admin-layout>
