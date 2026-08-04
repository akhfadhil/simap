import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { MapPin, Plus, Edit2, Trash2, X } from 'lucide-react';

export default function KecamatanPage({ kecamatans }) {
    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [editingKec, setEditingKec] = useState(null);

    const addForm = useForm({ nama: '' });
    const editForm = useForm({ nama: '' });

    const handleAdd = (e) => {
        e.preventDefault();
        addForm.post('/admin/kecamatan', {
            onSuccess: () => {
                setIsAddModalOpen(false);
                addForm.reset();
            }
        });
    };

    const handleUpdate = (e) => {
        e.preventDefault();
        editForm.put(`/admin/kecamatan/${editingKec.id}`, {
            onSuccess: () => setEditingKec(null)
        });
    };

    const handleDelete = (kec) => {
        if (confirm(`Yakin hapus Kecamatan ${kec.nama}?`)) {
            router.delete(`/admin/kecamatan/${kec.id}`);
        }
    };

    return (
        <AdminLayout title="Kelola Kecamatan">
            <Head title="Kelola Kecamatan" />

            <div className="space-y-6">
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md">
                            Master Wilayah
                        </span>
                        <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                            Kelola Kecamatan
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                            Daftar seluruh kecamatan di Kabupaten Banyuwangi.
                        </p>
                    </div>

                    <button
                        onClick={() => { addForm.reset(); setIsAddModalOpen(true); }}
                        className="inline-flex items-center gap-2 bg-[#bb152c] hover:bg-[#991124] text-white font-semibold px-4 py-2.5 rounded-xl text-xs shadow-md shadow-[#bb152c]/20 transition cursor-pointer"
                    >
                        <Plus className="w-4 h-4" />
                        <span>Tambah Kecamatan</span>
                    </button>
                </div>

                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-sm overflow-hidden">
                    <table className="w-full text-left text-xs">
                        <thead className="bg-[#f8fafc] dark:bg-[#0b0f19] border-b border-[#e2e8f0] dark:border-[#1e293b] uppercase tracking-wider text-[#475569] dark:text-[#94a3b8] font-bold">
                            <tr>
                                <th className="px-5 py-3.5">Nama Kecamatan</th>
                                <th className="px-5 py-3.5">Jumlah Desa</th>
                                <th className="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-[#f1f5f9] dark:divide-[#1e293b]">
                            {kecamatans?.length > 0 ? (
                                kecamatans.map((kec) => (
                                    <tr key={kec.id} className="hover:bg-[#f8fafc] dark:hover:bg-[#1f2937]/50 transition">
                                        <td className="px-5 py-3.5 font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                            {kec.nama}
                                        </td>
                                        <td className="px-5 py-3.5 text-[#475569] dark:text-[#94a3b8]">
                                            {kec.desas_count || 0} Desa
                                        </td>
                                        <td className="px-5 py-3.5 text-right space-x-2">
                                            <button
                                                onClick={() => { setEditingKec(kec); editForm.setData('nama', kec.nama); }}
                                                className="p-1.5 text-slate-500 hover:text-indigo-600 transition cursor-pointer"
                                            >
                                                <Edit2 className="w-4 h-4" />
                                            </button>
                                            <button
                                                onClick={() => handleDelete(kec)}
                                                className="p-1.5 text-slate-500 hover:text-red-600 transition cursor-pointer"
                                            >
                                                <Trash2 className="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="3" className="px-5 py-8 text-center text-xs text-[#94a3b8]">
                                        Belum ada kecamatan.
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
                            <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc]">Tambah Kecamatan</h3>
                            <button onClick={() => setIsAddModalOpen(false)} className="text-slate-400 hover:text-slate-600">
                                <X className="w-5 h-5" />
                            </button>
                        </div>
                        <form onSubmit={handleAdd} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold mb-1">Nama Kecamatan</label>
                                <input
                                    type="text"
                                    value={addForm.data.nama}
                                    onChange={(e) => addForm.setData('nama', e.target.value)}
                                    placeholder="Contoh: Bangorejo"
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl px-3 py-2 text-xs outline-none"
                                    required
                                    autoFocus
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
