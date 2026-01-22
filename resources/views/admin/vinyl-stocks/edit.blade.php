<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.vinyl-stocks.show', $vinylStock) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Editar Estoque</h1>
        <p class="mt-1 text-sm text-gray-600">{{ $vinylStock->vinylMaster->full_title }}</p>
    </div>

    @if(session('error'))
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.vinyl-stocks.update', $vinylStock) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <!-- Condition & Availability -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Condição e Disponibilidade</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Condição</label>
                            <div class="mt-2 flex gap-4">
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="is_new" value="1" {{ old('is_new', $vinylStock->is_new) ? 'checked' : '' }} class="text-indigo-600">
                                    <span class="text-sm text-gray-700">Novo</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="is_new" value="0" {{ !old('is_new', $vinylStock->is_new) ? 'checked' : '' }} class="text-indigo-600">
                                    <span class="text-sm text-gray-700">Usado</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo de Disco</label>
                            <div class="mt-2 flex gap-4">
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="store_section" value="dj" {{ old('store_section', $vinylStock->store_section) == 'dj' ? 'checked' : '' }} class="text-indigo-600">
                                    <span class="text-sm text-gray-700">DJ (Singles, Maxis, Promos)</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="store_section" value="albums" {{ old('store_section', $vinylStock->store_section) == 'albums' ? 'checked' : '' }} class="text-indigo-600">
                                    <span class="text-sm text-gray-700">Álbum (LPs, Coletâneas)</span>
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Define em qual seção da loja o disco será exibido</p>
                        </div>
                        <div>
                            <label for="availability" class="block text-sm font-medium text-gray-700">Disponibilidade</label>
                            <select name="availability" id="availability" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="available" {{ old('availability', $vinylStock->availability) == 'available' ? 'selected' : '' }}>Disponível</option>
                                <option value="unavailable" {{ old('availability', $vinylStock->availability) == 'unavailable' ? 'selected' : '' }}>Indisponível</option>
                                <option value="featured" {{ old('availability', $vinylStock->availability) == 'featured' ? 'selected' : '' }}>Destaque</option>
                                <option value="preorder" {{ old('availability', $vinylStock->availability) == 'preorder' ? 'selected' : '' }}>Pré-venda</option>
                            </select>
                        </div>
                        <div>
                            <label for="release_date" class="block text-sm font-medium text-gray-700">Data de Lançamento</label>
                            <input type="date" name="release_date" id="release_date" value="{{ old('release_date', $vinylStock->release_date?->format('Y-m-d')) }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="media_status_id" class="block text-sm font-medium text-gray-700">Estado da Mídia</label>
                            <select name="media_status_id" id="media_status_id" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                @foreach($mediaStatuses as $status)
                                    <option value="{{ $status->id }}" {{ old('media_status_id', $vinylStock->media_status_id) == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="cover_status_id" class="block text-sm font-medium text-gray-700">Estado da Capa</label>
                            <select name="cover_status_id" id="cover_status_id" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                @foreach($coverStatuses as $status)
                                    <option value="{{ $status->id }}" {{ old('cover_status_id', $vinylStock->cover_status_id) == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Categories -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Categorias</h3>
                    @php $selectedCategories = old('categories', $vinylStock->categories->pluck('id')->toArray()); @endphp
                    @php $primaryCategory = old('primary_category', $vinylStock->primary_category?->id); @endphp
                    <div class="space-y-4">
                        @foreach($categories as $parent)
                            <div class="rounded-lg border border-gray-200 p-4">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="categories[]" value="{{ $parent->id }}" id="cat_{{ $parent->id }}"
                                           {{ in_array($parent->id, $selectedCategories) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600">
                                    <label for="cat_{{ $parent->id }}" class="font-medium text-gray-900">{{ $parent->name }}</label>
                                    <label class="ml-auto flex items-center gap-1 text-xs text-gray-500">
                                        <input type="radio" name="primary_category" value="{{ $parent->id }}" {{ $primaryCategory == $parent->id ? 'checked' : '' }}>
                                        Principal
                                    </label>
                                </div>
                                @if($parent->children->count() > 0)
                                    <div class="mt-3 ml-6 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                        @foreach($parent->children as $child)
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" name="categories[]" value="{{ $child->id }}" id="cat_{{ $child->id }}"
                                                       {{ in_array($child->id, $selectedCategories) ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-indigo-600">
                                                <label for="cat_{{ $child->id }}" class="text-sm text-gray-700">{{ $child->name }}</label>
                                                <label class="ml-auto flex items-center gap-1 text-xs text-gray-400">
                                                    <input type="radio" name="primary_category" value="{{ $child->id }}" {{ $primaryCategory == $child->id ? 'checked' : '' }}>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Commercial Data -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Dados Comerciais</h3>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label for="sell_price" class="block text-sm font-medium text-gray-700">Preço de Venda *</label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">R$</span>
                                <input type="number" name="sell_price" id="sell_price" value="{{ old('sell_price', $vinylStock->sell_price) }}" step="0.01" min="0" required
                                       class="w-full rounded-lg border-gray-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label for="promotional_price" class="block text-sm font-medium text-gray-700">Preço Promocional</label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">R$</span>
                                <input type="number" name="promotional_price" id="promotional_price" value="{{ old('promotional_price', $vinylStock->promotional_price) }}" step="0.01" min="0"
                                       class="w-full rounded-lg border-gray-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label for="cost_price" class="block text-sm font-medium text-gray-700">Preço de Custo</label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">R$</span>
                                <input type="number" name="cost_price" id="cost_price" value="{{ old('cost_price', $vinylStock->cost_price) }}" step="0.01" min="0"
                                       class="w-full rounded-lg border-gray-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="flex items-center gap-4 sm:col-span-2 lg:col-span-3">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_promotional" value="1" {{ old('is_promotional', $vinylStock->is_promotional) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                                <span class="text-sm text-gray-700">Ativar promoção</span>
                            </label>
                        </div>
                        <div>
                            <label for="promo_starts_at" class="block text-sm font-medium text-gray-700">Início da Promoção</label>
                            <input type="datetime-local" name="promo_starts_at" id="promo_starts_at" value="{{ old('promo_starts_at', $vinylStock->promo_starts_at?->format('Y-m-d\TH:i')) }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="promo_ends_at" class="block text-sm font-medium text-gray-700">Fim da Promoção</label>
                            <input type="datetime-local" name="promo_ends_at" id="promo_ends_at" value="{{ old('promo_ends_at', $vinylStock->promo_ends_at?->format('Y-m-d\TH:i')) }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700">Fornecedor</label>
                            <select name="supplier_id" id="supplier_id" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id', $vinylStock->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="barcode" class="block text-sm font-medium text-gray-700">Código de Barras</label>
                            <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $vinylStock->barcode) }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="internal_code" class="block text-sm font-medium text-gray-700">Código Interno</label>
                            <input type="text" name="internal_code" id="internal_code" value="{{ old('internal_code', $vinylStock->internal_code) }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="weight_id" class="block text-sm font-medium text-gray-700">Peso</label>
                            <select name="weight_id" id="weight_id" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                @foreach($weights as $weight)
                                    <option value="{{ $weight->id }}" {{ old('weight_id', $vinylStock->weight_id) == $weight->id ? 'selected' : '' }}>{{ $weight->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Physical -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Características Físicas</h3>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label for="format" class="block text-sm font-medium text-gray-700">Formato</label>
                            <select name="format" id="format" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                <option value="LP" {{ old('format', $vinylStock->format) == 'LP' ? 'selected' : '' }}>LP (12")</option>
                                <option value="7"" {{ old('format', $vinylStock->format) == '7"' ? 'selected' : '' }}>7" (Compacto)</option>
                                <option value="10"" {{ old('format', $vinylStock->format) == '10"' ? 'selected' : '' }}>10"</option>
                                <option value="12"" {{ old('format', $vinylStock->format) == '12"' ? 'selected' : '' }}>12" (Maxi)</option>
                            </select>
                        </div>
                        <div>
                            <label for="num_discs" class="block text-sm font-medium text-gray-700">Nº de Discos</label>
                            <input type="number" name="num_discs" id="num_discs" value="{{ old('num_discs', $vinylStock->num_discs) }}" min="1"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="speed" class="block text-sm font-medium text-gray-700">Rotação</label>
                            <select name="speed" id="speed" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                <option value="33 RPM" {{ old('speed', $vinylStock->speed) == '33 RPM' ? 'selected' : '' }}>33 RPM</option>
                                <option value="45 RPM" {{ old('speed', $vinylStock->speed) == '45 RPM' ? 'selected' : '' }}>45 RPM</option>
                                <option value="78 RPM" {{ old('speed', $vinylStock->speed) == '78 RPM' ? 'selected' : '' }}>78 RPM</option>
                            </select>
                        </div>
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700">Cor do Vinil</label>
                            <input type="text" name="color" id="color" value="{{ old('color', $vinylStock->color) }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="edition" class="block text-sm font-medium text-gray-700">Edição</label>
                            <input type="text" name="edition" id="edition" value="{{ old('edition', $vinylStock->edition) }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="catalog_number" class="block text-sm font-medium text-gray-700">Nº de Catálogo</label>
                            <input type="text" name="catalog_number" id="catalog_number" value="{{ old('catalog_number', $vinylStock->catalog_number) }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="dimension_id" class="block text-sm font-medium text-gray-700">Dimensões</label>
                            <select name="dimension_id" id="dimension_id" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                @foreach($dimensions as $dimension)
                                    <option value="{{ $dimension->id }}" {{ old('dimension_id', $vinylStock->dimension_id) == $dimension->id ? 'selected' : '' }}>{{ $dimension->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Estoque Mínimo</h3>
                    <div>
                        <label for="stock_min" class="block text-sm font-medium text-gray-700">Quantidade mínima (alerta)</label>
                        <input type="number" name="stock_min" id="stock_min" value="{{ old('stock_min', $vinylStock->stock_min) }}" min="0"
                               class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <p class="mt-2 text-xs text-gray-500">O estoque atual é gerenciado via movimentações na página de detalhes.</p>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Observações</h3>
                    <textarea name="notes" rows="4" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $vinylStock->notes) }}</textarea>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-medium text-white hover:bg-indigo-700">
                        Salvar Alterações
                    </button>
                    <a href="{{ route('admin.vinyl-stocks.show', $vinylStock) }}" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
</x-admin-layout>
