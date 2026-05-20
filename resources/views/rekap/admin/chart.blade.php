<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIMAP - Grafik & Statistik</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="icon" type="image/png" href="{{ asset('images/logo-kpu.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">

    <style>
        :root {
            --surface: #f8f9fa;
            --surface-low: #eef1f4;
            --surface-card: #ffffff;
            --surface-soft: #f3f5f7;
            --ink: #17202a;
            --muted: #657181;
            --line: #d9dee5;
            --primary: #001f45;
            --primary-2: #2d476f;
            --red: #c81924;
            --red-soft: #ffe1df;
            --blue-soft: #dbe8ff;
            --map-dot: rgba(0, 31, 69, 0.08);
        }

        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            overflow: hidden;
            background: var(--surface);
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .font-mono-data {
            font-family: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .material-symbols-outlined {
            font-variation-settings: "FILL" 0, "wght" 500, "GRAD" 0, "opsz" 24;
            line-height: 1;
        }

        .map-grid {
            background-color: var(--surface-low);
            background-image: radial-gradient(circle at 2px 2px, var(--map-dot) 1px, transparent 0);
            background-size: 24px 24px;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .leaflet-tooltip-kec {
            background: rgba(0, 31, 69, 0.94);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 8px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.24);
            font-size: 12px;
            font-weight: 700;
            padding: 5px 10px;
        }

        .leaflet-container {
            background: transparent !important;
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
        }

        .leaflet-control-zoom {
            border: 1px solid var(--line) !important;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.16) !important;
        }

        .leaflet-control-zoom a {
            color: var(--primary) !important;
        }

        select, input, button {
            font-family: inherit;
        }
    </style>
</head>
<body>
@php
    $aktifJenis = \App\Models\PemiluSetting::aktif();
    $defaultJenis = collect(\App\Models\RekapHeader::JENIS_LABELS)
        ->keys()
        ->first(fn ($key) => in_array($key, $aktifJenis));
@endphp

<header class="fixed top-0 left-0 right-0 z-50 h-16 bg-white border-b border-slate-200 shadow-sm">
    <div class="h-full px-6 flex items-center justify-between gap-6">
        <div class="flex items-center gap-4 min-w-0">
            <a href="{{ route('dashboard.admin') }}" class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                <img src="{{ asset('images/logo-kpu.png') }}" alt="KPU" class="w-8 h-8 object-contain">
            </a>
            <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-[0.22em] text-slate-500 font-semibold">SIMAP Analytics</p>
                <h1 class="text-lg font-extrabold text-[var(--primary)] truncate">Grafik & Statistik Pemilu Banyuwangi</h1>
            </div>
        </div>

        <div class="hidden lg:flex items-center gap-6">
            <div class="text-right">
                <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-semibold">Wilayah</p>
                <p class="text-sm font-bold text-slate-800">Kabupaten Banyuwangi</p>
            </div>
            <div class="h-8 w-px bg-slate-200"></div>
            <div class="text-right">
                <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-semibold">Operator</p>
                <p class="text-sm font-bold text-slate-800">{{ Auth::user()->name }}</p>
            </div>
            <a href="{{ route('dashboard.admin') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Dashboard
            </a>
        </div>
    </div>
</header>

