@extends('layouts.app')
@section('title', 'Isi Rekap ' . \App\Models\RekapHeader::JENIS_LABELS[$jenis])

@section('content')
<div class="mb-6">
    <a href="{{ route('rekap.index') }}"
       class="inline-flex items-center gap-2 text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition font-medium mb-4">
        ← Kembali
    </a>
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">
        // KPPS — {{ $tps->nama }} · {{ $tps->desa->nama }}
    </p>
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h1 class="font-display text-4xl tracking-[2px] text-sky-300">
            {{ strtoupper(\App\Models\RekapHeader::JENIS_LABELS[$jenis]) }}
        </h1>
        @if($rekap && $rekap->status === 'final')
        <span class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-teal-500/20 text-teal-400 border border-teal-500/40">
            ✓ Sudah Difinalisasi
        </span>
        @endif
    </div>
</div>

@if(session('error'))
<div class="bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 text-xs mb-6 rounded-lg font-medium">
    ⚠ {{ session('error') }}
</div>
@endif

@php $isFinal = $rekap && $rekap->status === 'final'; @endphp

<form method="POST" action="{{ route('rekap.store', $jenis) }}" id="rekap-form">
@csrf

{{-- ══ SECTION I: DATA PEMILIH ══ --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm mb-4 overflow-hidden">
    <div class="px-6 py-4 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/50 bg-gray-50">
        <p class="text-xs font-bold dark:text-gray-300 text-gray-700 uppercase tracking-wider">I. Data Pemilih & Pengguna Hak Pilih</p>
    </div>
    <div class="p-6">
        @php
        $fields = [
            ['label'=>'DPT','lk'=>'dpt_lk','pr'=>'dpt_pr'],
            ['label'=>'Pengguna Hak Pilih DPT','lk'=>'pengguna_dpt_lk','pr'=>'pengguna_dpt_pr'],
            ['label'=>'Pengguna Hak Pilih DPTb','lk'=>'pengguna_dptb_lk','pr'=>'pengguna_dptb_pr'],
            ['label'=>'Pengguna Hak Pilih DPK','lk'=>'pengguna_dpk_lk','pr'=>'pengguna_dpk_pr'],
        ];
        @endphp
        <div class="grid grid-cols-1 gap-3">
        @foreach($fields as $f)
        <div class="grid grid-cols-12 items-center gap-3">
            <div class="col-span-5">
                <p class="text-sm dark:text-gray-300 text-gray-700">{{ $f['label'] }}</p>
            </div>
            <div class="col-span-3">
                <label class="block text-[10px] dark:text-gray-500 text-gray-400 mb-1">LK</label>
                <input type="number" name="{{ $f['lk'] }}" min="0" value="{{ old($f['lk'], $rekap?->{$f['lk']} ?? 0) }}"
                       {{ $isFinal ? 'disabled' : '' }}
                       class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2 text-sm rounded-lg focus:border-sky-400 focus:ring-0 focus:outline-none {{ $isFinal ? 'opacity-60 cursor-not-allowed' : '' }}">
            </div>
            <div class="col-span-3">
                <label class="block text-[10px] dark:text-gray-500 text-gray-400 mb-1">PR</label>
                <input type="number" name="{{ $f['pr'] }}" min="0" value="{{ old($f['pr'], $rekap?->{$f['pr']} ?? 0) }}"
                       {{ $isFinal ? 'disabled' : '' }}
                       class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2 text-sm rounded-lg focus:border-sky-400 focus:ring-0 focus:outline-none {{ $isFinal ? 'opacity-60 cursor-not-allowed' : '' }}">
            </div>
            <div class="col-span-1">
                <label class="block text-[10px] dark:text-gray-500 text-gray-400 mb-1">JML</label>
                <div class="w-full dark:bg-gray-900 bg-gray-100 border dark:border-gray-700 border-gray-300 px-3 py-2 text-sm font-semibold dark:text-gray-300 text-gray-700 rounded-lg text-right jml-display"
                    data-lk="{{ $f['lk'] }}" data-pr="{{ $f['pr'] }}">
                    {{ ($rekap?->{$f['lk']} ?? 0) + ($rekap?->{$f['pr']} ?? 0) }}
                </div>
            </div>
        </div>
        @endforeach
        </div>
    </div>
</div>

{{-- ══ SECTION II: SURAT SUARA ══ --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm mb-4 overflow-hidden">
    <div class="px-6 py-4 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/50 bg-gray-50">
        <p class="text-xs font-bold dark:text-gray-300 text-gray-700 uppercase tracking-wider">II. Data Penggunaan Surat Suara</p>
    </div>
    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- 3 field biasa --}}
        @foreach([
            ['ss_diterima', 'Surat suara diterima (termasuk cadangan 2%)'],
            ['ss_digunakan', 'Surat suara digunakan'],
            ['ss_rusak', 'Surat suara rusak / keliru coblos'],
        ] as [$name, $label])
        <div>
            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 mb-2">{{ $label }}</label>
            <input type="number" name="{{ $name }}" min="0" value="{{ old($name, $rekap?->{$name} ?? 0) }}"
                   {{ $isFinal ? 'disabled' : '' }}
                   class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2 text-sm rounded-lg focus:border-sky-400 focus:ring-0 focus:outline-none {{ $isFinal ? 'opacity-60 cursor-not-allowed' : '' }}">
        </div>
        @endforeach

        {{-- ss_sisa: otomatis --}}
        <div>
            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 mb-2">
                Surat suara tidak digunakan / sisa
                <span class="text-[10px] dark:text-gray-600 text-gray-400 normal-case tracking-normal font-normal">(otomatis)</span>
            </label>
            <input type="number" name="ss_sisa" id="ss_sisa" min="0"
                   value="{{ old('ss_sisa', $rekap?->ss_sisa ?? 0) }}"
                   readonly
                   class="w-full dark:bg-gray-900 bg-gray-100 border dark:border-gray-700 border-gray-300 dark:text-gray-400 text-gray-500 px-3 py-2 text-sm rounded-lg cursor-not-allowed">
        </div>

    </div>
</div>

{{-- ══ SECTION III: DISABILITAS ══ --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm mb-4 overflow-hidden">
    <div class="px-6 py-4 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/50 bg-gray-50">
        <p class="text-xs font-bold dark:text-gray-300 text-gray-700 uppercase tracking-wider">III. Data Pemilih Disabilitas</p>
    </div>
    <div class="p-6 grid grid-cols-12 items-center gap-3">
        <div class="col-span-5">
            <p class="text-sm dark:text-gray-300 text-gray-700">Pemilih disabilitas yang menggunakan hak pilih</p>
        </div>
        <div class="col-span-3">
            <label class="block text-[10px] dark:text-gray-500 text-gray-400 mb-1">LK</label>
            <input type="number" name="disabilitas_lk" min="0" value="{{ old('disabilitas_lk', $rekap?->disabilitas_lk ?? 0) }}"
                {{ $isFinal ? 'disabled' : '' }}
                class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2 text-sm rounded-lg focus:border-sky-400 focus:ring-0 focus:outline-none {{ $isFinal ? 'opacity-60 cursor-not-allowed' : '' }}">
        </div>
        <div class="col-span-3">
            <label class="block text-[10px] dark:text-gray-500 text-gray-400 mb-1">PR</label>
            <input type="number" name="disabilitas_pr" min="0" value="{{ old('disabilitas_pr', $rekap?->disabilitas_pr ?? 0) }}"
                {{ $isFinal ? 'disabled' : '' }}
                class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2 text-sm rounded-lg focus:border-sky-400 focus:ring-0 focus:outline-none {{ $isFinal ? 'opacity-60 cursor-not-allowed' : '' }}">
        </div>
        <div class="col-span-1">
            <label class="block text-[10px] dark:text-gray-500 text-gray-400 mb-1">JML</label>
            <div class="w-full dark:bg-gray-900 bg-gray-100 border dark:border-gray-700 border-gray-300 px-3 py-2 text-sm font-semibold dark:text-gray-300 text-gray-700 rounded-lg text-right jml-display"
                data-lk="disabilitas_lk" data-pr="disabilitas_pr">
                {{ ($rekap?->disabilitas_lk ?? 0) + ($rekap?->disabilitas_pr ?? 0) }}
            </div>
        </div>
    </div>
</div>

{{-- ══ SECTION IV: PEROLEHAN SUARA ══ --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm mb-4 overflow-hidden">
    <div class="px-6 py-4 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/50 bg-gray-50">
        <p class="text-xs font-bold dark:text-gray-300 text-gray-700 uppercase tracking-wider">IV. Perolehan Suara</p>
    </div>
    <div class="p-6">

    {{-- PPWP & DPD: list calon --}}
    @if(in_array($jenis, ['ppwp','dpd']))
        @if($data['calons']->isEmpty())
        <div class="text-center py-8 dark:text-gray-500 text-gray-400 text-sm">
            ⚠ Belum ada data {{ $jenis === 'ppwp' ? 'paslon' : 'calon DPD' }}. Minta admin untuk menginput terlebih dahulu.
        </div>
        @else
        <div class="space-y-3">
        @foreach($data['calons'] as $calon)
        <div class="flex items-center gap-4">
            <span class="w-8 h-8 rounded-full {{ $jenis === 'ppwp' ? 'bg-red-600' : 'bg-teal-500' }} text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                {{ $calon->nomor_urut }}
            </span>
            <p class="flex-1 text-sm dark:text-gray-200 text-gray-700">
                {{ $jenis === 'ppwp' ? $calon->nama_paslon : $calon->nama_calon }}
            </p>
            <input type="number" name="suara[{{ $calon->id }}]" min="0"
                   value="{{ old('suara.'.$calon->id, $data['suara'][$calon->id] ?? 0) }}"
                   {{ $isFinal ? 'disabled' : '' }}
                   class="w-28 dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2 text-sm rounded-lg focus:border-sky-400 focus:ring-0 focus:outline-none text-right {{ $isFinal ? 'opacity-60 cursor-not-allowed' : '' }}">
        </div>
        @endforeach
        </div>
        @endif

    {{-- DPR RI / DPRD: per partai + caleg --}}
    @else
        @if($data['partais']->isEmpty())
        <div class="text-center py-8 dark:text-gray-500 text-gray-400 text-sm">
            ⚠ Belum ada data partai. Minta admin untuk menginput terlebih dahulu.
        </div>
        @else
        <div class="space-y-4">
        @foreach($data['partais'] as $partai)
        <div class="border dark:border-gray-700 border-gray-200 rounded-xl overflow-hidden">
            {{-- Header partai --}}
            <div class="flex items-center justify-between px-5 py-3 dark:bg-gray-700/50 bg-gray-50 border-b dark:border-gray-700 border-gray-200">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-orange-400 text-white text-xs font-bold flex items-center justify-center">
                        {{ $partai->nomor_urut }}
                    </span>
                    <p class="text-sm font-semibold dark:text-gray-100 text-gray-800">{{ $partai->nama_partai }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <label class="text-xs dark:text-gray-400 text-gray-500 font-medium">Suara Partai</label>
                    <input type="number" name="suara_partai[{{ $partai->id }}]" min="0"
                           value="{{ old('suara_partai.'.$partai->id, $data['suara_partai'][$partai->id] ?? 0) }}"
                           {{ $isFinal ? 'disabled' : '' }}
                           class="w-28 dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-1.5 text-sm rounded-lg focus:border-sky-400 focus:ring-0 focus:outline-none text-right {{ $isFinal ? 'opacity-60 cursor-not-allowed' : '' }}">
                </div>
            </div>
            {{-- Caleg --}}
            @foreach($partai->calegs as $caleg)
            <div class="flex items-center justify-between px-5 py-3 border-b dark:border-gray-700 border-gray-100 last:border-0">
                <div class="flex items-center gap-3">
                    <span class="text-xs dark:text-gray-500 text-gray-400 w-5 text-center">{{ $caleg->nomor_urut }}</span>
                    <p class="text-sm dark:text-gray-300 text-gray-600">{{ $caleg->nama_caleg }}</p>
                </div>
                <input type="number" name="suara_caleg[{{ $caleg->id }}]" min="0"
                       value="{{ old('suara_caleg.'.$caleg->id, $data['suara_caleg'][$caleg->id] ?? 0) }}"
                       {{ $isFinal ? 'disabled' : '' }}
                       class="w-28 dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-1.5 text-sm rounded-lg focus:border-sky-400 focus:ring-0 focus:outline-none text-right {{ $isFinal ? 'opacity-60 cursor-not-allowed' : '' }}">
            </div>
            @endforeach

            {{-- Total per partai --}}
            <div class="flex items-center justify-between px-5 py-3 border-t dark:border-gray-600 border-gray-300 dark:bg-gray-700/50 bg-gray-50">
                <p class="text-xs font-bold dark:text-gray-300 text-gray-700 uppercase tracking-wider">
                    Jumlah suara sah {{ $partai->nama_partai }}
                    <span class="text-[10px] font-normal dark:text-gray-500 text-gray-400 normal-case tracking-normal">(partai + seluruh caleg)</span>
                </p>
                <div class="w-28 dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 px-3 py-1.5 text-sm font-bold text-orange-400 rounded-lg text-right partai-subtotal"
                     data-partai-id="{{ $partai->id }}">
                    0
                </div>
            </div>

        </div>
        @endforeach
        </div>
        @endif
    @endif
    </div>
</div>

{{-- ══ SECTION V: SUARA SAH & TIDAK SAH ══ --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm mb-6 overflow-hidden">
    <div class="px-6 py-4 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-700/50 bg-gray-50">
        <p class="text-xs font-bold dark:text-gray-300 text-gray-700 uppercase tracking-wider">V. Suara Sah & Tidak Sah</p>
    </div>
    <div class="p-6 space-y-4">

        {{-- Jumlah Suara Sah (otomatis dari section IV) --}}
        <div class="flex items-center gap-4">
            <p class="flex-1 text-sm dark:text-gray-300 text-gray-700">
                Jumlah seluruh suara sah
                <span class="text-[10px] dark:text-gray-600 text-gray-400 font-normal">(otomatis dari perolehan suara)</span>
            </p>
            <div id="display-suara-sah"
                 class="w-36 dark:bg-gray-900 bg-gray-100 border dark:border-gray-700 border-gray-300 px-3 py-2 text-sm font-semibold dark:text-gray-300 text-gray-700 rounded-lg text-right">
                0
            </div>
        </div>

        {{-- Jumlah Suara Tidak Sah --}}
        <div class="flex items-center gap-4">
            <p class="flex-1 text-sm dark:text-gray-300 text-gray-700">Jumlah suara tidak sah</p>
            <input type="number" name="suara_tidak_sah" id="input-tidak-sah" min="0"
                   value="{{ old('suara_tidak_sah', $rekap?->suara_tidak_sah ?? 0) }}"
                   {{ $isFinal ? 'disabled' : '' }}
                   class="w-36 dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2 text-sm rounded-lg focus:border-sky-400 focus:ring-0 focus:outline-none text-right {{ $isFinal ? 'opacity-60 cursor-not-allowed' : '' }}">
        </div>

        {{-- Jumlah Suara Sah + Tidak Sah (otomatis) --}}
        <div class="flex items-center gap-4 pt-3 border-t dark:border-gray-700 border-gray-200">
            <p class="flex-1 text-sm font-semibold dark:text-gray-200 text-gray-800">
                Jumlah seluruh suara sah dan tidak sah
                <span class="text-[10px] dark:text-gray-600 text-gray-400 font-normal">(otomatis)</span>
            </p>
            <div id="display-suara-total"
                 class="w-36 dark:bg-gray-900 bg-gray-100 border dark:border-gray-700 border-gray-300 px-3 py-2 text-sm font-bold dark:text-sky-400 text-sky-600 rounded-lg text-right">
                0
            </div>
        </div>

    </div>
</div>

{{-- Tombol Aksi --}}
@if(!$isFinal)
<div class="flex gap-3">
    <button type="submit"
            class="flex-1 bg-sky-500 hover:bg-sky-600 text-white font-semibold py-3 rounded-xl text-sm transition">
        💾 Simpan Draft
    </button>
    <button type="button" onclick="confirmFinalisasi()"
            class="flex-1 bg-teal-500 hover:bg-teal-600 text-white font-semibold py-3 rounded-xl text-sm transition">
        ✓ Simpan & Finalisasi
    </button>
</div>
@else
<div class="dark:bg-gray-800 bg-gray-50 rounded-xl border dark:border-gray-700 border-gray-200 p-4 text-center">
    <p class="text-sm dark:text-gray-400 text-gray-500">Data sudah difinalisasi dan tidak bisa diubah.</p>
</div>
@endif

</form>

{{-- Form finalisasi terpisah --}}
@if($rekap && !$isFinal)
<form id="form-finalisasi" method="POST" action="{{ route('rekap.finalisasi', $jenis) }}" class="hidden">
    @csrf
</form>
@endif

@push('scripts')
<script>
// ── JML LK+PR ────────────────────────────────────────────
function updateJml() {
    document.querySelectorAll('.jml-display').forEach(el => {
        const lk = parseInt(document.querySelector(`[name="${el.dataset.lk}"]`)?.value) || 0;
        const pr = parseInt(document.querySelector(`[name="${el.dataset.pr}"]`)?.value) || 0;
        el.textContent = (lk + pr).toLocaleString('id-ID');
    });
}

// ── SISA SURAT SUARA ─────────────────────────────────────
function updateSisa() {
    const diterima  = parseInt(document.querySelector('[name="ss_diterima"]')?.value) || 0;
    const digunakan = parseInt(document.querySelector('[name="ss_digunakan"]')?.value) || 0;
    const rusak     = parseInt(document.querySelector('[name="ss_rusak"]')?.value) || 0;
    const sisa      = Math.max(0, diterima - digunakan - rusak);
    const el        = document.getElementById('ss_sisa');
    if (el) el.value = sisa;
}

// ── SUARA SAH (dari section IV) ──────────────────────────
function updateSuaraSah() {
    let sah = 0;

    @if(in_array($jenis, ['ppwp', 'dpd']))
        document.querySelectorAll('[name^="suara["]').forEach(inp => {
            sah += parseInt(inp.value) || 0;
        });

    @else
        // Hitung subtotal per partai
        document.querySelectorAll('.partai-subtotal').forEach(elSubtotal => {
            const partaiId = elSubtotal.dataset.partaiId;
            let subtotal   = 0;

            // Suara partai
            const inpPartai = document.querySelector(`[name="suara_partai[${partaiId}]"]`);
            subtotal += parseInt(inpPartai?.value) || 0;

            // Suara semua caleg partai ini
            // Cari semua caleg yang ada di dalam container partai ini
            const containerPartai = elSubtotal.closest('.border.dark\\:border-gray-700');
            if (containerPartai) {
                containerPartai.querySelectorAll('[name^="suara_caleg["]').forEach(inp => {
                    subtotal += parseInt(inp.value) || 0;
                });
            }

            elSubtotal.textContent = subtotal.toLocaleString('id-ID');
            sah += subtotal;
        });
    @endif

    const elSah = document.getElementById('display-suara-sah');
    if (elSah) elSah.textContent = sah.toLocaleString('id-ID');

    updateTotal(sah);
}

// ── TOTAL SAH + TIDAK SAH ────────────────────────────────
function updateTotal(sah) {
    const tidakSah = parseInt(document.getElementById('input-tidak-sah')?.value) || 0;
    const total    = sah + tidakSah;
    const elTotal  = document.getElementById('display-suara-total');
    if (elTotal) elTotal.textContent = total.toLocaleString('id-ID');
}

// ── TRIGGER SEMUA ────────────────────────────────────────
function updateAll() {
    updateJml();
    updateSisa();
    updateSuaraSah();
}

document.querySelectorAll('input[type="number"]').forEach(inp => {
    inp.addEventListener('input', updateAll);
});

// Init
updateAll();

// ── FINALISASI ───────────────────────────────────────────
function confirmFinalisasi() {
    if (confirm('Finalisasi rekap ini? Data tidak bisa diubah setelah difinalisasi.')) {
        // set hidden input lalu submit form rekap dengan flag finalisasi
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'finalisasi';
        input.value = '1';
        document.getElementById('rekap-form').appendChild(input);
        document.getElementById('rekap-form').submit();
    }
}
</script>
@endpush
@endsection