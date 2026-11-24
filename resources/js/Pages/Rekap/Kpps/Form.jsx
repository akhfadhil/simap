import React, { useState, useMemo } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import {
    ArrowLeft,
    Save,
    CheckCircle2,
    AlertCircle,
    Download,
    Users,
    FileText,
    Check,
    ChevronDown,
    ChevronUp,
    Vote,
    ShieldAlert,
    HelpCircle
} from 'lucide-react';

export default function RekapKppsForm({
    tps,
    jenis,
    jenisLabel,
    rekap,
    data: masterData = {},
    isAdminRekapEdit = false,
    canEditRekap = false,
    backUrl = '/rekap',
}) {
    const isFinal = rekap && rekap.status === 'final';
    const isCandidateElection = ['ppwp', 'gubernur', 'bupati', 'dpd'].includes(jenis);
    const readOnly = (!canEditRekap || (isFinal && !isAdminRekapEdit));

    // Initialize Form State
    const { data, setData, post, processing, errors } = useForm({
        dpt_lk: rekap?.dpt_lk ?? 0,
        dpt_pr: rekap?.dpt_pr ?? 0,
        pengguna_dpt_lk: rekap?.pengguna_dpt_lk ?? 0,
        pengguna_dpt_pr: rekap?.pengguna_dpt_pr ?? 0,
        pengguna_dptb_lk: rekap?.pengguna_dptb_lk ?? 0,
        pengguna_dptb_pr: rekap?.pengguna_dptb_pr ?? 0,
        pengguna_dpk_lk: rekap?.pengguna_dpk_lk ?? 0,
        pengguna_dpk_pr: rekap?.pengguna_dpk_pr ?? 0,
        ss_diterima: rekap?.ss_diterima ?? 0,
        ss_rusak: rekap?.ss_rusak ?? 0,
        disabilitas_lk: rekap?.disabilitas_lk ?? 0,
        disabilitas_pr: rekap?.disabilitas_pr ?? 0,
        suara_tidak_sah: rekap?.suara_tidak_sah ?? 0,
        suara: masterData.suara || {},
        suara_partai: masterData.suara_partai || {},
        suara_caleg: masterData.suara_caleg || {},
        finalisasi: 0,
    });

    const [collapsedParties, setCollapsedParties] = useState({});
    const [finalConfirmModalOpen, setFinalConfirmModalOpen] = useState(false);

    // Number helpers
    const toInt = (val) => {
        const num = parseInt(val, 10);
        return isNaN(num) ? 0 : Math.max(0, num);
    };

    const formatNumber = (num) => (num || 0).toLocaleString('id-ID');

    // Section I Calculations
    const dptJml = toInt(data.dpt_lk) + toInt(data.dpt_pr);
    const penggunaDptJml = toInt(data.pengguna_dpt_lk) + toInt(data.pengguna_dpt_pr);
    const penggunaDptbJml = toInt(data.pengguna_dptb_lk) + toInt(data.pengguna_dptb_pr);
    const penggunaDpkJml = toInt(data.pengguna_dpk_lk) + toInt(data.pengguna_dpk_pr);
    const totalPenggunaHakPilih = penggunaDptJml + penggunaDptbJml + penggunaDpkJml;

    // Section II Calculations
    const ssDigunakan = totalPenggunaHakPilih;
    const ssSisa = Math.max(0, toInt(data.ss_diterima) - ssDigunakan - toInt(data.ss_rusak));

    // Section III Calculations
    const disabilitasJml = toInt(data.disabilitas_lk) + toInt(data.disabilitas_pr);

    // Section IV Calculations
    const { totalSuaraSah, subtotalPerPartai } = useMemo(() => {
        let sah = 0;
        const subPerPartai = {};

        if (isCandidateElection) {
            Object.values(data.suara || {}).forEach((val) => {
                sah += toInt(val);
            });
        } else {
            (masterData.partais || []).forEach((partai) => {
                let partSub = toInt(data.suara_partai?.[partai.id]);
                (partai.calegs || []).forEach((caleg) => {
                    partSub += toInt(data.suara_caleg?.[caleg.id]);
                });
                subPerPartai[partai.id] = partSub;
                sah += partSub;
            });
        }

        return { totalSuaraSah: sah, subtotalPerPartai: subPerPartai };
    }, [data.suara, data.suara_partai, data.suara_caleg, isCandidateElection, masterData.partais]);

    // Section V Calculations
    const suaraTidakSah = toInt(data.suara_tidak_sah);
    const totalSuaraSahDanTidakSah = totalSuaraSah + suaraTidakSah;
    const isSynchronized = ssDigunakan > 0 && totalSuaraSahDanTidakSah === ssDigunakan;
    const hasDiscrepancy = ssDigunakan > 0 && totalSuaraSahDanTidakSah !== ssDigunakan;

    const togglePartyCollapse = (partaiId) => {
        setCollapsedParties((prev) => ({
            ...prev,
            [partaiId]: !prev[partaiId],
        }));
    };

    const handleCandidateVoteChange = (calonId, value) => {
        setData('suara', {
            ...data.suara,
            [calonId]: value === '' ? '' : Math.max(0, parseInt(value, 10) || 0),
        });
    };

    const handlePartyVoteChange = (partaiId, value) => {
        setData('suara_partai', {
            ...data.suara_partai,
            [partaiId]: value === '' ? '' : Math.max(0, parseInt(value, 10) || 0),
        });
    };

    const handleCalegVoteChange = (calegId, value) => {
        setData('suara_caleg', {
            ...data.suara_caleg,
            [calegId]: value === '' ? '' : Math.max(0, parseInt(value, 10) || 0),
        });
    };

    const handleSubmit = (finalisasiFlag = 0) => {
        // Set finalisasi flag and post
        setData('finalisasi', finalisasiFlag);
        router.post(`/rekap/${jenis}`, {
            ...data,
            finalisasi: finalisasiFlag,
        }, {
            onSuccess: () => {
                setFinalConfirmModalOpen(false);
            },
        });
    };

    return (
        <AdminLayout title={`Formulir C-Hasil: ${jenisLabel}`}>
            <Head title={`Formulir C-Hasil: ${jenisLabel}`} />

            <div className="space-y-6 max-w-5xl mx-auto">
                {/* Header Action Bar */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <Link
                        href={backUrl}
                        className="inline-flex items-center gap-2 text-xs font-semibold text-[#475569] dark:text-[#94a3b8] hover:text-[#bb152c] transition w-fit"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        <span>Kembali ke Daftar Rekap</span>
                    </Link>

                    <div className="flex items-center gap-2">
                        {rekap && (
                            <a
                                href={`/rekap/${jenis}/export`}
                                className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-[#151f32] text-[#0f172a] dark:text-[#f8fafc] border border-[#e2e8f0] dark:border-[#1e293b] hover:bg-slate-50 transition shadow-2xs"
                            >
                                <Download className="w-3.5 h-3.5" />
                                <span>Export Excel</span>
                            </a>
                        )}

                        {isFinal ? (
                            <span className="text-xs uppercase font-bold tracking-wider px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800 flex items-center gap-1.5">
                                <Check className="w-4 h-4" />
                                <span>Sudah Difinalisasi</span>
                            </span>
                        ) : (
                            <span className="text-xs uppercase font-bold tracking-wider px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-300 dark:border-amber-800">
                                Mode Input / Draft
                            </span>
                        )}
                    </div>
                </div>

                {/* Banner Statuses */}
                {isAdminRekapEdit && (
                    <div className="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 rounded-2xl p-4 flex items-center gap-3">
                        <ShieldAlert className="w-5 h-5 text-red-600 dark:text-red-400 shrink-0" />
                        <p className="text-xs font-semibold text-red-800 dark:text-red-300">
                            Mode koreksi admin sementara. Perubahan ini langsung memperbarui data resmi suara TPS ini.
                        </p>
                    </div>
                )}

                {!canEditRekap && (
                    <div className="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 rounded-2xl p-4 flex items-center gap-3">
                        <AlertCircle className="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" />
                        <p className="text-xs font-semibold text-amber-800 dark:text-amber-300">
                            Mode lihat saja. Rekapitulasi perolehan suara hanya dapat diubah oleh KPPS pemilik TPS bersangkutan.
                        </p>
                    </div>
                )}

                {/* Main Card Header */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-sm">
                    <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md">
                        Formulir C-Hasil Resmi
                    </span>
                    <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                        {jenisLabel}
                    </h1>
                    <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                        {tps?.nama} · Desa {tps?.desa?.nama} · Kecamatan {tps?.desa?.kecamatan?.nama}
                        {tps?.desa?.kecamatan?.dapil?.nama && ` · Dapil: ${tps.desa.kecamatan.dapil.nama}`}
                    </p>
                </div>

                {/* FORM CONTAINER */}
                <form onSubmit={(e) => { e.preventDefault(); handleSubmit(0); }} className="space-y-6">

                    {/* ══ SECTION I: DATA PEMILIH & PENGGUNA HAK PILIH ══ */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-xs overflow-hidden">
                        <div className="px-6 py-4 bg-[#f8fafc] dark:bg-[#1e293b]/50 border-b border-[#f1f5f9] dark:border-[#1e293b] flex items-center justify-between">
                            <h2 className="text-xs font-bold uppercase tracking-wider text-[#0f172a] dark:text-[#f8fafc]">
                                I. Data Pemilih dan Pengguna Hak Pilih
                            </h2>
                            <span className="text-[11px] font-semibold text-[#bb152c] dark:text-red-400">
                                Total Pengguna: {formatNumber(totalPenggunaHakPilih)} Jiwa
                            </span>
                        </div>

                        <div className="p-6 space-y-4">
                            {[
                                {
                                    title: 'Daftar Pemilih Tetap (DPT)',
                                    lkKey: 'dpt_lk',
                                    prKey: 'dpt_pr',
                                    jml: dptJml,
                                },
                                {
                                    title: 'Pengguna Hak Pilih DPT',
                                    lkKey: 'pengguna_dpt_lk',
                                    prKey: 'pengguna_dpt_pr',
                                    jml: penggunaDptJml,
                                },
                                {
                                    title: 'Pengguna Hak Pilih DPTb (Pindahan)',
                                    lkKey: 'pengguna_dptb_lk',
                                    prKey: 'pengguna_dptb_pr',
                                    jml: penggunaDptbJml,
                                },
                                {
                                    title: 'Pengguna Hak Pilih DPK (Khusus / KTP-el)',
                                    lkKey: 'pengguna_dpk_lk',
                                    prKey: 'pengguna_dpk_pr',
                                    jml: penggunaDpkJml,
                                },
                            ].map((row, idx) => (
                                <div
                                    key={idx}
                                    className="grid grid-cols-1 md:grid-cols-12 items-center gap-3 p-3 rounded-xl bg-[#f8fafc] dark:bg-[#0b0f19]/50 border border-[#f1f5f9] dark:border-[#1e293b]"
                                >
                                    <div className="md:col-span-5">
                                        <p className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                            {row.title}
                                        </p>
                                    </div>
                                    <div className="md:col-span-3">
                                        <label className="block text-[10px] font-semibold text-[#94a3b8] mb-1">
                                            Laki-Laki (LK)
                                        </label>
                                        <input
                                            type="number"
                                            min="0"
                                            disabled={readOnly}
                                            value={data[row.lkKey]}
                                            onChange={(e) => setData(row.lkKey, e.target.value)}
                                            className="w-full bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] px-3 py-1.5 text-xs rounded-xl outline-none focus:border-[#bb152c] disabled:opacity-60 text-right font-mono"
                                        />
                                    </div>
                                    <div className="md:col-span-3">
                                        <label className="block text-[10px] font-semibold text-[#94a3b8] mb-1">
                                            Perempuan (PR)
                                        </label>
                                        <input
                                            type="number"
                                            min="0"
                                            disabled={readOnly}
                                            value={data[row.prKey]}
                                            onChange={(e) => setData(row.prKey, e.target.value)}
                                            className="w-full bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] px-3 py-1.5 text-xs rounded-xl outline-none focus:border-[#bb152c] disabled:opacity-60 text-right font-mono"
                                        />
                                    </div>
                                    <div className="md:col-span-1 text-right">
                                        <label className="block text-[10px] font-semibold text-[#94a3b8] mb-1">
                                            JML
                                        </label>
                                        <span className="inline-block px-2.5 py-1.5 text-xs font-bold font-mono text-[#0f172a] dark:text-[#f8fafc] bg-slate-200/60 dark:bg-slate-800 rounded-xl w-full text-right">
                                            {formatNumber(row.jml)}
                                        </span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* ══ SECTION II: DATA PENGGUNAAN SURAT SUARA ══ */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-xs overflow-hidden">
                        <div className="px-6 py-4 bg-[#f8fafc] dark:bg-[#1e293b]/50 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <h2 className="text-xs font-bold uppercase tracking-wider text-[#0f172a] dark:text-[#f8fafc]">
                                II. Data Penggunaan Surat Suara
                            </h2>
                        </div>

                        <div className="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                                    Surat suara diterima (termasuk cadangan 2%)
                                </label>
                                <input
                                    type="number"
                                    min="0"
                                    disabled={readOnly}
                                    value={data.ss_diterima}
                                    onChange={(e) => setData('ss_diterima', e.target.value)}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] px-3.5 py-2 text-xs rounded-xl outline-none focus:border-[#bb152c] disabled:opacity-60 font-mono"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                                    Surat suara rusak / keliru coblos
                                </label>
                                <input
                                    type="number"
                                    min="0"
                                    disabled={readOnly}
                                    value={data.ss_rusak}
                                    onChange={(e) => setData('ss_rusak', e.target.value)}
                                    className="w-full bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] px-3.5 py-2 text-xs rounded-xl outline-none focus:border-[#bb152c] disabled:opacity-60 font-mono"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                                    Surat suara digunakan
                                    <span className="text-[10px] text-[#94a3b8] font-normal block sm:inline sm:ml-1">
                                        (otomatis mengikuti total pengguna hak pilih)
                                    </span>
                                </label>
                                <input
                                    type="number"
                                    readOnly
                                    value={ssDigunakan}
                                    className="w-full bg-slate-100 dark:bg-[#1e293b] border border-slate-200 dark:border-slate-800 text-[#0f172a] dark:text-[#f8fafc] px-3.5 py-2 text-xs rounded-xl font-bold font-mono cursor-not-allowed"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-[#0f172a] dark:text-[#f8fafc] mb-1.5">
                                    Surat suara sisa / tidak digunakan
                                    <span className="text-[10px] text-[#94a3b8] font-normal block sm:inline sm:ml-1">
                                        (diterima - digunakan - rusak)
                                    </span>
                                </label>
                                <input
                                    type="number"
                                    readOnly
                                    value={ssSisa}
                                    className="w-full bg-slate-100 dark:bg-[#1e293b] border border-slate-200 dark:border-slate-800 text-[#0f172a] dark:text-[#f8fafc] px-3.5 py-2 text-xs rounded-xl font-bold font-mono cursor-not-allowed"
                                />
                            </div>
                        </div>
                    </div>

                    {/* ══ SECTION III: DATA PEMILIH DISABILITAS ══ */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-xs overflow-hidden">
                        <div className="px-6 py-4 bg-[#f8fafc] dark:bg-[#1e293b]/50 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <h2 className="text-xs font-bold uppercase tracking-wider text-[#0f172a] dark:text-[#f8fafc]">
                                III. Data Pemilih Disabilitas
                            </h2>
                        </div>

                        <div className="p-6">
                            <div className="grid grid-cols-1 md:grid-cols-12 items-center gap-3 p-3 rounded-xl bg-[#f8fafc] dark:bg-[#0b0f19]/50 border border-[#f1f5f9] dark:border-[#1e293b]">
                                <div className="md:col-span-5">
                                    <p className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                        Pemilih Disabilitas yang Menggunakan Hak Pilih
                                    </p>
                                </div>
                                <div className="md:col-span-3">
                                    <label className="block text-[10px] font-semibold text-[#94a3b8] mb-1">
                                        Laki-Laki (LK)
                                    </label>
                                    <input
                                        type="number"
                                        min="0"
                                        disabled={readOnly}
                                        value={data.disabilitas_lk}
                                        onChange={(e) => setData('disabilitas_lk', e.target.value)}
                                        className="w-full bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] px-3 py-1.5 text-xs rounded-xl outline-none focus:border-[#bb152c] disabled:opacity-60 text-right font-mono"
                                    />
                                </div>
                                <div className="md:col-span-3">
                                    <label className="block text-[10px] font-semibold text-[#94a3b8] mb-1">
                                        Perempuan (PR)
                                    </label>
                                    <input
                                        type="number"
                                        min="0"
                                        disabled={readOnly}
                                        value={data.disabilitas_pr}
                                        onChange={(e) => setData('disabilitas_pr', e.target.value)}
                                        className="w-full bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] px-3 py-1.5 text-xs rounded-xl outline-none focus:border-[#bb152c] disabled:opacity-60 text-right font-mono"
                                    />
                                </div>
                                <div className="md:col-span-1 text-right">
                                    <label className="block text-[10px] font-semibold text-[#94a3b8] mb-1">
                                        JML
                                    </label>
                                    <span className="inline-block px-2.5 py-1.5 text-xs font-bold font-mono text-[#0f172a] dark:text-[#f8fafc] bg-slate-200/60 dark:bg-slate-800 rounded-xl w-full text-right">
                                        {formatNumber(disabilitasJml)}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* ══ SECTION IV: PEROLEHAN SUARA (CALON / PARTAI) ══ */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-xs overflow-hidden">
                        <div className="px-6 py-4 bg-[#f8fafc] dark:bg-[#1e293b]/50 border-b border-[#f1f5f9] dark:border-[#1e293b] flex items-center justify-between">
                            <h2 className="text-xs font-bold uppercase tracking-wider text-[#0f172a] dark:text-[#f8fafc]">
                                IV. Rincian Perolehan Suara Sah
                            </h2>
                            <span className="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                Subtotal Suara Sah: {formatNumber(totalSuaraSah)}
                            </span>
                        </div>

                        <div className="p-6">
                            {/* Candidate elections (PPWP, Gubernur, Bupati, DPD) */}
                            {isCandidateElection ? (
                                <div className="space-y-3">
                                    {(masterData.calons || []).map((calon) => (
                                        <div
                                            key={calon.id}
                                            className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-[#f8fafc] dark:bg-[#0b0f19]/50 border border-[#f1f5f9] dark:border-[#1e293b] hover:border-slate-300 dark:hover:border-slate-700 transition"
                                        >
                                            <div className="flex items-center gap-3">
                                                <span className="w-9 h-9 rounded-xl bg-[#bb152c] text-white font-black text-sm flex items-center justify-center shrink-0">
                                                    {calon.nomor_urut}
                                                </span>
                                                <div>
                                                    <p className="text-xs uppercase font-bold tracking-wider text-slate-400">
                                                        Pasangan Calon #{calon.nomor_urut}
                                                    </p>
                                                    <h3 className="font-bold text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                                        {calon.nama_paslon || calon.nama_calon}
                                                    </h3>
                                                </div>
                                            </div>

                                            <div className="flex items-center gap-2 self-end sm:self-center">
                                                <label className="text-xs font-semibold text-[#475569] dark:text-[#94a3b8]">
                                                    Suara:
                                                </label>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    disabled={readOnly}
                                                    value={data.suara?.[calon.id] ?? 0}
                                                    onChange={(e) => handleCandidateVoteChange(calon.id, e.target.value)}
                                                    className="w-32 bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] px-3.5 py-2 text-sm rounded-xl outline-none focus:border-[#bb152c] font-bold font-mono text-right disabled:opacity-60"
                                                />
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                /* Party & Caleg elections (DPR RI, DPRD Prov, DPRD Kab) */
                                <div className="space-y-4">
                                    {(masterData.partais || []).map((partai) => {
                                        const isCollapsed = collapsedParties[partai.id];
                                        const pSubtotal = subtotalPerPartai[partai.id] || 0;

                                        return (
                                            <div
                                                key={partai.id}
                                                className="border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl overflow-hidden bg-white dark:bg-[#151f32]"
                                            >
                                                {/* Party Header Bar */}
                                                <div className="p-4 bg-[#f8fafc] dark:bg-[#0b0f19]/60 border-b border-[#f1f5f9] dark:border-[#1e293b] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                                    <div className="flex items-center gap-3">
                                                        <span className="w-8 h-8 rounded-lg bg-orange-500 text-white font-black text-xs flex items-center justify-center shrink-0">
                                                            {partai.nomor_urut}
                                                        </span>
                                                        <div>
                                                            <h3 className="font-bold text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                                                {partai.nama_partai}
                                                            </h3>
                                                            <p className="text-[10px] text-[#94a3b8]">
                                                                {(partai.calegs || []).length} Calon Legislatif terdaftar
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <div className="flex items-center gap-3">
                                                        <div className="flex items-center gap-2">
                                                            <label className="text-xs font-semibold text-[#475569] dark:text-[#94a3b8]">
                                                                Suara Partai:
                                                            </label>
                                                            <input
                                                                type="number"
                                                                min="0"
                                                                disabled={readOnly}
                                                                value={data.suara_partai?.[partai.id] ?? 0}
                                                                onChange={(e) => handlePartyVoteChange(partai.id, e.target.value)}
                                                                className="w-28 bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] px-3 py-1.5 text-xs rounded-xl outline-none focus:border-[#bb152c] font-bold font-mono text-right disabled:opacity-60"
                                                            />
                                                        </div>

                                                        <button
                                                            type="button"
                                                            onClick={() => togglePartyCollapse(partai.id)}
                                                            className="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-800 transition"
                                                            title={isCollapsed ? 'Tampilkan Caleg' : 'Sembunyikan Caleg'}
                                                        >
                                                            {isCollapsed ? <ChevronDown className="w-4 h-4" /> : <ChevronUp className="w-4 h-4" />}
                                                        </button>
                                                    </div>
                                                </div>

                                                {/* Caleg List (Collapsible) */}
                                                {!isCollapsed && (
                                                    <div className="p-4 space-y-2 bg-white dark:bg-[#151f32]">
                                                        {(partai.calegs || []).map((caleg) => (
                                                            <div
                                                                key={caleg.id}
                                                                className="flex items-center justify-between gap-3 p-2.5 rounded-xl hover:bg-[#f8fafc] dark:hover:bg-[#0b0f19]/40 border border-transparent hover:border-[#f1f5f9] dark:hover:border-[#1e293b] transition"
                                                            >
                                                                <div className="flex items-center gap-2.5">
                                                                    <span className="w-6 text-center text-xs font-bold text-slate-400">
                                                                        {caleg.nomor_urut}
                                                                    </span>
                                                                    <p className="text-xs font-medium text-[#0f172a] dark:text-[#f8fafc]">
                                                                        {caleg.nama_caleg}
                                                                    </p>
                                                                </div>

                                                                <input
                                                                    type="number"
                                                                    min="0"
                                                                    disabled={readOnly}
                                                                    value={data.suara_caleg?.[caleg.id] ?? 0}
                                                                    onChange={(e) => handleCalegVoteChange(caleg.id, e.target.value)}
                                                                    className="w-24 bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] px-3 py-1 text-xs rounded-xl outline-none focus:border-[#bb152c] font-mono text-right disabled:opacity-60"
                                                                />
                                                            </div>
                                                        ))}
                                                    </div>
                                                )}

                                                {/* Subtotal Footer */}
                                                <div className="px-5 py-3 bg-[#f8fafc] dark:bg-[#0b0f19]/40 border-t border-[#f1f5f9] dark:border-[#1e293b] flex items-center justify-between">
                                                    <span className="text-[11px] font-bold text-[#475569] dark:text-[#94a3b8] uppercase tracking-wider">
                                                        Total Suara Partai + Caleg:
                                                    </span>
                                                    <span className="text-xs font-bold font-mono text-orange-600 dark:text-orange-400">
                                                        {formatNumber(pSubtotal)} Suara
                                                    </span>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* ══ SECTION V: SUARA SAH & TIDAK SAH + VERIFIKASI ══ */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl shadow-xs overflow-hidden">
                        <div className="px-6 py-4 bg-[#f8fafc] dark:bg-[#1e293b]/50 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                            <h2 className="text-xs font-bold uppercase tracking-wider text-[#0f172a] dark:text-[#f8fafc]">
                                V. Rekapitulasi Suara Sah dan Tidak Sah
                            </h2>
                        </div>

                        <div className="p-6 space-y-4">
                            {/* Suara Sah */}
                            <div className="flex items-center justify-between gap-4 p-3 rounded-xl bg-[#f8fafc] dark:bg-[#0b0f19]/40 border border-[#f1f5f9] dark:border-[#1e293b]">
                                <div>
                                    <p className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                        Jumlah Seluruh Suara Sah
                                    </p>
                                    <p className="text-[10px] text-[#94a3b8]">
                                        (dihitung otomatis dari akumulasi perolehan suara di Section IV)
                                    </p>
                                </div>
                                <span className="text-sm font-bold font-mono text-emerald-600 dark:text-emerald-400 px-4 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800">
                                    {formatNumber(totalSuaraSah)}
                                </span>
                            </div>

                            {/* Suara Tidak Sah */}
                            <div className="flex items-center justify-between gap-4 p-3 rounded-xl bg-[#f8fafc] dark:bg-[#0b0f19]/40 border border-[#f1f5f9] dark:border-[#1e293b]">
                                <div>
                                    <p className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                        Jumlah Suara Tidak Sah
                                    </p>
                                    <p className="text-[10px] text-[#94a3b8]">
                                        Surat suara yang dicoblos tidak sah / tidak ada coblosan
                                    </p>
                                </div>
                                <input
                                    type="number"
                                    min="0"
                                    disabled={readOnly}
                                    value={data.suara_tidak_sah}
                                    onChange={(e) => setData('suara_tidak_sah', e.target.value)}
                                    className="w-36 bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-[#0f172a] dark:text-[#f8fafc] px-3.5 py-1.5 text-xs rounded-xl outline-none focus:border-[#bb152c] font-bold font-mono text-right disabled:opacity-60"
                                />
                            </div>

                            {/* Total Sah + Tidak Sah */}
                            <div className="flex items-center justify-between gap-4 p-4 rounded-xl bg-slate-100 dark:bg-[#1e293b]/70 border border-slate-200 dark:border-slate-800">
                                <div>
                                    <p className="text-sm font-extrabold text-[#0f172a] dark:text-[#f8fafc]">
                                        Jumlah Seluruh Suara Sah dan Tidak Sah
                                    </p>
                                    <p className="text-[10px] text-[#94a3b8]">
                                        Harus sama persis dengan Jumlah Pengguna Hak Pilih / Surat Suara Digunakan (Section II)
                                    </p>
                                </div>
                                <span className="text-base font-extrabold font-mono text-[#bb152c] dark:text-red-400">
                                    {formatNumber(totalSuaraSahDanTidakSah)}
                                </span>
                            </div>

                            {/* Validation Comparison Alert Box */}
                            {isSynchronized && (
                                <div className="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center gap-3">
                                    <CheckCircle2 className="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" />
                                    <div className="text-xs">
                                        <p className="font-bold text-emerald-800 dark:text-emerald-300">
                                            ✓ Data Suara Sinkron & Valid
                                        </p>
                                        <p className="text-emerald-700 dark:text-emerald-400 mt-0.5">
                                            Total perolehan suara ({totalSuaraSahDanTidakSah}) telah sesuai 100% dengan Surat Suara Digunakan ({ssDigunakan}).
                                        </p>
                                    </div>
                                </div>
                            )}

                            {hasDiscrepancy && (
                                <div className="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 flex items-center gap-3">
                                    <AlertCircle className="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" />
                                    <div className="text-xs">
                                        <p className="font-bold text-amber-800 dark:text-amber-300">
                                            ⚠ Terdapat Selisih Hitung
                                        </p>
                                        <p className="text-amber-700 dark:text-amber-400 mt-0.5">
                                            Total Suara Sah + Tidak Sah ({totalSuaraSahDanTidakSah}) berbeda dengan Surat Suara Digunakan ({ssDigunakan}). Selisih: {Math.abs(totalSuaraSahDanTidakSah - ssDigunakan)} suara.
                                        </p>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Bottom Action Buttons */}
                    <div className="pt-2">
                        {isAdminRekapEdit ? (
                            <button
                                type="button"
                                disabled={processing}
                                onClick={() => handleSubmit(0)}
                                className="w-full py-3.5 rounded-2xl text-xs font-bold text-white bg-red-600 hover:bg-red-700 transition shadow-lg shadow-red-600/20"
                            >
                                {processing ? 'Menyimpan...' : 'Simpan Perubahan Admin'}
                            </button>
                        ) : canEditRekap && !isFinal ? (
                            <div className="flex flex-col sm:flex-row gap-3">
                                <button
                                    type="button"
                                    disabled={processing}
                                    onClick={() => handleSubmit(0)}
                                    className="flex-1 py-3 rounded-2xl text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-xs flex items-center justify-center gap-2"
                                >
                                    <Save className="w-4 h-4" />
                                    <span>{processing ? 'Menyimpan...' : 'Simpan Sebagai Draft'}</span>
                                </button>
                                <button
                                    type="button"
                                    disabled={processing}
                                    onClick={() => setFinalConfirmModalOpen(true)}
                                    className="flex-1 py-3 rounded-2xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-2"
                                >
                                    <CheckCircle2 className="w-4 h-4" />
                                    <span>Simpan & Finalisasi Rekap</span>
                                </button>
                            </div>
                        ) : (
                            <div className="p-4 rounded-2xl bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] text-center text-xs text-[#94a3b8]">
                                Formulir C-Hasil ini telah difinalisasi dan dikunci resmi.
                            </div>
                        )}
                    </div>
                </form>
            </div>

            {/* Finalization Confirmation Modal */}
            {finalConfirmModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl w-full max-w-sm shadow-2xl p-6 animate-in fade-in zoom-in-95 duration-150">
                        <div className="flex items-start gap-3.5 mb-4">
                            <div className="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <CheckCircle2 className="w-5 h-5" />
                            </div>
                            <div>
                                <h3 className="font-bold text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                    Konfirmasi Finalisasi C-Hasil
                                </h3>
                                <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1 leading-relaxed">
                                    Data perolehan suara akan difinalisasi dan <strong className="text-emerald-600 dark:text-emerald-400">terkunci resmi</strong>. Pastikan semua angka perolehan suara sudah dicocokkan dengan Formulir C-Hasil fisik.
                                </p>
                            </div>
                        </div>

                        {hasDiscrepancy && (
                            <div className="mb-4 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-[11px] text-amber-800 dark:text-amber-300">
                                ⚠ Perhatian: Sistem mendeteksi adanya selisih antara Surat Suara Digunakan dengan Total Suara ({Math.abs(totalSuaraSahDanTidakSah - ssDigunakan)} suara).
                            </div>
                        )}

                        <div className="flex items-center gap-2 pt-2">
                            <button
                                type="button"
                                onClick={() => setFinalConfirmModalOpen(false)}
                                className="flex-1 py-2.5 rounded-xl text-xs font-bold text-[#475569] dark:text-[#94a3b8] hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                disabled={processing}
                                onClick={() => handleSubmit(1)}
                                className="flex-1 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-md shadow-emerald-600/20"
                            >
                                {processing ? 'Memproses...' : 'Ya, Finalisasi'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
