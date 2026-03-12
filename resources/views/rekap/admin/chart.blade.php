@extends('layouts.app')
@section('title', 'Grafik & Statistik')

@section('content')
<div class="mb-8">
    <a href="{{ route('dashboard.admin') }}"
       class="inline-flex items-center gap-2 text-xs dark:text-gray-500 text-gray-400 hover:text-red-500 transition font-medium mb-4">
        ← Kembali ke Dashboard
    </a>
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase mb-2 font-semibold">// Admin — Statistik</p>
    <h1 class="font-display text-4xl tracking-[2px] text-red-600">GRAFIK & STATISTIK</h1>
    <p class="dark:text-gray-400 text-gray-500 text-sm mt-1">Visualisasi data rekapitulasi pemilu.</p>
</div>

{{-- Filter Panel --}}
<div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm mb-6 p-6">
    <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold mb-4">// Filter Data</p>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Jenis --}}
        <div>
            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Jenis Pemilihan</label>
            <select id="f-jenis" onchange="onJenisChange()"
                    class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-3 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                <option value="">— Pilih —</option>
                @foreach(\App\Models\RekapHeader::JENIS_LABELS as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Level --}}
        <div>
            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Level</label>
            <select id="f-level" onchange="onLevelChange()"
                    class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-3 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                <option value="kabupaten">Kabupaten</option>
                <option value="kecamatan">Kecamatan</option>
                <option value="desa">Desa</option>
                <option value="tps">TPS</option>
            </select>
        </div>

        {{-- Dapil (hanya muncul untuk dprd_kab) --}}
        <div id="wrap-dapil" class="hidden">
            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Dapil</label>
            <select id="f-dapil" onchange="onDapilChange()"
                    class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-3 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                <option value="">— Pilih Dapil —</option>
                @foreach($dapils as $dapil)
                <option value="{{ $dapil->id }}">{{ $dapil->nama }}</option>
                @endforeach
            </select>
        </div>

        {{-- Kecamatan --}}
        <div id="wrap-kec" class="hidden">
            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Kecamatan</label>
            <select id="f-kec" onchange="onKecChange()"
                    class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-3 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                <option value="">— Pilih —</option>
                @foreach($kecamatans as $kec)
                <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                @endforeach
            </select>
        </div>

        {{-- Desa --}}
        <div id="wrap-desa" class="hidden">
            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">Desa</label>
            <select id="f-desa" onchange="onDesaChange()"
                    class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-3 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                <option value="">— Pilih —</option>
            </select>
        </div>

        {{-- TPS --}}
        <div id="wrap-tps" class="hidden">
            <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-2">TPS</label>
            <select id="f-tps" onchange="loadChart()"
                    class="w-full dark:bg-gray-900 bg-gray-50 border dark:border-gray-700 border-gray-300 dark:text-gray-300 text-gray-600 px-3 py-2.5 text-sm rounded-lg focus:border-red-500 focus:ring-0 focus:outline-none">
                <option value="">— Pilih —</option>
            </select>
        </div>

    </div>
</div>

{{-- Chart Area --}}
<div id="chart-placeholder" class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm p-16 text-center">
    <p class="text-4xl mb-3">📊</p>
    <p class="dark:text-gray-500 text-gray-400 text-sm">Pilih jenis pemilihan untuk menampilkan grafik</p>
</div>

<div id="chart-area" class="hidden space-y-6">

    {{-- Chart Perolehan Suara --}}
    <div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b dark:border-gray-700 border-gray-200">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Perolehan Suara</p>
            <p id="chart-subtitle" class="text-xs dark:text-gray-400 text-gray-500 mt-1"></p>
        </div>
        <div class="p-6">
            <div class="relative" style="height: 380px">
                <canvas id="chartSuara"></canvas>
            </div>
        </div>
    </div>

    {{-- Chart Partisipasi --}}
    <div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b dark:border-gray-700 border-gray-200">
            <p class="text-[10px] tracking-[3px] dark:text-gray-500 text-gray-400 uppercase font-semibold">// Tingkat Partisipasi</p>
            <p class="text-xs dark:text-gray-400 text-gray-500 mt-1">DPT vs Pengguna Hak Pilih</p>
        </div>
        <div class="p-6">
            <div class="relative" style="height: 300px">
                <canvas id="chartPartisipasi"></canvas>
            </div>
        </div>
    </div>

</div>

