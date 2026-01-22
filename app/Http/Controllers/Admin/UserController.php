<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $query = User::query();

        // Search filter
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('is_admin')) {
            $query->where('is_admin', $request->boolean('is_admin'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'is_admin' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_admin'] = $request->boolean('is_admin');
        $validated['is_active'] = $request->boolean('is_active', true);

        $user = User::create($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Usuário {$user->name} criado",
            $user,
            null,
            $user->toArray()
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        $activities = $user->activityLogs()->latest()->take(20)->get();

        return view('admin.users.show', compact('user', 'activities'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'is_admin' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $oldValues = $user->toArray();

        // Only update password if provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $validated['is_admin'] = $request->boolean('is_admin');
        $validated['is_active'] = $request->boolean('is_active');

        $user->update($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Usuário {$user->name} atualizado",
            $user,
            $oldValues,
            $user->fresh()->toArray()
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent self-deletion
        if ($user->id === auth('admin')->id()) {
            return back()->with('error', 'Você não pode excluir sua própria conta.');
        }

        $userName = $user->name;
        $oldValues = $user->toArray();

        $user->delete();

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Usuário {$userName} excluído",
            null,
            $oldValues,
            null
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuário excluído com sucesso!');
    }

    /**
     * Toggle user active status.
     */
    public function toggleActive(User $user): RedirectResponse
    {
        if ($user->id === auth('admin')->id()) {
            return back()->with('error', 'Você não pode desativar sua própria conta.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'ativado' : 'desativado';

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Usuário {$user->name} {$status}",
            $user
        );

        return back()->with('success', "Usuário {$status} com sucesso!");
    }

    /**
     * Unlock a locked user account.
     */
    public function unlock(User $user): RedirectResponse
    {
        $user->update([
            'locked_until' => null,
            'failed_login_attempts' => 0,
        ]);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Conta do usuário {$user->name} desbloqueada",
            $user
        );

        return back()->with('success', 'Conta desbloqueada com sucesso!');
    }
}
