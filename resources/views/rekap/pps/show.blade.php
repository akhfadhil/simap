@extends('layouts.app')
@section('title', 'Rekap ' . \App\Models\RekapHeader::JENIS_LABELS[$jenis])

@section('content')
<div class="mb-8">
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

{{-- Summary --}}
@php
    $totalDpt    = $rekaps->sum(fn($r) => $r->dpt_lk + $r->dpt_pr);
    $totalHadir  = $rekaps->sum(fn($r) => $r->total_pengguna_lk + $r->total_pengguna_pr);
    $totalTdkSah = $rekaps->sum('suara_tidak_sah');
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

{{-- PPWP / DPD: transpose --}}
@if(in_array($jenis, ['ppwp','dpd']))
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b dark:border-gray-700 border-gray-200">
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Perolehan Suara per TPS</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b dark:border-gray-700 border-gray-200">
                    <th class="text-left px-5 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase tracking-wider font-semibold min-w-48">
                        {{ $jenis === 'ppwp' ? 'Paslon' : 'Calon' }}
                    </th>
                    @foreach($tpsList as $tps)
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase tracking-wider font-semibold whitespace-nowrap">
                        {{ $tps->nama }}
                    </th>
                    @endforeach
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase tracking-wider font-semibold">Total</th>
                </tr>
            </thead>
            <tbody>
            @foreach($master['calons'] as $calon)
            @php
                $rowTotal = 0;
            @endphp
            <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 dark:hover:bg-gray-750 hover:bg-gray-50">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full {{ $jenis === 'ppwp' ? 'bg-red-600' : 'bg-teal-500' }} text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                            {{ $calon->nomor_urut }}
                        </span>
                        <span class="text-sm dark:text-gray-200 text-gray-700">
                            {{ $jenis === 'ppwp' ? $calon->nama_paslon : $calon->nama_calon }}
                        </span>
                    </div>
                </td>
                @foreach($tpsList as $tps)
                @php
                    $r = $rekaps[$tps->id] ?? null;
                    $suaraMap = $r ? ($jenis === 'ppwp'
                        ? $r->ppwpSuaras->pluck('suara','calon_id')
                        : $r->dpdSuaras->pluck('suara','calon_id')) : collect();
                    $s = $suaraMap[$calon->id] ?? null;
                    $rowTotal += $s ?? 0;
                @endphp
                <td class="px-3 py-3 text-center font-semibold dark:text-gray-200 text-gray-700">
                    {{ $r ? number_format($s ?? 0) : '—' }}
                </td>
                @endforeach
                <td class="px-3 py-3 text-center font-bold text-teal-400">{{ number_format($rowTotal) }}</td>
            </tr>
            @endforeach

            {{-- Baris suara tidak sah --}}
            <tr class="border-b dark:border-gray-700 border-gray-100 dark:bg-gray-700/20 bg-gray-50">
                <td class="px-5 py-3 text-sm dark:text-gray-400 text-gray-500 italic">Suara tidak sah</td>
                @foreach($tpsList as $tps)
                @php $r = $rekaps[$tps->id] ?? null; @endphp
                <td class="px-3 py-3 text-center dark:text-gray-400 text-gray-500">
                    {{ $r ? number_format($r->suara_tidak_sah) : '—' }}
                </td>
                @endforeach
                <td class="px-3 py-3 text-center font-bold dark:text-gray-400 text-gray-500">{{ number_format($totalTdkSah) }}</td>
            </tr>

            {{-- Baris status --}}
            <tr class="dark:bg-gray-700/10 bg-gray-50">
                <td class="px-5 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold tracking-wider">Status</td>
                @foreach($tpsList as $tps)
                @php $r = $rekaps[$tps->id] ?? null; @endphp
                <td class="px-3 py-3 text-center">
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

