@extends('layouts.public_marketing', ['title' => 'Lengkapi Pendaftaran Google - COOCA', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-12rem)] py-8 sm:py-12 lg:py-14 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-7xl mx-auto space-y-6 sm:space-y-8"
            x-data="{
                businessScale: '{{ old('business_scale', 'umkm') }}',
                selectedTemplate: '{{ old('template_code', '') }}',
                templates: {{ Js::from($templateSummaries ?? []) }},
                templateDisabledMap: {{ Js::from($templateDisabledMap ?? []) }},
                allModuleKeys: {{ Js::from(array_keys($allModules ?? [])) }},
                moduleFilter: 'all',
                moduleStates: {},

                init() {
                    const oldEnabled = {{ Js::from(old('enabled_modules', null)) }};
                    if (Array.isArray(oldEnabled)) {
                        const states = {};
                        this.allModuleKeys.forEach(k => {
                            states[k] = oldEnabled.includes(k);
                        });
                        this.moduleStates = states;
                    } else {
                        this.applyTemplatePreset(this.selectedTemplate);
                    }
                },

                applyTemplatePreset(code) {
                    const disabled = this.templateDisabledMap[code] || [];
                    const states = {};
                    this.allModuleKeys.forEach(k => {
                        states[k] = !disabled.includes(k);
                    });
                    this.moduleStates = states;
                },

                onTemplateChange() {
                    this.applyTemplatePreset(this.selectedTemplate);
                },

                isModuleEnabled(key) {
                    return !!this.moduleStates[key];
                },

                toggleModule(key, val) {
                    this.moduleStates[key] = typeof val === 'boolean' ? val : !this.moduleStates[key];
                },

                setAll(enable) {
                    const states = {};
                    this.allModuleKeys.forEach(k => {
                        states[k] = enable;
                    });
                    this.moduleStates = states;
                },

                resetPreset() {
                    this.applyTemplatePreset(this.selectedTemplate);
                },

                matchesFilter(key) {
                    if (this.moduleFilter === 'all') return true;
                    if (this.moduleFilter === 'active') return this.isModuleEnabled(key);
                    if (this.moduleFilter === 'inactive') return !this.isModuleEnabled(key);
                    return true;
                },

                get activeCount() {
                    return Object.values(this.moduleStates).filter(Boolean).length;
                },

                get inactiveCount() {
                    return this.allModuleKeys.length - this.activeCount;
                },

                get totalCount() {
                    return this.allModuleKeys.length;
                },

                get currentTemplate() {
                    return this.templates[this.selectedTemplate] || null;
                }
            }">

            <!-- Official COOCA Branding & Header -->
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <a href="{{ route('landing') }}" class="inline-block transition-transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 rounded-xl" aria-label="COOCA Beranda">
                    <img src="{{ asset('assets/image/cooca-logo-landscape.png') }}" alt="COOCA" class="h-9 sm:h-10 w-auto object-contain mx-auto">
                </a>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Lengkapi Pendaftaran Workspace
                </h1>
                
                <div class="flex flex-wrap items-center justify-center gap-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                    <span>Akun Google siap dihubungkan:</span>
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
                            <svg class="w-3 h-3 mr-0.5 inline" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
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

            <!-- Main Interactive Registration Form -->
            <form method="POST" action="{{ route('register.google.submit') }}" id="register-google-form">
                @csrf
                <input type="hidden" name="has_module_selection" value="1">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                    
                    <!-- ========================================================= -->
                    <!-- LEFT COLUMN: Identitas Bisnis & Submit Form              -->
                    <!-- ========================================================= -->
                    <div class="lg:col-span-5 xl:col-span-4 space-y-5 lg:sticky lg:top-8">
                        <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-sm space-y-5">
                            
                            <!-- Header Info -->
                            <div class="flex items-center gap-3 pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                                <div class="w-9 h-9 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                    <i data-lucide="building" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h2 class="text-[16px] font-bold text-black dark:text-white leading-tight">Identitas Bisnis</h2>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Profil dasar &amp; WhatsApp pemilik</p>
                                </div>
                            </div>

                            <!-- Input Nama Usaha -->
                            <div>
                                <label for="business_name" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                    Nama Usaha / Perusahaan <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="business_name" id="business_name"
                                    value="{{ old('business_name', 'Usaha ' . ($pending['name'] ?? 'Saya')) }}" required
                                    class="w-full px-3.5 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-sm sm:text-base text-slate-900 dark:text-white transition-all outline-none"
                                    placeholder="Contoh: Kedai Kopi Sukses / CV Berkah">
                            </div>

                            <!-- Skala & Model Operasional Bisnis -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200">
                                        Skala &amp; Model Operasional Bisnis <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">Bisa diubah nanti</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2.5">
                                    <!-- Kartu UMKM -->
                                    <label class="relative flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition-all active:scale-[0.99]"
                                        :class="businessScale === 'umkm' ? 'bg-[#007AFF]/5 border-[#007AFF] shadow-xs ring-1 ring-[#007AFF]/30' : 'bg-slate-50 dark:bg-[#2C2C2E] border-black/10 dark:border-white/10 hover:border-slate-300 dark:hover:border-white/20'">
                                        <input type="radio" name="business_scale" value="umkm" x-model="businessScale" class="sr-only">
                                        <div class="w-8 h-8 rounded-lg bg-[#34C759]/12 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0 font-bold mt-0.5">
                                            <i data-lucide="store" class="w-4 h-4"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">UMKM &amp; Toko Mandiri</h4>
                                                <span x-show="businessScale === 'umkm'" class="text-[10px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 px-2 py-0.5 rounded-full shrink-0">Terpilih</span>
                                            </div>
                                            <p class="text-[11.5px] text-slate-600 dark:text-slate-400 mt-0.5 leading-snug">
                                                Warung, kafe, butik, bengkel, atau toko 1–3 cabang.
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
                                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">Korporasi &amp; Multi-Cabang</h4>
                                                <span x-show="businessScale === 'corporate'" class="text-[10px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 px-2 py-0.5 rounded-full shrink-0">Terpilih</span>
                                            </div>
                                            <p class="text-[11.5px] text-slate-600 dark:text-slate-400 mt-0.5 leading-snug">
                                                Distributor, waralaba, &amp; multi-gudang bertingkat.
                                            </p>
                                        </div>
                                    </label>
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
                                        class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-sm sm:text-base text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                        placeholder="081234567890">
                                </div>
                                <p class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                                    Kode OTP verifikasi akan dikirim melalui pesan WhatsApp resmi.
                                </p>
                            </div>

                            <!-- Mini Live Modul Summary Widget -->
                            <div class="rounded-xl bg-slate-50 dark:bg-[#2C2C2E]/60 border border-black/5 dark:border-white/5 p-3.5 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-black/50 dark:text-white/50">Preset Modul:</span>
                                    <span class="font-semibold text-black dark:text-white truncate max-w-[180px]" x-text="currentTemplate ? currentTemplate.name : 'Bisnis Umum'"></span>
                                </div>
                                <div class="flex items-center gap-2 pt-1.5 border-t border-black/5 dark:border-white/5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] text-[11.5px] font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                        <span x-text="activeCount + ' Aktif'"></span>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-black/5 dark:bg-white/5 text-black/50 dark:text-white/50 text-[11.5px] font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-black/30 dark:bg-white/30"></span>
                                        <span x-text="inactiveCount + ' Nonaktif'"></span>
                                    </span>
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
                            <div class="text-center pt-1 border-t border-black/[0.04] dark:border-white/[0.06]">
                                <a href="{{ route('login') }}" class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors">
                                    Batalkan dan kembali ke login
                                </a>
                            </div>

                        </div>
                    </div>

                    <!-- ========================================================= -->
                    <!-- RIGHT COLUMN: Template Industri & Kelola Modul (Lebar)   -->
                    <!-- ========================================================= -->
                    <div class="lg:col-span-7 xl:col-span-8 space-y-6">
                        <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] rounded-2xl sm:rounded-3xl p-5 sm:p-7 shadow-sm space-y-6">
                            
                            <!-- Header Card -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                                <div>
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                            <i data-lucide="sliders" class="w-5 h-5"></i>
                                        </div>
                                        <h2 class="text-[17px] font-bold text-black dark:text-white leading-tight">Penataan Modul &amp; Fitur Bisnis</h2>
                                    </div>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-1">
                                        Pilih preset industri di bawah atau atur tombol sakelar modul secara mandiri sesuai alur operasional Anda.
                                    </p>
                                </div>
                            </div>

                            <!-- Selector Template Industri -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label for="template_code" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200">
                                        Template Industri (Preset Modul)
                                    </label>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">Bisa diubah kapan saja di Pengaturan</span>
                                </div>
                                <select name="template_code" id="template_code" x-model="selectedTemplate" @change="onTemplateChange()"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-sm sm:text-base font-semibold text-slate-900 dark:text-white transition-all outline-none cursor-pointer">
                                    <option value="">-- Mulai Bisnis Umum (Aktifkan Semua Modul) --</option>
                                    @foreach ($templates as $tmpl)
                                        <option value="{{ $tmpl->code }}" {{ old('template_code') == $tmpl->code ? 'selected' : '' }}>
                                            {{ $tmpl->name }} ({{ strtoupper($tmpl->industry_category) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Live Preset Info Callout -->
                            <div class="rounded-xl border p-4 transition-all"
                                :class="currentTemplate ? 'bg-[#007AFF]/5 border-[#007AFF]/20 text-black dark:text-white' : 'bg-slate-50 dark:bg-white/[0.03] border-black/5 dark:border-white/5 text-black/70 dark:text-white/70'">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                                        :class="currentTemplate ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-black/5 dark:bg-white/5 text-black/50 dark:text-white/50'">
                                        <i data-lucide="layout-template" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-sm font-bold" x-text="currentTemplate ? 'Preset: ' + currentTemplate.name : 'Preset: Bisnis Umum Standard'"></span>
                                            <span x-show="currentTemplate" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20"
                                                x-text="currentTemplate?.category"></span>
                                        </div>
                                        <p class="text-[12px] opacity-75 mt-0.5 leading-relaxed"
                                            x-text="currentTemplate ? 'Modul kerja telah disesuaikan otomatis dengan alur industri ' + currentTemplate.name + '. Gunakan sakelar toggle di bawah jika ingin mengaktifkan atau menonaktifkan modul.' : 'Seluruh 15 modul kerja diaktifkan secara default. Anda dapat mematikan modul yang tidak diperlukan.'">
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Filter Tabs & Quick Action Toolbar -->
                            <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                                <!-- Filter Tabs -->
                                <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-white/5 border border-black/5 dark:border-white/5 text-xs">
                                    <button type="button" @click="moduleFilter = 'all'"
                                        class="px-3 py-1.5 rounded-lg font-medium transition-all"
                                        :class="moduleFilter === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                                        Semua (<span x-text="totalCount"></span>)
                                    </button>
                                    <button type="button" @click="moduleFilter = 'active'"
                                        class="px-3 py-1.5 rounded-lg font-medium transition-all flex items-center gap-1.5"
                                        :class="moduleFilter === 'active' ? 'bg-white dark:bg-[#2C2C2E] text-[#248A3D] dark:text-[#30D158] shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                        <span>Aktif (<span x-text="activeCount"></span>)</span>
                                    </button>
                                    <button type="button" @click="moduleFilter = 'inactive'"
                                        class="px-3 py-1.5 rounded-lg font-medium transition-all flex items-center gap-1.5"
                                        :class="moduleFilter === 'inactive' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                                        <span class="w-1.5 h-1.5 rounded-full bg-black/30 dark:bg-white/30"></span>
                                        <span>Nonaktif (<span x-text="inactiveCount"></span>)</span>
                                    </button>
                                </div>

                                <!-- Quick Actions -->
                                <div class="flex items-center gap-2 text-xs">
                                    <button type="button" @click="setAll(true)"
                                        class="h-8 px-3 rounded-lg font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all flex items-center gap-1.5 cursor-pointer">
                                        <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                                        <span>Aktifkan Semua</span>
                                    </button>
                                    <button type="button" @click="resetPreset()"
                                        class="h-8 px-3 rounded-lg font-medium text-black/60 dark:text-white/60 bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 active:scale-[0.97] transition-all flex items-center gap-1.5 cursor-pointer">
                                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                        <span>Reset Preset</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Modules Grid (Identik dengan settings/index.blade.php tab kelola module) -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($allModules as $moduleKey => $mod)
                                    <div x-show="matchesFilter('{{ $moduleKey }}')"
                                        class="rounded-[16px] border p-4 sm:p-5 flex flex-col justify-between gap-4 transition-all duration-150"
                                        :class="isModuleEnabled('{{ $moduleKey }}')
                                            ? 'bg-white dark:bg-[#1C1C1E] border-black/[0.08] dark:border-white/10 shadow-xs ring-1 ring-[#007AFF]/15 hover:border-[#007AFF]/40'
                                            : 'bg-slate-50/70 dark:bg-[#161618] border-dashed border-black/10 dark:border-white/5 opacity-80 hover:opacity-100'">
                                        
                                        <div class="space-y-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-[10px] flex items-center justify-center shrink-0 transition-colors"
                                                        :class="isModuleEnabled('{{ $moduleKey }}')
                                                            ? 'bg-[#007AFF]/10 text-[#007AFF]'
                                                            : 'bg-black/5 dark:bg-white/5 text-black/40 dark:text-white/40'">
                                                        <i data-lucide="{{ $mod['icon'] }}" class="w-5 h-5"></i>
                                                    </div>
                                                    <div>
                                                        <span class="text-[11px] font-semibold tracking-wide uppercase text-black/40 dark:text-white/40 block">
                                                            {{ $mod['category'] }}
                                                        </span>
                                                        <h3 class="text-[14.5px] font-semibold text-black dark:text-white leading-snug">
                                                            {{ $mod['name'] }}
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>

                                            <p class="text-[12.5px] text-black/60 dark:text-white/60 leading-relaxed min-h-[36px]">
                                                {{ $mod['description'] }}
                                            </p>

                                            <div class="pt-2 border-t border-black/5 dark:border-white/5">
                                                <span class="text-[11px] text-black/40 dark:text-white/40 flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-black/30 dark:text-white/30" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                                    </svg>
                                                    <span>{{ count($mod['permissions']) }} hak akses terkait</span>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Apple Switch Toggle (Sama seperti pada Settings Index) -->
                                        <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                                            <span class="text-[12px] font-medium transition-colors"
                                                :class="isModuleEnabled('{{ $moduleKey }}') ? 'text-[#248A3D] dark:text-[#30D158]' : 'text-black/40 dark:text-white/40'"
                                                x-text="isModuleEnabled('{{ $moduleKey }}') ? 'Aktif (Menu Muncul)' : 'Nonaktif (Disembunyikan)'">
                                            </span>

                                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                                <input type="checkbox"
                                                    name="enabled_modules[]"
                                                    value="{{ $moduleKey }}"
                                                    :checked="isModuleEnabled('{{ $moduleKey }}')"
                                                    @change="toggleModule('{{ $moduleKey }}', $event.target.checked)"
                                                    class="sr-only peer">
                                                <div class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759] transition-colors"></div>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Bottom Info Footer -->
                            <div class="rounded-xl bg-slate-50 dark:bg-white/[0.02] border border-black/5 dark:border-white/5 p-4 flex items-start gap-3 text-xs text-black/60 dark:text-white/60">
                                <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                                <div class="space-y-0.5">
                                    <p class="font-medium text-black dark:text-white">Fleksibilitas Penuh Tanpa Menghapus Data</p>
                                    <p>Modul yang dinonaktifkan akan otomatis disembunyikan dari sidebar navigasi dan hak akses role staf tanpa menghapus data. Sebagai Owner, Anda dapat mengaktifkannya kembali kapan saja melalui menu Pengaturan Bisnis.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </form>

        </div>
    </div>
@endsection
