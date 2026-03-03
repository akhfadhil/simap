@extends('layouts.app')
@section('title', 'Kelola Desa')

@section('content')
<div class="mb-8">
    <a href="{{ route('dashboard.admin') }}"
       class="inline-flex items-center gap-2 font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase hover:text-brand transition mb-4">
        ← KEMBALI KE DASHBOARD
    </a>
    <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-2">// Admin — Wilayah</p>
    <h1 class="font-display text-4xl tracking-[2px] text-brand">KELOLA DESA</h1>
</div>

@if(session('success'))
<div class="bg-green-950 border border-green-800 text-green-400 px-4 py-3 font-mono2 text-xs mb-6">✓ {{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-[#141414] border border-gray-800 p-6">
        <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-5">// Tambah Desa</p>
        <form method="POST" action="{{ route('admin.desa.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Kecamatan</label>
                <select name="kecamatan_id" class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                    <option value="">— Pilih Kecamatan —</option>
                    @foreach($kecamatans as $kec)
                    <option value="{{ $kec->id }}" {{ old('kecamatan_id') == $kec->id ? 'selected' : '' }}>{{ $kec->nama }}</option>
                    @endforeach
                </select>
                @error('kecamatan_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="mb-4">
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Nama Desa</label>
                <input type="text" name="nama" value="{{ old('nama') }}" placeholder="cth: Desa Cimahi"
                       class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button class="w-full bg-brand text-white font-display text-lg tracking-[2px] py-3 hover:opacity-90 transition">TAMBAH →</button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-[#141414] border border-gray-800">
        {{-- Filter --}}
        <div class="p-4 border-b border-gray-800">
            <form method="GET" class="flex gap-3">
                <select name="kecamatan_id" onchange="this.form.submit()"
                        class="bg-[#070707] border border-gray-800 text-gray-400 px-3 py-2 text-xs font-mono2 focus:border-brand focus:ring-0 focus:outline-none">
                    <option value="">Semua Kecamatan</option>
                    @foreach($kecamatans as $kec)
                    <option value="{{ $kec->id }}" {{ request('kecamatan_id') == $kec->id ? 'selected' : '' }}>{{ $kec->nama }}</option>
                    @endforeach
                </select>
                <span class="font-mono2 text-[10px] text-gray-600 self-center">{{ $desas->count() }} DESA</span>
            </form>
        </div>
        @forelse($desas as $desa)
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-900 hover:bg-[#1a1a1a] group">
            <div>
                <p class="text-sm font-medium">{{ $desa->nama }}</p>
                <p class="text-xs text-gray-600 font-mono2 mt-0.5">{{ $desa->kecamatan->nama }} · {{ $desa->tps_count }} TPS</p>
            </div>
            <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition">
                <a href="{{ route('admin.desa.view', $desa) }}"
                class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-[#2EC4B6] hover:text-[#2EC4B6] transition">
                    VIEW
                </a>
                <button onclick="openEdit({{ $desa->id }}, '{{ $desa->nama }}', {{ $desa->kecamatan_id }})"
                        class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-brand hover:text-brand transition">
                    EDIT
                </button>
                <form method="POST" action="{{ route('admin.desa.destroy', $desa) }}"
                    onsubmit="return confirm('Hapus desa ini?')">
                    @csrf @method('DELETE')
                    <button class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-red-700 hover:text-red-500 transition">
                        HAPUS
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="px-6 py-10 text-center text-gray-700 font-mono2 text-xs">Belum ada desa.</div>
        @endforelse
    </div>
</div>

<div id="edit-modal" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center">
    <div class="bg-[#141414] border border-gray-800 p-8 w-full max-w-md">
        <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-5">// Edit Desa</p>
        <form id="edit-form" method="POST">
            @csrf @method('PUT')
            <div class="mb-4">
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Kecamatan</label>
                <select id="edit-kecamatan" name="kecamatan_id" class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                    @foreach($kecamatans as $kec)
                    <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Nama Desa</label>
                <input type="text" id="edit-nama" name="nama" class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')"
                        class="flex-1 border border-gray-800 text-gray-500 font-display text-lg tracking-[2px] py-3">BATAL</button>
                <button class="flex-1 bg-brand text-white font-display text-lg tracking-[2px] py-3 hover:opacity-90 transition">SIMPAN</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEdit(id, nama, kecId) {
    document.getElementById('edit-nama').value = nama;
    document.getElementById('edit-kecamatan').value = kecId;
    document.getElementById('edit-form').action = `/admin/desa/${id}`;
    document.getElementById('edit-modal').classList.remove('hidden');
}
</script>
@endpush
@endsection