<x-admin-layout>
    <div class="mb-8 flex items-center justify-between">
        <a href="{{ route('admin.clients.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar para Clientes
        </a>

        @if(!$client->orders()->exists())
            <form method="POST" action="{{ route('admin.clients.destroy', $client) }}"
                  onsubmit="return confirm('Excluir definitivamente o cliente {{ addslashes($client->name) }}?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                    </svg>
                    Excluir cliente
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-6 rounded-lg bg-red-50 p-4 text-red-800">{{ session('error') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Info do Cliente -->
        <div class="lg:col-span-1">
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full {{ $client->is_dj ? 'bg-purple-200' : 'bg-gray-200' }}">
                        @if($client->is_dj)
                            <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                            </svg>
                        @else
                            <span class="text-2xl font-bold text-gray-600">
                                {{ strtoupper(substr($client->name, 0, 2)) }}
                            </span>
                        @endif
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ $client->name }}</h1>
                        <p class="text-sm text-gray-500">{{ $client->email }}</p>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Telefone</span>
                        <span class="text-sm font-medium text-gray-900">{{ $client->formatted_phone ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">CPF</span>
                        <span class="text-sm font-medium text-gray-900">{{ $client->formatted_cpf ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Data de Nascimento</span>
                        <span class="text-sm font-medium text-gray-900">{{ $client->birth_date?->format('d/m/Y') ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Cadastro</span>
                        <span class="text-sm font-medium text-gray-900">{{ $client->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Email Verificado</span>
                        <span class="text-sm font-medium {{ $client->email_verified_at ? 'text-green-600' : 'text-red-600' }}">
                            {{ $client->email_verified_at ? 'Sim' : 'Não' }}
                        </span>
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <form method="POST" action="{{ route('admin.clients.toggle-dj', $client) }}" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full rounded-lg px-4 py-2 text-sm font-medium {{ $client->is_dj ? 'bg-purple-100 text-purple-700 hover:bg-purple-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ $client->is_dj ? 'Remover DJ' : 'Tornar DJ' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.clients.toggle-active', $client) }}" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full rounded-lg px-4 py-2 text-sm font-medium {{ $client->is_active ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                            {{ $client->is_active ? 'Desativar' : 'Ativar' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Playlist do DJ -->
            @if($client->is_dj)
                <div class="mt-6 rounded-lg bg-white p-6 shadow">
                    <h2 class="text-lg font-semibold text-gray-900">Playlist de DJ</h2>
                    @if($client->djPlaylist)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">{{ $client->djPlaylist->title }}</p>
                            <p class="text-xs text-gray-400">{{ $client->djPlaylist->tracks->count() }}/10 faixas</p>
                            <a href="{{ route('admin.music.dj-playlists.edit', $client->djPlaylist) }}" 
                               class="mt-3 inline-flex items-center gap-2 text-sm text-purple-600 hover:text-purple-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar Playlist
                            </a>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-gray-500">Este DJ ainda não possui uma playlist vinculada.</p>
                        <a href="{{ route('admin.music.dj-playlists.create') }}?client_id={{ $client->id }}" 
                           class="mt-3 inline-flex items-center gap-2 text-sm text-purple-600 hover:text-purple-800">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Criar Playlist
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- Pedidos -->
        <div class="lg:col-span-2">
            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Pedidos Recentes</h2>
                </div>
                @if($client->orders->count() > 0)
                    <div class="divide-y divide-gray-200">
                        @foreach($client->orders->take(10) as $order)
                            <div class="flex items-center justify-between px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-900">#{{ $order->order_number }}</p>
                                    <p class="text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-gray-900">R$ {{ number_format($order->total, 2, ',', '.') }}</p>
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                                        @switch($order->status)
                                            @case('pending') bg-yellow-100 text-yellow-800 @break
                                            @case('paid') bg-blue-100 text-blue-800 @break
                                            @case('processing') bg-indigo-100 text-indigo-800 @break
                                            @case('shipped') bg-purple-100 text-purple-800 @break
                                            @case('delivered') bg-green-100 text-green-800 @break
                                            @case('cancelled') bg-red-100 text-red-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch
                                    ">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-gray-500">
                        Este cliente ainda não fez nenhum pedido.
                    </div>
                @endif
            </div>

            <!-- Carrinho atual -->
            <div class="mt-6 rounded-lg bg-white shadow">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Carrinho atual</h2>
                        <p class="text-xs text-gray-500">
                            {{ $client->cart?->items?->count() ?? 0 }} item(ns) ·
                            Subtotal: R$ {{ number_format($client->cart?->subtotal ?? 0, 2, ',', '.') }}
                        </p>
                    </div>
                    @if($client->cart && $client->cart->items->isNotEmpty())
                        <form method="POST" action="{{ route('admin.clients.cart-to-pdv', $client) }}"
                              onsubmit="return confirm('Criar um novo pedido (PDV) com os {{ $client->cart->items->count() }} item(ns) do carrinho?');">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                </svg>
                                Exportar para PDV
                            </button>
                        </form>
                    @endif
                </div>

                @if($client->cart && $client->cart->items->isNotEmpty())
                    <div class="divide-y divide-gray-200">
                        @foreach($client->cart->items as $item)
                            @php
                                $stock = $item->vinylStock;
                                $master = $stock?->vinylMaster;
                                $img = $master?->vinylImages?->first()?->url;
                                $artist = $master?->mainArtists?->pluck('name')->join(', ');
                            @endphp
                            <div class="flex items-center gap-4 px-6 py-3">
                                @if($img)
                                    <img src="{{ $img }}" alt="" class="h-12 w-12 rounded object-cover">
                                @else
                                    <div class="h-12 w-12 rounded bg-gray-200"></div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900">{{ $master?->title ?? 'Sem título' }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ $artist ?: '—' }}</p>
                                </div>
                                <div class="text-right text-sm">
                                    <p class="text-gray-900">{{ $item->quantity }}× R$ {{ number_format($item->unit_price, 2, ',', '.') }}</p>
                                    <p class="text-xs text-gray-500">Estoque: {{ $stock?->stock ?? 0 }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-gray-500">Carrinho vazio.</div>
                @endif
            </div>

            <!-- Wishlist -->
            <div class="mt-6 rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Wishlist (Favoritos)</h2>
                    <p class="text-xs text-gray-500">{{ $client->wishlists->count() }} item(ns)</p>
                </div>
                @if($client->wishlists->isNotEmpty())
                    <div class="divide-y divide-gray-200">
                        @foreach($client->wishlists as $wish)
                            @php
                                $stock = $wish->vinylStock;
                                $master = $stock?->vinylMaster;
                                $img = $master?->vinylImages?->first()?->url;
                                $artist = $master?->mainArtists?->pluck('name')->join(', ');
                            @endphp
                            <div class="flex items-center gap-4 px-6 py-3">
                                @if($img)
                                    <img src="{{ $img }}" alt="" class="h-12 w-12 rounded object-cover">
                                @else
                                    <div class="h-12 w-12 rounded bg-gray-200"></div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900">{{ $master?->title ?? '—' }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ $artist ?: '—' }}</p>
                                </div>
                                <div class="text-right text-sm text-gray-600">
                                    R$ {{ number_format($stock?->current_price ?? 0, 2, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-gray-500">Wishlist vazia.</div>
                @endif
            </div>

            <!-- Wantlist -->
            <div class="mt-6 rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Wantlist (lista de procura)</h2>
                    <p class="text-xs text-gray-500">{{ $client->wantlists->count() }} item(ns)</p>
                </div>
                @if($client->wantlists->isNotEmpty())
                    <div class="divide-y divide-gray-200">
                        @foreach($client->wantlists as $want)
                            <div class="flex items-center gap-4 px-6 py-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900">{{ $want->display_name }}</p>
                                    @if($want->description)
                                        <p class="truncate text-xs text-gray-500">{{ $want->description }}</p>
                                    @endif
                                </div>
                                <div class="text-right text-sm">
                                    <span class="inline-flex rounded-full bg-{{ $want->priority_color }}-100 px-2 py-1 text-xs font-semibold text-{{ $want->priority_color }}-800">
                                        {{ $want->priority_label }}
                                    </span>
                                    @if($want->max_price)
                                        <p class="mt-1 text-xs text-gray-500">Máx: R$ {{ number_format($want->max_price, 2, ',', '.') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-gray-500">Wantlist vazia.</div>
                @endif
            </div>

            <!-- Endereços -->
            <div class="mt-6 rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Endereços</h2>
                    <p class="text-xs text-gray-500">{{ $client->addresses->count() }} cadastrado(s)</p>
                </div>
                @if($client->addresses->isNotEmpty())
                    <div class="divide-y divide-gray-200">
                        @foreach($client->addresses as $addr)
                            <div class="px-6 py-3 text-sm text-gray-700">
                                <p class="font-medium">
                                    {{ $addr->street }}, {{ $addr->number }}
                                    @if($addr->complement) — {{ $addr->complement }} @endif
                                    @if($addr->is_default)
                                        <span class="ml-2 inline-flex rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-800">Padrão</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $addr->neighborhood }} · {{ $addr->city }}/{{ $addr->state }} · CEP {{ $addr->zip_code }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-8 text-center text-gray-500">Nenhum endereço cadastrado.</div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
