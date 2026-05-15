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
        $rekaps     = RekapHeader::with([
                                    'ppwpSuaras.calon',
                                    'gubernurSuaras.calon',   // ← tambah ini
                                    'bupatiSuaras.calon',     // ← tambah ini
                                    'dpdSuaras.calon',
                                    'partaiSuaras.partai',
                                    'calegSuaras.caleg'
                                ])
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
        if ($jenis === 'ppwp')     return ['calons' => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get()];
        if ($jenis === 'gubernur') return ['calons' => \App\Models\RekapGubernurCalon::orderBy('nomor_urut')->get()];
        if ($jenis === 'bupati')   return ['calons' => \App\Models\RekapBupatiCalon::orderBy('nomor_urut')->get()];
        if ($jenis === 'dpd')      return ['calons' => \App\Models\RekapDpdCalon::orderBy('nomor_urut')->get()];
        return ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis',$jenis)->orderBy('nomor_urut')->get()];
    }

    private function getAllMaster(): array
    {
        return [
            'ppwp'      => ['calons'  => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get()],
            'gubernur'  => ['calons'  => \App\Models\RekapGubernurCalon::orderBy('nomor_urut')->get()],
            'bupati'    => ['calons'  => \App\Models\RekapBupatiCalon::orderBy('nomor_urut')->get()],
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
            // 'jenis' => 'required|in:ppwp,dpd,dpr_ri,dprd_prov,dprd_kab',
            // Di validasi jenis di KppsController
            'jenis' => 'required|in:ppwp,gubernur,bupati,dpd,dpr_ri,dprd_prov,dprd_kab',
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

    public function chartPage()
    {
        $kecamatans = Kecamatan::with(['desas.tps'])->orderBy('nama')->get();
        $dapils     = \App\Models\Dapil::with('kecamatans')->orderBy('nama')->get();
        return view('rekap.admin.chart', compact('kecamatans', 'dapils'));
    }

    public function chartData(\Illuminate\Http\Request $request)
    {
        $jenis   = $request->jenis;
        $level   = $request->level ?? 'kabupaten';
        $kecId   = $request->kecamatan_id;
        $desaId  = $request->desa_id;
        $tpsId   = $request->tps_id;
        $dapilId = $request->dapil_id;

        // Tentukan scope TPS
        $tpsQuery = Tps::query();
        if ($tpsId)       $tpsQuery->where('id', $tpsId);
        elseif ($desaId)  $tpsQuery->where('desa_id', $desaId);
        elseif ($kecId)   $tpsQuery->whereHas('desa', fn($q) => $q->where('kecamatan_id', $kecId));
        elseif ($dapilId) $tpsQuery->whereHas('desa.kecamatan', fn($q) => $q->where('dapil_id', $dapilId));
        $tpsIds = $tpsQuery->pluck('id');

        $rekaps = \App\Models\RekapHeader::with(['ppwpSuaras.calon','dpdSuaras.calon','partaiSuaras','calegSuaras'])
                    ->whereIn('tps_id', $tpsIds)
                    ->where('jenis', $jenis)
                    ->get();

        // Tentukan label & grouping
        $data = [];

        if ($level === 'kabupaten') {
            // Per kecamatan
            $kecamatans = Kecamatan::with(['desas.tps'])->orderBy('nama')->get();
            foreach ($kecamatans as $kec) {
                $kecTpsIds = $kec->desas->flatMap(fn($d) => $d->tps->pluck('id'))->toArray();
                $data[] = [
                    'label'       => $kec->nama,
                    'suara'       => $this->buildSuaraData($rekaps->whereIn('tps_id', $kecTpsIds), $jenis),
                    'partisipasi' => $this->buildPartisipasiData($rekaps->whereIn('tps_id', $kecTpsIds)),
                ];
            }
        } elseif ($level === 'dapil' && $dapilId) {
            // Per kecamatan dalam dapil tsb
            $kecamatans = Kecamatan::with(['desas.tps'])->where('dapil_id', $dapilId)->orderBy('nama')->get();
            foreach ($kecamatans as $kec) {
                $kecTpsIds = $kec->desas->flatMap(fn($d) => $d->tps->pluck('id'))->toArray();
                $data[] = [
                    'label'       => $kec->nama,
                    'suara'       => $this->buildSuaraData($rekaps->whereIn('tps_id', $kecTpsIds), $jenis),
                    'partisipasi' => $this->buildPartisipasiData($rekaps->whereIn('tps_id', $kecTpsIds)),
                ];
            }
        } elseif ($level === 'kecamatan' && $kecId) {
            // Per desa
            $desas = \App\Models\Desa::where('kecamatan_id', $kecId)->with('tps')->orderBy('nama')->get();
            foreach ($desas as $desa) {
                $desaTpsIds = $desa->tps->pluck('id')->toArray();
                $data[] = [
                    'label'       => $desa->nama,
                    'suara'       => $this->buildSuaraData($rekaps->whereIn('tps_id', $desaTpsIds), $jenis),
                    'partisipasi' => $this->buildPartisipasiData($rekaps->whereIn('tps_id', $desaTpsIds)),
                ];
            }
        } elseif ($level === 'desa' && $desaId) {
            // Per TPS
            $tpsList = \App\Models\Tps::where('desa_id', $desaId)->orderBy('nama')->get();
            foreach ($tpsList as $tps) {
                $r = $rekaps->where('tps_id', $tps->id)->first();
                $data[] = [
                    'label'       => $tps->nama,
                    'suara'       => $this->buildSuaraData($r ? collect([$r]) : collect(), $jenis),
                    'partisipasi' => $this->buildPartisipasiData($r ? collect([$r]) : collect()),
                ];
            }
        } elseif ($level === 'tps' && $tpsId) {
            // Single TPS
            $tps = Tps::find($tpsId);
            $r   = $rekaps->where('tps_id', $tpsId)->first();
            $data[] = [
                'label'       => $tps->nama,
                'suara'       => $this->buildSuaraData($r ? collect([$r]) : collect(), $jenis),
                'partisipasi' => $this->buildPartisipasiData($r ? collect([$r]) : collect()),
            ];
        }

        // Master labels
        $master = $this->getMaster($jenis);
        $labels = [];
        if (in_array($jenis, ['ppwp','dpd','gubernur','bupati'])) {
            $labels = $master['calons']->map(fn($c) => in_array($jenis, ['ppwp','gubernur','bupati']) ? $c->nama_paslon : $c->nama_calon)->toArray();
        } else {
            $labels = $master['partais']->map(fn($p) => $p->nama_partai)->toArray();
        }

        return response()->json([
            'type'   => in_array($jenis, ['ppwp','dpd']) ? 'pie' : 'bar',
            'jenis'  => $jenis,
            'labels' => $labels,
            'data'   => $data,
        ]);
    }

    private function buildSuaraData($rekaps, string $jenis): array
    {
        if (in_array($jenis, ['ppwp','dpd','gubernur','bupati'])) {
            $master = $this->getMaster($jenis);
            return $master['calons']->map(function($calon) use ($rekaps, $jenis) {
                return $rekaps->sum(fn($r) => match($jenis) {
                    'ppwp'     => $r->ppwpSuaras->firstWhere('calon_id', $calon->id)?->suara ?? 0,
                    'gubernur' => $r->gubernurSuaras->firstWhere('calon_id', $calon->id)?->suara ?? 0,
                    'bupati'   => $r->bupatiSuaras->firstWhere('calon_id', $calon->id)?->suara ?? 0,
                    'dpd'      => $r->dpdSuaras->firstWhere('calon_id', $calon->id)?->suara ?? 0,
                });
            })->toArray();
        } else {
            $master = $this->getMaster($jenis);
            return $master['partais']->map(function($partai) use ($rekaps) {
                return $rekaps->sum(fn($r) =>
                    ($r->partaiSuaras->firstWhere('partai_id', $partai->id)?->suara ?? 0) +
                    $r->calegSuaras->whereIn('caleg_id', $partai->calegs->pluck('id'))->sum('suara')
                );
            })->toArray();
        }
    }

    private function buildPartisipasiData($rekaps): array
    {
        return [
            'dpt'   => $rekaps->sum(fn($r) => ($r->dpt_lk ?? 0) + ($r->dpt_pr ?? 0)),
            'hadir' => $rekaps->sum(fn($r) => ($r->pengguna_dpt_lk ?? 0) + ($r->pengguna_dpt_pr ?? 0) +
                                            ($r->pengguna_dptb_lk ?? 0) + ($r->pengguna_dptb_pr ?? 0) +
                                            ($r->pengguna_dpk_lk ?? 0) + ($r->pengguna_dpk_pr ?? 0)),
        ];
    }

    public function unlock(Request $request, string $jenis)
    {
        $rekap = RekapHeader::where('tps_id', $request->tps_id)
                            ->where('jenis', $jenis)
                            ->firstOrFail();

        $rekap->update([
            'status'          => 'draft',
            'difinalisasi_at' => null,
        ]);

        return back()->with('success', 'Rekap ' . $rekap->tps->nama . ' berhasil dibuka kembali.');
    }
}