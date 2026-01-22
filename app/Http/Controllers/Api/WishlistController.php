<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientWishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Listar todos os itens da wishlist do usuário autenticado
     */
    public function index(Request $request): JsonResponse
    {
        $wishlists = ClientWishlist::with(['vinylStock.vinylMaster.mainArtists', 'vinylStock.vinylMaster.recordLabel'])
            ->where('client_user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn($item) => $this->formatWishlistItem($item));

        return response()->json([
            'data' => $wishlists,
            'count' => $wishlists->count()
        ]);
    }

    /**
     * Adicionar item à wishlist
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'vinyl_stock_id' => 'required|exists:vinyl_stocks,id',
            'notes' => 'nullable|string|max:500'
        ]);

        $existing = ClientWishlist::where('client_user_id', $request->user()->id)
            ->where('vinyl_stock_id', $request->vinyl_stock_id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Item já está na wishlist',
                'data' => $this->formatWishlistItem($existing->load(['vinylStock.vinylMaster.mainArtists']))
            ], 200);
        }

        $wishlist = ClientWishlist::create([
            'client_user_id' => $request->user()->id,
            'vinyl_stock_id' => $request->vinyl_stock_id,
            'notes' => $request->notes
        ]);

        $wishlist->load(['vinylStock.vinylMaster.mainArtists', 'vinylStock.vinylMaster.recordLabel']);

        return response()->json([
            'message' => 'Item adicionado à wishlist',
            'data' => $this->formatWishlistItem($wishlist)
        ], 201);
    }

    /**
     * Remover item da wishlist
     */
    public function destroy(Request $request, int $vinylStockId): JsonResponse
    {
        $deleted = ClientWishlist::where('client_user_id', $request->user()->id)
            ->where('vinyl_stock_id', $vinylStockId)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'message' => 'Item não encontrado na wishlist'
            ], 404);
        }

        return response()->json([
            'message' => 'Item removido da wishlist'
        ]);
    }

    /**
     * Verificar se item está na wishlist
     */
    public function check(Request $request, int $vinylStockId): JsonResponse
    {
        $exists = ClientWishlist::where('client_user_id', $request->user()->id)
            ->where('vinyl_stock_id', $vinylStockId)
            ->exists();

        return response()->json([
            'in_wishlist' => $exists
        ]);
    }

    /**
     * Toggle item na wishlist (adiciona se não existe, remove se existe)
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'vinyl_stock_id' => 'required|exists:vinyl_stocks,id'
        ]);

        $existing = ClientWishlist::where('client_user_id', $request->user()->id)
            ->where('vinyl_stock_id', $request->vinyl_stock_id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'message' => 'Item removido da wishlist',
                'in_wishlist' => false
            ]);
        }

        $wishlist = ClientWishlist::create([
            'client_user_id' => $request->user()->id,
            'vinyl_stock_id' => $request->vinyl_stock_id
        ]);

        $wishlist->load(['vinylStock.vinylMaster.mainArtists', 'vinylStock.vinylMaster.recordLabel']);

        return response()->json([
            'message' => 'Item adicionado à wishlist',
            'in_wishlist' => true,
            'data' => $this->formatWishlistItem($wishlist)
        ], 201);
    }

    /**
     * Formatar item da wishlist para resposta da API
     */
    private function formatWishlistItem(ClientWishlist $item): array
    {
        $stock = $item->vinylStock;
        $master = $stock?->vinylMaster;

        return [
            'id' => $item->id,
            'vinyl_stock_id' => $item->vinyl_stock_id,
            'notes' => $item->notes,
            'added_at' => $item->created_at->toISOString(),
            'vinyl' => $stock ? [
                'id' => $stock->id,
                'title' => $master?->title ?? 'Sem Título',
                'artist' => $master?->artist_names ?? 'Artista Desconhecido',
                'cover_image' => $master?->cover_url ?? '/images/vinyl-placeholder.jpg',
                'price' => $stock->current_price,
                'formatted_price' => $stock->formatted_current_price,
                'is_new' => $stock->is_new,
                'in_stock' => $stock->stock > 0,
                'availability' => $stock->availability,
                'record_label' => $master?->recordLabel?->name,
                'release_year' => $master?->release_year,
            ] : null
        ];
    }
}
