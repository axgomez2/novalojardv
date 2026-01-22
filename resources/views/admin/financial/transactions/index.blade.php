<x-admin-layout>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Transações Financeiras</h1>
            <p class="mt-1 text-sm text-gray-600">Gerencie contas a pagar e a receber</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.financial.transactions.create', ['type' => 'payable']) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Conta a Pagar
            </a>
            <a href="{{ route('admin.financial.transactions.create', ['type' => 'receivable']) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Conta a Receber
            </a>
        </div>
    </div>

    <!-- Summary -->
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg bg-red-50 p-4">
            <p class="text-sm text-red-600">Total a Pagar</p>
            <p class="text-xl font-bold text-red-700">R$ {{ number_format($summary['total_payable'], 2, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-green-50 p-4">
            <p class="text-sm text-green-600">Total a Receber</p>
            <p class="text-xl font-bold text-green-700">R$ {{ number_format($summary['total_receivable'], 2, ',', '.') }}</p>
        </div>
        <div class="rounded-lg bg-yellow-50 p-4">
            <p class="text-sm text-yellow-600">Vencidos</p>
            <p class="text-xl font-bold text-yellow-700">R$ {{ number_format($summary['overdue'], 2, ',', '.') }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 rounded-lg bg-white p-4 shadow">
        <form method="GET" action="{{ route('admin.financial.transactions.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[150px]">
                <label class="block text-xs font-medium text-gray-500">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Descrição..."
                       class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
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
                <select name="status" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todos</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Pago</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Vencido</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-500">Categoria</label>
                <select name="category" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todas</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-500">Data Início</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-500">Data Fim</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filtrar</button>
            @if(request()->hasAny(['search', 'type', 'status', 'category', 'start_date', 'end_date']))
                <a href="{{ route('admin.financial.transactions.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Limpar</a>
            @endif
        </form>
    </div>

    <!-- Transactions List -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
        @if($transactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Descrição</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Categoria</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Vencimento</th>
                            <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Valor</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($transactions as $transaction)
                            <tr class="hover:bg-gray-50 {{ $transaction->is_overdue ? 'bg-red-50' : '' }}">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-2 w-2 rounded-full {{ $transaction->type === 'payable' ? 'bg-red-500' : 'bg-green-500' }}"></span>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ Str::limit($transaction->description, 40) }}</p>
                                            @if($transaction->supplier)
                                                <p class="text-xs text-gray-500">{{ $transaction->supplier->name }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    @if($transaction->category)
                                        <span class="inline-flex items-center gap-1">
                                            <span class="h-2 w-2 rounded-full" style="background-color: {{ $transaction->category->color }}"></span>
                                            {{ $transaction->category->name }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm {{ $transaction->is_overdue ? 'font-semibold text-red-600' : 'text-gray-500' }}">
                                    {{ $transaction->due_date->format('d/m/Y') }}
                                    @if($transaction->payment_date)
                                        <br><span class="text-xs text-green-600">Pago: {{ $transaction->payment_date->format('d/m/Y') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                        {{ $transaction->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $transaction->status === 'pending' && !$transaction->is_overdue ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $transaction->is_overdue ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $transaction->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ $transaction->is_overdue && $transaction->status === 'pending' ? 'Vencido' : $transaction->status_name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-semibold {{ $transaction->type === 'payable' ? 'text-red-600' : 'text-green-600' }}">
                                    R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($transaction->status === 'pending')
                                            <button type="button" onclick="openPayModal({{ $transaction->id }})" 
                                                    class="rounded p-1 text-green-600 hover:bg-green-50" title="Marcar como pago">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        @endif
                                        <a href="{{ route('admin.financial.transactions.edit', $transaction) }}" 
                                           class="rounded p-1 text-indigo-600 hover:bg-indigo-50" title="Editar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.financial.transactions.destroy', $transaction) }}" class="inline" onsubmit="return confirm('Excluir esta transação?')">
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
            </div>
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $transactions->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhuma transação encontrada</h3>
                <p class="mt-2 text-sm text-gray-500">Comece adicionando uma conta a pagar ou a receber.</p>
            </div>
        @endif
    </div>

    <!-- Pay Modal -->
    <div id="payModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50" x-data>
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-900">Confirmar Pagamento</h3>
            <form id="payForm" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Data do Pagamento</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                           class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Forma de Pagamento</label>
                    <select name="payment_method" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecione...</option>
                        <option value="pix">PIX</option>
                        <option value="boleto">Boleto</option>
                        <option value="cartao_credito">Cartão de Crédito</option>
                        <option value="cartao_debito">Cartão de Débito</option>
                        <option value="transferencia">Transferência</option>
                        <option value="dinheiro">Dinheiro</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closePayModal()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                        Confirmar Pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPayModal(transactionId) {
            document.getElementById('payForm').action = `/admin/financial/transactions/${transactionId}/pay`;
            document.getElementById('payModal').classList.remove('hidden');
            document.getElementById('payModal').classList.add('flex');
        }
        function closePayModal() {
            document.getElementById('payModal').classList.add('hidden');
            document.getElementById('payModal').classList.remove('flex');
        }
    </script>
</x-admin-layout>
