@extends('layouts.app')
@section('title', 'Manajemen Pengguna')

@section('content')

<div class="mb-8">
    <a href="{{ route('dashboard.admin') }}"
       class="inline-flex items-center gap-2 font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase hover:text-brand transition mb-4">
        ← KEMBALI KE DASHBOARD
    </a>
    <div class="flex items-start justify-between">
        <div>
            <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-2">// Admin — Pengguna</p>
            <h1 class="font-display text-4xl tracking-[2px] text-brand">MANAJEMEN PENGGUNA</h1>
            <p class="text-gray-500 text-sm mt-1">Kelola akun PPK, PPS, dan KPPS.</p>
        </div>
        <button onclick="openModal('tambah')"
                class="flex items-center gap-2 bg-brand text-white font-mono2 text-[10px] tracking-[2px] uppercase px-5 py-3 hover:opacity-90 transition mt-1">
            + TAMBAH USER
        </button>
    </div>
</div>

@if(session('success'))
<div class="bg-green-950 border border-green-800 text-green-400 px-4 py-3 font-mono2 text-xs mb-6">
    ✓ {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="bg-red-950 border border-red-800 text-red-400 px-4 py-3 font-mono2 text-xs mb-6">
    ⚠ {{ session('error') }}
</div>
@endif

{{-- Filter --}}
<div class="flex gap-3 mb-6 flex-wrap">
    <form method="GET" class="flex gap-3 flex-wrap">
        <select name="role" onchange="this.form.submit()"
                class="bg-[#141414] border border-gray-800 text-gray-400 px-4 py-2.5 text-xs font-mono2 focus:border-brand focus:ring-0 focus:outline-none">
            <option value="">Semua Role</option>
            <option value="ppk"  {{ request('role') == 'ppk'  ? 'selected' : '' }}>PPK</option>
            <option value="pps"  {{ request('role') == 'pps'  ? 'selected' : '' }}>PPS</option>
            <option value="kpps" {{ request('role') == 'kpps' ? 'selected' : '' }}>KPPS</option>
        </select>
        <span class="font-mono2 text-[10px] text-gray-600 self-center">{{ $users->count() }} USER</span>
    </form>
</div>

{{-- Tabel --}}
<div class="bg-[#141414] border border-gray-800">
    {{-- Header --}}
    <div class="grid grid-cols-12 px-6 py-3 border-b border-gray-800">
        <div class="col-span-3 font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase">Nama</div>
        <div class="col-span-2 font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase">Username</div>
        <div class="col-span-1 font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase">Role</div>
        <div class="col-span-4 font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase">Wilayah</div>
        <div class="col-span-2 font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase text-right">Aksi</div>
    </div>

    @forelse($users->filter(fn($u) => !request('role') || $u->role === request('role')) as $user)
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
    <div class="grid grid-cols-12 px-6 py-4 border-b border-gray-900 hover:bg-[#1a1a1a] group items-center">
        <div class="col-span-3">
            <p class="text-sm font-medium">{{ $user->name }}</p>
        </div>
        <div class="col-span-2">
            <p class="font-mono2 text-xs text-gray-400">{{ $user->username }}</p>
        </div>
        <div class="col-span-1">
            <span class="font-mono2 text-[9px] tracking-widest uppercase px-2 py-1"
                  style="color:{{ $roleColor }};background:{{ $roleColor }}18;border:1px solid {{ $roleColor }}44">
                {{ strtoupper($user->role) }}
            </span>
        </div>
        <div class="col-span-4">
            <p class="text-xs text-gray-500">{{ $wilayah }}</p>
        </div>
        <div class="col-span-2 flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition">
            <button onclick="openEdit({{ json_encode($user) }})"
                    class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-brand hover:text-brand transition">
                EDIT
            </button>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                  onsubmit="return confirm('Hapus user {{ $user->username }}?')">
                @csrf @method('DELETE')
                <button class="border border-gray-700 text-gray-500 px-3 py-1 font-mono2 text-[10px] uppercase tracking-widest hover:border-red-700 hover:text-red-500 transition">
                    HAPUS
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="px-6 py-16 text-center text-gray-700 font-mono2 text-xs tracking-widest">
        BELUM ADA USER
    </div>
    @endforelse
</div>

{{-- ══════════════ MODAL TAMBAH ══════════════ --}}
<div id="modal-tambah" class="hidden fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4">
    <div class="bg-[#141414] border border-gray-800 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-8 py-5 border-b border-gray-800">
            <div>
                <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase">// Admin</p>
                <h2 class="font-display text-2xl tracking-wide text-brand mt-0.5">TAMBAH USER</h2>
            </div>
            <button onclick="closeModal('tambah')" class="text-gray-600 hover:text-gray-400 text-xl">✕</button>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="px-8 py-6 space-y-5">
            @csrf

            <div>
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="cth: Operator PPK Andir"
                       class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
            </div>

            <div>
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Username</label>
                <input type="text" name="username" value="{{ old('username') }}" placeholder="cth: ppk_andir"
                       class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
            </div>

            <div>
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Password</label>
                <input type="password" name="password" placeholder="Min. 6 karakter"
                       class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
            </div>

            <div>
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Role</label>
                <select name="role" id="tambah-role" onchange="updateWilayahField('tambah')"
                        class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                    <option value="">— Pilih Role —</option>
                    <option value="ppk"  {{ old('role') == 'ppk'  ? 'selected' : '' }}>PPK — Panitia Pemilihan Kecamatan</option>
                    <option value="pps"  {{ old('role') == 'pps'  ? 'selected' : '' }}>PPS — Panitia Pemungutan Suara</option>
                    <option value="kpps" {{ old('role') == 'kpps' ? 'selected' : '' }}>KPPS — Kelompok Penyelenggara</option>
                </select>
            </div>

            {{-- Wilayah dinamis --}}
            <div id="tambah-wilayah" class="space-y-4 hidden">

                {{-- PPK: pilih kecamatan --}}
                <div id="tambah-field-kecamatan" class="hidden">
                    <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Kecamatan</label>
                    <select name="kecamatan_id"
                            class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                        <option value="">— Pilih Kecamatan —</option>
                        @foreach($kecamatans as $kec)
                        <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- PPS: pilih kecamatan → desa --}}
                <div id="tambah-field-kecamatan-pps" class="hidden">
                    <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Kecamatan</label>
                    <select id="tambah-kec-pps" onchange="loadDesa('tambah', this.value)"
                            class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                        <option value="">— Pilih Kecamatan —</option>
                        @foreach($kecamatans as $kec)
                        <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="tambah-field-desa" class="hidden">
                    <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Desa</label>
                    <select name="desa_id" id="tambah-desa-select"
                            class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                        <option value="">— Pilih Desa —</option>
                    </select>
                </div>

                {{-- KPPS: pilih kecamatan → desa → TPS --}}
                <div id="tambah-field-kecamatan-kpps" class="hidden">
                    <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Kecamatan</label>
                    <select id="tambah-kec-kpps" onchange="loadDesa('tambah-kpps', this.value)"
                            class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                        <option value="">— Pilih Kecamatan —</option>
                        @foreach($kecamatans as $kec)
                        <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="tambah-field-desa-kpps" class="hidden">
                    <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Desa</label>
                    <select id="tambah-desa-kpps" onchange="loadTps('tambah', this.value)"
                            class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                        <option value="">— Pilih Desa —</option>
                    </select>
                </div>

                <div id="tambah-field-tps" class="hidden">
                    <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">TPS</label>
                    <select name="tps_id" id="tambah-tps-select"
                            class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                        <option value="">— Pilih TPS —</option>
                    </select>
                </div>

            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('tambah')"
                        class="flex-1 border border-gray-800 text-gray-500 font-display text-lg tracking-[2px] py-3 hover:border-gray-600 transition">
                    BATAL
                </button>
                <button type="submit"
                        class="flex-1 bg-brand text-white font-display text-lg tracking-[2px] py-3 hover:opacity-90 transition">
                    SIMPAN →
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════ MODAL EDIT ══════════════ --}}
<div id="modal-edit" class="hidden fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4">
    <div class="bg-[#141414] border border-gray-800 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-8 py-5 border-b border-gray-800">
            <div>
                <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase">// Admin</p>
                <h2 class="font-display text-2xl tracking-wide text-brand mt-0.5">EDIT USER</h2>
            </div>
            <button onclick="closeModal('edit')" class="text-gray-600 hover:text-gray-400 text-xl">✕</button>
        </div>

        <form method="POST" id="edit-form" class="px-8 py-6 space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Nama Lengkap</label>
                <input type="text" name="name" id="edit-name"
                       class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
            </div>

            <div>
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Username</label>
                <input type="text" name="username" id="edit-username"
                       class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
            </div>

            <div>
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">
                    Password <span class="text-gray-700 normal-case tracking-normal">(kosongkan jika tidak diganti)</span>
                </label>
                <input type="password" name="password" placeholder="••••••••"
                       class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
            </div>

            <div>
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Role</label>
                <input type="text" id="edit-role-display"
                       class="w-full bg-[#0a0a0a] border border-gray-900 text-gray-500 px-4 py-3 text-sm cursor-not-allowed" readonly>
            </div>

            {{-- Wilayah edit --}}
            <div id="edit-wilayah-ppk" class="hidden">
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Kecamatan</label>
                <select name="kecamatan_id" id="edit-kecamatan"
                        class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                    @foreach($kecamatans as $kec)
                    <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div id="edit-wilayah-pps" class="hidden space-y-4">
                <div>
                    <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Kecamatan</label>
                    <select id="edit-kec-pps" onchange="loadDesaEdit(this.value)"
                            class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                        @foreach($kecamatans as $kec)
                        <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Desa</label>
                    <select name="desa_id" id="edit-desa-select"
                            class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                    </select>
                </div>
            </div>

            <div id="edit-wilayah-kpps" class="hidden space-y-4">
                <div>
                    <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Kecamatan</label>
                    <select id="edit-kec-kpps" onchange="loadDesaEditKpps(this.value)"
                            class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                        @foreach($kecamatans as $kec)
                        <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Desa</label>
                    <select id="edit-desa-kpps" onchange="loadTpsEdit(this.value)"
                            class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                    </select>
                </div>
                <div>
                    <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">TPS</label>
                    <select name="tps_id" id="edit-tps-select"
                            class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3 text-sm focus:border-brand focus:ring-0 focus:outline-none">
                    </select>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('edit')"
                        class="flex-1 border border-gray-800 text-gray-500 font-display text-lg tracking-[2px] py-3 hover:border-gray-600 transition">
                    BATAL
                </button>
                <button type="submit"
                        class="flex-1 bg-brand text-white font-display text-lg tracking-[2px] py-3 hover:opacity-90 transition">
                    SIMPAN →
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Data wilayah dari server
const allDesas = @json($desas->map(fn($d) => ['id'=>$d->id,'nama'=>$d->nama,'kecamatan_id'=>$d->kecamatan_id]));
const allTps   = @json($tpsList->map(fn($t) => ['id'=>$t->id,'nama'=>$t->nama,'desa_id'=>$t->desa_id]));

