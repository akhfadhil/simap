<?php
namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use App\Models\RekapHeader;
use App\Models\Tps;
use Illuminate\Support\Facades\Auth;
use App\Exports\RekapExport;
use Maatwebsite\Excel\Facades\Excel;

class PpkController extends Controller
{
    public function index()
    {
        $kecamatan = Auth::user()->kecamatan;
        $tpsIds    = Tps::whereHas('desa', fn($q) => $q->where('kecamatan_id', $kecamatan->id))->pluck('id');
        $rekaps    = RekapHeader::whereIn('tps_id', $tpsIds)->get()->groupBy('jenis');
        return view('rekap.ppk.index', compact('kecamatan', 'rekaps'));
    }

    private function cekAktif(string $jenis): void
    {
        abort_if(!in_array($jenis, \App\Models\PemiluSetting::aktif()), 403, 'Jenis pemilu ini tidak aktif.');
    }

    public function show(string $jenis)
    {
        $this->cekAktif($jenis);
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

    public function export(string $jenis)
    {
        $this->cekAktif($jenis);
        $kecamatan = Auth::user()->kecamatan;
        $desas     = $kecamatan->desas()->with('tps')->get();
        $tpsIds    = $desas->flatMap(fn($d) => $d->tps->pluck('id'));

        $rekaps  = RekapHeader::with(['ppwpSuaras','dpdSuaras','partaiSuaras','calegSuaras'])
                            ->whereIn('tps_id', $tpsIds)
                            ->where('jenis', $jenis)
                            ->get();

        $tpsList = $desas->flatMap(fn($d) => $d->tps)->values();
        $master  = $this->getAllMaster();
        $label   = \App\Models\RekapHeader::JENIS_LABELS[$jenis];
        $wilayah = 'Kec. ' . $kecamatan->nama;
        $filename = 'Rekap_' . strtoupper($jenis) . '_PPK_' . str_replace(' ', '_', $kecamatan->nama) . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\RekapExport($rekaps, $master, $tpsList, 'ppk', $wilayah, $desas, $jenis),
            $filename
        );
    }

    private function getMaster(string $jenis): array
    {
        if ($jenis === 'ppwp') return ['calons' => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get()];
        if ($jenis === 'gubernur') return ['calons' => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get()];
        if ($jenis === 'bupati')   return ['calons' => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get()];
        if ($jenis === 'dpd')  return ['calons' => \App\Models\RekapDpdCalon::orderBy('nomor_urut')->get()];
        return ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis',$jenis)->orderBy('nomor_urut')->get()];
    }

    private function getAllMaster(): array
    {
        return [
            'ppwp'      => ['calons'  => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get()],
            'dpd'       => ['calons'  => \App\Models\RekapDpdCalon::orderBy('nomor_urut')->get()],
            'dpr_ri'    => ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis','dpr_ri')->orderBy('nomor_urut')->get()],
            'dprd_prov' => ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis','dprd_prov')->orderBy('nomor_urut')->get()],
            'dprd_kab'  => ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis','dprd_kab')->orderBy('nomor_urut')->get()],
        ];
    }
}