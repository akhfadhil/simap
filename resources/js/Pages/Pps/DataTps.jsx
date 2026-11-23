import React, { useState, useMemo } from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import {
    Vote,
    Building2,
    MapPin,
    CheckCircle2,
    Clock,
    XCircle,
    Search,
    Eye,
    ChevronDown,
    ChevronUp,
    Shield,
    Check,
    AlertCircle,
    UserCheck,
    UserX,
    FolderOpen,
    FileText
} from 'lucide-react';

export default function DataTps({
    desa = {},
    tpsList = [],
    stats = {},
    totalJenisAktif = 0,
    isAdminView = false
}) {
    const [searchQuery, setSearchQuery] = useState('');
    const [statusFilter, setStatusFilter] = useState('all'); // all | incomplete | complete | unassigned
    const [sortBy, setSortBy] = useState('name_asc'); // name_asc | progress_desc | progress_asc
    const [expandedTps, setExpandedTps] = useState({});

    // Toggle expand TPS detail
    const toggleExpand = (tpsId) => {
        setExpandedTps(prev => ({
            ...prev,
            [tpsId]: !prev[tpsId]
        }));
    };

    // Toggle expand all
    const allExpanded = tpsList.length > 0 && tpsList.every(t => expandedTps[t.id]);
    const toggleExpandAll = () => {
        if (allExpanded) {
            setExpandedTps({});
        } else {
            const next = {};
            tpsList.forEach(t => { next[t.id] = true; });
            setExpandedTps(next);
        }
    };

    // Filter & Sort TPS
    const filteredTps = useMemo(() => {
        let result = [...tpsList];

        // 1. Search filter
        if (searchQuery.trim()) {
            const query = searchQuery.toLowerCase().trim();
            result = result.filter(t => {
                const matchNama = t.nama?.toLowerCase().includes(query);
                const matchKpps = t.kpps_user && (
                    t.kpps_user.name?.toLowerCase().includes(query) ||
                    t.kpps_user.username?.toLowerCase().includes(query)
                );
                return matchNama || matchKpps;
            });
        }

        // 2. Status filter
        if (statusFilter === 'incomplete') {
            result = result.filter(t => !t.is_lengkap);
        } else if (statusFilter === 'complete') {
            result = result.filter(t => t.is_lengkap);
        } else if (statusFilter === 'unassigned') {
            result = result.filter(t => !t.kpps_user);
        }

        // 3. Sorting
        result.sort((a, b) => {
            if (sortBy === 'name_asc') {
                return a.nama.localeCompare(b.nama, undefined, { numeric: true });
            }
            if (sortBy === 'progress_desc') {
                return b.persentase - a.persentase;
            }
            if (sortBy === 'progress_asc') {
                return a.persentase - b.persentase;
            }
            return 0;
        });

        return result;
    }, [tpsList, searchQuery, statusFilter, sortBy]);

    return (
        <AdminLayout title="Data TPS & KPPS">
            <Head title={`Data TPS - Desa ${desa.nama || ''}`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2 mb-1">
                            <span className="text-[10px] font-bold tracking-[2px] text-red-600 dark:text-red-400 uppercase">
                                // PPS — DATA WILAYAH
                            </span>
                            {isAdminView && (
                                <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-500/10 text-amber-500 border border-amber-500/20">
                                    <Shield className="w-3 h-3" /> Mode Monitoring Admin
                                </span>
                            )}
                        </div>
                        <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] flex items-center gap-2.5">
                            <Vote className="w-6 h-6 text-[#bb152c]" />
                            <span>Data TPS & Petugas KPPS</span>
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1 flex items-center gap-1.5">
                            <Building2 className="w-3.5 h-3.5 text-teal-500" />
                            <span className="font-semibold text-[#0f172a] dark:text-[#f8fafc]">Desa {desa.nama}</span>
                            <span>•</span>
                            <MapPin className="w-3.5 h-3.5 text-blue-500" />
                            <span>Kecamatan {desa.kecamatan_nama}</span>
                        </p>
                    </div>

                    <Link
                        href="/dokumen/verifikasi"
                        className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#1e293b] transition shadow-xs self-start sm:self-auto cursor-pointer"
                    >
                        <FolderOpen className="w-4 h-4 text-[#bb152c]" />
                        <span>Ke Menu Verifikasi Dokumen</span>
                    </Link>
                </div>

                {/* Statistik Desa Cards */}
                <div className="grid grid-cols-2 sm:grid-cols-5 gap-3.5">
                    {/* Total TPS */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs">
                        <p className="text-[11px] text-[#475569] dark:text-[#94a3b8] font-medium">Total TPS</p>
                        <p className="text-xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] mt-1">
                            {stats.total_tps || 0} <span className="text-xs font-normal text-[#94a3b8]">TPS</span>
                        </p>
                        <p className="text-[10px] text-[#94a3b8] mt-0.5">Target {stats.total_target_dokumen || 0} berkas</p>
                    </div>

                    {/* KPPS Ditugaskan */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs">
                        <p className="text-[11px] text-blue-600 dark:text-blue-400 font-medium flex items-center gap-1">
                            <UserCheck className="w-3.5 h-3.5" />
                            <span>Akun KPPS</span>
                        </p>
                        <p className="text-xl font-bold font-heading text-blue-600 dark:text-blue-400 mt-1">
                            {stats.total_kpps_assigned || 0} <span className="text-xs font-normal text-[#94a3b8]">/ {stats.total_tps || 0}</span>
                        </p>
                        <p className="text-[10px] text-[#94a3b8] mt-0.5">
                            {(stats.total_tps - (stats.total_kpps_assigned || 0)) > 0
                                ? `${stats.total_tps - stats.total_kpps_assigned} belum assign`
                                : 'Semua terisi'}
                        </p>
                    </div>

                    {/* Dokumen Terverifikasi */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs">
                        <p className="text-[11px] text-teal-600 dark:text-teal-400 font-medium flex items-center gap-1">
                            <CheckCircle2 className="w-3.5 h-3.5" />
                            <span>Terverifikasi</span>
                        </p>
                        <p className="text-xl font-bold font-heading text-teal-600 dark:text-teal-400 mt-1">
                            {stats.total_terverifikasi || 0}
                        </p>
                        <p className="text-[10px] text-[#94a3b8] mt-0.5">{stats.persentase_terverifikasi || 0}% selesai</p>
                    </div>

                    {/* Menunggu Verifikasi */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs">
                        <p className="text-[11px] text-amber-600 dark:text-amber-400 font-medium flex items-center gap-1">
                            <Clock className="w-3.5 h-3.5" />
                            <span>Menunggu</span>
                        </p>
                        <p className="text-xl font-bold font-heading text-amber-600 dark:text-amber-400 mt-1">
                            {stats.total_menunggu || 0}
                        </p>
                        <p className="text-[10px] text-[#94a3b8] mt-0.5">
                            {stats.total_ditolak ? `${stats.total_ditolak} berkas ditolak` : 'Belum ada ditolak'}
                        </p>
                    </div>

                    {/* Progress Bar Desa */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs col-span-2 sm:col-span-1 flex flex-col justify-between">
                        <div>
                            <p className="text-[11px] text-[#475569] dark:text-[#94a3b8] font-medium">Progres Desa</p>
                            <p className="text-xl font-bold font-heading text-[#bb152c] dark:text-red-400 mt-1">
                                {stats.persentase_terverifikasi || 0}%
                            </p>
                        </div>
                        <div className="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden mt-2">
                            <div
                                className="h-full bg-teal-500 rounded-full transition-all duration-500"
                                style={{ width: `${stats.persentase_terverifikasi || 0}%` }}
                            />
                        </div>
                    </div>
                </div>

                {/* Filter & Search Toolbar */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div className="flex items-center gap-2 flex-1 max-w-md">
                        <div className="relative w-full">
                            <Search className="w-4 h-4 text-[#94a3b8] absolute left-3.5 top-1/2 -translate-y-1/2" />
                            <input
                                type="text"
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                placeholder="Cari nomor TPS atau nama petugas KPPS..."
                                className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl pl-9 pr-3.5 py-2 text-xs outline-none focus:border-[#bb152c]"
                            />
                        </div>
                    </div>

                    <div className="flex items-center gap-2 flex-wrap">
                        {/* Status Filter */}
                        <div className="flex items-center gap-1 bg-[#f8fafc] dark:bg-[#0b0f19] p-1 rounded-xl border border-[#e2e8f0] dark:border-[#1e293b] text-xs">
                            <button
                                type="button"
                                onClick={() => setStatusFilter('all')}
                                className={`px-2.5 py-1 rounded-lg font-medium transition cursor-pointer ${
                                    statusFilter === 'all'
                                        ? 'bg-white dark:bg-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] shadow-xs'
                                        : 'text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc]'
                                }`}
                            >
                                Semua ({tpsList.length})
                            </button>
                            <button
                                type="button"
                                onClick={() => setStatusFilter('incomplete')}
                                className={`px-2.5 py-1 rounded-lg font-medium transition cursor-pointer ${
                                    statusFilter === 'incomplete'
                                        ? 'bg-[#bb152c] text-white shadow-xs font-bold'
                                        : 'text-[#94a3b8] hover:text-[#bb152c]'
                                }`}
                            >
                                Belum Lengkap
                            </button>
                            <button
                                type="button"
                                onClick={() => setStatusFilter('complete')}
                                className={`px-2.5 py-1 rounded-lg font-medium transition cursor-pointer ${
                                    statusFilter === 'complete'
                                        ? 'bg-teal-500 text-white shadow-xs font-bold'
                                        : 'text-[#94a3b8] hover:text-teal-500'
                                }`}
                            >
                                Lengkap
                            </button>
                            <button
                                type="button"
                                onClick={() => setStatusFilter('unassigned')}
                                className={`px-2.5 py-1 rounded-lg font-medium transition cursor-pointer ${
                                    statusFilter === 'unassigned'
                                        ? 'bg-slate-700 text-white shadow-xs font-bold'
                                        : 'text-[#94a3b8] hover:text-slate-700 dark:hover:text-slate-300'
                                }`}
                            >
                                Belum Assign
                            </button>
                        </div>

                        {/* Sort Selector */}
                        <select
                            value={sortBy}
                            onChange={(e) => setSortBy(e.target.value)}
                            className="bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-1.5 text-xs outline-none focus:border-[#bb152c]"
                        >
                            <option value="name_asc">Urut Nomor TPS</option>
                            <option value="progress_desc">Progres Tertinggi</option>
                            <option value="progress_asc">Progres Terendah</option>
                        </select>

                        {/* Toggle Expand All */}
                        <button
                            type="button"
                            onClick={toggleExpandAll}
                            className="px-3 py-1.5 rounded-xl text-xs font-semibold bg-[#f1f5f9] dark:bg-[#1f2937] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition flex items-center gap-1.5 cursor-pointer"
                        >
                            {allExpanded ? <ChevronUp className="w-3.5 h-3.5" /> : <ChevronDown className="w-3.5 h-3.5" />}
                            <span>{allExpanded ? 'Tutup Semua' : 'Buka Semua'}</span>
                        </button>
                    </div>
                </div>

                {/* List TPS Cards */}
                {filteredTps.length === 0 ? (
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-12 text-center shadow-xs space-y-2">
                        <Vote className="w-10 h-10 text-[#94a3b8] mx-auto" />
                        <h3 className="font-bold font-heading text-sm text-[#0f172a] dark:text-[#f8fafc]">
                            Tidak ada TPS yang cocok
                        </h3>
                        <p className="text-xs text-[#94a3b8]">
                            Coba ubah kata kunci pencarian atau sesuaikan filter di atas.
                        </p>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {filteredTps.map(tps => {
                            const isExpanded = !!expandedTps[tps.id];

                            return (
                                <div
                                    key={tps.id}
                                    className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-xs overflow-hidden transition"
                                >
                                    {/* TPS Card Header */}
                                    <div className="p-4 sm:px-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div className="flex items-center gap-3.5 flex-1">
                                            <div className="w-11 h-11 rounded-xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center border border-red-100 dark:border-red-900/40 text-[#bb152c] dark:text-red-400 font-bold font-heading text-sm shrink-0">
                                                {tps.nama?.replace(/\D/g, '') || tps.nama}
                                            </div>

                                            <div className="space-y-1">
                                                <div className="flex items-center gap-2 flex-wrap">
                                                    <h3 className="font-bold font-heading text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                                        TPS {tps.nama}
                                                    </h3>

                                                    {tps.is_lengkap ? (
                                                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-400 border border-teal-100 dark:border-teal-900/50">
                                                            <Check className="w-3 h-3" /> Lengkap
                                                        </span>
                                                    ) : tps.total_menunggu > 0 ? (
                                                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-100 dark:border-amber-900/50">
                                                            <Clock className="w-3 h-3" /> {tps.total_menunggu} Menunggu Verifikasi
                                                        </span>
                                                    ) : null}
                                                </div>

                                                <div className="flex items-center gap-2 text-xs text-[#475569] dark:text-[#94a3b8]">
                                                    {tps.kpps_user ? (
                                                        <span className="flex items-center gap-1 text-[#0f172a] dark:text-[#f8fafc] font-medium text-[11px]">
                                                            <UserCheck className="w-3.5 h-3.5 text-blue-500" />
                                                            {tps.kpps_user.name} <span className="text-[#94a3b8] font-mono">(@{tps.kpps_user.username})</span>
                                                        </span>
                                                    ) : (
                                                        <span className="flex items-center gap-1 text-slate-400 dark:text-slate-500 italic text-[11px]">
                                                            <UserX className="w-3.5 h-3.5" />
                                                            Belum ada akun KPPS ditugaskan
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                        </div>

                                        <div className="flex items-center justify-between sm:justify-end gap-3 pt-3 sm:pt-0 border-t sm:border-0 border-[#f1f5f9] dark:border-[#1e293b]">
                                            {/* Progress Info */}
                                            <div className="flex flex-col items-end mr-2">
                                                <div className="flex items-center gap-2 w-32 sm:w-36">
                                                    <div className="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                                        <div
                                                            className={`h-full rounded-full transition-all duration-300 ${
                                                                tps.is_lengkap ? 'bg-teal-500' : 'bg-[#bb152c]'
                                                            }`}
                                                            style={{ width: `${tps.persentase}%` }}
                                                        />
                                                    </div>
                                                    <span className="text-[10px] font-mono text-[#94a3b8] w-8 text-right">
                                                        {tps.persentase}%
                                                    </span>
                                                </div>
                                                <p className="text-[10px] text-[#94a3b8] mt-0.5">
                                                    {tps.total_terverifikasi}/{tps.target} dokumen selesai
                                                </p>
                                            </div>

                                            {/* Actions */}
                                            <div className="flex items-center gap-1.5">
                                                <a
                                                    href={`/pps/view-tps/${tps.id}`}
                                                    className="px-3 py-1.5 rounded-xl text-xs font-semibold bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 hover:bg-[#bb152c] hover:text-white dark:hover:bg-[#bb152c] dark:hover:text-white border border-red-200 dark:border-red-900/50 transition flex items-center gap-1.5 cursor-pointer"
                                                    title="Simulasi masuk ke dashboard KPPS untuk TPS ini"
                                                >
                                                    <Eye className="w-3.5 h-3.5" />
                                                    <span>View KPPS</span>
                                                </a>

                                                <button
                                                    type="button"
                                                    onClick={() => toggleExpand(tps.id)}
                                                    className="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                                                >
                                                    {isExpanded ? <ChevronUp className="w-4 h-4" /> : <ChevronDown className="w-4 h-4" />}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {/* TPS Dokumen C-Hasil Accordion */}
                                    {isExpanded && (
                                        <div className="border-t border-[#f1f5f9] dark:border-[#1e293b] p-4 bg-[#f8fafc]/50 dark:bg-[#0b0f19]/30 space-y-3">
                                            <div className="flex items-center justify-between">
                                                <span className="text-[10px] font-bold uppercase tracking-wider text-[#94a3b8]">
                                                    Status Berkas C-Hasil TPS {tps.nama}
                                                </span>
                                                <Link
                                                    href={`/dokumen/verifikasi?tps_id=${tps.id}`}
                                                    className="text-[11px] font-semibold text-[#bb152c] dark:text-red-400 hover:underline flex items-center gap-1"
                                                >
                                                    <span>Buka Menu Verifikasi TPS Ini</span>
                                                    <span>→</span>
                                                </Link>
                                            </div>

                                            <div className="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                                {(tps.dokumens || []).length > 0 ? (
                                                    tps.dokumens.map(doc => (
                                                        <div
                                                            key={doc.id}
                                                            className={`p-2.5 rounded-xl border text-xs flex flex-col justify-between ${
                                                                doc.status === 'terverifikasi'
                                                                    ? 'bg-teal-50/50 dark:bg-teal-950/20 border-teal-200 dark:border-teal-900/50 text-teal-800 dark:text-teal-300'
                                                                    : doc.status === 'menunggu_verifikasi'
                                                                    ? 'bg-amber-50/50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-900/50 text-amber-800 dark:text-amber-300'
                                                                    : 'bg-red-50/50 dark:bg-red-950/20 border-red-200 dark:border-red-900/50 text-red-800 dark:text-red-300'
                                                            }`}
                                                        >
                                                            <div className="flex items-center justify-between mb-1">
                                                                <span className="font-bold uppercase tracking-wider text-[10px]">
                                                                    {doc.jenis}
                                                                </span>
                                                                {doc.status === 'terverifikasi' ? (
                                                                    <CheckCircle2 className="w-3.5 h-3.5 text-teal-600" />
                                                                ) : doc.status === 'menunggu_verifikasi' ? (
                                                                    <Clock className="w-3.5 h-3.5 text-amber-600" />
                                                                ) : (
                                                                    <XCircle className="w-3.5 h-3.5 text-red-600" />
                                                                )}
                                                            </div>
                                                            <p className="text-[10px] truncate opacity-80" title={doc.file_name}>
                                                                {doc.file_name}
                                                            </p>
                                                        </div>
                                                    ))
                                                ) : (
                                                    <p className="col-span-full text-xs text-[#94a3b8] py-2 italic text-center">
                                                        Belum ada berkas C-Hasil yang diunggah oleh KPPS untuk TPS ini.
                                                    </p>
                                                )}
                                            </div>
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
