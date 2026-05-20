<?php
namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use App\Models\RekapHeader;
use App\Models\RekapPpwpCalon;
use App\Models\RekapGubernurCalon;
use App\Models\RekapBupatiCalon;
use App\Models\RekapDpdCalon;
use App\Models\RekapPartai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exports\RekapSheetExport;
use App\Services\RekapAdminCache;
use Maatwebsite\Excel\Facades\Excel;

class KppsController extends Controller
{
    const JENIS = ['ppwp', 'gubernur', 'bupati', 'dpd', 'dpr_ri', 'dprd_prov', 'dprd_kab'];
    
    public function index()
    {
        $tps    = Auth::user()->tps;
        $rekaps = RekapHeader::where('tps_id', $tps->id)
                             ->get()->keyBy('jenis');
        return view('rekap.kpps.index', compact('tps', 'rekaps'));
    }

    private function cekAktif(string $jenis): void
    {
        $aktif = \App\Models\PemiluSetting::aktif();
        abort_if(!in_array($jenis, $aktif), 403, 'Jenis pemilu ini tidak aktif.');
    }

    public function form(string $jenis)
    {
        $this->cekAktif($jenis);
        abort_unless(in_array($jenis, self::JENIS), 404);
        $tps   = Auth::user()->tps;
        $rekap = RekapHeader::where('tps_id', $tps->id)->where('jenis', $jenis)->first();
        $data  = $this->getMasterData($jenis, $rekap);
        return view('rekap.kpps.form', compact('tps', 'jenis', 'rekap', 'data'));
    }

    public function store(Request $request, string $jenis)
    {
        $this->cekAktif($jenis);
        abort_unless(in_array($jenis, self::JENIS), 404);
        $tps = Auth::user()->tps;

        $existing = RekapHeader::where('tps_id', $tps->id)->where('jenis', $jenis)->first();
        if ($existing && $existing->status === 'final') {
            return back()->with('error', 'Rekap sudah difinalisasi, tidak bisa diubah.');
        }

        DB::transaction(function () use ($request, $jenis, $tps) {
            $rekap = RekapHeader::updateOrCreate(
                ['tps_id' => $tps->id, 'jenis' => $jenis],
                array_merge($request->only([
                    'dpt_lk','dpt_pr',
                    'pengguna_dpt_lk','pengguna_dpt_pr',
                    'pengguna_dptb_lk','pengguna_dptb_pr',
                    'pengguna_dpk_lk','pengguna_dpk_pr',
                    'ss_diterima','ss_digunakan','ss_rusak','ss_sisa',
                    'disabilitas_lk','disabilitas_pr',
                    'suara_tidak_sah',
                ]), ['diinput_oleh' => Auth::id(), 'status' => 'draft'])
            );

            if ($jenis === 'ppwp') {
                foreach ($request->input('suara', []) as $calon_id => $suara) {
                    $rekap->ppwpSuaras()->updateOrCreate(['calon_id' => $calon_id], ['suara' => (int)$suara]);
                }
            } elseif ($jenis === 'gubernur') {
                foreach ($request->input('suara', []) as $calon_id => $suara) {
                    $rekap->gubernurSuaras()->updateOrCreate(['calon_id' => $calon_id], ['suara' => (int)$suara]);
                }
            } elseif ($jenis === 'bupati') {
                foreach ($request->input('suara', []) as $calon_id => $suara) {
                    $rekap->bupatiSuaras()->updateOrCreate(['calon_id' => $calon_id], ['suara' => (int)$suara]);
                }
            } elseif ($jenis === 'dpd') {
                foreach ($request->input('suara', []) as $calon_id => $suara) {
                    $rekap->dpdSuaras()->updateOrCreate(['calon_id' => $calon_id], ['suara' => (int)$suara]);
                }
            } else {
                foreach ($request->input('suara_partai', []) as $partai_id => $suara) {
                    $rekap->partaiSuaras()->updateOrCreate(['partai_id' => $partai_id], ['suara' => (int)$suara]);
                }
                foreach ($request->input('suara_caleg', []) as $caleg_id => $suara) {
                    $rekap->calegSuaras()->updateOrCreate(['caleg_id' => $caleg_id], ['suara' => (int)$suara]);
                }
            }

            // Finalisasi langsung jika ada flag
            if (request('finalisasi') == '1') {
                $rekap->update(['status' => 'final', 'difinalisasi_at' => now()]);
                try {
                    $tps->load('desa.kecamatan');
                    app(\App\Services\RekapExportService::class)->handleFinalisasi($tps, $jenis);
                } catch (\Exception $e) {
                    \Log::error('Auto export gagal: ' . $e->getMessage());
                }
            }
        });
        RekapAdminCache::flushAggregate();

        $label = RekapHeader::JENIS_LABELS[$jenis];
        if (request('finalisasi') == '1') {
            return redirect()->route('rekap.index')->with('success', "Rekap {$label} berhasil difinalisasi.");
        }

        return redirect()->route('rekap.index')->with('success', "Rekap {$label} berhasil disimpan.");
    }

