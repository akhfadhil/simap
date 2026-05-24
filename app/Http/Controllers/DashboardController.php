<?php
namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Tps;
use App\Services\DashboardElectionSummary;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // Menampilkan dashboard admin kabupaten.
    public function admin(DashboardElectionSummary $summary)
    {
        $this->checkRole('admin');
        session()->forget(['admin_view_kecamatan_id', 'admin_view_desa_id', 'admin_view_tps_id']);

        return view('dashboard.admin', ['electionSummary' => $summary->forUser(Auth::user())]);
    }

    // Menampilkan dashboard PPK sesuai kecamatan user.
    public function ppk(DashboardElectionSummary $summary)
    {
        $user = Auth::user();
        $this->checkRoleOrAdminView('ppk', 'admin_view_kecamatan_id');

        $viewKecamatan = $user->role === 'admin'
            ? Kecamatan::findOrFail(session('admin_view_kecamatan_id'))
            : null;

        return view('dashboard.ppk', [
            'electionSummary' => $summary->forUser($user),
            'viewKecamatan' => $viewKecamatan,
            'isAdminView' => (bool) $viewKecamatan,
        ]);
    }

    // Menampilkan dashboard PPS sesuai desa user.
    public function pps(DashboardElectionSummary $summary)
    {
        $user = Auth::user();
        $this->checkRoleOrAdminView('pps', 'admin_view_desa_id');

        $viewDesa = $user->role === 'admin'
            ? Desa::with('kecamatan')->findOrFail(session('admin_view_desa_id'))
            : null;

        return view('dashboard.pps', [
            'electionSummary' => $summary->forUser($user),
            'viewDesa' => $viewDesa,
            'isAdminView' => (bool) $viewDesa,
        ]);
    }

    // Menampilkan dashboard KPPS sesuai TPS user.
    public function kpps(DashboardElectionSummary $summary)
    {
        $user = Auth::user();
        $this->checkRoleOrAdminView('kpps', 'admin_view_tps_id');

        $viewTps = $user->role === 'admin'
            ? Tps::with('desa.kecamatan')->findOrFail(session('admin_view_tps_id'))
            : null;

        return view('dashboard.kpps', [
            'electionSummary' => $summary->forUser($user),
            'viewTps' => $viewTps,
            'isAdminView' => (bool) $viewTps,
        ]);
    }

    // Memastikan user hanya membuka dashboard role miliknya.
    private function checkRole(string $role)
    {
        if (Auth::user()->role !== $role) abort(403, 'Akses ditolak.');
    }

    private function checkRoleOrAdminView(string $role, string $sessionKey): void
    {
        $user = Auth::user();

        if ($user->role === $role) {
            return;
        }

        if ($user->role === 'admin' && session()->has($sessionKey)) {
            return;
        }

        abort(403, 'Akses ditolak.');
    }

    // Menyimpan mode lihat sebagai PPK untuk admin.
    public function viewAsPpk(Kecamatan $kecamatan)
    {
        session([
            'admin_view_kecamatan_id' => $kecamatan->id,
        ]);
        session()->forget(['admin_view_desa_id', 'admin_view_tps_id']);

        return redirect()->route('dashboard.ppk');
    }

    // Menyimpan mode lihat sebagai PPS untuk admin.
    public function viewAsPps(Desa $desa)
    {
        session([
            'admin_view_kecamatan_id' => $desa->kecamatan_id,
            'admin_view_desa_id' => $desa->id,
        ]);
        session()->forget('admin_view_tps_id');

        return redirect()->route('dashboard.pps');
    }

    // Menyimpan mode lihat sebagai KPPS untuk admin.
    public function viewAsKpps(Tps $tps)
    {
        $tps->load('desa');
        session([
            'admin_view_kecamatan_id' => $tps->desa->kecamatan_id,
            'admin_view_desa_id' => $tps->desa_id,
            'admin_view_tps_id' => $tps->id,
        ]);

        return redirect()->route('dashboard.kpps');
    }
}
