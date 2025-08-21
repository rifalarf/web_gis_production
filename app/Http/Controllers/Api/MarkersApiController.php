<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kerusakan;
use Illuminate\Support\Facades\Cache;

class MarkersApiController extends Controller
{
    public function __invoke()
    {
        $data = Cache::remember('markers_geojson', now()->addMinutes(10), function () {
            $features = [];
            $rows = Kerusakan::with('ruas:code,nm_ruas')->get();

            foreach ($rows as $k) {
                $coords = ($k->point && method_exists($k->point, 'getX'))
                    ? [$k->point->getX(), $k->point->getY()]
                    : [0.0, 0.0];

                $features[] = [
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => $coords],
                    'properties' => [
                        'id'        => $k->id,
                        'ruas_code' => $k->ruas_code,
                        'nm_ruas'   => $k->ruas->nm_ruas ?? null,
                        'sta'       => $k->sta,
                        'image'     => $k->image_url,
                    ],
                ];
            }

            return ['type' => 'FeatureCollection', 'features' => $features];
        });

        return response()->json($data);
    }
}
