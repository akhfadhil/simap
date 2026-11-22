import React, { useState } from 'react';
import { Head, useForm, router, usePage } from '@inertiajs/react';
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
    Layers,
    AlertCircle
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
    const { errors } = usePage().props;
    const [activeTab, setActiveTab] = useState('pemilu_settings');

    // Global Custom Delete Modal State
    const [deleteModal, setDeleteModal] = useState({
        isOpen: false,
        title: '',
        message: '',
        url: '',
    });

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

    const handleDeletePpwp = (c) => {
        setDeleteModal({
            isOpen: true,
            title: 'Hapus Paslon PPWP',
            message: `Apakah Anda yakin ingin menghapus paslon #${c.nomor_urut} ${c.nama_paslon}?`,
            url: `/admin/setup/ppwp/${c.id}`
        });
    };

    const handleDeleteGub = (c) => {
        setDeleteModal({
            isOpen: true,
            title: 'Hapus Paslon Gubernur',
            message: `Apakah Anda yakin ingin menghapus paslon #${c.nomor_urut} ${c.nama_paslon}?`,
            url: `/admin/setup/gubernur/${c.id}`
        });
    };

    const handleDeleteBup = (c) => {
        setDeleteModal({
            isOpen: true,
            title: 'Hapus Paslon Bupati',
            message: `Apakah Anda yakin ingin menghapus paslon #${c.nomor_urut} ${c.nama_paslon}?`,
            url: `/admin/setup/bupati/${c.id}`
        });
    };

    const handleDeleteDpd = (c) => {
        setDeleteModal({
            isOpen: true,
            title: 'Hapus Calon DPD RI',
            message: `Apakah Anda yakin ingin menghapus calon DPD #${c.nomor_urut} ${c.nama_calon}?`,
            url: `/admin/setup/dpd/${c.id}`
        });
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

    const handleDeleteDapil = (d) => {
        setDeleteModal({
            isOpen: true,
            title: 'Hapus Daerah Pemilihan (Dapil)',
            message: `Apakah Anda yakin ingin menghapus ${d.nama}? Kecamatan yang terhubung akan dilepas dari dapil ini.`,
            url: `/admin/setup/dapil/${d.id}`
        });
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
        warna_utama: '#bb152c',
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
            warna_utama: p.warna_utama || '#bb152c',
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

    const handleDeletePartai = (p) => {
        setDeleteModal({
            isOpen: true,
            title: 'Hapus Partai',
            message: `Apakah Anda yakin ingin menghapus partai #${p.nomor_urut} ${p.nama_partai} beserta seluruh caleg yang ada di dalamnya?`,
            url: `/admin/setup/partai/${p.id}`
        });
    };

    // Inline Add Caleg State
    const [newCalegMap, setNewCalegMap] = useState({});
    const [calegErrors, setCalegErrors] = useState({});

    const handleAddCaleg = (partaiId) => {
        const row = newCalegMap[partaiId];
        if (!row?.nomor_urut || !row?.nama_caleg) {
            setCalegErrors(prev => ({ ...prev, [partaiId]: 'Nomor urut dan nama caleg wajib diisi!' }));
            return;
        }
        setCalegErrors(prev => ({ ...prev, [partaiId]: null }));
        router.post(`/admin/setup/partai/${partaiId}/caleg`, row, {
            onSuccess: () => {
                setNewCalegMap({ ...newCalegMap, [partaiId]: { nomor_urut: '', nama_caleg: '' } });
                setCalegErrors(prev => ({ ...prev, [partaiId]: null }));
            },
            preserveScroll: true
        });
    };

    const handleDeleteCaleg = (c) => {
        setDeleteModal({
            isOpen: true,
            title: 'Hapus Caleg',
            message: `Apakah Anda yakin ingin menghapus caleg #${c.nomor_urut} ${c.nama_caleg}?`,
            url: `/admin/setup/caleg/${c.id}`
        });
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
                                onClick={() => handleDeletePartai(p)}
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
                                            onClick={() => handleDeleteCaleg(c)}
                                            className="text-[#94a3b8] hover:text-red-600 dark:hover:text-red-400 transition ml-2 cursor-pointer"
                                            title="Hapus Caleg"
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
                        <div className="pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b] space-y-1">
                            <div className="flex gap-1.5">
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
                                    title="Tambah Caleg"
                                >
                                    +
                                </button>
                            </div>
                            {calegErrors[p.id] && (
                                <p className="text-red-500 text-[10px] font-medium">{calegErrors[p.id]}</p>
                            )}
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
                                        <button onClick={() => handleDeletePpwp(c)} className="text-[#94a3b8] hover:text-red-600 dark:hover:text-red-400 transition cursor-pointer" title="Hapus Paslon PPWP"><Trash2 className="w-4 h-4" /></button>
                                    </div>
                                ))}
                            </div>
                            <form onSubmit={handleAddPpwp} className="space-y-3 pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                                <div className="flex gap-2">
                                    <input type="number" placeholder="No" value={ppwpRows[0].nomor_urut} onChange={(e) => setPpwpRows([{ ...ppwpRows[0], nomor_urut: e.target.value }])} className="w-16 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]" required />
                                    <input type="text" placeholder="Nama Paslon PPWP" value={ppwpRows[0].nama_paslon} onChange={(e) => setPpwpRows([{ ...ppwpRows[0], nama_paslon: e.target.value }])} className="flex-1 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]" required />
                                </div>
                                {errors?.calons && <p className="text-red-500 text-xs font-medium">{errors.calons}</p>}
                                <button type="submit" className="w-full bg-[#0f172a] dark:bg-[#1f2937] hover:bg-[#334151] dark:hover:bg-[#374151] text-white py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 cursor-pointer transition"><Plus className="w-3.5 h-3.5" /> Tambah Paslon PPWP</button>
                            </form>
                        </div>

                        {/* Gubernur */}
                        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-sm space-y-4">
                            <h3 className="font-bold font-heading text-base text-[#0f172a] dark:text-[#f8fafc]">Paslon Gubernur</h3>
                            <div className="space-y-2">
                                {gubernurCalons?.map(c => (
                                    <div key={c.id} className="flex items-center justify-between bg-[#f8fafc] dark:bg-[#0b0f19] p-3 rounded-xl border border-[#e2e8f0] dark:border-[#1e293b] text-xs">
                                        <div><span className="font-bold text-[#bb152c] dark:text-red-400 mr-2">#{c.nomor_urut}</span><span className="text-[#0f172a] dark:text-[#f8fafc] font-medium">{c.nama_paslon}</span></div>
                                        <button onClick={() => handleDeleteGub(c)} className="text-[#94a3b8] hover:text-red-600 dark:hover:text-red-400 transition cursor-pointer" title="Hapus Paslon Gubernur"><Trash2 className="w-4 h-4" /></button>
                                    </div>
                                ))}
                            </div>
                            <form onSubmit={handleAddGub} className="space-y-3 pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                                <div className="flex gap-2">
                                    <input type="number" placeholder="No" value={gubRows[0].nomor_urut} onChange={(e) => setGubRows([{ ...gubRows[0], nomor_urut: e.target.value }])} className="w-16 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]" required />
                                    <input type="text" placeholder="Nama Paslon Gubernur" value={gubRows[0].nama_paslon} onChange={(e) => setGubRows([{ ...gubRows[0], nama_paslon: e.target.value }])} className="flex-1 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]" required />
                                </div>
                                {errors?.calons && <p className="text-red-500 text-xs font-medium">{errors.calons}</p>}
                                <button type="submit" className="w-full bg-[#0f172a] dark:bg-[#1f2937] hover:bg-[#334151] dark:hover:bg-[#374151] text-white py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 cursor-pointer transition"><Plus className="w-3.5 h-3.5" /> Tambah Paslon Gubernur</button>
                            </form>
                        </div>

                        {/* Bupati */}
                        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-sm space-y-4">
                            <h3 className="font-bold font-heading text-base text-[#0f172a] dark:text-[#f8fafc]">Paslon Bupati</h3>
                            <div className="space-y-2">
                                {bupatiCalons?.map(c => (
                                    <div key={c.id} className="flex items-center justify-between bg-[#f8fafc] dark:bg-[#0b0f19] p-3 rounded-xl border border-[#e2e8f0] dark:border-[#1e293b] text-xs">
                                        <div><span className="font-bold text-[#bb152c] dark:text-red-400 mr-2">#{c.nomor_urut}</span><span className="text-[#0f172a] dark:text-[#f8fafc] font-medium">{c.nama_paslon}</span></div>
                                        <button onClick={() => handleDeleteBup(c)} className="text-[#94a3b8] hover:text-red-600 dark:hover:text-red-400 transition cursor-pointer" title="Hapus Paslon Bupati"><Trash2 className="w-4 h-4" /></button>
                                    </div>
                                ))}
                            </div>
                            <form onSubmit={handleAddBup} className="space-y-3 pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                                <div className="flex gap-2">
                                    <input type="number" placeholder="No" value={bupRows[0].nomor_urut} onChange={(e) => setBupRows([{ ...bupRows[0], nomor_urut: e.target.value }])} className="w-16 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]" required />
                                    <input type="text" placeholder="Nama Paslon Bupati" value={bupRows[0].nama_paslon} onChange={(e) => setBupRows([{ ...bupRows[0], nama_paslon: e.target.value }])} className="flex-1 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]" required />
                                </div>
                                {errors?.calons && <p className="text-red-500 text-xs font-medium">{errors.calons}</p>}
                                <button type="submit" className="w-full bg-[#0f172a] dark:bg-[#1f2937] hover:bg-[#334151] dark:hover:bg-[#374151] text-white py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 cursor-pointer transition"><Plus className="w-3.5 h-3.5" /> Tambah Paslon Bupati</button>
                            </form>
                        </div>

                        {/* DPD */}
                        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-sm space-y-4">
                            <h3 className="font-bold font-heading text-base text-[#0f172a] dark:text-[#f8fafc]">Calon DPD RI</h3>
                            <div className="space-y-2">
                                {dpdCalons?.map(c => (
                                    <div key={c.id} className="flex items-center justify-between bg-[#f8fafc] dark:bg-[#0b0f19] p-3 rounded-xl border border-[#e2e8f0] dark:border-[#1e293b] text-xs">
                                        <div><span className="font-bold text-[#bb152c] dark:text-red-400 mr-2">#{c.nomor_urut}</span><span className="text-[#0f172a] dark:text-[#f8fafc] font-medium">{c.nama_calon}</span></div>
                                        <button onClick={() => handleDeleteDpd(c)} className="text-[#94a3b8] hover:text-red-600 dark:hover:text-red-400 transition cursor-pointer" title="Hapus Calon DPD"><Trash2 className="w-4 h-4" /></button>
                                    </div>
                                ))}
                            </div>
                            <form onSubmit={handleAddDpd} className="space-y-3 pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                                <div className="flex gap-2">
                                    <input type="number" placeholder="No" value={dpdRows[0].nomor_urut} onChange={(e) => setDpdRows([{ ...dpdRows[0], nomor_urut: e.target.value }])} className="w-16 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]" required />
                                    <input type="text" placeholder="Nama Calon DPD" value={dpdRows[0].nama_calon} onChange={(e) => setDpdRows([{ ...dpdRows[0], nama_calon: e.target.value }])} className="flex-1 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]" required />
                                </div>
                                {errors?.calons && <p className="text-red-500 text-xs font-medium">{errors.calons}</p>}
                                <button type="submit" className="w-full bg-[#0f172a] dark:bg-[#1f2937] hover:bg-[#334151] dark:hover:bg-[#374151] text-white py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 cursor-pointer transition"><Plus className="w-3.5 h-3.5" /> Tambah Calon DPD</button>
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
                                        <button
                                            onClick={() => handleDeleteDapil(d)}
                                            className="text-[#94a3b8] hover:text-red-600 dark:hover:text-red-400 transition cursor-pointer"
                                            title="Hapus Dapil"
                                        >
                                            <Trash2 className="w-4 h-4" />
                                        </button>
                                    </div>
                                ))}
                            </div>
                            <form onSubmit={handleAddDapil} className="space-y-2 pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                                <div className="flex gap-2">
                                    <input
                                        type="text"
                                        placeholder="Contoh: Dapil 1"
                                        value={newDapilNama}
                                        onChange={(e) => setNewDapilNama(e.target.value)}
                                        className="flex-1 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#bb152c]"
                                        required
                                    />
                                    <button type="submit" className="bg-[#bb152c] hover:bg-[#991124] text-white px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-1 cursor-pointer transition">
                                        <Plus className="w-4 h-4" /> Tambah
                                    </button>
                                </div>
                                {errors?.nama && <p className="text-red-500 text-xs font-medium">{errors.nama}</p>}
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
                                {errors?.kecamatan_dapil && <p className="text-red-500 text-xs font-medium">{errors.kecamatan_dapil}</p>}
                                <button type="submit" className="w-full bg-[#0f172a] dark:bg-[#1f2937] hover:bg-[#334151] text-white py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 cursor-pointer transition">
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
                                onClick={() => {
                                    if (tab4SubTab === 'dprd_kab') {
                                        setNewPartaiJenis('dprd_kab');
                                        setNewPartaiDapilId(selectedDapilKabId || dapils?.[0]?.id || '');
                                    } else if (tab4SubTab === 'dprd_prov') {
                                        setNewPartaiJenis('dprd_prov');
                                    } else {
                                        setNewPartaiJenis('dpr_ri');
                                    }
                                    setShowAddPartaiModal(true);
                                }}
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
                                            <div className="flex items-center gap-2.5 truncate">
                                                <span
                                                    className="font-extrabold text-xs px-2 py-0.5 rounded border"
                                                    style={{
                                                        backgroundColor: p.warna_utama ? `${p.warna_utama}18` : undefined,
                                                        color: p.warna_utama || '#bb152c',
                                                        borderColor: p.warna_utama ? `${p.warna_utama}35` : undefined
                                                    }}
                                                >
                                                    #{p.nomor_urut_aktif}
                                                </span>
                                                <div className="truncate">
                                                    <span className="font-bold text-[#0f172a] dark:text-[#f8fafc] block truncate" title={p.nama}>
                                                        {p.nama_singkat}
                                                    </span>
                                                    {p.warna_utama && (
                                                        <div className="flex items-center gap-1.5 mt-0.5">
                                                            <span className="w-2 h-2 rounded-full inline-block" style={{ backgroundColor: p.warna_utama }}></span>
                                                            <span className="text-[10px] text-[#64748b] dark:text-[#94a3b8] font-mono uppercase">{p.warna_utama}</span>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                            <button
                                                onClick={() => handleOpenEditProfile(p)}
                                                className="text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc] p-1.5 rounded-lg hover:bg-white dark:hover:bg-[#1e293b] transition cursor-pointer"
                                                title="Edit Profil Partai"
                                            >
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
                            <div>
                                <label className="block font-semibold mb-1 text-[#0f172a] dark:text-[#f8fafc]">Warna Utama Partai</label>
                                <div className="flex items-center gap-2">
                                    <input
                                        type="color"
                                        value={profileForm.data.warna_utama || '#bb152c'}
                                        onChange={(e) => profileForm.setData('warna_utama', e.target.value)}
                                        className="w-10 h-9 p-0.5 rounded-lg border border-[#e2e8f0] dark:border-[#1e293b] bg-transparent cursor-pointer"
                                    />
                                    <input
                                        type="text"
                                        value={profileForm.data.warna_utama || ''}
                                        onChange={(e) => profileForm.setData('warna_utama', e.target.value)}
                                        placeholder="#bb152c"
                                        className="flex-1 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] rounded-xl px-3 py-2 outline-none focus:border-[#bb152c] font-mono text-xs uppercase"
                                    />
                                </div>
                            </div>
                            <div className="pt-2 flex justify-end gap-2">
                                <button type="button" onClick={() => setEditingProfile(null)} className="px-4 py-2 rounded-xl bg-[#f1f5f9] dark:bg-[#1f2937] text-[#475569] dark:text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc] text-xs font-semibold cursor-pointer">Batal</button>
                                <button type="submit" disabled={profileForm.processing} className="px-4 py-2 rounded-xl bg-[#bb152c] hover:bg-[#991124] text-white text-xs font-semibold cursor-pointer">Simpan</button>
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

                            {errors?.partais && <p className="text-red-500 text-xs font-medium">{errors.partais}</p>}

                            <div className="pt-2 flex justify-end gap-2">
                                <button type="button" onClick={() => setShowAddPartaiModal(false)} className="px-4 py-2 rounded-xl bg-[#f1f5f9] dark:bg-[#1f2937] text-[#475569] dark:text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc] text-xs font-semibold cursor-pointer">Batal</button>
                                <button type="submit" className="px-4 py-2 rounded-xl bg-[#bb152c] hover:bg-[#991124] text-white text-xs font-semibold cursor-pointer transition">Tambah Partai</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Custom Global Delete Modal */}
            {deleteModal.isOpen && (
                <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-md p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150 space-y-4">
                        <div className="flex items-center justify-between pb-3 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <div className="flex items-center gap-2.5 text-red-600 dark:text-red-400">
                                <Trash2 className="w-5 h-5" />
                                <h3 className="font-bold font-heading text-lg text-[#0f172a] dark:text-[#f8fafc]">
                                    {deleteModal.title}
                                </h3>
                            </div>
                            <button
                                onClick={() => setDeleteModal({ isOpen: false, title: '', message: '', url: '' })}
                                className="text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc] p-1 cursor-pointer transition"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] leading-relaxed">
                            {deleteModal.message}
                        </p>
                        <div className="pt-2 flex items-center justify-end gap-2.5">
                            <button
                                type="button"
                                onClick={() => setDeleteModal({ isOpen: false, title: '', message: '', url: '' })}
                                className="h-9 px-4 rounded-xl text-xs font-semibold bg-[#f1f5f9] dark:bg-[#1e293b] text-[#475569] dark:text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc] transition cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                onClick={() => {
                                    router.delete(deleteModal.url, {
                                        preserveScroll: true,
                                        onFinish: () => setDeleteModal({ isOpen: false, title: '', message: '', url: '' }),
                                    });
                                }}
                                className="h-9 px-4 rounded-xl text-xs font-semibold bg-[#bb152c] hover:bg-[#991124] text-white transition cursor-pointer shadow-md shadow-[#bb152c]/20"
                            >
                                Ya, Hapus
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