<main class="pt-16 h-screen flex">
    <aside class="w-[330px] bg-[var(--primary)] text-white h-full overflow-y-auto flex flex-col p-6 shadow-xl z-40">
        <div class="mb-7">
            <p class="text-[10px] uppercase tracking-[0.24em] text-white/55 font-semibold mb-2">Filter Utama</p>
            <label class="block text-xs text-white/70 mb-2 font-semibold">Jenis Pemilihan</label>
            <div class="relative">
                <select id="f-jenis" onchange="onJenisChange()" class="w-full appearance-none rounded-lg border border-white/10 bg-white/12 px-4 py-3 pr-10 text-sm font-bold text-white outline-none focus:border-red-300 focus:ring-2 focus:ring-red-300/30">
                    <option value="">Pilih Jenis</option>
                    @foreach(\App\Models\RekapHeader::JENIS_LABELS as $key => $label)
                        @if(in_array($key, $aktifJenis))
                            <option value="{{ $key }}" @selected($key === $defaultJenis)>{{ $label }}</option>
                        @endif
                    @endforeach
                </select>
                <span class="material-symbols-outlined pointer-events-none absolute right-3 top-3.5 text-white/70">expand_more</span>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-xs text-white/70 mb-2 font-semibold">Level Tampilan</label>
                <div class="relative">
                    <select id="f-level" onchange="onLevelChange()" class="w-full appearance-none rounded-lg border border-white/10 bg-white/12 px-4 py-2.5 pr-10 text-sm font-semibold text-white outline-none focus:border-red-300 focus:ring-2 focus:ring-red-300/30">
                        <option value="kabupaten">Kabupaten</option>
                        <option value="dapil" class="hidden">Dapil</option>
                        <option value="kecamatan">Kecamatan</option>
                        <option value="desa">Desa</option>
                        <option value="tps">TPS</option>
                    </select>
                    <span class="material-symbols-outlined pointer-events-none absolute right-3 top-2.5 text-white/70">expand_more</span>
                </div>
            </div>

            <div id="wrap-dapil" class="hidden">
                <label class="block text-xs text-white/70 mb-2 font-semibold">Dapil</label>
                <select id="f-dapil" onchange="onDapilChange()" class="w-full rounded-lg border border-white/10 bg-white/12 px-4 py-2.5 text-sm font-semibold text-white outline-none focus:border-red-300">
                    <option value="">Pilih Dapil</option>
                    @foreach($dapils as $dapil)
                        <option value="{{ $dapil->id }}">{{ $dapil->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div id="wrap-kec" class="hidden">
                <label class="block text-xs text-white/70 mb-2 font-semibold">Kecamatan</label>
                <select id="f-kec" onchange="onKecChange()" class="w-full rounded-lg border border-white/10 bg-white/12 px-4 py-2.5 text-sm font-semibold text-white outline-none focus:border-red-300">
                    <option value="">Pilih Kecamatan</option>
                    @foreach($kecamatans as $kec)
                        <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div id="wrap-desa" class="hidden">
                <label class="block text-xs text-white/70 mb-2 font-semibold">Desa</label>
                <select id="f-desa" onchange="onDesaChange()" class="w-full rounded-lg border border-white/10 bg-white/12 px-4 py-2.5 text-sm font-semibold text-white outline-none focus:border-red-300">
                    <option value="">Pilih Desa</option>
                </select>
            </div>

            <div id="wrap-tps" class="hidden">
                <label class="block text-xs text-white/70 mb-2 font-semibold">TPS</label>
                <select id="f-tps" onchange="loadChart()" class="w-full rounded-lg border border-white/10 bg-white/12 px-4 py-2.5 text-sm font-semibold text-white outline-none focus:border-red-300">
                    <option value="">Pilih TPS</option>
                </select>
            </div>

            <button id="wrap-reset-kec" onclick="resetKecFilter()" class="hidden w-full rounded-lg border border-white/15 bg-white/8 px-4 py-2.5 text-xs font-bold text-white/80 hover:bg-white/12">
                Reset filter kecamatan
            </button>
        </div>

        <div class="my-7 h-px bg-white/12"></div>

        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-xl bg-[var(--red)] p-4">
                <p class="text-[10px] uppercase tracking-[0.16em] text-white/75 font-semibold">Total Suara</p>
                <p id="stat-total-suara" class="font-mono-data text-2xl font-bold mt-2">0</p>
            </div>
            <div class="rounded-xl bg-white/12 p-4 border border-white/10">
                <p class="text-[10px] uppercase tracking-[0.16em] text-white/65 font-semibold">Wilayah</p>
                <p id="stat-total-wilayah" class="font-mono-data text-2xl font-bold mt-2">0</p>
            </div>
            <div class="col-span-2 rounded-xl bg-white/12 p-4 border border-white/10 flex items-end justify-between gap-4">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.16em] text-white/65 font-semibold">Partisipasi</p>
                    <p id="stat-partisipasi" class="font-mono-data text-2xl font-bold mt-2 text-red-100">0%</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] uppercase tracking-[0.16em] text-white/65 font-semibold">DPT</p>
                    <p id="stat-dpt" class="font-mono-data text-lg font-bold mt-2">0</p>
                </div>
            </div>
        </div>

        <div class="mt-auto pt-6">
            <div class="rounded-xl border border-white/10 bg-white/8 p-4">
                <p class="text-[10px] uppercase tracking-[0.18em] text-white/55 font-semibold mb-2">Status Peta</p>
                <p id="map-selected-label" class="text-sm font-semibold text-white">Klik kecamatan untuk filter</p>
            </div>
        </div>
    </aside>

    <section class="flex-1 relative map-grid overflow-hidden">
        <div id="map" class="absolute inset-0"></div>

        <div class="absolute left-6 top-6 glass-panel rounded-xl border border-slate-200 shadow-lg px-4 py-3 max-w-sm">
            <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Peta Sebaran</p>
            <p class="text-sm text-slate-700 mt-1">Warna kecamatan mengikuti total suara pada filter aktif.</p>
        </div>

        <div id="map-legend" class="hidden absolute left-6 bottom-6 glass-panel rounded-xl border border-slate-200 shadow-lg p-4 min-w-56">
            <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold mb-3">Legenda Suara</p>
            <div class="space-y-2">
                <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#fee2e2"></span><span class="text-xs text-slate-600">Rendah</span></div>
                <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#fca5a5"></span><span class="text-xs text-slate-600">Menengah rendah</span></div>
                <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#f87171"></span><span class="text-xs text-slate-600">Menengah</span></div>
                <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#ef4444"></span><span class="text-xs text-slate-600">Tinggi</span></div>
                <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#b91c1c"></span><span class="text-xs text-slate-600">Sangat tinggi</span></div>
            </div>
        </div>
    </section>

    <aside class="w-[410px] bg-white border-l border-slate-200 h-full overflow-y-auto z-40 p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <p class="text-[10px] uppercase tracking-[0.22em] text-slate-500 font-bold">Visualisasi</p>
                <h2 class="text-2xl font-extrabold text-[var(--primary)] mt-1">Ringkasan Grafik</h2>
            </div>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[var(--red-soft)] text-[var(--red)]">
                <span class="material-symbols-outlined">bar_chart</span>
            </span>
        </div>

        <div id="chart-placeholder" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
            <span class="material-symbols-outlined text-4xl text-slate-400">query_stats</span>
            <p class="mt-3 text-sm font-semibold text-slate-600">Pilih jenis pemilihan untuk menampilkan grafik.</p>
        </div>

        <div id="chart-loading" class="hidden rounded-xl border border-slate-200 bg-slate-50 p-8 text-center">
            <div class="mx-auto mb-3 h-8 w-8 rounded-full border-2 border-red-600 border-t-transparent animate-spin"></div>
            <p class="text-sm font-semibold text-slate-600">Memuat data grafik...</p>
        </div>

        <div id="chart-error" class="hidden rounded-xl border border-red-200 bg-red-50 p-5 text-sm font-semibold text-red-700"></div>

        <section id="card-suara" class="hidden rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden mb-5">
            <div class="border-b border-slate-200 px-5 py-4">
                <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Perolehan Suara</p>
                <p id="chart-subtitle" class="text-sm text-slate-600 mt-1"></p>
            </div>
            <div class="p-5">
                <div class="relative h-[330px]">
                    <canvas id="chartSuara"></canvas>
                </div>
            </div>
        </section>

        <section id="card-partisipasi" class="hidden rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden mb-5">
            <div class="border-b border-slate-200 px-5 py-4">
                <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Tingkat Partisipasi</p>
                <p class="text-sm text-slate-600 mt-1">DPT dibanding pengguna hak pilih.</p>
            </div>
            <div class="p-5">
                <div class="relative h-[250px]">
                    <canvas id="chartPartisipasi"></canvas>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-slate-50 overflow-hidden">
            <div class="border-b border-slate-200 px-5 py-4 flex items-center justify-between">
                <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Wilayah Teratas</p>
                <span class="material-symbols-outlined text-sm text-[var(--red)]">bolt</span>
            </div>
            <div id="rank-list" class="divide-y divide-slate-200">
                <div class="px-5 py-4 text-sm text-slate-500">Belum ada data ditampilkan.</div>
            </div>
        </section>
    </aside>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const allKecamatans = @json($kecamatans->map(fn($k) => ['id' => $k->id, 'nama' => $k->nama])->values());
