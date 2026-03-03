@extends('layouts.app')
@section('title', 'Data PPS')

@section('content')

<div class="mb-8">
    <a href="{{ route('dashboard.ppk') }}"
       class="inline-flex items-center gap-2 font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase hover:text-brand transition mb-4">
        ← KEMBALI KE DASHBOARD
    </a>
    <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-2">// PPK — Data PPS</p>
    <h1 class="font-display text-4xl tracking-[2px]" style="color:#F4A261">DATA PPS</h1>
    <p class="text-gray-500 text-sm mt-1">{{ Auth::user()->kecamatan->nama ?? '' }}</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-px bg-gray-800 mb-8">
    <div class="bg-[#141414] p-6">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-3">Total Desa/PPS</p>
        <p class="font-display text-4xl" style="color:#F4A261">{{ $desas->count() }}</p>
    </div>
    <div class="bg-[#141414] p-6">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-3">Total TPS</p>
        <p class="font-display text-4xl" style="color:#F4A261">{{ $desas->sum(fn($d) => $d->tps->count()) }}</p>
    </div>
    <div class="bg-[#141414] p-6">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-3">Dokumen Masuk</p>
        @php $totalDok = $desas->sum(fn($d) => $d->tps->sum(fn($t) => $t->dokumens->count())); @endphp
        @php $totalMax = $desas->sum(fn($d) => $d->tps->count()) * 5; @endphp
        <p class="font-display text-4xl" style="color:#F4A261">{{ $totalDok }}/{{ $totalMax }}</p>
    </div>
</div>

{{-- Daftar Desa --}}
<p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-4 pb-3 border-b border-gray-800">
    // Daftar Desa & PPS
</p>

<div class="grid grid-cols-1 gap-px bg-gray-800">
@forelse($desas as $desa)
@php
    $totalTps    = $desa->tps->count();
    $totalDok    = $desa->tps->sum(fn($t) => $t->dokumens->count());
    $terverif    = $desa->tps->sum(fn($t) => $t->dokumens->where('status','terverifikasi')->count());
    $ppsUser     = $desa->users->first();
    $persen      = $totalTps > 0 ? round(($totalDok / ($totalTps * 5)) * 100) : 0;
@endphp
<div class="bg-[#141414] p-6 flex items-center justify-between flex-wrap gap-4 hover:bg-[#1a1a1a] transition group">
    <div class="flex items-center gap-4">
        <div class="w-[3px] h-12 rounded-sm flex-shrink-0" style="background:#F4A261"></div>
        <div>
            <p class="font-semibold text-sm">{{ $desa->nama }}</p>
            <p class="font-mono2 text-[10px] text-gray-600 mt-0.5">
                {{ $totalTps }} TPS
                · PPS: {{ $ppsUser->name ?? 'Belum assign' }}
            </p>
            {{-- Progress dokumen --}}
            <div class="flex items-center gap-2 mt-2">
                <div class="w-32 h-1 bg-gray-800 rounded-full">
                    <div class="h-1 rounded-full transition-all"
                         style="width:{{ $persen }}%; background:#F4A261"></div>
                </div>
                <span class="font-mono2 text-[10px] text-gray-600">
                    {{ $totalDok }}/{{ $totalTps * 5 }} dok · {{ $terverif }} terverifikasi
                </span>
            </div>
        </div>
    </div>

    <a href="{{ route('ppk.view-pps', $desa) }}"
       class="flex items-center gap-2 border px-4 py-2 font-mono2 text-[10px] uppercase tracking-widest transition"
       style="border-color:#F4A26144;color:#F4A261"
       onmouseover="this.style.borderColor='#F4A261'" onmouseout="this.style.borderColor='#F4A26144'">
        👁 VIEW PPS
    </a>
</div>
@empty
<div class="bg-[#141414] px-6 py-16 text-center text-gray-700 font-mono2 text-xs tracking-widest">
    BELUM ADA DESA DI KECAMATAN INI
</div>
@endforelse
</div>

@endsection