{{-- Loading --}}
<div id="chart-loading" class="hidden dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 shadow-sm p-16 text-center">
    <div class="inline-block w-8 h-8 border-2 border-red-500 border-t-transparent rounded-full animate-spin mb-3"></div>
    <p class="dark:text-gray-500 text-gray-400 text-sm">Memuat data...</p>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
    const allDesas       = @json($kecamatans->flatMap(fn($k) => $k->desas->map(fn($d) => ['id'=>$d->id,'nama'=>$d->nama,'kecamatan_id'=>$k->id]))->values());
    const allDapils      = @json($dapils->map(fn($d) => ['id'=>$d->id,'nama'=>$d->nama])->values());
    const dapilKecamatans = @json($dapils->map(fn($d) => ['dapil_id'=>$d->id,'kecamatans'=>$d->kecamatans->map(fn($k)=>['id'=>$k->id,'nama'=>$k->nama])->values()])->values());
    const allTps   = @json($kecamatans->flatMap(fn($k) => $k->desas->flatMap(fn($d) => $d->tps->map(fn($t) => ['id'=>$t->id,'nama'=>$t->nama,'desa_id'=>$d->id])))->values());
    const isDark   = () => document.documentElement.classList.contains('dark');

    let chartSuara       = null;
    let chartPartisipasi = null;

    const COLORS_PIE = ['#EF4444','#3B82F6','#10B981','#F59E0B','#8B5CF6','#EC4899','#06B6D4','#84CC16','#F97316','#6366F1'];
    const COLORS_BAR = '#EF4444';

    function gridColor()  { return isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)'; }
    function textColor()  { return isDark() ? '#9CA3AF' : '#6B7280'; }

    function onJenisChange() {
        const jenis = document.getElementById('f-jenis').value;
        const fLevel = document.getElementById('f-level');

        // Kalau dprd_kab, ganti opsi level kabupaten → dapil
        const kabOpt = fLevel.querySelector('option[value="kabupaten"]');
        if (jenis === 'dprd_kab') {
            kabOpt.value = 'dapil';
            kabOpt.textContent = 'Dapil';
        } else {
            kabOpt.value = 'kabupaten';
            kabOpt.textContent = 'Kabupaten';
        }

        // Reset semua filter wilayah
        fLevel.value = (jenis === 'dprd_kab') ? 'dapil' : 'kabupaten';
        document.getElementById('f-kec').value = '';
        document.getElementById('f-dapil').value = '';
        document.getElementById('f-desa').innerHTML = '<option value="">— Pilih —</option>';
        document.getElementById('f-tps').innerHTML  = '<option value="">— Pilih —</option>';
        document.getElementById('wrap-dapil').classList.toggle('hidden', jenis !== 'dprd_kab');
        document.getElementById('wrap-kec').classList.add('hidden');
        document.getElementById('wrap-desa').classList.add('hidden');
        document.getElementById('wrap-tps').classList.add('hidden');
        hideCharts();
    }

    function onLevelChange() {
        const level = document.getElementById('f-level').value;
        const jenis = document.getElementById('f-jenis').value;

        document.getElementById('wrap-dapil').classList.toggle('hidden', !(level === 'dapil' || jenis === 'dprd_kab'));
        document.getElementById('wrap-kec').classList.toggle('hidden',  level === 'kabupaten' || level === 'dapil');
        document.getElementById('wrap-desa').classList.toggle('hidden', !['desa','tps'].includes(level));
        document.getElementById('wrap-tps').classList.toggle('hidden',  level !== 'tps');

        document.getElementById('f-kec').value = '';
        document.getElementById('f-dapil').value = '';
        document.getElementById('f-desa').innerHTML = '<option value="">— Pilih —</option>';
        document.getElementById('f-tps').innerHTML  = '<option value="">— Pilih —</option>';
        hideCharts();

        if (level === 'kabupaten') loadChart();
    }

    function onDapilChange() {
        const dapilId = document.getElementById('f-dapil').value;
        hideCharts();
        if (!dapilId) return;
        loadChart();
    }

    function onKecChange() {
        const level = document.getElementById('f-level').value;
        const kecId = document.getElementById('f-kec').value;
        document.getElementById('f-desa').innerHTML = '<option value="">— Pilih —</option>';
        document.getElementById('f-tps').innerHTML  = '<option value="">— Pilih —</option>';
        hideCharts();

        if (!kecId) return;
        if (level === 'kecamatan') { loadChart(); return; }

        // Load desa
        const desas = allDesas.filter(d => d.kecamatan_id == kecId);
        const sel   = document.getElementById('f-desa');
        desas.forEach(d => sel.innerHTML += `<option value="${d.id}">${d.nama}</option>`);
        document.getElementById('wrap-desa').classList.remove('hidden');
    }

    function onDesaChange() {
        const level  = document.getElementById('f-level').value;
        const desaId = document.getElementById('f-desa').value;
        document.getElementById('f-tps').innerHTML = '<option value="">— Pilih —</option>';
        hideCharts();

        if (!desaId) return;
        if (level === 'desa') { loadChart(); return; }

        // Load TPS
        const tpsList = allTps.filter(t => t.desa_id == desaId);
        const sel     = document.getElementById('f-tps');
        tpsList.forEach(t => sel.innerHTML += `<option value="${t.id}">${t.nama}</option>`);
        document.getElementById('wrap-tps').classList.remove('hidden');
    }

    function hideCharts() {
        document.getElementById('chart-area').classList.add('hidden');
        document.getElementById('chart-placeholder').classList.remove('hidden');
        document.getElementById('chart-loading').classList.add('hidden');
    }

    async function loadChart() {
        const jenis = document.getElementById('f-jenis').value;
        if (!jenis) return;

        const level  = document.getElementById('f-level').value;
        const kecId  = document.getElementById('f-kec').value;
        const desaId = document.getElementById('f-desa').value;
        const tpsId  = document.getElementById('f-tps').value;

        // Validasi wilayah sesuai level
        if (level === 'dapil'     && !dapilId) return;
        if (level === 'kecamatan' && !kecId)   return;
        if (level === 'desa'      && !desaId)  return;
        if (level === 'tps'       && !tpsId)   return;

        // Show loading
        document.getElementById('chart-placeholder').classList.add('hidden');
        document.getElementById('chart-area').classList.add('hidden');
        document.getElementById('chart-loading').classList.remove('hidden');

        const dapilId = document.getElementById('f-dapil').value;
        const params  = new URLSearchParams({ jenis, level });
        if (dapilId) params.set('dapil_id', dapilId);
        if (kecId)   params.set('kecamatan_id', kecId);
        if (desaId)  params.set('desa_id', desaId);
        if (tpsId)   params.set('tps_id', tpsId);

        try {
            const res  = await fetch('{{ route('admin.rekap.chart.data') }}?' + params);
            const json = await res.json();
            renderCharts(json);
        } catch(e) {
            console.error(e);
        } finally {
            document.getElementById('chart-loading').classList.add('hidden');
            document.getElementById('chart-area').classList.remove('hidden');
        }
    }

    function renderCharts(json) {
        const wilayahLabels = json.data.map(d => d.label);
        const isPie = json.type === 'pie' && json.data.length === 1;

        // Subtitle
        document.getElementById('chart-subtitle').textContent =
            json.data.length === 1 ? json.data[0].label : `${json.data.length} wilayah`;

        // ── Chart Suara ──
        if (chartSuara) chartSuara.destroy();
        const ctxSuara = document.getElementById('chartSuara').getContext('2d');

        if (isPie) {
            // Pie: 1 wilayah, label = calon/partai
            chartSuara = new Chart(ctxSuara, {
                type: 'doughnut',
                data: {
                    labels: json.labels,
                    datasets: [{
                        data: json.data[0].suara,
                        backgroundColor: COLORS_PIE,
                        borderWidth: 2,
                        borderColor: isDark() ? '#1F2937' : '#FFFFFF',
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { color: textColor(), font: { size: 11 }, padding: 16 } },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed.toLocaleString()} suara` } }
                    }
                }
            });
        } else {
            // Bar: multi wilayah atau partai
            const isMultiWilayah = json.data.length > 1;
            let datasets;

            if (isMultiWilayah) {
                // Setiap calon/partai = 1 dataset
                datasets = json.labels.map((lbl, i) => ({
                    label: lbl,
                    data: json.data.map(d => d.suara[i] ?? 0),
                    backgroundColor: COLORS_PIE[i % COLORS_PIE.length],
                    borderRadius: 4,
                }));
            } else {
                // Single wilayah, bar per calon/partai
                datasets = [{
                    label: 'Suara',
                    data: json.data[0].suara,
                    backgroundColor: COLORS_PIE,
                    borderRadius: 4,
                }];
            }

            chartSuara = new Chart(ctxSuara, {
                type: 'bar',
                data: { labels: isMultiWilayah ? wilayahLabels : json.labels, datasets },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: isMultiWilayah, labels: { color: textColor(), font: { size: 11 } } },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString()}` } }
                    },
                    scales: {
                        x: { ticks: { color: textColor(), font: { size: 11 } }, grid: { color: gridColor() } },
                        y: { ticks: { color: textColor() }, grid: { color: gridColor() }, beginAtZero: true }
                    }
                }
            });
        }

        // ── Chart Partisipasi ──
        if (chartPartisipasi) chartPartisipasi.destroy();
        const ctxPart = document.getElementById('chartPartisipasi').getContext('2d');
        chartPartisipasi = new Chart(ctxPart, {
            type: 'bar',
            data: {
                labels: wilayahLabels,
                datasets: [
                    {
                        label: 'DPT',
                        data: json.data.map(d => d.partisipasi.dpt),
                        backgroundColor: 'rgba(107,114,128,0.5)',
                        borderRadius: 4,
                    },
                    {
                        label: 'Hadir',
                        data: json.data.map(d => d.partisipasi.hadir),
                        backgroundColor: '#EF4444',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: textColor(), font: { size: 11 } } },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString()}` } }
                },
                scales: {
                    x: { ticks: { color: textColor(), font: { size: 11 } }, grid: { color: gridColor() } },
                    y: { ticks: { color: textColor() }, grid: { color: gridColor() }, beginAtZero: true }
                }
            }
        });
    }
</script>
@endpush
@endsection