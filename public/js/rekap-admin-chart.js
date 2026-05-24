const chartConfig = window.SIMAP_CHART_CONFIG || {};
const allKecamatans = chartConfig.kecamatans || [];
const allDesas = chartConfig.desas || [];
const allTps = chartConfig.tps || [];

// State utama halaman grafik admin.
let geojsonLayer = null;
let kecamatanData = {};
let selectedKec = null;
let currentChartJson = null;
let kecamatanGeojson = null;
let desaGeojson = null;
let currentMapMode = 'kecamatan';
let currentMapLabels = [];

const WINNER_MAP_TYPES = ['ppwp', 'gubernur', 'bupati'];
const WINNER_COLORS = ['#c81924', '#002147', '#f59e0b', '#10b981', '#7c3aed', '#0891b2', '#db2777', '#ea580c'];

// Leaflet dipakai untuk peta interaktif dan highlight wilayah.
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

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function escapeJs(value) {
    return String(value ?? '')
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'")
        .replace(/\n/g, '\\n')
        .replace(/\r/g, '\\r');
}

function normalizeText(value) {
    return String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();
}

function updateJenisButtons() {
    const selected = document.getElementById('f-jenis').value;
    document.querySelectorAll('.jenis-btn').forEach((button) => {
        button.classList.toggle('is-active', button.dataset.jenis === selected);
    });
}

function selectJenis(jenis) {
    if (document.getElementById('f-jenis').value === jenis) return;
    document.getElementById('f-jenis').value = jenis;
    updateJenisButtons();
    onJenisChange();
}

function clearChartSearch() {
    document.getElementById('f-search').value = '';
    hideSearchSuggestions();
    applyChartSearch();
}

function searchSuggestionItems(json = currentChartJson) {
    if (!json) return [];

    const items = (json.labels || []).map((label, index) => ({
        label,
        meta: (json.search_meta?.[index] || label) === label ? '' : json.search_meta?.[index] || '',
        value: label,
    }));

    const candidates = json.candidate_series?.length ? json.candidate_series : (json.candidate_rank || []);
    candidates.forEach((item) => {
        items.push({
            label: item.label,
            meta: item.meta || '',
            value: item.label,
        });
    });

    const seen = new Set();
    return items.filter((item) => {
        const key = normalizeText(`${item.label} ${item.meta}`);
        if (seen.has(key)) return false;
        seen.add(key);
        return true;
    });
}

function renderSearchSuggestions() {
    const box = document.getElementById('search-suggestions');
    const input = document.getElementById('f-search');
    const term = normalizeText(input?.value || '');
    const items = searchSuggestionItems()
        .filter((item) => !term || normalizeText(`${item.label} ${item.meta}`).includes(term))
        .slice(0, 8);

    if (!currentChartJson || !items.length) {
        box.classList.add('hidden');
        box.innerHTML = '';
        return;
    }

    box.innerHTML = items.map((item) => `
        <button type="button"
                onclick="selectSearchSuggestion('${escapeJs(item.value)}')"
                class="block w-full px-4 py-3 text-left hover:bg-slate-50">
            <span class="block text-sm font-bold text-slate-800">${escapeHtml(item.label)}</span>
            ${item.meta ? `<span class="mt-0.5 block text-xs text-slate-500">${escapeHtml(item.meta)}</span>` : ''}
        </button>
    `).join('');
    box.classList.remove('hidden');
}

function selectSearchSuggestion(value) {
    document.getElementById('f-search').value = value;
    hideSearchSuggestions();
    applyChartSearch();
}

function hideSearchSuggestions() {
    const box = document.getElementById('search-suggestions');
    box.classList.add('hidden');
    box.innerHTML = '';
}

