<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Verificar se é cliente (guard web) ou admin
        $clientUser = auth('web')->user();
        $adminUser = auth('admin')->user();
        
        $user = null;
        
        if ($clientUser) {
            $user = [
                'id' => $clientUser->id,
                'name' => $clientUser->name,
                'email' => $clientUser->email,
                'phone' => $clientUser->phone ?? null,
                'cpf' => $clientUser->cpf ?? null,
                'is_client' => true,
            ];
        } elseif ($adminUser) {
            $user = [
                'id' => $adminUser->id,
                'name' => $adminUser->name,
                'email' => $adminUser->email,
                'role' => $adminUser->role,
                'is_admin' => $adminUser->isAdmin(),
            ];
        }
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
