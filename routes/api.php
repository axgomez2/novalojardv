<?php

use App\Http\Controllers\Api\AuthController;
use App\Models\Vinyl;
use App\Support\VinylApiFormatter;
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

// Banners da home (públicos — apenas ativos)
Route::get('/home-banners', function () {
    $banners = \App\Models\HomeBanner::active()->ordered()->get([
        'id', 'title', 'subtitle', 'image_path', 'link_url', 'open_in_new_tab', 'sort_order',
    ]);
    return response()->json(['data' => $banners]);
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
| Rotas Protegidas - DJ Playlist
|--------------------------------------------------------------------------
*/
Route::prefix('dj')->middleware('auth:sanctum')->group(function () {
    Route::get('/my-playlist', [App\Http\Controllers\Api\DjPlaylistController::class, 'myPlaylist']);
    Route::get('/search-tracks', [App\Http\Controllers\Api\DjPlaylistController::class, 'searchTracks']);
    Route::post('/tracks', [App\Http\Controllers\Api\DjPlaylistController::class, 'addTrack']);
    Route::delete('/tracks/{track}', [App\Http\Controllers\Api\DjPlaylistController::class, 'removeTrack']);
    Route::post('/tracks/reorder', [App\Http\Controllers\Api\DjPlaylistController::class, 'reorderTracks']);
});

/*
|--------------------------------------------------------------------------
| Rotas Públicas - Charts
|--------------------------------------------------------------------------
*/
Route::get('/charts', function () {
    $charts = \App\Models\Chart::with(['vinyls.mainArtists'])
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($chart) {
            return [
                'id' => $chart->id,
                'title' => $chart->title,
                'slug' => $chart->slug,
                'period' => $chart->period,
                'cover_image' => $chart->cover_url,
                'vinyls_count' => $chart->vinyls->count(),
            ];
        });

    return response()->json(['data' => $charts]);
});

Route::get('/charts/{slug}', function ($slug) {
    $chart = \App\Models\Chart::with(['vinyls.mainArtists', 'vinyls.recordLabel'])
        ->where('slug', $slug)
        ->where('is_active', true)
        ->firstOrFail();

    return response()->json([
        'data' => [
            'id' => $chart->id,
            'title' => $chart->title,
            'slug' => $chart->slug,
            'period' => $chart->period,
            'description' => $chart->description,
            'cover_image' => $chart->cover_url,
            'vinyls' => $chart->vinyls->map(function ($vinyl) {
                return [
                    'id' => $vinyl->id,
                    'position' => $vinyl->pivot->position,
                    'title' => $vinyl->title,
                    'artist' => $vinyl->artist_names,
                    'cover_image' => $vinyl->cover_url,
                    'slug' => $vinyl->slug,
                    'catalog_number' => $vinyl->catalog_number,
                ];
            }),
        ]
    ]);
});

/*
|--------------------------------------------------------------------------
| Rotas Públicas - DJ Playlists
|--------------------------------------------------------------------------
*/
Route::get('/dj-playlists', function () {
    $playlists = \App\Models\DjPlaylist::with(['tracks'])
        ->where('is_active', true)
        ->orderBy('is_featured', 'desc')
        ->orderBy('sort_order')
        ->get()
        ->map(function ($playlist) {
            return [
                'id' => $playlist->id,
                'title' => $playlist->title,
                'slug' => $playlist->slug,
                'dj_name' => $playlist->dj_name,
                'dj_image' => $playlist->dj_image_url,
                'tracks_count' => $playlist->tracks->count(),
                'is_featured' => $playlist->is_featured,
            ];
        });

    return response()->json(['data' => $playlists]);
});

Route::get('/dj-playlists/{slug}', function ($slug) {
    $playlist = \App\Models\DjPlaylist::with(['tracks.vinylMaster.mainArtists', 'tracks.vinylMaster.recordLabel'])
        ->where('slug', $slug)
        ->where('is_active', true)
        ->firstOrFail();

    return response()->json([
        'data' => [
            'id' => $playlist->id,
            'title' => $playlist->title,
            'slug' => $playlist->slug,
            'dj_name' => $playlist->dj_name,
            'dj_description' => $playlist->dj_description,
            'dj_image' => $playlist->dj_image_url,
            'social_links' => $playlist->getSocialLinks(),
            'tracks' => $playlist->tracks->map(function ($track) {
                return [
                    'id' => $track->id,
                    'position' => $track->pivot->position,
                    'name' => $track->name,
                    'vinyl' => [
                        'id' => $track->vinylMaster?->id,
                        'title' => $track->vinylMaster?->title,
                        'artist' => $track->vinylMaster?->artist_names,
                        'cover_image' => $track->vinylMaster?->cover_url,
                        'slug' => $track->vinylMaster?->slug,
                    ]
                ];
            }),
        ]
    ]);
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
            ->where('visibility', 'public')
            ->where('is_new', true)
            ->where('availability', 'available')
            ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc');
        
        $applySection($query);
        
        $vinyls = $query->take(request('limit', 10))
            ->get()
            ->map(fn($stock) => VinylApiFormatter::format($stock));

        return response()->json(['data' => $vinyls]);
    });

    // Pré-Venda - Discos novos em pré-venda
    Route::get('/preorder', function () use ($applySection) {
        $query = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel', 'vinylMaster.tracks'])
            ->where('visibility', 'public')
            ->where('is_new', true)
            ->where('availability', 'preorder')
            ->orderBy('release_date', 'asc');
        
        $applySection($query);
        
        $vinyls = $query->take(request('limit', 10))
            ->get()
            ->map(fn($stock) => VinylApiFormatter::format($stock));

        return response()->json(['data' => $vinyls]);
    });

    // Ofertas - Discos em promoção, incluindo sem estoque
    Route::get('/deals', function () use ($applySection) {
        $query = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel', 'vinylMaster.tracks'])
            ->where('visibility', 'public')
            ->where('is_promotional', true)
            ->whereIn('availability', ['available', 'featured'])
            ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc');
        
        $applySection($query);
        
        $vinyls = $query->take(request('limit', 10))
            ->get()
            ->map(fn($stock) => VinylApiFormatter::format($stock));

        return response()->json(['data' => $vinyls]);
    });

    // Usados - Discos usados por ordem de entrada, incluindo sem estoque
    Route::get('/used', function () use ($applySection) {
        $query = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel', 'vinylMaster.tracks'])
            ->where('visibility', 'public')
            ->where('is_new', false)
            ->whereIn('availability', ['available', 'featured'])
            ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc');
        
        $applySection($query);
        
        $vinyls = $query->take(request('limit', 10))
            ->get()
            ->map(fn($stock) => VinylApiFormatter::format($stock));

        return response()->json(['data' => $vinyls]);
    });

    // Discos usados para DJs (seção DJ - singles, maxis, promos - apenas usados), incluindo sem estoque
    Route::get('/dj', function () {
        $vinyls = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel', 'vinylMaster.tracks'])
            ->where('visibility', 'public')
            ->where('store_section', 'dj')
            ->where('is_new', false)
            ->whereIn('availability', ['available', 'featured'])
            ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc')
            ->take(request('limit', 10))
            ->get()
            ->map(fn($stock) => VinylApiFormatter::format($stock));

        return response()->json(['data' => $vinyls]);
    });

    // Álbuns / LPs (seção albums), incluindo sem estoque
    Route::get('/albums', function () {
        $vinyls = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel', 'vinylMaster.tracks'])
            ->where('visibility', 'public')
            ->where('store_section', 'albums')
            ->whereIn('availability', ['available', 'featured'])
            ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc')
            ->take(request('limit', 10))
            ->get()
            ->map(fn($stock) => VinylApiFormatter::format($stock));

        return response()->json(['data' => $vinyls]);
    });

    // Destaques, incluindo sem estoque
    Route::get('/featured', function () use ($applySection) {
        $query = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel', 'vinylMaster.tracks'])
            ->where('visibility', 'public')
            ->where('availability', 'featured')
            ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc');
        
        $applySection($query);
        
        $vinyls = $query->take(request('limit', 10))
            ->get()
            ->map(fn($stock) => VinylApiFormatter::format($stock));

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
        ])->where('visibility', 'public')->findOrFail($id);

        return response()->json(['data' => VinylApiFormatter::detailed($stock)]);
    });

    // Discos relacionados (mesma seção e categoria pai)
    Route::get('/{id}/related', function ($id) {
        $stock = \App\Models\VinylStock::with(['categories.parent'])->findOrFail($id);
        
        // Pegar a categoria primária e sua categoria pai
        $primaryCategory = $stock->categories()->wherePivot('is_primary', true)->first();
        $parentCategoryId = $primaryCategory?->parent_id ?? $primaryCategory?->id;
        
        // Buscar discos relacionados
        $query = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel'])
            ->where('visibility', 'public')
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
            ->map(fn($s) => VinylApiFormatter::format($s));

        return response()->json(['data' => $vinyls]);
    });

    // Listagem geral com filtros, incluindo sem estoque
    Route::get('/', function () {
        $query = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel', 'vinylMaster.vinylImages'])
            ->where('visibility', 'public')
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
            ->through(fn($stock) => VinylApiFormatter::format($stock));

        return response()->json($vinyls);
    });
});

