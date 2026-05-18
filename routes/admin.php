<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ArtistController;
use App\Http\Controllers\Admin\Auth\AdminAuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\AdminNewPasswordController;
use App\Http\Controllers\Admin\Auth\AdminPasswordResetLinkController;
use App\Http\Controllers\Admin\CoverStatusController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HomeBannerController;
use App\Http\Controllers\Admin\DimensionController;
use App\Http\Controllers\Admin\FinancialDashboardController;
use App\Http\Controllers\Admin\FinancialTransactionController;
use App\Http\Controllers\Admin\IncomeSourceController;
use App\Http\Controllers\Admin\MediaStatusController;
use App\Http\Controllers\Admin\PaymentCategoryController;
use App\Http\Controllers\Admin\RecordLabelController;
use App\Http\Controllers\Admin\RecurringPaymentController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VinylController;
use App\Http\Controllers\Admin\VinylImageController;
use App\Http\Controllers\Admin\VinylStockController;
use App\Http\Controllers\Admin\VinylTrackController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ChartController;
use App\Http\Controllers\Admin\DjPlaylistController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PreOrderController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\WeightController;
use App\Http\Controllers\Admin\ShippingSettingsController;
use App\Http\Controllers\Admin\SiteSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Authentication Routes (Guest)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware('guest:admin')->group(function () {
    Route::get('login', [AdminAuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AdminAuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [AdminPasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [AdminPasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [AdminNewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [AdminNewPasswordController::class, 'store'])->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Admin Routes - Protected by 'admin' middleware
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    // Logout
    Route::post('logout', [AdminAuthenticatedSessionController::class, 'destroy'])->name('logout');

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Users Management
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('users.toggle-active');
        Route::patch('users/{user}/unlock', [UserController::class, 'unlock'])
            ->name('users.unlock');

        // Activity Logs
        Route::get('activity-logs', [ActivityLogController::class, 'index'])
            ->name('activity-logs.index');
        Route::get('activity-logs/{activityLog}', [ActivityLogController::class, 'show'])
            ->name('activity-logs.show');

        // Vinyl Records (Discos)
        Route::prefix('vinyls')->name('vinyls.')->group(function () {
            Route::get('/', [VinylController::class, 'index'])->name('index');
            Route::get('/create', [VinylController::class, 'create'])->name('create');
            Route::get('/create/step2', [VinylController::class, 'createStep2'])->name('create.step2');
            Route::post('/', [VinylController::class, 'store'])->name('store');
            Route::get('/{vinyl}', [VinylController::class, 'show'])->name('show');
            Route::get('/{vinyl}/edit', [VinylController::class, 'edit'])->name('edit');
            Route::put('/{vinyl}', [VinylController::class, 'update'])->name('update');
            Route::delete('/{vinyl}', [VinylController::class, 'destroy'])->name('destroy');

            // Vinyl Images
            Route::get('/{vinyl}/images', [VinylImageController::class, 'index'])->name('images.index');
            Route::post('/{vinyl}/images', [VinylImageController::class, 'store'])->name('images.store');
            Route::post('/{vinyl}/images/import-discogs', [VinylImageController::class, 'importDiscogs'])->name('images.import-discogs');
            Route::post('/{vinyl}/images/{image}/set-primary', [VinylImageController::class, 'setPrimary'])->name('images.set-primary');
            Route::post('/{vinyl}/images/update-order', [VinylImageController::class, 'updateOrder'])->name('images.update-order');
            Route::delete('/{vinyl}/images/{image}', [VinylImageController::class, 'destroy'])->name('images.destroy');

            // Vinyl Tracks (Faixas)
            Route::get('/{vinyl}/tracks', [VinylTrackController::class, 'index'])->name('tracks.index');
            Route::post('/{vinyl}/tracks', [VinylTrackController::class, 'store'])->name('tracks.store');
            Route::put('/{vinyl}/tracks/{track}', [VinylTrackController::class, 'update'])->name('tracks.update');
            Route::delete('/{vinyl}/tracks/{track}', [VinylTrackController::class, 'destroy'])->name('tracks.destroy');
            Route::post('/{vinyl}/tracks/{track}/delete-audio', [VinylTrackController::class, 'deleteAudio'])->name('tracks.delete-audio');
            Route::post('/{vinyl}/tracks/update-order', [VinylTrackController::class, 'updateOrder'])->name('tracks.update-order');

            // Discogs API
            Route::get('/discogs/search', [VinylController::class, 'searchDiscogs'])->name('discogs.search');
            Route::post('/ai/description', [VinylController::class, 'generateDescription'])->name('ai.description');
            Route::get('/discogs/release/{releaseId}', [VinylController::class, 'getDiscogsRelease'])->name('discogs.release');
        });

        // Vinyl Stocks (Estoque de Discos)
        Route::prefix('vinyl-stocks')->name('vinyl-stocks.')->group(function () {
            Route::get('/', [VinylStockController::class, 'index'])->name('index');
            Route::get('/create', [VinylStockController::class, 'create'])->name('create');
            Route::post('/', [VinylStockController::class, 'store'])->name('store');
            Route::get('/report', [VinylStockController::class, 'report'])->name('report');
            Route::get('/{vinylStock}', [VinylStockController::class, 'show'])->name('show');
            Route::get('/{vinylStock}/edit', [VinylStockController::class, 'edit'])->name('edit');
            Route::put('/{vinylStock}', [VinylStockController::class, 'update'])->name('update');
            Route::delete('/{vinylStock}', [VinylStockController::class, 'destroy'])->name('destroy');
            Route::post('/{vinylStock}/add-stock', [VinylStockController::class, 'addStock'])->name('add-stock');
            Route::post('/{vinylStock}/quick-add-stock', [VinylStockController::class, 'quickAddStock'])->name('quick-add-stock');
            Route::post('/{vinylStock}/adjust-stock', [VinylStockController::class, 'adjustStock'])->name('adjust-stock');
        });

        // Categories (Categorias)
        Route::post('categories/ajax', [CategoryController::class, 'storeAjax'])->name('categories.ajax');
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Banners da Home
        Route::prefix('home-banners')->name('home-banners.')->group(function () {
            Route::get('/', [HomeBannerController::class, 'index'])->name('index');
            Route::post('/', [HomeBannerController::class, 'store'])->name('store');
            Route::post('/reorder', [HomeBannerController::class, 'reorder'])->name('reorder');
            Route::put('/{homeBanner}', [HomeBannerController::class, 'update'])->name('update');
            Route::post('/{homeBanner}/toggle', [HomeBannerController::class, 'toggle'])->name('toggle');
            Route::delete('/{homeBanner}', [HomeBannerController::class, 'destroy'])->name('destroy');
        });

        // Pré-vendas / Encomendas
        Route::prefix('pre-orders')->name('pre-orders.')->group(function () {
            Route::get('/', [PreOrderController::class, 'index'])->name('index');
            Route::get('/dashboard', [PreOrderController::class, 'dashboard'])->name('dashboard');
            Route::get('/export', [PreOrderController::class, 'export'])->name('export');
            Route::get('/report', [PreOrderController::class, 'report'])->name('report');
            Route::get('/create', [PreOrderController::class, 'create'])->name('create');
            Route::post('/', [PreOrderController::class, 'store'])->name('store');
            Route::get('/{preOrder}', [PreOrderController::class, 'show'])->name('show');
            Route::put('/{preOrder}', [PreOrderController::class, 'update'])->name('update');
            Route::post('/{preOrder}/status', [PreOrderController::class, 'changeStatus'])->name('status');
            Route::post('/{preOrder}/mark-signal-paid', [PreOrderController::class, 'markSignalPaid'])->name('mark-signal-paid');
            Route::post('/{preOrder}/mark-balance-paid', [PreOrderController::class, 'markBalancePaid'])->name('mark-balance-paid');
            Route::post('/{preOrder}/cancel', [PreOrderController::class, 'cancel'])->name('cancel');
        });

        // Settings Routes
        Route::prefix('settings')->name('settings.')->group(function () {
            // Media Statuses (Estado do Disco)
            Route::resource('media-statuses', MediaStatusController::class)->except(['show']);

            // Cover Statuses (Estado da Capa)
            Route::resource('cover-statuses', CoverStatusController::class)->except(['show']);

            // Suppliers (Fornecedores)
            Route::resource('suppliers', SupplierController::class);

            // Record Labels (Gravadoras)
            Route::resource('record-labels', RecordLabelController::class)->except(['show']);

            // Artists (Artistas)
            Route::resource('artists', ArtistController::class)->except(['show']);

            // Weights (Pesos)
            Route::resource('weights', WeightController::class)->except(['show']);

            // Dimensions (Dimensões)
            Route::resource('dimensions', DimensionController::class)->except(['show']);
        });

        // Financial Module (Módulo Financeiro)
        Route::prefix('financial')->name('financial.')->group(function () {
            // Dashboard
            Route::get('/', [FinancialDashboardController::class, 'index'])->name('dashboard');

            // Transactions (Contas a Pagar e Receber)
            Route::get('/transactions', [FinancialTransactionController::class, 'index'])->name('transactions.index');
            Route::get('/transactions/create', [FinancialTransactionController::class, 'create'])->name('transactions.create');
            Route::post('/transactions', [FinancialTransactionController::class, 'store'])->name('transactions.store');
            Route::get('/transactions/{transaction}', [FinancialTransactionController::class, 'show'])->name('transactions.show');
            Route::get('/transactions/{transaction}/edit', [FinancialTransactionController::class, 'edit'])->name('transactions.edit');
            Route::put('/transactions/{transaction}', [FinancialTransactionController::class, 'update'])->name('transactions.update');
            Route::delete('/transactions/{transaction}', [FinancialTransactionController::class, 'destroy'])->name('transactions.destroy');
            Route::post('/transactions/{transaction}/pay', [FinancialTransactionController::class, 'markAsPaid'])->name('transactions.pay');
            Route::post('/transactions/{transaction}/cancel', [FinancialTransactionController::class, 'cancel'])->name('transactions.cancel');

            // Payment Categories (Categorias de Pagamento)
            Route::resource('categories', PaymentCategoryController::class)->except(['show']);

            // Income Sources (Origens de Receita)
            Route::resource('income-sources', IncomeSourceController::class)->except(['show']);

            // Recurring Payments (Pagamentos Recorrentes)
            Route::get('/recurring', [RecurringPaymentController::class, 'index'])->name('recurring.index');
            Route::get('/recurring/create', [RecurringPaymentController::class, 'create'])->name('recurring.create');
            Route::post('/recurring', [RecurringPaymentController::class, 'store'])->name('recurring.store');
            Route::get('/recurring/{recurring}', [RecurringPaymentController::class, 'show'])->name('recurring.show');
            Route::get('/recurring/{recurring}/edit', [RecurringPaymentController::class, 'edit'])->name('recurring.edit');
            Route::put('/recurring/{recurring}', [RecurringPaymentController::class, 'update'])->name('recurring.update');
            Route::delete('/recurring/{recurring}', [RecurringPaymentController::class, 'destroy'])->name('recurring.destroy');
            Route::post('/recurring/{recurring}/generate', [RecurringPaymentController::class, 'generateTransaction'])->name('recurring.generate');
            Route::post('/recurring/{recurring}/toggle', [RecurringPaymentController::class, 'toggleActive'])->name('recurring.toggle');
        });

        // Playlists & Charts Module
        Route::prefix('music')->name('music.')->group(function () {
            // Charts da Loja (discos)
            Route::resource('charts', ChartController::class);
            Route::get('charts-search-vinyls', [ChartController::class, 'searchVinyls'])->name('charts.search-vinyls');
            Route::post('charts/{chart}/vinyls', [ChartController::class, 'addVinyl'])->name('charts.add-vinyl');
            Route::delete('charts/{chart}/vinyls/{vinyl}', [ChartController::class, 'removeVinyl'])->name('charts.remove-vinyl');
            Route::post('charts/{chart}/vinyls/reorder', [ChartController::class, 'updateVinylOrder'])->name('charts.reorder-vinyls');

            // Playlists de DJs (faixas)
            Route::resource('dj-playlists', DjPlaylistController::class);
            Route::get('dj-playlists-search-tracks', [DjPlaylistController::class, 'searchTracks'])->name('dj-playlists.search-tracks');
            Route::post('dj-playlists/{dj_playlist}/tracks', [DjPlaylistController::class, 'addTrack'])->name('dj-playlists.add-track');
            Route::delete('dj-playlists/{dj_playlist}/tracks/{track}', [DjPlaylistController::class, 'removeTrack'])->name('dj-playlists.remove-track');
            Route::post('dj-playlists/{dj_playlist}/tracks/reorder', [DjPlaylistController::class, 'updateTrackOrder'])->name('dj-playlists.reorder-tracks');
            Route::delete('dj-playlists/{dj_playlist}/image', [DjPlaylistController::class, 'removeImage'])->name('dj-playlists.remove-image');
        });

        // Configurações de Frete (Shipping)
        Route::prefix('settings/shipping')->name('settings.shipping.')->group(function () {
            Route::get('/', [ShippingSettingsController::class, 'index'])->name('index');
            Route::post('/update-settings', [ShippingSettingsController::class, 'updateSettings'])->name('update-settings');
            Route::post('/sync-carriers', [ShippingSettingsController::class, 'syncCarriers'])->name('sync-carriers');
            Route::post('/carriers/{carrier}/toggle', [ShippingSettingsController::class, 'toggleCarrier'])->name('toggle-carrier');
            Route::post('/carriers/{carrier}/update', [ShippingSettingsController::class, 'updateCarrier'])->name('update-carrier');
        });

        // Configurações do Site
        Route::prefix('settings/site')->name('settings.site.')->group(function () {
            Route::get('/', [SiteSettingsController::class, 'index'])->name('index');
            Route::put('/', [SiteSettingsController::class, 'update'])->name('update');
            Route::get('/remove-image/{key}', [SiteSettingsController::class, 'removeImage'])->name('remove-image');
        });

        // Clientes (ClientUsers)
        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/', [ClientController::class, 'index'])->name('index');
            Route::get('/{client}', [ClientController::class, 'show'])->name('show');
            Route::patch('/{client}/toggle-dj', [ClientController::class, 'toggleDj'])->name('toggle-dj');
            Route::patch('/{client}/toggle-active', [ClientController::class, 'toggleActive'])->name('toggle-active');
            Route::get('/ajax/search-djs', [ClientController::class, 'searchDjs'])->name('search-djs');
        });

        // Pedidos (Orders)
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/create', [OrderController::class, 'create'])->name('create');
            Route::post('/', [OrderController::class, 'store'])->name('store');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::post('/{order}/status', [OrderController::class, 'updateStatus'])->name('update-status');
            Route::post('/{order}/invoice', [OrderController::class, 'generateInvoice'])->name('generate-invoice');
            Route::get('/{order}/invoice/download', [OrderController::class, 'downloadInvoice'])->name('download-invoice');
            Route::post('/{order}/note', [OrderController::class, 'addNote'])->name('add-note');
            Route::post('/{order}/resend-notification', [OrderController::class, 'resendNotification'])->name('resend-notification');
            
            // AJAX endpoints
            Route::get('/ajax/search-clients', [OrderController::class, 'searchClients'])->name('search-clients');
            Route::get('/ajax/client/{client}/addresses', [OrderController::class, 'getClientAddresses'])->name('client-addresses');
            Route::get('/ajax/search-products', [OrderController::class, 'searchProducts'])->name('search-products');
        });
});