const allDesas = @json($kecamatans->flatMap(fn($k) => $k->desas->map(fn($d) => ['id' => $d->id, 'nama' => $d->nama, 'kecamatan_id' => $k->id]))->values());
const allTps = @json($kecamatans->flatMap(fn($k) => $k->desas->flatMap(fn($d) => $d->tps->map(fn($t) => ['id' => $t->id, 'nama' => $t->nama, 'desa_id' => $d->id])))->values());

const gridClr = () => 'rgba(15,23,42,0.08)';
const textClr = () => '#657181';
const COLORS = ['#c81924', '#002147', '#4e87e7', '#f59e0b', '#10b981', '#7c3aed', '#0891b2', '#db2777', '#64748b', '#ea580c', '#0f766e', '#9333ea', '#2563eb', '#65a30d', '#be123c', '#475569', '#d97706', '#0284c7'];

let chartSuara = null;
let chartPart = null;
let geojsonLayer = null;
let kecamatanData = {};
let selectedKec = null;

const map = L.map('map', {
    zoomControl: true,
    scrollWheelZoom: true,
}).setView([-8.25, 114.35], 9);

L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OpenStreetMap &copy; CARTO',
    maxZoom: 18,
}).addTo(map);

function formatNumber(value) {
    return (Number(value) || 0).toLocaleString('id-ID');
}

