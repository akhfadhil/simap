<?php

namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\PemiluSetting;
use App\Models\RekapCellFlag;
use App\Models\RekapHeader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PpsController extends Controller
{
    // Menampilkan daftar rekap TPS dalam desa PPS.
    public function index(): Response
    {
        $desa = $this->activeDesa();
        $tpsIds = $desa->tps->pluck('id');
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
                    'id' => $desa->kecamatan_id,
                    'nama' => $desa->kecamatan?->nama ?? 'Kecamatan',
                    'desas' => [
                        [
                            'id' => $desa->id,
                            'nama' => $desa->nama,
                        ],
                    ],
                ],
            ],
            'totalTps' => $totalTps,
            'rekapCounts' => $rekapCounts,
            'aktifJenis' => $aktifJenis,
            'selectedKecamatanId' => $desa->kecamatan_id,
            'selectedDesaId' => $desa->id,
            'baseRoute' => '/pps/rekap',
            'pageTitle' => 'Rekapitulasi Suara - Desa '.$desa->nama,
            'pageSubtitle' => 'Monitoring perolehan suara Formulir C-Hasil seluruh TPS di Desa '.$desa->nama.' (Kec. '.($desa->kecamatan?->nama ?? '').')',
            'badgeLabel' => 'PPS Desa',
            'isPps' => true,
        ]);
    }

    // Memastikan jenis pemilihan sedang aktif.
    private function cekAktif(string $jenis): void
    {
        abort_if(! in_array($jenis, PemiluSetting::aktif()), 403, 'Jenis pemilu ini tidak aktif.');
    }

    // Menampilkan rekap per TPS untuk jenis pemilihan.
    public function show(string $jenis): Response
    {
        $this->cekAktif($jenis);
        $desa = $this->activeDesa();
        $kecamatan = $desa->kecamatan;

        $scopeData = AdminController::getScopeAggregateData(
            $jenis,
            $desa->kecamatan_id,
            $desa->id,
            $kecamatan?->dapil_id,
            collect([$kecamatan])
        );

        $candidateOrPartyList = match ($jenis) {
            'ppwp' => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get(['id', 'nomor_urut', 'nama_paslon as nama']),
            'gubernur' => \App\Models\RekapGubernurCalon::orderBy('nomor_urut')->get(['id', 'nomor_urut', 'nama_paslon as nama']),
            'bupati' => \App\Models\RekapBupatiCalon::orderBy('nomor_urut')->get(['id', 'nomor_urut', 'nama_paslon as nama']),
            'dpd' => \App\Models\RekapDpdCalon::orderBy('nomor_urut')->get(['id', 'nomor_urut', 'nama_calon as nama']),
            default => \App\Models\RekapPartai::with('calegs')
                ->where('jenis', $jenis)
                ->when($jenis === 'dprd_kab' && $kecamatan?->dapil_id, fn ($q) => $q->where('dapil_id', $kecamatan->dapil_id))
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

        $tpsIds = $desa->tps->pluck('id');
        $flagRows = RekapCellFlag::where('jenis', $jenis)
            ->where(function ($query) use ($tpsIds, $desa) {
                $query->where(function ($query) use ($tpsIds) {
                    $query->where('level', 'tps')
                        ->whereIn('entity_id', $tpsIds);
                })->orWhere(function ($query) use ($desa) {
                    $query->where('level', 'desa')
                        ->where('entity_id', $desa->id);
                })->orWhere(function ($query) use ($desa) {
                    $query->where('level', 'kecamatan')
                        ->where('entity_id', $desa->kecamatan_id);
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
                    'id' => $desa->kecamatan_id,
                    'nama' => $kecamatan?->nama ?? 'Kecamatan',
                    'desas' => [
                        ['id' => $desa->id, 'nama' => $desa->nama],
                    ],
                ],
            ],
            'jenis' => $jenis,
            'jenisLabel' => RekapHeader::JENIS_LABELS[$jenis] ?? strtoupper($jenis),
            'scopeLevel' => 'desa',
            'selectedKecamatanId' => $desa->kecamatan_id,
            'selectedDesaId' => $desa->id,
            'columnList' => $scopeData['columnList'],
            'totalTitle' => $scopeData['totalTitle'],
            'masterList' => $candidateOrPartyList,
            'dapils' => [],
            'selectedDapilId' => $kecamatan?->dapil_id,
            'scopeStats' => $scopeData['scopeStats'],
            'scopeCalonTotals' => $scopeData['calonTotals'],
            'scopePartaiGrandTotals' => $scopeData['partaiTotals'],
            'scopePartaiOnlyTotals' => $scopeData['partaiOnlyTotals'] ?? [],
            'scopeCalegTotals' => $scopeData['calegTotals'] ?? [],
            'tpsCellFlags' => $tpsCellFlags,
            'cellFlags' => $cellFlags,
            'aktifJenis' => PemiluSetting::aktif(),
            'jenisLabels' => RekapHeader::JENIS_LABELS,
            'baseRoute' => '/pps/rekap',
            'exportUrl' => route('pps.rekap.export', $jenis),
            'isPps' => true,
        ]);
    }

    // Mengekspor rekap desa untuk jenis pemilihan.
    public function export(string $jenis)
    {
        $this->cekAktif($jenis);
        $desa = $this->activeDesa();
        $tpsIds = $desa->tps->pluck('id');

        $rekaps = RekapHeader::with(['ppwpSuaras', 'gubernurSuaras', 'bupatiSuaras', 'dpdSuaras', 'partaiSuaras', 'calegSuaras'])
            ->whereIn('tps_id', $tpsIds)
            ->where('jenis', $jenis)
            ->get();

        $tpsList = $desa->tps;
        $master = $this->getAllMaster($desa);
        $label = RekapHeader::JENIS_LABELS[$jenis];
        $wilayah = $desa->nama.' — '.$desa->kecamatan->nama;
        $filename = 'Rekap_'.strtoupper($jenis).'_'.str_replace(' ', '_', $desa->nama).'.xlsx';

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

    // Mengambil master data sesuai jenis pemilihan.
    private function getMaster(string $jenis, Desa $desa): array
    {
        if ($jenis === 'ppwp') {
            return ['calons' => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get()];
        }
        if ($jenis === 'gubernur') {
            return ['calons' => \App\Models\RekapGubernurCalon::orderBy('nomor_urut')->get()];
        }
        if ($jenis === 'bupati') {
            return ['calons' => \App\Models\RekapBupatiCalon::orderBy('nomor_urut')->get()];
        }
        if ($jenis === 'dpd') {
            return ['calons' => \App\Models\RekapDpdCalon::orderBy('nomor_urut')->get()];
        }
        $partais = \App\Models\RekapPartai::with('calegs')->where('jenis', $jenis);

        if ($jenis === 'dprd_kab') {
            $partais->where('dapil_id', $desa->kecamatan?->dapil_id);
        }

        return ['partais' => $partais->orderBy('nomor_urut')->get()];
    }

    // Mengambil semua master data untuk kebutuhan export.
    private function getAllMaster(Desa $desa): array
    {
        return [
            'ppwp' => ['calons' => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get()],
            'gubernur' => ['calons' => \App\Models\RekapGubernurCalon::orderBy('nomor_urut')->get()],
            'bupati' => ['calons' => \App\Models\RekapBupatiCalon::orderBy('nomor_urut')->get()],
            'dpd' => ['calons' => \App\Models\RekapDpdCalon::orderBy('nomor_urut')->get()],
            'dpr_ri' => ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis', 'dpr_ri')->orderBy('nomor_urut')->get()],
            'dprd_prov' => ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis', 'dprd_prov')->orderBy('nomor_urut')->get()],
            'dprd_kab' => ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis', 'dprd_kab')->where('dapil_id', $desa->kecamatan?->dapil_id)->orderBy('nomor_urut')->get()],
        ];
    }

    private function activeDesa(): Desa
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            abort_if(! session('admin_view_desa_id'), 403, 'Pilih desa yang ingin dilihat.');

            return Desa::with('kecamatan', 'tps')->findOrFail(session('admin_view_desa_id'));
        }

        if ($user->role === 'ppk') {
            abort_if(! session('admin_view_desa_id'), 403, 'Pilih desa yang ingin dilihat.');
            $desa = Desa::with('kecamatan', 'tps')->findOrFail(session('admin_view_desa_id'));
            abort_if($desa->kecamatan_id !== $user->kecamatan_id, 403, 'Akses ditolak.');

            return $desa;
        }

        abort_if(! $user->desa_id, 403, 'Akun belum di-assign ke Desa.');

        return Desa::with('kecamatan', 'tps')->findOrFail($user->desa_id);
    }
}
