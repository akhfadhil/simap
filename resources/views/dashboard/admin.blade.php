@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('content')
<div class="mb-10">
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// Administrator</p>
    <h1 class="font-display text-5xl tracking-[2px] text-red-600">DASHBOARD</h1>
    <p class="dark:text-gray-400 text-gray-500 text-sm mt-2">Kelola seluruh sistem, wilayah, pengguna, dan dokumen pemilu.</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @foreach([
        ['label'=>'Total Pengguna','value'=>'128','sub'=>'aktif di sistem'],
        ['label'=>'Wilayah TPS','value'=>'342','sub'=>'terdaftar'],
        ['label'=>'Dokumen Masuk','value'=>'89%','sub'=>'sudah diverifikasi'],
        ['label'=>'Log Aktivitas','value'=>'2.4K','sub'=>'hari ini'],
    ] as $stat)
    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">{{ $stat['label'] }}</p>
        <p class="font-display text-4xl text-red-600 tracking-wide">{{ $stat['value'] }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">{{ $stat['sub'] }}</p>
    </div>
    @endforeach
</div>

{{-- Menu --}}
<p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-4 pb-3 border-b dark:border-gray-800 border-gray-200 font-semibold">// Menu Utama</p>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

    <a href="{{ route('admin.users.index') }}"
       class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-red-600 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-red-500 transition text-lg">→</span>
        <div class="text-3xl mb-4">👥</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Manajemen Pengguna</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Tambah akun PPK, PPS, KPPS dan assign wilayah.</p>
    </a>

    <a href="{{ route('admin.kecamatan.index') }}"
       class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-red-600 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-red-500 transition text-lg">→</span>
        <div class="text-3xl mb-4">🗺️</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Kelola Kecamatan</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Tambah dan edit data kecamatan.</p>
    </a>

    <a href="{{ route('admin.desa.index') }}"
       class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-red-600 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-red-500 transition text-lg">→</span>
        <div class="text-3xl mb-4">🏘️</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Kelola Desa</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Tambah dan edit data desa per kecamatan.</p>
    </a>

    <a href="{{ route('admin.tps.index') }}"
       class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-red-600 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-red-500 transition text-lg">→</span>
        <div class="text-3xl mb-4">🗳️</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Kelola TPS</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Tambah dan edit TPS per desa.</p>
    </a>

    <a href="{{ route('dokumen.admin') }}"
       class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-red-600 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-red-500 transition text-lg">→</span>
        <div class="text-3xl mb-4">📁</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Rekap Dokumen</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Lihat dan download semua dokumen dengan filter kecamatan & desa.</p>
    </a>

    <a href="{{ route('admin.rekap.index') }}"
    class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-red-600 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-red-500 transition text-lg">→</span>
        <div class="text-3xl mb-4">📈</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Rekapitulasi Data</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Lihat rekap suara dari semua kecamatan dan TPS.</p>
    </a>

    <a href="{{ route('admin.setup.index') }}"
    class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-red-600 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-red-500 transition text-lg">→</span>
        <div class="text-3xl mb-4">⚙️</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Setup Data Pemilu</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Input paslon, calon DPD, partai, dan caleg untuk form rekap.</p>
    </a>

</div>
@endsection