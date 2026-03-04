<?php
namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use App\Models\RekapHeader;
use App\Models\Kecamatan;
use App\Models\Tps;

class AdminController extends Controller
{
    public function index()
    {
        $kecamatans = Kecamatan::all();
        $kecId      = request('kecamatan_id');
        $tpsIds     = Tps::when($kecId, fn($q) => $q->whereHas('desa', fn($q2) => $q2->where('kecamatan_id', $kecId)))->pluck('id');
        $rekaps     = RekapHeader::whereIn('tps_id', $tpsIds)->get()->groupBy('jenis');
        return view('rekap.admin.index', compact('kecamatans', 'rekaps'));
    }

    public function show(string $jenis)
    {
        $kecamatans = Kecamatan::all();
        $kecId      = request('kecamatan_id');
        $tpsIds     = Tps::when($kecId, fn($q) => $q->whereHas('desa', fn($q2) => $q2->where('kecamatan_id', $kecId)))->pluck('id');
        $rekaps     = RekapHeader::with(['tps.desa.kecamatan','ppwpSuaras.calon','dpdSuaras.calon','partaiSuaras.partai','calegSuaras.caleg'])
                                 ->whereIn('tps_id', $tpsIds)
                                 ->where('jenis', $jenis)
                                 ->get()->keyBy('tps_id');
        $master     = $this->getMaster($jenis);
        return view('rekap.admin.show', compact('kecamatans', 'jenis', 'rekaps', 'master'));
    }

    private function getMaster(string $jenis): array
    {
        if ($jenis === 'ppwp') return ['calons' => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get()];
        if ($jenis === 'dpd')  return ['calons' => \App\Models\RekapDpdCalon::orderBy('nomor_urut')->get()];
        return ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis',$jenis)->orderBy('nomor_urut')->get()];
    }
}