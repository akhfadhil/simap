<?php
namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use App\Models\RekapHeader;
use Illuminate\Support\Facades\Auth;

class PpsController extends Controller
{
    public function index()
    {
        $desa    = Auth::user()->desa;
        $tpsIds  = $desa->tps->pluck('id');
        $rekaps  = RekapHeader::whereIn('tps_id', $tpsIds)->get()
                              ->groupBy('jenis');
        return view('rekap.pps.index', compact('desa', 'rekaps'));
    }

    public function show(string $jenis)
    {
        $desa   = Auth::user()->desa;
        $tpsIds = $desa->tps->pluck('id');
        $rekaps = RekapHeader::with(['tps','ppwpSuaras.calon','dpdSuaras.calon','partaiSuaras.partai','calegSuaras.caleg'])
                             ->whereIn('tps_id', $tpsIds)
                             ->where('jenis', $jenis)
                             ->get()->keyBy('tps_id');
        $tpsList = $desa->tps;
        $master  = $this->getMaster($jenis);
        return view('rekap.pps.show', compact('desa', 'jenis', 'rekaps', 'tpsList', 'master'));
    }

    private function getMaster(string $jenis): array
    {
        if ($jenis === 'ppwp') return ['calons' => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get()];
        if ($jenis === 'dpd')  return ['calons' => \App\Models\RekapDpdCalon::orderBy('nomor_urut')->get()];
        return ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis',$jenis)->orderBy('nomor_urut')->get()];
    }
}