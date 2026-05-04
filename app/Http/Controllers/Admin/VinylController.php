<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Artist;
use App\Models\Category;
use App\Models\RecordLabel;
use App\Models\Track;
use App\Models\VinylMaster;
use App\Services\DiscogsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VinylController extends Controller
{
    public function __construct(
        protected DiscogsService $discogs
    ) {}

    /**
     * Display a listing of vinyl records
     */
    public function index(Request $request): View
    {
        $query = VinylMaster::with(['mainArtists', 'stocks.categories']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('mainArtists', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($year = $request->get('year')) {
            $query->where('release_year', $year);
        }

        // Filter by parent category
        if ($categoryId = $request->get('category')) {
            $query->whereHas('stocks.categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId)
                    ->orWhere('categories.parent_id', $categoryId);
            });
        }

        // Filter by availability
        if ($availability = $request->get('availability')) {
            $query->whereHas('stocks', function ($q) use ($availability) {
                $q->where('availability', $availability);
            });
        }

        $vinyls = $query->latest()->paginate(50)->withQueryString();

        // Get parent categories for filter
        $parentCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.vinyls.index', compact('vinyls', 'parentCategories'));
    }

    /**
     * Show the form for creating a new vinyl - Step 1: Search Discogs
     */
    public function create(): View
    {
        $isDiscogsConfigured = $this->discogs->isConfigured();

        return view('admin.vinyls.create', compact('isDiscogsConfigured'));
    }

    /**
     * Search Discogs API
     */
    public function searchDiscogs(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        if (!$this->discogs->isConfigured()) {
            return response()->json([
                'error' => 'Discogs não está configurado. Adicione o token no arquivo .env'
            ], 400);
        }

        $results = $this->discogs->search(
            $request->get('query'),
            'release',
            50,
            $request->get('page', 1)
        );

        return response()->json($results);
    }

    /**
     * Get release details from Discogs
     */
    public function getDiscogsRelease(int $releaseId)
    {
        if (!$this->discogs->isConfigured()) {
            return response()->json([
                'error' => 'Discogs não está configurado'
            ], 400);
        }

        $release = $this->discogs->getRelease($releaseId);

        if (!$release) {
            return response()->json([
                'error' => 'Release não encontrada'
            ], 404);
        }

        $parsed = $this->discogs->parseReleaseData($release);

        return response()->json($parsed);
    }

    /**
     * Show the form for step 2: Review and confirm data
     */
    public function createStep2(Request $request)
    {
        $releaseId = $request->get('release_id');

        if (!$releaseId) {
            return redirect()->route('admin.vinyls.create')
                ->with('error', 'Selecione uma release do Discogs primeiro.');
        }

        // Check if vinyl already exists with this release_id (including soft deleted)
        $existingVinyl = VinylMaster::withTrashed()->where('discogs_release_id', $releaseId)->first();
        if ($existingVinyl) {
            if ($existingVinyl->trashed()) {
                // Restore the soft-deleted vinyl and redirect to stock creation
                $existingVinyl->restore();
                
                AdminActivityLog::log(
                    auth('admin')->user(),
                    'restore',
                    "Disco '{$existingVinyl->full_title}' restaurado",
                    $existingVinyl
                );
                
                return redirect()
                    ->route('admin.vinyl-stocks.create', ['vinyl_master_id' => $existingVinyl->id])
                    ->with('success', "Disco \"{$existingVinyl->full_title}\" restaurado! Complete os dados de estoque.");
            }
            
            return redirect()->route('admin.vinyls.create')
                ->with('error', "Este disco já está cadastrado: \"{$existingVinyl->full_title}\"");
        }

        $release = $this->discogs->getRelease($releaseId);

        if (!$release) {
            return redirect()->route('admin.vinyls.create')
                ->with('error', 'Release não encontrada no Discogs.');
        }

        $data = $this->discogs->parseReleaseData($release);

        // Check if artists already exist in database by discogs_id
        $existingArtists = [];
        if (!empty($data['artists'])) {
            $discogsIds = collect($data['artists'])->pluck('discogs_id')->filter()->toArray();
            $existingArtists = Artist::whereIn('discogs_id', $discogsIds)
                ->get()
                ->keyBy('discogs_id')
                ->toArray();
        }

        return view('admin.vinyls.create-step2', compact('data', 'releaseId', 'existingArtists'));
    }

    /**
     * Store a newly created vinyl record
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'discogs_release_id' => 'nullable|string',
            'discogs_master_id' => 'nullable|string',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'images' => 'nullable|array',
            'discogs_url' => 'nullable|string',
            'release_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'country' => 'nullable|string|max:100',
            'genres' => 'nullable|array',
            'styles' => 'nullable|array',
            'artists' => 'nullable|array',
            'artists.*.discogs_id' => 'nullable|string',
            'artists.*.name' => 'required|string|max:255',
            'artists.*.role' => 'nullable|string|max:50',
            'tracklist' => 'nullable|array',
            'tracklist.*.position' => 'nullable|string|max:10',
            'tracklist.*.name' => 'required|string|max:255',
            'tracklist.*.duration' => 'nullable|string|max:10',
            'tracklist.*.youtube_url' => 'nullable|url|max:500',
            'tracklist.*.sort_order' => 'nullable|integer',
            'labels' => 'nullable|array',
        ]);

        // Check if vinyl exists (including soft deleted) and restore if needed
        if (!empty($validated['discogs_release_id'])) {
            $existingVinyl = VinylMaster::withTrashed()
                ->where('discogs_release_id', $validated['discogs_release_id'])
                ->first();
            
            if ($existingVinyl) {
                if ($existingVinyl->trashed()) {
                    $existingVinyl->restore();
                    
                    AdminActivityLog::log(
                        auth('admin')->user(),
                        'restore',
                        "Disco '{$existingVinyl->full_title}' restaurado",
                        $existingVinyl
                    );
                    
                    return redirect()
                        ->route('admin.vinyl-stocks.create', ['vinyl_master_id' => $existingVinyl->id])
                        ->with('success', "Disco restaurado! Complete os dados de estoque.");
                }
                
                return redirect()
                    ->route('admin.vinyl-stocks.create', ['vinyl_master_id' => $existingVinyl->id])
                    ->with('info', "Este disco já existe. Adicione um novo estoque.");
            }
        }

        // Create or find record label
        $recordLabelId = null;
        if (!empty($validated['labels'][0])) {
            $labelData = $validated['labels'][0];
            $recordLabel = RecordLabel::firstOrCreate(
                ['discogs_id' => $labelData['discogs_id']],
                [
                    'name' => $labelData['name'],
                    'slug' => Str::slug($labelData['name']),
                    'is_active' => true,
                ]
            );
            $recordLabelId = $recordLabel->id;
        }

        // Create vinyl master
        $vinyl = VinylMaster::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . uniqid(),
            'discogs_release_id' => $validated['discogs_release_id'] ?? null,
            'discogs_master_id' => $validated['discogs_master_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'cover_image' => $validated['cover_image'] ?? null,
            'images' => $validated['images'] ?? null,
            'discogs_url' => $validated['discogs_url'] ?? null,
            'release_year' => $validated['release_year'] ?? null,
            'country' => $validated['country'] ?? null,
            'genres' => $validated['genres'] ?? null,
            'styles' => $validated['styles'] ?? null,
            'record_label_id' => $recordLabelId,
        ]);

        // Create or find artists and attach
        if (!empty($validated['artists'])) {
            foreach ($validated['artists'] as $artistData) {
                $artist = Artist::firstOrCreate(
                    ['discogs_id' => $artistData['discogs_id']],
                    [
                        'name' => $artistData['name'],
                        'slug' => Str::slug($artistData['name']),
                        'is_active' => true,
                    ]
                );

                $vinyl->artists()->attach($artist->id, [
                    'role' => $artistData['role'] ?? 'main',
                ]);
            }
        }

        // Create tracks
        if (!empty($validated['tracklist'])) {
            foreach ($validated['tracklist'] as $index => $trackData) {
                Track::create([
                    'vinyl_master_id' => $vinyl->id,
                    'position' => $trackData['position'] ?? null,
                    'name' => $trackData['name'],
                    'duration' => $trackData['duration'] ?? null,
                    'duration_seconds' => Track::durationToSeconds($trackData['duration'] ?? null),
                    'youtube_url' => $trackData['youtube_url'] ?? null,
                    'sort_order' => $trackData['sort_order'] ?? $index,
                ]);
            }
        }

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Disco '{$vinyl->full_title}' criado",
            $vinyl
        );

        // Redirect to step 3: Stock/Commercial data
        return redirect()
            ->route('admin.vinyl-stocks.create', ['vinyl_master_id' => $vinyl->id])
            ->with('success', 'Disco cadastrado! Agora complete os dados de estoque e preços.');
    }

    /**
     * Display the specified vinyl record
     */
    public function show(VinylMaster $vinyl): View
    {
        $vinyl->load(['mainArtists', 'recordLabel', 'tracks', 'product']);

        return view('admin.vinyls.show', compact('vinyl'));
    }

    /**
     * Show the form for editing the specified vinyl record
     */
    public function edit(VinylMaster $vinyl): View
    {
        $vinyl->load(['artists', 'recordLabel', 'tracks']);
        $recordLabels = RecordLabel::active()->orderBy('name')->get();

        return view('admin.vinyls.edit', compact('vinyl', 'recordLabels'));
    }

    /**
     * Update the specified vinyl record
     */
    public function update(Request $request, VinylMaster $vinyl): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'release_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'country' => 'nullable|string|max:100',
            'record_label_id' => 'nullable|exists:record_labels,id',
            'genres' => 'nullable|string',
            'styles' => 'nullable|string',
        ]);

        $oldValues = $vinyl->toArray();

        $vinyl->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'release_year' => $validated['release_year'] ?? null,
            'country' => $validated['country'] ?? null,
            'record_label_id' => $validated['record_label_id'] ?? null,
            'genres' => $validated['genres'] ? array_map('trim', explode(',', $validated['genres'])) : null,
            'styles' => $validated['styles'] ? array_map('trim', explode(',', $validated['styles'])) : null,
        ]);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Disco '{$vinyl->full_title}' atualizado",
            $vinyl,
            $oldValues,
            $vinyl->fresh()->toArray()
        );

        return redirect()
            ->route('admin.vinyls.show', $vinyl)
            ->with('success', 'Disco atualizado com sucesso!');
    }

    /**
     * Remove the specified vinyl record
     */
    public function destroy(VinylMaster $vinyl): RedirectResponse
    {
        $title = $vinyl->full_title;

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Disco '{$title}' excluído"
        );

        $vinyl->delete();

        return redirect()
            ->route('admin.vinyls.index')
            ->with('success', 'Disco excluído com sucesso!');
    }
}
