@extends('layouts.app')
@section('title', 'Semua Dokumen')

@section('content')

<div class="mb-8">
    <a href="{{ route('dashboard.admin') }}"
       class="inline-flex items-center gap-2 font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase hover:text-brand transition mb-4">
        ← KEMBALI KE DASHBOARD
    </a>
    <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-2">// Admin — Semua Dokumen</p>
    <h1 class="font-display text-4xl tracking-[2px] text-brand">REKAP DOKUMEN</h1>
</div>

{{-- Filter --}}
<form method="GET" class="flex gap-3 mb-8 flex-wrap" id="filter-form">
    <select name="kecamatan_id" onchange="this.form.submit()"
            class="bg-[#141414] border border-gray-800 text-gray-400 px-4 py-2.5 text-xs font-mono2 focus:border-brand focus:ring-0 focus:outline-none">
        <option value="">Semua Kecamatan</option>
        @foreach($kecamatans as $kec)
        <option value="{{ $kec->id }}" {{ request('kecamatan_id') == $kec->id ? 'selected' : '' }}>
            {{ $kec->nama }}
        </option>
        @endforeach
    </select>
    @if($desas->count())
    <select name="desa_id" onchange="this.form.submit()"
            class="bg-[#141414] border border-gray-800 text-gray-400 px-4 py-2.5 text-xs font-mono2 focus:border-brand focus:ring-0 focus:outline-none">
        <option value="">Semua Desa</option>
        @foreach($desas as $d)
        <option value="{{ $d->id }}" {{ request('desa_id') == $d->id ? 'selected' : '' }}>
            {{ $d->nama }}
        </option>
        @endforeach
    </select>
    @endif
</form>

{{-- ── Dokumen Kecamatan (PPK) ── --}}
@if($dokumenKecamatan->count())
<p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-4 pb-3 border-b border-gray-800">
    // Dokumen Kecamatan (PPK)
</p>

@foreach($dokumenKecamatan as $kecId => $dokList)
@php $kecNama = $dokList->first()->kecamatan->nama; @endphp
<div class="bg-[#141414] border border-gray-800 mb-4">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800 cursor-pointer"
         onclick="toggleTps('kec-{{ $kecId }}')">
        <div>
            <p class="font-semibold text-sm">{{ $kecNama }}</p>
            <p class="font-mono2 text-[10px] text-gray-600 mt-0.5">
                {{ $dokList->count() }}/5 dokumen · PPK
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="w-24 h-1 bg-gray-800 rounded-full">
                <div class="h-1 rounded-full bg-brand"
                     style="width:{{ ($dokList->count()/5)*100 }}%"></div>
            </div>
            <span id="arrow-kec-{{ $kecId }}" class="text-gray-600 text-xs">▾</span>
        </div>
    </div>

    <div id="tps-kec-{{ $kecId }}">
    @foreach(App\Models\Dokumen::JENIS as $key => $label)
    @php $dok = $dokList->firstWhere('jenis', $key); @endphp
    <div class="flex items-center justify-between px-6 py-3.5 border-b border-gray-900 last:border-0 flex-wrap gap-2">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-1.5 rounded-full"
                 style="background: {{ $dok ? ($dok->status==='terverifikasi'?'#2EC4B6':'#F4A261') : '#333' }}"></div>
            <div>
                <p class="text-sm">{{ $label }}</p>
                @if($dok)
                <p class="font-mono2 text-[10px] text-gray-600 mt-0.5">
                    Diupload oleh {{ $dok->uploader->name }}
                    @if($dok->verifier) · ✓ {{ $dok->verifier->name }} @endif
                </p>
                @endif
            </div>
        </div>
        @if($dok)
        <div class="flex items-center gap-2">
            <span class="font-mono2 text-[9px] tracking-widest uppercase px-2 py-1"
                  style="color:{{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }};
                         background:{{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }}15;
                         border:1px solid {{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }}33">
                {{ App\Models\Dokumen::STATUS_LABELS[$dok->status] }}
            </span>
            <a href="{{ route('dokumen.preview', $dok) }}" target="_blank"
               class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-gray-500 hover:text-gray-300 transition">
                PREVIEW
            </a>
            <a href="{{ route('dokumen.download', $dok) }}"
               class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-gray-500 hover:text-gray-300 transition">
                UNDUH
            </a>
            @if($dok->status === 'menunggu_verifikasi')
            <form method="POST" action="{{ route('dokumen.verifikasi.admin', $dok) }}">
                @csrf
                <button class="px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest transition"
                        style="background:#E6394618;border:1px solid #E6394644;color:#E63946"
                        onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                    ✓ VERIFIKASI
                </button>
            </form>
            @endif
        </div>
        @else
        <span class="font-mono2 text-[10px] text-gray-700">Belum diupload</span>
        @endif
    </div>
    @endforeach
    </div>
