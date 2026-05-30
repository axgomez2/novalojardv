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
        $client->load([
            'orders' => fn ($q) => $q->orderByDesc('created_at'),
            'djPlaylist',
            'addresses',
            'cart.items.vinylStock.vinylMaster.mainArtists',
            'cart.items.vinylStock.vinylMaster.vinylImages',
            'wishlists.vinylStock.vinylMaster.mainArtists',
            'wishlists.vinylStock.vinylMaster.vinylImages',
            'wantlists.vinylMaster.mainArtists',
        ]);

        return view('admin.clients.show', compact('client'));
    }

    public function destroy(ClientUser $client)
    {
        // Bloquear exclusão se houver pedidos vinculados (preserva histórico)
        if ($client->orders()->exists()) {
            return back()->with('error', 'Não é possível excluir: cliente possui pedidos. Desative-o em vez disso.');
        }

        $name = $client->name;
        $client->delete();

        return redirect()->route('admin.clients.index')
            ->with('success', "Cliente {$name} excluído com sucesso.");
    }

    /**
     * Exportar carrinho do cliente para o PDV (pré-preenche o formulário de novo pedido)
     */
    public function exportCartToPdv(ClientUser $client)
    {
        $client->load('cart.items.vinylStock.vinylMaster.mainArtists', 'cart.items.vinylStock.vinylMaster.vinylImages');

        if (!$client->cart || $client->cart->items->isEmpty()) {
            return back()->with('error', 'O carrinho deste cliente está vazio.');
        }

        $items = $client->cart->items->map(function ($item) {
            $stock = $item->vinylStock;
            $master = $stock?->vinylMaster;
            return [
                'id' => $stock->id,
                'title' => $master?->title ?? 'Sem título',
                'artist' => $master?->mainArtists?->pluck('name')->join(', ') ?? '',
                'price' => (float) $stock->current_price,
                'stock' => (int) $stock->stock,
                'image' => $master?->vinylImages?->first()?->url ?? null,
                'quantity' => max(1, min((int) $item->quantity, (int) $stock->stock ?: 1)),
            ];
        })->values()->toArray();

        session()->flash('pdv_prefill', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->formatted_phone ?? $client->phone,
            ],
            'items' => $items,
        ]);

        return redirect()->route('admin.orders.create');
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
