<?php
namespace App\Http\Controllers;

use App\Services\DashboardElectionSummary;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // Menampilkan dashboard admin kabupaten.
    public function admin(DashboardElectionSummary $summary)
    {
        $this->checkRole('admin');
        return view('dashboard.admin', ['electionSummary' => $summary->forUser(Auth::user())]);
    }

    // Menampilkan dashboard PPK sesuai kecamatan user.
    public function ppk(DashboardElectionSummary $summary)
    {
        $this->checkRole('ppk');
        return view('dashboard.ppk', ['electionSummary' => $summary->forUser(Auth::user())]);
    }

    // Menampilkan dashboard PPS sesuai desa user.
    public function pps(DashboardElectionSummary $summary)
    {
        $this->checkRole('pps');
        return view('dashboard.pps', ['electionSummary' => $summary->forUser(Auth::user())]);
    }

    // Menampilkan dashboard KPPS sesuai TPS user.
    public function kpps(DashboardElectionSummary $summary)
    {
        $this->checkRole('kpps');
        return view('dashboard.kpps', ['electionSummary' => $summary->forUser(Auth::user())]);
    }

    // Memastikan user hanya membuka dashboard role miliknya.
    private function checkRole(string $role)
    {
        if (Auth::user()->role !== $role) abort(403, 'Akses ditolak.');
    }

    // Menyimpan mode lihat sebagai PPK untuk admin.
    public function viewAsPpk(\App\Models\Kecamatan $kecamatan)
    {
        session(['admin_view_kecamatan_id' => $kecamatan->id]);
        return redirect()->route('dokumen.ppk');
    }

    // Menyimpan mode lihat sebagai PPS untuk admin.
    public function viewAsPps(\App\Models\Desa $desa)
    {
        session(['admin_view_desa_id' => $desa->id]);
        return redirect()->route('dokumen.pps');
    }

    // Menyimpan mode lihat sebagai KPPS untuk admin.
    public function viewAsKpps(\App\Models\Tps $tps)
    {
        session(['admin_view_tps_id' => $tps->id]);
        return redirect()->route('dokumen.upload');
    }
}
