@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('content')
<div class="mb-10">
    <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-2">// Administrator</p>
    <h1 class="font-display text-5xl tracking-[2px] text-brand">PUSAT KONTROL</h1>
    <p class="text-gray-500 text-sm mt-2">Kelola seluruh sistem, wilayah, pengguna, dan dokumen pemilu.</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-px bg-gray-800 mb-8">
    @foreach([
        ['label'=>'Total Pengguna','value'=>'128','sub'=>'aktif di sistem'],
        ['label'=>'Wilayah TPS','value'=>'342','sub'=>'terdaftar'],
        ['label'=>'Dokumen Masuk','value'=>'89%','sub'=>'sudah diverifikasi'],
        ['label'=>'Log Aktivitas','value'=>'2.4K','sub'=>'hari ini'],
    ] as $stat)
    <div class="bg-[#141414] p-7">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-3">{{ $stat['label'] }}</p>
        <p class="font-display text-4xl text-brand tracking-wide">{{ $stat['value'] }}</p>
        <p class="text-xs text-gray-600 mt-1">{{ $stat['sub'] }}</p>
    </div>
    @endforeach
</div>

{{-- Menu --}}
<p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-4 pb-3 border-b border-gray-800">// Menu Utama</p>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-px bg-gray-800">

    <a href="{{ route('admin.users.index') }}"
       class="relative bg-[#141414] p-7 hover:bg-[#1a1a1a] transition group block">
        <div class="absolute top-0 left-0 w-[3px] h-full bg-brand"></div>
        <span class="absolute top-7 right-7 text-gray-800 group-hover:text-gray-600 transition">→</span>
        <div class="text-3xl mb-4">👥</div>
        <p class="font-semibold text-sm mb-1">Manajemen Pengguna</p>
        <p class="text-xs text-gray-500 leading-relaxed">Tambah akun PPK, PPS, KPPS dan assign wilayah.</p>
    </a>

    <a href="{{ route('admin.kecamatan.index') }}"
       class="relative bg-[#141414] p-7 hover:bg-[#1a1a1a] transition group block">
        <div class="absolute top-0 left-0 w-[3px] h-full bg-brand"></div>
        <span class="absolute top-7 right-7 text-gray-800 group-hover:text-gray-600 transition">→</span>
        <div class="text-3xl mb-4">🗺️</div>
        <p class="font-semibold text-sm mb-1">Kelola Kecamatan</p>
        <p class="text-xs text-gray-500 leading-relaxed">Tambah dan edit data kecamatan.</p>
    </a>

    <a href="{{ route('admin.desa.index') }}"
       class="relative bg-[#141414] p-7 hover:bg-[#1a1a1a] transition group block">
        <div class="absolute top-0 left-0 w-[3px] h-full bg-brand"></div>
        <span class="absolute top-7 right-7 text-gray-800 group-hover:text-gray-600 transition">→</span>
        <div class="text-3xl mb-4">🏘️</div>
        <p class="font-semibold text-sm mb-1">Kelola Desa</p>
        <p class="text-xs text-gray-500 leading-relaxed">Tambah dan edit data desa per kecamatan.</p>
    </a>

    <a href="{{ route('admin.tps.index') }}"
       class="relative bg-[#141414] p-7 hover:bg-[#1a1a1a] transition group block">
        <div class="absolute top-0 left-0 w-[3px] h-full bg-brand"></div>
        <span class="absolute top-7 right-7 text-gray-800 group-hover:text-gray-600 transition">→</span>
        <div class="text-3xl mb-4">🗳️</div>
        <p class="font-semibold text-sm mb-1">Kelola TPS</p>
        <p class="text-xs text-gray-500 leading-relaxed">Tambah dan edit TPS per desa.</p>
    </a>

    <a href="{{ route('dokumen.admin') }}"
       class="relative bg-[#141414] p-7 hover:bg-[#1a1a1a] transition group block">
        <div class="absolute top-0 left-0 w-[3px] h-full bg-brand"></div>
        <span class="absolute top-7 right-7 text-gray-800 group-hover:text-gray-600 transition">→</span>
        <div class="text-3xl mb-4">📁</div>
        <p class="font-semibold text-sm mb-1">Rekap Dokumen</p>
        <p class="text-xs text-gray-500 leading-relaxed">Lihat dan download semua dokumen dengan filter kecamatan & desa.</p>
    </a>

    <div class="relative bg-[#141414] p-7 hover:bg-[#1a1a1a] transition group cursor-not-allowed opacity-50">
        <div class="absolute top-0 left-0 w-[3px] h-full bg-brand"></div>
        <div class="text-3xl mb-4">📤</div>
        <p class="font-semibold text-sm mb-1">Export Data</p>
        <p class="text-xs text-gray-500 leading-relaxed">Ekspor data pemilu dalam format CSV.</p>
    </div>

</div>
@endsection