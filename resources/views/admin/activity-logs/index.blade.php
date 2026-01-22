<x-admin-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Logs de Atividade</h1>
        <p class="mt-1 text-sm text-gray-600">Histórico de ações realizadas no sistema</p>
    </div>

    <!-- Filters -->
    <div class="mb-6 rounded-lg bg-white p-4 shadow">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="flex flex-wrap gap-4">
            <select name="action" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todas as ações</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                        {{ ucfirst($action) }}
                    </option>
                @endforeach
            </select>
            <input type="date"
                   name="from"
                   value="{{ request('from') }}"
                   placeholder="De"
                   class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <input type="date"
                   name="to"
                   value="{{ request('to') }}"
                   placeholder="Até"
                   class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <button type="submit"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                Filtrar
            </button>
            @if(request()->hasAny(['action', 'from', 'to']))
                <a href="{{ route('admin.activity-logs.index') }}"
                   class="rounded-lg px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">
                    Limpar
                </a>
            @endif
        </form>
    </div>

    <!-- Logs Table -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Usuário
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Ação
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Descrição
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        IP
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        Data
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gray-200">
                                    <span class="text-xs font-medium text-gray-600">
                                        {{ $log->user ? strtoupper(substr($log->user->name, 0, 2)) : '?' }}
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $log->user?->name ?? 'Usuário removido' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            @php
                                $actionColors = [
                                    'login' => 'bg-green-100 text-green-800',
                                    'logout' => 'bg-gray-100 text-gray-800',
                                    'create' => 'bg-blue-100 text-blue-800',
                                    'update' => 'bg-yellow-100 text-yellow-800',
                                    'delete' => 'bg-red-100 text-red-800',
                                ];
                                $colorClass = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $colorClass }}">
                                {{ ucfirst($log->action) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="max-w-xs truncate text-sm text-gray-900">
                                {{ $log->description ?? '-' }}
                            </div>
                            @if ($log->model_type)
                                <div class="text-xs text-gray-500">
                                    {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                                </div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ $log->ip_address ?? '-' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ $log->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <a href="{{ route('admin.activity-logs.show', $log) }}"
                               class="text-indigo-600 hover:text-indigo-900">
                                Ver detalhes
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            Nenhum log encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($logs->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
