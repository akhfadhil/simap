@extends('layouts.app')
@section('title', 'Semua Dokumen')

@section('content')

<div class="mb-8">
    <a href="{{ route('dashboard.admin') }}"
       class="inline-flex items-center gap-2 text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition font-medium mb-4">
        ← Kembali ke Dashboard
    </a>
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// Admin — Semua Dokumen</p>
    <h1 class="font-display text-4xl tracking-[2px] text-red-600">REKAP DOKUMEN</h1>
</div>

{{-- Filter --}}
<form method="GET" class="flex gap-3 mb-8 flex-wrap">
    <select name="kecamatan_id" onchange="this.form.submit()"
            class="dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-4 py-2.5 text-xs rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
        <option value="">Semua Kecamatan</option>
        @foreach($kecamatans as $kec)
        <option value="{{ $kec->id }}" {{ request('kecamatan_id') == $kec->id ? 'selected' : '' }}>
            {{ $kec->nama }}
        </option>
        @endforeach
    </select>
    @if($desas->count())
    <select name="desa_id" onchange="this.form.submit()"
            class="dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-4 py-2.5 text-xs rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
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
<p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-4 pb-3 border-b dark:border-gray-800 border-gray-200 font-semibold">
    // Dokumen Kecamatan (PPK)
</p>

@foreach($dokumenKecamatan as $kecId => $dokList)
@php $kecNama = $dokList->first()->kecamatan->nama; @endphp
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 mb-4 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700 border-gray-200 cursor-pointer hover:dark:bg-gray-750 hover:bg-gray-50 transition"
         onclick="toggleTps('kec-{{ $kecId }}')">
        <div>
            <p class="font-semibold text-sm dark:text-gray-100 text-gray-800">{{ $kecNama }}</p>
            <p class="text-[11px] dark:text-gray-500 text-gray-400 mt-0.5">
                {{ $dokList->count() }}/5 dokumen · PPK
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="w-24 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                <div class="h-1.5 rounded-full bg-red-500"
                     style="width:{{ ($dokList->count()/5)*100 }}%"></div>
            </div>
            <span id="arrow-kec-{{ $kecId }}" class="dark:text-gray-500 text-gray-400 text-xs">▾</span>
        </div>
    </div>

    <div id="tps-kec-{{ $kecId }}">
    @foreach(App\Models\Dokumen::JENIS as $key => $label)
    @php $dok = $dokList->firstWhere('jenis', $key); @endphp
    <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700 border-gray-100 last:border-0 flex-wrap gap-2">
        <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full flex-shrink-0"
                 style="background: {{ $dok ? ($dok->status==='terverifikasi'?'#2EC4B6':'#F4A261') : '#9CA3AF' }}"></div>
            <div>
                <p class="text-sm dark:text-gray-200 text-gray-700">{{ $label }}</p>
                @if($dok)
                <p class="text-[11px] dark:text-gray-500 text-gray-400 mt-0.5">
                    Diupload oleh {{ $dok->uploader->name }}
                    @if($dok->verifier) · ✓ {{ $dok->verifier->name }} @endif
                </p>
                @endif
            </div>
        </div>
        @if($dok)
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-[9px] tracking-widest uppercase px-2 py-1 rounded font-semibold"
                  style="color:{{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }};
                         background:{{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }}20;
                         border:1px solid {{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }}40">
                {{ App\Models\Dokumen::STATUS_LABELS[$dok->status] }}
            </span>
            <button onclick="openPreview('{{ route('dokumen.preview', $dok) }}')"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium border dark:border-gray-600 border-gray-300 dark:text-gray-400 text-gray-500 dark:hover:bg-gray-700 hover:bg-gray-100 transition">
                Preview
            </button>
            <a href="{{ route('dokumen.download', $dok) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-medium border dark:border-gray-600 border-gray-300 dark:text-gray-400 text-gray-500 dark:hover:bg-gray-700 hover:bg-gray-100 transition">
                Unduh
            </a>
            @if($dok->status === 'menunggu_verifikasi')
            <form method="POST" action="{{ route('dokumen.verifikasi.admin', $dok) }}">
                @csrf
                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-500 hover:bg-red-600 text-white transition">
                    ✓ Verifikasi
                </button>
            </form>
            @endif
        </div>
        @else
        <span class="text-[11px] dark:text-gray-600 text-gray-400">Belum diupload</span>
        @endif
    </div>
    @endforeach
    </div>
