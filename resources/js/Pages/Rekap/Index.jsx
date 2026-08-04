import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import { BarChart2, ArrowRight, Filter, Vote, CheckCircle2, AlertCircle } from 'lucide-react';

export default function RekapIndex({ kecamatans, rekaps, flaggedJenis }) {
    const [selectedKec, setSelectedKec] = useState('');

    const handleFilter = (e) => {
        e.preventDefault();
        router.get('/admin/rekap', { kecamatan_id: selectedKec || undefined }, { preserveState: true });
    };

    const jenisOptions = [
        { key: 'ppwp', label: 'PPWP', desc: 'Pemilihan Presiden dan Wakil Presiden' },
        { key: 'gubernur', label: 'Pilgub', desc: 'Pemilihan Gubernur dan Wakil Gubernur' },
        { key: 'bupati', label: 'Pilbup', desc: 'Pemilihan Bupati dan Wakil Bupati' },
        { key: 'dpd', label: 'DPD', desc: 'Pemilihan Dewan Perwakilan Daerah' },
        { key: 'dpr_ri', label: 'DPR RI', desc: 'Pemilihan Dewan Perwakilan Rakyat RI' },
        { key: 'dprd_prov', label: 'DPRD Prov', desc: 'Pemilihan DPRD Provinsi Jawa Timur' },
        { key: 'dprd_kab', label: 'DPRD Kab', desc: 'Pemilihan DPRD Kabupaten Banyuwangi' },
    ];

    return (
        <AdminLayout title="Rekapitulasi Data Suara">
            <Head title="Rekapitulasi Data Suara" />

            <div className="space-y-6">
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md">
                            Rekapitulasi Suara
                        </span>
                        <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                            Rekapitulasi Tingkat Kabupaten
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                            Pilih jenis pemilihan untuk melihat perolehan suara per wilayah kecamatan/desa/TPS.
                        </p>
                    </div>
                </div>

                {/* Filter Form Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs">
                    <form onSubmit={handleFilter} className="flex flex-col sm:flex-row items-end gap-3">
                        <div className="flex-1">
                            <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                                Filter Kecamatan
                            </label>
                            <select
                                value={selectedKec}
                                onChange={(e) => setSelectedKec(e.target.value)}
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

                {/* Jenis Pemilihan Cards Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {jenisOptions.map((opt) => {
                        const count = rekaps?.[opt.key]?.length || 0;
                        const isFlagged = flaggedJenis && (opt.key in flaggedJenis);

                        return (
                            <a
                                key={opt.key}
                                href={`/admin/rekap/${opt.key}`}
                                className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] hover:border-[#bb152c] dark:hover:border-red-500 rounded-2xl p-5 shadow-xs transition group flex flex-col justify-between"
                            >
                                <div>
                                    <div className="flex items-center justify-between gap-2 mb-2">
                                        <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc]">
                                            {opt.label}
                                        </h3>
                                        {isFlagged && (
                                            <span className="flex items-center gap-1 text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded">
                                                <AlertCircle className="w-3 h-3" /> Flagged
                                            </span>
                                        )}
                                    </div>
                                    <p className="text-xs text-[#475569] dark:text-[#94a3b8] mb-4">
                                        {opt.desc}
                                    </p>
                                </div>

                                <div className="flex items-center justify-between pt-3 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                                    <span className="text-xs text-[#94a3b8] font-medium">
                                        {count} TPS Terisi
                                    </span>
                                    <span className="text-xs font-bold text-[#bb152c] dark:text-red-400 group-hover:translate-x-1 transition transform flex items-center gap-1">
                                        Buka Rekap <ArrowRight className="w-3.5 h-3.5" />
                                    </span>
                                </div>
                            </a>
                        );
                    })}
                </div>
            </div>
        </AdminLayout>
    );
}
