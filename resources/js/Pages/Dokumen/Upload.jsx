import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import {
    Upload,
    FileText,
    CheckCircle2,
    Clock,
    AlertCircle,
    Download,
    Eye,
    X,
    ArrowLeft,
    Check,
    FileCheck
} from 'lucide-react';

export default function DokumenUpload({
    tps,
    uploaded = {},
    isAdminView = false,
    aktifJenis = [],
    jenisLabels = {},
    statusLabels = {},
    statusColors = {},
}) {
    const [uploadModalOpen, setUploadModalOpen] = useState(false);
    const [selectedJenis, setSelectedJenis] = useState('');
    const [selectedLabel, setSelectedLabel] = useState('');
    const [previewModalOpen, setPreviewModalOpen] = useState(false);
    const [previewUrl, setPreviewUrl] = useState('');
    const [previewTitle, setPreviewTitle] = useState('');

    const { data, setData, post, processing, errors, reset, progress } = useForm({
        jenis: '',
        file: null,
    });

    const openUpload = (jenis, label) => {
        if (isAdminView) return;
        setSelectedJenis(jenis);
        setSelectedLabel(label);
        setData({ jenis: jenis, file: null });
        setUploadModalOpen(true);
    };

    const closeUpload = () => {
        setUploadModalOpen(false);
        reset();
    };

    const handleFileChange = (e) => {
        if (e.target.files && e.target.files[0]) {
            setData('file', e.target.files[0]);
        }
    };

    const handleSubmitUpload = (e) => {
        e.preventDefault();
        post('/dokumen/upload', {
            onSuccess: () => {
                closeUpload();
            },
        });
    };

    const openPreview = (dok, label) => {
        setPreviewUrl(`/dokumen/${dok.id}/preview`);
        setPreviewTitle(`Preview C-Hasil: ${label}`);
        setPreviewModalOpen(true);
    };

    const formatBytes = (bytes) => {
        if (!bytes || bytes === 0) return '0 KB';
        const k = 1024;
        const dm = 1;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    };

    const activeList = Array.isArray(aktifJenis) && aktifJenis.length > 0
        ? aktifJenis
        : Object.keys(jenisLabels || {});

    return (
        <AdminLayout title="Upload Dokumen C-Hasil">
            <Head title="Upload Dokumen C-Hasil" />

            <div className="space-y-6">
                {/* Mode View Banner if user is Admin or PPS viewing as KPPS */}
                {isAdminView && (
                    <div className="bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div className="flex items-center gap-3">
                            <div className="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-900/50 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                                <Eye className="w-4 h-4" />
                            </div>
                            <div>
                                <span className="text-[10px] font-bold uppercase tracking-wider text-sky-700 dark:text-sky-300">
                                    Mode Peninjauan (Read-Only)
                                </span>
                                <p className="text-xs text-sky-800 dark:text-sky-200">
                                    Anda sedang meninjau TPS sebagai pengawas tingkat atas. Dokumen tidak dapat diubah dari mode ini.
                                </p>
                            </div>
                        </div>
                        <Link
                            href="/pps/data-tps"
                            className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-[#151f32] text-sky-700 dark:text-sky-300 border border-sky-300 dark:border-sky-700 hover:bg-sky-100 transition shadow-xs"
                        >
                            <ArrowLeft className="w-3.5 h-3.5" />
                            <span>Kembali</span>
                        </Link>
                    </div>
                )}

                {/* Header Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md">
                            Tingkat TPS (KPPS)
                        </span>
                        <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                            Upload Dokumen C-Hasil
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                            {tps?.nama} · Desa {tps?.desa?.nama} · Kec. {tps?.desa?.kecamatan?.nama}
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <span className="text-xs font-semibold px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] border border-slate-200 dark:border-slate-700">
                            Format PDF · Maks. 5 MB
                        </span>
                    </div>
                </div>

                {/* Document Cards */}
                <div className="space-y-4">
                    {Object.entries(jenisLabels || {}).map(([key, label]) => {
                        if (!activeList.includes(key)) return null;
                        const dok = uploaded[key] || null;

                        return (
                            <div
                                key={key}
                                className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs transition hover:border-slate-300 dark:hover:border-slate-700"
                            >
                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div className="flex items-start gap-4">
                                        <div
                                            className={`w-12 h-12 rounded-xl flex items-center justify-center shrink-0 ${
                                                dok
                                                    ? dok.status === 'terverifikasi'
                                                        ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400'
                                                        : dok.status === 'ditolak'
                                                        ? 'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400'
                                                        : 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400'
                                                    : 'bg-slate-100 dark:bg-[#1e293b] text-slate-400'
                                            }`}
                                        >
                                            <FileText className="w-6 h-6" />
                                        </div>

                                        <div>
                                            <div className="flex items-center gap-2 flex-wrap">
                                                <h3 className="font-bold text-base text-[#0f172a] dark:text-[#f8fafc]">
                                                    {label}
                                                </h3>
                                                {dok ? (
                                                    <span
                                                        className={`text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-md border ${
                                                            dok.status === 'terverifikasi'
                                                                ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border-emerald-300 dark:border-emerald-800'
                                                                : dok.status === 'ditolak'
                                                                ? 'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border-red-300 dark:border-red-800'
                                                                : 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border-amber-300 dark:border-amber-800'
                                                        }`}
                                                    >
                                                        {statusLabels[dok.status] || dok.status}
                                                    </span>
                                                ) : (
                                                    <span className="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-300 dark:border-slate-700">
                                                        Belum Diupload
                                                    </span>
                                                )}
                                            </div>

                                            {dok ? (
                                                <div className="flex items-center gap-3 text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                                                    <span>{dok.file_name}</span>
                                                    <span>•</span>
                                                    <span>{formatBytes(dok.file_size)}</span>
                                                    <span>•</span>
                                                    <span className="flex items-center gap-1">
                                                        <Clock className="w-3 h-3" />
                                                        Terakhir diupdate
                                                    </span>
                                                </div>
                                            ) : (
                                                <p className="text-xs text-[#94a3b8] mt-1">
                                                    Formulir C-Hasil resmi belum diunggah oleh KPPS.
                                                </p>
                                            )}
                                        </div>
                                    </div>

                                    {/* Action buttons */}
                                    <div className="flex items-center gap-2 self-end sm:self-center">
                                        {dok && (
                                            <>
                                                <button
                                                    onClick={() => openPreview(dok, label)}
                                                    className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-[#1e293b] hover:bg-slate-200 dark:hover:bg-slate-700 text-[#0f172a] dark:text-[#f8fafc] border border-slate-200 dark:border-slate-700 transition"
                                                >
                                                    <Eye className="w-3.5 h-3.5" />
                                                    <span>Preview</span>
                                                </button>
                                                <a
                                                    href={`/dokumen/${dok.id}/download`}
                                                    className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-[#1e293b] hover:bg-slate-200 dark:hover:bg-slate-700 text-[#0f172a] dark:text-[#f8fafc] border border-slate-200 dark:border-slate-700 transition"
                                                >
                                                    <Download className="w-3.5 h-3.5" />
                                                    <span>Unduh</span>
                                                </a>
                                            </>
                                        )}

                                        {!isAdminView && (
                                            <button
                                                onClick={() => openUpload(key, label)}
                                                className={`inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white transition shadow-sm ${
                                                    dok
                                                        ? 'bg-slate-700 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600'
                                                        : 'bg-[#bb152c] hover:bg-[#991124]'
                                                }`}
                                            >
                                                <Upload className="w-3.5 h-3.5" />
                                                <span>{dok ? 'Ganti File' : 'Upload File'}</span>
                                            </button>
                                        )}
                                    </div>
                                </div>

                                {/* Rejection Note if rejected */}
                                {dok && dok.status === 'ditolak' && dok.komentar && (
                                    <div className="mt-4 pt-3 border-t border-red-100 dark:border-red-950/60 flex items-start gap-2.5 bg-red-50/60 dark:bg-red-950/30 p-3 rounded-xl border border-red-200 dark:border-red-900/50">
                                        <AlertCircle className="w-4 h-4 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                                        <div className="text-xs">
                                            <p className="font-bold text-red-800 dark:text-red-300">
                                                Catatan Penolakan / Revisi dari Verifikator:
                                            </p>
                                            <p className="text-red-700 dark:text-red-400 mt-0.5 leading-relaxed">
                                                "{dok.komentar}"
                                            </p>
                                            <p className="text-[10px] text-red-500 dark:text-red-400/80 mt-1">
                                                Silakan periksa kembali berkas fisik C-Hasil dan unggah ulang berkas PDF yang sesuai.
                                            </p>
                                        </div>
                                    </div>
                                )}
                            </div>
                        );
                    })}
                </div>
            </div>

            {/* Upload Modal */}
            {uploadModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                        <div className="p-5 border-b border-[#f1f5f9] dark:border-[#1e293b] flex items-center justify-between">
                            <div>
                                <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400">
                                    Unggah Berkas
                                </span>
                                <h3 className="font-bold text-[#0f172a] dark:text-[#f8fafc] text-base mt-0.5">
                                    {selectedLabel}
                                </h3>
                            </div>
                            <button
                                onClick={closeUpload}
                                className="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            >
                                <X className="w-4 h-4" />
                            </button>
                        </div>

                        <form onSubmit={handleSubmitUpload} className="p-5 space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-2">
                                    Pilih Berkas PDF C-Hasil
                                </label>
                                <div className="border-2 border-dashed border-[#e2e8f0] dark:border-[#1e293b] hover:border-[#bb152c] dark:hover:border-[#bb152c] rounded-2xl p-6 text-center transition bg-[#f8fafc] dark:bg-[#0b0f19]">
                                    <input
                                        type="file"
                                        id="file-upload-input"
                                        accept="application/pdf"
                                        onChange={handleFileChange}
                                        className="hidden"
                                    />
                                    <label
                                        htmlFor="file-upload-input"
                                        className="cursor-pointer flex flex-col items-center justify-center"
                                    >
                                        <div className="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 flex items-center justify-center mb-3">
                                            <Upload className="w-6 h-6" />
                                        </div>
                                        {data.file ? (
                                            <div>
                                                <p className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                                    {data.file.name}
                                                </p>
                                                <p className="text-[11px] text-[#475569] dark:text-[#94a3b8] mt-1">
                                                    {formatBytes(data.file.size)}
                                                </p>
                                                <span className="inline-block mt-2 text-[10px] font-bold text-[#bb152c] dark:text-red-400 underline">
                                                    Klik untuk ganti file
                                                </span>
                                            </div>
                                        ) : (
                                            <div>
                                                <p className="text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc]">
                                                    Klik untuk memilih file PDF
                                                </p>
                                                <p className="text-[10px] text-[#94a3b8] mt-1">
                                                    Format .PDF hingga maksimal 5 MB
                                                </p>
                                            </div>
                                        )}
                                    </label>
                                </div>
                                {errors.file && (
                                    <p className="text-xs text-red-500 mt-1.5 font-medium">
                                        {errors.file}
                                    </p>
                                )}
                            </div>

                            {progress && (
                                <div className="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                    <div
                                        className="bg-[#bb152c] h-2 transition-all duration-200"
                                        style={{ width: `${progress.percentage}%` }}
                                    ></div>
                                </div>
                            )}

                            <div className="flex items-center gap-3 pt-2">
                                <button
                                    type="button"
                                    onClick={closeUpload}
                                    className="flex-1 py-2.5 rounded-xl text-xs font-bold text-[#475569] dark:text-[#94a3b8] hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={!data.file || processing}
                                    className="flex-1 py-2.5 rounded-xl text-xs font-bold text-white bg-[#bb152c] hover:bg-[#991124] disabled:opacity-50 transition shadow-md shadow-[#bb152c]/20"
                                >
                                    {processing ? 'Mengunggah...' : 'Unggah Sekarang'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* In-App PDF Preview Modal */}
            {previewModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-xs">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-4xl h-[85vh] shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                        <div className="p-4 border-b border-[#f1f5f9] dark:border-[#1e293b] flex items-center justify-between shrink-0">
                            <div className="flex items-center gap-2">
                                <FileCheck className="w-5 h-5 text-[#bb152c]" />
                                <h3 className="font-bold text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                    {previewTitle}
                                </h3>
                            </div>
                            <div className="flex items-center gap-2">
                                <a
                                    href={previewUrl}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-[#0f172a] dark:text-[#f8fafc] hover:bg-slate-200 transition"
                                >
                                    Buka di Tab Baru
                                </a>
                                <button
                                    onClick={() => setPreviewModalOpen(false)}
                                    className="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                >
                                    <X className="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        <div className="flex-1 bg-slate-900 w-full h-full relative">
                            <iframe
                                src={previewUrl}
                                className="w-full h-full border-0"
                                title="Pratinjau PDF"
                            />
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
