<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Category;
use App\Models\CoverStatus;
use App\Models\Dimension;
use App\Models\MediaStatus;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\VinylMaster;
use App\Models\VinylStock;
use App\Models\Weight;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VinylStockController extends Controller
{
    /**
     * Display a listing of vinyl stocks with inventory management
     */
    public function index(Request $request): View
    {
        $query = VinylStock::with(['vinylMaster.mainArtists', 'mediaStatus', 'coverStatus', 'supplier', 'categories']);

        // Filters
        if ($search = $request->get('search')) {
            $query->whereHas('vinylMaster', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('mainArtists', fn($q) => $q->where('name', 'like', "%{$search}%"));
            })->orWhere('internal_code', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%");
        }

        if ($availability = $request->get('availability')) {
            $query->where('availability', $availability);
        }

        if ($request->get('is_new') !== null) {
            $query->where('is_new', $request->boolean('is_new'));
        }

        if ($storeSection = $request->get('store_section')) {
            $query->where('store_section', $storeSection);
        }

        if ($request->get('low_stock')) {
            $query->lowStock();
        }

        if ($supplierId = $request->get('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        $stocks = $query->latest()->paginate(20)->withQueryString();

        // Statistics
        $stats = [
            'total_items' => VinylStock::count(),
            'total_stock' => VinylStock::sum('stock'),
            'total_value' => VinylStock::selectRaw('SUM(stock * COALESCE(cost_price, sell_price)) as total')->value('total') ?? 0,
            'low_stock_count' => VinylStock::whereColumn('stock', '<=', 'stock_min')->count(),
            'out_of_stock' => VinylStock::where('stock', '<=', 0)->count(),
        ];

        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('admin.vinyl-stocks.index', compact('stocks', 'stats', 'suppliers'));
    }

    /**
     * Show the form for creating a new vinyl stock
     */
    public function create(Request $request): View
    {
        $vinylMasterId = $request->get('vinyl_master_id');
        $vinylMaster = $vinylMasterId ? VinylMaster::with(['mainArtists', 'recordLabel', 'tracks'])->findOrFail($vinylMasterId) : null;

        $vinylMasters = VinylMaster::with('mainArtists')->orderBy('title')->get();
        $mediaStatuses = MediaStatus::active()->orderBy('sort_order')->get();
        $coverStatuses = CoverStatus::active()->orderBy('sort_order')->get();
        $weights = Weight::active()->orderBy('value')->get();
        $dimensions = Dimension::active()->orderBy('name')->get();
        $suppliers = Supplier::active()->orderBy('name')->get();
        $categories = Category::with('children')->parents()->active()->orderBy('sort_order')->get();

        // Generate next internal code
        $lastCode = VinylStock::withTrashed()
            ->where('internal_code', 'like', 'RDV-%')
            ->orderByRaw("CAST(REPLACE(internal_code, 'RDV-', '') AS UNSIGNED) DESC")
            ->value('internal_code');
        
        $nextNumber = 1;
        if ($lastCode) {
            $lastNumber = (int) str_replace('RDV-', '', $lastCode);
            $nextNumber = $lastNumber + 1;
        }
        $nextInternalCode = 'RDV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        return view('admin.vinyl-stocks.create', compact(
            'vinylMaster',
            'vinylMasters',
            'mediaStatuses',
            'coverStatuses',
            'weights',
            'dimensions',
            'suppliers',
            'categories',
            'nextInternalCode'
        ));
    }

    /**
     * Store a newly created vinyl stock
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vinyl_master_id' => 'required|exists:vinyl_masters,id',
            'catalog_number' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:50',
            'internal_code' => 'nullable|string|max:50|unique:vinyl_stocks,internal_code',
            'format' => 'nullable|string|max:50',
            'num_discs' => 'integer|min:1',
            'speed' => 'nullable|string|max:20',
            'color' => 'nullable|string|max:50',
            'edition' => 'nullable|string|max:100',
            'is_new' => 'boolean',
            'store_section' => 'required|in:dj,albums',
            'media_status_id' => 'nullable|exists:media_statuses,id',
            'cover_status_id' => 'nullable|exists:cover_statuses,id',
            'weight_id' => 'nullable|exists:weights,id',
            'dimension_id' => 'nullable|exists:dimensions,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'stock' => 'integer|min:0',
            'stock_min' => 'integer|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'promotional_price' => 'nullable|numeric|min:0',
            'is_promotional' => 'boolean',
            'promo_starts_at' => 'nullable|date',
            'promo_ends_at' => 'nullable|date|after_or_equal:promo_starts_at',
            'availability' => 'required|in:available,unavailable,featured,preorder',
            'release_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'description' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'primary_category' => 'nullable|exists:categories,id',
        ]);

        DB::beginTransaction();
        try {
            $stock = VinylStock::create($validated);

            // Attach categories
            if (!empty($validated['categories'])) {
                $categoryData = [];
                foreach ($validated['categories'] as $categoryId) {
                    $categoryData[$categoryId] = [
                        'is_primary' => $categoryId == ($validated['primary_category'] ?? null),
                    ];
                }
                $stock->categories()->attach($categoryData);
            }

            // Create initial stock movement if stock > 0
            if ($stock->stock > 0) {
                $stock->stockMovements()->create([
                    'user_id' => auth('admin')->id(),
                    'type' => 'purchase',
                    'quantity' => $stock->stock,
                    'stock_before' => 0,
                    'stock_after' => $stock->stock,
                    'unit_price' => $stock->cost_price,
                    'total_price' => $stock->cost_price ? $stock->cost_price * $stock->stock : null,
                    'notes' => 'Estoque inicial',
                ]);
            }

            AdminActivityLog::log(
                auth('admin')->user(),
                'create',
                "Estoque de vinil criado: {$stock->vinylMaster->full_title}",
                $stock
            );

            DB::commit();

            return redirect()
                ->route('admin.vinyl-stocks.show', $stock)
                ->with('success', 'Estoque cadastrado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao cadastrar estoque: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified vinyl stock
     */
    public function show(VinylStock $vinylStock): View
    {
        $vinylStock->load([
            'vinylMaster.mainArtists',
            'vinylMaster.tracks',
            'mediaStatus',
            'coverStatus',
            'weight',
            'dimension',
            'supplier',
            'categories',
            'stockMovements.user'
        ]);

        return view('admin.vinyl-stocks.show', compact('vinylStock'));
    }

    /**
     * Show the form for editing the specified vinyl stock
     */
    public function edit(VinylStock $vinylStock): View
    {
        $vinylStock->load(['vinylMaster.mainArtists', 'categories']);

        $mediaStatuses = MediaStatus::active()->orderBy('sort_order')->get();
        $coverStatuses = CoverStatus::active()->orderBy('sort_order')->get();
        $weights = Weight::active()->orderBy('value')->get();
        $dimensions = Dimension::active()->orderBy('name')->get();
        $suppliers = Supplier::active()->orderBy('name')->get();
        $categories = Category::with('children')->parents()->active()->orderBy('sort_order')->get();

        return view('admin.vinyl-stocks.edit', compact(
            'vinylStock',
            'mediaStatuses',
            'coverStatuses',
            'weights',
            'dimensions',
            'suppliers',
            'categories'
        ));
    }

    /**
     * Update the specified vinyl stock
     */
    public function update(Request $request, VinylStock $vinylStock): RedirectResponse
    {
        $validated = $request->validate([
            'catalog_number' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:50',
            'internal_code' => 'nullable|string|max:50|unique:vinyl_stocks,internal_code,' . $vinylStock->id,
            'format' => 'nullable|string|max:50',
            'num_discs' => 'integer|min:1',
            'speed' => 'nullable|string|max:20',
            'color' => 'nullable|string|max:50',
            'edition' => 'nullable|string|max:100',
            'is_new' => 'boolean',
            'store_section' => 'required|in:dj,albums',
            'media_status_id' => 'nullable|exists:media_statuses,id',
            'cover_status_id' => 'nullable|exists:cover_statuses,id',
            'weight_id' => 'nullable|exists:weights,id',
            'dimension_id' => 'nullable|exists:dimensions,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'stock_min' => 'integer|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'promotional_price' => 'nullable|numeric|min:0',
            'is_promotional' => 'boolean',
            'promo_starts_at' => 'nullable|date',
            'promo_ends_at' => 'nullable|date|after_or_equal:promo_starts_at',
            'availability' => 'required|in:available,unavailable,featured,preorder',
            'release_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'description' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'primary_category' => 'nullable|exists:categories,id',
        ]);

        DB::beginTransaction();
        try {
            $oldValues = $vinylStock->toArray();
            $vinylStock->update($validated);

            // Sync categories
            if (isset($validated['categories'])) {
                $categoryData = [];
                foreach ($validated['categories'] as $categoryId) {
                    $categoryData[$categoryId] = [
                        'is_primary' => $categoryId == ($validated['primary_category'] ?? null),
                    ];
                }
                $vinylStock->categories()->sync($categoryData);
            } else {
                $vinylStock->categories()->detach();
            }

            AdminActivityLog::log(
                auth('admin')->user(),
                'update',
                "Estoque de vinil atualizado: {$vinylStock->vinylMaster->full_title}",
                $vinylStock,
                $oldValues,
                $vinylStock->fresh()->toArray()
            );

            DB::commit();

            return redirect()
                ->route('admin.vinyl-stocks.show', $vinylStock)
                ->with('success', 'Estoque atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao atualizar estoque: ' . $e->getMessage());
        }
    }

    /**
     * Add stock (purchase)
     */
    public function addStock(Request $request, VinylStock $vinylStock): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $movement = $vinylStock->addStock(
            $validated['quantity'],
            $validated['unit_price'] ?? null,
            $validated['reference'] ?? null,
            $validated['notes'] ?? null
        );

        // Update cost price if provided
        if (!empty($validated['unit_price'])) {
            $vinylStock->update(['cost_price' => $validated['unit_price']]);
        }

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Estoque adicionado: +{$validated['quantity']} unidades em {$vinylStock->vinylMaster->full_title}",
            $vinylStock
        );

        return back()->with('success', "Estoque adicionado: +{$validated['quantity']} unidades");
    }

    /**
     * Quick add stock with full purchase details (modal action)
     */
    public function quickAddStock(Request $request, VinylStock $vinylStock): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'cost_price' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_date' => 'nullable|date',
            'invoice_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $stockBefore = $vinylStock->stock;
            
            // Update stock quantity
            $vinylStock->increment('stock', $validated['quantity']);
            
            // Update cost price and supplier
            $updateData = ['cost_price' => $validated['cost_price']];
            if (!empty($validated['supplier_id'])) {
                $updateData['supplier_id'] = $validated['supplier_id'];
            }
            $vinylStock->update($updateData);

            // Create stock movement record
            $vinylStock->stockMovements()->create([
                'user_id' => auth('admin')->id(),
                'type' => 'purchase',
                'quantity' => $validated['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $vinylStock->stock,
                'unit_price' => $validated['cost_price'],
                'total_price' => $validated['cost_price'] * $validated['quantity'],
                'reference' => $validated['invoice_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_at' => $validated['purchase_date'] ?? now(),
            ]);

            AdminActivityLog::log(
                auth('admin')->user(),
                'update',
                "Entrada rápida: +{$validated['quantity']} unidades em {$vinylStock->vinylMaster->full_title} (R$ " . number_format($validated['cost_price'], 2, ',', '.') . "/un)",
                $vinylStock
            );

            DB::commit();

            return back()->with('success', "Entrada registrada: +{$validated['quantity']} unidades (Total: R$ " . number_format($validated['cost_price'] * $validated['quantity'], 2, ',', '.') . ")");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao registrar entrada: ' . $e->getMessage());
        }
    }

    /**
     * Adjust stock
     */
    public function adjustStock(Request $request, VinylStock $vinylStock): RedirectResponse
    {
        $validated = $request->validate([
            'new_stock' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $oldStock = $vinylStock->stock;
        $vinylStock->adjustStock($validated['new_stock'], $validated['notes'] ?? null);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Estoque ajustado: {$oldStock} → {$validated['new_stock']} em {$vinylStock->vinylMaster->full_title}",
            $vinylStock
        );

        return back()->with('success', "Estoque ajustado de {$oldStock} para {$validated['new_stock']}");
    }

    /**
     * Remove the specified vinyl stock
     */
    public function destroy(VinylStock $vinylStock): RedirectResponse
    {
        $title = $vinylStock->vinylMaster->full_title;

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Estoque de vinil excluído: {$title}"
        );

        $vinylStock->delete();

        return redirect()
            ->route('admin.vinyl-stocks.index')
            ->with('success', 'Estoque excluído com sucesso!');
    }

    /**
     * Stock report
     */
    public function report(Request $request): View
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        // General statistics
        $stats = [
            'total_items' => VinylStock::count(),
            'total_stock' => VinylStock::sum('stock'),
            'total_stock_value' => VinylStock::selectRaw('SUM(stock * COALESCE(cost_price, sell_price)) as total')->value('total') ?? 0,
            'total_sell_value' => VinylStock::selectRaw('SUM(stock * sell_price) as total')->value('total') ?? 0,
            'potential_profit' => 0,
            'low_stock_count' => VinylStock::whereColumn('stock', '<=', 'stock_min')->count(),
            'out_of_stock' => VinylStock::where('stock', '<=', 0)->count(),
            'new_items' => VinylStock::where('is_new', true)->count(),
            'used_items' => VinylStock::where('is_new', false)->count(),
        ];

        $stats['potential_profit'] = $stats['total_sell_value'] - $stats['total_stock_value'];

        // Movements in period
        $movements = StockMovement::with(['vinylStock.vinylMaster', 'user'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderByDesc('created_at')
            ->get();

        $movementStats = [
            'purchases' => $movements->where('type', 'purchase')->sum('quantity'),
            'purchases_value' => $movements->where('type', 'purchase')->sum('total_price'),
            'sales' => abs($movements->where('type', 'sale')->sum('quantity')),
            'sales_value' => $movements->where('type', 'sale')->sum('total_price'),
            'adjustments' => $movements->where('type', 'adjustment')->count(),
            'losses' => abs($movements->where('type', 'loss')->sum('quantity')),
        ];

        // Price averages
        $priceStats = [
            'avg_cost_price' => VinylStock::whereNotNull('cost_price')->avg('cost_price') ?? 0,
            'avg_sell_price' => VinylStock::avg('sell_price') ?? 0,
            'min_sell_price' => VinylStock::min('sell_price') ?? 0,
            'max_sell_price' => VinylStock::max('sell_price') ?? 0,
            'avg_margin' => 0,
        ];

        if ($priceStats['avg_cost_price'] > 0) {
            $priceStats['avg_margin'] = (($priceStats['avg_sell_price'] - $priceStats['avg_cost_price']) / $priceStats['avg_cost_price']) * 100;
        }

        // Top items by value
        $topByValue = VinylStock::with('vinylMaster.mainArtists')
            ->selectRaw('*, (stock * COALESCE(cost_price, sell_price)) as stock_value')
            ->orderByDesc('stock_value')
            ->limit(10)
            ->get();

        // Low stock items
        $lowStockItems = VinylStock::with('vinylMaster.mainArtists')
            ->whereColumn('stock', '<=', 'stock_min')
            ->orderBy('stock')
            ->limit(10)
            ->get();

        // By supplier
        $bySupplier = VinylStock::with('supplier')
            ->selectRaw('supplier_id, COUNT(*) as count, SUM(stock) as total_stock, SUM(stock * COALESCE(cost_price, sell_price)) as total_value')
            ->groupBy('supplier_id')
            ->get();

        return view('admin.vinyl-stocks.report', compact(
            'stats',
            'movementStats',
            'priceStats',
            'movements',
            'topByValue',
            'lowStockItems',
            'bySupplier',
            'startDate',
            'endDate'
        ));
    }
}
