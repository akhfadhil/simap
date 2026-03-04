@extends('layouts.app')
@section('title', 'Setup Data Pemilu')

@section('content')
<div class="mb-8">
    <a href="{{ route('dashboard.admin') }}"
       class="inline-flex items-center gap-2 text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition font-medium mb-4">
        ← Kembali ke Dashboard
    </a>
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// Admin — Setup</p>
    <h1 class="font-display text-4xl tracking-[2px] text-red-600">SETUP DATA PEMILU</h1>
    <p class="dark:text-gray-400 text-gray-500 text-sm mt-1">Input master data paslon, calon, partai, dan caleg.</p>
</div>

@if(session('success'))
<div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 px-4 py-3 text-xs mb-6 rounded-lg font-medium">
    ✓ {{ session('success') }}
</div>
@endif

{{-- TAB NAVIGATION --}}
<div class="flex gap-1 mb-6 dark:bg-gray-900 bg-gray-100 p-1 rounded-xl w-fit">
    @foreach(['ppwp'=>'PPWP','dpd'=>'DPD','dpr_ri'=>'DPR RI','dprd_prov'=>'DPRD Prov','dprd_kab'=>'DPRD Kab'] as $tab => $label)
    <button onclick="switchTab('{{ $tab }}')" id="tab-{{ $tab }}"
            class="px-4 py-2 text-xs font-semibold rounded-lg transition tab-btn"
            data-tab="{{ $tab }}">
        {{ $label }}
    </button>
    @endforeach
</div>

{{-- ══ TAB PPWP ══ --}}
<div id="panel-ppwp" class="tab-panel">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 p-6 shadow-sm">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-5 font-semibold">// Tambah Paslon</p>
            <form method="POST" action="{{ route('admin.setup.ppwp.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">No. Urut</label>
                    <input type="number" name="nomor_urut" min="1" max="99" placeholder="1"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Paslon</label>
                    <input type="text" name="nama_paslon" placeholder="NAMA CALON - NAMA WAKIL"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-lg text-sm transition">
                    Tambah →
                </button>
            </form>
        </div>
        <div class="lg:col-span-2 dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b dark:border-gray-700 border-gray-200">
                <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Daftar Paslon PPWP ({{ $ppwpCalons->count() }})</p>
            </div>
            @forelse($ppwpCalons as $c)
            <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700 border-gray-100 last:border-0 group">
                <div class="flex items-center gap-4">
                    <span class="w-8 h-8 rounded-full bg-red-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                        {{ $c->nomor_urut }}
                    </span>
                    <p class="text-sm font-medium dark:text-gray-100 text-gray-800">{{ $c->nama_paslon }}</p>
                </div>
                <form method="POST" action="{{ route('admin.setup.ppwp.destroy', $c) }}"
                      onsubmit="return confirm('Hapus paslon ini?')" class="opacity-0 group-hover:opacity-100 transition">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1.5 rounded-lg text-xs font-medium border border-red-400 text-red-400 hover:bg-red-500 hover:text-white transition">Hapus</button>
                </form>
            </div>
            @empty
            <div class="px-6 py-10 text-center dark:text-gray-600 text-gray-400 text-sm">Belum ada paslon.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ══ TAB DPD ══ --}}
