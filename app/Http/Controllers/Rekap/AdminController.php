<?php
namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use App\Models\RekapHeader;
use App\Models\Kecamatan;
use App\Models\Tps;
use App\Exports\RekapExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

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
        $kecamatans = Kecamatan::with(['desas.tps'])->orderBy('nama')->get();
        $tpsIds     = Tps::pluck('id');
        $rekaps     = RekapHeader::with(['ppwpSuaras.calon','dpdSuaras.calon','partaiSuaras.partai','calegSuaras.caleg'])
                                ->whereIn('tps_id', $tpsIds)
                                ->where('jenis', $jenis)
                                ->get()->keyBy('tps_id');
        $master     = $this->getMaster($jenis);
        return view('rekap.admin.show', compact('kecamatans', 'jenis', 'rekaps', 'master'));
    }

    public function export(string $jenis)
    {
        $kecId  = request('kecamatan_id');
        $desas  = \App\Models\Desa::with('tps')
                    ->when($kecId, fn($q) => $q->where('kecamatan_id', $kecId))
                    ->get();

        $tpsIds = $desas->flatMap(fn($d) => $d->tps->pluck('id'));

        $rekaps  = \App\Models\RekapHeader::with(['ppwpSuaras','dpdSuaras','partaiSuaras','calegSuaras'])
                    ->whereIn('tps_id', $tpsIds)
                    ->where('jenis', $jenis)
                    ->get();

        $tpsList = $desas->flatMap(fn($d) => $d->tps)->values();
        $master  = $this->getAllMaster();
        $masterJenis = $master[$jenis] ?? [];

        $wilayah  = $kecId
            ? 'Kec. ' . \App\Models\Kecamatan::find($kecId)?->nama
            : 'Semua Kecamatan';

        $suffix   = $kecId ? '_Kec_' . $kecId : '_Semua';
        $filename = 'Rekap_' . strtoupper($jenis) . '_Admin' . $suffix . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\RekapExport($rekaps, $master, $tpsList, 'admin', $wilayah, $desas, $jenis),
            $filename
        );
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
            'ppwp'      => ['calons'  => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get()],
            'dpd'       => ['calons'  => \App\Models\RekapDpdCalon::orderBy('nomor_urut')->get()],
            'dpr_ri'    => ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis','dpr_ri')->orderBy('nomor_urut')->get()],
            'dprd_prov' => ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis','dprd_prov')->orderBy('nomor_urut')->get()],
            'dprd_kab'  => ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis','dprd_kab')->orderBy('nomor_urut')->get()],
        ];
    }

    public function exportPage()
    {
        $kecamatans = \App\Models\Kecamatan::orderBy('nama')->get();
        return view('rekap.admin.export', compact('kecamatans'));
    }

    public function exportDownload(Request $request)
    {
        $request->validate([
            'jenis' => 'required|in:ppwp,dpd,dpr_ri,dprd_prov,dprd_kab',
            'level' => 'required|in:tps,desa,kecamatan,kabupaten',
        ]);

        $jenis = $request->jenis;
        $level = $request->level;
        $label = \App\Models\RekapHeader::JENIS_LABELS[$jenis];

        switch ($level) {
            case 'tps':
                $tps     = \App\Models\Tps::with('desa.kecamatan')->findOrFail($request->tps_id);
                $rekaps  = \App\Models\RekapHeader::with(['ppwpSuaras','dpdSuaras','partaiSuaras','calegSuaras'])
                            ->where('tps_id', $tps->id)->where('jenis', $jenis)->get();
                $tpsList = collect([$tps]);
                $master  = $this->getAllMaster();
                $wilayah = $tps->nama . ' — ' . $tps->desa->nama;
                $filename = 'Rekap_' . strtoupper($jenis) . '_' . str_replace(' ', '_', $tps->nama) . '.xlsx';
                $sheet = new \App\Exports\RekapSheetExport($jenis, $label, $rekaps, $master, $tpsList, 'kpps', $wilayah);
                return \Maatwebsite\Excel\Facades\Excel::download($sheet, $filename);

            case 'desa':
                $desa    = \App\Models\Desa::with('tps', 'kecamatan')->findOrFail($request->desa_id);
                $tpsIds  = $desa->tps->pluck('id');
                $rekaps  = \App\Models\RekapHeader::with(['ppwpSuaras','dpdSuaras','partaiSuaras','calegSuaras'])
                            ->whereIn('tps_id', $tpsIds)->where('jenis', $jenis)->get();
                $tpsList = $desa->tps;
                $master  = $this->getAllMaster();
                $wilayah = $desa->nama . ' — Kec. ' . $desa->kecamatan->nama;
                $filename = 'Rekap_' . strtoupper($jenis) . '_' . str_replace(' ', '_', $desa->nama) . '.xlsx';
                $sheet = new \App\Exports\RekapSheetExport($jenis, $label, $rekaps, $master, $tpsList, 'pps', $wilayah);
                return \Maatwebsite\Excel\Facades\Excel::download($sheet, $filename);

            case 'kecamatan':
                $kecamatan = \App\Models\Kecamatan::findOrFail($request->kecamatan_id);
                $desas     = \App\Models\Desa::with('tps')->where('kecamatan_id', $kecamatan->id)->get();
                $tpsIds    = $desas->flatMap(fn($d) => $d->tps->pluck('id'));
                $rekaps    = \App\Models\RekapHeader::with(['ppwpSuaras','dpdSuaras','partaiSuaras','calegSuaras'])
                            ->whereIn('tps_id', $tpsIds)->where('jenis', $jenis)->get();
                $tpsList   = $desas->flatMap(fn($d) => $d->tps)->values();
                $master    = $this->getAllMaster();
                $wilayah   = 'Kec. ' . $kecamatan->nama;
                $filename  = 'Rekap_' . strtoupper($jenis) . '_Kec_' . str_replace(' ', '_', $kecamatan->nama) . '.xlsx';
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\RekapExport($rekaps, $master, $tpsList, 'ppk', $wilayah, $desas, $jenis),
                    $filename
                );

            case 'kabupaten':
                $desas   = \App\Models\Desa::with('tps')->get();
                $tpsIds  = $desas->flatMap(fn($d) => $d->tps->pluck('id'));
                $rekaps  = \App\Models\RekapHeader::with(['ppwpSuaras','dpdSuaras','partaiSuaras','calegSuaras'])
                            ->whereIn('tps_id', $tpsIds)->where('jenis', $jenis)->get();
                $tpsList = $desas->flatMap(fn($d) => $d->tps)->values();
                $master  = $this->getAllMaster();
                $wilayah = 'Kabupaten';
                $filename = 'Rekap_' . strtoupper($jenis) . '_Kabupaten.xlsx';

                // Untuk kabupaten, group desas per kecamatan sebagai "desas" di RekapTotalSheet
                $kecamatans = \App\Models\Kecamatan::with('desas.tps')->get();
                // Buat pseudo-desas yang merepresentasikan kecamatan
                $pseudoDesas = $kecamatans->map(function($kec) {
                    $kec->nama = $kec->nama; // pakai nama kecamatan
                    $kec->tps  = $kec->desas->flatMap(fn($d) => $d->tps);
                    return $kec;
                });
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\RekapExport($rekaps, $master, $tpsList, 'admin', $wilayah, $pseudoDesas, $jenis),
                    $filename
                );
        }
    }
}