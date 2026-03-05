@extends('layouts.app')
@section('title', 'Dashboard PPK')

@section('content')
<div class="mb-10">
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// Panitia Pemilihan Kecamatan</p>
    <h1 class="font-display text-5xl tracking-[2px] text-orange-400">DASHBOARD PPK</h1>
    <p class="dark:text-gray-400 text-gray-500 text-sm mt-2">Rekap dan koordinasi dokumen pemilu tingkat kecamatan.</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
        $kecamatan    = Auth::user()->kecamatan;
        $desas        = $kecamatan ? $kecamatan->desas : collect();
        $tpsAll       = $desas->flatMap(fn($d) => $d->tps);
        $totalTps     = $tpsAll->count();

        // Dokumen D hasil PPK (bukan dari TPS)
        $dokPpk       = $kecamatan
                        ? \App\Models\Dokumen::where('kecamatan_id', $kecamatan->id)
                            ->where('level', 'ppk')
                            ->get()
                        : collect();
        $totalUpload  = $dokPpk->count();
        $totalVerif   = $dokPpk->where('status', 'terverifikasi')->count();
        $persenUp     = round(($totalUpload / 5) * 100);
        $persenVer    = round(($totalVerif / 5) * 100);
    @endphp

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Kecamatan</p>
        <p class="font-display text-3xl tracking-wide text-orange-400">{{ $kecamatan->nama ?? '-' }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">wilayah tugas</p>
    </div>

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Jumlah Desa | TPS</p>
        <p class="font-display text-3xl tracking-wide text-orange-400">{{ $desas->count() }} | {{ $totalTps }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">desa | titik pemungutan</p>
    </div>

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Dokumen D Upload</p>
        <p class="font-display text-3xl tracking-wide text-orange-400">{{ $totalUpload }}/5</p>
        <div class="mt-2 flex items-center gap-2">
            <div class="flex-1 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                <div class="h-1.5 rounded-full bg-orange-400 transition-all" style="width:{{ $persenUp }}%"></div>
            </div>
            <span class="text-xs dark:text-gray-500 text-gray-400">{{ $persenUp }}%</span>
        </div>
    </div>

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Dokumen D Terverifikasi</p>
        <p class="font-display text-3xl tracking-wide text-orange-400">{{ $totalVerif }}/5</p>
        <div class="mt-2 flex items-center gap-2">
            <div class="flex-1 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                <div class="h-1.5 rounded-full bg-orange-400 transition-all" style="width:{{ $persenVer }}%"></div>
            </div>
            <span class="text-xs dark:text-gray-500 text-gray-400">{{ $persenVer }}%</span>
        </div>
    </div>
</div>

{{-- Menu --}}
<p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-4 pb-3 border-b dark:border-gray-800 border-gray-200 font-semibold">// Menu Utama</p>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">

    <a href="{{ route('dokumen.ppk') }}"
       class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-orange-400 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-orange-400 transition text-lg">→</span>
        <div class="text-3xl mb-4">🗂️</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Rekap Dokumen</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Lihat dan download dokumen dari seluruh TPS di kecamatan.</p>
    </a>

    <a href="{{ route('ppk.data-pps') }}"
       class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-orange-400 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-orange-400 transition text-lg">→</span>
        <div class="text-3xl mb-4">🏘️</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Data PPS</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Pantau status desa dan lihat dashboard tiap PPS di kecamatan.</p>
    </a>

    <a href="{{ route('ppk.upload') }}"
       class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-orange-400 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-orange-400 transition text-lg">→</span>
        <div class="text-3xl mb-4">📤</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Upload Dokumen</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Upload 5 jenis dokumen D hasil rekapitulasi kecamatan.</p>
    </a>

    <a href="{{ route('ppk.rekap.index') }}"
   class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-orange-400 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-orange-400 transition text-lg">→</span>
        <div class="text-3xl mb-4">📊</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Rekapitulasi Data</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Lihat rekap suara dari seluruh TPS di kecamatan.</p>
    </a>
    
</div>
@endsection