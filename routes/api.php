<?php

use App\Http\Controllers\Api\AuthController;
use App\Models\Vinyl;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Rotas para o frontend Vue SPA (rdv2026)
| Autenticação via Laravel Sanctum
*/

// Rota de teste de conexão
Route::get('/test', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API conectada com sucesso!',
        'timestamp' => now()->toISOString(),
        'app' => config('app.name'),
    ]);
});

// Configurações do site (públicas)
Route::get('/site-settings', function () {
    return response()->json([
        'data' => \App\Models\SiteSetting::getAllForFrontend(),
    ]);
});

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    // Rotas públicas
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    // Google OAuth
    Route::get('/google', [AuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
    Route::post('/google/callback', [AuthController::class, 'handleGoogleCallback']);
    
    // Rotas protegidas
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'changePassword']);
        
        // Verificação de email
        Route::post('/email/send-verification', [AuthController::class, 'sendVerificationEmail']);
        Route::post('/email/verify', [AuthController::class, 'verifyEmail']);
    });
});

/*
|--------------------------------------------------------------------------
| Rotas Públicas - Vinis
|--------------------------------------------------------------------------
*/
Route::prefix('vinyls')->group(function () {
    // Helper para aplicar filtro de seção
    $applySection = function ($query) {
        $section = request('section');
        if ($section && in_array($section, ['dj', 'albums'])) {
            $query->where('store_section', $section);
        }
        return $query;
    };

    // Novidades - Discos novos (excluindo pré-venda), incluindo sem estoque
    Route::get('/new', function () use ($applySection) {
        $query = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel', 'vinylMaster.tracks'])
            ->where('is_new', true)
            ->where('availability', 'available')
            ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc');
        
        $applySection($query);
        
        $vinyls = $query->take(request('limit', 10))
            ->get()
            ->map(fn($stock) => formatVinylForApi($stock));

        return response()->json(['data' => $vinyls]);
    });

    // Pré-Venda - Discos novos em pré-venda
    Route::get('/preorder', function () use ($applySection) {
        $query = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel', 'vinylMaster.tracks'])
            ->where('is_new', true)
            ->where('availability', 'preorder')
            ->orderBy('release_date', 'asc');
        
        $applySection($query);
        
        $vinyls = $query->take(request('limit', 10))
            ->get()
            ->map(fn($stock) => formatVinylForApi($stock));

        return response()->json(['data' => $vinyls]);
    });

    // Ofertas - Discos em promoção, incluindo sem estoque
    Route::get('/deals', function () use ($applySection) {
        $query = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel', 'vinylMaster.tracks'])
            ->where('is_promotional', true)
            ->whereIn('availability', ['available', 'featured'])
            ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc');
        
        $applySection($query);
        
        $vinyls = $query->take(request('limit', 10))
            ->get()
            ->map(fn($stock) => formatVinylForApi($stock));

        return response()->json(['data' => $vinyls]);
    });

    // Usados - Discos usados por ordem de entrada, incluindo sem estoque
    Route::get('/used', function () use ($applySection) {
        $query = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel', 'vinylMaster.tracks'])
            ->where('is_new', false)
            ->whereIn('availability', ['available', 'featured'])
            ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc');
        
        $applySection($query);
        
        $vinyls = $query->take(request('limit', 10))
            ->get()
            ->map(fn($stock) => formatVinylForApi($stock));

        return response()->json(['data' => $vinyls]);
    });

    // Discos usados para DJs (seção DJ - singles, maxis, promos - apenas usados), incluindo sem estoque
    Route::get('/dj', function () {
        $vinyls = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel', 'vinylMaster.tracks'])
            ->where('store_section', 'dj')
            ->where('is_new', false)
            ->whereIn('availability', ['available', 'featured'])
            ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc')
            ->take(request('limit', 10))
            ->get()
            ->map(fn($stock) => formatVinylForApi($stock));

        return response()->json(['data' => $vinyls]);
    });

    // Álbuns / LPs (seção albums), incluindo sem estoque
    Route::get('/albums', function () {
        $vinyls = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel', 'vinylMaster.tracks'])
            ->where('store_section', 'albums')
            ->whereIn('availability', ['available', 'featured'])
            ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc')
            ->take(request('limit', 10))
            ->get()
            ->map(fn($stock) => formatVinylForApi($stock));

        return response()->json(['data' => $vinyls]);
    });

    // Destaques, incluindo sem estoque
    Route::get('/featured', function () use ($applySection) {
        $query = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel', 'vinylMaster.tracks'])
            ->where('availability', 'featured')
            ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc');
        
        $applySection($query);
        
        $vinyls = $query->take(request('limit', 10))
            ->get()
            ->map(fn($stock) => formatVinylForApi($stock));

        return response()->json(['data' => $vinyls]);
    });

    // Detalhes de um vinil
    Route::get('/{id}', function ($id) {
        $stock = \App\Models\VinylStock::with([
            'vinylMaster.mainArtists', 
            'vinylMaster.recordLabel', 
            'vinylMaster.tracks',
            'vinylMaster.vinylImages',
            'mediaStatus',
            'coverStatus',
            'categories.parent'
        ])->findOrFail($id);

        return response()->json(['data' => formatVinylForApi($stock, true)]);
    });

    // Discos relacionados (mesma seção e categoria pai)
    Route::get('/{id}/related', function ($id) {
        $stock = \App\Models\VinylStock::with(['categories.parent'])->findOrFail($id);
        
        // Pegar a categoria primária e sua categoria pai
        $primaryCategory = $stock->categories()->wherePivot('is_primary', true)->first();
        $parentCategoryId = $primaryCategory?->parent_id ?? $primaryCategory?->id;
        
        // Buscar discos relacionados
        $query = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel'])
            ->where('id', '!=', $id)
            ->where('stock', '>', 0)
            ->whereIn('availability', ['available', 'featured']);
        
        // Filtrar por store_section (dj ou albums)
        if ($stock->store_section) {
            $query->where('store_section', $stock->store_section);
        }
        
        // Filtrar por categoria pai
        if ($parentCategoryId) {
            $query->whereHas('categories', function($q) use ($parentCategoryId) {
                $q->where(function($subQ) use ($parentCategoryId) {
                    // Categoria é a pai ou tem a pai como parent
                    $subQ->where('categories.id', $parentCategoryId)
                         ->orWhere('categories.parent_id', $parentCategoryId);
                });
            });
        }
        
        $vinyls = $query->orderBy('created_at', 'desc')
            ->take(request('limit', 8))
            ->get()
            ->map(fn($s) => formatVinylForApi($s));

        return response()->json(['data' => $vinyls]);
    });

    // Listagem geral com filtros, incluindo sem estoque
    Route::get('/', function () {
        $query = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel'])
            ->where('availability', '!=', 'unavailable');

        // Filtros
        if (request('is_new') !== null) {
            $query->where('is_new', request('is_new') === 'true');
        }
        if (request('category')) {
            $query->whereHas('categories', fn($q) => $q->where('slug', request('category')));
        }
        if (request('artist')) {
            $query->whereHas('vinylMaster.mainArtists', fn($q) => $q->where('slug', request('artist')));
        }

        $vinyls = $query->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
            ->latest()
            ->paginate(request('per_page', 20))
            ->through(fn($stock) => formatVinylForApi($stock));

        return response()->json($vinyls);
    });
});

