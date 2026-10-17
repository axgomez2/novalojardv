<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.clients.show', $client) }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar para detalhes do cliente
        </a>
        <h1 class="mt-4 text-2xl font-bold text-gray-900">Editar Cliente</h1>
        <p class="mt-1 text-sm text-gray-600">Atualize as informações do cliente</p>
    </div>

    @if(session('error'))
        <div class="mb-6 rounded-lg bg-red-50 p-4 text-red-800">{{ session('error') }}</div>
    @endif

    <div class="rounded-lg bg-white p-6 shadow">
        <form method="POST" action="{{ route('admin.clients.update', $client) }}">
            @csrf
            @method('PUT')

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Nome -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nome Completo *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $client->name) }}" required
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alias (Apelido Interno) -->
                <div>
                    <label for="alias" class="block text-sm font-medium text-gray-700">
                        Apelido Interno
                        <span class="text-xs text-gray-400">(visível apenas no admin)</span>
                    </label>
                    <input type="text" name="alias" id="alias" value="{{ old('alias', $client->alias) }}" 
                           placeholder="Ex: João da Loja X, Cliente VIP..."
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('alias') border-red-500 @enderror">
                    @error('alias')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">E-mail *</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $client->email) }}" required
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Telefone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Telefone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $client->phone) }}" 
                           placeholder="(11) 99999-9999"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- CPF -->
                <div>
                    <label for="cpf" class="block text-sm font-medium text-gray-700">CPF</label>
                    <input type="text" name="cpf" id="cpf" value="{{ old('cpf', $client->cpf) }}" 
                           placeholder="000.000.000-00"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('cpf') border-red-500 @enderror">
                    @error('cpf')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Data de Nascimento -->
                <div>
                    <label for="birth_date" class="block text-sm font-medium text-gray-700">Data de Nascimento</label>
                    <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date', $client->birth_date?->format('Y-m-d')) }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('birth_date') border-red-500 @enderror">
                    @error('birth_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Flags -->
            <div class="mt-6 border-t border-gray-200 pt-6">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Status e Permissões</h3>
                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $client->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">Cliente Ativo</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_dj" value="1" {{ old('is_dj', $client->is_dj) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="text-sm text-gray-700">É DJ</span>
                    </label>
                </div>
            </div>

            <!-- Info adicional -->
            <div class="mt-6 border-t border-gray-200 pt-6">
                <div class="grid gap-4 text-sm text-gray-500 md:grid-cols-3">
                    <div>
                        <span class="font-medium">Cadastrado em:</span>
                        {{ $client->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div>
                        <span class="font-medium">E-mail verificado:</span>
                        {{ $client->email_verified_at ? $client->email_verified_at->format('d/m/Y H:i') : 'Não verificado' }}
                    </div>
                    <div>
                        <span class="font-medium">Total de pedidos:</span>
                        {{ $client->orders()->count() }}
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="mt-6 flex items-center justify-end gap-4">
                <a href="{{ route('admin.clients.show', $client) }}" 
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" 
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
