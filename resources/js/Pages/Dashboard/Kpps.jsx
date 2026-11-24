import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import { Upload, Edit, CheckCircle2, FileSpreadsheet, Eye, ArrowRight } from 'lucide-react';

export default function KppsDashboard({ electionSummary, viewTps, isAdminView }) {
    return (
        <AdminLayout title="Dashboard KPPS">
            <Head title="Dashboard KPPS" />

            <div className="space-y-6">
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md">
                            Tingkat TPS (KPPS)
                        </span>
                        <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                            {viewTps ? `${viewTps.nama} - ${viewTps.desa?.nama}` : 'Dashboard KPPS'}
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                            Input data perolehan suara per jenis pemilihan dan unggah dokumen C-Hasil TPS.
                        </p>
                    </div>

                    {isAdminView && (
                        <span className="text-xs font-semibold px-3 py-1.5 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800">
                            Mode Pengawasan
                        </span>
                    )}
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <Link
                        href="/rekap"
                        className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] hover:border-[#bb152c] dark:hover:border-[#bb152c] rounded-2xl p-6 shadow-xs transition group flex items-center justify-between"
                    >
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 flex items-center justify-center shrink-0">
                                <Edit className="w-6 h-6" />
                            </div>
                            <div>
                                <h3 className="font-bold text-[#0f172a] dark:text-[#f8fafc] text-base">Input Rekap Suara</h3>
                                <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-0.5">Isi suara TPS per jenis pemilihan</p>
                            </div>
                        </div>
                        <span className="text-xs font-semibold text-[#bb152c] dark:text-red-400 group-hover:translate-x-1 transition transform flex items-center gap-1">
                            <span>Input</span>
                            <ArrowRight className="w-3.5 h-3.5" />
                        </span>
                    </Link>

                    <Link
                        href="/dokumen/upload"
                        className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] hover:border-emerald-500 dark:hover:border-emerald-500 rounded-2xl p-6 shadow-xs transition group flex items-center justify-between"
                    >
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <Upload className="w-6 h-6" />
                            </div>
                            <div>
                                <h3 className="font-bold text-[#0f172a] dark:text-[#f8fafc] text-base">Upload Dokumen C-Hasil</h3>
                                <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-0.5">Unggah PDF C-Hasil TPS</p>
                            </div>
                        </div>
                        <span className="text-xs font-semibold text-emerald-600 dark:text-emerald-400 group-hover:translate-x-1 transition transform flex items-center gap-1">
                            <span>Upload</span>
                            <ArrowRight className="w-3.5 h-3.5" />
                        </span>
                    </Link>
                </div>
            </div>
        </AdminLayout>
    );
}
