@extends('layouts.public_marketing', ['title' => 'Daftar Bisnis Baru - COOCA', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-14rem)] flex flex-col justify-center py-12 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-xl mx-auto">

            <!-- Official COOCA Branding & Header -->
            <div class="text-center mb-8">
                <a href="{{ route('landing') }}" class="inline-block transition-transform hover:scale-105 mb-5 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 rounded-xl" aria-label="COOCA Beranda">
                    <img src="{{ asset('assets/image/cooca-logo-landscape.png') }}" alt="COOCA" class="h-9 sm:h-10 w-auto object-contain mx-auto">
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Daftarkan Bisnis Anda
                </h1>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
                    Pilih template industri siap pakai untuk memulai operasional &amp; pembukuan otomatis
                </p>
            </div>

            <!-- Structured Auth Card -->
            <div x-data="{
                businessScale: '{{ old('business_scale', 'umkm') }}',
                selectedTemplate: '{{ old('template_code', '') }}',
                templates: {{ Js::from($templateSummaries ?? []) }},
                showPassword: false,
                showPasswordConfirm: false,
                get currentTemplate() {
                    return this.templates[this.selectedTemplate] || null;
                }
            }"
                class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-9 shadow-sm dark:shadow-2xl dark:shadow-black/40 transition-colors">

                <!-- Alerts -->
                @if ($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs">
                        <div class="font-semibold mb-1 flex items-center gap-1.5 text-sm">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                            <span>Gagal Registrasi:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs opacity-90 pl-1">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <!-- Google SSO shortcut -->
                    <a href="{{ route('auth.google') }}"
                        class="w-full min-h-[46px] py-2.5 px-4 mb-3 rounded-xl bg-white dark:bg-white/[0.06] border border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-white/[0.1] text-slate-700 dark:text-slate-200 font-semibold text-xs sm:text-sm flex items-center justify-center gap-3 transition-all shadow-sm active:scale-[0.99] group focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20">
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
                        <span>Daftar Cepat dengan Google</span>
                    </a>

                    <!-- Divider -->
                    <div class="relative flex py-1 items-center">
                        <div class="flex-grow border-t border-slate-200 dark:border-white/10"></div>
                        <span class="flex-shrink mx-3 text-[11px] text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">
                            Atau lengkapi formulir pendaftaran
                        </span>
                        <div class="flex-grow border-t border-slate-200 dark:border-white/10"></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                Nama Pemilik / Admin <span class="text-[#007AFF] dark:text-[#0A84FF]">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                                class="w-full px-3.5 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-base text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                placeholder="Contoh: Budi Santoso">
                        </div>

                        <div>
                            <label for="email" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                Email Bisnis <span class="text-[#007AFF] dark:text-[#0A84FF]">*</span>
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="w-full px-3.5 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-base text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                placeholder="budi@usaha.com">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="business_name" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                Nama Usaha / Perusahaan <span class="text-[#007AFF] dark:text-[#0A84FF]">*</span>
                            </label>
                            <input type="text" name="business_name" id="business_name" value="{{ old('business_name') }}" required
                                class="w-full px-3.5 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-base text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                placeholder="Kedai Kopi Sukses / CV Berkah">
                        </div>

                        <div>
                            <label for="phone" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                Nomor WhatsApp / HP <span class="text-[#007AFF] dark:text-[#0A84FF]">*</span>
                            </label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required inputmode="tel" autocomplete="tel"
                                class="w-full px-3.5 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-base text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                placeholder="081234567890">
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                Kode OTP aktivasi akan dikirim ke WhatsApp ini.
                            </p>
                        </div>
                    </div>

                    <!-- Segment Selection Bento Cards -->
                    <div class="space-y-2 pt-1">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200">
                                Skala &amp; Model Operasional Bisnis <span class="text-[#007AFF] dark:text-[#0A84FF]">*</span>
                            </label>
                            <span class="text-[11px] text-slate-400 dark:text-slate-500">Bisa disesuaikan kapan saja</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Kartu UMKM -->
                            <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all active:scale-[0.99]"
                                :class="businessScale === 'umkm' ? 'bg-[#007AFF]/5 border-[#007AFF] shadow-sm ring-1 ring-[#007AFF]/30' : 'bg-slate-50/70 dark:bg-white/[0.04] border-slate-200 dark:border-white/10 hover:border-slate-300 dark:hover:border-white/20'">
                                <input type="radio" name="business_scale" value="umkm" x-model="businessScale" class="sr-only">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-[#34C759]/12 text-[#34C759] dark:text-[#30D158] flex items-center justify-center font-bold">
                                        <i data-lucide="store" class="w-4 h-4"></i>
                                    </div>
                                    <span x-show="businessScale === 'umkm'" class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 px-2 py-0.5 rounded-full">
                                        Terpilih
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">UMKM &amp; Toko Mandiri</h4>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                                    Untuk warung, kafe, butik, bengkel, atau toko 1–3 cabang. Tampilan ringkas, tanpa istilah akuntansi rumit, siap jualan 5 menit.
                                </p>
                            </label>

                            <!-- Kartu Korporasi -->
                            <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all active:scale-[0.99]"
                                :class="businessScale === 'corporate' ? 'bg-[#007AFF]/5 border-[#007AFF] shadow-sm ring-1 ring-[#007AFF]/30' : 'bg-slate-50/70 dark:bg-white/[0.04] border-slate-200 dark:border-white/10 hover:border-slate-300 dark:hover:border-white/20'">
                                <input type="radio" name="business_scale" value="corporate" x-model="businessScale" class="sr-only">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center font-bold">
                                        <i data-lucide="building-2" class="w-4 h-4"></i>
                                    </div>
                                    <span x-show="businessScale === 'corporate'" class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 px-2 py-0.5 rounded-full">
                                        Terpilih
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">Korporasi &amp; Multi-Cabang</h4>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                                    Untuk perusahaan berkembang, distributor, waralaba, &amp; multi-gudang. Fitur approval bertingkat, multi-ledger, &amp; audit trail lengkap.
                                </p>
                            </label>
                        </div>
                    </div>

                    <!-- Template Selection -->
                    <div class="space-y-2 pt-1">
                        <div class="flex items-center justify-between">
                            <label for="template_code" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200">
                                Template Industri (Preset Otomasi &amp; Penataan Menu)
                            </label>
                            <span class="text-[11px] text-slate-400 dark:text-slate-500">Bisa diubah nanti</span>
                        </div>
                        <select name="template_code" id="template_code" x-model="selectedTemplate"
                            class="w-full px-3.5 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-sm sm:text-base text-slate-900 dark:text-white transition-all outline-none">
                            <option value="">-- Mulai Bisnis Umum (Aktifkan Semua Modul) --</option>
                            @foreach ($templates as $tmpl)
                                <option value="{{ $tmpl->code }}" {{ old('template_code') == $tmpl->code ? 'selected' : '' }}>
                                    {{ $tmpl->name }} ({{ strtoupper($tmpl->industry_category) }})
                                </option>
                            @endforeach
                        </select>

                        <!-- Interactive Live Bento Preview Card -->
                        <template x-if="currentTemplate">
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200 dark:border-white/10 space-y-3 transition-all text-xs">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs sm:text-sm">
                                        <i data-lucide="layout-template" class="w-4 h-4 shrink-0"></i>
                                        <span x-text="'Penataan Modul: ' + currentTemplate.name"></span>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] border border-[#34C759]/20"
                                        x-text="currentTemplate.category"></span>
                                </div>

                                <!-- Enabled features badges -->
                                <div class="space-y-1.5">
                                    <div class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Modul Kerja Aktif:</div>
                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-for="item in currentTemplate.enabled" :key="item.key">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] border border-[#34C759]/20 text-[11px] font-medium">
                                                <i data-lucide="check" class="w-3 h-3"></i>
                                                <span x-text="item.name"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Decluttered/disabled features notice -->
                                <template x-if="currentTemplate.disabled && currentTemplate.disabled.length > 0">
                                    <div class="space-y-1.5 pt-2 border-t border-slate-200/60 dark:border-white/5">
                                        <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                            <i data-lucide="eye-off" class="w-3 h-3"></i>
                                            <span>Modul disederhanakan agar tampilan rapi:</span>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="item in currentTemplate.disabled" :key="item.key">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-200/50 dark:bg-white/[0.05] text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-white/5 text-[11px] line-through">
                                                    <span x-text="item.name"></span>
                                                </span>
                                            </template>
                                        </div>
                                        <p class="text-[10px] text-slate-400 dark:text-slate-500 italic mt-0.5">
                                            Dapat diaktifkan kembali kapan saja oleh Pemilik Bisnis di Pengaturan.
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                Kata Sandi <span class="text-[#007AFF] dark:text-[#0A84FF]">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required
                                    class="w-full pl-3.5 pr-11 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-base text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
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
                                Konfirmasi Sandi <span class="text-[#007AFF] dark:text-[#0A84FF]">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showPasswordConfirm ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required
                                    class="w-full pl-3.5 pr-11 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-base text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                    placeholder="Ulangi sandi">
                                <button type="button" @click="showPasswordConfirm = !showPasswordConfirm"
                                    :aria-label="showPasswordConfirm ? 'Sembunyikan konfirmasi sandi' : 'Tampilkan konfirmasi sandi'"
                                    class="absolute right-0 top-0 bottom-0 w-11 flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 focus:outline-none transition-colors cursor-pointer">
                                    <i :data-lucide="showPasswordConfirm ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] hover:from-[#1cd0ff] hover:to-[#006fe6] text-white font-bold text-sm sm:text-[15px] shadow-[0_2px_12px_rgba(0,194,255,0.3)] hover:shadow-[0_4px_20px_rgba(0,194,255,0.5)] active:scale-[0.99] min-h-[50px] transition-all focus:outline-none focus:ring-4 focus:ring-[#007AFF]/25 cursor-pointer">
                            <span>Buat Akun &amp; Mulai Usaha</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>

                <div class="mt-6 pt-5 border-t border-slate-200/80 dark:border-white/10 text-center text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    Sudah memiliki akun COOCA?
                    <a href="{{ route('login') }}"
                        class="font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded">
                        Masuk di Sini
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
