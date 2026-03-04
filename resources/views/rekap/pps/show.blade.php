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

{{-- Summary Cards --}}
@php
    $totalDpt   = $rekaps->sum(fn($r) => $r->dpt_lk + $r->dpt_pr);
    $totalHadir = $rekaps->sum(fn($r) => $r->total_pengguna_lk + $r->total_pengguna_pr);
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

{{-- Tabel per TPS --}}
@if(in_array($jenis, ['ppwp','dpd']))
{{-- ── PPWP / DPD: tabel horizontal ── --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b dark:border-gray-700 border-gray-200">
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Perolehan Suara per TPS</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b dark:border-gray-700 border-gray-200">
                    <th class="text-left px-5 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase tracking-wider font-semibold">TPS</th>
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase tracking-wider font-semibold">DPT</th>
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase tracking-wider font-semibold">Hadir</th>
                    @foreach($master['calons'] as $calon)
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase tracking-wider font-semibold">
                        No.{{ $calon->nomor_urut }}
                    </th>
                    @endforeach
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase tracking-wider font-semibold">Tdk Sah</th>
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase tracking-wider font-semibold">Status</th>
                </tr>
            </thead>
            <tbody>
            @php $totalPerCalon = []; @endphp
            @forelse($tpsList as $tps)
            @php
                $r        = $rekaps[$tps->id] ?? null;
                $suaraMap = $r ? ($jenis === 'ppwp'
                    ? $r->ppwpSuaras->pluck('suara','calon_id')
                    : $r->dpdSuaras->pluck('suara','calon_id')) : collect();
            @endphp
            <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 dark:hover:bg-gray-750 hover:bg-gray-50">
                <td class="px-5 py-3 font-medium dark:text-gray-200 text-gray-700">{{ $tps->nama }}</td>
                <td class="px-3 py-3 text-center dark:text-gray-400 text-gray-500">{{ $r ? number_format($r->dpt_lk+$r->dpt_pr) : '—' }}</td>
                <td class="px-3 py-3 text-center dark:text-gray-400 text-gray-500">{{ $r ? number_format($r->total_pengguna_lk+$r->total_pengguna_pr) : '—' }}</td>
                @foreach($master['calons'] as $calon)
                @php
                    $s = $suaraMap[$calon->id] ?? null;
                    $totalPerCalon[$calon->id] = ($totalPerCalon[$calon->id] ?? 0) + ($s ?? 0);
                @endphp
                <td class="px-3 py-3 text-center font-semibold dark:text-gray-200 text-gray-700">{{ $r ? number_format($s ?? 0) : '—' }}</td>
                @endforeach
                <td class="px-3 py-3 text-center dark:text-gray-400 text-gray-500">{{ $r ? number_format($r->suara_tidak_sah) : '—' }}</td>
                <td class="px-3 py-3 text-center">
                    @if(!$r)
                        <span class="text-[9px] px-2 py-1 rounded font-semibold bg-gray-500/20 dark:text-gray-400 text-gray-500 border border-gray-400/30">Kosong</span>
                    @elseif($r->status === 'final')
                        <span class="text-[9px] px-2 py-1 rounded font-semibold bg-teal-500/20 text-teal-400 border border-teal-500/40">Final</span>
                    @else
                        <span class="text-[9px] px-2 py-1 rounded font-semibold bg-orange-400/20 text-orange-400 border border-orange-400/40">Draft</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="20" class="text-center py-10 dark:text-gray-600 text-gray-400">Belum ada TPS.</td></tr>
            @endforelse
            </tbody>
            {{-- Baris total --}}
            <tfoot class="border-t-2 dark:border-gray-600 border-gray-300">
                <tr class="dark:bg-gray-700/30 bg-gray-50">
                    <td class="px-5 py-3 text-xs font-bold dark:text-gray-300 text-gray-700 uppercase">TOTAL</td>
                    <td class="px-3 py-3 text-center text-xs font-bold dark:text-gray-300 text-gray-700">{{ number_format($totalDpt) }}</td>
                    <td class="px-3 py-3 text-center text-xs font-bold dark:text-gray-300 text-gray-700">{{ number_format($totalHadir) }}</td>
                    @foreach($master['calons'] as $calon)
                    <td class="px-3 py-3 text-center text-xs font-bold text-teal-400">{{ number_format($totalPerCalon[$calon->id] ?? 0) }}</td>
                    @endforeach
                    <td class="px-3 py-3 text-center text-xs font-bold dark:text-gray-300 text-gray-700">{{ number_format($totalTdkSah) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Legend nama calon --}}
    <div class="p-5 border-t dark:border-gray-700 border-gray-200">
        <p class="text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold mb-2 tracking-wider">Keterangan</p>
        <div class="flex flex-wrap gap-3">
        @foreach($master['calons'] as $calon)
        <span class="text-xs dark:text-gray-400 text-gray-500">
            <span class="font-semibold dark:text-gray-200 text-gray-700">No.{{ $calon->nomor_urut }}</span>
            = {{ $jenis === 'ppwp' ? $calon->nama_paslon : $calon->nama_calon }}
        </span>
        @endforeach
        </div>
    </div>
</div>

@else
{{-- ── DPR RI / DPRD: per partai ── --}}
@foreach($master['partais'] as $partai)
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden mb-4">
    <div class="px-6 py-4 border-b dark:border-gray-700 border-gray-200 flex items-center gap-3 dark:bg-gray-750 bg-gray-50">
        <span class="w-7 h-7 rounded-lg bg-orange-400 text-white text-xs font-bold flex items-center justify-center">{{ $partai->nomor_urut }}</span>
        <p class="font-semibold dark:text-gray-100 text-gray-800">{{ $partai->nama_partai }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b dark:border-gray-700 border-gray-200">
                    <th class="text-left px-5 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase tracking-wider font-semibold">TPS</th>
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase tracking-wider font-semibold">Suara Partai</th>
                    @foreach($partai->calegs as $caleg)
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase tracking-wider font-semibold whitespace-nowrap">
                        {{ $caleg->nomor_urut }}. {{ \Str::limit($caleg->nama_caleg, 15) }}
                    </th>
                    @endforeach
                    <th class="text-center px-3 py-3 text-[10px] dark:text-gray-500 text-gray-400 uppercase tracking-wider font-semibold">Total</th>
                </tr>
            </thead>
            <tbody>
            @php $totalPartai = 0; $totalCaleg = []; @endphp
            @forelse($tpsList as $tps)
            @php
                $r = $rekaps[$tps->id] ?? null;
                $sp = $r ? ($r->partaiSuaras->firstWhere('partai_id', $partai->id)?->suara ?? 0) : null;
                $totalPartai += $sp ?? 0;
                $rowTotal = $sp ?? 0;
            @endphp
            <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 dark:hover:bg-gray-750 hover:bg-gray-50">
                <td class="px-5 py-3 font-medium dark:text-gray-200 text-gray-700">{{ $tps->nama }}</td>
                <td class="px-3 py-3 text-center font-semibold dark:text-gray-200 text-gray-700">{{ $r ? number_format($sp) : '—' }}</td>
                @foreach($partai->calegs as $caleg)
                @php
                    $sc = $r ? ($r->calegSuaras->firstWhere('caleg_id', $caleg->id)?->suara ?? 0) : null;
                    $totalCaleg[$caleg->id] = ($totalCaleg[$caleg->id] ?? 0) + ($sc ?? 0);
                    $rowTotal += $sc ?? 0;
                @endphp
                <td class="px-3 py-3 text-center dark:text-gray-400 text-gray-500">{{ $r ? number_format($sc) : '—' }}</td>
                @endforeach
                <td class="px-3 py-3 text-center font-semibold text-teal-400">{{ $r ? number_format($rowTotal) : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="20" class="text-center py-8 dark:text-gray-600 text-gray-400">Belum ada TPS.</td></tr>
            @endforelse
            </tbody>
            <tfoot class="border-t-2 dark:border-gray-600 border-gray-300">
                <tr class="dark:bg-gray-700/30 bg-gray-50">
                    <td class="px-5 py-3 text-xs font-bold dark:text-gray-300 text-gray-700 uppercase">Total</td>
                    <td class="px-3 py-3 text-center text-xs font-bold text-orange-400">{{ number_format($totalPartai) }}</td>
                    @foreach($partai->calegs as $caleg)
                    <td class="px-3 py-3 text-center text-xs font-bold text-teal-400">{{ number_format($totalCaleg[$caleg->id] ?? 0) }}</td>
                    @endforeach
                    <td class="px-3 py-3 text-center text-xs font-bold text-teal-400">
                        {{ number_format($totalPartai + array_sum($totalCaleg)) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endforeach
@endif

@endsection