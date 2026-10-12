<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.raffle.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar
            </a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">Números de {{ $client->name }}</h1>
            <p class="text-sm text-gray-600">{{ $client->email }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.raffle.client.pdf', $client) }}" 
               class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Baixar PDF
            </a>
            <form action="{{ route('admin.raffle.client.send-voucher', $client) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Enviar por Email
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4">
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Status do Aceite -->
    <div class="mb-6 rounded-lg {{ $acceptance && $acceptance->accepted ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200' }} border p-4">
        <div class="flex items-center gap-3">
            @if($acceptance && $acceptance->accepted)
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-medium text-green-800">Termos aceitos</p>
                    <p class="text-sm text-green-600">Em {{ $acceptance->accepted_at->format('d/m/Y H:i') }}</p>
                </div>
            @else
                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="font-medium text-yellow-800">Termos ainda não aceitos</p>
                    <p class="text-sm text-yellow-600">O cliente precisa aceitar os termos da promoção</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Total de Números</p>
            <p class="text-3xl font-bold text-gray-900">{{ $numbers->count() }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Números Ativos</p>
            <p class="text-3xl font-bold text-green-600">{{ $numbers->where('status', 'active')->count() }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Números Cancelados</p>
            <p class="text-3xl font-bold text-red-600">{{ $numbers->where('status', 'cancelled')->count() }}</p>
        </div>
    </div>

    <!-- Lista de Números -->
    <div class="rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 p-4">
            <h3 class="text-lg font-medium text-gray-900">Números do Sorteio</h3>
        </div>
        
        <div class="grid grid-cols-2 gap-4 p-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            @forelse($numbers as $number)
                <div class="rounded-lg border {{ $number->status === 'active' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} p-4 text-center">
                    <p class="font-mono text-2xl font-bold {{ $number->status === 'active' ? 'text-green-700' : 'text-red-700 line-through' }}">
                        {{ $number->formatted_number }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">{{ $number->created_at->format('d/m/Y') }}</p>
                    @if($number->status === 'cancelled')
                        <p class="mt-1 text-xs text-red-600">Cancelado</p>
                    @endif
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-gray-500">
                    Nenhum número cadastrado para este cliente.
                </div>
            @endforelse
        </div>
    </div>
</x-admin-layout>
