@extends('layouts.app')
@section('title', 'Upload Dokumen PPK')

@section('content')

<div class="mb-4">
    <a href="{{ route('dashboard.ppk') }}"
       class="inline-flex items-center gap-2 text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition font-medium">
        ← Kembali ke Dashboard
    </a>
</div>

<div class="mb-8">
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// PPK — Upload Dokumen</p>
    <h1 class="font-display text-4xl tracking-[2px] text-orange-400">UPLOAD DOKUMEN</h1>
    <p class="dark:text-gray-400 text-gray-500 text-sm mt-1">{{ $kecamatan->nama }}</p>
</div>

@if(session('success'))
<div class="bg-teal-50 dark:bg-teal-950 border border-teal-200 dark:border-teal-800 text-teal-600 dark:text-teal-400 px-4 py-3 text-xs mb-6 rounded-lg font-medium">
    ✓ {{ session('success') }}
</div>
@endif

<div class="space-y-3 mb-8">
@foreach(App\Models\Dokumen::JENIS as $key => $label)
@php $dok = $uploaded[$key] ?? null; @endphp
<div class="dark:bg-gray-800 bg-white rounded-xl p-5 border dark:border-gray-700 border-gray-200 flex items-center justify-between flex-wrap gap-4 shadow-sm">
    <div class="flex items-center gap-4">
        <div class="w-1 h-12 rounded-full flex-shrink-0"
             style="background: {{ $dok ? ($dok->status === 'terverifikasi' ? '#2EC4B6' : '#F4A261') : '#9CA3AF' }}"></div>
        <div>
            <p class="font-semibold text-sm dark:text-gray-100 text-gray-800">{{ $label }}</p>
            @if($dok)
                <p class="text-[11px] dark:text-gray-500 text-gray-400 mt-0.5">
                    {{ $dok->file_name }} · {{ number_format($dok->file_size / 1024, 0) }} KB
                    · {{ $dok->updated_at->diffForHumans() }}
                </p>
                <span class="inline-block text-[9px] tracking-widest uppercase px-2 py-0.5 mt-1 rounded font-semibold"
                      style="color: {{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }};
                             background: {{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }}20;
                             border: 1px solid {{ App\Models\Dokumen::STATUS_COLORS[$dok->status] }}40">
                    {{ App\Models\Dokumen::STATUS_LABELS[$dok->status] }}
                </span>
            @else
                <p class="text-[11px] dark:text-gray-600 text-gray-400 mt-0.5">Belum diupload</p>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-2">
        @if($dok)
        <button onclick="openPreview('{{ route('dokumen.preview', $dok) }}')"
                class="px-3 py-1.5 rounded-lg text-xs font-medium border dark:border-gray-600 border-gray-300 dark:text-gray-400 text-gray-500 dark:hover:bg-gray-700 hover:bg-gray-100 transition">
            Preview
        </button>
        <a href="{{ route('dokumen.download', $dok) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-medium border dark:border-gray-600 border-gray-300 dark:text-gray-400 text-gray-500 dark:hover:bg-gray-700 hover:bg-gray-100 transition">
            Download
        </a>
        @endif
        <button onclick="openUpload('{{ $key }}', '{{ $label }}')"
                class="px-4 py-1.5 rounded-lg text-xs font-semibold text-white bg-orange-400 hover:bg-orange-500 transition">
            {{ $dok ? 'Ganti' : 'Upload' }}
        </button>
    </div>
</div>
@endforeach
</div>

{{-- Upload Modal --}}
<div id="upload-modal" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
    <div class="dark:bg-gray-800 bg-white rounded-2xl border dark:border-gray-700 border-gray-200 p-8 w-full max-w-md shadow-2xl">
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-1 font-semibold">// Upload Dokumen</p>
        <h2 id="modal-title" class="font-display text-2xl tracking-wide mb-6 text-orange-400"></h2>

        <form method="POST" action="{{ route('ppk.upload.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="jenis" id="modal-jenis">

            <div class="mb-6">
                <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">File PDF</label>
                <input type="file" name="file" accept=".pdf" required
                       class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-4 py-3 text-sm rounded-lg
                              file:bg-orange-400 file:text-white file:border-0 file:px-4 file:py-1.5 file:mr-4
                              file:text-xs file:font-semibold file:rounded file:cursor-pointer">
                <p class="text-[11px] dark:text-gray-600 text-gray-400 mt-2">Format PDF · Maks. 10MB</p>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeUpload()"
                        class="flex-1 border dark:border-gray-600 border-gray-300 dark:text-gray-400 text-gray-500 py-2.5 rounded-lg text-sm font-medium dark:hover:bg-gray-700 hover:bg-gray-100 transition">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 bg-orange-400 hover:bg-orange-500 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                    Upload →
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openUpload(jenis, label) {
    document.getElementById('modal-jenis').value       = jenis;
    document.getElementById('modal-title').textContent = label;
    document.getElementById('upload-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeUpload() {
    document.getElementById('upload-modal').classList.add('hidden');
    document.body.style.overflow = '';
}
</script>
@endpush

@endsection