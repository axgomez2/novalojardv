<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Artist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArtistController extends Controller
{
    public function index(Request $request): View
    {
        $query = Artist::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('discogs_id', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $artists = $query->latest()->paginate(15)->withQueryString();

        return view('admin.settings.artists.index', compact('artists'));
    }

    public function create(): View
    {
        return view('admin.settings.artists.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:artists'],
            'discogs_id' => ['nullable', 'string', 'max:50', 'unique:artists'],
            'profile' => ['nullable', 'string'],
            'discogs_url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $artist = Artist::create($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'create',
            "Artista '{$artist->name}' criado",
            $artist
        );

        return redirect()
            ->route('admin.settings.artists.index')
            ->with('success', 'Artista criado com sucesso!');
    }

    public function edit(Artist $artist): View
    {
        return view('admin.settings.artists.edit', compact('artist'));
    }

    public function update(Request $request, Artist $artist): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('artists')->ignore($artist->id)],
            'discogs_id' => ['nullable', 'string', 'max:50', Rule::unique('artists')->ignore($artist->id)],
            'profile' => ['nullable', 'string'],
            'discogs_url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $oldValues = $artist->toArray();
        $artist->update($validated);

        AdminActivityLog::log(
            auth('admin')->user(),
            'update',
            "Artista '{$artist->name}' atualizado",
            $artist,
            $oldValues,
            $artist->fresh()->toArray()
        );

        return redirect()
            ->route('admin.settings.artists.index')
            ->with('success', 'Artista atualizado com sucesso!');
    }

    public function destroy(Artist $artist): RedirectResponse
    {
        $name = $artist->name;

        AdminActivityLog::log(
            auth('admin')->user(),
            'delete',
            "Artista '{$name}' excluído"
        );

        $artist->delete();

        return redirect()
            ->route('admin.settings.artists.index')
            ->with('success', 'Artista excluído com sucesso!');
    }
}
