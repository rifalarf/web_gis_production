<?php

namespace App\Http\Controllers;

use App\Models\Kerusakan;
use App\Models\Ruas;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Cloudinary\Cloudinary; // added

class KerusakanController extends Controller
{
    /* -----------------------------------------------------------------
       List page (stub) – we’ll build the Inertia view later
    ----------------------------------------------------------------- */
    public function index()
{
    $rows = Kerusakan::with('ruas')->get()->map(fn ($k) => [
        'id'       => $k->id,
        'sta'      => $k->sta,
        'nm_ruas'  => $k->ruas->nm_ruas,
        'lat'      => $k->point->getY(),   // Magellan helpers
        'lon'      => $k->point->getX(),
    ]);

    return Inertia::render('kerusakan/index', [
        'markers' => $rows,
    ]);
}

    /* -----------------------------------------------------------------
       Show the “add marker” form (stub)
    ----------------------------------------------------------------- */
    public function create()
    {
        return Inertia::render('kerusakan/form', [
        'mode'   => 'create',
        'ruasOptions' => Ruas::orderBy('nm_ruas')
                         ->get(['code AS value', 'nm_ruas AS label']),
        ]);
    }

    /* -----------------------------------------------------------------
       Store
    ----------------------------------------------------------------- */
    public function store(Request $request)
    {
        $data = $request->validate([
            'ruas_code' => 'required|exists:ruas,code',
            'sta'       => 'nullable|string|max:50',
            'lat'       => 'required|numeric|between:-90,90',
            'lon'       => 'required|numeric|between:-180,180',
            'photo'     => 'nullable|image|max:2048', // 2 MB
        ]);

        /* handle photo upload ------------------------------------------------ */
        if ($request->file('photo')) {
            // if CLOUDINARY_URL set, upload to Cloudinary, otherwise fallback to local storage
            if (env('CLOUDINARY_URL')) {
                $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
                $upload = $cloudinary->uploadApi()->upload(
                    $request->file('photo')->getRealPath(),
                    ['folder' => 'kerusakan']
                );
                $data['image_path'] = $upload['secure_url'] ?? null;
            } else {
                $data['image_path'] = $request
                    ->file('photo')
                    ->store('kerusakan', 'public'); // storage/kerusakan/…
            }
        }

        /* make Point geometry (lon, lat, srid) ------------------------------ */
        $point = Point::make(
            $data['lon'],   // x = longitude
            $data['lat'],   // y = latitude
            null,           // z
            null,           // m
            4326            // SRID  ← put it here
        );

        $data['point'] = $point;

        Kerusakan::create($data);

        Cache::forget('kerusakan_geojson');

        return to_route('kerusakan.index')
            ->with('success', 'Marker ditambahkan');
    }

    /* -----------------------------------------------------------------
       Show single marker (stub)
    ----------------------------------------------------------------- */
    public function show(Kerusakan $kerusakan)
{
    /* ---------------------------------------------------------------
       1.  Try to fetch the ruas this marker belongs to
    ----------------------------------------------------------------*/
    $ruas = $kerusakan->ruas;            // may be null if ruas deleted
    $lines = $ruas
        ? $ruas->toGeoJson()             // normal case: show polyline(s)
        : ['type' => 'FeatureCollection', 'features' => []]; // fallback

    /* ---------------------------------------------------------------
       2.  Build the pin feature.
           If 'point' column is NULL we fall back to dummy [0,0] so
           the page never crashes. You can later edit the marker and
           save real coordinates.
    ----------------------------------------------------------------*/
    if ($kerusakan->point) {
        // normal case
        $markerFeature = $kerusakan->toGeoJsonFeature();
    } else {
        // safety fallback
        $markerFeature = [
            'type'       => 'Feature',
            'geometry'   => [
                'type'        => 'Point',
                'coordinates' => [0, 0],
            ],
            'properties' => [
                'id'        => $kerusakan->id,
                'ruas_code' => $kerusakan->ruas_code,
                'nm_ruas'   => $ruas->nm_ruas ?? '',
                'sta'       => $kerusakan->sta,
                'image'     => $kerusakan->image_path
                                ? asset('storage/'.$kerusakan->image_path)
                                : null,
                'warning'   => 'Koordinat belum diisi',
            ],
        ];
    }

    /* ---------------------------------------------------------------
       3.  Send everything to Inertia
    ----------------------------------------------------------------*/
    return Inertia::render('kerusakan/show', [
        'marker' => $markerFeature,
        'lines'  => $lines,
        'info'   => [
            'sta'       => $kerusakan->sta,
            'nama_ruas' => $ruas->nm_ruas ?? '—',
            'image'     => $kerusakan->image_path,
        ],
    ]);
}


