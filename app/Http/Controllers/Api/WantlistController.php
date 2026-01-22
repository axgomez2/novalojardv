<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientWantlist;
use App\Models\VinylStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WantlistController extends Controller
{
    /**
     * Listar todos os itens da wantlist do usuário autenticado
     */
    public function index(Request $request): JsonResponse
    {
        $wantlists = ClientWantlist::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel'])
            ->where('client_user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn($item) => $this->formatWantlistItem($item));

        return response()->json([
            'data' => $wantlists,
            'count' => $wantlists->count()
        ]);
    }

    /**
     * Adicionar item à wantlist (por vinyl_stock_id - pega o vinyl_master_id)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'vinyl_stock_id' => 'nullable|exists:vinyl_stocks,id',
            'vinyl_master_id' => 'nullable|exists:vinyl_masters,id',
            'artist_name' => 'nullable|string|max:255',
            'album_name' => 'nullable|string|max:255',
            'release_year' => 'nullable|string|max:4',
            'description' => 'nullable|string|max:1000',
            'priority' => 'nullable|in:low,medium,high',
            'max_price' => 'nullable|numeric|min:0',
            'notify_when_available' => 'nullable|boolean'
        ]);

        // Se veio vinyl_stock_id, pega o vinyl_master_id
        $vinylMasterId = $request->vinyl_master_id;
        if ($request->vinyl_stock_id && !$vinylMasterId) {
            $stock = VinylStock::find($request->vinyl_stock_id);
            $vinylMasterId = $stock?->vinyl_master_id;
        }

        // Verifica se já existe na wantlist
        if ($vinylMasterId) {
            $existing = ClientWantlist::where('client_user_id', $request->user()->id)
                ->where('vinyl_master_id', $vinylMasterId)
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'Item já está na wantlist',
                    'data' => $this->formatWantlistItem($existing->load(['vinylMaster.mainArtists']))
                ], 200);
            }
        }

        $wantlist = ClientWantlist::create([
            'client_user_id' => $request->user()->id,
            'vinyl_master_id' => $vinylMasterId,
            'artist_name' => $request->artist_name,
            'album_name' => $request->album_name,
            'release_year' => $request->release_year,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'max_price' => $request->max_price,
            'notify_when_available' => $request->notify_when_available ?? true
        ]);

        $wantlist->load(['vinylMaster.mainArtists', 'vinylMaster.recordLabel']);

        return response()->json([
            'message' => 'Item adicionado à wantlist',
            'data' => $this->formatWantlistItem($wantlist)
        ], 201);
    }

    /**
     * Remover item da wantlist
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        // Tenta encontrar por ID da wantlist ou por vinyl_stock_id
        $wantlist = ClientWantlist::where('client_user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        // Se não encontrou por ID, tenta por vinyl_master_id (via vinyl_stock_id)
        if (!$wantlist) {
            $stock = VinylStock::find($id);
            if ($stock) {
                $wantlist = ClientWantlist::where('client_user_id', $request->user()->id)
                    ->where('vinyl_master_id', $stock->vinyl_master_id)
                    ->first();
            }
        }

        if (!$wantlist) {
            return response()->json([
                'message' => 'Item não encontrado na wantlist'
            ], 404);
        }

        $wantlist->delete();

        return response()->json([
            'message' => 'Item removido da wantlist'
        ]);
    }

    /**
     * Verificar se item está na wantlist (por vinyl_stock_id)
     */
    public function check(Request $request, int $vinylStockId): JsonResponse
    {
        $stock = VinylStock::find($vinylStockId);
        
        if (!$stock) {
            return response()->json(['in_wantlist' => false]);
        }

        $exists = ClientWantlist::where('client_user_id', $request->user()->id)
            ->where('vinyl_master_id', $stock->vinyl_master_id)
            ->exists();

        return response()->json([
            'in_wantlist' => $exists
        ]);
    }

    /**
     * Toggle item na wantlist
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'vinyl_stock_id' => 'required|exists:vinyl_stocks,id'
        ]);

        $stock = VinylStock::with('vinylMaster')->find($request->vinyl_stock_id);
        
        if (!$stock || !$stock->vinyl_master_id) {
            return response()->json([
                'message' => 'Disco não encontrado'
            ], 404);
        }

        $existing = ClientWantlist::where('client_user_id', $request->user()->id)
            ->where('vinyl_master_id', $stock->vinyl_master_id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'message' => 'Item removido da wantlist',
                'in_wantlist' => false
            ]);
        }

        $wantlist = ClientWantlist::create([
            'client_user_id' => $request->user()->id,
            'vinyl_master_id' => $stock->vinyl_master_id,
            'notify_when_available' => true
        ]);

        $wantlist->load(['vinylMaster.mainArtists', 'vinylMaster.recordLabel']);

        return response()->json([
            'message' => 'Item adicionado à wantlist',
            'in_wantlist' => true,
            'data' => $this->formatWantlistItem($wantlist)
        ], 201);
    }

    /**
     * Atualizar item da wantlist
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'priority' => 'nullable|in:low,medium,high',
            'max_price' => 'nullable|numeric|min:0',
            'notify_when_available' => 'nullable|boolean',
            'description' => 'nullable|string|max:1000'
        ]);

        $wantlist = ClientWantlist::where('client_user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$wantlist) {
            return response()->json([
                'message' => 'Item não encontrado na wantlist'
            ], 404);
        }

        $wantlist->update($request->only([
            'priority',
            'max_price',
            'notify_when_available',
            'description'
        ]));

        $wantlist->load(['vinylMaster.mainArtists', 'vinylMaster.recordLabel']);

        return response()->json([
            'message' => 'Item atualizado',
            'data' => $this->formatWantlistItem($wantlist)
        ]);
    }

    /**
     * Formatar item da wantlist para resposta da API
     */
    private function formatWantlistItem(ClientWantlist $item): array
    {
        $master = $item->vinylMaster;
        
        // Verifica disponibilidade
        $availableStock = null;
        if ($master) {
            $availableStock = VinylStock::where('vinyl_master_id', $master->id)
                ->where('stock', '>', 0)
                ->where('availability', '!=', 'unavailable')
                ->when($item->max_price, fn($q) => $q->where('sell_price', '<=', $item->max_price))
                ->first();
        }

        return [
            'id' => $item->id,
            'vinyl_master_id' => $item->vinyl_master_id,
            'display_name' => $item->display_name,
            'artist_name' => $item->artist_name ?? $master?->artist_names,
            'album_name' => $item->album_name ?? $master?->title,
            'release_year' => $item->release_year ?? $master?->release_year,
            'description' => $item->description,
            'priority' => $item->priority,
            'priority_label' => $item->priority_label,
            'max_price' => $item->max_price,
            'notify_when_available' => $item->notify_when_available,
            'notified_at' => $item->notified_at?->toISOString(),
            'added_at' => $item->created_at->toISOString(),
            'is_available' => $availableStock !== null,
            'available_stock' => $availableStock ? [
                'id' => $availableStock->id,
                'price' => $availableStock->current_price,
                'formatted_price' => $availableStock->formatted_current_price,
                'stock' => $availableStock->stock
            ] : null,
            'vinyl' => $master ? [
                'id' => $master->id,
                'title' => $master->title,
                'artist' => $master->artist_names,
                'cover_image' => $master->cover_url ?? '/images/vinyl-placeholder.jpg',
                'record_label' => $master->recordLabel?->name,
                'release_year' => $master->release_year,
            ] : null
        ];
    }
}
