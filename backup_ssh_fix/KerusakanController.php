<?php

namespace App\Http\Controllers;

use App\Models\Kerusakan;
use App\Models\Ruas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KerusakanController extends Controller
{
    public function index()
    {
        return Inertia::render('kerusakan/index', [
            'items' => Kerusakan::latest()->limit(20)->get(['id','ruas_code','sta']),
        ]);
    }

    public function create()
    {
        return Inertia::render('kerusakan/form', [
            'mode' => 'create',
            'ruasOptions' => Ruas::orderBy('nm_ruas')->get(['code AS value','nm_ruas AS label']),
        ]);
    }

    public function store(Request $request) { return back(); }

    public function show(Kerusakan $kerusakan)
    {
        $ruas = Ruas::where('code', $kerusakan->ruas_code)->first();

        if ($kerusakan->point && method_exists($kerusakan->point,'getX')) {
            $markerFeature = $kerusakan->toGeoJsonFeature();
        } else {
            $markerFeature = [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [0.0, 0.0]],
                'properties' => [
                    'id'        => $kerusakan->id,
                    'ruas_code' => $kerusakan->ruas_code,
                    'nm_ruas'   => $ruas->nm_ruas ?? '',
                    'sta'       => $kerusakan->sta,
                    'image'     => $kerusakan->image_url,
                    'warning'   => 'Koordinat belum diisi',
                ],
            ];
        }

        $lines = [];

        return Inertia::render('kerusakan/show', [
            'marker' => $markerFeature,
            'lines'  => $lines,
            'info'   => [
                'sta'       => $kerusakan->sta,
                'nama_ruas' => $ruas->nm_ruas ?? '—',
                'image'     => $kerusakan->image_url,
            ],
        ]);
    }

    public function edit(int $id)
    {
        $k = Kerusakan::with('ruas')->findOrFail($id);
        $lat = ($k->point && method_exists($k->point,'getY')) ? $k->point->getY() : null;
        $lon = ($k->point && method_exists($k->point,'getX')) ? $k->point->getX() : null;

        return Inertia::render('kerusakan/form', [
            'mode'   => 'edit',
            'marker' => [
                'id'        => $k->id,
                'ruas_code' => $k->ruas_code,
                'sta'       => $k->sta,
                'lat'       => $lat,
                'lon'       => $lon,
                'image'     => $k->image_url,
            ],
            'ruasOptions' => Ruas::orderBy('nm_ruas')->get(['code AS value','nm_ruas AS label']),
        ]);
    }

    public function update(Request $request, int $id) { return back(); }
    public function destroy(int $id) { return back(); }
}
