import React, { useState, useMemo } from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import {
    Building2,
    Vote,
    CheckCircle2,
    Clock,
    XCircle,
    Search,
    Eye,
    FolderOpen,
    UserCheck,
    UserX,
    ChevronDown,
    ChevronUp,
    Layers,
    ArrowUpRight,
    Filter,
    ShieldAlert
} from 'lucide-react';

export default function DataPps({
    kecamatan = {},
    desas = [],
    stats = {},
    totalJenisAktif = 0
}) {
    const [searchQuery, setSearchQuery] = useState('');
    const [statusFilter, setStatusFilter] = useState('all'); // all, incomplete, complete, unassigned
    const [sortBy, setSortBy] = useState('name_asc'); // name_asc, progress_desc, progress_asc, tps_desc
    const [expandedDesas, setExpandedDesas] = useState({});

    // Toggle expand TPS per desa
    const toggleExpand = (desaId) => {
        setExpandedDesas(prev => ({
            ...prev,
            [desaId]: !prev[desaId]
        }));
    };

    // Toggle expand all
    const allExpanded = desas.length > 0 && desas.every(d => expandedDesas[d.id]);
    const toggleExpandAll = () => {
        if (allExpanded) {
            setExpandedDesas({});
        } else {
            const next = {};
            desas.forEach(d => {
                next[d.id] = true;
            });
            setExpandedDesas(next);
        }
    };

    // Filter & Sort Desas
    const filteredDesas = useMemo(() => {
        let result = [...desas];

        // 1. Search Query
        if (searchQuery.trim()) {
            const query = searchQuery.toLowerCase().trim();
            result = result.filter(d => {
                const matchDesa = d.nama?.toLowerCase().includes(query);
                const matchPps = d.pps_users?.some(u =>
                    u.name?.toLowerCase().includes(query) ||
                    u.username?.toLowerCase().includes(query)
                );
                return matchDesa || matchPps;
            });
        }

        // 2. Status Filter
        if (statusFilter === 'incomplete') {
            result = result.filter(d => d.persentase_terverifikasi < 100);
        } else if (statusFilter === 'complete') {
            result = result.filter(d => d.persentase_terverifikasi === 100 && d.total_tps > 0);
        } else if (statusFilter === 'unassigned') {
            result = result.filter(d => !d.pps_users || d.pps_users.length === 0);
        }

        // 3. Sorting
        result.sort((a, b) => {
            if (sortBy === 'name_asc') {
                return a.nama.localeCompare(b.nama);
            }
            if (sortBy === 'progress_desc') {
                return b.persentase_terverifikasi - a.persentase_terverifikasi;
            }
            if (sortBy === 'progress_asc') {
                return a.persentase_terverifikasi - b.persentase_terverifikasi;
            }
            if (sortBy === 'tps_desc') {
                return b.total_tps - a.total_tps;
            }
            return 0;
        });

        return result;
    }, [desas, searchQuery, statusFilter, sortBy]);

    // Counts for filter pills
    const counts = useMemo(() => {
        return {
            all: desas.length,
            incomplete: desas.filter(d => d.persentase_terverifikasi < 100).length,
            complete: desas.filter(d => d.persentase_terverifikasi === 100 && d.total_tps > 0).length,
            unassigned: desas.filter(d => !d.pps_users || d.pps_users.length === 0).length,
        };
    }, [desas]);

    return (
        <AdminLayout title={`Data PPS - Kecamatan ${kecamatan?.nama || ''}`}>
            <Head title={`Data PPS - Kecamatan ${kecamatan?.nama || ''}`} />

            <div className="space-y-6 max-w-7xl mx-auto pb-12">
                {/* Header Section */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2 mb-1.5">
                            <span className="text-[10px] tracking-widest uppercase font-bold text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/50 px-2.5 py-0.5 rounded-md border border-red-100 dark:border-red-900/50">
                                Wilayah PPK & PPS
                            </span>
                            <span className="text-xs text-[#94a3b8] font-medium">
                                • Kecamatan {kecamatan?.nama || '-'}
                            </span>
                        </div>
                        <h1 className="text-2xl font-extrabold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight">
                            DATA PPS & WILAYAH KERJA
                        </h1>
                        <p className="text-xs text-[#64748b] dark:text-[#94a3b8] mt-1 max-w-2xl leading-relaxed">
                            Monitoring kemajuan verifikasi dokumen C-Hasil TPS di tingkat Desa/Kelurahan, status penugasan personel PPS, dan akses cepat simulasi tampilan PPS.
                        </p>
                    </div>

                    <div className="flex items-center gap-2.5 shrink-0">
                        <Link
                            href="/dokumen/kecamatan"
                            className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-[#bb152c] hover:bg-[#a01225] text-white shadow-xs transition duration-150"
                        >
                            <FolderOpen className="w-4 h-4" />
                            <span>Buka Rekap Dokumen</span>
                        </Link>
                    </div>
                </div>

                {/* Scorecards / KPI Metrics */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {/* Card 1: Total Desa */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs relative overflow-hidden">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-medium text-[#64748b] dark:text-[#94a3b8]">
                                Total Desa / Kelurahan
                            </span>
                            <div className="w-9 h-9 rounded-xl bg-slate-100 dark:bg-[#1e293b] text-slate-700 dark:text-slate-300 flex items-center justify-center">
                                <Building2 className="w-4 h-4" />
                            </div>
                        </div>
                        <div className="mt-3 flex items-baseline gap-2">
                            <span className="text-3xl font-extrabold font-heading text-[#0f172a] dark:text-[#f8fafc]">
                                {stats?.total_desa || 0}
                            </span>
                            <span className="text-xs text-[#64748b] dark:text-[#94a3b8]">Desa</span>
                        </div>
                        <div className="mt-2 flex items-center gap-1.5 text-[11px] text-[#64748b] dark:text-[#94a3b8]">
                            <UserCheck className="w-3.5 h-3.5 text-emerald-500 shrink-0" />
                            <span>
                                <strong>{stats?.total_pps_assigned || 0}</strong> dari {stats?.total_desa || 0} desa memiliki PPS
                            </span>
                        </div>
                    </div>

                    {/* Card 2: Total TPS */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs relative overflow-hidden">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-medium text-[#64748b] dark:text-[#94a3b8]">
                                Total TPS Terdaftar
                            </span>
                            <div className="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-100 dark:border-blue-900/50">
                                <Vote className="w-4 h-4" />
                            </div>
                        </div>
                        <div className="mt-3 flex items-baseline gap-2">
                            <span className="text-3xl font-extrabold font-heading text-[#0f172a] dark:text-[#f8fafc]">
                                {stats?.total_tps?.toLocaleString('id-ID') || 0}
                            </span>
                            <span className="text-xs text-[#64748b] dark:text-[#94a3b8]">Titik TPS</span>
                        </div>
                        <div className="mt-2 text-[11px] text-[#64748b] dark:text-[#94a3b8]">
                            Target Dokumen: <strong>{stats?.total_target_dokumen?.toLocaleString('id-ID') || 0}</strong> berkas ({totalJenisAktif} jenis)
                        </div>
                    </div>

                    {/* Card 3: Dokumen Terverifikasi */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs relative overflow-hidden">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-medium text-[#64748b] dark:text-[#94a3b8]">
                                Dokumen Terverifikasi
                            </span>
                            <div className="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-emerald-900/50">
                                <CheckCircle2 className="w-4 h-4" />
                            </div>
                        </div>
                        <div className="mt-3 flex items-baseline gap-2">
                            <span className="text-3xl font-extrabold font-heading text-emerald-600 dark:text-emerald-400">
                                {stats?.total_terverifikasi?.toLocaleString('id-ID') || 0}
                            </span>
                            <span className="text-xs text-[#64748b] dark:text-[#94a3b8]">
                                / {stats?.total_target_dokumen?.toLocaleString('id-ID') || 0}
                            </span>
                        </div>
                        <div className="mt-2 flex items-center gap-2">
                            <div className="flex-1 h-1.5 bg-slate-100 dark:bg-[#1e293b] rounded-full overflow-hidden">
                                <div
                                    className="h-full bg-emerald-500 rounded-full transition-all duration-300"
                                    style={{ width: `${Math.min(stats?.persentase_terverifikasi || 0, 100)}%` }}
                                />
                            </div>
                            <span className="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 shrink-0">
                                {stats?.persentase_terverifikasi || 0}%
                            </span>
                        </div>
                    </div>

                    {/* Card 4: Antrean Verifikasi */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs relative overflow-hidden">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-medium text-[#64748b] dark:text-[#94a3b8]">
                                Status Dokumen Lainnya
                            </span>
                            <div className="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-100 dark:border-amber-900/50">
                                <Clock className="w-4 h-4" />
                            </div>
                        </div>
                        <div className="mt-3 flex items-baseline gap-2">
                            <span className="text-3xl font-extrabold font-heading text-amber-600 dark:text-amber-400">
                                {stats?.total_menunggu?.toLocaleString('id-ID') || 0}
                            </span>
                            <span className="text-xs text-[#64748b] dark:text-[#94a3b8]">Menunggu</span>
                        </div>
                        <div className="mt-2 flex items-center justify-between text-[11px]">
                            <span className="text-red-600 dark:text-red-400 font-medium">
                                {stats?.total_ditolak || 0} Ditolak
                            </span>
                            <span className="text-[#64748b] dark:text-[#94a3b8]">
                                {stats?.total_masuk || 0} Total Diunggah
                            </span>
                        </div>
                    </div>
                </div>

                {/* Filter and Control Bar */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs space-y-3">
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-3">
                        {/* Search Input */}
                        <div className="relative flex-1 max-w-md">
                            <Search className="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[#94a3b8]" />
                            <input
                                type="text"
                                placeholder="Cari nama desa atau nama personel PPS..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-[#f8fafc] dark:bg-[#1e293b] border border-[#e2e8f0] dark:border-[#334155] text-[#0f172a] dark:text-[#f8fafc] placeholder-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-[#bb152c]/20 focus:border-[#bb152c]"
                            />
                            {searchQuery && (
                                <button
                                    onClick={() => setSearchQuery('')}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc]"
                                >
                                    ×
                                </button>
                            )}
                        </div>

                        {/* Sort & Toggle Expand Controls */}
                        <div className="flex items-center gap-2.5">
                            <div className="flex items-center gap-2">
                                <span className="text-xs text-[#64748b] dark:text-[#94a3b8] hidden sm:inline">Urutan:</span>
                                <select
                                    value={sortBy}
                                    onChange={(e) => setSortBy(e.target.value)}
                                    className="text-xs px-3 py-2 rounded-xl bg-[#f8fafc] dark:bg-[#1e293b] border border-[#e2e8f0] dark:border-[#334155] text-[#0f172a] dark:text-[#f8fafc] focus:outline-none focus:ring-2 focus:ring-[#bb152c]/20 focus:border-[#bb152c] cursor-pointer"
                                >
                                    <option value="name_asc">Nama Desa (A - Z)</option>
                                    <option value="progress_desc">Progres Tertinggi</option>
                                    <option value="progress_asc">Progres Terendah</option>
                                    <option value="tps_desc">TPS Terbanyak</option>
                                </select>
                            </div>

                            <button
                                onClick={toggleExpandAll}
                                className="px-3 py-2 rounded-xl text-xs font-medium border border-[#e2e8f0] dark:border-[#334155] hover:bg-[#f1f5f9] dark:hover:bg-[#1e293b] text-[#475569] dark:text-[#cbd5e1] transition cursor-pointer flex items-center gap-1.5 shrink-0"
                            >
                                <Layers className="w-3.5 h-3.5" />
                                <span>{allExpanded ? 'Tutup Semua Rincian' : 'Buka Semua Rincian'}</span>
                            </button>
                        </div>
                    </div>

                    {/* Status Filter Pills */}
                    <div className="flex items-center gap-2 overflow-x-auto pt-1 pb-0.5 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                        <span className="text-[11px] font-medium text-[#64748b] dark:text-[#94a3b8] flex items-center gap-1 shrink-0 mr-1">
                            <Filter className="w-3 h-3" /> Filter:
                        </span>

                        <button
                            onClick={() => setStatusFilter('all')}
                            className={`px-3 py-1 rounded-lg text-xs font-medium transition cursor-pointer shrink-0 ${
                                statusFilter === 'all'
                                    ? 'bg-[#bb152c] text-white shadow-xs'
                                    : 'bg-[#f1f5f9] dark:bg-[#1e293b] text-[#475569] dark:text-[#cbd5e1] hover:bg-[#e2e8f0] dark:hover:bg-[#334155]'
                            }`}
                        >
                            Semua ({counts.all})
                        </button>

                        <button
                            onClick={() => setStatusFilter('incomplete')}
                            className={`px-3 py-1 rounded-lg text-xs font-medium transition cursor-pointer shrink-0 ${
                                statusFilter === 'incomplete'
                                    ? 'bg-amber-600 text-white shadow-xs'
                                    : 'bg-[#f1f5f9] dark:bg-[#1e293b] text-[#475569] dark:text-[#cbd5e1] hover:bg-[#e2e8f0] dark:hover:bg-[#334155]'
                            }`}
                        >
                            Belum Lengkap ({counts.incomplete})
                        </button>

                        <button
                            onClick={() => setStatusFilter('complete')}
                            className={`px-3 py-1 rounded-lg text-xs font-medium transition cursor-pointer shrink-0 ${
                                statusFilter === 'complete'
                                    ? 'bg-emerald-600 text-white shadow-xs'
                                    : 'bg-[#f1f5f9] dark:bg-[#1e293b] text-[#475569] dark:text-[#cbd5e1] hover:bg-[#e2e8f0] dark:hover:bg-[#334155]'
                            }`}
                        >
                            Selesai 100% ({counts.complete})
                        </button>

                        <button
                            onClick={() => setStatusFilter('unassigned')}
                            className={`px-3 py-1 rounded-lg text-xs font-medium transition cursor-pointer shrink-0 ${
                                statusFilter === 'unassigned'
                                    ? 'bg-rose-600 text-white shadow-xs'
                                    : 'bg-[#f1f5f9] dark:bg-[#1e293b] text-[#475569] dark:text-[#cbd5e1] hover:bg-[#e2e8f0] dark:hover:bg-[#334155]'
                            }`}
                        >
                            Belum Ada PPS ({counts.unassigned})
                        </button>
                    </div>
                </div>

                {/* List of Desa Cards */}
                {filteredDesas.length === 0 ? (
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-12 text-center shadow-xs">
                        <div className="w-14 h-14 mx-auto rounded-2xl bg-slate-100 dark:bg-[#1e293b] flex items-center justify-center text-[#94a3b8] mb-3">
                            <Building2 className="w-7 h-7" />
                        </div>
                        <h3 className="text-base font-bold text-[#0f172a] dark:text-[#f8fafc]">Tidak ada desa ditemukan</h3>
                        <p className="text-xs text-[#64748b] dark:text-[#94a3b8] mt-1 max-w-sm mx-auto">
                            Tidak ada data desa yang cocok dengan kata kunci pencarian atau filter yang dipilih.
                        </p>
                        <button
                            onClick={() => {
                                setSearchQuery('');
                                setStatusFilter('all');
                            }}
                            className="mt-4 px-4 py-2 text-xs font-semibold bg-[#bb152c] text-white rounded-xl hover:bg-[#a01225] transition"
                        >
                            Reset Pencarian
                        </button>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {filteredDesas.map((desa) => {
                            const isExpanded = !!expandedDesas[desa.id];
                            const isComplete = desa.persentase_terverifikasi === 100 && desa.total_tps > 0;
                            const hasPps = desa.pps_users && desa.pps_users.length > 0;

                            return (
                                <div
                                    key={desa.id}
                                    className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-xs overflow-hidden transition duration-150 hover:border-slate-300 dark:hover:border-slate-700"
                                >
                                    {/* Main Card Content */}
                                    <div className="p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                                        {/* Left Side: Village & PPS Details */}
                                        <div className="flex items-start gap-4">
                                            {/* Status Indicator Bar / Icon */}
                                            <div
                                                className={`w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 border ${
                                                    isComplete
                                                        ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/50'
                                                        : desa.persentase_terverifikasi > 0
                                                        ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-900/50'
                                                        : 'bg-slate-100 dark:bg-[#1e293b] text-slate-500 border-slate-200 dark:border-slate-800'
                                                }`}
                                            >
                                                <Building2 className="w-6 h-6" />
                                            </div>

                                            <div className="space-y-1.5">
                                                <div className="flex items-center gap-2.5 flex-wrap">
                                                    <h2 className="text-base font-bold font-heading text-[#0f172a] dark:text-[#f8fafc]">
                                                        {desa.nama}
                                                    </h2>
                                                    <span className="text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-[#1e293b] text-[#475569] dark:text-[#cbd5e1] border border-slate-200 dark:border-slate-700">
                                                        {desa.total_tps} TPS
                                                    </span>

                                                    {/* PPS Status Badge */}
                                                    {hasPps ? (
                                                        <span className="inline-flex items-center gap-1.5 text-[11px] font-medium px-2.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                            <UserCheck className="w-3.5 h-3.5 text-emerald-500" />
                                                            <span>PPS: {desa.pps_users.map(u => u.name || u.username).join(', ')}</span>
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center gap-1.5 text-[11px] font-medium px-2.5 py-0.5 rounded-md bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                            <UserX className="w-3.5 h-3.5 text-rose-500" />
                                                            <span>Belum Ada Akun PPS</span>
                                                        </span>
                                                    )}
                                                </div>

                                                {/* Mini Progress & Stats info */}
                                                <div className="flex items-center gap-3 text-xs text-[#64748b] dark:text-[#94a3b8] flex-wrap">
                                                    <span>Target Dokumen: <strong>{desa.target_dokumen}</strong> berkas</span>
                                                    <span>•</span>
                                                    <span className="text-emerald-600 dark:text-emerald-400 font-semibold">
                                                        {desa.total_terverifikasi} Terverifikasi ({desa.persentase_terverifikasi}%)
                                                    </span>
                                                    {desa.total_menunggu > 0 && (
                                                        <>
                                                            <span>•</span>
                                                            <span className="text-amber-600 dark:text-amber-400 font-medium">
                                                                {desa.total_menunggu} Menunggu
                                                            </span>
                                                        </>
                                                    )}
                                                    {desa.total_ditolak > 0 && (
                                                        <>
                                                            <span>•</span>
                                                            <span className="text-red-600 dark:text-red-400 font-medium">
                                                                {desa.total_ditolak} Ditolak
                                                            </span>
                                                        </>
                                                    )}
                                                    <span>•</span>
                                                    <span>{desa.total_masuk} Total Diunggah</span>
                                                </div>

                                                {/* Progress Bar */}
                                                <div className="w-full max-w-md h-2 bg-slate-100 dark:bg-[#1e293b] rounded-full overflow-hidden mt-2">
                                                    <div
                                                        className={`h-full transition-all duration-300 rounded-full ${
                                                            isComplete
                                                                ? 'bg-emerald-500'
                                                                : 'bg-[#bb152c]'
                                                        }`}
                                                        style={{ width: `${Math.min(desa.persentase_terverifikasi, 100)}%` }}
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                        {/* Right Side: Action Buttons */}
                                        <div className="flex items-center gap-2.5 self-end lg:self-center shrink-0 flex-wrap">
                                            {/* Button 1: Toggle Rincian TPS */}
                                            <button
                                                onClick={() => toggleExpand(desa.id)}
                                                className="px-3 py-2 rounded-xl text-xs font-medium border border-[#e2e8f0] dark:border-[#334155] hover:bg-[#f1f5f9] dark:hover:bg-[#1e293b] text-[#475569] dark:text-[#cbd5e1] transition cursor-pointer flex items-center gap-1.5"
                                                title="Lihat status TPS di desa ini"
                                            >
                                                <span>{isExpanded ? 'Tutup TPS' : `Rincian ${desa.total_tps} TPS`}</span>
                                                {isExpanded ? (
                                                    <ChevronUp className="w-3.5 h-3.5 text-[#94a3b8]" />
                                                ) : (
                                                    <ChevronDown className="w-3.5 h-3.5 text-[#94a3b8]" />
                                                )}
                                            </button>

                                            {/* Button 2: Lihat Dokumen TPS */}
                                            <Link
                                                href={`/dokumen/kecamatan?desa_id=${desa.id}`}
                                                className="px-3.5 py-2 rounded-xl text-xs font-semibold border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] transition flex items-center gap-1.5 shadow-xs"
                                                title="Lihat dan verifikasi berkas dokumen TPS desa ini"
                                            >
                                                <FolderOpen className="w-3.5 h-3.5 text-[#bb152c] dark:text-red-400" />
                                                <span>Dokumen TPS</span>
                                            </Link>

                                            {/* Button 3: Mode View PPS */}
                                            <a
                                                href={`/ppk/view-pps/${desa.id}`}
                                                className="px-3.5 py-2 rounded-xl text-xs font-semibold bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-700/50 transition flex items-center gap-1.5 shadow-xs"
                                                title="Masuk mode simulasi tampilan dashboard PPS desa ini"
                                            >
                                                <Eye className="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" />
                                                <span>Mode View PPS</span>
                                            </a>
                                        </div>
                                    </div>

                                    {/* Expanded Section: TPS Grid Overview */}
                                    {isExpanded && (
                                        <div className="border-t border-[#f1f5f9] dark:border-[#1e293b] bg-[#f8fafc]/70 dark:bg-[#0f172a]/30 p-4 lg:p-5">
                                            <div className="flex items-center justify-between mb-3">
                                                <div className="flex items-center gap-2">
                                                    <span className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                                        Daftar TPS Desa {desa.nama} ({desa.total_tps} TPS)
                                                    </span>
                                                    <span className="text-[11px] text-[#94a3b8]">
                                                        • Klik salah satu TPS untuk membuka rekap dokumen
                                                    </span>
                                                </div>

                                                <Link
                                                    href={`/dokumen/kecamatan?desa_id=${desa.id}`}
                                                    className="text-xs font-semibold text-[#bb152c] dark:text-red-400 hover:underline inline-flex items-center gap-1"
                                                >
                                                    <span>Buka Semua Dokumen TPS</span>
                                                    <ArrowUpRight className="w-3 h-3" />
                                                </Link>
                                            </div>

                                            {(!desa.tps_list || desa.tps_list.length === 0) ? (
                                                <div className="text-xs text-[#94a3b8] py-4 text-center">
                                                    Belum ada TPS terdaftar di desa ini.
                                                </div>
                                            ) : (
                                                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5">
                                                    {desa.tps_list.map((tps) => {
                                                        const isTpsComplete = tps.is_lengkap;
                                                        const hasTpsWaiting = tps.total_menunggu > 0;
                                                        const hasTpsRejected = tps.total_ditolak > 0;
                                                        const hasTpsUploaded = tps.total_masuk > 0;

                                                        let statusBadgeColor = 'bg-slate-100 dark:bg-[#1e293b] text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700';
                                                        let statusLabel = 'Kosong';

                                                        if (isTpsComplete) {
                                                            statusBadgeColor = 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
                                                            statusLabel = 'Lengkap';
                                                        } else if (hasTpsWaiting) {
                                                            statusBadgeColor = 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800';
                                                            statusLabel = `${tps.total_menunggu} Menunggu`;
                                                        } else if (hasTpsRejected) {
                                                            statusBadgeColor = 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 border-red-200 dark:border-red-800';
                                                            statusLabel = `${tps.total_ditolak} Ditolak`;
                                                        } else if (hasTpsUploaded) {
                                                            statusBadgeColor = 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800';
                                                            statusLabel = `${tps.total_terverifikasi}/${tps.target}`;
                                                        }

                                                        return (
                                                            <Link
                                                                key={tps.id}
                                                                href={`/dokumen/kecamatan?desa_id=${desa.id}`}
                                                                className={`p-2.5 rounded-xl border transition group hover:shadow-xs flex flex-col justify-between ${
                                                                    isTpsComplete
                                                                        ? 'bg-emerald-50/30 dark:bg-emerald-950/10 border-emerald-200/70 dark:border-emerald-900/40 hover:border-emerald-400'
                                                                        : 'bg-white dark:bg-[#151f32] border-[#e2e8f0] dark:border-[#1e293b] hover:border-[#bb152c]'
                                                                }`}
                                                            >
                                                                <div className="flex items-center justify-between mb-1.5">
                                                                    <span className="font-bold text-xs text-[#0f172a] dark:text-[#f8fafc] group-hover:text-[#bb152c] transition">
                                                                        {tps.nama}
                                                                    </span>
                                                                    {isTpsComplete ? (
                                                                        <CheckCircle2 className="w-3.5 h-3.5 text-emerald-500 shrink-0" />
                                                                    ) : hasTpsWaiting ? (
                                                                        <Clock className="w-3.5 h-3.5 text-amber-500 shrink-0" />
                                                                    ) : hasTpsRejected ? (
                                                                        <XCircle className="w-3.5 h-3.5 text-red-500 shrink-0" />
                                                                    ) : (
                                                                        <span className="w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-700" />
                                                                    )}
                                                                </div>

                                                                <div className="flex items-center justify-between text-[10px]">
                                                                    <span className="text-[#64748b] dark:text-[#94a3b8]">
                                                                        {tps.total_terverifikasi}/{tps.target} Berkas
                                                                    </span>
                                                                    <span className={`px-1.5 py-0.2 rounded font-medium border ${statusBadgeColor}`}>
                                                                        {statusLabel}
                                                                    </span>
                                                                </div>
                                                            </Link>
                                                        );
                                                    })}
                                                </div>
                                            )}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
