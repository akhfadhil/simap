@extends('layouts.app')
@section('title', 'Verifikasi Dokumen')

@section('content')

{{-- Banner kalau admin/ppk lagi view-as-pps --}}
@if(isset($isAdminView) && $isAdminView)
<div class="bg-[#001a12] border border-[#2EC4B633] px-5 py-3 mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <span class="font-mono2 text-[10px] tracking-[2px] uppercase" style="color:#2EC4B6">
            👁 MODE VIEW
        </span>
        <span class="text-gray-600 text-xs font-mono2">
            @if(Auth::user()->role === 'admin')
                Anda melihat dashboard PPS sebagai admin
            @elseif(Auth::user()->role === 'ppk')
                Anda melihat dashboard PPS sebagai PPK
            @endif
        </span>
    </div>
    <a href="{{ Auth::user()->role === 'admin' ? route('admin.desa.index') : route('ppk.data-pps') }}"
       onclick="fetch('/clear-view-session')"
       class="font-mono2 text-[10px] tracking-[2px] uppercase hover:text-brand transition text-gray-600">
        ← KEMBALI
    </a>
</div>
@else
{{-- Tombol kembali untuk PPS login normal --}}
<div class="mb-4">
    <a href="{{ route('dashboard.pps') }}"
       class="inline-flex items-center gap-2 font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase hover:text-brand transition">
        ← KEMBALI KE DASHBOARD
    </a>
</div>
@endif

<div class="mb-8">
    <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-2">// PPS — Verifikasi Dokumen</p>
    <h1 class="font-display text-4xl tracking-[2px]" style="color:#2EC4B6">DOKUMEN TPS</h1>
    <p class="text-gray-500 text-sm mt-1">
        {{ isset($desa) ? $desa->nama . ' · ' . $desa->kecamatan->nama : (Auth::user()->desa->nama ?? '') }}
    </p>
</div>

@if(session('success'))
<div class="bg-teal-950 border border-teal-800 text-teal-400 px-4 py-3 font-mono2 text-xs mb-6">✓ {{ session('success') }}</div>
@endif

@forelse($tpsList as $tps)
@php
    $dokByJenis = $tps->dokumens->keyBy('jenis');
    $totalDok   = $tps->dokumens->count();
    $verified   = $tps->dokumens->where('status', 'terverifikasi')->count();
@endphp
<div class="bg-[#141414] border border-gray-800 mb-4">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800 cursor-pointer"
         onclick="toggleTps({{ $tps->id }})">
        <div>
            <p class="font-semibold text-sm">{{ $tps->nama }}</p>
            <p class="font-mono2 text-[10px] text-gray-600 mt-0.5">{{ $verified }}/5 dokumen terverifikasi</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-32 h-1 bg-gray-800 rounded-full">
                <div class="h-1 rounded-full transition-all"
                     style="width:{{ ($totalDok/5)*100 }}%; background:#2EC4B6"></div>
            </div>
            <span class="font-mono2 text-[10px] text-gray-600">{{ $totalDok }}/5</span>
            <span class="text-gray-600 text-xs" id="arrow-{{ $tps->id }}">▾</span>
        </div>
    </div>

    <div id="tps-{{ $tps->id }}">
    @foreach(App\Models\Dokumen::JENIS as $key => $label)
    @php $dok = $dokByJenis[$key] ?? null; @endphp
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-900 last:border-0 flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                 style="background: {{ $dok ? ($dok->status==='terverifikasi'?'#2EC4B6':'#F4A261') : '#333' }}"></div>
            <div>
                <p class="text-sm font-medium">{{ $label }}</p>
                @if($dok)
                <p class="font-mono2 text-[10px] text-gray-600 mt-0.5">
                    {{ $dok->file_name }} · {{ $dok->updated_at->diffForHumans() }}
                    @if($dok->verifier) · Diverifikasi oleh {{ $dok->verifier->name }} @endif
                </p>
                @else
                <p class="font-mono2 text-[10px] text-gray-700 mt-0.5">Belum diupload</p>
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
            {{-- Tombol verifikasi hanya muncul kalau bukan admin view --}}
            @if(!isset($isAdminView) && $dok->status === 'menunggu_verifikasi')
            <form method="POST" action="{{ route('dokumen.verifikasi', $dok) }}">
                @csrf
                <button class="px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest transition"
                        style="background:#2EC4B618;border:1px solid #2EC4B644;color:#2EC4B6"
                        onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                    ✓ VERIFIKASI
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
<div class="text-center py-20 text-gray-700 font-mono2 text-xs">Belum ada TPS di desa ini.</div>
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