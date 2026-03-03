@extends('layouts.app')
@section('title', 'Kelola TPS')

@section('content')
<div class="mb-8">
    <a href="{{ route('dashboard.admin') }}"
       class="inline-flex items-center gap-2 font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase hover:text-brand transition mb-4">
        ← KEMBALI KE DASHBOARD
    </a>
    <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-2">// Admin — Wilayah</p>
    <h1 class="font-display text-4xl tracking-[2px] text-brand">KELOLA TPS</h1>
</div>

@if(session('success'))
<div class="bg-green-950 border border-green-800 text-green-400 px-4 py-3 font-mono2 text-xs mb-6">✓ {{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-[#141414] border border-gray-800 p-6">
        <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-5">// Tambah TPS</p>
        <form method="POST" action="{{ route('admin.tps.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Kecamatan</label>
                <select id="filter-kec" onchange="filterDesa(this.value)"
                        class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                    <option value="">— Pilih Kecamatan —</option>
                    @foreach($kecamatans as $kec)
                    <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Desa</label>
                <select name="desa_id" id="desa-select"
                        class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                    <option value="">— Pilih Desa —</option>
                </select>
                @error('desa_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="mb-4">
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Nama TPS</label>
                <input type="text" name="nama" value="{{ old('nama') }}" placeholder="cth: TPS 001"
                       class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                @error('nama') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button class="w-full bg-brand text-white font-display text-lg tracking-[2px] py-3 hover:opacity-90 transition">TAMBAH →</button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-[#141414] border border-gray-800">
        <div class="p-4 border-b border-gray-800">
            <form method="GET" class="flex gap-3 flex-wrap">
                <select name="kecamatan_id" onchange="this.form.submit()"
                        class="bg-[#070707] border border-gray-800 text-gray-400 px-3 py-2 text-xs font-mono2 focus:border-brand focus:ring-0 focus:outline-none">
                    <option value="">Semua Kecamatan</option>
                    @foreach($kecamatans as $kec)
                    <option value="{{ $kec->id }}" {{ request('kecamatan_id') == $kec->id ? 'selected' : '' }}>{{ $kec->nama }}</option>
                    @endforeach
                </select>
                @if($desas->count())
                <select name="desa_id" onchange="this.form.submit()"
                        class="bg-[#070707] border border-gray-800 text-gray-400 px-3 py-2 text-xs font-mono2 focus:border-brand focus:ring-0 focus:outline-none">
                    <option value="">Semua Desa</option>
                    @foreach($desas as $d)
                    <option value="{{ $d->id }}" {{ request('desa_id') == $d->id ? 'selected' : '' }}>{{ $d->nama }}</option>
                    @endforeach
                </select>
                @endif
                <span class="font-mono2 text-[10px] text-gray-600 self-center">{{ $tps->count() }} TPS</span>
            </form>
        </div>
        @forelse($tps as $t)
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-900 hover:bg-[#1a1a1a] group">
            <div>
                <p class="text-sm font-medium">{{ $t->nama }}</p>
                <p class="text-xs text-gray-600 font-mono2 mt-0.5">{{ $t->desa->nama }} · {{ $t->desa->kecamatan->nama }}</p>
            </div>
            <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition">
                <a href="{{ route('admin.tps.view', $t) }}"
                class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-[#A8DADC] hover:text-[#A8DADC] transition">
                    VIEW
                </a>
                <form method="POST" action="{{ route('admin.tps.destroy', $t) }}"
                    onsubmit="return confirm('Hapus TPS ini?')">
                    @csrf @method('DELETE')
                    <button class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-red-700 hover:text-red-500 transition">
                        HAPUS
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="px-6 py-10 text-center text-gray-700 font-mono2 text-xs">Belum ada TPS.</div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
const allDesas = @json($desas ?? []);
// Semua desa dikirim dari controller, filter client-side
const desaData = @json(\App\Models\Desa::all(['id','nama','kecamatan_id']));

function filterDesa(kecId) {
    const sel = document.getElementById('desa-select');
    sel.innerHTML = '<option value="">— Pilih Desa —</option>';
    desaData.filter(d => d.kecamatan_id == kecId).forEach(d => {
        sel.innerHTML += `<option value="${d.id}">${d.nama}</option>`;
    });
}
</script>
@endpush
@endsection