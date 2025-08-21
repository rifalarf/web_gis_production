<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\GeojsonUploadRequest;
use App\Models\{Ruas, Segment};
use Clickbar\Magellan\IO\Parser\Geojson\GeojsonParser;
use Clickbar\Magellan\Data\Geometries\MultiLineString;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class GeojsonController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file'   => 'required|file|mimes:json,geojson',
            'mode'    => 'required|in:insert,update',
        ]);

        Cache::forget('segments_geojson');

        DB::transaction(function () use ($request) {
            $file = $request->file('file');
            $geojson    = json_decode($file->get(), true);
            $features   = collect($geojson['features']);
            $mode       = $request->mode;

            // group features by CODE
            $featuresByCode = $features->groupBy('properties.CODE');

            foreach ($featuresByCode as $code => $features) {
                if (empty($code)) continue;

                $first      = $features->first()['properties'];
                $ruasExists = Ruas::where('code', $code)->exists();

                if ($mode === 'insert' && $ruasExists) {
                    continue;
                }
                if ($mode === 'update' && !$ruasExists) {
                    continue;
                }

                // create or update ruas row
                $ruas = Ruas::firstOrNew(['code' => $code]);
                $ruas->fill([
                    'nm_ruas'      => $first['Nm_Ruas'] ?? 'Tanpa Nama',
                    'kon_baik'     => $first['Kon_Baik'] ?? 0,
                    'kon_sdg'      => $first['Kon_Sdg'] ?? 0,
                    'kon_rgn'      => $first['Kon_Rgn'] ?? 0,
                    'kon_rusak'    => $first['Kon_Rusak'] ?? 0,
                    'kon_mntp'     => $first['Kon_Mntp'] ?? (($first['Kon_Baik'] ?? 0) + ($first['Kon_Sdg'] ?? 0)),
                    'kon_t_mntp'   => $first['Kon_T_Mntp'] ?? (($first['Kon_Rgn'] ?? 0) + ($first['Kon_Rusak'] ?? 0)),
                    'panjang'      => $first['Panjang'] ?? 0,
                    'kecamatan'    => $first['Kecamatan'] ?? null,
                ])->save();

                // update mode wipes old segments for this ruas
                if ($mode === 'update') {
                    Segment::where('ruas_code', $code)->delete();
                }

                // insert all segments for this ruas
                foreach ($features as $f) {
                    $p = $f['properties'];
                    $rawGeometry = json_encode($f['geometry']);
                    $geometry    = app(GeojsonParser::class)->parse($rawGeometry);
                    $rawKondisi = $p['Kondisi'] ?? null;
                    $kondisi = match (strtolower(trim($rawKondisi ?? ''))) {
                        'rusak ringan' => 'rusak_ringan',
                        'rusak berat'  => 'rusak_berat',
                        'baik', 'sedang' => strtolower(trim($rawKondisi)),
                        default        => null,
                    };

                    Segment::create([
                        'ruas_code' => $code,
                        'sta'       => $p['STA']       ?? null,
                        'jens_perm' => $p['Jens_Perm'] ?? null,
                        'kondisi'   => $kondisi,
                        'geometry'  => $geometry,
                    ]);
                }
            }
        });

        return back()->with('success', 'GeoJSON processed.');
    }

    public function destroy(string $code)
    {
        Ruas::where('code', $code)->delete();   // segments auto-cascade
        Cache::forget('segments_geojson');
        return back()->with('success', "Ruas $code deleted.");
    }
    public function create(string $mode)
    {
        // just pass the mode to the Vue page
        return Inertia::render('ruas/GeojsonForm', ['mode' => $mode]);
    }

}


