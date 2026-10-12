<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.raffle.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Adicionar Números do Sorteio</h1>
        <p class="text-sm text-gray-600">Próximo número disponível: <span class="font-mono font-bold text-indigo-600">{{ str_pad($settings->current_number + 1, 6, '0', STR_PAD_LEFT) }}</span></p>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
            <ul class="list-disc list-inside text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-lg bg-white p-6 shadow" x-data="raffleForm()">
        <form method="POST" action="{{ route('admin.raffle.store') }}">
            @csrf

            <!-- Busca de Cliente -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Cliente *</label>
                <div class="relative">
                    <input type="text" 
                           x-model="clientSearch"
                           @input.debounce.300ms="searchClients()"
                           @focus="showDropdown = true"
                           placeholder="Digite o nome ou email do cliente..."
                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    
                    <!-- Dropdown de resultados -->
                    <div x-show="showDropdown && clientResults.length > 0" 
                         x-cloak
                         @click.away="showDropdown = false"
                         class="absolute z-10 mt-1 w-full rounded-lg bg-white shadow-lg border border-gray-200 max-h-60 overflow-auto">
                        <template x-for="client in clientResults" :key="client.id">
                            <button type="button"
                                    @click="selectClient(client)"
                                    class="w-full px-4 py-3 text-left hover:bg-indigo-50 flex items-center gap-3 border-b border-gray-100 last:border-0">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 font-bold">
                                    <span x-text="client.name.charAt(0).toUpperCase()"></span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900" x-text="client.name"></p>
                                    <p class="text-sm text-gray-500" x-text="client.email"></p>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Cliente selecionado -->
                <div x-show="selectedClient" x-cloak class="mt-3 rounded-lg border border-green-200 bg-green-50 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-200 text-green-700 font-bold text-lg">
                                <span x-text="selectedClient?.name?.charAt(0).toUpperCase()"></span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900" x-text="selectedClient?.name"></p>
                                <p class="text-sm text-gray-500" x-text="selectedClient?.email"></p>
                            </div>
                        </div>
                        <button type="button" @click="clearClient()" class="text-sm text-red-600 hover:text-red-800">
                            Remover
                        </button>
                    </div>
                    <input type="hidden" name="client_user_id" :value="selectedClient?.id">
                </div>
            </div>

            <!-- Quantidade de Números -->
            <div class="mb-6">
                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantidade de Números *</label>
                <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" min="1" max="100" required
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-gray-500">Cada disco comprado = 1 número. Máximo 100 por vez.</p>
            </div>

            <!-- Descrição da Compra -->
            <div class="mb-6">
                <label for="purchase_description" class="block text-sm font-medium text-gray-700 mb-2">Descrição da Compra (opcional)</label>
                <textarea name="purchase_description" id="purchase_description" rows="2"
                          class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                          placeholder="Ex: 5 discos de vinil - House/Techno">{{ old('purchase_description') }}</textarea>
            </div>

            <!-- Número do Pedido -->
            <div class="mb-6">
                <label for="order_number" class="block text-sm font-medium text-gray-700 mb-2">Número do Pedido (opcional)</label>
                <input type="text" name="order_number" id="order_number" value="{{ old('order_number') }}"
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                       placeholder="Ex: 20261012-ABC123">
            </div>

            <!-- Botões -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.raffle.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Cancelar
                </a>
                <button type="submit" 
                        :disabled="!selectedClient"
                        class="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Gerar Números
                </button>
            </div>
        </form>
    </div>

    <script>
        function raffleForm() {
            return {
                clientSearch: '',
                clientResults: [],
                showDropdown: false,
                selectedClient: null,

                async searchClients() {
                    if (this.clientSearch.length < 2) {
                        this.clientResults = [];
                        return;
                    }
                    try {
                        const response = await fetch(`{{ route('admin.raffle.search-clients') }}?q=${encodeURIComponent(this.clientSearch)}`);
                        const data = await response.json();
                        this.clientResults = data.clients || [];
                    } catch (e) {
                        console.error('Erro ao buscar clientes:', e);
                        this.clientResults = [];
                    }
                },

                selectClient(client) {
                    this.selectedClient = client;
                    this.showDropdown = false;
                    this.clientSearch = '';
                    this.clientResults = [];
                },

                clearClient() {
                    this.selectedClient = null;
                }
            }
        }
    </script>
</x-admin-layout>
