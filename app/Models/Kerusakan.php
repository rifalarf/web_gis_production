<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Clickbar\Magellan\Data\Geometries\Point;

class Kerusakan extends Model
{
    protected $table = 'kerusakans';

    protected $fillable = [
        'ruas_code','sta','point','image_path',
    ];

    protected $casts = [
        'point' => Point::class,
    ];

    public function ruas()
    {
        return $this->belongsTo(Ruas::class, 'ruas_code', 'code');
    }

    public function toGeoJsonFeature(): array
    {
        return [
            'type'       => 'Feature',
            'geometry'   => [
                'type'        => 'Point',
                'coordinates' => [$this->point->getX(), $this->point->getY()],
            ],
            'properties' => [
                'id'        => $this->id,
                'ruas_code' => $this->ruas_code,
                'nm_ruas'   => $this->ruas->nm_ruas ?? '',
                'sta'       => $this->sta,
                'image'     => $this->image_path,
            ],
        ];
    }

}
