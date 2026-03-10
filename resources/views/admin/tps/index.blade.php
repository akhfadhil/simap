@extends('layouts.app')
@section('title', 'Kelola TPS')

@section('content')
<div class="mb-8">
    <a href="{{ route('dashboard.admin') }}"
       class="inline-flex items-center gap-2 text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition font-medium mb-4">
        ← Kembali ke Dashboard
    </a>
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// Admin — Wilayah</p>
    <h1 class="font-display text-4xl tracking-[2px] text-red-600">KELOLA TPS</h1>
</div>

@if(session('success'))
<div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 px-4 py-3 text-xs mb-6 rounded-lg font-medium">
    ✓ {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Form Bulk Add ── --}}
    <div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 p-6 shadow-sm">
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-5 font-semibold">// Bulk Add TPS</p>
        <form method="POST" action="{{ route('admin.tps.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Kecamatan</label>
                <select id="bulk-kecamatan" onchange="loadDesa(this.value)"
                        class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                    <option value="">— Pilih Kecamatan —</option>
                    @foreach($kecamatans as $kec)
                    <option value="{{ $kec->id }}" {{ old('kecamatan_id_filter') == $kec->id ? 'selected' : '' }}>{{ $kec->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Desa</label>
                <select name="desa_id" id="bulk-desa"
                        class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                    <option value="">— Pilih Desa —</option>
                    @foreach($kecamatans as $kec)
                        @foreach($kec->desas->sortBy('nama') as $desa)
                        <option value="{{ $desa->id }}" data-kec="{{ $kec->id }}" class="desa-option" style="display:none"
                                {{ old('desa_id') == $desa->id ? 'selected' : '' }}>
                            {{ $desa->nama }}
                        </option>
                        @endforeach
                    @endforeach
                </select>
                @error('desa_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="mb-5">
                <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Jumlah TPS</label>
                <input type="number" name="jumlah" min="1" max="999" value="{{ old('jumlah') }}"
                       placeholder="cth: 10 → TPS 001–010"
                       class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                @error('jumlah') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                <p class="text-[11px] dark:text-gray-600 text-gray-400 mt-1.5">TPS yang sudah ada dilewati otomatis.</p>
            </div>
            <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-lg text-sm transition">
                Buat TPS →
            </button>
        </form>
    </div>

    {{-- ── Daftar TPS ── --}}
    <div class="lg:col-span-2 dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden">

        {{-- Filter --}}
        <div class="p-4 border-b dark:border-gray-700 border-gray-200">
            <form method="GET" id="filter-form" class="flex items-center gap-3 flex-wrap">
                <select name="kecamatan_id" onchange="updateDesaFilter(this.value)"
                        class="dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-3 py-2 text-xs rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                    <option value="">Semua Kecamatan</option>
                    @foreach($kecamatans as $kec)
                    <option value="{{ $kec->id }}" {{ request('kecamatan_id') == $kec->id ? 'selected' : '' }}>{{ $kec->nama }}</option>
                    @endforeach
                </select>
                <select name="desa_id" id="filter-desa" onchange="this.form.submit()"
                        class="dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-3 py-2 text-xs rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                    <option value="">Semua Desa</option>
                    @foreach($kecamatans as $kec)
                        @foreach($kec->desas->sortBy('nama') as $desa)
                        <option value="{{ $desa->id }}" data-kec="{{ $kec->id }}"
                                class="filter-desa-option"
                                {{ request('desa_id') == $desa->id ? 'selected' : '' }}>
                            {{ $desa->nama }}
                        </option>
                        @endforeach
                    @endforeach
                </select>
                @php
                    $filteredTps = collect();
                    foreach($kecamatans as $kec) {
                        if(request('kecamatan_id') && $kec->id != request('kecamatan_id')) continue;
                        foreach($kec->desas as $desa) {
                            if(request('desa_id') && $desa->id != request('desa_id')) continue;
                            $filteredTps = $filteredTps->merge($desa->tps);
                        }
                    }
                @endphp
                <span class="text-[10px] dark:text-gray-500 text-gray-400 font-semibold uppercase tracking-wider">
                    {{ $filteredTps->count() }} TPS
                </span>
            </form>
        </div>

        {{-- List --}}
        @forelse($filteredTps->sortBy('nama') as $tps)
        <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700 border-gray-100 last:border-0 dark:hover:bg-gray-750 hover:bg-gray-50 transition group">
            <div>
                <p class="text-sm font-medium dark:text-gray-100 text-gray-800 font-mono">{{ $tps->nama }}</p>
                <p class="text-xs dark:text-gray-500 text-gray-400 mt-0.5">
                    {{ $tps->desa->nama }} · {{ $tps->desa->kecamatan->nama }}
                </p>
            </div>
            <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition">
                <a href="{{ route('admin.tps.view', $tps) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium border border-teal-400 text-teal-400 hover:bg-teal-400 hover:text-white transition">
                    View
                </a>
                <button onclick="openEdit({{ $tps->id }}, '{{ addslashes($tps->nama) }}')"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium border dark:border-gray-600 border-gray-300 dark:text-gray-400 text-gray-500 dark:hover:bg-gray-700 hover:bg-gray-100 transition">
                    Edit
                </button>
                <form method="POST" action="{{ route('admin.tps.destroy', $tps) }}"
                      onsubmit="return confirm('Hapus {{ addslashes($tps->nama) }}?')">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1.5 rounded-lg text-xs font-medium border border-red-400 text-red-400 hover:bg-red-500 hover:text-white transition">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="px-6 py-10 text-center dark:text-gray-600 text-gray-400 text-sm">
            Belum ada TPS. Gunakan form di kiri untuk menambah.
        </div>
        @endforelse
    </div>
</div>

{{-- ── Modal Edit ── --}}
<div id="edit-modal" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
    <div class="dark:bg-gray-800 bg-white rounded-2xl border dark:border-gray-700 border-gray-200 p-8 w-full max-w-md shadow-2xl">
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-5 font-semibold">// Edit Nama TPS</p>
        <form id="edit-form" method="POST">
            @csrf @method('PUT')
            <div class="mb-5">
                <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama TPS</label>
                <input type="text" id="edit-nama" name="nama" required maxlength="100"
                       class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeEdit()"
                        class="flex-1 border dark:border-gray-600 border-gray-300 dark:text-gray-400 text-gray-500 py-2.5 rounded-lg text-sm font-medium dark:hover:bg-gray-700 hover:bg-gray-100 transition">
                    Batal
                </button>
                <button class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function loadDesa(kecId) {
    const sel = document.getElementById('bulk-desa');
    sel.value = '';
    document.querySelectorAll('.desa-option').forEach(opt => {
        opt.style.display = (!kecId || opt.dataset.kec === kecId) ? '' : 'none';
    });
}

function updateDesaFilter(kecId) {
    const sel = document.getElementById('filter-desa');
    sel.value = '';
    document.querySelectorAll('.filter-desa-option').forEach(opt => {
        opt.style.display = (!kecId || opt.dataset.kec === kecId) ? '' : 'none';
    });
    document.getElementById('filter-form').submit();
}

function openEdit(id, nama) {
    document.getElementById('edit-nama').value = nama;
    document.getElementById('edit-form').action = `/admin/tps/${id}`;
    document.getElementById('edit-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('edit-nama').select(), 50);
}

function closeEdit() {
    document.getElementById('edit-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', () => {
    const kecId = '{{ request('kecamatan_id') }}';
    if (kecId) {
        document.querySelectorAll('.filter-desa-option').forEach(opt => {
            opt.style.display = opt.dataset.kec === kecId ? '' : 'none';
        });
    }
});
</script>
@endpush
@endsection