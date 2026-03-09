@extends('layouts.app')
@section('title', 'Rekap ' . \App\Models\RekapHeader::JENIS_LABELS[$jenis])

@section('content')

{{-- Header --}}
<div class="mb-8 flex items-end justify-between gap-4">
    <div>
        <a href="{{ route('admin.rekap.index') }}"
           class="inline-flex items-center gap-2 text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition font-medium mb-4">
            ← Kembali
        </a>
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// Admin — Rekapitulasi</p>
        <h1 class="font-display text-4xl tracking-[2px] text-red-600">
            {{ strtoupper(\App\Models\RekapHeader::JENIS_LABELS[$jenis]) }}
        </h1>
    </div>
    <button onclick="openExportModal()"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold bg-red-500 hover:bg-red-600 text-white transition flex-shrink-0">
        ↓ Export Excel
    </button>
</div>

{{-- Summary cards --}}
@php
    $totalDpt    = $rekaps->sum(fn($r) => $r->dpt_lk + $r->dpt_pr);
    $totalHadir  = $rekaps->sum(fn($r) => $r->pengguna_dpt_lk + $r->pengguna_dpt_pr + $r->pengguna_dptb_lk + $r->pengguna_dptb_pr + $r->pengguna_dpk_lk + $r->pengguna_dpk_pr);
    $totalTdkSah = $rekaps->sum('suara_tidak_sah');
    $totalFinal  = $rekaps->where('status','final')->count();
    $totalRekap  = $rekaps->count();

    $rows1 = [
        ['label'=>'DPT Laki-laki',            'field'=>'dpt_lk'],
        ['label'=>'DPT Perempuan',             'field'=>'dpt_pr'],
        ['label'=>'DPT Jumlah',                'sum'=>['dpt_lk','dpt_pr'], 'bold'=>true],
        ['label'=>'Pengguna DPT LK',           'field'=>'pengguna_dpt_lk'],
        ['label'=>'Pengguna DPT PR',           'field'=>'pengguna_dpt_pr'],
        ['label'=>'Pengguna DPTB LK',          'field'=>'pengguna_dptb_lk'],
        ['label'=>'Pengguna DPTB PR',          'field'=>'pengguna_dptb_pr'],
        ['label'=>'Pengguna DPK LK',           'field'=>'pengguna_dpk_lk'],
        ['label'=>'Pengguna DPK PR',           'field'=>'pengguna_dpk_pr'],
        ['label'=>'Total Pengguna Hak Pilih',  'sum'=>['pengguna_dpt_lk','pengguna_dpt_pr','pengguna_dptb_lk','pengguna_dptb_pr','pengguna_dpk_lk','pengguna_dpk_pr'], 'bold'=>true],
    ];
    $rows2 = [
        ['label'=>'Surat Suara Diterima',  'field'=>'ss_diterima'],
        ['label'=>'Surat Suara Digunakan', 'field'=>'ss_digunakan'],
        ['label'=>'Surat Suara Rusak',     'field'=>'ss_rusak'],
        ['label'=>'Surat Suara Sisa',      'field'=>'ss_sisa', 'bold'=>true],
    ];
    $rows3 = [
        ['label'=>'Disabilitas Laki-laki', 'field'=>'disabilitas_lk'],
        ['label'=>'Disabilitas Perempuan', 'field'=>'disabilitas_pr'],
        ['label'=>'Disabilitas Jumlah',    'sum'=>['disabilitas_lk','disabilitas_pr'], 'bold'=>true],
    ];

    // Helper: sum nilai field untuk semua TPS di kecamatan
    $getKecVal = function($kecamatan, $row) use ($rekaps) {
        $tpsIds = $kecamatan->desas->flatMap(fn($d) => $d->tps->pluck('id'))->toArray();
        $kecRekaps = $rekaps->whereIn('tps_id', $tpsIds);
        return $kecRekaps->sum(fn($r) => isset($row['field'])
            ? ($r->{$row['field']} ?? 0)
            : collect($row['sum'])->sum(fn($f) => $r->$f ?? 0));
    };
