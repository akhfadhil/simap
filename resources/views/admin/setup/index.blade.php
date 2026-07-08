@extends('layouts.admin')
@section('title', 'Setup Data Pemilu')
@section('admin_active', 'setup')

@section('admin_content')
<div class="mb-8">
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// Admin — Setup</p>
    <h1 class="font-display text-4xl tracking-[2px] admin-text">SETUP DATA PEMILU</h1>
    <p class="dark:text-gray-400 text-gray-500 text-sm mt-1">Input master data paslon, calon, partai, dan caleg.</p>
</div>

<div id="alert-container">
    @if(session('success'))
    <div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 px-4 py-3 text-xs mb-6 rounded-lg font-medium">
        ✓ {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 text-xs mb-6 rounded-lg font-medium">
        {{ $errors->first() }}
    </div>
    @endif
</div>

{{-- TAB NAVIGATION --}}
<div class="flex gap-1 mb-6 dark:bg-gray-900 bg-gray-100 p-1 rounded-xl w-fit">
    @foreach(['ppwp'=>'PPWP','gubernur'=>'Gubernur','bupati'=>'Bupati','dpd'=>'DPD','dpr_ri'=>'DPR RI','dprd_prov'=>'DPRD Prov','dprd_kab'=>'DPRD Kab'] as $tab => $label)
    <button onclick="switchTab('{{ $tab }}')" id="tab-{{ $tab }}"
            class="px-4 py-2 text-xs font-semibold rounded-lg transition tab-btn"
            data-tab="{{ $tab }}">
        {{ $label }}
    </button>
    @endforeach
</div>

{{-- Pengaturan Jenis Pemilu --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden mb-8">
    <div class="px-6 py-4 border-b dark:border-gray-700 border-gray-200">
        <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Aktifkan Jenis Pemilu</p>
        <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">Hanya jenis yang dicentang yang bisa diakses oleh KPPS, PPS, dan PPK.</p>
    </div>
    <form method="POST" action="{{ route('admin.setup.pemilu.settings') }}" class="p-6">
        @csrf
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 mb-5">
            @foreach([
                'ppwp'      => 'Presiden & Wakil Presiden',
                'gubernur'  => 'Gubernur & Wakil Gubernur',
                'bupati'    => 'Bupati & Wakil Bupati',
                'dpd'       => 'DPD',
                'dpr_ri'    => 'DPR RI',
                'dprd_prov' => 'DPRD Provinsi',
                'dprd_kab'  => 'DPRD Kabupaten',
            ] as $key => $label)
            @php $active = $pemiluSettings[$key]->is_active ?? true; @endphp
            <label class="flex items-center gap-3 dark:bg-gray-700/50 bg-gray-50 border dark:border-gray-700 border-gray-200 rounded-lg px-4 py-3 cursor-pointer hover:border-red-400 transition {{ $active ? 'border-red-400/50' : '' }}">
                <input type="checkbox" name="jenis_{{ $key }}" value="1" {{ $active ? 'checked' : '' }}
                       class="w-4 h-4 accent-red-500">
                <span class="text-sm dark:text-gray-300 text-gray-600 font-medium">{{ $label }}</span>
            </label>
            @endforeach
        </div>
        <button class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition">
            Simpan Pengaturan
        </button>
    </form>
</div>

{{-- ══ SEKSI SETUP PROFIL PARTAI (COLLAPSIBLE) ══ --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden mb-8">
    <button type="button" onclick="togglePartaiProfileSection()" class="w-full px-6 py-4 flex items-center justify-between border-b dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
        <div class="text-left">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Setup Profil & Nomor Urut Partai</p>
            <p class="text-xs dark:text-gray-400 text-gray-500 mt-1">Kelola nama resmi, akronim, nomor urut aktif, logo, dan warna aksen partai politik.</p>
        </div>
        <span id="partai-profile-section-arrow" class="text-lg dark:text-gray-400 text-gray-500">▼</span>
    </button>
    
    <div id="partai-profile-section-content" class="p-6">
        <!-- Grid Partai Profiles -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($partaiProfiles as $profile)
            <div data-profile-card="{{ $profile->id }}" class="dark:bg-gray-700/40 bg-gray-50 border dark:border-gray-700 border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition duration-200">
                <!-- Header with dynamic accent color -->
                <div class="h-2 w-full" data-color-bar style="background-color: {{ $profile->warna_utama ?: '#6B7280' }}"></div>
                
                <div class="p-5 flex-1">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3" data-logo-container>
                            @if($profile->logo_path)
                                <img data-logo src="{{ asset($profile->logo_path) }}" alt="{{ $profile->nama_singkat }}" class="w-12 h-12 object-contain rounded-lg border dark:border-gray-700 border-gray-100 p-1 bg-white">
                            @else
                                <div data-avatar-placeholder class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center border dark:border-gray-600 border-gray-200 text-gray-400 dark:text-gray-500 font-bold text-lg">
                                    {{ substr($profile->nama_singkat, 0, 2) }}
                                </div>
                            @endif
                            <div>
                                <h3 class="font-bold text-sm dark:text-gray-100 text-gray-800 leading-snug" data-nama>{{ $profile->nama }}</h3>
                                <p class="text-xs dark:text-gray-400 text-gray-500 font-medium" data-nama-singkat>{{ $profile->nama_singkat }}</p>
                            </div>
                        </div>
                        
                        <span class="w-8 h-8 rounded-full text-white text-sm font-extrabold flex items-center justify-center flex-shrink-0 shadow-sm" data-nomor-badge style="background-color: {{ $profile->warna_utama ?: '#ef4444' }}">
                            {{ $profile->nomor_urut_aktif }}
                        </span>
                    </div>

                    <div class="border-t dark:border-gray-700 border-gray-200 pt-3.5 mt-3 text-xs space-y-3.5">
                        <!-- Kepengurusan DPC -->
                        <div class="space-y-1.5">
                            <span class="text-[10px] uppercase font-bold dark:text-gray-400 text-gray-500 tracking-wider flex items-center gap-1.5">
                                👥 Kepengurusan DPC
                            </span>
                            <div class="pl-2.5 border-l-2 border-red-500/30 dark:border-red-500/20 space-y-1 dark:text-gray-300 text-gray-600">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold text-gray-400 dark:text-gray-500">Ketua:</span> 
                                        <span class="font-medium" data-display-ketua>{{ $profile->nama_ketua ?: '-' }}</span>
                                    </div>
                                    <div data-telp-ketua-container>
                                        @if($profile->telp_ketua)
                                            <a href="tel:{{ $profile->telp_ketua }}" class="text-[10px] bg-red-50 dark:bg-red-950/30 dark:text-red-400 text-red-600 hover:bg-red-100 dark:hover:bg-red-950/60 px-2 py-0.5 rounded font-mono transition" data-display-telp-ketua>{{ $profile->telp_ketua }}</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold text-gray-400 dark:text-gray-500">Sekretaris:</span> 
                                        <span class="font-medium" data-display-sekretaris>{{ $profile->nama_sekretaris ?: '-' }}</span>
                                    </div>
                                    <div data-telp-sekretaris-container>
                                        @if($profile->telp_sekretaris)
                                            <a href="tel:{{ $profile->telp_sekretaris }}" class="text-[10px] bg-red-50 dark:bg-red-950/30 dark:text-red-400 text-red-600 hover:bg-red-100 dark:hover:bg-red-950/60 px-2 py-0.5 rounded font-mono transition" data-display-telp-sekretaris>{{ $profile->telp_sekretaris }}</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold text-gray-400 dark:text-gray-500">Bendahara:</span> 
                                        <span class="font-medium" data-display-bendahara>{{ $profile->nama_bendahara ?: '-' }}</span>
                                    </div>
                                    <div data-telp-bendahara-container>
                                        @if($profile->telp_bendahara)
                                            <a href="tel:{{ $profile->telp_bendahara }}" class="text-[10px] bg-red-50 dark:bg-red-950/30 dark:text-red-400 text-red-600 hover:bg-red-100 dark:hover:bg-red-950/60 px-2 py-0.5 rounded font-mono transition" data-display-telp-bendahara>{{ $profile->telp_bendahara }}</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kantor Sekretariat -->
                        <div class="space-y-1.5 pt-1.5">
                            <span class="text-[10px] uppercase font-bold dark:text-gray-400 text-gray-500 tracking-wider flex items-center gap-1.5">
                                📍 Kantor Sekretariat
                            </span>
                            <div class="pl-2.5 border-l-2 border-blue-500/30 dark:border-blue-500/20 space-y-2 dark:text-gray-300 text-gray-600">
                                <div class="line-clamp-2 leading-relaxed text-xs" data-display-alamat title="{{ $profile->alamat_kantor }}">{{ $profile->alamat_kantor ?: 'Alamat belum diatur' }}</div>
                                <div class="flex items-center justify-between text-[10px] pt-0.5">
                                    <span class="px-2 py-0.5 rounded-full dark:bg-gray-800 bg-gray-100 border dark:border-gray-700 border-gray-200 text-gray-500 dark:text-gray-400 font-semibold" data-display-status>Status: {{ $profile->status_kantor ?: '-' }}</span>
                                    <div data-display-maps-container>
                                        @if($profile->google_maps_url)
                                            <a href="{{ $profile->google_maps_url }}" target="_blank" class="text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 font-bold flex items-center gap-0.5" data-display-maps>Peta Lokasi ↗</a>
                                        @else
                                            <span class="text-gray-400 italic" data-display-maps-placeholder>Maps ↗</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-3.5 dark:bg-gray-900/30 bg-gray-100 border-t dark:border-gray-700 border-gray-200 flex justify-end">
                    <button type="button" 
                            data-profile="{{ json_encode($profile) }}"
                            onclick="openEditProfileModal(this)"
                            class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-600 hover:text-white dark:bg-red-950/20 dark:text-red-400 dark:hover:bg-red-900/60 border border-red-200 dark:border-red-900/30 transition-all duration-200 shadow-sm flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Profil
                    </button>
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 text-center dark:text-gray-500 text-gray-400 text-sm">
                Belum ada data profil partai. Jalankan seeder partai atau buat data terlebih dahulu.
            </div>
            @endforelse
        </div>
    </div>
</div>


{{-- ══ TAB PPWP ══ --}}
<div id="panel-ppwp" class="tab-panel">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <div class="lg:col-span-2 dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 p-6 shadow-sm">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-5 font-semibold">// Tambah Paslon</p>
            <form method="POST" action="{{ route('admin.setup.ppwp.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">No. Urut</label>
                    <input type="number" name="calons[0][nomor_urut]" min="1" max="99" placeholder="1"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Paslon</label>
                    <input type="text" name="calons[0][nama_paslon]" placeholder="NAMA CALON - NAMA WAKIL"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div class="paslon-extra-rows"></div>
                <button type="button" onclick="addPaslonFields(this)"
                        class="w-full mb-3 border dark:border-gray-700 border-gray-300 dark:text-gray-400 text-gray-500 hover:border-red-400 hover:text-red-500 font-semibold py-2.5 rounded-lg text-xs transition">
                    + Tambah Baris Paslon
                </button>
                <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-lg text-sm transition">
                    Tambah →
                </button>
            </form>
        </div>
        <div class="lg:col-span-3 dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden">
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

{{-- ══ TAB GUBERNUR ══ --}}
<div id="panel-gubernur" class="tab-panel hidden">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <div class="lg:col-span-2 dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 p-6 shadow-sm">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-5 font-semibold">// Tambah Paslon Gubernur</p>
            <form method="POST" action="{{ route('admin.setup.gubernur.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">No. Urut</label>
                    <input type="number" name="calons[0][nomor_urut]" min="1" max="99" placeholder="1"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Paslon</label>
                    <input type="text" name="calons[0][nama_paslon]" placeholder="NAMA CALON - NAMA WAKIL"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div class="paslon-extra-rows"></div>
                <button type="button" onclick="addPaslonFields(this)"
                        class="w-full mb-3 border dark:border-gray-700 border-gray-300 dark:text-gray-400 text-gray-500 hover:border-red-400 hover:text-red-500 font-semibold py-2.5 rounded-lg text-xs transition">
                    + Tambah Baris Paslon
                </button>
                <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-lg text-sm transition">
                    Tambah →
                </button>
            </form>
        </div>
        <div class="lg:col-span-3 dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b dark:border-gray-700 border-gray-200">
                <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Daftar Paslon Gubernur ({{ $gubernurCalons->count() }})</p>
            </div>
            @forelse($gubernurCalons as $c)
            <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700 border-gray-100 last:border-0 group">
                <div class="flex items-center gap-4">
                    <span class="w-8 h-8 rounded-full bg-red-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                        {{ $c->nomor_urut }}
                    </span>
                    <p class="text-sm font-medium dark:text-gray-100 text-gray-800">{{ $c->nama_paslon }}</p>
                </div>
                <form method="POST" action="{{ route('admin.setup.gubernur.destroy', $c) }}"
                      onsubmit="return confirm('Hapus paslon ini?')" class="opacity-0 group-hover:opacity-100 transition">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1.5 rounded-lg text-xs font-medium border border-red-400 text-red-400 hover:bg-red-500 hover:text-white transition">Hapus</button>
                </form>
            </div>
            @empty
            <div class="px-6 py-10 text-center dark:text-gray-600 text-gray-400 text-sm">Belum ada paslon gubernur.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ══ TAB BUPATI ══ --}}
<div id="panel-bupati" class="tab-panel hidden">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <div class="lg:col-span-2 dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 p-6 shadow-sm">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-5 font-semibold">// Tambah Paslon Bupati</p>
            <form method="POST" action="{{ route('admin.setup.bupati.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">No. Urut</label>
                    <input type="number" name="calons[0][nomor_urut]" min="1" max="99" placeholder="1"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Paslon</label>
                    <input type="text" name="calons[0][nama_paslon]" placeholder="NAMA CALON - NAMA WAKIL"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div class="paslon-extra-rows"></div>
                <button type="button" onclick="addPaslonFields(this)"
                        class="w-full mb-3 border dark:border-gray-700 border-gray-300 dark:text-gray-400 text-gray-500 hover:border-red-400 hover:text-red-500 font-semibold py-2.5 rounded-lg text-xs transition">
                    + Tambah Baris Paslon
                </button>
                <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-lg text-sm transition">
                    Tambah →
                </button>
            </form>
        </div>
        <div class="lg:col-span-3 dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b dark:border-gray-700 border-gray-200">
                <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Daftar Paslon Bupati ({{ $bupatiCalons->count() }})</p>
            </div>
            @forelse($bupatiCalons as $c)
            <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700 border-gray-100 last:border-0 group">
                <div class="flex items-center gap-4">
                    <span class="w-8 h-8 rounded-full bg-red-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                        {{ $c->nomor_urut }}
                    </span>
                    <p class="text-sm font-medium dark:text-gray-100 text-gray-800">{{ $c->nama_paslon }}</p>
                </div>
                <form method="POST" action="{{ route('admin.setup.bupati.destroy', $c) }}"
                      onsubmit="return confirm('Hapus paslon ini?')" class="opacity-0 group-hover:opacity-100 transition">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1.5 rounded-lg text-xs font-medium border border-red-400 text-red-400 hover:bg-red-500 hover:text-white transition">Hapus</button>
                </form>
            </div>
            @empty
            <div class="px-6 py-10 text-center dark:text-gray-600 text-gray-400 text-sm">Belum ada paslon bupati.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- ══ TAB DPD ══ --}}
<div id="panel-dpd" class="tab-panel hidden">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <div class="lg:col-span-2 dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 p-6 shadow-sm">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-5 font-semibold">// Tambah Calon DPD</p>
            <form method="POST" action="{{ route('admin.setup.dpd.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">No. Urut</label>
                    <input type="number" name="calons[0][nomor_urut]" min="1" placeholder="1"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Calon</label>
                    <input type="text" name="calons[0][nama_calon]" placeholder="Nama lengkap calon DPD"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div class="calon-extra-rows"></div>
                <button type="button" onclick="addCalonFields(this)"
                        class="w-full mb-3 border dark:border-gray-700 border-gray-300 dark:text-gray-400 text-gray-500 hover:border-red-400 hover:text-red-500 font-semibold py-2.5 rounded-lg text-xs transition">
                    + Tambah Baris Calon
                </button>
                <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-lg text-sm transition">
                    Tambah →
                </button>
            </form>
        </div>
        <div class="lg:col-span-3 dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden">
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
@foreach(['dpr_ri'=>['partaiDprRi','DPR RI','bg-orange-500'],'dprd_prov'=>['partaiProv','DPRD Provinsi','bg-sky-500']] as $jenis => [$var, $label, $color])
<div id="panel-{{ $jenis }}" class="tab-panel hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 p-6 shadow-sm">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-5 font-semibold">// Tambah Partai</p>
            <form method="POST" action="{{ route('admin.setup.partai.store') }}">
                @csrf
                <input type="hidden" name="jenis" value="{{ $jenis }}">
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">No. Urut</label>
                    <input type="number" name="partais[0][nomor_urut]" min="1" placeholder="1"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Partai</label>
                    <input type="text" name="partais[0][nama_partai]" placeholder="cth: Partai Kebangkitan Bangsa"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div class="partai-extra-rows"></div>
                <button type="button" onclick="addPartaiFields(this)"
                        class="w-full mb-3 border dark:border-gray-700 border-gray-300 dark:text-gray-400 text-gray-500 hover:border-red-400 hover:text-red-500 font-semibold py-2.5 rounded-lg text-xs transition">
                    + Tambah Baris Partai
                </button>
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
                <div class="flex items-center justify-between px-6 py-3 dark:bg-gray-700 bg-gray-50 cursor-pointer group"
                     onclick="togglePartai({{ $partai->id }})"
                     data-rekap-partai-row="{{ $partai->id }}"
                     data-rekap-partai-nama="{{ strtolower($partai->nama_partai) }}">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg {{ $color }} text-white text-xs font-bold flex items-center justify-center flex-shrink-0"
                              data-rekap-partai-badge>
                            {{ $partai->nomor_urut }}
                        </span>
                        <p class="text-sm font-semibold dark:text-gray-100 text-gray-800"
                           data-rekap-partai-name-text>{{ $partai->nama_partai }}</p>
                        <span class="text-[10px] dark:text-gray-500 text-gray-400" data-caleg-count="{{ $partai->id }}">{{ $partai->calegs->count() }} caleg</span>
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
                              data-ajax-delete="caleg" class="opacity-0 group-hover:opacity-100 transition">
                            @csrf @method('DELETE')
                            <button class="px-2 py-1 rounded text-xs border border-red-400 text-red-400 hover:bg-red-500 hover:text-white transition">×</button>
                        </form>
                    </div>
                    @endforeach
                    <div class="px-8 py-4 border-t dark:border-gray-700 border-gray-100 dark:bg-gray-900/30 bg-gray-50">
                        <form method="POST" action="{{ route('admin.setup.caleg.store', $partai) }}" class="flex gap-2" data-ajax-caleg data-partai-id="{{ $partai->id }}">
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
            @if($kecamatans->isEmpty())
            <p class="text-xs dark:text-gray-600 text-gray-400 text-center py-4">Belum ada kecamatan.</p>
            @else
            <form method="POST" action="{{ route('admin.setup.kecamatan.dapil') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-96 overflow-y-auto pr-1 mb-5">
            @foreach($kecamatans as $kec)
                    <label class="dark:bg-gray-900/60 bg-gray-50 border dark:border-gray-700 border-gray-200 rounded-lg px-3 py-3">
                <span class="block text-xs font-semibold dark:text-gray-300 text-gray-700 mb-2 truncate">{{ $kec->nama }}</span>
                <select name="kecamatan_dapil[{{ $kec->id }}]"
                        class="w-full dark:bg-gray-900 bg-white border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-3 py-2 text-xs rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                    <option value="">— Pilih Dapil —</option>
                    @foreach($dapils as $dapil)
                    <option value="{{ $dapil->id }}" {{ $kec->dapil_id == $dapil->id ? 'selected' : '' }}>
                        {{ $dapil->nama }}
                    </option>
                    @endforeach
                </select>
                    </label>
            @endforeach
                </div>
                <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-lg text-sm transition">
                    Simpan Assign Dapil
                </button>
            </form>
            @endif
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
                    <input type="number" name="partais[0][nomor_urut]" min="1" placeholder="1"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Partai</label>
                    <input type="text" name="partais[0][nama_partai]" placeholder="cth: Partai Kebangkitan Bangsa"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div class="partai-extra-rows"></div>
                <button type="button" onclick="addPartaiFields(this)"
                        class="w-full mb-3 border dark:border-gray-700 border-gray-300 dark:text-gray-400 text-gray-500 hover:border-red-400 hover:text-red-500 font-semibold py-2.5 rounded-lg text-xs transition">
                    + Tambah Baris Partai
                </button>
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
                    <div class="flex items-center justify-between px-6 py-3 dark:bg-gray-700 bg-gray-50 cursor-pointer group"                    
                        onclick="togglePartai({{ $partai->id }})"
                        data-rekap-partai-row="{{ $partai->id }}"
                        data-rekap-partai-nama="{{ strtolower($partai->nama_partai) }}">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-violet-500 text-white text-xs font-bold flex items-center justify-center flex-shrink-0"
                                  data-rekap-partai-badge>
                                {{ $partai->nomor_urut }}
                            </span>
                            <p class="text-sm font-semibold dark:text-gray-100 text-gray-800"
                               data-rekap-partai-name-text>{{ $partai->nama_partai }}</p>
                            <span class="text-[10px] dark:text-gray-500 text-gray-400" data-caleg-count="{{ $partai->id }}">{{ $partai->calegs->count() }} caleg</span>
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
                              data-ajax-delete="caleg" class="opacity-0 group-hover:opacity-100 transition">
                                @csrf @method('DELETE')
                                <button class="px-2 py-1 rounded text-xs border border-red-400 text-red-400 hover:bg-red-500 hover:text-white transition">×</button>
                            </form>
                        </div>
                        @endforeach
                        {{-- Form tambah caleg --}}
                        <div class="px-8 py-4 border-t dark:border-gray-700 border-gray-100 dark:bg-gray-900/30 bg-gray-50">
                            <form method="POST" action="{{ route('admin.setup.caleg.store', $partai) }}" class="flex gap-2" data-ajax-caleg data-partai-id="{{ $partai->id }}">
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

{{-- MODAL EDIT PROFIL PARTAI --}}
<div id="modal-edit-profile" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl border dark:border-gray-700 border-gray-200 dark:bg-gray-900 bg-white shadow-xl flex flex-col">
        <div class="px-6 py-4 border-b dark:border-gray-700 border-gray-200 flex items-center justify-between sticky top-0 bg-white dark:bg-gray-900 z-10">
            <h3 class="text-sm font-semibold dark:text-gray-100 text-gray-800">Edit Profil Partai</h3>
            <button type="button" onclick="closeEditProfileModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <span class="text-xl">&times;</span>
            </button>
        </div>
        
        <form id="form-edit-profile" method="POST" action="" enctype="multipart/form-data" class="p-6 space-y-4 flex-1">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Resmi Partai</label>
                    <input type="text" id="edit-nama" name="nama" required
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Singkat / Akronim</label>
                    <input type="text" id="edit-nama-singkat" name="nama_singkat" required
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nomor Urut Aktif</label>
                    <input type="number" id="edit-nomor-urut" name="nomor_urut_aktif" required min="1" max="999"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Upload Logo Partai (Opsional)</label>
                    <input type="file" id="edit-logo-file" name="logo" accept="image/*"
                           class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Warna Utama (HEX)</label>
                    <div class="flex gap-2">
                        <input type="color" id="edit-warna-utama-picker" oninput="document.getElementById('edit-warna-utama').value = this.value"
                               class="w-10 h-10 p-0 border-0 rounded-lg cursor-pointer bg-transparent">
                        <input type="text" id="edit-warna-utama" name="warna_utama" placeholder="#008000" pattern="^#[a-fA-F0-9]{6}$" required
                               oninput="document.getElementById('edit-warna-utama-picker').value = this.value"
                               class="flex-1 dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Warna Aksen (HEX)</label>
                    <div class="flex gap-2">
                        <input type="color" id="edit-warna-aksen-picker" oninput="document.getElementById('edit-warna-aksen').value = this.value"
                               class="w-10 h-10 p-0 border-0 rounded-lg cursor-pointer bg-transparent">
                        <input type="text" id="edit-warna-aksen" name="warna_aksen" placeholder="#006400" pattern="^#[a-fA-F0-9]{6}$" required
                               oninput="document.getElementById('edit-warna-aksen-picker').value = this.value"
                               class="flex-1 dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Section: Kantor Sekretariat -->
            <div class="border-t dark:border-gray-700 border-gray-200 pt-3">
                <h4 class="text-xs font-bold dark:text-gray-300 text-gray-700 uppercase tracking-wider mb-3">📍 Kantor Sekretariat</h4>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Alamat Lengkap Kantor</label>
                        <textarea id="edit-alamat-kantor" name="alamat_kantor" rows="2"
                                  class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Status Kantor</label>
                            <select id="edit-status-kantor" name="status_kantor"
                                    class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                                <option value="">-- Pilih Status --</option>
                                <option value="Milik Sendiri">Milik Sendiri</option>
                                <option value="Sewa">Sewa</option>
                                <option value="Pinjam Pakai">Pinjam Pakai</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Link Google Maps URL</label>
                            <input type="text" id="edit-google-maps-url" name="google_maps_url" placeholder="https://maps.google.com/..."
                                   class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section: Kepengurusan DPC -->
            <div class="border-t dark:border-gray-700 border-gray-200 pt-3">
                <h4 class="text-xs font-bold dark:text-gray-300 text-gray-700 uppercase tracking-wider mb-3">👥 Kepengurusan DPC</h4>
                <div class="space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Ketua</label>
                            <input type="text" id="edit-nama-ketua" name="nama_ketua"
                                   class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">No. HP Ketua</label>
                            <input type="text" id="edit-telp-ketua" name="telp_ketua" placeholder="0812..."
                                   class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Sekretaris</label>
                            <input type="text" id="edit-nama-sekretaris" name="nama_sekretaris"
                                   class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">No. HP Sekretaris</label>
                            <input type="text" id="edit-telp-sekretaris" name="telp_sekretaris" placeholder="0812..."
                                   class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Bendahara</label>
                            <input type="text" id="edit-nama-bendahara" name="nama_bendahara"
                                   class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">No. HP Bendahara</label>
                            <input type="text" id="edit-telp-bendahara" name="telp_bendahara" placeholder="0812..."
                                   class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t dark:border-gray-700 border-gray-200 sticky bottom-0 bg-white dark:bg-gray-900 py-2 z-10">
                <button type="button" onclick="closeEditProfileModal()" 
                        class="px-4 py-2 rounded-lg text-xs font-semibold border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 rounded-lg text-xs font-semibold bg-red-600 hover:bg-red-700 text-white transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const tabs = ['ppwp','gubernur','bupati','dpd','dpr_ri','dprd_prov','dprd_kab'];

function addPaslonFields(button) {
    const form = button.closest('form');
    const container = form.querySelector('.paslon-extra-rows');
    const indexes = Array.from(form.querySelectorAll('input[name*="[nomor_urut]"]'))
        .map((input) => input.name.match(/calons\[(\d+)\]/))
        .filter(Boolean)
        .map((match) => Number(match[1]));
    const index = indexes.length ? Math.max(...indexes) + 1 : 0;
    const row = document.createElement('div');
    row.className = 'grid grid-cols-1 md:grid-cols-[96px_1fr_auto] gap-2 mb-4 items-end';
    row.innerHTML = `
        <div>
            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">No. Urut</label>
            <input type="number" name="calons[${index}][nomor_urut]" min="1" max="99" placeholder="${index + 1}"
                   class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
        </div>
        <div>
            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Paslon</label>
            <input type="text" name="calons[${index}][nama_paslon]" placeholder="NAMA CALON - NAMA WAKIL"
                   class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
        </div>
        <button type="button" onclick="this.closest('div').remove()"
                class="px-3 py-2.5 rounded-lg text-xs font-semibold border border-red-400/40 text-red-400 hover:bg-red-500/10 transition">
            Hapus
        </button>
    `;
    container.appendChild(row);
}

function addCalonFields(button) {
    const form = button.closest('form');
    const container = form.querySelector('.calon-extra-rows');
    const indexes = Array.from(form.querySelectorAll('input[name*="[nomor_urut]"]'))
        .map((input) => input.name.match(/calons\[(\d+)\]/))
        .filter(Boolean)
        .map((match) => Number(match[1]));
    const index = indexes.length ? Math.max(...indexes) + 1 : 0;
    const row = document.createElement('div');
    row.className = 'grid grid-cols-1 md:grid-cols-[96px_1fr_auto] gap-2 mb-4 items-end';
    row.innerHTML = `
        <div>
            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">No. Urut</label>
            <input type="number" name="calons[${index}][nomor_urut]" min="1" placeholder="${index + 1}"
                   class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
        </div>
        <div>
            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Calon</label>
            <input type="text" name="calons[${index}][nama_calon]" placeholder="Nama lengkap calon DPD"
                   class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
        </div>
        <button type="button" onclick="this.closest('div').remove()"
                class="px-3 py-2.5 rounded-lg text-xs font-semibold border border-red-400/40 text-red-400 hover:bg-red-500/10 transition">
            Hapus
        </button>
    `;
    container.appendChild(row);
}

function addPartaiFields(button) {
    const form = button.closest('form');
    const container = form.querySelector('.partai-extra-rows');
    const indexes = Array.from(form.querySelectorAll('input[name*="[nomor_urut]"]'))
        .map((input) => input.name.match(/partais\[(\d+)\]/))
        .filter(Boolean)
        .map((match) => Number(match[1]));
    const index = indexes.length ? Math.max(...indexes) + 1 : 0;
    const row = document.createElement('div');
    row.className = 'grid grid-cols-1 md:grid-cols-[96px_1fr_auto] gap-2 mb-4 items-end';
    row.innerHTML = `
        <div>
            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">No. Urut</label>
            <input type="number" name="partais[${index}][nomor_urut]" min="1" placeholder="${index + 1}"
                   class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
        </div>
        <div>
            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Nama Partai</label>
            <input type="text" name="partais[${index}][nama_partai]" placeholder="cth: Partai Kebangkitan Bangsa"
                   class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-3 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
        </div>
        <button type="button" onclick="this.closest('div').remove()"
                class="px-3 py-2.5 rounded-lg text-xs font-semibold border border-red-400/40 text-red-400 hover:bg-red-500/10 transition">
            Hapus
        </button>
    `;
    container.appendChild(row);
}

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

function togglePartaiProfileSection() {
    const content = document.getElementById('partai-profile-section-content');
    const arrow = document.getElementById('partai-profile-section-arrow');
    const isHidden = content.classList.contains('hidden');
    if (isHidden) {
        content.classList.remove('hidden');
        arrow.textContent = '▼';
        localStorage.setItem('partai_profile_section_collapsed', 'false');
    } else {
        content.classList.add('hidden');
        arrow.textContent = '▲';
        localStorage.setItem('partai_profile_section_collapsed', 'true');
    }
}

function openEditProfileModal(btn) {
    const profile = btn.dataset && btn.dataset.profile ? JSON.parse(btn.dataset.profile) : btn;
    const form = document.getElementById('form-edit-profile');
    form.action = `/admin/setup/partai-profile/${profile.id}`;
    
    document.getElementById('edit-nama').value = profile.nama;
    document.getElementById('edit-nama-singkat').value = profile.nama_singkat;
    document.getElementById('edit-nomor-urut').value = profile.nomor_urut_aktif;
    
    const warnaUtama = profile.warna_utama || '#000000';
    document.getElementById('edit-warna-utama').value = warnaUtama;
    document.getElementById('edit-warna-utama-picker').value = warnaUtama;
    
    const warnaAksen = profile.warna_aksen || '#000000';
    document.getElementById('edit-warna-aksen').value = warnaAksen;
    document.getElementById('edit-warna-aksen-picker').value = warnaAksen;

    // Reset file upload input
    document.getElementById('edit-logo-file').value = '';

    // Populate new detail inputs
    document.getElementById('edit-alamat-kantor').value = profile.alamat_kantor || '';
    document.getElementById('edit-status-kantor').value = profile.status_kantor || '';
    document.getElementById('edit-google-maps-url').value = profile.google_maps_url || '';
    document.getElementById('edit-nama-ketua').value = profile.nama_ketua || '';
    document.getElementById('edit-telp-ketua').value = profile.telp_ketua || '';
    document.getElementById('edit-nama-sekretaris').value = profile.nama_sekretaris || '';
    document.getElementById('edit-telp-sekretaris').value = profile.telp_sekretaris || '';
    document.getElementById('edit-nama-bendahara').value = profile.nama_bendahara || '';
    document.getElementById('edit-telp-bendahara').value = profile.telp_bendahara || '';

    const modal = document.getElementById('modal-edit-profile');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditProfileModal() {
    const modal = document.getElementById('modal-edit-profile');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.getElementById('form-edit-profile').addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.classList.add('opacity-60');

    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token,
            },
            body: formData
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Gagal menyimpan perubahan');
        }

        const data = await response.json();
        
        // 1. Close Modal
        closeEditProfileModal();

        // 2. Update the specific party card
        const profile = data.profile;
        const sync = data.sync;
        const card = document.querySelector(`[data-profile-card="${profile.id}"]`);
        
        if (card) {
            // Update color bar background
            const colorBar = card.querySelector('[data-color-bar]');
            if (colorBar) colorBar.style.backgroundColor = profile.warna_utama || '#6B7280';
            
            // Update logo/placeholder
            let logoImg = card.querySelector('[data-logo]');
            const placeholder = card.querySelector('[data-avatar-placeholder]');
            if (profile.logo_path) {
                if (logoImg) {
                    logoImg.src = '/' + profile.logo_path;
                    logoImg.alt = profile.nama_singkat;
                } else {
                    if (placeholder) placeholder.remove();
                    logoImg = document.createElement('img');
                    logoImg.dataset.logo = '';
                    logoImg.src = '/' + profile.logo_path;
                    logoImg.alt = profile.nama_singkat;
                    logoImg.className = 'w-12 h-12 object-contain rounded-lg border dark:border-gray-700 border-gray-100 p-1 bg-white';
                    card.querySelector('[data-logo-container]').prepend(logoImg);
                }
            } else {
                if (logoImg) logoImg.remove();
                if (!placeholder) {
                    const newPlaceholder = document.createElement('div');
                    newPlaceholder.dataset.avatarPlaceholder = '';
                    newPlaceholder.className = 'w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center border dark:border-gray-600 border-gray-200 text-gray-400 dark:text-gray-500 font-bold text-lg';
                    newPlaceholder.textContent = (profile.nama_singkat || '').substring(0, 2);
                    card.querySelector('[data-logo-container]').prepend(newPlaceholder);
                } else {
                    placeholder.textContent = (profile.nama_singkat || '').substring(0, 2);
                }
            }

            // Update names
            const namaEl = card.querySelector('[data-nama]');
            if (namaEl) namaEl.textContent = profile.nama;
            const namaSingkatEl = card.querySelector('[data-nama-singkat]');
            if (namaSingkatEl) namaSingkatEl.textContent = profile.nama_singkat;

            // Update badge
            const badge = card.querySelector('[data-nomor-badge]');
            if (badge) {
                badge.textContent = profile.nomor_urut_aktif;
                badge.style.backgroundColor = profile.warna_utama || '#ef4444';
            }

            // Update color dots and text
            const warnaUtamaDot = card.querySelector('[data-warna-utama-dot]');
            if (warnaUtamaDot) warnaUtamaDot.style.backgroundColor = profile.warna_utama || '#6B7280';
            const warnaUtamaText = card.querySelector('[data-warna-utama-text]');
            if (warnaUtamaText) warnaUtamaText.textContent = profile.warna_utama || '-';
            const warnaAksenDot = card.querySelector('[data-warna-aksen-dot]');
            if (warnaAksenDot) warnaAksenDot.style.backgroundColor = profile.warna_aksen || '#6B7280';
            const warnaAksenText = card.querySelector('[data-warna-aksen-text]');
            if (warnaAksenText) warnaAksenText.textContent = profile.warna_aksen || '-';

            // Update history
            const historyContainer = card.querySelector('[data-history-container]');
            if (historyContainer && profile.nomor_urut_historis_json) {
                historyContainer.innerHTML = '';
                const historyDiv = document.createElement('div');
                historyDiv.className = 'flex flex-wrap gap-1 justify-end max-w-[180px]';
                Object.entries(profile.nomor_urut_historis_json).forEach(([tahun, no]) => {
                    const span = document.createElement('span');
                    span.className = 'px-2 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-[10px] dark:text-gray-300 text-gray-600 font-semibold';
                    span.title = `Tahun ${tahun}`;
                    span.textContent = `${tahun}: No.${no}`;
                    historyDiv.appendChild(span);
                });
                historyContainer.appendChild(historyDiv);
            }

            // Update Kepengurusan DPC
            const ketuaEl = card.querySelector('[data-display-ketua]');
            if (ketuaEl) ketuaEl.textContent = profile.nama_ketua || '-';
            const telpKetuaContainer = card.querySelector('[data-telp-ketua-container]');
            if (telpKetuaContainer) {
                telpKetuaContainer.innerHTML = '';
                if (profile.telp_ketua) {
                    const link = document.createElement('a');
                    link.href = `tel:${profile.telp_ketua}`;
                    link.className = 'text-[10px] bg-red-50 dark:bg-red-950/30 dark:text-red-400 text-red-600 hover:bg-red-100 dark:hover:bg-red-950/60 px-2 py-0.5 rounded font-mono transition';
                    link.dataset.displayTelpKetua = '';
                    link.textContent = profile.telp_ketua;
                    telpKetuaContainer.appendChild(link);
                }
            }

            const sekretarisEl = card.querySelector('[data-display-sekretaris]');
            if (sekretarisEl) sekretarisEl.textContent = profile.nama_sekretaris || '-';
            const telpSekretarisContainer = card.querySelector('[data-telp-sekretaris-container]');
            if (telpSekretarisContainer) {
                telpSekretarisContainer.innerHTML = '';
                if (profile.telp_sekretaris) {
                    const link = document.createElement('a');
                    link.href = `tel:${profile.telp_sekretaris}`;
                    link.className = 'text-[10px] bg-red-50 dark:bg-red-950/30 dark:text-red-400 text-red-600 hover:bg-red-100 dark:hover:bg-red-950/60 px-2 py-0.5 rounded font-mono transition';
                    link.dataset.displayTelpSekretaris = '';
                    link.textContent = profile.telp_sekretaris;
                    telpSekretarisContainer.appendChild(link);
                }
            }

            const bendaharaEl = card.querySelector('[data-display-bendahara]');
            if (bendaharaEl) bendaharaEl.textContent = profile.nama_bendahara || '-';
            const telpBendaharaContainer = card.querySelector('[data-telp-bendahara-container]');
            if (telpBendaharaContainer) {
                telpBendaharaContainer.innerHTML = '';
                if (profile.telp_bendahara) {
                    const link = document.createElement('a');
                    link.href = `tel:${profile.telp_bendahara}`;
                    link.className = 'text-[10px] bg-red-50 dark:bg-red-950/30 dark:text-red-400 text-red-600 hover:bg-red-100 dark:hover:bg-red-950/60 px-2 py-0.5 rounded font-mono transition';
                    link.dataset.displayTelpBendahara = '';
                    link.textContent = profile.telp_bendahara;
                    telpBendaharaContainer.appendChild(link);
                }
            }

            // Update Alamat & Status Kantor
            const alamatEl = card.querySelector('[data-display-alamat]');
            if (alamatEl) {
                alamatEl.textContent = profile.alamat_kantor || 'Alamat belum diatur';
                alamatEl.title = profile.alamat_kantor || '';
            }
            const statusEl = card.querySelector('[data-display-status]');
            if (statusEl) statusEl.textContent = `Status: ${profile.status_kantor || '-'}`;

            // Update Maps Link
            const mapsContainer = card.querySelector('[data-display-maps-container]');
            if (mapsContainer) {
                mapsContainer.innerHTML = '';
                if (profile.google_maps_url) {
                    const mapsLink = document.createElement('a');
                    mapsLink.href = profile.google_maps_url;
                    mapsLink.target = '_blank';
                    mapsLink.className = 'text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 font-bold flex items-center gap-0.5';
                    mapsLink.dataset.displayMaps = '';
                    mapsLink.textContent = 'Peta Lokasi ↗';
                    mapsContainer.appendChild(mapsLink);
                } else {
                    const mapsSpan = document.createElement('span');
                    mapsSpan.className = 'text-gray-400 italic';
                    mapsSpan.dataset.displayMapsPlaceholder = '';
                    mapsSpan.textContent = 'Maps ↗';
                    mapsContainer.appendChild(mapsSpan);
                }
            }

            // Update edit button profile data attribute
            const editBtn = card.querySelector('button[data-profile]');
            if (editBtn) editBtn.dataset.profile = JSON.stringify(profile);
        }

        // 3. Update all RekapPartai headers dynamically matching old/new acronyms or names
        if (sync) {
            document.querySelectorAll('[data-rekap-partai-row]').forEach(row => {
                const rowNama = (row.dataset.rekapPartaiNama || '').toLowerCase();
                const oldSingkat = sync.old_nama_singkat.toLowerCase();
                const oldNama = sync.old_nama.toLowerCase();
                const newSingkat = sync.new_nama_singkat.toLowerCase();

                if (rowNama === oldSingkat || rowNama === oldNama || rowNama === newSingkat ||
                    rowNama.includes(oldSingkat) || rowNama.includes(newSingkat)) {
                    
                    const rBadge = row.querySelector('[data-rekap-partai-badge]');
                    if (rBadge) rBadge.textContent = sync.new_nomor_urut;

                    const rNameText = row.querySelector('[data-rekap-partai-name-text]');
                    if (rNameText) rNameText.textContent = sync.new_nama_singkat;

                    row.dataset.rekapPartaiNama = sync.new_nama_singkat.toLowerCase();
                }
            });
        }

        // 4. Show success alert
        const alertContainer = document.getElementById('alert-container');
        if (alertContainer) {
            alertContainer.innerHTML = `
                <div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 px-4 py-3 text-xs mb-6 rounded-lg font-medium transition duration-300">
                    ✓ ${data.message}
                </div>
            `;
            alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        
    } catch (error) {
        alert('Gagal menyimpan perubahan: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-60');
    }
});

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

