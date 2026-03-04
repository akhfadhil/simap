@extends('layouts.app')
@section('title', 'Verifikasi Dokumen')

@section('content')

{{-- Banner view mode --}}
@if(isset($isAdminView) && $isAdminView)
<div class="dark:bg-teal-950 bg-teal-50 border dark:border-teal-900 border-teal-200 px-5 py-3 mb-6 rounded-lg flex items-center justify-between">
    <div class="flex items-center gap-3">
        <span class="text-teal-400 text-xs font-semibold">👁 MODE VIEW</span>
        <span class="dark:text-gray-400 text-gray-500 text-xs">
            @if(Auth::user()->role === 'admin')
                Anda melihat dashboard PPS sebagai admin
            @elseif(Auth::user()->role === 'ppk')
                Anda melihat dashboard PPS sebagai PPK
            @endif
        </span>
    </div>
    <a href="{{ Auth::user()->role === 'admin' ? route('admin.desa.index') : route('ppk.data-pps') }}"
       onclick="fetch('/clear-view-session')"
       class="text-xs font-semibold dark:text-gray-400 text-gray-500 hover:text-red-500 transition">
        ← Kembali
    </a>
</div>
@else
<div class="mb-4">
    <a href="{{ route('dashboard.pps') }}"
       class="inline-flex items-center gap-2 text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition font-medium">
        ← Kembali ke Dashboard
    </a>
</div>
@endif

<div class="mb-8">
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// PPS — Verifikasi Dokumen</p>
    <h1 class="font-display text-4xl tracking-[2px] text-teal-400">DOKUMEN TPS</h1>
    <p class="dark:text-gray-400 text-gray-500 text-sm mt-1">
        {{ isset($desa) ? $desa->nama . ' · ' . $desa->kecamatan->nama : (Auth::user()->desa->nama ?? '') }}
    </p>
</div>

@if(session('success'))
<div class="bg-teal-50 dark:bg-teal-950 border border-teal-200 dark:border-teal-800 text-teal-600 dark:text-teal-400 px-4 py-3 text-xs mb-6 rounded-lg font-medium">
    ✓ {{ session('success') }}
</div>
@endif

@forelse($tpsList as $tps)
@php
    $dokByJenis = $tps->dokumens->keyBy('jenis');
    $totalDok   = $tps->dokumens->count();
    $verified   = $tps->dokumens->where('status', 'terverifikasi')->count();
@endphp
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 mb-4 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700 border-gray-200 cursor-pointer dark:hover:bg-gray-750 hover:bg-gray-50 transition"
         onclick="toggleTps({{ $tps->id }})">
        <div>
            <p class="font-semibold text-sm dark:text-gray-100 text-gray-800">{{ $tps->nama }}</p>
            <p class="text-[11px] dark:text-gray-500 text-gray-400 mt-0.5">{{ $verified }}/5 dokumen terverifikasi</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-32 h-1.5 dark:bg-gray-700 bg-gray-200 rounded-full">
                <div class="h-1.5 rounded-full transition-all bg-teal-400"
                     style="width:{{ ($totalDok/5)*100 }}%"></div>
            </div>
            <span class="text-[11px] dark:text-gray-500 text-gray-400">{{ $totalDok }}/5</span>
            <span class="dark:text-gray-500 text-gray-400 text-xs" id="arrow-{{ $tps->id }}">▾</span>
        </div>
    </div>

    <div id="tps-{{ $tps->id }}">
    @foreach(App\Models\Dokumen::JENIS as $key => $label)
    @php $dok = $dokByJenis[$key] ?? null; @endphp
    <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700 border-gray-100 last:border-0 flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full flex-shrink-0"
                 style="background: {{ $dok ? ($dok->status==='terverifikasi'?'#2EC4B6':'#F4A261') : '#9CA3AF' }}"></div>
            <div>
                <p class="text-sm font-medium dark:text-gray-200 text-gray-700">{{ $label }}</p>
                @if($dok)
                <p class="text-[11px] dark:text-gray-500 text-gray-400 mt-0.5">
                    {{ $dok->file_name }} · {{ $dok->updated_at->diffForHumans() }}
                    @if($dok->verifier) · ✓ {{ $dok->verifier->name }} @endif
                </p>
                @else
                <p class="text-[11px] dark:text-gray-600 text-gray-400 mt-0.5">Belum diupload</p>
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
            @if($dok->status === 'menunggu_verifikasi' && (!isset($isAdminView) || !$isAdminView))
            <form method="POST" action="{{ route('dokumen.verifikasi', $dok) }}">
                @csrf
                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-teal-500 hover:bg-teal-600 text-white transition">
                    ✓ Verifikasi
                </button>
            </form>
            @endif
        </div>
        @endif
    </div>
    @endforeach
    </div>
</div>
@empty
<div class="text-center py-20 dark:text-gray-600 text-gray-400 text-sm">Belum ada TPS di desa ini.</div>
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