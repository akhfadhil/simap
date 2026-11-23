import React, { useState, useEffect } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import {
    LayoutDashboard,
    Users,
    BarChart3,
    MapPin,
    Building2,
    Pin,
    FolderOpen,
    BarChart2,
    Settings,
    Upload,
    Lock,
    LogOut,
    Menu,
    X,
    Sun,
    Moon,
    CheckCircle2,
    AlertCircle,
    ChevronRight,
    Eye,
    ArrowLeft
} from 'lucide-react';

export default function AdminLayout({ children, title }) {
    const { auth, flash, adminViewSession } = usePage().props;
    const user = auth?.user;
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [darkMode, setDarkMode] = useState(() => {
        return localStorage.getItem('oliviana_dark_mode') === 'true' ||
            (!('oliviana_dark_mode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
    });

    useEffect(() => {
        if (darkMode) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('oliviana_dark_mode', 'true');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('oliviana_dark_mode', 'false');
        }
    }, [darkMode]);

    const currentUrl = typeof window !== 'undefined' ? window.location.pathname : '';
    const isAdmin = user?.role === 'admin';
    const isAdminPath = currentUrl.startsWith('/admin');
    const isSimulated = !isAdminPath && Boolean(adminViewSession?.kecamatan_id || adminViewSession?.desa_id || adminViewSession?.tps_id);

    // Tentukan role efektif yang sedang ditampilkan di layout
    const activeRole = isSimulated
        ? (adminViewSession?.tps_id ? 'kpps' : adminViewSession?.desa_id ? 'pps' : 'ppk')
        : user?.role;

    const isKomisioner = activeRole === 'komisioner';
    const isPpk = activeRole === 'ppk';
    const isPps = activeRole === 'pps';
    const isKpps = activeRole === 'kpps';

    const simulatedTerritory = adminViewSession?.tps_nama
        ? `TPS ${adminViewSession.tps_nama}`
        : adminViewSession?.desa_nama
        ? `Desa ${adminViewSession.desa_nama}`
        : adminViewSession?.kecamatan_nama
        ? `Kecamatan ${adminViewSession.kecamatan_nama}`
        : 'Wilayah';

    const roleLabel = isKomisioner
        ? 'Komisioner'
        : isKpps
        ? (isSimulated ? `KPPS (${simulatedTerritory})` : 'KPPS')
        : isPps
        ? (isSimulated ? `PPS (${simulatedTerritory})` : 'PPS Desa')
        : isPpk
        ? (isSimulated ? `PPK (${simulatedTerritory})` : 'PPK Kecamatan')
        : isAdmin
        ? 'Administrator'
        : user?.role?.toUpperCase();

    const backLink = user?.role === 'ppk'
        ? { href: '/clear-view-session?redirect=' + encodeURIComponent('/ppk/data-pps'), label: 'Data PPS' }
        : adminViewSession?.tps_id
        ? { href: '/clear-view-session?redirect=' + encodeURIComponent('/admin/tps'), label: 'Kelola TPS' }
        : adminViewSession?.desa_id
        ? { href: '/clear-view-session?redirect=' + encodeURIComponent('/admin/desa'), label: 'Kelola Desa' }
        : adminViewSession?.kecamatan_id
        ? { href: '/clear-view-session?redirect=' + encodeURIComponent('/admin/kecamatan'), label: 'Kelola Kecamatan' }
        : null;

    const menus = isKomisioner
        ? [
              { href: '/dashboard/komisioner', label: 'Beranda', icon: LayoutDashboard, isBlade: false },
              { href: '/admin/rekap/chart', label: 'Grafik & Statistik', icon: BarChart3, isBlade: false },
              { href: '/dokumen/semua', label: 'Rekap Dokumen', icon: FolderOpen, isBlade: false },
              { href: '/admin/rekap', label: 'Rekapitulasi Data', icon: BarChart2, isBlade: false },
          ]
        : isPpk
        ? [
              { href: '/dashboard/ppk', label: 'Beranda', icon: LayoutDashboard, isBlade: false },
              { href: '/dokumen/kecamatan', label: 'Rekap Dokumen', icon: FolderOpen, isBlade: false },
              { href: '/ppk/data-pps', label: 'Data PPS', icon: Building2, isBlade: false },
              { href: '/ppk/rekap', label: 'Rekapitulasi Data', icon: BarChart2, isBlade: false },
          ]
        : isPps
        ? [
              { href: '/dashboard/pps', label: 'Beranda', icon: LayoutDashboard, isBlade: false },
              { href: '/dokumen/verifikasi', label: 'Rekap Dokumen', icon: FolderOpen, isBlade: true },
              { href: '/pps/data-tps', label: 'Data TPS', icon: Building2, isBlade: true },
              { href: '/pps/rekap', label: 'Rekapitulasi Data', icon: BarChart2, isBlade: true },
          ]
        : isKpps
        ? [
              { href: '/dashboard/kpps', label: 'Beranda', icon: LayoutDashboard, isBlade: false },
              { href: '/dokumen/upload', label: 'Upload C-Hasil', icon: Upload, isBlade: true },
          ]
        : [
              { href: '/dashboard/admin', label: 'Beranda', icon: LayoutDashboard, isBlade: false },
              { href: '/admin/users', label: 'Pengguna', icon: Users, isBlade: false },
              { href: '/admin/rekap/chart', label: 'Grafik & Statistik', icon: BarChart3, isBlade: false },
              { href: '/admin/kecamatan', label: 'Kelola Kecamatan', icon: MapPin, isBlade: false },
              { href: '/admin/desa', label: 'Kelola Desa', icon: Building2, isBlade: false },
              { href: '/admin/tps', label: 'Kelola TPS', icon: Pin, isBlade: false },
              { href: '/dokumen/semua', label: 'Rekap Dokumen', icon: FolderOpen, isBlade: false },
              { href: '/admin/rekap', label: 'Rekapitulasi Data', icon: BarChart2, isBlade: false },
              { href: '/admin/setup', label: 'Setup Data Pemilu', icon: Settings, isBlade: false },
          ];

    const clearViewSession = (e) => {
        if (e) e.preventDefault();
        window.location.href = '/clear-view-session';
    };

    const isMenuActive = (menuHref, url) => {
        if (menuHref === '/admin/rekap/chart') {
            return url.startsWith('/admin/rekap/chart');
        }
        if (menuHref === '/admin/rekap') {
            return url.startsWith('/admin/rekap') && !url.startsWith('/admin/rekap/chart');
        }
        if (menuHref === '/ppk/rekap') {
            return url.startsWith('/ppk/rekap');
        }
        if (menuHref === '/pps/rekap') {
            return url.startsWith('/pps/rekap');
        }
        if (menuHref === '/dashboard/admin' || menuHref === '/dashboard/komisioner' || menuHref === '/dashboard/ppk' || menuHref === '/dashboard/pps' || menuHref === '/dashboard/kpps') {
            return url === menuHref;
        }
        return url === menuHref || url.startsWith(menuHref);
    };

    return (
        <div className="min-h-screen bg-[#f8fafc] dark:bg-[#0b0f19] text-[#0f172a] dark:text-[#f8fafc] font-sans antialiased selection:bg-[#bb152c] selection:text-white transition-colors duration-200">
            {/* Mobile Drawer Overlay */}
            {mobileMenuOpen && (
                <div
                    className="fixed inset-0 z-50 bg-[#0f172a]/40 backdrop-blur-xs md:hidden"
                    onClick={() => setMobileMenuOpen(false)}
                />
            )}

            {/* Mobile Sidebar */}
            <aside
                className={`fixed top-0 left-0 z-50 w-[260px] h-full bg-white dark:bg-[#151f32] border-r border-[#e2e8f0] dark:border-[#1e293b] flex flex-col transition-transform duration-250 md:hidden ${
                    mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
                }`}
            >
                <div className="p-5 flex items-center justify-between border-b border-[#f1f5f9] dark:border-[#1e293b]">
                    <div className="flex items-center gap-3">
                        <div className="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/40 p-1.5 flex items-center justify-center border border-red-100 dark:border-red-900/50">
                            <img src="/images/logo-kpu.png" alt="KPU Logo" className="w-full h-full object-contain" />
                        </div>
                        <div>
                            <h1 className="font-extrabold text-lg font-heading text-[#0f172a] dark:text-[#f8fafc] leading-tight">SIMAP</h1>
                            <span className="text-[10px] text-[#94a3b8] font-medium tracking-wide uppercase">{roleLabel}</span>
                        </div>
                    </div>
                    <button onClick={() => setMobileMenuOpen(false)} className="p-1.5 text-[#94a3b8] hover:text-[#0f172a] dark:hover:text-[#f8fafc]">
                        <X className="w-5 h-5" />
                    </button>
                </div>

                <nav className="flex-1 p-3 space-y-1 overflow-y-auto">
                    {menus.map((menu, idx) => {
                        const Icon = menu.icon;
                        const active = isMenuActive(menu.href, currentUrl);
                        const className = `flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-medium transition duration-150 ${
                            active
                                ? 'bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 font-semibold'
                                : 'text-[#475569] dark:text-[#94a3b8] hover:bg-[#f1f5f9] dark:hover:bg-[#1f2937] hover:text-[#0f172a] dark:hover:text-[#f8fafc]'
                        }`;

                        return menu.isBlade ? (
                            <a
                                key={idx}
                                href={menu.href}
                                onClick={() => setMobileMenuOpen(false)}
                                className={className}
                            >
                                <Icon className={`w-4 h-4 ${active ? 'text-[#bb152c] dark:text-red-400' : 'text-[#94a3b8]'}`} />
                                <span>{menu.label}</span>
                            </a>
                        ) : (
                            <Link
                                key={idx}
                                href={menu.href}
                                onClick={() => setMobileMenuOpen(false)}
                                className={className}
                            >
                                <Icon className={`w-4 h-4 ${active ? 'text-[#bb152c] dark:text-red-400' : 'text-[#94a3b8]'}`} />
                                <span>{menu.label}</span>
                            </Link>
                        );
                    })}
                </nav>

                <div className="p-3 border-t border-[#f1f5f9] dark:border-[#1e293b] space-y-1">
                    <Link
                        href="/password"
                        className="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs text-[#475569] dark:text-[#94a3b8] hover:bg-[#f1f5f9] dark:hover:bg-[#1f2937] transition"
                    >
                        <Lock className="w-4 h-4 text-[#94a3b8]" />
                        <span>Ubah Password</span>
                    </Link>
                    <a
                        href="/logout"
                        onClick={(e) => {
                            e.preventDefault();
                            router.post('/logout');
                        }}
                        className="w-full flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs text-[#bb152c] hover:bg-red-50 dark:hover:bg-red-950/30 transition font-semibold"
                    >
                        <LogOut className="w-4 h-4" />
                        <span>Log Keluar</span>
                    </a>
                </div>
            </aside>

            {/* Desktop View Layout */}
            <div className="flex min-h-screen">
                {/* Desktop Sidebar */}
                <aside className="hidden md:flex flex-col w-[260px] h-screen sticky top-0 bg-white dark:bg-[#151f32] border-r border-[#e2e8f0] dark:border-[#1e293b] z-30">
                    <div className="p-5 flex flex-col gap-1 border-b border-[#f1f5f9] dark:border-[#1e293b]">
                        <div className="flex items-center gap-3">
                            <div className="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/40 p-1.5 flex items-center justify-center border border-red-100 dark:border-red-900/50">
                                <img src="/images/logo-kpu.png" alt="SIMAP Logo" className="w-full h-full object-contain" />
                            </div>
                            <div>
                                <h1 className="font-extrabold text-lg font-heading text-[#0f172a] dark:text-[#f8fafc] leading-none">SIMAP</h1>
                                <span className="text-[10px] text-[#94a3b8] font-medium tracking-wide">Banyuwangi</span>
                            </div>
                        </div>
                    </div>

                    <nav className="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                        {menus.map((menu, idx) => {
                            const Icon = menu.icon;
                            const active = isMenuActive(menu.href, currentUrl);
                            const className = `flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-medium transition duration-150 ${
                                active
                                    ? 'bg-red-50 dark:bg-red-950/40 text-[#bb152c] dark:text-red-400 font-semibold border-l-4 border-[#bb152c] dark:border-red-500'
                                    : 'text-[#475569] dark:text-[#94a3b8] hover:bg-[#f1f5f9] dark:hover:bg-[#1f2937] hover:text-[#0f172a] dark:hover:text-[#f8fafc]'
                            }`;

                            return menu.isBlade ? (
                                <a
                                    key={idx}
                                    href={menu.href}
                                    className={className}
                                >
                                    <Icon className={`w-4 h-4 ${active ? 'text-[#bb152c] dark:text-red-400' : 'text-[#94a3b8]'}`} />
                                    <span>{menu.label}</span>
                                </a>
                            ) : (
                                <Link
                                    key={idx}
                                    href={menu.href}
                                    className={className}
                                >
                                    <Icon className={`w-4 h-4 ${active ? 'text-[#bb152c] dark:text-red-400' : 'text-[#94a3b8]'}`} />
                                    <span>{menu.label}</span>
                                </Link>
                            );
                        })}
                    </nav>

                    <div className="p-3 border-t border-[#f1f5f9] dark:border-[#1e293b] space-y-1 mt-auto">
                        <Link
                            href="/password"
                            className="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs text-[#475569] dark:text-[#94a3b8] hover:bg-[#f1f5f9] dark:hover:bg-[#1f2937] transition"
                        >
                            <Lock className="w-4 h-4 text-[#94a3b8]" />
                            <span>Ubah Password</span>
                        </Link>
                        <a
                            href="/logout"
                            onClick={(e) => {
                                e.preventDefault();
                                router.post('/logout');
                            }}
                            className="w-full flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs text-[#bb152c] hover:bg-red-50 dark:hover:bg-red-950/30 transition font-semibold cursor-pointer"
                        >
                            <LogOut className="w-4 h-4" />
                            <span>Log Keluar</span>
                        </a>
                    </div>
                </aside>

                {/* Main Content Area */}
                <main className="flex-1 flex flex-col min-w-0">
                    {/* Header Topbar */}
                    <header className="sticky top-0 z-20 h-16 w-full flex items-center justify-between gap-4 px-4 lg:px-8 bg-white/90 dark:bg-[#151f32]/90 backdrop-blur-md border-b border-[#e2e8f0] dark:border-[#1e293b]">
                        <div className="flex items-center gap-4">
                            <button
                                onClick={() => setMobileMenuOpen(true)}
                                className="md:hidden p-2 text-[#475569] dark:text-[#94a3b8] hover:text-[#0f172a]"
                            >
                                <Menu className="w-5 h-5" />
                            </button>
                            <div className="hidden lg:flex items-center gap-2 text-xs text-[#94a3b8]">
                                <span className="font-semibold text-[#0f172a] dark:text-[#f8fafc]">SIMAP</span>
                                <ChevronRight className="w-3.5 h-3.5 text-[#94a3b8]" />
                                <span>{title || 'Dashboard'}</span>
                            </div>
                        </div>

                        <div className="flex items-center gap-3">
                            <button
                                onClick={() => setDarkMode(!darkMode)}
                                className="p-2 rounded-xl text-[#94a3b8] hover:bg-[#f1f5f9] dark:hover:bg-[#1f2937] transition cursor-pointer"
                                title="Ubah Tema"
                            >
                                {darkMode ? <Sun className="w-4 h-4 text-amber-400" /> : <Moon className="w-4 h-4" />}
                            </button>

                            <div className="h-5 w-px bg-[#e2e8f0] dark:bg-[#1e293b] mx-1" />

                            <div className="flex items-center gap-2.5">
                                <div className="text-right hidden sm:block">
                                    <p className="text-xs font-bold text-[#0f172a] dark:text-[#f8fafc] uppercase leading-none">{user?.name}</p>
                                    <p className="text-[10px] text-[#94a3b8] mt-1 leading-none font-medium">{roleLabel}</p>
                                </div>
                                <div className="w-8 h-8 rounded-full bg-red-50 dark:bg-red-950/50 text-[#bb152c] dark:text-red-400 font-bold text-xs flex items-center justify-center border border-red-100 dark:border-red-900/50">
                                    {user?.name ? user.name.charAt(0).toUpperCase() : 'U'}
                                </div>
                            </div>
                        </div>
                    </header>

                    {/* View As Session Notice Banner */}
                    {isSimulated && (
                        <div className="bg-amber-500/10 border-b border-amber-500/20 px-4 sm:px-6 py-2.5 flex flex-wrap items-center justify-between gap-3 text-xs text-amber-900 dark:text-amber-300 font-medium">
                            <div className="flex items-center gap-2.5 flex-wrap">
                                <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider text-[10px] bg-amber-500/20 text-amber-900 dark:text-amber-200 border border-amber-500/30">
                                    <Eye className="w-3 h-3" />
                                    Mode Simulasi
                                </span>
                                <span>
                                    Anda sedang melihat tampilan sebagai <strong className="font-semibold">{roleLabel}</strong>.
                                </span>
                            </div>
                            <div className="flex items-center gap-2 sm:gap-3">
                                {backLink && (
                                    <a
                                        href={backLink.href}
                                        className="text-amber-800 dark:text-amber-300 hover:text-amber-950 dark:hover:text-amber-100 hover:underline inline-flex items-center gap-1 font-semibold text-xs"
                                    >
                                        <ArrowLeft className="w-3.5 h-3.5" />
                                        <span>Kembali ke {backLink.label}</span>
                                    </a>
                                )}
                                <span className="text-amber-400 dark:text-amber-600">|</span>
                                <button
                                    onClick={clearViewSession}
                                    className="px-2.5 py-1 rounded-lg bg-amber-600/15 hover:bg-amber-600/25 text-amber-950 dark:text-amber-100 font-bold transition inline-flex items-center gap-1 text-xs cursor-pointer"
                                    title="Hentikan simulasi dan kembali ke akun Admin"
                                >
                                    <span>Kembalikan Tampilan Admin</span>
                                    <span className="font-bold text-sm leading-none">×</span>
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Flash Notifications */}
                    <div className="px-4 lg:px-8 pt-4">
                        {flash?.success && (
                            <div className="bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 px-4 py-3 text-xs rounded-xl flex items-center gap-2 mb-4 font-medium shadow-xs">
                                <CheckCircle2 className="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                                <span>{flash.success}</span>
                            </div>
                        )}
                        {flash?.error && (
                            <div className="bg-red-50 dark:bg-red-950/60 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 text-xs rounded-xl flex items-center gap-2 mb-4 font-medium shadow-xs">
                                <AlertCircle className="w-4 h-4 text-red-600 dark:text-red-400 shrink-0" />
                                <span>{flash.error}</span>
                            </div>
                        )}
                    </div>

                    {/* Dynamic Page Content */}
                    <div className="p-4 lg:p-8 flex-1 overflow-y-auto">
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