<div id="panel-dpd" class="tab-panel hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 p-6 shadow-sm">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-5 font-semibold">// Tambah Calon DPD</p>
            <form method="POST" action="{{ route('admin.setup.dpd.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">No. Urut</label>
                    <input type="number" name="nomor_urut" min="1" placeholder="1"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Calon</label>
                    <input type="text" name="nama_calon" placeholder="Nama lengkap calon DPD"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-lg text-sm transition">
                    Tambah →
                </button>
            </form>
        </div>
        <div class="lg:col-span-2 dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b dark:border-gray-700 border-gray-200">
                <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Daftar Calon DPD ({{ $dpdCalons->count() }})</p>
            </div>
            @forelse($dpdCalons as $c)
            <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700 border-gray-100 last:border-0 group">
                <div class="flex items-center gap-4">
                    <span class="w-8 h-8 rounded-full bg-teal-500 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                        {{ $c->nomor_urut }}
                    </span>
                    <p class="text-sm font-medium dark:text-gray-100 text-gray-800">{{ $c->nama_calon }}</p>
                </div>
                <form method="POST" action="{{ route('admin.setup.dpd.destroy', $c) }}"
                      onsubmit="return confirm('Hapus calon ini?')" class="opacity-0 group-hover:opacity-100 transition">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1.5 rounded-lg text-xs font-medium border border-red-400 text-red-400 hover:bg-red-500 hover:text-white transition">Hapus</button>
                </form>
            </div>
            @empty
            <div class="px-6 py-10 text-center dark:text-gray-600 text-gray-400 text-sm">Belum ada calon DPD.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ══ TAB DPR RI & DPRD PROV ══ --}}
