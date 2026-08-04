import React, { useState } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import {
    Users,
    UserPlus,
    Filter,
    Download,
    Search,
    Edit2,
    Trash2,
    CheckCircle2,
    X,
    UserCheck,
    Layers,
    MapPin,
    Building2,
    Pin
} from 'lucide-react';

export default function UserIndex({ users, usersLoaded, kecamatans, desas, tpsList, partais, filters }) {
    const [filterRole, setFilterRole] = useState(filters?.role || 'admin');
    const [filterKec, setFilterKec] = useState(filters?.kecamatan_id || '');
    const [filterDesa, setFilterDesa] = useState(filters?.desa_id || '');

    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [editingUser, setEditingUser] = useState(null);

    // Form Hooks
    const addForm = useForm({
        name: '',
        username: '',
        password: '',
        role: 'admin',
        kecamatan_id: '',
        desa_id: '',
        tps_id: '',
        partai_id: '',
    });

    const editForm = useForm({
        name: '',
        username: '',
        password: '',
        kecamatan_id: '',
        desa_id: '',
        tps_id: '',
        partai_id: '',
    });

    const handleApplyFilter = (e) => {
        e.preventDefault();
        router.get('/admin/users', {
            role: filterRole || undefined,
            kecamatan_id: filterKec || undefined,
            desa_id: filterDesa || undefined,
        }, { preserveState: true });
    };

    const handleOpenAddModal = () => {
        addForm.reset();
        setIsAddModalOpen(true);
    };

    const handleAddUser = (e) => {
        e.preventDefault();
        addForm.post('/admin/users', {
            onSuccess: () => {
                setIsAddModalOpen(false);
                addForm.reset();
            }
        });
    };

    const handleOpenEditModal = (user) => {
        setEditingUser(user);
        editForm.setData({
            name: user.name,
            username: user.username,
            password: '',
            kecamatan_id: user.kecamatan_id || '',
            desa_id: user.desa_id || '',
            tps_id: user.tps_id || '',
            partai_id: user.partai_id || '',
        });
    };

    const handleUpdateUser = (e) => {
        e.preventDefault();
        editForm.put(`/admin/users/${editingUser.id}`, {
            onSuccess: () => {
                setEditingUser(null);
            }
        });
    };

    const handleDeleteUser = (user) => {
        if (confirm(`Yakin ingin menghapus pengguna ${user.name}?`)) {
            router.delete(`/admin/users/${user.id}`);
        }
    };

    const filteredDesas = filterKec ? desas?.filter(d => d.kecamatan_id === parseInt(filterKec)) : desas;

    return (
        <AdminLayout title="Manajemen Pengguna">
            <Head title="Manajemen Pengguna" />

            <div className="space-y-6">
                {/* Banner Header */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_10px_15px_-3px_rgba(0,0,0,0.3)]">
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md">
                                Akses Sistem
                            </span>
                            <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                                Kelola Pengguna & Akun Petugas
                            </h1>
                            <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                                Kelola hak akses akun operator, PPK, PPS, KPPS, komisioner, dan partai politik.
                            </p>
                        </div>

                        <div className="flex items-center gap-2">
                            <Link
                                href="/admin/users/bulk"
                                className="inline-flex items-center gap-2 bg-[#f1f5f9] dark:bg-[#1f2937] hover:bg-[#e2e8f0] dark:hover:bg-[#374151] text-[#0f172a] dark:text-[#f8fafc] font-semibold px-4 py-2.5 rounded-xl text-xs border border-[#e2e8f0] dark:border-[#1e293b] transition"
                            >
                                <Users className="w-4 h-4" />
                                <span>Bulk Input User</span>
                            </Link>
                            <button
                                onClick={handleOpenAddModal}
                                className="inline-flex items-center gap-2 bg-[#bb152c] hover:bg-[#991124] text-white font-semibold px-4 py-2.5 rounded-xl text-xs shadow-md shadow-[#bb152c]/20 transition cursor-pointer"
                            >
                                <UserPlus className="w-4 h-4" />
                                <span>Tambah User Baru</span>
                            </button>
                        </div>
                    </div>
                </div>

                {/* Filter Form Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs">
                    <form onSubmit={handleApplyFilter} className="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                        <div>
                            <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                                Role Pengguna
                            </label>
                            <select
                                value={filterRole}
                                onChange={(e) => setFilterRole(e.target.value)}
                                className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]"
                            >
                                <option value="">Semua Role</option>
                                <option value="admin">Administrator / Operator</option>
                                <option value="komisioner">Komisioner</option>
                                <option value="ppk">PPK (Kecamatan)</option>
                                <option value="pps">PPS (Desa)</option>
                                <option value="kpps">KPPS (TPS)</option>
                                <option value="partai">Partai Politik</option>
                            </select>
                        </div>

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
                                <option value="">Semua Kecamatan</option>
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
                                <option value="">Semua Desa</option>
                                {filteredDesas?.map(desa => (
                                    <option key={desa.id} value={desa.id}>{desa.nama}</option>
                                ))}
                            </select>
                        </div>

                        <div className="flex gap-2">
                            <button
                                type="submit"
                                className="flex-1 bg-[#0f172a] dark:bg-[#1f2937] hover:bg-[#334151] text-white font-semibold py-2 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition cursor-pointer"
                            >
                                <Filter className="w-3.5 h-3.5" />
                                <span>Terapkan Filter</span>
                            </button>

                            <a
                                href={`/admin/users/export?role=${filterRole}&kecamatan_id=${filterKec}&desa_id=${filterDesa}`}
                                className="bg-[#f1f5f9] dark:bg-[#1f2937] hover:bg-[#e2e8f0] text-[#0f172a] dark:text-[#f8fafc] p-2 rounded-xl border border-[#e2e8f0] dark:border-[#1e293b] flex items-center justify-center transition"
                                title="Export CSV"
                            >
                                <Download className="w-4 h-4" />
                            </a>
                        </div>
                    </form>
                </div>

                {/* Users Table Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-sm overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-[#f8fafc] dark:bg-[#0b0f19] border-b border-[#e2e8f0] dark:border-[#1e293b] uppercase tracking-wider text-[#475569] dark:text-[#94a3b8] font-bold">
                                <tr>
                                    <th className="px-5 py-3.5">Nama & Username</th>
                                    <th className="px-5 py-3.5">Role</th>
                                    <th className="px-5 py-3.5">Wilayah / Afiliasi</th>
                                    <th className="px-5 py-3.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#f1f5f9] dark:divide-[#1e293b]">
                                {users?.data?.length > 0 ? (
                                    users.data.map((u) => (
                                        <tr key={u.id} className="hover:bg-[#f8fafc] dark:hover:bg-[#1f2937]/50 transition">
                                            <td className="px-5 py-3.5">
                                                <p className="font-bold text-[#0f172a] dark:text-[#f8fafc]">{u.name}</p>
                                                <p className="text-[10px] text-[#94a3b8] mt-0.5">@{u.username}</p>
                                            </td>
                                            <td className="px-5 py-3.5">
                                                <span className={`inline-flex px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider ${
                                                    u.role === 'admin'
                                                        ? 'bg-red-50 text-[#bb152c] border border-red-100'
                                                        : u.role === 'komisioner'
                                                        ? 'bg-amber-50 text-amber-700 border border-amber-100'
                                                        : u.role === 'ppk'
                                                        ? 'bg-blue-50 text-blue-700 border border-blue-100'
                                                        : u.role === 'pps'
                                                        ? 'bg-teal-50 text-teal-700 border border-teal-100'
                                                        : u.role === 'kpps'
                                                        ? 'bg-indigo-50 text-indigo-700 border border-indigo-100'
                                                        : 'bg-purple-50 text-purple-700 border border-purple-100'
                                                }`}>
                                                    {u.role}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3.5 text-[#475569] dark:text-[#94a3b8]">
                                                {u.role === 'ppk' && u.kecamatan ? `Kec. ${u.kecamatan.nama}` : ''}
                                                {u.role === 'pps' && u.desa ? `Desa ${u.desa.nama}` : ''}
                                                {u.role === 'kpps' && u.tps ? `TPS ${u.tps.nama} (${u.tps.desa?.nama})` : ''}
                                                {u.role === 'partai' && u.partai ? `Partai ${u.partai.nama_partai}` : ''}
                                                {!['ppk', 'pps', 'kpps', 'partai'].includes(u.role) && 'Tingkat Kabupaten'}
                                            </td>
                                            <td className="px-5 py-3.5 text-right space-x-2">
                                                <button
                                                    onClick={() => handleOpenEditModal(u)}
                                                    className="p-1.5 text-slate-500 hover:text-indigo-600 transition cursor-pointer"
                                                    title="Edit User"
                                                >
                                                    <Edit2 className="w-4 h-4" />
                                                </button>
                                                <button
                                                    onClick={() => handleDeleteUser(u)}
                                                    className="p-1.5 text-slate-500 hover:text-red-600 transition cursor-pointer"
                                                    title="Hapus User"
                                                >
                                                    <Trash2 className="w-4 h-4" />
                                                </button>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="4" className="px-5 py-8 text-center text-xs text-[#94a3b8]">
                                            Tidak ada pengguna ditemukan.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* Add User Modal */}
            {isAddModalOpen && (
                <div className="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-lg p-6 shadow-xl">
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc]">Tambah User Baru</h3>
                            <button onClick={() => setIsAddModalOpen(false)} className="text-slate-400 hover:text-slate-600">
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <form onSubmit={handleAddUser} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold mb-1">Nama Lengkap</label>
                                <input
                                    type="text"
                                    value={addForm.data.name}
                                    onChange={(e) => addForm.setData('name', e.target.value)}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl px-3 py-2 text-xs outline-none"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold mb-1">Username</label>
                                <input
                                    type="text"
                                    value={addForm.data.username}
                                    onChange={(e) => addForm.setData('username', e.target.value)}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl px-3 py-2 text-xs outline-none"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold mb-1">Role</label>
                                <select
                                    value={addForm.data.role}
                                    onChange={(e) => addForm.setData('role', e.target.value)}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl px-3 py-2 text-xs outline-none"
                                >
                                    <option value="admin">Administrator / Operator</option>
                                    <option value="komisioner">Komisioner</option>
                                    <option value="ppk">PPK (Kecamatan)</option>
                                    <option value="pps">PPS (Desa)</option>
                                    <option value="kpps">KPPS (TPS)</option>
                                    <option value="partai">Partai Politik</option>
                                </select>
                            </div>

                            <div className="pt-2 flex justify-end gap-2">
                                <button
                                    type="button"
                                    onClick={() => setIsAddModalOpen(false)}
                                    className="px-4 py-2 rounded-xl text-xs font-semibold bg-[#f1f5f9] text-slate-700 hover:bg-slate-200"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={addForm.processing}
                                    className="px-4 py-2 rounded-xl text-xs font-semibold bg-[#bb152c] text-white hover:bg-[#991124]"
                                >
                                    {addForm.processing ? 'Memproses...' : 'Simpan User'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
