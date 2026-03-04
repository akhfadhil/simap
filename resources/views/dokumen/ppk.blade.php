@extends('layouts.app')
@section('title', 'Dokumen Kecamatan')

@section('content')

{{-- Banner kalau admin lagi view-as-ppk --}}
@if(isset($isAdminView) && $isAdminView)
<div class="bg-[#1a1000] border border-[#F4A26133] px-5 py-3 mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <span class="font-mono2 text-[10px] tracking-[2px] uppercase" style="color:#F4A261">
            👁 MODE VIEW ADMIN
        </span>
        <span class="text-gray-600 text-xs font-mono2">
            Anda melihat dashboard PPK sebagai admin
        </span>
    </div>
    <a href="{{ route('admin.kecamatan.index') }}"
       onclick="fetch('/clear-view-session')"
       class="font-mono2 text-[10px] tracking-[2px] uppercase hover:text-brand transition text-gray-600">
        ← KEMBALI
    </a>
</div>
@else
{{-- Tombol kembali untuk PPK login normal --}}
<div class="mb-4">
    <a href="{{ route('dashboard.ppk') }}"
       class="inline-flex items-center gap-2 font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase hover:text-brand transition">
        ← KEMBALI KE DASHBOARD
    </a>
</div>
@endif

<div class="mb-8">
    <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-2">// PPK — Rekap Dokumen</p>
    <h1 class="font-display text-4xl tracking-[2px]" style="color:#F4A261">DOKUMEN KECAMATAN</h1>
    <p class="text-gray-500 text-sm mt-1">{{ $kecamatan->nama ?? '' }}</p>
</div>

{{-- Filter Desa --}}
<form method="GET" class="flex gap-3 mb-6">
    <select name="desa_id" onchange="this.form.submit()"
            class="bg-[#141414] border border-gray-800 text-gray-400 px-4 py-2.5 text-xs font-mono2 focus:border-brand focus:ring-0 focus:outline-none">
        <option value="">Semua Desa</option>
        @foreach($desas as $desa)
        <option value="{{ $desa->id }}" {{ request('desa_id') == $desa->id ? 'selected' : '' }}>
            {{ $desa->nama }}
        </option>
        @endforeach
    </select>
</form>

@forelse($tpsList as $tps)
@php $dokByJenis = $tps->dokumens->keyBy('jenis'); @endphp
<div class="bg-[#141414] border border-gray-800 mb-4">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800 cursor-pointer"
         onclick="toggleTps({{ $tps->id }})">
        <div>
            <p class="font-semibold text-sm">{{ $tps->nama }}</p>
            <p class="font-mono2 text-[10px] text-gray-600 mt-0.5">
                {{ $tps->desa->nama }} · {{ $tps->dokumens->count() }}/5 dokumen
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="w-24 h-1 bg-gray-800 rounded-full">
                <div class="h-1 rounded-full"
                     style="width:{{ ($tps->dokumens->count()/5)*100 }}%; background:#F4A261"></div>
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
            <button onclick="openPreview('{{ route('dokumen.preview', $dok) }}')" 
                    class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-gray-500 hover:text-gray-300 transition">
                PREVIEW
            </button>
            <a href="{{ route('dokumen.download', $dok) }}"
               class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-gray-500 hover:text-gray-300 transition">
                UNDUH
            </a>
        </div>
        @else
        <span class="font-mono2 text-[10px] text-gray-700">Belum diupload</span>
        @endif
    </div>
    @endforeach
    </div>
</div>
@empty
<div class="text-center py-20 text-gray-700 font-mono2 text-xs">Belum ada TPS.</div>
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