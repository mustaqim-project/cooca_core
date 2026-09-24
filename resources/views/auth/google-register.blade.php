@extends('layouts.public_marketing', ['title' => 'Lengkapi Pendaftaran Google - COOCA', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-14rem)] flex flex-col justify-center py-12 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md mx-auto">

            <!-- Official COOCA Branding & Header -->
            <div class="text-center mb-8">
                <a href="{{ route('landing') }}" class="inline-block transition-transform hover:scale-105 mb-5 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 rounded-xl" aria-label="COOCA Beranda">
                    <img src="{{ asset('assets/image/cooca-logo-landscape.png') }}" alt="COOCA" class="h-9 sm:h-10 w-auto object-contain mx-auto">
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Lengkapi Pendaftaran
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
                    Akun Google <span class="font-semibold text-slate-900 dark:text-white px-1.5 py-0.5 rounded bg-slate-100 dark:bg-white/10">{{ $pending['email'] }}</span> siap dihubungkan.
                </p>
            </div>

            <!-- Structured Auth Card -->
            <div x-data="{
                businessScale: '{{ old('business_scale', 'umkm') }}',
                selectedTemplate: '{{ old('template_code', '') }}',
                templates: {{ Js::from($templateSummaries ?? []) }},
                get currentTemplate() {
                    return this.templates[this.selectedTemplate] || null;
                }
            }"
                class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-black/40 transition-colors">

                @if ($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs">
                        <div class="font-semibold mb-1 flex items-center gap-1.5 text-sm">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                            <span>Mohon Periksa Kembali:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs opacity-90 pl-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.google.submit') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="business_name" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                            Nama Usaha / Perusahaan <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="business_name" id="business_name"
                            value="{{ old('business_name', 'Usaha ' . ($pending['name'] ?? 'Saya')) }}" required
                            class="w-full px-3.5 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-sm sm:text-base text-slate-900 dark:text-white transition-all outline-none"
                            placeholder="Contoh: Kedai Kopi Sukses / CV Berkah">
                    </div>

                    <!-- Segment Selection Bento Cards -->
                    <div class="space-y-2 pt-1">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200">
                                Skala &amp; Model Operasional Bisnis <span class="text-[#FF3B30]">*</span>
                            </label>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">Bisa disesuaikan</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Kartu UMKM -->
                            <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all active:scale-[0.99]"
                                :class="businessScale === 'umkm' ? 'bg-[#007AFF]/5 border-[#007AFF] shadow-sm ring-1 ring-[#007AFF]/30' : 'bg-slate-50 dark:bg-[#1E2638] border-slate-200 dark:border-white/10 hover:border-slate-300 dark:hover:border-white/20'">
                                <input type="radio" name="business_scale" value="umkm" x-model="businessScale" class="sr-only">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-[#34C759]/12 text-[#34C759] dark:text-[#30D158] flex items-center justify-center font-bold">
                                        <i data-lucide="store" class="w-4 h-4"></i>
                                    </div>
                                    <span x-show="businessScale === 'umkm'" class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 px-2 py-0.5 rounded-full">Terpilih</span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">UMKM Mandiri</h4>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                                    Untuk warung, kafe, butik, bengkel, atau toko 1–3 cabang.
                                </p>
                            </label>

                            <!-- Kartu Korporasi -->
                            <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all active:scale-[0.99]"
                                :class="businessScale === 'corporate' ? 'bg-[#007AFF]/5 border-[#007AFF] shadow-sm ring-1 ring-[#007AFF]/30' : 'bg-slate-50 dark:bg-[#1E2638] border-slate-200 dark:border-white/10 hover:border-slate-300 dark:hover:border-white/20'">
                                <input type="radio" name="business_scale" value="corporate" x-model="businessScale" class="sr-only">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center font-bold">
                                        <i data-lucide="building-2" class="w-4 h-4"></i>
                                    </div>
                                    <span x-show="businessScale === 'corporate'" class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 px-2 py-0.5 rounded-full">Terpilih</span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">Multi-Cabang</h4>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                                    Untuk distributor, waralaba, &amp; multi-gudang bertingkat.
                                </p>
                            </label>
                        </div>
                    </div>

                    <!-- Template Selection with Live Preview Card -->
                    <div class="space-y-2 pt-1">
                        <div class="flex items-center justify-between">
                            <label for="template_code" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200">
                                Template Industri (Preset Modul)
                            </label>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">Bisa diubah nanti</span>
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

                        <!-- Interactive Live Preview Card -->
                        <template x-if="currentTemplate">
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 space-y-3 transition-all text-xs">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs sm:text-sm">
                                        <i data-lucide="layout-template" class="w-4 h-4 shrink-0"></i>
                                        <span x-text="'Penataan Modul: ' + currentTemplate.name"></span>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] border border-[#34C759]/20"
                                        x-text="currentTemplate.category"></span>
                                </div>

                                <div class="space-y-1.5">
                                    <div class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Modul Kerja Aktif:</div>
                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-for="item in currentTemplate.enabled" :key="item.key">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] border border-[#34C759]/20 text-[11px] font-medium">
                                                <i data-lucide="check" class="w-3 h-3"></i>
                                                <span x-text="item.name"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <template x-if="currentTemplate.disabled && currentTemplate.disabled.length > 0">
                                    <div class="space-y-1.5 pt-2 border-t border-slate-200/80 dark:border-white/5">
                                        <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                            <i data-lucide="eye-off" class="w-3 h-3"></i>
                                            <span>Modul disederhanakan:</span>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="item in currentTemplate.disabled" :key="item.key">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-200/60 dark:bg-white/5 text-slate-500 dark:text-slate-400 border border-slate-300/40 dark:border-white/5 text-[11px] line-through">
                                                    <span x-text="item.name"></span>
                                                </span>
                                            </template>
                                        </div>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 italic mt-0.5">Dapat diaktifkan kembali kapan saja di Pengaturan.</p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div>
                        <label for="phone" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                            Nomor WhatsApp Pemilik <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <i data-lucide="phone" class="w-4 h-4"></i>
                            </div>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required inputmode="tel" autocomplete="tel"
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-sm sm:text-base text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                placeholder="081234567890">
                        </div>
                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                            Kode OTP verifikasi akan dikirim melalui pesan WhatsApp resmi.
                        </p>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] hover:from-[#1cd0ff] hover:to-[#006fe6] text-white font-bold text-sm sm:text-[15px] shadow-[0_2px_12px_rgba(0,194,255,0.3)] hover:shadow-[0_4px_20px_rgba(0,194,255,0.5)] active:scale-[0.99] min-h-[46px] transition-all focus:outline-none focus:ring-4 focus:ring-[#007AFF]/25 cursor-pointer">
                            <span>Lanjutkan &amp; Kirim OTP WhatsApp</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>

                <div class="mt-5 pt-4 border-t border-slate-200/80 dark:border-white/10 text-center text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    <a href="{{ route('login') }}" class="font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded">
                        Batalkan dan kembali ke login
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
