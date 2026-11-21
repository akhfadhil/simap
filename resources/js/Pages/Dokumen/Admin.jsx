import React, { useState, useMemo, useEffect } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import {
    FolderOpen,
    Filter,
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
    Check,
    Loader2
} from 'lucide-react';

export default function DokumenAdmin({
    tpsList = [],
    kecamatans = [],
    desas = [],
    dokumenKecamatan = [],
    selectedKecamatanId,
    selectedDesaId,
    aktifJenis = [],
    aktifDokumenMap = {}
}) {
    const { auth } = usePage().props;
    const currentUser = auth?.user;

    const [filterKec, setFilterKec] = useState(selectedKecamatanId || '');
    const [filterDesa, setFilterDesa] = useState(selectedDesaId || '');
    const [previewDoc, setPreviewDoc] = useState(null);
    const [rejectingDoc, setRejectingDoc] = useState(null);
    const [rejectReason, setRejectReason] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [actionError, setActionError] = useState(null);

    // Sinkronisasi state filter saat props berubah (misal setelah submit atau navigasi)
    useEffect(() => {
        setFilterKec(selectedKecamatanId || '');
        setFilterDesa(selectedDesaId || '');
    }, [selectedKecamatanId, selectedDesaId]);

    const handleFilter = (e) => {
        e.preventDefault();
        router.get('/dokumen/semua', {
            kecamatan_id: filterKec || undefined,
            desa_id: filterDesa || undefined,
        }, { preserveState: true });
    };

    const activeKec = kecamatans?.find(k => k.id === parseInt(filterKec));
    const availableDesas = activeKec?.desas || (desas?.filter(d => d.kecamatan_id === parseInt(filterKec))) || [];
    const activeDesa = availableDesas?.find(d => d.id === parseInt(filterDesa)) || desas?.find(d => d.id === parseInt(filterDesa));

    // Jumlah jenis pemilihan aktif (target per TPS & Kecamatan)
    const targetJenisCount = Object.keys(aktifDokumenMap).length > 0
        ? Object.keys(aktifDokumenMap).length
        : (aktifJenis.length > 0 ? aktifJenis.length : 1);

    const activeElectionEntries = Object.keys(aktifDokumenMap).length > 0
        ? Object.entries(aktifDokumenMap)
        : (aktifJenis.length > 0 ? aktifJenis.map(j => [j.toUpperCase(), j.toUpperCase()]) : []);

    // Normalisasi dokumen kecamatan jika berupa array atau object hasil groupBy
    const kecDocs = useMemo(() => {
        if (!dokumenKecamatan) return [];
        if (Array.isArray(dokumenKecamatan)) return dokumenKecamatan;
        if (typeof dokumenKecamatan === 'object') {
            if (selectedKecamatanId && dokumenKecamatan[selectedKecamatanId]) {
                return dokumenKecamatan[selectedKecamatanId];
            }
            return Object.values(dokumenKecamatan).flat();
        }
        return [];
    }, [dokumenKecamatan, selectedKecamatanId]);

    // Filter dokumen kecamatan yang jenis pemilihannya aktif
    const activeKecDocs = useMemo(() => {
        if (!kecDocs || kecDocs.length === 0) return [];
        if (!aktifJenis || aktifJenis.length === 0) return kecDocs;
        return kecDocs.filter(doc => aktifJenis.includes(doc.jenis?.toLowerCase()));
    }, [kecDocs, aktifJenis]);

    const totalKecTerverifikasi = activeKecDocs.filter(d => d.status === 'terverifikasi').length;
    const isAllKecVerified = targetJenisCount > 0 && totalKecTerverifikasi === targetJenisCount;

    // Filter dokumen TPS agar hanya menampilkan jenis pemilu yang aktif
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

    // Verifikasi dokumen (baik TPS maupun Kecamatan)
    const handleVerify = (doc) => {
        if (!doc || isSubmitting) return;
        setIsSubmitting(true);
        router.post(`/dokumen/${doc.id}/verifikasi-admin`, {
            aksi: 'terverifikasi',
        }, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                setIsSubmitting(false);
                if (previewDoc && previewDoc.id === doc.id) {
                    setPreviewDoc(prev => ({
                        ...prev,
                        status: 'terverifikasi',
                        komentar: null,
                        verifier: currentUser,
                        verified_at: new Date().toISOString()
                    }));
                }
            },
            onError: (err) => {
                setIsSubmitting(false);
                alert(err?.message || 'Gagal memverifikasi dokumen.');
            }
        });
    };

    // Buka modal tolak dokumen
    const handleOpenReject = (doc, tpsNama = '', kecNama = '') => {
        setRejectingDoc({
            ...doc,
            tps_nama: tpsNama || doc.tps_nama || (previewDoc?.tps_nama) || '',
            kec_nama: kecNama || doc.kec_nama || (previewDoc?.kec_nama) || (activeKec?.nama) || ''
        });
        setRejectReason(doc.komentar || '');
        setActionError(null);
    };

    // Tutup modal tolak dokumen
    const handleCloseReject = () => {
        setRejectingDoc(null);
        setRejectReason('');
        setActionError(null);
    };

    // Submit penolakan dokumen
    const handleRejectSubmit = (e) => {
        e.preventDefault();
        if (!rejectReason.trim()) {
            setActionError('Alasan penolakan wajib diisi.');
            return;
        }
        if (!rejectingDoc || isSubmitting) return;

        setIsSubmitting(true);
        setActionError(null);

        router.post(`/dokumen/${rejectingDoc.id}/verifikasi-admin`, {
            aksi: 'ditolak',
            komentar: rejectReason.trim(),
        }, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                setIsSubmitting(false);
                if (previewDoc && previewDoc.id === rejectingDoc.id) {
                    setPreviewDoc(prev => ({
                        ...prev,
                        status: 'ditolak',
                        komentar: rejectReason.trim(),
                        verifier: currentUser,
                        verified_at: new Date().toISOString()
                    }));
                }
                handleCloseReject();
            },
            onError: (errors) => {
                setIsSubmitting(false);
                setActionError(errors?.komentar || 'Gagal menolak dokumen.');
            }
        });
    };

    // Hitung statistik keseluruhan untuk TPS di desa terpilih
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
        <AdminLayout title="Rekap Dokumen Pemilu">
            <Head title="Rekap Dokumen Pemilu" />

            <div className="space-y-5 sm:space-y-6">
                {/* Header Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 sm:p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="space-y-1">
                        <div className="flex items-center gap-2">
                            <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-0.5 rounded-md border border-red-100 dark:border-red-900/30">
                                Arsip & PDF
                            </span>
                            <span className="text-[11px] text-[#475569] dark:text-[#94a3b8]">
                                • {targetJenisCount} Jenis Pemilihan Aktif
                            </span>
                        </div>
                        <h1 className="text-xl sm:text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight">
                            Rekap Dokumen C-Hasil & D-Hasil
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8]">
                            {activeKec
                                ? `Wilayah aktif: Kec. ${activeKec.nama}${activeDesa ? ` • Desa ${activeDesa.nama}` : ''}`
                                : 'Pilih kecamatan dan desa untuk melihat dan memverifikasi dokumen.'}
                        </p>
                    </div>
                </div>

                {/* Filter Form Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 sm:p-5 shadow-xs">
                    <form onSubmit={handleFilter} className="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        <div className="sm:col-span-5">
                            <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                                Kecamatan
                            </label>
                            <select
                                value={filterKec}
                                onChange={(e) => {
                                    setFilterKec(e.target.value);
                                    setFilterDesa('');
                                }}
                                className="w-full h-10 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 text-xs outline-none focus:border-[#bb152c] focus:ring-2 focus:ring-[#bb152c]/15 transition"
                            >
                                <option value="">-- Pilih Kecamatan --</option>
                                {kecamatans?.map(kec => (
                                    <option key={kec.id} value={kec.id}>{kec.nama}</option>
                                ))}
                            </select>
                        </div>

                        <div className="sm:col-span-4">
                            <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                                Desa / Kelurahan
                            </label>
                            <select
                                value={filterDesa}
                                onChange={(e) => setFilterDesa(e.target.value)}
                                disabled={!filterKec}
                                className="w-full h-10 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 text-xs outline-none focus:border-[#bb152c] focus:ring-2 focus:ring-[#bb152c]/15 transition disabled:opacity-50"
                            >
                                <option value="">
                                    {filterKec ? (availableDesas.length > 0 ? '-- Pilih Desa (Opsional) --' : '-- Belum ada data desa --') : '-- Pilih Kecamatan Dahulu --'}
                                </option>
                                {availableDesas?.map(desa => (
                                    <option key={desa.id} value={desa.id}>{desa.nama}</option>
                                ))}
                            </select>
                        </div>

                        <div className="sm:col-span-3">
                            <button
                                type="submit"
                                className="w-full h-10 bg-[#0f172a] dark:bg-[#1e293b] hover:bg-[#1e293b] dark:hover:bg-[#27354f] active:scale-[0.98] text-white font-semibold px-4 rounded-xl text-xs inline-flex items-center justify-center gap-2 transition cursor-pointer shadow-xs"
                            >
                                <Filter className="w-3.5 h-3.5" />
                                <span>Tampilkan Dokumen</span>
                            </button>
                        </div>
                    </form>
                </div>

                {/* Section Dokumen Kecamatan (D-Hasil PPK) */}
                {activeKec ? (
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-sm overflow-hidden">
                        {/* Header Dokumen Kecamatan */}
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
                                            Dokumen D-Hasil Pleno Kec. {activeKec.nama}
                                        </h2>
                                    </div>
                                    <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-0.5">
                                        Berkas formulir pleno rekapitulasi D-Hasil yang diunggah oleh PPK Kecamatan {activeKec.nama}.
                                    </p>
                                </div>
                            </div>

                            {/* Status Progres D-Hasil PPK */}
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

                        {/* Daftar Formulir D-Hasil per Jenis Pemilihan Aktif */}
                        <div className="divide-y divide-[#f1f5f9] dark:divide-[#1e293b]">
                            {activeElectionEntries.map(([key, label]) => {
                                const doc = activeKecDocs.find(d => d.jenis?.toLowerCase() === key.toLowerCase());
                                const isVerified = doc?.status === 'terverifikasi';
                                const isRejected = doc?.status === 'ditolak';
                                const isWaiting = doc?.status === 'menunggu_verifikasi';

                                return (
                                    <div key={key} className="p-4 sm:px-6 hover:bg-[#f8fafc] dark:hover:bg-[#1f2937]/30 transition">
                                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                            {/* Left: Info Jenis & File */}
                                            <div className="flex items-start gap-3">
                                                <div className="mt-0.5">
                                                    {doc ? (
                                                        isVerified ? (
                                                            <CheckCircle2 className="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                                        ) : isRejected ? (
                                                            <XCircle className="w-5 h-5 text-red-600 dark:text-red-400" />
                                                        ) : (
                                                            <Clock className="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                                        )
                                                    ) : (
                                                        <div className="w-5 h-5 rounded-full border-2 border-dashed border-slate-300 dark:border-slate-600 flex items-center justify-center" />
                                                    )}
                                                </div>
                                                <div>
                                                    <div className="flex items-center gap-2">
                                                        <h4 className="font-bold text-xs sm:text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                                            D-Hasil {label}
                                                        </h4>
                                                        <span className="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                                            {key}
                                                        </span>
                                                    </div>
                                                    {doc ? (
                                                        <div className="text-[11px] text-[#475569] dark:text-[#94a3b8] mt-1 space-x-1">
                                                            <span className="font-semibold text-slate-700 dark:text-slate-300">{doc.file_name}</span>
                                                            <span>•</span>
                                                            <span>Diupload oleh {doc.uploader?.name || 'PPK'}</span>
                                                            {doc.created_at && (
                                                                <>
                                                                    <span>•</span>
                                                                    <span>{formatDateTime(doc.created_at)}</span>
                                                                </>
                                                            )}
                                                            {doc.verifier && (
                                                                <>
                                                                    <span>•</span>
                                                                    <span className="text-emerald-600 dark:text-emerald-400 font-medium">
                                                                        ✓ Disahkan oleh {doc.verifier.name}
                                                                    </span>
                                                                </>
                                                            )}
                                                        </div>
                                                    ) : (
                                                        <p className="text-[11px] text-[#94a3b8] italic mt-0.5">
                                                            Belum diunggah oleh PPK Kecamatan {activeKec.nama}
                                                        </p>
                                                    )}
                                                </div>
                                            </div>

                                            {/* Right: Status Badge & Actions */}
                                            <div className="flex items-center gap-2 self-start md:self-center shrink-0">
                                                {doc ? (
                                                    <>
                                                        <span
                                                            className={`text-[10px] font-bold px-2.5 py-1 rounded-lg uppercase tracking-wider border ${
                                                                isVerified
                                                                    ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200/80 dark:border-emerald-800/50'
                                                                    : isRejected
                                                                    ? 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400 border-red-200/80 dark:border-red-800/50'
                                                                    : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border-amber-200/80 dark:border-amber-800/50'
                                                            }`}
                                                        >
                                                            {isVerified ? 'Terverifikasi' : isRejected ? 'Ditolak' : 'Menunggu Verifikasi'}
                                                        </span>

                                                        <button
                                                            type="button"
                                                            onClick={() => setPreviewDoc({ ...doc, level: 'kecamatan', kec_nama: activeKec.nama })}
                                                            className="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer"
                                                        >
                                                            <Eye className="w-3.5 h-3.5" />
                                                            <span>Preview</span>
                                                        </button>

                                                        <a
                                                            href={`/dokumen/${doc.id}/download`}
                                                            title="Unduh Berkas D-Hasil"
                                                            className="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition"
                                                        >
                                                            <Download className="w-3.5 h-3.5" />
                                                        </a>

                                                        {isWaiting && (
                                                            <>
                                                                <button
                                                                    type="button"
                                                                    onClick={() => handleOpenReject(doc, '', activeKec.nama)}
                                                                    disabled={isSubmitting}
                                                                    className="inline-flex items-center gap-1 h-8 px-2.5 rounded-lg text-xs font-semibold border border-red-200 dark:border-red-900/40 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition cursor-pointer disabled:opacity-50"
                                                                >
                                                                    <XCircle className="w-3.5 h-3.5" />
                                                                    <span>Tolak</span>
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    onClick={() => handleVerify(doc)}
                                                                    disabled={isSubmitting}
                                                                    className="inline-flex items-center gap-1 h-8 px-2.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition cursor-pointer disabled:opacity-50 shadow-xs"
                                                                >
                                                                    <Check className="w-3.5 h-3.5" />
                                                                    <span>Verifikasi</span>
                                                                </button>
                                                            </>
                                                        )}

                                                        {isRejected && (
                                                            <button
                                                                type="button"
                                                                onClick={() => handleVerify(doc)}
                                                                disabled={isSubmitting}
                                                                className="inline-flex items-center gap-1 h-8 px-2.5 rounded-lg text-xs font-semibold border border-emerald-300 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition cursor-pointer disabled:opacity-50"
                                                            >
                                                                <Check className="w-3.5 h-3.5" />
                                                                <span>Verifikasi Ulang</span>
                                                            </button>
                                                        )}
                                                    </>
                                                ) : (
                                                    <span className="text-[11px] font-semibold text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800/60 px-3 py-1.5 rounded-lg border border-dashed border-slate-200 dark:border-slate-700">
                                                        Belum Ada Berkas
                                                    </span>
                                                )}
                                            </div>
                                        </div>

                                        {/* Note Alasan Penolakan Jika Ada */}
                                        {doc && isRejected && doc.komentar && (
                                            <div className="mt-3 flex items-start gap-2 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/40 rounded-xl px-3.5 py-2 text-xs">
                                                <AlertCircle className="w-4 h-4 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                                                <div>
                                                    <span className="font-bold text-red-700 dark:text-red-300">Alasan Penolakan: </span>
                                                    <span className="text-red-600 dark:text-red-400">{doc.komentar}</span>
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                ) : (
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 text-center shadow-xs">
                        <Building2 className="w-8 h-8 text-slate-300 dark:text-slate-600 stroke-[1.5] mx-auto mb-2" />
                        <p className="text-xs font-medium text-slate-600 dark:text-slate-400">
                            Pilih kecamatan pada filter di atas untuk melihat dokumen D-Hasil pleno PPK.
                        </p>
                    </div>
                )}

                {/* Ringkasan Statistik Verifikasi TPS (Jika ada TPS dimuat) */}
                {totalTps > 0 && (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        {/* Card 1: Progress Total */}
                        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs">
                            <div className="flex items-center justify-between mb-2">
                                <span className="text-xs font-medium text-[#475569] dark:text-[#94a3b8]">Kelengkapan TPS</span>
                                <span className="text-xs font-bold text-[#bb152c] dark:text-red-400">{persenVerifikasi}%</span>
                            </div>
                            <div className="flex items-baseline gap-2 mb-2">
                                <span className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc]">
                                    {totalTerverifikasi}
                                </span>
                                <span className="text-xs text-[#94a3b8]">/ {totalTargetDokumen} Terverifikasi</span>
                            </div>
                            <div className="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div
                                    className="h-full bg-[#bb152c] dark:bg-red-500 rounded-full transition-all duration-300"
                                    style={{ width: `${persenVerifikasi}%` }}
                                />
                            </div>
                        </div>

                        {/* Card 2: Terverifikasi */}
                        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-[#475569] dark:text-[#94a3b8]">Terverifikasi</span>
                                <span className="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                                    <CheckCircle2 className="w-4 h-4" />
                                </span>
                            </div>
                            <div className="mt-2 flex items-baseline gap-2">
                                <span className="text-2xl font-bold font-heading text-emerald-600 dark:text-emerald-400">
                                    {totalTerverifikasi}
                                </span>
                                <span className="text-xs text-[#94a3b8]">berkas TPS sah</span>
                            </div>
                        </div>

                        {/* Card 3: Menunggu Verifikasi */}
                        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-[#475569] dark:text-[#94a3b8]">Menunggu Verifikasi</span>
                                <span className="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                                    <Clock className="w-4 h-4" />
                                </span>
                            </div>
                            <div className="mt-2 flex items-baseline gap-2">
                                <span className="text-2xl font-bold font-heading text-amber-600 dark:text-amber-400">
                                    {totalMenunggu}
                                </span>
                                <span className="text-xs text-[#94a3b8]">perlu dicek</span>
                            </div>
                        </div>

                        {/* Card 4: Ditolak / Belum Upload */}
                        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-[#475569] dark:text-[#94a3b8]">Ditolak / Belum Upload</span>
                                <span className="p-1.5 rounded-lg bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400">
                                    <XCircle className="w-4 h-4" />
                                </span>
                            </div>
                            <div className="mt-2 flex items-baseline gap-2">
                                <span className="text-2xl font-bold font-heading text-red-600 dark:text-red-400">
                                    {totalDitolak}
                                </span>
                                <span className="text-xs text-[#94a3b8]">
                                    ditolak · {Math.max(0, totalTargetDokumen - totalMasuk)} belum ada
                                </span>
                            </div>
                        </div>
                    </div>
                )}

                {/* TPS Documents List Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-sm overflow-hidden">
                    <div className="p-4 border-b border-[#e2e8f0] dark:border-[#1e293b] flex items-center justify-between">
                        <div>
                            <h3 className="font-bold font-heading text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                Daftar Dokumen C-Hasil TPS {activeDesa ? `(Desa ${activeDesa.nama})` : ''}
                            </h3>
                            <p className="text-xs text-[#475569] dark:text-[#94a3b8]">
                                Klik berkas untuk membuka preview PDF, atau gunakan tombol centang/silang untuk verifikasi langsung.
                            </p>
                        </div>
                        {totalTps > 0 && (
                            <span className="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                Total: {totalTps} TPS
                            </span>
                        )}
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs min-w-[650px]">
                            <thead className="bg-[#f8fafc] dark:bg-[#0b0f19] border-b border-[#e2e8f0] dark:border-[#1e293b] uppercase tracking-wider text-[#475569] dark:text-[#94a3b8] font-bold text-[11px]">
                                <tr>
                                    <th className="px-5 py-3.5">Nama TPS</th>
                                    <th className="px-5 py-3.5">Desa & Kecamatan</th>
                                    <th className="px-5 py-3.5">Progres Verifikasi</th>
                                    <th className="px-5 py-3.5">Dokumen Ter-upload</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#f1f5f9] dark:divide-[#1e293b]">
                                {tpsList?.length > 0 ? (
                                    tpsList.map((tps) => {
                                        const activeDocs = getActiveDocs(tps.dokumens);
                                        const tpsVerifiedCount = activeDocs.filter(d => d.status === 'terverifikasi').length;
                                        const isAllVerified = targetJenisCount > 0 && tpsVerifiedCount === targetJenisCount;

                                        return (
                                            <tr key={tps.id} className="hover:bg-[#f8fafc] dark:hover:bg-[#1f2937]/50 transition">
                                                <td className="px-5 py-3.5 font-bold text-[#0f172a] dark:text-[#f8fafc] whitespace-nowrap">
                                                    <div className="flex items-center gap-2">
                                                        <div className="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400">
                                                            <MapPin className="w-3.5 h-3.5" />
                                                        </div>
                                                        <span>{tps.nama}</span>
                                                    </div>
                                                </td>
                                                <td className="px-5 py-3.5 text-[#475569] dark:text-[#94a3b8] whitespace-nowrap">
                                                    {tps.desa?.nama} — Kec. {tps.desa?.kecamatan?.nama}
                                                </td>
                                                <td className="px-5 py-3.5 whitespace-nowrap">
                                                    <span
                                                        className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold border ${
                                                            isAllVerified
                                                                ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200/80 dark:border-emerald-800/40'
                                                                : tpsVerifiedCount > 0
                                                                ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border-amber-200/80 dark:border-amber-800/40'
                                                                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700'
                                                        }`}
                                                    >
                                                        {isAllVerified ? (
                                                            <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600" />
                                                        ) : (
                                                            <FileCheck className="w-3.5 h-3.5" />
                                                        )}
                                                        <span>{tpsVerifiedCount}/{targetJenisCount} Terverifikasi</span>
                                                    </span>
                                                </td>
                                                <td className="px-5 py-3.5">
                                                    <div className="flex flex-wrap gap-2">
                                                        {activeDocs.length > 0 ? (
                                                            activeDocs.map(doc => {
                                                                const isVerified = doc.status === 'terverifikasi';
                                                                const isRejected = doc.status === 'ditolak';
                                                                const isWaiting = doc.status === 'menunggu_verifikasi';

                                                                const badgeColor = isVerified
                                                                    ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200/80 dark:border-emerald-800/50'
                                                                    : isRejected
                                                                    ? 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400 border-red-200/80 dark:border-red-800/50'
                                                                    : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border-amber-200/80 dark:border-amber-800/50';

                                                                return (
                                                                    <div
                                                                        key={doc.id}
                                                                        className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition shadow-2xs ${badgeColor}`}
                                                                    >
                                                                        {/* Status Icon */}
                                                                        {isVerified && (
                                                                            <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
                                                                        )}
                                                                        {isRejected && (
                                                                            <XCircle className="w-3.5 h-3.5 text-red-600 dark:text-red-400 flex-shrink-0" />
                                                                        )}
                                                                        {isWaiting && (
                                                                            <Clock className="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 flex-shrink-0" />
                                                                        )}

                                                                        {/* Document Button -> Preview Modal */}
                                                                        <button
                                                                            type="button"
                                                                            onClick={() => setPreviewDoc({ ...doc, level: 'tps', tps_nama: tps.nama, desa_nama: tps.desa?.nama })}
                                                                            title={`Klik untuk Preview PDF (${isVerified ? 'Terverifikasi' : isRejected ? `Ditolak: ${doc.komentar || ''}` : 'Menunggu Verifikasi'})`}
                                                                            className="inline-flex items-center gap-1 cursor-pointer font-bold hover:underline"
                                                                        >
                                                                            <span>{doc.jenis?.toUpperCase()}</span>
                                                                        </button>

                                                                        <span className="text-slate-300 dark:text-slate-700 select-none">|</span>

                                                                        {/* Quick Actions (Verifikasi / Tolak Cepat) */}
                                                                        {isWaiting && (
                                                                            <>
                                                                                <button
                                                                                    type="button"
                                                                                    onClick={(e) => {
                                                                                        e.stopPropagation();
                                                                                        handleVerify(doc);
                                                                                    }}
                                                                                    disabled={isSubmitting}
                                                                                    title="Verifikasi Cepat"
                                                                                    className="p-0.5 rounded hover:bg-emerald-200/60 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 transition cursor-pointer"
                                                                                >
                                                                                    <Check className="w-3 h-3" />
                                                                                </button>
                                                                                <button
                                                                                    type="button"
                                                                                    onClick={(e) => {
                                                                                        e.stopPropagation();
                                                                                        handleOpenReject(doc, tps.nama);
                                                                                    }}
                                                                                    disabled={isSubmitting}
                                                                                    title="Tolak Dokumen"
                                                                                    className="p-0.5 rounded hover:bg-red-200/60 dark:hover:bg-red-900/60 text-red-700 dark:text-red-300 transition cursor-pointer"
                                                                                >
                                                                                    <X className="w-3 h-3" />
                                                                                </button>
                                                                                <span className="text-slate-300 dark:text-slate-700 select-none">|</span>
                                                                            </>
                                                                        )}

                                                                        {/* Download Button */}
                                                                        <a
                                                                            href={`/dokumen/${doc.id}/download`}
                                                                            title="Unduh Berkas PDF"
                                                                            className="hover:opacity-80 transition cursor-pointer p-0.5"
                                                                        >
                                                                            <Download className="w-3 h-3" />
                                                                        </a>
                                                                    </div>
                                                                );
                                                            })
                                                        ) : (
                                                            <span className="text-[11px] text-[#94a3b8] italic">Belum ada PDF yang diupload</span>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })
                                ) : (
                                    <tr>
                                        <td colSpan="4" className="px-5 py-12 text-center text-xs text-[#94a3b8]">
                                            <div className="flex flex-col items-center justify-center gap-2">
                                                <FolderOpen className="w-8 h-8 text-slate-300 dark:text-slate-600 stroke-[1.5]" />
                                                <p className="font-medium text-slate-600 dark:text-slate-400">
                                                    {activeKec
                                                        ? 'Pilih desa di filter atas untuk melihat rincian dokumen C-Hasil tingkat TPS.'
                                                        : 'Pilih kecamatan dan desa di atas untuk melihat dokumen C-Hasil TPS.'}
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* Modal Preview PDF In-App */}
            {previewDoc && (
                <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4 md:p-6 animate-in fade-in duration-150">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-5xl h-[90vh] flex flex-col shadow-2xl overflow-hidden">
                        {/* Topbar Modal */}
                        <div className="px-5 py-3.5 border-b border-[#e2e8f0] dark:border-[#1e293b] flex items-center justify-between gap-3 bg-white dark:bg-[#151f32] shrink-0">
                            <div className="flex items-center gap-3">
                                <div className="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 flex items-center justify-center shrink-0">
                                    <FileText className="w-4 h-4" />
                                </div>
                                <div>
                                    <div className="flex items-center gap-2 flex-wrap">
                                        <h3 className="font-bold font-heading text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                            {previewDoc.level === 'kecamatan' ? 'Formulir D-Hasil Pleno: ' : 'Formulir C-Hasil: '}
                                            {previewDoc.jenis?.toUpperCase()}
                                        </h3>
                                        <span
                                            className={`text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider inline-flex items-center gap-1 ${
                                                previewDoc.status === 'terverifikasi'
                                                    ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400'
                                                    : previewDoc.status === 'ditolak'
                                                    ? 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-400'
                                                    : 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-400'
                                            }`}
                                        >
                                            {previewDoc.status === 'terverifikasi' ? (
                                                <>
                                                    <CheckCircle2 className="w-3 h-3" />
                                                    <span>Terverifikasi</span>
                                                </>
                                            ) : previewDoc.status === 'ditolak' ? (
                                                <>
                                                    <XCircle className="w-3 h-3" />
                                                    <span>Ditolak</span>
                                                </>
                                            ) : (
                                                <>
                                                    <Clock className="w-3 h-3" />
                                                    <span>Menunggu Verifikasi</span>
                                                </>
                                            )}
                                        </span>
                                    </div>
                                    <p className="text-[11px] text-[#475569] dark:text-[#94a3b8] mt-0.5">
                                        {previewDoc.level === 'kecamatan'
                                            ? `PPK Kec. ${previewDoc.kec_nama || activeKec?.nama || ''} • `
                                            : previewDoc.tps_nama ? `TPS ${previewDoc.tps_nama} • ` : ''}
                                        {previewDoc.file_name} {previewDoc.uploader ? `• Diupload oleh ${previewDoc.uploader.name}` : ''}
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-center gap-2 shrink-0">
                                <a
                                    href={`/dokumen/${previewDoc.id}/download`}
                                    className="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 transition"
                                >
                                    <Download className="w-3.5 h-3.5" />
                                    <span className="hidden sm:inline">Unduh PDF</span>
                                </a>
                                <button
                                    onClick={() => setPreviewDoc(null)}
                                    className="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                                >
                                    <X className="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        {/* PDF Viewer Frame */}
                        <div className="flex-1 w-full h-full bg-slate-100 dark:bg-slate-900 relative">
                            <iframe
                                src={`/dokumen/${previewDoc.id}/preview`}
                                className="w-full h-full border-0"
                                title={`PDF ${previewDoc.jenis}`}
                            />
                        </div>

                        {/* Bottom Bar: Action Toolbar & Status */}
                        <div className="px-5 py-3 border-t border-[#e2e8f0] dark:border-[#1e293b] bg-slate-50 dark:bg-[#0b0f19] flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                            {/* Left: Status / Info / Alasan Penolakan */}
                            <div className="flex-1 w-full sm:w-auto">
                                {previewDoc.status === 'ditolak' && (
                                    <div className="flex items-start gap-2 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/40 rounded-xl px-3.5 py-2 text-xs">
                                        <XCircle className="w-4 h-4 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                                        <div>
                                            <div className="flex items-center gap-2">
                                                <span className="font-bold text-red-700 dark:text-red-300">Dokumen Ditolak</span>
                                                {previewDoc.verified_at && (
                                                    <span className="text-[10px] text-red-500/80">
                                                        ({formatDateTime(previewDoc.verified_at)})
                                                    </span>
                                                )}
                                            </div>
                                            <p className="text-red-600 dark:text-red-400 mt-0.5 font-medium">
                                                "{previewDoc.komentar || 'Tanpa keterangan'}"
                                            </p>
                                        </div>
                                    </div>
                                )}

                                {previewDoc.status === 'terverifikasi' && (
                                    <div className="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/40 rounded-xl px-3.5 py-2 text-xs">
                                        <CheckCircle2 className="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                                        <div>
                                            <span className="font-bold">Dokumen Terverifikasi & Sah</span>
                                            <span className="text-[11px] text-emerald-600/80 dark:text-emerald-400/80 ml-1.5">
                                                {previewDoc.verifier ? `oleh ${previewDoc.verifier.name}` : ''}
                                                {previewDoc.verified_at ? ` pada ${formatDateTime(previewDoc.verified_at)}` : ''}
                                            </span>
                                        </div>
                                    </div>
                                )}

                                {previewDoc.status === 'menunggu_verifikasi' && (
                                    <div className="flex items-center gap-2 text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/40 rounded-xl px-3.5 py-2 text-xs">
                                        <Clock className="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" />
                                        <span>Dokumen menunggu verifikasi. Periksa berkas sebelum menentukan status.</span>
                                    </div>
                                )}
                            </div>

                            {/* Right: Action Buttons */}
                            <div className="flex items-center gap-2 w-full sm:w-auto justify-end shrink-0">
                                {previewDoc.status !== 'ditolak' && (
                                    <button
                                        type="button"
                                        onClick={() => handleOpenReject(previewDoc, previewDoc.tps_nama, previewDoc.kec_nama)}
                                        disabled={isSubmitting}
                                        className="px-4 py-2 rounded-xl text-xs font-semibold border border-red-200 dark:border-red-900/50 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 active:scale-[0.98] transition cursor-pointer disabled:opacity-50 inline-flex items-center gap-1.5"
                                    >
                                        <XCircle className="w-3.5 h-3.5" />
                                        <span>Tolak Dokumen</span>
                                    </button>
                                )}

                                {previewDoc.status !== 'terverifikasi' && (
                                    <button
                                        type="button"
                                        onClick={() => handleVerify(previewDoc)}
                                        disabled={isSubmitting}
                                        className="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white transition cursor-pointer disabled:opacity-50 inline-flex items-center gap-1.5 shadow-xs"
                                    >
                                        {isSubmitting ? (
                                            <Loader2 className="w-3.5 h-3.5 animate-spin" />
                                        ) : (
                                            <CheckCircle2 className="w-3.5 h-3.5" />
                                        )}
                                        <span>{isSubmitting ? 'Memproses...' : 'Verifikasi Dokumen'}</span>
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Modal Input Alasan Penolakan */}
            {rejectingDoc && (
                <div className="fixed inset-0 z-60 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in duration-150">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-md p-5 sm:p-6 shadow-2xl space-y-4">
                        <div className="flex items-start justify-between gap-3">
                            <div className="flex items-center gap-3">
                                <div className="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                                    <XCircle className="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 className="font-bold font-heading text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                        Tolak Dokumen {rejectingDoc.level === 'kecamatan' ? 'D-Hasil' : 'C-Hasil'} {rejectingDoc.jenis?.toUpperCase()}
                                    </h3>
                                    <p className="text-xs text-[#475569] dark:text-[#94a3b8]">
                                        {rejectingDoc.level === 'kecamatan'
                                            ? `PPK Kec. ${rejectingDoc.kec_nama || activeKec?.nama || ''}`
                                            : `TPS: ${rejectingDoc.tps_nama || ''}`}
                                        {rejectingDoc.file_name ? ` • ${rejectingDoc.file_name}` : ''}
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={handleCloseReject}
                                className="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <form onSubmit={handleRejectSubmit} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                                    Alasan Penolakan <span className="text-red-500">*</span>
                                </label>
                                <textarea
                                    rows={4}
                                    maxLength={500}
                                    required
                                    value={rejectReason}
                                    onChange={(e) => setRejectReason(e.target.value)}
                                    placeholder="Contoh: Dokumen terlalu buram, tanda tangan saksi terpotong, atau lembar hasil tidak lengkap..."
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl p-3 text-xs outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/15 transition resize-none"
                                />
                                <div className="flex items-center justify-between mt-1 text-[11px] text-[#94a3b8]">
                                    <span>Wajib diisi agar pihak pengunggah dapat mengupload ulang berkas yang sesuai.</span>
                                    <span>{rejectReason.length}/500</span>
                                </div>
                                {actionError && (
                                    <p className="text-xs text-red-600 dark:text-red-400 mt-1 font-medium">
                                        {actionError}
                                    </p>
                                )}
                            </div>

                            <div className="flex items-center justify-end gap-2 pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                                <button
                                    type="button"
                                    onClick={handleCloseReject}
                                    disabled={isSubmitting}
                                    className="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={!rejectReason.trim() || isSubmitting}
                                    className="px-4 py-2 text-xs font-semibold bg-red-600 hover:bg-red-700 active:scale-[0.98] text-white rounded-xl transition cursor-pointer disabled:opacity-50 inline-flex items-center gap-1.5 shadow-xs"
                                >
                                    {isSubmitting ? (
                                        <Loader2 className="w-3.5 h-3.5 animate-spin" />
                                    ) : (
                                        <XCircle className="w-3.5 h-3.5" />
                                    )}
                                    <span>{isSubmitting ? 'Menyimpan...' : 'Tolak Dokumen'}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
