<x-admin-layout>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Playlists de DJs</h1>
            <p class="mt-1 text-sm text-gray-600">Gerencie as playlists criadas por DJs parceiros</p>
        </div>
        <a href="{{ route('admin.music.dj-playlists.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Nova Playlist
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 p-4 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-lg bg-white shadow">
        @if($playlists->count() > 0)
            <div class="grid gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($playlists as $playlist)
                    <div class="rounded-lg border border-gray-200 bg-white overflow-hidden hover:shadow-lg transition-shadow">
                        <!-- DJ Image -->
                        <div class="aspect-video bg-gradient-to-br from-purple-600 to-indigo-600 relative">
                            @if($playlist->dj_image)
                                <img src="{{ $playlist->dj_image_url }}" alt="{{ $playlist->dj_name }}" class="w-full h-full object-cover">
                            @else
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <svg class="h-16 w-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            @endif
                            <!-- Status badges -->
                            <div class="absolute top-2 right-2 flex gap-2">
                                @if($playlist->is_featured)
                                    <span class="rounded-full bg-yellow-500 px-2 py-1 text-xs font-medium text-white">Destaque</span>
                                @endif
                                <span class="rounded-full px-2 py-1 text-xs font-medium {{ $playlist->is_active ? 'bg-green-500 text-white' : 'bg-gray-500 text-white' }}">
                                    {{ $playlist->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-4">
                            <h3 class="font-bold text-gray-900">{{ $playlist->dj_name }}</h3>
                            <p class="text-sm text-indigo-600">{{ $playlist->title }}</p>
                            
                            <div class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                </svg>
                                {{ $playlist->tracks->count() }}/10 faixas
                            </div>

                            @if($playlist->dj_description)
                                <p class="mt-2 text-sm text-gray-500 line-clamp-2">{{ $playlist->dj_description }}</p>
                            @endif

                            <!-- Social Links -->
                            @php $socialLinks = $playlist->getSocialLinks(); @endphp
                            @if(count($socialLinks) > 0)
                                <div class="mt-3 flex items-center gap-2">
                                    @foreach($socialLinks as $key => $link)
                                        <a href="{{ $link['url'] }}" target="_blank" class="text-gray-400 hover:text-gray-600" title="{{ $link['label'] }}">
                                            @if($key === 'instagram')
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                            @elseif($key === 'soundcloud')
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M1.175 12.225c-.051 0-.094.046-.101.1l-.233 2.154.233 2.105c.007.058.05.098.101.098.05 0 .09-.04.099-.098l.255-2.105-.27-2.154c-.009-.06-.052-.1-.084-.1zm-.899 1.256c-.057 0-.1.046-.108.1l-.2 1.048.2 1.022c.008.058.051.098.108.098.056 0 .099-.04.107-.098l.228-1.022-.228-1.048c-.008-.06-.051-.1-.107-.1zm1.8-.238c-.06 0-.105.046-.112.1l-.2 1.286.2 1.256c.007.058.052.098.112.098.06 0 .104-.04.112-.098l.228-1.256-.228-1.286c-.008-.06-.052-.1-.112-.1zm.9-.238c-.063 0-.11.046-.117.1l-.171 1.524.171 1.488c.007.058.054.098.117.098.063 0 .11-.04.117-.098l.2-1.488-.2-1.524c-.007-.06-.054-.1-.117-.1zm.9-.238c-.066 0-.114.046-.121.1l-.143 1.762.143 1.72c.007.058.055.098.121.098.066 0 .114-.04.121-.098l.171-1.72-.171-1.762c-.007-.06-.055-.1-.121-.1zm.9-.238c-.069 0-.117.046-.124.1l-.114 2 .114 1.952c.007.058.055.098.124.098.069 0 .117-.04.124-.098l.143-1.952-.143-2c-.007-.06-.055-.1-.124-.1zm.9-.238c-.072 0-.12.046-.127.1l-.086 2.238.086 2.184c.007.058.055.098.127.098.072 0 .12-.04.127-.098l.114-2.184-.114-2.238c-.007-.06-.055-.1-.127-.1zm.9-.238c-.075 0-.123.046-.13.1l-.057 2.476.057 2.416c.007.058.055.098.13.098.075 0 .123-.04.13-.098l.086-2.416-.086-2.476c-.007-.06-.055-.1-.13-.1zm.9-.238c-.078 0-.126.046-.133.1l-.029 2.714.029 2.648c.007.058.055.098.133.098.078 0 .126-.04.133-.098l.057-2.648-.057-2.714c-.007-.06-.055-.1-.133-.1zm.9-.238c-.081 0-.129.046-.136.1l-.001 2.952.001 2.88c.007.058.055.098.136.098.081 0 .129-.04.136-.098l.029-2.88-.029-2.952c-.007-.06-.055-.1-.136-.1zm5.751.238c-.489 0-.951.096-1.371.27-.284-3.21-2.995-5.724-6.293-5.724-.849 0-1.666.173-2.408.486-.276.116-.35.235-.353.466v11.283c.003.242.181.449.424.466h9.999c1.932 0 3.499-1.567 3.499-3.499 0-1.932-1.567-3.499-3.499-3.499z"/></svg>
                                            @elseif($key === 'spotify')
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                                            @else
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4">
                                <span class="text-xs text-gray-400">
                                    {{ $playlist->last_updated_at?->format('d/m/Y') ?? 'Nunca atualizado' }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.music.dj-playlists.edit', $playlist) }}" class="text-indigo-600 hover:text-indigo-900">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.music.dj-playlists.destroy', $playlist) }}" class="inline" onsubmit="return confirm('Excluir esta playlist?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $playlists->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhuma playlist de DJ cadastrada</h3>
                <p class="mt-2 text-sm text-gray-500">Comece criando playlists de DJs parceiros.</p>
                <a href="{{ route('admin.music.dj-playlists.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Criar Playlist
                </a>
            </div>
        @endif
    </div>
</x-admin-layout>
