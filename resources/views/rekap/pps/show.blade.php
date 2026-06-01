@extends('layouts.app')
@section('title', 'Rekap ' . \App\Models\RekapHeader::JENIS_LABELS[$jenis])

@section('content')
<div class="mb-8 flex items-end justify-between gap-4">
    <div>
        <a href="{{ route('pps.rekap.index') }}"
           class="inline-flex items-center gap-2 text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition font-medium mb-4">
            ← Kembali
        </a>
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">
            // PPS — {{ $desa->nama }}
        </p>
        <h1 class="font-display text-4xl tracking-[2px] text-teal-400">
            {{ strtoupper(\App\Models\RekapHeader::JENIS_LABELS[$jenis]) }}
        </h1>
    </div>
    <a href="{{ route('pps.rekap.export', $jenis) }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold bg-teal-500 hover:bg-teal-600 text-white transition flex-shrink-0">
        ↓ Export Excel
    </a>
</div>

{{-- Summary cards --}}
@php
    $totalDpt    = $rekaps->sum(fn($r) => $r->dpt_lk + $r->dpt_pr);
    $totalHadir  = $rekaps->sum(fn($r) => $r->total_pengguna_lk + $r->total_pengguna_pr);
    $totalTdkSah = $rekaps->sum('suara_tidak_sah');

    $rowKeyFor = fn($row) => isset($row['field']) && $row['field'] ? $row['field'] : 'sum:' . implode('+', $row['sum']);
    $renderFlaggedTotalCell = function(string $rowKey, $value, string $baseClass = '') use ($cellFlags) {
        $flagged = $cellFlags->has($rowKey);
        $classes = trim($baseClass . ' ' . ($flagged
            ? 'bg-red-500/20 text-red-600 dark:bg-red-500/20 dark:text-red-200 ring-1 ring-inset ring-red-400/60'
            : ''));
        $content = is_null($value) ? '&mdash;' : number_format($value);

        return new \Illuminate\Support\HtmlString('<td class="' . e($classes) . '">' . $content . '</td>');
    };
