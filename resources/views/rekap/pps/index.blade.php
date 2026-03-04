@extends('layouts.app')
@section('title', 'Rekapitulasi Data')

@section('content')
<div class="mb-8">
    <a href="{{ route('dashboard.pps') }}"
       class="inline-flex items-center gap-2 text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition font-medium mb-4">
        ← Kembali ke Dashboard
    </a>
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// PPS — Rekapitulasi</p>
    <h1 class="font-display text-4xl tracking-[2px] text-teal-400">REKAPITULASI DATA</h1>
    <p class="dark:text-gray-400 text-gray-500 text-sm mt-1">
        {{ $desa->nama }} · {{ $desa->kecamatan->nama }}
    </p>
</div>

<p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-4 pb-3 border-b dark:border-gray-800 border-gray-200 font-semibold">
    // Pilih Jenis Rekap
</p>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
@foreach(\App\Models\RekapHeader::JENIS_LABELS as $jenis => $label)
@php
    $jenisRekaps = $rekaps[$jenis] ?? collect();
    $total       = $desa->tps->count();
    $sudahIsi    = $jenisRekaps->count();
    $sudahFinal  = $jenisRekaps->where('status','final')->count();
    $persen      = $total > 0 ? round(($sudahFinal / $total) * 100) : 0;
@endphp
<a href="{{ route('pps.rekap.show', $jenis) }}"
   class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm hover:shadow-md transition overflow-hidden group block">
    <div class="p-5 border-b dark:border-gray-700 border-gray-200">
        <div class="flex items-start justify-between mb-3">
            <p class="text-sm font-semibold dark:text-gray-200 text-gray-700">{{ $label }}</p>
            <span class="text-lg group-hover:translate-x-0.5 transition-transform dark:text-gray-500 text-gray-400">→</span>
        </div>
        <div class="w-full h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full mb-2">
            <div class="h-1.5 rounded-full bg-teal-400 transition-all" style="width:{{ $persen }}%"></div>
        </div>
        <p class="text-[11px] dark:text-gray-500 text-gray-400">
            {{ $sudahFinal }}/{{ $total }} TPS difinalisasi
            @if($sudahIsi > $sudahFinal)
                · {{ $sudahIsi - $sudahFinal }} draft
            @endif
        </p>
    </div>
    <div class="px-5 py-3 flex items-center justify-between">
        <span class="text-[10px] dark:text-gray-500 text-gray-400 font-semibold uppercase tracking-wider">Lihat Rekap</span>
        @if($sudahFinal === $total && $total > 0)
            <span class="text-[9px] tracking-widest uppercase px-2 py-1 rounded font-semibold bg-teal-500/20 text-teal-400 border border-teal-500/40">Lengkap</span>
        @elseif($sudahIsi > 0)
            <span class="text-[9px] tracking-widest uppercase px-2 py-1 rounded font-semibold bg-orange-400/20 text-orange-400 border border-orange-400/40">Sebagian</span>
        @else
            <span class="text-[9px] tracking-widest uppercase px-2 py-1 rounded font-semibold bg-gray-500/20 dark:text-gray-400 text-gray-500 border border-gray-400/30">Kosong</span>
        @endif
    </div>
</a>
@endforeach
</div>
@endsection