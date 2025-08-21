<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Ruas;
use Illuminate\Support\Facades\DB;


class StatistikController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rows = Ruas::select(
            'kon_baik', 'kon_sdg', 'kon_rgn', 'kon_rusak',
            DB::raw('(kon_baik + kon_sdg)  AS kon_mntp'),
            DB::raw('(kon_rgn  + kon_rusak) AS kon_t_mntp')
        )->get();

        return Inertia::render('Statistik', [
            'totals' => [
                'baik'   => $rows->sum('kon_baik'),
                'sedang' => $rows->sum('kon_sdg'),
                'rgn'    => $rows->sum('kon_rgn'),
                'rusak'  => $rows->sum('kon_rusak'),
                'mntp'   => $rows->sum('kon_mntp'),
                't_mntp' => $rows->sum('kon_t_mntp'),
                'ruas'   => (int)   Ruas::count(),
                'panjang'=> (float) Ruas::sum('panjang'),
            ],
        ]);

    }

}
