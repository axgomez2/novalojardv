<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar para Pedidos
        </a>
        <h1 class="mt-4 text-2xl font-bold text-gray-900">Novo Pedido (PDV)</h1>
        <p class="mt-1 text-sm text-gray-600">Crie um pedido diretamente no painel</p>
    </div>

    @if(session('error'))
        <div class="mb-6 rounded-lg bg-red-50 p-4 text-red-800">{{ session('error') }}</div>
    @endif

    @php($pdvPrefill = session('pdv_prefill'))
    @if($pdvPrefill)
        <script>
            window.__pdvPrefill = @json($pdvPrefill);
        </script>
        <div class="mb-6 rounded-lg bg-indigo-50 p-4 text-indigo-800 text-sm">
            Carrinho de <strong>{{ $pdvPrefill['client']['name'] ?? '' }}</strong> importado com {{ count($pdvPrefill['items'] ?? []) }} item(ns). Revise e finalize o pedido.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.orders.store') }}" x-data="pdvForm()" x-init="init()" @submit.prevent="submitForm">
        @csrf
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Coluna Principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Cliente -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Cliente</h2>
                    
                    <div class="mb-4">
                        <label class="flex items-center gap-4">
                            <input type="radio" name="client_type" value="registered" x-model="clientType" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700">Cliente Cadastrado</span>
                        </label>
                        <label class="mt-2 flex items-center gap-4">
                            <input type="radio" name="client_type" value="guest" x-model="clientType" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700">Cliente Não Cadastrado</span>
                        </label>
                    </div>

                    <!-- Cliente Cadastrado -->
                    <div x-show="clientType === 'registered'" x-cloak class="space-y-4">
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700">Buscar Cliente</label>
                            <input type="text" x-model="clientSearch" @input.debounce.300ms="searchClients()" placeholder="Nome, e-mail ou CPF..."
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="hidden" name="client_user_id" :value="selectedClient?.id">
                            
                            <div x-show="clientResults.length > 0" x-cloak class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg max-h-48 overflow-y-auto">
                                <template x-for="client in clientResults" :key="client.id">
                                    <button type="button" @click="selectClient(client)" class="w-full px-4 py-2 text-left hover:bg-gray-50 border-b border-gray-100 last:border-0">
                                        <div class="font-medium text-gray-900" x-text="client.name"></div>
                                        <div class="text-sm text-gray-500" x-text="client.email + (client.phone ? ' | ' + client.phone : '')"></div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div x-show="selectedClient" x-cloak class="rounded-lg bg-indigo-50 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-indigo-900" x-text="selectedClient?.name"></div>
                                    <div class="text-sm text-indigo-700" x-text="selectedClient?.email"></div>
                                    <div class="text-sm text-indigo-600" x-text="selectedClient?.phone"></div>
                                </div>
                                <button type="button" @click="clearClient()" class="text-indigo-600 hover:text-indigo-800">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Cliente Não Cadastrado -->
                    <div x-show="clientType === 'guest'" x-cloak class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nome *</label>
                            <input type="text" name="guest_name" x-model="guestName" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">E-mail</label>
                            <input type="email" name="guest_email" x-model="guestEmail" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Telefone</label>
                            <input type="text" name="guest_phone" x-model="guestPhone" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">CPF</label>
                            <input type="text" name="guest_cpf" x-model="guestCpf" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Produtos -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Produtos</h2>
                    
                    <div class="relative mb-4">
                        <label class="block text-sm font-medium text-gray-700">Adicionar Produto</label>
                        <input type="text" x-model="productSearch" @input.debounce.300ms="searchProducts()" placeholder="Buscar por título ou artista..."
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        
                        <div x-show="productResults.length > 0" x-cloak class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="product in productResults" :key="product.id">
                                <button type="button" @click="addProduct(product)" class="w-full px-4 py-3 text-left hover:bg-gray-50 border-b border-gray-100 last:border-0 flex items-center gap-3">
                                    <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded bg-gray-100">
                                        <img x-show="product.image" :src="product.image" class="h-full w-full object-cover">
                                        <div x-show="!product.image" class="flex h-full w-full items-center justify-center">
                                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-gray-900" x-text="product.title"></div>
                                        <div class="text-sm text-gray-500" x-text="product.artist"></div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium text-gray-900" x-text="product.formatted_price"></div>
                                        <div class="text-xs text-gray-500" x-text="'Estoque: ' + product.stock"></div>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Lista de Itens -->
                    <div x-show="items.length > 0" class="divide-y divide-gray-200">
                        <template x-for="(item, index) in items" :key="item.id">
                            <div class="flex items-center gap-4 py-4">
                                <input type="hidden" :name="'items[' + index + '][vinyl_stock_id]'" :value="item.id">
                                <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                                
                                <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded bg-gray-100">
                                    <img x-show="item.image" :src="item.image" class="h-full w-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900" x-text="item.title"></div>
                                    <div class="text-sm text-gray-500" x-text="item.artist"></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="decrementQuantity(index)" class="rounded-full bg-gray-100 p-1 hover:bg-gray-200">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <span class="w-8 text-center font-medium" x-text="item.quantity"></span>
                                    <button type="button" @click="incrementQuantity(index)" class="rounded-full bg-gray-100 p-1 hover:bg-gray-200">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="w-24 text-right font-medium text-gray-900" x-text="formatCurrency(item.price * item.quantity)"></div>
                                <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-800">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <div x-show="items.length === 0" class="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
                        <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Nenhum produto adicionado</p>
                    </div>
                </div>

                <!-- Entrega -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Entrega</h2>
                    
                    <div class="mb-4">
                        <label class="flex items-center gap-4">
                            <input type="radio" name="shipping_type" value="pickup" x-model="shippingType" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700">Retirada na Loja</span>
                        </label>
                        <label class="mt-2 flex items-center gap-4">
                            <input type="radio" name="shipping_type" value="delivery" x-model="shippingType" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700">Entrega</span>
                        </label>
                    </div>

                    <div x-show="shippingType === 'delivery'" x-cloak class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">CEP *</label>
                            <input type="text" name="shipping_zip_code" x-model="shippingZipCode" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Rua *</label>
                            <input type="text" name="shipping_street" x-model="shippingStreet" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Número *</label>
                            <input type="text" name="shipping_number" x-model="shippingNumber" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Complemento</label>
                            <input type="text" name="shipping_complement" x-model="shippingComplement" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Bairro *</label>
                            <input type="text" name="shipping_neighborhood" x-model="shippingNeighborhood" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cidade *</label>
                            <input type="text" name="shipping_city" x-model="shippingCity" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estado *</label>
                            <select name="shipping_state" x-model="shippingState" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                                    <option value="{{ $uf }}">{{ $uf }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Valor do Frete</label>
                            <input type="number" name="shipping_cost" x-model="shippingCost" step="0.01" min="0" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Observações -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Observações</h2>
                    <div class="grid gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Observações do Cliente</label>
                            <textarea name="customer_notes" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notas Administrativas</label>
                            <textarea name="admin_notes" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral - Resumo -->
            <div class="space-y-6">
                <div class="sticky top-6 rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Resumo do Pedido</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="text-gray-900" x-text="formatCurrency(subtotal)"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Frete</span>
                            <span class="text-gray-900" x-text="formatCurrency(parseFloat(shippingCost) || 0)"></span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Desconto</label>
                            <input type="number" name="discount" x-model="discount" step="0.01" min="0" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="border-t border-gray-200 pt-3 flex justify-between text-lg font-bold">
                            <span>Total</span>
                            <span x-text="formatCurrency(total)"></span>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Forma de Pagamento *</label>
                            <select name="payment_method" x-model="paymentMethod" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                <option value="pix">PIX</option>
                                <option value="credit_card">Cartão de Crédito</option>
                                <option value="debit_card">Cartão de Débito</option>
                                <option value="cash">Dinheiro</option>
                                <option value="bank_transfer">Transferência</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status do Pagamento *</label>
                            <select name="payment_status" x-model="paymentStatus" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="pending">Aguardando Pagamento</option>
                                <option value="paid">Pago</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" :disabled="items.length === 0 || (!selectedClient && clientType === 'registered') || (!guestName && clientType === 'guest')"
                                class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
                            Finalizar Pedido
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        function pdvForm() {
            return {
                // Cliente
                clientType: 'registered',
                clientSearch: '',
                clientResults: [],
                selectedClient: null,
                guestName: '',
                guestEmail: '',
                guestPhone: '',
                guestCpf: '',

                // Produtos
                productSearch: '',
                productResults: [],
                items: [],

                // Entrega
                shippingType: 'pickup',
                shippingZipCode: '',
                shippingStreet: '',
                shippingNumber: '',
                shippingComplement: '',
                shippingNeighborhood: '',
                shippingCity: '',
                shippingState: '',
                shippingCost: 0,

                // Pagamento
                discount: 0,
                paymentMethod: '',
                paymentStatus: 'paid',

                init() {
                    // Pré-preencher a partir do carrinho exportado de um cliente
                    const prefill = window.__pdvPrefill;
                    if (prefill) {
                        this.clientType = 'registered';
                        if (prefill.client) {
                            this.selectedClient = {
                                id: prefill.client.id,
                                name: prefill.client.name,
                                email: prefill.client.email,
                                phone: prefill.client.phone || '',
                            };
                        }
                        if (Array.isArray(prefill.items)) {
                            this.items = prefill.items.map(p => ({
                                id: p.id,
                                title: p.title,
                                artist: p.artist || '',
                                price: parseFloat(p.price) || 0,
                                stock: parseInt(p.stock) || 0,
                                image: p.image || null,
                                quantity: parseInt(p.quantity) || 1,
                            }));
                        }
                        // Limpa para evitar reaplicar em reload
                        delete window.__pdvPrefill;
                    }
                },

                get subtotal() {
                    return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                get total() {
                    return this.subtotal + (parseFloat(this.shippingCost) || 0) - (parseFloat(this.discount) || 0);
                },

                formatCurrency(value) {
                    return 'R$ ' + value.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                },

                async searchClients() {
                    if (this.clientSearch.length < 2) {
                        this.clientResults = [];
                        return;
                    }
                    try {
                        const response = await fetch(`{{ route('admin.orders.search-clients') }}?q=${encodeURIComponent(this.clientSearch)}`);
                        this.clientResults = await response.json();
                    } catch (e) {
                        console.error(e);
                    }
                },

                selectClient(client) {
                    this.selectedClient = client;
                    this.clientSearch = '';
                    this.clientResults = [];
                },

                clearClient() {
                    this.selectedClient = null;
                },

                async searchProducts() {
                    if (this.productSearch.length < 2) {
                        this.productResults = [];
                        return;
                    }
                    try {
                        const response = await fetch(`{{ route('admin.orders.search-products') }}?q=${encodeURIComponent(this.productSearch)}`);
                        this.productResults = await response.json();
                    } catch (e) {
                        console.error(e);
                    }
                },

                addProduct(product) {
                    const existingIndex = this.items.findIndex(item => item.id === product.id);
                    if (existingIndex >= 0) {
                        if (this.items[existingIndex].quantity < product.stock) {
                            this.items[existingIndex].quantity++;
                        }
                    } else {
                        this.items.push({
                            id: product.id,
                            title: product.title,
                            artist: product.artist,
                            price: product.price,
                            stock: product.stock,
                            image: product.image,
                            quantity: 1
                        });
                    }
                    this.productSearch = '';
                    this.productResults = [];
                },

                incrementQuantity(index) {
                    if (this.items[index].quantity < this.items[index].stock) {
                        this.items[index].quantity++;
                    }
                },

                decrementQuantity(index) {
                    if (this.items[index].quantity > 1) {
                        this.items[index].quantity--;
                    }
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                submitForm() {
                    this.$el.submit();
                }
            }
        }
    </script>
</x-admin-layout>
