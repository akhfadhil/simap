<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIMAP - Grafik & Statistik</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="icon" type="image/png" href="{{ asset('images/logo-kpu.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">

    <style>
        :root {
            --surface: #f8f9fa;
            --surface-low: #eef1f4;
            --surface-card: #ffffff;
            --surface-soft: #f3f5f7;
            --ink: #17202a;
            --muted: #657181;
            --line: #d9dee5;
            --primary: #001f45;
            --primary-2: #2d476f;
            --red: #c81924;
            --red-soft: #ffe1df;
            --blue-soft: #dbe8ff;
            --map-dot: rgba(0, 31, 69, 0.08);
        }

        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            overflow: hidden;
            background: var(--surface);
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .font-mono-data {
            font-family: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .material-symbols-outlined {
            font-variation-settings: "FILL" 0, "wght" 500, "GRAD" 0, "opsz" 24;
            line-height: 1;
        }

        .admin-nav-sidebar {
            background: rgba(255, 255, 255, 0.94);
            border-color: var(--line);
        }

        .admin-nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 24px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .admin-nav-item:hover {
            background: var(--surface-soft);
            color: var(--red);
        }

        .admin-nav-item.active {
            background: var(--red-soft);
            color: var(--red);
            border-right: 4px solid var(--red);
        }

        .admin-mobile-overlay {
            position: fixed;
            inset: 0;
            z-index: 80;
            background: rgba(15, 23, 42, 0.46);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .admin-mobile-drawer {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 90;
            width: min(82vw, 20rem);
            transform: translateX(-100%);
            transition: transform 0.25s ease;
        }

        #admin-mobile-menu:checked ~ .admin-mobile-overlay {
            opacity: 1;
            pointer-events: auto;
        }

        #admin-mobile-menu:checked ~ .admin-mobile-drawer {
            transform: translateX(0);
        }

        @media (min-width: 768px) {
            .admin-mobile-overlay,
            .admin-mobile-drawer {
                display: none;
            }
        }

        .map-grid {
            background-color: var(--surface-low);
            background-image: radial-gradient(circle at 2px 2px, var(--map-dot) 1px, transparent 0);
            background-size: 24px 24px;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .leaflet-tooltip-kec {
            background: rgba(0, 31, 69, 0.94);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 8px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.24);
            font-size: 12px;
            font-weight: 500;
            padding: 0;
        }

        .map-tooltip {
            min-width: 190px;
            padding: 10px 12px;
        }

        .map-tooltip-title {
            display: block;
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .map-tooltip-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            color: rgba(255, 255, 255, 0.82);
            font-size: 11px;
            line-height: 1.45;
        }

        .map-tooltip-row b {
            color: #ffffff;
            font-weight: 800;
        }

        .leaflet-container {
            background: transparent !important;
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
        }

        .leaflet-control-zoom {
            border: 1px solid var(--line) !important;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.16) !important;
        }

        .leaflet-control-zoom a {
            color: var(--primary) !important;
        }

        select, input, button {
            font-family: inherit;
        }

        .jenis-btn {
            background: #f8fafc;
            border-color: #e2e8f0;
            color: #475569;
        }

        .jenis-btn:hover {
            background: #ffffff;
            border-color: #cbd5e1;
            color: var(--primary);
        }

        .jenis-btn.is-active {
            background: var(--primary);
            border-color: var(--primary);
            color: #ffffff;
            box-shadow: 0 12px 28px rgba(0, 31, 69, 0.18);
        }

        .detail-table-scroll {
            max-height: 286px;
            overflow-y: auto;
        }

        .detail-table-scroll thead {
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .candidate-rank-scroll {
            max-height: 350px;
            overflow-y: auto;
        }

        @media (max-width: 1280px) {
            body {
                overflow: auto;
            }

            main.chart-shell {
                height: auto;
                min-height: 100vh;
                display: grid;
                grid-template-columns: 300px minmax(0, 1fr);
            }

            main.chart-shell > aside:first-child {
                height: calc(100vh - 4rem);
                position: sticky;
                top: 4rem;
            }

            main.chart-shell > section {
                min-width: 0;
            }

            main.chart-shell > aside:last-child {
                grid-column: 1 / -1;
                width: auto;
                height: auto;
                border-left: 0;
                border-top: 1px solid var(--line);
            }
        }

        @media (max-width: 1024px) {
            main.chart-shell {
                display: block;
                padding-top: 4rem;
            }

            header {
                height: auto;
                min-height: 4rem;
            }

            main.chart-shell > aside:first-child,
            main.chart-shell > aside:last-child {
                width: auto;
                height: auto;
                position: static;
            }

            main.chart-shell > section {
                height: auto;
            }

            .map-panel {
                height: 620px;
                min-height: 520px;
            }

            .kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .map-info {
                top: 13.5rem;
            }
        }

        @media (max-width: 640px) {
            header .h-full {
                padding: 0.75rem 1rem;
            }

            main.chart-shell {
                padding-top: 5rem;
            }

            main.chart-shell > aside:first-child,
            main.chart-shell > aside:last-child {
                padding: 1rem;
            }

            main.chart-shell > section {
                padding: 0.75rem;
            }

            .map-panel {
                height: 720px;
                min-height: 640px;
            }

            .kpi-grid {
                left: 0.75rem;
                right: 0.75rem;
                top: 0.75rem;
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }

            .kpi-card {
                padding: 0.75rem;
            }

            .kpi-card p:nth-child(2) {
                font-size: 1.25rem;
                line-height: 1.5rem;
                margin-top: 0.25rem;
            }

            .map-info {
                left: 0.75rem;
                right: 0.75rem;
                top: 21rem;
                max-width: none;
            }

            #map-legend {
                left: 0.75rem;
                right: 0.75rem;
                bottom: 0.75rem;
                min-width: 0;
                max-height: 190px;
                overflow-y: auto;
            }

            .detail-table-scroll {
                max-height: 310px;
            }
        }
    </style>
</head>
<body>
@php
    $aktifJenis = \App\Models\PemiluSetting::aktif();
    $defaultJenis = collect(\App\Models\RekapHeader::JENIS_LABELS)
        ->keys()
        ->first(fn ($key) => in_array($key, $aktifJenis));
    $adminMenus = [
        ['key' => 'dashboard', 'label' => 'Beranda', 'icon' => 'dashboard', 'route' => route('dashboard.admin')],
        ['key' => 'users', 'label' => 'Pengguna', 'icon' => 'group', 'route' => route('admin.users.index')],
        ['key' => 'chart', 'label' => 'Grafik & Statistik', 'icon' => 'bar_chart', 'route' => route('admin.rekap.chart')],
        ['key' => 'kecamatan', 'label' => 'Kelola Kecamatan', 'icon' => 'map', 'route' => route('admin.kecamatan.index')],
        ['key' => 'desa', 'label' => 'Kelola Desa', 'icon' => 'location_city', 'route' => route('admin.desa.index')],
        ['key' => 'tps', 'label' => 'Kelola TPS', 'icon' => 'pin_drop', 'route' => route('admin.tps.index')],
        ['key' => 'dokumen', 'label' => 'Rekap Dokumen', 'icon' => 'folder_open', 'route' => route('dokumen.admin')],
        ['key' => 'rekap', 'label' => 'Rekapitulasi Data', 'icon' => 'analytics', 'route' => route('admin.rekap.index')],
        ['key' => 'setup', 'label' => 'Setup Data Pemilu', 'icon' => 'settings', 'route' => route('admin.setup.index')],
    ];
@endphp