    // public function finalisasi(string $jenis)
    // {
    //     $tps   = Auth::user()->tps;
    //     $rekap = RekapHeader::where('tps_id', $tps->id)
    //                         ->where('jenis', $jenis)
    //                         ->firstOrFail();

    //     $rekap->update(['status' => 'final', 'difinalisasi_at' => now()]);

    //     // Auto export ke storage
    //     try {
    //         $tps->load('desa.kecamatan');
    //         app(\App\Services\RekapExportService::class)->handleFinalisasi($tps, $jenis);
    //     } catch (\Exception $e) {
    //         // Jangan gagalkan finalisasi jika export error
    //         \Log::error('Auto export gagal: ' . $e->getMessage());
    //     }

    //     return back()->with('success', 'Rekap berhasil difinalisasi dan disimpan ke storage.');
    // }

    // public function finalisasi(string $jenis)
    // {
    //     $this->cekAktif($jenis);
    //     $tps   = Auth::user()->tps;
    //     $rekap = RekapHeader::where('tps_id', $tps->id)
    //                         ->where('jenis', $jenis)
    //                         ->firstOrFail();

    //     $rekap->update(['status' => 'final', 'difinalisasi_at' => now()]);
        
    //     // \Log::info('Finalisasi dipanggil', ['tps_id' => $tps->id, 'jenis' => $jenis]);

    //     try {
    //         // \Log::info('Masuk try block');
    //         $tps->load('desa.kecamatan');
    //         app(\App\Services\RekapExportService::class)->handleFinalisasi($tps, $jenis);
    //         // \Log::info('handleFinalisasi selesai');
    //     } catch (\Exception $e) {
    //         \Log::error('Auto export gagal: ' . $e->getMessage());
    //     }

    //     // return back()->with('success', 'Rekap berhasil difinalisasi dan disimpan ke storage.');
    //     return redirect()->route('rekap.index')->with('success', 'Rekap berhasil difinalisasi dan disimpan ke storage.');
    // }

    public function export(string $jenis)
    {
        $this->cekAktif($jenis);
        abort_unless(in_array($jenis, self::JENIS), 404);

        $tps    = Auth::user()->tps;
        $relations = match ($jenis) {
            'ppwp'      => ['ppwpSuaras'],
            'gubernur'  => ['gubernurSuaras'],
            'bupati'    => ['bupatiSuaras'],
            'dpd'       => ['dpdSuaras'],
            default     => ['partaiSuaras', 'calegSuaras'],
        };
        $rekap  = RekapHeader::with($relations)
                            ->where('tps_id', $tps->id)
                            ->where('jenis', $jenis)
                            ->get();

        $tpsList = collect([$tps]);
        $master  = $this->getAllMaster();
        $wilayah = $tps->nama . ' — ' . $tps->desa->nama;
        $label   = RekapHeader::JENIS_LABELS[$jenis];
        $filename = 'Rekap_' . strtoupper($jenis) . '_' . str_replace(' ', '_', $tps->nama) . '.xlsx';

        $sheet = new RekapSheetExport(
            $jenis,
            $label,
            $rekap,
            $master,
            $tpsList,
            'kpps',
            $wilayah
        );

        return Excel::download($sheet, $filename);
    }

