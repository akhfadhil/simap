@extends('layouts.app')
@section('title', 'Grafik & Statistik')

@section('content')

{{-- Back + Header --}}
<div class="mb-6">
    <a href="{{ route('dashboard.admin') }}"
       class="inline-flex items-center gap-2 text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition font-medium mb-4">
        ← Kembali ke Dashboard
    </a>
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-1 font-semibold">// Admin — Visualisasi Data</p>
            <h1 class="font-display text-4xl tracking-[2px] text-red-600">GRAFIK & STATISTIK</h1>
        </div>
    </div>
</div>

{{-- Main Layout --}}
<div class="flex gap-4" style="height: calc(100vh - 220px); min-height: 600px;">

    {{-- PETA KIRI --}}
    <div class="flex-shrink-0 dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden flex flex-col" style="width:55%">
        <div class="px-4 py-3 border-b dark:border-gray-700 border-gray-200 flex items-center justify-between flex-shrink-0">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Peta Kabupaten Banyuwangi</p>
            <span id="map-selected-label" class="text-xs dark:text-gray-400 text-gray-500 italic">Klik kecamatan untuk filter</span>
        </div>
        <div id="map" class="flex-1 w-full"></div>
        <div id="map-legend" class="hidden px-4 py-2 border-t dark:border-gray-700 border-gray-200 flex items-center gap-3 flex-wrap">
            <span class="text-[10px] dark:text-gray-500 text-gray-400 uppercase font-semibold tracking-wider">Suara:</span>
            <div class="flex items-center gap-1"><div class="w-3 h-3 rounded-sm" style="background:#fee2e2"></div><span class="text-[10px] dark:text-gray-400 text-gray-500">Rendah</span></div>
            <div class="flex items-center gap-1"><div class="w-3 h-3 rounded-sm" style="background:#fca5a5"></div></div>
            <div class="flex items-center gap-1"><div class="w-3 h-3 rounded-sm" style="background:#f87171"></div></div>
            <div class="flex items-center gap-1"><div class="w-3 h-3 rounded-sm" style="background:#ef4444"></div></div>
            <div class="flex items-center gap-1"><div class="w-3 h-3 rounded-sm" style="background:#b91c1c"></div><span class="text-[10px] dark:text-gray-400 text-gray-500">Tinggi</span></div>
        </div>
    </div>

    {{-- PANEL KANAN --}}
    <div class="flex-1 flex flex-col gap-4 overflow-y-auto min-w-0">

        {{-- Filter --}}
        <div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm p-4 flex-shrink-0">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold mb-3">// Filter</p>
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="block text-[10px] font-semibold dark:text-gray-500 text-gray-400 uppercase tracking-wider mb-1.5">Jenis Pemilihan</label>
                    <select id="f-jenis" onchange="onJenisChange()"
                            class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-3 py-2 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                        <option value="">— Pilih Jenis —</option>
                        @php $aktifJenis = \App\Models\PemiluSetting::aktif(); @endphp
                        @foreach(\App\Models\RekapHeader::JENIS_LABELS as $key => $label)
                        @if(in_array($key, $aktifJenis))
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-semibold dark:text-gray-500 text-gray-400 uppercase tracking-wider mb-1.5">Level</label>
                    <select id="f-level" onchange="onLevelChange()"
                            class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-3 py-2 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                        <option value="kabupaten">Kabupaten</option>
                        <option value="kecamatan">Kecamatan</option>
                        <option value="desa">Desa</option>
                        <option value="tps">TPS</option>
                    </select>
                </div>
                <div id="wrap-dapil" class="hidden">
                    <label class="block text-[10px] font-semibold dark:text-gray-500 text-gray-400 uppercase tracking-wider mb-1.5">Dapil</label>
                    <select id="f-dapil" onchange="onDapilChange()"
                            class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-3 py-2 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                        <option value="">— Pilih Dapil —</option>
                        @foreach($dapils as $dapil)
                        <option value="{{ $dapil->id }}">{{ $dapil->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="wrap-kec" class="hidden col-span-2">
                    <label class="block text-[10px] font-semibold dark:text-gray-500 text-gray-400 uppercase tracking-wider mb-1.5">Kecamatan</label>
                    <select id="f-kec" onchange="onKecChange()"
                            class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-3 py-2 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                        <option value="">— Pilih Kecamatan —</option>
                        @foreach($kecamatans as $kec)
                        <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="wrap-desa" class="hidden col-span-2">
                    <label class="block text-[10px] font-semibold dark:text-gray-500 text-gray-400 uppercase tracking-wider mb-1.5">Desa</label>
                    <select id="f-desa" onchange="onDesaChange()"
                            class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-3 py-2 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                        <option value="">— Pilih Desa —</option>
                    </select>
                </div>
                <div id="wrap-tps" class="hidden col-span-2">
                    <label class="block text-[10px] font-semibold dark:text-gray-500 text-gray-400 uppercase tracking-wider mb-1.5">TPS</label>
                    <select id="f-tps" onchange="loadChart()"
                            class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-3 py-2 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                        <option value="">— Pilih TPS —</option>
                    </select>
                </div>
                <div id="wrap-reset-kec" class="hidden col-span-2">
                    <button onclick="resetKecFilter()" class="text-xs text-red-400 hover:text-red-500 transition">✕ Reset filter kecamatan</button>
                </div>
            </div>
        </div>

        {{-- Placeholder --}}
        <div id="chart-placeholder" class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm p-10 text-center flex-shrink-0">
            <p class="text-3xl mb-2">📊</p>
            <p class="dark:text-gray-500 text-gray-400 text-sm">Pilih jenis pemilihan untuk menampilkan grafik</p>
        </div>

        {{-- Loading --}}
        <div id="chart-loading" class="hidden dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm p-10 text-center flex-shrink-0">
            <div class="inline-block w-7 h-7 border-2 border-red-500 border-t-transparent rounded-full animate-spin mb-2"></div>
            <p class="dark:text-gray-500 text-gray-400 text-sm">Memuat data...</p>
        </div>

        {{-- Chart Suara --}}
        <div id="card-suara" class="hidden dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden flex-shrink-0">
            <div class="px-4 py-3 border-b dark:border-gray-700 border-gray-200">
                <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Perolehan Suara</p>
                <p id="chart-subtitle" class="text-xs dark:text-gray-400 text-gray-500 mt-0.5"></p>
            </div>
            <div class="p-4"><div class="relative" style="height:320px"><canvas id="chartSuara"></canvas></div></div>
        </div>

        {{-- Chart Partisipasi --}}
        <div id="card-partisipasi" class="hidden dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden flex-shrink-0">
            <div class="px-4 py-3 border-b dark:border-gray-700 border-gray-200">
                <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Tingkat Partisipasi</p>
                <p class="text-xs dark:text-gray-400 text-gray-500 mt-0.5">DPT vs Pengguna Hak Pilih</p>
            </div>
            <div class="p-4"><div class="relative" style="height:240px"><canvas id="chartPartisipasi"></canvas></div></div>
        </div>

    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const allKecamatans = @json($kecamatans->map(fn($k) => ['id'=>$k->id,'nama'=>$k->nama])->values());
const allDesas      = @json($kecamatans->flatMap(fn($k) => $k->desas->map(fn($d) => ['id'=>$d->id,'nama'=>$d->nama,'kecamatan_id'=>$k->id]))->values());
const allTps        = @json($kecamatans->flatMap(fn($k) => $k->desas->flatMap(fn($d) => $d->tps->map(fn($t) => ['id'=>$t->id,'nama'=>$t->nama,'desa_id'=>$d->id])))->values());

const isDark  = () => document.documentElement.classList.contains('dark');
const gridClr = () => isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
const textClr = () => isDark() ? '#9CA3AF' : '#6B7280';
const COLORS  = ['#ef4444','#3b82f6','#10b981','#f59e0b','#8b5cf6','#ec4899','#06b6d4','#84cc16','#f97316','#6366f1','#14b8a6','#f43f5e','#a855f7','#0ea5e9','#22c55e','#eab308','#d946ef','#0891b2'];

let chartSuara = null, chartPart = null;
let geojsonLayer = null, kecamatanData = {}, selectedKec = null;

// ── MAP ─────────────────────────────────────────────────
const tileSrc = isDark()
    ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
    : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
const map = L.map('map', { zoomControl: true, scrollWheelZoom: true }).setView([-8.25, 114.35], 9);
L.tileLayer(tileSrc, { attribution: '© OpenStreetMap © CARTO', maxZoom: 18 }).addTo(map);

function getColor(val, max) {
    if (!max) return isDark() ? '#374151' : '#E5E7EB';
    const r = val / max;
    if (r > 0.8) return '#b91c1c';
    if (r > 0.6) return '#ef4444';
    if (r > 0.4) return '#f87171';
    if (r > 0.2) return '#fca5a5';
    return '#fee2e2';
}

function styleFeature(feature) {
    const nama = (feature.properties.nama || '').toLowerCase();
    const val  = kecamatanData[nama] ?? 0;
    const max  = Math.max(...Object.values(kecamatanData), 1);
    const sel  = selectedKec && selectedKec.toLowerCase() === nama;
    return { fillColor: getColor(val, max), fillOpacity: 0.75, color: sel ? '#fbbf24' : (isDark() ? '#4B5563' : '#9CA3AF'), weight: sel ? 3 : 1, opacity: 1 };
}

function onEachFeature(feature, layer) {
    const nama = feature.properties.nama || '';
    layer.bindTooltip(`<b>${nama}</b>`, { permanent: false, direction: 'center', className: 'leaflet-tooltip-kec' });
    layer.on({
        mouseover: (e) => { if (selectedKec !== nama) e.target.setStyle({ fillOpacity: 0.95, weight: 2 }); },
        mouseout:  (e) => { if (selectedKec !== nama) geojsonLayer?.resetStyle(e.target); },
        click:     ()  => selectKecamatan(nama),
    });
}

fetch('/geojson/banyuwangi_kecamatan.geojson')
    .then(r => r.json())
    .then(data => {
        geojsonLayer = L.geoJSON(data, { style: styleFeature, onEachFeature }).addTo(map);
        map.fitBounds(geojsonLayer.getBounds());
    });

function selectKecamatan(namaKec) {
    const kec = allKecamatans.find(k => k.nama.toLowerCase() === namaKec.toLowerCase());
    if (!kec) return;
    selectedKec = namaKec;
    geojsonLayer?.setStyle(styleFeature);
    const level = document.getElementById('f-level').value;
    if (level === 'kabupaten') return;
    document.getElementById('f-kec').value = kec.id;
    document.getElementById('wrap-kec').classList.remove('hidden');
    document.getElementById('map-selected-label').textContent = '📍 ' + kec.nama;
    document.getElementById('wrap-reset-kec').classList.remove('hidden');
    document.getElementById('f-desa').innerHTML = '<option value="">— Pilih Desa —</option>';
    document.getElementById('f-tps').innerHTML  = '<option value="">— Pilih TPS —</option>';
    if (level === 'kecamatan') { loadChart(); return; }
    allDesas.filter(d => d.kecamatan_id == kec.id).forEach(d => {
        document.getElementById('f-desa').innerHTML += `<option value="${d.id}">${d.nama}</option>`;
    });
    document.getElementById('wrap-desa').classList.remove('hidden');
}

function resetKecFilter() {
    selectedKec = null;
    document.getElementById('f-kec').value = '';
    document.getElementById('f-desa').innerHTML = '<option value="">— Pilih Desa —</option>';
    document.getElementById('f-tps').innerHTML  = '<option value="">— Pilih TPS —</option>';
    document.getElementById('map-selected-label').textContent = 'Klik kecamatan untuk filter';
    document.getElementById('wrap-reset-kec').classList.add('hidden');
    geojsonLayer?.setStyle(styleFeature);
    hideCharts();
    if (document.getElementById('f-level').value === 'kabupaten') loadChart();
}

function updateMapColors(data) {
    kecamatanData = {};
    data.forEach(d => { kecamatanData[d.label.toLowerCase()] = d.suara.reduce((a,b) => a+b, 0); });
    geojsonLayer?.setStyle(styleFeature);
    document.getElementById('map-legend').classList.remove('hidden');
}

// ── FILTER ──────────────────────────────────────────────
function onJenisChange() {
    const jenis  = document.getElementById('f-jenis').value;
    const fLevel = document.getElementById('f-level');
    const kabOpt = fLevel.querySelector('option[value="kabupaten"]') || fLevel.querySelector('option[value="dapil"]');
    if (jenis === 'dprd_kab') { kabOpt.value = 'dapil'; kabOpt.textContent = 'Dapil'; fLevel.value = 'dapil'; }
    else { kabOpt.value = 'kabupaten'; kabOpt.textContent = 'Kabupaten'; fLevel.value = 'kabupaten'; }
    document.getElementById('wrap-dapil').classList.toggle('hidden', jenis !== 'dprd_kab');
    document.getElementById('wrap-kec').classList.add('hidden');
    document.getElementById('wrap-desa').classList.add('hidden');
    document.getElementById('wrap-tps').classList.add('hidden');
    document.getElementById('f-kec').value = '';
    document.getElementById('f-dapil').value = '';
    document.getElementById('f-desa').innerHTML = '<option value="">— Pilih Desa —</option>';
    document.getElementById('f-tps').innerHTML  = '<option value="">— Pilih TPS —</option>';
    hideCharts();
    if (jenis && jenis !== 'dprd_kab') loadChart();
}

function onLevelChange() {
    const level = document.getElementById('f-level').value;
    const jenis = document.getElementById('f-jenis').value;
    document.getElementById('wrap-dapil').classList.toggle('hidden', !(level === 'dapil' || jenis === 'dprd_kab'));
    document.getElementById('wrap-kec').classList.toggle('hidden', level === 'kabupaten' || level === 'dapil');
    document.getElementById('wrap-desa').classList.toggle('hidden', !['desa','tps'].includes(level));
    document.getElementById('wrap-tps').classList.toggle('hidden', level !== 'tps');
    document.getElementById('f-kec').value = '';
    document.getElementById('f-dapil').value = '';
    document.getElementById('f-desa').innerHTML = '<option value="">— Pilih Desa —</option>';
    document.getElementById('f-tps').innerHTML  = '<option value="">— Pilih TPS —</option>';
    hideCharts();
    if (level === 'kabupaten') loadChart();
}

function onDapilChange() { if (document.getElementById('f-dapil').value) loadChart(); else hideCharts(); }

function onKecChange() {
    const level = document.getElementById('f-level').value;
    const kecId = document.getElementById('f-kec').value;
    document.getElementById('f-desa').innerHTML = '<option value="">— Pilih Desa —</option>';
    document.getElementById('f-tps').innerHTML  = '<option value="">— Pilih TPS —</option>';
    hideCharts();
    if (!kecId) return;
    if (level === 'kecamatan') { loadChart(); return; }
    allDesas.filter(d => d.kecamatan_id == kecId).forEach(d => {
        document.getElementById('f-desa').innerHTML += `<option value="${d.id}">${d.nama}</option>`;
    });
    document.getElementById('wrap-desa').classList.remove('hidden');
}

function onDesaChange() {
    const level  = document.getElementById('f-level').value;
    const desaId = document.getElementById('f-desa').value;
    document.getElementById('f-tps').innerHTML = '<option value="">— Pilih TPS —</option>';
    hideCharts();
    if (!desaId) return;
    if (level === 'desa') { loadChart(); return; }
    allTps.filter(t => t.desa_id == desaId).forEach(t => {
        document.getElementById('f-tps').innerHTML += `<option value="${t.id}">${t.nama}</option>`;
    });
    document.getElementById('wrap-tps').classList.remove('hidden');
}

function hideCharts() {
    document.getElementById('chart-placeholder').classList.remove('hidden');
    document.getElementById('chart-loading').classList.add('hidden');
    document.getElementById('card-suara').classList.add('hidden');
    document.getElementById('card-partisipasi').classList.add('hidden');
}

// ── LOAD & RENDER ────────────────────────────────────────
async function loadChart() {
    const jenis   = document.getElementById('f-jenis').value; if (!jenis) return;
    const level   = document.getElementById('f-level').value;
    const kecId   = document.getElementById('f-kec').value;
    const desaId  = document.getElementById('f-desa').value;
    const tpsId   = document.getElementById('f-tps').value;
    const dapilId = document.getElementById('f-dapil').value;
    if (level === 'dapil'     && !dapilId) return;
    if (level === 'kecamatan' && !kecId)   return;
    if (level === 'desa'      && !desaId)  return;
    if (level === 'tps'       && !tpsId)   return;

    document.getElementById('chart-placeholder').classList.add('hidden');
    document.getElementById('card-suara').classList.add('hidden');
    document.getElementById('card-partisipasi').classList.add('hidden');
    document.getElementById('chart-loading').classList.remove('hidden');

    const params = new URLSearchParams({ jenis, level });
    if (dapilId) params.set('dapil_id', dapilId);
    if (kecId)   params.set('kecamatan_id', kecId);
    if (desaId)  params.set('desa_id', desaId);
    if (tpsId)   params.set('tps_id', tpsId);

    try {
        const res  = await fetch('{{ route("admin.rekap.chart.data") }}?' + params);
        const json = await res.json();
        renderCharts(json);
        if (level === 'kabupaten' || level === 'dapil') updateMapColors(json.data);
    } catch(e) { console.error(e); }
    finally { document.getElementById('chart-loading').classList.add('hidden'); }
}

function renderCharts(json) {
    const wLabels = json.data.map(d => d.label);
    const isPie   = json.type === 'pie' && json.data.length === 1;
    document.getElementById('chart-subtitle').textContent = json.data.length === 1 ? json.data[0].label : json.data.length + ' wilayah';

    if (chartSuara) chartSuara.destroy();
    const ctxS = document.getElementById('chartSuara').getContext('2d');
    if (isPie) {
        chartSuara = new Chart(ctxS, { type:'doughnut', data:{ labels:json.labels, datasets:[{ data:json.data[0].suara, backgroundColor:COLORS, borderWidth:2, borderColor:isDark()?'#1F2937':'#fff' }]},
            options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{position:'right',labels:{color:textClr(),font:{size:11},padding:12}}, tooltip:{callbacks:{label:ctx=>` ${ctx.label}: ${ctx.parsed.toLocaleString()} suara`}} }}});
    } else {
        const isMulti = json.data.length > 1;
        const ds = isMulti
            ? json.labels.map((l,i) => ({ label:l, data:json.data.map(d=>d.suara[i]??0), backgroundColor:COLORS[i%COLORS.length], borderRadius:3 }))
            : [{ label:'Suara', data:json.data[0].suara, backgroundColor:COLORS, borderRadius:3 }];
        chartSuara = new Chart(ctxS, { type:'bar', data:{ labels:isMulti?wLabels:json.labels, datasets:ds },
            options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:isMulti,labels:{color:textClr(),font:{size:10}}}, tooltip:{callbacks:{label:ctx=>` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString()}`}} },
            scales:{ x:{ticks:{color:textClr(),font:{size:10},maxRotation:45},grid:{color:gridClr()}}, y:{ticks:{color:textClr(),font:{size:10}},grid:{color:gridClr()},beginAtZero:true} }}});
    }
    document.getElementById('card-suara').classList.remove('hidden');

    if (chartPart) chartPart.destroy();
    const ctxP = document.getElementById('chartPartisipasi').getContext('2d');
    chartPart = new Chart(ctxP, { type:'bar',
        data:{ labels:wLabels, datasets:[
            { label:'DPT',   data:json.data.map(d=>d.partisipasi.dpt),   backgroundColor:isDark()?'rgba(107,114,128,0.4)':'rgba(107,114,128,0.3)', borderRadius:3 },
            { label:'Hadir', data:json.data.map(d=>d.partisipasi.hadir), backgroundColor:'#EF4444', borderRadius:3 },
        ]},
        options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{labels:{color:textClr(),font:{size:10}}}, tooltip:{callbacks:{label:ctx=>` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString()}`}} },
        scales:{ x:{ticks:{color:textClr(),font:{size:10},maxRotation:45},grid:{color:gridClr()}}, y:{ticks:{color:textClr(),font:{size:10}},grid:{color:gridClr()},beginAtZero:true} }}});
    document.getElementById('card-partisipasi').classList.remove('hidden');
}
</script>
<style>
.leaflet-tooltip-kec { background:rgba(17,24,39,0.9); color:#F9FAFB; border:1px solid #374151; border-radius:6px; font-size:12px; font-weight:600; padding:4px 10px; box-shadow:0 2px 8px rgba(0,0,0,0.3); }
.leaflet-container { background:transparent !important; }
</style>
@endpush
@endsection
