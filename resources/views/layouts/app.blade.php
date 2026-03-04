<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPEMILU — @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#070707] text-gray-100 min-h-screen">

    <nav class="sticky top-0 z-10 h-[60px] border-b border-gray-800 bg-[#070707] flex items-center justify-between px-8">
        <div class="font-display text-2xl tracking-widest">
            SIM<span class="text-brand">PEMILU</span>
        </div>
        <div class="flex items-center gap-5">
            <span class="font-mono2 text-[10px] tracking-widest uppercase px-3 py-1 border"
                  style="color:{{ Auth::user()->roleColor() }};border-color:{{ Auth::user()->roleColor() }}44;background:{{ Auth::user()->roleColor() }}11">
                {{ strtoupper(Auth::user()->role) }}
            </span>
            <span class="text-sm text-gray-500">{{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="border border-gray-800 text-gray-500 px-4 py-1.5 font-mono2 text-[10px] tracking-widest uppercase hover:border-brand hover:text-brand transition">
                    KELUAR
                </button>
            </form>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-8 py-10">
        @yield('content')
    </main>
    @stack('scripts')
    {{-- Modal Preview PDF --}}
    <div id="pdf-modal" class="hidden fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4">
        <div class="bg-[#141414] border border-gray-800 w-full max-w-5xl h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-3 border-b border-gray-800 flex-shrink-0">
                <span class="font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase">// Preview Dokumen</span>
                <button onclick="closePreview()"
                        class="text-gray-600 hover:text-gray-400 font-mono2 text-[10px] uppercase tracking-widest">
                    ✕ TUTUP
                </button>
            </div>
            <iframe id="pdf-frame" src="" class="flex-1 w-full bg-white"></iframe>
        </div>
    </div>

    <script>
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
    // Tutup kalau klik backdrop
    document.getElementById('pdf-modal').addEventListener('click', function(e) {
        if (e.target === this) closePreview();
    });
</script>
</body>
</html>