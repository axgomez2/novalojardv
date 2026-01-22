<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para lista
        </a>
        <div class="mt-2 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
            <a href="{{ route('admin.users.edit', $user) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar
            </a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- User Info -->
        <div class="lg:col-span-1">
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-center">
                    <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full bg-gray-200">
                        <span class="text-xl font-medium text-gray-600">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </span>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-medium text-gray-900">{{ $user->name }}</h2>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Tipo</span>
                        @if ($user->is_admin)
                            <span class="inline-flex rounded-full bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-800">
                                Administrador
                            </span>
                        @else
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800">
                                Usuário
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Status</span>
                        @if ($user->isLocked())
                            <span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800">
                                Bloqueado
                            </span>
                        @elseif ($user->is_active)
                            <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                Ativo
                            </span>
                        @else
                            <span class="inline-flex rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-800">
                                Inativo
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Criado em</span>
                        <span class="text-sm text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Último login</span>
                        <span class="text-sm text-gray-900">{{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Nunca' }}</span>
                    </div>

                    @if ($user->last_login_ip)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">IP do último login</span>
                            <span class="text-sm text-gray-900">{{ $user->last_login_ip }}</span>
                        </div>
                    @endif

                    @if ($user->failed_login_attempts > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Tentativas falhas</span>
                            <span class="text-sm text-red-600">{{ $user->failed_login_attempts }}</span>
                        </div>
                    @endif

                    @if ($user->isLocked())
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Bloqueado até</span>
                            <span class="text-sm text-red-600">{{ $user->locked_until->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>

                @if ($user->id !== auth()->id())
                    <div class="mt-6 flex gap-2">
                        @if ($user->isLocked())
                            <form method="POST" action="{{ route('admin.users.unlock', $user) }}" class="flex-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                                    Desbloquear
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="flex-1">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="w-full rounded-lg {{ $user->is_active ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700' }} px-4 py-2 text-sm font-medium text-white">
                                {{ $user->is_active ? 'Desativar' : 'Ativar' }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <!-- Activity Log -->
        <div class="lg:col-span-2">
            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-medium text-gray-900">Atividade Recente</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse ($activities as $activity)
                        <div class="flex items-start gap-4 px-6 py-4">
                            <div class="flex-shrink-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100">
                                    @if ($activity->action === 'login')
                                        <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                        </svg>
                                    @elseif ($activity->action === 'logout')
                                        <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                    @else
                                        <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-900">
                                    {{ $activity->description ?? ucfirst($activity->action) }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $activity->created_at->format('d/m/Y H:i') }}
                                    @if ($activity->ip_address)
                                        &bull; IP: {{ $activity->ip_address }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500">
                            Nenhuma atividade registrada.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