@endphp

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="dark:bg-gray-800 bg-white rounded-xl p-5 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">Total DPT</p>
        <p class="font-display text-3xl text-red-600">{{ number_format($totalDpt) }}</p>
    </div>
    <div class="dark:bg-gray-800 bg-white rounded-xl p-5 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">Total Hadir</p>
        <p class="font-display text-3xl text-red-600">{{ number_format($totalHadir) }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">{{ $totalDpt > 0 ? round(($totalHadir/$totalDpt)*100,1) : 0 }}% partisipasi</p>
    </div>
    <div class="dark:bg-gray-800 bg-white rounded-xl p-5 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">Suara Tidak Sah</p>
        <p class="font-display text-3xl text-red-600">{{ number_format($totalTdkSah) }}</p>
    </div>
    <div class="dark:bg-gray-800 bg-white rounded-xl p-5 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">TPS Terisi</p>
        <p class="font-display text-3xl text-red-600">{{ $totalFinal }}/{{ $totalRekap }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">difinalisasi</p>
    </div>
</div>

{{-- ══════════════════════════════════════
     REKAP TOTAL KABUPATEN (kolom = kecamatan)
══════════════════════════════════════ --}}
<div class="mb-2">
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold mb-3">// Rekap Total Kabupaten</p>
</div>

@foreach([
    ['title'=>'Section I — DPT & Pengguna Hak Pilih', 'rows'=>$rows1],
    ['title'=>'Section II — Surat Suara',              'rows'=>$rows2],
    ['title'=>'Section III — Pemilih Disabilitas',     'rows'=>$rows3],
] as $sec)
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-2.5 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/50 bg-gray-50">
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// {{ $sec['title'] }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b dark:border-gray-700 border-gray-200">
                    <th class="text-left px-5 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold min-w-56">Keterangan</th>
                    @foreach($kecamatans as $kec)
                    <th class="text-center px-3 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">{{ $kec->nama }}</th>
                    @endforeach
                    <th class="text-center px-3 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                </tr>
            </thead>
            <tbody>
            @foreach($sec['rows'] as $row)
            @php $rowTotal = 0; $isBold = $row['bold'] ?? false; @endphp
            <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 {{ $isBold ? 'dark:bg-gray-700/20 bg-gray-50' : 'dark:hover:bg-gray-750 hover:bg-gray-50' }}">
                <td class="px-5 py-2 text-sm {{ $isBold ? 'font-bold dark:text-gray-200 text-gray-800' : 'dark:text-gray-300 text-gray-600' }}">{{ $row['label'] }}</td>
                @foreach($kecamatans as $kec)
                @php $val = $getKecVal($kec, $row); $rowTotal += $val; @endphp
                <td class="px-3 py-2 text-center {{ $isBold ? 'font-bold dark:text-gray-200 text-gray-700' : 'dark:text-gray-400 text-gray-500' }}">{{ number_format($val) }}</td>
                @endforeach
                <td class="px-3 py-2 text-center font-bold text-red-500">{{ number_format($rowTotal) }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endforeach

{{-- Section IV Total --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-2.5 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/50 bg-gray-50">
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Section IV — Perolehan Suara</p>
    </div>
    @if(in_array($jenis, ['ppwp','dpd']))
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b dark:border-gray-700 border-gray-200">
                    <th class="text-left px-5 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold min-w-48">Calon</th>
                    @foreach($kecamatans as $kec)
                    <th class="text-center px-3 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">{{ $kec->nama }}</th>
                    @endforeach
                    <th class="text-center px-3 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                </tr>
            </thead>
            <tbody>
            @foreach($master['calons'] as $calon)
            @php $rowTotal = 0; $name = $jenis === 'ppwp' ? $calon->nama_paslon : $calon->nama_calon; @endphp
            <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 dark:hover:bg-gray-750 hover:bg-gray-50">
                <td class="px-5 py-2.5 text-sm dark:text-gray-200 text-gray-700">
                    <span class="text-xs dark:text-gray-500 text-gray-400 mr-1">{{ $calon->nomor_urut }}.</span>{{ $name }}
                </td>
                @foreach($kecamatans as $kec)
                @php
                    $kecTpsIds = $kec->desas->flatMap(fn($d) => $d->tps->pluck('id'))->toArray();
                    $val = $rekaps->whereIn('tps_id', $kecTpsIds)->sum(fn($r) => $jenis === 'ppwp'
                        ? ($r->ppwpSuaras->firstWhere('calon_id', $calon->id)?->suara ?? 0)
                        : ($r->dpdSuaras->firstWhere('calon_id', $calon->id)?->suara ?? 0));
                    $rowTotal += $val;
                @endphp
                <td class="px-3 py-2.5 text-center dark:text-gray-400 text-gray-500">{{ number_format($val) }}</td>
                @endforeach
                <td class="px-3 py-2.5 text-center font-bold text-red-500">{{ number_format($rowTotal) }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @else
    @foreach($master['partais'] as $partai)
    <div class="border-b dark:border-gray-700 border-gray-200 last:border-0">
        <div class="px-5 py-2 dark:bg-gray-700/30 bg-gray-50">
            <p class="text-xs font-bold dark:text-gray-300 text-gray-700">{{ $partai->nomor_urut }}. {{ $partai->nama_partai }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-700 border-gray-100">
                        <th class="text-left px-5 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold min-w-48">Caleg</th>
                        @foreach($kecamatans as $kec)
                        <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">{{ $kec->nama }}</th>
                        @endforeach
                        <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody>
                @php $partaiRowTotal = 0; @endphp
                <tr class="border-b dark:border-gray-700 border-gray-100 dark:bg-gray-700/20 bg-gray-50">
                    <td class="px-5 py-2 text-xs font-bold dark:text-gray-300 text-gray-700 uppercase">Suara Partai</td>
                    @foreach($kecamatans as $kec)
                    @php $kecTpsIds = $kec->desas->flatMap(fn($d) => $d->tps->pluck('id'))->toArray(); $spKec = $rekaps->whereIn('tps_id', $kecTpsIds)->sum(fn($r) => $r->partaiSuaras->firstWhere('partai_id', $partai->id)?->suara ?? 0); $partaiRowTotal += $spKec; @endphp
                    <td class="px-3 py-2 text-center dark:text-gray-400 text-gray-500">{{ number_format($spKec) }}</td>
                    @endforeach
                    <td class="px-3 py-2 text-center font-bold text-red-500">{{ number_format($partaiRowTotal) }}</td>
                </tr>
                @foreach($partai->calegs as $caleg)
                @php $calegRowTotal = 0; @endphp
                <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 dark:hover:bg-gray-750 hover:bg-gray-50">
                    <td class="px-5 py-2"><div class="flex items-center gap-2"><span class="text-xs dark:text-gray-500 text-gray-400 w-4">{{ $caleg->nomor_urut }}.</span><span class="text-sm dark:text-gray-200 text-gray-700">{{ $caleg->nama_caleg }}</span></div></td>
                    @foreach($kecamatans as $kec)
                    @php $kecTpsIds = $kec->desas->flatMap(fn($d) => $d->tps->pluck('id'))->toArray(); $scKec = $rekaps->whereIn('tps_id', $kecTpsIds)->sum(fn($r) => $r->calegSuaras->firstWhere('caleg_id', $caleg->id)?->suara ?? 0); $calegRowTotal += $scKec; @endphp
                    <td class="px-3 py-2 text-center dark:text-gray-400 text-gray-500">{{ number_format($scKec) }}</td>
                    @endforeach
                    <td class="px-3 py-2 text-center font-bold text-teal-400">{{ number_format($calegRowTotal) }}</td>
                </tr>
                @endforeach
                @php $grandTotal = 0; @endphp
                <tr class="border-t-2 dark:border-gray-600 border-gray-300 dark:bg-gray-700/30 bg-gray-50">
                    <td class="px-5 py-2 text-xs font-bold dark:text-gray-300 text-gray-700 uppercase">Total Suara Sah</td>
                    @foreach($kecamatans as $kec)
                    @php $kecTpsIds = $kec->desas->flatMap(fn($d) => $d->tps->pluck('id'))->toArray(); $colTotal = $rekaps->whereIn('tps_id', $kecTpsIds)->sum(fn($r) => ($r->partaiSuaras->firstWhere('partai_id', $partai->id)?->suara ?? 0) + $r->calegSuaras->whereIn('caleg_id', $partai->calegs->pluck('id'))->sum('suara')); $grandTotal += $colTotal; @endphp
                    <td class="px-3 py-2 text-center font-bold text-teal-400">{{ number_format($colTotal) }}</td>
                    @endforeach
                    <td class="px-3 py-2 text-center font-bold text-teal-400">{{ number_format($grandTotal) }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
    @endif
</div>

{{-- Section V Total --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden mb-8">
    <div class="px-5 py-2.5 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/50 bg-gray-50">
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Section V — Suara Sah, Tidak Sah & Total</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b dark:border-gray-700 border-gray-200">
                    <th class="text-left px-5 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold min-w-56">Keterangan</th>
                    @foreach($kecamatans as $kec)
                    <th class="text-center px-3 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">{{ $kec->nama }}</th>
                    @endforeach
                    <th class="text-center px-3 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                </tr>
            </thead>
            <tbody>
            @foreach([['label'=>'Jumlah Suara Sah','field'=>'suara_sah'],['label'=>'Jumlah Suara Tidak Sah','field'=>'suara_tidak_sah']] as $row)
            @php $rowTotal = 0; @endphp
            <tr class="border-b dark:border-gray-700 border-gray-100 dark:hover:bg-gray-750 hover:bg-gray-50">
                <td class="px-5 py-2 text-sm dark:text-gray-300 text-gray-600">{{ $row['label'] }}</td>
                @foreach($kecamatans as $kec)
                @php $kecTpsIds = $kec->desas->flatMap(fn($d) => $d->tps->pluck('id'))->toArray(); $val = $rekaps->whereIn('tps_id', $kecTpsIds)->sum($row['field']); $rowTotal += $val; @endphp
                <td class="px-3 py-2 text-center dark:text-gray-400 text-gray-500">{{ number_format($val) }}</td>
                @endforeach
                <td class="px-3 py-2 text-center font-bold text-red-500">{{ number_format($rowTotal) }}</td>
            </tr>
            @endforeach
            @php $rowTotalAll = 0; @endphp
            <tr class="dark:bg-gray-700/20 bg-gray-50">
                <td class="px-5 py-2 text-sm font-bold dark:text-gray-200 text-gray-800">Jumlah Seluruh Suara</td>
                @foreach($kecamatans as $kec)
                @php $kecTpsIds = $kec->desas->flatMap(fn($d) => $d->tps->pluck('id'))->toArray(); $val = $rekaps->whereIn('tps_id', $kecTpsIds)->sum(fn($r) => $r->suara_sah + $r->suara_tidak_sah); $rowTotalAll += $val; @endphp
                <td class="px-3 py-2 text-center font-bold dark:text-gray-200 text-gray-700">{{ number_format($val) }}</td>
                @endforeach
                <td class="px-3 py-2 text-center font-bold text-red-500">{{ number_format($rowTotalAll) }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- ══════════════════════════════════════
     DETAIL PER KECAMATAN (accordion)
══════════════════════════════════════ --}}
<div class="mb-2">
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold mb-3">// Detail Per Kecamatan</p>
</div>

@foreach($kecamatans as $kecamatan)
@php
    $kecTpsIds = $kecamatan->desas->flatMap(fn($d) => $d->tps->pluck('id'))->toArray();
    $kecRekaps = $rekaps->whereIn('tps_id', $kecTpsIds);
    $kecFinal  = $kecRekaps->where('status','final')->count();
@endphp
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm mb-4 overflow-hidden">

    {{-- Header accordion kecamatan --}}
    <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700 border-gray-200 cursor-pointer dark:hover:bg-gray-750 hover:bg-gray-50 transition"
         onclick="toggleKec({{ $kecamatan->id }})">
        <div>
            <p class="font-semibold text-sm dark:text-gray-100 text-gray-800">{{ $kecamatan->nama }}</p>
            <p class="text-[11px] dark:text-gray-500 text-gray-400 mt-0.5">{{ $kecFinal }}/{{ count($kecTpsIds) }} TPS difinalisasi</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="w-24 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                <div class="h-1.5 rounded-full bg-red-500" style="width:{{ count($kecTpsIds) > 0 ? round(($kecFinal/count($kecTpsIds))*100) : 0 }}%"></div>
            </div>
            <span id="arrow-kec-{{ $kecamatan->id }}" class="dark:text-gray-500 text-gray-400 text-xs">▸</span>
        </div>
    </div>

    {{-- Isi accordion: per desa → TPS sebagai kolom --}}
    <div id="kec-{{ $kecamatan->id }}" class="hidden">
    @foreach($kecamatan->desas as $desa)
    @php
        $desaTpsIds = $desa->tps->pluck('id')->toArray();
        $desaFinal  = $rekaps->whereIn('tps_id', $desaTpsIds)->where('status','final')->count();
    @endphp

    {{-- Sub-header desa --}}
    <div class="flex items-center justify-between px-6 py-3 dark:bg-gray-700/30 bg-gray-50 border-b dark:border-gray-700 border-gray-100 cursor-pointer"
         onclick="toggleDesa({{ $desa->id }})">
        <div>
            <p class="text-xs font-semibold dark:text-gray-300 text-gray-600">{{ $desa->nama }}</p>
            <p class="text-[10px] dark:text-gray-500 text-gray-400">{{ $desaFinal }}/{{ $desa->tps->count() }} TPS difinalisasi</p>
        </div>
        <span id="arrow-desa-{{ $desa->id }}" class="dark:text-gray-500 text-gray-400 text-xs">▸</span>
    </div>

    <div id="desa-{{ $desa->id }}" class="hidden">

        {{-- Section I --}}
        <div class="px-5 py-2 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/20 bg-gray-50">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Section I — DPT & Pengguna Hak Pilih</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-700 border-gray-200">
                        <th class="text-left px-5 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold min-w-56">Keterangan</th>
                        @foreach($desa->tps as $tps)
                        <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">{{ $tps->nama }}</th>
                        @endforeach
                        <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($rows1 as $row)
                @php $rowTotal = 0; $isBold = $row['bold'] ?? false; @endphp
                <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 {{ $isBold ? 'dark:bg-gray-700/20 bg-gray-50' : 'dark:hover:bg-gray-750 hover:bg-gray-50' }}">
                    <td class="px-5 py-1.5 text-sm {{ $isBold ? 'font-bold dark:text-gray-200 text-gray-800' : 'dark:text-gray-300 text-gray-600' }}">{{ $row['label'] }}</td>
                    @foreach($desa->tps as $tps)
                    @php $r = $rekaps[$tps->id] ?? null; $val = $r ? (isset($row['field']) ? ($r->{$row['field']} ?? 0) : collect($row['sum'])->sum(fn($f) => $r->$f ?? 0)) : null; $rowTotal += $val ?? 0; @endphp
                    <td class="px-3 py-1.5 text-center {{ $isBold ? 'font-bold dark:text-gray-200 text-gray-700' : 'dark:text-gray-400 text-gray-500' }}">{{ $r ? number_format($val) : '—' }}</td>
                    @endforeach
                    <td class="px-3 py-1.5 text-center font-bold text-red-500">{{ number_format($rowTotal) }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Section II --}}
        <div class="px-5 py-2 border-t border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/20 bg-gray-50">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Section II — Surat Suara</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-700 border-gray-200">
                        <th class="text-left px-5 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold min-w-56">Keterangan</th>
                        @foreach($desa->tps as $tps)
                        <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">{{ $tps->nama }}</th>
                        @endforeach
                        <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($rows2 as $row)
                @php $rowTotal = 0; $isBold = $row['bold'] ?? false; @endphp
                <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 {{ $isBold ? 'dark:bg-gray-700/20 bg-gray-50' : 'dark:hover:bg-gray-750 hover:bg-gray-50' }}">
                    <td class="px-5 py-1.5 text-sm {{ $isBold ? 'font-bold dark:text-gray-200 text-gray-800' : 'dark:text-gray-300 text-gray-600' }}">{{ $row['label'] }}</td>
                    @foreach($desa->tps as $tps)
                    @php $r = $rekaps[$tps->id] ?? null; $val = $r ? ($r->{$row['field']} ?? 0) : null; $rowTotal += $val ?? 0; @endphp
                    <td class="px-3 py-1.5 text-center {{ $isBold ? 'font-bold dark:text-gray-200 text-gray-700' : 'dark:text-gray-400 text-gray-500' }}">{{ $r ? number_format($val) : '—' }}</td>
                    @endforeach
                    <td class="px-3 py-1.5 text-center font-bold text-red-500">{{ number_format($rowTotal) }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Section III --}}
        <div class="px-5 py-2 border-t border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/20 bg-gray-50">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Section III — Pemilih Disabilitas</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-700 border-gray-200">
                        <th class="text-left px-5 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold min-w-56">Keterangan</th>
                        @foreach($desa->tps as $tps)
                        <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">{{ $tps->nama }}</th>
                        @endforeach
                        <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($rows3 as $row)
                @php $rowTotal = 0; $isBold = $row['bold'] ?? false; @endphp
                <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 {{ $isBold ? 'dark:bg-gray-700/20 bg-gray-50' : 'dark:hover:bg-gray-750 hover:bg-gray-50' }}">
                    <td class="px-5 py-1.5 text-sm {{ $isBold ? 'font-bold dark:text-gray-200 text-gray-800' : 'dark:text-gray-300 text-gray-600' }}">{{ $row['label'] }}</td>
                    @foreach($desa->tps as $tps)
                    @php $r = $rekaps[$tps->id] ?? null; $val = $r ? (isset($row['field']) ? ($r->{$row['field']} ?? 0) : collect($row['sum'])->sum(fn($f) => $r->$f ?? 0)) : null; $rowTotal += $val ?? 0; @endphp
                    <td class="px-3 py-1.5 text-center {{ $isBold ? 'font-bold dark:text-gray-200 text-gray-700' : 'dark:text-gray-400 text-gray-500' }}">{{ $r ? number_format($val) : '—' }}</td>
                    @endforeach
                    <td class="px-3 py-1.5 text-center font-bold text-red-500">{{ number_format($rowTotal) }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Section IV --}}
        <div class="px-5 py-2 border-t border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/20 bg-gray-50">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Section IV — Perolehan Suara</p>
        </div>
        @if(in_array($jenis, ['ppwp','dpd']))
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-700 border-gray-200">
                        <th class="text-left px-5 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold min-w-48">Calon</th>
                        @foreach($desa->tps as $tps)
                        <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">{{ $tps->nama }}</th>
                        @endforeach
                        <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($master['calons'] as $calon)
                @php $rowTotal = 0; $name = $jenis === 'ppwp' ? $calon->nama_paslon : $calon->nama_calon; @endphp
                <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 dark:hover:bg-gray-750 hover:bg-gray-50">
                    <td class="px-5 py-1.5 text-sm dark:text-gray-200 text-gray-700"><span class="text-xs dark:text-gray-500 text-gray-400 mr-1">{{ $calon->nomor_urut }}.</span>{{ $name }}</td>
                    @foreach($desa->tps as $tps)
                    @php $r = $rekaps[$tps->id] ?? null; $s = $r ? ($jenis === 'ppwp' ? ($r->ppwpSuaras->firstWhere('calon_id', $calon->id)?->suara ?? 0) : ($r->dpdSuaras->firstWhere('calon_id', $calon->id)?->suara ?? 0)) : null; $rowTotal += $s ?? 0; @endphp
                    <td class="px-3 py-1.5 text-center dark:text-gray-400 text-gray-500">{{ $r ? number_format($s) : '—' }}</td>
                    @endforeach
                    <td class="px-3 py-1.5 text-center font-bold text-red-500">{{ number_format($rowTotal) }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        @foreach($master['partais'] as $partai)
        <div class="border-b dark:border-gray-700 border-gray-200 last:border-0">
            <div class="px-5 py-2 dark:bg-gray-700/30 bg-gray-50">
                <p class="text-xs font-bold dark:text-gray-300 text-gray-700">{{ $partai->nomor_urut }}. {{ $partai->nama_partai }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b dark:border-gray-700 border-gray-100">
                            <th class="text-left px-5 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold min-w-48">Caleg</th>
                            @foreach($desa->tps as $tps)
                            <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">{{ $tps->nama }}</th>
                            @endforeach
                            <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php $partaiRowTotal = 0; @endphp
                    <tr class="border-b dark:border-gray-700 border-gray-100 dark:bg-gray-700/20 bg-gray-50">
                        <td class="px-5 py-1.5 text-xs font-bold dark:text-gray-300 text-gray-700 uppercase">Suara Partai</td>
                        @foreach($desa->tps as $tps)
                        @php $r = $rekaps[$tps->id] ?? null; $sp = $r ? ($r->partaiSuaras->firstWhere('partai_id', $partai->id)?->suara ?? 0) : null; $partaiRowTotal += $sp ?? 0; @endphp
                        <td class="px-3 py-1.5 text-center dark:text-gray-400 text-gray-500">{{ $r ? number_format($sp) : '—' }}</td>
                        @endforeach
                        <td class="px-3 py-1.5 text-center font-bold text-red-500">{{ number_format($partaiRowTotal) }}</td>
                    </tr>
                    @foreach($partai->calegs as $caleg)
                    @php $calegRowTotal = 0; @endphp
                    <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 dark:hover:bg-gray-750 hover:bg-gray-50">
                        <td class="px-5 py-1.5"><div class="flex items-center gap-2"><span class="text-xs dark:text-gray-500 text-gray-400 w-4">{{ $caleg->nomor_urut }}.</span><span class="text-sm dark:text-gray-200 text-gray-700">{{ $caleg->nama_caleg }}</span></div></td>
                        @foreach($desa->tps as $tps)
                        @php $r = $rekaps[$tps->id] ?? null; $sc = $r ? ($r->calegSuaras->firstWhere('caleg_id', $caleg->id)?->suara ?? 0) : null; $calegRowTotal += $sc ?? 0; @endphp
                        <td class="px-3 py-1.5 text-center dark:text-gray-400 text-gray-500">{{ $r ? number_format($sc) : '—' }}</td>
                        @endforeach
                        <td class="px-3 py-1.5 text-center font-bold text-teal-400">{{ number_format($calegRowTotal) }}</td>
                    </tr>
                    @endforeach
                    @php $grandTotal = 0; @endphp
                    <tr class="border-t-2 dark:border-gray-600 border-gray-300 dark:bg-gray-700/30 bg-gray-50">
                        <td class="px-5 py-1.5 text-xs font-bold dark:text-gray-300 text-gray-700 uppercase">Total Suara Sah</td>
                        @foreach($desa->tps as $tps)
                        @php $r = $rekaps[$tps->id] ?? null; $sp = $r ? ($r->partaiSuaras->firstWhere('partai_id', $partai->id)?->suara ?? 0) : 0; $sc_sum = $r ? $r->calegSuaras->whereIn('caleg_id', $partai->calegs->pluck('id'))->sum('suara') : 0; $colTotal = $r ? ($sp + $sc_sum) : null; $grandTotal += $colTotal ?? 0; @endphp
                        <td class="px-3 py-1.5 text-center font-bold text-teal-400">{{ $r ? number_format($colTotal) : '—' }}</td>
                        @endforeach
                        <td class="px-3 py-1.5 text-center font-bold text-teal-400">{{ number_format($grandTotal) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
        @endif

        {{-- Section V --}}
        <div class="px-5 py-2 border-t border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/20 bg-gray-50">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Section V — Suara Sah, Tidak Sah & Total</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-700 border-gray-200">
                        <th class="text-left px-5 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold min-w-56">Keterangan</th>
                        @foreach($desa->tps as $tps)
                        <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">{{ $tps->nama }}</th>
                        @endforeach
                        <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody>
                @php $rowTotalSah = 0; @endphp
                <tr class="border-b dark:border-gray-700 border-gray-100 dark:hover:bg-gray-750 hover:bg-gray-50">
                    <td class="px-5 py-1.5 text-sm dark:text-gray-300 text-gray-600">Jumlah Suara Sah</td>
                    @foreach($desa->tps as $tps)
                    @php $r = $rekaps[$tps->id] ?? null; $sah = $r ? $r->suara_sah : null; $rowTotalSah += $sah ?? 0; @endphp
                    <td class="px-3 py-1.5 text-center dark:text-gray-400 text-gray-500">{{ $r ? number_format($sah) : '—' }}</td>
                    @endforeach
                    <td class="px-3 py-1.5 text-center font-bold text-red-500">{{ number_format($rowTotalSah) }}</td>
                </tr>
                @php $rowTotalTdk = 0; @endphp
                <tr class="border-b dark:border-gray-700 border-gray-100 dark:hover:bg-gray-750 hover:bg-gray-50">
                    <td class="px-5 py-1.5 text-sm dark:text-gray-300 text-gray-600">Jumlah Suara Tidak Sah</td>
                    @foreach($desa->tps as $tps)
                    @php $r = $rekaps[$tps->id] ?? null; $tdk = $r ? $r->suara_tidak_sah : null; $rowTotalTdk += $tdk ?? 0; @endphp
                    <td class="px-3 py-1.5 text-center dark:text-gray-400 text-gray-500">{{ $r ? number_format($tdk) : '—' }}</td>
                    @endforeach
                    <td class="px-3 py-1.5 text-center font-bold text-red-500">{{ number_format($rowTotalTdk) }}</td>
                </tr>
                @php $rowTotalAll = 0; @endphp
                <tr class="dark:bg-gray-700/20 bg-gray-50">
                    <td class="px-5 py-1.5 text-sm font-bold dark:text-gray-200 text-gray-800">Jumlah Seluruh Suara</td>
                    @foreach($desa->tps as $tps)
                    @php $r = $rekaps[$tps->id] ?? null; $all = $r ? ($r->suara_sah + $r->suara_tidak_sah) : null; $rowTotalAll += $all ?? 0; @endphp
                    <td class="px-3 py-1.5 text-center font-bold dark:text-gray-200 text-gray-700">{{ $r ? number_format($all) : '—' }}</td>
                    @endforeach
                    <td class="px-3 py-1.5 text-center font-bold text-red-500">{{ number_format($rowTotalAll) }}</td>
                </tr>
                <tr class="dark:bg-gray-700/10 bg-gray-50 border-t dark:border-gray-700 border-gray-200">
                    <td class="px-5 py-1.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold tracking-wider">Status</td>
                    @foreach($desa->tps as $tps)
                    @php $r = $rekaps[$tps->id] ?? null; @endphp
                    <td class="px-3 py-1.5 text-center">
                        @if(!$r) <span class="text-[9px] px-2 py-1 rounded font-semibold bg-gray-500/20 dark:text-gray-400 text-gray-500 border border-gray-400/30">Kosong</span>
                        @elseif($r->status === 'final') <span class="text-[9px] px-2 py-1 rounded font-semibold bg-teal-500/20 text-teal-400 border border-teal-500/40">Final</span>
                        @else <span class="text-[9px] px-2 py-1 rounded font-semibold bg-orange-400/20 text-orange-400 border border-orange-400/40">Draft</span>
                        @endif
                    </td>
                    @endforeach
                    <td></td>
                </tr>
                </tbody>
            </table>
        </div>

    </div>{{-- end desa --}}
    @endforeach
    </div>{{-- end kec accordion --}}
</div>
@endforeach

{{-- Export Modal --}}
<div id="export-modal" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
    <div class="dark:bg-gray-800 bg-white rounded-2xl border dark:border-gray-700 border-gray-200 w-full max-w-md shadow-2xl p-8">
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-1 font-semibold">// Export</p>
        <h2 class="font-display text-2xl tracking-wide text-red-600 mb-6">EXPORT EXCEL</h2>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Level Export</label>
                <select id="export-level" onchange="updateExportFilter()"
                        class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                    <option value="">— Pilih Level —</option>
                    <option value="tps">Tingkat TPS</option>
                    <option value="desa">Tingkat Desa / PPS</option>
                    <option value="kecamatan">Tingkat Kecamatan / PPK</option>
                    <option value="kabupaten">Tingkat Kabupaten</option>
                </select>
            </div>

            <div id="export-filter-kec" class="hidden">
                <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Kecamatan</label>
                <select id="export-kec" onchange="loadExportDesa(this.value)"
                        class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                    <option value="">— Pilih Kecamatan —</option>
                    @foreach($kecamatans as $kec)
                    <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div id="export-filter-desa" class="hidden">
                <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Desa</label>
                <select id="export-desa" onchange="loadExportTps(this.value)"
                        class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                    <option value="">— Pilih Desa —</option>
                </select>
            </div>

            <div id="export-filter-tps" class="hidden">
                <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">TPS</label>
                <select id="export-tps"
                        class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                    <option value="">— Pilih TPS —</option>
                </select>
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="button" onclick="closeExportModal()"
                    class="flex-1 border dark:border-gray-600 border-gray-300 dark:text-gray-400 text-gray-500 py-2.5 rounded-lg text-sm font-medium dark:hover:bg-gray-700 hover:bg-gray-100 transition">
                Batal
            </button>
            <a id="export-download-btn" href="#"
               class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg text-sm font-semibold transition text-center opacity-50 pointer-events-none">
                ↓ Download
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const allDesas = @json(\App\Models\Desa::orderBy('nama')->get(['id','nama','kecamatan_id']));
    const allTps   = @json(\App\Models\Tps::orderBy('nama')->get(['id','nama','desa_id']));
    const baseUrl  = '{{ route('admin.rekap.export', $jenis) }}';
    const exportDownloadBase = '{{ url('admin/rekap/export/download') }}';

    function toggleKec(id) {
        const el    = document.getElementById('kec-' + id);
        const arrow = document.getElementById('arrow-kec-' + id);
        el.classList.toggle('hidden');
        arrow.textContent = el.classList.contains('hidden') ? '▸' : '▾';
    }
    function toggleDesa(id) {
        const el    = document.getElementById('desa-' + id);
        const arrow = document.getElementById('arrow-desa-' + id);
        el.classList.toggle('hidden');
        arrow.textContent = el.classList.contains('hidden') ? '▸' : '▾';
    }
    function openExportModal() {
        document.getElementById('export-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeExportModal() {
        document.getElementById('export-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }
    document.getElementById('export-modal').addEventListener('click', function(e) {
        if (e.target === this) closeExportModal();
    });

    function updateExportFilter() {
        const level = document.getElementById('export-level').value;
        document.getElementById('export-filter-kec').classList.add('hidden');
        document.getElementById('export-filter-desa').classList.add('hidden');
        document.getElementById('export-filter-tps').classList.add('hidden');
        document.getElementById('export-kec').value = '';
        disableDownload();

        if (level === 'kabupaten') { enableDownload('kabupaten'); return; }
        if (['tps','desa','kecamatan'].includes(level)) {
            document.getElementById('export-filter-kec').classList.remove('hidden');
        }
    }

    function loadExportDesa(kecId) {
        const level = document.getElementById('export-level').value;
        document.getElementById('export-filter-desa').classList.add('hidden');
        document.getElementById('export-filter-tps').classList.add('hidden');
        disableDownload();

        if (!kecId) return;
        if (level === 'kecamatan') { enableDownload('kecamatan', kecId); return; }

        const desas = allDesas.filter(d => d.kecamatan_id == kecId);
        const sel   = document.getElementById('export-desa');
        sel.innerHTML = '<option value="">— Pilih Desa —</option>';
        desas.forEach(d => sel.innerHTML += `<option value="${d.id}">${d.nama}</option>`);
        document.getElementById('export-filter-desa').classList.remove('hidden');
    }

    function loadExportTps(desaId) {
        const level = document.getElementById('export-level').value;
        document.getElementById('export-filter-tps').classList.add('hidden');
        disableDownload();

        if (!desaId) return;
        if (level === 'desa') { enableDownload('desa', null, desaId); return; }

        const tpsList = allTps.filter(t => t.desa_id == desaId);
        const sel     = document.getElementById('export-tps');
        sel.innerHTML = '<option value="">— Pilih TPS —</option>';
        tpsList.forEach(t => sel.innerHTML += `<option value="${t.id}" onchange="enableDownload('tps',null,null,this.value)">${t.nama}</option>`);
        document.getElementById('export-filter-tps').classList.remove('hidden');

        // Listen change on TPS select
        document.getElementById('export-tps').onchange = function() {
            if (this.value) enableDownload('tps', null, null, this.value);
            else disableDownload();
        };
    }

    function enableDownload(level, kecId = null, desaId = null, tpsId = null) {
        const jenis  = '{{ $jenis }}';
        const params = new URLSearchParams({ jenis, level });
        if (kecId)  params.set('kecamatan_id', kecId);
        if (desaId) params.set('desa_id', desaId);
        if (tpsId)  params.set('tps_id', tpsId);

        const btn = document.getElementById('export-download-btn');
        btn.href  = exportDownloadBase + '?' + params.toString();
        btn.classList.remove('opacity-50','pointer-events-none');
    }

    function disableDownload() {
        const btn = document.getElementById('export-download-btn');
        btn.href  = '#';
        btn.classList.add('opacity-50','pointer-events-none');
    }
</script>
@endpush

@endsection