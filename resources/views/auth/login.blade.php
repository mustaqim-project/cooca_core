@extends('layouts.public_marketing', ['title' => 'Masuk - COOCA', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-14rem)] flex flex-col justify-center py-12 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md mx-auto">

            <!-- Official COOCA Branding & Header -->
            <div class="text-center mb-8">
                <a href="{{ route('landing') }}" class="inline-block transition-transform hover:scale-105 mb-5 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 rounded-xl" aria-label="COOCA Beranda">
                    <img src="{{ asset('assets/image/cooca-logo-landscape.png') }}" alt="COOCA" class="h-9 sm:h-10 w-auto object-contain mx-auto">
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Masuk ke Cooca
                </h1>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
                    Akses platform operasional &amp; pembukuan otomatis UMKM Anda
                </p>
            </div>

            <!-- Structured Auth Card -->
            <div class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-black/40 transition-colors">

                <!-- Success / Status Alert -->
                @if (session('status') || session('success'))
                    <div class="mb-5 p-3.5 rounded-xl bg-[#34C759]/10 border border-[#34C759]/25 text-[#34C759] dark:text-[#30D158] text-xs sm:text-sm flex items-start gap-2.5">
                        <i data-lucide="check-circle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                        <span>{{ session('status') ?? session('success') }}</span>
                    </div>
                @endif

                <!-- Info Alert -->
                @if (session('info'))
                    <div class="mb-5 p-3.5 rounded-xl bg-[#007AFF]/10 border border-[#007AFF]/25 text-[#007AFF] dark:text-[#0A84FF] text-xs sm:text-sm flex items-start gap-2.5">
                        <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                <!-- Validation Errors Alert -->
                @if ($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs">
                        <div class="font-semibold mb-1 flex items-center gap-1.5 text-sm">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                            <span>Gagal Masuk:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs opacity-90 pl-1">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Google SSO Button -->
                <a href="{{ route('auth.google') }}"
                    class="w-full min-h-[46px] py-2.5 px-4 mb-4 rounded-xl bg-white dark:bg-white/[0.06] border border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-white/[0.1] text-slate-700 dark:text-slate-200 font-semibold text-xs sm:text-sm flex items-center justify-center gap-3 transition-all shadow-sm active:scale-[0.99] group focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20">
                    <svg class="w-5 h-5 shrink-0" width="20" height="20" viewBox="0 0 24 24">
                        <path fill="#EA4335"
                            d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z" />
                        <path fill="#4285F4"
                            d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z" />
                        <path fill="#FBBC05"
                            d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15s.7 5.3 1.9 7.7l3.7-2.9z" />
                        <path fill="#34A853"
                            d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.4-6.4-5.2L1.9 16c1.8 3.7 5.6 7 10.1 7z" />
                    </svg>
                    <span>Masuk Cepat dengan Google</span>
                </a>

                <!-- Divider -->
                <div class="relative flex py-2 items-center mb-4">
                    <div class="flex-grow border-t border-slate-200 dark:border-white/10"></div>
                    <span class="flex-shrink mx-3 text-[11px] text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">
                        Atau dengan Email
                    </span>
                    <div class="flex-grow border-t border-slate-200 dark:border-white/10"></div>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block font-semibold text-slate-700 dark:text-slate-200 text-xs sm:text-sm mb-1.5">
                            Alamat Email Akun
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all text-[16px] sm:text-base outline-none"
                                placeholder="nama@perusahaan.com">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block font-semibold text-slate-700 dark:text-slate-200 text-xs sm:text-sm">
                                Kata Sandi
                            </label>
                            <a href="{{ route('password.request') }}"
                                class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded">
                                Lupa Kata Sandi?
                            </a>
                        </div>
                        <div class="relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input :type="show ? 'text' : 'password'" name="password" id="password" required
                                class="w-full pl-10 pr-11 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all text-[16px] sm:text-base outline-none"
                                placeholder="••••••••">
                            <button type="button" @click="show = !show"
                                :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                class="absolute inset-y-0 right-0 w-11 flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 transition-colors cursor-pointer focus:outline-none">
                                <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white text-xs sm:text-sm">
                            <input type="checkbox" name="remember"
                                class="w-4 h-4 rounded border-slate-300 dark:border-white/20 bg-slate-50 dark:bg-[#1E2638] text-[#007AFF] focus:ring-[#007AFF] focus:ring-offset-0">
                            <span>Ingat sesi saya di perangkat ini</span>
                        </label>
                    </div>

                    <!-- Primary CTA Button -->
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] hover:from-[#1cd0ff] hover:to-[#006fe6] text-white font-bold text-sm sm:text-[15px] shadow-[0_2px_12px_rgba(0,194,255,0.3)] hover:shadow-[0_4px_20px_rgba(0,194,255,0.5)] active:scale-[0.99] min-h-[50px] transition-all focus:outline-none focus:ring-4 focus:ring-[#007AFF]/25 cursor-pointer">
                        <span>Masuk ke Dashboard</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>

                <!-- Card Bottom Section -->
                <div class="mt-6 pt-5 border-t border-slate-200/80 dark:border-white/10 flex flex-col gap-3 text-center text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    <div>
                        Belum memiliki akun COOCA?
                        <a href="{{ route('register') }}"
                            class="font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded">
                            Daftar Akun Baru
                        </a>
                    </div>
                    <div class="pt-2 border-t border-slate-100 dark:border-white/5">
                        <a href="{{ route('account-recovery.create') }}"
                            class="text-xs text-[#FF9500] dark:text-[#FF9F0A] hover:underline transition-colors inline-flex items-center gap-1 font-medium">
                            <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                            <span>Kendala WhatsApp / Email? Ajukan Pemulihan Akun</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
