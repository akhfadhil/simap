<?php
namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\Tps;
use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DokumenController extends Controller
{
    // ── KPPS: Form Upload ──────────────────────────────────────
    public function uploadForm()
    {
        $user = Auth::user();

        if (session('admin_view_tps_id')) {
            $tpsId       = session('admin_view_tps_id');
            $isAdminView = true;
        } else {
            abort_if(!$user->tps_id, 403, 'Akun belum di-assign ke TPS.');
            $tpsId       = $user->tps_id;
            $isAdminView = false;
        }

        $tps = Tps::with('desa.kecamatan')->findOrFail($tpsId);

        $uploaded = Dokumen::where('tps_id', $tps->id)
            ->get()
            ->keyBy('jenis');

        return view('dokumen.upload', compact('tps', 'uploaded', 'isAdminView'));
    }

    // ── KPPS: Store / Replace ──────────────────────────────────
    public function store(Request $request)
    {
        $user = Auth::user();

        if (session('admin_view_tps_id')) {
            $tpsId = session('admin_view_tps_id');
        } else {
            abort_if(!$user->tps_id, 403, 'Akun belum di-assign ke TPS.');
            $tpsId = $user->tps_id;
        }

        $request->validate([
            'jenis' => 'required|in:' . implode(',', array_keys(Dokumen::JENIS)),
            'file'  => 'required|file|mimes:pdf|max:10240',
        ]);

        $tps = Tps::with('desa.kecamatan')->findOrFail($tpsId);

        // Hapus file lama kalau ada
        $existing = Dokumen::where('tps_id', $tps->id)
            ->where('jenis', $request->jenis)
            ->first();

        if ($existing) {
            Storage::delete($existing->file_path);
            $existing->delete();
        }

        // Buat path terstruktur
        $kecFolder  = preg_replace('/[^A-Za-z0-9_\-]/', '_', $tps->desa->kecamatan->nama);
        $desaFolder = preg_replace('/[^A-Za-z0-9_\-]/', '_', $tps->desa->nama);
        $tpsFolder  = preg_replace('/[^A-Za-z0-9_\-]/', '_', $tps->nama);

        $file = $request->file('file');
        $path = $file->storeAs(
            "documents/{$kecFolder}/desa/{$desaFolder}/{$tpsFolder}",
            strtolower($request->jenis) . '.pdf'
        );

        Dokumen::create([
            'tps_id'      => $tps->id,
            'kecamatan_id'=> null,
            'uploaded_by' => $user->id,
            'jenis'       => $request->jenis,
            'level'       => 'tps',
            'status'      => 'menunggu_verifikasi',
            'file_path'   => $path,
            'file_name'   => $file->getClientOriginalName(),
            'file_size'   => $file->getSize(),
        ]);

        return back()->with('success', Dokumen::JENIS[$request->jenis] . ' berhasil diupload.');
    }
    // ── PPS: Index ─────────────────────────────────────────────
    public function indexPps(Request $request)
    {
        $user = Auth::user();

        // Admin atau PPK sedang view-as-pps
        if (session('admin_view_desa_id')) {
            $desaId      = session('admin_view_desa_id');
            $isAdminView = true;
        } else {
            abort_if(!$user->desa_id, 403, 'Akun belum di-assign ke Desa.');
            $desaId      = $user->desa_id;
            $isAdminView = false;
        }

        $tpsList = Tps::where('desa_id', $desaId)
            ->with(['dokumens' => fn($q) => $q->with('uploader', 'verifier')])
            ->when($request->tps_id, fn($q) => $q->where('id', $request->tps_id))
            ->get();

        $desa = \App\Models\Desa::with('kecamatan')->findOrFail($desaId);

        return view('dokumen.pps', compact('tpsList', 'desa', 'isAdminView'));
    }

    // ── PPS: Verifikasi ────────────────────────────────────────
    public function verifikasi(Request $request, Dokumen $dokumen)
    {
        $user = Auth::user();

        // Pastikan dokumen ini milik desa si PPS
        $tps = Tps::findOrFail($dokumen->tps_id);
        abort_if($tps->desa_id !== $user->desa_id, 403);

        $dokumen->update([
            'status'      => 'terverifikasi',
            'verified_by' => $user->id,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Dokumen berhasil diverifikasi.');
    }

    // ── PPK: Index ─────────────────────────────────────────────
    public function indexPpk(Request $request)
    {
        $user = Auth::user();

        // Kalau admin sedang view-as-ppk, pakai kecamatan dari session
        if ($user->role === 'admin' && session('admin_view_kecamatan_id')) {
            $kecamatanId = session('admin_view_kecamatan_id');
            $isAdminView = true;
        } else {
            abort_if(!$user->kecamatan_id, 403, 'Akun belum di-assign ke Kecamatan.');
            $kecamatanId = $user->kecamatan_id;
            $isAdminView = false;
        }

        $kecamatan = \App\Models\Kecamatan::findOrFail($kecamatanId);
        $desaIds   = \App\Models\Desa::where('kecamatan_id', $kecamatanId)->pluck('id');

        $tpsList = Tps::whereIn('desa_id', $desaIds)
            ->with(['desa', 'dokumens.uploader', 'dokumens.verifier'])
            ->when($request->desa_id, fn($q) => $q->where('desa_id', $request->desa_id))
            ->get();

        $desas = \App\Models\Desa::where('kecamatan_id', $kecamatanId)->get();

        return view('dokumen.ppk', compact('tpsList', 'desas', 'kecamatan', 'isAdminView'));
    }

    // ── PPK: Form Upload ───────────────────────────────────────────
    public function uploadFormPpk()
    {
        $user = Auth::user();
        abort_if(!$user->kecamatan_id, 403, 'Akun belum di-assign ke Kecamatan.');

        $kecamatan = \App\Models\Kecamatan::findOrFail($user->kecamatan_id);

        $uploaded = Dokumen::where('kecamatan_id', $kecamatan->id)
            ->where('level', 'kecamatan')
            ->get()
            ->keyBy('jenis');

        return view('dokumen.upload_ppk', compact('kecamatan', 'uploaded'));
    }

    // ── PPK: Store ─────────────────────────────────────────────────
    public function storePpk(Request $request)
    {
        $user = Auth::user();
        abort_if(!$user->kecamatan_id, 403, 'Akun belum di-assign ke Kecamatan.');

        $request->validate([
            'jenis' => 'required|in:' . implode(',', array_keys(Dokumen::JENIS)),
            'file'  => 'required|file|mimes:pdf|max:10240',
        ]);

        $kecamatan = \App\Models\Kecamatan::findOrFail($user->kecamatan_id);

        // Hapus file lama kalau ada
        $existing = Dokumen::where('kecamatan_id', $kecamatan->id)
            ->where('level', 'kecamatan')
            ->where('jenis', $request->jenis)
            ->first();

        if ($existing) {
            Storage::delete($existing->file_path);
            $existing->delete();
        }

        // Buat path terstruktur
        $kecFolder = preg_replace('/[^A-Za-z0-9_\-]/', '_', $kecamatan->nama);

        $file = $request->file('file');
        $path = $file->storeAs(
            "documents/{$kecFolder}/d_hasil",
            strtolower($request->jenis) . '.pdf'
        );

        Dokumen::create([
            'kecamatan_id' => $kecamatan->id,
            'tps_id'       => null,
            'uploaded_by'  => $user->id,
            'jenis'        => $request->jenis,
            'level'        => 'kecamatan',
            'status'       => 'menunggu_verifikasi',
            'file_path'    => $path,
            'file_name'    => $file->getClientOriginalName(),
            'file_size'    => $file->getSize(),
        ]);

        return back()->with('success', Dokumen::JENIS[$request->jenis] . ' berhasil diupload.');
    }
    
    // ── Admin: Index ───────────────────────────────────────────
    public function indexAdmin(Request $request)
    {
        $kecamatans = Kecamatan::all();

        $desaIds = $request->kecamatan_id
            ? Desa::where('kecamatan_id', $request->kecamatan_id)->pluck('id')
            : null;

        // Dokumen TPS
        $tpsList = Tps::with(['desa.kecamatan', 'dokumens.uploader', 'dokumens.verifier'])
            ->when($desaIds,           fn($q) => $q->whereIn('desa_id', $desaIds))
            ->when($request->desa_id,  fn($q) => $q->where('desa_id', $request->desa_id))
            ->get();

        // Dokumen Kecamatan (dari PPK)
        $dokumenKecamatan = Dokumen::where('level', 'kecamatan')
            ->with(['kecamatan', 'uploader', 'verifier'])
            ->when($request->kecamatan_id, fn($q) => $q->where('kecamatan_id', $request->kecamatan_id))
            ->get()
            ->groupBy('kecamatan_id');

        $desas = $request->kecamatan_id
            ? Desa::where('kecamatan_id', $request->kecamatan_id)->get()
            : collect();

        return view('dokumen.admin', compact('tpsList', 'kecamatans', 'desas', 'dokumenKecamatan'));
    }

    // ── Admin: Verifikasi dokumen TPS atau Kecamatan ───────────────
    public function verifikasiAdmin(Request $request, Dokumen $dokumen)
    {
        // Pastikan hanya admin
        abort_if(Auth::user()->role !== 'admin', 403);

        $dokumen->update([
            'status'      => 'terverifikasi',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Dokumen berhasil diverifikasi.');
    }

    // ── Preview PDF (semua role, dengan guard) ─────────────────
    public function preview(Dokumen $dokumen)
    {
        $this->authorizeAccess($dokumen);

        $path = Storage::path($dokumen->file_path);

        abort_if(!Storage::exists($dokumen->file_path), 404, 'File tidak ditemukan.');

        return response()->file($path, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $dokumen->file_name . '"',
        ]);
    }
    
    // ── Download PDF ───────────────────────────────────────────
    public function download(Dokumen $dokumen)
    {
        $this->authorizeAccess($dokumen);

        abort_if(!Storage::exists($dokumen->file_path), 404, 'File tidak ditemukan.');

        return Storage::download($dokumen->file_path, $dokumen->file_name);
    }

    // ── Guard: pastikan user boleh akses dokumen ini ───────────
    private function authorizeAccess(Dokumen $dokumen): void
    {
        $user = Auth::user();

        if ($dokumen->level === 'kecamatan') {
            $allowed = match($user->role) {
                'admin' => true,
                'ppk'   => $dokumen->kecamatan_id === $user->kecamatan_id,
                default => false,
            };
        } else {
            $tps = Tps::with('desa')->findOrFail($dokumen->tps_id);
            $allowed = match($user->role) {
                'admin' => true,
                'ppk'   => \App\Models\Desa::where('kecamatan_id', $user->kecamatan_id)
                                ->where('id', $tps->desa_id)->exists(),
                'pps'   => $tps->desa_id === $user->desa_id,
                'kpps'  => $tps->id === $user->tps_id,
                default => false,
            };
        }

        abort_if(!$allowed, 403);
    }
}