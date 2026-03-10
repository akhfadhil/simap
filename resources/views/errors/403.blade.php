<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMAP — 403 Akses Ditolak</title>
    <script>
        (function() {
            const saved  = localStorage.getItem('theme') || 'dark';
            document.documentElement.classList.toggle('dark', saved === 'dark');
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Bebas+Neue&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { fontFamily: { display: ['Bebas Neue', 'sans-serif'], sans: ['Plus Jakarta Sans', 'sans-serif'] } } }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-display { font-family: 'Bebas Neue', sans-serif; }
    </style>
</head>
<body class="dark:bg-gray-950 bg-slate-100 dark:text-gray-100 text-gray-800 min-h-screen flex flex-col">

    {-- Topbar minimal --}
    <header class="dark:bg-gray-900 bg-white border-b dark:border-gray-800 border-gray-200 px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-center gap-3">
            <div class="w-7 h-7 bg-red-600 flex items-center justify-center rounded">
                <span class="font-display text-white text-sm leading-none">KPU</span>
            </div>
            <p class="font-display text-base leading-none dark:text-white text-gray-900 tracking-wide">SIMAP</p>
        </div>
    </header>

    {-- Content --}
    <main class="flex-1 flex items-center justify-center px-4 py-16">
        <div class="text-center max-w-md w-full">

            {-- Icon --}
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 rounded-2xl bg-red-500/10 border border-red-500/20 flex items-center justify-center">
                    <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>

            {-- Code --}
            <p class="font-display text-8xl text-red-400 leading-none mb-2">403</p>

            {-- Title --}
            <h1 class="text-xl font-semibold dark:text-gray-100 text-gray-800 mb-3">Akses Ditolak</h1>

            {-- Desc --}
            <p class="text-sm dark:text-gray-400 text-gray-500 leading-relaxed mb-8">Kamu tidak memiliki izin untuk mengakses halaman ini.</p>

            {-- Message from Laravel (jika ada) --}
            @if(!empty($exception) && config('app.debug') && $exception->getMessage())
            <div class="mb-6 text-left dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 overflow-hidden shadow-sm">
                <div class="px-4 py-2.5 border-b dark:border-gray-700 border-gray-200 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>
                    <p class="text-[10px] tracking-widest uppercase font-semibold dark:text-gray-500 text-gray-400">Pesan Error</p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-xs dark:text-gray-300 text-gray-600 font-mono break-all leading-relaxed">
                        { $exception->getMessage() }
                    </p>
                </div>
                @if($exception->getFile())
                <div class="px-4 py-2.5 border-t dark:border-gray-700 border-gray-200">
                    <p class="text-[11px] dark:text-gray-500 text-gray-400">
                        <span class="font-semibold">File:</span>
                        { str_replace(base_path(), '', $exception->getFile()) }
                        <span class="font-semibold ml-2">Line:</span>
                        { $exception->getLine() }
                    </p>
                </div>
                @endif
            </div>
            @endif

            {-- Actions --}
            <div class="flex gap-3 justify-center flex-wrap">
                <a href="javascript:history.back()"
                   class="px-5 py-2.5 rounded-xl text-sm font-semibold border dark:border-gray-700 border-gray-300
                          dark:text-gray-400 text-gray-500 dark:hover:bg-gray-800 hover:bg-gray-100 transition">
                    ← Kembali
                </a>
                @auth
                <a href="{ route('dashboard.' . Auth::user()->role) }"
                   class="px-5 py-2.5 rounded-xl text-sm font-semibold bg-red-600 hover:bg-red-700 text-white transition">
                    Ke Dashboard
                </a>
                @else
                <a href="{ route('login') }"
                   class="px-5 py-2.5 rounded-xl text-sm font-semibold bg-red-600 hover:bg-red-700 text-white transition">
                    Login
                </a>
                @endauth
            </div>

        </div>
    </main>

    {-- Footer --}
    <footer class="text-center py-4 text-[11px] dark:text-gray-700 text-gray-400">
        SIMAP &copy; { date('Y') } — KPU Banyuwangi
    </footer>

</body>
</html>
