<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.pre-orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Voltar</a>
        <h1 class="mt-1 text-2xl font-bold text-gray-900">Nova Pré-venda</h1>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.pre-orders.store') }}" x-data="preOrderForm()" class="space-y-6">
        @csrf

        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Cliente e Disco</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cliente *</label>
                    <select name="client_user_id" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecione...</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" @selected(old('client_user_id') == $c->id)>{{ $c->name }} ({{ $c->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Disco (pré-venda ou privado) *</label>
                    <select name="vinyl_stock_id" required x-model="stockId" @change="fillPrice()" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecione...</option>
                        @foreach($vinylStocks as $s)
                            <option value="{{ $s->id }}"
                                    data-price="{{ $s->sell_price }}"
                                    data-signal="{{ $s->default_signal_percentage }}"
                                    @selected(old('vinyl_stock_id', $preselectedStockId) == $s->id)>
                                {{ $s->vinylMaster?->title }} — {{ $s->vinylMaster?->artist_names }} ({{ $s->internal_code }})
                                @if($s->visibility === 'private_preorder') [PRIVADO] @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Apenas discos com availability=preorder ou visibility=private_preorder.</p>
                </div>
            </div>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Valores</h3>
            <div class="grid gap-4 sm:grid-cols-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Qtde *</label>
                    <input type="number" name="quantity" x-model.number="quantity" min="1" value="{{ old('quantity', 1) }}" required
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Preço unit. (R$) *</label>
                    <input type="number" step="0.01" min="0" name="unit_price" x-model.number="unitPrice" value="{{ old('unit_price') }}" required
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Total</label>
                    <input type="text" :value="formatMoney(total)" readonly
                           class="mt-1 block w-full rounded-lg border-gray-200 bg-gray-50 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Sinal (R$) *</label>
                    <input type="number" step="0.01" min="0" name="signal_amount" x-model.number="signalAmount" value="{{ old('signal_amount') }}" required
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">
                        <span x-text="signalPercent + '%'"></span> do total. Saldo: <span x-text="formatMoney(total - signalAmount)"></span>
                    </p>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500">Atalhos de sinal:</span>
                <template x-for="pct in [25, 30, 50, 70, 100]" :key="pct">
                    <button type="button" @click="setSignalPercent(pct)" class="rounded bg-gray-100 px-2 py-0.5 text-xs hover:bg-gray-200" x-text="pct + '%'"></button>
                </template>
            </div>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Prazos</h3>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Vencimento do sinal</label>
                    <input type="date" name="signal_due_date" value="{{ old('signal_due_date') }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Chegada prevista na loja</label>
                    <input type="date" name="expected_arrival_date" value="{{ old('expected_arrival_date') }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Vencimento do saldo</label>
                    <input type="date" name="balance_due_date" value="{{ old('balance_due_date') }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Observações</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Observações (cliente)</label>
                    <textarea name="customer_notes" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('customer_notes') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Notas internas (admin)</label>
                    <textarea name="admin_notes" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('admin_notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.pre-orders.index') }}" class="rounded-lg px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cancelar</a>
            <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-700">Criar pré-venda</button>
        </div>
    </form>

    <script>
        function preOrderForm() {
            return {
                stockId: @json(old('vinyl_stock_id', $preselectedStockId ?? '')),
                quantity: Number(@json(old('quantity', 1))) || 1,
                unitPrice: Number(@json(old('unit_price'))) || 0,
                signalAmount: Number(@json(old('signal_amount'))) || 0,
                init() { this.fillPrice(); },
                get total() { return Math.round((this.unitPrice * this.quantity) * 100) / 100; },
                get signalPercent() { return this.total > 0 ? Math.round((this.signalAmount / this.total) * 10000) / 100 : 0; },
                setSignalPercent(pct) {
                    this.signalAmount = Math.round(this.total * (pct / 100) * 100) / 100;
                },
                fillPrice() {
                    const sel = document.querySelector(`select[name="vinyl_stock_id"] option[value="${this.stockId}"]`);
                    if (!sel) return;
                    const price = parseFloat(sel.dataset.price);
                    const defaultSignal = parseFloat(sel.dataset.signal);
                    if (!this.unitPrice && price) this.unitPrice = price;
                    if (!this.signalAmount && defaultSignal && this.total > 0) {
                        this.setSignalPercent(defaultSignal);
                    }
                },
                formatMoney(v) {
                    return 'R$ ' + (Number(v) || 0).toFixed(2).replace('.', ',');
                }
            }
        }
    </script>
</x-admin-layout>
