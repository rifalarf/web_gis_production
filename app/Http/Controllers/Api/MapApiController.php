<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Segment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class MapApiController extends Controller
{
    public function index()
    {
        // cache for 5 min so repeated public hits are fast
        return Cache::remember('segments_geojson', now()->addMinutes(5), function () {

            $colourMap = [
                'baik'          => '#22c55e', // green
                'sedang'        => '#eab308', // yellow
                'rusak_ringan'  => '#f97316', // orange
                'rusak_berat'   => '#ef4444', // red
            ];

            $features = Segment::with('ruas')
                ->get()
                ->map(function ($s) use ($colourMap) {
                    return [
                        'type'       => 'Feature',
                        'properties' => [
                            'code'        => $s->ruas_code,
                            'nm_ruas'     => $s->ruas->nm_ruas ?? '',
                            'sta'         => $s->sta,
                            'jens_perm'   => $s->jens_perm,
                            'kondisi'     => $s->kondisi,
                            'kecamatan'   => $s->ruas->kecamatan ?? null,
                            'panjang'     => (float) ($s->ruas->panjang ?? 0),
                            // front-end can read this directly
                            'stroke'      => $colourMap[$s->kondisi] ?? '#9ca3af', // grey when null
                            'stroke-width'=> 3,
                        ],
                        // Magellan can give GeoJSON out-of-the-box
                        'geometry' => $s->geometry->jsonSerialize(),
                    ];
                });

            return response()->json([
                'type'     => 'FeatureCollection',
                'features' => $features,
            ]);
        });
    }
}
