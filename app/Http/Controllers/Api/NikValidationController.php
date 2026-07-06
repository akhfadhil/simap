<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NikReservasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NikValidationController extends Controller
{
    public function checkAndReserve(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|size:16',
            'partai_slug' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $nik = $request->input('nik');
        $partaiSlug = $request->input('partai_slug');

        $reservasi = NikReservasi::where('nik', $nik)->first();

        if ($reservasi) {
            if ($reservasi->partai_slug === $partaiSlug) {
                return response()->json([
                    'success' => true,
                    'message' => 'NIK sudah terdaftar untuk mendukung partai ini.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'NIK ini sudah terdaftar memberikan dukungan untuk partai lain.',
            ], 422);
        }

        // Simpan reservasi baru
        NikReservasi::create([
            'nik' => $nik,
            'partai_slug' => $partaiSlug,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'NIK berhasil diregistrasikan.',
        ]);
    }
}
