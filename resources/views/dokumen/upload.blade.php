@extends('layouts.app')
@section('title', 'Upload Dokumen')

@section('content')

{{-- Banner kalau admin lagi view-as-kpps --}}
@if(isset($isAdminView) && $isAdminView)
<div class="bg-[#0d0d1a] border border-[#A8DADC33] px-5 py-3 mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <span class="font-mono2 text-[10px] tracking-[2px] uppercase" style="color:#A8DADC">
            👁 MODE VIEW
        </span>
        <span class="text-gray-600 text-xs font-mono2">
            @if(Auth::user()->role === 'admin')
                Anda melihat dashboard KPPS sebagai admin
            @else
                Anda melihat dashboard KPPS sebagai PPS
            @endif
        </span>
    </div>
    <a href="{{ Auth::user()->role === 'admin' ? route('admin.tps.index') : route('pps.data-tps') }}"
       onclick="fetch('/clear-view-session')"
       class="font-mono2 text-[10px] tracking-[2px] uppercase hover:text-brand transition text-gray-600">
        ← KEMBALI
    </a>
</div>
@else
{{-- Tombol kembali untuk KPPS login normal --}}
<div class="mb-4">
    <a href="{{ route('dashboard.kpps') }}"
       class="inline-flex items-center gap-2 font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase hover:text-brand transition">
        ← KEMBALI KE DASHBOARD
    </a>
</div>
@endif

<div class="mb-8">
    <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-2">
        // KPPS — {{ $tps->desa->kecamatan->nama }}
    </p>
    <h1 class="font-display text-4xl tracking-[2px]" style="color:#A8DADC">UPLOAD DOKUMEN</h1>
    <p class="text-gray-500 text-sm mt-1">{{ $tps->nama }} · {{ $tps->desa->nama }}</p>
</div>

@if(session('success'))
<div class="bg-teal-950 border border-teal-800 text-teal-400 px-4 py-3 font-mono2 text-xs mb-6">
    ✓ {{ session('success') }}
</div>
@endif

{{-- Status 5 Dokumen --}}
<div class="grid grid-cols-1 gap-px bg-gray-800 mb-8">
@foreach(App\Models\Dokumen::JENIS as $key => $label)
@php $dok = $uploaded[$key] ?? null; @endphp
<div class="bg-[#141414] p-6 flex items-center justify-between flex-wrap gap-4">
    <div class="flex items-center gap-4">
        <div class="w-2 h-10 rounded-sm flex-shrink-0"
             style="background: {{ $dok ? ($dok->status === 'terverifikasi' ? '#2EC4B6' : '#F4A261') : '#333' }}"></div>
        <div>
            <p class="font-semibold text-sm">{{ $label }}</p>
            @if($dok)
                <p class="font-mono2 text-[10px] text-gray-500 mt-0.5">
                    {{ $dok->file_name }} · {{ number_format($dok->file_size / 1024, 0) }} KB
                    · Diupload {{ $dok->updated_at->diffForHumans() }}
                </p>
                <span class="inline-block font-mono2 text-[9px] tracking-widest uppercase px-2 py-0.5 mt-1"
                      style="color: {{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }};
                             background: {{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }}18;
                             border: 1px solid {{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }}44">
                    {{ App\Models\Dokumen::STATUS_LABELS[$dok->status] }}
                </span>
            @else
                <p class="font-mono2 text-[10px] text-gray-700 mt-0.5">Belum diupload</p>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-2">
        @if($dok)
        <button onclick="openPreview('{{ route('dokumen.preview', $dok) }}')" 
                class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-gray-500 hover:text-gray-300 transition">
            PREVIEW
        </button>
        <a href="{{ route('dokumen.download', $dok) }}"
           class="border border-gray-700 text-gray-500 px-3 py-1.5 font-mono2 text-[10px] uppercase tracking-widest hover:border-gray-500 hover:text-gray-300 transition">
            DOWNLOAD
        </a>
        @endif

        {{-- Tombol upload hanya muncul kalau bukan admin view --}}
        @if(!isset($isAdminView) || !$isAdminView)
        <button onclick="openUpload('{{ $key }}', '{{ $label }}')"
                class="px-4 py-1.5 font-mono2 text-[10px] uppercase tracking-widest transition"
                style="background:#A8DADC18;border:1px solid #A8DADC44;color:#A8DADC"
                onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
            {{ $dok ? 'REPLACE' : 'UPLOAD' }}
        </button>
        @endif
    </div>
</div>
@endforeach
</div>

{{-- Upload Modal — hanya tampil kalau bukan admin view --}}
@if(!isset($isAdminView) || !$isAdminView)
<div id="upload-modal" class="hidden fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4">
    <div class="bg-[#141414] border border-gray-800 p-8 w-full max-w-md">
        <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-1">// Upload Dokumen</p>
        <h2 id="modal-title" class="font-display text-2xl tracking-wide mb-6" style="color:#A8DADC"></h2>

        <form method="POST" action="{{ route('dokumen.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="jenis" id="modal-jenis">

            <div class="mb-6">
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">File PDF</label>
                <input type="file" name="file" accept=".pdf" required
                       class="w-full bg-[#070707] border border-gray-800 text-gray-400 px-4 py-3 text-sm
                              file:bg-brand file:text-white file:border-0 file:px-4 file:py-1.5 file:mr-4
                              file:font-mono2 file:text-[10px] file:uppercase file:tracking-widest file:cursor-pointer">
                <p class="font-mono2 text-[10px] text-gray-700 mt-2">Format PDF · Maks. 10MB</p>
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="closeUpload()"
                        class="flex-1 border border-gray-800 text-gray-500 font-display text-lg tracking-[2px] py-3 hover:border-gray-600 transition">
                    BATAL
                </button>
                <button type="submit"
                        class="flex-1 font-display text-lg tracking-[2px] py-3 hover:opacity-90 transition"
                        style="background:#A8DADC;color:#0D0D0D">
                    UPLOAD →
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openUpload(jenis, label) {
    document.getElementById('modal-jenis').value  = jenis;
    document.getElementById('modal-title').textContent = label;
    document.getElementById('upload-modal').classList.remove('hidden');
}
function closeUpload() {
    document.getElementById('upload-modal').classList.add('hidden');
}
</script>
@endpush
@endif

@endsection