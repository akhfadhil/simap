<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tps;
use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Http\Request;

class TpsController extends Controller
{
    public function index()
    {
        $kecamatans = Kecamatan::with(['desas.tps'])->orderBy('nama')->get();
        return view('admin.tps.index', compact('kecamatans'));
    }

    /**
     * Bulk store: buat TPS 001 s/d {jumlah} untuk desa tertentu.
     * Jika TPS sudah ada di rentang itu, skip (insertOrIgnore by nama+desa_id).
     */
    public function store(Request $request)
    {
        $request->validate([
            'desa_id' => 'required|exists:desas,id',
            'jumlah'  => 'required|integer|min:1|max:999',
        ]);

        $desaId  = $request->desa_id;
        $jumlah  = (int) $request->jumlah;
        $created = 0;

        for ($i = 1; $i <= $jumlah; $i++) {
            $nama = 'TPS ' . str_pad($i, 3, '0', STR_PAD_LEFT);

            $exists = Tps::where('desa_id', $desaId)->where('nama', $nama)->exists();
            if (!$exists) {
                Tps::create(['nama' => $nama, 'desa_id' => $desaId]);
                $created++;
            }
        }

        $desa = Desa::find($desaId);
        return back()->with('success', "Berhasil membuat {$created} TPS baru di {$desa->nama}. (TPS yang sudah ada dilewati)");
    }

    /**
     * Update nama TPS.
     */
    public function update(Request $request, Tps $tps)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
        ]);

        $tps->update(['nama' => $request->nama]);

        return back()->with('success', "Nama TPS berhasil diubah menjadi \"{$tps->nama}\".");
    }

    public function destroy(Tps $tps)
    {
        $nama = $tps->nama;
        $tps->delete();
        return back()->with('success', "{$nama} berhasil dihapus.");
    }
}
