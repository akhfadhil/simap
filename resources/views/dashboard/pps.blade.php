@extends('layouts.app')
@section('title', 'Dashboard PPS')

@section('content')
<div class="mb-10">
    <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-2">// Panitia Pemungutan Suara</p>
    <h1 class="font-display text-5xl tracking-[2px]" style="color:#2EC4B6">DASHBOARD PPS</h1>
    <p class="text-gray-500 text-sm mt-2">Rekap dan verifikasi dokumen TPS di wilayah desa.</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-px bg-gray-800 mb-8">
    @php
        $desa      = Auth::user()->desa;
        $tpsList   = $desa ? $desa->tps : collect();
        $totalTps  = $tpsList->count();
        $sudahUpload = $tpsList->filter(fn($t) => $t->dokumens->count() > 0)->count();
        $terverif    = $tpsList->sum(fn($t) => $t->dokumens->where('status','terverifikasi')->count());
    @endphp
    <div class="bg-[#141414] p-7">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-3">Desa</p>
        <p class="font-display text-3xl tracking-wide" style="color:#2EC4B6">{{ $desa->nama ?? '-' }}</p>
        <p class="text-xs text-gray-600 mt-1">{{ $desa->kecamatan->nama ?? '-' }}</p>
    </div>
    <div class="bg-[#141414] p-7">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-3">Jumlah TPS</p>
        <p class="font-display text-3xl tracking-wide" style="color:#2EC4B6">{{ $totalTps }}</p>
        <p class="text-xs text-gray-600 mt-1">di desa ini</p>
    </div>
    <div class="bg-[#141414] p-7">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-3">Sudah Upload</p>
        <p class="font-display text-3xl tracking-wide" style="color:#2EC4B6">{{ $sudahUpload }}</p>
        <p class="text-xs text-gray-600 mt-1">dari {{ $totalTps }} TPS</p>
    </div>
    <div class="bg-[#141414] p-7">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-3">Terverifikasi</p>
        <p class="font-display text-3xl tracking-wide" style="color:#2EC4B6">{{ $terverif }}</p>
        <p class="text-xs text-gray-600 mt-1">dokumen</p>
    </div>
</div>

{{-- Menu --}}
<p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-4 pb-3 border-b border-gray-800">// Menu Utama</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-px bg-gray-800">

    <a href="{{ route('dokumen.pps') }}"
       class="relative bg-[#141414] p-7 hover:bg-[#1a1a1a] transition group block">
        <div class="absolute top-0 left-0 w-[3px] h-full" style="background:#2EC4B6"></div>
        <span class="absolute top-7 right-7 text-gray-800 group-hover:text-gray-600 transition">→</span>
        <div class="text-3xl mb-4">✅</div>
        <p class="font-semibold text-sm mb-1">Verifikasi Dokumen</p>
        <p class="text-xs text-gray-500 leading-relaxed">Preview, download, dan verifikasi dokumen yang diupload KPPS dari tiap TPS.</p>
    </a>

    <a href="{{ route('pps.data-tps') }}"
       class="relative bg-[#141414] p-7 hover:bg-[#1a1a1a] transition group block">
        <div class="absolute top-0 left-0 w-[3px] h-full" style="background:#2EC4B6"></div>
        <span class="absolute top-7 right-7 text-gray-800 group-hover:text-gray-600 transition">→</span>
        <div class="text-3xl mb-4">🗳️</div>
        <p class="font-semibold text-sm mb-1">Data TPS</p>
        <p class="text-xs text-gray-500 leading-relaxed">Pantau status TPS dan lihat dashboard tiap KPPS di desa.</p>
    </a>

</div>

@endsection