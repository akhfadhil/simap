import React, { useState, useMemo, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
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
    FileCheck,
    Search,
    ChevronDown,
    ChevronUp,
    Upload
} from 'lucide-react';

export default function DokumenPpk({
    tpsList = [],
    desas = [],
    kecamatan,
    dokumenKecamatan = [],
    selectedDesaId,
    aktifJenis = [],
    aktifDokumenMap = {},
    isAdminView = false
}) {
    const [filterDesa, setFilterDesa] = useState(selectedDesaId || '');
    const [searchTps, setSearchTps] = useState('');
    const [expandedTps, setExpandedTps] = useState({});
    const [previewDoc, setPreviewDoc] = useState(null);

    // State Modal Upload D-Hasil
    const [uploadModal, setUploadModal] = useState(null); // { jenisKey, label, doc }
    const [uploadFile, setUploadFile] = useState(null);
    const [isUploading, setIsUploading] = useState(false);
    const [uploadError, setUploadError] = useState(null);

    // Sinkronkan state desa jika props berubah
    useEffect(() => {
        setFilterDesa(selectedDesaId || '');
    }, [selectedDesaId]);

    const activeDesa = desas?.find(d => d.id === parseInt(filterDesa));

    const handleFilterChange = (desaId) => {
        setFilterDesa(desaId);
        router.get('/dokumen/kecamatan', {
            desa_id: desaId || undefined,
        }, { preserveState: true });
    };

    // Buka dan tutup modal upload
    const openUploadModal = (jenisKey, label, doc) => {
        setUploadModal({ jenisKey, label, doc });
        setUploadFile(null);
        setUploadError(null);
    };

    const closeUploadModal = () => {
        setUploadModal(null);
        setUploadFile(null);
        setUploadError(null);
        setIsUploading(false);
    };

    // Submit upload berkas D-Hasil
    const handleUploadSubmit = (e) => {
        e.preventDefault();
        if (!uploadFile) {
            setUploadError('Silakan pilih berkas dokumen PDF yang akan diupload.');
            return;
        }

        if (uploadFile.type !== 'application/pdf' && !uploadFile.name.toLowerCase().endsWith('.pdf')) {
            setUploadError('Format berkas harus berupa dokumen PDF (.pdf).');
            return;
        }

        if (uploadFile.size > 5 * 1024 * 1024) {
            setUploadError('Ukuran file melebihi batas maksimal 5 MB.');
            return;
        }

        setIsUploading(true);
        setUploadError(null);

        const formData = new FormData();
        formData.append('jenis', uploadModal.jenisKey);
        formData.append('file', uploadFile);

        router.post('/ppk/upload', formData, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setIsUploading(false);
                closeUploadModal();
            },
            onError: (errors) => {
                setIsUploading(false);
                setUploadError(errors?.file || errors?.jenis || 'Gagal mengupload berkas. Pastikan file valid dan coba lagi.');
            }
        });
    };

    // Target jumlah jenis pemilihan aktif
    const targetJenisCount = Object.keys(aktifDokumenMap).length > 0
        ? Object.keys(aktifDokumenMap).length
        : (aktifJenis.length > 0 ? aktifJenis.length : 1);

    const activeElectionEntries = Object.keys(aktifDokumenMap).length > 0
        ? Object.entries(aktifDokumenMap)
        : (aktifJenis.length > 0 ? aktifJenis.map(j => [j.toUpperCase(), j.toUpperCase()]) : []);

    // Filter dokumen TPS agar hanya mencakup jenis pemilu yang aktif
    const getActiveDocs = (dokumens) => {
        if (!dokumens || dokumens.length === 0) return [];
        if (!aktifJenis || aktifJenis.length === 0) return dokumens;
        return dokumens.filter(doc => aktifJenis.includes(doc.jenis?.toLowerCase()));
    };

    // Filter dokumen kecamatan yang jenisnya aktif
    const activeKecDocs = useMemo(() => {
        if (!dokumenKecamatan || dokumenKecamatan.length === 0) return [];
        if (!aktifJenis || aktifJenis.length === 0) return dokumenKecamatan;
        return dokumenKecamatan.filter(doc => aktifJenis.includes(doc.jenis?.toLowerCase()));
    }, [dokumenKecamatan, aktifJenis]);

    const totalKecTerverifikasi = activeKecDocs.filter(d => d.status === 'terverifikasi').length;
    const isAllKecVerified = targetJenisCount > 0 && totalKecTerverifikasi === targetJenisCount;

    // Filter TPS berdasarkan pencarian nama/nomor TPS
    const filteredTpsList = useMemo(() => {
        if (!searchTps.trim()) return tpsList;
        const q = searchTps.toLowerCase();
        return tpsList.filter(t => t.nama?.toLowerCase().includes(q));
    }, [tpsList, searchTps]);

    // Buka / Tutup Semua TPS
    const allExpanded = useMemo(() => {
        if (filteredTpsList.length === 0) return false;
        return filteredTpsList.every(t => expandedTps[t.id]);
    }, [filteredTpsList, expandedTps]);

    const toggleExpandAll = () => {
        if (allExpanded) {
            setExpandedTps({});
        } else {
            const next = {};
            filteredTpsList.forEach(t => {
                next[t.id] = true;
            });
            setExpandedTps(next);
        }
    };

    const toggleTps = (id) => {
        setExpandedTps(prev => ({
            ...prev,
            [id]: !prev[id]
        }));
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

    // Hitung statistik untuk TPS di desa terpilih
    const totalTps = tpsList?.length || 0;
    const totalTargetDokumen = totalTps * targetJenisCount;

    let totalTerverifikasi = 0;
    let totalMenunggu = 0;
    let totalDitolak = 0;
    let totalMasuk = 0;

    tpsList.forEach(tps => {
        const docs = getActiveDocs(tps.dokumens);
        totalMasuk += docs.length;
        docs.forEach(d => {
            if (d.status === 'terverifikasi') totalTerverifikasi++;
            else if (d.status === 'ditolak') totalDitolak++;
            else if (d.status === 'menunggu_verifikasi') totalMenunggu++;
        });
    });

    const persenVerifikasi = totalTargetDokumen > 0
        ? Math.min(100, Math.round((totalTerverifikasi / totalTargetDokumen) * 100))
        : 0;

    return (
        <AdminLayout title={`Rekap Dokumen - Kecamatan ${kecamatan?.nama || ''}`}>
            <Head title={`Rekap Dokumen - Kecamatan ${kecamatan?.nama || ''}`} />

            <div className="space-y-5 sm:space-y-6">
                {/* Header Card (Tombol upload di header telah dihapus) */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 sm:p-6 shadow-sm">
                    <div className="space-y-1">
                        <div className="flex items-center gap-2">
                            <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-0.5 rounded-md border border-red-100 dark:border-red-900/30">
                                PPK Kecamatan {kecamatan?.nama}
                            </span>
                            <span className="text-[11px] text-[#475569] dark:text-[#94a3b8]">
                                • {targetJenisCount} Jenis Pemilihan Aktif
                            </span>
                        </div>
                        <h1 className="text-xl sm:text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight">
                            Rekapitulasi Dokumen C-Hasil & D-Hasil
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8]">
                            {activeDesa
                                ? `Memantau kelengkapan berkas TPS di Desa ${activeDesa.nama}, Kec. ${kecamatan?.nama}.`
                                : `Pilih desa untuk memeriksa kelengkapan dokumen formulir C-Hasil TPS.`}
                        </p>
                    </div>
                </div>

                {/* Filter Desa Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 sm:p-5 shadow-xs">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div className="flex-1 max-w-md">
                            <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5 flex items-center gap-1.5">
                                <Building2 className="w-3.5 h-3.5 text-[#bb152c]" />
                                <span>Pilih Desa / Kelurahan:</span>
                            </label>
                            <select
                                value={filterDesa}
                                onChange={(e) => handleFilterChange(e.target.value)}
                                className="w-full h-10 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 text-xs outline-none focus:border-[#bb152c] focus:ring-2 focus:ring-[#bb152c]/15 transition cursor-pointer font-medium"
                            >
                                <option value="">-- Pilih Desa --</option>
                                {desas?.map(desa => (
                                    <option key={desa.id} value={desa.id}>Desa {desa.nama}</option>
                                ))}
                            </select>
                        </div>

                        {activeDesa && (
                            <div className="flex items-center gap-2 self-end sm:self-auto text-xs text-[#475569] dark:text-[#94a3b8]">
                                <span className="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 font-semibold text-[#0f172a] dark:text-[#f8fafc]">
                                    {totalTps} Total TPS
                                </span>
                            </div>
                        )}
                    </div>
                </div>

                {/* Section Dokumen Kecamatan (D-Hasil Pleno PPK) */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-sm overflow-hidden">
                    <div className="p-4 sm:p-5 border-b border-[#e2e8f0] dark:border-[#1e293b] flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/70 dark:bg-[#0b0f19]/40">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 flex items-center justify-center shrink-0 border border-red-100 dark:border-red-900/30">
                                <Building2 className="w-5 h-5" />
                            </div>
                            <div>
                                <div className="flex items-center gap-2">
                                    <span className="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-md bg-red-100 dark:bg-red-950 text-[#bb152c] dark:text-red-400 border border-red-200 dark:border-red-900/50">
                                        Tingkat PPK
                                    </span>
                                    <h2 className="font-bold font-heading text-sm sm:text-base text-[#0f172a] dark:text-[#f8fafc]">
                                        Dokumen D-Hasil Pleno Kecamatan {kecamatan?.nama}
                                    </h2>
                                </div>
                                <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-0.5">
                                    Berkas formulir pleno rekapitulasi D-Hasil tingkat kecamatan.
                                </p>
                            </div>
                        </div>

                        <div className="flex items-center gap-2">
                            <span
                                className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border ${
                                    isAllKecVerified
                                        ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/40'
                                        : totalKecTerverifikasi > 0
                                        ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800/40'
                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700'
                                }`}
                            >
                                {isAllKecVerified ? (
                                    <CheckCircle2 className="w-4 h-4 text-emerald-600" />
                                ) : (
                                    <FileCheck className="w-4 h-4" />
                                )}
                                <span>{totalKecTerverifikasi}/{targetJenisCount} D-Hasil Terverifikasi</span>
                            </span>
                        </div>
                    </div>

                    {/* Grid Dokumen Kecamatan */}
                    <div className="p-4 sm:p-5">
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            {activeElectionEntries.map(([jenisKey, label]) => {
                                const doc = activeKecDocs.find(d => d.jenis?.toLowerCase() === jenisKey.toLowerCase());
                                return (
                                    <div
                                        key={jenisKey}
                                        className="bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl p-3.5 flex flex-col justify-between gap-3"
                                    >
                                        <div className="flex items-start justify-between gap-2">
                                            <div>
                                                <span className="text-[10px] font-mono font-bold uppercase text-[#bb152c] dark:text-red-400">
                                                    {jenisKey}
                                                </span>
                                                <h3 className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] line-clamp-1">
                                                    {label}
                                                </h3>
                                            </div>

                                            {doc ? (
                                                <span
                                                    className={`text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md border shrink-0 ${
                                                        doc.status === 'terverifikasi'
                                                            ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/40'
                                                            : doc.status === 'ditolak'
                                                            ? 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800/40'
                                                            : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800/40'
                                                    }`}
                                                >
                                                    {doc.status === 'terverifikasi' ? 'Terverifikasi' : doc.status === 'ditolak' ? 'Ditolak' : 'Menunggu'}
                                                </span>
                                            ) : (
                                                <span className="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700 shrink-0">
                                                    Belum Upload
                                                </span>
                                            )}
                                        </div>

                                        {doc ? (
                                            <div className="space-y-2 pt-1 border-t border-[#e2e8f0]/60 dark:border-[#1e293b]">
                                                <div className="text-[10px] text-[#475569] dark:text-[#94a3b8]">
                                                    <p className="truncate">Oleh: <b>{doc.uploader?.name || 'PPK'}</b></p>
                                                    <p className="text-[9px] text-[#94a3b8]">{formatDateTime(doc.created_at)}</p>
                                                </div>

                                                {doc.status === 'ditolak' && doc.komentar && (
                                                    <div className="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/40 rounded-lg p-2 text-[10px] text-red-700 dark:text-red-300">
                                                        <span className="font-bold block">Alasan Ditolak:</span>
                                                        <span>{doc.komentar}</span>
                                                    </div>
                                                )}

                                                <div className="flex items-center gap-1.5 pt-1">
                                                    <button
                                                        type="button"
                                                        onClick={() => setPreviewDoc(doc)}
                                                        className="flex-1 h-8 rounded-lg bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] hover:bg-slate-50 dark:hover:bg-slate-800 transition flex items-center justify-center gap-1 cursor-pointer"
                                                    >
                                                        <Eye className="w-3.5 h-3.5 text-[#bb152c]" />
                                                        <span>Preview</span>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => openUploadModal(jenisKey, label, doc)}
                                                        className="h-8 px-2.5 rounded-lg bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-xs font-semibold text-[#475569] dark:text-[#94a3b8] hover:text-[#bb152c] dark:hover:text-red-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition flex items-center justify-center gap-1 cursor-pointer"
                                                        title="Ganti Dokumen D-Hasil"
                                                    >
                                                        <Upload className="w-3.5 h-3.5 text-[#bb152c]" />
                                                        <span className="hidden sm:inline">Ganti</span>
                                                    </button>
                                                    <a
                                                        href={`/dokumen/${doc.id}/download`}
                                                        className="h-8 px-2.5 rounded-lg bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-xs font-semibold text-[#475569] dark:text-[#94a3b8] hover:text-[#0f172a] hover:bg-slate-50 dark:hover:bg-slate-800 transition flex items-center justify-center cursor-pointer"
                                                        title="Unduh File"
                                                    >
                                                        <Download className="w-3.5 h-3.5" />
                                                    </a>
                                                </div>
                                            </div>
                                        ) : (
                                            <div className="pt-2 border-t border-[#e2e8f0]/60 dark:border-[#1e293b]">
                                                <button
                                                    type="button"
                                                    onClick={() => openUploadModal(jenisKey, label, null)}
                                                    className="w-full h-8 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-red-950/40 dark:hover:bg-red-900/60 text-xs font-semibold text-[#bb152c] dark:text-red-400 border border-red-200 dark:border-red-800/40 transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs"
                                                >
                                                    <Upload className="w-3.5 h-3.5" />
                                                    <span>Upload Dokumen</span>
                                                </button>
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>

                {/* Section Dokumen TPS per Desa */}
                {activeDesa ? (
                    <div className="space-y-4">
                        {/* KPI Cards Desa */}
                        <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
                            <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs">
                                <span className="text-[11px] font-semibold text-[#475569] dark:text-[#94a3b8] block">
                                    Total TPS di Desa
                                </span>
                                <div className="flex items-baseline gap-2 mt-1">
                                    <span className="text-xl sm:text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc]">
                                        {totalTps}
                                    </span>
                                    <span className="text-xs text-[#94a3b8]">TPS</span>
                                </div>
                            </div>

                            <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs">
                                <span className="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 block">
                                    Terverifikasi
                                </span>
                                <div className="flex items-baseline gap-2 mt-1">
                                    <span className="text-xl sm:text-2xl font-bold font-heading text-emerald-600 dark:text-emerald-400">
                                        {totalTerverifikasi}
                                    </span>
                                    <span className="text-xs text-[#94a3b8]">/ {totalTargetDokumen} ({persenVerifikasi}%)</span>
                                </div>
                            </div>

                            <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs">
                                <span className="text-[11px] font-semibold text-amber-600 dark:text-amber-400 block">
                                    Menunggu Verifikasi
                                </span>
                                <div className="flex items-baseline gap-2 mt-1">
                                    <span className="text-xl sm:text-2xl font-bold font-heading text-amber-600 dark:text-amber-400">
                                        {totalMenunggu}
                                    </span>
                                    <span className="text-xs text-[#94a3b8]">berkas</span>
                                </div>
                            </div>

                            <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs">
                                <span className="text-[11px] font-semibold text-red-600 dark:text-red-400 block">
                                    Dokumen Ditolak
                                </span>
                                <div className="flex items-baseline gap-2 mt-1">
                                    <span className="text-xl sm:text-2xl font-bold font-heading text-red-600 dark:text-red-400">
                                        {totalDitolak}
                                    </span>
                                    <span className="text-xs text-[#94a3b8]">berkas</span>
                                </div>
                            </div>
                        </div>

                        {/* Search & Accordion Controls */}
                        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                            <div className="relative flex-1 max-w-sm">
                                <Search className="w-4 h-4 text-[#94a3b8] absolute left-3 top-1/2 -translate-y-1/2" />
                                <input
                                    type="text"
                                    placeholder="Cari TPS..."
                                    value={searchTps}
                                    onChange={(e) => setSearchTps(e.target.value)}
                                    className="w-full h-9 pl-9 pr-3 bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl text-xs text-[#0f172a] dark:text-[#f8fafc] outline-none focus:border-[#bb152c] transition"
                                />
                            </div>

                            <button
                                type="button"
                                onClick={toggleExpandAll}
                                className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-xs font-semibold text-[#475569] dark:text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-white transition cursor-pointer self-start sm:self-auto"
                            >
                                {allExpanded ? (
                                    <>
                                        <ChevronUp className="w-3.5 h-3.5" />
                                        <span>Tutup Semua TPS</span>
                                    </>
                                ) : (
                                    <>
                                        <ChevronDown className="w-3.5 h-3.5" />
                                        <span>Buka Semua TPS</span>
                                    </>
                                )}
                            </button>
                        </div>

                        {/* List TPS Cards */}
                        <div className="space-y-3">
                            {filteredTpsList.length > 0 ? (
                                filteredTpsList.map((tps) => {
                                    const isExpanded = !!expandedTps[tps.id];
                                    const docs = getActiveDocs(tps.dokumens);
                                    const dokByJenis = docs.reduce((acc, d) => {
                                        if (d.jenis) acc[d.jenis.toLowerCase()] = d;
                                        return acc;
                                    }, {});

                                    const tpsTerverifikasi = docs.filter(d => d.status === 'terverifikasi').length;
                                    const tpsDitolak = docs.filter(d => d.status === 'ditolak').length;
                                    const tpsMenunggu = docs.filter(d => d.status === 'menunggu_verifikasi').length;
                                    const isComplete = targetJenisCount > 0 && tpsTerverifikasi === targetJenisCount;
                                    const percent = targetJenisCount > 0 ? Math.min(100, Math.round((tpsTerverifikasi / targetJenisCount) * 100)) : 0;

                                    return (
                                        <div
                                            key={tps.id}
                                            className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-xs overflow-hidden transition"
                                        >
                                            {/* Header Accordion */}
                                            <div
                                                onClick={() => toggleTps(tps.id)}
                                                className="p-4 sm:p-5 flex items-center justify-between gap-3 cursor-pointer hover:bg-slate-50/70 dark:hover:bg-[#0b0f19]/40 transition select-none"
                                            >
                                                <div className="flex items-center gap-3 min-w-0">
                                                    <div className={`w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 ${
                                                        isComplete
                                                            ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40'
                                                            : tpsDitolak > 0
                                                            ? 'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800/40'
                                                            : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'
                                                    }`}>
                                                        {tps.nama?.replace(/[^0-9]/g, '') || tps.id}
                                                    </div>
                                                    <div className="min-w-0">
                                                        <h3 className="font-bold text-sm text-[#0f172a] dark:text-[#f8fafc] truncate">
                                                            {tps.nama}
                                                        </h3>
                                                        <p className="text-[11px] text-[#94a3b8] truncate">
                                                            Desa {tps.desa?.nama} • {tpsTerverifikasi}/{targetJenisCount} Terverifikasi • {docs.length} Masuk
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="flex items-center gap-3 shrink-0">
                                                    <div className="hidden sm:flex flex-col items-end gap-1">
                                                        <div className="w-24 h-1.5 bg-[#e2e8f0] dark:bg-[#1e293b] rounded-full overflow-hidden">
                                                            <div
                                                                className={`h-full rounded-full transition-all duration-300 ${
                                                                    isComplete ? 'bg-emerald-500' : 'bg-[#bb152c]'
                                                                }`}
                                                                style={{ width: `${percent}%` }}
                                                            />
                                                        </div>
                                                        <span className="text-[10px] font-mono text-[#94a3b8]">{percent}%</span>
                                                    </div>

                                                    <div className="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[#94a3b8]">
                                                        {isExpanded ? (
                                                            <ChevronUp className="w-4 h-4" />
                                                        ) : (
                                                            <ChevronDown className="w-4 h-4" />
                                                        )}
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Body Accordion: List Dokumen C-Hasil */}
                                            {isExpanded && (
                                                <div className="p-4 sm:p-5 border-t border-[#e2e8f0] dark:border-[#1e293b] bg-slate-50/40 dark:bg-[#0b0f19]/20 space-y-3">
                                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                        {activeElectionEntries.map(([jenisKey, label]) => {
                                                            const doc = dokByJenis[jenisKey.toLowerCase()];
                                                            return (
                                                                <div
                                                                    key={jenisKey}
                                                                    className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl p-3.5 flex flex-col justify-between gap-3 shadow-2xs"
                                                                >
                                                                    <div className="flex items-start justify-between gap-2">
                                                                        <div>
                                                                            <span className="text-[10px] font-mono font-bold uppercase text-[#bb152c] dark:text-red-400">
                                                                                {jenisKey}
                                                                            </span>
                                                                            <h4 className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] line-clamp-1">
                                                                                {label}
                                                                            </h4>
                                                                        </div>

                                                                        {doc ? (
                                                                            <span
                                                                                className={`text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md border shrink-0 ${
                                                                                    doc.status === 'terverifikasi'
                                                                                        ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/40'
                                                                                        : doc.status === 'ditolak'
                                                                                        ? 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800/40'
                                                                                        : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800/40'
                                                                                }`}
                                                                            >
                                                                                {doc.status === 'terverifikasi' ? 'Terverifikasi' : doc.status === 'ditolak' ? 'Ditolak' : 'Menunggu'}
                                                                            </span>
                                                                        ) : (
                                                                            <span className="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700 shrink-0">
                                                                                Belum Upload
                                                                            </span>
                                                                        )}
                                                                    </div>

                                                                    {doc ? (
                                                                        <div className="space-y-2 pt-1 border-t border-[#e2e8f0]/60 dark:border-[#1e293b]">
                                                                            <div className="text-[10px] text-[#475569] dark:text-[#94a3b8]">
                                                                                <p className="truncate">Uploader: <b>{doc.uploader?.name || 'Petugas'}</b></p>
                                                                                {doc.verifier && (
                                                                                    <p className="text-[9px] text-emerald-600 dark:text-emerald-400">
                                                                                        ✓ Diverifikasi: {doc.verifier.name}
                                                                                    </p>
                                                                                )}
                                                                            </div>

                                                                            {/* Alasan Ditolak (Jika Ada) */}
                                                                            {doc.status === 'ditolak' && doc.komentar && (
                                                                                <div className="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/40 rounded-lg p-2 text-[10px] text-red-700 dark:text-red-300">
                                                                                    <div className="flex items-center gap-1 font-bold text-red-800 dark:text-red-200 mb-0.5">
                                                                                        <AlertCircle className="w-3 h-3 shrink-0" />
                                                                                        <span>Alasan Penolakan:</span>
                                                                                    </div>
                                                                                    <span>{doc.komentar}</span>
                                                                                </div>
                                                                            )}

                                                                            <div className="flex items-center gap-2 pt-1">
                                                                                <button
                                                                                    type="button"
                                                                                    onClick={() => setPreviewDoc(doc)}
                                                                                    className="flex-1 h-8 rounded-lg bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] hover:bg-slate-50 dark:hover:bg-slate-800 transition flex items-center justify-center gap-1.5 cursor-pointer"
                                                                                >
                                                                                    <Eye className="w-3.5 h-3.5 text-[#bb152c]" />
                                                                                    <span>Preview</span>
                                                                                </button>
                                                                                <a
                                                                                    href={`/dokumen/${doc.id}/download`}
                                                                                    className="h-8 px-2.5 rounded-lg bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-xs font-semibold text-[#475569] dark:text-[#94a3b8] hover:text-[#0f172a] hover:bg-slate-50 dark:hover:bg-slate-800 transition flex items-center justify-center"
                                                                                    title="Unduh File"
                                                                                >
                                                                                    <Download className="w-3.5 h-3.5" />
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    ) : (
                                                                        <div className="pt-2 border-t border-[#e2e8f0]/60 dark:border-[#1e293b] text-[10px] text-[#94a3b8] text-center italic">
                                                                            Formulir C-Hasil belum diunggah oleh KPPS.
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
                                })
                            ) : (
                                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-10 text-center text-xs text-[#94a3b8]">
                                    {searchTps ? 'Tidak ada TPS yang sesuai dengan kata kunci pencarian.' : 'Belum ada data TPS di desa ini.'}
                                </div>
                            )}
                        </div>
                    </div>
                ) : (
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-12 text-center text-xs text-[#94a3b8]">
                        Silakan pilih salah satu desa di atas untuk menampilkan daftar TPS dan dokumen C-Hasil.
                    </div>
                )}
            </div>

            {/* Modal Upload Dokumen D-Hasil PPK */}
            {uploadModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                    <div className="relative w-full max-w-lg bg-white dark:bg-[#151f32] rounded-2xl shadow-2xl overflow-hidden border border-[#e2e8f0] dark:border-[#1e293b]">
                        {/* Modal Header */}
                        <div className="px-5 py-4 border-b border-[#e2e8f0] dark:border-[#1e293b] flex items-center justify-between bg-slate-50/70 dark:bg-[#0b0f19]/40">
                            <div className="flex items-center gap-3">
                                <div className="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 flex items-center justify-center shrink-0">
                                    <Upload className="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 className="font-bold text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                        Upload Dokumen D-Hasil PPK
                                    </h3>
                                    <p className="text-[11px] text-[#94a3b8]">
                                        {uploadModal.label} • Kecamatan {kecamatan?.nama}
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={closeUploadModal}
                                disabled={isUploading}
                                className="p-1.5 rounded-xl text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer disabled:opacity-50"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        {/* Modal Body / Form */}
                        <form onSubmit={handleUploadSubmit} className="p-5 space-y-4">
                            {uploadError && (
                                <div className="bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-900/50 text-red-700 dark:text-red-300 p-3 rounded-xl text-xs flex items-center gap-2">
                                    <AlertCircle className="w-4 h-4 shrink-0 text-red-600" />
                                    <span>{uploadError}</span>
                                </div>
                            )}

                            {uploadModal.doc && (
                                <div className="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/40 p-3 rounded-xl text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2">
                                    <AlertCircle className="w-4 h-4 shrink-0 mt-0.5" />
                                    <div>
                                        <p className="font-semibold">Dokumen sebelumnya sudah ada</p>
                                        <p className="text-[11px] mt-0.5 text-amber-700 dark:text-amber-400">
                                            Mengupload berkas baru akan secara otomatis menggantikan berkas D-Hasil lama.
                                        </p>
                                    </div>
                                </div>
                            )}

                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                                    Pilih File PDF Pleno D-Hasil (Maks. 5 MB)
                                </label>
                                <div className="relative border-2 border-dashed border-[#e2e8f0] dark:border-[#1e293b] hover:border-[#bb152c] dark:hover:border-red-500/50 rounded-2xl p-6 text-center transition bg-[#f8fafc] dark:bg-[#0b0f19]">
                                    <input
                                        type="file"
                                        accept=".pdf,application/pdf"
                                        disabled={isUploading}
                                        onChange={(e) => {
                                            const file = e.target.files?.[0];
                                            setUploadFile(file || null);
                                            setUploadError(null);
                                        }}
                                        className="absolute inset-0 w-full h-full opacity-0 cursor-pointer disabled:cursor-not-allowed"
                                    />
                                    <div className="flex flex-col items-center justify-center gap-2 pointer-events-none">
                                        <div className="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 flex items-center justify-center">
                                            <FileText className="w-6 h-6" />
                                        </div>
                                        {uploadFile ? (
                                            <div>
                                                <p className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] line-clamp-1">
                                                    {uploadFile.name}
                                                </p>
                                                <p className="text-[10px] text-[#94a3b8] mt-0.5">
                                                    {(uploadFile.size / 1024 / 1024).toFixed(2)} MB • Klik untuk ganti file
                                                </p>
                                            </div>
                                        ) : (
                                            <div>
                                                <p className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                                    Klik atau seret file PDF ke sini
                                                </p>
                                                <p className="text-[10px] text-[#94a3b8] mt-0.5">
                                                    Hanya format dokumen .pdf resmi (maksimal 5 MB)
                                                </p>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Modal Footer Buttons */}
                            <div className="flex items-center justify-end gap-2 pt-2 border-t border-[#e2e8f0] dark:border-[#1e293b]">
                                <button
                                    type="button"
                                    onClick={closeUploadModal}
                                    disabled={isUploading}
                                    className="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-xs font-semibold text-[#475569] dark:text-[#94a3b8] transition cursor-pointer disabled:opacity-50"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={isUploading || !uploadFile}
                                    className="px-5 py-2 rounded-xl bg-[#bb152c] hover:bg-[#991124] text-white text-xs font-semibold transition cursor-pointer flex items-center gap-2 shadow-md shadow-[#bb152c]/20 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {isUploading ? (
                                        <>
                                            <span className="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                                            <span>Mengupload...</span>
                                        </>
                                    ) : (
                                        <>
                                            <Upload className="w-3.5 h-3.5" />
                                            <span>Upload Sekarang</span>
                                        </>
                                    )}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal Preview PDF Interaktif */}
            {previewDoc && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-black/60 backdrop-blur-xs">
                    <div className="relative w-full max-w-5xl h-[88vh] bg-white dark:bg-[#151f32] rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-[#e2e8f0] dark:border-[#1e293b]">
                        {/* Header Modal */}
                        <div className="px-5 py-4 border-b border-[#e2e8f0] dark:border-[#1e293b] flex items-center justify-between bg-slate-50/70 dark:bg-[#0b0f19]/40">
                            <div className="flex items-center gap-3">
                                <div className="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 flex items-center justify-center shrink-0">
                                    <FileText className="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 className="font-bold text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                        Preview Dokumen: {previewDoc.jenis?.toUpperCase()}
                                    </h3>
                                    <p className="text-[11px] text-[#94a3b8]">
                                        {previewDoc.level === 'kecamatan'
                                            ? `D-Hasil Kecamatan ${kecamatan?.nama}`
                                            : `C-Hasil TPS • Diupload oleh ${previewDoc.uploader?.name || 'Petugas'}`}
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-center gap-2">
                                <a
                                    href={`/dokumen/${previewDoc.id}/download`}
                                    className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-[#1e293b] border border-[#e2e8f0] dark:border-[#334155] text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] hover:bg-slate-50 transition"
                                >
                                    <Download className="w-3.5 h-3.5" />
                                    <span className="hidden sm:inline">Unduh PDF</span>
                                </a>

                                <button
                                    type="button"
                                    onClick={() => setPreviewDoc(null)}
                                    className="p-1.5 rounded-xl text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                                >
                                    <X className="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        {/* Body PDF Viewer */}
                        <div className="flex-1 bg-slate-100 dark:bg-slate-900 overflow-hidden">
                            <iframe
                                src={`/dokumen/${previewDoc.id}/preview`}
                                className="w-full h-full border-0"
                                title="Pratinjau PDF"
                            />
                        </div>

                        {/* Footer Modal Info */}
                        <div className="px-5 py-3 border-t border-[#e2e8f0] dark:border-[#1e293b] flex items-center justify-between text-xs bg-white dark:bg-[#151f32]">
                            <div className="flex items-center gap-2">
                                <span
                                    className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase ${
                                        previewDoc.status === 'terverifikasi'
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                            : previewDoc.status === 'ditolak'
                                            ? 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400'
                                            : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400'
                                    }`}
                                >
                                    {previewDoc.status?.toUpperCase()}
                                </span>
                                {previewDoc.komentar && (
                                    <span className="text-red-600 dark:text-red-400 text-[11px]">
                                        Catatan: {previewDoc.komentar}
                                    </span>
                                )}
                            </div>

                            <button
                                type="button"
                                onClick={() => setPreviewDoc(null)}
                                className="px-4 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-semibold text-[#475569] dark:text-[#94a3b8] hover:bg-slate-200 transition cursor-pointer"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
