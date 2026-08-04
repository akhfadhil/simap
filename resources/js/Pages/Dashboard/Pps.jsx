import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import { Building2, Vote, CheckCircle2, Clock } from 'lucide-react';

export default function PpsDashboard({ electionSummary, viewDesa, isAdminView }) {
    const docSummary = electionSummary?.documentsSummary || {};

    return (
        <AdminLayout title="Dashboard PPS">
            <Head title="Dashboard PPS" />

            <div className="space-y-6">
                <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                    <span className="text-[10px] uppercase font-bold tracking-widest text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-950/50 px-2.5 py-1 rounded-md">
                        Tingkat Desa/Kelurahan (PPS)
                    </span>
                    <h1 className="text-2xl font-bold text-slate-900 dark:text-white tracking-tight mt-2">
                        {viewDesa ? `Desa ${viewDesa.nama}` : 'Dashboard PPS'}
                    </h1>
                    <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Monitoring dan verifikasi dokumen C-Hasil TPS di tingkat desa.
                    </p>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex items-center gap-4">
                        <div className="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200 shrink-0">
                            <Vote className="w-6 h-6" />
                        </div>
                        <div>
                            <span className="text-xs font-medium text-slate-500 dark:text-slate-400">Total TPS</span>
                            <p className="text-xl font-bold text-slate-900 dark:text-white mt-0.5">
                                {docSummary.total_tps?.toLocaleString('id-ID') || 0}
                            </p>
                        </div>
                    </div>

                    <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex items-center gap-4">
                        <div className="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0 border border-emerald-100 dark:border-emerald-900/50">
                            <CheckCircle2 className="w-6 h-6" />
                        </div>
                        <div>
                            <span className="text-xs font-medium text-slate-500 dark:text-slate-400">Terverifikasi</span>
                            <p className="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">
                                {docSummary.verified_count?.toLocaleString('id-ID') || 0}
                            </p>
                        </div>
                    </div>

                    <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex items-center gap-4">
                        <div className="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0 border border-amber-100 dark:border-amber-900/50">
                            <Clock className="w-6 h-6" />
                        </div>
                        <div>
                            <span className="text-xs font-medium text-slate-500 dark:text-slate-400">Menunggu</span>
                            <p className="text-xl font-bold text-amber-600 dark:text-amber-400 mt-0.5">
                                {docSummary.uploaded_count?.toLocaleString('id-ID') || 0}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
