import React, { useState, useEffect, useMemo, useRef } from 'react';
import { Head } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import {
    Vote,
    MapPin,
    Layers,
    Search,
    X,
    RefreshCw,
    Trophy,
    Building2,
    Users,
    CheckCircle2,
    BarChart3
} from 'lucide-react';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement
} from 'chart.js';
import { Bar, Pie } from 'react-chartjs-2';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement
);

function LeafletMapComponent({
    jenis,
    level,
    kecamatans,
    selectedKecId,
    selectedDesaId,
    onSelectKec,
    onSelectDesa,
    onResetMap,
    chartData
}) {
    const mapRef = useRef(null);
    const mapInstance = useRef(null);
    const geojsonLayerRef = useRef(null);

    const [kecamatanGeojson, setKecamatanGeojson] = useState(null);
    const [desaGeojson, setDesaGeojson] = useState(null);

    useEffect(() => {
        fetch('/geojson/banyuwangi_kecamatan.geojson')
            .then(res => res.json())
            .then(data => setKecamatanGeojson(data))
            .catch(err => console.error('Failed to load kecamatan geojson:', err));

        fetch('/geojson/banyuwangi_desa_full.geojson')
            .then(res => res.json())
            .then(data => setDesaGeojson(data))
            .catch(err => console.error('Failed to load desa geojson:', err));
    }, []);

    useEffect(() => {
        if (!mapRef.current || mapInstance.current) return;

        const map = L.map(mapRef.current, {
            center: [-8.35, 114.25],
            zoom: 10,
            zoomControl: true,
            attributionControl: false,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(map);

        mapInstance.current = map;

        return () => {
            if (mapInstance.current) {
                mapInstance.current.remove();
                mapInstance.current = null;
            }
        };
    }, []);

    const normalizeKey = (str) => (str || '').toString().toLowerCase().replace(/^(kecamatan|desa|kelurahan)\s+/i, '').replace(/[^a-z0-9]/g, '');

    const activeKecObj = kecamatans?.find(k => String(k.id) === String(selectedKecId));
    const activeKecName = activeKecObj?.nama || '';

    const activeDesaObj = activeKecObj?.desas?.find(d => String(d.id) === String(selectedDesaId));
    const activeDesaName = activeDesaObj?.nama || '';

    const getColor = (val, max) => {
        if (!max || max <= 0) return '#e2e8f0';
        const r = val / max;
        if (r > 0.8) return '#b91c1c';
        if (r > 0.6) return '#ef4444';
        if (r > 0.4) return '#f87171';
        if (r > 0.2) return '#fca5a5';
        return '#fee2e2';
    };

    const WINNER_MAP_TYPES = ['ppwp', 'gubernur', 'bupati'];
    const WINNER_COLORS = ['#e11d48', '#2563eb', '#059669', '#d97706', '#8b5cf6', '#0891b2'];
    const isWinnerType = WINNER_MAP_TYPES.includes(jenis);

    const regionData = useMemo(() => {
        const mapData = {};
        if (!chartData || !chartData.data) return mapData;

        chartData.data.forEach((item) => {
            const metricTotal = (item.suara || []).reduce((sum, val) => sum + val, 0);
            const total = Number(item.total_suara_basis) || metricTotal;
            const filteredWinnerIndex = metricTotal > 0
                ? item.suara.reduce((bestIndex, value, index, values) => value > values[bestIndex] ? index : bestIndex, 0)
                : null;
            const winnerIndex = isWinnerType && filteredWinnerIndex !== null
                ? (chartData.search_label_indexes?.[filteredWinnerIndex] ?? filteredWinnerIndex)
                : null;

            mapData[normalizeKey(item.label)] = {
                total,
                metricTotal,
                winnerIndex,
                suara: item.suara || [],
                label: item.label
            };
        });

        return mapData;
    }, [chartData, jenis, isWinnerType]);

    useEffect(() => {
        const map = mapInstance.current;
        if (!map) return;

        const useDesaMode = Boolean(selectedKecId && (level === 'desa' || level === 'kecamatan'));
        const sourceGeojson = useDesaMode ? desaGeojson : kecamatanGeojson;

        if (!sourceGeojson) return;

        if (geojsonLayerRef.current) {
            map.removeLayer(geojsonLayerRef.current);
        }

        const values = Object.values(regionData).map(item => item.metricTotal ?? item.total ?? 0);
        const maxSuara = values.length ? Math.max(...values) : 0;
        const currentLabels = chartData?.labels || [];

        const layer = L.geoJSON(sourceGeojson, {
            filter: (feature) => {
                if (!useDesaMode) return true;
                const featureDistrict = feature.properties?.sub_district || feature.properties?.nama || '';
                return normalizeKey(featureDistrict) === normalizeKey(activeKecName);
            },
            style: (feature) => {
                const featureName = useDesaMode
                    ? (feature.properties?.village || feature.properties?.nama || '')
                    : (feature.properties?.nama || feature.properties?.sub_district || '');
                const key = normalizeKey(featureName);
                const item = regionData[key] || { total: 0, metricTotal: 0, winnerIndex: null };
                const isSelected = useDesaMode
                    ? (activeDesaName && normalizeKey(activeDesaName) === key)
                    : (activeKecName && normalizeKey(activeKecName) === key);

                return {
                    fillColor: isWinnerType && item.winnerIndex !== null
                        ? WINNER_COLORS[item.winnerIndex % WINNER_COLORS.length]
                        : getColor(item.metricTotal ?? item.total, maxSuara),
                    fillOpacity: isSelected ? 0.88 : ((item.metricTotal ?? item.total) > 0 ? 0.78 : 0.38),
                    color: isSelected ? '#f59e0b' : '#94a3b8',
                    weight: isSelected ? 3 : 1,
                    opacity: 1,
                };
            },
            onEachFeature: (feature, featureLayer) => {
                const featureName = useDesaMode
                    ? (feature.properties?.village || feature.properties?.nama || '')
                    : (feature.properties?.nama || feature.properties?.sub_district || '');
                const key = normalizeKey(featureName);
                const item = regionData[key] || { total: 0, metricTotal: 0, suara: [] };

                const rows = (item.suara || [])
                    .map((suara, idx) => ({ label: currentLabels[idx] || `Kandidat ${idx + 1}`, suara }))
                    .sort((a, b) => b.suara - a.suara)
                    .slice(0, 5);
                const total = item.total || rows.reduce((sum, r) => sum + r.suara, 0);

                const tooltipHtml = `
                    <div style="min-width: 190px; padding: 6px 8px; font-family: sans-serif; font-size: 11px; background: #0f172a; color: #ffffff; border-radius: 8px;">
                        <b style="display: block; font-size: 12px; margin-bottom: 4px; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 3px;">${featureName}</b>
                        ${rows.length > 0 ? rows.map(r => `
                            <div style="display: flex; justify-content: space-between; gap: 8px; margin-top: 2px;">
                                <span style="opacity: 0.9;">${r.label}</span>
                                <b>${r.suara.toLocaleString('id-ID')} (${total > 0 ? ((r.suara / total) * 100).toFixed(1) : 0}%)</b>
                            </div>
                        `).join('') : '<div style="opacity: 0.7;">Belum ada data</div>'}
                    </div>
                `;

                featureLayer.bindTooltip(tooltipHtml, {
                    sticky: true,
                    direction: 'auto',
                    opacity: 0.95,
                });

                featureLayer.on({
                    click: () => {
                        if (!useDesaMode) {
                            const kecObj = kecamatans.find(k => normalizeKey(k.nama) === key);
                            if (kecObj) {
                                onSelectKec(String(kecObj.id));
                            }
                        } else {
                            const desaObj = activeKecObj?.desas?.find(d => normalizeKey(d.nama) === key);
                            if (desaObj) {
                                onSelectDesa(String(desaObj.id));
                            }
                        }
                    }
                });
            }
        }).addTo(map);

        geojsonLayerRef.current = layer;

        if (layer.getLayers().length > 0) {
            const bounds = layer.getBounds();
            if (bounds.isValid()) {
                map.fitBounds(bounds, { padding: [20, 20] });
            }
        }

        setTimeout(() => {
            map.invalidateSize();
        }, 100);
    }, [kecamatanGeojson, desaGeojson, selectedKecId, selectedDesaId, regionData, level, chartData, activeKecName, activeDesaName]);

    return (
        <div className="relative w-full h-[480px] rounded-2xl overflow-hidden border border-[#e2e8f0] dark:border-[#1e293b] shadow-xs bg-[#f8fafc] dark:bg-[#0b0f19]">
            <div ref={mapRef} className="w-full h-full z-10" />

            {/* Map Status Info Badge */}
            <div className="absolute top-4 left-4 z-20 bg-white/90 dark:bg-[#151f32]/90 backdrop-blur-md border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl px-3.5 py-2.5 shadow-md flex items-center gap-3">
                <div>
                    <p className="text-[10px] uppercase font-bold tracking-widest text-[#94a3b8]">Status Peta</p>
                    <p className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc]">
                        {activeDesaName
                            ? `Desa ${activeDesaName}`
                            : activeKecName
                            ? `Kecamatan ${activeKecName}`
                            : 'Kabupaten Banyuwangi'}
                    </p>
                </div>
                {(selectedKecId || selectedDesaId) && (
                    <button
                        onClick={onResetMap}
                        className="bg-[#f1f5f9] dark:bg-[#1f2937] hover:bg-red-50 text-[#bb152c] dark:text-red-400 text-[11px] font-bold px-2.5 py-1 rounded-lg border border-[#e2e8f0] dark:border-[#1e293b] transition cursor-pointer"
                    >
                        Reset Peta
                    </button>
                )}
            </div>

            {/* Map Legend Overlay at Bottom Left */}
            <div className="absolute bottom-4 left-4 z-20 bg-white/90 dark:bg-[#151f32]/90 backdrop-blur-md border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl p-3.5 shadow-md max-w-xs max-h-48 overflow-y-auto">
                <p className="text-[10px] uppercase font-bold tracking-widest text-[#94a3b8] mb-2">
                    {isWinnerType ? 'Legenda Pemenang' : 'Legenda Suara'}
                </p>
                {isWinnerType ? (
                    <div className="space-y-1.5">
                        {(chartData?.labels || []).map((label, index) => (
                            <div key={index} className="flex items-center gap-2">
                                <span
                                    className="w-3.5 h-3.5 rounded-md shrink-0"
                                    style={{ backgroundColor: WINNER_COLORS[index % WINNER_COLORS.length] }}
                                />
                                <span className="text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] truncate">
                                    {label}
                                </span>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="space-y-1.5 text-xs text-[#0f172a] dark:text-[#f8fafc]">
                        <div className="flex items-center gap-2"><span className="w-3.5 h-3.5 rounded-md shrink-0" style={{ background: '#fee2e2' }} /><span className="text-[11px]">Rendah</span></div>
                        <div className="flex items-center gap-2"><span className="w-3.5 h-3.5 rounded-md shrink-0" style={{ background: '#fca5a5' }} /><span className="text-[11px]">Menengah rendah</span></div>
                        <div className="flex items-center gap-2"><span className="w-3.5 h-3.5 rounded-md shrink-0" style={{ background: '#f87171' }} /><span className="text-[11px]">Menengah</span></div>
                        <div className="flex items-center gap-2"><span className="w-3.5 h-3.5 rounded-md shrink-0" style={{ background: '#ef4444' }} /><span className="text-[11px]">Tinggi</span></div>
                        <div className="flex items-center gap-2"><span className="w-3.5 h-3.5 rounded-md shrink-0" style={{ background: '#b91c1c' }} /><span className="text-[11px]">Sangat tinggi</span></div>
                    </div>
                )}
            </div>
        </div>
    );
}

export default function ChartPage({ kecamatans = [], dapils = [], aktifJenis = [], jenisLabels = {} }) {
    const defaultJenis = useMemo(() => {
        return aktifJenis?.length > 0 ? aktifJenis[0] : 'ppwp';
    }, [aktifJenis]);

    const [jenis, setJenis] = useState(defaultJenis);
    const [level, setLevel] = useState('kabupaten');
    const [selectedDapilId, setSelectedDapilId] = useState('');
    const [selectedKecId, setSelectedKecId] = useState('');
    const [selectedDesaId, setSelectedDesaId] = useState('');
    const [searchQuery, setSearchQuery] = useState('');
    const [loading, setLoading] = useState(false);
    const [rawChartData, setRawChartData] = useState(null);

    const handleJenisChange = (newJenis) => {
        setJenis(newJenis);
        setSelectedKecId('');
        setSelectedDesaId('');
        if (newJenis === 'dprd_kab') {
            setLevel('dapil');
            setSelectedDapilId(dapils?.[0]?.id ? String(dapils[0].id) : '');
        } else {
            setLevel('kabupaten');
            setSelectedDapilId('');
        }
    };

    const handleLevelChange = (newLevel) => {
        setLevel(newLevel);
        setSelectedKecId('');
        setSelectedDesaId('');
        if (newLevel !== 'dapil') {
            setSelectedDapilId('');
        }
    };

    const handleKecChange = (kecId) => {
        setSelectedKecId(kecId);
        setSelectedDesaId('');
        if (kecId && level === 'kabupaten') {
            setLevel('kecamatan');
        }
    };

    const handleResetMap = () => {
        setSelectedKecId('');
        setSelectedDesaId('');
        setLevel(jenis === 'dprd_kab' ? 'dapil' : 'kabupaten');
    };

    const loadData = async () => {
        setLoading(true);
        try {
            const params = {
                jenis,
                level,
                dapil_id: selectedDapilId || undefined,
                kecamatan_id: selectedKecId || undefined,
                desa_id: selectedDesaId || undefined,
            };
            const res = await axios.get('/admin/rekap/chart/data', { params });
            setRawChartData(res.data);
        } catch (err) {
            console.error('Failed to load chart data:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadData();
    }, [jenis, level, selectedDapilId, selectedKecId, selectedDesaId]);

    const activeKec = kecamatans?.find(k => String(k.id) === String(selectedKecId));
    const desas = activeKec?.desas || [];

    // Filter chart data by searchQuery
    const filteredChartData = useMemo(() => {
        if (!rawChartData || !rawChartData.data) return null;
        const term = searchQuery.trim().toLowerCase();
        if (!term) return rawChartData;

        const labels = rawChartData.labels || [];
        const searchMeta = rawChartData.search_meta || labels;
        const partyIndexes = labels
            .map((label, index) => ({ label, index }))
            .filter((item) => item.label.toLowerCase().includes(term))
            .map((item) => item.index);

        const candidateMatches = partyIndexes.length
            ? []
            : (rawChartData.candidate_series || [])
                .filter((item) => `${item.label} ${item.meta || ''}`.toLowerCase().includes(term));

        if (candidateMatches.length) {
            const candidateRank = candidateMatches
                .map((item) => ({
                    id: item.id,
                    label: item.label,
                    meta: item.meta || '',
                    suara: (item.suara || []).reduce((sum, val) => sum + (Number(val) || 0), 0),
                }))
                .sort((a, b) => b.suara - a.suara);

            return {
                ...rawChartData,
                search_mode: 'candidate',
                labels: candidateMatches.map((item) => item.label),
                search_meta: candidateMatches.map((item) => item.meta || ''),
                candidate_rank: candidateRank,
                data: rawChartData.data.map((item, groupIndex) => ({
                    ...item,
                    total_suara_basis: item.suara.reduce((sum, val) => sum + (Number(val) || 0), 0),
                    suara: candidateMatches.map((candidate) => candidate.suara?.[groupIndex] ?? 0),
                })),
            };
        }

        const indexes = partyIndexes.length
            ? partyIndexes
            : labels
                .map((label, index) => ({ label, index }))
                .filter((item) => `${item.label} ${searchMeta[item.index] || ''}`.toLowerCase().includes(term))
                .map((item) => item.index);
        const selectedParties = new Set(indexes.map((index) => labels[index].toLowerCase()));

        return {
            ...rawChartData,
            search_mode: partyIndexes.length ? 'party' : null,
            search_label_indexes: indexes,
            labels: indexes.map((index) => labels[index]),
            candidate_rank: partyIndexes.length
                ? rawChartData.candidate_rank?.filter((item) => selectedParties.has((item.meta || '').toLowerCase())) || []
                : rawChartData.candidate_rank?.filter((item) => `${item.label} ${item.meta || ''}`.toLowerCase().includes(term)) || [],
            data: rawChartData.data.map((item) => ({
                ...item,
                total_suara_basis: item.suara.reduce((sum, val) => sum + (Number(val) || 0), 0),
                suara: indexes.map((index) => item.suara[index] ?? 0),
            })),
        };
    }, [rawChartData, searchQuery]);

    // Compute KPI Stats
    const stats = useMemo(() => {
        const data = filteredChartData?.data || [];
        const totalSuara = data.reduce((sum, item) => sum + item.suara.reduce((a, b) => a + b, 0), 0);
        const totalDpt = data.reduce((sum, item) => sum + (item.partisipasi?.dpt || 0), 0);
        const totalHadir = data.reduce((sum, item) => sum + (item.partisipasi?.hadir || 0), 0);
        const totalTpsMasuk = data.reduce((sum, item) => sum + (item.partisipasi?.tps_masuk || 0), 0);
        const totalTps = data.reduce((sum, item) => sum + (item.partisipasi?.tps_total || 0), 0);
        const partisipasiPersen = totalDpt > 0 ? Math.round((totalHadir / totalDpt) * 1000) / 10 : 0;
        const tpsPersen = totalTps > 0 ? Math.round((totalTpsMasuk / totalTps) * 1000) / 10 : 0;

        let sortedTotals = [];
        if (filteredChartData?.candidate_rank?.length) {
            sortedTotals = filteredChartData.candidate_rank.map((item) => Number(item.suara) || 0).sort((a, b) => b - a);
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

        return {
            totalSuara,
            totalDpt,
            totalHadir,
            totalTpsMasuk,
            totalTps,
            partisipasiPersen,
            tpsPersen,
            topMargin,
            topMarginPersen,
        };
    }, [filteredChartData]);

    const chartConfig = useMemo(() => {
        const labels = filteredChartData?.labels || [];
        const data = filteredChartData?.data || [];
        const aggregated = labels.map((_, idx) => {
            return data.reduce((acc, curr) => acc + (curr.suara?.[idx] || 0), 0);
        });

        const palette = [
            '#bb152c', '#2563eb', '#059669', '#d97706', '#0891b2',
            '#db2777', '#7c3aed', '#475569', '#b45309', '#047857'
        ];

        return {
            type: filteredChartData?.type || 'bar',
            barData: {
                labels,
                datasets: [
                    {
                        label: 'Total Suara',
                        data: aggregated,
                        backgroundColor: 'rgba(187, 21, 44, 0.85)',
                        borderColor: '#bb152c',
                        borderWidth: 1,
                        borderRadius: 6,
                    }
                ]
            },
            pieData: {
                labels,
                datasets: [
                    {
                        data: aggregated,
                        backgroundColor: palette.slice(0, labels.length),
                        borderWidth: 1,
                    }
                ]
            }
        };
    }, [filteredChartData]);

    const formatNumber = (num) => (num || 0).toLocaleString('id-ID');

    return (
        <AdminLayout title="Grafik & Statistik">
            <Head title="Grafik & Statistik" />

            <div className="space-y-6">
                {/* Header Banner */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-xs">
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md">
                                Visualisasi Real-Time
                            </span>
                            <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                                Grafik & Peta Perolehan Suara
                            </h1>
                            <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                                Analisis data perolehan suara per jenis pemilihan dan hirarki wilayah Kabupaten Banyuwangi.
                            </p>
                        </div>

                        <button
                            onClick={loadData}
                            disabled={loading}
                            className="inline-flex items-center gap-2 bg-[#f1f5f9] dark:bg-[#1f2937] hover:bg-[#e2e8f0] dark:hover:bg-[#374151] text-[#0f172a] dark:text-[#f8fafc] px-4 py-2.5 rounded-xl text-xs font-semibold border border-[#e2e8f0] dark:border-[#1e293b] transition cursor-pointer"
                        >
                            <RefreshCw className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} />
                            <span>Refresh Data</span>
                        </button>
                    </div>
                </div>

                {/* Filters Row */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-xs space-y-5">
                    {/* Jenis Pemilihan Buttons */}
                    <div>
                        <label className="block text-xs font-bold uppercase tracking-wider text-[#94a3b8] mb-3">
                            Jenis Pemilihan Active
                        </label>
                        <div className="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
                            {Object.entries(jenisLabels).map(([key, label]) => {
                                const isAktif = aktifJenis.includes(key);
                                if (!isAktif) return null;

                                const active = jenis === key;
                                return (
                                    <button
                                        key={key}
                                        type="button"
                                        onClick={() => handleJenisChange(key)}
                                        className={`px-3 py-2.5 rounded-xl text-xs font-bold text-left leading-tight transition border cursor-pointer ${
                                            active
                                                ? 'bg-[#bb152c] text-white border-[#bb152c] shadow-sm'
                                                : 'bg-[#f8fafc] dark:bg-[#0b0f19] text-[#0f172a] dark:text-[#f8fafc] border-[#e2e8f0] dark:border-[#1e293b] hover:border-[#bb152c]'
                                        }`}
                                    >
                                        {label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* Level & Region Selectors & Search Input */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                        {/* Search Input */}
                        <div>
                            <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5 flex items-center gap-1.5">
                                <Search className="w-4 h-4 text-[#bb152c]" />
                                <span>Cari Partai / Caleg</span>
                            </label>
                            <div className="relative">
                                <input
                                    type="text"
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    placeholder="Ketik nama..."
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl pl-3 pr-8 py-2 text-xs outline-none focus:border-[#bb152c]"
                                />
                                {searchQuery && (
                                    <button
                                        onClick={() => setSearchQuery('')}
                                        className="absolute right-2.5 top-2.5 text-[#94a3b8] hover:text-[#0f172a]"
                                    >
                                        <X className="w-3.5 h-3.5" />
                                    </button>
                                )}
                            </div>
                        </div>

                        {/* Level Selector */}
                        <div>
                            <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5 flex items-center gap-1.5">
                                <Layers className="w-4 h-4 text-[#bb152c]" />
                                <span>Level Tampilan</span>
                            </label>
                            <select
                                value={level}
                                onChange={(e) => handleLevelChange(e.target.value)}
                                className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]"
                            >
                                <option value="kabupaten">Kabupaten</option>
                                {jenis === 'dprd_kab' && <option value="dapil">Dapil</option>}
                                <option value="kecamatan">Kecamatan</option>
                                <option value="desa">Desa</option>
                            </select>
                        </div>

                        {/* Dapil / Kecamatan Filter */}
                        {level === 'dapil' || jenis === 'dprd_kab' ? (
                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5 flex items-center gap-1.5">
                                    <Building2 className="w-4 h-4 text-[#bb152c]" />
                                    <span>Dapil</span>
                                </label>
                                <select
                                    value={selectedDapilId}
                                    onChange={(e) => setSelectedDapilId(e.target.value)}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]"
                                >
                                    <option value="">Pilih Dapil</option>
                                    {dapils.map((d) => (
                                        <option key={d.id} value={d.id}>{d.nama}</option>
                                    ))}
                                </select>
                            </div>
                        ) : (
                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5 flex items-center gap-1.5">
                                    <MapPin className="w-4 h-4 text-[#bb152c]" />
                                    <span>Kecamatan</span>
                                </label>
                                <select
                                    value={selectedKecId}
                                    onChange={(e) => handleKecChange(e.target.value)}
                                    disabled={level === 'kabupaten'}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c] disabled:opacity-50"
                                >
                                    <option value="">Pilih Kecamatan</option>
                                    {kecamatans.map((k) => (
                                        <option key={k.id} value={k.id}>{k.nama}</option>
                                    ))}
                                </select>
                            </div>
                        )}

                        {/* Desa Filter */}
                        <div>
                            <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5 flex items-center gap-1.5">
                                <Building2 className="w-4 h-4 text-[#bb152c]" />
                                <span>Desa / Kelurahan</span>
                            </label>
                            <select
                                value={selectedDesaId}
                                onChange={(e) => setSelectedDesaId(e.target.value)}
                                disabled={level !== 'desa' || !selectedKecId}
                                className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c] disabled:opacity-50"
                            >
                                <option value="">Pilih Desa</option>
                                {desas.map((d) => (
                                    <option key={d.id} value={d.id}>{d.nama}</option>
                                ))}
                            </select>
                        </div>
                    </div>
                </div>

                {/* KPI Metrics Cards Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs">
                        <p className="text-[10px] uppercase font-bold tracking-widest text-[#94a3b8]">Total Suara Sah</p>
                        <p className="text-2xl font-extrabold font-mono text-[#0f172a] dark:text-[#f8fafc] mt-2">
                            {formatNumber(stats.totalSuara)}
                        </p>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">suara terhitung</p>
                    </div>

                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs">
                        <p className="text-[10px] uppercase font-bold tracking-widest text-[#94a3b8]">TPS Masuk</p>
                        <p className="text-2xl font-extrabold font-mono text-[#0f172a] dark:text-[#f8fafc] mt-2">
                            {stats.tpsPersen}%
                        </p>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                            {formatNumber(stats.totalTpsMasuk)} / {formatNumber(stats.totalTps)} TPS
                        </p>
                    </div>

                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs">
                        <p className="text-[10px] uppercase font-bold tracking-widest text-[#94a3b8]">Partisipasi</p>
                        <p className="text-2xl font-extrabold font-mono text-[#bb152c] dark:text-red-400 mt-2">
                            {stats.partisipasiPersen}%
                        </p>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                            {formatNumber(stats.totalHadir)} hadir / {formatNumber(stats.totalDpt)} DPT
                        </p>
                    </div>

                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs">
                        <p className="text-[10px] uppercase font-bold tracking-widest text-[#94a3b8]">Selisih Teratas</p>
                        <p className="text-2xl font-extrabold font-mono text-[#0f172a] dark:text-[#f8fafc] mt-2">
                            {stats.topMarginPersen}%
                        </p>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                            {formatNumber(stats.topMargin)} suara margin
                        </p>
                    </div>
                </div>

                {/* Leaflet Spatial Map */}
                <LeafletMapComponent
                    jenis={jenis}
                    level={level}
                    kecamatans={kecamatans}
                    selectedKecId={selectedKecId}
                    selectedDesaId={selectedDesaId}
                    onSelectKec={handleKecChange}
                    onSelectDesa={(desaId) => {
                        setSelectedDesaId(desaId);
                        setLevel('desa');
                    }}
                    onResetMap={handleResetMap}
                    chartData={filteredChartData}
                />

                {/* Main Visualizations & Leaderboard Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Left 2 Cols: Main Chart */}
                    <div className="lg:col-span-2 bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-xs">
                        <div className="flex items-center justify-between pb-4 mb-4 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <div>
                                <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc]">
                                    Grafik Suara Masuk
                                </h3>
                                <p className="text-xs text-[#475569] dark:text-[#94a3b8]">
                                    {jenisLabels[jenis] || jenis.toUpperCase()} · Total: {formatNumber(stats.totalSuara)} Suara
                                </p>
                            </div>
                        </div>

                        {loading ? (
                            <div className="h-72 flex items-center justify-center text-xs text-[#94a3b8]">
                                <RefreshCw className="w-5 h-5 animate-spin mr-2" />
                                <span>Memuat data grafik...</span>
                            </div>
                        ) : filteredChartData?.data?.length > 0 ? (
                            <div className="h-80 relative flex items-center justify-center">
                                {chartConfig.type === 'pie' ? (
                                    <Pie data={chartConfig.pieData} options={{ responsive: true, maintainAspectRatio: false }} />
                                ) : (
                                    <Bar data={chartConfig.barData} options={{ responsive: true, maintainAspectRatio: false }} />
                                )}
                            </div>
                        ) : (
                            <div className="h-72 flex items-center justify-center text-xs text-[#94a3b8]">
                                Belum ada data suara untuk filter yang dipilih.
                            </div>
                        )}
                    </div>

                    {/* Right Col: Leaderboard Candidate Rank */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-xs flex flex-col justify-between">
                        <div>
                            <div className="flex items-center gap-2 pb-3 mb-4 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                                <Trophy className="w-5 h-5 text-amber-500" />
                                <h3 className="font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] text-base">
                                    Peringkat Suara Teratas
                                </h3>
                            </div>

                            <div className="space-y-2.5 max-h-80 overflow-y-auto pr-1">
                                {filteredChartData?.candidate_rank?.length > 0 ? (
                                    filteredChartData.candidate_rank.slice(0, 15).map((item, idx) => {
                                        const persen = stats.totalSuara > 0
                                            ? Math.round(((item.suara || 0) / stats.totalSuara) * 1000) / 10
                                            : 0;

                                        return (
                                            <div
                                                key={idx}
                                                className="bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl p-3 flex items-center justify-between"
                                            >
                                                <div className="flex items-center gap-3 min-w-0">
                                                    <span className="w-6 h-6 rounded-full bg-[#f1f5f9] dark:bg-[#1f2937] text-xs font-bold flex items-center justify-center text-[#0f172a] dark:text-[#f8fafc] shrink-0">
                                                        {idx + 1}
                                                    </span>
                                                    <div className="min-w-0">
                                                        <p className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] leading-tight truncate">
                                                            {item.label}
                                                        </p>
                                                        {item.meta && (
                                                            <p className="text-[10px] text-[#94a3b8] truncate">{item.meta}</p>
                                                        )}
                                                    </div>
                                                </div>
                                                <div className="text-right shrink-0">
                                                    <p className="text-xs font-bold text-[#bb152c] dark:text-red-400">
                                                        {formatNumber(item.suara)}
                                                    </p>
                                                    <p className="text-[10px] text-[#94a3b8]">{persen}%</p>
                                                </div>
                                            </div>
                                        );
                                    })
                                ) : (
                                    <div className="text-center py-10 text-xs text-[#94a3b8]">
                                        Tidak ada peringkat data.
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Detail Breakdown Table */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-xs overflow-hidden">
                    <div className="p-6 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                        <h3 className="font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] text-base">
                            Tabel Detail Wilayah
                        </h3>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-0.5">
                            Rincian partisipasi dan hasil suara per wilayah mengikuti filter aktif.
                        </p>
                    </div>

                    <div className="overflow-x-auto max-h-80">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-[#f8fafc] dark:bg-[#0b0f19] text-[#94a3b8] uppercase tracking-wider font-bold border-b border-[#e2e8f0] dark:border-[#1e293b]">
                                <tr>
                                    <th className="px-6 py-3">Wilayah</th>
                                    <th className="px-6 py-3 text-right">Total Suara Sah</th>
                                    <th className="px-6 py-3 text-right">Hadir / DPT</th>
                                    <th className="px-6 py-3 text-right">Partisipasi</th>
                                    <th className="px-6 py-3 text-right">TPS Masuk</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#f1f5f9] dark:divide-[#1e293b]">
                                {filteredChartData?.data?.length > 0 ? (
                                    filteredChartData.data.map((row, idx) => {
                                        const sumSuara = row.suara?.reduce((a, b) => a + b, 0) || 0;
                                        const dpt = row.partisipasi?.dpt || 0;
                                        const hadir = row.partisipasi?.hadir || 0;
                                        const partPersen = dpt > 0 ? Math.round((hadir / dpt) * 1000) / 10 : 0;
                                        const tpsMasuk = row.partisipasi?.tps_masuk || 0;
                                        const tpsTotal = row.partisipasi?.tps_total || 0;

                                        return (
                                            <tr key={idx} className="hover:bg-[#f8fafc] dark:hover:bg-[#0b0f19]/50">
                                                <td className="px-6 py-3.5 font-bold text-[#0f172a] dark:text-[#f8fafc]">{row.label}</td>
                                                <td className="px-6 py-3.5 text-right font-mono text-[#0f172a] dark:text-[#f8fafc] font-semibold">{formatNumber(sumSuara)}</td>
                                                <td className="px-6 py-3.5 text-right text-[#475569] dark:text-[#94a3b8]">{formatNumber(hadir)} / {formatNumber(dpt)}</td>
                                                <td className="px-6 py-3.5 text-right font-bold text-[#bb152c] dark:text-red-400">{partPersen}%</td>
                                                <td className="px-6 py-3.5 text-right text-[#475569] dark:text-[#94a3b8]">{formatNumber(tpsMasuk)} / {formatNumber(tpsTotal)}</td>
                                            </tr>
                                        );
                                    })
                                ) : (
                                    <tr>
                                        <td colSpan={5} className="px-6 py-6 text-center text-[#94a3b8]">
                                            Belum ada data detail wilayah.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
