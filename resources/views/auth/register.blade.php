@extends('layouts.public_marketing', ['title' => 'Daftar Bisnis Baru - Cooca', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-16rem)] flex flex-col justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-xl">
            <!-- Apple HIG Header -->
            <div class="text-center mb-7">
                <div
                    class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] mb-3 shadow-sm transition-transform hover:scale-105">
                    <i data-lucide="sparkles" class="w-7 h-7"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white tracking-tight">Daftarkan Bisnis
                    Anda</h1>
                <p class="mt-1.5 text-xs sm:text-sm text-black/60 dark:text-white/60">Pilih dari template industri siap pakai
                    untuk memulai operasional</p>
            </div>

            <!-- Apple Inset Register Card -->
            <div x-data="{
                selectedTemplate: '{{ old('template_code', '') }}',
                templates: {{ Js::from($templateSummaries ?? []) }},
                showPassword: false,
                showPasswordConfirm: false,
                get currentTemplate() {
                    return this.templates[this.selectedTemplate] || null;
                }
            }"
                class="glass-card bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] rounded-[28px] p-6 sm:p-9 shadow-2xl shadow-black/5 dark:shadow-black/60 relative overflow-hidden transition-colors">

                <!-- Alerts -->
                @if ($errors->any())
                    <div
                        class="mb-5 p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs">
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

                    <!-- Google SSO shortcut (Apple Inset Style, 48px Touch Target) -->
                    <a href="{{ route('auth.google') }}"
                        class="w-full min-h-[48px] py-3 px-4 mb-2 rounded-[14px] bg-white dark:bg-white/[0.06] border border-black/10 dark:border-white/10 hover:bg-black/[0.03] dark:hover:bg-white/[0.1] text-black/85 dark:text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-3 transition-all shadow-sm active:scale-[0.99]">
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
                        <div class="flex-grow border-t border-black/10 dark:border-white/10"></div>
                        <span
                            class="flex-shrink mx-3 text-[11px] text-black/40 dark:text-white/40 font-semibold uppercase tracking-wider">Atau
                            lengkapi formulir pendaftaran</span>
                        <div class="flex-grow border-t border-black/10 dark:border-white/10"></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="name"
                                class="block text-xs sm:text-sm font-semibold text-black/80 dark:text-white/85 mb-1.5">Nama
                                Pemilik / Admin <span class="text-[#007AFF] dark:text-[#0A84FF]">*</span></label>
                            <div class="relative">
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    autofocus
                                    class="w-full px-3.5 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[14px] text-[16px] sm:text-sm text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all"
                                    placeholder="Contoh: Budi Santoso">
                            </div>
                        </div>

                        <div>
                            <label for="email"
                                class="block text-xs sm:text-sm font-semibold text-black/80 dark:text-white/85 mb-1.5">Email
                                Bisnis <span class="text-[#007AFF] dark:text-[#0A84FF]">*</span></label>
                            <div class="relative">
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                    class="w-full px-3.5 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[14px] text-[16px] sm:text-sm text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all"
                                    placeholder="budi@usaha.com">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="business_name"
                                class="block text-xs sm:text-sm font-semibold text-black/80 dark:text-white/85 mb-1.5">Nama
                                Usaha / Perusahaan <span class="text-[#007AFF] dark:text-[#0A84FF]">*</span></label>
                            <div class="relative">
                                <input type="text" name="business_name" id="business_name"
                                    value="{{ old('business_name') }}" required
                                    class="w-full px-3.5 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[14px] text-[16px] sm:text-sm text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all"
                                    placeholder="Kedai Kopi Sukses / CV Berkah">
                            </div>
                        </div>

                        <div>
                            <label for="phone"
                                class="block text-xs sm:text-sm font-semibold text-black/80 dark:text-white/85 mb-1.5">Nomor
                                WhatsApp / HP <span class="text-[#007AFF] dark:text-[#0A84FF]">*</span></label>
                            <div class="relative">
                                <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required
                                    inputmode="tel" autocomplete="tel"
                                    class="w-full px-3.5 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[14px] text-[16px] sm:text-sm text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all"
                                    placeholder="081234567890">
                            </div>
                            <p class="text-[11px] text-black/50 dark:text-white/50 mt-1">Kode OTP aktivasi akan dikirim ke
                                WhatsApp ini.</p>
                        </div>
                    </div>

                    <!-- Template Selection with Bento Preview Card (Apple Inset Style) -->
                    <div class="space-y-2 pt-1">
                        <div class="flex items-center justify-between">
                            <label for="template_code"
                                class="block text-xs sm:text-sm font-semibold text-black/80 dark:text-white/85">
                                Template Industri (Preset Otomasi &amp; Penataan Menu)
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

                                <!-- Enabled features badges -->
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

                                <!-- Decluttered/disabled features notice -->
                                <template x-if="currentTemplate.disabled && currentTemplate.disabled.length > 0">
                                    <div class="space-y-1.5 pt-2 border-t border-black/5 dark:border-white/5">
                                        <div
                                            class="text-[11px] font-semibold text-black/45 dark:text-white/45 flex items-center gap-1">
                                            <i data-lucide="eye-off" class="w-3 h-3"></i>
                                            <span>Modul disederhanakan agar tampilan rapi:</span>
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
                                            diaktifkan kembali kapan saja oleh Pemilik Bisnis di Pengaturan.</p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password"
                                class="block text-xs sm:text-sm font-semibold text-black/80 dark:text-white/85 mb-1.5">Kata
                                Sandi <span class="text-[#007AFF] dark:text-[#0A84FF]">*</span></label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required
                                    class="w-full pl-3.5 pr-11 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[14px] text-[16px] sm:text-sm text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all"
                                    placeholder="Min. 8 karakter">
                                <button type="button" @click="showPassword = !showPassword"
                                    :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                    class="absolute right-0 top-0 bottom-0 w-11 flex items-center justify-center text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white focus:outline-none transition-colors cursor-pointer">
                                    <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="password_confirmation"
                                class="block text-xs sm:text-sm font-semibold text-black/80 dark:text-white/85 mb-1.5">Konfirmasi
                                Sandi <span class="text-[#007AFF] dark:text-[#0A84FF]">*</span></label>
                            <div class="relative">
                                <input :type="showPasswordConfirm ? 'text' : 'password'" name="password_confirmation"
                                    id="password_confirmation" required
                                    class="w-full pl-3.5 pr-11 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[14px] text-[16px] sm:text-sm text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all"
                                    placeholder="Ulangi sandi">
                                <button type="button" @click="showPasswordConfirm = !showPasswordConfirm"
                                    :aria-label="showPasswordConfirm ? 'Sembunyikan konfirmasi sandi' : 'Tampilkan konfirmasi sandi'"
                                    class="absolute right-0 top-0 bottom-0 w-11 flex items-center justify-center text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white focus:outline-none transition-colors cursor-pointer">
                                    <i :data-lucide="showPasswordConfirm ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full min-h-[50px] py-3.5 px-4 rounded-[14px] glow-btn bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm sm:text-base shadow-lg shadow-[#007AFF]/25 flex items-center justify-center gap-2 transition-all active:scale-[0.98]">
                            <span>Buat Akun &amp; Mulai Usaha</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>

                <div
                    class="mt-5 pt-4 border-t border-black/5 dark:border-white/10 text-center text-xs sm:text-sm text-black/60 dark:text-white/60">
                    Sudah memiliki akun?
                    <a href="{{ route('login') }}"
                        class="font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors">Masuk di
                        Sini</a>
                </div>
            </div>
        </div>
    </div>
@endsection
