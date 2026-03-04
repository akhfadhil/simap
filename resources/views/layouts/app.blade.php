<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPEG PEMILU — @yield('title', 'Dashboard')</title>
    <script>
        (function() {
            const saved  = localStorage.getItem('theme') || 'dark';
            const isDark = saved === 'dark';
            document.documentElement.classList.toggle('dark', isDark);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Bebas+Neue&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-display { font-family: 'Bebas Neue', sans-serif; }
        .font-mono2 { font-family: 'Plus Jakarta Sans', monospace; }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        .dark ::-webkit-scrollbar-thumb { background: #374151; border-radius: 4px; }
        html:not(.dark) ::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 4px; }

        .role-ppk   { background: rgba(244,162,97,0.15); color: #F4A261; border: 1px solid rgba(244,162,97,0.35); }
        .role-pps   { background: rgba(46,196,182,0.15); color: #2EC4B6; border: 1px solid rgba(46,196,182,0.35); }
        .role-kpps  { background: rgba(168,218,220,0.15); color: #5BA4CF; border: 1px solid rgba(168,218,220,0.35); }
        .role-admin { background: rgba(220,38,38,0.15); color: #DC2626; border: 1px solid rgba(220,38,38,0.35); }
    </style>
</head>

<body class="dark:bg-gray-950 bg-slate-200 dark:text-gray-100 text-gray-800 min-h-screen transition-colors duration-200">

{{-- ── TOPBAR ────────────────────────────────────────────── --}}
<header class="sticky top-0 z-50 dark:bg-gray-900 bg-white border-b dark:border-gray-800 border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 h-16 flex items-center justify-between gap-4">

        {{-- Brand --}}
        <a href="{{ route('dashboard.' . Auth::user()->role) }}" class="flex items-center gap-3 flex-shrink-0">
            <div class="w-8 h-8 bg-red-600 flex items-center justify-center rounded">
                <span class="font-display text-white text-base leading-none">KPU</span>
            </div>
            <div class="hidden sm:block">
                <p class="font-display text-lg leading-none dark:text-white text-gray-900 tracking-wide">SIMPEG PEMILU</p>
                <p class="text-[9px] dark:text-gray-500 text-gray-400 tracking-widest uppercase">Sistem Informasi Manajemen</p>
            </div>
        </a>

        {{-- Page title (center, desktop) --}}
        <p class="hidden lg:block text-sm font-medium dark:text-gray-400 text-gray-500 flex-1 text-center">
            @yield('title', 'Dashboard')
        </p>

        {{-- Right side --}}
        <div class="flex items-center gap-2 flex-shrink-0">

            {{-- Dark/Light toggle --}}
            <button onclick="toggleTheme()"
                    class="p-2 rounded-lg dark:text-gray-400 text-gray-500 dark:hover:bg-gray-800 hover:bg-gray-100 transition">
                <svg id="icon-sun" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <svg id="icon-moon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            {{-- Divider --}}
            <div class="w-px h-6 dark:bg-gray-700 bg-gray-200"></div>

            {{-- User info --}}
            <div class="flex items-center gap-2.5">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-semibold dark:text-gray-200 text-gray-700 leading-tight">{{ Auth::user()->name }}</p>
                    <span class="inline-block text-[9px] px-1.5 py-0.5 rounded-sm tracking-widest uppercase font-semibold role-{{ Auth::user()->role }}">
                        {{ strtoupper(Auth::user()->role) }}
                    </span>
                </div>
                <div class="w-8 h-8 rounded-full bg-red-600/20 flex items-center justify-center border dark:border-red-900 border-red-200">
                    <span class="text-red-500 text-xs font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </span>
                </div>
            </div>

            {{-- Divider --}}
            <div class="w-px h-6 dark:bg-gray-700 bg-gray-200"></div>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium dark:text-gray-400 text-gray-500 dark:hover:bg-gray-800 hover:bg-gray-100 hover:text-red-500 dark:hover:text-red-400 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="hidden sm:inline">Keluar</span>
                </button>
            </form>

        </div>
    </div>
</header>

{{-- ── CONTENT ───────────────────────────────────────────── --}}
<main class="max-w-7xl mx-auto px-4 lg:px-8 py-6">
    @yield('content')
</main>

{{-- Modal Preview PDF --}}
<div id="pdf-modal" class="hidden fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4">
    <div class="dark:bg-gray-900 bg-white border dark:border-gray-700 border-gray-200 w-full max-w-5xl h-[90vh] flex flex-col rounded-lg overflow-hidden shadow-2xl">
        <div class="flex items-center justify-between px-5 py-3 border-b dark:border-gray-700 border-gray-200 flex-shrink-0">
            <span class="text-sm font-semibold dark:text-gray-300 text-gray-600">Preview Dokumen</span>
            <button onclick="closePreview()"
                    class="px-3 py-1.5 rounded-lg text-xs dark:text-gray-400 text-gray-500 dark:hover:bg-gray-800 hover:bg-gray-100 transition">
                ✕ Tutup
            </button>
        </div>
        <iframe id="pdf-frame" src="" class="flex-1 w-full bg-white"></iframe>
    </div>
</div>

@stack('scripts')

<script>
    function updateThemeIcon(isDark) {
        const sun  = document.getElementById('icon-sun');
        const moon = document.getElementById('icon-moon');
        if (!sun || !moon) return;
        sun.classList.toggle('hidden', !isDark);
        moon.classList.toggle('hidden', isDark);
    }

    function toggleTheme() {
        const isDark = document.documentElement.classList.contains('dark');
        document.documentElement.classList.toggle('dark', !isDark);
        localStorage.setItem('theme', isDark ? 'light' : 'dark');
        updateThemeIcon(!isDark);
    }

    // Set icon sesuai tema saat ini
    updateThemeIcon(document.documentElement.classList.contains('dark'));

    // PDF Preview
    function openPreview(url) {
        document.getElementById('pdf-frame').src = url;
        document.getElementById('pdf-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closePreview() {
        document.getElementById('pdf-frame').src = '';
        document.getElementById('pdf-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }
    document.getElementById('pdf-modal').addEventListener('click', function(e) {
        if (e.target === this) closePreview();
    });
</script>

</body>
</html>