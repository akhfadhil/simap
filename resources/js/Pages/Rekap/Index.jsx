import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import { BarChart2, ArrowRight, Filter, Vote, CheckCircle2 } from 'lucide-react';

export default function RekapIndex({
    kecamatans = [],
    totalTps = 0,
    rekapCounts = {},
    aktifJenis = [],
    selectedKecamatanId = null,
    baseRoute = '/admin/rekap',
    pageTitle = 'Rekapitulasi Tingkat Kabupaten',
    pageSubtitle = 'Pilih jenis pemilihan yang aktif di Setup Data Pemilu untuk melihat perolehan suara.',
    badgeLabel = 'Rekapitulasi Suara',
    isPpk = false,
    isPps = false,
}) {
    const [selectedKec, setSelectedKec] = useState(selectedKecamatanId ? String(selectedKecamatanId) : '');

    const handleFilterChange = (newKecId) => {
        setSelectedKec(newKecId);
        router.get(baseRoute, { kecamatan_id: newKecId || undefined }, { preserveState: true });
    };

    const handleFilterSubmit = (e) => {
        e.preventDefault();
        router.get(baseRoute, { kecamatan_id: selectedKec || undefined }, { preserveState: true });
    };

    const allJenisOptions = [
        { key: 'ppwp', label: 'PPWP', desc: 'Pemilihan Presiden dan Wakil Presiden' },
        { key: 'gubernur', label: 'Pilgub', desc: 'Pemilihan Gubernur dan Wakil Gubernur' },
        { key: 'bupati', label: 'Pilbup', desc: 'Pemilihan Bupati dan Wakil Bupati' },
        { key: 'dpd', label: 'DPD', desc: 'Pemilihan Dewan Perwakilan Daerah' },
        { key: 'dpr_ri', label: 'DPR RI', desc: 'Pemilihan Dewan Perwakilan Rakyat RI' },
        { key: 'dprd_prov', label: 'DPRD Prov', desc: 'Pemilihan DPRD Provinsi Jawa Timur' },
        { key: 'dprd_kab', label: 'DPRD Kab', desc: 'Pemilihan DPRD Kabupaten Banyuwangi' },
    ];

    const activeList = Array.isArray(aktifJenis) && aktifJenis.length > 0
        ? aktifJenis
        : allJenisOptions.map(o => o.key);

    const filteredJenisOptions = allJenisOptions.filter(o => activeList.includes(o.key));

    return (
        <AdminLayout title="Rekapitulasi Data Suara">
            <Head title="Rekapitulasi Data Suara" />

            <div className="space-y-6">
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md">
                            {badgeLabel}
                        </span>
                        <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                            {pageTitle}
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                            {pageSubtitle}
                        </p>
                    </div>
                </div>

                {/* Filter Form Card */}
                {isPps ? (
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div className="flex items-center gap-2.5 text-xs text-[#475569] dark:text-[#94a3b8]">
                            <div className="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 flex items-center justify-center shrink-0">
                                <Vote className="w-4 h-4" />
                            </div>
                            <div>
                                <span className="text-[10px] uppercase tracking-wider text-[#94a3b8] font-bold block">Wilayah Kerja PPS</span>
                                <span className="font-bold text-[#0f172a] dark:text-[#f8fafc] text-sm">
                                    Desa {kecamatans[0]?.desas?.[0]?.nama || '-'} (Kec. {kecamatans[0]?.nama || '-'})
                                </span>
                            </div>
                        </div>
                        <div className="flex items-center gap-2">
                            <span className="text-xs font-semibold px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] border border-slate-200 dark:border-slate-700">
                                {totalTps} Total TPS Terdaftar
                            </span>
                        </div>
                    </div>
                ) : isPpk ? (
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div className="flex items-center gap-2.5 text-xs text-[#475569] dark:text-[#94a3b8]">
                            <div className="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 flex items-center justify-center shrink-0">
                                <Vote className="w-4 h-4" />
                            </div>
                            <div>
                                <span className="text-[10px] uppercase tracking-wider text-[#94a3b8] font-bold block">Wilayah Kerja PPK</span>
                                <span className="font-bold text-[#0f172a] dark:text-[#f8fafc] text-sm">Kecamatan {kecamatans[0]?.nama || '-'}</span>
                            </div>
                        </div>
                        <div className="flex items-center gap-2">
                            <span className="text-xs font-semibold px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] border border-slate-200 dark:border-slate-700">
                                {totalTps} Total TPS Terdaftar
                            </span>
                        </div>
                    </div>
                ) : (
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs">
                        <form onSubmit={handleFilterSubmit} className="flex flex-col sm:flex-row items-end gap-3">
                            <div className="flex-1">
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                                    Filter Kecamatan
                                </label>
                                <select
                                    value={selectedKec}
                                    onChange={(e) => handleFilterChange(e.target.value)}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]"
                                >
                                    <option value="">Semua Kecamatan (Kabupaten)</option>
                                    {kecamatans?.map(kec => (
                                        <option key={kec.id} value={kec.id}>{kec.nama}</option>
                                    ))}
                                </select>
                            </div>
                            <button
                                type="submit"
                                className="bg-[#0f172a] dark:bg-[#1f2937] hover:bg-[#334151] text-white font-semibold py-2 px-5 rounded-xl text-xs flex items-center gap-2 transition cursor-pointer"
                            >
                                <Filter className="w-3.5 h-3.5" />
                                <span>Filter Rekap</span>
                            </button>
                        </form>
                    </div>
                )}

                {/* Jenis Pemilihan Cards Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {filteredJenisOptions.map((opt) => {
                        const count = rekapCounts?.[opt.key] || 0;
                        const percentage = totalTps > 0 ? Math.min(100, Math.round((count / totalTps) * 100)) : 0;
                        const isCompleted = totalTps > 0 && count >= totalTps;

                        const targetUrl = isPpk
                            ? `${baseRoute}/${opt.key}`
                            : selectedKec
                            ? `${baseRoute}/${opt.key}?kecamatan_id=${selectedKec}`
                            : `${baseRoute}/${opt.key}`;

                        return (
                            <Link
                                key={opt.key}
                                href={targetUrl}
                                className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] hover:border-[#bb152c] dark:hover:border-red-500 rounded-2xl p-5 shadow-xs hover:shadow-md transition group flex flex-col justify-between"
                            >
                                <div>
                                    <div className="flex items-center justify-between gap-2 mb-2">
                                        <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc] group-hover:text-[#bb152c] dark:group-hover:text-red-400 transition-colors">
                                            {opt.label}
                                        </h3>
                                        {isCompleted ? (
                                            <span className="flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800/40">
                                                <CheckCircle2 className="w-3 h-3" /> Selesai
                                            </span>
                                        ) : count > 0 ? (
                                            <span className="text-[10px] font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2.5 py-0.5 rounded-full border border-amber-200 dark:border-amber-800/40">
                                                {percentage}% Terisi
                                            </span>
                                        ) : (
                                            <span className="text-[10px] font-medium text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800/60 px-2.5 py-0.5 rounded-full">
                                                0%
                                            </span>
                                        )}
                                    </div>
                                    <p className="text-xs text-[#475569] dark:text-[#94a3b8] mb-4 line-clamp-2">
                                        {opt.desc}
                                    </p>

                                    {/* Progress Bar TPS */}
                                    <div className="space-y-1.5 my-3 bg-slate-50 dark:bg-slate-800/40 p-3 rounded-xl border border-slate-100 dark:border-slate-800/60">
                                        <div className="flex items-center justify-between text-xs">
                                            <span className="font-semibold text-slate-700 dark:text-slate-300">
                                                {count} <span className="font-normal text-slate-400">/ {totalTps} TPS</span>
                                            </span>
                                            <span className="font-mono font-bold text-xs text-[#bb152c] dark:text-red-400">
                                                {percentage}%
                                            </span>
                                        </div>
                                        <div className="w-full h-2 bg-slate-200/70 dark:bg-slate-700/60 rounded-full overflow-hidden">
                                            <div
                                                className={`h-full rounded-full transition-all duration-500 ${
                                                    isCompleted
                                                        ? 'bg-emerald-500'
                                                        : count > 0
                                                        ? 'bg-[#bb152c]'
                                                        : 'bg-transparent'
                                                }`}
                                                style={{ width: `${percentage}%` }}
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div className="flex items-center justify-between pt-3 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                                    <span className="text-xs text-[#94a3b8] font-medium">
                                        {isPpk ? `Kec. ${kecamatans[0]?.nama || ''}` : selectedKec ? 'Filter Kecamatan Aktif' : 'Semua Wilayah'}
                                    </span>
                                    <span className="text-xs font-bold text-[#bb152c] dark:text-red-400 group-hover:translate-x-1 transition transform flex items-center gap-1">
                                        Buka Rekap <ArrowRight className="w-3.5 h-3.5" />
                                    </span>
                                </div>
                            </Link>
                        );
                    })}
                </div>
            </div>
        </AdminLayout>
    );
}
