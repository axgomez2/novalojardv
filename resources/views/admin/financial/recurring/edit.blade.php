<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.financial.recurring.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">
            Editar Pagamento Recorrente - {{ $payment->type === 'payable' ? 'Despesa' : 'Receita' }}
        </h1>
    </div>

    <form method="POST" action="{{ route('admin.financial.recurring.update', $payment) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Informações Básicas</h2>
            
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700">Nome *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $payment->name) }}" required
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700">Valor *</label>
                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">R$</span>
                        <input type="number" name="amount" id="amount" value="{{ old('amount', $payment->amount) }}" step="0.01" min="0.01" required
                               class="block w-full rounded-lg border-gray-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="frequency" class="block text-sm font-medium text-gray-700">Frequência *</label>
                    <select name="frequency" id="frequency" required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="daily" {{ old('frequency', $payment->frequency) == 'daily' ? 'selected' : '' }}>Diário</option>
                        <option value="weekly" {{ old('frequency', $payment->frequency) == 'weekly' ? 'selected' : '' }}>Semanal</option>
                        <option value="biweekly" {{ old('frequency', $payment->frequency) == 'biweekly' ? 'selected' : '' }}>Quinzenal</option>
                        <option value="monthly" {{ old('frequency', $payment->frequency) == 'monthly' ? 'selected' : '' }}>Mensal</option>
                        <option value="bimonthly" {{ old('frequency', $payment->frequency) == 'bimonthly' ? 'selected' : '' }}>Bimestral</option>
                        <option value="quarterly" {{ old('frequency', $payment->frequency) == 'quarterly' ? 'selected' : '' }}>Trimestral</option>
                        <option value="semiannual" {{ old('frequency', $payment->frequency) == 'semiannual' ? 'selected' : '' }}>Semestral</option>
                        <option value="annual" {{ old('frequency', $payment->frequency) == 'annual' ? 'selected' : '' }}>Anual</option>
                    </select>
                </div>

                <div>
                    <label for="day_of_month" class="block text-sm font-medium text-gray-700">Dia do Mês</label>
                    <input type="number" name="day_of_month" id="day_of_month" value="{{ old('day_of_month', $payment->day_of_month) }}" min="1" max="31"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">Data de Término</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $payment->end_date?->format('Y-m-d')) }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="payment_category_id" class="block text-sm font-medium text-gray-700">Categoria</label>
                    <select name="payment_category_id" id="payment_category_id"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecione...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('payment_category_id', $payment->payment_category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($payment->type === 'receivable')
                <div>
                    <label for="income_source_id" class="block text-sm font-medium text-gray-700">Origem da Receita</label>
                    <select name="income_source_id" id="income_source_id"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecione...</option>
                        @foreach($incomeSources as $source)
                            <option value="{{ $source->id }}" {{ old('income_source_id', $payment->income_source_id) == $source->id ? 'selected' : '' }}>
                                {{ $source->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if($payment->type === 'payable')
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700">Fornecedor</label>
                    <select name="supplier_id" id="supplier_id"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecione...</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $payment->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700">Forma de Pagamento</label>
                    <select name="payment_method" id="payment_method"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecione...</option>
                        <option value="pix" {{ old('payment_method', $payment->payment_method) == 'pix' ? 'selected' : '' }}>PIX</option>
                        <option value="boleto" {{ old('payment_method', $payment->payment_method) == 'boleto' ? 'selected' : '' }}>Boleto</option>
                        <option value="cartao_credito" {{ old('payment_method', $payment->payment_method) == 'cartao_credito' ? 'selected' : '' }}>Cartão de Crédito</option>
                        <option value="cartao_debito" {{ old('payment_method', $payment->payment_method) == 'cartao_debito' ? 'selected' : '' }}>Cartão de Débito</option>
                        <option value="transferencia" {{ old('payment_method', $payment->payment_method) == 'transferencia' ? 'selected' : '' }}>Transferência</option>
                        <option value="debito_automatico" {{ old('payment_method', $payment->payment_method) == 'debito_automatico' ? 'selected' : '' }}>Débito Automático</option>
                    </select>
                </div>

                <div>
                    <label for="days_before_notify" class="block text-sm font-medium text-gray-700">Dias para Notificar</label>
                    <input type="number" name="days_before_notify" id="days_before_notify" value="{{ old('days_before_notify', $payment->days_before_notify) }}" min="0" max="30"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                    <textarea name="description" id="description" rows="2"
                              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $payment->description) }}</textarea>
                </div>

                <div class="flex items-center gap-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="auto_generate" id="auto_generate" value="1" {{ old('auto_generate', $payment->auto_generate) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="auto_generate" class="ml-2 block text-sm text-gray-700">Gerar transações automaticamente</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $payment->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">Ativo</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.financial.recurring.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Salvar Alterações
            </button>
        </div>
    </form>
</x-admin-layout>
