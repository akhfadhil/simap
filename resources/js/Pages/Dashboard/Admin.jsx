import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import {
    Vote,
    CheckCircle2,
    Clock,
    XCircle,
    ArrowRight,
    Trophy,
    TrendingUp
} from 'lucide-react';

export default function AdminDashboard({ electionSummary }) {
    const docSummary = electionSummary?.documentsSummary || {};
    const summaries = electionSummary?.summaries || [];

    return (
        <AdminLayout title="Beranda Administrator">
            <Head title="Beranda Admin" />

            <div className="space-y-6">
                {/* Top Banner Header */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_10px_15px_-3px_rgba(0,0,0,0.3)]">
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md">
                                Kabupaten Banyuwangi
                            </span>
                            <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                                Ringkasan Perolehan Suara Pemilu
                            </h1>
                            <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                                Pantau statistik rekapitulasi data dan kelengkapan dokumen arsip secara real-time.
                            </p>
                        </div>

                        <div className="flex items-center gap-2">
                            <Link
                                href="/admin/rekap/chart"
                                className="inline-flex items-center gap-2 bg-[#bb152c] hover:bg-[#991124] text-white font-semibold px-4 py-2.5 rounded-xl text-xs shadow-md shadow-[#bb152c]/20 transition duration-150"
                            >
                                <TrendingUp className="w-4 h-4" />
                                <span>Lihat Grafik & Peta</span>
                            </Link>
                        </div>
                    </div>
                </div>

                {/* KPI Cards Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {/* Card 1: Total TPS */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_10px_15px_-3px_rgba(0,0,0,0.3)] flex items-center gap-4">
                        <div className="w-12 h-12 rounded-xl bg-[#f1f5f9] dark:bg-[#1f2937] flex items-center justify-center text-[#0f172a] dark:text-[#f8fafc] shrink-0">
                            <Vote className="w-6 h-6" />
                        </div>
                        <div>
                            <span className="text-xs font-medium text-[#475569] dark:text-[#94a3b8]">Total TPS</span>
                            <p className="text-xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] mt-0.5">
                                {docSummary.total_tps?.toLocaleString('id-ID') || 0}
                            </p>
                        </div>
                    </div>

                    {/* Card 2: Terverifikasi */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_10px_15px_-3px_rgba(0,0,0,0.3)] flex items-center gap-4">
                        <div className="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-[#10b981] shrink-0 border border-emerald-100 dark:border-emerald-900/40">
                            <CheckCircle2 className="w-6 h-6" />
                        </div>
                        <div>
                            <span className="text-xs font-medium text-[#475569] dark:text-[#94a3b8]">Dokumen Terverifikasi</span>
                            <p className="text-xl font-bold font-heading text-[#10b981] mt-0.5">
                                {docSummary.verified_count?.toLocaleString('id-ID') || 0}
                            </p>
                        </div>
                    </div>

                    {/* Card 3: Menunggu Verifikasi */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_10px_15px_-3px_rgba(0,0,0,0.3)] flex items-center gap-4">
                        <div className="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center text-[#f59e0b] shrink-0 border border-amber-100 dark:border-amber-900/40">
                            <Clock className="w-6 h-6" />
                        </div>
                        <div>
                            <span className="text-xs font-medium text-[#475569] dark:text-[#94a3b8]">Menunggu Verifikasi</span>
                            <p className="text-xl font-bold font-heading text-[#f59e0b] mt-0.5">
                                {docSummary.uploaded_count?.toLocaleString('id-ID') || 0}
                            </p>
                        </div>
                    </div>

                    {/* Card 4: Ditolak */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_10px_15px_-3px_rgba(0,0,0,0.3)] flex items-center gap-4">
                        <div className="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center text-[#ef4444] shrink-0 border border-red-100 dark:border-red-900/40">
                            <XCircle className="w-6 h-6" />
                        </div>
                        <div>
                            <span className="text-xs font-medium text-[#475569] dark:text-[#94a3b8]">Dokumen Ditolak</span>
                            <p className="text-xl font-bold font-heading text-[#ef4444] mt-0.5">
                                {docSummary.rejected_count?.toLocaleString('id-ID') || 0}
                            </p>
                        </div>
                    </div>
                </div>

                {/* Election Summaries per Category Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {summaries.map((item, idx) => (
                        <div
                            key={idx}
                            className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_10px_15px_-3px_rgba(0,0,0,0.3)] flex flex-col justify-between"
                        >
                            <div>
                                <div className="flex items-center justify-between gap-2 pb-3 mb-4 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                                    <h3 className="font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] text-base">
                                        {item.label}
                                    </h3>
                                    <span className="text-[10px] font-semibold uppercase px-2 py-0.5 rounded bg-[#f1f5f9] dark:bg-[#1f2937] text-[#475569] dark:text-[#94a3b8]">
                                        {item.key}
                                    </span>
                                </div>

                                {item.winner ? (
                                    <div className="bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl p-4 mb-4">
                                        <div className="flex items-center gap-2 text-amber-500 text-xs font-bold uppercase tracking-wider mb-1">
                                            <Trophy className="w-4 h-4" />
                                            <span>Unggul Sementara</span>
                                        </div>
                                        <p className="font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] text-sm">
                                            {item.winner.nama}
                                        </p>
                                        <div className="flex items-center justify-between text-xs text-[#475569] dark:text-[#94a3b8] mt-2 pt-2 border-t border-[#e2e8f0]/60 dark:border-[#1e293b]">
                                            <span>Total Suara:</span>
                                            <span className="font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                                {item.winner.suara?.toLocaleString('id-ID')} ({item.winner.persen}%)
                                            </span>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl p-4 mb-4 text-center text-xs text-[#94a3b8]">
                                        Belum ada data masuk.
                                    </div>
                                )}
                            </div>

                            <Link
                                href={`/admin/rekap/${item.key}`}
                                className="inline-flex items-center justify-between w-full text-xs font-semibold text-[#bb152c] dark:text-red-400 hover:underline pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b] transition"
                            >
                                <span>Detail Rekapitulasi</span>
                                <ArrowRight className="w-4 h-4" />
                            </Link>
                        </div>
                    ))}
                </div>
            </div>
        </AdminLayout>
    );
}
