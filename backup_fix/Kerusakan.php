<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kerusakan extends Model
{
    protected $table = 'kerusakan';

    public function ruas(): BelongsTo
    {
        return $this->belongsTo(Ruas::class, 'ruas_code', 'code');
    }

    // absolute URL (Cloudinary http/https or local storage)
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image_path)) {
            return null;
        }
        return str_starts_with($this->image_path, 'http')
            ? $this->image_path
            : asset('storage/' . ltrim($this->image_path, '/'));
    }

    // helper (aman bila point null)
    public function toGeoJsonFeature(): array
    {
        $x = ($this->point && method_exists($this->point, 'getX')) ? $this->point->getX() : 0.0;
        $y = ($this->point && method_exists($this->point, 'getY')) ? $this->point->getY() : 0.0;

        return [
            'type' => 'Feature',
            'geometry' => ['type' => 'Point', 'coordinates' => [$x, $y]],
            'properties' => [
                'id'        => $this->id,
                'ruas_code' => $this->ruas_code ?? null,
                'nm_ruas'   => optional($this->ruas)->nm_ruas,
                'sta'       => $this->sta ?? null,
                'image'     => $this->image_url,
            ],
        ];
    }
}
