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
</body>
</html>