</div>
@endforeach
@endif

{{-- ── Dokumen TPS (KPPS) ── --}}
<p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-4 pb-3 border-b dark:border-gray-800 border-gray-200 font-semibold">
    // Dokumen TPS (KPPS)
</p>

@forelse($tpsList as $tps)
@php $dokByJenis = $tps->dokumens->keyBy('jenis'); @endphp
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 mb-4 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700 border-gray-200 cursor-pointer hover:dark:bg-gray-750 hover:bg-gray-50 transition"
         onclick="toggleTps({{ $tps->id }})">
        <div>
            <p class="font-semibold text-sm dark:text-gray-100 text-gray-800">{{ $tps->nama }}</p>
            <p class="text-[11px] dark:text-gray-500 text-gray-400 mt-0.5">
                {{ $tps->desa->nama }} · {{ $tps->desa->kecamatan->nama }} · {{ $tps->dokumens->count() }}/5 dokumen
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="w-24 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                <div class="h-1.5 rounded-full bg-red-500"
                     style="width:{{ ($tps->dokumens->count()/5)*100 }}%"></div>
            </div>
            <span id="arrow-{{ $tps->id }}" class="dark:text-gray-500 text-gray-400 text-xs">▾</span>
        </div>
    </div>

    <div id="tps-{{ $tps->id }}">
    @foreach(App\Models\Dokumen::JENIS as $key => $label)
    @php $dok = $dokByJenis[$key] ?? null; @endphp
    <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700 border-gray-100 last:border-0 flex-wrap gap-2">
        <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full flex-shrink-0"
                 style="background: {{ $dok ? ($dok->status==='terverifikasi'?'#2EC4B6':'#F4A261') : '#9CA3AF' }}"></div>
            <div>
                <p class="text-sm dark:text-gray-200 text-gray-700">{{ $label }}</p>
                @if($dok)
                <p class="text-[11px] dark:text-gray-500 text-gray-400 mt-0.5">
                    Diupload oleh {{ $dok->uploader->name }}
                    @if($dok->verifier) · ✓ {{ $dok->verifier->name }} @endif
                </p>
                @endif
            </div>
        </div>
        @if($dok)
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-[9px] tracking-widest uppercase px-2 py-1 rounded font-semibold"
                  style="color:{{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }};
                         background:{{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }}20;
                         border:1px solid {{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }}40">
                {{ App\Models\Dokumen::STATUS_LABELS[$dok->status] }}
            </span>
            <button onclick="openPreview('{{ route('dokumen.preview', $dok) }}')"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium border dark:border-gray-600 border-gray-300 dark:text-gray-400 text-gray-500 dark:hover:bg-gray-700 hover:bg-gray-100 transition">
                Preview
            </button>
            <a href="{{ route('dokumen.download', $dok) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-medium border dark:border-gray-600 border-gray-300 dark:text-gray-400 text-gray-500 dark:hover:bg-gray-700 hover:bg-gray-100 transition">
                Unduh
            </a>
            @if($dok->status === 'menunggu_verifikasi')
            <form method="POST" action="{{ route('dokumen.verifikasi.admin', $dok) }}">
                @csrf
                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-500 hover:bg-red-600 text-white transition">
                    ✓ Verifikasi
                </button>
            </form>
            @endif
        </div>
        @else
        <span class="text-[11px] dark:text-gray-600 text-gray-400">Belum diupload</span>
        @endif
    </div>
    @endforeach
    </div>
</div>
@empty
<div class="text-center py-20 dark:text-gray-600 text-gray-400 text-sm">Belum ada data.</div>
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