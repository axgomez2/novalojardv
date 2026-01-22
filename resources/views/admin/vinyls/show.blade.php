<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.vinyls.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar
        </a>
        <div class="mt-2 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">{{ $vinyl->full_title }}</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.vinyls.edit', $vinyl) }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Editar
                </a>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Cover Image -->
        <div class="lg:col-span-1">
            <div class="sticky top-24 space-y-4">
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="aspect-square overflow-hidden bg-gray-100">
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
                    @if($vinyl->discogs_url)
                        <div class="border-t border-gray-100 p-4">
                            <a href="{{ $vinyl->discogs_url }}" target="_blank" class="flex items-center justify-center gap-2 text-sm text-indigo-600 hover:text-indigo-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                Ver no Discogs
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Quick Stats -->
                <div class="rounded-lg bg-white p-4 shadow">
                    <dl class="space-y-3">
                        @if($vinyl->release_year)
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Ano</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $vinyl->release_year }}</dd>
                            </div>
                        @endif
                        @if($vinyl->country)
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">País</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $vinyl->country }}</dd>
                            </div>
                        @endif
                        @if($vinyl->recordLabel)
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Gravadora</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $vinyl->recordLabel->name }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Faixas</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $vinyl->tracks->count() }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Artists -->
            @if($vinyl->mainArtists->isNotEmpty())
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Artistas</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($vinyl->mainArtists as $artist)
                            <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ $artist->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Description -->
            @if($vinyl->description)
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Descrição</h3>
                    <div class="prose prose-sm max-w-none text-gray-600">
                        {!! nl2br(e($vinyl->description)) !!}
                    </div>
                </div>
            @endif

            <!-- Genres & Styles -->
            @if($vinyl->genres || $vinyl->styles)
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Gêneros e Estilos</h3>
                    @if($vinyl->genres)
                        <div class="mb-3">
                            <span class="text-sm font-medium text-gray-500">Gêneros:</span>
                            <div class="mt-1 flex flex-wrap gap-2">
                                @foreach($vinyl->genres as $genre)
                                    <span class="rounded-full bg-purple-100 px-3 py-1 text-sm text-purple-800">{{ $genre }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if($vinyl->styles)
                        <div>
                            <span class="text-sm font-medium text-gray-500">Estilos:</span>
                            <div class="mt-1 flex flex-wrap gap-2">
                                @foreach($vinyl->styles as $style)
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-800">{{ $style }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Tracklist -->
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

            <!-- Metadata -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Informações do Sistema</h3>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-gray-500">ID</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $vinyl->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Slug</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $vinyl->slug }}</dd>
                    </div>
                    @if($vinyl->discogs_release_id)
                        <div>
                            <dt class="text-sm text-gray-500">Discogs Release ID</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $vinyl->discogs_release_id }}</dd>
                        </div>
                    @endif
                    @if($vinyl->discogs_master_id)
                        <div>
                            <dt class="text-sm text-gray-500">Discogs Master ID</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $vinyl->discogs_master_id }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm text-gray-500">Criado em</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $vinyl->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Atualizado em</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $vinyl->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-admin-layout>