/*
|--------------------------------------------------------------------------
| Rotas Públicas - Utilitários
|--------------------------------------------------------------------------
*/
// Busca de CEP (rota pública - não requer autenticação)
Route::post('/cep/search', [App\Http\Controllers\Api\ClientAddressController::class, 'searchByCep']);

// Cálculo de frete (rota pública - pode ser usada sem login)
Route::post('/shipping/calculate', [App\Http\Controllers\Api\ShippingController::class, 'calculate']);

// Webhook do Mercado Pago (rota pública)
Route::post('/webhooks/mercadopago', [App\Http\Controllers\Api\WebhookController::class, 'mercadoPago']);

// Helper function para formatar vinil
function formatVinylForApi(\App\Models\VinylStock $stock, bool $detailed = false): array
{
    $master = $stock->vinylMaster;
    $isPreorder = $stock->availability === 'preorder';
    $inStock = $stock->stock > 0;
    
    // Formatar tracks para o player híbrido
    $tracks = $master?->tracks?->map(function($track) {
        $audioUrl = null;
        $audioSource = null;
        
        // Prioridade: audio local > youtube
        if ($track->audio_path) {
            $audioUrl = asset('storage/' . $track->audio_path);
            $audioSource = 'local';
        } elseif ($track->youtube_url) {
            $audioUrl = $track->youtube_url;
            $audioSource = 'youtube';
        }
        
        return [
            'id' => $track->id,
            'position' => $track->position,
            'name' => $track->name,
            'duration' => $track->duration,
            'duration_seconds' => $track->duration_seconds,
            'audio_url' => $audioUrl,
            'audio_source' => $audioSource,
            'has_audio' => $audioUrl !== null,
        ];
    }) ?? collect();
    
    $data = [
        'id' => $stock->id,
        'title' => $master?->title ?? 'Sem Título',
        'slug' => $master?->slug,
        'artist' => $master?->artist_names ?? 'Artista Desconhecido',
        'record_label' => $master?->recordLabel?->name,
        'release_year' => $master?->release_year,
        'cover_image' => $master?->cover_url ?? '/images/vinyl-placeholder.jpg',
        'price' => $stock->current_price,
        'formatted_price' => $stock->formatted_current_price,
        'original_price' => $stock->is_promotional ? $stock->sell_price : null,
        'formatted_original_price' => $stock->is_promotional ? $stock->formatted_sell_price : null,
        'is_promotional' => $stock->isOnPromotion(),
        'is_new' => $stock->is_new,
        'is_preorder' => $isPreorder,
        'release_date' => $stock->release_date?->format('Y-m-d'),
        'formatted_release_date' => $stock->release_date?->format('d/m/Y'),
        'condition' => $stock->condition_label,
        'format' => $stock->format,
        'stock' => $stock->stock,
        'in_stock' => $inStock,
        'availability' => $stock->availability,
        'store_section' => $stock->store_section,
        'can_buy' => $inStock || $isPreorder,
        'show_wishlist' => $inStock && !$isPreorder,
        'show_wantlist' => !$inStock || $isPreorder,
        'tracks' => $tracks->values()->toArray(),
        'tracks_count' => $tracks->count(),
        'has_playable_tracks' => $tracks->where('has_audio', true)->count() > 0,
    ];

    if ($detailed) {
        $data['description'] = $master?->description;
        $data['genres'] = $master?->genres ?? [];
        $data['styles'] = $master?->styles ?? [];
        $data['country'] = $master?->country;
        $data['catalog_number'] = $stock->catalog_number;
        $data['barcode'] = $stock->barcode;
        $data['color'] = $stock->color;
        $data['edition'] = $stock->edition;
        $data['num_discs'] = $stock->num_discs;
        $data['speed'] = $stock->speed;
        $data['media_status'] = $stock->mediaStatus?->name;
        $data['cover_status'] = $stock->coverStatus?->name;
        $data['notes'] = $stock->notes;
        $data['vinyl_master_id'] = $master?->id;
        $data['images'] = $master?->vinylImages?->map(fn($img) => [
            'url' => $img->url,
            'is_primary' => $img->is_primary,
        ]) ?? [];
        
        // Buscar categorias do disco
        $primaryCategory = $stock->categories()->wherePivot('is_primary', true)->first();
        $data['category'] = $primaryCategory ? [
            'id' => $primaryCategory->id,
            'name' => $primaryCategory->name,
            'slug' => $primaryCategory->slug,
            'parent_id' => $primaryCategory->parent_id,
            'parent' => $primaryCategory->parent ? [
                'id' => $primaryCategory->parent->id,
                'name' => $primaryCategory->parent->name,
                'slug' => $primaryCategory->parent->slug,
            ] : null,
        ] : null;
    }

    return $data;
}