@foreach(['dpr_ri'=>['partaiDprRi','DPR RI','orange'],'dprd_prov'=>['partaiProv','DPRD Provinsi','sky']] as $jenis => [$var, $label, $color])
<div id="panel-{{ $jenis }}" class="tab-panel hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 p-6 shadow-sm">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-5 font-semibold">// Tambah Partai</p>
            <form method="POST" action="{{ route('admin.setup.partai.store') }}">
                @csrf
                <input type="hidden" name="jenis" value="{{ $jenis }}">
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">No. Urut</label>
                    <input type="number" name="nomor_urut" min="1" placeholder="1"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Partai</label>
                    <input type="text" name="nama_partai" placeholder="cth: Partai Kebangkitan Bangsa"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-lg text-sm transition">
                    Tambah Partai →
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b dark:border-gray-700 border-gray-200">
                <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Daftar Partai {{ $label }} ({{ $$var->count() }})</p>
            </div>
            @forelse($$var as $partai)
            <div class="border-b dark:border-gray-700 border-gray-100 last:border-0">
                <div class="flex items-center justify-between px-6 py-3 dark:bg-gray-750 bg-gray-50 cursor-pointer group"
                     onclick="togglePartai({{ $partai->id }})">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-{{ $color }}-500 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                            {{ $partai->nomor_urut }}
                        </span>
                        <p class="text-sm font-semibold dark:text-gray-100 text-gray-800">{{ $partai->nama_partai }}</p>
                        <span class="text-[10px] dark:text-gray-500 text-gray-400">{{ $partai->calegs->count() }} caleg</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="arrow-partai-{{ $partai->id }}" class="dark:text-gray-500 text-gray-400 text-xs">▾</span>
                        <form method="POST" action="{{ route('admin.setup.partai.destroy', $partai) }}"
                              onsubmit="return confirm('Hapus partai dan semua calegnya?')" class="opacity-0 group-hover:opacity-100 transition">
                            @csrf @method('DELETE')
                            <button class="px-3 py-1 rounded-lg text-xs font-medium border border-red-400 text-red-400 hover:bg-red-500 hover:text-white transition">Hapus</button>
                        </form>
                    </div>
                </div>
                <div id="partai-{{ $partai->id }}" class="hidden">
                    @foreach($partai->calegs as $caleg)
                    <div class="flex items-center justify-between px-8 py-3 border-t dark:border-gray-700 border-gray-100 group">
                        <div class="flex items-center gap-3">
                            <span class="text-xs dark:text-gray-500 text-gray-400 w-4">{{ $caleg->nomor_urut }}</span>
                            <p class="text-sm dark:text-gray-200 text-gray-700">{{ $caleg->nama_caleg }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.setup.caleg.destroy', $caleg) }}"
                              onsubmit="return confirm('Hapus caleg ini?')" class="opacity-0 group-hover:opacity-100 transition">
                            @csrf @method('DELETE')
                            <button class="px-2 py-1 rounded text-xs border border-red-400 text-red-400 hover:bg-red-500 hover:text-white transition">×</button>
                        </form>
                    </div>
                    @endforeach
                    <div class="px-8 py-4 border-t dark:border-gray-700 border-gray-100 dark:bg-gray-900/30 bg-gray-50">
                        <form method="POST" action="{{ route('admin.setup.caleg.store', $partai) }}" class="flex gap-2">
                            @csrf
                            <input type="number" name="nomor_urut" placeholder="No" min="1"
                                   class="w-16 dark:bg-gray-900 bg-white border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2 text-xs rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                            <input type="text" name="nama_caleg" placeholder="Nama caleg..."
                                   class="flex-1 dark:bg-gray-900 bg-white border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2 text-xs rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                            <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition">+ Caleg</button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="px-6 py-10 text-center dark:text-gray-600 text-gray-400 text-sm">Belum ada partai.</div>
            @endforelse
        </div>
    </div>
</div>
@endforeach

{{-- ══ TAB DPRD KAB ══ --}}
<div id="panel-dprd_kab" class="tab-panel hidden">

    {{-- Row 1: Setup Dapil + Assign Kecamatan --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Form tambah dapil --}}
        <div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 p-6 shadow-sm">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-5 font-semibold">// Daftar Dapil</p>
            <form method="POST" action="{{ route('admin.setup.dapil.store') }}" class="flex gap-2 mb-4">
                @csrf
                <input type="text" name="nama" placeholder="cth: Dapil 1"
                       class="flex-1 dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                <button class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition">+ Tambah</button>
            </form>
            @forelse($dapils as $dapil)
            <div class="flex items-center justify-between py-2.5 border-b dark:border-gray-700 border-gray-100 last:border-0 group">
                <span class="text-sm dark:text-gray-200 text-gray-700 font-medium">{{ $dapil->nama }}</span>
                <div class="flex items-center gap-2">
                    <span class="text-xs dark:text-gray-500 text-gray-400">{{ $dapil->kecamatans->count() }} kecamatan</span>
                    <form method="POST" action="{{ route('admin.setup.dapil.destroy', $dapil) }}"
                          onsubmit="return confirm('Hapus dapil ini?')" class="opacity-0 group-hover:opacity-100 transition">
                        @csrf @method('DELETE')
                        <button class="px-2 py-1 rounded text-xs border border-red-400 text-red-400 hover:bg-red-500 hover:text-white transition">×</button>
                    </form>
                </div>
            </div>
            @empty
            <p class="text-xs dark:text-gray-600 text-gray-400 text-center py-4">Belum ada dapil.</p>
            @endforelse
        </div>

        {{-- Assign kecamatan ke dapil --}}
        <div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 p-6 shadow-sm">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-5 font-semibold">// Assign Kecamatan ke Dapil</p>
            @forelse($kecamatans as $kec)
            <form method="POST" action="{{ route('admin.setup.kecamatan.dapil') }}"
                  class="flex items-center gap-3 py-2.5 border-b dark:border-gray-700 border-gray-100 last:border-0">
                @csrf
                <input type="hidden" name="kecamatan_id" value="{{ $kec->id }}">
                <p class="flex-1 text-sm dark:text-gray-200 text-gray-700">{{ $kec->nama }}</p>
                <select name="dapil_id" onchange="this.form.submit()"
                        class="dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-3 py-1.5 text-xs rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                    <option value="">— Pilih Dapil —</option>
                    @foreach($dapils as $dapil)
                    <option value="{{ $dapil->id }}" {{ $kec->dapil_id == $dapil->id ? 'selected' : '' }}>
                        {{ $dapil->nama }}
                    </option>
                    @endforeach
                </select>
            </form>
            @empty
            <p class="text-xs dark:text-gray-600 text-gray-400 text-center py-4">Belum ada kecamatan.</p>
            @endforelse
        </div>
    </div>

    {{-- Row 2: Tambah Partai per Dapil --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 p-6 shadow-sm">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-5 font-semibold">// Tambah Partai DPRD Kab</p>
            <form method="POST" action="{{ route('admin.setup.partai.store') }}">
                @csrf
                <input type="hidden" name="jenis" value="dprd_kab">
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Dapil</label>
                    <select name="dapil_id"
                            class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                        <option value="">— Pilih Dapil —</option>
                        @foreach($dapils as $dapil)
                        <option value="{{ $dapil->id }}">{{ $dapil->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">No. Urut</label>
                    <input type="number" name="nomor_urut" min="1" placeholder="1"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Partai</label>
                    <input type="text" name="nama_partai" placeholder="cth: Partai Kebangkitan Bangsa"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-lg text-sm transition">
                    Tambah Partai →
                </button>
            </form>
        </div>

        {{-- Daftar partai per dapil --}}
        <div class="lg:col-span-2 dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b dark:border-gray-700 border-gray-200">
                <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Daftar Partai DPRD Kab per Dapil</p>
            </div>

            @if($dapils->isEmpty())
            <div class="px-6 py-10 text-center dark:text-gray-600 text-gray-400 text-sm">Belum ada dapil. Tambah dapil terlebih dahulu.</div>
            @else

            {{-- Tab dapil --}}
            <div class="flex gap-1 p-3 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-900/30 bg-gray-50 flex-wrap">
                @foreach($dapils as $i => $dapil)
                @php $dapilPartais = $partaiKab[(string)$dapil->id] ?? collect(); @endphp
                <button onclick="switchDapilTab({{ $dapil->id }})" id="dapil-tab-{{ $dapil->id }}"
                        class="px-4 py-2 text-xs font-semibold rounded-lg transition dapil-tab-btn">
                    {{ $dapil->nama }}
                    <span class="ml-1 px-1.5 py-0.5 rounded text-[10px]
                                dark:bg-gray-700 bg-gray-200 dark:text-gray-400 text-gray-500">
                        {{ $dapilPartais->count() }}
                    </span>
                </button>
                @endforeach
            </div>

            {{-- Panel per dapil --}}
            @foreach($dapils as $dapil)
            @php $dapilPartais = $partaiKab[(string)$dapil->id] ?? collect(); @endphp
            <div id="dapil-panel-{{ $dapil->id }}" class="dapil-panel hidden">
                @forelse($dapilPartais as $partai)
                <div class="border-b dark:border-gray-700 border-gray-100 last:border-0">
                    {{-- Header partai --}}
                    <div class="flex items-center justify-between px-6 py-3 dark:bg-gray-750 bg-gray-50 cursor-pointer group"
                        onclick="togglePartai({{ $partai->id }})">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-violet-500 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                                {{ $partai->nomor_urut }}
                            </span>
                            <p class="text-sm font-semibold dark:text-gray-100 text-gray-800">{{ $partai->nama_partai }}</p>
                            <span class="text-[10px] dark:text-gray-500 text-gray-400">{{ $partai->calegs->count() }} caleg</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="arrow-partai-{{ $partai->id }}" class="dark:text-gray-500 text-gray-400 text-xs">▸</span>
                            <form method="POST" action="{{ route('admin.setup.partai.destroy', $partai) }}"
                                onsubmit="return confirm('Hapus partai dan semua calegnya?')" class="opacity-0 group-hover:opacity-100 transition">
                                @csrf @method('DELETE')
                                <button class="px-3 py-1 rounded-lg text-xs font-medium border border-red-400 text-red-400 hover:bg-red-500 hover:text-white transition">Hapus</button>
                            </form>
                        </div>
                    </div>
                    {{-- Caleg --}}
                    <div id="partai-{{ $partai->id }}" class="hidden">
                        @foreach($partai->calegs as $caleg)
                        <div class="flex items-center justify-between px-8 py-3 border-t dark:border-gray-700 border-gray-100 group">
                            <div class="flex items-center gap-3">
                                <span class="text-xs dark:text-gray-500 text-gray-400 w-4">{{ $caleg->nomor_urut }}</span>
                                <p class="text-sm dark:text-gray-200 text-gray-700">{{ $caleg->nama_caleg }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.setup.caleg.destroy', $caleg) }}"
                                onsubmit="return confirm('Hapus caleg ini?')" class="opacity-0 group-hover:opacity-100 transition">
                                @csrf @method('DELETE')
                                <button class="px-2 py-1 rounded text-xs border border-red-400 text-red-400 hover:bg-red-500 hover:text-white transition">×</button>
                            </form>
                        </div>
                        @endforeach
                        {{-- Form tambah caleg --}}
                        <div class="px-8 py-4 border-t dark:border-gray-700 border-gray-100 dark:bg-gray-900/30 bg-gray-50">
                            <form method="POST" action="{{ route('admin.setup.caleg.store', $partai) }}" class="flex gap-2">
                                @csrf
                                <input type="number" name="nomor_urut" placeholder="No" min="1"
                                    class="w-16 dark:bg-gray-900 bg-white border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2 text-xs rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                                <input type="text" name="nama_caleg" placeholder="Nama caleg..."
                                    class="flex-1 dark:bg-gray-900 bg-white border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2 text-xs rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                                <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition">+ Caleg</button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-10 text-center dark:text-gray-600 text-gray-400 text-sm">
                    Belum ada partai untuk {{ $dapil->nama }}.
                </div>
                @endforelse
            </div>
            @endforeach

            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
const tabs = ['ppwp','dpd','dpr_ri','dprd_prov','dprd_kab'];

function switchTab(active) {
    tabs.forEach(t => {
        const panel = document.getElementById('panel-' + t);
        const btn   = document.getElementById('tab-' + t);
        if (t === active) {
            panel.classList.remove('hidden');
            btn.classList.add('dark:bg-gray-700','bg-white','shadow','dark:text-white','text-gray-800');
            btn.classList.remove('dark:text-gray-500','text-gray-400');
        } else {
            panel.classList.add('hidden');
            btn.classList.remove('dark:bg-gray-700','bg-white','shadow','dark:text-white','text-gray-800');
            btn.classList.add('dark:text-gray-500','text-gray-400');
        }
    });
    localStorage.setItem('setup_tab', active);
}

function togglePartai(id) {
    const el    = document.getElementById('partai-' + id);
    const arrow = document.getElementById('arrow-partai-' + id);
    el.classList.toggle('hidden');
    arrow.textContent = el.classList.contains('hidden') ? '▸' : '▾';
}

function toggleDapil(id) {
    const el    = document.getElementById('dapil-' + id);
    const arrow = document.getElementById('arrow-dapil-' + id);
    el.classList.toggle('hidden');
    arrow.textContent = el.classList.contains('hidden') ? '▸' : '▾';
}

function switchDapilTab(activeId) {
    // panel
    document.querySelectorAll('.dapil-panel').forEach(el => el.classList.add('hidden'));
    document.getElementById('dapil-panel-' + activeId).classList.remove('hidden');

    // tab button style
    document.querySelectorAll('.dapil-tab-btn').forEach(btn => {
        btn.classList.remove('dark:bg-gray-700','bg-white','shadow','dark:text-white','text-gray-800');
        btn.classList.add('dark:text-gray-500','text-gray-400');
    });
    const activeBtn = document.getElementById('dapil-tab-' + activeId);
    activeBtn.classList.add('dark:bg-gray-700','bg-white','shadow','dark:text-white','text-gray-800');
    activeBtn.classList.remove('dark:text-gray-500','text-gray-400');

    localStorage.setItem('dapil_tab', activeId);
}

// auto-aktifkan tab pertama atau yang tersimpan
const savedDapilTab = localStorage.getItem('dapil_tab');
const firstDapilBtn = document.querySelector('.dapil-tab-btn');
if (firstDapilBtn) {
    const firstId = firstDapilBtn.id.replace('dapil-tab-','');
    switchDapilTab(savedDapilTab || firstId);
}

// Restore tab dari localStorage
const savedTab = localStorage.getItem('setup_tab') || 'ppwp';
switchTab(savedTab);
</script>
@endpush
@endsection