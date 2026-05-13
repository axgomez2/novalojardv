<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pré-vendas e Encomendas</h1>
            <p class="mt-1 text-sm text-gray-600">Gestão de discos em pré-venda e encomendas com sinal.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.pre-orders.dashboard') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow hover:bg-gray-50">
                📊 Painel
            </a>
            <a href="{{ route('admin.pre-orders.report') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow hover:bg-gray-50">
                📈 Relatório
            </a>
            <a href="{{ route('admin.pre-orders.export', request()->query()) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-emerald-700">
                ⬇ CSV
            </a>
            <a href="{{ route('admin.pre-orders.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nova pré-venda
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    {{-- KPIs --}}
    <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
        <a href="{{ route('admin.pre-orders.index', ['status' => 'active']) }}" class="rounded-lg bg-white p-4 shadow hover:shadow-md transition">
            <p class="text-xs text-gray-500 uppercase">Ativas</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['active'] }}</p>
        </a>
        <a href="{{ route('admin.pre-orders.index', ['status' => 'awaiting_signal']) }}" class="rounded-lg bg-yellow-50 p-4 shadow hover:shadow-md transition">
            <p class="text-xs text-yellow-700 uppercase">Aguard. sinal</p>
            <p class="mt-1 text-2xl font-bold text-yellow-900">{{ $stats['awaiting_signal'] }}</p>
        </a>
        <a href="{{ route('admin.pre-orders.index', ['status' => 'awaiting_balance']) }}" class="rounded-lg bg-orange-50 p-4 shadow hover:shadow-md transition">
            <p class="text-xs text-orange-700 uppercase">Aguard. saldo</p>
            <p class="mt-1 text-2xl font-bold text-orange-900">{{ $stats['awaiting_balance'] }}</p>
        </a>
        <a href="{{ route('admin.pre-orders.index', ['status' => 'signal_overdue']) }}" class="rounded-lg bg-red-50 p-4 shadow hover:shadow-md transition">
            <p class="text-xs text-red-700 uppercase">Sinal vencido</p>
            <p class="mt-1 text-2xl font-bold text-red-900">{{ $stats['signal_overdue'] }}</p>
        </a>
        <a href="{{ route('admin.pre-orders.index', ['status' => 'balance_overdue']) }}" class="rounded-lg bg-red-50 p-4 shadow hover:shadow-md transition">
            <p class="text-xs text-red-700 uppercase">Saldo vencido</p>
            <p class="mt-1 text-2xl font-bold text-red-900">{{ $stats['balance_overdue'] }}</p>
        </a>
        <div class="rounded-lg bg-indigo-50 p-4 shadow col-span-2 lg:col-span-1">
            <p class="text-xs text-indigo-700 uppercase">Valor em aberto</p>
            <p class="mt-1 text-xl font-bold text-indigo-900">R$ {{ number_format($stats['total_open'], 2, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" class="mb-4 rounded-lg bg-white p-4 shadow">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-700">Buscar</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Código ou cliente..."
                       class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todos</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Ativas (não finalizadas)</option>
                    <option value="signal_overdue" @selected(($filters['status'] ?? '') === 'signal_overdue')>Sinal vencido</option>
                    <option value="balance_overdue" @selected(($filters['status'] ?? '') === 'balance_overdue')>Saldo vencido</option>
                    @foreach($statusOptions as $opt)
                        <option value="{{ $opt['value'] }}" @selected(($filters['status'] ?? '') === $opt['value'])>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">De</label>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Até</label>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
        <div class="mt-3 flex items-center gap-2">
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">Filtrar</button>
            <a href="{{ route('admin.pre-orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Limpar</a>
        </div>
    </form>

    {{-- Lista --}}
    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Código</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Disco</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Total</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Sinal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Vencimentos</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($preOrders as $po)
                    @php
                        $color = $po->status->color();
                        $signalOver = $po->isSignalOverdue();
                        $balanceOver = $po->isBalanceOverdue();
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $signalOver || $balanceOver ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3 text-sm font-mono font-medium text-gray-900">{{ $po->code }}</td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium text-gray-900">{{ $po->client?->name }}</div>
                            <div class="text-xs text-gray-500">{{ $po->client?->email }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="text-gray-900">{{ $po->vinylStock?->vinylMaster?->title }}</div>
                            <div class="text-xs text-gray-500">{{ $po->vinylStock?->vinylMaster?->artist_names }}</div>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ $po->formatted_total }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">
                            {{ $po->formatted_signal }}
                            @if($po->signal_paid_at)
                                <span class="block text-[10px] text-green-600">✓ pago</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex items-center rounded-full bg-{{ $color }}-100 px-2 py-0.5 text-xs font-medium text-{{ $color }}-800">
                                {{ $po->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            @if($po->signal_due_date && !$po->signal_paid_at)
                                <div class="{{ $signalOver ? 'text-red-600 font-bold' : '' }}">Sinal: {{ $po->signal_due_date->format('d/m/Y') }}</div>
                            @endif
                            @if($po->balance_due_date && !$po->balance_paid_at)
                                <div class="{{ $balanceOver ? 'text-red-600 font-bold' : '' }}">Saldo: {{ $po->balance_due_date->format('d/m/Y') }}</div>
                            @endif
                            @if($po->expected_arrival_date)
                                <div>Chega: {{ $po->expected_arrival_date->format('d/m/Y') }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.pre-orders.show', $po) }}" class="text-sm text-indigo-600 hover:text-indigo-800">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">Nenhuma pré-venda encontrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $preOrders->links() }}</div>
</x-admin-layout>
