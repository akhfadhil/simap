import React, { useState } from 'react';
import { Head, Link, useForm, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import {
    Users,
    UserPlus,
    Filter,
    Download,
    Edit2,
    Trash2,
    X,
    Building2,
    MapPin,
    Pin,
    ChevronLeft,
    ChevronRight,
    AlertTriangle,
    Shield,
    CheckCircle2
} from 'lucide-react';

export default function UserIndex({ users, usersLoaded, kecamatans = [], desas = [], tpsList = [], partais = [], filters = {} }) {
    const { auth } = usePage().props;
    const currentUserId = auth?.user?.id;

    // Filter Bar State
    const [filterRole, setFilterRole] = useState(filters?.role || 'admin');
    const [filterKec, setFilterKec] = useState(filters?.kecamatan_id || '');
    const [filterDesa, setFilterDesa] = useState(filters?.desa_id || '');

    // Modal States
    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [editingUser, setEditingUser] = useState(null);
    const [userToDelete, setUserToDelete] = useState(null);

    // Cascading state for Add Modal
    const [addKecId, setAddKecId] = useState('');
    const [addDesaId, setAddDesaId] = useState('');

    // Cascading state for Edit Modal
    const [editKecId, setEditKecId] = useState('');
    const [editDesaId, setEditDesaId] = useState('');

    // Add Form Hook
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

    // Edit Form Hook
    const editForm = useForm({
        name: '',
        username: '',
        password: '',
        kecamatan_id: '',
        desa_id: '',
        tps_id: '',
        partai_id: '',
    });

    // Handle Filter
    const handleApplyFilter = (e) => {
        e.preventDefault();
        router.get('/admin/users', {
            role: filterRole || undefined,
            kecamatan_id: filterKec || undefined,
            desa_id: filterDesa || undefined,
        }, { preserveState: true });
    };

    // Open Add Modal
    const handleOpenAddModal = () => {
        addForm.reset();
        addForm.clearErrors();
        setAddKecId('');
        setAddDesaId('');
        setIsAddModalOpen(true);
    };

    // Submit Add User
    const handleAddUser = (e) => {
        e.preventDefault();
        addForm.post('/admin/users', {
            onSuccess: () => {
                setIsAddModalOpen(false);
                addForm.reset();
            }
        });
    };

    // Open Edit Modal
    const handleOpenEditModal = (u) => {
        setEditingUser(u);
        editForm.clearErrors();

        const userKecId = u.role === 'ppk'
            ? (u.kecamatan_id || '')
            : u.role === 'pps'
            ? (u.desa?.kecamatan_id || u.kecamatan_id || '')
            : u.role === 'kpps'
            ? (u.tps?.desa?.kecamatan_id || '')
            : '';

        const userDesaId = u.role === 'pps'
            ? (u.desa_id || '')
            : u.role === 'kpps'
            ? (u.tps?.desa_id || u.desa_id || '')
            : '';

        setEditKecId(userKecId ? String(userKecId) : '');
        setEditDesaId(userDesaId ? String(userDesaId) : '');

        editForm.setData({
            name: u.name || '',
            username: u.username || '',
            password: '',
            kecamatan_id: u.role === 'ppk' ? (u.kecamatan_id || '') : '',
            desa_id: u.role === 'pps' ? (u.desa_id || '') : '',
            tps_id: u.role === 'kpps' ? (u.tps_id || '') : '',
            partai_id: u.role === 'partai' ? (u.partai_id || '') : '',
        });
    };

    // Submit Update User
    const handleUpdateUser = (e) => {
        e.preventDefault();
        editForm.put(`/admin/users/${editingUser.id}`, {
            onSuccess: () => {
                setEditingUser(null);
                editForm.reset();
            }
        });
    };

    // Confirm Delete User
    const handleConfirmDelete = () => {
        if (!userToDelete) return;
        router.delete(`/admin/users/${userToDelete.id}`, {
            onSuccess: () => setUserToDelete(null)
        });
    };

    // Cascading helpers for Add Modal
    const availableDesasForAdd = addKecId
        ? desas.filter(d => d.kecamatan_id === parseInt(addKecId))
        : [];
    const availableTpsForAdd = addDesaId
        ? tpsList.filter(t => t.desa_id === parseInt(addDesaId))
        : [];

    // Cascading helpers for Edit Modal
    const availableDesasForEdit = editKecId
        ? desas.filter(d => d.kecamatan_id === parseInt(editKecId))
        : [];
    const availableTpsForEdit = editDesaId
        ? tpsList.filter(t => t.desa_id === parseInt(editDesaId))
        : [];

    // Filtered Desas for Filter Bar
    const filteredDesasForBar = filterKec ? desas.filter(d => d.kecamatan_id === parseInt(filterKec)) : desas;

    return (
        <AdminLayout title="Manajemen Pengguna">
            <Head title="Manajemen Pengguna" />

            <div className="space-y-6">
                {/* Banner Header */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-xs">
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
                                {filteredDesasForBar?.map(desa => (
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
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-xs overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-[#f8fafc] dark:bg-[#0b0f19] border-b border-[#e2e8f0] dark:border-[#1e293b] uppercase tracking-wider text-[#475569] dark:text-[#94a3b8] font-bold">
                                <tr>
                                    <th className="px-5 py-3.5">Nama & Username</th>
                                    <th className="px-5 py-3.5">Role</th>
                                    <th className="px-5 py-3.5">Wilayah / Afiliasi Penugasan</th>
                                    <th className="px-5 py-3.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#f1f5f9] dark:divide-[#1e293b]">
                                {users?.data?.length > 0 ? (
                                    users.data.map((u) => {
                                        const isCurrentUser = u.id === currentUserId;

                                        return (
                                            <tr key={u.id} className="hover:bg-[#f8fafc] dark:hover:bg-[#1f2937]/50 transition">
                                                <td className="px-5 py-3.5">
                                                    <div className="flex items-center gap-2">
                                                        <p className="font-bold text-[#0f172a] dark:text-[#f8fafc]">{u.name}</p>
                                                        {isCurrentUser && (
                                                            <span className="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                                Anda
                                                            </span>
                                                        )}
                                                    </div>
                                                    <p className="text-[11px] text-[#94a3b8] font-mono mt-0.5">@{u.username}</p>
                                                </td>
                                                <td className="px-5 py-3.5">
                                                    <span className={`inline-flex px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider ${
                                                        u.role === 'admin'
                                                            ? 'bg-red-50 text-[#bb152c] dark:bg-red-950/40 dark:text-red-400 border border-red-100 dark:border-red-900/50'
                                                            : u.role === 'komisioner'
                                                            ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-100 dark:border-amber-900/50'
                                                            : u.role === 'ppk'
                                                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-100 dark:border-blue-900/50'
                                                            : u.role === 'pps'
                                                            ? 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-400 border border-teal-100 dark:border-teal-900/50'
                                                            : u.role === 'kpps'
                                                            ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/50'
                                                            : 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400 border border-purple-100 dark:border-purple-900/50'
                                                    }`}>
                                                        {u.role}
                                                    </span>
                                                </td>
                                                <td className="px-5 py-3.5 text-[#475569] dark:text-[#94a3b8]">
                                                    {u.role === 'ppk' && (
                                                        <span className="font-medium text-[#0f172a] dark:text-[#f8fafc]">
                                                            Kecamatan {u.kecamatan?.nama || '-'}
                                                        </span>
                                                    )}
                                                    {u.role === 'pps' && (
                                                        <span className="font-medium text-[#0f172a] dark:text-[#f8fafc]">
                                                            Desa {u.desa?.nama || '-'} <span className="text-[#94a3b8] text-[11px]">({u.desa?.kecamatan?.nama ? `Kec. ${u.desa.kecamatan.nama}` : ''})</span>
                                                        </span>
                                                    )}
                                                    {u.role === 'kpps' && (
                                                        <span className="font-medium text-[#0f172a] dark:text-[#f8fafc]">
                                                            TPS {u.tps?.nama || '-'} <span className="text-[#94a3b8] text-[11px]">({u.tps?.desa?.nama ? `Desa ${u.tps.desa.nama}` : ''})</span>
                                                        </span>
                                                    )}
                                                    {u.role === 'partai' && (
                                                        <span className="font-medium text-[#0f172a] dark:text-[#f8fafc]">
                                                            Partai {u.partai?.nama_partai || '-'}
                                                        </span>
                                                    )}
                                                    {!['ppk', 'pps', 'kpps', 'partai'].includes(u.role) && (
                                                        <span className="text-slate-500 dark:text-slate-400 italic">
                                                            Tingkat Kabupaten (Wewenang Penuh)
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="px-5 py-3.5 text-right space-x-1">
                                                    <button
                                                        onClick={() => handleOpenEditModal(u)}
                                                        className="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/40 transition cursor-pointer"
                                                        title="Edit User"
                                                    >
                                                        <Edit2 className="w-4 h-4" />
                                                    </button>
                                                    <button
                                                        onClick={() => setUserToDelete(u)}
                                                        disabled={isCurrentUser}
                                                        className={`p-1.5 rounded-lg transition ${
                                                            isCurrentUser
                                                                ? 'text-slate-300 dark:text-slate-700 cursor-not-allowed opacity-40'
                                                                : 'text-slate-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 cursor-pointer'
                                                        }`}
                                                        title={isCurrentUser ? 'Tidak bisa menghapus akun sendiri' : 'Hapus User'}
                                                    >
                                                        <Trash2 className="w-4 h-4" />
                                                    </button>
                                                </td>
                                            </tr>
                                        );
                                    })
                                ) : (
                                    <tr>
                                        <td colSpan="4" className="px-5 py-10 text-center text-xs text-[#94a3b8]">
                                            Tidak ada pengguna ditemukan untuk filter yang dipilih.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination Bar */}
                    {users?.links && users.links.length > 3 && (
                        <div className="px-5 py-4 border-t border-[#f1f5f9] dark:border-[#1e293b] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#475569] dark:text-[#94a3b8]">
                            <div>
                                Menampilkan <span className="font-semibold text-[#0f172a] dark:text-[#f8fafc]">{users.from || 0}</span> sampai{' '}
                                <span className="font-semibold text-[#0f172a] dark:text-[#f8fafc]">{users.to || 0}</span> dari{' '}
                                <span className="font-semibold text-[#0f172a] dark:text-[#f8fafc]">{users.total || 0}</span> pengguna
                            </div>

                            <div className="flex items-center gap-1">
                                {users.links.map((link, idx) => {
                                    if (!link.url) {
                                        return (
                                            <span
                                                key={idx}
                                                className="px-3 py-1.5 rounded-lg text-slate-300 dark:text-slate-700 cursor-not-allowed text-xs"
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        );
                                    }

                                    return (
                                        <Link
                                            key={idx}
                                            href={link.url}
                                            preserveState
                                            className={`px-3 py-1.5 rounded-lg font-medium transition text-xs ${
                                                link.active
                                                    ? 'bg-[#bb152c] text-white font-bold'
                                                    : 'hover:bg-[#f1f5f9] dark:hover:bg-[#1f2937] text-[#475569] dark:text-[#94a3b8]'
                                            }`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    );
                                })}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* ADD USER MODAL */}
            {isAddModalOpen && (
                <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4 my-8">
                        <div className="flex items-center justify-between pb-3 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <div>
                                <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc]">Tambah User Baru</h3>
                                <p className="text-xs text-[#94a3b8] mt-0.5">Buat akun petugas atau operator baru</p>
                            </div>
                            <button
                                onClick={() => setIsAddModalOpen(false)}
                                className="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <form onSubmit={handleAddUser} className="space-y-4">
                            {/* Nama Lengkap */}
                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1">
                                    Nama Lengkap <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={addForm.data.name}
                                    onChange={(e) => addForm.setData('name', e.target.value)}
                                    placeholder="Contoh: Budi Santoso"
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3.5 py-2 text-xs outline-none focus:border-[#bb152c]"
                                    required
                                />
                                {addForm.errors.name && (
                                    <p className="text-red-500 text-[10px] mt-1 font-medium">{addForm.errors.name}</p>
                                )}
                            </div>

                            {/* Username */}
                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1">
                                    Username <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={addForm.data.username}
                                    onChange={(e) => addForm.setData('username', e.target.value)}
                                    placeholder="Contoh: ppk_bangorejo"
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3.5 py-2 text-xs outline-none focus:border-[#bb152c]"
                                    required
                                />
                                {addForm.errors.username && (
                                    <p className="text-red-500 text-[10px] mt-1 font-medium">{addForm.errors.username}</p>
                                )}
                            </div>

                            {/* Password */}
                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1">
                                    Password
                                </label>
                                <input
                                    type="password"
                                    value={addForm.data.password}
                                    onChange={(e) => addForm.setData('password', e.target.value)}
                                    placeholder="Kosongkan untuk otomatis sama dengan username"
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3.5 py-2 text-xs outline-none focus:border-[#bb152c]"
                                />
                                <p className="text-[10px] text-[#94a3b8] mt-1">Minimal 6 karakter jika diisi.</p>
                                {addForm.errors.password && (
                                    <p className="text-red-500 text-[10px] mt-1 font-medium">{addForm.errors.password}</p>
                                )}
                            </div>

                            {/* Role Dropdown */}
                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1">
                                    Role Pengguna <span className="text-red-500">*</span>
                                </label>
                                <select
                                    value={addForm.data.role}
                                    onChange={(e) => {
                                        const r = e.target.value;
                                        addForm.setData(data => ({
                                            ...data,
                                            role: r,
                                            kecamatan_id: '',
                                            desa_id: '',
                                            tps_id: '',
                                            partai_id: '',
                                        }));
                                        setAddKecId('');
                                        setAddDesaId('');
                                    }}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3.5 py-2 text-xs outline-none focus:border-[#bb152c]"
                                >
                                    <option value="admin">Administrator / Operator</option>
                                    <option value="komisioner">Komisioner</option>
                                    <option value="ppk">PPK (Kecamatan)</option>
                                    <option value="pps">PPS (Desa)</option>
                                    <option value="kpps">KPPS (TPS)</option>
                                    <option value="partai">Partai Politik</option>
                                </select>
                            </div>

                            {/* CONDITIONAL TERRITORY ASSIGNMENT */}
                            {/* PPK -> Pilih Kecamatan */}
                            {addForm.data.role === 'ppk' && (
                                <div className="p-3.5 bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/40 rounded-xl space-y-2">
                                    <label className="block text-xs font-semibold text-blue-900 dark:text-blue-300">
                                        Pilih Kecamatan Tugas <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={addForm.data.kecamatan_id}
                                        onChange={(e) => addForm.setData('kecamatan_id', e.target.value)}
                                        className="w-full bg-white dark:bg-[#0b0f19] border border-blue-200 dark:border-blue-900/60 text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-blue-500"
                                        required
                                    >
                                        <option value="">-- Pilih Kecamatan --</option>
                                        {kecamatans.map(k => (
                                            <option key={k.id} value={k.id}>{k.nama}</option>
                                        ))}
                                    </select>
                                    {addForm.errors.kecamatan_id && (
                                        <p className="text-red-500 text-[10px] font-medium">{addForm.errors.kecamatan_id}</p>
                                    )}
                                </div>
                            )}

                            {/* PPS -> Pilih Kecamatan lalu Desa */}
                            {addForm.data.role === 'pps' && (
                                <div className="p-3.5 bg-teal-50/50 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-900/40 rounded-xl space-y-3">
                                    <div>
                                        <label className="block text-xs font-semibold text-teal-900 dark:text-teal-300 mb-1">
                                            1. Pilih Kecamatan
                                        </label>
                                        <select
                                            value={addKecId}
                                            onChange={(e) => {
                                                setAddKecId(e.target.value);
                                                addForm.setData('desa_id', '');
                                            }}
                                            className="w-full bg-white dark:bg-[#0b0f19] border border-teal-200 dark:border-teal-900/60 text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-teal-500"
                                        >
                                            <option value="">-- Filter Berdasarkan Kecamatan --</option>
                                            {kecamatans.map(k => (
                                                <option key={k.id} value={k.id}>{k.nama}</option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-teal-900 dark:text-teal-300 mb-1">
                                            2. Pilih Desa / Kelurahan Tugas <span className="text-red-500">*</span>
                                        </label>
                                        <select
                                            value={addForm.data.desa_id}
                                            onChange={(e) => addForm.setData('desa_id', e.target.value)}
                                            disabled={!addKecId}
                                            className="w-full bg-white dark:bg-[#0b0f19] border border-teal-200 dark:border-teal-900/60 text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-teal-500 disabled:opacity-50"
                                            required
                                        >
                                            <option value="">-- Pilih Desa --</option>
                                            {availableDesasForAdd.map(d => (
                                                <option key={d.id} value={d.id}>{d.nama}</option>
                                            ))}
                                        </select>
                                        {addForm.errors.desa_id && (
                                            <p className="text-red-500 text-[10px] font-medium mt-1">{addForm.errors.desa_id}</p>
                                        )}
                                    </div>
                                </div>
                            )}

                            {/* KPPS -> Pilih Kecamatan -> Desa -> TPS */}
                            {addForm.data.role === 'kpps' && (
                                <div className="p-3.5 bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-900/40 rounded-xl space-y-3">
                                    <div>
                                        <label className="block text-xs font-semibold text-indigo-900 dark:text-indigo-300 mb-1">
                                            1. Pilih Kecamatan
                                        </label>
                                        <select
                                            value={addKecId}
                                            onChange={(e) => {
                                                setAddKecId(e.target.value);
                                                setAddDesaId('');
                                                addForm.setData('tps_id', '');
                                            }}
                                            className="w-full bg-white dark:bg-[#0b0f19] border border-indigo-200 dark:border-indigo-900/60 text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-indigo-500"
                                        >
                                            <option value="">-- Filter Berdasarkan Kecamatan --</option>
                                            {kecamatans.map(k => (
                                                <option key={k.id} value={k.id}>{k.nama}</option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-indigo-900 dark:text-indigo-300 mb-1">
                                            2. Pilih Desa
                                        </label>
                                        <select
                                            value={addDesaId}
                                            onChange={(e) => {
                                                setAddDesaId(e.target.value);
                                                addForm.setData('tps_id', '');
                                            }}
                                            disabled={!addKecId}
                                            className="w-full bg-white dark:bg-[#0b0f19] border border-indigo-200 dark:border-indigo-900/60 text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-indigo-500 disabled:opacity-50"
                                        >
                                            <option value="">-- Pilih Desa --</option>
                                            {availableDesasForAdd.map(d => (
                                                <option key={d.id} value={d.id}>{d.nama}</option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-indigo-900 dark:text-indigo-300 mb-1">
                                            3. Pilih TPS Tugas <span className="text-red-500">*</span>
                                        </label>
                                        <select
                                            value={addForm.data.tps_id}
                                            onChange={(e) => addForm.setData('tps_id', e.target.value)}
                                            disabled={!addDesaId}
                                            className="w-full bg-white dark:bg-[#0b0f19] border border-indigo-200 dark:border-indigo-900/60 text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-indigo-500 disabled:opacity-50"
                                            required
                                        >
                                            <option value="">-- Pilih TPS --</option>
                                            {availableTpsForAdd.map(t => (
                                                <option key={t.id} value={t.id}>TPS {t.nama}</option>
                                            ))}
                                        </select>
                                        {addForm.errors.tps_id && (
                                            <p className="text-red-500 text-[10px] font-medium mt-1">{addForm.errors.tps_id}</p>
                                        )}
                                    </div>
                                </div>
                            )}

                            {/* PARTAI -> Pilih Partai Politik */}
                            {addForm.data.role === 'partai' && (
                                <div className="p-3.5 bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900/40 rounded-xl space-y-2">
                                    <label className="block text-xs font-semibold text-purple-900 dark:text-purple-300">
                                        Pilih Partai Politik <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={addForm.data.partai_id}
                                        onChange={(e) => addForm.setData('partai_id', e.target.value)}
                                        className="w-full bg-white dark:bg-[#0b0f19] border border-purple-200 dark:border-purple-900/60 text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-purple-500"
                                        required
                                    >
                                        <option value="">-- Pilih Partai --</option>
                                        {partais.map(p => (
                                            <option key={p.id} value={p.id}>No. {p.nomor_urut} - {p.nama_partai}</option>
                                        ))}
                                    </select>
                                    {addForm.errors.partai_id && (
                                        <p className="text-red-500 text-[10px] font-medium">{addForm.errors.partai_id}</p>
                                    )}
                                </div>
                            )}

                            {/* ADMIN / KOMISIONER INFO */}
                            {['admin', 'komisioner'].includes(addForm.data.role) && (
                                <div className="p-3 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl flex items-center gap-2.5 text-xs text-slate-600 dark:text-slate-400">
                                    <Shield className="w-4 h-4 text-[#bb152c] shrink-0" />
                                    <span>Akun ini bertugas di tingkat Kabupaten dan memiliki wewenang lintas wilayah.</span>
                                </div>
                            )}

                            {/* Modal Actions */}
                            <div className="pt-3 border-t border-[#f1f5f9] dark:border-[#1e293b] flex justify-end gap-2">
                                <button
                                    type="button"
                                    onClick={() => setIsAddModalOpen(false)}
                                    className="px-4 py-2 rounded-xl text-xs font-semibold bg-[#f1f5f9] dark:bg-[#1f2937] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={addForm.processing}
                                    className="px-5 py-2 rounded-xl text-xs font-semibold bg-[#bb152c] hover:bg-[#991124] text-white shadow-md shadow-[#bb152c]/20 transition cursor-pointer"
                                >
                                    {addForm.processing ? 'Menyimpan...' : 'Simpan User'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* EDIT USER MODAL */}
            {editingUser && (
                <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4 my-8">
                        <div className="flex items-center justify-between pb-3 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <div>
                                <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc]">
                                    Edit Pengguna
                                </h3>
                                <p className="text-xs text-[#94a3b8] mt-0.5">
                                    Role: <span className="font-bold uppercase text-[#bb152c] dark:text-red-400">{editingUser.role}</span>
                                </p>
                            </div>
                            <button
                                onClick={() => setEditingUser(null)}
                                className="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <form onSubmit={handleUpdateUser} className="space-y-4">
                            {/* Nama Lengkap */}
                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1">
                                    Nama Lengkap <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={editForm.data.name}
                                    onChange={(e) => editForm.setData('name', e.target.value)}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3.5 py-2 text-xs outline-none focus:border-[#bb152c]"
                                    required
                                />
                                {editForm.errors.name && (
                                    <p className="text-red-500 text-[10px] mt-1 font-medium">{editForm.errors.name}</p>
                                )}
                            </div>

                            {/* Username */}
                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1">
                                    Username <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={editForm.data.username}
                                    onChange={(e) => editForm.setData('username', e.target.value)}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3.5 py-2 text-xs outline-none focus:border-[#bb152c]"
                                    required
                                />
                                {editForm.errors.username && (
                                    <p className="text-red-500 text-[10px] mt-1 font-medium">{editForm.errors.username}</p>
                                )}
                            </div>

                            {/* Password Baru */}
                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1">
                                    Password Baru
                                </label>
                                <input
                                    type="password"
                                    value={editForm.data.password}
                                    onChange={(e) => editForm.setData('password', e.target.value)}
                                    placeholder="Kosongkan jika tidak ingin mengubah password"
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3.5 py-2 text-xs outline-none focus:border-[#bb152c]"
                                />
                                <p className="text-[10px] text-[#94a3b8] mt-1">Isi minimal 6 karakter hanya jika ingin mengganti password.</p>
                                {editForm.errors.password && (
                                    <p className="text-red-500 text-[10px] mt-1 font-medium">{editForm.errors.password}</p>
                                )}
                            </div>

                            {/* CONDITIONAL TERRITORY ASSIGNMENT FOR EDIT */}
                            {/* PPK -> Pilih Kecamatan */}
                            {editingUser.role === 'ppk' && (
                                <div className="p-3.5 bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/40 rounded-xl space-y-2">
                                    <label className="block text-xs font-semibold text-blue-900 dark:text-blue-300">
                                        Kecamatan Tugas <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={editForm.data.kecamatan_id}
                                        onChange={(e) => editForm.setData('kecamatan_id', e.target.value)}
                                        className="w-full bg-white dark:bg-[#0b0f19] border border-blue-200 dark:border-blue-900/60 text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-blue-500"
                                        required
                                    >
                                        <option value="">-- Pilih Kecamatan --</option>
                                        {kecamatans.map(k => (
                                            <option key={k.id} value={k.id}>{k.nama}</option>
                                        ))}
                                    </select>
                                    {editForm.errors.kecamatan_id && (
                                        <p className="text-red-500 text-[10px] font-medium">{editForm.errors.kecamatan_id}</p>
                                    )}
                                </div>
                            )}

                            {/* PPS -> Pilih Kecamatan lalu Desa */}
                            {editingUser.role === 'pps' && (
                                <div className="p-3.5 bg-teal-50/50 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-900/40 rounded-xl space-y-3">
                                    <div>
                                        <label className="block text-xs font-semibold text-teal-900 dark:text-teal-300 mb-1">
                                            Kecamatan Wilayah
                                        </label>
                                        <select
                                            value={editKecId}
                                            onChange={(e) => {
                                                setEditKecId(e.target.value);
                                                editForm.setData('desa_id', '');
                                            }}
                                            className="w-full bg-white dark:bg-[#0b0f19] border border-teal-200 dark:border-teal-900/60 text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-teal-500"
                                        >
                                            <option value="">-- Pilih Kecamatan --</option>
                                            {kecamatans.map(k => (
                                                <option key={k.id} value={k.id}>{k.nama}</option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-teal-900 dark:text-teal-300 mb-1">
                                            Desa / Kelurahan Tugas <span className="text-red-500">*</span>
                                        </label>
                                        <select
                                            value={editForm.data.desa_id}
                                            onChange={(e) => editForm.setData('desa_id', e.target.value)}
                                            disabled={!editKecId}
                                            className="w-full bg-white dark:bg-[#0b0f19] border border-teal-200 dark:border-teal-900/60 text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-teal-500 disabled:opacity-50"
                                            required
                                        >
                                            <option value="">-- Pilih Desa --</option>
                                            {availableDesasForEdit.map(d => (
                                                <option key={d.id} value={d.id}>{d.nama}</option>
                                            ))}
                                        </select>
                                        {editForm.errors.desa_id && (
                                            <p className="text-red-500 text-[10px] font-medium mt-1">{editForm.errors.desa_id}</p>
                                        )}
                                    </div>
                                </div>
                            )}

                            {/* KPPS -> Pilih Kecamatan -> Desa -> TPS */}
                            {editingUser.role === 'kpps' && (
                                <div className="p-3.5 bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-900/40 rounded-xl space-y-3">
                                    <div>
                                        <label className="block text-xs font-semibold text-indigo-900 dark:text-indigo-300 mb-1">
                                            Kecamatan Wilayah
                                        </label>
                                        <select
                                            value={editKecId}
                                            onChange={(e) => {
                                                setEditKecId(e.target.value);
                                                setEditDesaId('');
                                                editForm.setData('tps_id', '');
                                            }}
                                            className="w-full bg-white dark:bg-[#0b0f19] border border-indigo-200 dark:border-indigo-900/60 text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-indigo-500"
                                        >
                                            <option value="">-- Pilih Kecamatan --</option>
                                            {kecamatans.map(k => (
                                                <option key={k.id} value={k.id}>{k.nama}</option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-indigo-900 dark:text-indigo-300 mb-1">
                                            Desa Wilayah
                                        </label>
                                        <select
                                            value={editDesaId}
                                            onChange={(e) => {
                                                setEditDesaId(e.target.value);
                                                editForm.setData('tps_id', '');
                                            }}
                                            disabled={!editKecId}
                                            className="w-full bg-white dark:bg-[#0b0f19] border border-indigo-200 dark:border-indigo-900/60 text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-indigo-500 disabled:opacity-50"
                                        >
                                            <option value="">-- Pilih Desa --</option>
                                            {availableDesasForEdit.map(d => (
                                                <option key={d.id} value={d.id}>{d.nama}</option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-indigo-900 dark:text-indigo-300 mb-1">
                                            TPS Tugas <span className="text-red-500">*</span>
                                        </label>
                                        <select
                                            value={editForm.data.tps_id}
                                            onChange={(e) => editForm.setData('tps_id', e.target.value)}
                                            disabled={!editDesaId}
                                            className="w-full bg-white dark:bg-[#0b0f19] border border-indigo-200 dark:border-indigo-900/60 text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-indigo-500 disabled:opacity-50"
                                            required
                                        >
                                            <option value="">-- Pilih TPS --</option>
                                            {availableTpsForEdit.map(t => (
                                                <option key={t.id} value={t.id}>TPS {t.nama}</option>
                                            ))}
                                        </select>
                                        {editForm.errors.tps_id && (
                                            <p className="text-red-500 text-[10px] font-medium mt-1">{editForm.errors.tps_id}</p>
                                        )}
                                    </div>
                                </div>
                            )}

                            {/* PARTAI -> Pilih Partai Politik */}
                            {editingUser.role === 'partai' && (
                                <div className="p-3.5 bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900/40 rounded-xl space-y-2">
                                    <label className="block text-xs font-semibold text-purple-900 dark:text-purple-300">
                                        Partai Politik <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={editForm.data.partai_id}
                                        onChange={(e) => editForm.setData('partai_id', e.target.value)}
                                        className="w-full bg-white dark:bg-[#0b0f19] border border-purple-200 dark:border-purple-900/60 text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-purple-500"
                                        required
                                    >
                                        <option value="">-- Pilih Partai --</option>
                                        {partais.map(p => (
                                            <option key={p.id} value={p.id}>No. {p.nomor_urut} - {p.nama_partai}</option>
                                        ))}
                                    </select>
                                    {editForm.errors.partai_id && (
                                        <p className="text-red-500 text-[10px] font-medium">{editForm.errors.partai_id}</p>
                                    )}
                                </div>
                            )}

                            {/* Modal Actions */}
                            <div className="pt-3 border-t border-[#f1f5f9] dark:border-[#1e293b] flex justify-end gap-2">
                                <button
                                    type="button"
                                    onClick={() => setEditingUser(null)}
                                    className="px-4 py-2 rounded-xl text-xs font-semibold bg-[#f1f5f9] dark:bg-[#1f2937] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    disabled={editForm.processing}
                                    className="px-5 py-2 rounded-xl text-xs font-semibold bg-[#bb152c] hover:bg-[#991124] text-white shadow-md shadow-[#bb152c]/20 transition cursor-pointer"
                                >
                                    {editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* DELETE USER CONFIRMATION MODAL */}
            {userToDelete && (
                <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-md p-6 shadow-2xl space-y-4">
                        <div className="flex items-center gap-3 text-red-600 dark:text-red-400">
                            <div className="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center border border-red-100 dark:border-red-900/50">
                                <AlertTriangle className="w-5 h-5" />
                            </div>
                            <div>
                                <h3 className="font-bold font-heading text-base text-[#0f172a] dark:text-[#f8fafc]">
                                    Hapus Pengguna?
                                </h3>
                                <p className="text-xs text-[#94a3b8]">Tindakan ini tidak dapat dibatalkan</p>
                            </div>
                        </div>

                        <div className="p-3 bg-red-50/50 dark:bg-red-950/20 border border-red-100 dark:border-red-900/40 rounded-xl text-xs text-slate-700 dark:text-slate-300 space-y-1">
                            <p>Anda yakin ingin menghapus akun pengguna berikut?</p>
                            <p className="font-bold text-[#0f172a] dark:text-[#f8fafc] text-sm">
                                {userToDelete.name} <span className="font-mono text-xs text-[#94a3b8]">(@{userToDelete.username})</span>
                            </p>
                            <p className="text-[11px] text-[#94a3b8]">
                                Role: <span className="font-bold uppercase text-red-600 dark:text-red-400">{userToDelete.role}</span>
                            </p>
                        </div>

                        <div className="flex justify-end gap-2 pt-2">
                            <button
                                type="button"
                                onClick={() => setUserToDelete(null)}
                                className="px-4 py-2 rounded-xl text-xs font-semibold bg-[#f1f5f9] dark:bg-[#1f2937] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                onClick={handleConfirmDelete}
                                className="px-4 py-2 rounded-xl text-xs font-semibold bg-red-600 hover:bg-red-700 text-white shadow-md shadow-red-600/20 transition cursor-pointer"
                            >
                                Ya, Hapus Pengguna
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
