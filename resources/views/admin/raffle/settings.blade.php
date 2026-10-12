<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.raffle.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Configurações do Sorteio</h1>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4">
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    <div class="rounded-lg bg-white p-6 shadow">
        <form method="POST" action="{{ route('admin.raffle.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Nome da Promoção -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nome da Promoção</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $settings->name) }}" required
                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Data de Início -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Data de Início</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $settings->start_date?->format('Y-m-d')) }}"
                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Data de Término -->
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Data de Término</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $settings->end_date?->format('Y-m-d')) }}"
                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Data do Sorteio -->
                <div>
                    <label for="draw_date" class="block text-sm font-medium text-gray-700 mb-2">Data do Sorteio</label>
                    <input type="date" name="draw_date" id="draw_date" value="{{ old('draw_date', $settings->draw_date?->format('Y-m-d')) }}"
                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ $settings->is_active ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Promoção ativa</span>
                    </label>
                </div>

                <!-- Regulamento -->
                <div class="md:col-span-2">
                    <label for="regulation" class="block text-sm font-medium text-gray-700 mb-2">Regulamento</label>
                    <textarea name="regulation" id="regulation" rows="20"
                              class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">{{ old('regulation', $settings->regulation) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Cole aqui o regulamento completo da promoção.</p>
                </div>
            </div>

            <!-- Info -->
            <div class="mt-6 rounded-lg bg-blue-50 border border-blue-200 p-4">
                <div class="flex gap-3">
                    <svg class="h-5 w-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-blue-700">
                        <p><strong>Número inicial:</strong> {{ $settings->starting_number }}</p>
                        <p><strong>Último número gerado:</strong> {{ $settings->current_number }}</p>
                        <p><strong>Próximo número:</strong> {{ $settings->current_number + 1 }}</p>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.raffle.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Cancelar
                </a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Salvar Configurações
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