function getColor(val, max) {
    if (!max || max <= 0) return '#e2e8f0';
    const r = val / max;
    if (r > 0.8) return '#b91c1c';
    if (r > 0.6) return '#ef4444';
    if (r > 0.4) return '#f87171';
    if (r > 0.2) return '#fca5a5';
    return '#fee2e2';
}

function styleFeature(feature) {
    const nama = (feature.properties.nama || '').toLowerCase();
    const values = Object.values(kecamatanData);
    const max = values.length ? Math.max(...values) : 0;
    const val = kecamatanData[nama] ?? 0;
    const sel = selectedKec && selectedKec.toLowerCase() === nama;

    return {
        fillColor: getColor(val, max),
        fillOpacity: max > 0 ? 0.76 : 0.46,
        color: sel ? '#f59e0b' : '#94a3b8',
        weight: sel ? 3 : 1,
        opacity: 1,
    };
}

function onEachFeature(feature, layer) {
    const nama = feature.properties.nama || '';
    layer.bindTooltip(`<b>${nama}</b>`, {
        permanent: false,
        direction: 'center',
        className: 'leaflet-tooltip-kec',
    });
    layer.on({
        mouseover: (e) => {
            if (selectedKec !== nama) e.target.setStyle({ fillOpacity: 0.92, weight: 2 });
        },
        mouseout: (e) => {
            if (selectedKec !== nama) geojsonLayer?.resetStyle(e.target);
        },
        click: () => selectKecamatan(nama),
    });
}

fetch('/geojson/banyuwangi_kecamatan.geojson')
    .then((response) => response.json())
    .then((data) => {
        geojsonLayer = L.geoJSON(data, { style: styleFeature, onEachFeature }).addTo(map);
        map.fitBounds(geojsonLayer.getBounds());
    })
    .catch(() => showError('Peta kecamatan gagal dimuat.'));

function selectKecamatan(namaKec) {
    const kec = allKecamatans.find((item) => item.nama.toLowerCase() === namaKec.toLowerCase());
    if (!kec) return;

    selectedKec = namaKec;
    geojsonLayer?.setStyle(styleFeature);
    document.getElementById('map-selected-label').textContent = `Kecamatan ${kec.nama}`;
    document.getElementById('wrap-reset-kec').classList.remove('hidden');

    const levelSelect = document.getElementById('f-level');
    if (levelSelect.value === 'kabupaten' || levelSelect.value === 'dapil') {
        levelSelect.value = 'kecamatan';
        onLevelChange(false);
    }

    document.getElementById('f-kec').value = kec.id;
    document.getElementById('wrap-kec').classList.remove('hidden');
    document.getElementById('f-desa').innerHTML = '<option value="">Pilih Desa</option>';
    document.getElementById('f-tps').innerHTML = '<option value="">Pilih TPS</option>';

    if (levelSelect.value === 'kecamatan') {
        loadChart();
        return;
    }

    allDesas.filter((desa) => desa.kecamatan_id == kec.id).forEach((desa) => {
        document.getElementById('f-desa').innerHTML += `<option value="${desa.id}">${desa.nama}</option>`;
    });
    document.getElementById('wrap-desa').classList.remove('hidden');
}

