<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.vinyls.show', $vinyl) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Editar Disco</h1>
        <p class="mt-1 text-sm text-gray-600">{{ $vinyl->full_title }}</p>
    </div>

    <form method="POST" action="{{ route('admin.vinyls.update', $vinyl) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Cover Image -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 rounded-lg bg-white p-4 shadow">
                    <div class="aspect-square overflow-hidden rounded-lg bg-gray-100">
                        @if($vinyl->cover_url)
                            <img src="{{ $vinyl->cover_url }}" alt="{{ $vinyl->title }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center">
                                <svg class="h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Form Fields -->
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Informações Básicas</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="title" class="block text-sm font-medium text-gray-700">Título *</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $vinyl->title) }}" required
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="release_year" class="block text-sm font-medium text-gray-700">Ano de Lançamento</label>
                            <input type="number" name="release_year" id="release_year" value="{{ old('release_year', $vinyl->release_year) }}" min="1900" max="{{ date('Y') + 1 }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700">País</label>
                            <input type="text" name="country" id="country" value="{{ old('country', $vinyl->country) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="record_label_id" class="block text-sm font-medium text-gray-700">Gravadora</label>
                            <select name="record_label_id" id="record_label_id"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                @foreach($recordLabels as $label)
                                    <option value="{{ $label->id }}" {{ old('record_label_id', $vinyl->record_label_id) == $label->id ? 'selected' : '' }}>
                                        {{ $label->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="description" id="description" rows="4"
                                      class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $vinyl->description) }}</textarea>
                        </div>
                        <div>
                            <label for="genres" class="block text-sm font-medium text-gray-700">Gêneros</label>
                            <input type="text" name="genres" id="genres" value="{{ old('genres', $vinyl->genres ? implode(', ', $vinyl->genres) : '') }}"
                                   placeholder="Rock, Pop, Jazz..."
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Separe por vírgulas</p>
                        </div>
                        <div>
                            <label for="styles" class="block text-sm font-medium text-gray-700">Estilos</label>
                            <input type="text" name="styles" id="styles" value="{{ old('styles', $vinyl->styles ? implode(', ', $vinyl->styles) : '') }}"
                                   placeholder="Hard Rock, Prog Rock..."
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Separe por vírgulas</p>
                        </div>
                    </div>
                </div>

                <!-- Artists (read-only) -->
                @if($vinyl->artists->isNotEmpty())
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Artistas</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($vinyl->artists as $artist)
                                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-800">
                                    {{ $artist->name }}
                                    @if($artist->pivot->role !== 'main')
                                        <span class="text-indigo-500">({{ $artist->pivot->role }})</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Para alterar artistas, exclua e recadastre o disco.</p>
                    </div>
                @endif

                <!-- Tracks (read-only) -->
                @if($vinyl->tracks->isNotEmpty())
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Faixas</h3>
                        <div class="overflow-hidden rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Pos.</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Título</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Duração</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach($vinyl->tracks as $track)
                                        <tr>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-500">{{ $track->position }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $track->name }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-500">{{ $track->duration ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('admin.vinyls.show', $vinyl) }}" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Salvar Alterações
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-admin-layout>
