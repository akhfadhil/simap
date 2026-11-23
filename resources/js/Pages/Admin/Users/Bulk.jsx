import React, { useState, useEffect } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import {
    Users,
    ArrowLeft,
    CheckCircle2,
    AlertCircle,
    Building2,
    MapPin,
    Pin,
    Key,
    Shield,
    CheckSquare,
    Square,
    Save
} from 'lucide-react';

export default function BulkUser({
    role = 'kpps',
    kecamatans = [],
    desas = [],
    rows = [],
    selectedKecamatanId = null,
    selectedDesaId = null
}) {
    const { flash = {}, errors = {} } = usePage().props;

    // Local state for role and filter scope
    const [currentRole, setCurrentRole] = useState(role);
    const [currentKecamatanId, setCurrentKecamatanId] = useState(selectedKecamatanId ? String(selectedKecamatanId) : '');
    const [currentDesaId, setCurrentDesaId] = useState(selectedDesaId ? String(selectedDesaId) : '');
    const [isSubmitting, setIsSubmitting] = useState(false);

    // Row edit states: { [id]: { enabled: boolean, name: string, username: string, password: string } }
    const [rowState, setRowState] = useState({});

    // Synchronize rowState whenever `rows` prop changes
    useEffect(() => {
        const initial = {};
        (rows || []).forEach(row => {
            const hasUser = !!row.user;
            initial[row.id] = {
                enabled: !hasUser, // default check new users
                name: row.name || '',
                username: row.username || '',
                password: '',
            };
        });
        setRowState(initial);
    }, [rows]);

    // Handle role change
    const handleRoleChange = (newRole) => {
        setCurrentRole(newRole);
        setCurrentKecamatanId('');
        setCurrentDesaId('');
        router.get('/admin/users/bulk', { role: newRole }, { preserveState: false, preserveScroll: true });
    };

    // Handle kecamatan change
    const handleKecamatanChange = (kecId) => {
        setCurrentKecamatanId(kecId);
        setCurrentDesaId('');
        router.get('/admin/users/bulk', {
            role: currentRole,
            kecamatan_id: kecId || undefined,
        }, { preserveState: false, preserveScroll: true });
    };

    // Handle desa change
    const handleDesaChange = (desaId) => {
        setCurrentDesaId(desaId);
        router.get('/admin/users/bulk', {
            role: currentRole,
            kecamatan_id: currentKecamatanId || undefined,
            desa_id: desaId || undefined,
        }, { preserveState: false, preserveScroll: true });
    };

    // Row field updater
    const updateRowField = (id, field, value) => {
        setRowState(prev => ({
            ...prev,
            [id]: {
                ...(prev[id] || {}),
                [field]: value
            }
        }));
    };

    // Checkbox toggles
    const toggleRowCheck = (id) => {
        setRowState(prev => ({
            ...prev,
            [id]: {
                ...(prev[id] || {}),
                enabled: !(prev[id]?.enabled)
            }
        }));
    };

    const handleSelectAll = (select = true) => {
        setRowState(prev => {
            const next = { ...prev };
            Object.keys(next).forEach(id => {
                next[id] = { ...next[id], enabled: select };
            });
            return next;
        });
    };

    const handleSelectOnlyNew = () => {
        setRowState(prev => {
            const next = { ...prev };
            (rows || []).forEach(row => {
                next[row.id] = {
                    ...next[row.id],
                    enabled: !row.user
                };
            });
            return next;
        });
    };

    // Calculation of selected count
    const selectedCount = Object.values(rowState).filter(r => r.enabled).length;
    const allSelected = rows.length > 0 && selectedCount === rows.length;

    // Submit form
    const handleSubmit = (e) => {
        e.preventDefault();
        if (selectedCount === 0) {
            alert('Silakan centang minimal satu baris user untuk disimpan.');
            return;
        }

        setIsSubmitting(true);
        router.post('/admin/users/bulk', {
            role: currentRole,
            kecamatan_id: currentKecamatanId || undefined,
            desa_id: currentDesaId || undefined,
            rows: rowState,
        }, {
            preserveScroll: true,
            onFinish: () => setIsSubmitting(false),
        });
    };

    return (
        <AdminLayout title="Bulk Input User">
            <Head title="Bulk Input User" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2 mb-1">
                            <span className="text-[10px] font-bold tracking-[2px] text-red-600 dark:text-red-400 uppercase">
                                // ADMIN - PENGGUNA
                            </span>
                        </div>
                        <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] flex items-center gap-2.5">
                            <Users className="w-6 h-6 text-[#bb152c]" />
                            <span>Bulk Input User</span>
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1 max-w-2xl">
                            Generate dan perbarui akun petugas sekaligus per wilayah. Akun yang sudah ada akan diperbarui; akun baru dibuat otomatis dengan username terstandarisasi.
                        </p>
                    </div>

                    <Link
                        href="/admin/users"
                        className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#1e293b] transition shadow-xs self-start sm:self-auto cursor-pointer"
                    >
                        <ArrowLeft className="w-4 h-4 text-[#94a3b8]" />
                        <span>Kembali ke Daftar User</span>
                    </Link>
                </div>

                {/* Flash Messages */}
                {flash?.success && (
                    <div className="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 flex items-center gap-3 text-xs text-emerald-800 dark:text-emerald-300">
                        <CheckCircle2 className="w-5 h-5 text-emerald-600 shrink-0" />
                        <span className="font-medium">{flash.success}</span>
                    </div>
                )}

                {errors?.rows && (
                    <div className="p-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 flex items-center gap-3 text-xs text-red-800 dark:text-red-300">
                        <AlertCircle className="w-5 h-5 text-red-600 shrink-0" />
                        <span className="font-medium">{errors.rows}</span>
                    </div>
                )}

                {/* Scope & Filter Selector Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs">
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        {/* 1. Role Selector */}
                        <div>
                            <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5 flex items-center gap-1.5">
                                <Shield className="w-3.5 h-3.5 text-[#bb152c]" />
                                <span>Tingkat Peran (Role)</span>
                            </label>
                            <select
                                value={currentRole}
                                onChange={(e) => handleRoleChange(e.target.value)}
                                className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3.5 py-2.5 text-xs outline-none focus:border-[#bb152c] transition"
                            >
                                <option value="kpps">KPPS per TPS</option>
                                <option value="pps">PPS per Desa</option>
                                <option value="ppk">PPK per Kecamatan</option>
                            </select>
                        </div>

                        {/* 2. Kecamatan Selector (PPK tidak perlu filter kecamatan) */}
                        {currentRole !== 'ppk' && (
                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5 flex items-center gap-1.5">
                                    <Building2 className="w-3.5 h-3.5 text-blue-500" />
                                    <span>Pilih Kecamatan</span>
                                </label>
                                <select
                                    value={currentKecamatanId}
                                    onChange={(e) => handleKecamatanChange(e.target.value)}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3.5 py-2.5 text-xs outline-none focus:border-[#bb152c] transition"
                                >
                                    <option value="">-- Pilih Kecamatan --</option>
                                    {kecamatans.map(k => (
                                        <option key={k.id} value={k.id}>{k.nama}</option>
                                    ))}
                                </select>
                            </div>
                        )}

                        {/* 3. Desa Selector (Hanya untuk KPPS) */}
                        {currentRole === 'kpps' && (
                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5 flex items-center gap-1.5">
                                    <MapPin className="w-3.5 h-3.5 text-teal-500" />
                                    <span>Pilih Desa</span>
                                </label>
                                <select
                                    value={currentDesaId}
                                    onChange={(e) => handleDesaChange(e.target.value)}
                                    disabled={!currentKecamatanId}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3.5 py-2.5 text-xs outline-none focus:border-[#bb152c] transition disabled:opacity-50"
                                >
                                    <option value="">-- Pilih Desa --</option>
                                    {desas.map(d => (
                                        <option key={d.id} value={d.id}>{d.nama}</option>
                                    ))}
                                </select>
                            </div>
                        )}
                    </div>
                </div>

                {/* Table Content */}
                {rows.length === 0 ? (
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-12 text-center shadow-xs space-y-3">
                        <div className="w-14 h-14 mx-auto rounded-2xl bg-slate-100 dark:bg-[#0b0f19] flex items-center justify-center border border-[#e2e8f0] dark:border-[#1e293b] text-[#94a3b8]">
                            <Pin className="w-6 h-6" />
                        </div>
                        <h3 className="font-bold font-heading text-base text-[#0f172a] dark:text-[#f8fafc]">
                            Pilih Wilayah Terlebih Dahulu
                        </h3>
                        <p className="text-xs text-[#94a3b8] max-w-md mx-auto">
                            {currentRole === 'kpps'
                                ? 'Silakan tentukan Kecamatan dan Desa untuk memuat daftar seluruh TPS.'
                                : currentRole === 'pps'
                                ? 'Silakan tentukan Kecamatan untuk memuat daftar seluruh Desa.'
                                : 'Data kecamatan sedang dimuat.'}
                        </p>
                    </div>
                ) : (
                    <form onSubmit={handleSubmit} className="space-y-4">
                        {/* Quick Selection Toolbar */}
                        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs flex flex-wrap items-center justify-between gap-3">
                            <div className="flex items-center gap-2 flex-wrap">
                                <button
                                    type="button"
                                    onClick={() => handleSelectAll(!allSelected)}
                                    className="px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#f1f5f9] dark:bg-[#1f2937] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition flex items-center gap-1.5 cursor-pointer"
                                >
                                    {allSelected ? <CheckSquare className="w-3.5 h-3.5 text-[#bb152c]" /> : <Square className="w-3.5 h-3.5 text-[#94a3b8]" />}
                                    <span>{allSelected ? 'Batal Pilih Semua' : 'Pilih Semua'}</span>
                                </button>

                                <button
                                    type="button"
                                    onClick={handleSelectOnlyNew}
                                    className="px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#f1f5f9] dark:bg-[#1f2937] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer"
                                >
                                    Pilih Hanya User Baru
                                </button>
                            </div>

                            <div className="flex items-center gap-3">
                                <div className="text-xs text-[#475569] dark:text-[#94a3b8]">
                                    <span className="font-bold text-[#0f172a] dark:text-[#f8fafc]">{selectedCount}</span> dari {rows.length} akun dipilih
                                </div>
                                <button
                                    type="submit"
                                    disabled={isSubmitting || selectedCount === 0}
                                    className="px-4 py-2 rounded-xl text-xs font-semibold bg-[#bb152c] hover:bg-[#991124] text-white shadow-md shadow-[#bb152c]/20 transition flex items-center gap-2 disabled:opacity-50 cursor-pointer"
                                >
                                    <Save className="w-4 h-4" />
                                    <span>{isSubmitting ? 'Menyimpan...' : `Simpan ${selectedCount} Akun`}</span>
                                </button>
                            </div>
                        </div>

                        {/* Table */}
                        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-xs overflow-hidden">
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-xs">
                                    <thead className="bg-[#f8fafc] dark:bg-[#0b0f19] border-b border-[#e2e8f0] dark:border-[#1e293b] uppercase tracking-wider text-[#475569] dark:text-[#94a3b8] font-bold">
                                        <tr>
                                            <th className="px-4 py-3.5 w-12 text-center">
                                                <input
                                                    type="checkbox"
                                                    checked={allSelected}
                                                    onChange={(e) => handleSelectAll(e.target.checked)}
                                                    className="w-4 h-4 rounded text-[#bb152c] focus:ring-[#bb152c] cursor-pointer"
                                                />
                                            </th>
                                            <th className="px-4 py-3.5 min-w-[180px]">Wilayah / Entitas</th>
                                            <th className="px-4 py-3.5 min-w-[200px]">Nama Lengkap</th>
                                            <th className="px-4 py-3.5 min-w-[160px]">Username</th>
                                            <th className="px-4 py-3.5 min-w-[160px]">Password</th>
                                            <th className="px-4 py-3.5 w-24 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-[#f1f5f9] dark:divide-[#1e293b]">
                                        {rows.map((row) => {
                                            const state = rowState[row.id] || {
                                                enabled: false,
                                                name: row.name || '',
                                                username: row.username || '',
                                                password: '',
                                            };
                                            const isExisting = !!row.user;

                                            return (
                                                <tr
                                                    key={row.id}
                                                    className={`hover:bg-[#f8fafc] dark:hover:bg-[#1f2937]/50 transition ${
                                                        state.enabled ? 'bg-red-50/10 dark:bg-red-950/10' : 'opacity-80'
                                                    }`}
                                                >
                                                    <td className="px-4 py-3.5 text-center">
                                                        <input
                                                            type="checkbox"
                                                            checked={state.enabled}
                                                            onChange={() => toggleRowCheck(row.id)}
                                                            className="w-4 h-4 rounded text-[#bb152c] focus:ring-[#bb152c] cursor-pointer"
                                                        />
                                                    </td>
                                                    <td className="px-4 py-3.5">
                                                        <p className="font-bold text-[#0f172a] dark:text-[#f8fafc]">{row.label}</p>
                                                        <p className="text-[11px] text-[#94a3b8] mt-0.5">{row.scope}</p>
                                                    </td>
                                                    <td className="px-4 py-3.5">
                                                        <input
                                                            type="text"
                                                            value={state.name}
                                                            onChange={(e) => updateRowField(row.id, 'name', e.target.value)}
                                                            placeholder="Nama Lengkap"
                                                            className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-1.5 text-xs outline-none focus:border-[#bb152c]"
                                                        />
                                                    </td>
                                                    <td className="px-4 py-3.5">
                                                        <input
                                                            type="text"
                                                            value={state.username}
                                                            onChange={(e) => updateRowField(row.id, 'username', e.target.value)}
                                                            placeholder="Username"
                                                            className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-1.5 text-xs outline-none focus:border-[#bb152c] font-mono text-[11px]"
                                                        />
                                                    </td>
                                                    <td className="px-4 py-3.5">
                                                        <input
                                                            type="text"
                                                            value={state.password}
                                                            onChange={(e) => updateRowField(row.id, 'password', e.target.value)}
                                                            placeholder={isExisting ? 'Kosong = tetap' : 'Kosong = username'}
                                                            className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-1.5 text-xs outline-none focus:border-[#bb152c] placeholder:text-[#94a3b8]"
                                                        />
                                                    </td>
                                                    <td className="px-4 py-3.5 text-center">
                                                        {isExisting ? (
                                                            <span className="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-400 border border-teal-100 dark:border-teal-900/50">
                                                                Ada
                                                            </span>
                                                        ) : (
                                                            <span className="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-100 dark:border-amber-900/50">
                                                                Baru
                                                            </span>
                                                        )}
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>

                            {/* Table Footer */}
                            <div className="px-5 py-4 border-t border-[#f1f5f9] dark:border-[#1e293b] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#475569] dark:text-[#94a3b8]">
                                <p>
                                    Menampilkan {rows.length} baris. Password kosong untuk user baru akan memakai username sebagai password awal.
                                </p>
                                <button
                                    type="submit"
                                    disabled={isSubmitting || selectedCount === 0}
                                    className="px-5 py-2.5 rounded-xl text-xs font-semibold bg-[#bb152c] hover:bg-[#991124] text-white shadow-md shadow-[#bb152c]/20 transition flex items-center gap-2 disabled:opacity-50 cursor-pointer"
                                >
                                    <Save className="w-4 h-4" />
                                    <span>{isSubmitting ? 'Menyimpan...' : `Simpan ${selectedCount} User Terpilih`}</span>
                                </button>
                            </div>
                        </div>
                    </form>
                )}
            </div>
        </AdminLayout>
    );
}
