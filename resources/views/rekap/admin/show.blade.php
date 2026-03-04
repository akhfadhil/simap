@extends('layouts.app')
@section('title', 'Rekap ' . \App\Models\RekapHeader::JENIS_LABELS[$jenis])

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.rekap.index') }}{{ request('kecamatan_id') ? '?kecamatan_id='.request('kecamatan_id') : '' }}"
       class="inline-flex items-center gap-2 text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition font-medium mb-4">
        ← Kembali
    </a>
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// Admin — Rekapitulasi</p>
    <h1 class="font-display text-4xl tracking-[2px] text-red-600">
        {{ strtoupper(\App\Models\RekapHeader::JENIS_LABELS[$jenis]) }}
    </h1>
</div>

{{-- Filter --}}
<form method="GET" class="flex gap-3 mb-8 items-center">
    <select name="kecamatan_id" onchange="this.form.submit()"
            class="dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-4 py-2.5 text-xs rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
        <option value="">Semua Kecamatan</option>
        @foreach($kecamatans as $kec)
        <option value="{{ $kec->id }}" {{ request('kecamatan_id') == $kec->id ? 'selected' : '' }}>
            {{ $kec->nama }}
        </option>
        @endforeach
    </select>
    @if(request('kecamatan_id'))
    <a href="{{ route('admin.rekap.show', $jenis) }}"
       class="text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition">
        × Reset
    </a>
    @endif
</form>

{{-- Summary --}}
@php
    $totalDpt    = $rekaps->sum(fn($r) => $r->dpt_lk + $r->dpt_pr);
    $totalHadir  = $rekaps->sum(fn($r) => $r->total_pengguna_lk + $r->total_pengguna_pr);
    $totalTdkSah = $rekaps->sum('suara_tidak_sah');
    $totalFinal  = $rekaps->where('status','final')->count();
    $totalRekap  = $rekaps->count();
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="dark:bg-gray-800 bg-white rounded-xl p-5 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">Total DPT</p>
        <p class="font-display text-3xl text-red-600">{{ number_format($totalDpt) }}</p>
    </div>
    <div class="dark:bg-gray-800 bg-white rounded-xl p-5 border dark:border-gray-700 border-gray-200 shadow-sm">
        <p class="text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">Total Hadir</p>
        <p class="font-display text-3xl text-red-600">{{ number_format($totalHadir) }}</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">
            {{ $totalDpt > 0 ? round(($totalHadir/$totalDpt)*100,1) : 0 }}% partisipasi
        </p>
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

{{-- Konten per kecamatan --}}
@php
    $kecamatanList = request('kecamatan_id')
        ? $kecamatans->where('id', request('kecamatan_id'))
        : $kecamatans;
@endphp

@forelse($kecamatanList as $kecamatan)
@php
    $kecTpsIds = \App\Models\Tps::whereHas('desa', fn($q) => $q->where('kecamatan_id', $kecamatan->id))->pluck('id');
    $kecRekaps = $rekaps->whereIn('tps_id', $kecTpsIds->toArray());
    $kecFinal  = $kecRekaps->where('status','final')->count();