function filterChartJson(json) {
    if (!json) return null;

    const term = normalizeText(document.getElementById('f-search')?.value || '');
    document.getElementById('clear-search')?.classList.toggle('hidden', term.length === 0);
    document.getElementById('clear-search')?.classList.toggle('flex', term.length > 0);
    renderSearchSuggestions();

    if (!term) return json;

    const searchMeta = json.search_meta || json.labels || [];
    const partyIndexes = json.labels
        .map((label, index) => ({ label, index }))
        .filter((item) => normalizeText(item.label).includes(term))
        .map((item) => item.index);
    const candidateMatches = partyIndexes.length
        ? []
        : (json.candidate_series || [])
            .filter((item) => normalizeText(`${item.label} ${item.meta || ''}`).includes(term));

    if (candidateMatches.length) {
        const candidateRank = candidateMatches
            .map((item) => ({
                id: item.id,
                label: item.label,
                meta: item.meta || '',
                suara: (item.suara || []).reduce((sum, value) => sum + (Number(value) || 0), 0),
            }))
            .sort((a, b) => b.suara - a.suara);

        return {
            ...json,
            search_mode: 'candidate',
            labels: candidateMatches.map((item) => item.label),
            search_meta: candidateMatches.map((item) => item.meta || ''),
            candidate_rank: candidateRank,
            data: json.data.map((item, groupIndex) => ({
                ...item,
                suara: candidateMatches.map((candidate) => candidate.suara?.[groupIndex] ?? 0),
            })),
        };
    }

    const indexes = partyIndexes.length
        ? partyIndexes
        : json.labels
        .map((label, index) => ({ label, index }))
        .filter((item) => normalizeText(`${item.label} ${searchMeta[item.index] || ''}`).includes(term))
        .map((item) => item.index);
    const selectedParties = new Set(indexes.map((index) => normalizeText(json.labels[index])));

    return {
        ...json,
        search_mode: partyIndexes.length ? 'party' : null,
        labels: indexes.map((index) => json.labels[index]),
        candidate_rank: partyIndexes.length
            ? json.candidate_rank?.filter((item) => selectedParties.has(normalizeText(item.meta || ''))) || []
            : json.candidate_rank?.filter((item) => normalizeText(`${item.label} ${item.meta || ''}`).includes(term)) || [],
        data: json.data.map((item) => ({
            ...item,
            suara: indexes.map((index) => item.suara[index] ?? 0),
        })),
    };
}

