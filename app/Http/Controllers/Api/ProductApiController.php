<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductType;
use App\Models\VinylStock;
use App\Support\VinylApiFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints públicos por ProductType.
 *
 * Cada tipo tem uma rota dedicada na API (mais explícito e fácil de cachear/evoluir):
 *   GET /api/discos-novos
 *   GET /api/discos-usados
 *   GET /api/discos-nacionais
 *   GET /api/equipamentos     (placeholder — ainda sem produtos cadastrados)
 *   GET /api/acessorios       (placeholder — ainda sem produtos cadastrados)
 *   GET /api/product-types    (lista todos os tipos ativos)
 *   GET /api/product-types/{slug}/items   (genérico — usado internamente pelos métodos acima)
 */
class ProductApiController extends Controller
{
    /**
     * Lista todos os tipos de produto ativos.
     */
    public function types(): JsonResponse
    {
        $types = ProductType::active()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'icon']);

        return response()->json(['data' => $types]);
    }

    public function discosNovos(Request $request): JsonResponse
    {
        return $this->itemsByType('discos-novos', $request);
    }

    public function discosUsados(Request $request): JsonResponse
    {
        return $this->itemsByType('discos-usados', $request);
    }

    public function discosNacionais(Request $request): JsonResponse
    {
        return $this->itemsByType('discos-nacionais', $request);
    }

    public function equipamentos(Request $request): JsonResponse
    {
        // Equipamentos ainda não usam vinyl_stocks. Retorna estrutura vazia
        // pronta para evoluir quando o modelo Product (polimórfico) for ativado.
        return response()->json([
            'data' => [],
            'meta' => ['total' => 0, 'product_type' => 'equipamentos'],
        ]);
    }

    public function acessorios(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [],
            'meta' => ['total' => 0, 'product_type' => 'acessorios'],
        ]);
    }

    /**
     * Endpoint genérico — reaproveitado pelos métodos específicos por tipo.
     *
     * Query params:
     *  - availability: available | preorder | featured (ou "all" para todos)
     *  - in_stock: 1 para filtrar apenas com stock > 0
     *  - limit: atalho para trazer os N últimos SEM paginar (retorna só `data`)
     *  - per_page: tamanho da página (default 20, max 100) — ignorado se `limit` for passado
     *  - page: página (default 1)
     */
    public function itemsByType(string $slug, Request $request): JsonResponse
    {
        $type = ProductType::where('slug', $slug)->where('is_active', true)->first();

        if (!$type) {
            return response()->json([
                'message' => "Tipo de produto '{$slug}' não encontrado ou inativo.",
            ], 404);
        }

        $query = VinylStock::with([
                'vinylMaster.mainArtists',
                'vinylMaster.recordLabel',
                'vinylMaster.tracks',
                'productType',
            ])
            ->where('product_type_id', $type->id);

        // Filtro de availability (default: available)
        $availability = $request->get('availability', 'available');
        $allowedAvailability = ['available', 'preorder', 'featured'];

        if ($availability === 'all') {
            $query->whereIn('availability', $allowedAvailability);
        } elseif (in_array($availability, $allowedAvailability, true)) {
            $query->where('availability', $availability);
        } else {
            return response()->json([
                'message' => "availability inválido. Use: available, preorder, featured ou all.",
            ], 422);
        }

        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }

        $query->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc');

        // Modo "limit" (para vitrine/home) — traz N últimos sem paginar
        if ($request->filled('limit')) {
            $limit = max(1, min((int) $request->get('limit'), 50));
            $items = $query->take($limit)->get()
                ->map(fn ($stock) => VinylApiFormatter::format($stock));

            return response()->json([
                'data' => $items,
                'meta' => [
                    'total' => $items->count(),
                    'limit' => $limit,
                    'availability' => $availability,
                    'product_type' => [
                        'id' => $type->id,
                        'name' => $type->name,
                        'slug' => $type->slug,
                    ],
                ],
            ]);
        }

        // Modo paginado (páginas de listagem)
        $perPage = max(1, min((int) $request->get('per_page', 20), 100));
        $results = $query
            ->paginate($perPage)
            ->through(fn ($stock) => VinylApiFormatter::format($stock));

        return response()->json([
            'data' => $results->items(),
            'meta' => [
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'availability' => $availability,
                'product_type' => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                ],
            ],
        ]);
    }
}
