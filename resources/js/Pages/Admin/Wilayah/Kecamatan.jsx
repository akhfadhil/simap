import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { MapPin, Plus, Edit2, Trash2, X, Eye, Search, Building2 } from 'lucide-react';

export default function KecamatanPage({ kecamatans }) {
    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [editingKec, setEditingKec] = useState(null);
    const [deletingKec, setDeletingKec] = useState(null);
    const [isDeleting, setIsDeleting] = useState(false);
    const [search, setSearch] = useState('');

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
        if (!editingKec) return;
        editForm.put(`/admin/kecamatan/${editingKec.id}`, {
            onSuccess: () => {
                setEditingKec(null);
                editForm.reset();
            }
        });
    };

    const handleConfirmDelete = () => {
        if (!deletingKec) return;
        setIsDeleting(true);
        router.delete(`/admin/kecamatan/${deletingKec.id}`, {
            onSuccess: () => {
                setDeletingKec(null);
                setIsDeleting(false);
            },
            onError: () => {
                setIsDeleting(false);
            }
        });
    };

    const filteredKecamatans = (kecamatans || []).filter((kec) =>
        kec.nama?.toLowerCase().includes(search.toLowerCase())
    );

    return (
        <AdminLayout title="Kelola Kecamatan">
            <Head title="Kelola Kecamatan" />

            <div className="space-y-5 sm:space-y-6">
                {/* Header Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 sm:p-6 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div className="space-y-1">
                        <div className="flex items-center gap-2">
                            <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-0.5 rounded-md border border-red-100 dark:border-red-900/30">
                                Master Wilayah
                            </span>
                            <span className="text-[11px] text-[#475569] dark:text-[#94a3b8]">
                                • {kecamatans?.length || 0} Total Kecamatan
                            </span>
                        </div>
                        <h1 className="text-xl sm:text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight">
                            Kelola Kecamatan
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8]">
                            Daftar seluruh kecamatan dan alokasi wilayah di Kabupaten Banyuwangi.
                        </p>
                    </div>

                    {/* Search & Action Controls */}
                    <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full lg:w-auto">
                        <div className="relative flex-1 sm:w-64 md:w-72">
                            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Cari nama kecamatan..."
                                className="w-full h-10 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl pl-9 pr-8 py-2 text-xs outline-none text-[#0f172a] dark:text-[#f8fafc] placeholder:text-slate-400 focus:border-[#bb152c] focus:ring-2 focus:ring-[#bb152c]/15 transition"
                            />
                            {search && (
                                <button
                                    onClick={() => setSearch('')}
                                    className="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5 rounded-md transition"
                                >
                                    <X className="w-3.5 h-3.5" />
                                </button>
                            )}
                        </div>

                        <button
                            onClick={() => { addForm.reset(); addForm.clearErrors(); setIsAddModalOpen(true); }}
                            className="inline-flex items-center justify-center gap-2 bg-[#bb152c] hover:bg-[#991124] active:scale-[0.98] text-white font-semibold h-10 px-4 rounded-xl text-xs shadow-md shadow-[#bb152c]/20 transition cursor-pointer flex-shrink-0"
                        >
                            <Plus className="w-4 h-4" />
                            <span>Tambah Kecamatan</span>
                        </button>
                    </div>
                </div>

                {/* Table Container */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-sm overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs min-w-[500px]">
                            <thead className="bg-[#f8fafc] dark:bg-[#0b0f19] border-b border-[#e2e8f0] dark:border-[#1e293b] uppercase tracking-wider text-[#475569] dark:text-[#94a3b8] font-bold text-[11px]">
                                <tr>
                                    <th className="px-5 py-3.5">Nama Kecamatan</th>
                                    <th className="px-5 py-3.5">Jumlah Desa</th>
                                    <th className="px-5 py-3.5 text-right w-44">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#f1f5f9] dark:divide-[#1e293b]">
                                {filteredKecamatans.length > 0 ? (
                                    filteredKecamatans.map((kec) => (
                                        <tr key={kec.id} className="hover:bg-[#f8fafc] dark:hover:bg-[#1f2937]/50 transition group">
                                            <td className="px-5 py-3.5 font-semibold text-[#0f172a] dark:text-[#f8fafc]">
                                                <div className="flex items-center gap-2.5">
                                                    <div className="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 flex-shrink-0">
                                                        <MapPin className="w-3.5 h-3.5" />
                                                    </div>
                                                    <span className="text-sm font-medium">{kec.nama}</span>
                                                </div>
                                            </td>
                                            <td className="px-5 py-3.5">
                                                <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700/60">
                                                    <Building2 className="w-3 h-3 text-slate-400" />
                                                    <span>{kec.desas_count || 0} Desa</span>
                                                </span>
                                            </td>
                                            <td className="px-5 py-3.5 text-right">
                                                <div className="inline-flex items-center justify-end gap-1.5">
                                                    <a
                                                        href={`/admin/kecamatan/${kec.id}/view`}
                                                        title="Lihat Dashboard sebagai PPK"
                                                        className="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-900/40 hover:bg-amber-100 dark:hover:bg-amber-900/60 hover:border-amber-300 transition-all duration-150 cursor-pointer shadow-2xs group/btn"
                                                    >
                                                        <Eye className="w-4 h-4 transition-transform group-hover/btn:scale-110" />
                                                    </a>
                                                    <button
                                                        title="Edit Nama Kecamatan"
                                                        onClick={() => {
                                                            setEditingKec(kec);
                                                            editForm.clearErrors();
                                                            editForm.setData('nama', kec.nama);
                                                        }}
                                                        className="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-900/40 hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:border-blue-300 transition-all duration-150 cursor-pointer shadow-2xs group/btn"
                                                    >
                                                        <Edit2 className="w-4 h-4 transition-transform group-hover/btn:scale-110" />
                                                    </button>
                                                    <button
                                                        title="Hapus Kecamatan"
                                                        onClick={() => setDeletingKec(kec)}
                                                        className="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/40 border border-red-200/80 dark:border-red-900/40 hover:bg-red-100 dark:hover:bg-red-900/60 hover:border-red-300 transition-all duration-150 cursor-pointer shadow-2xs group/btn"
                                                    >
                                                        <Trash2 className="w-4 h-4 transition-transform group-hover/btn:scale-110" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="3" className="px-5 py-12 text-center text-xs text-[#94a3b8]">
                                            <div className="flex flex-col items-center justify-center gap-2">
                                                <MapPin className="w-8 h-8 text-slate-300 dark:text-slate-600 stroke-[1.5]" />
                                                <p className="font-medium text-slate-600 dark:text-slate-400">
                                                    {search ? `Tidak ada kecamatan yang cocok dengan "${search}".` : 'Belum ada data kecamatan.'}
                                                </p>
                                                {search && (
                                                    <button
                                                        onClick={() => setSearch('')}
                                                        className="text-xs text-[#bb152c] dark:text-red-400 hover:underline font-semibold"
                                                    >
                                                        Reset pencarian
                                                    </button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* Modal Add */}
            {isAddModalOpen && (
                <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-md p-5 sm:p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <div>
                                <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc]">Tambah Kecamatan</h3>
                                <p className="text-xs text-[#475569] dark:text-[#94a3b8]">Masukkan nama kecamatan baru.</p>
                            </div>
                            <button
                                onClick={() => { setIsAddModalOpen(false); addForm.clearErrors(); }}
                                className="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>
                        <form onSubmit={handleAdd} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold mb-1.5 text-[#0f172a] dark:text-[#f8fafc]">
                                    Nama Kecamatan <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={addForm.data.nama}
                                    onChange={(e) => addForm.setData('nama', e.target.value)}
                                    placeholder="Contoh: Bangorejo"
                                    className="w-full h-10 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl px-3.5 text-xs outline-none text-[#0f172a] dark:text-[#f8fafc] focus:border-[#bb152c] focus:ring-2 focus:ring-[#bb152c]/15 transition"
                                    required
                                    autoFocus
                                />
                                {addForm.errors.nama && (
                                    <p className="text-red-500 text-[11px] mt-1.5 font-medium">{addForm.errors.nama}</p>
                                )}
                            </div>
                            <div className="pt-2 flex items-center justify-end gap-2.5">
                                <button
                                    type="button"
                                    onClick={() => { setIsAddModalOpen(false); addForm.clearErrors(); }}
                                    className="h-10 px-4 rounded-xl text-xs font-semibold bg-[#f1f5f9] dark:bg-[#1e293b] text-slate-700 dark:text-slate-300 hover:bg-[#e2e8f0] dark:hover:bg-[#27354f] transition cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={addForm.processing}
                                    className="h-10 px-5 rounded-xl text-xs font-semibold bg-[#bb152c] hover:bg-[#991124] text-white transition disabled:opacity-50 cursor-pointer shadow-md shadow-[#bb152c]/20"
                                >
                                    {addForm.processing ? 'Menyimpan...' : 'Simpan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal Edit */}
            {editingKec && (
                <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-md p-5 sm:p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <div>
                                <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc]">Edit Kecamatan</h3>
                                <p className="text-xs text-[#475569] dark:text-[#94a3b8]">Perbarui nama kecamatan ini.</p>
                            </div>
                            <button
                                onClick={() => { setEditingKec(null); editForm.clearErrors(); }}
                                className="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>
                        <form onSubmit={handleUpdate} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold mb-1.5 text-[#0f172a] dark:text-[#f8fafc]">
                                    Nama Kecamatan <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={editForm.data.nama}
                                    onChange={(e) => editForm.setData('nama', e.target.value)}
                                    placeholder="Contoh: Bangorejo"
                                    className="w-full h-10 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl px-3.5 text-xs outline-none text-[#0f172a] dark:text-[#f8fafc] focus:border-[#bb152c] focus:ring-2 focus:ring-[#bb152c]/15 transition"
                                    required
                                    autoFocus
                                />
                                {editForm.errors.nama && (
                                    <p className="text-red-500 text-[11px] mt-1.5 font-medium">{editForm.errors.nama}</p>
                                )}
                            </div>
                            <div className="pt-2 flex items-center justify-end gap-2.5">
                                <button
                                    type="button"
                                    onClick={() => { setEditingKec(null); editForm.clearErrors(); }}
                                    className="h-10 px-4 rounded-xl text-xs font-semibold bg-[#f1f5f9] dark:bg-[#1e293b] text-slate-700 dark:text-slate-300 hover:bg-[#e2e8f0] dark:hover:bg-[#27354f] transition cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={editForm.processing}
                                    className="h-10 px-5 rounded-xl text-xs font-semibold bg-[#bb152c] hover:bg-[#991124] text-white transition disabled:opacity-50 cursor-pointer shadow-md shadow-[#bb152c]/20"
                                >
                                    {editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal Delete */}
            {deletingKec && (
                <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-md p-5 sm:p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <div>
                                <h3 className="font-bold font-heading text-lg text-red-600 dark:text-red-400">
                                    Hapus Kecamatan
                                </h3>
                                <p className="text-xs text-[#475569] dark:text-[#94a3b8]">
                                    Konfirmasi penghapusan data wilayah kecamatan.
                                </p>
                            </div>
                            <button
                                onClick={() => setDeletingKec(null)}
                                className="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <div className="space-y-4">
                            <div className="p-4 rounded-xl bg-red-50 dark:bg-red-950/30 border border-red-200/80 dark:border-red-900/40 flex items-start gap-3">
                                <div className="w-9 h-9 rounded-lg bg-red-100 dark:bg-red-900/50 flex items-center justify-center text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5">
                                    <Trash2 className="w-5 h-5" />
                                </div>
                                <div className="text-xs space-y-1">
                                    <p className="font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                        Hapus Kecamatan {deletingKec.nama}?
                                    </p>
                                    <p className="text-[#475569] dark:text-[#94a3b8] leading-relaxed">
                                        Tindakan ini tidak dapat dibatalkan. Seluruh data <strong>Desa ({deletingKec.desas_count || 0} desa)</strong>, TPS, dan dokumen pemilu yang ada di kecamatan ini akan ikut terhapus secara permanen.
                                    </p>
                                </div>
                            </div>

                            <div className="pt-2 flex items-center justify-end gap-2.5">
                                <button
                                    type="button"
                                    onClick={() => setDeletingKec(null)}
                                    className="h-10 px-4 rounded-xl text-xs font-semibold bg-[#f1f5f9] dark:bg-[#1e293b] text-slate-700 dark:text-slate-300 hover:bg-[#e2e8f0] dark:hover:bg-[#27354f] transition cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="button"
                                    disabled={isDeleting}
                                    onClick={handleConfirmDelete}
                                    className="h-10 px-5 rounded-xl text-xs font-semibold bg-red-600 hover:bg-red-700 text-white transition disabled:opacity-50 cursor-pointer shadow-md shadow-red-600/20"
                                >
                                    {isDeleting ? 'Menghapus...' : 'Ya, Hapus Kecamatan'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
