import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import { Pin, Upload, Edit, CheckCircle2 } from 'lucide-react';

export default function KppsDashboard({ electionSummary, viewTps, isAdminView }) {
    return (
        <AdminLayout title="Dashboard KPPS">
            <Head title="Dashboard KPPS" />

            <div className="space-y-6">
                <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                    <span className="text-[10px] uppercase font-bold tracking-widest text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/50 px-2.5 py-1 rounded-md">
                        Tingkat TPS (KPPS)
                    </span>
                    <h1 className="text-2xl font-bold text-slate-900 dark:text-white tracking-tight mt-2">
                        {viewTps ? `${viewTps.nama} - ${viewTps.desa?.nama}` : 'Dashboard KPPS'}
                    </h1>
                    <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Input data perolehan suara per jenis pemilihan dan unggah dokumen C-Hasil TPS.
                    </p>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <Link
                        href="/rekap"
                        className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-red-500 dark:hover:border-red-500 rounded-2xl p-6 shadow-xs transition group flex items-center justify-between"
                    >
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                                <Edit className="w-6 h-6" />
                            </div>
                            <div>
                                <h3 className="font-bold text-slate-900 dark:text-white text-base">Input Rekap Suara</h3>
                                <p className="text-xs text-slate-500 mt-0.5">Isi suara TPS per jenis pemilihan</p>
                            </div>
                        </div>
                        <span className="text-xs font-semibold text-red-600 group-hover:translate-x-1 transition transform">Input →</span>
                    </Link>

                    <Link
                        href="/dokumen/upload"
                        className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-teal-500 dark:hover:border-teal-500 rounded-2xl p-6 shadow-xs transition group flex items-center justify-between"
                    >
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                                <Upload className="w-6 h-6" />
                            </div>
                            <div>
                                <h3 className="font-bold text-slate-900 dark:text-white text-base">Upload Dokumen C-Hasil</h3>
                                <p className="text-xs text-slate-500 mt-0.5">Unggah PDF C-Hasil TPS</p>
                            </div>
                        </div>
                        <span className="text-xs font-semibold text-teal-600 group-hover:translate-x-1 transition transform">Upload →</span>
                    </Link>
                </div>
            </div>
        </AdminLayout>
    );
}
