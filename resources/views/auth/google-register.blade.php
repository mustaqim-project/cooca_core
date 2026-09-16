@extends('layouts.public_marketing', ['title' => 'Lengkapi Pendaftaran Google - Cooca', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-16rem)] flex flex-col justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Apple HIG Header -->
            <div class="text-center mb-7">
                <div
                    class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] mb-3 shadow-sm transition-transform hover:scale-105">
                    <i data-lucide="user-plus" class="w-7 h-7"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white tracking-tight">Lengkapi
                    Pendaftaran</h1>
                <p class="mt-1.5 text-xs sm:text-sm text-black/60 dark:text-white/60">
                    Akun Google <span
                        class="font-semibold text-black dark:text-white px-1 py-0.5 rounded bg-black/[0.04] dark:bg-white/[0.08]">{{ $pending['email'] }}</span>
                    siap dihubungkan.
                </p>
            </div>

            <div x-data="{
                selectedTemplate: '{{ old('template_code', '') }}',
                templates: {{ Js::from($templateSummaries ?? []) }},
                get currentTemplate() {
                    return this.templates[this.selectedTemplate] || null;
                }
            }"
                class="glass-card bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] rounded-[28px] p-6 sm:p-8 shadow-2xl shadow-black/5 dark:shadow-black/60 transition-colors">
                @if ($errors->any())
                    <div
                        class="mb-5 p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs">
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
                        <label for="business_name"
                            class="block text-xs sm:text-sm font-semibold text-black/80 dark:text-white/85 mb-1.5">Nama
                            Usaha / Perusahaan <span class="text-[#007AFF] dark:text-[#0A84FF]">*</span></label>
                        <input type="text" name="business_name" id="business_name"
                            value="{{ old('business_name', 'Usaha ' . ($pending['name'] ?? 'Saya')) }}" required
                            class="w-full px-3.5 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[14px] text-[16px] sm:text-sm text-black dark:text-white transition-all"
                            placeholder="Contoh: Kedai Kopi Sukses / CV Berkah">
                    </div>

                    <!-- Template Selection with Live Preview Card (Apple Inset Style) -->
                    <div class="space-y-2 pt-1">
                        <div class="flex items-center justify-between">
                            <label for="template_code"
                                class="block text-xs sm:text-sm font-semibold text-black/80 dark:text-white/85">
                                Template Industri (Preset Modul)
                            </label>
                            <span class="text-[11px] text-black/45 dark:text-white/45">Bisa diubah nanti</span>
                        </div>
                        <select name="template_code" id="template_code" x-model="selectedTemplate"
                            class="w-full px-3.5 py-3 bg-black/[0.03] dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[14px] text-[16px] sm:text-sm text-black dark:text-white transition-all">
                            <option value="">-- Mulai Bisnis Umum (Aktifkan Semua Modul) --</option>
                            @foreach ($templates as $tmpl)
                                <option value="{{ $tmpl->code }}"
                                    {{ old('template_code') == $tmpl->code ? 'selected' : '' }}>
                                    {{ $tmpl->name }} ({{ strtoupper($tmpl->industry_category) }})
                                </option>
                            @endforeach
                        </select>

                        <!-- Interactive Live Bento Preview Card -->
                        <template x-if="currentTemplate">
                            <div
                                class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-3 transition-all text-xs">
                                <div class="flex items-center justify-between">
                                    <div
                                        class="flex items-center gap-2 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs sm:text-sm">
                                        <i data-lucide="layout-template" class="w-4 h-4 shrink-0"></i>
                                        <span x-text="'Penataan Modul: ' + currentTemplate.name"></span>
                                    </div>
                                    <span
                                        class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] border border-[#34C759]/20"
                                        x-text="currentTemplate.category"></span>
                                </div>

                                <div class="space-y-1.5">
                                    <div class="text-[11px] font-semibold text-black/70 dark:text-white/70">Modul Kerja
                                        Aktif:</div>
                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-for="item in currentTemplate.enabled" :key="item.key">
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[10px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] border border-[#34C759]/20 text-[11px] font-medium">
                                                <i data-lucide="check" class="w-3 h-3"></i>
                                                <span x-text="item.name"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <template x-if="currentTemplate.disabled && currentTemplate.disabled.length > 0">
                                    <div class="space-y-1.5 pt-2 border-t border-black/5 dark:border-white/5">
                                        <div
                                            class="text-[11px] font-semibold text-black/45 dark:text-white/45 flex items-center gap-1">
                                            <i data-lucide="eye-off" class="w-3 h-3"></i>
                                            <span>Modul disederhanakan:</span>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="item in currentTemplate.disabled" :key="item.key">
                                                <span
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.05] text-black/40 dark:text-white/40 border border-black/5 dark:border-white/5 text-[11px] line-through">
                                                    <span x-text="item.name"></span>
                                                </span>
                                            </template>
                                        </div>
                                        <p class="text-[10px] text-black/40 dark:text-white/40 italic mt-0.5">Dapat
                                            diaktifkan kembali kapan saja di Pengaturan.</p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div>
                        <label for="phone"
                            class="block text-xs sm:text-sm font-semibold text-black/80 dark:text-white/85 mb-1.5">Nomor
                            WhatsApp Pemilik <span class="text-[#007AFF] dark:text-[#0A84FF]">*</span></label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required
                            inputmode="tel" autocomplete="tel"
                            class="w-full px-3.5 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[14px] text-[16px] sm:text-sm text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all"
                            placeholder="081234567890">
                        <p class="mt-1 text-[11px] text-black/50 dark:text-white/50">Kode OTP verifikasi akan dikirim
                            melalui pesan WhatsApp resmi.</p>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full min-h-[50px] py-3.5 px-4 rounded-[14px] glow-btn bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm sm:text-base shadow-lg shadow-[#007AFF]/25 transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                            <span>Lanjutkan &amp; Kirim OTP WhatsApp</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>

                <div
                    class="mt-5 pt-4 border-t border-black/5 dark:border-white/10 text-center text-xs sm:text-sm text-black/60 dark:text-white/60">
                    <a href="{{ route('login') }}"
                        class="font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors">Batalkan
                        dan kembali ke login</a>
                </div>
            </div>
        </div>
    </div>
@endsection
