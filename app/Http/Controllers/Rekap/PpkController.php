<?php

namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use App\Models\PemiluSetting;
use App\Models\RekapCellFlag;
use App\Models\RekapHeader;
use App\Models\Tps;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PpkController extends Controller
{
    // Menampilkan daftar rekap TPS dalam kecamatan PPK.
    public function index(): Response
    {
        $kecamatan = $this->activeKecamatan();
        $tpsIds = Tps::whereHas('desa', fn ($q) => $q->where('kecamatan_id', $kecamatan->id))->pluck('id');
        $totalTps = $tpsIds->count();

        $rekapCounts = RekapHeader::query()
            ->whereIn('tps_id', $tpsIds)
            ->where('status', 'final')
            ->select('jenis', DB::raw('count(distinct tps_id) as total_tps'))
            ->groupBy('jenis')
            ->pluck('total_tps', 'jenis');

        $aktifJenis = PemiluSetting::aktif();

        return Inertia::render('Rekap/Index', [
            'kecamatans' => [
                [
                    'id' => $kecamatan->id,
                    'nama' => $kecamatan->nama,
                ],
            ],
            'totalTps' => $totalTps,
            'rekapCounts' => $rekapCounts,
            'aktifJenis' => $aktifJenis,
            'selectedKecamatanId' => $kecamatan->id,
            'baseRoute' => '/ppk/rekap',
            'pageTitle' => 'Rekapitulasi Suara - Kecamatan '.$kecamatan->nama,
            'pageSubtitle' => 'Monitoring perolehan suara Formulir C-Hasil TPS se-Kecamatan '.$kecamatan->nama,
            'badgeLabel' => 'PPK Kecamatan',
            'isPpk' => true,
        ]);
    }

    // Memastikan jenis pemilihan sedang aktif.
    private function cekAktif(string $jenis): void
    {
        abort_if(! in_array($jenis, PemiluSetting::aktif()), 403, 'Jenis pemilu ini tidak aktif.');
    }

    // Menampilkan rekap per desa dan detail TPS.
    public function show(string $jenis): Response
    {
        $this->cekAktif($jenis);
        $kecamatan = $this->activeKecamatan();
        $detailDesaId = (int) request('detail_desa_id') ?: (int) request('desa_id');

        $kecamatan->load([
            'desas' => fn ($q) => $q->orderBy('nama'),
            'desas.tps' => fn ($q) => $q->orderBy('nama'),
        ]);

        $scopeData = AdminController::getScopeAggregateData(
            $jenis,
            $kecamatan->id,
            $detailDesaId ?: null,
            $kecamatan->dapil_id,
            collect([$kecamatan])
        );

        $candidateOrPartyList = match ($jenis) {
            'ppwp' => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get(['id', 'nomor_urut', 'nama_paslon as nama']),
            'gubernur' => \App\Models\RekapGubernurCalon::orderBy('nomor_urut')->get(['id', 'nomor_urut', 'nama_paslon as nama']),
            'bupati' => \App\Models\RekapBupatiCalon::orderBy('nomor_urut')->get(['id', 'nomor_urut', 'nama_paslon as nama']),
            'dpd' => \App\Models\RekapDpdCalon::orderBy('nomor_urut')->get(['id', 'nomor_urut', 'nama_calon as nama']),
            default => \App\Models\RekapPartai::with('calegs')
                ->where('jenis', $jenis)
                ->when($jenis === 'dprd_kab' && $kecamatan->dapil_id, fn ($q) => $q->where('dapil_id', $kecamatan->dapil_id))
                ->orderBy('nomor_urut')
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'nomor_urut' => $p->nomor_urut,
                    'nama' => $p->nama_partai,
                    'calegs' => $p->calegs->map(fn ($c) => [
                        'id' => $c->id,
                        'partai_id' => $c->partai_id,
                        'nomor_urut' => $c->nomor_urut,
                        'nama' => $c->nama_caleg,
                    ])->values()->all(),
                ])->values()->all(),
        };

        $tpsIds = Tps::whereHas('desa', fn ($q) => $q->where('kecamatan_id', $kecamatan->id))->pluck('id');
        $desaIds = $kecamatan->desas()->pluck('id');
        $flagRows = RekapCellFlag::where('jenis', $jenis)
            ->where(function ($query) use ($tpsIds, $desaIds, $kecamatan) {
                $query->where(function ($query) use ($tpsIds) {
                    $query->where('level', 'tps')
                        ->whereIn('entity_id', $tpsIds);
                })->orWhere(function ($query) use ($desaIds) {
                    $query->where('level', 'desa')
                        ->whereIn('entity_id', $desaIds);
                })->orWhere(function ($query) use ($kecamatan) {
                    $query->where('level', 'kecamatan')
                        ->where('entity_id', $kecamatan->id);
                });
            })
            ->get();
        $tpsCellFlags = collect();
        $cellFlags = collect();
        foreach ($flagRows as $flag) {
            if ($flag->level === 'tps') {
                $tpsCellFlags->put($flag->entity_id.':'.$flag->row_key, true);
                $cellFlags->put($flag->row_key, true);
            } else {
                $cellFlags->put($flag->row_key, true);
            }
        }

        return Inertia::render('Rekap/Show', [
            'kecamatans' => [
                [
                    'id' => $kecamatan->id,
                    'nama' => $kecamatan->nama,
                    'desas' => $kecamatan->desas->map(fn ($d) => ['id' => $d->id, 'nama' => $d->nama]),
                ],
            ],
            'jenis' => $jenis,
            'jenisLabel' => RekapHeader::JENIS_LABELS[$jenis] ?? strtoupper($jenis),
            'scopeLevel' => $scopeData['scopeLevel'],
            'selectedKecamatanId' => $kecamatan->id,
            'selectedDesaId' => $detailDesaId ?: null,
            'columnList' => $scopeData['columnList'],
            'totalTitle' => $scopeData['totalTitle'],
            'masterList' => $candidateOrPartyList,
            'dapils' => [],
            'selectedDapilId' => $kecamatan->dapil_id,
            'scopeStats' => $scopeData['scopeStats'],
            'scopeCalonTotals' => $scopeData['calonTotals'],
            'scopePartaiGrandTotals' => $scopeData['partaiTotals'],
            'scopePartaiOnlyTotals' => $scopeData['partaiOnlyTotals'] ?? [],
            'scopeCalegTotals' => $scopeData['calegTotals'] ?? [],
            'tpsCellFlags' => $tpsCellFlags,
            'cellFlags' => $cellFlags,
            'aktifJenis' => PemiluSetting::aktif(),
            'jenisLabels' => RekapHeader::JENIS_LABELS,
            'baseRoute' => '/ppk/rekap',
            'exportUrl' => route('ppk.rekap.export', $jenis),
            'isPpk' => true,
        ]);
    }

    // Mengekspor rekap kecamatan untuk jenis pemilihan.
    public function export(string $jenis)
    {
        $this->cekAktif($jenis);
        $kecamatan = $this->activeKecamatan();
        $desas = $kecamatan->desas()->with('tps')->get();
        $tpsIds = $desas->flatMap(fn ($d) => $d->tps->pluck('id'));

        $rekaps = RekapHeader::with(['ppwpSuaras', 'gubernurSuaras', 'bupatiSuaras', 'dpdSuaras', 'partaiSuaras', 'calegSuaras'])
            ->whereIn('tps_id', $tpsIds)
            ->where('jenis', $jenis)
            ->get();

        $tpsList = $desas->flatMap(fn ($d) => $d->tps)->values();
        $master = $this->getAllMaster($kecamatan);
        $wilayah = 'Kec. '.$kecamatan->nama;
        $filename = 'Rekap_'.strtoupper($jenis).'_PPK_'.str_replace(' ', '_', $kecamatan->nama).'.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\RekapExport($rekaps, $master, $tpsList, 'ppk', $wilayah, $desas, $jenis),
            $filename
        );
    }

    // Mengambil semua master data untuk kebutuhan export.
    private function getAllMaster(Kecamatan $kecamatan): array
    {
        return [
            'ppwp' => ['calons' => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get()],
            'gubernur' => ['calons' => \App\Models\RekapGubernurCalon::orderBy('nomor_urut')->get()],
            'bupati' => ['calons' => \App\Models\RekapBupatiCalon::orderBy('nomor_urut')->get()],
            'dpd' => ['calons' => \App\Models\RekapDpdCalon::orderBy('nomor_urut')->get()],
            'dpr_ri' => ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis', 'dpr_ri')->orderBy('nomor_urut')->get()],
            'dprd_prov' => ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis', 'dprd_prov')->orderBy('nomor_urut')->get()],
            'dprd_kab' => ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis', 'dprd_kab')->where('dapil_id', $kecamatan->dapil_id)->orderBy('nomor_urut')->get()],
        ];
    }

    private function activeKecamatan(): Kecamatan
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            abort_if(! session('admin_view_kecamatan_id'), 403, 'Pilih kecamatan yang ingin dilihat.');

            return Kecamatan::findOrFail(session('admin_view_kecamatan_id'));
        }

        abort_if(! $user->kecamatan_id, 403, 'Akun belum di-assign ke Kecamatan.');

        return Kecamatan::findOrFail($user->kecamatan_id);
    }
}
