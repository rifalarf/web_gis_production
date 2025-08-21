<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kerusakan;
use Illuminate\Support\Facades\Cache;


class KerusakanApiController extends Controller
{
    public function index()
    {
        return Cache::remember('kerusakan_geojson', 60, function () {
            $features = Kerusakan::with('ruas')->get()->map(fn ($k) => [
                'type'       => 'Feature',
                'properties' => [
                    'id'        => $k->id,
                    'ruas_code' => $k->ruas_code,
                    'nm_ruas'   => $k->ruas->nm_ruas,
                    'sta'       => $k->sta,
                    'image'     => $k->image_path
                        ? (str_starts_with($k->image_path, 'http') ? $k->image_path : asset('storage/'.$k->image_path))
                        : null,
                ],
                'geometry' => $k->point->jsonSerialize(),
            ]);

            return response()->json([
                'type'     => 'FeatureCollection',
                'features' => $features,
            ]);
        });
    }
}