// Restore collapsible profile section state
const isCollapsed = localStorage.getItem('partai_profile_section_collapsed') === 'true';
if (isCollapsed) {
    const content = document.getElementById('partai-profile-section-content');
    const arrow = document.getElementById('partai-profile-section-arrow');
    if (content && arrow) {
        content.classList.add('hidden');
        arrow.textContent = '▲';
    }
}

document.querySelectorAll('.paslon-extra-rows').forEach((container) => {
    const addButton = container.nextElementSibling;
    if (addButton) {
        addPaslonFields(addButton);
        addPaslonFields(addButton);
    }
});

document.querySelectorAll('.calon-extra-rows').forEach((container) => {
    const addButton = container.nextElementSibling;
    if (addButton) {
        addCalonFields(addButton);
        addCalonFields(addButton);
    }
});

document.querySelectorAll('.partai-extra-rows').forEach((container) => {
    const addButton = container.nextElementSibling;
    if (addButton) {
        addPartaiFields(addButton);
        addPartaiFields(addButton);
    }
});

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

function calegRowHtml(caleg) {
    return `
        <div class="flex items-center justify-between px-8 py-3 border-t dark:border-gray-700 border-gray-100 group">
            <div class="flex items-center gap-3">
                <span class="text-xs dark:text-gray-500 text-gray-400 w-4">${escapeHtml(caleg.nomor_urut)}</span>
                <p class="text-sm dark:text-gray-200 text-gray-700">${escapeHtml(caleg.nama_caleg)}</p>
            </div>
            <form method="POST" action="${escapeHtml(caleg.destroy_url)}" data-ajax-delete="caleg" class="opacity-0 group-hover:opacity-100 transition">
                <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                <input type="hidden" name="_method" value="DELETE">
                <button class="px-2 py-1 rounded text-xs border border-red-400 text-red-400 hover:bg-red-500 hover:text-white transition">x</button>
            </form>
        </div>
    `;
}