    /* -----------------------------------------------------------------
       Edit form (stub)
    ----------------------------------------------------------------- */
    public function edit(int $id)
    {
        $k = Kerusakan::with('ruas')->findOrFail($id);

        return Inertia::render('kerusakan/form', [
            'mode'   => 'edit',
            'marker' => [
                'id'        => $k->id,
                'ruas_code' => $k->ruas_code,
                'sta'       => $k->sta,
                'lat'       => $k->point->getY(),
                'lon'       => $k->point->getX(),
                'image'     => $k->image_path,
            ],
            'ruasOptions' => Ruas::orderBy('nm_ruas')
                            ->get(['code AS value', 'nm_ruas AS label']),
        ]);
    }

    /* -----------------------------------------------------------------
       Update (only changed part)
    ----------------------------------------------------------------- */
    public function update(Request $request, Kerusakan $kerusakan)
    {
        $data = $request->validate([
            'ruas_code' => 'required|exists:ruas,code',
            'sta'       => 'nullable|string|max:50',
            'lat'       => 'required|numeric|between:-90,90',
            'lon'       => 'required|numeric|between:-180,180',
            'photo'     => 'nullable|image|max:2048',
        ]);

        /* 2. replace photo if new uploaded ------------------------------------ */
        if ($request->file('photo')) {
            // delete old
            if ($kerusakan->image_path) {
                // if stored on Cloudinary try to remove by public_id, else delete local
                if (env('CLOUDINARY_URL') && str_contains($kerusakan->image_path, 'res.cloudinary.com')) {
                    if (preg_match('#/upload/(?:v\d+/)?(.+)\.[a-zA-Z0-9]+$#', $kerusakan->image_path, $m)) {
                        $publicId = $m[1];
                        try {
                            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
                            $cloudinary->uploadApi()->destroy($publicId);
                        } catch (\Throwable $e) {
                            // ignore deletion errors
                        }
                    }
                } else {
                    Storage::disk('public')->delete($kerusakan->image_path);
                }
            }

            if (env('CLOUDINARY_URL')) {
                $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
                $upload = $cloudinary->uploadApi()->upload(
                    $request->file('photo')->getRealPath(),
                    ['folder' => 'kerusakan']
                );
                $data['image_path'] = $upload['secure_url'] ?? null;
            } else {
                $data['image_path'] = $request
                    ->file('photo')
                    ->store('kerusakan', 'public');
            }
        }

        /* 3. geometry ---------------------------------------------------------- */
        $data['point'] = Point::make(
            $data['lon'],     // x = lon
            $data['lat'],     // y = lat
            null, null, 4326  // z, m, SRID
        );

        /* 4. update ------------------------------------------------------------ */
        $kerusakan->update($data);
        Cache::forget('kerusakan_geojson');

        return to_route('kerusakan.index')
            ->with('success', 'Marker diperbarui');
    }

    /* -----------------------------------------------------------------
       Delete
    ----------------------------------------------------------------- */
    public function destroy(int $id)
    {
        Cache::forget('kerusakan_geojson');
        $k = Kerusakan::findOrFail($id);

        /* remove file if exists */
        if ($k->image_path) {
            // try delete from Cloudinary if applicable, otherwise delete local file
            if (env('CLOUDINARY_URL') && str_contains($k->image_path, 'res.cloudinary.com')) {
                if (preg_match('#/upload/(?:v\d+/)?(.+)\.[a-zA-Z0-9]+$#', $k->image_path, $m)) {
                    $publicId = $m[1];
                    try {
                        $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
                        $cloudinary->uploadApi()->destroy($publicId);
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            } else {
                Storage::disk('public')->delete($k->image_path);
            }
        }

        $k->delete();
        Cache::forget('kerusakan_geojson');

        return back()->with('success', 'Marker dihapus');
    }
}
