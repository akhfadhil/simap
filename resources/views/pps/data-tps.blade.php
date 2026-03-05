@extends('layouts.app')
@section('title', 'Data TPS')

@section('content')

<div class="mb-8">
    <a href="{{ route('dashboard.pps') }}"
       class="inline-flex items-center gap-2 text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition font-medium mb-4">
        ← Kembali ke Dashboard
    </a>
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// PPS — Data TPS</p>
    <h1 class="font-display text-4xl tracking-[2px] text-teal-400">DATA TPS</h1>
    <p class="dark:text-gray-400 text-gray-500 text-sm mt-1">
        {{ Auth::user()->desa->nama ?? '' }} · {{ Auth::user()->desa->kecamatan->nama ?? '' }}
    </p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-8">
    @php
        $totalTps      = $tpsList->count();
        $totalMaxDok   = $totalTps * 5;
        $totalUploaded = $tpsList->sum(fn($t) => $t->dokumens->count());
        $totalVerif    = $tpsList->sum(fn($t) => $t->dokumens->where('status','terverifikasi')->count());
        $persenUpload  = $totalMaxDok > 0 ? round(($totalUploaded / $totalMaxDok) * 100) : 0;
        $persenVerif   = $totalMaxDok > 0 ? round(($totalVerif / $totalMaxDok) * 100) : 0;
    @endphp

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Total TPS</p>
        <p class="font-display text-4xl text-teal-400">{{ $totalTps }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">{{ $totalTps * 5 }} dokumen maksimal</p>
    </div>

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Sudah Upload</p>
        <p class="font-display text-4xl text-teal-400">{{ $totalUploaded }}/{{ $totalMaxDok }}</p>
        <div class="mt-2 flex items-center gap-2">
            <div class="flex-1 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                <div class="h-1.5 rounded-full bg-teal-400 transition-all" style="width:{{ $persenUpload }}%"></div>
            </div>
            <span class="text-xs dark:text-gray-500 text-gray-400">{{ $persenUpload }}%</span>
        </div>
    </div>

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Terverifikasi</p>
        <p class="font-display text-4xl text-teal-400">{{ $totalVerif }}/{{ $totalMaxDok }}</p>
        <div class="mt-2 flex items-center gap-2">
            <div class="flex-1 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                <div class="h-1.5 rounded-full bg-teal-400 transition-all" style="width:{{ $persenVerif }}%"></div>
            </div>
            <span class="text-xs dark:text-gray-500 text-gray-400">{{ $persenVerif }}%</span>
        </div>
    </div>
</div>

{{-- Daftar TPS --}}
<p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-4 pb-3 border-b dark:border-gray-800 border-gray-200 font-semibold">
    // Daftar TPS
</p>

<div class="space-y-3">
@forelse($tpsList as $tps)
@php
    $totalDok = $tps->dokumens->count();
    $terverif = $tps->dokumens->where('status','terverifikasi')->count();
    $kppsUser = $tps->users->first();
    $persen   = round(($totalDok / 5) * 100);
@endphp
<div class="dark:bg-gray-800 bg-white rounded-xl p-5 border dark:border-gray-700 border-gray-200 shadow-sm flex items-center justify-between flex-wrap gap-4">
    <div class="flex items-center gap-4">
        <div class="w-1 h-14 rounded-full flex-shrink-0 bg-teal-400"></div>
        <div>
            <p class="font-semibold text-sm dark:text-gray-100 text-gray-800">{{ $tps->nama }}</p>
            <p class="text-[11px] dark:text-gray-500 text-gray-400 mt-0.5">
                KPPS: {{ $kppsUser->name ?? 'Belum assign' }}
            </p>
            <div class="flex items-center gap-2 mt-2">
                <div class="w-32 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                    <div class="h-1.5 rounded-full bg-teal-400 transition-all"
                         style="width:{{ $persen }}%"></div>
                </div>
                <span class="text-[11px] dark:text-gray-500 text-gray-400">
                    {{ $totalDok }}/5 dok · {{ $terverif }} terverifikasi
                </span>
            </div>
        </div>
    </div>

    <a href="{{ route('pps.view-tps', $tps) }}"
       class="px-4 py-2 rounded-lg text-xs font-semibold border border-teal-400 text-teal-400 hover:bg-teal-400 hover:text-white transition">
        👁 View KPPS
    </a>
</div>
@empty
<div class="dark:bg-gray-800 bg-white rounded-xl px-6 py-16 text-center dark:text-gray-600 text-gray-400 text-sm border dark:border-gray-700 border-gray-200">
    Belum ada TPS di desa ini.
</div>
@endforelse
</div>

@endsection