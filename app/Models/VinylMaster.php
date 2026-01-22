<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class VinylMaster extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'discogs_master_id',
        'discogs_release_id',
        'description',
        'cover_image',
        'images',
        'discogs_url',
        'release_year',
        'country',
        'genres',
        'styles',
        'record_label_id',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'genres' => 'array',
            'styles' => 'array',
            'release_year' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('title') && !$model->isDirty('slug')) {
                $model->slug = Str::slug($model->title);
            }
        });
    }

    public function recordLabel(): BelongsTo
    {
        return $this->belongsTo(RecordLabel::class);
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'artist_vinyl_master')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function mainArtists(): BelongsToMany
    {
        return $this->artists()->wherePivot('role', 'main');
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class)->orderBy('sort_order');
    }

    public function product(): MorphOne
    {
        return $this->morphOne(Product::class, 'productable');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(VinylStock::class);
    }

    public function vinylImages(): HasMany
    {
        return $this->hasMany(VinylImage::class)->ordered();
    }

    public function primaryVinylImage()
    {
        return $this->hasOne(VinylImage::class)->where('is_primary', true);
    }

    public function getArtistNamesAttribute(): string
    {
        return $this->mainArtists->pluck('name')->join(', ');
    }

    public function getFullTitleAttribute(): string
    {
        $artists = $this->artist_names;
        return $artists ? "{$artists} - {$this->title}" : $this->title;
    }

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->cover_image) {
            if (Str::startsWith($this->cover_image, ['http://', 'https://'])) {
                return $this->cover_image;
            }
            return asset('storage/' . $this->cover_image);
        }
        return null;
    }
}
