@extends('layouts.app')
@section('title', 'Data TPS')

@section('content')

<div class="mb-8">
    <a href="{{ route('dashboard.pps') }}"
       class="inline-flex items-center gap-2 font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase hover:text-brand transition mb-4">
        ← KEMBALI KE DASHBOARD
    </a>
    <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-2">// PPS — Data TPS</p>
    <h1 class="font-display text-4xl tracking-[2px]" style="color:#2EC4B6">DATA TPS</h1>
    <p class="text-gray-500 text-sm mt-1">{{ Auth::user()->desa->nama ?? '' }} · {{ Auth::user()->desa->kecamatan->nama ?? '' }}</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-px bg-gray-800 mb-8">
    <div class="bg-[#141414] p-6">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-3">Total TPS</p>
        <p class="font-display text-4xl" style="color:#2EC4B6">{{ $tpsList->count() }}</p>
    </div>
    <div class="bg-[#141414] p-6">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-3">Sudah Upload</p>
        @php $sudahUpload = $tpsList->filter(fn($t) => $t->dokumens->count() > 0)->count(); @endphp
        <p class="font-display text-4xl" style="color:#2EC4B6">{{ $sudahUpload }}/{{ $tpsList->count() }}</p>
    </div>
    <div class="bg-[#141414] p-6">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-3">Terverifikasi</p>
        @php $terverif = $tpsList->sum(fn($t) => $t->dokumens->where('status','terverifikasi')->count()); @endphp
        <p class="font-display text-4xl" style="color:#2EC4B6">{{ $terverif }}</p>
        <p class="text-xs text-gray-600 mt-1">dokumen</p>
    </div>
</div>

{{-- Daftar TPS --}}
<p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-4 pb-3 border-b border-gray-800">
    // Daftar TPS
</p>

<div class="grid grid-cols-1 gap-px bg-gray-800">
@forelse($tpsList as $tps)
@php
    $totalDok  = $tps->dokumens->count();
    $terverif  = $tps->dokumens->where('status','terverifikasi')->count();
    $kppsUser  = $tps->users->first();
    $persen    = round(($totalDok / 5) * 100);
@endphp
<div class="bg-[#141414] p-6 flex items-center justify-between flex-wrap gap-4 hover:bg-[#1a1a1a] transition">
    <div class="flex items-center gap-4">
        <div class="w-[3px] h-12 rounded-sm flex-shrink-0" style="background:#2EC4B6"></div>
        <div>
            <p class="font-semibold text-sm">{{ $tps->nama }}</p>
            <p class="font-mono2 text-[10px] text-gray-600 mt-0.5">
                KPPS: {{ $kppsUser->name ?? 'Belum assign' }}
            </p>
            <div class="flex items-center gap-2 mt-2">
                <div class="w-32 h-1 bg-gray-800 rounded-full">
                    <div class="h-1 rounded-full transition-all"
                         style="width:{{ $persen }}%; background:#2EC4B6"></div>
                </div>
                <span class="font-mono2 text-[10px] text-gray-600">
                    {{ $totalDok }}/5 dok · {{ $terverif }} terverifikasi
                </span>
            </div>
        </div>
    </div>

    <a href="{{ route('pps.view-tps', $tps) }}"
       class="flex items-center gap-2 border px-4 py-2 font-mono2 text-[10px] uppercase tracking-widest transition"
       style="border-color:#2EC4B644;color:#2EC4B6"
       onmouseover="this.style.borderColor='#2EC4B6'" onmouseout="this.style.borderColor='#2EC4B644'">
        👁 VIEW KPPS
    </a>
</div>
@empty
<div class="bg-[#141414] px-6 py-16 text-center text-gray-700 font-mono2 text-xs tracking-widest">
    BELUM ADA TPS DI DESA INI
</div>
@endforelse
</div>

@endsection