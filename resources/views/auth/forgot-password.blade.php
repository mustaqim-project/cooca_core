@extends('layouts.guest', ['title' => 'Lupa Kata Sandi — Cooca Core'])

@section('content')
<div class="sm:mx-auto sm:w-full sm:max-w-md px-4">
    <!-- Brand Badge -->
    <div class="text-center mb-8">
        <a href="{{ route('landing') }}" class="inline-flex items-center justify-center mb-4 hover:scale-105 transition-transform">
            <img src="https://cooca.id/assets/image/1785229034_logo_dark.png" alt="COOCA.ID" style="height: 38px; width: auto; object-fit: contain;">
        </a>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Atur Ulang Kata Sandi</h2>
        <p class="mt-2 text-sm text-slate-400">Masukkan email Anda untuk menerima tautan pemulihan kata sandi</p>
    </div>

    <!-- Card -->
    <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl shadow-black/60 relative overflow-hidden">
        
        @if (session('status'))
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 p-3.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs">
                <div class="font-semibold mb-1">Terjadi Kesalahan:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4 text-xs">
            @csrf

            <div>
                <label for="email" class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email Terdaftar</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </div>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-950/80 border border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl text-white placeholder-slate-500 transition-colors"
                           placeholder="nama@perusahaan.com">
                </div>
            </div>

            <button type="submit"
                    class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-semibold text-xs shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-2 transition-all transform active:scale-[0.99]">
                <i data-lucide="send" class="w-4 h-4"></i>
                <span>Kirim Tautan Pemulihan</span>
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-800/80 text-center">
            <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-emerald-400 transition-colors inline-flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Halaman Masuk</span>
            </a>
        </div>
    </div>
</div>
@endsection
