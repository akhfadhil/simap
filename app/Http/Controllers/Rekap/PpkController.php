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
        $showDetail = request()->boolean('detail');
        $detailDesaId = (int) request('detail_desa_id');
        $tpsIds    = Tps::whereHas('desa', fn($q) => $q->where('kecamatan_id', $kecamatan->id))->pluck('id');
        $relations = match ($jenis) {
            'ppwp'      => ['tps.desa', 'ppwpSuaras.calon'],
            'gubernur'  => ['tps.desa', 'gubernurSuaras.calon'],
            'bupati'    => ['tps.desa', 'bupatiSuaras.calon'],
            'dpd'       => ['tps.desa', 'dpdSuaras.calon'],
            default     => ['tps.desa', 'partaiSuaras.partai', 'calegSuaras.caleg'],
        };
        $rekaps    = RekapHeader::with($relations)
                                ->whereIn('tps_id', $tpsIds)
                                ->where('jenis', $jenis)
                                ->get()->keyBy('tps_id');
        $desas   = $kecamatan->desas()->with('tps')->get();
        $fieldNames = [
            'dpt_lk', 'dpt_pr',
            'pengguna_dpt_lk', 'pengguna_dpt_pr',
            'pengguna_dptb_lk', 'pengguna_dptb_pr',
            'pengguna_dpk_lk', 'pengguna_dpk_pr',
            'ss_diterima', 'ss_digunakan', 'ss_rusak', 'ss_sisa',
            'disabilitas_lk', 'disabilitas_pr',
            'suara_tidak_sah',
        ];
        $desaStats = [];
        $desaCalonTotals = [];
        $desaPartaiTotals = [];
        $desaCalegTotals = [];
        $desaPartaiGrandTotals = [];
        $tpsDesa = [];

        foreach ($desas as $desa) {
            $desaStats[$desa->id] = array_fill_keys($fieldNames, 0);
            $desaStats[$desa->id]['suara_sah'] = 0;
            $desaStats[$desa->id]['suara_total'] = 0;

            foreach ($desa->tps as $tps) {
                $tpsDesa[$tps->id] = $desa->id;
            }
        }

        foreach ($rekaps as $rekap) {
            $desaId = $tpsDesa[$rekap->tps_id] ?? null;
            if (!$desaId) {
                continue;
            }

            foreach ($fieldNames as $field) {
                $desaStats[$desaId][$field] += (int) ($rekap->{$field} ?? 0);
            }

            if (in_array($jenis, ['ppwp', 'gubernur', 'bupati', 'dpd'], true)) {
                $suaraRows = match ($jenis) {
                    'ppwp'     => $rekap->ppwpSuaras,
                    'gubernur' => $rekap->gubernurSuaras,
                    'bupati'   => $rekap->bupatiSuaras,
                    default    => $rekap->dpdSuaras,
                };

                foreach ($suaraRows as $suara) {
                    $desaCalonTotals[$desaId][$suara->calon_id] =
                        ($desaCalonTotals[$desaId][$suara->calon_id] ?? 0) + (int) $suara->suara;
                    $desaStats[$desaId]['suara_sah'] += (int) $suara->suara;
                }
            } else {
                foreach ($rekap->partaiSuaras as $suara) {
                    $desaPartaiTotals[$desaId][$suara->partai_id] =
                        ($desaPartaiTotals[$desaId][$suara->partai_id] ?? 0) + (int) $suara->suara;
                    $desaPartaiGrandTotals[$desaId][$suara->partai_id] =
                        ($desaPartaiGrandTotals[$desaId][$suara->partai_id] ?? 0) + (int) $suara->suara;
                    $desaStats[$desaId]['suara_sah'] += (int) $suara->suara;
                }

                foreach ($rekap->calegSuaras as $suara) {
                    $partaiId = $suara->caleg?->partai_id;
                    $desaCalegTotals[$desaId][$suara->caleg_id] =
                        ($desaCalegTotals[$desaId][$suara->caleg_id] ?? 0) + (int) $suara->suara;

                    if ($partaiId) {
                        $desaPartaiGrandTotals[$desaId][$partaiId] =
                            ($desaPartaiGrandTotals[$desaId][$partaiId] ?? 0) + (int) $suara->suara;
                    }

                    $desaStats[$desaId]['suara_sah'] += (int) $suara->suara;
                }
            }
        }

        foreach ($desaStats as $desaId => $stats) {
            $desaStats[$desaId]['suara_total'] = $stats['suara_sah'] + $stats['suara_tidak_sah'];
        }

        $detailDesa = $desas->firstWhere('id', $detailDesaId);
        $detailDesas = $showDetail && $detailDesa ? collect([$detailDesa]) : collect();
        $detailRekaps = $showDetail
            ? $rekaps->whereIn('tps_id', $detailDesas->flatMap(fn($desa) => $desa->tps->pluck('id'))->all())
            : collect();
        $master  = $this->getMaster($jenis);
        return view('rekap.ppk.show', compact(
            'kecamatan',
            'jenis',
            'rekaps',
            'detailRekaps',
            'desas',
            'detailDesas',
            'detailDesaId',
            'master',
            'desaStats',
            'desaCalonTotals',
            'desaPartaiTotals',
            'desaCalegTotals',
            'desaPartaiGrandTotals'
        ));
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
        if ($jenis === 'ppwp')     return ['calons' => \App\Models\RekapPpwpCalon::orderBy('nomor_urut')->get()];
        if ($jenis === 'gubernur') return ['calons' => \App\Models\RekapGubernurCalon::orderBy('nomor_urut')->get()];
        if ($jenis === 'bupati')   return ['calons' => \App\Models\RekapBupatiCalon::orderBy('nomor_urut')->get()];
        if ($jenis === 'dpd')      return ['calons' => \App\Models\RekapDpdCalon::orderBy('nomor_urut')->get()];
        $partais = \App\Models\RekapPartai::with('calegs')->where('jenis', $jenis);

        if ($jenis === 'dprd_kab') {
            $partais->where('dapil_id', Auth::user()->kecamatan?->dapil_id);
        }

        return ['partais' => $partais->orderBy('nomor_urut')->get()];
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
            'dprd_kab'  => ['partais' => \App\Models\RekapPartai::with('calegs')->where('jenis','dprd_kab')->where('dapil_id', Auth::user()->kecamatan?->dapil_id)->orderBy('nomor_urut')->get()],
        ];
    }
}