</div>
@endforeach
@endif

{{-- ── Dokumen TPS (KPPS) ── --}}
<p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-4 pb-3 border-b border-gray-800">
    // Dokumen TPS (KPPS)
</p>

@forelse($tpsList as $tps)
@php $dokByJenis = $tps->dokumens->keyBy('jenis'); @endphp
<div class="bg-[#141414] border border-gray-800 mb-4">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800 cursor-pointer"
         onclick="toggleTps({{ $tps->id }})">
        <div>
            <p class="font-semibold text-sm">{{ $tps->nama }}</p>
            <p class="font-mono2 text-[10px] text-gray-600 mt-0.5">
                {{ $tps->desa->nama }} · {{ $tps->desa->kecamatan->nama }} · {{ $tps->dokumens->count() }}/5 dokumen
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="w-24 h-1 bg-gray-800 rounded-full">
                <div class="h-1 rounded-full bg-brand"
                     style="width:{{ ($tps->dokumens->count()/5)*100 }}%"></div>
            </div>
            <span id="arrow-{{ $tps->id }}" class="text-gray-600 text-xs">▾</span>
        </div>
    </div>

    <div id="tps-{{ $tps->id }}">
    @foreach(App\Models\Dokumen::JENIS as $key => $label)
    @php $dok = $dokByJenis[$key] ?? null; @endphp
    <div class="flex items-center justify-between px-6 py-3.5 border-b border-gray-900 last:border-0 flex-wrap gap-2">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-1.5 rounded-full"
                 style="background: {{ $dok ? ($dok->status==='terverifikasi'?'#2EC4B6':'#F4A261') : '#333' }}"></div>
            <div>
                <p class="text-sm">{{ $label }}</p>
                @if($dok)
                <p class="font-mono2 text-[10px] text-gray-600 mt-0.5">
                    Diupload oleh {{ $dok->uploader->name }}
                    @if($dok->verifier) · ✓ {{ $dok->verifier->name }} @endif
                </p>
                @endif
            </div>
        </div>
        @if($dok)
        <div class="flex items-center gap-2">
            <span class="font-mono2 text-[9px] tracking-widest uppercase px-2 py-1"
                  style="color:{{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }};
                         background:{{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }}15;
                         border:1px solid {{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }}33">
                {{ App\Models\Dokumen::STATUS_LABELS[$dok->status] }}
            </span>
            <a href="{{ route('dokumen.preview', $dok) }}" target="_blank"
               class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-gray-500 hover:text-gray-300 transition">
                PREVIEW
            </a>
            <a href="{{ route('dokumen.download', $dok) }}"
               class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-gray-500 hover:text-gray-300 transition">
                UNDUH
            </a>
            @if($dok->status === 'menunggu_verifikasi')
            <form method="POST" action="{{ route('dokumen.verifikasi.admin', $dok) }}">
                @csrf
                <button class="px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest transition"
                        style="background:#E6394618;border:1px solid #E6394644;color:#E63946"
                        onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                    ✓ VERIFIKASI
                </button>
            </form>
            @endif
        </div>
        @else
        <span class="font-mono2 text-[10px] text-gray-700">Belum diupload</span>
        @endif
    </div>
    @endforeach
    </div>
</div>
@empty
<div class="text-center py-20 text-gray-700 font-mono2 text-xs">Belum ada data.</div>
@endforelse

@push('scripts')
<script>
function toggleTps(id) {
    const el    = document.getElementById('tps-' + id);
    const arrow = document.getElementById('arrow-' + id);
    el.classList.toggle('hidden');
    arrow.textContent = el.classList.contains('hidden') ? '▸' : '▾';
}
</script>
@endpush

@endsection