<input id="admin-mobile-menu" type="checkbox" class="hidden">
<label for="admin-mobile-menu" class="admin-mobile-overlay"></label>
<aside class="admin-mobile-drawer admin-nav-sidebar flex flex-col border-r">
    <div class="p-5 flex items-center justify-between border-b border-slate-200">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-red-50 border border-red-100 flex items-center justify-center overflow-hidden">
                <img src="{{ asset('images/logo-kpu.png') }}" alt="SIMAP Logo" class="w-8 h-8 object-contain">
            </div>
            <div>
                <p class="text-lg font-extrabold text-[var(--primary)] leading-none">SIMAP</p>
                <p class="font-mono-data text-[10px] uppercase tracking-widest text-slate-500 mt-1">Administrator</p>
            </div>
        </div>
        <label for="admin-mobile-menu" class="cursor-pointer p-2 text-slate-500 hover:text-red-600">
            <span class="material-symbols-outlined">close</span>
        </label>
    </div>
    <nav class="flex-1 py-4 overflow-y-auto">
        @foreach($adminMenus as $menu)
            <a class="admin-nav-item {{ $menu['key'] === 'chart' ? 'active' : '' }}" href="{{ $menu['route'] }}">
                <span class="material-symbols-outlined">{{ $menu['icon'] }}</span>
                <span>{{ $menu['label'] }}</span>
            </a>
        @endforeach
    </nav>
</aside>

<header class="fixed top-0 left-0 right-0 z-50 h-16 bg-white border-b border-slate-200 shadow-sm">
    <div class="h-full px-6 flex items-center justify-between gap-6">
        <div class="flex items-center gap-4 min-w-0">
            <label for="admin-mobile-menu" class="md:hidden cursor-pointer -ml-2 p-2 text-slate-500 hover:text-red-600">
                <span class="material-symbols-outlined text-3xl">menu</span>
            </label>
            <a href="{{ route('dashboard.admin') }}" class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                <img src="{{ asset('images/logo-kpu.png') }}" alt="KPU" class="w-8 h-8 object-contain">
            </a>
            <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-[0.22em] text-slate-500 font-semibold">Sistem Informasi</p>
                <h1 class="text-lg font-extrabold text-[var(--primary)] truncate">Sistem Informasi Manajemen Arsip Pemilu</h1>
            </div>
        </div>

        <div class="hidden lg:flex items-center gap-6">
            <div class="text-right">
                <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-semibold">Wilayah</p>
                <p class="text-sm font-bold text-slate-800">Kabupaten Banyuwangi</p>
            </div>
            <div class="h-8 w-px bg-slate-200"></div>
            <div class="text-right">
                <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-semibold">Operator</p>
                <p class="text-sm font-bold text-slate-800">{{ Auth::user()->name }}</p>
            </div>
        </div>
    </div>
</header>

