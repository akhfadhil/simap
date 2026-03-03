@extends('layouts.app')
@section('title', 'Kelola Kecamatan')

@section('content')
<div class="mb-8">
    <a href="{{ route('dashboard.admin') }}"
       class="inline-flex items-center gap-2 font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase hover:text-brand transition mb-4">
        ← KEMBALI KE DASHBOARD
    </a>
    <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-2">// Admin — Wilayah</p>
    <h1 class="font-display text-4xl tracking-[2px] text-brand">KELOLA KECAMATAN</h1>
</div>

@if(session('success'))
<div class="bg-green-950 border border-green-800 text-green-400 px-4 py-3 font-mono2 text-xs mb-6">
    ✓ {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Form Tambah --}}
    <div class="bg-[#141414] border border-gray-800 p-6">
        <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-5">// Tambah Kecamatan</p>
        <form method="POST" action="{{ route('admin.kecamatan.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Nama Kecamatan</label>
                <input type="text" name="nama" value="{{ old('nama') }}" placeholder="cth: Kecamatan Andir"
                       class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button class="w-full bg-brand text-white font-display text-lg tracking-[2px] py-3 hover:opacity-90 transition">
                TAMBAH →
            </button>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="lg:col-span-2 bg-[#141414] border border-gray-800">
        <div class="p-6 border-b border-gray-800">
            <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase">// Daftar Kecamatan ({{ $kecamatans->count() }})</p>
        </div>
        @forelse($kecamatans as $kec)
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-900 hover:bg-[#1a1a1a] group">
            <div>
                <p class="text-sm font-medium">{{ $kec->nama }}</p>
                <p class="text-xs text-gray-600 font-mono2 mt-0.5">{{ $kec->desas_count }} desa</p>
            </div>
            <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition">
                <a href="{{ route('admin.kecamatan.view', $kec) }}"
                class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-[#F4A261] hover:text-[#F4A261] transition">
                    VIEW
                </a>
                <button onclick="openEdit('kec', {{ $kec->id }}, '{{ $kec->nama }}')"
                        class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-brand hover:text-brand transition">
                    EDIT
                </button>
                <form method="POST" action="{{ route('admin.kecamatan.destroy', $kec) }}"
                    onsubmit="return confirm('Hapus kecamatan ini? Semua desa & TPS di dalamnya akan terhapus.')">
                    @csrf @method('DELETE')
                    <button class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-red-700 hover:text-red-500 transition">
                        HAPUS
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="px-6 py-10 text-center text-gray-700 font-mono2 text-xs">Belum ada kecamatan.</div>
        @endforelse
    </div>
</div>

{{-- Edit Modal --}}
<div id="edit-modal" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center">
    <div class="bg-[#141414] border border-gray-800 p-8 w-full max-w-md">
        <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-5">// Edit Kecamatan</p>
        <form id="edit-form" method="POST">
            @csrf @method('PUT')
            <div class="mb-4">
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Nama Kecamatan</label>
                <input type="text" id="edit-nama" name="nama"
                       class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="closeEdit()"
                        class="flex-1 border border-gray-800 text-gray-500 font-display text-lg tracking-[2px] py-3 hover:border-gray-600 transition">
                    BATAL
                </button>
                <button class="flex-1 bg-brand text-white font-display text-lg tracking-[2px] py-3 hover:opacity-90 transition">
                    SIMPAN
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEdit(type, id, nama) {
    document.getElementById('edit-nama').value = nama;
    document.getElementById('edit-form').action = `/admin/kecamatan/${id}`;
    document.getElementById('edit-modal').classList.remove('hidden');
}
function closeEdit() {
    document.getElementById('edit-modal').classList.add('hidden');
}
</script>
@endpush
@endsection