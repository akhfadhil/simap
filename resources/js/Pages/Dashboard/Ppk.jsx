import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import {
    Vote,
    CheckCircle2,
    Clock,
    XCircle,
    ArrowRight,
    Trophy,
    Building2,
    Users,
    Star,
    MapPin,
    FolderOpen,
    Upload,
    BarChart2,
    FileText
} from 'lucide-react';

// Sub-component for Candidate Elections (PPWP, Gubernur, Bupati)
function CandidateWidget({ widget }) {
    const formatNumber = (num) => (num || 0).toLocaleString('id-ID');
    const winner = widget.winner;
    const hasVotes = (widget.total_suara || 0) > 0 && (winner?.suara || 0) > 0;

    return (
        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-xs space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                <div className="flex items-center gap-2.5">
                    <Vote className="w-5 h-5 text-[#bb152c]" />
                    <h3 className="font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] text-base">
                        {widget.title}
                    </h3>
                </div>
                <span className="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-md bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400">
                    {widget.jenis?.toUpperCase()}
                </span>
            </div>

            {/* Unggul Sementara Banner */}
            {hasVotes ? (
                <div className="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/20 rounded-xl p-4 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold shrink-0 shadow-sm">
                            <Trophy className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                                Unggul Sementara (#1)
                            </span>
                            <p className="font-bold text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                {winner.nama}
                            </p>
                        </div>
                    </div>
                    <div className="text-right">
                        <p className="text-sm font-extrabold font-mono text-[#bb152c] dark:text-red-400">
                            {formatNumber(winner.suara)}
                        </p>
                        <p className="text-[11px] font-bold text-[#475569] dark:text-[#94a3b8]">{winner.persen}% Suara</p>
                    </div>
                </div>
            ) : (
                <div className="bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl p-4 text-center text-xs text-[#94a3b8]">
                    Belum ada data suara masuk di kecamatan ini.
                </div>
            )}

            {/* Perolehan Suara Paslon List */}
            <div className="space-y-3 pt-1">
                <p className="text-[11px] font-bold uppercase tracking-wider text-[#94a3b8]">
                    Perolehan Suara Seluruh Paslon:
                </p>
                {widget.rows?.map((row, idx) => (
                    <div key={idx} className="bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl p-3.5 space-y-2">
                        <div className="flex items-center justify-between min-w-0">
                            <div className="flex items-center gap-2.5 min-w-0">
                                <span className="w-6 h-6 rounded-full bg-[#f1f5f9] dark:bg-[#1f2937] text-xs font-bold flex items-center justify-center text-[#0f172a] dark:text-[#f8fafc] shrink-0">
                                    {row.rank}
                                </span>
                                <div className="min-w-0">
                                    <p className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] truncate">
                                        {row.label}
                                    </p>
                                    <p className="text-[10px] text-[#94a3b8]">{row.meta}</p>
                                </div>
                            </div>
                            <div className="text-right shrink-0">
                                <p className="text-xs font-bold text-[#bb152c] dark:text-red-400">
                                    {formatNumber(row.suara)}
                                </p>
                                <p className="text-[10px] font-semibold text-[#94a3b8]">{row.persentase}%</p>
                            </div>
                        </div>
                        <div className="w-full h-1.5 bg-[#e2e8f0] dark:bg-[#1e293b] rounded-full overflow-hidden">
                            <div
                                className="h-full bg-[#bb152c] rounded-full transition-all duration-300"
                                style={{ width: `${Math.min(row.persentase || 0, 100)}%` }}
                            />
                        </div>
                    </div>
                ))}
            </div>

            {/* Footer */}
            <div className="flex items-center justify-between pt-3 border-t border-[#f1f5f9] dark:border-[#1e293b] text-xs">
                <span className="text-[#94a3b8] font-medium">
                    Total Suara Sah: <b className="text-[#0f172a] dark:text-[#f8fafc] font-mono">{formatNumber(widget.total_suara)}</b>
                </span>
                <a
                    href={`/ppk/rekap/${widget.jenis}`}
                    className="inline-flex items-center gap-1 font-bold text-[#bb152c] dark:text-red-400 hover:underline"
                >
                    <span>Detail Rekapitulasi</span>
                    <ArrowRight className="w-3.5 h-3.5" />
                </a>
            </div>
        </div>
    );
}