    private function getMasterData(string $jenis, ?RekapHeader $rekap): array
    {
        $existingSuara  = [];
        $existingPartai = [];
        $existingCaleg  = [];

        if ($rekap) {
            $existingPartai = $rekap->partaiSuaras->pluck('suara','partai_id')->toArray();
            $existingCaleg  = $rekap->calegSuaras->pluck('suara','caleg_id')->toArray();
        }

        if ($jenis === 'ppwp') {
            if ($rekap) $existingSuara = $rekap->ppwpSuaras->pluck('suara','calon_id')->toArray();
            return [
                'calons' => RekapPpwpCalon::orderBy('nomor_urut')->get(),
                'suara'  => $existingSuara,
            ];
        }

        if ($jenis === 'gubernur') {
            if ($rekap) $existingSuara = $rekap->gubernurSuaras->pluck('suara','calon_id')->toArray();
            return [
                'calons' => RekapGubernurCalon::orderBy('nomor_urut')->get(),
                'suara'  => $existingSuara,
            ];
        }

        if ($jenis === 'bupati') {
            if ($rekap) $existingSuara = $rekap->bupatiSuaras->pluck('suara','calon_id')->toArray();
            return [
                'calons' => RekapBupatiCalon::orderBy('nomor_urut')->get(),
                'suara'  => $existingSuara,
            ];
        }

        if ($jenis === 'dpd') {
            if ($rekap) $existingSuara = $rekap->dpdSuaras->pluck('suara','calon_id')->toArray();
            return [
                'calons' => RekapDpdCalon::orderBy('nomor_urut')->get(),
                'suara'  => $existingSuara,
            ];
        }

        if ($jenis === 'dpr_ri') {
            return [
                'partais'       => RekapPartai::with('calegs')
                                    ->where('jenis', 'dpr_ri')
                                    ->orderBy('nomor_urut')
                                    ->get(),
                'suara_partai'  => $existingPartai,
                'suara_caleg'   => $existingCaleg,
            ];
        }

        if ($jenis === 'dprd_prov') {
            return [
                'partais'       => RekapPartai::with('calegs')
                                    ->where('jenis', 'dprd_prov')
                                    ->orderBy('nomor_urut')
                                    ->get(),
                'suara_partai'  => $existingPartai,
                'suara_caleg'   => $existingCaleg,
            ];
        }

        if ($jenis === 'dprd_kab') {
            $kecamatan = Auth::user()->tps->desa->kecamatan;
            $dapilId   = $kecamatan->dapil_id;

            return [
                'partais'       => RekapPartai::with('calegs')
                                    ->where('jenis', 'dprd_kab')
                                    ->where('dapil_id', $dapilId)
                                    ->orderBy('nomor_urut')
                                    ->get(),
                'suara_partai'  => $existingPartai,
                'suara_caleg'   => $existingCaleg,
                'dapil'         => $kecamatan->dapil,
            ];
        }

        return [];
    }

    private function getAllMaster(): array
    {
        $kecamatan = Auth::user()->tps->desa->kecamatan;
        return [
            'ppwp'      => ['calons'  => RekapPpwpCalon::orderBy('nomor_urut')->get()],
            'gubernur'  => ['calons'  => RekapGubernurCalon::orderBy('nomor_urut')->get()],
            'bupati'    => ['calons'  => RekapBupatiCalon::orderBy('nomor_urut')->get()],
            'dpd'       => ['calons'  => RekapDpdCalon::orderBy('nomor_urut')->get()],
            'dpr_ri'    => ['partais' => RekapPartai::with('calegs')->where('jenis','dpr_ri')->orderBy('nomor_urut')->get()],
            'dprd_prov' => ['partais' => RekapPartai::with('calegs')->where('jenis','dprd_prov')->orderBy('nomor_urut')->get()],
            'dprd_kab'  => ['partais' => RekapPartai::with('calegs')->where('jenis','dprd_kab')->where('dapil_id', $kecamatan->dapil_id)->orderBy('nomor_urut')->get()],
        ];
    }

}
