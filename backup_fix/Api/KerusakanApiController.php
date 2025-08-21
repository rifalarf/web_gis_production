<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kerusakan;
use Illuminate\Support\Facades\Cache;

class KerusakanApiController extends Controller
{
    public function __invoke()
    {
        try {
            $data = Cache::remember('kerusakan_geojson', now()->addMinutes(10), function () {
                $features = [];
                $rows = Kerusakan::with('ruas:code,nm_ruas')->get();

                foreach ($rows as $k) {
                    $x = ($k->point && method_exists($k->point, 'getX')) ? $k->point->getX() : null;
                    $y = ($k->point && method_exists($k->point, 'getY')) ? $k->point->getY() : null;
                    if ($x === null || $y === null) continue;

                    $features[] = [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [$x, $y]],
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
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['type' => 'FeatureCollection', 'features' => []], 200);
        }
    }
}
