@extends('layouts.guest', ['title' => 'Masuk — Cooca Core'])

@section('content')
    <div class="sm:mx-auto sm:w-full sm:max-w-md px-4">
        <!-- Brand Badge -->
        <div class="text-center mb-8">
            <a href="{{ route('landing') }}"
                class="inline-flex items-center justify-center mb-4 hover:scale-105 transition-transform">
                <img src="https://cooca.id/assets/image/1785229034_logo_dark.png" alt="COOCA.ID"
                    style="height: 38px; width: auto; object-fit: contain;">
            </a>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Cooca Core</h2>
            <p class="mt-2 text-sm text-slate-400">Business Operating System — cooca.id</p>
        </div>

        <!-- Login Card -->
        <div
            class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl shadow-black/60 relative overflow-hidden">

            <!-- Alerts -->
            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs">
                    <div class="font-semibold mb-1">Gagal Masuk:</div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Google SSO Button -->
            <a href="{{ route('auth.google') }}"
                class="w-full py-2.5 px-4 mb-5 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 hover:bg-slate-800/60 text-white font-semibold text-xs flex items-center justify-center gap-3 transition-all shadow-sm group">
                <svg class="w-4 h-4" viewBox="0 0 24 24">
                    <path fill="#EA4335"
                        d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z" />
                    <path fill="#4285F4"
                        d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z" />
                    <path fill="#FBBC05"
                        d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15s.7 5.3 1.9 7.7l3.7-2.9z" />
                    <path fill="#34A853"
                        d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.4-6.4-5.2L1.9 16c1.8 3.7 5.6 7 10.1 7z" />
                </svg>
                <span>Masuk dengan Google</span>
            </a>

            <!-- Divider -->
            <div class="relative flex py-2 items-center mb-5">
                <div class="flex-grow border-t border-slate-800"></div>
                <span class="flex-shrink mx-4 text-[10px] text-slate-500 font-bold uppercase tracking-wider">Atau via
                    Email</span>
                <div class="flex-grow border-t border-slate-800"></div>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label for="email" class="block font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email
                        Pengguna</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-950/80 border border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl text-white placeholder-slate-500 transition-colors"
                            placeholder="nama@perusahaan.com">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block font-semibold text-slate-300 uppercase tracking-wider">Kata
                            Sandi</label>
                        <a href="{{ route('password.request') }}" class="text-[11px] font-semibold text-emerald-400 hover:text-emerald-300 transition-colors">
                            Lupa Kata Sandi?
                        </a>
                    </div>
                    <div class="relative" x-data="{ show: false }">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input :type="show ? 'text' : 'password'" name="password" id="password" required
                            class="w-full pl-10 pr-10 py-2.5 bg-slate-950/80 border border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl text-white placeholder-slate-500 transition-colors"
                            placeholder="••••••••">
                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300">
                            <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-300">
                        <input type="checkbox" name="remember"
                            class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-emerald-500 focus:ring-emerald-500">
                        <span>Ingat sesi saya</span>
                    </label>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-semibold text-xs shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-2 transition-all transform active:scale-[0.99]">
                    <span>Masuk ke Dashboard</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-800 text-center text-xs text-slate-400">
                Belum memiliki akun?
                <a href="{{ route('register') }}"
                    class="font-semibold text-emerald-400 hover:text-emerald-300 transition-colors">Daftar Akun Baru</a>
            </div>
        </div>
    </div>
@endsection
