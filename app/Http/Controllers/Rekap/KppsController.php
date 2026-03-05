<?php
namespace App\Http\Controllers\Rekap;

use App\Http\Controllers\Controller;
use App\Models\RekapHeader;
use App\Models\RekapPpwpCalon;
use App\Models\RekapDpdCalon;
use App\Models\RekapPartai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KppsController extends Controller
{
    const JENIS = ['ppwp', 'dpd', 'dpr_ri', 'dprd_prov', 'dprd_kab'];

    public function index()
    {
        $tps    = Auth::user()->tps;
        $rekaps = RekapHeader::where('tps_id', $tps->id)
                             ->get()->keyBy('jenis');
        return view('rekap.kpps.index', compact('tps', 'rekaps'));
    }

    public function form(string $jenis)
    {
        abort_unless(in_array($jenis, self::JENIS), 404);
        $tps   = Auth::user()->tps;
        $rekap = RekapHeader::where('tps_id', $tps->id)->where('jenis', $jenis)->first();
        $data  = $this->getMasterData($jenis, $rekap);
        return view('rekap.kpps.form', compact('tps', 'jenis', 'rekap', 'data'));
    }

    public function store(Request $request, string $jenis)
    {
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
            }
        });

        $label = RekapHeader::JENIS_LABELS[$jenis];
        if (request('finalisasi') == '1') {
            return redirect()->route('rekap.index')->with('success', "Rekap {$label} berhasil difinalisasi.");
        }

        return redirect()->route('rekap.index')->with('success', "Rekap {$label} berhasil disimpan.");
    }

    public function finalisasi(string $jenis)
    {
        $tps   = Auth::user()->tps;
        $rekap = RekapHeader::where('tps_id', $tps->id)->where('jenis', $jenis)->firstOrFail();
        $rekap->update(['status' => 'final', 'difinalisasi_at' => now()]);
        return back()->with('success', 'Rekap berhasil difinalisasi.');
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
}