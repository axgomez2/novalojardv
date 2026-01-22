<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.settings.dimensions.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Nova Dimensão</h1>
    </div>

    <div class="mx-auto max-w-lg">
        <form method="POST" action="{{ route('admin.settings.dimensions.store') }}" class="rounded-lg bg-white p-6 shadow">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nome *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Ex: LP 12&quot;, CD, Box Set" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="height" class="block text-sm font-medium text-gray-700">Altura *</label>
                        <input type="number" name="height" id="height" value="{{ old('height') }}" required min="0.01" step="0.01" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('height')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="width" class="block text-sm font-medium text-gray-700">Largura *</label>
                        <input type="number" name="width" id="width" value="{{ old('width') }}" required min="0.01" step="0.01" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('width')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="depth" class="block text-sm font-medium text-gray-700">Profund. *</label>
                        <input type="number" name="depth" id="depth" value="{{ old('depth') }}" required min="0.01" step="0.01" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('depth')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label for="unit" class="block text-sm font-medium text-gray-700">Unidade *</label>
                    <select name="unit" id="unit" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="cm" {{ old('unit', 'cm') === 'cm' ? 'selected' : '' }}>Centímetros (cm)</option>
                        <option value="mm" {{ old('unit') === 'mm' ? 'selected' : '' }}>Milímetros (mm)</option>
                        <option value="m" {{ old('unit') === 'm' ? 'selected' : '' }}>Metros (m)</option>
                    </select>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">Ativo</label>
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('admin.settings.dimensions.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Cancelar</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Salvar</button>
            </div>
        </form>
    </div>
</x-admin-layout>
