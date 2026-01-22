<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\VinylImage;
use App\Models\VinylMaster;
use App\Services\DiscogsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VinylImageController extends Controller
{
    public function __construct(
        protected DiscogsService $discogs
    ) {}

    /**
     * Display images for a vinyl
     */
    public function index(VinylMaster $vinyl): View
    {
        $vinyl->load(['vinylImages', 'mainArtists']);
        
        // Get Discogs images if available
        $discogsImages = [];
        if ($vinyl->discogs_release_id && $this->discogs->isConfigured()) {
            $release = $this->discogs->getRelease($vinyl->discogs_release_id);
            if ($release && isset($release['images'])) {
                $discogsImages = $release['images'];
            }
        }
        
        // Also check stored images array
        if (empty($discogsImages) && $vinyl->images) {
            $discogsImages = array_map(function($img) {
                return [
                    'uri' => $img['uri'] ?? $img,
                    'uri150' => $img['uri150'] ?? $img,
                    'type' => $img['type'] ?? 'secondary',
                    'width' => $img['width'] ?? null,
                    'height' => $img['height'] ?? null,
                ];
            }, $vinyl->images);
        }

        return view('admin.vinyls.images', compact('vinyl', 'discogsImages'));
    }

    /**
     * Upload a new image
     */
    public function store(Request $request, VinylMaster $vinyl): RedirectResponse
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'alt_text' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
        ]);

        $file = $request->file('image');
        $path = $file->store('vinyl-images/' . $vinyl->id, 'public');
        
        // Get image dimensions
        $imageInfo = getimagesize($file->getPathname());
        
        // If setting as primary, unset other primaries
        if ($request->boolean('is_primary')) {
            $vinyl->vinylImages()->update(['is_primary' => false]);
        }

        $image = $vinyl->vinylImages()->create([
            'type' => 'local',
            'url' => Storage::url($path),
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $imageInfo[0] ?? null,
            'height' => $imageInfo[1] ?? null,
            'alt_text' => $validated['alt_text'] ?? $vinyl->title,
            'is_primary' => $request->boolean('is_primary'),
            'sort_order' => $vinyl->vinylImages()->count(),
        ]);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Imagem adicionada ao disco '{$vinyl->title}'",
            $image
        );

        return redirect()
            ->route('admin.vinyls.images.index', $vinyl)
            ->with('success', 'Imagem enviada com sucesso!');
    }

    /**
     * Import image from Discogs
     */
    public function importDiscogs(Request $request, VinylMaster $vinyl): RedirectResponse
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'is_primary' => 'boolean',
        ]);

        // If setting as primary, unset other primaries
        if ($request->boolean('is_primary')) {
            $vinyl->vinylImages()->update(['is_primary' => false]);
        }

        // Check if image already imported
        $exists = $vinyl->vinylImages()->where('url', $validated['url'])->exists();
        if ($exists) {
            return redirect()
                ->route('admin.vinyls.images.index', $vinyl)
                ->with('error', 'Esta imagem já foi importada.');
        }

        $image = $vinyl->vinylImages()->create([
            'type' => 'discogs',
            'url' => $validated['url'],
            'alt_text' => $vinyl->title,
            'is_primary' => $request->boolean('is_primary'),
            'sort_order' => $vinyl->vinylImages()->count(),
        ]);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Imagem do Discogs importada para o disco '{$vinyl->title}'",
            $image
        );

        return redirect()
            ->route('admin.vinyls.images.index', $vinyl)
            ->with('success', 'Imagem do Discogs importada com sucesso!');
    }

    /**
     * Set image as primary
     */
    public function setPrimary(VinylMaster $vinyl, VinylImage $image): RedirectResponse
    {
        // Unset all primaries
        $vinyl->vinylImages()->update(['is_primary' => false]);
        
        // Set this one as primary
        $image->update(['is_primary' => true]);

        return redirect()
            ->route('admin.vinyls.images.index', $vinyl)
            ->with('success', 'Imagem definida como principal!');
    }

    /**
     * Update image order
     */
    public function updateOrder(Request $request, VinylMaster $vinyl): RedirectResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:vinyl_images,id',
        ]);

        foreach ($validated['order'] as $index => $imageId) {
            VinylImage::where('id', $imageId)
                ->where('vinyl_master_id', $vinyl->id)
                ->update(['sort_order' => $index]);
        }

        return redirect()
            ->route('admin.vinyls.images.index', $vinyl)
            ->with('success', 'Ordem das imagens atualizada!');
    }

    /**
     * Delete an image
     */
    public function destroy(VinylMaster $vinyl, VinylImage $image): RedirectResponse
    {
        // Delete file if local
        if ($image->type === 'local' && $image->path) {
            Storage::disk('public')->delete($image->path);
        }

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Imagem removida do disco '{$vinyl->title}'"
        );

        $image->delete();

        return redirect()
            ->route('admin.vinyls.images.index', $vinyl)
            ->with('success', 'Imagem removida com sucesso!');
    }
}