<main class="chart-shell pt-16 h-screen flex">
    <aside class="admin-nav-sidebar hidden md:flex flex-col w-64 border-r h-full overflow-y-auto shrink-0 z-40">
        <div class="p-6 border-b border-slate-200">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-lg bg-red-50 border border-red-100 flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('images/logo-kpu.png') }}" alt="SIMAP Logo" class="w-8 h-8 object-contain">
                </div>
                <p class="text-xl font-extrabold text-[var(--primary)] leading-none">SIMAP</p>
            </div>
            <span class="font-mono-data text-[10px] uppercase tracking-widest text-red-600 bg-red-50 px-2 py-1 rounded">Administrator</span>
        </div>
        <nav class="flex-1 py-4">
            @foreach($adminMenus as $menu)
                <a class="admin-nav-item {{ $menu['key'] === 'chart' ? 'active' : '' }}" href="{{ $menu['route'] }}">
                    <span class="material-symbols-outlined">{{ $menu['icon'] }}</span>
                    <span>{{ $menu['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </aside>

    <aside class="w-[330px] bg-white text-slate-800 border-r border-slate-200 h-full overflow-y-auto flex flex-col p-6 shadow-xl z-40">
        <div class="mb-7">
            <p class="text-[10px] uppercase tracking-[0.24em] text-slate-500 font-bold mb-2">Filter Utama</p>
            <label class="block text-xs text-slate-600 mb-2 font-semibold">Jenis Pemilihan</label>
            <input type="hidden" id="f-jenis" value="{{ $defaultJenis }}">
            <div id="jenis-buttons" class="grid grid-cols-2 gap-2">
                @foreach(\App\Models\RekapHeader::JENIS_LABELS as $key => $label)
                    @if(in_array($key, $aktifJenis))
                        <button type="button"
                                data-jenis="{{ $key }}"
                                onclick="selectJenis('{{ $key }}')"
                                class="jenis-btn min-h-11 rounded-lg border px-3 py-2 text-left text-xs font-bold leading-tight transition">
                            {{ $label }}
                        </button>
                    @endif
                @endforeach
            </div>

            <div class="mt-5">
                <label class="block text-xs text-slate-600 mb-2 font-semibold">Cari Partai / Caleg</label>
                <div class="relative">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-3 text-slate-400 text-lg">search</span>
                    <input id="f-search"
                           type="search"
                           oninput="applyChartSearch()"
                           onfocus="renderSearchSuggestions()"
                           placeholder="Ketik nama partai atau caleg"
                           autocomplete="off"
                           class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-9 text-sm font-semibold text-slate-800 placeholder:text-slate-400 outline-none focus:border-red-300 focus:ring-2 focus:ring-red-300/30">
                    <button type="button" onclick="clearChartSearch()" class="absolute right-2 top-2 hidden h-7 w-7 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700" id="clear-search">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                    <div id="search-suggestions" class="hidden absolute left-0 right-0 top-full z-[1200] mt-2 max-h-64 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl"></div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-xs text-slate-600 mb-2 font-semibold">Level Tampilan</label>
                <div class="relative">
                    <select id="f-level" onchange="onLevelChange()" class="w-full appearance-none rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 pr-10 text-sm font-semibold text-slate-800 outline-none focus:border-red-300 focus:ring-2 focus:ring-red-300/30">
                        <option value="kabupaten">Kabupaten</option>
                        <option value="dapil" class="hidden">Dapil</option>
                        <option value="kecamatan">Kecamatan</option>
                        <option value="desa">Desa</option>
                        <option value="tps">TPS</option>
                    </select>
                    <span class="material-symbols-outlined pointer-events-none absolute right-3 top-2.5 text-slate-500">expand_more</span>
                </div>
            </div>

            <div id="wrap-dapil" class="hidden">
                <label class="block text-xs text-slate-600 mb-2 font-semibold">Dapil</label>
                <select id="f-dapil" onchange="onDapilChange()" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-800 outline-none focus:border-red-300">
                    <option value="">Pilih Dapil</option>
                    @foreach($dapils as $dapil)
                        <option value="{{ $dapil->id }}">{{ $dapil->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div id="wrap-kec" class="hidden">
                <label class="block text-xs text-slate-600 mb-2 font-semibold">Kecamatan</label>
                <select id="f-kec" onchange="onKecChange()" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-800 outline-none focus:border-red-300">
                    <option value="">Pilih Kecamatan</option>
                    @foreach($kecamatans as $kec)
                        <option value="{{ $kec->id }}">{{ $kec->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div id="wrap-desa" class="hidden">
                <label class="block text-xs text-slate-600 mb-2 font-semibold">Desa</label>
                <select id="f-desa" onchange="onDesaChange()" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-800 outline-none focus:border-red-300">
                    <option value="">Pilih Desa</option>
                </select>
            </div>

            <div id="wrap-tps" class="hidden">
                <label class="block text-xs text-slate-600 mb-2 font-semibold">TPS</label>
                <select id="f-tps" onchange="loadChart()" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-800 outline-none focus:border-red-300">
                    <option value="">Pilih TPS</option>
                </select>
            </div>

            <button id="wrap-reset-kec" onclick="resetKecFilter()" class="hidden w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-100">
                Lihat seluruh kabupaten
            </button>
        </div>

        <div class="my-7 h-px bg-slate-200"></div>

        <div class="mt-auto pt-6">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold mb-2">Status Peta</p>
                <p id="map-selected-label" class="text-sm font-semibold text-slate-800">Klik kecamatan untuk filter</p>
            </div>
        </div>
    </aside>

    <section class="flex-1 h-full overflow-y-auto bg-[var(--surface-low)] p-5">
        <div class="map-panel relative h-[640px] min-h-[520px] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm map-grid">
            <div id="map" class="absolute inset-0"></div>

            <section class="kpi-grid absolute left-6 right-6 top-6 z-[1000] grid grid-cols-4 gap-3">
                <div class="kpi-card glass-panel rounded-xl border border-slate-200 shadow-lg p-4">
                    <p class="text-[10px] uppercase tracking-[0.16em] text-slate-500 font-bold">Total Suara</p>
                    <p id="stat-total-suara" class="font-mono-data text-2xl font-extrabold text-[var(--primary)] mt-2">0</p>
                    <p class="text-xs text-slate-500 mt-1">suara sah</p>
                </div>
                <div class="kpi-card glass-panel rounded-xl border border-slate-200 shadow-lg p-4">
                    <p class="text-[10px] uppercase tracking-[0.16em] text-slate-500 font-bold">TPS Masuk</p>
                    <p id="stat-tps-masuk" class="font-mono-data text-2xl font-extrabold text-[var(--primary)] mt-2">0%</p>
                    <p id="stat-tps-detail" class="text-xs text-slate-500 mt-1">0 / 0 TPS</p>
                </div>
                <div class="kpi-card glass-panel rounded-xl border border-slate-200 shadow-lg p-4">
                    <p class="text-[10px] uppercase tracking-[0.16em] text-slate-500 font-bold">Partisipasi</p>
                    <p id="stat-partisipasi" class="font-mono-data text-2xl font-extrabold text-[var(--red)] mt-2">0%</p>
                    <p id="stat-partisipasi-detail" class="text-xs text-slate-500 mt-1">0 hadir / 0 DPT</p>
                </div>
                <div class="kpi-card glass-panel rounded-xl border border-slate-200 shadow-lg p-4">
                    <p class="text-[10px] uppercase tracking-[0.16em] text-slate-500 font-bold">Selisih Teratas</p>
                    <p id="stat-selisih-teratas" class="font-mono-data text-2xl font-extrabold text-[var(--primary)] mt-2">0%</p>
                    <p id="stat-selisih-detail" class="text-xs text-slate-500 mt-1">Top 1 vs Top 2</p>
                </div>
            </section>

            <div class="map-info absolute left-6 top-36 z-[1000] glass-panel rounded-xl border border-slate-200 shadow-lg px-4 py-3 max-w-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Peta Sebaran</p>
                        <p class="text-sm text-slate-700 mt-1">Warna wilayah mengikuti data pada filter aktif.</p>
                    </div>
                    <button id="map-reset-btn" type="button" onclick="resetKecFilter()" class="hidden shrink-0 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[11px] font-bold text-slate-700 shadow-sm hover:bg-slate-50">
                        Reset
                    </button>
                </div>
            </div>

            <div id="map-legend" class="hidden absolute left-6 bottom-6 z-[1000] glass-panel rounded-xl border border-slate-200 shadow-lg p-4 min-w-56">
                <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold mb-3">Legenda Suara</p>
                <div class="space-y-2">
                    <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#fee2e2"></span><span class="text-xs text-slate-600">Rendah</span></div>
                    <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#fca5a5"></span><span class="text-xs text-slate-600">Menengah rendah</span></div>
                    <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#f87171"></span><span class="text-xs text-slate-600">Menengah</span></div>
                    <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#ef4444"></span><span class="text-xs text-slate-600">Tinggi</span></div>
                    <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#b91c1c"></span><span class="text-xs text-slate-600">Sangat tinggi</span></div>
                </div>
            </div>
        </div>

        <section class="mt-5 rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-200 px-5 py-4">
                <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Tabel Detail Kecamatan</p>
                <p id="detail-table-subtitle" class="text-sm text-slate-600 mt-1">Data mengikuti filter aktif.</p>
            </div>
            <div class="detail-table-scroll overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[10px] uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-bold">Wilayah</th>
                            <th id="detail-subject-header" class="px-5 py-3 font-bold">Pemenang</th>
                            <th class="px-5 py-3 font-bold text-right">Total Suara</th>
                            <th class="px-5 py-3 font-bold text-right">Partisipasi</th>
                            <th class="px-5 py-3 font-bold text-right">TPS Masuk</th>
                        </tr>
                    </thead>
                    <tbody id="detail-table-body" class="divide-y divide-slate-200">
                        <tr>
                            <td colspan="5" class="px-5 py-5 text-center text-sm text-slate-500">Belum ada data ditampilkan.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </section>

    <aside class="w-[410px] bg-white border-l border-slate-200 h-full overflow-y-auto z-40 p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <p class="text-[10px] uppercase tracking-[0.22em] text-slate-500 font-bold">Visualisasi</p>
                <h2 class="text-2xl font-extrabold text-[var(--primary)] mt-1">Ringkasan</h2>
            </div>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[var(--red-soft)] text-[var(--red)]">
                <span class="material-symbols-outlined">bar_chart</span>
            </span>
        </div>

        <div id="chart-placeholder" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
            <span class="material-symbols-outlined text-4xl text-slate-400">query_stats</span>
            <p class="mt-3 text-sm font-semibold text-slate-600">Pilih jenis pemilihan untuk menampilkan grafik.</p>
        </div>

        <div id="chart-loading" class="hidden rounded-xl border border-slate-200 bg-slate-50 p-8 text-center">
            <div class="mx-auto mb-3 h-8 w-8 rounded-full border-2 border-red-600 border-t-transparent animate-spin"></div>
            <p class="text-sm font-semibold text-slate-600">Memuat data grafik...</p>
        </div>

        <div id="chart-error" class="hidden rounded-xl border border-red-200 bg-red-50 p-5 text-sm font-semibold text-red-700"></div>

        <section id="card-kandidat" class="hidden rounded-xl border border-slate-200 bg-slate-50 overflow-hidden mb-5">
            <div class="border-b border-slate-200 px-5 py-4">
                <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Ranking Kandidat</p>
                <p class="text-sm text-slate-600 mt-1">Perolehan suara utama.</p>
            </div>
            <div id="candidate-rank-list" class="candidate-rank-scroll divide-y divide-slate-200">
                <div class="px-5 py-4 text-sm text-slate-500">Belum ada data ditampilkan.</div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-slate-50 overflow-hidden">
            <div class="border-b border-slate-200 px-5 py-4 flex items-center justify-between">
                <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Wilayah Teratas</p>
                <!-- <span class="material-symbols-outlined text-sm text-[var(--red)]">bolt</span> -->
            </div>
            <div id="rank-list" class="divide-y divide-slate-200">
                <div class="px-5 py-4 text-sm text-slate-500">Belum ada data ditampilkan.</div>
            </div>
        </section>

        <section id="card-quick-stats" class="hidden rounded-xl border border-slate-200 bg-slate-50 overflow-hidden mt-5">
            <div class="border-b border-slate-200 px-5 py-4">
                <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Quick Stats</p>
            </div>
            <div class="grid grid-cols-2 gap-3 p-5">
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <p class="text-[10px] uppercase tracking-[0.12em] text-slate-500 font-bold">Partisipasi tertinggi</p>
                    <p id="quick-partisipasi" class="mt-2 text-sm font-extrabold text-slate-800 truncate">-</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <p class="text-[10px] uppercase tracking-[0.12em] text-slate-500 font-bold">DPT terbesar</p>
                    <p id="quick-dpt" class="mt-2 text-sm font-extrabold text-slate-800 truncate">-</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <p class="text-[10px] uppercase tracking-[0.12em] text-slate-500 font-bold">Suara sah tertinggi</p>
                    <p id="quick-suara" class="mt-2 text-sm font-extrabold text-slate-800 truncate">-</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <p class="text-[10px] uppercase tracking-[0.12em] text-slate-500 font-bold">Selisih tertipis</p>
                    <p id="quick-selisih" class="mt-2 text-sm font-extrabold text-slate-800 truncate">-</p>
                </div>
            </div>
        </section>

        <section id="card-demografi" class="hidden rounded-xl border border-slate-200 bg-slate-50 overflow-hidden mt-5">
            <div class="border-b border-slate-200 px-5 py-4">
                <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold">Demografi Pemilih</p>
            </div>
            <div class="space-y-4 p-5">
                <div>
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="font-semibold text-slate-600">Laki-laki</span>
                        <b id="demo-lk-label" class="font-mono-data text-slate-800">0%</b>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-slate-200">
                        <div id="demo-lk-bar" class="h-full rounded-full bg-[var(--primary)]" style="width:0%"></div>
                    </div>
                </div>
                <div>
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="font-semibold text-slate-600">Perempuan</span>
                        <b id="demo-pr-label" class="font-mono-data text-slate-800">0%</b>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-slate-200">
                        <div id="demo-pr-bar" class="h-full rounded-full bg-[var(--red)]" style="width:0%"></div>
                    </div>
                </div>
            </div>
        </section>
    </aside>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
const allKecamatans = @json($kecamatans->map(fn($k) => ['id' => $k->id, 'nama' => $k->nama])->values());
const allDesas = @json($kecamatans->flatMap(fn($k) => $k->desas->map(fn($d) => ['id' => $d->id, 'nama' => $d->nama, 'kecamatan_id' => $k->id]))->values());
const allTps = @json($kecamatans->flatMap(fn($k) => $k->desas->flatMap(fn($d) => $d->tps->map(fn($t) => ['id' => $t->id, 'nama' => $t->nama, 'desa_id' => $d->id])))->values());

let geojsonLayer = null;
let kecamatanData = {};
let selectedKec = null;
let currentChartJson = null;
let kecamatanGeojson = null;
let desaGeojson = null;
let currentMapMode = 'kecamatan';
let currentMapLabels = [];

const WINNER_MAP_TYPES = ['ppwp', 'gubernur', 'bupati'];
const WINNER_COLORS = ['#c81924', '#002147', '#f59e0b', '#10b981', '#7c3aed', '#0891b2', '#db2777', '#ea580c'];

const map = L.map('map', {
    zoomControl: true,
    scrollWheelZoom: true,
}).setView([-8.25, 114.35], 9);

L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OpenStreetMap &copy; CARTO',
    maxZoom: 18,
}).addTo(map);

function formatNumber(value) {
    return (Number(value) || 0).toLocaleString('id-ID');
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function escapeJs(value) {
    return String(value ?? '')
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'")
        .replace(/\n/g, '\\n')
        .replace(/\r/g, '\\r');
}

function normalizeText(value) {
    return String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();
}

function updateJenisButtons() {
    const selected = document.getElementById('f-jenis').value;
    document.querySelectorAll('.jenis-btn').forEach((button) => {
        button.classList.toggle('is-active', button.dataset.jenis === selected);
    });
}

function selectJenis(jenis) {
    if (document.getElementById('f-jenis').value === jenis) return;
    document.getElementById('f-jenis').value = jenis;
    updateJenisButtons();
    onJenisChange();
}

function clearChartSearch() {
    document.getElementById('f-search').value = '';
    hideSearchSuggestions();
    applyChartSearch();
}

function searchSuggestionItems(json = currentChartJson) {
    if (!json) return [];

    const items = (json.labels || []).map((label, index) => ({
        label,
        meta: (json.search_meta?.[index] || label) === label ? '' : json.search_meta?.[index] || '',
        value: label,
    }));

    const candidates = json.candidate_series?.length ? json.candidate_series : (json.candidate_rank || []);
    candidates.forEach((item) => {
        items.push({
            label: item.label,
            meta: item.meta || '',
            value: item.label,
        });
    });

    const seen = new Set();
    return items.filter((item) => {
        const key = normalizeText(`${item.label} ${item.meta}`);
        if (seen.has(key)) return false;
        seen.add(key);
        return true;
    });
}

function renderSearchSuggestions() {
    const box = document.getElementById('search-suggestions');
    const input = document.getElementById('f-search');
    const term = normalizeText(input?.value || '');
    const items = searchSuggestionItems()
        .filter((item) => !term || normalizeText(`${item.label} ${item.meta}`).includes(term))
        .slice(0, 8);

    if (!currentChartJson || !items.length) {
        box.classList.add('hidden');
        box.innerHTML = '';
        return;
    }

    box.innerHTML = items.map((item) => `
        <button type="button"
                onclick="selectSearchSuggestion('${escapeJs(item.value)}')"
                class="block w-full px-4 py-3 text-left hover:bg-slate-50">
            <span class="block text-sm font-bold text-slate-800">${escapeHtml(item.label)}</span>
            ${item.meta ? `<span class="mt-0.5 block text-xs text-slate-500">${escapeHtml(item.meta)}</span>` : ''}
        </button>
    `).join('');
    box.classList.remove('hidden');
}

function selectSearchSuggestion(value) {
    document.getElementById('f-search').value = value;
    hideSearchSuggestions();
    applyChartSearch();
}

function hideSearchSuggestions() {
    const box = document.getElementById('search-suggestions');
    box.classList.add('hidden');
    box.innerHTML = '';
}

function filterChartJson(json) {
    if (!json) return null;

    const term = normalizeText(document.getElementById('f-search')?.value || '');
    document.getElementById('clear-search')?.classList.toggle('hidden', term.length === 0);
    document.getElementById('clear-search')?.classList.toggle('flex', term.length > 0);
    renderSearchSuggestions();

    if (!term) return json;

    const searchMeta = json.search_meta || json.labels || [];
    const partyIndexes = json.labels
        .map((label, index) => ({ label, index }))
        .filter((item) => normalizeText(item.label).includes(term))
        .map((item) => item.index);
    const candidateMatches = partyIndexes.length
        ? []
        : (json.candidate_series || [])
            .filter((item) => normalizeText(`${item.label} ${item.meta || ''}`).includes(term));

    if (candidateMatches.length) {
        const candidateRank = candidateMatches
            .map((item) => ({
                id: item.id,
                label: item.label,
                meta: item.meta || '',
                suara: (item.suara || []).reduce((sum, value) => sum + (Number(value) || 0), 0),
            }))
            .sort((a, b) => b.suara - a.suara);

        return {
            ...json,
            search_mode: 'candidate',
            labels: candidateMatches.map((item) => item.label),
            search_meta: candidateMatches.map((item) => item.meta || ''),
            candidate_rank: candidateRank,
            data: json.data.map((item, groupIndex) => ({
                ...item,
                suara: candidateMatches.map((candidate) => candidate.suara?.[groupIndex] ?? 0),
            })),
        };
    }

    const indexes = partyIndexes.length
        ? partyIndexes
        : json.labels
        .map((label, index) => ({ label, index }))
        .filter((item) => normalizeText(`${item.label} ${searchMeta[item.index] || ''}`).includes(term))
        .map((item) => item.index);
    const selectedParties = new Set(indexes.map((index) => normalizeText(json.labels[index])));

    return {
        ...json,
        search_mode: partyIndexes.length ? 'party' : null,
        labels: indexes.map((index) => json.labels[index]),
        candidate_rank: partyIndexes.length
            ? json.candidate_rank?.filter((item) => selectedParties.has(normalizeText(item.meta || ''))) || []
            : json.candidate_rank?.filter((item) => normalizeText(`${item.label} ${item.meta || ''}`).includes(term)) || [],
        data: json.data.map((item) => ({
            ...item,
            suara: indexes.map((index) => item.suara[index] ?? 0),
        })),
    };
}

function applyChartSearch() {
    if (!currentChartJson) {
        document.getElementById('clear-search')?.classList.toggle('hidden', !document.getElementById('f-search')?.value);
        document.getElementById('clear-search')?.classList.toggle('flex', !!document.getElementById('f-search')?.value);
        hideSearchSuggestions();
        return;
    }

    const filtered = filterChartJson(currentChartJson);
    if (!filtered.labels.length) {
        showError('Partai atau caleg tidak ditemukan pada jenis pemilihan ini.');
        updateStats([]);
        updateRanking([]);
        updateDetailTable(null);
        updateMapColors(null);
        return;
    }

    renderCharts(filtered);
    const level = document.getElementById('f-level').value;
    if (['kabupaten', 'dapil', 'kecamatan'].includes(level)) updateMapColors(filtered);
    else updateMapColors(null);
}

function getColor(val, max) {
    if (!max || max <= 0) return '#e2e8f0';
    const r = val / max;
    if (r > 0.8) return '#b91c1c';
    if (r > 0.6) return '#ef4444';
    if (r > 0.4) return '#f87171';
    if (r > 0.2) return '#fca5a5';
    return '#fee2e2';
}

function isWinnerMapType(jenis = document.getElementById('f-jenis').value) {
    return WINNER_MAP_TYPES.includes(jenis);
}

function featureName(feature) {
    const props = feature.properties || {};
    return currentMapMode === 'desa'
        ? (props.village || props.nama || '')
        : (props.nama || props.sub_district || '');
}

function featureDistrict(feature) {
    const props = feature.properties || {};
    return props.sub_district || props.nama || '';
}

function styleFeature(feature) {
    const nama = featureName(feature);
    const key = normalizeText(nama);
    const values = Object.values(kecamatanData).map((item) => item.total || 0);
    const max = values.length ? Math.max(...values) : 0;
    const item = kecamatanData[key] || { total: 0, winnerIndex: null };
    const sel = currentMapMode === 'kecamatan' && selectedKec && normalizeText(selectedKec) === key;
    const winnerMode = isWinnerMapType();

    return {
        fillColor: winnerMode && item.winnerIndex !== null
            ? WINNER_COLORS[item.winnerIndex % WINNER_COLORS.length]
            : getColor(item.total, max),
        fillOpacity: item.total > 0 ? 0.78 : 0.38,
        color: sel ? '#f59e0b' : '#94a3b8',
        weight: sel ? 3 : 1,
        opacity: 1,
    };
}

function onEachFeature(feature, layer) {
    const nama = featureName(feature);
    layer.bindTooltip(mapTooltipContent(feature), {
        permanent: false,
        direction: 'center',
        className: 'leaflet-tooltip-kec',
    });
    layer.on({
        mouseover: (e) => {
            e.target.setTooltipContent(mapTooltipContent(feature));
            if (currentMapMode !== 'kecamatan' || selectedKec !== nama) e.target.setStyle({ fillOpacity: 0.92, weight: 2 });
        },
        mouseout: (e) => {
            if (currentMapMode !== 'kecamatan' || selectedKec !== nama) geojsonLayer?.resetStyle(e.target);
        },
        click: () => {
            if (currentMapMode === 'kecamatan') selectKecamatan(nama);
        },
    });
}

function mapTooltipContent(feature) {
    const nama = featureName(feature);
    const item = kecamatanData[normalizeText(nama)];

    if (!item || !item.total) {
        return `<div class="map-tooltip"><span class="map-tooltip-title">${escapeHtml(nama)}</span><div class="map-tooltip-row"><span>Belum ada data</span><b>0</b></div></div>`;
    }

    const rows = (item.suara || [])
        .map((suara, index) => ({ label: currentMapLabels[index] || `Calon ${index + 1}`, suara }))
        .sort((a, b) => b.suara - a.suara)
        .slice(0, 5);

    return `
        <div class="map-tooltip">
            <span class="map-tooltip-title">${escapeHtml(nama)}</span>
            ${rows.map((row) => `
                <div class="map-tooltip-row">
                    <span>${escapeHtml(row.label)}</span>
                    <b>${formatNumber(row.suara)}</b>
                </div>
            `).join('')}
        </div>
    `;
}

fetch('/geojson/banyuwangi_kecamatan.geojson')
    .then((response) => response.json())
    .then((data) => {
        kecamatanGeojson = data;
        renderMapLayer('kecamatan');
    })
    .catch(() => showError('Peta kecamatan gagal dimuat.'));

fetch('/geojson/banyuwangi_desa_full.geojson')
    .then((response) => response.json())
    .then((data) => {
        desaGeojson = data;
        if (document.getElementById('f-level').value === 'kecamatan' && selectedKec) {
            renderMapLayer('desa');
            geojsonLayer?.setStyle(styleFeature);
        }
    })
    .catch(() => showError('Peta desa gagal dimuat.'));

function renderMapLayer(mode) {
    const source = mode === 'desa' ? desaGeojson : kecamatanGeojson;
    if (!source) return;

    if (geojsonLayer) map.removeLayer(geojsonLayer);
    currentMapMode = mode;

    geojsonLayer = L.geoJSON(source, {
        filter: (feature) => {
            if (mode !== 'desa') return true;
            return selectedKec && normalizeText(featureDistrict(feature)) === normalizeText(selectedKec);
        },
        style: styleFeature,
        onEachFeature,
    }).addTo(map);

    if (geojsonLayer.getLayers().length) {
        map.fitBounds(geojsonLayer.getBounds());
    }

    setTimeout(() => map.invalidateSize(), 0);
}

function selectKecamatan(namaKec) {
    const kec = allKecamatans.find((item) => normalizeText(item.nama) === normalizeText(namaKec));
    if (!kec) return;

    selectedKec = namaKec;
    document.getElementById('map-selected-label').textContent = `Kecamatan ${kec.nama}`;
    document.getElementById('wrap-reset-kec').classList.remove('hidden');
    document.getElementById('map-reset-btn').classList.remove('hidden');

    const levelSelect = document.getElementById('f-level');
    if (levelSelect.value === 'kabupaten' || levelSelect.value === 'dapil') {
        levelSelect.value = 'kecamatan';
        onLevelChange(false);
    }

    document.getElementById('f-kec').value = kec.id;
    document.getElementById('wrap-kec').classList.remove('hidden');
    document.getElementById('f-desa').innerHTML = '<option value="">Pilih Desa</option>';
    document.getElementById('f-tps').innerHTML = '<option value="">Pilih TPS</option>';

    if (levelSelect.value === 'kecamatan') {
        renderMapLayer('desa');
        loadChart();
        return;
    }

    allDesas.filter((desa) => desa.kecamatan_id == kec.id).forEach((desa) => {
        document.getElementById('f-desa').innerHTML += `<option value="${desa.id}">${desa.nama}</option>`;
    });
    document.getElementById('wrap-desa').classList.remove('hidden');
}

function resetKecFilter() {
    selectedKec = null;
    const jenis = document.getElementById('f-jenis').value;
    const levelSelect = document.getElementById('f-level');
    levelSelect.value = jenis === 'dprd_kab' ? 'dapil' : 'kabupaten';
    document.getElementById('f-kec').value = '';
    document.getElementById('f-dapil').value = '';
    document.getElementById('f-desa').innerHTML = '<option value="">Pilih Desa</option>';
    document.getElementById('f-tps').innerHTML = '<option value="">Pilih TPS</option>';
    document.getElementById('wrap-dapil').classList.toggle('hidden', jenis !== 'dprd_kab');
    document.getElementById('wrap-kec').classList.add('hidden');
    document.getElementById('wrap-desa').classList.add('hidden');
    document.getElementById('wrap-tps').classList.add('hidden');
    document.getElementById('map-selected-label').textContent = 'Klik kecamatan untuk filter';
    document.getElementById('wrap-reset-kec').classList.add('hidden');
    document.getElementById('map-reset-btn').classList.add('hidden');
    renderMapLayer('kecamatan');
    hideCharts();

    if (levelSelect.value === 'kabupaten') loadChart();
}

function updateMapColors(payload) {
    const json = Array.isArray(payload) ? { data: payload, labels: [], jenis: document.getElementById('f-jenis').value } : payload;
    const data = json?.data || [];
    const level = document.getElementById('f-level').value;
    const mode = level === 'kecamatan' && selectedKec ? 'desa' : 'kecamatan';

    if (currentMapMode !== mode) {
        renderMapLayer(mode);
    }

    currentMapLabels = json?.labels || [];
    kecamatanData = {};
    data.forEach((item) => {
        const total = item.suara.reduce((sum, value) => sum + value, 0);
        const winnerIndex = isWinnerMapType(json?.jenis) && total > 0
            ? item.suara.reduce((bestIndex, value, index, values) => value > values[bestIndex] ? index : bestIndex, 0)
            : null;

        kecamatanData[normalizeText(item.label)] = { total, winnerIndex, suara: item.suara };
    });

    geojsonLayer?.setStyle(styleFeature);
    updateMapLegend(json);
}

function updateMapLegend(json) {
    const legend = document.getElementById('map-legend');
    const total = Object.values(kecamatanData).reduce((sum, item) => sum + (item.total || 0), 0);

    if (total <= 0) {
        legend.classList.add('hidden');
        return;
    }

    if (isWinnerMapType(json?.jenis)) {
        legend.innerHTML = `
            <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold mb-3">Legenda Pemenang</p>
            <div class="space-y-2">
                ${(json.labels || []).map((label, index) => `
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded" style="background:${WINNER_COLORS[index % WINNER_COLORS.length]}"></span>
                        <span class="text-xs text-slate-600">${escapeHtml(label)}</span>
                    </div>
                `).join('')}
            </div>
        `;
    } else {
        legend.innerHTML = `
            <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500 font-bold mb-3">Legenda Suara</p>
            <div class="space-y-2">
                <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#fee2e2"></span><span class="text-xs text-slate-600">Rendah</span></div>
                <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#fca5a5"></span><span class="text-xs text-slate-600">Menengah rendah</span></div>
                <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#f87171"></span><span class="text-xs text-slate-600">Menengah</span></div>
                <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#ef4444"></span><span class="text-xs text-slate-600">Tinggi</span></div>
                <div class="flex items-center gap-3"><span class="w-4 h-4 rounded" style="background:#b91c1c"></span><span class="text-xs text-slate-600">Sangat tinggi</span></div>
            </div>
        `;
    }

    legend.classList.remove('hidden');
}

function setDapilMode(enabled) {
    const levelSelect = document.getElementById('f-level');
    const dapilOption = levelSelect.querySelector('option[value="dapil"]');
    dapilOption.classList.toggle('hidden', !enabled);

    if (enabled) {
        levelSelect.value = 'dapil';
    } else if (levelSelect.value === 'dapil') {
        levelSelect.value = 'kabupaten';
    }
}

function onJenisChange() {
    const jenis = document.getElementById('f-jenis').value;
    updateJenisButtons();
    setDapilMode(jenis === 'dprd_kab');
    resetDependentFilters();
    hideCharts();

    if (jenis && jenis !== 'dprd_kab') loadChart();
}

function onLevelChange(shouldLoad = true) {
    const level = document.getElementById('f-level').value;
    const jenis = document.getElementById('f-jenis').value;

    if (level === 'kabupaten' || level === 'dapil') {
        selectedKec = null;
        document.getElementById('map-selected-label').textContent = 'Klik kecamatan untuk filter';
        document.getElementById('wrap-reset-kec').classList.add('hidden');
        document.getElementById('map-reset-btn').classList.add('hidden');
        renderMapLayer('kecamatan');
    }

    document.getElementById('wrap-dapil').classList.toggle('hidden', !(level === 'dapil' || jenis === 'dprd_kab'));
    document.getElementById('wrap-kec').classList.toggle('hidden', level === 'kabupaten' || level === 'dapil');
    document.getElementById('wrap-desa').classList.toggle('hidden', !['desa', 'tps'].includes(level));
    document.getElementById('wrap-tps').classList.toggle('hidden', level !== 'tps');

    document.getElementById('f-kec').value = '';
    document.getElementById('f-dapil').value = '';
    document.getElementById('f-desa').innerHTML = '<option value="">Pilih Desa</option>';
    document.getElementById('f-tps').innerHTML = '<option value="">Pilih TPS</option>';
    hideCharts();

    if (shouldLoad && level === 'kabupaten') loadChart();
}

function resetDependentFilters() {
    document.getElementById('wrap-dapil').classList.toggle('hidden', document.getElementById('f-jenis').value !== 'dprd_kab');
    document.getElementById('wrap-kec').classList.add('hidden');
    document.getElementById('wrap-desa').classList.add('hidden');
    document.getElementById('wrap-tps').classList.add('hidden');
    document.getElementById('f-kec').value = '';
    document.getElementById('f-dapil').value = '';
    document.getElementById('f-desa').innerHTML = '<option value="">Pilih Desa</option>';
    document.getElementById('f-tps').innerHTML = '<option value="">Pilih TPS</option>';
}

function onDapilChange() {
    if (document.getElementById('f-dapil').value) loadChart();
    else hideCharts();
}

function onKecChange() {
    const level = document.getElementById('f-level').value;
    const kecId = document.getElementById('f-kec').value;
    document.getElementById('f-desa').innerHTML = '<option value="">Pilih Desa</option>';
    document.getElementById('f-tps').innerHTML = '<option value="">Pilih TPS</option>';
    hideCharts();

    if (!kecId) return;
    const kec = allKecamatans.find((item) => item.id == kecId);
    selectedKec = kec?.nama || null;
    document.getElementById('map-selected-label').textContent = kec ? `Kecamatan ${kec.nama}` : 'Klik kecamatan untuk filter';
    document.getElementById('wrap-reset-kec').classList.toggle('hidden', !kec);
    document.getElementById('map-reset-btn').classList.toggle('hidden', !kec);

    if (level === 'kecamatan') {
        renderMapLayer('desa');
        loadChart();
        return;
    }

    allDesas.filter((desa) => desa.kecamatan_id == kecId).forEach((desa) => {
        document.getElementById('f-desa').innerHTML += `<option value="${desa.id}">${desa.nama}</option>`;
    });
    document.getElementById('wrap-desa').classList.remove('hidden');
}

function onDesaChange() {
    const level = document.getElementById('f-level').value;
    const desaId = document.getElementById('f-desa').value;
    document.getElementById('f-tps').innerHTML = '<option value="">Pilih TPS</option>';
    hideCharts();

    if (!desaId) return;
    if (level === 'desa') {
        loadChart();
        return;
    }

    allTps.filter((tps) => tps.desa_id == desaId).forEach((tps) => {
        document.getElementById('f-tps').innerHTML += `<option value="${tps.id}">${tps.nama}</option>`;
    });
    document.getElementById('wrap-tps').classList.remove('hidden');
}

function hideCharts() {
    currentChartJson = null;
    document.getElementById('chart-placeholder').classList.remove('hidden');
    document.getElementById('chart-loading').classList.add('hidden');
    document.getElementById('chart-error').classList.add('hidden');
    document.getElementById('card-kandidat').classList.add('hidden');
    document.getElementById('card-quick-stats').classList.add('hidden');
    document.getElementById('card-demografi').classList.add('hidden');
    updateCandidateRanking(null);
    updateQuickStats([]);
    updateDemographics([]);
    updateDetailTable(null);
    updateMapColors(null);
}

function showError(message) {
    document.getElementById('chart-placeholder').classList.add('hidden');
    document.getElementById('chart-loading').classList.add('hidden');
    document.getElementById('card-kandidat').classList.add('hidden');
    document.getElementById('card-quick-stats').classList.add('hidden');
    document.getElementById('card-demografi').classList.add('hidden');
    updateCandidateRanking(null);
    updateQuickStats([]);
    updateDemographics([]);
    updateDetailTable(null);
    updateMapColors(null);
    document.getElementById('chart-error').textContent = message;
    document.getElementById('chart-error').classList.remove('hidden');
}

async function loadChart() {
    const jenis = document.getElementById('f-jenis').value;
    if (!jenis) return;

    const level = document.getElementById('f-level').value;
    const kecId = document.getElementById('f-kec').value;
    const desaId = document.getElementById('f-desa').value;
    const tpsId = document.getElementById('f-tps').value;
    const dapilId = document.getElementById('f-dapil').value;

    if (level === 'dapil' && !dapilId) return;
    if (level === 'kecamatan' && !kecId) return;
    if (level === 'desa' && !desaId) return;
    if (level === 'tps' && !tpsId) return;

    document.getElementById('chart-placeholder').classList.add('hidden');
    document.getElementById('chart-error').classList.add('hidden');
    document.getElementById('card-kandidat').classList.add('hidden');
    document.getElementById('card-quick-stats').classList.add('hidden');
    document.getElementById('card-demografi').classList.add('hidden');
    document.getElementById('chart-loading').classList.remove('hidden');

    const params = new URLSearchParams({ jenis, level });
    if (dapilId) params.set('dapil_id', dapilId);
    if (kecId) params.set('kecamatan_id', kecId);
    if (desaId) params.set('desa_id', desaId);
    if (tpsId) params.set('tps_id', tpsId);

    try {
        const res = await fetch('{{ route("admin.rekap.chart.data") }}?' + params);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const json = await res.json();
        currentChartJson = json;
        applyChartSearch();
    } catch (error) {
        console.error(error);
        showError('Gagal memuat data grafik. Periksa koneksi atau data rekap.');
    } finally {
        document.getElementById('chart-loading').classList.add('hidden');
    }
}

function renderCharts(json) {
    if (!json.data || !json.data.length) {
        showError('Data belum tersedia untuk filter ini.');
        updateStats([]);
        updateCandidateRanking(null);
        updateRanking([]);
        updateQuickStats([]);
        updateDemographics([]);
        updateDetailTable(null);
        return;
    }

    document.getElementById('chart-placeholder').classList.add('hidden');
    document.getElementById('chart-error').classList.add('hidden');
    document.getElementById('chart-loading').classList.add('hidden');

    updateStats(json);
    updateCandidateRanking(json);
    updateRanking(json.data);
    updateQuickStats(json.data);
    updateDemographics(json.data);
    updateDetailTable(json);
}

function updateCandidateRanking(json) {
    const card = document.getElementById('card-kandidat');
    const target = document.getElementById('candidate-rank-list');

    if (!json?.labels?.length || !json?.data?.length) {
        target.innerHTML = '<div class="px-5 py-4 text-sm text-slate-500">Belum ada data ditampilkan.</div>';
        return;
    }

    const rankSource = json.candidate_rank?.length
        ? json.candidate_rank
        : json.labels
            .map((label, index) => ({
                label,
                meta: '',
                suara: json.data.reduce((sum, item) => sum + (item.suara[index] || 0), 0),
            }))
            .sort((a, b) => b.suara - a.suara);
    const totalSuara = rankSource.reduce((sum, item) => sum + (Number(item.suara) || 0), 0);
    const term = normalizeText(document.getElementById('f-search')?.value || '');
    const isPartySearch = term.length > 0 && (json.labels || []).some((label) => normalizeText(label).includes(term));
    const rank = rankSource.slice(0, isPartySearch ? rankSource.length : 20);

    target.innerHTML = rank.map((item, index) => {
        const persen = totalSuara > 0 ? Math.round((item.suara / totalSuara) * 1000) / 10 : 0;
        const meta = item.meta ? `<span class="font-semibold text-slate-600">${escapeHtml(item.meta)}</span> &bull; ` : '';
        return `
            <div class="px-5 py-4 flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-slate-800 truncate">${index + 1}. ${escapeHtml(item.label)}</p>
                    <p class="text-xs text-slate-500 mt-0.5">${meta}${formatNumber(item.suara)} suara &bull; ${persen}%</p>
                </div>
            </div>
        `;
    }).join('');

    card.classList.remove('hidden');
}

function updateStats(payload) {
    const data = Array.isArray(payload) ? payload : (payload?.data || []);
    const totalSuara = data.reduce((sum, item) => sum + item.suara.reduce((a, b) => a + b, 0), 0);
    const totalDpt = data.reduce((sum, item) => sum + (item.partisipasi?.dpt || 0), 0);
    const totalHadir = data.reduce((sum, item) => sum + (item.partisipasi?.hadir || 0), 0);
    const totalTpsMasuk = data.reduce((sum, item) => sum + (item.partisipasi?.tps_masuk || 0), 0);
    const totalTps = data.reduce((sum, item) => sum + (item.partisipasi?.tps_total || 0), 0);
    const partisipasiPersen = totalDpt > 0 ? Math.round((totalHadir / totalDpt) * 1000) / 10 : 0;
    const tpsPersen = totalTps > 0 ? Math.round((totalTpsMasuk / totalTps) * 1000) / 10 : 0;

    let sortedTotals = [];
    if (!Array.isArray(payload) && payload?.candidate_rank?.length) {
        sortedTotals = payload.candidate_rank.map((item) => Number(item.suara) || 0).sort((a, b) => b - a);
    } else {
        const totalsByCandidate = [];
        data.forEach((item) => {
            item.suara.forEach((suara, index) => {
                totalsByCandidate[index] = (totalsByCandidate[index] || 0) + suara;
            });
        });
        sortedTotals = totalsByCandidate.sort((a, b) => b - a);
    }
    const topMargin = sortedTotals.length > 1 ? sortedTotals[0] - sortedTotals[1] : 0;
    const topMarginPersen = totalSuara > 0 ? Math.round((topMargin / totalSuara) * 1000) / 10 : 0;

    document.getElementById('stat-total-suara').textContent = formatNumber(totalSuara);
    document.getElementById('stat-tps-masuk').textContent = `${tpsPersen}%`;
    document.getElementById('stat-tps-detail').textContent = `${formatNumber(totalTpsMasuk)} / ${formatNumber(totalTps)} TPS`;
    document.getElementById('stat-partisipasi').textContent = `${partisipasiPersen}%`;
    document.getElementById('stat-partisipasi-detail').textContent = `${formatNumber(totalHadir)} hadir / ${formatNumber(totalDpt)} DPT`;
    document.getElementById('stat-selisih-teratas').textContent = `${topMarginPersen}%`;
    document.getElementById('stat-selisih-detail').textContent = `${formatNumber(topMargin)} suara`;
}

function updateDetailTable(json) {
    const target = document.getElementById('detail-table-body');
    const subtitle = document.getElementById('detail-table-subtitle');
    const subjectHeader = document.getElementById('detail-subject-header');

    if (!json?.data?.length) {
        target.innerHTML = '<tr><td colspan="5" class="px-5 py-5 text-center text-sm text-slate-500">Belum ada data ditampilkan.</td></tr>';
        subtitle.textContent = 'Data mengikuti filter aktif.';
        subjectHeader.textContent = 'Pemenang';
        return;
    }

    const levelLabels = {
        kabupaten: 'Kecamatan',
        dapil: 'Kecamatan',
        kecamatan: 'Desa',
        desa: 'TPS',
        tps: 'TPS',
    };
    const level = document.getElementById('f-level').value;
    const candidateMode = json.search_mode === 'candidate';
    subtitle.textContent = candidateMode
        ? `Perolehan suara caleg per ${levelLabels[level] || 'wilayah'} pada filter aktif.`
        : `Detail per ${levelLabels[level] || 'wilayah'} pada filter aktif.`;
    subjectHeader.textContent = candidateMode ? 'Caleg' : 'Pemenang';

    target.innerHTML = json.data.map((item) => {
        const totalSuara = item.suara.reduce((sum, value) => sum + value, 0);
        const winnerIndex = item.suara.reduce((bestIndex, value, index, values) => value > values[bestIndex] ? index : bestIndex, 0);
        const pemenang = candidateMode
            ? (json.labels.length === 1 ? json.labels[0] : (json.labels[winnerIndex] || '-'))
            : (totalSuara > 0 ? (json.labels[winnerIndex] || '-') : '-');
        const dpt = item.partisipasi?.dpt || 0;
        const hadir = item.partisipasi?.hadir || 0;
        const partisipasi = dpt > 0 ? Math.round((hadir / dpt) * 1000) / 10 : 0;
        const tpsMasuk = item.partisipasi?.tps_masuk || 0;
        const tpsTotal = item.partisipasi?.tps_total || 0;
        const tpsPersen = tpsTotal > 0 ? Math.round((tpsMasuk / tpsTotal) * 1000) / 10 : 0;

        return `
            <tr class="hover:bg-slate-50">
                <td class="px-5 py-4 font-bold text-slate-800">${escapeHtml(item.label)}</td>
                <td class="px-5 py-4 text-slate-600">${escapeHtml(pemenang)}</td>
                <td class="px-5 py-4 text-right font-mono-data font-bold text-[var(--primary)]">${formatNumber(totalSuara)}</td>
                <td class="px-5 py-4 text-right font-mono-data text-slate-700">${partisipasi}%</td>
                <td class="px-5 py-4 text-right font-mono-data text-slate-700">${tpsPersen}%</td>
            </tr>
        `;
    }).join('');
}

function updateRanking(data) {
    const rank = [...data]
        .map((item) => ({
            label: item.label,
            suara: item.suara.reduce((a, b) => a + b, 0),
            dpt: item.partisipasi?.dpt || 0,
            hadir: item.partisipasi?.hadir || 0,
        }))
        .sort((a, b) => b.suara - a.suara)
        .slice(0, 5);

    const target = document.getElementById('rank-list');
    if (!rank.length) {
        target.innerHTML = '<div class="px-5 py-4 text-sm text-slate-500">Belum ada data ditampilkan.</div>';
        return;
    }

    target.innerHTML = rank.map((item, index) => {
        const persen = item.dpt > 0 ? Math.round((item.hadir / item.dpt) * 1000) / 10 : 0;
        return `
            <div class="px-5 py-4 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-slate-800 truncate">${index + 1}. ${item.label}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Partisipasi ${persen}%</p>
                </div>
                <p class="font-mono-data text-sm font-bold text-[var(--red)]">${formatNumber(item.suara)}</p>
            </div>
        `;
    }).join('');
}

function updateQuickStats(data) {
    const card = document.getElementById('card-quick-stats');
    const defaults = {
        'quick-partisipasi': '-',
        'quick-dpt': '-',
        'quick-suara': '-',
        'quick-selisih': '-',
    };

    if (!data.length) {
        Object.entries(defaults).forEach(([id, value]) => document.getElementById(id).textContent = value);
        return;
    }

    const rows = data.map((item) => {
        const suaraTotal = item.suara.reduce((a, b) => a + b, 0);
        const sortedSuara = [...item.suara].sort((a, b) => b - a);
        const dpt = item.partisipasi?.dpt || 0;
        const hadir = item.partisipasi?.hadir || 0;

        return {
            label: item.label,
            suara: suaraTotal,
            dpt,
            persen: dpt > 0 ? (hadir / dpt) * 100 : 0,
            margin: sortedSuara.length > 1 ? sortedSuara[0] - sortedSuara[1] : null,
        };
    });

    const byPartisipasi = [...rows].sort((a, b) => b.persen - a.persen)[0];
    const byDpt = [...rows].sort((a, b) => b.dpt - a.dpt)[0];
    const bySuara = [...rows].sort((a, b) => b.suara - a.suara)[0];
    const byMargin = rows
        .filter((item) => item.margin !== null)
        .sort((a, b) => a.margin - b.margin)[0];

    document.getElementById('quick-partisipasi').textContent = byPartisipasi?.label || '-';
    document.getElementById('quick-dpt').textContent = byDpt?.label || '-';
    document.getElementById('quick-suara').textContent = bySuara?.label || '-';
    document.getElementById('quick-selisih').textContent = byMargin?.label || '-';
    card.classList.remove('hidden');
}

function updateDemographics(data) {
    const card = document.getElementById('card-demografi');
    const totalLk = data.reduce((sum, item) => sum + (item.partisipasi?.dpt_lk || 0), 0);
    const totalPr = data.reduce((sum, item) => sum + (item.partisipasi?.dpt_pr || 0), 0);
    const total = totalLk + totalPr;
    const persenLk = total > 0 ? Math.round((totalLk / total) * 1000) / 10 : 0;
    const persenPr = total > 0 ? Math.round((totalPr / total) * 1000) / 10 : 0;

    document.getElementById('demo-lk-label').textContent = `${persenLk}%`;
    document.getElementById('demo-pr-label').textContent = `${persenPr}%`;
    document.getElementById('demo-lk-bar').style.width = `${Math.min(100, persenLk)}%`;
    document.getElementById('demo-pr-bar').style.width = `${Math.min(100, persenPr)}%`;

    if (data.length) card.classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    const jenis = document.getElementById('f-jenis').value;
    updateJenisButtons();
    setDapilMode(jenis === 'dprd_kab');
    if (jenis && jenis !== 'dprd_kab') loadChart();
});

document.addEventListener('click', (event) => {
    if (!event.target.closest('#f-search') && !event.target.closest('#search-suggestions')) {
        hideSearchSuggestions();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') hideSearchSuggestions();
});
</script>
</body>
</html>