function applyChartSearch() {
    if (!currentChartJson) {
        document.getElementById('clear-search')?.classList.toggle('hidden', !document.getElementById('f-search')?.value);
        document.getElementById('clear-search')?.classList.toggle('flex', !!document.getElementById('f-search')?.value);
        hideSearchSuggestions();
        return;
    }

    const filtered = filterChartJson(currentChartJson);
    if (!filtered.labels.length) {
        showError('Partai atau caleg tidak ditemukan pada jenis pemilihan ini.');
        updateStats([]);
        updateRanking([]);
        updateDetailTable(null);
        updateMapColors(null);
        return;
    }

    renderCharts(filtered);
    const level = document.getElementById('f-level').value;
    if (['kabupaten', 'dapil', 'kecamatan'].includes(level)) updateMapColors(filtered);
    else updateMapColors(null);
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

function isWinnerMapType(jenis = document.getElementById('f-jenis').value) {
    return WINNER_MAP_TYPES.includes(jenis);
}

function featureName(feature) {
    const props = feature.properties || {};
    return currentMapMode === 'desa'
        ? (props.village || props.nama || '')
        : (props.nama || props.sub_district || '');
}

function featureDistrict(feature) {
    const props = feature.properties || {};
    return props.sub_district || props.nama || '';
}

function styleFeature(feature) {
    const nama = featureName(feature);
    const key = normalizeText(nama);
    const values = Object.values(kecamatanData).map((item) => item.total || 0);
    const max = values.length ? Math.max(...values) : 0;
    const item = kecamatanData[key] || { total: 0, winnerIndex: null };
    const sel = currentMapMode === 'kecamatan' && selectedKec && normalizeText(selectedKec) === key;
    const winnerMode = isWinnerMapType();

    return {
        fillColor: winnerMode && item.winnerIndex !== null
            ? WINNER_COLORS[item.winnerIndex % WINNER_COLORS.length]
            : getColor(item.total, max),
        fillOpacity: item.total > 0 ? 0.78 : 0.38,
        color: sel ? '#f59e0b' : '#94a3b8',
        weight: sel ? 3 : 1,
        opacity: 1,
    };
}

function onEachFeature(feature, layer) {
    const nama = featureName(feature);
    layer.bindTooltip(mapTooltipContent(feature), {
        permanent: false,
        direction: 'center',
        className: 'leaflet-tooltip-kec',
    });
    layer.on({
        mouseover: (e) => {
            e.target.setTooltipContent(mapTooltipContent(feature));
            if (currentMapMode !== 'kecamatan' || selectedKec !== nama) e.target.setStyle({ fillOpacity: 0.92, weight: 2 });
        },
        mouseout: (e) => {
            if (currentMapMode !== 'kecamatan' || selectedKec !== nama) geojsonLayer?.resetStyle(e.target);
        },
        click: () => {
            if (currentMapMode === 'kecamatan') selectKecamatan(nama);
        },
    });
}

function mapTooltipContent(feature) {
    const nama = featureName(feature);
    const item = kecamatanData[normalizeText(nama)];

    if (!item || !item.total) {
        return `<div class="map-tooltip"><span class="map-tooltip-title">${escapeHtml(nama)}</span><div class="map-tooltip-row"><span>Belum ada data</span><b>0</b></div></div>`;
    }

    const rows = (item.suara || [])
        .map((suara, index) => ({ label: currentMapLabels[index] || `Calon ${index + 1}`, suara }))
        .sort((a, b) => b.suara - a.suara)
        .slice(0, 5);

    return `
        <div class="map-tooltip">
            <span class="map-tooltip-title">${escapeHtml(nama)}</span>
            ${rows.map((row) => `
                <div class="map-tooltip-row">
                    <span>${escapeHtml(row.label)}</span>
                    <b>${formatNumber(row.suara)}</b>
                </div>
            `).join('')}
        </div>
    `;
}

fetch('/geojson/banyuwangi_kecamatan.geojson')
    .then((response) => response.json())
    .then((data) => {
        kecamatanGeojson = data;
        renderMapLayer('kecamatan');
    })
    .catch(() => showError('Peta kecamatan gagal dimuat.'));

fetch('/geojson/banyuwangi_desa_full.geojson')
    .then((response) => response.json())
    .then((data) => {
        desaGeojson = data;
        if (document.getElementById('f-level').value === 'kecamatan' && selectedKec) {
            renderMapLayer('desa');
            geojsonLayer?.setStyle(styleFeature);
        }
    })
    .catch(() => showError('Peta desa gagal dimuat.'));

function renderMapLayer(mode) {
    const source = mode === 'desa' ? desaGeojson : kecamatanGeojson;
    if (!source) return;

    if (geojsonLayer) map.removeLayer(geojsonLayer);
    currentMapMode = mode;

    geojsonLayer = L.geoJSON(source, {
        filter: (feature) => {
            if (mode !== 'desa') return true;
            return selectedKec && normalizeText(featureDistrict(feature)) === normalizeText(selectedKec);
        },
        style: styleFeature,
        onEachFeature,
    }).addTo(map);

    if (geojsonLayer.getLayers().length) {
        map.fitBounds(geojsonLayer.getBounds());
    }

    setTimeout(() => map.invalidateSize(), 0);
}

function selectKecamatan(namaKec) {
    const kec = allKecamatans.find((item) => normalizeText(item.nama) === normalizeText(namaKec));
    if (!kec) return;

    selectedKec = namaKec;
    document.getElementById('map-selected-label').textContent = `Kecamatan ${kec.nama}`;
    document.getElementById('wrap-reset-kec').classList.remove('hidden');
    document.getElementById('map-reset-btn').classList.remove('hidden');

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
        renderMapLayer('desa');
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
    const jenis = document.getElementById('f-jenis').value;
    const levelSelect = document.getElementById('f-level');
    levelSelect.value = jenis === 'dprd_kab' ? 'dapil' : 'kabupaten';
    document.getElementById('f-kec').value = '';
    document.getElementById('f-dapil').value = '';
    document.getElementById('f-desa').innerHTML = '<option value="">Pilih Desa</option>';
    document.getElementById('f-tps').innerHTML = '<option value="">Pilih TPS</option>';
    document.getElementById('wrap-dapil').classList.toggle('hidden', jenis !== 'dprd_kab');
    document.getElementById('wrap-kec').classList.add('hidden');
    document.getElementById('wrap-desa').classList.add('hidden');
    document.getElementById('wrap-tps').classList.add('hidden');
    document.getElementById('map-selected-label').textContent = 'Klik kecamatan untuk filter';
    document.getElementById('wrap-reset-kec').classList.add('hidden');
    document.getElementById('map-reset-btn').classList.add('hidden');
    renderMapLayer('kecamatan');
    hideCharts();

    if (levelSelect.value === 'kabupaten') loadChart();
}

function updateMapColors(payload) {
    const json = Array.isArray(payload) ? { data: payload, labels: [], jenis: document.getElementById('f-jenis').value } : payload;
    const data = json?.data || [];
    const level = document.getElementById('f-level').value;
    const mode = level === 'kecamatan' && selectedKec ? 'desa' : 'kecamatan';

    if (currentMapMode !== mode) {
        renderMapLayer(mode);
    }

    currentMapLabels = json?.labels || [];
    kecamatanData = {};
    data.forEach((item) => {
        const total = item.suara.reduce((sum, value) => sum + value, 0);
        const winnerIndex = isWinnerMapType(json?.jenis) && total > 0
            ? item.suara.reduce((bestIndex, value, index, values) => value > values[bestIndex] ? index : bestIndex, 0)
            : null;

        kecamatanData[normalizeText(item.label)] = { total, winnerIndex, suara: item.suara };
    });

    geojsonLayer?.setStyle(styleFeature);
    updateMapLegend(json);
}

function updateMapLegend(json) {
    const legend = document.getElementById('map-legend');
    const total = Object.values(kecamatanData).reduce((sum, item) => sum + (item.total || 0), 0);

    if (total <= 0) {
        legend.classList.add('hidden');
        return;
    }

    if (isWinnerMapType(json?.jenis)) {
        legend.innerHTML = `
            <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold mb-3">Legenda Pemenang</p>
            <div class="space-y-2">
                ${(json.labels || []).map((label, index) => `
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded" style="background:${WINNER_COLORS[index % WINNER_COLORS.length]}"></span>
                        <span class="text-xs text-slate-600">${escapeHtml(label)}</span>
                    </div>
                `).join('')}
            </div>
        `;
    } else {
        legend.innerHTML = `
            <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold mb-3">Legenda Suara</p>
            <div class="space-y-2">
                <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#fee2e2"></span><span class="text-xs text-slate-600">Rendah</span></div>
                <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#fca5a5"></span><span class="text-xs text-slate-600">Menengah rendah</span></div>
                <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#f87171"></span><span class="text-xs text-slate-600">Menengah</span></div>
                <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#ef4444"></span><span class="text-xs text-slate-600">Tinggi</span></div>
                <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#b91c1c"></span><span class="text-xs text-slate-600">Sangat tinggi</span></div>
            </div>
        `;
    }

    legend.classList.remove('hidden');
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
    updateJenisButtons();
    setDapilMode(jenis === 'dprd_kab');
    resetDependentFilters();
    hideCharts();

    if (jenis && jenis !== 'dprd_kab') loadChart();
}

function onLevelChange(shouldLoad = true) {
    const level = document.getElementById('f-level').value;
    const jenis = document.getElementById('f-jenis').value;

    if (level === 'kabupaten' || level === 'dapil') {
        selectedKec = null;
        document.getElementById('map-selected-label').textContent = 'Klik kecamatan untuk filter';
        document.getElementById('wrap-reset-kec').classList.add('hidden');
        document.getElementById('map-reset-btn').classList.add('hidden');
        renderMapLayer('kecamatan');
    }

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
    document.getElementById('map-reset-btn').classList.toggle('hidden', !kec);

    if (level === 'kecamatan') {
        renderMapLayer('desa');
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
    currentChartJson = null;
    document.getElementById('chart-placeholder').classList.remove('hidden');
    document.getElementById('chart-loading').classList.add('hidden');
    document.getElementById('chart-error').classList.add('hidden');
    document.getElementById('card-kandidat').classList.add('hidden');
    document.getElementById('card-quick-stats').classList.add('hidden');
    document.getElementById('card-demografi').classList.add('hidden');
    updateCandidateRanking(null);
    updateQuickStats([]);
    updateDemographics([]);
    updateDetailTable(null);
    updateMapColors(null);
}

function showError(message) {
    document.getElementById('chart-placeholder').classList.add('hidden');
    document.getElementById('chart-loading').classList.add('hidden');
    document.getElementById('card-kandidat').classList.add('hidden');
    document.getElementById('card-quick-stats').classList.add('hidden');
    document.getElementById('card-demografi').classList.add('hidden');
    updateCandidateRanking(null);
    updateQuickStats([]);
    updateDemographics([]);
    updateDetailTable(null);
    updateMapColors(null);
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
    document.getElementById('card-kandidat').classList.add('hidden');
    document.getElementById('card-quick-stats').classList.add('hidden');
    document.getElementById('card-demografi').classList.add('hidden');
    document.getElementById('chart-loading').classList.remove('hidden');

    const params = new URLSearchParams({ jenis, level });
    if (dapilId) params.set('dapil_id', dapilId);
    if (kecId) params.set('kecamatan_id', kecId);
    if (desaId) params.set('desa_id', desaId);
    if (tpsId) params.set('tps_id', tpsId);

    try {
        const res = await fetch(`${chartConfig.dataUrl}?${params}`);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const json = await res.json();
        currentChartJson = json;
        applyChartSearch();
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
        updateCandidateRanking(null);
        updateRanking([]);
        updateQuickStats([]);
        updateDemographics([]);
        updateDetailTable(null);
        return;
    }

    document.getElementById('chart-placeholder').classList.add('hidden');
    document.getElementById('chart-error').classList.add('hidden');
    document.getElementById('chart-loading').classList.add('hidden');

    updateStats(json);
    updateCandidateRanking(json);
    updateRanking(json.data);
    updateQuickStats(json.data);
    updateDemographics(json.data);
    updateDetailTable(json);
}

function updateCandidateRanking(json) {
    const card = document.getElementById('card-kandidat');
    const target = document.getElementById('candidate-rank-list');

    if (!json?.labels?.length || !json?.data?.length) {
        target.innerHTML = '<div class="px-5 py-4 text-sm text-slate-500">Belum ada data ditampilkan.</div>';
        return;
    }

    const rankSource = json.candidate_rank?.length
        ? json.candidate_rank
        : json.labels
            .map((label, index) => ({
                label,
                meta: '',
                suara: json.data.reduce((sum, item) => sum + (item.suara[index] || 0), 0),
            }))
            .sort((a, b) => b.suara - a.suara);
    const totalSuara = rankSource.reduce((sum, item) => sum + (Number(item.suara) || 0), 0);
    const term = normalizeText(document.getElementById('f-search')?.value || '');
    const isPartySearch = term.length > 0 && (json.labels || []).some((label) => normalizeText(label).includes(term));
    const rank = rankSource.slice(0, isPartySearch ? rankSource.length : 20);

    target.innerHTML = rank.map((item, index) => {
        const persen = totalSuara > 0 ? Math.round((item.suara / totalSuara) * 1000) / 10 : 0;
        const meta = item.meta ? `<span class="font-semibold text-slate-600">${escapeHtml(item.meta)}</span> &bull; ` : '';
        return `
            <div class="px-5 py-4 flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-slate-800 truncate">${index + 1}. ${escapeHtml(item.label)}</p>
                    <p class="text-xs text-slate-500 mt-0.5">${meta}${formatNumber(item.suara)} suara &bull; ${persen}%</p>
                </div>
            </div>
        `;
    }).join('');

    card.classList.remove('hidden');
}

function updateStats(payload) {
    const data = Array.isArray(payload) ? payload : (payload?.data || []);
    const totalSuara = data.reduce((sum, item) => sum + item.suara.reduce((a, b) => a + b, 0), 0);
    const totalDpt = data.reduce((sum, item) => sum + (item.partisipasi?.dpt || 0), 0);
    const totalHadir = data.reduce((sum, item) => sum + (item.partisipasi?.hadir || 0), 0);
    const totalTpsMasuk = data.reduce((sum, item) => sum + (item.partisipasi?.tps_masuk || 0), 0);
    const totalTps = data.reduce((sum, item) => sum + (item.partisipasi?.tps_total || 0), 0);
    const partisipasiPersen = totalDpt > 0 ? Math.round((totalHadir / totalDpt) * 1000) / 10 : 0;
    const tpsPersen = totalTps > 0 ? Math.round((totalTpsMasuk / totalTps) * 1000) / 10 : 0;

    let sortedTotals = [];
    if (!Array.isArray(payload) && payload?.candidate_rank?.length) {
        sortedTotals = payload.candidate_rank.map((item) => Number(item.suara) || 0).sort((a, b) => b - a);
    } else {
        const totalsByCandidate = [];
        data.forEach((item) => {
            item.suara.forEach((suara, index) => {
                totalsByCandidate[index] = (totalsByCandidate[index] || 0) + suara;
            });
        });
        sortedTotals = totalsByCandidate.sort((a, b) => b - a);
    }
    const topMargin = sortedTotals.length > 1 ? sortedTotals[0] - sortedTotals[1] : 0;
    const topMarginPersen = totalSuara > 0 ? Math.round((topMargin / totalSuara) * 1000) / 10 : 0;

    document.getElementById('stat-total-suara').textContent = formatNumber(totalSuara);
    document.getElementById('stat-tps-masuk').textContent = `${tpsPersen}%`;
    document.getElementById('stat-tps-detail').textContent = `${formatNumber(totalTpsMasuk)} / ${formatNumber(totalTps)} TPS`;
    document.getElementById('stat-partisipasi').textContent = `${partisipasiPersen}%`;
    document.getElementById('stat-partisipasi-detail').textContent = `${formatNumber(totalHadir)} hadir / ${formatNumber(totalDpt)} DPT`;
    document.getElementById('stat-selisih-teratas').textContent = `${topMarginPersen}%`;
    document.getElementById('stat-selisih-detail').textContent = `${formatNumber(topMargin)} suara`;
}

function updateDetailTable(json) {
    const target = document.getElementById('detail-table-body');
    const subtitle = document.getElementById('detail-table-subtitle');
    const subjectHeader = document.getElementById('detail-subject-header');

    if (!json?.data?.length) {
        target.innerHTML = '<tr><td colspan="5" class="px-5 py-5 text-center text-sm text-slate-500">Belum ada data ditampilkan.</td></tr>';
        subtitle.textContent = 'Data mengikuti filter aktif.';
        subjectHeader.textContent = 'Pemenang';
        return;
    }

    const levelLabels = {
        kabupaten: 'Kecamatan',
        dapil: 'Kecamatan',
        kecamatan: 'Desa',
        desa: 'TPS',
        tps: 'TPS',
    };
    const level = document.getElementById('f-level').value;
    const candidateMode = json.search_mode === 'candidate';
    subtitle.textContent = candidateMode
        ? `Perolehan suara caleg per ${levelLabels[level] || 'wilayah'} pada filter aktif.`
        : `Detail per ${levelLabels[level] || 'wilayah'} pada filter aktif.`;
    subjectHeader.textContent = candidateMode ? 'Caleg' : 'Pemenang';

    target.innerHTML = json.data.map((item) => {
        const totalSuara = item.suara.reduce((sum, value) => sum + value, 0);
        const winnerIndex = item.suara.reduce((bestIndex, value, index, values) => value > values[bestIndex] ? index : bestIndex, 0);
        const pemenang = candidateMode
            ? (json.labels.length === 1 ? json.labels[0] : (json.labels[winnerIndex] || '-'))
            : (totalSuara > 0 ? (json.labels[winnerIndex] || '-') : '-');
        const dpt = item.partisipasi?.dpt || 0;
        const hadir = item.partisipasi?.hadir || 0;
        const partisipasi = dpt > 0 ? Math.round((hadir / dpt) * 1000) / 10 : 0;
        const tpsMasuk = item.partisipasi?.tps_masuk || 0;
        const tpsTotal = item.partisipasi?.tps_total || 0;
        const tpsPersen = tpsTotal > 0 ? Math.round((tpsMasuk / tpsTotal) * 1000) / 10 : 0;

        return `
            <tr class="hover:bg-slate-50">
                <td class="px-5 py-4 font-bold text-slate-800">${escapeHtml(item.label)}</td>
                <td class="px-5 py-4 text-slate-600">${escapeHtml(pemenang)}</td>
                <td class="px-5 py-4 text-right font-mono-data font-bold text-[var(--primary)]">${formatNumber(totalSuara)}</td>
                <td class="px-5 py-4 text-right font-mono-data text-slate-700">${partisipasi}%</td>
                <td class="px-5 py-4 text-right font-mono-data text-slate-700">${tpsPersen}%</td>
            </tr>
        `;
    }).join('');
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

function updateQuickStats(data) {
    const card = document.getElementById('card-quick-stats');
    const defaults = {
        'quick-partisipasi': '-',
        'quick-dpt': '-',
        'quick-suara': '-',
        'quick-selisih': '-',
    };

    if (!data.length) {
        Object.entries(defaults).forEach(([id, value]) => document.getElementById(id).textContent = value);
        return;
    }

    const rows = data.map((item) => {
        const suaraTotal = item.suara.reduce((a, b) => a + b, 0);
        const sortedSuara = [...item.suara].sort((a, b) => b - a);
        const dpt = item.partisipasi?.dpt || 0;
        const hadir = item.partisipasi?.hadir || 0;

        return {
            label: item.label,
            suara: suaraTotal,
            dpt,
            persen: dpt > 0 ? (hadir / dpt) * 100 : 0,
            margin: sortedSuara.length > 1 ? sortedSuara[0] - sortedSuara[1] : null,
        };
    });

    const byPartisipasi = [...rows].sort((a, b) => b.persen - a.persen)[0];
    const byDpt = [...rows].sort((a, b) => b.dpt - a.dpt)[0];
    const bySuara = [...rows].sort((a, b) => b.suara - a.suara)[0];
    const byMargin = rows
        .filter((item) => item.margin !== null)
        .sort((a, b) => a.margin - b.margin)[0];

    document.getElementById('quick-partisipasi').textContent = byPartisipasi?.label || '-';
    document.getElementById('quick-dpt').textContent = byDpt?.label || '-';
    document.getElementById('quick-suara').textContent = bySuara?.label || '-';
    document.getElementById('quick-selisih').textContent = byMargin?.label || '-';
    card.classList.remove('hidden');
}

function updateDemographics(data) {
    const card = document.getElementById('card-demografi');
    const totalLk = data.reduce((sum, item) => sum + (item.partisipasi?.dpt_lk || 0), 0);
    const totalPr = data.reduce((sum, item) => sum + (item.partisipasi?.dpt_pr || 0), 0);
    const total = totalLk + totalPr;
    const persenLk = total > 0 ? Math.round((totalLk / total) * 1000) / 10 : 0;
    const persenPr = total > 0 ? Math.round((totalPr / total) * 1000) / 10 : 0;

    document.getElementById('demo-lk-label').textContent = `${persenLk}%`;
    document.getElementById('demo-pr-label').textContent = `${persenPr}%`;
    document.getElementById('demo-lk-bar').style.width = `${Math.min(100, persenLk)}%`;
    document.getElementById('demo-pr-bar').style.width = `${Math.min(100, persenPr)}%`;

    if (data.length) card.classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    const jenis = document.getElementById('f-jenis').value;
    updateJenisButtons();
    setDapilMode(jenis === 'dprd_kab');
    if (jenis && jenis !== 'dprd_kab') loadChart();
});

document.addEventListener('click', (event) => {
    if (!event.target.closest('#f-search') && !event.target.closest('#search-suggestions')) {
        hideSearchSuggestions();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') hideSearchSuggestions();
});

