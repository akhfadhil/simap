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
        $totalPengguna   = \App\Models\User::count();
        $totalTps        = \App\Models\Tps::count();
        $totalKecamatan  = \App\Models\Kecamatan::count();
        $totalSeharusnya = ($totalTps + $totalKecamatan) * 5;
        $totalVerif      = \App\Models\Dokumen::where('status','terverifikasi')->count();
        $persenVerif     = $totalSeharusnya > 0 ? round(($totalVerif / $totalSeharusnya) * 100) : 0;
        $tpsSelesai      = \App\Models\RekapHeader::select('tps_id')
                            ->where('status','final')
                            ->groupBy('tps_id')
                            ->havingRaw('COUNT(DISTINCT jenis) = 5')
                            ->count();
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
        <p class="font-display text-4xl text-red-600 tracking-wide">{{ $totalVerif }}/{{ $totalSeharusnya }}</p>
        <div class="mt-auto pt-3">
            <div class="flex items-center gap-2 mb-1">
                <div class="flex-1 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                    <div class="h-1.5 rounded-full bg-red-500 transition-all" style="width:{{ $persenVerif }}%"></div>
                </div>
                <span class="text-xs dark:text-gray-500 text-gray-400">{{ $persenVerif }}%</span>
            </div>
            <p class="text-xs dark:text-gray-500 text-gray-400">dokumen terunggah</p>
        </div>
    </div>

    <div class="dark:bg-gray-800 bg-white rounded-xl p-6 border dark:border-gray-700 border-gray-200 shadow-sm flex flex-col">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-3 font-semibold">Rekap Finalisasi</p>
        <p class="font-display text-4xl text-red-600 tracking-wide">{{ $tpsSelesai }}/{{ $totalTps }}</p>
        <div class="mt-auto pt-3">
            <div class="flex items-center gap-2 mb-1">
                <div class="flex-1 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                    <div class="h-1.5 rounded-full bg-red-500 transition-all"
                         style="width:{{ $totalTps > 0 ? round(($tpsSelesai/$totalTps)*100) : 0 }}%"></div>
                </div>
                <span class="text-xs dark:text-gray-500 text-gray-400">
                    {{ $totalTps > 0 ? round(($tpsSelesai/$totalTps)*100) : 0 }}%
                </span>
            </div>
            <p class="text-xs dark:text-gray-500 text-gray-400">TPS selesai semua rekap</p>
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