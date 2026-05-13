@extends('admin.layouts.app')

@section('title', 'Relatório Financeiro de Pré-vendas')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Relatório Financeiro de Pré-vendas</h1>
            <p class="text-sm text-gray-500">Recebido por mês e valores pendentes</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Últimos</label>
                <select name="months" onchange="this.form.submit()" class="rounded border-gray-300 text-sm">
                    @foreach([3, 6, 12, 24, 36] as $m)
                        <option value="{{ $m }}" @selected($months === $m)>{{ $m }} meses</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('admin.pre-orders.index') }}" class="text-sm text-gray-600 hover:underline">← Voltar</a>
        </div>
    </div>

    {{-- Pendentes --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border-l-4 border-yellow-500 rounded-lg shadow p-4">
            <div class="text-xs text-gray-500 uppercase">Sinais a receber</div>
            <div class="text-2xl font-bold text-gray-800">R$ {{ number_format($pending['signal'], 2, ',', '.') }}</div>
        </div>
        <div class="bg-white border-l-4 border-red-500 rounded-lg shadow p-4">
            <div class="text-xs text-gray-500 uppercase">Sinais vencidos</div>
            <div class="text-2xl font-bold text-red-700">R$ {{ number_format($pending['signal_overdue'], 2, ',', '.') }}</div>
        </div>
        <div class="bg-white border-l-4 border-blue-500 rounded-lg shadow p-4">
            <div class="text-xs text-gray-500 uppercase">Saldos a receber</div>
            <div class="text-2xl font-bold text-gray-800">R$ {{ number_format($pending['balance'], 2, ',', '.') }}</div>
        </div>
        <div class="bg-white border-l-4 border-red-500 rounded-lg shadow p-4">
            <div class="text-xs text-gray-500 uppercase">Saldos vencidos</div>
            <div class="text-2xl font-bold text-red-700">R$ {{ number_format($pending['balance_overdue'], 2, ',', '.') }}</div>
        </div>
    </div>

    {{-- Mensal --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 border-b flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Recebimentos por mês</h2>
            <a href="{{ route('admin.pre-orders.export') }}" class="text-sm text-emerald-600 hover:text-emerald-800">
                <i class="fas fa-file-csv mr-1"></i>Export CSV
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Mês</th>
                        <th class="px-4 py-2 text-right font-semibold text-gray-600">Pré-vendas criadas</th>
                        <th class="px-4 py-2 text-right font-semibold text-gray-600">Total contratado</th>
                        <th class="px-4 py-2 text-right font-semibold text-gray-600">Sinal recebido</th>
                        <th class="px-4 py-2 text-right font-semibold text-gray-600">Saldo recebido</th>
                        <th class="px-4 py-2 text-right font-semibold text-gray-600">Total recebido</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $totSignal = 0; $totBalance = 0; $totCreated = 0; $totQty = 0;
                    @endphp
                    @foreach($rows as $r)
                        @php
                            $totSignal += $r['signal_received'];
                            $totBalance += $r['balance_received'];
                            $totCreated += $r['created_total'];
                            $totQty += $r['created_qty'];
                            $rowTotal = $r['signal_received'] + $r['balance_received'];
                        @endphp
                        <tr>
                            <td class="px-4 py-2">{{ $r['label'] }}</td>
                            <td class="px-4 py-2 text-right">{{ $r['created_qty'] }}</td>
                            <td class="px-4 py-2 text-right">R$ {{ number_format($r['created_total'], 2, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right text-emerald-700">R$ {{ number_format($r['signal_received'], 2, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right text-emerald-700">R$ {{ number_format($r['balance_received'], 2, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right font-semibold">R$ {{ number_format($rowTotal, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                        <td class="px-4 py-2">Total</td>
                        <td class="px-4 py-2 text-right">{{ $totQty }}</td>
                        <td class="px-4 py-2 text-right">R$ {{ number_format($totCreated, 2, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right text-emerald-700">R$ {{ number_format($totSignal, 2, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right text-emerald-700">R$ {{ number_format($totBalance, 2, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right">R$ {{ number_format($totSignal + $totBalance, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
