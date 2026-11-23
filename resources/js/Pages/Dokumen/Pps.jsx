import React, { useState, useMemo } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import {
    FolderOpen,
    FileText,
    CheckCircle2,
    Clock,
    XCircle,
    Download,
    X,
    Eye,
    Building2,
    MapPin,
    AlertCircle,
    Search,
    ChevronDown,
    ChevronUp,
    Shield,
    Check,
    RotateCcw
} from 'lucide-react';

export default function DokumenPps({
    desa,
    tpsList = [],
    tpsOptions = [],
    selectedTpsId = null,
    aktifJenis = [],
    aktifDokumenMap = {},
    isAdminView = false
}) {
    const { flash = {} } = usePage().props;

    // Filter & Search states
    const [searchQuery, setSearchQuery] = useState('');
    const [statusFilter, setStatusFilter] = useState('all'); // all | pending | complete | incomplete
    const [expandedTps, setExpandedTps] = useState({});

    // Modal Preview & Tolak
    const [previewDoc, setPreviewDoc] = useState(null);
    const [rejectDoc, setRejectDoc] = useState(null);
    const [rejectComment, setRejectComment] = useState('');
    const [isProcessing, setIsProcessing] = useState(false);

    // Hitung jenis pemilihan aktif
    const activeElectionEntries = Object.keys(aktifDokumenMap).length > 0
        ? Object.entries(aktifDokumenMap)
        : (aktifJenis.length > 0 ? aktifJenis.map(j => [j.toUpperCase(), j.toUpperCase()]) : []);
    const targetJenisCount = activeElectionEntries.length > 0 ? activeElectionEntries.length : 1;

    // Helper filter dokumen hanya jenis aktif
    const getActiveDocs = (dokumens) => {
        if (!dokumens || dokumens.length === 0) return [];
        if (!aktifJenis || aktifJenis.length === 0) return dokumens;
        return dokumens.filter(doc => aktifJenis.includes(doc.jenis?.toLowerCase()));
    };

    // Format tanggal
    const formatDateTime = (dateString) => {
        if (!dateString) return '';
        try {
            const d = new Date(dateString);
            return d.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        } catch {
            return dateString;
        }
    };

    // Statistik Desa
    const totalTps = tpsList.length;
    const totalTarget = totalTps * targetJenisCount;

    let totalMasuk = 0;
    let totalTerverifikasi = 0;
    let totalMenunggu = 0;
    let totalDitolak = 0;

    tpsList.forEach(tps => {
        const docs = getActiveDocs(tps.dokumens);
        totalMasuk += docs.length;
        docs.forEach(d => {
            if (d.status === 'terverifikasi') totalTerverifikasi++;
            else if (d.status === 'ditolak') totalDitolak++;
            else totalMenunggu++;
        });
    });

    const completionPercent = totalTarget > 0 ? Math.round((totalTerverifikasi / totalTarget) * 100) : 0;

    // Filter daftar TPS
    const filteredTpsList = useMemo(() => {
        return tpsList.filter(tps => {
            // Search query filter
            const matchSearch = !searchQuery.trim() || tps.nama?.toLowerCase().includes(searchQuery.toLowerCase());
            if (!matchSearch) return false;

            // Status filter
            const docs = getActiveDocs(tps.dokumens);
            const verifiedCount = docs.filter(d => d.status === 'terverifikasi').length;
            const hasPending = docs.some(d => d.status === 'menunggu_verifikasi');

            if (statusFilter === 'pending') {
                return hasPending;
            }
            if (statusFilter === 'complete') {
                return targetJenisCount > 0 && verifiedCount === targetJenisCount;
            }
            if (statusFilter === 'incomplete') {
                return verifiedCount < targetJenisCount;
            }

            return true;
        });
    }, [tpsList, searchQuery, statusFilter, targetJenisCount, aktifJenis]);

    // Accordion Toggle
    const allExpanded = filteredTpsList.length > 0 && filteredTpsList.every(t => expandedTps[t.id]);

    const toggleExpandAll = () => {
        if (allExpanded) {
            setExpandedTps({});
        } else {
            const next = {};
            filteredTpsList.forEach(t => { next[t.id] = true; });
            setExpandedTps(next);
        }
    };

    const toggleTps = (id) => {
        setExpandedTps(prev => ({
            ...prev,
            [id]: !prev[id]
        }));
    };

    // Aksi Verifikasi Dokumen
    const handleVerify = (doc) => {
        if (isAdminView) return;
        setIsProcessing(true);
        router.post(`/dokumen/${doc.id}/verifikasi`, {
            aksi: 'terverifikasi'
        }, {
            preserveScroll: true,
            onFinish: () => {
                setIsProcessing(false);
                if (previewDoc && previewDoc.id === doc.id) {
                    setPreviewDoc(prev => ({ ...prev, status: 'terverifikasi' }));
                }
            }
        });
    };

    // Aksi Tolak Dokumen
    const handleOpenReject = (doc) => {
        setRejectDoc(doc);
        setRejectComment('');
    };

    const handleRejectSubmit = (e) => {
        e.preventDefault();
        if (!rejectDoc || !rejectComment.trim()) return;

        setIsProcessing(true);
        router.post(`/dokumen/${rejectDoc.id}/verifikasi`, {
            aksi: 'ditolak',
            komentar: rejectComment.trim()
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setRejectDoc(null);
                setRejectComment('');
                if (previewDoc && previewDoc.id === rejectDoc.id) {
                    setPreviewDoc(prev => ({ ...prev, status: 'ditolak', komentar: rejectComment.trim() }));
                }
            },
            onFinish: () => setIsProcessing(false)
        });
    };

    return (
        <AdminLayout title="Verifikasi Dokumen C-Hasil">
            <Head title="Verifikasi Dokumen C-Hasil - PPS" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2 mb-1">
                            <span className="text-[10px] font-bold tracking-[2px] text-red-600 dark:text-red-400 uppercase">
                                // PPS — VERIFIKASI DOKUMEN
                            </span>
                            {isAdminView && (
                                <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-500/10 text-amber-500 border border-amber-500/20">
                                    <Shield className="w-3 h-3" /> Mode Monitoring Admin (Read-Only)
                                </span>
                            )}
                        </div>
                        <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] flex items-center gap-2.5">
                            <FolderOpen className="w-6 h-6 text-[#bb152c]" />
                            <span>Rekap Dokumen C-Hasil TPS</span>
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1 flex items-center gap-1.5">
                            <Building2 className="w-3.5 h-3.5 text-teal-500" />
                            <span className="font-semibold text-[#0f172a] dark:text-[#f8fafc]">Desa {desa?.nama}</span>
                            <span>•</span>
                            <MapPin className="w-3.5 h-3.5 text-blue-500" />
                            <span>Kecamatan {desa?.kecamatan?.nama}</span>
                        </p>
                    </div>
                </div>

                {/* Flash Messages */}
                {flash?.success && (
                    <div className="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 flex items-center gap-3 text-xs text-emerald-800 dark:text-emerald-300">
                        <CheckCircle2 className="w-5 h-5 text-emerald-600 shrink-0" />
                        <span className="font-medium">{flash.success}</span>
                    </div>
                )}

                {/* Statistik Desa Cards */}
                <div className="grid grid-cols-2 sm:grid-cols-5 gap-3.5">
                    {/* Total TPS */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs">
                        <p className="text-[11px] text-[#475569] dark:text-[#94a3b8] font-medium">Total TPS</p>
                        <p className="text-xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] mt-1">
                            {totalTps} <span className="text-xs font-normal text-[#94a3b8]">TPS</span>
                        </p>
                        <p className="text-[10px] text-[#94a3b8] mt-0.5">Target {totalTarget} berkas</p>
                    </div>

                    {/* Terverifikasi */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs">
                        <p className="text-[11px] text-teal-600 dark:text-teal-400 font-medium flex items-center gap-1">
                            <CheckCircle2 className="w-3.5 h-3.5" />
                            <span>Terverifikasi</span>
                        </p>
                        <p className="text-xl font-bold font-heading text-teal-600 dark:text-teal-400 mt-1">
                            {totalTerverifikasi}
                        </p>
                        <p className="text-[10px] text-[#94a3b8] mt-0.5">{completionPercent}% dari target</p>
                    </div>

                    {/* Menunggu Verifikasi */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs">
                        <p className="text-[11px] text-amber-600 dark:text-amber-400 font-medium flex items-center gap-1">
                            <Clock className="w-3.5 h-3.5" />
                            <span>Menunggu</span>
                        </p>
                        <p className="text-xl font-bold font-heading text-amber-600 dark:text-amber-400 mt-1">
                            {totalMenunggu}
                        </p>
                        <p className="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5 font-medium">
                            {totalMenunggu > 0 ? 'Perlu tindakan' : 'Semua diperiksa'}
                        </p>
                    </div>

                    {/* Ditolak */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs">
                        <p className="text-[11px] text-red-600 dark:text-red-400 font-medium flex items-center gap-1">
                            <XCircle className="w-3.5 h-3.5" />
                            <span>Ditolak</span>
                        </p>
                        <p className="text-xl font-bold font-heading text-red-600 dark:text-red-400 mt-1">
                            {totalDitolak}
                        </p>
                        <p className="text-[10px] text-[#94a3b8] mt-0.5">Perlu revisi KPPS</p>
                    </div>

                    {/* Progress Desa */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs col-span-2 sm:col-span-1 flex flex-col justify-between">
                        <div>
                            <p className="text-[11px] text-[#475569] dark:text-[#94a3b8] font-medium">Progres Desa</p>
                            <p className="text-xl font-bold font-heading text-[#bb152c] dark:text-red-400 mt-1">
                                {completionPercent}%
                            </p>
                        </div>
                        <div className="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden mt-2">
                            <div
                                className="h-full bg-teal-500 rounded-full transition-all duration-500"
                                style={{ width: `${completionPercent}%` }}
                            />
                        </div>
                    </div>
                </div>

                {/* Toolbar Filter & Search */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div className="flex items-center gap-2 flex-1 max-w-md">
                        <div className="relative w-full">
                            <Search className="w-4 h-4 text-[#94a3b8] absolute left-3.5 top-1/2 -translate-y-1/2" />
                            <input
                                type="text"
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                placeholder="Cari TPS (misal: 001, TPS 02)..."
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
                                Semua ({totalTps})
                            </button>
                            <button
                                type="button"
                                onClick={() => setStatusFilter('pending')}
                                className={`px-2.5 py-1 rounded-lg font-medium transition cursor-pointer ${
                                    statusFilter === 'pending'
                                        ? 'bg-amber-500 text-white shadow-xs font-bold'
                                        : 'text-[#94a3b8] hover:text-amber-500'
                                }`}
                            >
                                Perlu Tindakan ({totalMenunggu})
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
                        </div>

                        {/* Toggle Expand All */}
                        <button
                            type="button"
                            onClick={toggleExpandAll}
                            className="px-3 py-2 rounded-xl text-xs font-semibold bg-[#f1f5f9] dark:bg-[#1f2937] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition flex items-center gap-1.5 cursor-pointer"
                        >
                            {allExpanded ? <ChevronUp className="w-3.5 h-3.5" /> : <ChevronDown className="w-3.5 h-3.5" />}
                            <span>{allExpanded ? 'Tutup Semua' : 'Buka Semua'}</span>
                        </button>
                    </div>
                </div>

                {/* List TPS Cards */}
                {filteredTpsList.length === 0 ? (
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-12 text-center shadow-xs space-y-2">
                        <FolderOpen className="w-10 h-10 text-[#94a3b8] mx-auto" />
                        <h3 className="font-bold font-heading text-sm text-[#0f172a] dark:text-[#f8fafc]">
                            Tidak ada TPS yang cocok
                        </h3>
                        <p className="text-xs text-[#94a3b8]">
                            Coba ubah kata kunci pencarian atau ganti filter status di atas.
                        </p>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {filteredTpsList.map(tps => {
                            const isExpanded = !!expandedTps[tps.id];
                            const docs = getActiveDocs(tps.dokumens);
                            const docsMap = {};
                            docs.forEach(d => {
                                if (d.jenis) docsMap[d.jenis.toUpperCase()] = d;
                            });

                            const verifiedCount = docs.filter(d => d.status === 'terverifikasi').length;
                            const pendingCount = docs.filter(d => d.status === 'menunggu_verifikasi').length;
                            const rejectedCount = docs.filter(d => d.status === 'ditolak').length;
                            const tpsPercent = targetJenisCount > 0 ? Math.round((verifiedCount / targetJenisCount) * 100) : 0;
                            const isComplete = verifiedCount === targetJenisCount;

                            return (
                                <div
                                    key={tps.id}
                                    className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-xs overflow-hidden transition"
                                >
                                    {/* TPS Card Header */}
                                    <div
                                        onClick={() => toggleTps(tps.id)}
                                        className="p-4 sm:px-5 flex items-center justify-between gap-4 cursor-pointer hover:bg-slate-50 dark:hover:bg-[#1f2937]/40 transition"
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center border border-red-100 dark:border-red-900/40 text-[#bb152c] dark:text-red-400 font-bold font-heading text-sm">
                                                {tps.nama?.replace(/\D/g, '') || tps.nama}
                                            </div>
                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <h3 className="font-bold font-heading text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                                        TPS {tps.nama}
                                                    </h3>
                                                    {isComplete ? (
                                                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-400 border border-teal-100 dark:border-teal-900/50">
                                                            <Check className="w-3 h-3" /> Lengkap
                                                        </span>
                                                    ) : pendingCount > 0 ? (
                                                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-100 dark:border-amber-900/50 animate-pulse">
                                                            <Clock className="w-3 h-3" /> {pendingCount} Perlu Verifikasi
                                                        </span>
                                                    ) : null}
                                                </div>
                                                <p className="text-[11px] text-[#94a3b8] mt-0.5">
                                                    {verifiedCount} dari {targetJenisCount} dokumen C-Hasil terverifikasi
                                                </p>
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-4">
                                            {/* Mini Progress */}
                                            <div className="hidden sm:flex items-center gap-2 w-36">
                                                <div className="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                                    <div
                                                        className={`h-full rounded-full transition-all duration-300 ${
                                                            isComplete ? 'bg-teal-500' : 'bg-[#bb152c]'
                                                        }`}
                                                        style={{ width: `${tpsPercent}%` }}
                                                    />
                                                </div>
                                                <span className="text-[10px] font-mono text-[#94a3b8] w-8 text-right">
                                                    {tpsPercent}%
                                                </span>
                                            </div>

                                            <button
                                                type="button"
                                                className="p-1 text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc] transition"
                                            >
                                                {isExpanded ? <ChevronUp className="w-4 h-4" /> : <ChevronDown className="w-4 h-4" />}
                                            </button>
                                        </div>
                                    </div>

                                    {/* TPS Documents Accordion Body */}
                                    {isExpanded && (
                                        <div className="border-t border-[#f1f5f9] dark:border-[#1e293b] p-4 sm:p-5 bg-[#f8fafc]/50 dark:bg-[#0b0f19]/30 space-y-3">
                                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                {activeElectionEntries.map(([key, label]) => {
                                                    const doc = docsMap[key.toUpperCase()];
                                                    const isVerified = doc?.status === 'terverifikasi';
                                                    const isPending = doc?.status === 'menunggu_verifikasi';
                                                    const isRejected = doc?.status === 'ditolak';

                                                    return (
                                                        <div
                                                            key={key}
                                                            className={`p-3.5 rounded-xl border transition flex flex-col justify-between ${
                                                                doc
                                                                    ? isVerified
                                                                        ? 'bg-white dark:bg-[#151f32] border-teal-200 dark:border-teal-900/60 shadow-xs'
                                                                        : isPending
                                                                        ? 'bg-white dark:bg-[#151f32] border-amber-300 dark:border-amber-800/80 shadow-sm'
                                                                        : 'bg-white dark:bg-[#151f32] border-red-200 dark:border-red-900/60 shadow-xs'
                                                                    : 'bg-slate-50/50 dark:bg-[#0b0f19]/50 border-dashed border-[#e2e8f0] dark:border-[#1e293b] opacity-75'
                                                            }`}
                                                        >
                                                            {/* Card Top: Jenis Pemilu & Status */}
                                                            <div>
                                                                <div className="flex items-start justify-between gap-2 mb-2">
                                                                    <div className="flex items-center gap-2">
                                                                        <FileText className={`w-4 h-4 ${
                                                                            doc ? (isVerified ? 'text-teal-600' : isPending ? 'text-amber-500' : 'text-red-500') : 'text-[#94a3b8]'
                                                                        }`} />
                                                                        <p className="font-bold text-xs text-[#0f172a] dark:text-[#f8fafc]">
                                                                            {label}
                                                                        </p>
                                                                    </div>

                                                                    {doc ? (
                                                                        <span className={`inline-flex px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider ${
                                                                            isVerified
                                                                                ? 'bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-400 border border-teal-200 dark:border-teal-800'
                                                                                : isPending
                                                                                ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200 dark:border-amber-800'
                                                                                : 'bg-red-50 text-red-700 dark:bg-red-950/60 dark:text-red-400 border border-red-200 dark:border-red-800'
                                                                        }`}>
                                                                            {doc.status?.replace('_', ' ')}
                                                                        </span>
                                                                    ) : (
                                                                        <span className="text-[9px] text-[#94a3b8] font-medium italic">
                                                                            Belum diupload
                                                                        </span>
                                                                    )}
                                                                </div>

                                                                {/* File Details */}
                                                                {doc ? (
                                                                    <div className="text-[11px] text-[#475569] dark:text-[#94a3b8] space-y-1 mb-3">
                                                                        <p className="truncate font-mono text-[10px]" title={doc.file_name}>
                                                                            {doc.file_name}
                                                                        </p>
                                                                        <p className="text-[10px] text-[#94a3b8]">
                                                                            Diupload: {formatDateTime(doc.created_at || doc.updated_at)}
                                                                        </p>
                                                                        {doc.verifier && (
                                                                            <p className="text-[10px] text-teal-600 dark:text-teal-400">
                                                                                Diverifikasi oleh: {doc.verifier.name}
                                                                            </p>
                                                                        )}
                                                                    </div>
                                                                ) : (
                                                                    <p className="text-[11px] text-[#94a3b8] my-3 italic">
                                                                        Menunggu KPPS mengunggah berkas C-Hasil.
                                                                    </p>
                                                                )}

                                                                {/* Catatan Penolakan jika ada */}
                                                                {isRejected && doc.komentar && (
                                                                    <div className="mb-3 p-2 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-100 dark:border-red-900/50 text-[11px] text-red-700 dark:text-red-400">
                                                                        <p className="font-bold text-[9px] uppercase tracking-wider mb-0.5 flex items-center gap-1">
                                                                            <AlertCircle className="w-3 h-3" /> Alasan Ditolak:
                                                                        </p>
                                                                        <p>{doc.komentar}</p>
                                                                    </div>
                                                                )}
                                                            </div>

                                                            {/* Card Bottom: Action Buttons */}
                                                            {doc && (
                                                                <div className="pt-2.5 border-t border-[#f1f5f9] dark:border-[#1e293b] flex items-center justify-between gap-1.5 flex-wrap">
                                                                    <div className="flex items-center gap-1">
                                                                        <button
                                                                            type="button"
                                                                            onClick={() => setPreviewDoc(doc)}
                                                                            className="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-[#f1f5f9] dark:bg-[#1f2937] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition flex items-center gap-1 cursor-pointer"
                                                                        >
                                                                            <Eye className="w-3 h-3" />
                                                                            <span>Preview</span>
                                                                        </button>
                                                                        <a
                                                                            href={`/dokumen/${doc.id}/download`}
                                                                            className="p-1 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                                                            title="Unduh File"
                                                                        >
                                                                            <Download className="w-3.5 h-3.5" />
                                                                        </a>
                                                                    </div>

                                                                    {/* Action Verification (Only for actual PPS role) */}
                                                                    {!isAdminView && (
                                                                        <div className="flex items-center gap-1">
                                                                            {/* Tolak Button */}
                                                                            {!isRejected && (
                                                                                <button
                                                                                    type="button"
                                                                                    onClick={() => handleOpenReject(doc)}
                                                                                    disabled={isProcessing}
                                                                                    className="px-2 py-1 rounded-lg text-[11px] font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 border border-red-200 dark:border-red-900/60 transition cursor-pointer"
                                                                                >
                                                                                    Tolak
                                                                                </button>
                                                                            )}

                                                                            {/* Verifikasi Button */}
                                                                            {!isVerified && (
                                                                                <button
                                                                                    type="button"
                                                                                    onClick={() => handleVerify(doc)}
                                                                                    disabled={isProcessing}
                                                                                    className="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-teal-600 hover:bg-teal-700 text-white shadow-xs transition flex items-center gap-1 cursor-pointer"
                                                                                >
                                                                                    <Check className="w-3 h-3" />
                                                                                    <span>Verifikasi</span>
                                                                                </button>
                                                                            )}
                                                                        </div>
                                                                    )}
                                                                </div>
                                                            )}
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                )}
            </div>

            {/* MODAL PREVIEW DOKUMEN */}
            {previewDoc && (
                <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6 overflow-hidden">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-4xl h-[90vh] flex flex-col shadow-2xl overflow-hidden">
                        {/* Modal Header */}
                        <div className="px-5 py-3.5 border-b border-[#f1f5f9] dark:border-[#1e293b] flex items-center justify-between gap-3">
                            <div>
                                <h3 className="font-bold font-heading text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                    Preview Berkas C-Hasil: {previewDoc.jenis?.toUpperCase()}
                                </h3>
                                <p className="text-[11px] text-[#94a3b8]">
                                    {previewDoc.file_name} • Diunggah oleh {previewDoc.uploader?.name || 'Petugas KPPS'}
                                </p>
                            </div>

                            <div className="flex items-center gap-2">
                                <a
                                    href={`/dokumen/${previewDoc.id}/download`}
                                    className="px-3 py-1.5 rounded-xl text-xs font-semibold bg-[#f1f5f9] dark:bg-[#1f2937] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition flex items-center gap-1.5"
                                >
                                    <Download className="w-3.5 h-3.5" />
                                    <span>Unduh</span>
                                </a>
                                <button
                                    onClick={() => setPreviewDoc(null)}
                                    className="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                                >
                                    <X className="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        {/* Modal Body: iframe preview */}
                        <div className="flex-1 bg-slate-900 relative">
                            <iframe
                                src={`/dokumen/${previewDoc.id}/preview`}
                                title="Preview Dokumen"
                                className="w-full h-full border-0"
                            />
                        </div>

                        {/* Modal Footer: Action Verifikasi & Tolak */}
                        <div className="px-5 py-3 border-t border-[#f1f5f9] dark:border-[#1e293b] flex items-center justify-between gap-3 bg-[#f8fafc] dark:bg-[#0b0f19]">
                            <div className="flex items-center gap-2">
                                <span className={`inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${
                                    previewDoc.status === 'terverifikasi'
                                        ? 'bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-400 border border-teal-200'
                                        : previewDoc.status === 'ditolak'
                                        ? 'bg-red-50 text-red-700 dark:bg-red-950/60 dark:text-red-400 border border-red-200'
                                        : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200'
                                }`}>
                                    Status: {previewDoc.status?.replace('_', ' ')}
                                </span>
                                {previewDoc.komentar && (
                                    <span className="text-xs text-red-500 truncate max-w-xs">
                                        Catatan: {previewDoc.komentar}
                                    </span>
                                )}
                            </div>

                            <div className="flex items-center gap-2">
                                {!isAdminView && (
                                    <>
                                        {previewDoc.status !== 'ditolak' && (
                                            <button
                                                type="button"
                                                onClick={() => handleOpenReject(previewDoc)}
                                                disabled={isProcessing}
                                                className="px-3 py-1.5 rounded-xl text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 border border-red-200 dark:border-red-900/60 transition cursor-pointer"
                                            >
                                                Tolak Dokumen
                                            </button>
                                        )}
                                        {previewDoc.status !== 'terverifikasi' && (
                                            <button
                                                type="button"
                                                onClick={() => handleVerify(previewDoc)}
                                                disabled={isProcessing}
                                                className="px-4 py-1.5 rounded-xl text-xs font-semibold bg-teal-600 hover:bg-teal-700 text-white shadow-xs transition flex items-center gap-1.5 cursor-pointer"
                                            >
                                                <Check className="w-3.5 h-3.5" />
                                                <span>Verifikasi Dokumen</span>
                                            </button>
                                        )}
                                    </>
                                )}
                                <button
                                    type="button"
                                    onClick={() => setPreviewDoc(null)}
                                    className="px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition cursor-pointer"
                                >
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* MODAL TOLAK DOKUMEN */}
            {rejectDoc && (
                <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-md p-6 shadow-2xl space-y-4">
                        <div className="flex items-center gap-3 text-red-600 dark:text-red-400">
                            <div className="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center border border-red-100 dark:border-red-900/50">
                                <XCircle className="w-5 h-5" />
                            </div>
                            <div>
                                <h3 className="font-bold font-heading text-base text-[#0f172a] dark:text-[#f8fafc]">
                                    Tolak Dokumen C-Hasil
                                </h3>
                                <p className="text-xs text-[#94a3b8]">
                                    {rejectDoc.jenis?.toUpperCase()} • {rejectDoc.file_name}
                                </p>
                            </div>
                        </div>

                        <form onSubmit={handleRejectSubmit} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                                    Alasan Penolakan <span className="text-red-500">*</span>
                                </label>
                                <textarea
                                    rows="3"
                                    value={rejectComment}
                                    onChange={(e) => setRejectComment(e.target.value)}
                                    placeholder="Contoh: Foto dokumen buram pada bagian perolehan suara sah, mohon scan ulang..."
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl p-3 text-xs outline-none focus:border-red-500 resize-none"
                                    required
                                    autoFocus
                                />
                                <p className="text-[10px] text-[#94a3b8] mt-1">
                                    Catatan ini akan langsung terlihat oleh petugas KPPS untuk perbaikan.
                                </p>
                            </div>

                            <div className="flex justify-end gap-2 pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                                <button
                                    type="button"
                                    onClick={() => setRejectDoc(null)}
                                    className="px-4 py-2 rounded-xl text-xs font-semibold bg-[#f1f5f9] dark:bg-[#1f2937] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={isProcessing || !rejectComment.trim()}
                                    className="px-5 py-2 rounded-xl text-xs font-semibold bg-red-600 hover:bg-red-700 text-white shadow-md shadow-red-600/20 transition cursor-pointer disabled:opacity-50"
                                >
                                    {isProcessing ? 'Menyimpan...' : 'Kirim Penolakan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