// Sub-component for DPD Election
function DpdWidget({ widget }) {
    const formatNumber = (num) => (num || 0).toLocaleString('id-ID');
    const winner = widget.winner;
    const hasVotes = (widget.total_suara || 0) > 0 && (winner?.suara || 0) > 0;

    return (
        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-xs space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                <div className="flex items-center gap-2.5">
                    <Users className="w-5 h-5 text-[#bb152c]" />
                    <h3 className="font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] text-base">
                        Dewan Perwakilan Daerah (DPD)
                    </h3>
                </div>
                <span className="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-md bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400">
                    DPD
                </span>
            </div>

            {/* Unggul Sementara */}
            {hasVotes ? (
                <div className="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/20 rounded-xl p-4 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold shrink-0 shadow-sm">
                            <Trophy className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                                Unggul Sementara (#1)
                            </span>
                            <p className="font-bold text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                {winner.nama}
                            </p>
                        </div>
                    </div>
                    <div className="text-right">
                        <p className="text-sm font-extrabold font-mono text-[#bb152c] dark:text-red-400">
                            {formatNumber(winner.suara)}
                        </p>
                        <p className="text-[11px] font-bold text-[#475569] dark:text-[#94a3b8]">{winner.persen}% Suara</p>
                    </div>
                </div>
            ) : null}

            {/* Top 4 Candidates */}
            <div className="space-y-3 pt-1">
                <div className="flex items-center justify-between">
                    <p className="text-[11px] font-bold uppercase tracking-wider text-[#94a3b8]">
                        Top 4 Calon Teratas di Kecamatan:
                    </p>
                    <span className="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded">
                        Kuota 4 Kursi
                    </span>
                </div>

                {hasVotes && widget.top4?.length > 0 ? (
                    widget.top4.map((row, idx) => (
                        <div key={idx} className="bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl p-3.5 space-y-2">
                            <div className="flex items-center justify-between min-w-0">
                                <div className="flex items-center gap-2.5 min-w-0">
                                    <span className="w-6 h-6 rounded-full bg-[#f1f5f9] dark:bg-[#1f2937] text-xs font-bold flex items-center justify-center text-[#0f172a] dark:text-[#f8fafc] shrink-0">
                                        {row.rank}
                                    </span>
                                    <div className="min-w-0">
                                        <p className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] truncate">
                                            {row.label}
                                        </p>
                                        <p className="text-[10px] text-[#94a3b8]">{row.meta}</p>
                                    </div>
                                </div>
                                <div className="text-right shrink-0">
                                    <p className="text-xs font-bold text-[#bb152c] dark:text-red-400">
                                        {formatNumber(row.suara)}
                                    </p>
                                    <p className="text-[10px] font-semibold text-[#94a3b8]">{row.persentase}%</p>
                                </div>
                            </div>
                            <div className="w-full h-1.5 bg-[#e2e8f0] dark:bg-[#1e293b] rounded-full overflow-hidden">
                                <div
                                    className="h-full bg-[#bb152c] rounded-full transition-all duration-300"
                                    style={{ width: `${Math.min(row.persentase || 0, 100)}%` }}
                                />
                            </div>
                        </div>
                    ))
                ) : (
                    <div className="bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl p-4 text-center text-xs text-[#94a3b8]">
                        Belum ada data DPD masuk di kecamatan ini.
                    </div>
                )}
            </div>

            {/* Footer */}
            <div className="flex items-center justify-between pt-3 border-t border-[#f1f5f9] dark:border-[#1e293b] text-xs">
                <span className="text-[#94a3b8] font-medium">
                    Total Suara Sah: <b className="text-[#0f172a] dark:text-[#f8fafc] font-mono">{formatNumber(widget.total_suara)}</b>
                </span>
                <a
                    href="/ppk/rekap/dpd"
                    className="inline-flex items-center gap-1 font-bold text-[#bb152c] dark:text-red-400 hover:underline"
                >
                    <span>Detail Rekapitulasi</span>
                    <ArrowRight className="w-3.5 h-3.5" />
                </a>
            </div>
        </div>
    );
}

