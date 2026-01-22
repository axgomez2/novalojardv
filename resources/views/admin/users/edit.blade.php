<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para lista
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Editar Usuário</h1>
        <p class="mt-1 text-sm text-gray-600">{{ $user->email }}</p>
    </div>

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="rounded-lg bg-white p-6 shadow">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name', $user->name) }}"
                           required
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email', $user->email) }}"
                           required
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Nova Senha</label>
                    <input type="password"
                           name="password"
                           id="password"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-sm text-gray-500">Deixe em branco para manter a senha atual</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Nova Senha</label>
                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Checkboxes -->
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox"
                               name="is_admin"
                               id="is_admin"
                               value="1"
                               {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                               {{ $user->id === auth()->id() ? 'disabled' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="is_admin" class="ml-2 block text-sm text-gray-700">
                            Administrador
                        </label>
                        @if ($user->id === auth()->id())
                            <span class="ml-2 text-xs text-gray-500">(Você não pode alterar seu próprio status de admin)</span>
                        @endif
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               id="is_active"
                               value="1"
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               {{ $user->id === auth()->id() ? 'disabled' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            Conta ativa
                        </label>
                        @if ($user->id === auth()->id())
                            <span class="ml-2 text-xs text-gray-500">(Você não pode desativar sua própria conta)</span>
                        @endif
                    </div>
                </div>

                <!-- Account Status Info -->
                @if ($user->isLocked())
                    <div class="rounded-lg bg-red-50 p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Conta bloqueada</h3>
                                <p class="mt-1 text-sm text-red-700">
                                    Esta conta está bloqueada até {{ $user->locked_until->format('d/m/Y H:i') }}
                                    ({{ $user->failed_login_attempts }} tentativas falhas)
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}"
                   class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Cancelar
                </a>
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
