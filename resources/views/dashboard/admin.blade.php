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
    @php
        $totalPengguna     = \App\Models\User::count();
        $totalTps          = \App\Models\Tps::count();
        $aktifJenis        = \App\Models\PemiluSetting::aktif();
        $totalPemiluAktif  = count($aktifJenis);
        $targetPemiluTps   = $totalTps * $totalPemiluAktif;
        $dokumenJenisAktif = array_map('strtoupper', $aktifJenis);

        $totalDokumenMasuk = \App\Models\Dokumen::where('level', 'tps')
                            ->whereIn('jenis', $dokumenJenisAktif)
                            ->count();
        $persenDokumen     = $targetPemiluTps > 0 ? min(100, round(($totalDokumenMasuk / $targetPemiluTps) * 100)) : 0;

        $totalRekapFinal   = \App\Models\RekapHeader::select('tps_id')
                            ->where('status', 'final')
                            ->whereIn('jenis', $aktifJenis)
                            ->groupBy('tps_id')
                            ->havingRaw('COUNT(DISTINCT jenis) = ?', [$totalPemiluAktif])
                            ->count();
        $persenRekap       = $totalTps > 0 ? min(100, round(($totalRekapFinal / $totalTps) * 100)) : 0;
    @endphp

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm flex flex-col">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Total Pengguna</p>
        <p class="font-display text-4xl text-red-600 tracking-wide">{{ $totalPengguna }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-auto pt-3">terdaftar di sistem</p>
    </div>

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm flex flex-col">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Wilayah TPS</p>
        <p class="font-display text-4xl text-red-600 tracking-wide">{{ $totalTps }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-auto pt-3">titik pemungutan suara</p>
    </div>

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm flex flex-col">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Dokumen Masuk</p>
        <p class="font-display text-4xl text-red-600 tracking-wide">{{ $totalDokumenMasuk }}/{{ $targetPemiluTps }}</p>
        <div class="mt-auto pt-3">
            <div class="flex items-center gap-2 mb-1">
                <div class="flex-1 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                    <div class="h-1.5 rounded-full bg-red-500 transition-all" style="width:{{ $persenDokumen }}%"></div>
                </div>
                <span class="text-xs dark:text-gray-500 text-gray-400">{{ $persenDokumen }}%</span>
            </div>
            <p class="text-xs dark:text-gray-500 text-gray-400">TPS x {{ $totalPemiluAktif }} pemilu aktif</p>
        </div>
    </div>

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm flex flex-col">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Rekap Finalisasi</p>
        <p class="font-display text-4xl text-red-600 tracking-wide">{{ $totalRekapFinal }}/{{ $totalTps }}</p>
        <div class="mt-auto pt-3">
            <div class="flex items-center gap-2 mb-1">
                <div class="flex-1 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                    <div class="h-1.5 rounded-full bg-red-500 transition-all"
                         style="width:{{ $persenRekap }}%"></div>
                </div>
                <span class="text-xs dark:text-gray-500 text-gray-400">
                    {{ $persenRekap }}%
                </span>
            </div>
            <p class="text-xs dark:text-gray-500 text-gray-400">TPS final semua pemilu aktif</p>
        </div>
    </div>
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

    <a href="{{ route('admin.rekap.chart') }}"
       class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-red-600 dark:border-gray-700 border-gray-200 hover:shadow-md transition group block">
        <span class="float-right dark:text-gray-600 text-gray-300 group-hover:text-red-500 transition text-lg">→</span>
        <div class="text-3xl mb-4">📊</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Grafik & Statistik</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed">Visualisasi data rekap suara per kecamatan, desa, hingga TPS.</p>
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

{{-- Tools Admin
<p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-4 pb-3 border-b dark:border-gray-800 border-gray-200 font-semibold mt-8">// Tools</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-amber-500 dark:border-gray-700 border-gray-200 shadow-sm">
        <div class="text-3xl mb-3">💾</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Backup Dokumen</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed mb-4">Pindahkan file PDF dokumen yang sudah terverifikasi ke folder backup eksternal.</p>
        <form method="POST" action="{{ route('admin.tools.backup') }}">
            @csrf
            <button class="px-4 py-2 text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition">
                ▶ Jalankan Backup
            </button>
        </form>
        @if(session('backup_result'))
        <p class="text-xs mt-3 dark:text-gray-400 text-gray-500">{{ session('backup_result') }}</p>
        @endif
    </div>

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border-l-4 border border-l-blue-500 dark:border-gray-700 border-gray-200 shadow-sm">
        <div class="text-3xl mb-3">🌱</div>
        <p class="font-semibold text-sm mb-1 dark:text-gray-100 text-gray-800">Seed Partai</p>
        <p class="text-xs dark:text-gray-500 text-gray-500 leading-relaxed mb-4">Isi otomatis 18 partai untuk DPR RI, DPRD Prov, dan semua dapil DPRD Kab. Aman dijalankan berulang.</p>
        <form method="POST" action="{{ route('admin.tools.seed-partai') }}">
            @csrf
            <button class="px-4 py-2 text-xs font-semibold bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
                ▶ Jalankan Seeder
            </button>
        </form>
        @if(session('seed_result'))
        <p class="text-xs mt-3 dark:text-gray-400 text-gray-500">{{ session('seed_result') }}</p>
        @endif
    </div>

</div>
--}}
@endsection
