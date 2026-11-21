import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { Building2, Plus, Edit2, Trash2, X, Filter, MapPin, Eye } from 'lucide-react';

export default function DesaPage({ desas, kecamatans, filters }) {
    const [selectedKec, setSelectedKec] = useState(filters?.kecamatan_id || '');
    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [editingDesa, setEditingDesa] = useState(null);
    const [deletingDesa, setDeletingDesa] = useState(null);
    const [isDeleting, setIsDeleting] = useState(false);

    const addForm = useForm({
        nama: '',
        kecamatan_id: selectedKec || (kecamatans?.[0]?.id || '')
    });

    const editForm = useForm({
        nama: '',
        kecamatan_id: ''
    });

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
        if (!editingDesa) return;
        editForm.put(`/admin/desa/${editingDesa.id}`, {
            onSuccess: () => {
                setEditingDesa(null);
                editForm.reset();
            }
        });
    };

    const handleConfirmDelete = () => {
        if (!deletingDesa) return;
        setIsDeleting(true);
        router.delete(`/admin/desa/${deletingDesa.id}`, {
            onSuccess: () => {
                setDeletingDesa(null);
                setIsDeleting(false);
            },
            onError: () => {
                setIsDeleting(false);
            }
        });
    };

    const activeKecamatan = kecamatans?.find((k) => String(k.id) === String(selectedKec));

    return (
        <AdminLayout title="Kelola Desa">
            <Head title="Kelola Desa" />

            <div className="space-y-5 sm:space-y-6">
                {/* Header Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 sm:p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div className="space-y-1">
                        <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-0.5 rounded-md border border-red-100 dark:border-red-900/30">
                            Master Wilayah
                        </span>
                        <h1 className="text-xl sm:text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight">
                            Kelola Desa / Kelurahan
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8]">
                            {activeKecamatan
                                ? `Menampilkan desa di Kecamatan ${activeKecamatan.nama} (${desas?.length || 0} desa).`
                                : 'Pilih kecamatan untuk mengelola daftar desa.'}
                        </p>
                    </div>

                    <button
                        onClick={() => {
                            addForm.clearErrors();
                            addForm.setData({
                                nama: '',
                                kecamatan_id: selectedKec || (kecamatans?.[0]?.id || '')
                            });
                            setIsAddModalOpen(true);
                        }}
                        className="inline-flex items-center justify-center gap-2 bg-[#bb152c] hover:bg-[#991124] active:scale-[0.98] text-white font-semibold h-10 px-4 rounded-xl text-xs shadow-md shadow-[#bb152c]/20 transition cursor-pointer flex-shrink-0"
                    >
                        <Plus className="w-4 h-4" />
                        <span>Tambah Desa Baru</span>
                    </button>
                </div>

                {/* Filter Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 sm:p-5 shadow-xs">
                    <form onSubmit={handleFilter} className="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                        <div className="flex-1">
                            <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                                Pilih Kecamatan
                            </label>
                            <select
                                value={selectedKec}
                                onChange={(e) => setSelectedKec(e.target.value)}
                                className="w-full h-10 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 text-xs outline-none focus:border-[#bb152c] focus:ring-2 focus:ring-[#bb152c]/15 transition"
                            >
                                <option value="">-- Pilih Kecamatan --</option>
                                {kecamatans?.map((kec) => (
                                    <option key={kec.id} value={kec.id}>
                                        {kec.nama}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <button
                            type="submit"
                            className="h-10 bg-[#0f172a] dark:bg-[#1e293b] hover:bg-[#1e293b] dark:hover:bg-[#27354f] active:scale-[0.98] text-white font-semibold px-5 rounded-xl text-xs inline-flex items-center justify-center gap-2 transition cursor-pointer shadow-xs flex-shrink-0"
                        >
                            <Filter className="w-3.5 h-3.5" />
                            <span>Tampilkan Desa</span>
                        </button>
                    </form>
                </div>

                {/* Table Container */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-sm overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs min-w-[500px]">
                            <thead className="bg-[#f8fafc] dark:bg-[#0b0f19] border-b border-[#e2e8f0] dark:border-[#1e293b] uppercase tracking-wider text-[#475569] dark:text-[#94a3b8] font-bold text-[11px]">
                                <tr>
                                    <th className="px-5 py-3.5">Nama Desa</th>
                                    <th className="px-5 py-3.5">Kecamatan</th>
                                    <th className="px-5 py-3.5">Jumlah TPS</th>
                                    <th className="px-5 py-3.5 text-right w-44">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#f1f5f9] dark:divide-[#1e293b]">
                                {desas?.length > 0 ? (
                                    desas.map((desa) => (
                                        <tr key={desa.id} className="hover:bg-[#f8fafc] dark:hover:bg-[#1f2937]/50 transition group">
                                            <td className="px-5 py-3.5 font-semibold text-[#0f172a] dark:text-[#f8fafc]">
                                                <div className="flex items-center gap-2.5">
                                                    <div className="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 flex-shrink-0">
                                                        <Building2 className="w-3.5 h-3.5" />
                                                    </div>
                                                    <span className="text-sm font-medium">{desa.nama}</span>
                                                </div>
                                            </td>
                                            <td className="px-5 py-3.5 text-[#475569] dark:text-[#94a3b8]">
                                                <span className="inline-flex items-center gap-1">
                                                    <MapPin className="w-3 h-3 text-slate-400" />
                                                    <span>{desa.kecamatan?.nama || '-'}</span>
                                                </span>
                                            </td>
                                            <td className="px-5 py-3.5">
                                                <span className="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700/60">
                                                    {desa.tps_count || 0} TPS
                                                </span>
                                            </td>
                                            <td className="px-5 py-3.5 text-right">
                                                <div className="inline-flex items-center justify-end gap-1.5">
                                                    <a
                                                        href={`/admin/desa/${desa.id}/view`}
                                                        title="Lihat Dashboard sebagai PPS"
                                                        className="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-900/40 hover:bg-amber-100 dark:hover:bg-amber-900/60 hover:border-amber-300 transition-all duration-150 cursor-pointer shadow-2xs group/btn"
                                                    >
                                                        <Eye className="w-4 h-4 transition-transform group-hover/btn:scale-110" />
                                                    </a>
                                                    <button
                                                        title="Edit Desa"
                                                        onClick={() => {
                                                            setEditingDesa(desa);
                                                            editForm.clearErrors();
                                                            editForm.setData({
                                                                nama: desa.nama,
                                                                kecamatan_id: desa.kecamatan_id,
                                                            });
                                                        }}
                                                        className="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-900/40 hover:bg-blue-100 dark:hover:bg-blue-900/60 hover:border-blue-300 transition-all duration-150 cursor-pointer shadow-2xs group/btn"
                                                    >
                                                        <Edit2 className="w-4 h-4 transition-transform group-hover/btn:scale-110" />
                                                    </button>
                                                    <button
                                                        title="Hapus Desa"
                                                        onClick={() => setDeletingDesa(desa)}
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
                                        <td colSpan="4" className="px-5 py-12 text-center text-xs text-[#94a3b8]">
                                            <div className="flex flex-col items-center justify-center gap-2">
                                                <Building2 className="w-8 h-8 text-slate-300 dark:text-slate-600 stroke-[1.5]" />
                                                <p className="font-medium text-slate-600 dark:text-slate-400">
                                                    {selectedKec
                                                        ? 'Belum ada desa yang terdaftar di kecamatan ini.'
                                                        : 'Pilih kecamatan di atas untuk melihat daftar desa.'}
                                                </p>
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
                                <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc]">Tambah Desa Baru</h3>
                                <p className="text-xs text-[#475569] dark:text-[#94a3b8]">Pilih kecamatan dan masukkan nama desa.</p>
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
                                    Kecamatan <span className="text-red-500">*</span>
                                </label>
                                <select
                                    value={addForm.data.kecamatan_id}
                                    onChange={(e) => addForm.setData('kecamatan_id', e.target.value)}
                                    className="w-full h-10 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 text-xs outline-none focus:border-[#bb152c] focus:ring-2 focus:ring-[#bb152c]/15 transition"
                                    required
                                >
                                    <option value="">-- Pilih Kecamatan --</option>
                                    {kecamatans?.map((k) => (
                                        <option key={k.id} value={k.id}>{k.nama}</option>
                                    ))}
                                </select>
                                {addForm.errors.kecamatan_id && (
                                    <p className="text-red-500 text-[11px] mt-1.5 font-medium">{addForm.errors.kecamatan_id}</p>
                                )}
                            </div>
                            <div>
                                <label className="block text-xs font-semibold mb-1.5 text-[#0f172a] dark:text-[#f8fafc]">
                                    Nama Desa <span className="text-red-500">*</span>
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
            {editingDesa && (
                <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-md p-5 sm:p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <div>
                                <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc]">Edit Desa</h3>
                                <p className="text-xs text-[#475569] dark:text-[#94a3b8]">Perbarui data desa atau ubah kecamatannya.</p>
                            </div>
                            <button
                                onClick={() => { setEditingDesa(null); editForm.clearErrors(); }}
                                className="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg transition"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>
                        <form onSubmit={handleUpdate} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold mb-1.5 text-[#0f172a] dark:text-[#f8fafc]">
                                    Kecamatan <span className="text-red-500">*</span>
                                </label>
                                <select
                                    value={editForm.data.kecamatan_id}
                                    onChange={(e) => editForm.setData('kecamatan_id', e.target.value)}
                                    className="w-full h-10 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 text-xs outline-none focus:border-[#bb152c] focus:ring-2 focus:ring-[#bb152c]/15 transition"
                                    required
                                >
                                    <option value="">-- Pilih Kecamatan --</option>
                                    {kecamatans?.map((k) => (
                                        <option key={k.id} value={k.id}>{k.nama}</option>
                                    ))}
                                </select>
                                {editForm.errors.kecamatan_id && (
                                    <p className="text-red-500 text-[11px] mt-1.5 font-medium">{editForm.errors.kecamatan_id}</p>
                                )}
                            </div>
                            <div>
                                <label className="block text-xs font-semibold mb-1.5 text-[#0f172a] dark:text-[#f8fafc]">
                                    Nama Desa <span className="text-red-500">*</span>
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
                                    onClick={() => { setEditingDesa(null); editForm.clearErrors(); }}
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
            {deletingDesa && (
                <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-md p-5 sm:p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <div>
                                <h3 className="font-bold font-heading text-lg text-red-600 dark:text-red-400">
                                    Hapus Desa
                                </h3>
                                <p className="text-xs text-[#475569] dark:text-[#94a3b8]">
                                    Konfirmasi penghapusan data wilayah desa.
                                </p>
                            </div>
                            <button
                                onClick={() => setDeletingDesa(null)}
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
                                        Hapus Desa {deletingDesa.nama}?
                                    </p>
                                    <p className="text-[#475569] dark:text-[#94a3b8] leading-relaxed">
                                        Tindakan ini tidak dapat dibatalkan. Seluruh data <strong>TPS</strong> dan dokumen pemilu yang terhubung dengan desa ini akan ikut terhapus secara permanen.
                                    </p>
                                </div>
                            </div>

                            <div className="pt-2 flex items-center justify-end gap-2.5">
                                <button
                                    type="button"
                                    onClick={() => setDeletingDesa(null)}
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
                                    {isDeleting ? 'Menghapus...' : 'Ya, Hapus Desa'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
