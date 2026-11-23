<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Dokumen;
use App\Models\PemiluSetting;
use App\Models\Tps;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PpsController extends Controller
{
    // Menampilkan daftar TPS dalam desa PPS.
    public function dataTps(): Response
    {
        $desa = $this->activeDesa();
        $desa->load('kecamatan');

        $aktifJenis = PemiluSetting::aktif();
        $aktifDokumenKeys = collect(Dokumen::JENIS)
            ->filter(fn ($label, $key) => in_array(strtolower($key), $aktifJenis, true))
            ->keys()
            ->all();

        $totalJenisAktif = count($aktifDokumenKeys);

        $tpsList = Tps::where('desa_id', $desa->id)
            ->with([
                'dokumens' => fn ($q) => $q->whereIn('jenis', $aktifDokumenKeys),
                'users' => fn ($q) => $q->where('role', 'kpps')->select('id', 'name', 'username', 'tps_id'),
            ])
            ->orderBy('nama')
            ->get();

        $tpsData = $tpsList->map(function ($tps) use ($totalJenisAktif) {
            $dokumens = $tps->dokumens;
            $masuk = $dokumens->count();
            $terverifikasi = $dokumens->where('status', 'terverifikasi')->count();
            $menunggu = $dokumens->where('status', 'menunggu_verifikasi')->count();
            $ditolak = $dokumens->where('status', 'ditolak')->count();
            $persen = $totalJenisAktif > 0 ? round(($terverifikasi / $totalJenisAktif) * 100, 1) : 0;
            $kppsUser = $tps->users->first();

            return [
                'id' => $tps->id,
                'nama' => $tps->nama,
                'kpps_user' => $kppsUser ? [
                    'id' => $kppsUser->id,
                    'name' => $kppsUser->name,
                    'username' => $kppsUser->username,
                ] : null,
                'total_masuk' => $masuk,
                'total_terverifikasi' => $terverifikasi,
                'total_menunggu' => $menunggu,
                'total_ditolak' => $ditolak,
                'target' => $totalJenisAktif,
                'persentase' => $persen,
                'is_lengkap' => $totalJenisAktif > 0 && $terverifikasi >= $totalJenisAktif,
                'dokumens' => $dokumens->map(fn ($d) => [
                    'id' => $d->id,
                    'jenis' => $d->jenis,
                    'status' => $d->status,
                    'file_name' => $d->file_name,
                ]),
            ];
        });

        $totalTps = $tpsData->count();
        $totalTargetDok = $totalTps * $totalJenisAktif;
        $totalTerverifikasi = $tpsData->sum('total_terverifikasi');
        $totalMenunggu = $tpsData->sum('total_menunggu');
        $totalDitolak = $tpsData->sum('total_ditolak');
        $totalMasuk = $tpsData->sum('total_masuk');
        $totalKppsAssigned = $tpsData->filter(fn ($t) => ! empty($t['kpps_user']))->count();

        $stats = [
            'total_tps' => $totalTps,
            'total_kpps_assigned' => $totalKppsAssigned,
            'total_target_dokumen' => $totalTargetDok,
            'total_masuk' => $totalMasuk,
            'total_terverifikasi' => $totalTerverifikasi,
            'total_menunggu' => $totalMenunggu,
            'total_ditolak' => $totalDitolak,
            'persentase_terverifikasi' => $totalTargetDok > 0 ? round(($totalTerverifikasi / $totalTargetDok) * 100, 1) : 0,
            'persentase_masuk' => $totalTargetDok > 0 ? round(($totalMasuk / $totalTargetDok) * 100, 1) : 0,
        ];

        return Inertia::render('Pps/DataTps', [
            'desa' => [
                'id' => $desa->id,
                'nama' => $desa->nama,
                'kecamatan_nama' => $desa->kecamatan?->nama ?? '',
            ],
            'tpsList' => $tpsData,
            'stats' => $stats,
            'totalJenisAktif' => $totalJenisAktif,
            'isAdminView' => Auth::user()->role !== 'pps',
        ]);
    }

    // Mengaktifkan mode lihat/upload dokumen KPPS untuk TPS tertentu.
    public function viewTps(Tps $tps)
    {
        $desa = $this->activeDesa();

        abort_if($tps->desa_id !== $desa->id, 403);

        session([
            'admin_view_kecamatan_id' => $desa->kecamatan_id,
            'admin_view_desa_id' => $tps->desa_id,
            'admin_view_tps_id' => $tps->id,
        ]);

        return redirect()->route('dashboard.kpps');
    }

    private function activeDesa(): Desa
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            abort_if(! session('admin_view_desa_id'), 403, 'Pilih desa yang ingin dilihat.');

            return Desa::findOrFail(session('admin_view_desa_id'));
        }

        if ($user->role === 'ppk') {
            abort_if(! session('admin_view_desa_id'), 403, 'Pilih desa yang ingin dilihat.');
            $desa = Desa::findOrFail(session('admin_view_desa_id'));
            abort_if($desa->kecamatan_id !== $user->kecamatan_id, 403, 'Akses ditolak.');

            return $desa;
        }

        abort_if(! $user->desa_id, 403, 'Akun belum di-assign ke Desa.');

        return Desa::findOrFail($user->desa_id);
    }
}
