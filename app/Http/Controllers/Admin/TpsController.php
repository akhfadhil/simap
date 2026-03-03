<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tps;
use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Http\Request;

class TpsController extends Controller
{
    public function index(Request $request)
    {
        $kecamatans = Kecamatan::all();
        $desas = $request->kecamatan_id
            ? Desa::where('kecamatan_id', $request->kecamatan_id)->get()
            : collect();

        $tps = Tps::with('desa.kecamatan')
            ->when($request->desa_id, fn($q) => $q->where('desa_id', $request->desa_id))
            ->when($request->kecamatan_id && !$request->desa_id, fn($q) =>
                $q->whereHas('desa', fn($q2) => $q2->where('kecamatan_id', $request->kecamatan_id))
            )
            ->latest()->get();

        return view('admin.wilayah.tps', compact('tps', 'desas', 'kecamatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'    => 'required|string|max:100',
            'desa_id' => 'required|exists:desas,id',
        ]);
        Tps::create($request->only('nama', 'desa_id'));
        return back()->with('success', 'TPS berhasil ditambahkan.');
    }

    public function update(Request $request, Tps $tps)
    {
        $request->validate([
            'nama'    => 'required|string|max:100',
            'desa_id' => 'required|exists:desas,id',
        ]);
        $tps->update($request->only('nama', 'desa_id'));
        return back()->with('success', 'TPS berhasil diupdate.');
    }

    public function destroy(Tps $tps)
    {
        $tps->delete();
        return back()->with('success', 'TPS berhasil dihapus.');
    }
}