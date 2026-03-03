@extends('layouts.app')
@section('title', 'Dashboard PPK')

@section('content')
<div class="mb-10">
    <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-2">// Panitia Pemilihan Kecamatan</p>
    <h1 class="font-display text-5xl tracking-[2px]" style="color:#F4A261">DASHBOARD PPK</h1>
    <p class="text-gray-500 text-sm mt-2">Rekap dan koordinasi dokumen pemilu tingkat kecamatan.</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-px bg-gray-800 mb-8">
    @foreach([
        ['label'=>'Kecamatan','value'=> Auth::user()->kecamatan->nama ?? '-','sub'=>'wilayah tugas'],
        ['label'=>'Jumlah Desa','value'=> Auth::user()->kecamatan ? Auth::user()->kecamatan->desas->count() : '-','sub'=>'di kecamatan'],
        ['label'=>'Total TPS','value'=> Auth::user()->kecamatan ? Auth::user()->kecamatan->desas->sum(fn($d) => $d->tps->count()) : '-','sub'=>'titik pemungutan'],
        ['label'=>'Jumlah PPS','value'=> Auth::user()->kecamatan ? Auth::user()->kecamatan->desas->count() : '-','sub'=>'panitia aktif'],
    ] as $stat)
    <div class="bg-[#141414] p-7">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-3">{{ $stat['label'] }}</p>
        <p class="font-display text-3xl tracking-wide" style="color:#F4A261">{{ $stat['value'] }}</p>
        <p class="text-xs text-gray-600 mt-1">{{ $stat['sub'] }}</p>
    </div>
    @endforeach
</div>

{{-- Menu --}}
<p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-4 pb-3 border-b border-gray-800">// Menu Utama</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-px bg-gray-800">

    <a href="{{ route('dokumen.ppk') }}"
       class="relative bg-[#141414] p-7 hover:bg-[#1a1a1a] transition group block">
        <div class="absolute top-0 left-0 w-[3px] h-full" style="background:#F4A261"></div>
        <span class="absolute top-7 right-7 text-gray-800 group-hover:text-gray-600 transition">→</span>
        <div class="text-3xl mb-4">📊</div>
        <p class="font-semibold text-sm mb-1">Rekap Dokumen</p>
        <p class="text-xs text-gray-500 leading-relaxed">Lihat dan download dokumen dari seluruh TPS di kecamatan.</p>
    </a>

    <a href="{{ route('ppk.data-pps') }}"
       class="relative bg-[#141414] p-7 hover:bg-[#1a1a1a] transition group block">
        <div class="absolute top-0 left-0 w-[3px] h-full" style="background:#F4A261"></div>
        <span class="absolute top-7 right-7 text-gray-800 group-hover:text-gray-600 transition">→</span>
        <div class="text-3xl mb-4">🏘️</div>
        <p class="font-semibold text-sm mb-1">Data PPS</p>
        <p class="text-xs text-gray-500 leading-relaxed">Pantau status desa dan lihat dashboard tiap PPS di kecamatan.</p>
    </a>

</div>
@endsection