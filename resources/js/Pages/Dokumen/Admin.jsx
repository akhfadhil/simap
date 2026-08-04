import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import { FolderOpen, Filter, FileText, CheckCircle2, Clock, XCircle } from 'lucide-react';

export default function DokumenAdmin({ tpsList, kecamatans, desas, dokumenKecamatan, selectedKecamatanId, selectedDesaId }) {
    const [filterKec, setFilterKec] = useState(selectedKecamatanId || '');
    const [filterDesa, setFilterDesa] = useState(selectedDesaId || '');

    const handleFilter = (e) => {
        e.preventDefault();
        router.get('/dokumen/semua', {
            kecamatan_id: filterKec || undefined,
            desa_id: filterDesa || undefined,
        }, { preserveState: true });
    };

    const activeKec = kecamatans?.find(k => k.id === parseInt(filterKec));
    const filteredDesas = activeKec ? desas?.filter(d => d.kecamatan_id === parseInt(filterKec)) : desas;

    return (
        <AdminLayout title="Rekap Dokumen Pemilu">
            <Head title="Rekap Dokumen Pemilu" />

            <div className="space-y-6">
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md">
                            Arsip & PDF
                        </span>
                        <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                            Rekap Dokumen C-Hasil & D-Hasil
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                            Default: Desa Bangorejo, Kecamatan Bangorejo.
                        </p>
                    </div>
                </div>

                {/* Filter Form Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs">
                    <form onSubmit={handleFilter} className="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                        <div>
                            <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                                Kecamatan
                            </label>
                            <select
                                value={filterKec}
                                onChange={(e) => {
                                    setFilterKec(e.target.value);
                                    setFilterDesa('');
                                }}
                                className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]"
                            >
                                <option value="">Pilih Kecamatan</option>
                                {kecamatans?.map(kec => (
                                    <option key={kec.id} value={kec.id}>{kec.nama}</option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                                Desa / Kelurahan
                            </label>
                            <select
                                value={filterDesa}
                                onChange={(e) => setFilterDesa(e.target.value)}
                                disabled={!filterKec}
                                className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c] disabled:opacity-50"
                            >
                                <option value="">Pilih Desa</option>
                                {filteredDesas?.map(desa => (
                                    <option key={desa.id} value={desa.id}>{desa.nama}</option>
                                ))}
                            </select>
                        </div>

                        <button
                            type="submit"
                            className="bg-[#0f172a] dark:bg-[#1f2937] hover:bg-[#334151] text-white font-semibold py-2 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition cursor-pointer"
                        >
                            <Filter className="w-3.5 h-3.5" />
                            <span>Tampilkan Dokumen</span>
                        </button>
                    </form>
                </div>

                {/* TPS Documents List Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-sm overflow-hidden">
                    <div className="p-4 border-b border-[#e2e8f0] dark:border-[#1e293b]">
                        <h3 className="font-bold font-heading text-sm text-[#0f172a] dark:text-[#f8fafc]">Daftar Dokumen TPS</h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-[#f8fafc] dark:bg-[#0b0f19] border-b border-[#e2e8f0] dark:border-[#1e293b] uppercase tracking-wider text-[#475569] dark:text-[#94a3b8] font-bold">
                                <tr>
                                    <th className="px-5 py-3.5">TPS</th>
                                    <th className="px-5 py-3.5">Desa & Kecamatan</th>
                                    <th className="px-5 py-3.5">Dokumen Ter-upload</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#f1f5f9] dark:divide-[#1e293b]">
                                {tpsList?.length > 0 ? (
                                    tpsList.map((tps) => (
                                        <tr key={tps.id} className="hover:bg-[#f8fafc] dark:hover:bg-[#1f2937]/50 transition">
                                            <td className="px-5 py-3.5 font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                                {tps.nama}
                                            </td>
                                            <td className="px-5 py-3.5 text-[#475569] dark:text-[#94a3b8]">
                                                {tps.desa?.nama} — Kec. {tps.desa?.kecamatan?.nama}
                                            </td>
                                            <td className="px-5 py-3.5">
                                                <div className="flex flex-wrap gap-1.5">
                                                    {tps.dokumens?.length > 0 ? (
                                                        tps.dokumens.map(doc => (
                                                            <a
                                                                key={doc.id}
                                                                href={`/storage/${doc.file_path}`}
                                                                target="_blank"
                                                                rel="noreferrer"
                                                                className="inline-flex items-center gap-1 bg-red-50 text-[#bb152c] px-2 py-1 rounded text-[10px] font-bold border border-red-100 hover:underline"
                                                            >
                                                                <FileText className="w-3 h-3" />
                                                                <span>{doc.jenis?.toUpperCase()}</span>
                                                            </a>
                                                        ))
                                                    ) : (
                                                        <span className="text-[10px] text-[#94a3b8]">Belum ada PDF</span>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="3" className="px-5 py-8 text-center text-xs text-[#94a3b8]">
                                            Pilih kecamatan dan desa untuk melihat daftar dokumen PDF TPS.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
