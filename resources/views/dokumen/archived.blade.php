<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMAP — Dokumen Diarsipkan</title>
    <script>
        (function() {
            const saved = localStorage.getItem('theme') || 'dark';
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
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; } .font-display { font-family: 'Bebas Neue', sans-serif; }</style>
</head>
<body class="dark:bg-gray-950 bg-slate-100 dark:text-gray-100 text-gray-800 min-h-screen flex flex-col">

    {{-- Topbar --}}
    <header class="dark:bg-gray-900 bg-white border-b dark:border-gray-800 border-gray-200 px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-center gap-3">
            <div class="w-7 h-7 bg-red-600 flex items-center justify-center rounded">
                <span class="font-display text-white text-sm leading-none">KPU</span>
            </div>
            <p class="font-display text-base leading-none dark:text-white text-gray-900 tracking-wide">SIMAP</p>
        </div>
    </header>

    {{-- Content --}}
    <main class="flex-1 flex items-center justify-center px-4 py-16">
        <div class="text-center max-w-md w-full">

            {{-- Icon --}}
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center">
                    <svg class="w-10 h-10 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
            </div>

            <p class="font-display text-5xl text-amber-400 leading-none mb-3">DIARSIPKAN</p>
            <h1 class="text-lg font-semibold dark:text-gray-100 text-gray-800 mb-2">File Telah Diarsipkan</h1>
            <p class="text-sm dark:text-gray-400 text-gray-500 leading-relaxed mb-6">
                File ini sudah dipindahkan ke storage backup dan tidak dapat dipreview secara langsung.
            </p>

            {{-- Info dokumen --}}
            <div class="dark:bg-gray-800 bg-white rounded-xl border dark:border-gray-700 border-gray-200 text-left mb-6 overflow-hidden shadow-sm">
                <div class="px-4 py-2.5 border-b dark:border-gray-700 border-gray-200">
                    <p class="text-[10px] tracking-widest uppercase font-semibold dark:text-gray-500 text-gray-400">Info Dokumen</p>
                </div>
                <div class="divide-y dark:divide-gray-700 divide-gray-100">
                    <div class="px-4 py-2.5 flex justify-between items-center">
                        <span class="text-xs dark:text-gray-500 text-gray-400">Nama File</span>
                        <span class="text-xs font-medium dark:text-gray-200 text-gray-700">{{ $dokumen->file_name }}</span>
                    </div>
                    <div class="px-4 py-2.5 flex justify-between items-center">
                        <span class="text-xs dark:text-gray-500 text-gray-400">Jenis</span>
                        <span class="text-xs font-medium dark:text-gray-200 text-gray-700">{{ \App\Models\Dokumen::JENIS[$dokumen->jenis] ?? $dokumen->jenis }}</span>
                    </div>
                    <div class="px-4 py-2.5 flex justify-between items-center">
                        <span class="text-xs dark:text-gray-500 text-gray-400">Diarsipkan</span>
                        <span class="text-xs font-medium dark:text-gray-200 text-gray-700">
                            {{ $dokumen->archived_at ? $dokumen->archived_at->format('d/m/Y H:i') : '-' }}
                        </span>
                    </div>
                    <div class="px-4 py-2.5 flex justify-between items-center">
                        <span class="text-xs dark:text-gray-500 text-gray-400">Status</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-400 border border-amber-500/25 font-semibold uppercase tracking-wider">
                            Diarsipkan
                        </span>
                    </div>
                </div>
            </div>

            {{-- Tombol restore (admin only) --}}
            @if($isAdmin)
            <div class="mb-4">
                <form method="POST" action="{{ route('dokumen.restore', $dokumen) }}"
                      onsubmit="return confirm('Restore file ini dari backup ke storage aktif?')">
                    @csrf
                    <button class="w-full px-5 py-2.5 rounded-xl text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white transition">
                        ↩ Restore File dari Backup
                    </button>
                </form>
            </div>
            @else
            <p class="text-xs dark:text-gray-500 text-gray-400 mb-4">
                Hubungi administrator untuk meminta restore file ini.
            </p>
            @endif

            <button onclick="window.close(); history.back();"
                    class="w-full px-5 py-2.5 rounded-xl text-sm font-semibold border dark:border-gray-700 border-gray-300
                           dark:text-gray-400 text-gray-500 dark:hover:bg-gray-800 hover:bg-gray-100 transition">
                ← Kembali
            </button>

        </div>
    </main>

</body>
</html>