/*
|--------------------------------------------------------------------------
| Rotas Protegidas (Cliente Autenticado)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Wishlist
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\WishlistController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\WishlistController::class, 'store']);
        Route::post('/toggle', [App\Http\Controllers\Api\WishlistController::class, 'toggle']);
        Route::get('/check/{vinylStockId}', [App\Http\Controllers\Api\WishlistController::class, 'check']);
        Route::delete('/{vinylStockId}', [App\Http\Controllers\Api\WishlistController::class, 'destroy']);
    });

    // Wantlist
    Route::prefix('wantlist')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\WantlistController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\WantlistController::class, 'store']);
        Route::post('/toggle', [App\Http\Controllers\Api\WantlistController::class, 'toggle']);
        Route::get('/check/{vinylStockId}', [App\Http\Controllers\Api\WantlistController::class, 'check']);
        Route::put('/{id}', [App\Http\Controllers\Api\WantlistController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\WantlistController::class, 'destroy']);
    });

    // Endereços
    Route::prefix('addresses')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\ClientAddressController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\ClientAddressController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\ClientAddressController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\ClientAddressController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\ClientAddressController::class, 'destroy']);
        Route::post('/{id}/set-default', [App\Http\Controllers\Api\ClientAddressController::class, 'setDefault']);
    });

    // Checkout e Pedidos
    Route::prefix('checkout')->group(function () {
        Route::get('/requirements', [App\Http\Controllers\Api\CheckoutController::class, 'checkRequirements']);
        Route::post('/shipping', [App\Http\Controllers\Api\CheckoutController::class, 'calculateShipping']);
        Route::post('/order', [App\Http\Controllers\Api\CheckoutController::class, 'createOrder']);
        Route::get('/mercadopago-key', [App\Http\Controllers\Api\CheckoutController::class, 'getMercadoPagoPublicKey']);
    });

    // Pedidos
    Route::prefix('orders')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\CheckoutController::class, 'listOrders']);
        Route::get('/{orderNumber}', [App\Http\Controllers\Api\CheckoutController::class, 'getOrder']);
        Route::get('/{orderNumber}/payment-status', [App\Http\Controllers\Api\CheckoutController::class, 'checkPaymentStatus']);
        Route::post('/{orderNumber}/simulate-payment', [App\Http\Controllers\Api\CheckoutController::class, 'simulatePaymentApproval']);
    });
});