@endphp
<div class="grid grid-cols-3 gap-4 mb-8">
    <div class="dark:bg-gray-800 bg-white rounded-xl p-5 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">Total DPT</p>
        <p class="font-display text-3xl text-teal-400">{{ number_format($totalDpt) }}</p>
    </div>
    <div class="dark:bg-gray-800 bg-white rounded-xl p-5 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">Total Hadir</p>
        <p class="font-display text-3xl text-teal-400">{{ number_format($totalHadir) }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">
            {{ $totalDpt > 0 ? round(($totalHadir/$totalDpt)*100,1) : 0 }}% partisipasi
        </p>
    </div>
    <div class="dark:bg-gray-800 bg-white rounded-xl p-5 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">Suara Tidak Sah</p>
        <p class="font-display text-3xl text-teal-400">{{ number_format($totalTdkSah) }}</p>
    </div>
</div>

{{-- ═══ MACRO: header kolom TPS (dipakai berulang) ═══ --}}
{{-- ══════════════════════════════════════
     SECTION I, II, III — Data Pemilih & Surat Suara
     (sama untuk semua jenis pemilihan)
═══════════════════════════════════════ --}}

{{-- SECTION I: DPT & Pengguna Hak Pilih --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-3 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/50 bg-gray-50">
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Section I — DPT &amp; Pengguna Hak Pilih</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b dark:border-gray-700 border-gray-200">
                    <th class="text-left px-5 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold min-w-56">Keterangan</th>
                    @foreach($tpsList as $tps)
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">{{ $tps->nama }}</th>
                    @endforeach
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                </tr>
            </thead>
            <tbody>
            @php
                $rows1 = [
                    ['label'=>'DPT Laki-laki',         'field'=>'dpt_lk'],
                    ['label'=>'DPT Perempuan',          'field'=>'dpt_pr'],
                    ['label'=>'DPT Jumlah',             'field'=>null, 'sum'=>['dpt_lk','dpt_pr'], 'bold'=>true],
                    ['label'=>'Pengguna DPT LK',        'field'=>'pengguna_dpt_lk'],
                    ['label'=>'Pengguna DPT PR',        'field'=>'pengguna_dpt_pr'],
                    ['label'=>'Pengguna DPTB LK',       'field'=>'pengguna_dptb_lk'],
                    ['label'=>'Pengguna DPTB PR',       'field'=>'pengguna_dptb_pr'],
                    ['label'=>'Pengguna DPK LK',        'field'=>'pengguna_dpk_lk'],
                    ['label'=>'Pengguna DPK PR',        'field'=>'pengguna_dpk_pr'],
                    ['label'=>'Total Pengguna Hak Pilih','field'=>null, 'sum'=>['pengguna_dpt_lk','pengguna_dpt_pr','pengguna_dptb_lk','pengguna_dptb_pr','pengguna_dpk_lk','pengguna_dpk_pr'], 'bold'=>true],
                ];
            @endphp
            @foreach($rows1 as $row)
            @php
                $rowTotal = 0;
                $isBold = $row['bold'] ?? false;
            @endphp
            <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 {{ $isBold ? 'dark:bg-gray-700/20 bg-gray-50' : 'dark:hover:bg-gray-750 hover:bg-gray-50' }}">
                <td class="px-5 py-2.5 text-sm {{ $isBold ? 'font-bold dark:text-gray-200 text-gray-800' : 'dark:text-gray-300 text-gray-600' }}">{{ $row['label'] }}</td>
                @foreach($tpsList as $tps)
                @php
                    $r = $rekaps[$tps->id] ?? null;
                    if ($row['field']) {
                        $val = $r ? ($r->{$row['field']} ?? 0) : null;
                    } else {
                        $val = $r ? collect($row['sum'])->sum(fn($f) => $r->$f ?? 0) : null;
                    }
                    $rowTotal += $val ?? 0;
                @endphp
                <td class="px-3 py-2.5 text-center {{ $isBold ? 'font-bold dark:text-gray-200 text-gray-700' : 'dark:text-gray-400 text-gray-500' }}">
                    {{ $r ? number_format($val) : '—' }}
                </td>
                @endforeach
                {!! $renderFlaggedTotalCell($rowKeyFor($row), $rowTotal, 'px-3 py-2.5 text-center font-bold text-teal-400') !!}
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- SECTION II: Surat Suara --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-3 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/50 bg-gray-50">
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Section II — Surat Suara</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b dark:border-gray-700 border-gray-200">
                    <th class="text-left px-5 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold min-w-56">Keterangan</th>
                    @foreach($tpsList as $tps)
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">{{ $tps->nama }}</th>
                    @endforeach
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                </tr>
            </thead>
            <tbody>
            @php
                $rows2 = [
                    ['label'=>'Surat Suara Diterima',  'field'=>'ss_diterima'],
                    ['label'=>'Surat Suara Digunakan', 'field'=>'ss_digunakan'],
                    ['label'=>'Surat Suara Rusak',     'field'=>'ss_rusak'],
                    ['label'=>'Surat Suara Sisa',      'field'=>'ss_sisa', 'bold'=>true],
                ];
            @endphp
            @foreach($rows2 as $row)
            @php $rowTotal = 0; $isBold = $row['bold'] ?? false; @endphp
            <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 {{ $isBold ? 'dark:bg-gray-700/20 bg-gray-50' : 'dark:hover:bg-gray-750 hover:bg-gray-50' }}">
                <td class="px-5 py-2.5 text-sm {{ $isBold ? 'font-bold dark:text-gray-200 text-gray-800' : 'dark:text-gray-300 text-gray-600' }}">{{ $row['label'] }}</td>
                @foreach($tpsList as $tps)
                @php
                    $r = $rekaps[$tps->id] ?? null;
                    $val = $r ? ($r->{$row['field']} ?? 0) : null;
                    $rowTotal += $val ?? 0;
                @endphp
                <td class="px-3 py-2.5 text-center {{ $isBold ? 'font-bold dark:text-gray-200 text-gray-700' : 'dark:text-gray-400 text-gray-500' }}">
                    {{ $r ? number_format($val) : '—' }}
                </td>
                @endforeach
                {!! $renderFlaggedTotalCell($rowKeyFor($row), $rowTotal, 'px-3 py-2.5 text-center font-bold text-teal-400') !!}
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- SECTION III: Disabilitas --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-3 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/50 bg-gray-50">
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Section III — Pemilih Disabilitas</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b dark:border-gray-700 border-gray-200">
                    <th class="text-left px-5 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold min-w-56">Keterangan</th>
                    @foreach($tpsList as $tps)
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">{{ $tps->nama }}</th>
                    @endforeach
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                </tr>
            </thead>
            <tbody>
            @php
                $rows3 = [
                    ['label'=>'Disabilitas Laki-laki',   'field'=>'disabilitas_lk'],
                    ['label'=>'Disabilitas Perempuan',   'field'=>'disabilitas_pr'],
                    ['label'=>'Disabilitas Jumlah',      'field'=>null, 'sum'=>['disabilitas_lk','disabilitas_pr'], 'bold'=>true],
                ];
            @endphp
            @foreach($rows3 as $row)
            @php $rowTotal = 0; $isBold = $row['bold'] ?? false; @endphp
            <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 {{ $isBold ? 'dark:bg-gray-700/20 bg-gray-50' : 'dark:hover:bg-gray-750 hover:bg-gray-50' }}">
                <td class="px-5 py-2.5 text-sm {{ $isBold ? 'font-bold dark:text-gray-200 text-gray-800' : 'dark:text-gray-300 text-gray-600' }}">{{ $row['label'] }}</td>
                @foreach($tpsList as $tps)
                @php
                    $r = $rekaps[$tps->id] ?? null;
                    if ($row['field']) {
                        $val = $r ? ($r->{$row['field']} ?? 0) : null;
                    } else {
                        $val = $r ? collect($row['sum'])->sum(fn($f) => $r->$f ?? 0) : null;
                    }
                    $rowTotal += $val ?? 0;
                @endphp
                <td class="px-3 py-2.5 text-center {{ $isBold ? 'font-bold dark:text-gray-200 text-gray-700' : 'dark:text-gray-400 text-gray-500' }}">
                    {{ $r ? number_format($val) : '—' }}
                </td>
                @endforeach
                {!! $renderFlaggedTotalCell($rowKeyFor($row), $rowTotal, 'px-3 py-2.5 text-center font-bold text-teal-400') !!}
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ══════════════════════════════════════
     SECTION IV — Perolehan Suara
═══════════════════════════════════════ --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-3 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/50 bg-gray-50">
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Section IV — Perolehan Suara</p>
    </div>

@if(in_array($jenis, ['ppwp','gubernur','bupati','dpd']))
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b dark:border-gray-700 border-gray-200">
                    <th class="text-left px-5 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold min-w-48">{{ in_array($jenis, ['ppwp','gubernur','bupati']) ? 'Paslon' : 'Calon' }}</th>
                    @foreach($tpsList as $tps)
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">{{ $tps->nama }}</th>
                    @endforeach
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                </tr>
            </thead>
            <tbody>
            @foreach($master['calons'] as $calon)
            @php $rowTotal = 0; @endphp
            <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 dark:hover:bg-gray-750 hover:bg-gray-50">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full {{ $jenis === 'ppwp' ? 'bg-red-600' : 'bg-teal-500' }} text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                            {{ $calon->nomor_urut }}
                        </span>
                        <span class="text-sm dark:text-gray-200 text-gray-700">
                            {{ in_array($jenis, ['ppwp','gubernur','bupati']) ? $calon->nama_paslon : $calon->nama_calon }}
                        </span>
                    </div>
                </td>
                @foreach($tpsList as $tps)
                @php
                    $r = $rekaps[$tps->id] ?? null;
                    $suaraMap = $r ? match($jenis) {
                        'ppwp'     => $r->ppwpSuaras->pluck('suara','calon_id'),
                        'gubernur' => $r->gubernurSuaras->pluck('suara','calon_id'),
                        'bupati'   => $r->bupatiSuaras->pluck('suara','calon_id'),
                        default    => $r->dpdSuaras->pluck('suara','calon_id'),
                    } : collect();
                    $s = $suaraMap[$calon->id] ?? null;
                    $rowTotal += $s ?? 0;
                @endphp
                <td class="px-3 py-3 text-center font-semibold dark:text-gray-200 text-gray-700">{{ $r ? number_format($s ?? 0) : '—' }}</td>
                @endforeach
                {!! $renderFlaggedTotalCell('calon:' . $calon->id, $rowTotal, 'px-3 py-3 text-center font-bold text-teal-400') !!}
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@else
    @foreach($master['partais'] as $partai)
    <div class="border-b dark:border-gray-700 border-gray-100 last:border-0">
        <div class="px-5 py-2.5 dark:bg-gray-700/30 bg-gray-100 flex items-center gap-2 border-b dark:border-gray-700 border-gray-100">
            <span class="w-6 h-6 rounded bg-orange-400 text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0">{{ $partai->nomor_urut }}</span>
            <p class="text-xs font-semibold dark:text-gray-200 text-gray-700">{{ $partai->nama_partai }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-700 border-gray-100">
                        <th class="text-left px-5 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold min-w-48">Caleg</th>
                        @foreach($tpsList as $tps)
                        <th class="text-center px-3 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">{{ $tps->nama }}</th>
                        @endforeach
                        <th class="text-center px-3 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody>
                @php $partaiRowTotal = 0; @endphp
                <tr class="border-b dark:border-gray-700 border-gray-100 dark:bg-gray-700/20 bg-gray-50">
                    <td class="px-5 py-2.5 text-xs font-bold dark:text-gray-300 text-gray-700 uppercase">Suara Partai</td>
                    @foreach($tpsList as $tps)
                    @php $r = $rekaps[$tps->id] ?? null; $sp = $r ? ($r->partaiSuaras->firstWhere('partai_id', $partai->id)?->suara ?? 0) : null; $partaiRowTotal += $sp ?? 0; @endphp
                    <td class="px-3 py-2.5 text-center font-semibold dark:text-gray-200 text-gray-700">{{ $r ? number_format($sp) : '—' }}</td>
                    @endforeach
                    {!! $renderFlaggedTotalCell('partai:' . $partai->id, $partaiRowTotal, 'px-3 py-2.5 text-center font-bold text-orange-400') !!}
                </tr>
                @foreach($partai->calegs as $caleg)
                @php $calegRowTotal = 0; @endphp
                <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 dark:hover:bg-gray-750 hover:bg-gray-50">
                    <td class="px-5 py-2.5">
                        <div class="flex items-center gap-2">
                            <span class="text-xs dark:text-gray-500 text-gray-400 w-4">{{ $caleg->nomor_urut }}.</span>
                            <span class="text-sm dark:text-gray-200 text-gray-700">{{ $caleg->nama_caleg }}</span>
                        </div>
                    </td>
                    @foreach($tpsList as $tps)
                    @php $r = $rekaps[$tps->id] ?? null; $sc = $r ? ($r->calegSuaras->firstWhere('caleg_id', $caleg->id)?->suara ?? 0) : null; $calegRowTotal += $sc ?? 0; @endphp
                    <td class="px-3 py-2.5 text-center dark:text-gray-400 text-gray-500">{{ $r ? number_format($sc) : '—' }}</td>
                    @endforeach
                    {!! $renderFlaggedTotalCell('caleg:' . $caleg->id, $calegRowTotal, 'px-3 py-2.5 text-center font-bold text-teal-400') !!}
                </tr>
                @endforeach
                @php $grandTotal = 0; @endphp
                <tr class="border-t-2 dark:border-gray-600 border-gray-300 dark:bg-gray-700/30 bg-gray-50">
                    <td class="px-5 py-2.5 text-xs font-bold dark:text-gray-300 text-gray-700 uppercase">Total Suara Sah</td>
                    @foreach($tpsList as $tps)
                    @php $r = $rekaps[$tps->id] ?? null; $sp = $r ? ($r->partaiSuaras->firstWhere('partai_id', $partai->id)?->suara ?? 0) : 0; $sc_sum = $r ? $r->calegSuaras->whereIn('caleg_id', $partai->calegs->pluck('id'))->sum('suara') : 0; $colTotal = $r ? ($sp + $sc_sum) : null; $grandTotal += $colTotal ?? 0; @endphp
                    <td class="px-3 py-2.5 text-center font-bold text-teal-400">{{ $r ? number_format($colTotal) : '—' }}</td>
                    @endforeach
                    {!! $renderFlaggedTotalCell('partai_total:' . $partai->id, $grandTotal, 'px-3 py-2.5 text-center font-bold text-teal-400') !!}
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
@endif
</div>

{{-- ══════════════════════════════════════
     SECTION V — Suara Sah, Tidak Sah & Total
═══════════════════════════════════════ --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-3 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/50 bg-gray-50">
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Section V — Suara Sah, Tidak Sah &amp; Total</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b dark:border-gray-700 border-gray-200">
                    <th class="text-left px-5 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold min-w-56">Keterangan</th>
                    @foreach($tpsList as $tps)
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">{{ $tps->nama }}</th>
                    @endforeach
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                </tr>
            </thead>
            <tbody>
            {{-- Suara sah --}}
            @php $rowTotalSah = 0; @endphp
            <tr class="border-b dark:border-gray-700 border-gray-100 dark:hover:bg-gray-750 hover:bg-gray-50">
                <td class="px-5 py-2.5 text-sm dark:text-gray-300 text-gray-600">Jumlah Suara Sah</td>
                @foreach($tpsList as $tps)
                @php $r = $rekaps[$tps->id] ?? null; $sah = $r ? $r->suara_sah : null; $rowTotalSah += $sah ?? 0; @endphp
                <td class="px-3 py-2.5 text-center dark:text-gray-400 text-gray-500">{{ $r ? number_format($sah) : '—' }}</td>
                @endforeach
                {!! $renderFlaggedTotalCell('suara_sah', $rowTotalSah, 'px-3 py-2.5 text-center font-bold text-teal-400') !!}
            </tr>
            {{-- Suara tidak sah --}}
            @php $rowTotalTdk = 0; @endphp
            <tr class="border-b dark:border-gray-700 border-gray-100 dark:hover:bg-gray-750 hover:bg-gray-50">
                <td class="px-5 py-2.5 text-sm dark:text-gray-300 text-gray-600">Jumlah Suara Tidak Sah</td>
                @foreach($tpsList as $tps)
                @php $r = $rekaps[$tps->id] ?? null; $tdk = $r ? $r->suara_tidak_sah : null; $rowTotalTdk += $tdk ?? 0; @endphp
                <td class="px-3 py-2.5 text-center dark:text-gray-400 text-gray-500">{{ $r ? number_format($tdk) : '—' }}</td>
                @endforeach
                {!! $renderFlaggedTotalCell('suara_tidak_sah', $rowTotalTdk, 'px-3 py-2.5 text-center font-bold text-teal-400') !!}
            </tr>
            {{-- Total --}}
            @php $rowTotalAll = 0; @endphp
            <tr class="dark:bg-gray-700/20 bg-gray-50">
                <td class="px-5 py-2.5 text-sm font-bold dark:text-gray-200 text-gray-800">Jumlah Seluruh Suara</td>
                @foreach($tpsList as $tps)
                @php $r = $rekaps[$tps->id] ?? null; $all = $r ? ($r->suara_sah + $r->suara_tidak_sah) : null; $rowTotalAll += $all ?? 0; @endphp
                <td class="px-3 py-2.5 text-center font-bold dark:text-gray-200 text-gray-700">{{ $r ? number_format($all) : '—' }}</td>
                @endforeach
                {!! $renderFlaggedTotalCell('suara_total', $rowTotalAll, 'px-3 py-2.5 text-center font-bold text-teal-400') !!}
            </tr>
            {{-- Status --}}
            <tr class="dark:bg-gray-700/10 bg-gray-50 border-t dark:border-gray-700 border-gray-200">
                <td class="px-5 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold tracking-wider">Status</td>
                @foreach($tpsList as $tps)
                @php $r = $rekaps[$tps->id] ?? null; @endphp
                <td class="px-3 py-2.5 text-center">
                    @if(!$r)
                        <span class="text-[9px] px-2 py-1 rounded font-semibold bg-gray-500/20 dark:text-gray-400 text-gray-500 border border-gray-400/30">Kosong</span>
                    @elseif($r->status === 'final')
                        <span class="text-[9px] px-2 py-1 rounded font-semibold bg-teal-500/20 text-teal-400 border border-teal-500/40">Final</span>
                    @else
                        <span class="text-[9px] px-2 py-1 rounded font-semibold bg-orange-400/20 text-orange-400 border border-orange-400/40">Draft</span>
                    @endif
                </td>
                @endforeach
                <td></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection
