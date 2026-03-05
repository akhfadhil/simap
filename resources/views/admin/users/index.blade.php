@extends('layouts.app')
@section('title', 'Manajemen Pengguna')

@section('content')

<div class="mb-8">
    <a href="{{ route('dashboard.admin') }}"
       class="inline-flex items-center gap-2 text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition font-medium mb-4">
        ← Kembali ke Dashboard
    </a>
    <div class="flex items-start justify-between">
        <div>
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// Admin — Pengguna</p>
            <h1 class="font-display text-4xl tracking-[2px] text-red-600">MANAJEMEN PENGGUNA</h1>
            <p class="dark:text-gray-400 text-gray-500 text-sm mt-1">Kelola akun PPK, PPS, dan KPPS.</p>
        </div>
        <button onclick="openModal('tambah')"
                class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-5 py-2.5 rounded-lg transition mt-1">
            + Tambah User
        </button>
    </div>
</div>

@if(session('success'))
<div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 px-4 py-3 text-xs mb-6 rounded-lg font-medium">
    ✓ {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 text-xs mb-6 rounded-lg font-medium">
    ⚠ {{ session('error') }}
</div>
@endif

{{-- Filter --}}
<div class="flex gap-3 mb-6 flex-wrap">
    <form method="GET" id="filter-form" class="flex gap-3 flex-wrap items-center">
        {{-- Role --}}
        <select name="role" onchange="this.form.submit()"
                class="dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-4 py-2.5 text-xs rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
            <option value="">Semua Role</option>
            <option value="ppk"  {{ request('role') == 'ppk'  ? 'selected' : '' }}>PPK</option>
            <option value="pps"  {{ request('role') == 'pps'  ? 'selected' : '' }}>PPS</option>
            <option value="kpps" {{ request('role') == 'kpps' ? 'selected' : '' }}>KPPS</option>
        </select>

        {{-- Kecamatan --}}
        <select name="kecamatan_id" onchange="filterKecChange(this.value)"
                class="dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-4 py-2.5 text-xs rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
            <option value="">Semua Kecamatan</option>
            @foreach($kecamatans as $kec)
            <option value="{{ $kec->id }}" {{ request('kecamatan_id') == $kec->id ? 'selected' : '' }}>
                {{ $kec->nama }}
            </option>
            @endforeach
        </select>

        {{-- Desa (muncul jika kecamatan dipilih) --}}
        <select name="desa_id" id="filter-desa" onchange="this.form.submit()"
                class="dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-4 py-2.5 text-xs rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none {{ !request('kecamatan_id') ? 'hidden' : '' }}">
            <option value="">Semua Desa</option>
            @foreach($desas->where('kecamatan_id', request('kecamatan_id')) as $desa)
            <option value="{{ $desa->id }}" {{ request('desa_id') == $desa->id ? 'selected' : '' }}>
                {{ $desa->nama }}
            </option>
            @endforeach
        </select>

        {{-- Reset --}}
        @if(request('role') || request('kecamatan_id') || request('desa_id'))
        <a href="{{ route('admin.users.index') }}"
           class="text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition">× Reset</a>
        @endif

        <span class="text-[10px] dark:text-gray-500 text-gray-400 font-semibold uppercase tracking-wider">
            {{ $users->total() }} User
        </span>
    </form>
</div>

{{-- Tabel --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden">
    <div class="grid grid-cols-12 px-6 py-3 border-b dark:border-gray-700 border-gray-200">
        <div class="col-span-3 text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Nama</div>
        <div class="col-span-2 text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Username</div>
        <div class="col-span-1 text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Role</div>
        <div class="col-span-4 text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase font-semibold">Wilayah</div>
        <div class="col-span-2 text-[10px] tracking-[2px] dark:text-gray-500 text-gray-400 uppercase font-semibold text-right">Aksi</div>
    </div>

    @forelse($users as $user)
    @php
        $roleColor = match($user->role) {
            'ppk'  => '#F4A261',
            'pps'  => '#2EC4B6',
            'kpps' => '#A8DADC',
            default => '#666'
        };
        $wilayah = match($user->role) {
            'ppk'  => $user->kecamatan->nama ?? '-',
            'pps'  => ($user->desa->nama ?? '-') . ' / ' . ($user->desa->kecamatan->nama ?? '-'),
            'kpps' => ($user->tps->nama ?? '-') . ' / ' . ($user->tps->desa->nama ?? '-'),
            default => '-'
        };
    @endphp
    <div class="grid grid-cols-12 px-6 py-4 border-b dark:border-gray-700 border-gray-100 last:border-0 dark:hover:bg-gray-750 hover:bg-gray-50 transition group items-center">
        <div class="col-span-3">
            <p class="text-sm font-medium dark:text-gray-100 text-gray-800">{{ $user->name }}</p>
        </div>
        <div class="col-span-2">
            <p class="text-xs dark:text-gray-400 text-gray-500">{{ $user->username }}</p>
        </div>
        <div class="col-span-1">
            <span class="text-[9px] tracking-widest uppercase px-2 py-1 rounded font-semibold"
                  style="color:{{ $roleColor }};background:{{ $roleColor }}20;border:1px solid {{ $roleColor }}40">
                {{ strtoupper($user->role) }}
            </span>
        </div>
        <div class="col-span-4">
            <p class="text-xs dark:text-gray-500 text-gray-400">{{ $wilayah }}</p>
        </div>
        <div class="col-span-2 flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition">
            <button onclick="openEdit({{ json_encode($user) }})"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium border dark:border-gray-600 border-gray-300 dark:text-gray-400 text-gray-500 dark:hover:bg-gray-700 hover:bg-gray-100 transition">
                Edit
            </button>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                  onsubmit="return confirm('Hapus user {{ $user->username }}?')">
                @csrf @method('DELETE')
                <button class="px-3 py-1.5 rounded-lg text-xs font-medium border border-red-400 text-red-400 hover:bg-red-500 hover:text-white transition">
                    Hapus
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="px-6 py-16 text-center dark:text-gray-600 text-gray-400 text-sm">
        Belum ada user.
    </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($users->hasPages())
<div class="flex items-center justify-between mt-4 flex-wrap gap-3">
    <p class="text-xs dark:text-gray-500 text-gray-400">
        Menampilkan <span class="font-semibold dark:text-gray-300 text-gray-600">{{ $users->firstItem() }}–{{ $users->lastItem() }}</span>
        dari <span class="font-semibold dark:text-gray-300 text-gray-600">{{ $users->total() }}</span> user
    </p>
    <div class="flex items-center gap-1">
        {{-- Prev --}}
        @if($users->onFirstPage())
            <span class="px-3 py-1.5 text-xs rounded-lg dark:text-gray-600 text-gray-300 cursor-not-allowed">← Prev</span>
        @else
            <a href="{{ $users->previousPageUrl() }}"
               class="px-3 py-1.5 text-xs rounded-lg border dark:border-gray-700 border-gray-300 dark:text-gray-400 text-gray-500 dark:hover:bg-gray-700 hover:bg-gray-100 transition">
                ← Prev
            </a>
        @endif

        {{-- Page numbers --}}
        @php
            $current  = $users->currentPage();
            $last     = $users->lastPage();
            $start    = max(1, $current - 2);
            $end      = min($last, $current + 2);
        @endphp

        @if($start > 1)
            <a href="{{ $users->url(1) }}"
               class="px-3 py-1.5 text-xs rounded-lg border dark:border-gray-700 border-gray-300 dark:text-gray-400 text-gray-500 dark:hover:bg-gray-700 hover:bg-gray-100 transition">1</a>
            @if($start > 2)
                <span class="px-2 text-xs dark:text-gray-600 text-gray-400">…</span>
            @endif
        @endif

        @for($page = $start; $page <= $end; $page++)
            @if($page == $current)
                <span class="px-3 py-1.5 text-xs rounded-lg bg-red-600 text-white font-semibold">{{ $page }}</span>
            @else
                <a href="{{ $users->url($page) }}"
                   class="px-3 py-1.5 text-xs rounded-lg border dark:border-gray-700 border-gray-300 dark:text-gray-400 text-gray-500 dark:hover:bg-gray-700 hover:bg-gray-100 transition">
                    {{ $page }}
                </a>
            @endif
        @endfor

        @if($end < $last)
            @if($end < $last - 1)
                <span class="px-2 text-xs dark:text-gray-600 text-gray-400">…</span>
            @endif
            <a href="{{ $users->url($last) }}"
               class="px-3 py-1.5 text-xs rounded-lg border dark:border-gray-700 border-gray-300 dark:text-gray-400 text-gray-500 dark:hover:bg-gray-700 hover:bg-gray-100 transition">{{ $last }}</a>
        @endif

        {{-- Next --}}
        @if($users->hasMorePages())
            <a href="{{ $users->nextPageUrl() }}"
               class="px-3 py-1.5 text-xs rounded-lg border dark:border-gray-700 border-gray-300 dark:text-gray-400 text-gray-500 dark:hover:bg-gray-700 hover:bg-gray-100 transition">
                Next →
            </a>
        @else
            <span class="px-3 py-1.5 text-xs rounded-lg dark:text-gray-600 text-gray-300 cursor-not-allowed">Next →</span>
        @endif
    </div>
</div>
@endif

@php
$inputClass = "w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-100 text-gray-800 px-4 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none";
$labelClass = "block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2";
@endphp

{{-- ══════════════ MODAL TAMBAH ══════════════ --}}
<div id="modal-tambah" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
    <div class="dark:bg-gray-800 bg-white rounded-2xl border dark:border-gray-700 border-gray-200 w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="flex items-center justify-between px-8 py-5 border-b dark:border-gray-700 border-gray-200">
            <div>
                <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Admin</p>
                <h2 class="font-display text-2xl tracking-wide text-red-600 mt-0.5">TAMBAH USER</h2>
            </div>
            <button onclick="closeModal('tambah')" class="dark:text-gray-500 text-gray-400 hover:text-red-500 transition text-xl">✕</button>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="px-8 py-6 space-y-5">
            @csrf
            <div>
                <label class="{{ $labelClass }}">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="cth: Operator PPK Andir" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">Username</label>
                <input type="text" name="username" value="{{ old('username') }}" placeholder="cth: ppk_andir" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">Password</label>
                <input type="password" name="password" placeholder="Min. 6 karakter" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">Role</label>
                <select name="role" id="tambah-role" onchange="updateWilayahField('tambah')" class="{{ $inputClass }}">
                    <option value="">— Pilih Role —</option>
                    <option value="ppk"  {{ old('role') == 'ppk'  ? 'selected' : '' }}>PPK — Panitia Pemilihan Kecamatan</option>
                    <option value="pps"  {{ old('role') == 'pps'  ? 'selected' : '' }}>PPS — Panitia Pemungutan Suara</option>
                    <option value="kpps" {{ old('role') == 'kpps' ? 'selected' : '' }}>KPPS — Kelompok Penyelenggara</option>
                </select>
            </div>

            <div id="tambah-wilayah" class="space-y-4 hidden">
                <div id="tambah-field-kecamatan" class="hidden">
                    <label class="{{ $labelClass }}">Kecamatan</label>
                    <select name="kecamatan_id" class="{{ $inputClass }}">
                        <option value="">— Pilih Kecamatan —</option>
                        @foreach($kecamatans as $kec)
                        <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="tambah-field-kecamatan-pps" class="hidden">
                    <label class="{{ $labelClass }}">Kecamatan</label>
                    <select id="tambah-kec-pps" onchange="loadDesa('tambah', this.value)" class="{{ $inputClass }}">
                        <option value="">— Pilih Kecamatan —</option>
                        @foreach($kecamatans as $kec)
                        <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="tambah-field-desa" class="hidden">
                    <label class="{{ $labelClass }}">Desa</label>
                    <select name="desa_id" id="tambah-desa-select" class="{{ $inputClass }}">
                        <option value="">— Pilih Desa —</option>
                    </select>
                </div>
                <div id="tambah-field-kecamatan-kpps" class="hidden">
                    <label class="{{ $labelClass }}">Kecamatan</label>
                    <select id="tambah-kec-kpps" onchange="loadDesa('tambah-kpps', this.value)" class="{{ $inputClass }}">
                        <option value="">— Pilih Kecamatan —</option>
                        @foreach($kecamatans as $kec)
                        <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="tambah-field-desa-kpps" class="hidden">
                    <label class="{{ $labelClass }}">Desa</label>
                    <select id="tambah-desa-kpps" onchange="loadTps('tambah', this.value)" class="{{ $inputClass }}">
                        <option value="">— Pilih Desa —</option>
                    </select>
                </div>
                <div id="tambah-field-tps" class="hidden">
                    <label class="{{ $labelClass }}">TPS</label>
                    <select name="tps_id" id="tambah-tps-select" class="{{ $inputClass }}">
                        <option value="">— Pilih TPS —</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('tambah')"
                        class="flex-1 border dark:border-gray-600 border-gray-300 dark:text-gray-400 text-gray-500 py-2.5 rounded-lg text-sm font-medium dark:hover:bg-gray-700 hover:bg-gray-100 transition">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                    Simpan →
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════ MODAL EDIT ══════════════ --}}
<div id="modal-edit" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
    <div class="dark:bg-gray-800 bg-white rounded-2xl border dark:border-gray-700 border-gray-200 w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="flex items-center justify-between px-8 py-5 border-b dark:border-gray-700 border-gray-200">
            <div>
                <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Admin</p>
                <h2 class="font-display text-2xl tracking-wide text-red-600 mt-0.5">EDIT USER</h2>
            </div>
            <button onclick="closeModal('edit')" class="dark:text-gray-500 text-gray-400 hover:text-red-500 transition text-xl">✕</button>
        </div>

        <form method="POST" id="edit-form" class="px-8 py-6 space-y-5">
            @csrf @method('PUT')
            <div>
                <label class="{{ $labelClass }}">Nama Lengkap</label>
                <input type="text" name="name" id="edit-name" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">Username</label>
                <input type="text" name="username" id="edit-username" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">Password <span class="dark:text-gray-600 text-gray-400 normal-case tracking-normal font-normal">(kosongkan jika tidak diganti)</span></label>
                <input type="password" name="password" placeholder="••••••••" class="{{ $inputClass }}">
            </div>
            <div>
                <label class="{{ $labelClass }}">Role</label>
                <input type="text" id="edit-role-display"
                       class="w-full dark:bg-gray-900 bg-gray-100 border dark:border-gray-700 border-gray-200 dark:text-gray-500 text-gray-400 px-4 py-2.5 text-sm rounded-lg cursor-not-allowed" readonly>
            </div>

            <div id="edit-wilayah-ppk" class="hidden">
                <label class="{{ $labelClass }}">Kecamatan</label>
                <select name="kecamatan_id" id="edit-kecamatan" class="{{ $inputClass }}">
                    @foreach($kecamatans as $kec)
                    <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div id="edit-wilayah-pps" class="hidden space-y-4">
                <div>
                    <label class="{{ $labelClass }}">Kecamatan</label>
                    <select id="edit-kec-pps" onchange="loadDesaEdit(this.value)" class="{{ $inputClass }}">
                        @foreach($kecamatans as $kec)
                        <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Desa</label>
                    <select name="desa_id" id="edit-desa-select" class="{{ $inputClass }}"></select>
                </div>
            </div>

            <div id="edit-wilayah-kpps" class="hidden space-y-4">
                <div>
                    <label class="{{ $labelClass }}">Kecamatan</label>
                    <select id="edit-kec-kpps" onchange="loadDesaEditKpps(this.value)" class="{{ $inputClass }}">
                        @foreach($kecamatans as $kec)
                        <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Desa</label>
                    <select id="edit-desa-kpps" onchange="loadTpsEdit(this.value)" class="{{ $inputClass }}"></select>
                </div>
                <div>
                    <label class="{{ $labelClass }}">TPS</label>
                    <select name="tps_id" id="edit-tps-select" class="{{ $inputClass }}"></select>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('edit')"
                        class="flex-1 border dark:border-gray-600 border-gray-300 dark:text-gray-400 text-gray-500 py-2.5 rounded-lg text-sm font-medium dark:hover:bg-gray-700 hover:bg-gray-100 transition">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                    Simpan →
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const allDesas = @json($desas->map(fn($d) => ['id'=>$d->id,'nama'=>$d->nama,'kecamatan_id'=>$d->kecamatan_id]));
    const allTps   = @json($tpsList->map(fn($t) => ['id'=>$t->id,'nama'=>$t->nama,'desa_id'=>$t->desa_id]));

    function openModal(type) {
        document.getElementById('modal-' + type).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(type) {
        document.getElementById('modal-' + type).classList.add('hidden');
        document.body.style.overflow = '';
    }
    ['tambah','edit'].forEach(type => {
        document.getElementById('modal-' + type).addEventListener('click', function(e) {
            if (e.target === this) closeModal(type);
        });
    });

    function updateWilayahField(prefix) {
        const role = document.getElementById(prefix + '-role').value;
        const wrap = document.getElementById(prefix + '-wilayah');
        ['kecamatan','kecamatan-pps','field-desa','kecamatan-kpps','field-desa-kpps','field-tps'].forEach(f => {
            const el = document.getElementById(prefix + '-field-' + f);
            if (el) el.classList.add('hidden');
        });
        if (!role) { wrap.classList.add('hidden'); return; }
        wrap.classList.remove('hidden');
        if (role === 'ppk')       document.getElementById(prefix + '-field-kecamatan').classList.remove('hidden');
        else if (role === 'pps')  document.getElementById(prefix + '-field-kecamatan-pps').classList.remove('hidden');
        else if (role === 'kpps') document.getElementById(prefix + '-field-kecamatan-kpps').classList.remove('hidden');
    }

    function loadDesa(prefix, kecId) {
        const desas   = allDesas.filter(d => d.kecamatan_id == kecId);
        let selId, fieldId;
        if (prefix === 'tambah') { selId = 'tambah-desa-select'; fieldId = 'tambah-field-desa'; }
        else                     { selId = 'tambah-desa-kpps';   fieldId = 'tambah-field-desa-kpps'; }
        const sel = document.getElementById(selId);
        sel.innerHTML = '<option value="">— Pilih Desa —</option>';
        desas.forEach(d => sel.innerHTML += `<option value="${d.id}">${d.nama}</option>`);
        document.getElementById(fieldId).classList.toggle('hidden', desas.length === 0);
    }

    function loadTps(prefix, desaId) {
        const list = allTps.filter(t => t.desa_id == desaId);
        const sel  = document.getElementById(prefix + '-tps-select');
        sel.innerHTML = '<option value="">— Pilih TPS —</option>';
        list.forEach(t => sel.innerHTML += `<option value="${t.id}">${t.nama}</option>`);
        document.getElementById(prefix + '-field-tps').classList.toggle('hidden', list.length === 0);
    }

    function openEdit(user) {
        document.getElementById('edit-name').value         = user.name;
        document.getElementById('edit-username').value     = user.username;
        document.getElementById('edit-role-display').value = user.role.toUpperCase();
        document.getElementById('edit-form').action        = `/admin/users/${user.id}`;
        ['ppk','pps','kpps'].forEach(r => document.getElementById('edit-wilayah-' + r).classList.add('hidden'));
        if (user.role === 'ppk') {
            document.getElementById('edit-wilayah-ppk').classList.remove('hidden');
            if (user.kecamatan_id) document.getElementById('edit-kecamatan').value = user.kecamatan_id;
        } else if (user.role === 'pps') {
            document.getElementById('edit-wilayah-pps').classList.remove('hidden');
            if (user.desa_id) {
                const desa = allDesas.find(d => d.id == user.desa_id);
                if (desa) { document.getElementById('edit-kec-pps').value = desa.kecamatan_id; loadDesaEdit(desa.kecamatan_id, user.desa_id); }
            }
        } else if (user.role === 'kpps') {
            document.getElementById('edit-wilayah-kpps').classList.remove('hidden');
            if (user.tps_id) {
                const tps  = allTps.find(t => t.id == user.tps_id);
                const desa = tps ? allDesas.find(d => d.id == tps.desa_id) : null;
                if (desa) { document.getElementById('edit-kec-kpps').value = desa.kecamatan_id; loadDesaEditKpps(desa.kecamatan_id, tps.desa_id, user.tps_id); }
            }
        }
        openModal('edit');
    }

    function loadDesaEdit(kecId, selectedDesaId = null) {
        const desas = allDesas.filter(d => d.kecamatan_id == kecId);
        const sel   = document.getElementById('edit-desa-select');
        sel.innerHTML = '<option value="">— Pilih Desa —</option>';
        desas.forEach(d => sel.innerHTML += `<option value="${d.id}" ${d.id == selectedDesaId ? 'selected' : ''}>${d.nama}</option>`);
    }

    function loadDesaEditKpps(kecId, selectedDesaId = null, selectedTpsId = null) {
        const desas = allDesas.filter(d => d.kecamatan_id == kecId);
        const sel   = document.getElementById('edit-desa-kpps');
        sel.innerHTML = '<option value="">— Pilih Desa —</option>';
        desas.forEach(d => sel.innerHTML += `<option value="${d.id}" ${d.id == selectedDesaId ? 'selected' : ''}>${d.nama}</option>`);
        if (selectedDesaId) loadTpsEdit(selectedDesaId, selectedTpsId);
    }

    function loadTpsEdit(desaId, selectedTpsId = null) {
        const list = allTps.filter(t => t.desa_id == desaId);
        const sel  = document.getElementById('edit-tps-select');
        sel.innerHTML = '<option value="">— Pilih TPS —</option>';
        list.forEach(t => sel.innerHTML += `<option value="${t.id}" ${t.id == selectedTpsId ? 'selected' : ''}>${t.nama}</option>`);
    }
</script>
@endpush

@endsection