function appendCalegRow(partaiId, caleg) {
    const panel = document.getElementById('partai-' + partaiId);
    const formWrapper = panel?.querySelector('form[data-ajax-caleg]')?.closest('.px-8');
    let appended = false;
    if (formWrapper) {
        formWrapper.insertAdjacentHTML('beforebegin', calegRowHtml(caleg));
        appended = true;
        panel.classList.remove('hidden');
        const arrow = document.getElementById('arrow-partai-' + partaiId);
        if (arrow) arrow.textContent = '\u25be';
    }

    updateCalegCount(partaiId, 1);

    return appended;
}

function updateCalegCount(partaiId, delta) {
    const counter = document.querySelector(`[data-caleg-count="${partaiId}"]`);
    if (counter) {
        const current = parseInt(counter.textContent, 10) || 0;
        counter.textContent = `${Math.max(0, current + delta)} caleg`;
    }
}

function confirmDeleteCaleg() {
    let dialog = document.querySelector('[data-delete-caleg-dialog]');
    if (!dialog) {
        dialog = document.createElement('div');
        dialog.dataset.deleteCalegDialog = '1';
        dialog.className = 'fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4';
        dialog.innerHTML = `
            <div class="w-full max-w-sm rounded-xl border dark:border-gray-700 border-gray-200 dark:bg-gray-900 bg-white p-5 shadow-xl">
                <p class="text-sm font-semibold dark:text-gray-100 text-gray-800 mb-2">Hapus caleg ini?</p>
                <p class="text-xs dark:text-gray-400 text-gray-500 mb-5">Data caleg akan dihapus dari daftar setup.</p>
                <div class="flex justify-end gap-2">
                    <button type="button" data-delete-cancel class="px-4 py-2 rounded-lg text-xs font-semibold border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition">Batal</button>
                    <button type="button" data-delete-confirm class="px-4 py-2 rounded-lg text-xs font-semibold bg-red-600 hover:bg-red-700 text-white transition">Hapus</button>
                </div>
            </div>
        `;
        document.body.appendChild(dialog);
    }

    return new Promise((resolve) => {
        const close = (value) => {
            dialog.classList.add('hidden');
            dialog.classList.remove('flex');
            dialog.querySelector('[data-delete-cancel]').onclick = null;
            dialog.querySelector('[data-delete-confirm]').onclick = null;
            resolve(value);
        };

        dialog.querySelector('[data-delete-cancel]').onclick = () => close(false);
        dialog.querySelector('[data-delete-confirm]').onclick = () => close(true);
        dialog.classList.remove('hidden');
        dialog.classList.add('flex');
        dialog.querySelector('[data-delete-cancel]').focus();
    });
}

