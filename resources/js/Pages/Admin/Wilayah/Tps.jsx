import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { Pin, Plus, Edit2, Trash2, X, Filter } from 'lucide-react';

export default function TpsPage({ kecamatans, filteredTps, selectedKecamatanId, selectedDesaId }) {
    const [filterKec, setFilterKec] = useState(selectedKecamatanId || '');
    const [filterDesa, setFilterDesa] = useState(selectedDesaId || '');
    const [editingTps, setEditingTps] = useState(null);

    const editForm = useForm({ nama: '' });

    const handleFilter = (e) => {
        e.preventDefault();
        router.get('/admin/tps', {
            kecamatan_id: filterKec || undefined,
            desa_id: filterDesa || undefined,
        }, { preserveState: true });
    };

    const handleUpdate = (e) => {
        e.preventDefault();
        editForm.put(`/admin/tps/${editingTps.id}`, {
            onSuccess: () => setEditingTps(null)
        });
    };

    const handleDelete = (tps) => {
        if (confirm(`Yakin hapus TPS ${tps.nama}?`)) {
            router.delete(`/admin/tps/${tps.id}`);
        }
    };

    const activeKec = kecamatans?.find(k => k.id === parseInt(filterKec));
    const desas = activeKec?.desas || [];

    return (
        <AdminLayout title="Kelola TPS">
            <Head title="Kelola TPS" />

            <div className="space-y-6">
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md">
                            Master Wilayah
                        </span>
                        <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                            Kelola Tempat Pemungutan Suara (TPS)
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
                                {desas.map(desa => (
                                    <option key={desa.id} value={desa.id}>{desa.nama}</option>
                                ))}
                            </select>
                        </div>

                        <button
                            type="submit"
                            className="bg-[#0f172a] dark:bg-[#1f2937] hover:bg-[#334151] text-white font-semibold py-2 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition cursor-pointer"
                        >
                            <Filter className="w-3.5 h-3.5" />
                            <span>Tampilkan TPS</span>
                        </button>
                    </form>
                </div>

                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-sm overflow-hidden">
                    <table className="w-full text-left text-xs">
                        <thead className="bg-[#f8fafc] dark:bg-[#0b0f19] border-b border-[#e2e8f0] dark:border-[#1e293b] uppercase tracking-wider text-[#475569] dark:text-[#94a3b8] font-bold">
                            <tr>
                                <th className="px-5 py-3.5">Nama TPS</th>
                                <th className="px-5 py-3.5">Desa</th>
                                <th className="px-5 py-3.5">Kecamatan</th>
                                <th className="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-[#f1f5f9] dark:divide-[#1e293b]">
                            {filteredTps?.length > 0 ? (
                                filteredTps.map((tps) => (
                                    <tr key={tps.id} className="hover:bg-[#f8fafc] dark:hover:bg-[#1f2937]/50 transition">
                                        <td className="px-5 py-3.5 font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                            {tps.nama}
                                        </td>
                                        <td className="px-5 py-3.5 text-[#475569] dark:text-[#94a3b8]">
                                            {tps.desa?.nama}
                                        </td>
                                        <td className="px-5 py-3.5 text-[#475569] dark:text-[#94a3b8]">
                                            {tps.desa?.kecamatan?.nama}
                                        </td>
                                        <td className="px-5 py-3.5 text-right space-x-2">
                                            <button
                                                onClick={() => { setEditingTps(tps); editForm.setData('nama', tps.nama); }}
                                                className="p-1.5 text-slate-500 hover:text-indigo-600 transition cursor-pointer"
                                            >
                                                <Edit2 className="w-4 h-4" />
                                            </button>
                                            <button
                                                onClick={() => handleDelete(tps)}
                                                className="p-1.5 text-slate-500 hover:text-red-600 transition cursor-pointer"
                                            >
                                                <Trash2 className="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="4" className="px-5 py-8 text-center text-xs text-[#94a3b8]">
                                        Pilih kecamatan dan desa di atas untuk melihat TPS.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