// Sub-component for Legislative Elections (DPR RI, DPRD Prov)
function LegislativeWidget({ widget }) {
    const formatNumber = (num) => (num || 0).toLocaleString('id-ID');
    const winner = widget.winner;
    const hasVotes = (widget.total_suara || 0) > 0 && (winner?.suara || 0) > 0;

    return (
        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-xs space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                <div className="flex items-center gap-2.5">
                    <Building2 className="w-5 h-5 text-[#bb152c]" />
                    <h3 className="font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] text-base">
                        {widget.title}
                    </h3>
                </div>
                <span className="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-md bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400">
                    {widget.jenis?.toUpperCase()}
                </span>
            </div>

            {/* Unggul Sementara Banner */}
            {hasVotes ? (
                <div className="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/20 rounded-xl p-4 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold shrink-0 shadow-sm">
                            <Trophy className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                                Partai Unggul Sementara (#1)
                            </span>
                            <p className="font-bold text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                {winner.nama} (No. {winner.nomor_urut})
                            </p>
                        </div>
                    </div>
                    <div className="text-right">
                        <p className="text-sm font-extrabold font-mono text-[#bb152c] dark:text-red-400">
                            {formatNumber(winner.suara)}
                        </p>
                        <p className="text-[11px] font-bold text-[#475569] dark:text-[#94a3b8]">{winner.persen}% Suara</p>
                    </div>
                </div>
            ) : null}

            {/* Top 5 Calegs List */}
            <div className="space-y-3 pt-1">
                <p className="text-[11px] font-bold uppercase tracking-wider text-[#94a3b8]">
                    Top 5 Calon Legislatif (Suara Terbanyak di Kecamatan):
                </p>

                {hasVotes && widget.top5_calegs?.length > 0 ? (
                    widget.top5_calegs.map((caleg, idx) => (
                        <div key={idx} className="bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl p-3.5 space-y-2">
                            <div className="flex items-center justify-between min-w-0">
                                <div className="flex items-center gap-2.5 min-w-0">
                                    <span className="w-6 h-6 rounded-full bg-[#f1f5f9] dark:bg-[#1f2937] text-xs font-bold flex items-center justify-center text-[#0f172a] dark:text-[#f8fafc] shrink-0">
                                        {caleg.rank}
                                    </span>
                                    <div className="min-w-0">
                                        <p className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] truncate">
                                            {caleg.nama_caleg}
                                        </p>
                                        <p className="text-[10px] text-[#94a3b8]">
                                            {caleg.nama_partai} (No. Urut {caleg.nomor_urut})
                                        </p>
                                    </div>
                                </div>
                                <div className="text-right shrink-0">
                                    <p className="text-xs font-bold text-[#bb152c] dark:text-red-400">
                                        {formatNumber(caleg.suara)}
                                    </p>
                                    <p className="text-[10px] font-semibold text-[#94a3b8]">{caleg.persentase}%</p>
                                </div>
                            </div>

                            {/* Mini Progress Bar */}
                            <div className="w-full h-1.5 bg-[#e2e8f0] dark:bg-[#1e293b] rounded-full overflow-hidden">
                                <div
                                    className="h-full bg-[#bb152c] rounded-full transition-all duration-300"
                                    style={{ width: `${Math.min(caleg.persentase || 0, 100)}%` }}
                                />
                            </div>
                        </div>
                    ))
                ) : (
                    <div className="bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl p-4 text-center text-xs text-[#94a3b8]">
                        Belum ada data suara caleg masuk di kecamatan ini.
                    </div>
                )}
            </div>

            {/* Footer */}
            <div className="flex items-center justify-between pt-3 border-t border-[#f1f5f9] dark:border-[#1e293b] text-xs">
                <span className="text-[#94a3b8] font-medium">
                    Total Suara Sah: <b className="text-[#0f172a] dark:text-[#f8fafc] font-mono">{formatNumber(widget.total_suara)}</b>
                </span>
                <a
                    href={`/ppk/rekap/${widget.jenis}`}
                    className="inline-flex items-center gap-1 font-bold text-[#bb152c] dark:text-red-400 hover:underline"
                >
                    <span>Detail Rekapitulasi</span>
                    <ArrowRight className="w-3.5 h-3.5" />
                </a>
            </div>
        </div>
    );
}

