<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function admin() { $this->checkRole('admin'); return view('dashboard.admin'); }
    public function ppk()   { $this->checkRole('ppk');   return view('dashboard.ppk'); }
    public function pps()   { $this->checkRole('pps');   return view('dashboard.pps'); }
    public function kpps()  { $this->checkRole('kpps');  return view('dashboard.kpps'); }

    private function checkRole(string $role)
    {
        if (Auth::user()->role !== $role) abort(403, 'Akses ditolak.');
    }

    public function viewAsPpk(\App\Models\Kecamatan $kecamatan)
    {
        // Simpan kecamatan_id yang mau di-view ke session
        session(['admin_view_kecamatan_id' => $kecamatan->id]);
        return redirect()->route('dokumen.ppk');
    }

    public function viewAsPps(\App\Models\Desa $desa)
    {
        session(['admin_view_desa_id' => $desa->id]);
        return redirect()->route('dokumen.pps');
    }

    public function viewAsKpps(\App\Models\Tps $tps)
    {
        session(['admin_view_tps_id' => $tps->id]);
        return redirect()->route('dokumen.upload');
    }
}