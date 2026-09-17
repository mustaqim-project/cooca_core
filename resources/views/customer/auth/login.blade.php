@extends('layouts.customer', ['title' => 'Masuk Akun Pelanggan'])

@section('content')
    <div class="max-w-md mx-auto py-6 sm:py-16">
        <div class="bento-card p-8 sm:p-10 space-y-8">

            {{-- Logo / Branding --}}
            <div class="text-center space-y-3">
                <div
                    class="w-16 h-16 rounded-3xl bg-gradient-to-br from-[#007AFF] to-[#5AC8FA] mx-auto flex items-center justify-center shadow-lg shadow-[#007AFF]/30">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                @if (isset($store) && $store)
                    <p class="text-xs font-bold uppercase tracking-widest text-[#007AFF]">Belanja di</p>
                    <h1 class="text-2xl font-extrabold text-black dark:text-white">{{ $store->name }}</h1>
                @else
                    <h1 class="text-2xl font-extrabold text-black dark:text-white">COOCA Customer</h1>
                @endif
                <p class="text-sm text-black/50 dark:text-white/50 leading-relaxed">
                    Satu akun untuk belanja di semua toko COOCA.<br>
                    Login atau daftar dengan Google - gratis, aman, instan.
                </p>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
                <div class="p-4 bg-[#FF3B30]/10 border border-[#FF3B30]/20 rounded-2xl text-sm text-[#FF3B30] space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if (session('info'))
                <div class="p-4 bg-[#007AFF]/10 border border-[#007AFF]/20 rounded-2xl text-sm text-[#007AFF]">
                    {{ session('info') }}
                </div>
            @endif

            {{-- Form Login Email / WhatsApp & Password --}}
            <form method="POST" action="{{ route('customer.login.submit') }}" class="space-y-4" x-data="{
                loginValue: '{{ old('login', '') }}',
                passwordValue: '',
                fillDemo(login, pass) {
                    this.loginValue = login;
                    this.passwordValue = pass;
                }
            }">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1.5">
                        Nomor WhatsApp atau Email
                    </label>
                    <input type="text" name="login" x-model="loginValue" required autofocus
                        placeholder="Contoh: mandiri@cooca.id atau 081234567890"
                        class="w-full px-4 py-3 bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 rounded-2xl text-[15px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:border-[#007AFF] transition">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            Kata Sandi
                        </label>
                    </div>
                    <input type="password" name="password" x-model="passwordValue" required
                        placeholder="Masukkan kata sandi akun"
                        class="w-full px-4 py-3 bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 rounded-2xl text-[15px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:border-[#007AFF] transition">
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-black/60 dark:text-white/60">
                        <input type="checkbox" name="remember" class="rounded text-[#007AFF] focus:ring-[#007AFF]">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit"
                    class="w-full py-3.5 bg-[#007AFF] hover:bg-[#007AFF]/90 active:scale-[0.98] text-white font-bold text-[14.5px] rounded-2xl transition shadow-md shadow-[#007AFF]/20 flex items-center justify-center gap-2 cursor-pointer">
                    <span>Masuk Akun Pelanggan</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>

                {{-- Demo Accounts Quick-Fill Box (Bento HIG) --}}
                <div class="pt-2 p-3.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 block">
                        Akun Seeder Demo (Klik untuk Isi Cepat):
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <button type="button" @click="fillDemo('mandiri@cooca.id', 'password')"
                            class="p-2.5 rounded-xl bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-left transition active:scale-95 group cursor-pointer shadow-2xs">
                            <span class="text-[12px] font-bold text-black dark:text-white block group-hover:text-[#007AFF]">Ahmad (Mandiri)</span>
                            <span class="text-[10.5px] text-black/50 dark:text-white/50 font-mono">mandiri@cooca.id</span>
                        </button>
                        <button type="button" @click="fillDemo('bca@cooca.id', 'password')"
                            class="p-2.5 rounded-xl bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-left transition active:scale-95 group cursor-pointer shadow-2xs">
                            <span class="text-[12px] font-bold text-black dark:text-white block group-hover:text-[#007AFF]">Budi (BCA)</span>
                            <span class="text-[10.5px] text-black/50 dark:text-white/50 font-mono">bca@cooca.id</span>
                        </button>
                    </div>
                </div>
            </form>

            {{-- Divider --}}
            <div class="relative flex items-center justify-center">
                <div class="border-t border-black/10 dark:border-white/10 w-full"></div>
                <span class="bg-white dark:bg-[#1C1C1E] px-3 text-[11px] font-bold uppercase tracking-wider text-black/40 dark:text-white/40 absolute">
                    Atau Masuk dengan Google
                </span>
            </div>

            {{-- Google Sign-In Button --}}
            <div class="space-y-3">
                <a href="{{ route('customer.auth.google') }}{{ $redirectTo ? '?redirect=' . urlencode($redirectTo) : '' }}"
                    class="flex items-center justify-center gap-3 w-full px-6 py-3.5 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-2xl text-[14px] font-bold text-black dark:text-white hover:border-[#007AFF]/40 hover:shadow-md active:scale-[0.98] transition-all duration-200 group">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                        <path fill="#4285F4"
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853"
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05"
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path fill="#EA4335"
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                    Lanjutkan dengan Google
                </a>
            </div>

            {{-- Trust indicators --}}
            <div class="flex items-center justify-center gap-6 text-[11px] text-black/40 dark:text-white/40">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#34C759]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                            clip-rule="evenodd" />
                    </svg>
                    Aman & Terenkripsi
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#34C759]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    Satu akun semua toko
                </span>
            </div>

        </div>
    </div>
@endsection
