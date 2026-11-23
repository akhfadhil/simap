import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import {
    ArrowLeft,
    Download,
    Building2,
    Filter,
    MapPin,
    RotateCcw
} from 'lucide-react';

export default function RekapShow({
    kecamatans = [],
    jenis = 'ppwp',
    jenisLabel = '',
    scopeLevel = 'kabupaten',
    selectedKecamatanId = null,
    selectedDesaId = null,
    columnList = [],
    totalTitle = 'TOTAL KABUPATEN',
    masterList = [],
    dapils = [],
    selectedDapilId = null,
    scopeStats = {},
    scopeCalonTotals = {},
    scopePartaiGrandTotals = {},
    scopePartaiOnlyTotals = {},
    scopeCalegTotals = {},
    aktifJenis = [],
    jenisLabels = {},
    baseRoute = '/admin/rekap',
    exportUrl = null,
    isPpk = false,
    isPps = false,
}) {
    const [kecIdState, setKecIdState] = useState(selectedKecamatanId ? String(selectedKecamatanId) : '');
    const [desaIdState, setDesaIdState] = useState(selectedDesaId ? String(selectedDesaId) : '');
    const [collapsedParties, setCollapsedParties] = useState({});

    useEffect(() => {
        setKecIdState(selectedKecamatanId ? String(selectedKecamatanId) : '');
        setDesaIdState(selectedDesaId ? String(selectedDesaId) : '');
    }, [selectedKecamatanId, selectedDesaId]);

    const formatNumber = (num) => (num || 0).toLocaleString('id-ID');
    const isCandidateType = ['ppwp', 'gubernur', 'bupati', 'dpd'].includes(jenis);

    const cols = Array.isArray(columnList) && columnList.length > 0
        ? columnList
        : kecamatans.map(k => ({ id: k.id, nama: `Kec. ${k.nama}` }));

    const items = Array.isArray(masterList) ? masterList : [];

    const allCollapsed = !isCandidateType && items.length > 0 && items.every((p) => !!collapsedParties[p.id]);

    const togglePartyCollapse = (partaiId) => {
        setCollapsedParties((prev) => ({
            ...prev,
            [partaiId]: !prev[partaiId],
        }));
    };

    const toggleCollapseAll = () => {
        if (allCollapsed) {
            setCollapsedParties({});
        } else {
            const next = {};
            items.forEach((p) => {
                next[p.id] = true;
            });
            setCollapsedParties(next);
        }
    };

    // Find current selected kecamatan to populate desa options
    const currentKecObj = kecamatans.find(k => String(k.id) === String(kecIdState)) || kecamatans[0];
    const desaOptions = currentKecObj?.desas || [];

    // Calculate Scope Grand Totals
    const scopeTotals = {
        dpt_lk: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.dpt_lk || 0), 0),
        dpt_pr: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.dpt_pr || 0), 0),
        pengguna_dpt_lk: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.pengguna_dpt_lk || 0), 0),
        pengguna_dpt_pr: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.pengguna_dpt_pr || 0), 0),
        pengguna_dptb_lk: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.pengguna_dptb_lk || 0), 0),
        pengguna_dptb_pr: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.pengguna_dptb_pr || 0), 0),
        pengguna_dpk_lk: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.pengguna_dpk_lk || 0), 0),
        pengguna_dpk_pr: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.pengguna_dpk_pr || 0), 0),
        ss_diterima: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.ss_diterima || 0), 0),
        ss_digunakan: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.ss_digunakan || 0), 0),
        ss_rusak: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.ss_rusak || 0), 0),
        ss_sisa: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.ss_sisa || 0), 0),
        disabilitas_lk: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.disabilitas_lk || 0), 0),
        disabilitas_pr: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.disabilitas_pr || 0), 0),
        suara_tidak_sah: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.suara_tidak_sah || 0), 0),
        suara_sah: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.suara_sah || 0), 0),
        final: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.final || 0), 0),
        tps_count: cols.reduce((sum, c) => sum + (scopeStats[c.id]?.tps_count || 0), 0),
    };

    scopeTotals.total_dpt = scopeTotals.dpt_lk + scopeTotals.dpt_pr;
    scopeTotals.total_hadir = scopeTotals.pengguna_dpt_lk + scopeTotals.pengguna_dpt_pr +
        scopeTotals.pengguna_dptb_lk + scopeTotals.pengguna_dptb_pr +
        scopeTotals.pengguna_dpk_lk + scopeTotals.pengguna_dpk_pr;
    scopeTotals.suara_total = scopeTotals.suara_sah + scopeTotals.suara_tidak_sah;

    const handleDapilChange = (dapilId) => {
        const params = {};
        if (dapilId) params.dapil_id = dapilId;
        setKecIdState('');
        setDesaIdState('');
        router.get(`${baseRoute}/${jenis}`, params, { preserveState: true });
    };

    const handleKecamatanFilterChange = (newKecId) => {
        setKecIdState(newKecId);
        setDesaIdState('');
        const params = {};
        if (selectedDapilId) params.dapil_id = selectedDapilId;
        if (newKecId) params.kecamatan_id = newKecId;
        router.get(`${baseRoute}/${jenis}`, params, { preserveState: true });
    };

    const handleDesaFilterChange = (newDesaId) => {
        setDesaIdState(newDesaId);
        const params = {};
        if (!isPpk && selectedDapilId) params.dapil_id = selectedDapilId;
        if (!isPpk && kecIdState) params.kecamatan_id = kecIdState;
        if (newDesaId) params.desa_id = newDesaId;
        router.get(`${baseRoute}/${jenis}`, params, { preserveState: true });
    };

    const handleResetFilter = () => {
        setKecIdState('');
        setDesaIdState('');
        const params = {};
        if (!isPpk && selectedDapilId) params.dapil_id = selectedDapilId;
        router.get(`${baseRoute}/${jenis}`, params, { preserveState: true });
    };

    // Form C-Hasil Standard Row Schema
    const rowGroup1 = [
        { label: 'DPT Laki-laki', getVal: (st) => st.dpt_lk || 0, isBold: false },
        { label: 'DPT Perempuan', getVal: (st) => st.dpt_pr || 0, isBold: false },
        { label: 'DPT Jumlah', getVal: (st) => (st.dpt_lk || 0) + (st.dpt_pr || 0), isBold: true, isGroupSum: true },
        { label: 'Pengguna DPT Laki-laki', getVal: (st) => st.pengguna_dpt_lk || 0, isBold: false },
        { label: 'Pengguna DPT Perempuan', getVal: (st) => st.pengguna_dpt_pr || 0, isBold: false },
        { label: 'Pengguna DPT Jumlah', getVal: (st) => (st.pengguna_dpt_lk || 0) + (st.pengguna_dpt_pr || 0), isBold: true, isGroupSum: true },
        { label: 'Pengguna DPTb Laki-laki', getVal: (st) => st.pengguna_dptb_lk || 0, isBold: false },
        { label: 'Pengguna DPTb Perempuan', getVal: (st) => st.pengguna_dptb_pr || 0, isBold: false },
        { label: 'Pengguna DPTb Jumlah', getVal: (st) => (st.pengguna_dptb_lk || 0) + (st.pengguna_dptb_pr || 0), isBold: true, isGroupSum: true },
        { label: 'Pengguna DPK Laki-laki', getVal: (st) => st.pengguna_dpk_lk || 0, isBold: false },
        { label: 'Pengguna DPK Perempuan', getVal: (st) => st.pengguna_dpk_pr || 0, isBold: false },
        { label: 'Pengguna DPK Jumlah', getVal: (st) => (st.pengguna_dpk_lk || 0) + (st.pengguna_dpk_pr || 0), isBold: true, isGroupSum: true },
        { label: 'Total Hak Pilih Laki-laki', getVal: (st) => (st.pengguna_dpt_lk || 0) + (st.pengguna_dptb_lk || 0) + (st.pengguna_dpk_lk || 0), isBold: true },
        { label: 'Total Hak Pilih Perempuan', getVal: (st) => (st.pengguna_dpt_pr || 0) + (st.pengguna_dptb_pr || 0) + (st.pengguna_dpk_pr || 0), isBold: true },
        { label: 'TOTAL PENGGUNA HAK PILIH', getVal: (st) => (st.pengguna_dpt_lk || 0) + (st.pengguna_dpt_pr || 0) + (st.pengguna_dptb_lk || 0) + (st.pengguna_dptb_pr || 0) + (st.pengguna_dpk_lk || 0) + (st.pengguna_dpk_pr || 0), isBold: true, isHighlight: true },
    ];

    const rowGroup2 = [
        { label: 'Surat Suara Diterima', getVal: (st) => st.ss_diterima || 0, isBold: false },
        { label: 'Surat Suara Digunakan', getVal: (st) => st.ss_digunakan || 0, isBold: false },
        { label: 'Surat Suara Rusak', getVal: (st) => st.ss_rusak || 0, isBold: false },
        { label: 'Surat Suara Sisa', getVal: (st) => st.ss_sisa || 0, isBold: true },
    ];

    const rowGroup3 = [
        { label: 'Disabilitas Laki-laki', getVal: (st) => st.disabilitas_lk || 0, isBold: false },
        { label: 'Disabilitas Perempuan', getVal: (st) => st.disabilitas_pr || 0, isBold: false },
        { label: 'Disabilitas Jumlah', getVal: (st) => (st.disabilitas_lk || 0) + (st.disabilitas_pr || 0), isBold: true, isGroupSum: true },
    ];

    const stickyRightStyle = "md:sticky md:right-0 z-10 bg-[#fffbeb] dark:bg-[#1e293b] text-[#bb152c] dark:text-red-400 font-extrabold md:border-l border-amber-300 dark:border-amber-900/60 shadow-sm";
    const stickyRightHeaderStyle = "md:sticky md:right-0 z-30 bg-[#fef3c7] dark:bg-[#1a2332] text-amber-800 dark:text-amber-300 font-extrabold md:border-l border-amber-300 dark:border-amber-900/60 shadow-sm";

    return (
        <AdminLayout title={`Rekap ${jenisLabel}`}>
            <Head title={`Rekap ${jenisLabel}`} />

            <div className="space-y-6">
                {/* Header Banner */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-xs">
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <Link
                                href={baseRoute}
                                className="inline-flex items-center gap-1.5 text-xs font-semibold text-[#475569] dark:text-[#94a3b8] hover:text-[#bb152c] dark:hover:text-red-400 transition mb-3"
                            >
                                <ArrowLeft className="w-4 h-4" />
                                <span>Kembali ke Daftar Rekap</span>
                            </Link>
                            <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md ml-3">
                                {isPps ? 'PPS Desa' : isPpk ? 'PPK Kecamatan' : 'Admin Rekapitulasi'}
                            </span>
                            <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                                Rekapitulasi {jenisLabel}
                            </h1>
                            <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                                {isPps
                                    ? `Formulir C-Hasil resmi perolehan suara seluruh TPS di Desa ${kecamatans[0]?.desas?.[0]?.nama || ''} (Kec. ${kecamatans[0]?.nama || ''}).`
                                    : isPpk
                                    ? `Formulir C-Hasil resmi perolehan suara Kecamatan ${kecamatans[0]?.nama || ''}.`
                                    : 'Formulir C-Hasil resmi agregat data pemilih, surat suara, dan perolehan suara Kabupaten Banyuwangi.'}
                            </p>
                        </div>

                        <div className="flex items-center gap-3">
                            <a
                                href={exportUrl || (() => {
                                    const params = new URLSearchParams();
                                    if (selectedDapilId) params.set('dapil_id', selectedDapilId);
                                    if (kecIdState) params.set('kecamatan_id', kecIdState);
                                    if (desaIdState) params.set('desa_id', desaIdState);
                                    const q = params.toString();
                                    return `${baseRoute}/${jenis}/export${q ? '?' + q : ''}`;
                                })()}
                                className="inline-flex items-center gap-2 bg-[#bb152c] hover:bg-[#991124] text-white font-semibold px-4 py-2.5 rounded-xl text-xs shadow-md shadow-[#bb152c]/20 transition"
                            >
                                <Download className="w-4 h-4" />
                                <span>Export Excel</span>
                            </a>
                        </div>
                    </div>

                    {/* Active Jenis Election Buttons */}
                    <div className="pt-4 mt-4 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                        <label className="block text-[10px] font-bold uppercase tracking-wider text-[#94a3b8] mb-2">
                            Pilih Jenis Pemilihan:
                        </label>
                        <div className="flex flex-wrap gap-2">
                            {Object.entries(jenisLabels || {}).map(([key, label]) => {
                                if (Array.isArray(aktifJenis) && !aktifJenis.includes(key)) return null;
                                const active = jenis === key;
                                const params = new URLSearchParams();
                                if (!isPpk && !isPps && kecIdState) params.set('kecamatan_id', kecIdState);
                                if (!isPps && desaIdState) params.set('desa_id', desaIdState);
                                const q = params.toString();
                                const tabHref = `${baseRoute}/${key}${q ? '?' + q : ''}`;

                                return (
                                    <Link
                                        key={key}
                                        href={tabHref}
                                        className={`px-3 py-2 rounded-xl text-xs font-bold transition border ${
                                            active
                                                ? 'bg-[#bb152c] text-white border-[#bb152c] shadow-xs'
                                                : 'bg-[#f8fafc] dark:bg-[#0b0f19] text-[#0f172a] dark:text-[#f8fafc] border-[#e2e8f0] dark:border-[#1e293b] hover:border-[#bb152c]'
                                        }`}
                                    >
                                        {label}
                                    </Link>
                                );
                            })}
                        </div>
                    </div>
                </div>

                {/* Dapil Filter (DPRD Kab Only - Admin only) */}
                {!isPpk && !isPps && jenis === 'dprd_kab' && Array.isArray(dapils) && dapils.length > 0 && (
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs flex items-center gap-4">
                        <Building2 className="w-5 h-5 text-[#bb152c]" />
                        <div className="flex-1">
                            <label className="block text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] mb-1">
                                Dapil DPRD Kabupaten:
                            </label>
                            <select
                                value={selectedDapilId || ''}
                                onChange={(e) => handleDapilChange(e.target.value)}
                                className="w-full md:w-80 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3.5 py-2 text-xs outline-none focus:border-[#bb152c]"
                            >
                                {dapils.map((d) => (
                                    <option key={d.id} value={d.id}>{d.nama}</option>
                                ))}
                            </select>
                        </div>
                    </div>
                )}

                {/* DRILL-DOWN WILAYAH FILTER BAR (Kabupaten -> Kecamatan -> Desa) */}
                {isPps ? (
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs flex flex-wrap items-center justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-3">
                            <Filter className="w-4 h-4 text-[#bb152c]" />
                            <span className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                Wilayah Kerja PPS:
                            </span>
                            <div className="flex items-center gap-1.5">
                                <Building2 className="w-3.5 h-3.5 text-[#bb152c] shrink-0" />
                                <span className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] bg-slate-100 dark:bg-[#1e293b] px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                    Desa {kecamatans[0]?.desas?.[0]?.nama || '-'} (Kec. {kecamatans[0]?.nama || '-'})
                                </span>
                            </div>
                        </div>
                        <div className="text-right">
                            <span className="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-md bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400">
                                Scope: {cols.length} TPS Desa
                            </span>
                        </div>
                    </div>
                ) : (
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs flex flex-wrap items-center justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-3 flex-1">
                            <Filter className="w-4 h-4 text-[#bb152c]" />
                            <span className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                Fokus Tingkat Wilayah:
                            </span>

                            {/* Dropdown 1: Kecamatan (Or Fixed Badge for PPK) */}
                            {isPpk ? (
                                <div className="flex items-center gap-1.5">
                                    <MapPin className="w-3.5 h-3.5 text-[#bb152c] shrink-0" />
                                    <span className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] bg-slate-100 dark:bg-[#1e293b] px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                        Kec. {kecamatans[0]?.nama || '-'}
                                    </span>
                                </div>
                            ) : (
                                <div className="flex items-center gap-1.5 w-full sm:w-auto">
                                    <MapPin className="w-3.5 h-3.5 text-[#94a3b8] shrink-0" />
                                    <select
                                        value={kecIdState}
                                        onChange={(e) => handleKecamatanFilterChange(e.target.value)}
                                        className="w-full sm:w-auto bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-1.5 text-xs outline-none focus:border-[#bb152c]"
                                    >
                                        <option value="">🌐 Seluruh Kabupaten (Ringkasan Per-Kecamatan)</option>
                                        {kecamatans.map((k) => (
                                            <option key={k.id} value={k.id}>📍 Kec. {k.nama}</option>
                                        ))}
                                    </select>
                                </div>
                            )}

                            {/* Dropdown 2: Desa (Active when Kecamatan selected or in PPK mode) */}
                            {(isPpk || kecIdState) && (
                                <div className="flex items-center gap-1.5 w-full sm:w-auto">
                                    <Building2 className="w-3.5 h-3.5 text-[#94a3b8] shrink-0" />
                                    <select
                                        value={desaIdState}
                                        onChange={(e) => handleDesaFilterChange(e.target.value)}
                                        className="w-full sm:w-auto bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-1.5 text-xs outline-none focus:border-[#bb152c]"
                                    >
                                        <option value="">🏢 Seluruh Desa di Kecamatan Ini (Ringkasan Per-Desa)</option>
                                        {desaOptions.map((d) => (
                                            <option key={d.id} value={d.id}>🏘️ Desa {d.nama}</option>
                                        ))}
                                    </select>
                                </div>
                            )}

                            {/* Reset Filter Button */}
                            {isPpk ? (
                                desaIdState && (
                                    <button
                                        onClick={handleResetFilter}
                                        className="inline-flex items-center gap-1 bg-[#f1f5f9] dark:bg-[#1f2937] text-[#475569] dark:text-[#94a3b8] font-bold px-3 py-1.5 rounded-xl text-xs border border-[#e2e8f0] dark:border-[#1e293b] hover:text-[#bb152c] transition cursor-pointer"
                                    >
                                        <RotateCcw className="w-3 h-3" />
                                        <span>Reset Ke Semua Desa</span>
                                    </button>
                                )
                            ) : (
                                (kecIdState || desaIdState) && (
                                    <button
                                        onClick={handleResetFilter}
                                        className="inline-flex items-center gap-1 bg-[#f1f5f9] dark:bg-[#1f2937] text-[#475569] dark:text-[#94a3b8] font-bold px-3 py-1.5 rounded-xl text-xs border border-[#e2e8f0] dark:border-[#1e293b] hover:text-[#bb152c] transition cursor-pointer"
                                    >
                                        <RotateCcw className="w-3 h-3" />
                                        <span>Reset Ke Kabupaten</span>
                                    </button>
                                )
                            )}
                        </div>

                        <div className="text-right">
                            <span className="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-md bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400">
                                Scope: {scopeLevel.toUpperCase()} ({cols.length} Kolom)
                            </span>
                        </div>
                    </div>
                )}

                {/* KPI Metrics Summary Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs">
                        <p className="text-[10px] uppercase font-bold tracking-widest text-[#94a3b8]">Total DPT</p>
                        <p className="text-2xl font-extrabold font-mono text-[#0f172a] dark:text-[#f8fafc] mt-2">
                            {formatNumber(scopeTotals.total_dpt)}
                        </p>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">pemilih terdaftar</p>
                    </div>

                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs">
                        <p className="text-[10px] uppercase font-bold tracking-widest text-[#94a3b8]">Pengguna Hak Pilih</p>
                        <p className="text-2xl font-extrabold font-mono text-[#bb152c] dark:text-red-400 mt-2">
                            {formatNumber(scopeTotals.total_hadir)}
                        </p>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                            {scopeTotals.total_dpt > 0 ? ((scopeTotals.total_hadir / scopeTotals.total_dpt) * 100).toFixed(1) : 0}% partisipasi
                        </p>
                    </div>

                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs">
                        <p className="text-[10px] uppercase font-bold tracking-widest text-[#94a3b8]">Suara Tidak Sah</p>
                        <p className="text-2xl font-extrabold font-mono text-amber-500 mt-2">
                            {formatNumber(scopeTotals.suara_tidak_sah)}
                        </p>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">surat suara</p>
                    </div>

                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs">
                        <p className="text-[10px] uppercase font-bold tracking-widest text-[#94a3b8]">Progress Rekap</p>
                        <p className="text-2xl font-extrabold font-mono text-emerald-500 mt-2">
                            {scopeTotals.tps_count > 0 ? Math.min(100, Math.round((scopeTotals.final / scopeTotals.tps_count) * 100)) : 0}%
                        </p>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                            {formatNumber(scopeTotals.final)} / {formatNumber(scopeTotals.tps_count)} TPS Final
                        </p>
                    </div>
                </div>

                {/* UNIFIED C-HASIL FORM REKAPITULASI TABLE */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-xs overflow-hidden">
                    <div className="p-5 border-b border-[#f1f5f9] dark:border-[#1e293b] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 className="font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] text-base">
                                Tabel Rekapitulasi C-Hasil Resmi ({jenisLabel})
                            </h3>
                            <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-0.5">
                                Format gabungan data pemilih, surat suara, disabilitas, dan perolehan suara ({totalTitle}).
                            </p>
                        </div>
                        <span className="inline-flex items-center gap-1 text-[11px] text-slate-400 dark:text-slate-500 font-medium md:hidden">
                            👉 Geser tabel ke kanan untuk data wilayah
                        </span>
                    </div>

                    <div className="overflow-x-auto max-h-[650px] relative">
                        <table className="w-full text-left text-xs border-collapse">
                            <thead className="bg-[#f8fafc] dark:bg-[#0b0f19] text-[#94a3b8] uppercase tracking-wider font-bold sticky top-0 z-20 border-b border-[#e2e8f0] dark:border-[#1e293b]">
                                <tr>
                                    <th className="px-4 py-3 sticky left-0 z-30 bg-[#f8fafc] dark:bg-[#0b0f19] min-w-[240px] border-r border-[#e2e8f0] dark:border-[#1e293b]">
                                        Uraian Data / Wilayah
                                    </th>
                                    {cols.map((c) => (
                                        <th key={c.id} className="px-4 py-3 text-right min-w-[130px]">
                                            <div className="flex items-center justify-end gap-1.5">
                                                <span>{c.nama}</span>
                                                {scopeLevel === 'desa' && (
                                                    <a
                                                        href={`${baseRoute}/${jenis}/export?tps_id=${c.id}`}
                                                        title={`Download Excel C-Hasil ${c.nama}`}
                                                        className="p-1 rounded-md text-slate-400 hover:text-[#bb152c] dark:hover:text-red-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                                    >
                                                        <Download className="w-3.5 h-3.5" />
                                                    </a>
                                                )}
                                            </div>
                                        </th>
                                    ))}
                                    <th className={`px-4 py-3 text-right min-w-[160px] ${stickyRightHeaderStyle}`}>
                                        {totalTitle}
                                    </th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-[#f1f5f9] dark:divide-[#1e293b]">
                                {/* ── SECTION 1: DATA PEMILIH & HAK PILIH ── */}
                                <tr className="bg-[#f1f5f9]/80 dark:bg-[#1e293b]/60 text-[#0f172a] dark:text-[#f8fafc]">
                                    <td colSpan={cols.length + 1} className="px-4 py-2.5 font-extrabold text-xs uppercase tracking-wider text-[#bb152c]">
                                        I. DATA PEMILIH DAN PENGGUNAAN HAK PILIH
                                    </td>
                                    <td className={stickyRightStyle}></td>
                                </tr>
                                {rowGroup1.map((row, idx) => (
                                    <tr key={idx} className={`hover:bg-[#f8fafc] dark:hover:bg-[#0b0f19]/50 ${row.isGroupSum ? 'bg-slate-50/50 dark:bg-slate-900/30' : ''}`}>
                                        <td className={`px-4 py-2.5 sticky left-0 z-10 bg-white dark:bg-[#151f32] border-r border-[#e2e8f0] dark:border-[#1e293b] ${row.isBold ? 'font-bold text-[#0f172a] dark:text-[#f8fafc]' : 'text-[#475569] dark:text-[#94a3b8]'}`}>
                                            {row.label}
                                        </td>
                                        {cols.map((c) => {
                                            const st = scopeStats[c.id] || {};
                                            return (
                                                <td key={c.id} className={`px-4 py-2.5 text-right font-mono ${row.isBold ? 'font-bold text-[#0f172a] dark:text-[#f8fafc]' : 'text-[#475569] dark:text-[#94a3b8]'}`}>
                                                    {formatNumber(row.getVal(st))}
                                                </td>
                                            );
                                        })}
                                        <td className={`px-4 py-2.5 text-right font-mono ${stickyRightStyle}`}>
                                            {formatNumber(row.getVal(scopeTotals))}
                                        </td>
                                    </tr>
                                ))}

                                {/* ── SECTION 2: PENGGUNAAN SURAT SUARA ── */}
                                <tr className="bg-[#f1f5f9]/80 dark:bg-[#1e293b]/60 text-[#0f172a] dark:text-[#f8fafc]">
                                    <td colSpan={cols.length + 1} className="px-4 py-2.5 font-extrabold text-xs uppercase tracking-wider text-[#bb152c]">
                                        II. PENGGUNAAN SURAT SUARA
                                    </td>
                                    <td className={stickyRightStyle}></td>
                                </tr>
                                {rowGroup2.map((row, idx) => (
                                    <tr key={idx} className="hover:bg-[#f8fafc] dark:hover:bg-[#0b0f19]/50">
                                        <td className={`px-4 py-2.5 sticky left-0 z-10 bg-white dark:bg-[#151f32] border-r border-[#e2e8f0] dark:border-[#1e293b] ${row.isBold ? 'font-bold text-[#0f172a] dark:text-[#f8fafc]' : 'text-[#475569] dark:text-[#94a3b8]'}`}>
                                            {row.label}
                                        </td>
                                        {cols.map((c) => {
                                            const st = scopeStats[c.id] || {};
                                            return (
                                                <td key={c.id} className={`px-4 py-2.5 text-right font-mono ${row.isBold ? 'font-bold text-[#0f172a] dark:text-[#f8fafc]' : 'text-[#475569] dark:text-[#94a3b8]'}`}>
                                                    {formatNumber(row.getVal(st))}
                                                </td>
                                            );
                                        })}
                                        <td className={`px-4 py-2.5 text-right font-mono ${stickyRightStyle}`}>
                                            {formatNumber(row.getVal(scopeTotals))}
                                        </td>
                                    </tr>
                                ))}

                                {/* ── SECTION 3: DATA PEMILIH DISABILITAS ── */}
                                <tr className="bg-[#f1f5f9]/80 dark:bg-[#1e293b]/60 text-[#0f172a] dark:text-[#f8fafc]">
                                    <td colSpan={cols.length + 1} className="px-4 py-2.5 font-extrabold text-xs uppercase tracking-wider text-[#bb152c]">
                                        III. DATA PEMILIH DISABILITAS
                                    </td>
                                    <td className={stickyRightStyle}></td>
                                </tr>
                                {rowGroup3.map((row, idx) => (
                                    <tr key={idx} className="hover:bg-[#f8fafc] dark:hover:bg-[#0b0f19]/50">
                                        <td className={`px-4 py-2.5 sticky left-0 z-10 bg-white dark:bg-[#151f32] border-r border-[#e2e8f0] dark:border-[#1e293b] ${row.isBold ? 'font-bold text-[#0f172a] dark:text-[#f8fafc]' : 'text-[#475569] dark:text-[#94a3b8]'}`}>
                                            {row.label}
                                        </td>
                                        {cols.map((c) => {
                                            const st = scopeStats[c.id] || {};
                                            return (
                                                <td key={c.id} className={`px-4 py-2.5 text-right font-mono ${row.isBold ? 'font-bold text-[#0f172a] dark:text-[#f8fafc]' : 'text-[#475569] dark:text-[#94a3b8]'}`}>
                                                    {formatNumber(row.getVal(st))}
                                                </td>
                                            );
                                        })}
                                        <td className={`px-4 py-2.5 text-right font-mono ${stickyRightStyle}`}>
                                            {formatNumber(row.getVal(scopeTotals))}
                                        </td>
                                    </tr>
                                ))}

                                {/* ── SECTION 4: PEROLEHAN SUARA CALON / PARTAI ── */}
                                <tr className="bg-[#f1f5f9]/80 dark:bg-[#1e293b]/60 text-[#0f172a] dark:text-[#f8fafc]">
                                    <td colSpan={cols.length + 1} className="px-4 py-2.5 font-extrabold text-xs uppercase tracking-wider text-[#bb152c]">
                                        <div className="flex items-center justify-between">
                                            <span>IV. PEROLEHAN SUARA {isCandidateType ? 'CALON / PASLON' : 'PARTAI POLITIK & CALEG'}</span>
                                            {!isCandidateType && items.length > 0 && (
                                                <button
                                                    type="button"
                                                    onClick={toggleCollapseAll}
                                                    className="text-[11px] font-semibold text-slate-500 hover:text-[#bb152c] dark:text-slate-400 dark:hover:text-[#ff4d6d] normal-case tracking-normal px-2.5 py-0.5 rounded-lg border border-slate-300 dark:border-slate-700 hover:border-[#bb152c] transition-colors"
                                                >
                                                    {allCollapsed ? 'Buka Semua Caleg' : 'Tutup Semua Caleg'}
                                                </button>
                                            )}
                                        </div>
                                    </td>
                                    <td className={stickyRightStyle}></td>
                                </tr>
                                {isCandidateType ? (
                                    items.map((item, idx) => {
                                        const scopeItemTotal = cols.reduce((sum, c) => {
                                            const calonTotals = (scopeCalonTotals && scopeCalonTotals[c.id]) || {};
                                            return sum + (calonTotals[item.id] || 0);
                                        }, 0);

                                        return (
                                            <tr key={item.id || idx} className="hover:bg-[#f8fafc] dark:hover:bg-[#0b0f19]/50">
                                                <td className="px-4 py-2.5 font-bold text-[#0f172a] dark:text-[#f8fafc] sticky left-0 z-10 bg-white dark:bg-[#151f32] border-r border-[#e2e8f0] dark:border-[#1e293b]">
                                                    <div className="flex items-center gap-2">
                                                        {item.nomor_urut && (
                                                            <span className="text-xs text-slate-400 dark:text-slate-500 font-mono">
                                                                {item.nomor_urut}.
                                                            </span>
                                                        )}
                                                        <span>{item.nama}</span>
                                                    </div>
                                                </td>
                                                {cols.map((c) => {
                                                    const calonTotals = (scopeCalonTotals && scopeCalonTotals[c.id]) || {};
                                                    const val = calonTotals[item.id] || 0;

                                                    return (
                                                        <td key={c.id} className="px-4 py-2.5 text-right font-mono text-[#0f172a] dark:text-[#f8fafc]">
                                                            {formatNumber(val)}
                                                        </td>
                                                    );
                                                })}
                                                <td className={`px-4 py-2.5 text-right font-mono ${stickyRightStyle}`}>
                                                    {formatNumber(scopeItemTotal)}
                                                </td>
                                            </tr>
                                        );
                                    })
                                ) : (
                                    items.map((partai) => {
                                        const isCollapsed = !!collapsedParties[partai.id];
                                        const partaiOnlyRowTotal = cols.reduce((sum, c) => sum + (scopePartaiOnlyTotals?.[c.id]?.[partai.id] || 0), 0);
                                        const grandTotal = cols.reduce((sum, c) => sum + (scopePartaiGrandTotals?.[c.id]?.[partai.id] || 0), 0);

                                        return (
                                            <React.Fragment key={partai.id}>
                                                {/* Header Partai */}
                                                <tr
                                                    onClick={() => togglePartyCollapse(partai.id)}
                                                    className="cursor-pointer bg-[#f8fafc] dark:bg-[#1e293b]/70 hover:bg-[#f1f5f9] dark:hover:bg-[#1e293b] border-t-2 border-[#e2e8f0] dark:border-[#334155] select-none transition-colors"
                                                >
                                                    <td className="px-4 py-2 font-bold text-xs uppercase tracking-wide text-[#0f172a] dark:text-[#f8fafc] sticky left-0 z-10 bg-[#f8fafc] dark:bg-[#1e293b] border-r border-[#e2e8f0] dark:border-[#334155]">
                                                        <div className="flex items-center justify-between">
                                                            <div className="flex items-center gap-2">
                                                                <span className="inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 rounded bg-[#bb152c] text-white font-extrabold text-[11px] shadow-sm">
                                                                    {partai.nomor_urut}
                                                                </span>
                                                                <span className="text-[#0f172a] dark:text-white font-bold">{partai.nama}</span>
                                                            </div>
                                                            <span className="text-[10px] text-slate-400 dark:text-slate-500 font-normal ml-2">
                                                                {isCollapsed ? `(Tampilkan ${(partai.calegs || []).length} caleg ▶)` : `(Sembunyikan ▼)`}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td colSpan={cols.length} className="px-4 py-2 text-xs text-slate-400 dark:text-slate-500 font-medium">
                                                        Rincian Suara Partai & Caleg
                                                    </td>
                                                    <td className={`px-4 py-2 text-right font-mono text-xs font-semibold ${stickyRightStyle}`}>
                                                        {isCollapsed ? formatNumber(grandTotal) : ''}
                                                    </td>
                                                </tr>

                                                {!isCollapsed && (
                                                    <>
                                                        {/* Row: Suara Partai Sahaja */}
                                                        <tr className="hover:bg-slate-50/70 dark:hover:bg-[#0b0f19]/40 border-b border-slate-100 dark:border-slate-800/60">
                                                            <td className="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 sticky left-0 z-10 bg-white dark:bg-[#151f32] border-r border-[#e2e8f0] dark:border-[#1e293b] pl-8">
                                                                <div className="flex items-center gap-2">
                                                                    <span className="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                                    <span>Suara Partai</span>
                                                                </div>
                                                            </td>
                                                            {cols.map((c) => {
                                                                const val = scopePartaiOnlyTotals?.[c.id]?.[partai.id] || 0;
                                                                return (
                                                                    <td key={c.id} className="px-4 py-2 text-right font-mono text-xs text-slate-700 dark:text-slate-300">
                                                                        {formatNumber(val)}
                                                                    </td>
                                                                );
                                                            })}
                                                            <td className={`px-4 py-2 text-right font-mono text-xs ${stickyRightStyle}`}>
                                                                {formatNumber(partaiOnlyRowTotal)}
                                                            </td>
                                                        </tr>

                                                        {/* Rows: Tiap Caleg */}
                                                        {(partai.calegs || []).map((caleg) => {
                                                            const calegRowTotal = cols.reduce((sum, c) => sum + (scopeCalegTotals?.[c.id]?.[caleg.id] || 0), 0);
                                                            return (
                                                                <tr key={caleg.id} className="hover:bg-slate-50/70 dark:hover:bg-[#0b0f19]/40 border-b border-slate-100 dark:border-slate-800/60">
                                                                    <td className="px-4 py-1.5 text-xs text-slate-700 dark:text-slate-300 sticky left-0 z-10 bg-white dark:bg-[#151f32] border-r border-[#e2e8f0] dark:border-[#1e293b] pl-8">
                                                                        <div className="flex items-center gap-2">
                                                                            <span className="text-[11px] text-slate-400 dark:text-slate-500 min-w-[18px] text-right font-mono">
                                                                                {caleg.nomor_urut}.
                                                                            </span>
                                                                            <span className="truncate max-w-[280px]" title={caleg.nama}>
                                                                                {caleg.nama}
                                                                            </span>
                                                                        </div>
                                                                    </td>
                                                                    {cols.map((c) => {
                                                                        const val = scopeCalegTotals?.[c.id]?.[caleg.id] || 0;
                                                                        return (
                                                                            <td key={c.id} className="px-4 py-1.5 text-right font-mono text-xs text-slate-700 dark:text-slate-300">
                                                                                {formatNumber(val)}
                                                                            </td>
                                                                        );
                                                                    })}
                                                                    <td className={`px-4 py-1.5 text-right font-mono text-xs ${stickyRightStyle}`}>
                                                                        {formatNumber(calegRowTotal)}
                                                                    </td>
                                                                </tr>
                                                            );
                                                        })}

                                                        {/* Row: Total Suara Sah Partai */}
                                                        <tr className="bg-emerald-50/40 dark:bg-emerald-950/20 font-bold text-xs border-b-2 border-[#e2e8f0] dark:border-[#334155]">
                                                            <td className="px-4 py-2 text-emerald-800 dark:text-emerald-400 sticky left-0 z-10 bg-emerald-50/80 dark:bg-[#132828] border-r border-[#e2e8f0] dark:border-[#1e293b] pl-6 uppercase">
                                                                Total Suara Sah {partai.nama}
                                                            </td>
                                                            {cols.map((c) => {
                                                                const val = scopePartaiGrandTotals?.[c.id]?.[partai.id] || 0;
                                                                return (
                                                                    <td key={c.id} className="px-4 py-2 text-right font-mono font-bold text-emerald-700 dark:text-emerald-400">
                                                                        {formatNumber(val)}
                                                                    </td>
                                                                );
                                                            })}
                                                            <td className={`px-4 py-2 text-right font-mono font-bold text-emerald-700 dark:text-emerald-400 ${stickyRightStyle}`}>
                                                                {formatNumber(grandTotal)}
                                                            </td>
                                                        </tr>
                                                    </>
                                                )}
                                            </React.Fragment>
                                        );
                                    })
                                )}

                                {/* ── SECTION 5: SUARA SAH & TIDAK SAH ── */}
                                <tr className="bg-[#f1f5f9]/80 dark:bg-[#1e293b]/60 text-[#0f172a] dark:text-[#f8fafc]">
                                    <td colSpan={cols.length + 1} className="px-4 py-2.5 font-extrabold text-xs uppercase tracking-wider text-[#bb152c]">
                                        V. REKAPITULASI SUARA SAH DAN TIDAK SAH
                                    </td>
                                    <td className={stickyRightStyle}></td>
                                </tr>
                                <tr className="hover:bg-[#f8fafc] dark:hover:bg-[#0b0f19]/50 font-bold">
                                    <td className="px-4 py-2.5 sticky left-0 z-10 bg-white dark:bg-[#151f32] border-r border-[#e2e8f0] dark:border-[#1e293b] text-[#10b981]">
                                        JUMLAH SUARA SAH
                                    </td>
                                    {cols.map((c) => (
                                        <td key={c.id} className="px-4 py-2.5 text-right font-mono text-[#10b981]">
                                            {formatNumber(scopeStats[c.id]?.suara_sah)}
                                        </td>
                                    ))}
                                    <td className={`px-4 py-2.5 text-right font-mono text-[#10b981] ${stickyRightStyle}`}>
                                        {formatNumber(scopeTotals.suara_sah)}
                                    </td>
                                </tr>

                                <tr className="hover:bg-[#f8fafc] dark:hover:bg-[#0b0f19]/50 font-bold">
                                    <td className="px-4 py-2.5 sticky left-0 z-10 bg-white dark:bg-[#151f32] border-r border-[#e2e8f0] dark:border-[#1e293b] text-amber-500">
                                        JUMLAH SUARA TIDAK SAH
                                    </td>
                                    {cols.map((c) => (
                                        <td key={c.id} className="px-4 py-2.5 text-right font-mono text-amber-500">
                                            {formatNumber(scopeStats[c.id]?.suara_tidak_sah)}
                                        </td>
                                    ))}
                                    <td className={`px-4 py-2.5 text-right font-mono text-amber-600 dark:text-amber-400 ${stickyRightStyle}`}>
                                        {formatNumber(scopeTotals.suara_tidak_sah)}
                                    </td>
                                </tr>

                                <tr className="hover:bg-[#f8fafc] dark:hover:bg-[#0b0f19]/50 font-extrabold bg-red-50/50 dark:bg-red-950/20">
                                    <td className="px-4 py-3 sticky left-0 z-10 bg-white dark:bg-[#151f32] border-r border-[#e2e8f0] dark:border-[#1e293b] text-[#bb152c]">
                                        TOTAL SUARA SAH + TIDAK SAH
                                    </td>
                                    {cols.map((c) => (
                                        <td key={c.id} className="px-4 py-3 text-right font-mono text-[#bb152c] dark:text-red-400">
                                            {formatNumber((scopeStats[c.id]?.suara_sah || 0) + (scopeStats[c.id]?.suara_tidak_sah || 0))}
                                        </td>
                                    ))}
                                    <td className={`px-4 py-3 text-right font-mono text-[#bb152c] dark:text-red-400 ${stickyRightStyle}`}>
                                        {formatNumber(scopeTotals.suara_total)}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
