@extends('layouts.guest')

@section('content')
<div class="relative z-10 w-full max-w-md px-5">

    {{-- Brand --}}
    <div class="text-center mb-10">
        <span class="inline-block bg-brand text-white font-mono2 text-[10px] tracking-[3px] px-3 py-1 mb-4">KPU RI — 2024</span>
        <h1 class="font-display text-6xl tracking-[4px]">SIM<span class="text-brand">PEMILU</span></h1>
        <p class="text-[11px] text-gray-600 tracking-[2px] uppercase mt-2">Sistem Informasi Manajemen Pemilu</p>
    </div>

    {{-- Card --}}
    <div class="bg-[#141414] border border-gray-800 p-9">
        <p class="font-mono2 text-[10px] tracking-[3px] text-gray-600 uppercase mb-7">// Masuk ke Sistem</p>

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            @if ($errors->any())
            <div class="bg-red-950 border border-red-800 text-red-400 px-4 py-3 font-mono2 text-xs mb-6">
                ⚠ {{ $errors->first() }}
            </div>
            @endif

            <div class="mb-5">
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Username</label>
                <input type="text" name="username" value="{{ old('username') }}" placeholder="Masukkan username"
                       class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3.5 text-sm focus:border-brand focus:ring-0 focus:outline-none">
            </div>

            <div class="mb-6">
                <label class="block font-mono2 text-[10px] tracking-[2px] text-gray-600 uppercase mb-2">Password</label>
                <input type="password" name="password" placeholder="••••••••"
                       class="w-full bg-[#070707] border border-gray-800 text-gray-100 px-4 py-3.5 text-sm focus:border-brand focus:ring-0 focus:outline-none">
            </div>

            <button type="submit"
                    class="w-full bg-brand text-white font-display text-xl tracking-[3px] py-4 hover:opacity-90 active:scale-[0.99] transition">
                MASUK →
            </button>
        </form>
    </div>

    {{-- Hint --}}
    <div class="border border-gray-800 p-4 mt-6">
        <p class="font-mono2 text-[10px] tracking-[2px] text-gray-700 uppercase mb-3">// Akun Demo</p>
        @foreach(['admin'=>'admin123','ppk'=>'ppk123','pps'=>'pps123','kpps'=>'kpps123'] as $u => $p)
        <div class="flex justify-between font-mono2 text-xs text-gray-700 py-1">
            <span class="uppercase">{{ $u }}</span>
            <span class="text-gray-400">{{ $u }} / {{ $p }}</span>
        </div>
        @endforeach
    </div>

</div>
@endsection