@endphp
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm mb-4 overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700 border-gray-200 cursor-pointer dark:hover:bg-gray-750 hover:bg-gray-50 transition"
         onclick="toggleKec({{ $kecamatan->id }})">
        <div>
            <p class="font-semibold text-sm dark:text-gray-100 text-gray-800">{{ $kecamatan->nama }}</p>
            <p class="text-[11px] dark:text-gray-500 text-gray-400 mt-0.5">
                {{ $kecFinal }}/{{ $kecTpsIds->count() }} TPS difinalisasi
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="w-24 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                <div class="h-1.5 rounded-full bg-red-500"
                     style="width:{{ $kecTpsIds->count() > 0 ? round(($kecFinal/$kecTpsIds->count())*100) : 0 }}%"></div>
            </div>
            <span id="arrow-kec-{{ $kecamatan->id }}" class="dark:text-gray-500 text-gray-400 text-xs">▾</span>
        </div>
    </div>

    <div id="kec-{{ $kecamatan->id }}">
    @foreach($kecamatan->desas as $desa)
    @php $desaTpsIds = $desa->tps->pluck('id'); @endphp
    <div class="border-b dark:border-gray-700 border-gray-100 last:border-0">
        {{-- Sub-header desa --}}
        <div class="flex items-center justify-between px-6 py-3 dark:bg-gray-700/30 bg-gray-50 border-b dark:border-gray-700 border-gray-100 cursor-pointer"
             onclick="toggleDesa({{ $desa->id }})">
            <p class="text-xs font-semibold dark:text-gray-300 text-gray-600">{{ $desa->nama }}</p>
            <span id="arrow-desa-{{ $desa->id }}" class="dark:text-gray-500 text-gray-400 text-xs">▾</span>
        </div>

        <div id="desa-{{ $desa->id }}">
        @if(in_array($jenis, ['ppwp','dpd']))
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-700 border-gray-100">
                        <th class="text-left px-5 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">TPS</th>
                        <th class="text-center px-3 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">DPT</th>
                        <th class="text-center px-3 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Hadir</th>
                        @foreach($master['calons'] as $calon)
                        <th class="text-center px-3 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">No.{{ $calon->nomor_urut }}</th>
                        @endforeach
                        <th class="text-center px-3 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Tdk Sah</th>
                        <th class="text-center px-3 py-2.5 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($desa->tps as $tps)
                @php
                    $r        = $rekaps[$tps->id] ?? null;
                    $suaraMap = $r ? ($jenis === 'ppwp'
                        ? $r->ppwpSuaras->pluck('suara','calon_id')
                        : $r->dpdSuaras->pluck('suara','calon_id')) : collect();
                @endphp
                <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 dark:hover:bg-gray-750 hover:bg-gray-50">
                    <td class="px-5 py-2.5 font-medium dark:text-gray-200 text-gray-700">{{ $tps->nama }}</td>
                    <td class="px-3 py-2.5 text-center dark:text-gray-400 text-gray-500">{{ $r ? number_format($r->dpt_lk+$r->dpt_pr) : '—' }}</td>
                    <td class="px-3 py-2.5 text-center dark:text-gray-400 text-gray-500">{{ $r ? number_format($r->total_pengguna_lk+$r->total_pengguna_pr) : '—' }}</td>
                    @foreach($master['calons'] as $calon)
                    <td class="px-3 py-2.5 text-center font-semibold dark:text-gray-200 text-gray-700">
                        {{ $r ? number_format($suaraMap[$calon->id] ?? 0) : '—' }}
                    </td>
                    @endforeach
                    <td class="px-3 py-2.5 text-center dark:text-gray-400 text-gray-500">{{ $r ? number_format($r->suara_tidak_sah) : '—' }}</td>
                    <td class="px-3 py-2.5 text-center">
                        @if(!$r)
                            <span class="text-[9px] px-2 py-1 rounded font-semibold bg-gray-500/20 dark:text-gray-400 text-gray-500 border border-gray-400/30">Kosong</span>
                        @elseif($r->status === 'final')
                            <span class="text-[9px] px-2 py-1 rounded font-semibold bg-teal-500/20 text-teal-400 border border-teal-500/40">Final</span>
                        @else
                            <span class="text-[9px] px-2 py-1 rounded font-semibold bg-orange-400/20 text-orange-400 border border-orange-400/40">Draft</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{-- Legend --}}
        <div class="p-4 border-t dark:border-gray-700 border-gray-100 flex flex-wrap gap-3">
            @foreach($master['calons'] as $calon)
            <span class="text-xs dark:text-gray-400 text-gray-500">
                <span class="font-semibold dark:text-gray-200 text-gray-700">No.{{ $calon->nomor_urut }}</span>
                = {{ $jenis === 'ppwp' ? $calon->nama_paslon : $calon->nama_calon }}
            </span>
            @endforeach
        </div>

        @else
        {{-- Partai --}}
        @foreach($master['partais'] as $partai)
        <div class="border-b dark:border-gray-700 border-gray-100 last:border-0">
            <div class="px-6 py-2 dark:bg-gray-700/20 bg-gray-50 border-b dark:border-gray-700 border-gray-100 flex items-center gap-2">
                <span class="w-5 h-5 rounded bg-red-500 text-white text-[9px] font-bold flex items-center justify-center">{{ $partai->nomor_urut }}</span>
                <p class="text-xs font-semibold dark:text-gray-300 text-gray-600">{{ $partai->nama_partai }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b dark:border-gray-700 border-gray-100">
                            <th class="text-left px-5 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">TPS</th>
                            <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Partai</th>
                            @foreach($partai->calegs as $caleg)
                            <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold whitespace-nowrap">
                                {{ $caleg->nomor_urut }}. {{ \Str::limit($caleg->nama_caleg, 12) }}
                            </th>
                            @endforeach
                            <th class="text-center px-3 py-2 text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php $totalP = 0; $totalC = []; @endphp
                    @foreach($desa->tps as $tps)
                    @php
                        $r  = $rekaps[$tps->id] ?? null;
                        $sp = $r ? ($r->partaiSuaras->firstWhere('partai_id',$partai->id)?->suara ?? 0) : null;
                        $totalP += $sp ?? 0;
                        $rowTotal = $sp ?? 0;
                    @endphp
                    <tr class="border-b dark:border-gray-700 border-gray-100 last:border-0 dark:hover:bg-gray-750 hover:bg-gray-50">
                        <td class="px-5 py-2.5 dark:text-gray-300 text-gray-600">{{ $tps->nama }}</td>
                        <td class="px-3 py-2.5 text-center font-semibold dark:text-gray-200 text-gray-700">{{ $r ? number_format($sp) : '—' }}</td>
                        @foreach($partai->calegs as $caleg)
                        @php
                            $sc = $r ? ($r->calegSuaras->firstWhere('caleg_id',$caleg->id)?->suara ?? 0) : null;
                            $totalC[$caleg->id] = ($totalC[$caleg->id] ?? 0) + ($sc ?? 0);
                            $rowTotal += $sc ?? 0;
                        @endphp
                        <td class="px-3 py-2.5 text-center dark:text-gray-400 text-gray-500">{{ $r ? number_format($sc) : '—' }}</td>
                        @endforeach
                        <td class="px-3 py-2.5 text-center font-semibold text-red-500">{{ $r ? number_format($rowTotal) : '—' }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="border-t dark:border-gray-600 border-gray-300">
                        <tr class="dark:bg-gray-700/30 bg-gray-50">
                            <td class="px-5 py-2 text-xs font-bold dark:text-gray-300 text-gray-700">Total</td>
                            <td class="px-3 py-2 text-center text-xs font-bold text-red-500">{{ number_format($totalP) }}</td>
                            @foreach($partai->calegs as $caleg)
                            <td class="px-3 py-2 text-center text-xs font-bold text-teal-400">{{ number_format($totalC[$caleg->id] ?? 0) }}</td>
                            @endforeach
                            <td class="px-3 py-2 text-center text-xs font-bold text-red-500">{{ number_format($totalP + array_sum($totalC)) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endforeach
        @endif
        </div>
    </div>
    @endforeach
    </div>
</div>
@empty
<div class="text-center py-16 dark:text-gray-600 text-gray-400">Belum ada data.</div>
@endforelse

@push('scripts')
<script>
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
</script>
@endpush
@endsection