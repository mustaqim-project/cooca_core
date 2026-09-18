@extends('layouts.public_marketing', ['title' => 'Masuk - Cooca UMKM', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-16rem)] flex flex-col justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">

            <!-- Apple HIG Centered Title Header -->
            <div class="text-center mb-7">
                <div
                    class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] mb-3 shadow-sm transition-transform hover:scale-105">
                    <i data-lucide="lock" class="w-7 h-7"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white tracking-tight">Masuk ke Cooca UMKM</h1>
                <p class="mt-1.5 text-xs sm:text-sm text-black/60 dark:text-white/60">Akses platform operasional &amp;
                    pembukuan otomatis UMKM Anda</p>
            </div>

            <!-- Apple Inset Grouped Login Card -->
            <div
                class="glass-card bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] rounded-[28px] p-6 sm:p-8 shadow-2xl shadow-black/5 dark:shadow-black/60 relative overflow-hidden transition-colors">

                <!-- Success / Status Alert -->
                @if (session('status') || session('success'))
                    <div
                        class="mb-5 p-3.5 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/25 text-[#34C759] dark:text-[#30D158] text-xs sm:text-sm flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                        <span>{{ session('status') ?? session('success') }}</span>
                    </div>
                @endif

                <!-- Info Alert -->
                @if (session('info'))
                    <div
                        class="mb-5 p-3.5 rounded-[16px] bg-[#007AFF]/10 border border-[#007AFF]/25 text-[#007AFF] dark:text-[#0A84FF] text-xs sm:text-sm flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div
                        class="mb-5 p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs">
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

                <!-- Google SSO Button (Apple Inset Style, 48px Touch Target) -->
                <a href="{{ route('auth.google') }}"
                    class="w-full min-h-[48px] py-3 px-4 mb-4 rounded-[14px] bg-white dark:bg-white/[0.06] border border-black/10 dark:border-white/10 hover:bg-black/[0.03] dark:hover:bg-white/[0.1] text-black/85 dark:text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-3 transition-all shadow-sm active:scale-[0.99] group">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
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
                    <div class="flex-grow border-t border-black/10 dark:border-white/10"></div>
                    <span
                        class="flex-shrink mx-3 text-[11px] text-black/40 dark:text-white/40 font-semibold uppercase tracking-wider">Atau
                        dengan Email</span>
                    <div class="flex-grow border-t border-black/10 dark:border-white/10"></div>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <!-- Email Input (Anti-Zoom text-[16px] sm:text-sm) -->
                    <div>
                        <label for="email"
                            class="block font-semibold text-black/80 dark:text-white/85 text-xs sm:text-sm mb-1.5">
                            Alamat Email Akun
                        </label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                autofocus
                                class="w-full pl-10 pr-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm"
                                placeholder="nama@perusahaan.com">
                        </div>
                    </div>

                    <!-- Password Input (Anti-Zoom text-[16px] sm:text-sm) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password"
                                class="block font-semibold text-black/80 dark:text-white/85 text-xs sm:text-sm">
                                Kata Sandi
                            </label>
                            <a href="{{ route('password.request') }}"
                                class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors">
                                Lupa Kata Sandi?
                            </a>
                        </div>
                        <div class="relative" x-data="{ show: false }">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input :type="show ? 'text' : 'password'" name="password" id="password" required
                                class="w-full pl-10 pr-11 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm"
                                placeholder="••••••••">
                            <button type="button" @click="show = !show"
                                :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                class="absolute inset-y-0 right-0 w-11 flex items-center justify-center text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition-colors cursor-pointer">
                                <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-1">
                        <label
                            class="flex items-center gap-2.5 cursor-pointer text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white text-xs sm:text-sm">
                            <input type="checkbox" name="remember"
                                class="w-4 h-4 rounded-[5px] border-black/20 dark:border-white/20 bg-black/[0.03] dark:bg-white/[0.05] text-[#007AFF] focus:ring-[#007AFF]">
                            <span>Ingat sesi saya di perangkat ini</span>
                        </label>
                    </div>

                    <!-- Apple System Blue CTA Button (50px Touch Target) -->
                    <button type="submit"
                        class="w-full min-h-[50px] py-3.5 px-4 rounded-[14px] glow-btn bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm sm:text-base shadow-lg shadow-[#007AFF]/25 flex items-center justify-center gap-2 transition-all active:scale-[0.98]">
                        <span>Masuk ke Dashboard</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>

                <!-- Card Bottom Section -->
                <div
                    class="mt-5 pt-4 border-t border-black/5 dark:border-white/10 flex flex-col gap-2.5 text-center text-xs sm:text-sm text-black/60 dark:text-white/60">
                    <div>
                        Belum memiliki akun Cooca?
                        <a href="{{ route('register') }}"
                            class="font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors">Daftar
                            Akun Baru</a>
                    </div>
                    <div class="pt-2 border-t border-black/5 dark:border-white/5">
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
