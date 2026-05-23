@extends('layouts.app')
@section('title', 'Dashboard KPPS')

@section('content')
<div class="mb-10">
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// Kelompok Penyelenggara Pemungutan Suara</p>
    <h1 class="font-display text-5xl tracking-[2px] text-sky-300">DASHBOARD KPPS</h1>
    <p class="dark:text-gray-400 text-gray-500 text-sm mt-2">Input dan laporan data pemungutan suara di TPS.</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
        $tps      = Auth::user()->tps;
        $aktifJenis = \App\Models\PemiluSetting::aktif();
        $totalPemiluAktif = count($aktifJenis);
        $dokumenJenisAktif = array_map('strtoupper', $aktifJenis);
        $uploaded = $tps
            ? \App\Models\Dokumen::where('tps_id', $tps->id)->whereIn('jenis', $dokumenJenisAktif)->count()
            : 0;
        $terverif = $tps
            ? \App\Models\Dokumen::where('tps_id', $tps->id)->whereIn('jenis', $dokumenJenisAktif)->where('status','terverifikasi')->count()
            : 0;
        $totalRekap = $tps
            ? \App\Models\RekapHeader::where('tps_id', $tps->id)->whereIn('jenis', $aktifJenis)->count()
            : 0;
        $finalRekap = $tps
            ? \App\Models\RekapHeader::where('tps_id', $tps->id)->whereIn('jenis', $aktifJenis)->where('status','final')->count()
            : 0;
        $draftRekap = max(0, $totalRekap - $finalRekap);
        $belumRekap = max(0, $totalPemiluAktif - $totalRekap);
    @endphp

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">TPS</p>
        <p class="font-display text-3xl tracking-wide text-sky-300">{{ $tps->nama ?? '-' }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">{{ $tps->desa->nama ?? '-' }}</p>
    </div>

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Dokumen Upload</p>
        <p class="font-display text-3xl tracking-wide text-sky-300">{{ $uploaded }}/{{ $totalPemiluAktif }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">sudah diupload</p>
    </div>

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Terverifikasi</p>
        <p class="font-display text-3xl tracking-wide text-sky-300">{{ $terverif }}/{{ $totalPemiluAktif }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">oleh PPS</p>
    </div>

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Rekap Data</p>
        <p class="font-display text-3xl tracking-wide text-sky-300">{{ $finalRekap }}/{{ $totalPemiluAktif }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">
            @if($totalPemiluAktif > 0 && $finalRekap === $totalPemiluAktif)
                semua difinalisasi ✓
            @elseif($totalRekap > 0)
                {{ $draftRekap }} draft · {{ $belumRekap }} belum diisi
            @else
                belum ada rekap
            @endif
        </p>
    </div>
</div>

{{-- Menu --}}
<p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-4 pb-3 border-b dark:border-gray-800 border-gray-200 font-semibold">// Menu Utama</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    <a href="{{ route('dokumen.upload') }}"
       class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-sky-300 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-sky-300 transition text-lg">→</span>
        <div class="text-3xl mb-4">📄</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Upload Dokumen</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Upload dokumen PDF hasil pemungutan suara untuk pemilu aktif.</p>
    </a>
    
    <a href="{{ route('rekap.index') }}"
        class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-sky-300 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-sky-300 transition text-lg">→</span>
        <div class="text-3xl mb-4">📊</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Isi Data Rekapitulasi</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Input data hasil suara ke dalam tabel rekapitulasi untuk pemilu aktif.</p>
    </a>

</div>
@endsection
