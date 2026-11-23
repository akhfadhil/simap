<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Dokumen;
use App\Models\Kecamatan;
use App\Models\PemiluSetting;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PpkController extends Controller
{
    // Menampilkan daftar PPS/desa dalam kecamatan PPK.
    public function dataPps(): Response
    {
        $kecamatan = $this->activeKecamatan();

        $aktifJenis = PemiluSetting::aktif();
        $aktifDokumenKeys = collect(Dokumen::JENIS)
            ->filter(fn ($label, $key) => in_array(strtolower($key), $aktifJenis, true))
            ->keys()
            ->all();

        $totalJenisAktif = count($aktifDokumenKeys);

        $desas = Desa::where('kecamatan_id', $kecamatan->id)
            ->with([
                'tps' => fn ($q) => $q->orderBy('nama'),
                'tps.dokumens' => fn ($q) => $q->whereIn('jenis', $aktifDokumenKeys),
                'users' => fn ($q) => $q->where('role', 'pps')->select('id', 'name', 'username', 'desa_id'),
            ])
            ->orderBy('nama')
            ->get();

        $desaData = $desas->map(function ($desa) use ($totalJenisAktif) {
            $totalTps = $desa->tps->count();
            $targetDok = $totalTps * $totalJenisAktif;

            $dokumens = $desa->tps->flatMap->dokumens;
            $masuk = $dokumens->count();
            $terverifikasi = $dokumens->where('status', 'terverifikasi')->count();
            $menunggu = $dokumens->where('status', 'menunggu_verifikasi')->count();
            $ditolak = $dokumens->where('status', 'ditolak')->count();

            $persenTerverifikasi = $targetDok > 0 ? round(($terverifikasi / $targetDok) * 100, 1) : 0;
            $persenMasuk = $targetDok > 0 ? round(($masuk / $targetDok) * 100, 1) : 0;

            $tpsList = $desa->tps->map(function ($tps) use ($totalJenisAktif) {
                $tpsDok = $tps->dokumens;
                $tpsMasuk = $tpsDok->count();
                $tpsTerverif = $tpsDok->where('status', 'terverifikasi')->count();
                $tpsMenunggu = $tpsDok->where('status', 'menunggu_verifikasi')->count();
                $tpsDitolak = $tpsDok->where('status', 'ditolak')->count();

                return [
                    'id' => $tps->id,
                    'nama' => $tps->nama,
                    'total_masuk' => $tpsMasuk,
                    'total_terverifikasi' => $tpsTerverif,
                    'total_menunggu' => $tpsMenunggu,
                    'total_ditolak' => $tpsDitolak,
                    'target' => $totalJenisAktif,
                    'is_lengkap' => $totalJenisAktif > 0 && $tpsTerverif >= $totalJenisAktif,
                ];
            });

            return [
                'id' => $desa->id,
                'nama' => $desa->nama,
                'total_tps' => $totalTps,
                'pps_users' => $desa->users->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                ]),
                'target_dokumen' => $targetDok,
                'total_masuk' => $masuk,
                'total_terverifikasi' => $terverifikasi,
                'total_menunggu' => $menunggu,
                'total_ditolak' => $ditolak,
                'persentase_terverifikasi' => $persenTerverifikasi,
                'persentase_masuk' => $persenMasuk,
                'tps_list' => $tpsList,
            ];
        });

        $totalTps = $desaData->sum('total_tps');
        $totalTargetDok = $desaData->sum('target_dokumen');
        $totalTerverifikasi = $desaData->sum('total_terverifikasi');
        $totalMenunggu = $desaData->sum('total_menunggu');
        $totalDitolak = $desaData->sum('total_ditolak');
        $totalMasuk = $desaData->sum('total_masuk');
        $totalPpsAssigned = $desaData->filter(fn ($d) => count($d['pps_users']) > 0)->count();

        $stats = [
            'total_desa' => $desaData->count(),
            'total_tps' => $totalTps,
            'total_pps_assigned' => $totalPpsAssigned,
            'total_target_dokumen' => $totalTargetDok,
            'total_masuk' => $totalMasuk,
            'total_terverifikasi' => $totalTerverifikasi,
            'total_menunggu' => $totalMenunggu,
            'total_ditolak' => $totalDitolak,
            'persentase_terverifikasi' => $totalTargetDok > 0 ? round(($totalTerverifikasi / $totalTargetDok) * 100, 1) : 0,
            'persentase_masuk' => $totalTargetDok > 0 ? round(($totalMasuk / $totalTargetDok) * 100, 1) : 0,
        ];

        return Inertia::render('Ppk/DataPps', [
            'kecamatan' => [
                'id' => $kecamatan->id,
                'nama' => $kecamatan->nama,
            ],
            'desas' => $desaData,
            'stats' => $stats,
            'totalJenisAktif' => $totalJenisAktif,
        ]);
    }

    // Mengaktifkan mode lihat dokumen PPS untuk desa tertentu.
    public function viewPps(Desa $desa)
    {
        $kecamatan = $this->activeKecamatan();

        abort_if($desa->kecamatan_id !== $kecamatan->id, 403);

        session([
            'admin_view_kecamatan_id' => $desa->kecamatan_id,
            'admin_view_desa_id' => $desa->id,
        ]);
        session()->forget('admin_view_tps_id');

        return redirect()->route('dashboard.pps');
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
