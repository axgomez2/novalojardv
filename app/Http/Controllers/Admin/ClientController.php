<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientUser;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = ClientUser::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_dj')) {
            $query->where('is_dj', $request->is_dj === '1');
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.clients.index', compact('clients'));
    }

    public function show(ClientUser $client)
    {
        $client->load(['orders', 'djPlaylist']);
        return view('admin.clients.show', compact('client'));
    }

    public function toggleDj(ClientUser $client)
    {
        $client->update(['is_dj' => !$client->is_dj]);

        $status = $client->is_dj ? 'ativado' : 'desativado';
        return back()->with('success', "Status DJ {$status} para {$client->name}!");
    }

    public function toggleActive(ClientUser $client)
    {
        $client->update(['is_active' => !$client->is_active]);

        $status = $client->is_active ? 'ativado' : 'desativado';
        return back()->with('success', "Cliente {$status} com sucesso!");
    }

    public function searchDjs(Request $request)
    {
        $search = $request->get('q', '');
        
        $clients = ClientUser::where('is_dj', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->doesntHave('djPlaylist')
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($clients);
    }
}