function resetKecFilter() {
    selectedKec = null;
    document.getElementById('f-kec').value = '';
    document.getElementById('f-desa').innerHTML = '<option value="">Pilih Desa</option>';
    document.getElementById('f-tps').innerHTML = '<option value="">Pilih TPS</option>';
    document.getElementById('map-selected-label').textContent = 'Klik kecamatan untuk filter';
    document.getElementById('wrap-reset-kec').classList.add('hidden');
    geojsonLayer?.setStyle(styleFeature);
    hideCharts();

    if (document.getElementById('f-level').value === 'kabupaten') loadChart();
}

function updateMapColors(data) {
    kecamatanData = {};
    data.forEach((item) => {
        kecamatanData[item.label.toLowerCase()] = item.suara.reduce((sum, value) => sum + value, 0);
    });
    geojsonLayer?.setStyle(styleFeature);

    const total = Object.values(kecamatanData).reduce((sum, value) => sum + value, 0);
    document.getElementById('map-legend').classList.toggle('hidden', total <= 0);
}

function setDapilMode(enabled) {
    const levelSelect = document.getElementById('f-level');
    const dapilOption = levelSelect.querySelector('option[value="dapil"]');
    dapilOption.classList.toggle('hidden', !enabled);

    if (enabled) {
        levelSelect.value = 'dapil';
    } else if (levelSelect.value === 'dapil') {
        levelSelect.value = 'kabupaten';
    }
}

function onJenisChange() {
    const jenis = document.getElementById('f-jenis').value;
    setDapilMode(jenis === 'dprd_kab');
    resetDependentFilters();
    hideCharts();

    if (jenis && jenis !== 'dprd_kab') loadChart();
}

function onLevelChange(shouldLoad = true) {
    const level = document.getElementById('f-level').value;
    const jenis = document.getElementById('f-jenis').value;

    document.getElementById('wrap-dapil').classList.toggle('hidden', !(level === 'dapil' || jenis === 'dprd_kab'));
    document.getElementById('wrap-kec').classList.toggle('hidden', level === 'kabupaten' || level === 'dapil');
    document.getElementById('wrap-desa').classList.toggle('hidden', !['desa', 'tps'].includes(level));
    document.getElementById('wrap-tps').classList.toggle('hidden', level !== 'tps');

    document.getElementById('f-kec').value = '';
    document.getElementById('f-dapil').value = '';
    document.getElementById('f-desa').innerHTML = '<option value="">Pilih Desa</option>';
    document.getElementById('f-tps').innerHTML = '<option value="">Pilih TPS</option>';
    hideCharts();

    if (shouldLoad && level === 'kabupaten') loadChart();
}

function resetDependentFilters() {
    document.getElementById('wrap-dapil').classList.toggle('hidden', document.getElementById('f-jenis').value !== 'dprd_kab');
    document.getElementById('wrap-kec').classList.add('hidden');
    document.getElementById('wrap-desa').classList.add('hidden');
    document.getElementById('wrap-tps').classList.add('hidden');
    document.getElementById('f-kec').value = '';
    document.getElementById('f-dapil').value = '';
    document.getElementById('f-desa').innerHTML = '<option value="">Pilih Desa</option>';
    document.getElementById('f-tps').innerHTML = '<option value="">Pilih TPS</option>';
}

function onDapilChange() {
    if (document.getElementById('f-dapil').value) loadChart();
    else hideCharts();
}

function onKecChange() {
    const level = document.getElementById('f-level').value;
    const kecId = document.getElementById('f-kec').value;
    document.getElementById('f-desa').innerHTML = '<option value="">Pilih Desa</option>';
    document.getElementById('f-tps').innerHTML = '<option value="">Pilih TPS</option>';
    hideCharts();

    if (!kecId) return;
    const kec = allKecamatans.find((item) => item.id == kecId);
    selectedKec = kec?.nama || null;
    document.getElementById('map-selected-label').textContent = kec ? `Kecamatan ${kec.nama}` : 'Klik kecamatan untuk filter';
    document.getElementById('wrap-reset-kec').classList.toggle('hidden', !kec);
    geojsonLayer?.setStyle(styleFeature);

    if (level === 'kecamatan') {
        loadChart();
        return;
    }

    allDesas.filter((desa) => desa.kecamatan_id == kecId).forEach((desa) => {
        document.getElementById('f-desa').innerHTML += `<option value="${desa.id}">${desa.nama}</option>`;
    });
    document.getElementById('wrap-desa').classList.remove('hidden');
}

