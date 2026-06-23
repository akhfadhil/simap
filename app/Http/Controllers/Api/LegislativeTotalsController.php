<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LegislativeTotalsController extends Controller
{
    public function index(Request $request)
    {
        $scopeType = $request->query('scope_type');
        $scopeId = $request->query('scope_id');

        // Base query to get total caleg votes
        $baseQuery = DB::table('rekap_caleg_suaras as s')
            ->join('rekap_headers as h', 'h.id', '=', 's.rekap_id')
            ->join('tps as t', 't.id', '=', 'h.tps_id')
            ->join('desas as d', 'd.id', '=', 't.desa_id')
            ->join('kecamatans as k', 'k.id', '=', 'd.kecamatan_id')
            ->join('rekap_calegs as c', 'c.id', '=', 's.caleg_id')
            ->join('rekap_partais as p', 'p.id', '=', 'c.partai_id');

        // Apply geographic scope filter if provided
        if ($scopeType === 'kecamatan' && $scopeId) {
            $baseQuery->where('k.id', $scopeId);
        } elseif ($scopeType === 'desa' && $scopeId) {
            $baseQuery->where('d.id', $scopeId);
        } elseif ($scopeType === 'tps' && $scopeId) {
            $baseQuery->where('t.id', $scopeId);
        }

        // Sum for non-dprd_kab (DPR RI & DPRD Prov)
        $nonKabTotals = (clone $baseQuery)
            ->whereIn('h.jenis', ['dpr_ri', 'dprd_prov'])
            ->select('h.jenis', DB::raw('SUM(s.suara) as total_suara'))
            ->groupBy('h.jenis')
            ->pluck('total_suara', 'jenis');

        // Sum for dprd_kab, grouped by dapil_id
        $kabTotals = (clone $baseQuery)
            ->where('h.jenis', 'dprd_kab')
            ->select('p.dapil_id', DB::raw('SUM(s.suara) as total_suara'))
            ->groupBy('p.dapil_id')
            ->pluck('total_suara', 'dapil_id');

        return response()->json([
            'dpr_ri' => (int) ($nonKabTotals['dpr_ri'] ?? 0),
            'dprd_prov' => (int) ($nonKabTotals['dprd_prov'] ?? 0),
            'dprd_kab' => collect($kabTotals)->map(fn($v) => (int) $v)->toArray(),
        ]);
    }
}
