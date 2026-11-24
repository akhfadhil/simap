import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import {
    FileSpreadsheet,
    Edit3,
    Eye,
    Download,
    CheckCircle2,
    Clock,
    AlertCircle,
    Vote,
    Users
} from 'lucide-react';

export default function RekapKppsIndex({
    tps,
    rekaps = {},
    aktifJenis = [],
    jenisLabels = {},
    canEditRekap = false,
}) {
    const activeList = Array.isArray(aktifJenis) && aktifJenis.length > 0
        ? aktifJenis
        : Object.keys(jenisLabels || {});

    const formatNumber = (num) => (num || 0).toLocaleString('id-ID');

    return (
        <AdminLayout title="Input Rekap Suara C-Hasil">
            <Head title="Input Rekap Suara C-Hasil" />

            <div className="space-y-6">
                {/* Header Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md">
                            Tingkat TPS (KPPS)
                        </span>
                        <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                            Rekapitulasi Suara C-Hasil
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                            {tps?.nama} · Desa {tps?.desa?.nama} · Kec. {tps?.desa?.kecamatan?.nama}
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <span className="text-xs font-semibold px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] border border-slate-200 dark:border-slate-700">
                            {activeList.length} Jenis Pemilihan Aktif
                        </span>
                    </div>
                </div>

                {/* Grid of Election Types */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    {Object.entries(jenisLabels || {}).map(([key, label]) => {
                        if (!activeList.includes(key)) return null;
                        const rekap = rekaps[key] || null;
                        const isDraft = rekap && rekap.status === 'draft';
                        const isFinal = rekap && rekap.status === 'final';

                        const totalDpt = (rekap?.dpt_lk || 0) + (rekap?.dpt_pr || 0);

                        return (
                            <div
                                key={key}
                                className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-xs overflow-hidden flex flex-col justify-between transition hover:border-slate-300 dark:hover:border-slate-700"
                            >
                                <div>
                                    {/* Card Header */}
                                    <div className="p-5 border-b border-[#f1f5f9] dark:border-[#1e293b] flex items-start justify-between gap-3">
                                        <div>
                                            <span className="text-[10px] uppercase font-bold tracking-wider text-slate-400">
                                                Formulir C-Hasil
                                            </span>
                                            <h3 className="font-bold text-base text-[#0f172a] dark:text-[#f8fafc] mt-0.5">
                                                {label}
                                            </h3>
                                        </div>

                                        {isFinal ? (
                                            <span className="text-[10px] uppercase font-bold tracking-wider px-2.5 py-1 rounded-md bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800 shrink-0">
                                                ✓ Final
                                            </span>
                                        ) : isDraft ? (
                                            <span className="text-[10px] uppercase font-bold tracking-wider px-2.5 py-1 rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-300 dark:border-amber-800 shrink-0">
                                                Draft
                                            </span>
                                        ) : (
                                            <span className="text-[10px] uppercase font-bold tracking-wider px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-300 dark:border-slate-700 shrink-0">
                                                Belum Diisi
                                            </span>
                                        )}
                                    </div>

                                    {/* Body Stats Preview */}
                                    <div className="p-5 space-y-2.5 text-xs text-[#475569] dark:text-[#94a3b8]">
                                        {rekap ? (
                                            <>
                                                <div className="flex items-center justify-between">
                                                    <span>Total Pemilih (DPT)</span>
                                                    <span className="font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                                        {formatNumber(totalDpt)}
                                                    </span>
                                                </div>
                                                <div className="flex items-center justify-between">
                                                    <span>Surat Suara Digunakan</span>
                                                    <span className="font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                                        {formatNumber(rekap.ss_digunakan || 0)}
                                                    </span>
                                                </div>
                                                <div className="flex items-center justify-between">
                                                    <span>Suara Sah</span>
                                                    <span className="font-bold text-emerald-600 dark:text-emerald-400">
                                                        {formatNumber(rekap.suara_sah || 0)}
                                                    </span>
                                                </div>
                                                <div className="flex items-center justify-between">
                                                    <span>Suara Tidak Sah</span>
                                                    <span className="font-bold text-amber-600 dark:text-amber-400">
                                                        {formatNumber(rekap.suara_tidak_sah || 0)}
                                                    </span>
                                                </div>
                                            </>
                                        ) : (
                                            <div className="py-4 text-center">
                                                <Vote className="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto mb-2" />
                                                <p className="text-xs text-slate-400">
                                                    Belum ada data perolehan suara yang disimpan.
                                                </p>
                                            </div>
                                        )}
                                    </div>
                                </div>

                                {/* Card Actions */}
                                <div className="p-4 bg-[#f8fafc] dark:bg-[#0b0f19]/40 border-t border-[#f1f5f9] dark:border-[#1e293b] flex items-center gap-2">
                                    {rekap && (
                                        <a
                                            href={`/rekap/${key}/export`}
                                            title="Export Excel"
                                            className="p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] hover:bg-slate-50 transition shadow-2xs"
                                        >
                                            <Download className="w-4 h-4" />
                                        </a>
                                    )}

                                    <Link
                                        href={`/rekap/${key}`}
                                        className={`flex-1 py-2 rounded-xl text-xs font-bold text-center transition flex items-center justify-center gap-1.5 shadow-2xs ${
                                            canEditRekap && !isFinal
                                                ? 'bg-[#bb152c] hover:bg-[#991124] text-white shadow-[#bb152c]/20'
                                                : 'bg-white dark:bg-[#151f32] text-[#0f172a] dark:text-[#f8fafc] border border-[#e2e8f0] dark:border-[#1e293b] hover:bg-slate-50 dark:hover:bg-slate-800'
                                        }`}
                                    >
                                        {canEditRekap && !isFinal ? (
                                            <>
                                                <Edit3 className="w-3.5 h-3.5" />
                                                <span>{isDraft ? 'Lanjut Isi' : 'Mulai Isi'}</span>
                                            </>
                                        ) : (
                                            <>
                                                <Eye className="w-3.5 h-3.5" />
                                                <span>Lihat Detail</span>
                                            </>
                                        )}
                                    </Link>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </AdminLayout>
    );
}
