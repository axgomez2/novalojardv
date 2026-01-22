<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.financial.transactions.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">
            Editar {{ $transaction->type === 'payable' ? 'Conta a Pagar' : 'Conta a Receber' }}
        </h1>
    </div>

    <form method="POST" action="{{ route('admin.financial.transactions.update', $transaction) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Informações da Transação</h2>
            
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Descrição *</label>
                    <input type="text" name="description" id="description" value="{{ old('description', $transaction->description) }}" required
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700">Valor *</label>
                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">R$</span>
                        <input type="number" name="amount" id="amount" value="{{ old('amount', $transaction->amount) }}" step="0.01" min="0.01" required
                               class="block w-full rounded-lg border-gray-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700">Data de Vencimento *</label>
                    <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $transaction->due_date->format('Y-m-d')) }}" required
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('due_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="payment_category_id" class="block text-sm font-medium text-gray-700">Categoria</label>
                    <select name="payment_category_id" id="payment_category_id"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecione...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('payment_category_id', $transaction->payment_category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($transaction->type === 'receivable')
                <div>
                    <label for="income_source_id" class="block text-sm font-medium text-gray-700">Origem da Receita</label>
                    <select name="income_source_id" id="income_source_id"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecione...</option>
                        @foreach($incomeSources as $source)
                            <option value="{{ $source->id }}" {{ old('income_source_id', $transaction->income_source_id) == $source->id ? 'selected' : '' }}>
                                {{ $source->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if($transaction->type === 'payable')
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700">Fornecedor</label>
                    <select name="supplier_id" id="supplier_id"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecione...</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $transaction->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label for="reference" class="block text-sm font-medium text-gray-700">Referência</label>
                    <input type="text" name="reference" id="reference" value="{{ old('reference', $transaction->reference) }}" placeholder="Nº NF, Boleto, etc"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700">Forma de Pagamento</label>
                    <select name="payment_method" id="payment_method"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecione...</option>
                        <option value="pix" {{ old('payment_method', $transaction->payment_method) == 'pix' ? 'selected' : '' }}>PIX</option>
                        <option value="boleto" {{ old('payment_method', $transaction->payment_method) == 'boleto' ? 'selected' : '' }}>Boleto</option>
                        <option value="cartao_credito" {{ old('payment_method', $transaction->payment_method) == 'cartao_credito' ? 'selected' : '' }}>Cartão de Crédito</option>
                        <option value="cartao_debito" {{ old('payment_method', $transaction->payment_method) == 'cartao_debito' ? 'selected' : '' }}>Cartão de Débito</option>
                        <option value="transferencia" {{ old('payment_method', $transaction->payment_method) == 'transferencia' ? 'selected' : '' }}>Transferência</option>
                        <option value="dinheiro" {{ old('payment_method', $transaction->payment_method) == 'dinheiro' ? 'selected' : '' }}>Dinheiro</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700">Observações</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $transaction->notes) }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label for="attachment" class="block text-sm font-medium text-gray-700">Anexo</label>
                    @if($transaction->attachment)
                        <p class="mb-2 text-sm text-gray-500">
                            Arquivo atual: <a href="{{ Storage::url($transaction->attachment) }}" target="_blank" class="text-indigo-600 hover:underline">Ver anexo</a>
                        </p>
                    @endif
                    <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png"
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-500">PDF, JPG ou PNG. Máximo 5MB.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.financial.transactions.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Salvar Alterações
            </button>
        </div>
    </form>
</x-admin-layout>
