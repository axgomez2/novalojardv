<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.settings.artists.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Editar Artista</h1>
    </div>

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('admin.settings.artists.update', $artist) }}" class="rounded-lg bg-white p-6 shadow">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nome *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $artist->name) }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $artist->slug) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="discogs_id" class="block text-sm font-medium text-gray-700">Discogs ID</label>
                    <input type="text" name="discogs_id" id="discogs_id" value="{{ old('discogs_id', $artist->discogs_id) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('discogs_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="discogs_url" class="block text-sm font-medium text-gray-700">URL Discogs</label>
                    <input type="url" name="discogs_url" id="discogs_url" value="{{ old('discogs_url', $artist->discogs_url) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="profile" class="block text-sm font-medium text-gray-700">Perfil/Biografia</label>
                    <textarea name="profile" id="profile" rows="4" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('profile', $artist->profile) }}</textarea>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $artist->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">Ativo</label>
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('admin.settings.artists.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Cancelar</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Salvar</button>
            </div>
        </form>
    </div>
</x-admin-layout>
