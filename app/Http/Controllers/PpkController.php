<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Desa;

class PpkController extends Controller
{
    // Menampilkan daftar PPS/desa dalam kecamatan PPK.
    public function dataPps()
    {
        $user = Auth::user();
        abort_if(!$user->kecamatan_id, 403, 'Akun belum di-assign ke Kecamatan.');

        $desas = Desa::where('kecamatan_id', $user->kecamatan_id)
            ->with(['tps.dokumens', 'users' => fn($q) => $q->where('role', 'pps')])
            ->get();

        return view('ppk.data-pps', compact('desas'));
    }

    // Mengaktifkan mode lihat dokumen PPS untuk desa tertentu.
    public function viewPps(Desa $desa)
    {
        $user = Auth::user();

        abort_if($desa->kecamatan_id !== $user->kecamatan_id, 403);

        session(['admin_view_desa_id' => $desa->id]);
        return redirect()->route('dokumen.pps');
    }
}
