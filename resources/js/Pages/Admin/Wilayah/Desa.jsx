import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { Building2, Plus, Edit2, Trash2, X, Filter } from 'lucide-react';

export default function DesaPage({ desas, kecamatans, filters }) {
    const [selectedKec, setSelectedKec] = useState(filters?.kecamatan_id || '');
    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [editingDesa, setEditingDesa] = useState(null);

    const addForm = useForm({ nama: '', kecamatan_id: selectedKec || (kecamatans?.[0]?.id || '') });
    const editForm = useForm({ nama: '', kecamatan_id: '' });

    const handleFilter = (e) => {
        e.preventDefault();
        router.get('/admin/desa', { kecamatan_id: selectedKec || undefined }, { preserveState: true });
    };

    const handleAdd = (e) => {
        e.preventDefault();
        addForm.post('/admin/desa', {
            onSuccess: () => {
                setIsAddModalOpen(false);
                addForm.reset();
            }
        });
    };

    const handleUpdate = (e) => {
        e.preventDefault();
        editForm.put(`/admin/desa/${editingDesa.id}`, {
            onSuccess: () => setEditingDesa(null)
        });
    };

    const handleDelete = (desa) => {
        if (confirm(`Yakin hapus Desa ${desa.nama}?`)) {
            router.delete(`/admin/desa/${desa.id}`);
        }
    };

    return (
        <AdminLayout title="Kelola Desa">
            <Head title="Kelola Desa" />

            <div className="space-y-6">
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md">
                            Master Wilayah
                        </span>
                        <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                            Kelola Desa / Kelurahan
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                            Default: Kecamatan Bangorejo.
                        </p>
                    </div>

                    <button
                        onClick={() => { addForm.setData('kecamatan_id', selectedKec || (kecamatans?.[0]?.id || '')); setIsAddModalOpen(true); }}
                        className="inline-flex items-center gap-2 bg-[#bb152c] hover:bg-[#991124] text-white font-semibold px-4 py-2.5 rounded-xl text-xs shadow-md shadow-[#bb152c]/20 transition cursor-pointer"
                    >
                        <Plus className="w-4 h-4" />
                        <span>Tambah Desa Baru</span>
                    </button>
                </div>

                {/* Filter Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs">
                    <form onSubmit={handleFilter} className="flex flex-col sm:flex-row items-end gap-3">
                        <div className="flex-1">
                            <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                                Pilih Kecamatan
                            </label>
                            <select
                                value={selectedKec}
                                onChange={(e) => setSelectedKec(e.target.value)}
                                className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]"
                            >
                                <option value="">Pilih Kecamatan</option>
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
                            <span>Tampilkan Desa</span>
                        </button>
                    </form>
                </div>

                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-sm overflow-hidden">
                    <table className="w-full text-left text-xs">
                        <thead className="bg-[#f8fafc] dark:bg-[#0b0f19] border-b border-[#e2e8f0] dark:border-[#1e293b] uppercase tracking-wider text-[#475569] dark:text-[#94a3b8] font-bold">
                            <tr>
                                <th className="px-5 py-3.5">Nama Desa</th>
                                <th className="px-5 py-3.5">Kecamatan</th>
                                <th className="px-5 py-3.5">Jumlah TPS</th>
                                <th className="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-[#f1f5f9] dark:divide-[#1e293b]">
                            {desas?.length > 0 ? (
                                desas.map((desa) => (
                                    <tr key={desa.id} className="hover:bg-[#f8fafc] dark:hover:bg-[#1f2937]/50 transition">
                                        <td className="px-5 py-3.5 font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                            {desa.nama}
                                        </td>
                                        <td className="px-5 py-3.5 text-[#475569] dark:text-[#94a3b8]">
                                            {desa.kecamatan?.nama}
                                        </td>
                                        <td className="px-5 py-3.5 text-[#475569] dark:text-[#94a3b8]">
                                            {desa.tps_count || 0} TPS
                                        </td>
                                        <td className="px-5 py-3.5 text-right space-x-2">
                                            <button
                                                onClick={() => { setEditingDesa(desa); editForm.setData({ nama: desa.nama, kecamatan_id: desa.kecamatan_id }); }}
                                                className="p-1.5 text-slate-500 hover:text-indigo-600 transition cursor-pointer"
                                            >
                                                <Edit2 className="w-4 h-4" />
                                            </button>
                                            <button
                                                onClick={() => handleDelete(desa)}
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
                                        Pilih kecamatan di atas untuk melihat desa.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Modal Add */}
            {isAddModalOpen && (
                <div className="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-md p-6 shadow-xl">
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc]">Tambah Desa Baru</h3>
                            <button onClick={() => setIsAddModalOpen(false)} className="text-slate-400 hover:text-slate-600">
                                <X className="w-5 h-5" />
                            </button>
                        </div>
                        <form onSubmit={handleAdd} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold mb-1">Kecamatan</label>
                                <select
                                    value={addForm.data.kecamatan_id}
                                    onChange={(e) => addForm.setData('kecamatan_id', e.target.value)}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl px-3 py-2 text-xs outline-none"
                                >
                                    {kecamatans?.map(k => (
                                        <option key={k.id} value={k.id}>{k.nama}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="block text-xs font-semibold mb-1">Nama Desa</label>
                                <input
                                    type="text"
                                    value={addForm.data.nama}
                                    onChange={(e) => addForm.setData('nama', e.target.value)}
                                    placeholder="Contoh: Bangorejo"
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl px-3 py-2 text-xs outline-none"
                                    required
                                />
                            </div>
                            <div className="pt-2 flex justify-end gap-2">
                                <button type="button" onClick={() => setIsAddModalOpen(false)} className="px-4 py-2 rounded-xl text-xs font-semibold bg-[#f1f5f9] text-slate-700">
                                    Batal
                                </button>
                                <button type="submit" disabled={addForm.processing} className="px-4 py-2 rounded-xl text-xs font-semibold bg-[#bb152c] text-white">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
