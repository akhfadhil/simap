import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import axios from 'axios';
import {
    BarChart3,
    Filter,
    Layers,
    MapPin,
    Trophy,
    Vote,
    TrendingUp,
    RefreshCw
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

export default function ChartPage({ kecamatans, dapils }) {
    const [jenis, setJenis] = useState('ppwp');
    const [level, setLevel] = useState('kabupaten');
    const [selectedKecId, setSelectedKecId] = useState('');
    const [selectedDesaId, setSelectedDesaId] = useState('');
    const [loading, setLoading] = useState(false);
    const [chartData, setChartData] = useState(null);

    const jenisOptions = [
        { key: 'ppwp', label: 'PPWP (President)' },
        { key: 'gubernur', label: 'Pilgub (Gubernur)' },
        { key: 'bupati', label: 'Pilbup (Bupati)' },
        { key: 'dpd', label: 'DPD RI' },
        { key: 'dpr_ri', label: 'DPR RI' },
        { key: 'dprd_prov', label: 'DPRD Provinsi' },
        { key: 'dprd_kab', label: 'DPRD Kabupaten' },
    ];

    const loadData = async () => {
        setLoading(true);
        try {
            const res = await axios.get('/admin/rekap/chart/data', {
                params: {
                    jenis,
                    level: selectedDesaId ? 'desa' : selectedKecId ? 'kecamatan' : 'kabupaten',
                    kecamatan_id: selectedKecId || undefined,
                    desa_id: selectedDesaId || undefined,
                }
            });
            setChartData(res.data);
        } catch (err) {
            console.error('Failed to load chart data:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadData();
    }, [jenis, selectedKecId, selectedDesaId]);

    const activeKec = kecamatans?.find(k => k.id === parseInt(selectedKecId));
    const desas = activeKec?.desas || [];

    // Calculate Totals & Stats
    const totalSuara = chartData?.data?.reduce((acc, curr) => {
        const sum = Array.isArray(curr.suara) ? curr.suara.reduce((a, b) => a + b, 0) : 0;
        return acc + sum;
    }, 0) || 0;

    const barChartConfig = {
        labels: chartData?.labels || [],
        datasets: [
            {
                label: 'Total Suara',
                data: chartData?.labels?.map((_, idx) => {
                    return chartData?.data?.reduce((acc, curr) => acc + (curr.suara?.[idx] || 0), 0) || 0;
                }) || [],
                backgroundColor: 'rgba(187, 21, 44, 0.85)',
                borderColor: '#bb152c',
                borderWidth: 1,
                borderRadius: 8,
            }
        ]
    };

    const pieChartConfig = {
        labels: chartData?.labels || [],
        datasets: [
            {
                data: chartData?.labels?.map((_, idx) => {
                    return chartData?.data?.reduce((acc, curr) => acc + (curr.suara?.[idx] || 0), 0) || 0;
                }) || [],
                backgroundColor: [
                    '#bb152c', '#4f46e5', '#10b981', '#f59e0b', '#06b6d4',
                    '#ec4899', '#8b5cf6', '#64748b', '#d97706', '#059669'
                ],
                borderWidth: 1,
            }
        ]
    };

    return (
        <AdminLayout title="Grafik & Peta Statistik">
            <Head title="Grafik & Peta Statistik" />

            <div className="space-y-6">
                {/* Header Banner */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_10px_15px_-3px_rgba(0,0,0,0.3)]">
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md">
                                Visualisasi Real-Time
                            </span>
                            <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                                Grafik & Peta Perolehan Suara
                            </h1>
                            <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                                Analisis data perolehan suara per jenis pemilihan dan wilayah kecamatan/desa.
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

                {/* Filter Bar Grid */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {/* Filter Jenis Pemilihan */}
                    <div>
                        <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5 flex items-center gap-1.5">
                            <Vote className="w-4 h-4 text-[#bb152c]" />
                            <span>Jenis Pemilihan</span>
                        </label>
                        <select
                            value={jenis}
                            onChange={(e) => setJenis(e.target.value)}
                            className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]"
                        >
                            {jenisOptions.map(opt => (
                                <option key={opt.key} value={opt.key}>{opt.label}</option>
                            ))}
                        </select>
                    </div>

                    {/* Filter Kecamatan */}
                    <div>
                        <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5 flex items-center gap-1.5">
                            <MapPin className="w-4 h-4 text-[#bb152c]" />
                            <span>Kecamatan</span>
                        </label>
                        <select
                            value={selectedKecId}
                            onChange={(e) => {
                                setSelectedKecId(e.target.value);
                                setSelectedDesaId('');
                            }}
                            className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]"
                        >
                            <option value="">Semua Kecamatan (Kabupaten)</option>
                            {kecamatans?.map(kec => (
                                <option key={kec.id} value={kec.id}>{kec.nama}</option>
                            ))}
                        </select>
                    </div>

                    {/* Filter Desa */}
                    <div>
                        <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5 flex items-center gap-1.5">
                            <Layers className="w-4 h-4 text-[#bb152c]" />
                            <span>Desa / Kelurahan</span>
                        </label>
                        <select
                            value={selectedDesaId}
                            onChange={(e) => setSelectedDesaId(e.target.value)}
                            disabled={!selectedKecId}
                            className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c] disabled:opacity-50"
                        >
                            <option value="">Semua Desa</option>
                            {desas.map(desa => (
                                <option key={desa.id} value={desa.id}>{desa.nama}</option>
                            ))}
                        </select>
                    </div>
                </div>

                {/* Main Visualizations Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Left 2 Cols: Main Chart */}
                    <div className="lg:col-span-2 bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between pb-4 mb-4 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <div>
                                <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc]">
                                    Grafik Suara Masuk
                                </h3>
                                <p className="text-xs text-[#475569] dark:text-[#94a3b8]">
                                    {jenisOptions.find(o => o.key === jenis)?.label} · Total: {totalSuara.toLocaleString('id-ID')} Suara
                                </p>
                            </div>
                        </div>

                        {loading ? (
                            <div className="h-72 flex items-center justify-center text-xs text-[#94a3b8]">
                                <RefreshCw className="w-5 h-5 animate-spin mr-2" />
                                <span>Memuat data grafik...</span>
                            </div>
                        ) : chartData?.data?.length > 0 ? (
                            <div className="h-80 relative flex items-center justify-center">
                                {chartData.type === 'pie' ? (
                                    <Pie data={pieChartConfig} options={{ responsive: true, maintainAspectRatio: false }} />
                                ) : (
                                    <Bar data={barChartConfig} options={{ responsive: true, maintainAspectRatio: false }} />
                                )}
                            </div>
                        ) : (
                            <div className="h-72 flex items-center justify-center text-xs text-[#94a3b8]">
                                Belum ada data suara untuk filter yang dipilih.
                            </div>
                        )}
                    </div>

                    {/* Right Col: Leaderboard Candidate Rank */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                        <div>
                            <div className="flex items-center gap-2 pb-3 mb-4 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                                <Trophy className="w-5 h-5 text-amber-500" />
                                <h3 className="font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] text-base">
                                    Peringkat Suara Teratas
                                </h3>
                            </div>

                            <div className="space-y-3 max-h-80 overflow-y-auto">
                                {chartData?.candidate_rank?.length > 0 ? (
                                    chartData.candidate_rank.map((item, idx) => (
                                        <div
                                            key={idx}
                                            className="bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl p-3 flex items-center justify-between"
                                        >
                                            <div className="flex items-center gap-3">
                                                <span className="w-6 h-6 rounded-full bg-[#f1f5f9] dark:bg-[#1f2937] text-xs font-bold flex items-center justify-center text-[#0f172a] dark:text-[#f8fafc]">
                                                    {idx + 1}
                                                </span>
                                                <div>
                                                    <p className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] leading-tight">
                                                        {item.label}
                                                    </p>
                                                    {item.meta && (
                                                        <p className="text-[10px] text-[#94a3b8]">{item.meta}</p>
                                                    )}
                                                </div>
                                            </div>
                                            <span className="text-xs font-bold text-[#bb152c] dark:text-red-400">
                                                {item.suara?.toLocaleString('id-ID')}
                                            </span>
                                        </div>
                                    ))
                                ) : (
                                    <div className="text-center py-10 text-xs text-[#94a3b8]">
                                        Tidak ada peringkat data.
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