{{-- DPR RI / DPRD: per partai, transpose --}}
@else
@foreach($master['partais'] as $partai)
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden mb-4">
    {{-- Header partai --}}
    <div class="px-6 py-4 border-b dark:border-gray-700 border-gray-200 flex items-center gap-3 dark:bg-gray-700/50 bg-gray-50">
        <span class="w-7 h-7 rounded-lg bg-orange-400 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
            {{ $partai->nomor_urut }}
        </span>
        <p class="font-semibold dark:text-gray-100 text-gray-800">{{ $partai->nama_partai }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b dark:border-gray-700 border-gray-200">
                    <th class="text-left px-5 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase tracking-wider font-semibold min-w-48">Caleg</th>
                    @foreach($tpsList as $tps)
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase tracking-wider font-semibold whitespace-nowrap">
                        {{ $tps->nama }}
                    </th>
                    @endforeach
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase tracking-wider font-semibold">Total</th>
                </tr>
            </thead>
            <tbody>

            {{-- Baris suara partai --}}
            @php $partaiRowTotal = 0; @endphp
            <tr class="border-b dark:border-gray-700 border-gray-100 dark:bg-gray-700/30 bg-gray-50">
                <td class="px-5 py-3 text-xs font-bold dark:text-gray-300 text-gray-700 uppercase tracking-wider">
                    Suara Partai
                </td>
                @foreach($tpsList as $tps)
                @php
                    $r  = $rekaps[$tps->id] ?? null;
                    $sp = $r ? ($r->partaiSuaras->firstWhere('partai_id', $partai->id)?->suara ?? 0) : null;
                    $partaiRowTotal += $sp ?? 0;
                @endphp
                <td class="px-3 py-3 text-center font-semibold dark:text-gray-200 text-gray-700">
                    {{ $r ? number_format($sp) : '—' }}
                </td>
                @endforeach
                <td class="px-3 py-3 text-center font-bold text-orange-400">{{ number_format($partaiRowTotal) }}</td>
            </tr>

            {{-- Baris per caleg --}}
            @foreach($partai->calegs as $caleg)
            @php $calegRowTotal = 0; @endphp
            <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 dark:hover:bg-gray-750 hover:bg-gray-50">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs dark:text-gray-500 text-gray-400 w-4">{{ $caleg->nomor_urut }}.</span>
                        <span class="text-sm dark:text-gray-200 text-gray-700">{{ $caleg->nama_caleg }}</span>
                    </div>
                </td>
                @foreach($tpsList as $tps)
                @php
                    $r  = $rekaps[$tps->id] ?? null;
                    $sc = $r ? ($r->calegSuaras->firstWhere('caleg_id', $caleg->id)?->suara ?? 0) : null;
                    $calegRowTotal += $sc ?? 0;
                @endphp
                <td class="px-3 py-3 text-center dark:text-gray-400 text-gray-500">
                    {{ $r ? number_format($sc) : '—' }}
                </td>
                @endforeach
                <td class="px-3 py-3 text-center font-bold text-teal-400">{{ number_format($calegRowTotal) }}</td>
            </tr>
            @endforeach

            {{-- Baris total partai+caleg --}}
            @php
                $grandTotal = 0;
            @endphp
            <tr class="border-t-2 dark:border-gray-600 border-gray-300 dark:bg-gray-700/30 bg-gray-50">
                <td class="px-5 py-3 text-xs font-bold dark:text-gray-300 text-gray-700 uppercase tracking-wider">Total Suara Sah</td>
                @foreach($tpsList as $tps)
                @php
                    $r       = $rekaps[$tps->id] ?? null;
                    $sp      = $r ? ($r->partaiSuaras->firstWhere('partai_id', $partai->id)?->suara ?? 0) : 0;
                    $sc_sum  = $r ? $r->calegSuaras->whereIn('caleg_id', $partai->calegs->pluck('id'))->sum('suara') : 0;
                    $colTotal = $r ? ($sp + $sc_sum) : null;
                    $grandTotal += $colTotal ?? 0;
                @endphp
                <td class="px-3 py-3 text-center font-bold text-teal-400">
                    {{ $r ? number_format($colTotal) : '—' }}
                </td>
                @endforeach
                <td class="px-3 py-3 text-center font-bold text-teal-400">{{ number_format($grandTotal) }}</td>
            </tr>

            </tbody>
        </table>
    </div>
</div>
@endforeach
@endif

@endsection