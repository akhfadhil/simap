@extends('layouts.app')
@section('title', 'Dashboard KPPS')

@section('content')
<div class="mb-10">
    <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-2">// Kelompok Penyelenggara Pemungutan Suara</p>
    <h1 class="font-display text-5xl tracking-[2px]" style="color:#A8DADC">DASHBOARD KPPS</h1>
    <p class="text-gray-500 text-sm mt-2">Input dan laporan data pemungutan suara di TPS.</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-px bg-gray-800 mb-8">
    @php
        $tps      = Auth::user()->tps;
        $uploaded = $tps ? \App\Models\Dokumen::where('tps_id', $tps->id)->count() : 0;
        $terverif = $tps ? \App\Models\Dokumen::where('tps_id', $tps->id)->where('status','terverifikasi')->count() : 0;
    @endphp
    <div class="bg-[#141414] p-7">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-3">TPS</p>
        <p class="font-display text-3xl tracking-wide" style="color:#A8DADC">{{ $tps->nama ?? '-' }}</p>
        <p class="text-xs text-gray-600 mt-1">{{ $tps->desa->nama ?? '-' }}</p>
    </div>
    <div class="bg-[#141414] p-7">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-3">Kecamatan</p>
        <p class="font-display text-3xl tracking-wide" style="color:#A8DADC">{{ $tps->desa->kecamatan->nama ?? '-' }}</p>
        <p class="text-xs text-gray-600 mt-1">wilayah tugas</p>
    </div>
    <div class="bg-[#141414] p-7">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-3">Dokumen Upload</p>
        <p class="font-display text-3xl tracking-wide" style="color:#A8DADC">{{ $uploaded }}/5</p>
        <p class="text-xs text-gray-600 mt-1">sudah diupload</p>
    </div>
    <div class="bg-[#141414] p-7">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-3">Terverifikasi</p>
        <p class="font-display text-3xl tracking-wide" style="color:#A8DADC">{{ $terverif }}/5</p>
        <p class="text-xs text-gray-600 mt-1">oleh PPS</p>
    </div>
</div>

{{-- Menu --}}
<p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-4 pb-3 border-b border-gray-800">// Menu Utama</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-px bg-gray-800">

    <a href="{{ route('dokumen.upload') }}"
       class="relative bg-[#141414] p-7 hover:bg-[#1a1a1a] transition group block">
        <div class="absolute top-0 left-0 w-[3px] h-full" style="background:#A8DADC"></div>
        <span class="absolute top-7 right-7 text-gray-800 group-hover:text-gray-600 transition">→</span>
        <div class="text-3xl mb-4">📄</div>
        <p class="font-semibold text-sm mb-1">Upload Dokumen</p>
        <p class="text-xs text-gray-500 leading-relaxed">Upload 5 jenis dokumen PDF hasil pemungutan suara (PPWP, DPR RI, DPD, DPRD Prov, DPRD Kab).</p>
    </a>

    <div class="relative bg-[#141414] p-7 hover:bg-[#1a1a1a] transition group cursor-not-allowed opacity-50">
        <div class="absolute top-0 left-0 w-[3px] h-full" style="background:#A8DADC"></div>
        <div class="text-3xl mb-4">📊</div>
        <p class="font-semibold text-sm mb-1">Isi Data Rekapitulasi</p>
        <p class="text-xs text-gray-500 leading-relaxed">Input data hasil suara ke dalam tabel rekapitulasi (PPWP, DPR RI, DPD, DPRD Prov, DPRD Kab).</p>
    </div>

</div>
@endsection