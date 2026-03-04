<?php
namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use App\Models\RekapHeader;
use App\Models\Tps;
use Illuminate\Support\Facades\Auth;

class PpkController extends Controller
{
    public function index()
    {
        $kecamatan = Auth::user()->kecamatan;
        $tpsIds    = Tps::whereHas('desa', fn($q) => $q->where('kecamatan_id', $kecamatan->id))->pluck('id');
        $rekaps    = RekapHeader::whereIn('tps_id', $tpsIds)->get()->groupBy('jenis');
        return view('rekap.ppk.index', compact('kecamatan', 'rekaps'));
    }

    public function show(string $jenis)
    {
        $kecamatan = Auth::user()->kecamatan;
        $tpsIds    = Tps::whereHas('desa', fn($q) => $q->where('kecamatan_id', $kecamatan->id))->pluck('id');
        $rekaps    = RekapHeader::with(['tps.desa','ppwpSuaras.calon','dpdSuaras.calon','partaiSuaras.partai','calegSuaras.caleg'])
                                ->whereIn('tps_id', $tpsIds)
                                ->where('jenis', $jenis)
                                ->get()->keyBy('tps_id');
        $desas   = $kecamatan->desas()->with('tps')->get();
        $master  = $this->getMaster($jenis);
        return view('rekap.ppk.show', compact('kecamatan', 'jenis', 'rekaps', 'desas', 'master'));
    }

    private function getMaster(string $jenis): array
    {
        if ($jenis === 'ppwp') return ['calons' => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get()];
        if ($jenis === 'dpd')  return ['calons' => \App\Models\RekapDpdCalon::orderBy('nomor_urut')->get()];
        return ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis',$jenis)->orderBy('nomor_urut')->get()];
    }
}