function onDesaChange() {
    const level = document.getElementById('f-level').value;
    const desaId = document.getElementById('f-desa').value;
    document.getElementById('f-tps').innerHTML = '<option value="">Pilih TPS</option>';
    hideCharts();

    if (!desaId) return;
    if (level === 'desa') {
        loadChart();
        return;
    }

    allTps.filter((tps) => tps.desa_id == desaId).forEach((tps) => {
        document.getElementById('f-tps').innerHTML += `<option value="${tps.id}">${tps.nama}</option>`;
    });
    document.getElementById('wrap-tps').classList.remove('hidden');
}

function hideCharts() {
    document.getElementById('chart-placeholder').classList.remove('hidden');
    document.getElementById('chart-loading').classList.add('hidden');
    document.getElementById('chart-error').classList.add('hidden');
    document.getElementById('card-suara').classList.add('hidden');
    document.getElementById('card-partisipasi').classList.add('hidden');
}

function showError(message) {
    document.getElementById('chart-placeholder').classList.add('hidden');
    document.getElementById('chart-loading').classList.add('hidden');
    document.getElementById('chart-error').textContent = message;
    document.getElementById('chart-error').classList.remove('hidden');
}

async function loadChart() {
    const jenis = document.getElementById('f-jenis').value;
    if (!jenis) return;

    const level = document.getElementById('f-level').value;
    const kecId = document.getElementById('f-kec').value;
    const desaId = document.getElementById('f-desa').value;
    const tpsId = document.getElementById('f-tps').value;
    const dapilId = document.getElementById('f-dapil').value;

    if (level === 'dapil' && !dapilId) return;
    if (level === 'kecamatan' && !kecId) return;
    if (level === 'desa' && !desaId) return;
    if (level === 'tps' && !tpsId) return;

    document.getElementById('chart-placeholder').classList.add('hidden');
    document.getElementById('chart-error').classList.add('hidden');
    document.getElementById('card-suara').classList.add('hidden');
    document.getElementById('card-partisipasi').classList.add('hidden');
    document.getElementById('chart-loading').classList.remove('hidden');

    const params = new URLSearchParams({ jenis, level });
    if (dapilId) params.set('dapil_id', dapilId);
    if (kecId) params.set('kecamatan_id', kecId);
    if (desaId) params.set('desa_id', desaId);
    if (tpsId) params.set('tps_id', tpsId);

    try {
        const res = await fetch('{{ route("admin.rekap.chart.data") }}?' + params);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const json = await res.json();
        renderCharts(json);
        if (level === 'kabupaten' || level === 'dapil') updateMapColors(json.data);
    } catch (error) {
        console.error(error);
        showError('Gagal memuat data grafik. Periksa koneksi atau data rekap.');
    } finally {
        document.getElementById('chart-loading').classList.add('hidden');
    }
}

