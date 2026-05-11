<?php
namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use App\Models\RekapHeader;
use App\Exports\RekapExport;
use Maatwebsite\Excel\Facades\Excel;
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

    private function cekAktif(string $jenis): void
    {
        abort_if(!in_array($jenis, \App\Models\PemiluSetting::aktif()), 403, 'Jenis pemilu ini tidak aktif.');
    }

    public function show(string $jenis)
    {
        $this->cekAktif($jenis);
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

    public function export(string $jenis)
    {
        $this->cekAktif($jenis);
        $desa   = Auth::user()->desa;
        $tpsIds = $desa->tps->pluck('id');

        $rekaps = RekapHeader::with(['ppwpSuaras','dpdSuaras','partaiSuaras','calegSuaras'])
                            ->whereIn('tps_id', $tpsIds)
                            ->where('jenis', $jenis)  // filter jenis!
                            ->get();

        $tpsList = $desa->tps;
        $master  = $this->getAllMaster();
        $label   = RekapHeader::JENIS_LABELS[$jenis];
        $wilayah = $desa->nama . ' — ' . $desa->kecamatan->nama;
        $filename = 'Rekap_' . strtoupper($jenis) . '_' . str_replace(' ', '_', $desa->nama) . '.xlsx';

        $sheet = new \App\Exports\RekapSheetExport(
            $jenis,
            $label,
            $rekaps,
            $master,
            $tpsList,
            'pps',
            $wilayah
        );

        return \Maatwebsite\Excel\Facades\Excel::download($sheet, $filename);
    }

    private function getMaster(string $jenis): array
    {
        if ($jenis === 'ppwp') return ['calons' => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get()];
        if ($jenis === 'dpd')  return ['calons' => \App\Models\RekapDpdCalon::orderBy('nomor_urut')->get()];
        return ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis',$jenis)->orderBy('nomor_urut')->get()];
    }

    private function getAllMaster(): array
    {
        return [
            'ppwp'     => ['calons' => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get()],
            'dpd'      => ['calons' => \App\Models\RekapDpdCalon::orderBy('nomor_urut')->get()],
            'dpr_ri'   => ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis','dpr_ri')->orderBy('nomor_urut')->get()],
            'dprd_prov'=> ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis','dprd_prov')->orderBy('nomor_urut')->get()],
            'dprd_kab' => ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis','dprd_kab')->orderBy('nomor_urut')->get()],
        ];
    }
}