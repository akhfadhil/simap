@extends('layouts.app')
@section('title', 'Data PPS')

@section('content')

<div class="mb-8">
    <a href="{{ route('dashboard.ppk') }}"
       class="inline-flex items-center gap-2 text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition font-medium mb-4">
        ← Kembali ke Dashboard
    </a>
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// PPK — Data PPS</p>
    <h1 class="font-display text-4xl tracking-[2px] text-orange-400">DATA PPS</h1>
    <p class="dark:text-gray-400 text-gray-500 text-sm mt-1">{{ Auth::user()->kecamatan->nama ?? '' }}</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-8">
    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Total Desa/PPS</p>
        <p class="font-display text-4xl text-orange-400">{{ $desas->count() }}</p>
    </div>
    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Total TPS</p>
        <p class="font-display text-4xl text-orange-400">{{ $desas->sum(fn($d) => $d->tps->count()) }}</p>
    </div>
    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Dokumen Masuk</p>
        @php
            $totalDok = $desas->sum(fn($d) => $d->tps->sum(fn($t) => $t->dokumens->count()));
            $totalMax = $desas->sum(fn($d) => $d->tps->count()) * 5;
        @endphp
        <p class="font-display text-4xl text-orange-400">{{ $totalDok }}/{{ $totalMax }}</p>
    </div>
</div>

{{-- Daftar Desa --}}
<p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-4 pb-3 border-b dark:border-gray-800 border-gray-200 font-semibold">
    // Daftar Desa & PPS
</p>

<div class="space-y-3">
@forelse($desas as $desa)
@php
    $totalTps = $desa->tps->count();
    $totalDok = $desa->tps->sum(fn($t) => $t->dokumens->count());
    $terverif = $desa->tps->sum(fn($t) => $t->dokumens->where('status','terverifikasi')->count());
    $ppsUser  = $desa->users->first();
    $persen   = $totalTps > 0 ? round(($totalDok / ($totalTps * 5)) * 100) : 0;
@endphp
<div class="dark:bg-gray-800 bg-white rounded-xl p-5 border dark:border-gray-700 border-gray-200 shadow-sm flex items-center justify-between flex-wrap gap-4">
    <div class="flex items-center gap-4">
        <div class="w-1 h-14 rounded-full flex-shrink-0 bg-orange-400"></div>
        <div>
            <p class="font-semibold text-sm dark:text-gray-100 text-gray-800">{{ $desa->nama }}</p>
            <p class="text-[11px] dark:text-gray-500 text-gray-400 mt-0.5">
                {{ $totalTps }} TPS · PPS: {{ $ppsUser->name ?? 'Belum assign' }}
            </p>
            <div class="flex items-center gap-2 mt-2">
                <div class="w-32 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                    <div class="h-1.5 rounded-full bg-orange-400 transition-all"
                         style="width:{{ $persen }}%"></div>
                </div>
                <span class="text-[11px] dark:text-gray-500 text-gray-400">
                    {{ $totalDok }}/{{ $totalTps * 5 }} dok · {{ $terverif }} terverifikasi
                </span>
            </div>
        </div>
    </div>

    <a href="{{ route('ppk.view-pps', $desa) }}"
       class="px-4 py-2 rounded-lg text-xs font-semibold border border-orange-400 text-orange-400 hover:bg-orange-400 hover:text-white transition">
        👁 View PPS
    </a>
</div>
@empty
<div class="dark:bg-gray-800 bg-white rounded-xl px-6 py-16 text-center dark:text-gray-600 text-gray-400 text-sm border dark:border-gray-700 border-gray-200">
    Belum ada desa di kecamatan ini.
</div>
@endforelse
</div>

@endsection