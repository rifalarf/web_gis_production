<?php

namespace App\Http\Controllers;

use App\Models\{Ruas, Segment, Kerusakan};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;   // add
use Illuminate\Validation\ValidationException;

class RuasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil data menggunakan Eloquent
        $allRuas = Ruas::all();

        // Transformasi data dan PAKSA 'panjang' menjadi ANGKA (float)
        $ruas = $allRuas->map(fn ($r) => [
            'code'    => $r->code,
            'nm_ruas' => $r->nm_ruas,
            'panjang' => (float) $r->panjang, // INI PERBAIKANNYA
        ]);

        // Hapus atau beri komentar pada baris dd() ini setelah selesai
        // dd($ruas);

        return Inertia::render('ruas/index', [
            'ruas' => $ruas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $code)
    {
        $ruas = Ruas::with('segments')->where('code', $code)->firstOrFail();

        /* ── line features for THIS ruas ─────────────────────────── */
        $features = $ruas->segments->map(fn ($s) => [
            'type'       => 'Feature',
            'properties' => [
                'code'      => $s->ruas_code,
                'nm_ruas'   => $s->ruas->nm_ruas,
                'sta'       => $s->sta,
                'jens_perm' => $s->jens_perm,
                'kondisi'   => $s->kondisi,
            ],
            'geometry'   => $s->geometry->jsonSerialize(),
        ]);

        $geojson = [
            'type'     => 'FeatureCollection',
            'features' => $features,
        ];

        /* ── gather markers that belong to this ruas ─────────────── */
        $markerFeatures = Kerusakan::where('ruas_code', $code)
            ->get()
            ->map->toGeoJsonFeature()
            ->all();

        $markersGeojson = [
            'type'     => 'FeatureCollection',
            'features' => $markerFeatures,
        ];

        /* ── table rows (id, sta, lat, lon) ───────────────────── */
        $markerRows = Kerusakan::where('ruas_code', $code)
            ->orderBy('sta')
            ->get()
            ->map(fn ($k) => [
                'id'  => $k->id,
                'sta' => $k->sta ?? '—',
                'lat' => $k->point->getY(),
                'lon' => $k->point->getX(),
            ]);


        /* ── inertia view ─────────────────────────────────────────── */
        return Inertia::render('ruas/show', [
            'ruas'            => $ruas->only('code', 'nm_ruas','kecamatan','panjang'),
            'geojson'         => $geojson,
            'markersGeojson'  => $markersGeojson,
            'markerRows'      => $markerRows,    // ← NEW PROP
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function purge(Request $request)
{
    // 1. require & verify password in ONE rule
    $request->validate([
        'password' => ['required', 'current_password'],
    ]);

    // 2. wipe data (only reached when password is correct)
    Ruas::truncate();
    Segment::truncate();      // FK cascade? keep or remove
    Kerusakan::truncate();    // optional
    Cache::forget('segments_geojson');
    Cache::forget('kerusakan_geojson');

    // 3. redirect wherever you like
    return redirect()
        ->route('dashboard')
        ->with('success', 'Semua ruas berhasil dihapus.');
    }

}