document.querySelectorAll('form[data-ajax-caleg]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const button = form.querySelector('button[type="submit"], button:not([type])');
        button?.setAttribute('disabled', 'disabled');
        button?.classList.add('opacity-60');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: new FormData(form),
            });

            if (!response.ok) throw new Error('Request failed');

            const payload = await response.json();
            const partaiId = payload.partai_id || form.dataset.partaiId;
            const appended = partaiId && payload.caleg ? appendCalegRow(partaiId, payload.caleg) : false;
            form.reset();
            if (!appended) {
                let status = form.querySelector('[data-ajax-caleg-status]');
                if (!status) {
                    status = document.createElement('p');
                    status.dataset.ajaxCalegStatus = '1';
                    status.className = 'mt-3 text-xs font-semibold text-green-500';
                    form.appendChild(status);
                }
                status.textContent = payload.message || 'Caleg berhasil ditambahkan.';
            }
        } catch (error) {
            form.submit();
        } finally {
            button?.removeAttribute('disabled');
            button?.classList.remove('opacity-60');
        }
    });
});

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form[data-ajax-delete="caleg"]');
    if (!form) return;

    event.preventDefault();
    event.stopPropagation();

    const confirmed = await confirmDeleteCaleg();
    if (!confirmed) return;

    const button = form.querySelector('button[type="submit"], button:not([type])');
    button?.setAttribute('disabled', 'disabled');
    button?.classList.add('opacity-60');

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: new FormData(form),
        });

        if (!response.ok) throw new Error('Request failed');

        const panel = form.closest('[id^="partai-"]');
        const partaiId = panel?.id.replace('partai-', '');
        form.closest('.group')?.remove();
        if (partaiId) updateCalegCount(partaiId, -1);
    } catch (error) {
        form.submit();
    } finally {
        button?.removeAttribute('disabled');
        button?.classList.remove('opacity-60');
    }
});
</script>
@endpush
@endsection