// Sub-component for DPRD Kabupaten
function DprdKabWidget({ widget }) {
    const formatNumber = (num) => (num || 0).toLocaleString('id-ID');
    const winner = widget.winner_party;
    const hasVotes = (widget.total_suara || 0) > 0 && (winner?.suara || 0) > 0;

    // Filter to dapil with votes in this kecamatan if available, else show all dapils
    const activeDapils = widget.dapils?.filter(d => (d.total_suara || 0) > 0);
    const displayedDapils = activeDapils && activeDapils.length > 0 ? activeDapils : widget.dapils;

    return (
        <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-xs space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                <div className="flex items-center gap-2.5">
                    <Building2 className="w-5 h-5 text-[#bb152c]" />
                    <div>
                        <h3 className="font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] text-base">
                            DPRD Kabupaten
                        </h3>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8]">
                            Perolehan suara partai dan calon anggota legislatif tingkat kecamatan.
                        </p>
                    </div>
                </div>
                <span className="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-md bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400">
                    DPRD KAB
                </span>
            </div>

            {/* Unggul Sementara Banner */}
            {hasVotes ? (
                <div className="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/20 rounded-xl p-4 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold shrink-0 shadow-sm">
                            <Trophy className="w-5 h-5" />
                        </div>
                        <div>
                            <span className="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                                Partai Unggul di Kecamatan (#1)
                            </span>
                            <p className="font-bold text-sm text-[#0f172a] dark:text-[#f8fafc]">
                                {winner.nama_partai} (No. {winner.nomor_urut})
                            </p>
                        </div>
                    </div>
                    <div className="text-right">
                        <p className="text-sm font-extrabold font-mono text-[#bb152c] dark:text-red-400">
                            {formatNumber(winner.suara)}
                        </p>
                        <p className="text-[11px] font-bold text-[#475569] dark:text-[#94a3b8]">{winner.persentase}% Suara</p>
                    </div>
                </div>
            ) : null}

            {/* Top 5 Calegs List */}
            <div className="space-y-3 pt-1">
                <p className="text-[11px] font-bold uppercase tracking-wider text-[#94a3b8]">
                    Top 5 Calon Legislatif (Suara Terbanyak di Kecamatan):
                </p>

                {hasVotes && widget.top5_calegs?.length > 0 ? (
                    widget.top5_calegs.map((caleg, idx) => (
                        <div key={idx} className="bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl p-3.5 space-y-2">
                            <div className="flex items-center justify-between min-w-0">
                                <div className="flex items-center gap-2.5 min-w-0">
                                    <span className="w-6 h-6 rounded-full bg-[#f1f5f9] dark:bg-[#1f2937] text-xs font-bold flex items-center justify-center text-[#0f172a] dark:text-[#f8fafc] shrink-0">
                                        {caleg.rank}
                                    </span>
                                    <div className="min-w-0">
                                        <p className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] truncate">
                                            {caleg.nama_caleg}
                                        </p>
                                        <p className="text-[10px] text-[#94a3b8]">
                                            {caleg.nama_partai} (No. Urut {caleg.nomor_urut}) • {caleg.nama_dapil}
                                        </p>
                                    </div>
                                </div>
                                <div className="text-right shrink-0">
                                    <p className="text-xs font-bold text-[#bb152c] dark:text-red-400">
                                        {formatNumber(caleg.suara)}
                                    </p>
                                    <p className="text-[10px] font-semibold text-[#94a3b8]">{caleg.persentase}%</p>
                                </div>
                            </div>

                            {/* Mini Progress Bar */}
                            <div className="w-full h-1.5 bg-[#e2e8f0] dark:bg-[#1e293b] rounded-full overflow-hidden">
                                <div
                                    className="h-full bg-[#bb152c] rounded-full transition-all duration-300"
                                    style={{ width: `${Math.min(caleg.persentase || 0, 100)}%` }}
                                />
                            </div>
                        </div>
                    ))
                ) : (
                    <div className="bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl p-4 text-center text-xs text-[#94a3b8]">
                        Belum ada data suara caleg masuk di kecamatan ini.
                    </div>
                )}
            </div>

            {/* Dapil Cards */}
            <div className="space-y-3 pt-2 border-t border-[#f1f5f9] dark:border-[#1e293b]">
                <p className="text-[11px] font-bold uppercase tracking-wider text-[#94a3b8]">
                    Hasil Suara Dapil Terkait:
                </p>

                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    {displayedDapils?.map((dapil, idx) => (
                        <div
                            key={idx}
                            className="bg-[#f8fafc] dark:bg-[#0b0f19] border border-[#e2e8f0] dark:border-[#1e293b] rounded-xl p-3.5 space-y-2 flex flex-col justify-between"
                        >
                            <div>
                                <div className="flex items-center justify-between pb-2 border-b border-[#e2e8f0] dark:border-[#1e293b]">
                                    <span className="text-xs font-extrabold text-[#0f172a] dark:text-[#f8fafc] flex items-center gap-1">
                                        <MapPin className="w-3.5 h-3.5 text-[#bb152c]" />
                                        <span>{dapil.nama_dapil}</span>
                                    </span>
                                    <span className="text-[10px] text-[#94a3b8] font-mono font-semibold">
                                        {formatNumber(dapil.total_suara)} Suara
                                    </span>
                                </div>

                                {dapil.winner_party && (dapil.total_suara || 0) > 0 && (dapil.winner_party?.suara || 0) > 0 ? (
                                    <div className="pt-2 space-y-1.5">
                                        <div className="flex items-center justify-between">
                                            <p className="text-xs font-bold text-[#bb152c] dark:text-red-400 truncate">
                                                🥇 {dapil.winner_party.nama_partai}
                                            </p>
                                            <span className="text-[10px] font-bold text-[#0f172a] dark:text-[#f8fafc]">
                                                {dapil.winner_party.persen}%
                                            </span>
                                        </div>

                                        {dapil.star_caleg && (dapil.star_caleg.suara || 0) > 0 && (
                                            <div className="flex items-center gap-1 text-[10px] text-[#475569] dark:text-[#94a3b8]">
                                                <Star className="w-3 h-3 text-amber-500 shrink-0 fill-amber-500" />
                                                <span className="truncate">
                                                    <b>{dapil.star_caleg.nama_caleg}</b> ({formatNumber(dapil.star_caleg.suara)})
                                                </span>
                                            </div>
                                        )}
                                    </div>
                                ) : (
                                    <div className="pt-2 text-center text-[10px] text-[#94a3b8]">
                                        Belum ada data suara.
                                    </div>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Footer */}
            <div className="flex items-center justify-between pt-3 border-t border-[#f1f5f9] dark:border-[#1e293b] text-xs">
                <span className="text-[#94a3b8] font-medium">
                    Total Suara Sah: <b className="text-[#0f172a] dark:text-[#f8fafc] font-mono">{formatNumber(widget.total_suara)}</b>
                </span>
                <a
                    href="/ppk/rekap/dprd_kab"
                    className="inline-flex items-center gap-1 font-bold text-[#bb152c] dark:text-red-400 hover:underline"
                >
                    <span>Detail Rekapitulasi</span>
                    <ArrowRight className="w-3.5 h-3.5" />
                </a>
            </div>
        </div>
    );
}

export default function PpkDashboard({ electionSummary, viewKecamatan, isAdminView }) {
    const docSummary = electionSummary?.documentsSummary || {};
    const widgets = electionSummary?.widgets || {};
    const activeJenis = electionSummary?.active_jenis || [];

    const formatNumber = (num) => (num || 0).toLocaleString('id-ID');

    const wilayahTitle = viewKecamatan
        ? `Kecamatan ${viewKecamatan.nama}`
        : (electionSummary?.scope_label || 'Dashboard PPK');

    return (
        <AdminLayout title={`Dashboard PPK - ${wilayahTitle}`}>
            <Head title={`Dashboard PPK - ${wilayahTitle}`} />

            <div className="space-y-6">
                {/* Banner Mode View Admin */}
                {isAdminView && (
                    <div className="bg-orange-50 dark:bg-orange-950/40 border border-orange-200 dark:border-orange-900/60 rounded-2xl px-5 py-3.5 flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <span className="text-orange-600 dark:text-orange-400 text-xs font-bold uppercase tracking-wider">
                                👁 Mode Monitoring Admin
                            </span>
                            <span className="text-xs text-slate-600 dark:text-slate-300">
                                Anda sedang melihat dashboard PPK {wilayahTitle} sebagai Administrator.
                            </span>
                        </div>
                        <a
                            href="/admin/kecamatan"
                            className="text-xs font-bold text-orange-600 dark:text-orange-400 hover:underline"
                        >
                            ← Kembali ke Kelola Kecamatan
                        </a>
                    </div>
                )}

                {/* Top Banner Header */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-xs">
                    <div>
                        <span className="text-[10px] uppercase font-bold tracking-widest text-[#bb152c] dark:text-red-400 bg-red-50 dark:bg-red-950/40 px-2.5 py-1 rounded-md">
                            Tingkat Kecamatan (PPK)
                        </span>
                        <h1 className="text-2xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] tracking-tight mt-2">
                            {wilayahTitle}
                        </h1>
                        <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-1">
                            Memantau kelengkapan dokumen D-Hasil dan perolehan suara tingkat kecamatan secara real-time.
                        </p>
                    </div>
                </div>

                {/* Quick Action Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a
                        href="/dokumen/kecamatan"
                        className="group bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs hover:border-[#bb152c] dark:hover:border-red-500/50 transition duration-150 flex items-center justify-between"
                    >
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 flex items-center justify-center shrink-0">
                                <FolderOpen className="w-5 h-5" />
                            </div>
                            <div>
                                <h4 className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] group-hover:text-[#bb152c] dark:group-hover:text-red-400 transition">
                                    Rekap Dokumen
                                </h4>
                                <p className="text-[10px] text-[#94a3b8]">Periksa C-Hasil TPS</p>
                            </div>
                        </div>
                        <ArrowRight className="w-4 h-4 text-[#94a3b8] group-hover:text-[#bb152c] dark:group-hover:text-red-400 group-hover:translate-x-0.5 transition" />
                    </a>

                    <a
                        href="/ppk/data-pps"
                        className="group bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs hover:border-[#bb152c] dark:hover:border-red-500/50 transition duration-150 flex items-center justify-between"
                    >
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                <Building2 className="w-5 h-5" />
                            </div>
                            <div>
                                <h4 className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] group-hover:text-[#bb152c] dark:group-hover:text-red-400 transition">
                                    Data PPS
                                </h4>
                                <p className="text-[10px] text-[#94a3b8]">Daftar Desa & Progres</p>
                            </div>
                        </div>
                        <ArrowRight className="w-4 h-4 text-[#94a3b8] group-hover:text-[#bb152c] dark:group-hover:text-red-400 group-hover:translate-x-0.5 transition" />
                    </a>

                    <a
                        href="/ppk/upload"
                        className="group bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs hover:border-[#bb152c] dark:hover:border-red-500/50 transition duration-150 flex items-center justify-between"
                    >
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                                <Upload className="w-5 h-5" />
                            </div>
                            <div>
                                <h4 className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] group-hover:text-[#bb152c] dark:group-hover:text-red-400 transition">
                                    Upload Dokumen
                                </h4>
                                <p className="text-[10px] text-[#94a3b8]">Unggah D-Hasil Kecamatan</p>
                            </div>
                        </div>
                        <ArrowRight className="w-4 h-4 text-[#94a3b8] group-hover:text-[#bb152c] dark:group-hover:text-red-400 group-hover:translate-x-0.5 transition" />
                    </a>

                    <a
                        href="/ppk/rekap"
                        className="group bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-4 shadow-xs hover:border-[#bb152c] dark:hover:border-red-500/50 transition duration-150 flex items-center justify-between"
                    >
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <BarChart2 className="w-5 h-5" />
                            </div>
                            <div>
                                <h4 className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] group-hover:text-[#bb152c] dark:group-hover:text-red-400 transition">
                                    Rekapitulasi
                                </h4>
                                <p className="text-[10px] text-[#94a3b8]">Lihat Hasil Tiap Pemilihan</p>
                            </div>
                        </div>
                        <ArrowRight className="w-4 h-4 text-[#94a3b8] group-hover:text-[#bb152c] dark:group-hover:text-red-400 group-hover:translate-x-0.5 transition" />
                    </a>
                </div>

                {/* Progress Bar Finalisasi TPS Card */}
                <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-6 shadow-xs">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                        <div>
                            <h3 className="font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] text-sm flex items-center gap-2">
                                <Vote className="w-4 h-4 text-[#bb152c]" />
                                <span>Progres Entry Data TPS Final di {wilayahTitle}</span>
                            </h3>
                            <p className="text-xs text-[#475569] dark:text-[#94a3b8] mt-0.5">
                                Persentase TPS yang telah memfinalisasi seluruh jenis pemilihan yang aktif ({docSummary.active_jenis_count || activeJenis.length} jenis pemilu).
                            </p>
                        </div>

                        <div className="text-left sm:text-right">
                            <span className="text-xl font-extrabold font-mono text-[#bb152c] dark:text-red-400">
                                {docSummary.tps_percent || 0}%
                            </span>
                            <span className="text-xs text-[#94a3b8] block">
                                {formatNumber(docSummary.finalized_tps)} dari {formatNumber(docSummary.total_tps)} TPS Selesai Penuh
                            </span>
                        </div>
                    </div>

                    <div className="w-full h-3 bg-[#f1f5f9] dark:bg-[#0b0f19] rounded-full overflow-hidden border border-[#e2e8f0] dark:border-[#1e293b]">
                        <div
                            className="h-full bg-gradient-to-r from-[#bb152c] to-red-500 rounded-full transition-all duration-500"
                            style={{ width: `${Math.min(docSummary.tps_percent || 0, 100)}%` }}
                        />
                    </div>
                </div>

                {/* KPI Cards Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {/* Card 1: Total TPS */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs flex items-center gap-4">
                        <div className="w-12 h-12 rounded-xl bg-[#f1f5f9] dark:bg-[#1f2937] flex items-center justify-center text-[#0f172a] dark:text-[#f8fafc] shrink-0">
                            <Vote className="w-6 h-6" />
                        </div>
                        <div>
                            <span className="text-xs font-medium text-[#475569] dark:text-[#94a3b8]">Total TPS Kecamatan</span>
                            <p className="text-xl font-bold font-heading text-[#0f172a] dark:text-[#f8fafc] mt-0.5">
                                {formatNumber(docSummary.total_tps)}
                            </p>
                        </div>
                    </div>

                    {/* Card 2: Terverifikasi */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs flex items-center gap-4">
                        <div className="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-[#10b981] shrink-0 border border-emerald-100 dark:border-emerald-900/40">
                            <CheckCircle2 className="w-6 h-6" />
                        </div>
                        <div>
                            <span className="text-xs font-medium text-[#475569] dark:text-[#94a3b8]">Dokumen Terverifikasi</span>
                            <p className="text-xl font-bold font-heading text-[#10b981] mt-0.5">
                                {formatNumber(docSummary.verified_count)}
                            </p>
                        </div>
                    </div>

                    {/* Card 3: Menunggu Verifikasi */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs flex items-center gap-4">
                        <div className="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center text-[#f59e0b] shrink-0 border border-amber-100 dark:border-amber-900/40">
                            <Clock className="w-6 h-6" />
                        </div>
                        <div>
                            <span className="text-xs font-medium text-[#475569] dark:text-[#94a3b8]">Menunggu Verifikasi</span>
                            <p className="text-xl font-bold font-heading text-[#f59e0b] mt-0.5">
                                {formatNumber(docSummary.uploaded_count)}
                            </p>
                        </div>
                    </div>

                    {/* Card 4: Ditolak */}
                    <div className="bg-white dark:bg-[#151f32] border border-[#e2e8f0] dark:border-[#1e293b] rounded-2xl p-5 shadow-xs flex items-center gap-4">
                        <div className="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center text-[#ef4444] shrink-0 border border-red-100 dark:border-red-900/40">
                            <XCircle className="w-6 h-6" />
                        </div>
                        <div>
                            <span className="text-xs font-medium text-[#475569] dark:text-[#94a3b8]">Dokumen Ditolak</span>
                            <p className="text-xl font-bold font-heading text-[#ef4444] mt-0.5">
                                {formatNumber(docSummary.rejected_count)}
                            </p>
                        </div>
                    </div>
                </div>

                {/* ADAPTIVE ELECTION TYPE WIDGETS GRID */}
                <div className="space-y-6">
                    {/* Render active candidate widgets (PPWP, Gubernur, Bupati) */}
                    {['ppwp', 'gubernur', 'bupati'].map((jenis) => {
                        if (!activeJenis.includes(jenis) || !widgets[jenis]) return null;
                        return <CandidateWidget key={jenis} widget={widgets[jenis]} />;
                    })}

                    {/* Render DPD widget */}
                    {activeJenis.includes('dpd') && widgets.dpd && (
                        <DpdWidget widget={widgets.dpd} />
                    )}

                    {/* Render legislative widgets (DPR RI, DPRD Prov) */}
                    {['dpr_ri', 'dprd_prov'].map((jenis) => {
                        if (!activeJenis.includes(jenis) || !widgets[jenis]) return null;
                        return <LegislativeWidget key={jenis} widget={widgets[jenis]} />;
                    })}

                    {/* Render DPRD Kabupaten */}
                    {activeJenis.includes('dprd_kab') && widgets.dprd_kab && (
                        <DprdKabWidget widget={widgets.dprd_kab} />
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
