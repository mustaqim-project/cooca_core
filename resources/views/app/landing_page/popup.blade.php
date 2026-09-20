@extends('layouts.app', [
    'title' => 'Pop Up Promo & Pengumuman Website - Cooca',
    'headerTitle' => 'Website & Toko Online',
    'headerSubtitle' => 'Kelola jendela pop-up promo, banner pengumuman modal, dan penawaran khusus toko online ' . $business->name,
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" 
         x-data="storefrontPopupEditor({
             enabled: {{ $landingPage->popup_enabled ? 'true' : 'false' }},
             frequency: '{{ $landingPage->popup_frequency ?? 'once_per_day' }}',
             title: @js($landingPage->popup_title ?? ''),
             badge: @js($landingPage->popup_badge ?? 'PROMO SPESIAL'),
             content: @js($landingPage->popup_content ?? ''),
             ctaText: @js($landingPage->popup_cta_text ?? 'Lihat Penawaran'),
             ctaUrl: @js($landingPage->popup_cta_url ?? ''),
             imageUrl: @js($landingPage->popup_image_url ?? ''),
             startsAt: @js($landingPage->popup_starts_at ? $landingPage->popup_starts_at->format('Y-m-d\TH:i') : ''),
             endsAt: @js($landingPage->popup_ends_at ? $landingPage->popup_ends_at->format('Y-m-d\TH:i') : ''),
             businessSlug: @js($business->slug),
             businessPhone: @js($business->phone ?? ''),
             businessName: @js($business->name)
         })">

        {{-- 0. STOREFRONT & WEBSITE HUB NAVIGATION TABS --}}
        @include('app.storefront.partials.navigation', ['title' => 'Pop Up Promo & Pengumuman'])

        {{-- Flash Messages & Validation Errors --}}
        @if (session('success'))
            <div class="rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 p-4 text-[#248A3D] dark:text-[#30D158] flex items-center justify-between gap-3 text-[13px] font-medium shadow-xs">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <a href="{{ $business->public_url }}" target="_blank" class="text-xs underline hover:no-underline font-semibold shrink-0">
                    Buka Toko &rarr;
                </a>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 p-4 text-[#C41E17] dark:text-[#FF453A] space-y-1.5 text-[13px] shadow-xs">
                <div class="flex items-center gap-2 font-semibold">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-[#FF3B30] shrink-0"></i>
                    <span>Terdapat beberapa kesalahan input:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-0.5 pl-6 opacity-90">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- TOP HEADER & STATUS COCKPIT --}}
        <header class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xs">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full" :class="enabled ? 'bg-[#34C759] animate-pulse' : 'bg-black/30 dark:bg-white/30'"></span>
                    <h2 class="text-lg sm:text-xl font-bold text-black dark:text-white tracking-tight">
                        CMS Pop-Up Promo Storefront
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold"
                          :class="enabled ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/[0.06] dark:bg-white/[0.08] text-black/50 dark:text-white/50'"
                          x-text="enabled ? 'Sedang Aktif' : 'Non-Aktif'">
                        {{ $landingPage->popup_enabled ? 'Sedang Aktif' : 'Non-Aktif' }}
                    </span>
                </div>
                <p class="text-[12.5px] text-black/55 dark:text-white/55 max-w-2xl leading-relaxed">
                    Sambut pengunjung website dengan penawaran menarik, voucher diskon pembuka, info event, atau tombol chat WhatsApp otomatis begitu halaman dibuka.
                </p>
            </div>

            <div class="flex items-center gap-2.5 w-full md:w-auto">
                <button type="button" @click="toggleSimulation()" 
                        class="h-9 px-3.5 rounded-[10px] text-[12.5px] font-medium text-black/75 dark:text-white/75 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-all flex items-center justify-center gap-1.5 active:scale-[0.98]">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                    <span x-text="simulatedModalOpen ? 'Tutup Preview' : 'Buka Preview'">Buka Preview</span>
                </button>
                <a href="{{ $business->public_url }}" target="_blank" rel="noopener"
                   class="h-9 px-3.5 rounded-[10px] text-[12.5px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] transition-all flex items-center justify-center gap-1.5 shadow-xs active:scale-[0.98]">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span>Cek Website Publik</span>
                </a>
            </div>
        </header>

        {{-- 2-COLUMN BENTO GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- ========================================================================= --}}
            {{-- LEFT COLUMN: CMS POP-UP SETTINGS FORM (7 Cols)                            --}}
            {{-- ========================================================================= --}}
            <div class="lg:col-span-7 space-y-6">
                <form id="popup-cms-form" 
                      action="{{ route('landing-page.popup.update') }}" 
                      method="POST" 
                      enctype="multipart/form-data" 
                      class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- CARD 1: SAKLAR AKTIVASI & FREKUENSI TAMPIL --}}
                    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-5 shadow-xs">
                        <div class="flex items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/5">
                            <div>
                                <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                                    <i data-lucide="toggle-right" class="w-4 h-4 text-[#007AFF]"></i>
                                    <span>Status &amp; Visibilitas Pop-Up</span>
                                </h3>
                                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">
                                    Kontrol apakah jendela modal promo akan muncul saat halaman beranda dibuka.
                                </p>
                            </div>

                            {{-- Apple HIG Toggle Switch --}}
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" 
                                       name="popup_enabled" 
                                       value="1" 
                                       class="sr-only peer" 
                                       x-model="enabled"
                                       {{ old('popup_enabled', $landingPage->popup_enabled) ? 'checked' : '' }}>
                                <div class="w-12 h-7 bg-black/15 dark:bg-white/20 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all after:shadow-sm peer-checked:bg-[#34C759]"></div>
                            </label>
                        </div>

                        {{-- Frekuensi Capping Radio Cards --}}
                        <div class="space-y-2.5">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-black/60 dark:text-white/60">
                                Frekuensi Tampil untuk Pengunjung
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                {{-- Option 1: 1x Per Hari (Recommended) --}}
                                <label class="relative flex flex-col p-3 rounded-[12px] border cursor-pointer transition-all select-none"
                                       :class="frequency === 'once_per_day' ? 'bg-[#007AFF]/5 border-[#007AFF] text-[#007AFF] dark:border-[#007AFF]' : 'border-black/10 dark:border-white/10 hover:border-black/20 dark:hover:border-white/20 text-black/70 dark:text-white/70'">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            <input type="radio" name="popup_frequency" value="once_per_day" class="text-[#007AFF] focus:ring-0" x-model="frequency">
                                            <span class="text-xs font-bold">1x Per Hari</span>
                                        </div>
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">Rekomendasi</span>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-1.5 leading-snug">
                                        Maksimal 1 kali dalam 24 jam. Ramah pengunjung tanpa mengganggu belanja.
                                    </p>
                                </label>

                                {{-- Option 2: 1x Per Sesi --}}
                                <label class="relative flex flex-col p-3 rounded-[12px] border cursor-pointer transition-all select-none"
                                       :class="frequency === 'once_per_session' ? 'bg-[#007AFF]/5 border-[#007AFF] text-[#007AFF] dark:border-[#007AFF]' : 'border-black/10 dark:border-white/10 hover:border-black/20 dark:hover:border-white/20 text-black/70 dark:text-white/70'">
                                    <div class="flex items-center gap-1.5">
                                        <input type="radio" name="popup_frequency" value="once_per_session" class="text-[#007AFF] focus:ring-0" x-model="frequency">
                                        <span class="text-xs font-bold">1x Per Sesi Tab</span>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-1.5 leading-snug">
                                        Tampil 1x selama tab terbuka. Muncul kembali saat tab baru dibuka.
                                    </p>
                                </label>

                                {{-- Option 3: Always --}}
                                <label class="relative flex flex-col p-3 rounded-[12px] border cursor-pointer transition-all select-none"
                                       :class="frequency === 'always' ? 'bg-[#007AFF]/5 border-[#007AFF] text-[#007AFF] dark:border-[#007AFF]' : 'border-black/10 dark:border-white/10 hover:border-black/20 dark:hover:border-white/20 text-black/70 dark:text-white/70'">
                                    <div class="flex items-center gap-1.5">
                                        <input type="radio" name="popup_frequency" value="always" class="text-[#007AFF] focus:ring-0" x-model="frequency">
                                        <span class="text-xs font-bold">Setiap Buka</span>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-1.5 leading-snug">
                                        Selalu muncul tiap refresh. Cocok untuk info darurat / flash sale mendesak.
                                    </p>
                                </label>
                            </div>
                        </div>

                        {{-- Jadwal Mulai & Selesai (Opsional) --}}
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <div class="flex items-center justify-between cursor-pointer" @click="showSchedule = !showSchedule">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="calendar" class="w-4 h-4 text-black/40 dark:text-white/40"></i>
                                    <span class="text-xs font-semibold text-black/80 dark:text-white/80">Jadwalkan Periode Tayang Otomatis (Opsional)</span>
                                </div>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-black/40 transition-transform" :class="showSchedule ? 'rotate-180' : ''"></i>
                            </div>

                            <div x-show="showSchedule" x-collapse class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                <div>
                                    <label class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">Mulai Tayang</label>
                                    <input type="datetime-local" 
                                           name="popup_starts_at" 
                                           x-model="startsAt"
                                           class="w-full h-9 px-3 text-xs rounded-[9px] border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white focus:border-[#007AFF] focus:ring-0">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">Selesai Tayang</label>
                                    <input type="datetime-local" 
                                           name="popup_ends_at" 
                                           x-model="endsAt"
                                           class="w-full h-9 px-3 text-xs rounded-[9px] border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white focus:border-[#007AFF] focus:ring-0">
                                </div>
                                <p class="text-[11px] text-black/40 dark:text-white/40 sm:col-span-2">
                                    Kosongkan tanggal jika pop-up ingin langsung tampil tanpa batas kadaluarsa.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- CARD 2: KONTEN TEKS & PESAN PROMOSI --}}
                    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-xs">
                        <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2 pb-2 border-b border-black/5 dark:border-white/5">
                            <i data-lucide="type" class="w-4 h-4 text-[#FF2D55]"></i>
                            <span>Pesan &amp; Teks Promosi</span>
                        </h3>

                        {{-- Badge & Label Promo --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Label / Badge Atas
                                </label>
                                <input type="text" 
                                       name="popup_badge" 
                                       x-model="badge"
                                       placeholder="PROMO SPESIAL"
                                       maxlength="50"
                                       class="w-full h-10 px-3 text-xs font-bold uppercase tracking-wider rounded-[10px] border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white focus:border-[#007AFF] focus:ring-0">
                                <div class="flex flex-wrap gap-1 mt-1.5">
                                    <button type="button" @click="badge = 'PROMO SPESIAL'" class="text-[10px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/5 hover:bg-black/10 text-black/60 dark:text-white/60">Promo</button>
                                    <button type="button" @click="badge = 'DISKON HARI INI'" class="text-[10px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/5 hover:bg-black/10 text-black/60 dark:text-white/60">Diskon</button>
                                    <button type="button" @click="badge = 'PENGUMUMAN'" class="text-[10px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/5 hover:bg-black/10 text-black/60 dark:text-white/60">Info</button>
                                </div>
                            </div>

                            {{-- Judul Utama --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Judul Utama Pop-Up <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" 
                                       name="popup_title" 
                                       x-model="title"
                                       placeholder="Contoh: Diskon 20% Khusus Hari Ini!"
                                       maxlength="255"
                                       class="w-full h-10 px-3 text-sm font-semibold rounded-[10px] border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white focus:border-[#007AFF] focus:ring-0">
                                <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Judul yang singkat, jelas, dan memikat perhatian pengunjung.</p>
                            </div>
                        </div>

                        {{-- Deskripsi / Isi Promo --}}
                        <div>
                            <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                Deskripsi / Syarat Penawaran
                            </label>
                            <textarea name="popup_content" 
                                      x-model="content"
                                      rows="3" 
                                      placeholder="Tuliskan detail penawaran, kode voucher promo, jadwal buka layanan, atau ajakan langsung menghubungi kontak kami..."
                                      maxlength="3000"
                                      class="w-full p-3 text-xs sm:text-sm rounded-[10px] border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white focus:border-[#007AFF] focus:ring-0 leading-relaxed"></textarea>
                        </div>
                    </div>

                    {{-- CARD 3: BANNER / GAMBAR POP-UP --}}
                    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-xs">
                        <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2 pb-2 border-b border-black/5 dark:border-white/5">
                            <i data-lucide="image" class="w-4 h-4 text-[#5856D6]"></i>
                            <span>Banner Visual / Foto Promosi</span>
                        </h3>

                        {{-- Image Preview & Delete Area --}}
                        <div x-show="imageUrl" class="relative rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 bg-black/[0.02] max-w-sm">
                            <img :src="imageUrl" alt="Preview Banner" class="w-full h-36 object-cover">
                            <div class="p-2.5 bg-white/95 dark:bg-[#2C2C2E]/95 flex items-center justify-between border-t border-black/5 dark:border-white/5">
                                <span class="text-[11px] text-black/60 dark:text-white/60 truncate max-w-[200px]" x-text="imageUrl"></span>
                                <label class="inline-flex items-center gap-1.5 text-xs text-[#FF3B30] font-medium cursor-pointer hover:underline">
                                    <input type="checkbox" name="remove_popup_image" value="1" @change="if($el.checked) { imageUrl = ''; }" class="rounded text-[#FF3B30] focus:ring-0">
                                    <span>Hapus Banner</span>
                                </label>
                            </div>
                        </div>

                        {{-- Upload File or Input URL Tabs --}}
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Upload File Foto / Desain Banner
                                </label>
                                <input type="file" 
                                       name="popup_image" 
                                       accept="image/jpeg,image/png,image/webp" 
                                       @change="handleFileUpload($event)"
                                       class="block w-full text-xs text-black/60 dark:text-white/60 file:mr-3 file:py-2 file:px-3 file:rounded-[8px] file:border-0 file:text-xs file:font-semibold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/20 cursor-pointer">
                                <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Format: JPG, PNG, atau WebP. Maksimal 4 MB. Rasio ideal: 16:9 atau 4:3 horizontal.</p>
                            </div>

                            <div class="relative flex py-1 items-center">
                                <div class="flex-grow border-t border-black/5 dark:border-white/5"></div>
                                <span class="flex-shrink mx-3 text-[10px] uppercase font-bold text-black/30 dark:text-white/30">atau tautan eksternal</span>
                                <div class="flex-grow border-t border-black/5 dark:border-white/5"></div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    URL Gambar Eksternal
                                </label>
                                <input type="url" 
                                       name="popup_image_url" 
                                       x-model="imageUrl"
                                       placeholder="https://images.unsplash.com/photo-..."
                                       class="w-full h-9 px-3 text-xs rounded-[9px] border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white focus:border-[#007AFF] focus:ring-0">
                            </div>
                        </div>
                    </div>

                    {{-- CARD 4: TOMBOL AKSI (CALL TO ACTION) --}}
                    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-xs">
                        <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2 pb-2 border-b border-black/5 dark:border-white/5">
                            <i data-lucide="mouse-pointer-click" class="w-4 h-4 text-[#34C759]"></i>
                            <span>Tombol Aksi Utama (Call To Action)</span>
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Teks Tombol
                                </label>
                                <input type="text" 
                                       name="popup_cta_text" 
                                       x-model="ctaText"
                                       placeholder="Contoh: Klaim Promo Sekarang"
                                       maxlength="100"
                                       class="w-full h-10 px-3 text-xs sm:text-sm font-semibold rounded-[10px] border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white focus:border-[#007AFF] focus:ring-0">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                    Tautan / URL Tujuan
                                </label>
                                <input type="text" 
                                       name="popup_cta_url" 
                                       x-model="ctaUrl"
                                       placeholder="https://wa.me/... atau /katalog"
                                       maxlength="500"
                                       class="w-full h-10 px-3 text-xs sm:text-sm rounded-[10px] border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black dark:text-white focus:border-[#007AFF] focus:ring-0">
                            </div>
                        </div>

                        {{-- Shortcut Presets --}}
                        <div class="space-y-1.5 pt-1">
                            <span class="text-[11px] font-medium text-black/50 dark:text-white/50">Isi Cepat Tautan Aksi:</span>
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" @click="setWhatsappPreset()" 
                                        class="h-7 px-2.5 rounded-[7px] text-[11px] font-medium bg-[#34C759]/10 hover:bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] flex items-center gap-1 transition">
                                    <i data-lucide="message-circle" class="w-3 h-3"></i>
                                    <span>Chat WhatsApp CS</span>
                                </button>
                                <button type="button" @click="ctaText = 'Jelajahi Katalog'; ctaUrl = '/' + businessSlug + '/katalog'"
                                        class="h-7 px-2.5 rounded-[7px] text-[11px] font-medium bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] flex items-center gap-1 transition">
                                    <i data-lucide="shopping-bag" class="w-3 h-3"></i>
                                    <span>Katalog Produk</span>
                                </button>
                                <button type="button" @click="ctaText = 'Reservasi Meja'; ctaUrl = '/' + businessSlug + '/reservasi'"
                                        class="h-7 px-2.5 rounded-[7px] text-[11px] font-medium bg-[#FF9500]/10 hover:bg-[#FF9500]/20 text-[#FF9500] flex items-center gap-1 transition">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                    <span>Halaman Reservasi</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- SUBMIT BAR --}}
                    <div class="pt-2 flex items-center justify-between gap-3">
                        <div class="text-xs text-black/50 dark:text-white/50">
                            Perubahan akan langsung aktif di beranda storefront publik.
                        </div>

                        <button type="submit" 
                                class="h-11 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white font-semibold text-[13.5px] transition shadow-md flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Simpan Pengaturan Pop-Up</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- ========================================================================= --}}
            {{-- RIGHT COLUMN: LIVE SMARTPHONE MOCKUP PREVIEW (5 Cols)                     --}}
            {{-- ========================================================================= --}}
            <div class="lg:col-span-5 lg:sticky lg:top-6 space-y-4">
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <i data-lucide="smartphone" class="w-4 h-4 text-[#007AFF]"></i>
                        <span class="text-xs font-bold uppercase tracking-wider text-black/70 dark:text-white/70">Live Mobile Preview</span>
                    </div>
                    <span class="text-[11px] text-black/45 dark:text-white/45">Real-time simulator</span>
                </div>

                {{-- Modern Smartphone Chassis (iPhone 16 Style) --}}
                <div class="relative mx-auto w-full max-w-[340px] rounded-[44px] bg-[#1a1a1c] p-3 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.35)] border-4 border-[#3a3a3c]">
                    
                    {{-- Outer Screen Frame --}}
                    <div class="relative h-[620px] w-full overflow-hidden rounded-[34px] bg-neutral-100 dark:bg-neutral-900 border border-black/20 flex flex-col justify-between">
                        
                        {{-- Phone Status Bar & Dynamic Island --}}
                        <div class="absolute top-0 inset-x-0 z-40 pt-2 px-6 flex items-center justify-between text-[11px] font-semibold text-black/70 dark:text-white/70 pointer-events-none">
                            <span>09:41</span>
                            {{-- Dynamic Island Pill --}}
                            <div class="w-24 h-4.5 bg-black rounded-full flex items-center justify-end px-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500/80"></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i data-lucide="wifi" class="w-3 h-3"></i>
                                <i data-lucide="battery" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>

                        {{-- Mockup Storefront Background --}}
                        <div class="pt-12 px-3 pb-6 space-y-4 filter" :class="simulatedModalOpen ? 'blur-[2px] brightness-75 transition-all duration-300' : 'transition-all duration-300'">
                            {{-- Storefront Mini Header --}}
                            <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center font-bold text-xs">
                                        <i data-lucide="store" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs font-bold text-black dark:text-white truncate max-w-[140px]" x-text="businessName"></span>
                                </div>
                                <i data-lucide="menu" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                            </div>

                            {{-- Storefront Mini Hero Banner --}}
                            <div class="rounded-2xl p-4 bg-gradient-to-br from-[#007AFF]/10 to-[#5856D6]/10 border border-black/5 dark:border-white/5 space-y-2">
                                <div class="w-16 h-4 rounded-full bg-[#007AFF]/20"></div>
                                <div class="w-3/4 h-5 rounded-md bg-black/20 dark:bg-white/20"></div>
                                <div class="w-full h-3 rounded-md bg-black/10 dark:bg-white/10"></div>
                                <div class="w-20 h-6 rounded-lg bg-[#007AFF] mt-2"></div>
                            </div>

                            {{-- Storefront Mini Catalog Grid --}}
                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <div class="rounded-xl p-2 bg-white dark:bg-neutral-800 border border-black/5 space-y-1.5">
                                    <div class="aspect-square rounded-lg bg-black/5 dark:bg-white/5"></div>
                                    <div class="w-3/4 h-2.5 rounded bg-black/15 dark:bg-white/15"></div>
                                    <div class="w-1/2 h-2 rounded bg-black/10 dark:bg-white/10"></div>
                                </div>
                                <div class="rounded-xl p-2 bg-white dark:bg-neutral-800 border border-black/5 space-y-1.5">
                                    <div class="aspect-square rounded-lg bg-black/5 dark:bg-white/5"></div>
                                    <div class="w-3/4 h-2.5 rounded bg-black/15 dark:bg-white/15"></div>
                                    <div class="w-1/2 h-2 rounded bg-black/10 dark:bg-white/10"></div>
                                </div>
                            </div>
                        </div>

                        {{-- SIMULATED POP-UP MODAL OVERLAY --}}
                        <div x-show="simulatedModalOpen" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute inset-0 z-50 flex items-center justify-center p-3.5 bg-black/50 backdrop-blur-[2px]">

                            {{-- The Pop-up Card --}}
                            <div class="w-full max-w-[290px] rounded-[22px] bg-white dark:bg-[#1E1E20] border border-black/10 dark:border-white/15 shadow-2xl overflow-hidden relative animate-in fade-in zoom-in-95 duration-200 flex flex-col">
                                
                                {{-- Close 'X' Button --}}
                                <button type="button" 
                                        @click="simulatedModalOpen = false" 
                                        class="absolute top-2.5 right-2.5 z-20 w-7 h-7 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center backdrop-blur-md transition active:scale-90">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                </button>

                                {{-- Image Banner inside Modal --}}
                                <template x-if="imageUrl">
                                    <div class="relative w-full aspect-[16/9] bg-neutral-100 dark:bg-neutral-800 overflow-hidden">
                                        <img :src="imageUrl" alt="Banner" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                                    </div>
                                </template>

                                {{-- Pop-up Body Content --}}
                                <div class="p-4 text-center space-y-2.5">
                                    {{-- Badge --}}
                                    <div x-show="badge" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-[#FF2D55]/15 text-[#FF2D55] dark:text-[#FF375F]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        <span x-text="badge"></span>
                                    </div>

                                    {{-- Title --}}
                                    <h4 class="font-bold text-sm text-neutral-900 dark:text-white leading-tight" 
                                        x-text="title || 'Judul Promo Toko Online'"></h4>

                                    {{-- Content / Description --}}
                                    <p class="text-[11px] text-neutral-600 dark:text-neutral-300 leading-relaxed max-h-24 overflow-y-auto" 
                                       x-text="content || 'Dapatkan potongan harga istimewa khusus hari ini untuk produk terlaris kami!'"></p>

                                    {{-- Action Buttons --}}
                                    <div class="pt-1.5 space-y-1.5">
                                        <button type="button" 
                                                class="w-full py-2 px-3 rounded-[10px] bg-[#007AFF] text-white text-xs font-bold shadow-xs hover:bg-[#0071E3] transition active:scale-95 flex items-center justify-center gap-1.5">
                                            <span x-text="ctaText || 'Lihat Penawaran'"></span>
                                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                        </button>

                                        <button type="button" 
                                                @click="simulatedModalOpen = false" 
                                                class="text-[10px] text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 font-medium">
                                            Nanti Saja
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Phone Home Indicator Bar --}}
                        <div class="absolute bottom-1.5 inset-x-0 z-40 flex justify-center pointer-events-none">
                            <div class="w-28 h-1 rounded-full bg-black/40 dark:bg-white/40"></div>
                        </div>
                    </div>
                </div>

                {{-- Interactive controls for preview --}}
                <div class="flex items-center justify-center gap-2 pt-1">
                    <button type="button" 
                            @click="simulatedModalOpen = true; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                            class="text-xs font-semibold text-[#007AFF] hover:underline inline-flex items-center gap-1">
                        <i data-lucide="play" class="w-3 h-3"></i>
                        <span>Uji Buka Pop-Up Ulang</span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- Alpine.js State Controller Script --}}
    <script>
        function storefrontPopupEditor(initial) {
            return {
                enabled: initial.enabled,
                frequency: initial.frequency,
                title: initial.title,
                badge: initial.badge,
                content: initial.content,
                ctaText: initial.ctaText,
                ctaUrl: initial.ctaUrl,
                imageUrl: initial.imageUrl,
                startsAt: initial.startsAt,
                endsAt: initial.endsAt,
                businessSlug: initial.businessSlug,
                businessPhone: initial.businessPhone,
                businessName: initial.businessName,

                showSchedule: Boolean(initial.startsAt || initial.endsAt),
                simulatedModalOpen: true,

                init() {
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                },

                toggleSimulation() {
                    this.simulatedModalOpen = !this.simulatedModalOpen;
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                },

                handleFileUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imageUrl = e.target.result;
                        this.simulatedModalOpen = true;
                        this.$nextTick(() => {
                            if (window.lucide) {
                                window.lucide.createIcons();
                            }
                        });
                    };
                    reader.readAsDataURL(file);
                },

                setWhatsappPreset() {
                    this.ctaText = 'Chat WhatsApp CS';
                    let phone = (this.businessPhone || '').replace(/[^0-9]/g, '');
                    if (phone.startsWith('0')) {
                        phone = '62' + phone.substring(1);
                    }
                    const text = encodeURIComponent('Halo ' + this.businessName + ', saya ingin bertanya seputar promo pop-up website.');
                    this.ctaUrl = phone ? ('https://wa.me/' + phone + '?text=' + text) : 'https://wa.me/';
                }
            };
        }
    </script>
@endsection