// ── Modal ──────────────────────────────────────────────
function openModal(type) {
    document.getElementById('modal-' + type).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeModal(type) {
    document.getElementById('modal-' + type).classList.add('hidden');
    document.body.style.overflow = '';
}

// Tutup modal kalau klik backdrop
['tambah','edit'].forEach(type => {
    document.getElementById('modal-' + type).addEventListener('click', function(e) {
        if (e.target === this) closeModal(type);
    });
});

// ── Tambah: update field wilayah saat role berubah ──────
function updateWilayahField(prefix) {
    const role = document.getElementById(prefix + '-role').value;
    const wrap = document.getElementById(prefix + '-wilayah');

    // Sembunyikan semua field dulu
    ['kecamatan','kecamatan-pps','field-desa','kecamatan-kpps','field-desa-kpps','field-tps'].forEach(f => {
        const el = document.getElementById(prefix + '-field-' + f);
        if (el) el.classList.add('hidden');
    });

    if (!role) { wrap.classList.add('hidden'); return; }
    wrap.classList.remove('hidden');

    if (role === 'ppk') {
        document.getElementById(prefix + '-field-kecamatan').classList.remove('hidden');
    } else if (role === 'pps') {
        document.getElementById(prefix + '-field-kecamatan-pps').classList.remove('hidden');
    } else if (role === 'kpps') {
        document.getElementById(prefix + '-field-kecamatan-kpps').classList.remove('hidden');
    }
}

// ── Load Desa by Kecamatan ──────────────────────────────
function loadDesa(prefix, kecId) {
    const desas = allDesas.filter(d => d.kecamatan_id == kecId);
    let selId, fieldId;

    if (prefix === 'tambah') {
        selId   = 'tambah-desa-select';
        fieldId = 'tambah-field-desa';
    } else {
        selId   = 'tambah-desa-kpps';
        fieldId = 'tambah-field-desa-kpps';
    }

    const sel = document.getElementById(selId);
    sel.innerHTML = '<option value="">— Pilih Desa —</option>';
    desas.forEach(d => sel.innerHTML += `<option value="${d.id}">${d.nama}</option>`);
    document.getElementById(fieldId).classList.toggle('hidden', desas.length === 0);
}

// ── Load TPS by Desa ────────────────────────────────────
function loadTps(prefix, desaId) {
    const tpsList = allTps.filter(t => t.desa_id == desaId);
    const sel = document.getElementById(prefix + '-tps-select');
    sel.innerHTML = '<option value="">— Pilih TPS —</option>';
    tpsList.forEach(t => sel.innerHTML += `<option value="${t.id}">${t.nama}</option>`);
    document.getElementById(prefix + '-field-tps').classList.toggle('hidden', tpsList.length === 0);
}

// ── Edit Modal ──────────────────────────────────────────
function openEdit(user) {
    document.getElementById('edit-name').value     = user.name;
    document.getElementById('edit-username').value = user.username;
    document.getElementById('edit-role-display').value = user.role.toUpperCase();
    document.getElementById('edit-form').action    = `/admin/users/${user.id}`;

    // Sembunyikan semua wilayah dulu
    ['ppk','pps','kpps'].forEach(r => {
        document.getElementById('edit-wilayah-' + r).classList.add('hidden');
    });

    if (user.role === 'ppk') {
        document.getElementById('edit-wilayah-ppk').classList.remove('hidden');
        if (user.kecamatan_id) document.getElementById('edit-kecamatan').value = user.kecamatan_id;

    } else if (user.role === 'pps') {
        document.getElementById('edit-wilayah-pps').classList.remove('hidden');
        if (user.desa_id) {
            // Set kecamatan dulu
            const desa = allDesas.find(d => d.id == user.desa_id);
            if (desa) {
                document.getElementById('edit-kec-pps').value = desa.kecamatan_id;
                loadDesaEdit(desa.kecamatan_id, user.desa_id);
            }
        }

    } else if (user.role === 'kpps') {
        document.getElementById('edit-wilayah-kpps').classList.remove('hidden');
        if (user.tps_id) {
            const tps  = allTps.find(t => t.id == user.tps_id);
            const desa = tps ? allDesas.find(d => d.id == tps.desa_id) : null;
            if (desa) {
                document.getElementById('edit-kec-kpps').value = desa.kecamatan_id;
                loadDesaEditKpps(desa.kecamatan_id, tps.desa_id, user.tps_id);
            }
        }
    }

    openModal('edit');
}

function loadDesaEdit(kecId, selectedDesaId = null) {
    const desas = allDesas.filter(d => d.kecamatan_id == kecId);
    const sel = document.getElementById('edit-desa-select');
    sel.innerHTML = '<option value="">— Pilih Desa —</option>';
    desas.forEach(d => {
        sel.innerHTML += `<option value="${d.id}" ${d.id == selectedDesaId ? 'selected' : ''}>${d.nama}</option>`;
    });
}

function loadDesaEditKpps(kecId, selectedDesaId = null, selectedTpsId = null) {
    const desas = allDesas.filter(d => d.kecamatan_id == kecId);
    const sel = document.getElementById('edit-desa-kpps');
    sel.innerHTML = '<option value="">— Pilih Desa —</option>';
    desas.forEach(d => {
        sel.innerHTML += `<option value="${d.id}" ${d.id == selectedDesaId ? 'selected' : ''}>${d.nama}</option>`;
    });
    if (selectedDesaId) loadTpsEdit(selectedDesaId, selectedTpsId);
}

function loadTpsEdit(desaId, selectedTpsId = null) {
    const list = allTps.filter(t => t.desa_id == desaId);
    const sel = document.getElementById('edit-tps-select');
    sel.innerHTML = '<option value="">— Pilih TPS —</option>';
    list.forEach(t => {
        sel.innerHTML += `<option value="${t.id}" ${t.id == selectedTpsId ? 'selected' : ''}>${t.nama}</option>`;
    });
}
</script>
@endpush

@endsection