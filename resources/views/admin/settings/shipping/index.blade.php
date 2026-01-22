<x-admin-layout>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Configurações de Frete</h1>
            <p class="mt-1 text-sm text-gray-600">Gerencie transportadoras, acréscimos e prazos de entrega</p>
        </div>
        <button type="button" onclick="syncCarriers()" id="syncBtn"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Sincronizar Transportadoras
        </button>
    </div>

    <!-- Configurações Gerais -->
    <div class="mb-6 rounded-lg bg-white p-6 shadow">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Configurações Gerais</h2>
        <form method="POST" action="{{ route('admin.settings.shipping.update-settings') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">CEP de Origem</label>
                    <input type="text" name="sender_postal_code" value="{{ $settings['sender_postal_code'] ?? '' }}" 
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="00000-000">
                    <p class="mt-1 text-xs text-gray-500">CEP de onde os produtos serão enviados</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Token Melhor Envio</label>
                    <input type="password" name="melhor_envio_token" value="{{ $settings['melhor_envio_token'] ?? '' }}" 
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="••••••••">
                </div>
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="sandbox_mode" value="1" {{ ($settings['sandbox_mode'] ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">Modo Sandbox (testes)</span>
                </label>
            </div>
            <div class="pt-4">
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Salvar Configurações
                </button>
            </div>
        </form>
    </div>

    <!-- Transportadoras -->
    <div class="rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Transportadoras</h2>
            <p class="text-sm text-gray-500">Configure quais transportadoras estarão disponíveis e seus acréscimos</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Ativo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Transportadora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Empresa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Acréscimo (R$)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Acréscimo (%)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Dias Extras</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Ordem</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($carriers as $carrier)
                        <tr>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.settings.shipping.toggle-carrier', $carrier) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="focus:outline-none">
                                        @if ($carrier->is_active)
                                            <span class="inline-flex h-6 w-11 items-center rounded-full bg-indigo-600">
                                                <span class="ml-5 h-5 w-5 rounded-full bg-white shadow"></span>
                                            </span>
                                        @else
                                            <span class="inline-flex h-6 w-11 items-center rounded-full bg-gray-200">
                                                <span class="ml-0.5 h-5 w-5 rounded-full bg-white shadow"></span>
                                            </span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($carrier->logo)
                                        <img src="{{ $carrier->logo }}" alt="{{ $carrier->name }}" class="h-8 w-8 object-contain">
                                    @endif
                                    <span class="text-sm font-medium text-gray-900">{{ $carrier->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $carrier->company }}</td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.settings.shipping.update-carrier', $carrier) }}" class="inline-flex items-center gap-1">
                                    @csrf
                                    <input type="number" name="additional_cost" value="{{ $carrier->additional_cost }}" step="0.01" min="0"
                                           class="w-20 rounded border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="hidden" name="field" value="additional_cost">
                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.settings.shipping.update-carrier', $carrier) }}" class="inline-flex items-center gap-1">
                                    @csrf
                                    <input type="number" name="additional_percentage" value="{{ $carrier->additional_percentage }}" step="0.1" min="0"
                                           class="w-20 rounded border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="hidden" name="field" value="additional_percentage">
                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.settings.shipping.update-carrier', $carrier) }}" class="inline-flex items-center gap-1">
                                    @csrf
                                    <input type="number" name="additional_days" value="{{ $carrier->additional_days }}" min="0"
                                           class="w-16 rounded border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="hidden" name="field" value="additional_days">
                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.settings.shipping.update-carrier', $carrier) }}" class="inline-flex items-center gap-1">
                                    @csrf
                                    <input type="number" name="sort_order" value="{{ $carrier->sort_order }}" min="0"
                                           class="w-16 rounded border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="hidden" name="field" value="sort_order">
                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-xs text-gray-400">ID: {{ $carrier->melhor_envio_id }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                Nenhuma transportadora cadastrada. Clique em "Sincronizar Transportadoras" para importar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Configurações de Pré-Venda -->
    <div class="mt-6 rounded-lg bg-white p-6 shadow">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Configurações de Pré-Venda</h2>
        <p class="text-sm text-gray-500 mb-4">
            Para pedidos com itens em pré-venda, o prazo de entrega será calculado a partir da data de disponibilidade do disco.
        </p>
        <form method="POST" action="{{ route('admin.settings.shipping.update-settings') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Dias extras para processamento de pré-venda</label>
                    <input type="number" name="preorder_additional_days" value="{{ $settings['preorder_additional_days'] ?? 3 }}" min="0"
                           class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Dias adicionais após a data de disponibilidade para preparar o envio</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Mensagem para pedidos com pré-venda</label>
                    <textarea name="preorder_message" rows="2"
                              class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Este pedido contém itens em pré-venda...">{{ $settings['preorder_message'] ?? '' }}</textarea>
                </div>
            </div>
            <div class="pt-4">
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Salvar Configurações
                </button>
            </div>
        </form>
    </div>

    <script>
        function syncCarriers() {
            const btn = document.getElementById('syncBtn');
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sincronizando...';
            
            fetch('{{ route("admin.settings.shipping.sync-carriers") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao sincronizar');
                    btn.disabled = false;
                    btn.innerHTML = '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Sincronizar Transportadoras';
                }
            })
            .catch(error => {
                alert('Erro ao sincronizar transportadoras');
                btn.disabled = false;
                btn.innerHTML = '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Sincronizar Transportadoras';
            });
        }
    </script>
</x-admin-layout>
