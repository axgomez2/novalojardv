<x-admin-layout>
    <div class="mb-8">
        @if($vinylMaster)
            <a href="{{ route('admin.vinyls.show', $vinylMaster) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Ver Disco
            </a>
        @else
            <a href="{{ route('admin.vinyl-stocks.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Voltar
            </a>
        @endif
        <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $vinylMaster ? 'Etapa 3: Estoque e Preços' : 'Novo Estoque de Disco' }}</h1>
        <p class="mt-1 text-sm text-gray-600">Cadastre os dados variáveis do disco para venda</p>
    </div>

    @if($vinylMaster)
        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center">
                <div class="flex items-center">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="ml-3 text-sm font-medium text-green-600">Buscar no Discogs</span>
                </div>
                <div class="mx-4 h-0.5 flex-1 bg-green-500"></div>
                <div class="flex items-center">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="ml-3 text-sm font-medium text-green-600">Dados do Disco</span>
                </div>
                <div class="mx-4 h-0.5 flex-1 bg-indigo-600"></div>
                <div class="flex items-center">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white">
                        <span class="text-sm font-medium">3</span>
                    </div>
                    <span class="ml-3 text-sm font-medium text-indigo-600">Estoque e Preços</span>
                </div>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="ml-3 text-sm text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.vinyl-stocks.store') }}" class="space-y-6">
        @csrf

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Left Column -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Vinyl Selection -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Disco</h3>
                    @if($vinylMaster)
                        <input type="hidden" name="vinyl_master_id" value="{{ $vinylMaster->id }}">
                        <div class="flex items-center gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded bg-gray-100">
                                @if($vinylMaster->cover_url)
                                    <img src="{{ $vinylMaster->cover_url }}" alt="{{ $vinylMaster->title }}" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $vinylMaster->title }}</p>
                                <p class="text-sm text-indigo-600">{{ $vinylMaster->artist_names }}</p>
                                @if($vinylMaster->release_year)
                                    <p class="text-xs text-gray-500">{{ $vinylMaster->release_year }}</p>
                                @endif
                            </div>
                        </div>
                    @else
                        <select name="vinyl_master_id" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecione um disco...</option>
                            @foreach($vinylMasters as $vm)
                                <option value="{{ $vm->id }}" {{ old('vinyl_master_id') == $vm->id ? 'selected' : '' }}>
                                    {{ $vm->full_title }}
                                </option>
                            @endforeach
                        </select>
                        @error('vinyl_master_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    @endif
                </div>

                <!-- Part 1: Condition & Availability -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Condição e Disponibilidade</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Condição *</label>
                            <div class="mt-2 flex gap-4">
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="is_new" value="1" {{ old('is_new', '1') == '1' ? 'checked' : '' }} class="text-indigo-600">
                                    <span class="text-sm text-gray-700">Novo</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="is_new" value="0" {{ old('is_new') === '0' ? 'checked' : '' }} class="text-indigo-600">
                                    <span class="text-sm text-gray-700">Usado</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo de Disco *</label>
                            <div class="mt-2 flex gap-4">
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="store_section" value="dj" {{ old('store_section', 'dj') == 'dj' ? 'checked' : '' }} class="text-indigo-600">
                                    <span class="text-sm text-gray-700">DJ (Singles, Maxis, Promos)</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="store_section" value="albums" {{ old('store_section') == 'albums' ? 'checked' : '' }} class="text-indigo-600">
                                    <span class="text-sm text-gray-700">Álbum (LPs, Coletâneas)</span>
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Define em qual seção da loja o disco será exibido</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="product_type_id" class="block text-sm font-medium text-gray-700">Tipo de Produto *</label>
                            <select name="product_type_id" id="product_type_id" required
                                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione o tipo de produto...</option>
                                @foreach($productTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('product_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Categoria principal usada pela API e pela vitrine da loja</p>
                            @error('product_type_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="availability" class="block text-sm font-medium text-gray-700">Disponibilidade *</label>
                            <select name="availability" id="availability" required x-data x-on:change="$refs.releaseDate.classList.toggle('hidden', $event.target.value !== 'preorder')"
                                    class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="available" {{ old('availability', 'available') == 'available' ? 'selected' : '' }}>Disponível</option>
                                <option value="unavailable" {{ old('availability') == 'unavailable' ? 'selected' : '' }}>Indisponível</option>
                                <option value="featured" {{ old('availability') == 'featured' ? 'selected' : '' }}>Destaque</option>
                                <option value="preorder" {{ old('availability') == 'preorder' ? 'selected' : '' }}>Pré-venda</option>
                            </select>
                        </div>
                        <div x-ref="releaseDate" class="{{ old('availability') == 'preorder' ? '' : 'hidden' }} sm:col-span-2">
                            <label for="release_date" class="block text-sm font-medium text-gray-700">Data de Lançamento (Pré-venda)</label>
                            <input type="date" name="release_date" id="release_date" value="{{ old('release_date') }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="media_status_id" class="block text-sm font-medium text-gray-700">Estado da Mídia</label>
                            <select name="media_status_id" id="media_status_id" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                @foreach($mediaStatuses as $status)
                                    <option value="{{ $status->id }}" {{ old('media_status_id') == $status->id ? 'selected' : '' }}>{{ $status->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="cover_status_id" class="block text-sm font-medium text-gray-700">Estado da Capa</label>
                            <select name="cover_status_id" id="cover_status_id" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                @foreach($coverStatuses as $status)
                                    <option value="{{ $status->id }}" {{ old('cover_status_id') == $status->id ? 'selected' : '' }}>{{ $status->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Part 2: Categories -->
                <div class="rounded-lg bg-white p-6 shadow" x-data="categoriesPanel()">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Categorias</h3>
                        <button type="button" @click="showForm = !showForm"
                                class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Nova categoria
                        </button>
                    </div>
                    <p class="mb-4 text-sm text-gray-500">Clique em uma categoria principal para ver as subcategorias. Marque múltiplas categorias e defina a principal.</p>

                    {{-- Form inline para criar nova categoria --}}
                    <div x-show="showForm" x-cloak x-collapse class="mb-4 rounded-lg border border-indigo-200 bg-indigo-50 p-4">
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-700">Nome da categoria</label>
                                <input type="text" x-model="newName" @keydown.enter.prevent="createCategory()"
                                       placeholder="Ex: Deep House"
                                       class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Categoria pai (opcional)</label>
                                <select x-model="newParentId" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">— Nenhuma (raiz) —</option>
                                    @foreach($categories as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                    @endforeach
                                    <template x-for="c in createdParents" :key="c.id">
                                        <option :value="c.id" x-text="c.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-2">
                            <button type="button" @click="createCategory()" :disabled="creating || !newName.trim()"
                                    class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                                <span x-text="creating ? 'Criando...' : 'Criar e adicionar'"></span>
                            </button>
                            <button type="button" @click="showForm = false; newName = ''"
                                    class="text-xs text-gray-600 hover:text-gray-800">Cancelar</button>
                            <span x-show="error" class="text-xs text-red-600" x-text="error"></span>
                        </div>
                    </div>

                    {{-- Categorias criadas inline --}}
                    <template x-for="cat in created" :key="cat.id">
                        <div class="mb-2 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-3">
                            <input type="checkbox" :name="'categories[]'" :value="cat.id" :id="'cat_new_' + cat.id" checked
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label :for="'cat_new_' + cat.id" class="flex-1 font-medium text-gray-900">
                                <span x-text="cat.name"></span>
                                <span x-show="cat.parent_name" class="text-xs text-gray-500"> (em <span x-text="cat.parent_name"></span>)</span>
                                <span class="ml-2 rounded bg-green-200 px-1.5 py-0.5 text-[10px] uppercase text-green-800">nova</span>
                            </label>
                            <label class="flex items-center gap-1 text-xs text-gray-500">
                                <input type="radio" name="primary_category" :value="cat.id" class="text-indigo-600 focus:ring-indigo-500">
                                <span>Principal</span>
                            </label>
                        </div>
                    </template>
                    <div class="space-y-2">
                        @foreach($categories as $parent)
                            <div class="rounded-lg border border-gray-200 overflow-hidden">
                                <!-- Parent Category Header -->
                                <div class="flex items-center gap-3 p-3 bg-gray-50 cursor-pointer hover:bg-gray-100 transition-colors"
                                     @click="expandedCategories.includes({{ $parent->id }}) ? expandedCategories = expandedCategories.filter(id => id !== {{ $parent->id }}) : expandedCategories.push({{ $parent->id }})">
                                    <div class="flex items-center gap-3 flex-1" @click.stop>
                                        <input type="checkbox" name="categories[]" value="{{ $parent->id }}" id="cat_{{ $parent->id }}"
                                               {{ in_array($parent->id, old('categories', [])) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <label for="cat_{{ $parent->id }}" class="font-medium text-gray-900 cursor-pointer">{{ $parent->name }}</label>
                                        @if($parent->children->count() > 0)
                                            <span class="text-xs text-gray-400">({{ $parent->children->count() }} subcategorias)</span>
                                        @endif
                                    </div>
                                    <label class="flex items-center gap-1 text-xs text-gray-500" @click.stop>
                                        <input type="radio" name="primary_category" value="{{ $parent->id }}" {{ old('primary_category') == $parent->id ? 'checked' : '' }}
                                               class="text-indigo-600 focus:ring-indigo-500">
                                        <span>Principal</span>
                                    </label>
                                    @if($parent->children->count() > 0)
                                        <svg class="h-5 w-5 text-gray-400 transition-transform" :class="{ 'rotate-180': expandedCategories.includes({{ $parent->id }}) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    @endif
                                </div>
                                <!-- Child Categories -->
                                @if($parent->children->count() > 0)
                                    <div x-show="expandedCategories.includes({{ $parent->id }})" x-collapse class="border-t border-gray-200 bg-white p-3">
                                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                            @foreach($parent->children as $child)
                                                <div class="flex items-center gap-2 p-2 rounded hover:bg-gray-50">
                                                    <input type="checkbox" name="categories[]" value="{{ $child->id }}" id="cat_{{ $child->id }}"
                                                           {{ in_array($child->id, old('categories', [])) ? 'checked' : '' }}
                                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                    <label for="cat_{{ $child->id }}" class="text-sm text-gray-700 cursor-pointer flex-1">{{ $child->name }}</label>
                                                    <label class="flex items-center text-xs text-gray-400">
                                                        <input type="radio" name="primary_category" value="{{ $child->id }}" {{ old('primary_category') == $child->id ? 'checked' : '' }}
                                                               class="text-indigo-600 focus:ring-indigo-500">
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        @if($categories->isEmpty())
                            <p class="text-sm text-gray-500">Nenhuma categoria cadastrada. <a href="{{ route('admin.categories.create') }}" class="text-indigo-600 hover:underline">Criar categoria</a></p>
                        @endif
                    </div>
                </div>

                <!-- Part 3: Commercial Data -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Dados Comerciais</h3>

                    @if(!empty($marketplaceStats))
                        @php
                            $lowestValue = data_get($marketplaceStats, 'lowest_price.value');
                            $lowestCurrency = data_get($marketplaceStats, 'lowest_price.currency', 'BRL');
                            $numForSale = data_get($marketplaceStats, 'num_for_sale');
                            $discogsUrl = $vinylMaster?->discogs_url ?? ($vinylMaster?->discogs_release_id ? 'https://www.discogs.com/release/' . $vinylMaster->discogs_release_id : null);
                        @endphp
                        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex gap-3">
                                    <svg class="h-5 w-5 flex-shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <div class="text-sm">
                                        <p class="font-medium text-amber-900">Referência do Discogs Marketplace</p>
                                        <div class="mt-1 flex flex-wrap gap-x-6 gap-y-1 text-amber-800">
                                            @if($lowestValue)
                                                <span><strong>Menor preço:</strong> {{ number_format($lowestValue, 2, ',', '.') }} {{ $lowestCurrency }}</span>
                                            @else
                                                <span><strong>Menor preço:</strong> indisponível</span>
                                            @endif
                                            <span><strong>À venda no mundo:</strong> {{ $numForSale ?? 0 }} {{ ($numForSale ?? 0) == 1 ? 'cópia' : 'cópias' }}</span>
                                        </div>
                                        <p class="mt-1 text-xs text-amber-700">Use como referência para precificar — especialmente discos usados. Preços são da menor oferta global.</p>
                                    </div>
                                </div>
                                @if($discogsUrl)
                                    <a href="{{ $discogsUrl }}" target="_blank" class="flex-shrink-0 text-xs text-amber-700 underline hover:text-amber-900">Ver no Discogs</a>
                                @endif
                            </div>
                            @if($lowestValue)
                                <div class="mt-3 flex items-center gap-2">
                                    <button type="button"
                                            onclick="document.getElementById('sell_price').value = '{{ number_format($lowestValue, 2, '.', '') }}'"
                                            class="rounded-md bg-amber-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-amber-700">
                                        Usar menor preço como sugestão
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label for="sell_price" class="block text-sm font-medium text-gray-700">Preço de Venda *</label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">R$</span>
                                <input type="number" name="sell_price" id="sell_price" value="{{ old('sell_price') }}" step="0.01" min="0" required
                                       class="w-full rounded-lg border-gray-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            @error('sell_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="promotional_price" class="block text-sm font-medium text-gray-700">Preço Promocional</label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">R$</span>
                                <input type="number" name="promotional_price" id="promotional_price" value="{{ old('promotional_price') }}" step="0.01" min="0"
                                       class="w-full rounded-lg border-gray-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label for="cost_price" class="block text-sm font-medium text-gray-700">Preço de Custo</label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">R$</span>
                                <input type="number" name="cost_price" id="cost_price" value="{{ old('cost_price') }}" step="0.01" min="0"
                                       class="w-full rounded-lg border-gray-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="flex items-center gap-4 sm:col-span-2 lg:col-span-3">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_promotional" value="1" {{ old('is_promotional') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                                <span class="text-sm text-gray-700">Ativar promoção</span>
                            </label>
                        </div>
                        <div>
                            <label for="promo_starts_at" class="block text-sm font-medium text-gray-700">Início da Promoção</label>
                            <input type="datetime-local" name="promo_starts_at" id="promo_starts_at" value="{{ old('promo_starts_at') }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="promo_ends_at" class="block text-sm font-medium text-gray-700">Fim da Promoção</label>
                            <input type="datetime-local" name="promo_ends_at" id="promo_ends_at" value="{{ old('promo_ends_at') }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700">Fornecedor</label>
                            <select name="supplier_id" id="supplier_id" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="barcode" class="block text-sm font-medium text-gray-700">Código de Barras</label>
                            <input type="text" name="barcode" id="barcode" value="{{ old('barcode') }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="internal_code" class="block text-sm font-medium text-gray-700">Código Interno</label>
                            <input type="text" name="internal_code" id="internal_code" value="{{ old('internal_code', $nextInternalCode) }}" readonly
                                   class="mt-1 w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                            <p class="mt-1 text-xs text-gray-500">Gerado automaticamente</p>
                        </div>
                    </div>
                </div>

                <!-- Physical Characteristics -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Características Físicas</h3>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label for="format" class="block text-sm font-medium text-gray-700">Formato</label>
                            <select name="format" id="format" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                <option value="LP" {{ old('format') == 'LP' ? 'selected' : '' }}>LP (12")</option>
                                <option value="7&quot;" {{ old('format') == '7"' ? 'selected' : '' }}>7" (Compacto)</option>
                                <option value="10&quot;" {{ old('format') == '10"' ? 'selected' : '' }}>10"</option>
                                <option value="12&quot;" {{ old('format') == '12"' ? 'selected' : '' }}>12" (Maxi)</option>
                            </select>
                        </div>
                        <div>
                            <label for="num_discs" class="block text-sm font-medium text-gray-700">Nº de Discos</label>
                            <input type="number" name="num_discs" id="num_discs" value="{{ old('num_discs', 1) }}" min="1"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="speed" class="block text-sm font-medium text-gray-700">Rotação</label>
                            <select name="speed" id="speed" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                <option value="33 RPM" {{ old('speed') == '33 RPM' ? 'selected' : '' }}>33 RPM</option>
                                <option value="45 RPM" {{ old('speed') == '45 RPM' ? 'selected' : '' }}>45 RPM</option>
                                <option value="78 RPM" {{ old('speed') == '78 RPM' ? 'selected' : '' }}>78 RPM</option>
                            </select>
                        </div>
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700">Cor do Vinil</label>
                            <input type="text" name="color" id="color" value="{{ old('color') }}" placeholder="Ex: Preto, Vermelho..."
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="edition" class="block text-sm font-medium text-gray-700">Edição</label>
                            <input type="text" name="edition" id="edition" value="{{ old('edition') }}" placeholder="Ex: Limitada, Deluxe..."
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="catalog_number" class="block text-sm font-medium text-gray-700">Nº de Catálogo</label>
                            <input type="text" name="catalog_number" id="catalog_number" value="{{ old('catalog_number') }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="weight_id" class="block text-sm font-medium text-gray-700">Peso</label>
                            <select name="weight_id" id="weight_id" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                @foreach($weights as $weight)
                                    <option value="{{ $weight->id }}" {{ old('weight_id') == $weight->id ? 'selected' : '' }}>
                                        {{ $weight->name }} ({{ $weight->formatted_value }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="dimension_id" class="block text-sm font-medium text-gray-700">Dimensões</label>
                            <select name="dimension_id" id="dimension_id" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                @foreach($dimensions as $dimension)
                                    <option value="{{ $dimension->id }}" {{ old('dimension_id') == $dimension->id ? 'selected' : '' }}>
                                        {{ $dimension->name }} ({{ $dimension->formatted_dimensions }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Stock & Notes -->
            <div class="space-y-6">
                <!-- Stock -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Estoque</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700">Quantidade Inicial</label>
                            <input type="number" name="stock" id="stock" value="{{ old('stock', 0) }}" min="0"
                                   class="mt-1 w-full rounded-lg border-gray-300 text-center text-2xl font-bold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="stock_min" class="block text-sm font-medium text-gray-700">Estoque Mínimo (alerta)</label>
                            <input type="number" name="stock_min" id="stock_min" value="{{ old('stock_min', 0) }}" min="0"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Description with AI Generation -->
                @if($vinylMaster)
                <div class="rounded-lg bg-white p-6 shadow" x-data="descriptionGenerator()">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Descrição do Produto</h3>
                        <button type="button" 
                                @click="generateDescription()"
                                :disabled="generating"
                                class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-purple-500 to-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:from-purple-600 hover:to-indigo-700 disabled:opacity-50">
                            <svg x-show="!generating" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <svg x-show="generating" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="generating ? 'Gerando...' : 'Gerar com IA'"></span>
                        </button>
                    </div>
                    <textarea name="description" id="description" rows="6" x-model="description"
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                              placeholder="Descrição do produto para exibição na loja...">{{ old('description') }}</textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        <svg class="inline h-3 w-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Clique em "Gerar com IA" para criar uma descrição baseada nos dados do disco
                    </p>
                </div>
                @endif

                <!-- Notes -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Observações Internas</h3>
                    <textarea name="notes" rows="4" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Observações internas (não visíveis na loja)...">{{ old('notes') }}</textarea>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-3">
                    <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-medium text-white hover:bg-indigo-700">
                        Cadastrar Estoque
                    </button>
                    <a href="{{ route('admin.vinyl-stocks.index') }}" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>

    @if($vinylMaster)
    <script>
        function descriptionGenerator() {
            return {
                description: '{{ old("description", "") }}',
                generating: false,
                
                generateDescription() {
                    this.generating = true;
                    
                    // Collect vinyl data
                    const vinylData = {
                        title: @json($vinylMaster->title),
                        artists: @json($vinylMaster->artist_names),
                        year: @json($vinylMaster->release_year),
                        country: @json($vinylMaster->country),
                        genres: @json($vinylMaster->genres ?? []),
                        styles: @json($vinylMaster->styles ?? []),
                        label: @json($vinylMaster->recordLabel?->name),
                        tracks: @json($vinylMaster->tracks->pluck('name')->toArray()),
                        description: @json($vinylMaster->description)
                    };
                    
                    // Generate description locally (simulated AI)
                    setTimeout(() => {
                        this.description = this.buildDescription(vinylData);
                        this.generating = false;
                    }, 800);
                },
                
                buildDescription(data) {
                    let desc = '';
                    
                    // Opening
                    if (data.artists && data.title) {
                        desc += `${data.title} é um álbum ${data.year ? `de ${data.year} ` : ''}do artista ${data.artists}`;
                    } else if (data.title) {
                        desc += `${data.title}${data.year ? ` (${data.year})` : ''}`;
                    }
                    
                    // Label and country
                    if (data.label || data.country) {
                        desc += ', lançado';
                        if (data.label) desc += ` pela ${data.label}`;
                        if (data.country) desc += ` (${data.country})`;
                    }
                    desc += '.\n\n';
                    
                    // Genres and styles
                    const allGenres = [...(data.genres || []), ...(data.styles || [])];
                    if (allGenres.length > 0) {
                        desc += `Este disco de vinil apresenta sonoridades de ${allGenres.slice(0, 3).join(', ')}`;
                        if (allGenres.length > 3) {
                            desc += ` e outros estilos`;
                        }
                        desc += '.\n\n';
                    }
                    
                    // Tracks
                    if (data.tracks && data.tracks.length > 0) {
                        desc += `O álbum contém ${data.tracks.length} faixas`;
                        if (data.tracks.length <= 5) {
                            desc += `: ${data.tracks.join(', ')}`;
                        } else {
                            desc += `, incluindo "${data.tracks[0]}", "${data.tracks[1]}" e "${data.tracks[2]}"`;
                        }
                        desc += '.\n\n';
                    }
                    
                    // Closing
                    desc += 'Disco de vinil em excelente estado, ideal para colecionadores e amantes da música em formato analógico.';
                    
                    return desc;
                }
            }
        }
    </script>
    @endif

    <script>
        function categoriesPanel() {
            const parentNameMap = @json($categories->pluck('name', 'id'));
            return {
                expandedCategories: [],
                showForm: false,
                newName: '',
                newParentId: '',
                creating: false,
                error: '',
                created: [],
                get createdParents() {
                    return this.created.filter(c => !c.parent_id);
                },
                async createCategory() {
                    if (!this.newName.trim() || this.creating) return;
                    this.creating = true;
                    this.error = '';
                    try {
                        const res = await fetch('{{ route('admin.categories.ajax') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                name: this.newName.trim(),
                                parent_id: this.newParentId || null,
                            }),
                        });
                        const data = await res.json();
                        if (!res.ok) {
                            this.error = data.message || 'Erro ao criar categoria.';
                            return;
                        }
                        const parentName = data.parent_id
                            ? (parentNameMap[data.parent_id] || this.created.find(c => c.id === data.parent_id)?.name || null)
                            : null;
                        this.created.push({
                            id: data.id,
                            name: data.name,
                            parent_id: data.parent_id,
                            parent_name: parentName,
                        });
                        this.newName = '';
                        this.newParentId = '';
                    } catch (e) {
                        this.error = 'Erro de rede: ' + e.message;
                    } finally {
                        this.creating = false;
                    }
                }
            }
        }
    </script>
</x-admin-layout>