function renderCharts(json) {
    if (!json.data || !json.data.length) {
        showError('Data belum tersedia untuk filter ini.');
        updateStats([]);
        updateRanking([]);
        return;
    }

    const wLabels = json.data.map((item) => item.label);
    const isPie = json.type === 'pie' && json.data.length === 1;
    document.getElementById('chart-subtitle').textContent = json.data.length === 1 ? json.data[0].label : `${json.data.length} wilayah`;

    if (chartSuara) chartSuara.destroy();
    const ctxS = document.getElementById('chartSuara').getContext('2d');

    if (isPie) {
        chartSuara = new Chart(ctxS, {
            type: 'doughnut',
            data: {
                labels: json.labels,
                datasets: [{
                    data: json.data[0].suara,
                    backgroundColor: COLORS,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '58%',
                plugins: {
                    legend: { position: 'right', labels: { color: textClr(), font: { size: 11 }, padding: 12 } },
                    tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: ${formatNumber(ctx.parsed)} suara` } },
                },
            },
        });
    } else {
        const isMulti = json.data.length > 1;
        const datasets = isMulti
            ? json.labels.map((label, index) => ({
                label,
                data: json.data.map((item) => item.suara[index] ?? 0),
                backgroundColor: COLORS[index % COLORS.length],
                borderRadius: 4,
            }))
            : [{
                label: 'Suara',
                data: json.data[0].suara,
                backgroundColor: COLORS,
                borderRadius: 4,
            }];

        chartSuara = new Chart(ctxS, {
            type: 'bar',
            data: { labels: isMulti ? wLabels : json.labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: isMulti, labels: { color: textClr(), font: { size: 10 } } },
                    tooltip: { callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${formatNumber(ctx.parsed.y)}` } },
                },
                scales: {
                    x: { ticks: { color: textClr(), font: { size: 10 }, maxRotation: 45 }, grid: { color: gridClr() } },
                    y: { ticks: { color: textClr(), font: { size: 10 } }, grid: { color: gridClr() }, beginAtZero: true },
                },
            },
        });
    }

    document.getElementById('card-suara').classList.remove('hidden');

    if (chartPart) chartPart.destroy();
    const ctxP = document.getElementById('chartPartisipasi').getContext('2d');
    chartPart = new Chart(ctxP, {
        type: 'bar',
        data: {
            labels: wLabels,
            datasets: [
                { label: 'DPT', data: json.data.map((item) => item.partisipasi.dpt), backgroundColor: 'rgba(100,116,139,0.32)', borderRadius: 4 },
                { label: 'Hadir', data: json.data.map((item) => item.partisipasi.hadir), backgroundColor: '#c81924', borderRadius: 4 },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: textClr(), font: { size: 10 } } },
                tooltip: { callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${formatNumber(ctx.parsed.y)}` } },
            },
            scales: {
                x: { ticks: { color: textClr(), font: { size: 10 }, maxRotation: 45 }, grid: { color: gridClr() } },
                y: { ticks: { color: textClr(), font: { size: 10 } }, grid: { color: gridClr() }, beginAtZero: true },
            },
        },
    });
    document.getElementById('card-partisipasi').classList.remove('hidden');

    updateStats(json.data);
    updateRanking(json.data);
}

function updateStats(data) {
    const totalSuara = data.reduce((sum, item) => sum + item.suara.reduce((a, b) => a + b, 0), 0);
    const totalDpt = data.reduce((sum, item) => sum + (item.partisipasi?.dpt || 0), 0);
    const totalHadir = data.reduce((sum, item) => sum + (item.partisipasi?.hadir || 0), 0);
    const persen = totalDpt > 0 ? Math.round((totalHadir / totalDpt) * 1000) / 10 : 0;

    document.getElementById('stat-total-suara').textContent = formatNumber(totalSuara);
    document.getElementById('stat-total-wilayah').textContent = formatNumber(data.length);
    document.getElementById('stat-partisipasi').textContent = `${persen}%`;
    document.getElementById('stat-dpt').textContent = formatNumber(totalDpt);
}

function updateRanking(data) {
    const rank = [...data]
        .map((item) => ({
            label: item.label,
            suara: item.suara.reduce((a, b) => a + b, 0),
            dpt: item.partisipasi?.dpt || 0,
            hadir: item.partisipasi?.hadir || 0,
        }))
        .sort((a, b) => b.suara - a.suara)
        .slice(0, 5);

    const target = document.getElementById('rank-list');
    if (!rank.length) {
        target.innerHTML = '<div class="px-5 py-4 text-sm text-slate-500">Belum ada data ditampilkan.</div>';
        return;
    }

    target.innerHTML = rank.map((item, index) => {
        const persen = item.dpt > 0 ? Math.round((item.hadir / item.dpt) * 1000) / 10 : 0;
        return `
            <div class="px-5 py-4 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-slate-800 truncate">${index + 1}. ${item.label}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Partisipasi ${persen}%</p>
                </div>
                <p class="font-mono-data text-sm font-bold text-[var(--red)]">${formatNumber(item.suara)}</p>
            </div>
        `;
    }).join('');
}

document.addEventListener('DOMContentLoaded', () => {
    const jenis = document.getElementById('f-jenis').value;
    setDapilMode(jenis === 'dprd_kab');
    if (jenis && jenis !== 'dprd_kab') loadChart();
});
</script>
</body>
</html>
