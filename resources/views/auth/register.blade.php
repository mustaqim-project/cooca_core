@extends('layouts.public_marketing', ['title' => 'Daftar Bisnis Baru - COOCA', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-12rem)] py-8 sm:py-12 lg:py-16 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-xl mx-auto space-y-6 sm:space-y-8"
            x-data="{
                businessScale: '{{ old('business_scale', 'umkm') }}',
                selectedTemplate: '{{ old('template_code', '') }}',
                showPassword: false,
                showPasswordConfirm: false
            }">

            <!-- Official COOCA Branding & Header -->
            <div class="text-center space-y-3">
                @include('auth.partials.brand-logo', ['class' => ''])
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Daftarkan Bisnis Anda
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
                    Mulai operasional kasir, pembukuan otomatis, dan manajemen usaha terpadu dalam hitungan menit.
                </p>
            </div>

            <!-- Error Notification Banner -->
            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs sm:text-sm shadow-xs">
                    <div class="font-semibold mb-1.5 flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                        <span>Mohon periksa kembali formulir berikut:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs opacity-90 pl-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Main Single-Column Focused Register Card -->
            <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-black/40">
                <form method="POST" action="{{ route('register') }}" id="register-form" class="space-y-4 sm:space-y-5">
                    @csrf

                    <!-- Google SSO Shortcut -->
                    <a href="{{ route('auth.google') }}"
                        class="w-full min-h-[48px] py-2.5 px-4 rounded-xl bg-slate-50 dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:bg-slate-100 dark:hover:bg-white/[0.08] text-slate-800 dark:text-slate-100 font-semibold text-xs sm:text-sm flex items-center justify-center gap-3 transition-all shadow-xs active:scale-[0.99] group focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 cursor-pointer">
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
                        <span>Daftar Cepat dengan Google</span>
                    </a>

                    <!-- Divider -->
                    <div class="relative flex py-1 items-center">
                        <div class="flex-grow border-t border-black/5 dark:border-white/10"></div>
                        <span class="flex-shrink mx-3 text-[10px] text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">
                            Atau isi data lengkap
                        </span>
                        <div class="flex-grow border-t border-black/5 dark:border-white/10"></div>
                    </div>

                    <!-- Input Nama Pemilik -->
                    <div>
                        <label for="name" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                            Nama Pemilik / Admin <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <i data-lucide="user" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                placeholder="Contoh: Budi Santoso">
                        </div>
                    </div>

                    <!-- Input Email Bisnis -->
                    <div>
                        <label for="email" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                            Email Bisnis <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                placeholder="budi@usaha.com">
                        </div>
                    </div>

                    <!-- Input Kata Sandi & Konfirmasi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-3.5">
                        <div>
                            <label for="password" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                Kata Sandi <span class="text-[#FF3B30]">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required
                                    class="w-full pl-3.5 pr-11 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                    placeholder="Min. 8 karakter">
                                <button type="button" @click="showPassword = !showPassword"
                                    :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                    class="absolute right-0 top-0 bottom-0 w-11 flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 focus:outline-none transition-colors cursor-pointer">
                                    <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                Konfirmasi Sandi <span class="text-[#FF3B30]">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showPasswordConfirm ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required
                                    class="w-full pl-3.5 pr-11 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                    placeholder="Ulangi sandi">
                                <button type="button" @click="showPasswordConfirm = !showPasswordConfirm"
                                    :aria-label="showPasswordConfirm ? 'Sembunyikan konfirmasi sandi' : 'Tampilkan konfirmasi sandi'"
                                    class="absolute right-0 top-0 bottom-0 w-11 flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 focus:outline-none transition-colors cursor-pointer">
                                    <i :data-lucide="showPasswordConfirm ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Input Nama Usaha -->
                    <div>
                        <label for="business_name" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                            Nama Usaha / Perusahaan <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <i data-lucide="store" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="business_name" id="business_name" value="{{ old('business_name') }}" required
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                placeholder="Contoh: Kedai Kopi Sukses / CV Berkah">
                        </div>
                    </div>

                    <!-- Input Nomor WhatsApp -->
                    <div>
                        <label for="phone" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                            Nomor WhatsApp Pemilik <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <i data-lucide="phone" class="w-4 h-4"></i>
                            </div>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required inputmode="tel" autocomplete="tel"
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                placeholder="081234567890">
                        </div>
                        <p class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                            Kode OTP aktivasi akun akan dikirim ke WhatsApp ini.
                        </p>
                    </div>

                    <!-- Pilihan Kategori Bisnis (Preset Sederhana) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="template_code" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200">
                                Kategori / Bidang Usaha
                            </label>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">Opsional</span>
                        </div>
                        <select name="template_code" id="template_code" x-model="selectedTemplate"
                            class="w-full px-3.5 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm font-medium text-slate-900 dark:text-white transition-all outline-none cursor-pointer">
                            <option value="">-- Bisnis Umum (Semua Modul Standar) --</option>
                            @foreach ($templates as $tmpl)
                                <option value="{{ $tmpl->code }}" {{ old('template_code') == $tmpl->code ? 'selected' : '' }}>
                                    {{ $tmpl->name }} ({{ strtoupper($tmpl->industry_category) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Skala & Model Operasional Bisnis (Grid Kiri Kanan Baik Mobile Maupun Desktop) -->
                    <div class="space-y-2 pt-1">
                        <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Skala &amp; Model Operasional Bisnis <span class="text-[#FF3B30]">*</span>
                        </label>

                        <div class="grid grid-cols-2 gap-2.5 sm:gap-3.5">
                            <!-- Kartu Kiri: UMKM -->
                            <label class="relative flex flex-col justify-between p-3 sm:p-4 rounded-xl sm:rounded-2xl border cursor-pointer transition-all active:scale-[0.98]"
                                :class="businessScale === 'umkm' ? 'bg-[#007AFF]/5 border-[#007AFF] shadow-xs ring-1 ring-[#007AFF]/30' : 'bg-slate-50 dark:bg-[#2C2C2E] border-black/10 dark:border-white/10 hover:border-slate-300 dark:hover:border-white/20'">
                                <input type="radio" name="business_scale" value="umkm" x-model="businessScale" class="sr-only">
                                <div>
                                    <div class="flex items-center justify-between gap-1 mb-2">
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-[#34C759]/12 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                                            <i data-lucide="store" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                        </div>
                                        <span x-show="businessScale === 'umkm'" class="text-[9.5px] sm:text-[10px] font-bold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 px-2 py-0.5 rounded-full">Pilihan</span>
                                    </div>
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white leading-tight">UMKM &amp; Toko Mandiri</h4>
                                    <p class="text-[10.5px] sm:text-[11.5px] text-slate-500 dark:text-slate-400 mt-1 leading-snug">
                                        Warung, kafe, butik, bengkel, 1–3 cabang.
                                    </p>
                                </div>
                            </label>

                            <!-- Kartu Kanan: Multi-Cabang / Korporasi -->
                            <label class="relative flex flex-col justify-between p-3 sm:p-4 rounded-xl sm:rounded-2xl border cursor-pointer transition-all active:scale-[0.98]"
                                :class="businessScale === 'corporate' ? 'bg-[#007AFF]/5 border-[#007AFF] shadow-xs ring-1 ring-[#007AFF]/30' : 'bg-slate-50 dark:bg-[#2C2C2E] border-black/10 dark:border-white/10 hover:border-slate-300 dark:hover:border-white/20'">
                                <input type="radio" name="business_scale" value="corporate" x-model="businessScale" class="sr-only">
                                <div>
                                    <div class="flex items-center justify-between gap-1 mb-2">
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                                            <i data-lucide="building-2" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                        </div>
                                        <span x-show="businessScale === 'corporate'" class="text-[9.5px] sm:text-[10px] font-bold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 px-2 py-0.5 rounded-full">Pilihan</span>
                                    </div>
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white leading-tight">Korporasi &amp; Multi-Cabang</h4>
                                    <p class="text-[10.5px] sm:text-[11.5px] text-slate-500 dark:text-slate-400 mt-1 leading-snug">
                                        Distributor, waralaba, &amp; multi-gudang.
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] hover:from-[#1cd0ff] hover:to-[#006fe6] text-white font-bold text-sm sm:text-[15px] shadow-[0_2px_12px_rgba(0,194,255,0.3)] hover:shadow-[0_4px_20px_rgba(0,194,255,0.5)] active:scale-[0.99] min-h-[50px] transition-all focus:outline-none focus:ring-4 focus:ring-[#007AFF]/25 cursor-pointer">
                            <span>Buat Akun &amp; Mulai Usaha</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Login Link -->
                    <div class="text-center pt-2 border-t border-black/[0.04] dark:border-white/[0.06] text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                        Sudah memiliki akun COOCA?
                        <a href="{{ route('login') }}" class="font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded ml-1">
                            Masuk di Sini
                        </a>
                    </div>
                </form>
            </div>

            <!-- Trust & Privacy Security Callout -->
            <div class="text-center space-y-1 text-slate-500 dark:text-slate-400 text-xs">
                <div class="inline-flex items-center gap-1.5 font-medium text-[#34C759]">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    <span>Data usaha Anda terisolasi privat &amp; terenkripsi aman</span>
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                    Dengan mendaftar, Anda menyetujui Ketentuan Layanan &amp; Kebijakan Privasi COOCA.
                </p>
            </div>

        </div>
    </div>
@endsection
