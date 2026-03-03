<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Tps;

class PpsController extends Controller
{
    public function dataTps()
    {
        $user = Auth::user();
        abort_if(!$user->desa_id, 403, 'Akun belum di-assign ke Desa.');

        $tpsList = Tps::where('desa_id', $user->desa_id)
            ->with(['dokumens', 'users' => fn($q) => $q->where('role', 'kpps')])
            ->get();

        return view('pps.data-tps', compact('tpsList'));
    }

    public function viewTps(Tps $tps)
    {
        $user = Auth::user();

        // Pastikan TPS ini milik desa si PPS
        abort_if($tps->desa_id !== $user->desa_id, 403);

        session(['admin_view_tps_id' => $tps->id]);
        return redirect()->route('dokumen.upload');
    }
}