/*
|--------------------------------------------------------------------------
| Rotas Públicas - Busca
|--------------------------------------------------------------------------
*/
Route::get('/search', function () {
    $query = request('q', '');
    $section = request('section'); // dj ou albums
    $perPage = request('per_page', 20);
    
    if (strlen($query) < 2) {
        return response()->json([
            'data' => [],
            'meta' => ['total' => 0, 'query' => $query]
        ]);
    }
    
    $searchTerms = '%' . $query . '%';
    
    $results = \App\Models\VinylStock::with(['vinylMaster.mainArtists', 'vinylMaster.recordLabel', 'vinylMaster.tracks', 'vinylMaster.vinylImages'])
        ->where('visibility', 'public')
        ->where('availability', '!=', 'unavailable')
        ->where(function ($q) use ($searchTerms) {
            // Busca no título do disco
            $q->whereHas('vinylMaster', function ($subQ) use ($searchTerms) {
                $subQ->where('title', 'like', $searchTerms)
                     ->orWhere('catalog_number', 'like', $searchTerms);
            })
            // Busca no artista
            ->orWhereHas('vinylMaster.mainArtists', function ($subQ) use ($searchTerms) {
                $subQ->where('name', 'like', $searchTerms);
            })
            // Busca na gravadora
            ->orWhereHas('vinylMaster.recordLabel', function ($subQ) use ($searchTerms) {
                $subQ->where('name', 'like', $searchTerms);
            })
            // Busca nas faixas
            ->orWhereHas('vinylMaster.tracks', function ($subQ) use ($searchTerms) {
                $subQ->where('name', 'like', $searchTerms);
            });
        })
        ->when($section, function ($q) use ($section) {
            $q->where('store_section', $section);
        })
        ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
        ->orderBy('created_at', 'desc')
        ->paginate($perPage)
        ->through(fn($stock) => VinylApiFormatter::format($stock));
    
    return response()->json([
        'data' => $results->items(),
        'meta' => [
            'total' => $results->total(),
            'per_page' => $results->perPage(),
            'current_page' => $results->currentPage(),
            'last_page' => $results->lastPage(),
            'query' => $query,
        ]
    ]);
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

/*
|--------------------------------------------------------------------------
| Rotas Públicas - Product Types (endpoints isolados por tipo)
|--------------------------------------------------------------------------
| Cada tipo de produto tem uma rota dedicada para o frontend Vue consumir.
| Aceitam ?per_page=20&page=1&in_stock=1
*/
Route::get('/product-types', [App\Http\Controllers\Api\ProductApiController::class, 'types']);
Route::get('/product-types/{slug}/items', [App\Http\Controllers\Api\ProductApiController::class, 'itemsByType']);

Route::get('/discos-novos', [App\Http\Controllers\Api\ProductApiController::class, 'discosNovos']);
Route::get('/discos-usados', [App\Http\Controllers\Api\ProductApiController::class, 'discosUsados']);
Route::get('/discos-nacionais', [App\Http\Controllers\Api\ProductApiController::class, 'discosNacionais']);
Route::get('/equipamentos', [App\Http\Controllers\Api\ProductApiController::class, 'equipamentos']);
Route::get('/acessorios', [App\Http\Controllers\Api\ProductApiController::class, 'acessorios']);

/*
|--------------------------------------------------------------------------
| Rotas Protegidas (Cliente Autenticado)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Carrinho persistente
    Route::prefix('cart')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\CartController::class, 'index']);
        Route::post('/items', [App\Http\Controllers\Api\CartController::class, 'storeItem']);
        Route::patch('/items/{vinylStockId}', [App\Http\Controllers\Api\CartController::class, 'updateItem']);
        Route::delete('/items/{vinylStockId}', [App\Http\Controllers\Api\CartController::class, 'destroyItem']);
        Route::post('/sync', [App\Http\Controllers\Api\CartController::class, 'sync']);
        Route::delete('/', [App\Http\Controllers\Api\CartController::class, 'clear']);
    });

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

    // Pré-vendas (cliente)
    Route::prefix('pre-orders')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\PreOrderClientController::class, 'index']);
        Route::get('/{code}', [App\Http\Controllers\Api\PreOrderClientController::class, 'show']);
        Route::post('/{code}/pay-signal', [App\Http\Controllers\Api\PreOrderClientController::class, 'paySignal']);
        Route::post('/{code}/pay-balance', [App\Http\Controllers\Api\PreOrderClientController::class, 'payBalance']);
        Route::post('/{code}/request-manual-pix', [App\Http\Controllers\Api\PreOrderClientController::class, 'requestManualPix']);
    });
});
