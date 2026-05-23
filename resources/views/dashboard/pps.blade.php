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
        $tpsIds      = $tpsList->pluck('id');
        $aktifJenis  = \App\Models\PemiluSetting::aktif();
        $totalPemiluAktif = count($aktifJenis);
        $targetPemiluTps  = $totalTps * $totalPemiluAktif;
        $dokumenJenisAktif = array_map('strtoupper', $aktifJenis);

        $totalDokumenMasuk = \App\Models\Dokumen::where('level', 'tps')
                            ->whereIn('tps_id', $tpsIds)
                            ->whereIn('jenis', $dokumenJenisAktif)
                            ->count();
        $persenDokumen = $targetPemiluTps > 0 ? min(100, round(($totalDokumenMasuk / $targetPemiluTps) * 100)) : 0;

        $totalRekapFinal = \App\Models\RekapHeader::select('tps_id')
                            ->where('status', 'final')
                            ->whereIn('tps_id', $tpsIds)
                            ->whereIn('jenis', $aktifJenis)
                            ->groupBy('tps_id')
                            ->havingRaw('COUNT(DISTINCT jenis) = ?', [$totalPemiluAktif])
                            ->count();
        $persenRekap = $totalTps > 0 ? min(100, round(($totalRekapFinal / $totalTps) * 100)) : 0;
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
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Dokumen Masuk</p>
        <p class="font-display text-3xl tracking-wide text-teal-400">{{ $totalDokumenMasuk }}/{{ $targetPemiluTps }}</p>
        <div class="mt-2 flex items-center gap-2">
            <div class="flex-1 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                <div class="h-1.5 rounded-full bg-teal-400 transition-all" style="width:{{ $persenDokumen }}%"></div>
            </div>
            <span class="text-xs dark:text-gray-500 text-gray-400">{{ $persenDokumen }}%</span>
        </div>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">TPS x {{ $totalPemiluAktif }} pemilu aktif</p>
    </div>
    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Rekap Finalisasi</p>
        <p class="font-display text-3xl tracking-wide text-teal-400">{{ $totalRekapFinal }}/{{ $totalTps }}</p>
        <div class="mt-2 flex items-center gap-2">
            <div class="flex-1 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                <div class="h-1.5 rounded-full bg-teal-400 transition-all" style="width:{{ $persenRekap }}%"></div>
            </div>
            <span class="text-xs dark:text-gray-500 text-gray-400">{{ $persenRekap }}%</span>
        </div>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">TPS final semua pemilu aktif</p>
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
