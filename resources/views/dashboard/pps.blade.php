@extends('layouts.app')
@section('title', 'Dashboard PPS')

@section('content')
<div class="mb-10">
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// Panitia Pemungutan Suara</p>
    <h1 class="font-display text-5xl tracking-[2px] text-teal-400">DASHBOARD PPS</h1>
    <p class="dark:text-gray-400 text-gray-500 text-sm mt-2">Rekap dan verifikasi dokumen TPS di wilayah desa.</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
        $desa        = Auth::user()->desa;
        $tpsList     = $desa ? $desa->tps : collect();
        $totalTps    = $tpsList->count();
        $sudahUpload = $tpsList->filter(fn($t) => $t->dokumens->count() > 0)->count();
        $terverif    = $tpsList->sum(fn($t) => $t->dokumens->where('status','terverifikasi')->count());
    @endphp
    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Desa</p>
        <p class="font-display text-3xl tracking-wide text-teal-400">{{ $desa->nama ?? '-' }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">{{ $desa->kecamatan->nama ?? '-' }}</p>
    </div>
    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Jumlah TPS</p>
        <p class="font-display text-3xl tracking-wide text-teal-400">{{ $totalTps }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">di desa ini</p>
    </div>
    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Sudah Upload</p>
        <p class="font-display text-3xl tracking-wide text-teal-400">{{ $sudahUpload }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">dari {{ $totalTps }} TPS</p>
    </div>
    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Terverifikasi</p>
        <p class="font-display text-3xl tracking-wide text-teal-400">{{ $terverif }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">dokumen</p>
    </div>
</div>

{{-- Menu --}}
<p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-4 pb-3 border-b dark:border-gray-800 border-gray-200 font-semibold">// Menu Utama</p>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">

    <a href="{{ route('dokumen.pps') }}"
       class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-teal-400 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-teal-400 transition text-lg">→</span>
        <div class="text-3xl mb-4">✅</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Verifikasi Dokumen</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Preview, download, dan verifikasi dokumen yang diupload KPPS dari tiap TPS.</p>
    </a>

    <a href="{{ route('pps.data-tps') }}"
       class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-teal-400 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-teal-400 transition text-lg">→</span>
        <div class="text-3xl mb-4">🗳️</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Data TPS</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Pantau status TPS dan lihat dashboard tiap KPPS di desa.</p>
    </a>

    <a href="{{ route('pps.rekap.index') }}"
        class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-teal-400 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-teal-400 transition text-lg">→</span>
        <div class="text-3xl mb-4">📈</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Rekapitulasi Data</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Lihat rekap suara dari seluruh TPS di desa.</p>
    </a>
</div>
@endsection