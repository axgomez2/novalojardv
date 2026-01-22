<x-admin-layout>
    <div class="mb-8">
        <a href="{{ route('admin.activity-logs.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para logs
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Detalhes do Log</h1>
    </div>

    <div class="mx-auto max-w-3xl">
        <div class="rounded-lg bg-white p-6 shadow">
            <!-- Header Info -->
            <div class="mb-6 flex items-start justify-between">
                <div class="flex items-center">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-gray-200">
                        <span class="text-sm font-medium text-gray-600">
                            {{ $activityLog->user ? strtoupper(substr($activityLog->user->name, 0, 2)) : '?' }}
                        </span>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ $activityLog->user?->name ?? 'Usuário removido' }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            {{ $activityLog->user?->email ?? '-' }}
                        </p>
                    </div>
                </div>
                @php
                    $actionColors = [
                        'login' => 'bg-green-100 text-green-800',
                        'logout' => 'bg-gray-100 text-gray-800',
                        'create' => 'bg-blue-100 text-blue-800',
                        'update' => 'bg-yellow-100 text-yellow-800',
                        'delete' => 'bg-red-100 text-red-800',
                    ];
                    $colorClass = $actionColors[$activityLog->action] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $colorClass }}">
                    {{ ucfirst($activityLog->action) }}
                </span>
            </div>

            <!-- Details -->
            <dl class="divide-y divide-gray-200">
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Descrição</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                        {{ $activityLog->description ?? '-' }}
                    </dd>
                </div>

                @if ($activityLog->model_type)
                    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Modelo</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            {{ class_basename($activityLog->model_type) }} #{{ $activityLog->model_id }}
                        </dd>
                    </div>
                @endif

                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Data/Hora</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                        {{ $activityLog->created_at->format('d/m/Y H:i:s') }}
                    </dd>
                </div>

                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Endereço IP</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                        {{ $activityLog->ip_address ?? '-' }}
                    </dd>
                </div>

                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                        <span class="break-all">{{ $activityLog->user_agent ?? '-' }}</span>
                    </dd>
                </div>

                @if ($activityLog->old_values)
                    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Valores Anteriores</dt>
                        <dd class="mt-1 sm:col-span-2 sm:mt-0">
                            <pre class="overflow-x-auto rounded-lg bg-gray-50 p-4 text-xs text-gray-800">{{ json_encode($activityLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </dd>
                    </div>
                @endif

                @if ($activityLog->new_values)
                    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Novos Valores</dt>
                        <dd class="mt-1 sm:col-span-2 sm:mt-0">
                            <pre class="overflow-x-auto rounded-lg bg-gray-50 p-4 text-xs text-gray-800">{{ json_encode($activityLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>
</x-admin-layout>
