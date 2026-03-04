<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RekapPpwpCalon;
use App\Models\RekapDpdCalon;
use App\Models\RekapPartai;
use App\Models\RekapCaleg;
use Illuminate\Http\Request;

class SetupController extends Controller
{
    public function index()
    {
        $ppwpCalons  = RekapPpwpCalon::orderBy('nomor_urut')->get();
        $dpdCalons   = RekapDpdCalon::orderBy('nomor_urut')->get();
        $partaiDprRi = RekapPartai::with('calegs')->where('jenis','dpr_ri')->orderBy('nomor_urut')->get();
        $partaiProv  = RekapPartai::with('calegs')->where('jenis','dprd_prov')->orderBy('nomor_urut')->get();
        $partaiKab   = RekapPartai::with('calegs')->where('jenis','dprd_kab')->orderBy('nomor_urut')->get();

        return view('admin.setup.index', compact('ppwpCalons','dpdCalons','partaiDprRi','partaiProv','partaiKab'));
    }

    public function storePpwp(Request $request)
    {
        $request->validate(['nomor_urut' => 'required|integer', 'nama_paslon' => 'required|string|max:200']);
        RekapPpwpCalon::create($request->only('nomor_urut','nama_paslon'));
        return back()->with('success', 'Paslon PPWP berhasil ditambahkan.');
    }

    public function destroyPpwp(RekapPpwpCalon $calon)
    {
        $calon->delete();
        return back()->with('success', 'Paslon dihapus.');
    }

    public function storeDpd(Request $request)
    {
        $request->validate(['nomor_urut' => 'required|integer', 'nama_calon' => 'required|string|max:200']);
        RekapDpdCalon::create($request->only('nomor_urut','nama_calon'));
        return back()->with('success', 'Calon DPD berhasil ditambahkan.');
    }

    public function destroyDpd(RekapDpdCalon $calon)
    {
        $calon->delete();
        return back()->with('success', 'Calon DPD dihapus.');
    }

    public function storePartai(Request $request)
    {
        $request->validate([
            'jenis'       => 'required|in:dpr_ri,dprd_prov,dprd_kab',
            'nomor_urut'  => 'required|integer',
            'nama_partai' => 'required|string|max:200',
        ]);
        RekapPartai::create($request->only('jenis','nomor_urut','nama_partai'));
        return back()->with('success', 'Partai berhasil ditambahkan.');
    }

    public function destroyPartai(RekapPartai $partai)
    {
        $partai->delete(); // calegs cascade
        return back()->with('success', 'Partai dan caleg-calegnya dihapus.');
    }

    public function storeCaleg(Request $request, RekapPartai $partai)
    {
        $request->validate(['nomor_urut' => 'required|integer', 'nama_caleg' => 'required|string|max:200']);
        $partai->calegs()->create($request->only('nomor_urut','nama_caleg'));
        return back()->with('success', 'Caleg berhasil ditambahkan.');
    }

    public function destroyCaleg(RekapCaleg $caleg)
    {
        $caleg->delete();
        return back()->with('success', 'Caleg dihapus.');
    }
}