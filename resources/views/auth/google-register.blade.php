@extends('layouts.public_marketing', ['title' => 'Lengkapi Pendaftaran Google - COOCA', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-12rem)] py-8 sm:py-12 lg:py-14 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-6xl mx-auto space-y-6 sm:space-y-8"
            x-data="{
                businessScale: '{{ old('business_scale', 'umkm') }}',
                selectedTemplate: '{{ old('template_code', '') }}'
            }">

            <!-- Official COOCA Branding & Header -->
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <a href="{{ route('landing') }}" class="inline-block transition-transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 rounded-xl" aria-label="COOCA Beranda">
                    <img src="{{ asset('assets/image/cooca-logo-landscape.png') }}" alt="COOCA" class="h-9 sm:h-10 w-auto object-contain mx-auto">
                </a>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Lengkapi Pendaftaran Bisnis
                </h1>
                
                <div class="flex flex-wrap items-center justify-center gap-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                    <span>Akun Google terhubung:</span>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white dark:bg-white/10 border border-slate-200/80 dark:border-white/10 text-slate-900 dark:text-white font-medium shadow-xs">
                        @if (!empty($pending['avatar']))
                            <img src="{{ $pending['avatar'] }}" alt="{{ $pending['name'] ?? 'User' }}" class="w-5 h-5 rounded-full object-cover">
                        @else
                            <div class="w-5 h-5 rounded-full bg-[#007AFF] text-white text-[10px] font-bold flex items-center justify-center">
                                {{ strtoupper(substr($pending['name'] ?? $pending['email'] ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                        <span class="font-semibold text-xs sm:text-[13px]">{{ $pending['email'] }}</span>
                        <span class="inline-flex items-center text-[10px] font-semibold text-[#248A3D] dark:text-[#30D158] bg-[#34C759]/15 px-1.5 py-0.5 rounded-full">
                            <i data-lucide="check" class="w-3 h-3 mr-0.5"></i>
                            Terverifikasi
                        </span>
                    </div>
                </div>
            </div>

            <!-- Error Notification Banner -->
            @if ($errors->any())
                <div class="max-w-4xl mx-auto p-4 rounded-2xl bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs sm:text-sm">
                    <div class="font-semibold mb-1.5 flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                        <span>Mohon periksa kembali formulir berikut:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs opacity-90 pl-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Main Split Layout -->
            <form method="POST" action="{{ route('register.google.submit') }}" id="register-google-form">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                    
                    <!-- ========================================================= -->
                    <!-- LEFT COLUMN: Formulir Data Usaha & WhatsApp              -->
                    <!-- ========================================================= -->
                    <div class="lg:col-span-6 xl:col-span-6 space-y-5">
                        <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] rounded-2xl sm:rounded-3xl p-5 sm:p-7 shadow-sm space-y-4">
                            
                            <div class="flex items-center gap-3 pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                                <div class="w-9 h-9 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                    <i data-lucide="store" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white leading-tight">Profil Usaha Baru</h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Atur nama toko dan WhatsApp penerima OTP</p>
                                </div>
                            </div>

                            <!-- Input Nama Usaha -->
                            <div>
                                <label for="business_name" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                    Nama Usaha / Perusahaan <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="business_name" id="business_name"
                                    value="{{ old('business_name', 'Usaha ' . ($pending['name'] ?? 'Saya')) }}" required autofocus
                                    class="w-full px-3.5 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                    placeholder="Contoh: Kedai Kopi Sukses / CV Berkah">
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

                            <!-- Skala Operasional Bisnis -->
                            <div class="space-y-2 pt-1">
                                <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    Skala Operasional <span class="text-[#FF3B30]">*</span>
                                </label>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    <!-- Kartu UMKM -->
                                    <label class="relative flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition-all active:scale-[0.99]"
                                        :class="businessScale === 'umkm' ? 'bg-[#007AFF]/5 border-[#007AFF] shadow-xs ring-1 ring-[#007AFF]/30' : 'bg-slate-50 dark:bg-[#2C2C2E] border-black/10 dark:border-white/10 hover:border-slate-300 dark:hover:border-white/20'">
                                        <input type="radio" name="business_scale" value="umkm" x-model="businessScale" class="sr-only">
                                        <div class="w-8 h-8 rounded-lg bg-[#34C759]/12 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0 font-bold mt-0.5">
                                            <i data-lucide="store" class="w-4 h-4"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">UMKM Mandiri</h4>
                                                <span x-show="businessScale === 'umkm'" class="text-[10px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 px-2 py-0.5 rounded-full shrink-0">Pilihan</span>
                                            </div>
                                            <p class="text-[11.5px] text-slate-600 dark:text-slate-400 mt-0.5 leading-snug">
                                                Warung, kafe, butik, bengkel, atau 1–3 cabang.
                                            </p>
                                        </div>
                                    </label>

                                    <!-- Kartu Multi-Cabang / Korporasi -->
                                    <label class="relative flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition-all active:scale-[0.99]"
                                        :class="businessScale === 'corporate' ? 'bg-[#007AFF]/5 border-[#007AFF] shadow-xs ring-1 ring-[#007AFF]/30' : 'bg-slate-50 dark:bg-[#2C2C2E] border-black/10 dark:border-white/10 hover:border-slate-300 dark:hover:border-white/20'">
                                        <input type="radio" name="business_scale" value="corporate" x-model="businessScale" class="sr-only">
                                        <div class="w-8 h-8 rounded-lg bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0 font-bold mt-0.5">
                                            <i data-lucide="building-2" class="w-4 h-4"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">Multi-Cabang</h4>
                                                <span x-show="businessScale === 'corporate'" class="text-[10px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 px-2 py-0.5 rounded-full shrink-0">Pilihan</span>
                                            </div>
                                            <p class="text-[11.5px] text-slate-600 dark:text-slate-400 mt-0.5 leading-snug">
                                                Distributor, waralaba, &amp; multi-gudang.
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-2">
                                <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] hover:from-[#1cd0ff] hover:to-[#006fe6] text-white font-bold text-sm sm:text-[15px] shadow-[0_2px_12px_rgba(0,194,255,0.3)] hover:shadow-[0_4px_20px_rgba(0,194,255,0.5)] active:scale-[0.99] min-h-[48px] transition-all focus:outline-none focus:ring-4 focus:ring-[#007AFF]/25 cursor-pointer">
                                    <span>Lanjutkan &amp; Kirim OTP WhatsApp</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <!-- Cancel Link -->
                            <div class="text-center pt-2 border-t border-black/[0.04] dark:border-white/[0.06] text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                <a href="{{ route('login') }}" class="font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded">
                                    Batalkan dan kembali ke halaman masuk
                                </a>
                            </div>

                        </div>
                    </div>

                    <!-- ========================================================= -->
                    <!-- RIGHT COLUMN: Bento Value Proposition & Keamanan Bisnis   -->
                    <!-- ========================================================= -->
                    <div class="lg:col-span-6 xl:col-span-6 space-y-6 lg:sticky lg:top-8">
                        <div class="bg-gradient-to-br from-slate-900 via-[#1C1C1E] to-[#121214] text-white border border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
                            
                            <!-- Header Core Value -->
                            <div class="space-y-2">
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-[11px] font-semibold text-[#00C2FF]">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                    <span>Platform SaaS ERP &amp; POS Terpercaya</span>
                                </div>
                                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white leading-snug">
                                    Semua Kebutuhan Usaha Anda dalam Satu Ekosistem
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                                    COOCA dirancang untuk mempermudah operasional harian, mengamankan arus kas, dan membantu bisnis Anda berkembang lebih cepat.
                                </p>
                            </div>

                            <!-- 4 Bento Feature Cards -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <!-- Fitur 1: POS -->
                                <div class="p-4 rounded-xl bg-white/[0.05] border border-white/10 space-y-2">
                                    <div class="w-8 h-8 rounded-lg bg-[#007AFF]/20 text-[#007AFF] flex items-center justify-center">
                                        <i data-lucide="calculator" class="w-4 h-4"></i>
                                    </div>
                                    <h3 class="text-sm font-bold text-white">Kasir POS &amp; Meja</h3>
                                    <p class="text-[11.5px] text-slate-400 leading-relaxed">
                                        Cetak struk kilat, terima pembayaran QRIS otomatis, split bill, dan layar pesanan dapur (KDS).
                                    </p>
                                </div>

                                <!-- Fitur 2: Akuntansi -->
                                <div class="p-4 rounded-xl bg-white/[0.05] border border-white/10 space-y-2">
                                    <div class="w-8 h-8 rounded-lg bg-[#34C759]/20 text-[#34C759] flex items-center justify-center">
                                        <i data-lucide="line-chart" class="w-4 h-4"></i>
                                    </div>
                                    <h3 class="text-sm font-bold text-white">Laba Rugi Otomatis</h3>
                                    <p class="text-[11.5px] text-slate-400 leading-relaxed">
                                        Jurnal transaksi otomatis dan laporan keuangan berstandar SAK EMKM tanpa keahlian akunting rumit.
                                    </p>
                                </div>

                                <!-- Fitur 3: Stok & Gudang -->
                                <div class="p-4 rounded-xl bg-white/[0.05] border border-white/10 space-y-2">
                                    <div class="w-8 h-8 rounded-lg bg-[#FF9500]/20 text-[#FF9500] flex items-center justify-center">
                                        <i data-lucide="package" class="w-4 h-4"></i>
                                    </div>
                                    <h3 class="text-sm font-bold text-white">Stok &amp; Resep HPP</h3>
                                    <p class="text-[11.5px] text-slate-400 leading-relaxed">
                                        Hitung HPP otomatis dari resep BOM, kontrol multi-gudang, kartu mutasi, dan opname stok fisik.
                                    </p>
                                </div>

                                <!-- Fitur 4: WhatsApp & CRM -->
                                <div class="p-4 rounded-xl bg-white/[0.05] border border-white/10 space-y-2">
                                    <div class="w-8 h-8 rounded-lg bg-[#AF52DE]/20 text-[#AF52DE] flex items-center justify-center">
                                        <i data-lucide="message-square" class="w-4 h-4"></i>
                                    </div>
                                    <h3 class="text-sm font-bold text-white">WhatsApp &amp; Online</h3>
                                    <p class="text-[11.5px] text-slate-400 leading-relaxed">
                                        Kirim struk ke WA pelanggan, broadcast promo resmi, dan terima pesanan dari website toko mandiri.
                                    </p>
                                </div>
                            </div>

                            <!-- Trust & Privacy Security Assurance Callout -->
                            <div class="rounded-xl bg-white/[0.04] border border-white/10 p-4 space-y-2">
                                <div class="flex items-center gap-2 text-xs font-bold text-[#34C759]">
                                    <i data-lucide="lock" class="w-4 h-4"></i>
                                    <span>Jaminan Keamanan &amp; Kerahasiaan Data Bisnis</span>
                                </div>
                                <p class="text-[12px] text-slate-300 leading-relaxed">
                                    Data operasional, margin, dan transaksi Anda terisolasi secara privat dengan enkripsi multi-tenant mandiri dan dilengkapi fitur jejak audit anti-fraud.
                                </p>
                            </div>

                            <!-- Microcopy Note -->
                            <div class="pt-2 text-center text-[11px] text-slate-400 flex items-center justify-center gap-1.5">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Penataan modul kerja dapat disesuaikan kapan saja di menu Pengaturan Akun Owner.</span>
                            </div>

                        </div>
                    </div>

                </div>
            </form>

        </div>
    </div>
@endsection
