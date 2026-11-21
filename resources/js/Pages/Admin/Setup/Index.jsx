import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import {
    Settings,
    Vote,
    Users,
    MapPin,
    Save,
    Plus,
    Trash2,
    Edit2,
    CheckCircle2,
    X,
    Building2,
    Layers
} from 'lucide-react';

export default function SetupIndex({
    pemiluSettings,
    ppwpCalons,
    gubernurCalons,
    bupatiCalons,
    dpdCalons,
    partaiDprRi,
    partaiProv,
    partaiKab,
    dapils,
    kecamatans,
    partaiProfiles
}) {
    const [activeTab, setActiveTab] = useState('pemilu_settings');

    // Tab 1: Pemilu Settings Form
    const pemiluForm = useForm({
        jenis_ppwp: pemiluSettings?.ppwp?.is_active || false,
        jenis_gubernur: pemiluSettings?.gubernur?.is_active || false,
        jenis_bupati: pemiluSettings?.bupati?.is_active || false,
        jenis_dpd: pemiluSettings?.dpd?.is_active || false,
        jenis_dpr_ri: pemiluSettings?.dpr_ri?.is_active || false,
        jenis_dprd_prov: pemiluSettings?.dprd_prov?.is_active || false,
        jenis_dprd_kab: pemiluSettings?.dprd_kab?.is_active || false,
    });

    const handleSavePemiluSettings = (e) => {
        e.preventDefault();
        pemiluForm.post('/admin/setup/pemilu-settings', { preserveScroll: true });
    };

    // Tab 2: Paslon Batch Forms
    const [ppwpRows, setPpwpRows] = useState([{ nomor_urut: '', nama_paslon: '' }]);
    const [gubRows, setGubRows] = useState([{ nomor_urut: '', nama_paslon: '' }]);
    const [bupRows, setBupRows] = useState([{ nomor_urut: '', nama_paslon: '' }]);
    const [dpdRows, setDpdRows] = useState([{ nomor_urut: '', nama_calon: '' }]);

    const handleAddPpwp = (e) => {
        e.preventDefault();
        router.post('/admin/setup/ppwp', { calons: ppwpRows }, {
            onSuccess: () => setPpwpRows([{ nomor_urut: '', nama_paslon: '' }]),
            preserveScroll: true
        });
    };

    const handleAddGub = (e) => {
        e.preventDefault();
        router.post('/admin/setup/gubernur', { calons: gubRows }, {
            onSuccess: () => setGubRows([{ nomor_urut: '', nama_paslon: '' }]),
            preserveScroll: true
        });
    };

    const handleAddBup = (e) => {
        e.preventDefault();
        router.post('/admin/setup/bupati', { calons: bupRows }, {
            onSuccess: () => setBupRows([{ nomor_urut: '', nama_paslon: '' }]),
            preserveScroll: true
        });
    };

    const handleAddDpd = (e) => {
        e.preventDefault();
        router.post('/admin/setup/dpd', { calons: dpdRows }, {
            onSuccess: () => setDpdRows([{ nomor_urut: '', nama_calon: '' }]),
            preserveScroll: true
        });
    };

    const handleDeletePpwp = (id) => {
        if (confirm('Hapus paslon PPWP ini?')) router.delete(`/admin/setup/ppwp/${id}`, { preserveScroll: true });
    };
    const handleDeleteGub = (id) => {
        if (confirm('Hapus paslon Gubernur ini?')) router.delete(`/admin/setup/gubernur/${id}`, { preserveScroll: true });
    };
    const handleDeleteBup = (id) => {
        if (confirm('Hapus paslon Bupati ini?')) router.delete(`/admin/setup/bupati/${id}`, { preserveScroll: true });
    };
    const handleDeleteDpd = (id) => {
        if (confirm('Hapus calon DPD ini?')) router.delete(`/admin/setup/dpd/${id}`, { preserveScroll: true });
    };

    // Tab 3: Dapil Forms
    const [newDapilNama, setNewDapilNama] = useState('');
    const [kecamatanDapilMap, setKecamatanDapilMap] = useState(() => {
        const map = {};
        kecamatans?.forEach(k => { map[k.id] = k.dapil_id || ''; });
        return map;
    });

    const handleAddDapil = (e) => {
        e.preventDefault();
        router.post('/admin/setup/dapil', { nama: newDapilNama }, {
            onSuccess: () => setNewDapilNama(''),
            preserveScroll: true
        });
    };

    const handleDeleteDapil = (id) => {
        if (confirm('Hapus Dapil ini?')) router.delete(`/admin/setup/dapil/${id}`, { preserveScroll: true });
    };

    const handleSaveKecamatanDapil = (e) => {
        e.preventDefault();
        router.post('/admin/setup/kecamatan-dapil', { kecamatan_dapil: kecamatanDapilMap }, { preserveScroll: true });
    };

    // Tab 4 State Management
    const [tab4SubTab, setTab4SubTab] = useState('profiles'); // 'profiles' | 'dpr_ri' | 'dprd_prov' | 'dprd_kab'
    const [selectedDapilKabId, setSelectedDapilKabId] = useState(dapils?.[0]?.id || '');
    const [showAddPartaiModal, setShowAddPartaiModal] = useState(false);

    const [editingProfile, setEditingProfile] = useState(null);
    const profileForm = useForm({
        nomor_urut_aktif: '',
        nama_singkat: '',
        nama: '',
        nama_ketua: '',
        nama_sekretaris: '',
        nama_bendahara: '',
    });

    const handleOpenEditProfile = (p) => {
        setEditingProfile(p);
        profileForm.setData({
            nomor_urut_aktif: p.nomor_urut_aktif || '',
            nama_singkat: p.nama_singkat || '',
            nama: p.nama || '',
            nama_ketua: p.nama_ketua || '',
            nama_sekretaris: p.nama_sekretaris || '',
            nama_bendahara: p.nama_bendahara || '',
        });
    };

    const handleUpdateProfile = (e) => {
        e.preventDefault();
        profileForm.put(`/admin/setup/partai-profile/${editingProfile.id}`, {
            onSuccess: () => setEditingProfile(null),
            preserveScroll: true
        });
    };

    // Form Tambah Partai Baru Modal
    const [newPartaiJenis, setNewPartaiJenis] = useState('dpr_ri');
    const [newPartaiDapilId, setNewPartaiDapilId] = useState(dapils?.[0]?.id || '');
    const [newPartaiNomor, setNewPartaiNomor] = useState('');
    const [newPartaiNama, setNewPartaiNama] = useState('');

    const handleAddPartai = (e) => {
        e.preventDefault();
        router.post('/admin/setup/partai', {
            jenis: newPartaiJenis,
            dapil_id: newPartaiJenis === 'dprd_kab' ? newPartaiDapilId : undefined,
            partais: [{ nomor_urut: newPartaiNomor, nama_partai: newPartaiNama }]
        }, {
            onSuccess: () => {
                setNewPartaiNomor('');
                setNewPartaiNama('');
                setShowAddPartaiModal(false);
            },
            preserveScroll: true
        });
    };

    const handleDeletePartai = (partaiId) => {
        if (confirm('Hapus partai ini beserta seluruh calegnya?')) {
            router.delete(`/admin/setup/partai/${partaiId}`, { preserveScroll: true });
        }
    };

    // Inline Add Caleg State
    const [newCalegMap, setNewCalegMap] = useState({});
    const handleAddCaleg = (partaiId) => {
        const row = newCalegMap[partaiId];
        if (!row?.nomor_urut || !row?.nama_caleg) return alert('Isi nomor urut dan nama caleg!');
        router.post(`/admin/setup/partai/${partaiId}/caleg`, row, {
            onSuccess: () => {
                setNewCalegMap({ ...newCalegMap, [partaiId]: { nomor_urut: '', nama_caleg: '' } });
            },
            preserveScroll: true
        });
    };

    const handleDeleteCaleg = (calegId) => {
        if (confirm('Hapus caleg ini?')) router.delete(`/admin/setup/caleg/${calegId}`, { preserveScroll: true });
    };

    const jenisOptions = [
        { key: 'ppwp', label: 'PPWP (Presiden & Wakil Presiden)', desc: 'Pemilihan Presiden dan Wakil Presiden Republik Indonesia' },
        { key: 'gubernur', label: 'Pilgub (Gubernur & Wakil Gubernur)', desc: 'Pemilihan Gubernur dan Wakil Gubernur Jawa Timur' },
        { key: 'bupati', label: 'Pilbup (Bupati & Wakil Bupati)', desc: 'Pemilihan Bupati dan Wakil Bupati Banyuwangi' },
        { key: 'dpd', label: 'DPD RI (Dewan Perwakilan Daerah)', desc: 'Pemilihan Anggota DPD RI Utusan Jawa Timur' },
        { key: 'dpr_ri', label: 'DPR RI (Dewan Perwakilan Rakyat)', desc: 'Pemilihan Anggota DPR RI Dapil Jawa Timur III' },
        { key: 'dprd_prov', label: 'DPRD Provinsi', desc: 'Pemilihan Anggota DPRD Provinsi Jawa Timur' },
        { key: 'dprd_kab', label: 'DPRD Kabupaten', desc: 'Pemilihan Anggota DPRD Kabupaten Banyuwangi' },
    ];

    // Sub-render helper for Party & Caleg Cards with sleek Dark Mode
    const renderPartaiCalegCardGrid = (partaiList) => (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {partaiList && partaiList.length > 0 ? (
                partaiList.map((p) => (
                    <div
                        key={p.id}
                        className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs flex flex-col justify-between space-y-3"
                    >
                        {/* Party Card Header */}
                        <div className="flex items-center justify-between pb-2.5 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <div className="flex items-center gap-2">
                                <span className="bg-red-50 text-[#bb152c] dark:bg-red-950/50 dark:text-red-400 font-extrabold text-xs px-2.5 py-1 rounded-lg border border-red-100 dark:border-red-900/50">
                                    #{p.nomor_urut}
                                </span>
                                <span className="font-bold text-sm text-[#0f172a] dark:text-[#f8fafc] truncate max-w-[160px]" title={p.nama_partai}>
                                    {p.nama_partai}
                                </span>
                            </div>
                            <button
                                onClick={() => handleDeletePartai(p.id)}
                                className="text-[#94a3b8] hover:text-red-600 dark:hover:text-red-400 transition p-1 cursor-pointer"
                                title="Hapus Partai"
                            >
                                <Trash2 className="w-4 h-4" />
                            </button>
                        </div>

                        {/* Caleg List */}
                        <div className="space-y-1.5 min-h-[100px] max-h-56 overflow-y-auto pr-1">
                            {p.calegs && p.calegs.length > 0 ? (
                                p.calegs.map((c) => (
                                    <div
                                        key={c.id}
                                        className="flex justify-between items-center bg-[#f8fafc] dark:bg-[#0b0f19] px-2.5 py-1.5 rounded-xl border border-[#e2e8f0] dark:border-[#1e293b] text-xs"
                                    >
                                        <span className="text-[#0f172a] dark:text-[#f8fafc] font-medium truncate">
                                            <strong className="text-[#bb152c] dark:text-red-400 mr-1.5">#{c.nomor_urut}</strong>
                                            {c.nama_caleg}
                                        </span>
                                        <button
                                            onClick={() => handleDeleteCaleg(c.id)}
                                            className="text-[#94a3b8] hover:text-red-600 dark:hover:text-red-400 transition ml-2 cursor-pointer"
                                        >
                                            <X className="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                ))
                            ) : (
                                <div className="text-center py-6 text-[11px] text-[#94a3b8]">
                                    Belum ada caleg terdaftar.
                                </div>
                            )}
                        </div>

                        {/* Add Caleg Form */}
                        <div className="pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b] flex gap-1.5">
                            <input
                                type="number"
                                placeholder="No"
                                value={newCalegMap[p.id]?.nomor_urut || ''}
                                onChange={(e) => setNewCalegMap({ ...newCalegMap, [p.id]: { ...(newCalegMap[p.id] || {}), nomor_urut: e.target.value } })}
                                className="w-12 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-2 py-1.5 text-xs outline-none focus:border-[#bb152c] placeholder-[#94a3b8]"
                            />
                            <input
                                type="text"
                                placeholder="Nama Caleg Baru"
                                value={newCalegMap[p.id]?.nama_caleg || ''}
                                onChange={(e) => setNewCalegMap({ ...newCalegMap, [p.id]: { ...(newCalegMap[p.id] || {}), nama_caleg: e.target.value } })}
                                className="flex-1 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-2.5 py-1.5 text-xs outline-none focus:border-[#bb152c] placeholder-[#94a3b8]"
                            />
                            <button
                                onClick={() => handleAddCaleg(p.id)}
                                className="bg-[#0f172a] dark:bg-[#1f2937] hover:bg-[#334151] dark:hover:bg-[#374151] text-white font-semibold px-3 py-1.5 text-xs rounded-xl transition cursor-pointer"
                            >
                                +
                            </button>
                        </div>
                    </div>
                ))
            ) : (
                <div className="col-span-full bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-8 text-center text-xs text-[#94a3b8]">
                    Belum ada partai politik yang terdaftar di kategori ini.
                </div>
            )}
        </div>
    );

    return (
        <AdminLayout title="Setup Data Pemilu">
            <Head title="Setup Data Pemilu" />

            <div className="space-y-6">
                {/* Header Banner */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md">
                            Pengaturan Sistem
                        </span>
                        <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                            Setup Master Data Pemilu
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                            Atur jenis pemilu yang aktif, master calon/paslon, dapil, dan partai politik.
                        </p>
                    </div>
                </div>

                {/* Tabs Navigation Header */}
                <div className="flex border-b border-[#e2e8f0] dark:border-[#1e293b] gap-2 overflow-x-auto pb-1">
                    <button
                        onClick={() => setActiveTab('pemilu_settings')}
                        className={`px-4 py-2.5 text-xs font-semibold rounded-t-xl transition flex items-center gap-2 border-b-2 cursor-pointer ${
                            activeTab === 'pemilu_settings'
                                ? 'border-[#bb152c] text-[#bb152c] dark:text-red-400 bg-white dark:bg-[#151f32]'
                                : 'border-transparent text-[#475569] dark:text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc]'
                        }`}
                    >
                        <Settings className="w-4 h-4" />
                        <span>Tab 1: Jenis Pemilu Aktif</span>
                    </button>

                    <button
                        onClick={() => setActiveTab('paslon')}
                        className={`px-4 py-2.5 text-xs font-semibold rounded-t-xl transition flex items-center gap-2 border-b-2 cursor-pointer ${
                            activeTab === 'paslon'
                                ? 'border-[#bb152c] text-[#bb152c] dark:text-red-400 bg-white dark:bg-[#151f32]'
                                : 'border-transparent text-[#475569] dark:text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc]'
                        }`}
                    >
                        <Vote className="w-4 h-4" />
                        <span>Tab 2: Master Paslon & DPD</span>
                    </button>

                    <button
                        onClick={() => setActiveTab('dapil')}
                        className={`px-4 py-2.5 text-xs font-semibold rounded-t-xl transition flex items-center gap-2 border-b-2 cursor-pointer ${
                            activeTab === 'dapil'
                                ? 'border-[#bb152c] text-[#bb152c] dark:text-red-400 bg-white dark:bg-[#151f32]'
                                : 'border-transparent text-[#475569] dark:text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc]'
                        }`}
                    >
                        <MapPin className="w-4 h-4" />
                        <span>Tab 3: Dapil Wilayah</span>
                    </button>

                    <button
                        onClick={() => setActiveTab('partai')}
                        className={`px-4 py-2.5 text-xs font-semibold rounded-t-xl transition flex items-center gap-2 border-b-2 cursor-pointer ${
                            activeTab === 'partai'
                                ? 'border-[#bb152c] text-[#bb152c] dark:text-red-400 bg-white dark:bg-[#151f32]'
                                : 'border-transparent text-[#475569] dark:text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc]'
                        }`}
                    >
                        <Users className="w-4 h-4" />
                        <span>Tab 4: Partai & Caleg</span>
                    </button>
                </div>

                {/* TAB 1: PENGATURAN PEMILU AKTIF */}
                {activeTab === 'pemilu_settings' && (
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-sm space-y-6">
                        <div className="flex items-center justify-between pb-4 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <div>
                                <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc]">
                                    Status Aktif Jenis Pemilihan
                                </h3>
                                <p className="text-xs text-[#475569] dark:text-[#94a3b8]">
                                    Centang jenis pemilihan yang ingin diaktifkan untuk input data & rekapitulasi.
                                </p>
                            </div>
                        </div>

                        <form onSubmit={handleSavePemiluSettings} className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {jenisOptions.map((opt) => {
                                    const fieldName = `jenis_${opt.key}`;
                                    const isChecked = pemiluForm.data[fieldName];

                                    return (
                                        <label
                                            key={opt.key}
                                            className={`p-4 rounded-xl border transition cursor-pointer flex items-start gap-3 ${
                                                isChecked
                                                    ? 'bg-red-50/60 dark:bg-red-950/40 border-[#bb152c] dark:border-red-500/80'
                                                    : 'bg-[#f8fafc] dark:bg-[#0b0f19] border-[#e2e8f0] dark:border-[#1e293b]'
                                            }`}
                                        >
                                            <input
                                                type="checkbox"
                                                checked={isChecked}
                                                onChange={(e) => pemiluForm.setData(fieldName, e.target.checked)}
                                                className="mt-0.5 w-4 h-4 text-[#bb152c] rounded focus:ring-[#bb152c]"
                                            />
                                            <div>
                                                <p className="font-bold text-xs text-[#0f172a] dark:text-[#f8fafc]">
                                                    {opt.label}
                                                </p>
                                                <p className="text-[11px] text-[#475569] dark:text-[#94a3b8] mt-0.5">
                                                    {opt.desc}
                                                </p>
                                            </div>
                                        </label>
                                    );
                                })}
                            </div>

                            <div className="pt-4 border-t border-[#f1f5f9] dark:border-[#1e293b] flex justify-end">
                                <button
                                    type="submit"
                                    disabled={pemiluForm.processing}
                                    className="inline-flex items-center gap-2 bg-[#bb152c] hover:bg-[#991124] text-white font-semibold px-5 py-2.5 rounded-xl text-xs shadow-md shadow-[#bb152c]/20 transition cursor-pointer"
                                >
                                    <Save className="w-4 h-4" />
                                    <span>{pemiluForm.processing ? 'Memproses...' : 'Simpan Pengaturan Pemilu'}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                )}

                {/* TAB 2: MASTER PASLON & DPD */}
                {activeTab === 'paslon' && (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {/* PPWP */}
                        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-sm space-y-4">
                            <h3 className="font-bold font-heading text-base text-[#0f172a] dark:text-[#f8fafc]">Paslon PPWP</h3>
                            <div className="space-y-2">
                                {ppwpCalons?.map(c => (
                                    <div key={c.id} className="flex items-center justify-between bg-[#f8fafc] dark:bg-[#0b0f19] p-3 rounded-xl border border-[#e2e8f0] dark:border-[#1e293b] text-xs">
                                        <div><span className="font-bold text-[#bb152c] dark:text-red-400 mr-2">#{c.nomor_urut}</span><span className="text-[#0f172a] dark:text-[#f8fafc] font-medium">{c.nama_paslon}</span></div>
                                        <button onClick={() => handleDeletePpwp(c.id)} className="text-[#94a3b8] hover:text-red-600 dark:hover:text-red-400"><Trash2 className="w-4 h-4" /></button>
                                    </div>
                                ))}
                            </div>
                            <form onSubmit={handleAddPpwp} className="space-y-3 pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                                <div className="flex gap-2">
                                    <input type="number" placeholder="No" value={ppwpRows[0].nomor_urut} onChange={(e) => setPpwpRows([{ ...ppwpRows[0], nomor_urut: e.target.value }])} className="w-16 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]" required />
                                    <input type="text" placeholder="Nama Paslon PPWP" value={ppwpRows[0].nama_paslon} onChange={(e) => setPpwpRows([{ ...ppwpRows[0], nama_paslon: e.target.value }])} className="flex-1 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]" required />
                                </div>
                                <button type="submit" className="w-full bg-[#0f172a] dark:bg-[#1f2937] hover:bg-[#334151] dark:hover:bg-[#374151] text-white py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 cursor-pointer"><Plus className="w-3.5 h-3.5" /> Tambah Paslon PPWP</button>
                            </form>
                        </div>

                        {/* Gubernur */}
                        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-sm space-y-4">
                            <h3 className="font-bold font-heading text-base text-[#0f172a] dark:text-[#f8fafc]">Paslon Gubernur</h3>
                            <div className="space-y-2">
                                {gubernurCalons?.map(c => (
                                    <div key={c.id} className="flex items-center justify-between bg-[#f8fafc] dark:bg-[#0b0f19] p-3 rounded-xl border border-[#e2e8f0] dark:border-[#1e293b] text-xs">
                                        <div><span className="font-bold text-[#bb152c] dark:text-red-400 mr-2">#{c.nomor_urut}</span><span className="text-[#0f172a] dark:text-[#f8fafc] font-medium">{c.nama_paslon}</span></div>
                                        <button onClick={() => handleDeleteGub(c.id)} className="text-[#94a3b8] hover:text-red-600 dark:hover:text-red-400"><Trash2 className="w-4 h-4" /></button>
                                    </div>
                                ))}
                            </div>
                            <form onSubmit={handleAddGub} className="space-y-3 pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                                <div className="flex gap-2">
                                    <input type="number" placeholder="No" value={gubRows[0].nomor_urut} onChange={(e) => setGubRows([{ ...gubRows[0], nomor_urut: e.target.value }])} className="w-16 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]" required />
                                    <input type="text" placeholder="Nama Paslon Gubernur" value={gubRows[0].nama_paslon} onChange={(e) => setGubRows([{ ...gubRows[0], nama_paslon: e.target.value }])} className="flex-1 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]" required />
                                </div>
                                <button type="submit" className="w-full bg-[#0f172a] dark:bg-[#1f2937] hover:bg-[#334151] dark:hover:bg-[#374151] text-white py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 cursor-pointer"><Plus className="w-3.5 h-3.5" /> Tambah Paslon Gubernur</button>
                            </form>
                        </div>

                        {/* Bupati */}
                        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-sm space-y-4">
                            <h3 className="font-bold font-heading text-base text-[#0f172a] dark:text-[#f8fafc]">Paslon Bupati</h3>
                            <div className="space-y-2">
                                {bupatiCalons?.map(c => (
                                    <div key={c.id} className="flex items-center justify-between bg-[#f8fafc] dark:bg-[#0b0f19] p-3 rounded-xl border border-[#e2e8f0] dark:border-[#1e293b] text-xs">
                                        <div><span className="font-bold text-[#bb152c] dark:text-red-400 mr-2">#{c.nomor_urut}</span><span className="text-[#0f172a] dark:text-[#f8fafc] font-medium">{c.nama_paslon}</span></div>
                                        <button onClick={() => handleDeleteBup(c.id)} className="text-[#94a3b8] hover:text-red-600 dark:hover:text-red-400"><Trash2 className="w-4 h-4" /></button>
                                    </div>
                                ))}
                            </div>
                            <form onSubmit={handleAddBup} className="space-y-3 pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                                <div className="flex gap-2">
                                    <input type="number" placeholder="No" value={bupRows[0].nomor_urut} onChange={(e) => setBupRows([{ ...bupRows[0], nomor_urut: e.target.value }])} className="w-16 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]" required />
                                    <input type="text" placeholder="Nama Paslon Bupati" value={bupRows[0].nama_paslon} onChange={(e) => setBupRows([{ ...bupRows[0], nama_paslon: e.target.value }])} className="flex-1 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]" required />
                                </div>
                                <button type="submit" className="w-full bg-[#0f172a] dark:bg-[#1f2937] hover:bg-[#334151] dark:hover:bg-[#374151] text-white py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 cursor-pointer"><Plus className="w-3.5 h-3.5" /> Tambah Paslon Bupati</button>
                            </form>
                        </div>

                        {/* DPD */}
                        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-sm space-y-4">
                            <h3 className="font-bold font-heading text-base text-[#0f172a] dark:text-[#f8fafc]">Calon DPD RI</h3>
                            <div className="space-y-2">
                                {dpdCalons?.map(c => (
                                    <div key={c.id} className="flex items-center justify-between bg-[#f8fafc] dark:bg-[#0b0f19] p-3 rounded-xl border border-[#e2e8f0] dark:border-[#1e293b] text-xs">
                                        <div><span className="font-bold text-[#bb152c] dark:text-red-400 mr-2">#{c.nomor_urut}</span><span className="text-[#0f172a] dark:text-[#f8fafc] font-medium">{c.nama_calon}</span></div>
                                        <button onClick={() => handleDeleteDpd(c.id)} className="text-[#94a3b8] hover:text-red-600 dark:hover:text-red-400"><Trash2 className="w-4 h-4" /></button>
                                    </div>
                                ))}
                            </div>
                            <form onSubmit={handleAddDpd} className="space-y-3 pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                                <div className="flex gap-2">
                                    <input type="number" placeholder="No" value={dpdRows[0].nomor_urut} onChange={(e) => setDpdRows([{ ...dpdRows[0], nomor_urut: e.target.value }])} className="w-16 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]" required />
                                    <input type="text" placeholder="Nama Calon DPD" value={dpdRows[0].nama_calon} onChange={(e) => setDpdRows([{ ...dpdRows[0], nama_calon: e.target.value }])} className="flex-1 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]" required />
                                </div>
                                <button type="submit" className="w-full bg-[#0f172a] dark:bg-[#1f2937] hover:bg-[#334151] dark:hover:bg-[#374151] text-white py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 cursor-pointer"><Plus className="w-3.5 h-3.5" /> Tambah Calon DPD</button>
                            </form>
                        </div>
                    </div>
                )}

                {/* TAB 3: DAPIL WILAYAH */}
                {activeTab === 'dapil' && (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {/* Section Master Dapil */}
                        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-sm space-y-4">
                            <h3 className="font-bold font-heading text-base text-[#0f172a] dark:text-[#f8fafc]">
                                Master Daerah Pemilihan (Dapil)
                            </h3>
                            <div className="space-y-2">
                                {dapils?.map(d => (
                                    <div key={d.id} className="flex items-center justify-between bg-[#f8fafc] dark:bg-[#0b0f19] p-3 rounded-xl border border-[#e2e8f0] dark:border-[#1e293b] text-xs">
                                        <span className="font-bold text-[#0f172a] dark:text-[#f8fafc]">{d.nama}</span>
                                        <button onClick={() => handleDeleteDapil(d.id)} className="text-[#94a3b8] hover:text-red-600 dark:hover:text-red-400">
                                            <Trash2 className="w-4 h-4" />
                                        </button>
                                    </div>
                                ))}
                            </div>
                            <form onSubmit={handleAddDapil} className="flex gap-2 pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                                <input
                                    type="text"
                                    placeholder="Contoh: Dapil 1"
                                    value={newDapilNama}
                                    onChange={(e) => setNewDapilNama(e.target.value)}
                                    className="flex-1 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]"
                                    required
                                />
                                <button type="submit" className="bg-[#bb152c] text-white px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-1 cursor-pointer">
                                    <Plus className="w-4 h-4" /> Tambah
                                </button>
                            </form>
                        </div>

                        {/* Section Pemetaan Kecamatan -> Dapil */}
                        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-sm space-y-4">
                            <h3 className="font-bold font-heading text-base text-[#0f172a] dark:text-[#f8fafc]">
                                Pemetaan Kecamatan ke Dapil
                            </h3>
                            <form onSubmit={handleSaveKecamatanDapil} className="space-y-3">
                                <div className="space-y-2 max-h-72 overflow-y-auto pr-1">
                                    {kecamatans?.map(k => (
                                        <div key={k.id} className="flex items-center justify-between gap-3 text-xs bg-[#f8fafc] dark:bg-[#0b0f19] p-2.5 rounded-xl border border-[#e2e8f0] dark:border-[#1e293b]">
                                            <span className="font-semibold text-[#0f172a] dark:text-[#f8fafc]">{k.nama}</span>
                                            <select
                                                value={kecamatanDapilMap[k.id] || ''}
                                                onChange={(e) => setKecamatanDapilMap({ ...kecamatanDapilMap, [k.id]: e.target.value })}
                                                className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] text-xs rounded-lg px-2 py-1 outline-none"
                                            >
                                                <option value="">Belum Ada Dapil</option>
                                                {dapils?.map(d => (
                                                    <option key={d.id} value={d.id}>{d.nama}</option>
                                                ))}
                                            </select>
                                        </div>
                                    ))}
                                </div>
                                <button type="submit" className="w-full bg-[#0f172a] dark:bg-[#1f2937] hover:bg-[#334151] text-white py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 cursor-pointer">
                                    <Save className="w-4 h-4" /> Simpan Pemetaan Dapil
                                </button>
                            </form>
                        </div>
                    </div>
                )}

                {/* TAB 4: PARTAI & CALEG (DESAIN DARK MODE MODERN & COMPACT) */}
                {activeTab === 'partai' && (
                    <div className="space-y-5">
                        {/* Sub-Header Control Bar */}
                        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                            {/* Sub-Tabs Pills */}
                            <div className="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                                <button
                                    onClick={() => setTab4SubTab('profiles')}
                                    className={`px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-2 ${
                                        tab4SubTab === 'profiles'
                                            ? 'bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 border border-red-100 dark:border-red-900/50'
                                            : 'text-[#475569] dark:text-[#94a3b8] hover:bg-[#f1f5f9] dark:hover:bg-[#1f2937] hover:text-[#0f172a] dark:hover:text-[#f8fafc]'
                                    }`}
                                >
                                    <Users className="w-3.5 h-3.5" />
                                    <span>Profil Utama Partai</span>
                                </button>

                                <button
                                    onClick={() => setTab4SubTab('dpr_ri')}
                                    className={`px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-2 ${
                                        tab4SubTab === 'dpr_ri'
                                            ? 'bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 border border-red-100 dark:border-red-900/50'
                                            : 'text-[#475569] dark:text-[#94a3b8] hover:bg-[#f1f5f9] dark:hover:bg-[#1f2937] hover:text-[#0f172a] dark:hover:text-[#f8fafc]'
                                    }`}
                                >
                                    <Layers className="w-3.5 h-3.5" />
                                    <span>DPR RI ({partaiDprRi?.length || 0})</span>
                                </button>

                                <button
                                    onClick={() => setTab4SubTab('dprd_prov')}
                                    className={`px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-2 ${
                                        tab4SubTab === 'dprd_prov'
                                            ? 'bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 border border-red-100 dark:border-red-900/50'
                                            : 'text-[#475569] dark:text-[#94a3b8] hover:bg-[#f1f5f9] dark:hover:bg-[#1f2937] hover:text-[#0f172a] dark:hover:text-[#f8fafc]'
                                    }`}
                                >
                                    <Building2 className="w-3.5 h-3.5" />
                                    <span>DPRD Provinsi ({partaiProv?.length || 0})</span>
                                </button>

                                <button
                                    onClick={() => setTab4SubTab('dprd_kab')}
                                    className={`px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-2 ${
                                        tab4SubTab === 'dprd_kab'
                                            ? 'bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 border border-red-100 dark:border-red-900/50'
                                            : 'text-[#475569] dark:text-[#94a3b8] hover:bg-[#f1f5f9] dark:hover:bg-[#1f2937] hover:text-[#0f172a] dark:hover:text-[#f8fafc]'
                                    }`}
                                >
                                    <MapPin className="w-3.5 h-3.5" />
                                    <span>DPRD Kabupaten</span>
                                </button>
                            </div>

                            {/* Tombol Tambah Partai Modal */}
                            <button
                                onClick={() => setShowAddPartaiModal(true)}
                                className="bg-[#bb152c] hover:bg-[#991124] text-white font-semibold px-4 py-2 rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-xs transition cursor-pointer shrink-0"
                            >
                                <Plus className="w-4 h-4" />
                                <span>Tambah Partai</span>
                            </button>
                        </div>

                        {/* SUB-CONTENT 1: PROFIL UTAMA PARTAI */}
                        {tab4SubTab === 'profiles' && (
                            <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-sm space-y-4">
                                <div className="flex items-center justify-between pb-3 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                                    <h3 className="font-bold font-heading text-base text-[#0f172a] dark:text-[#f8fafc]">
                                        Profil & Nomor Urut Resmi Partai Politik
                                    </h3>
                                </div>
                                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    {partaiProfiles?.map(p => (
                                        <div key={p.id} className="bg-[#f8fafc] dark:bg-[#0b0f19] p-3.5 rounded-xl border border-[#e2e8f0] dark:border-[#1e293b] flex items-center justify-between text-xs hover:border-[#bb152c] dark:hover:border-red-500 transition">
                                            <div className="flex items-center gap-2 truncate">
                                                <span className="bg-red-50 dark:bg-red-950/50 text-[#bb152c] dark:text-red-400 font-extrabold text-xs px-2 py-0.5 rounded border border-red-100 dark:border-red-900/50">
                                                    #{p.nomor_urut_aktif}
                                                </span>
                                                <span className="font-bold text-[#0f172a] dark:text-[#f8fafc] truncate" title={p.nama}>
                                                    {p.nama_singkat}
                                                </span>
                                            </div>
                                            <button onClick={() => handleOpenEditProfile(p)} className="text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc] p-1 cursor-pointer">
                                                <Edit2 className="w-3.5 h-3.5" />
                                            </button>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* SUB-CONTENT 2: DPR RI */}
                        {tab4SubTab === 'dpr_ri' && renderPartaiCalegCardGrid(partaiDprRi)}

                        {/* SUB-CONTENT 3: DPRD PROVINSI */}
                        {tab4SubTab === 'dprd_prov' && renderPartaiCalegCardGrid(partaiProv)}

                        {/* SUB-CONTENT 4: DPRD KABUPATEN (DENGAN FILTER DAPIL FILTER PILLS) */}
                        {tab4SubTab === 'dprd_kab' && (
                            <div className="space-y-4">
                                {/* Dapil Selector Bar */}
                                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-3.5 shadow-xs flex items-center gap-2 overflow-x-auto">
                                    <span className="text-xs font-bold text-[#94a3b8] uppercase tracking-wider px-2 shrink-0">
                                        Pilih Dapil:
                                    </span>
                                    {dapils?.map((d) => {
                                        const isSelected = selectedDapilKabId === d.id;
                                        const countPartai = (partaiKab?.[d.id] || []).length;

                                        return (
                                            <button
                                                key={d.id}
                                                onClick={() => setSelectedDapilKabId(d.id)}
                                                className={`px-3.5 py-1.5 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer flex items-center gap-1.5 ${
                                                    isSelected
                                                        ? 'bg-[#bb152c] text-white dark:bg-[#bb152c] dark:text-white shadow-xs'
                                                        : 'bg-[#f8fafc] dark:bg-[#0b0f19] text-[#475569] dark:text-[#94a3b8] hover:bg-[#e2e8f0] dark:hover:bg-[#1f2937] border border-[#e2e8f0] dark:border-[#1e293b]'
                                                }`}
                                            >
                                                <span>{d.nama}</span>
                                                <span className={`text-[10px] px-1.5 py-0.2 rounded-full ${isSelected ? 'bg-white/20 text-white' : 'bg-[#e2e8f0] dark:bg-[#1e293b] text-[#64748b] dark:text-[#94a3b8]'}`}>
                                                    {countPartai}
                                                </span>
                                            </button>
                                        );
                                    })}
                                </div>

                                {/* Active Selected Dapil Content */}
                                {renderPartaiCalegCardGrid(partaiKab?.[selectedDapilKabId] || [])}
                            </div>
                        )}
                    </div>
                )}
            </div>

            {/* Modal Edit Profile Partai */}
            {editingProfile && (
                <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-md p-6 shadow-2xl space-y-4">
                        <div className="flex items-center justify-between pb-3 border-b border-[#e2e8f0] dark:border-[#1e293b]">
                            <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc]">
                                Edit Profil {editingProfile.nama_singkat}
                            </h3>
                            <button onClick={() => setEditingProfile(null)} className="text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc] cursor-pointer"><X className="w-5 h-5" /></button>
                        </div>
                        <form onSubmit={handleUpdateProfile} className="space-y-3 text-xs">
                            <div>
                                <label className="block font-semibold mb-1 text-[#0f172a] dark:text-[#f8fafc]">Nomor Urut Aktif</label>
                                <input type="number" value={profileForm.data.nomor_urut_aktif} onChange={(e) => profileForm.setData('nomor_urut_aktif', e.target.value)} className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 outline-none focus:border-[#bb152c]" required />
                            </div>
                            <div>
                                <label className="block font-semibold mb-1 text-[#0f172a] dark:text-[#f8fafc]">Nama Singkat Partai</label>
                                <input type="text" value={profileForm.data.nama_singkat} onChange={(e) => profileForm.setData('nama_singkat', e.target.value)} className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 outline-none focus:border-[#bb152c]" required />
                            </div>
                            <div>
                                <label className="block font-semibold mb-1 text-[#0f172a] dark:text-[#f8fafc]">Nama Lengkap Partai</label>
                                <input type="text" value={profileForm.data.nama} onChange={(e) => profileForm.setData('nama', e.target.value)} className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 outline-none focus:border-[#bb152c]" required />
                            </div>
                            <div className="pt-2 flex justify-end gap-2">
                                <button type="button" onClick={() => setEditingProfile(null)} className="px-4 py-2 rounded-xl bg-[#f1f5f9] dark:bg-[#1f2937] text-[#475569] dark:text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc] text-xs font-semibold cursor-pointer">Batal</button>
                                <button type="submit" disabled={profileForm.processing} className="px-4 py-2 rounded-xl bg-[#bb152c] text-white text-xs font-semibold cursor-pointer">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal Tambah Partai Baru */}
            {showAddPartaiModal && (
                <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-md p-6 shadow-2xl space-y-4">
                        <div className="flex items-center justify-between pb-3 border-b border-[#e2e8f0] dark:border-[#1e293b]">
                            <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc]">
                                Tambah Partai Peserta
                            </h3>
                            <button onClick={() => setShowAddPartaiModal(false)} className="text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc] cursor-pointer"><X className="w-5 h-5" /></button>
                        </div>
                        <form onSubmit={handleAddPartai} className="space-y-3 text-xs">
                            <div>
                                <label className="block font-semibold mb-1 text-[#0f172a] dark:text-[#f8fafc]">Tingkatan Pemilu</label>
                                <select
                                    value={newPartaiJenis}
                                    onChange={(e) => setNewPartaiJenis(e.target.value)}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 outline-none focus:border-[#bb152c]"
                                >
                                    <option value="dpr_ri">DPR RI</option>
                                    <option value="dprd_prov">DPRD Provinsi</option>
                                    <option value="dprd_kab">DPRD Kabupaten</option>
                                </select>
                            </div>

                            {newPartaiJenis === 'dprd_kab' && (
                                <div>
                                    <label className="block font-semibold mb-1 text-[#0f172a] dark:text-[#f8fafc]">Dapil Kabupaten</label>
                                    <select
                                        value={newPartaiDapilId}
                                        onChange={(e) => setNewPartaiDapilId(e.target.value)}
                                        className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 outline-none focus:border-[#bb152c]"
                                        required
                                    >
                                        {dapils?.map(d => (
                                            <option key={d.id} value={d.id}>{d.nama}</option>
                                        ))}
                                    </select>
                                </div>
                            )}

                            <div>
                                <label className="block font-semibold mb-1 text-[#0f172a] dark:text-[#f8fafc]">No. Urut Partai</label>
                                <input
                                    type="number"
                                    placeholder="Contoh: 1"
                                    value={newPartaiNomor}
                                    onChange={(e) => setNewPartaiNomor(e.target.value)}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 outline-none focus:border-[#bb152c]"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block font-semibold mb-1 text-[#0f172a] dark:text-[#f8fafc]">Nama Partai</label>
                                <input
                                    type="text"
                                    placeholder="Nama Partai"
                                    value={newPartaiNama}
                                    onChange={(e) => setNewPartaiNama(e.target.value)}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 outline-none focus:border-[#bb152c]"
                                    required
                                />
                            </div>

                            <div className="pt-2 flex justify-end gap-2">
                                <button type="button" onClick={() => setShowAddPartaiModal(false)} className="px-4 py-2 rounded-xl bg-[#f1f5f9] dark:bg-[#1f2937] text-[#475569] dark:text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc] text-xs font-semibold cursor-pointer">Batal</button>
                                <button type="submit" className="px-4 py-2 rounded-xl bg-[#bb152c] text-white text-xs font-semibold cursor-pointer">Tambah Partai</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
