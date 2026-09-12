@extends('layouts.app', [
    'title' => 'Website & Landing Page Bisnis — Cooca UMKM',
    'headerTitle' => 'Website & Landing Page Bisnis',
    'headerSubtitle' => 'Kelola profil digital, katalog produk & layanan, galeri, serta kontak online ' . $business->name,
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-24" x-data="landingPageEditor()">

        <!-- ========================================== -->
        <!-- 0. BREADCRUMB BAR (macOS Minimalist Style) -->
        <!-- ========================================== -->
        <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 print:hidden" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
            <span>›</span>
            <span class="text-black/70 dark:text-white/70 font-medium">Pemasaran &amp; Profil</span>
            <span>›</span>
            <span class="text-black dark:text-white font-medium">Website &amp; Landing Page Bisnis</span>
        </nav>

        <!-- ========================================== -->
        <!-- 1. TOP TOOLBAR & ACTION COCKPIT            -->
        <!-- ========================================== -->
        <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 transition-colors">
            <div class="space-y-1.5 max-w-3xl">
                <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">
                    Website &amp; Profil Digital: {{ $business->name }}
                </h1>
                <p class="text-[13px] text-black/50 dark:text-white/50 leading-relaxed">
                    Kelola halaman landing page profesional, katalog layanan, galeri foto suasana usaha, kontak WhatsApp 1-klik, dan optimasi SEO Google.
                </p>

                <!-- Public URL bar capsule -->
                <div class="pt-1 flex flex-wrap items-center gap-2 text-[12px]">
                    <span class="text-black/50 dark:text-white/50 font-medium">Tautan Publik:</span>
                    <a href="{{ $publicUrl }}" target="_blank"
                        class="font-mono text-[#007AFF] hover:underline font-semibold inline-flex items-center gap-1">
                        <span>{{ $publicUrl }}</span>
                        <svg class="w-3 h-3 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                    </a>
                    <span class="text-[11px] text-black/40 dark:text-white/40">URL mengikuti nama bisnis</span>
                    <button type="button" @click="copyLink('{{ $publicUrl }}')"
                        class="h-6 px-2 rounded-[6px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 font-medium active:scale-[0.97] transition inline-flex items-center gap-1 text-[11px]">
                        <svg class="w-3 h-3 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                        </svg>
                        <span x-text="copied ? 'Tersalin!' : 'Salin'">Salin</span>
                    </button>
                    <a href="https://api.whatsapp.com/send?text={{ urlencode('Halo, kunjungi profil dan katalog layanan kami di: ' . $publicUrl) }}"
                        target="_blank"
                        class="h-6 px-2 rounded-[6px] bg-[#34C759]/12 hover:bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] font-medium active:scale-[0.97] transition inline-flex items-center gap-1 text-[11px]">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                        </svg>
                        <span>Share ke WA</span>
                    </a>
                </div>
            </div>

            <!-- Toolbar Actions -->
            <div class="flex flex-wrap items-center gap-2 z-10 w-full lg:w-auto">
                <button type="button" @click="showPresetModal = true"
                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-[#B25E00] dark:text-[#FF9F0A] bg-[#FF9500]/10 hover:bg-[#FF9500]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                    <svg class="w-4 h-4 text-[#FF9500] dark:text-[#FF9F0A]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                    </svg>
                    <span>Template Industri (20)</span>
                </button>

                <button type="button" @click="togglePublish()" :disabled="saving"
                    :class="isPublished
                        ? 'text-[#C41E17] dark:text-[#FF453A] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15'
                        : 'text-[#248A3D] dark:text-[#30D158] bg-[#34C759]/10 hover:bg-[#34C759]/15'"
                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-semibold active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 flex-1 sm:flex-none disabled:opacity-50">
                    <svg x-show="!isPublished" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg x-show="isPublished" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                    <span x-text="isPublished ? 'Arsipkan Publik' : 'Terbitkan Sekarang'"></span>
                </button>

                <button type="button" @click="openPreview()" :disabled="saving"
                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                    <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                    </svg>
                    <span x-text="saving ? 'Menyiapkan...' : 'Preview Live'">Preview Live</span>
                </button>

                <a href="{{ $publicUrl }}" target="_blank"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] flex-1 sm:flex-none">
                    <span>Buka Website</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>
            </div>
        </header>

        <!-- ========================================== -->
        <!-- 2. 4 COMMAND PILLARS (Flat Neutral KPI)    -->
        <!-- ========================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">

            <!-- Pillar 1: Status Publikasi -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <div>
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Status Website</span>
                    <div class="mt-2 text-[20px] sm:text-[22px] font-bold tabular-nums tracking-tight truncate"
                        :class="isPublished ? 'text-[#34C759] dark:text-[#30D158]' : 'text-black/50 dark:text-white/50'"
                        x-text="isPublished ? 'Live Publik' : 'Draft Offline'">
                        {{ $landingPage->is_published ? 'Live Publik' : 'Draft Offline' }}
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50 flex items-center justify-between">
                    <span>Visibilitas</span>
                    <span class="font-medium text-black dark:text-white"
                        x-text="isPublished ? 'Terbuka Umum' : 'Hanya Pemilik'">
                        {{ $landingPage->is_published ? 'Terbuka Umum' : 'Hanya Pemilik' }}
                    </span>
                </div>
            </div>

            <!-- Pillar 2: Layanan & Menu -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <div>
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Katalog Layanan &amp; Menu</span>
                    <div class="mt-2 text-[20px] sm:text-[22px] font-bold tabular-nums text-black dark:text-white tracking-tight truncate">
                        <span x-text="services.length">0</span> Layanan
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50 flex items-center justify-between">
                    <span>Sinkron POS</span>
                    <span class="font-medium text-[#34C759] dark:text-[#30D158]">
                        {{ $landingPage->show_pos_products ? $posProducts->count() . ' Terhubung' : 'Non-Aktif' }}
                    </span>
                </div>
            </div>

            <!-- Pillar 3: Kredibilitas & Keunggulan -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <div>
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Pilar Nilai &amp; Kredibilitas</span>
                    <div class="mt-2 text-[20px] sm:text-[22px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6] tracking-tight truncate">
                        <span x-text="values.length">4</span> Pilar Nilai
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50 flex items-center justify-between">
                    <span>Ulasan &amp; FAQ</span>
                    <span class="font-medium text-black dark:text-white">
                        <span x-text="testimonials.length">0</span> ulasan · <span x-text="faqs.length">0</span> FAQ
                    </span>
                </div>
            </div>

            <!-- Pillar 4: Galeri Bisnis -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
                <div>
                    <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Galeri Foto Bisnis</span>
                    <div class="mt-2 text-[20px] sm:text-[22px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A] tracking-tight truncate">
                        <span x-text="galleryItems.length + newGalleryUploads.length">0</span> / 20 Foto
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 text-[11px] text-black/50 dark:text-white/50 flex items-center justify-between">
                    <span>Status Galeri</span>
                    <span class="font-medium"
                        :class="sectionVisibility.gallery ? 'text-[#34C759] dark:text-[#30D158]' : 'text-black/40 dark:text-white/40'">
                        <span x-text="sectionVisibility.gallery ? 'Aktif Tampil' : 'Disembunyikan'"></span>
                    </span>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- 3. MAIN FORM & STUDIO WORKSPACE (ARKETIPE B) -->
        <!-- ========================================== -->
        <form action="{{ route('landing-page.update') }}" method="POST" id="landingPageForm"
            enctype="multipart/form-data" @submit.prevent="saveAll">
            @csrf
            @method('PUT')

            <!-- Hidden JSON & Preset inputs synced via Alpine -->
            <input type="hidden" name="values_json" :value="JSON.stringify(values)">
            <input type="hidden" name="custom_services_json" :value="JSON.stringify(services)">
            <input type="hidden" name="faqs_json" :value="JSON.stringify(faqs)">
            <input type="hidden" name="testimonials_json" :value="JSON.stringify(testimonials)">
            <input type="hidden" name="operational_hours_json" :value="JSON.stringify(operationalHours)">
            <input type="hidden" name="gallery_images_json" :value="JSON.stringify(galleryItems)">
            <input type="hidden" name="section_visibility_json" :value="JSON.stringify(sectionVisibility)">
            <input type="hidden" name="industry_preset" :value="form.industry_preset">
            <input type="hidden" name="theme_color" :value="form.theme_color">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 md:gap-6 items-start">

                <!-- SIDEBAR TABS NAVIGATION (3 KOLOM DESKTOP — macOS Source List) -->
                <div class="lg:col-span-3 space-y-3">
                    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 lg:sticky lg:top-20 transition-colors">

                        <div class="px-2.5 pb-2 mb-1.5 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">Bagian Studio</span>
                            <span class="text-[11px] font-mono font-semibold text-[#007AFF]">11 Bagian</span>
                        </div>

                        <!-- Horizontal scroll on mobile / vertical list on desktop -->
                        <div class="flex lg:block gap-1 overflow-x-auto pb-1.5 lg:pb-0 snap-x snap-mandatory">
                            <template x-for="tab in tabs" :key="tab.id">
                                <button type="button" @click="activeTab = tab.id"
                                    class="w-auto lg:w-full shrink-0 snap-start flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium transition-colors text-left whitespace-nowrap mb-0.5 cursor-pointer"
                                    :class="activeTab === tab.id ?
                                        'bg-[#007AFF] text-white font-semibold shadow-[0_1px_2px_rgba(0,122,255,0.25)]' :
                                        'text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5'">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="truncate" x-text="tab.label"></span>
                                    </div>
                                    <span x-show="activeTab === tab.id" class="text-white/70 hidden lg:inline text-[11px]">›</span>
                                </button>
                            </template>
                        </div>

                        <!-- Quick Direct Save in Sidebar -->
                        <div class="pt-3 mt-2 border-t border-black/5 dark:border-white/5 space-y-2">
                            <button type="submit" :disabled="saving"
                                class="w-full h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)] disabled:opacity-50 cursor-pointer">
                                <svg x-show="saving" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="saving ? 'Menyimpan...' : 'Simpan Perubahan'">Simpan Perubahan</span>
                            </button>
                            <p class="text-[11px] text-center truncate"
                                :class="saveError ? 'text-[#FF3B30] font-medium' : 'text-black/50 dark:text-white/50'"
                                x-text="saveError || saveMessage || 'Perubahan tersimpan otomatis'"></p>
                        </div>
                    </div>
                </div>

                <!-- STUDIO CONTENT AREA (9 KOLOM DESKTOP) -->
                <div class="lg:col-span-9 space-y-5">

                    <!-- ============================================================ -->
                    <!-- TAB 1: GENERAL / IDENTITAS & WARNA TEMA                       -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'general'" x-cloak class="space-y-5">
                        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">
                                        1. Identitas Visual &amp; Warna Tema
                                    </h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Tentukan warna dominan dan karakter visual yang merepresentasikan identitas brand bisnis Anda.</p>
                                </div>
                                <span class="text-[11px] font-medium text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] px-2.5 py-1 rounded-full">
                                    Preset: <strong class="text-[#007AFF] uppercase font-mono" x-text="form.industry_preset"></strong>
                                </span>
                            </div>

                            <!-- Theme Color Picker -->
                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-2 uppercase tracking-wider">
                                    Palet Warna Aksen Utama
                                </label>
                                <div class="flex flex-wrap items-center gap-2">
                                    <template x-for="color in themePresets" :key="color.hex">
                                        <button type="button" @click="form.theme_color = color.hex"
                                            :style="`background-color: ${color.hex}`"
                                            :class="form.theme_color === color.hex ?
                                                'ring-2 ring-[#007AFF] ring-offset-2 ring-offset-white dark:ring-offset-[#1C1C1E] scale-110' :
                                                'opacity-80 hover:opacity-100 hover:scale-105'"
                                            class="w-8 h-8 rounded-full transition-all flex items-center justify-center text-white shadow-xs cursor-pointer"
                                            :title="color.label">
                                            <svg x-show="form.theme_color === color.hex" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        </button>
                                    </template>
                                    <div class="flex items-center gap-2 ml-2 px-3 py-1.5 bg-black/[0.04] dark:bg-white/[0.06] rounded-[10px]">
                                        <input type="color" x-model="form.theme_color"
                                            class="w-6 h-6 rounded-md cursor-pointer bg-transparent border-0 p-0"
                                            title="Pilih warna kustom">
                                        <code class="text-[12px] text-[#007AFF] font-mono font-semibold uppercase"
                                            x-text="form.theme_color"></code>
                                    </div>
                                </div>
                                <p class="text-[11px] text-black/50 dark:text-white/50 mt-2">
                                    Warna aksen ini akan diterapkan pada tombol utama, badge produk, serta elemen sorotan pada website publik.
                                </p>
                            </div>

                            <!-- Dark Mode Toggle for Landing Page -->
                            <div class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
                                <div>
                                    <div class="text-[14px] font-semibold text-black dark:text-white">Tampilan Elegan Gelap (Dark Mode Publik)</div>
                                    <div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Aktifkan untuk memberikan nuansa mewah bertema gelap pada landing page publik bisnis Anda.</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="dark_mode" value="1"
                                        {{ $landingPage->dark_mode ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#007AFF]">
                                    </div>
                                </label>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 2: HERO / BANNER UTAMA                                   -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'hero'" x-cloak class="space-y-5">
                        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">
                                        2. Banner Utama (Hero Section)
                                    </h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Bagian teratas yang pertama kali dilihat calon pelanggan saat membuka website.</p>
                                </div>
                                <label class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                    <input type="checkbox" x-model="sectionVisibility.hero"
                                        class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span>Tampilkan Section Hero</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Badge Pengumuman -->
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        Badge Pengumuman Singkat
                                    </label>
                                    <input type="text" name="announcement_badge" x-model="form.announcement_badge"
                                        placeholder="🔥 Solusi Terpercaya Sejak 2018"
                                        class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Muncul di atas judul besar sebagai pemikat perhatian pertama.</p>
                                </div>

                                <!-- Upload Logo -->
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        Upload Logo Bisnis
                                    </label>
                                    <div x-show="previews.logo || form.logo_url" class="mb-2 flex items-center gap-3">
                                        <img :src="previews.logo || form.logo_url" alt="Logo Preview"
                                            class="w-12 h-12 rounded-[10px] object-contain bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 p-1">
                                        <span class="text-[11px] text-[#007AFF] font-medium"
                                            x-text="previews.logo ? 'Pratinjau logo baru dipilih' : 'Logo tersimpan aktif'"></span>
                                    </div>
                                    <input type="file" name="logo_image" accept="image/jpeg,image/png,image/webp"
                                        @change="handleImagePreview($event, 'logo')"
                                        class="w-full text-[12px] text-black/50 dark:text-white/50 file:mr-3 file:rounded-[8px] file:border-0 file:bg-[#007AFF]/10 file:px-3 file:py-1.5 file:text-[#007AFF] file:font-semibold file:cursor-pointer">
                                    <label class="mt-2 flex items-center gap-2 text-[11px] text-black/50 dark:text-white/50">
                                        <input type="checkbox" name="remove_logo_image" value="1"
                                            class="rounded-[4px] border-black/20 text-[#FF3B30] focus:ring-[#FF3B30]">
                                        <span>Hapus logo tersimpan</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Headline Utama -->
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Headline Utama (Judul Besar) <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="headline" x-model="form.headline"
                                    placeholder="Solusi Layanan &amp; Produk Terpercaya untuk Kebutuhan Anda"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] font-semibold text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <!-- Subheadline -->
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Subheadline (Deskripsi Lengkap Hero)
                                </label>
                                <textarea name="subheadline" x-model="form.subheadline" rows="3"
                                    placeholder="Kami hadir memberikan solusi terbaik dengan pengerjaan profesional, garansi kepuasan, dan harga terjangkau..."
                                    class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 py-2.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                            </div>

                            <!-- Call-to-Action Buttons -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        Teks Tombol Primer (CTA)
                                    </label>
                                    <input type="text" name="cta_primary_text" x-model="form.cta_primary_text"
                                        placeholder="Pesan via WhatsApp"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        URL Tujuan Tombol Primer
                                    </label>
                                    <input type="text" name="cta_primary_url" x-model="form.cta_primary_url"
                                        placeholder="https://wa.me/... atau #kontak"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition font-mono">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        Teks Tombol Sekunder
                                    </label>
                                    <input type="text" name="cta_secondary_text" x-model="form.cta_secondary_text"
                                        placeholder="Lihat Daftar Layanan &amp; Harga"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        URL Tujuan Tombol Sekunder
                                    </label>
                                    <input type="text" name="cta_secondary_url" x-model="form.cta_secondary_url"
                                        placeholder="#layanan atau URL tujuan"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition font-mono">
                                </div>
                            </div>

                            <!-- Upload Hero Image -->
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Upload Foto Banner Hero (Opsional)
                                </label>
                                <div x-show="previews.hero || form.hero_image_url" class="mb-2 flex items-center gap-3">
                                    <img :src="previews.hero || form.hero_image_url" alt="Hero Preview"
                                        class="w-32 h-20 rounded-[10px] object-cover border border-black/5 dark:border-white/10">
                                    <span class="text-[11px] text-[#007AFF] font-medium"
                                        x-text="previews.hero ? 'Pratinjau foto hero baru dipilih' : 'Foto hero aktif tersimpan'"></span>
                                </div>
                                <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp"
                                    @change="handleImagePreview($event, 'hero')"
                                    class="w-full text-[12px] text-black/50 dark:text-white/50 file:mr-3 file:rounded-[8px] file:border-0 file:bg-[#007AFF]/10 file:px-3 file:py-1.5 file:text-[#007AFF] file:font-semibold file:cursor-pointer">
                                <label class="mt-2 flex items-center gap-2 text-[11px] text-black/50 dark:text-white/50">
                                    <input type="checkbox" name="remove_hero_image" value="1"
                                        class="rounded-[4px] border-black/20 text-[#FF3B30] focus:ring-[#FF3B30]">
                                    <span>Hapus foto hero tersimpan</span>
                                </label>
                                <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Maks. 4MB. Format JPG, PNG, atau WebP. Resolusi ideal 1200x800px.</p>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 3: ABOUT / PROFIL, KEUNGGULAN & JAM BUKA                 -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'about'" x-cloak class="space-y-5">

                        <!-- Profil Bisnis & Story Card -->
                        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">
                                        3. Profil, Cerita &amp; Kredibilitas Bisnis
                                    </h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Bangun kepercayaan pelanggan melalui transparansi sejarah, reputasi, dan standar kualitas.</p>
                                </div>
                                <label class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                    <input type="checkbox" x-model="sectionVisibility.about"
                                        class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span>Tampilkan Section Profil</span>
                                </label>
                            </div>

                            <!-- Gambar About -->
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Upload Foto Profil / Tempat Usaha (Opsional)
                                </label>
                                <div x-show="previews.about || form.about_image_url" class="mb-2 flex items-center gap-3">
                                    <img :src="previews.about || form.about_image_url" alt="About Preview"
                                        class="w-32 h-20 rounded-[10px] object-cover border border-black/5 dark:border-white/10">
                                    <span class="text-[11px] text-[#007AFF] font-medium"
                                        x-text="previews.about ? 'Pratinjau foto profil baru dipilih' : 'Foto profil aktif tersimpan'"></span>
                                </div>
                                <input type="file" name="about_image" accept="image/jpeg,image/png,image/webp"
                                    @change="handleImagePreview($event, 'about')"
                                    class="w-full text-[12px] text-black/50 dark:text-white/50 file:mr-3 file:rounded-[8px] file:border-0 file:bg-[#007AFF]/10 file:px-3 file:py-1.5 file:text-[#007AFF] file:font-semibold file:cursor-pointer">
                                <label class="mt-2 flex items-center gap-2 text-[11px] text-black/50 dark:text-white/50">
                                    <input type="checkbox" name="remove_about_image" value="1"
                                        class="rounded-[4px] border-black/20 text-[#FF3B30] focus:ring-[#FF3B30]">
                                    <span>Hapus foto profil tersimpan</span>
                                </label>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Judul Bagian Profil / About
                                </label>
                                <input type="text" x-model="form.about_title"
                                    placeholder="Dedikasi Kami untuk Kualitas &amp; Kepuasan Pelanggan"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Cerita / Sejarah / Filosofi Usaha
                                </label>
                                <textarea x-model="form.about_story" rows="4"
                                    placeholder="Ceritakan bagaimana usaha Anda beroperasi, komitmen kualitas, keahlian tim, serta garansi yang diberikan..."
                                    class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 py-2.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                            </div>

                            <!-- 3 STATS KREDIBILITAS -->
                            <div class="border-t border-black/5 dark:border-white/5 pt-4">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-3">
                                    3 Angka Metrik Kredibilitas Usaha
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3 space-y-2">
                                        <label class="text-[10px] text-black/50 dark:text-white/50 uppercase font-semibold">Metrik 1 (Pelanggan)</label>
                                        <input type="text" name="stats[clients]"
                                            value="{{ data_get($landingPage->values, 'stats.clients', '5.000+') }}"
                                            placeholder="5.000+"
                                            class="w-full h-9 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-3 text-[15px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] focus:outline-none text-center">
                                        <input type="text" name="stats[clients_label]" value="Pelanggan Puas"
                                            placeholder="Label Pelanggan"
                                            class="w-full h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[12px] text-black/70 dark:text-white/70 focus:outline-none text-center">
                                    </div>
                                    <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3 space-y-2">
                                        <label class="text-[10px] text-black/50 dark:text-white/50 uppercase font-semibold">Metrik 2 (Pengalaman)</label>
                                        <input type="text" name="stats[experience]" value="8+ Tahun"
                                            placeholder="8+ Tahun"
                                            class="w-full h-9 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-3 text-[15px] font-bold tabular-nums text-[#007AFF] focus:outline-none text-center">
                                        <input type="text" name="stats[experience_label]"
                                            value="Pengalaman Profesional" placeholder="Label Pengalaman"
                                            class="w-full h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[12px] text-black/70 dark:text-white/70 focus:outline-none text-center">
                                    </div>
                                    <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3 space-y-2">
                                        <label class="text-[10px] text-black/50 dark:text-white/50 uppercase font-semibold">Metrik 3 (Review / Rating)</label>
                                        <input type="text" name="stats[rating]" value="4.9 / 5.0"
                                            placeholder="4.9 / 5.0"
                                            class="w-full h-9 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-3 text-[15px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A] focus:outline-none text-center">
                                        <input type="text" name="stats[rating_label]" value="Rating Kepuasan Ulasan"
                                            placeholder="Label Rating"
                                            class="w-full h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[12px] text-black/70 dark:text-white/70 focus:outline-none text-center">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- 4 VALUE PILLARS CARD -->
                        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 transition-colors">
                            <div class="border-b border-black/5 dark:border-white/5 pb-3">
                                <h3 class="text-[16px] font-semibold text-black dark:text-white">
                                    4 Pilar Keunggulan Utama Bisnis
                                </h3>
                                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Alasan kuat mengapa calon pelanggan harus memilih produk atau layanan Anda dibanding kompetitor.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <template x-for="(val, index) in values" :key="index">
                                    <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 space-y-2.5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold text-[11px] shrink-0 tabular-nums"
                                                x-text="index + 1"></div>
                                            <input type="text" x-model="val.title"
                                                placeholder="Judul Pilar Keunggulan"
                                                class="flex-1 h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[12px] font-semibold text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                            <select x-model="val.icon"
                                                class="h-8 px-2 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] text-[12px] text-[#007AFF] font-medium focus:outline-none">
                                                <option value="shield-check">🛡️ Shield</option>
                                                <option value="zap">⚡ Zap</option>
                                                <option value="award">🏆 Award</option>
                                                <option value="star">⭐ Star</option>
                                                <option value="clock">🕐 Clock</option>
                                                <option value="heart">❤️ Heart</option>
                                                <option value="truck">🚚 Truck</option>
                                                <option value="thumbs-up">👍 Thumbs Up</option>
                                                <option value="check-circle">✅ Check</option>
                                                <option value="gem">💎 Gem</option>
                                                <option value="badge-check">🎖️ Badge</option>
                                                <option value="tag">🏷️ Tag</option>
                                                <option value="sparkles">✨ Sparkles</option>
                                            </select>
                                        </div>
                                        <textarea x-model="val.description" rows="2" placeholder="Uraian singkat keunggulan..."
                                            class="w-full bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] p-2.5 text-[12px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-1 focus:ring-[#007AFF] resize-none"></textarea>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- OPERATIONAL HOURS CARD -->
                        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-4 transition-colors">
                            <div class="border-b border-black/5 dark:border-white/5 pb-3">
                                <h3 class="text-[16px] font-semibold text-black dark:text-white">
                                    Jadwal &amp; Jam Operasional Outlet
                                </h3>
                                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Informasikan jam buka harian agar calon pelanggan tahu waktu terbaik untuk datang atau memesan.</p>
                            </div>

                            <div class="space-y-1.5">
                                <template x-for="(day, dIdx) in operationalHours" :key="dIdx">
                                    <div class="flex items-center gap-3 p-2.5 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                                        <div class="w-20 sm:w-24 text-[13px] font-medium text-black dark:text-white shrink-0"
                                            x-text="day.day"></div>
                                        <input type="text" x-model="day.hours" :disabled="!day.is_open"
                                            placeholder="08:00 - 17:00 WIB"
                                            :class="day.is_open ?
                                                'text-black dark:text-white bg-white dark:bg-[#2C2C2E]' :
                                                'text-black/35 dark:text-white/35 bg-black/[0.04] dark:bg-white/[0.04] italic'"
                                            class="flex-1 h-8 px-2.5 border-none rounded-[6px] text-[12px] tabular-nums focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                                        <label class="flex items-center gap-1.5 cursor-pointer text-[12px] shrink-0 select-none">
                                            <input type="checkbox" x-model="day.is_open"
                                                class="rounded-[4px] border-black/20 dark:border-white/20 text-[#34C759] focus:ring-[#34C759]">
                                            <span :class="day.is_open ? 'text-[#34C759] dark:text-[#30D158] font-semibold' : 'text-black/40 dark:text-white/40'"
                                                x-text="day.is_open ? 'Buka' : 'Tutup'"></span>
                                        </label>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 4: SERVICES / KATALOG LAYANAN & MENU                     -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'services'" x-cloak class="space-y-5">
                        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">
                                        4. Katalog Layanan, Menu &amp; Paket Produk
                                    </h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Tampilkan daftar layanan, menu unggulan, atau paket komersial dengan foto dan rincian harga transparan.</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <label class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                        <input type="checkbox" x-model="sectionVisibility.services"
                                            class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                        <span>Tampilkan di Web</span>
                                    </label>
                                    <button type="button" @click="addService"
                                        class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        <span>Tambah Layanan</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Services Title & Subtitle -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Judul Section Layanan</label>
                                    <input type="text" name="services_title" x-model="form.services_title"
                                        placeholder="Layanan &amp; Produk Pilihan"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Subjudul / Deskripsi Pengantar</label>
                                    <input type="text" name="services_subtitle" x-model="form.services_subtitle"
                                        placeholder="Kualitas terbaik dan pelayanan prima untuk setiap pelanggan."
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                            </div>

                            <!-- Services Repeater List -->
                            <div class="space-y-3">
                                <template x-for="(service, sIdx) in services" :key="sIdx">
                                    <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 space-y-2.5">

                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full bg-black/[0.06] dark:bg-white/[0.08] text-black/70 dark:text-white/70 flex items-center justify-center text-[11px] font-bold tabular-nums shrink-0"
                                                x-text="sIdx + 1"></span>
                                            <input type="text" x-model="service.title"
                                                placeholder="Nama Layanan / Menu / Paket"
                                                class="flex-1 h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[12px] font-semibold text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                            <input type="text" x-model="service.price" placeholder="Rp 150.000"
                                                class="w-full sm:w-36 h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[12px] font-semibold tabular-nums text-[#34C759] dark:text-[#30D158] focus:outline-none text-right">
                                            <button type="button" @click="removeService(sIdx)"
                                                class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 flex items-center justify-center transition cursor-pointer"
                                                title="Hapus Layanan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                            <div class="md:col-span-2">
                                                <input type="text" x-model="service.description"
                                                    placeholder="Deskripsi ringkas, rincian termasuk, garansi..."
                                                    class="w-full h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[12px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                            </div>
                                            <div>
                                                <input type="text" x-model="service.badge"
                                                    placeholder="Badge: Terpopuler / Promo"
                                                    class="w-full h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[12px] font-medium text-[#FF9500] dark:text-[#FF9F0A] focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                            </div>
                                        </div>

                                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                                            <label class="block text-[10px] font-semibold text-black/50 dark:text-white/50 mb-1 uppercase tracking-wider">Foto Layanan</label>
                                            <div x-show="service.preview_url || service.image_url" class="mb-2 flex items-center gap-2">
                                                <img :src="service.preview_url || service.image_url" alt="Preview Layanan"
                                                    class="w-14 h-10 rounded-[6px] object-cover border border-black/5 dark:border-white/10">
                                                <span class="text-[10px] text-[#007AFF] font-medium"
                                                    x-text="service.preview_url ? 'Foto baru dipilih' : 'Foto tersimpan aktif'"></span>
                                            </div>
                                            <input type="file" :name="'service_images[' + sIdx + ']'"
                                                accept="image/jpeg,image/png,image/webp"
                                                @change="handleServiceImagePreview($event, sIdx)"
                                                class="w-full text-[11px] text-black/50 dark:text-white/50 file:mr-2 file:rounded-[6px] file:border-0 file:bg-[#007AFF]/10 file:px-2.5 file:py-1 file:text-[#007AFF] file:font-semibold file:cursor-pointer">
                                            <label class="mt-1 flex items-center gap-1.5 text-[10px] text-black/50 dark:text-white/50">
                                                <input type="checkbox" :name="'remove_service_images[' + sIdx + ']'"
                                                    value="1" class="rounded-[4px] border-black/20 text-[#FF3B30]">
                                                <span>Hapus foto layanan</span>
                                            </label>
                                        </div>

                                    </div>
                                </template>

                                <!-- Empty State Layanan -->
                                <div x-show="services.length === 0"
                                    class="p-8 text-center bg-black/[0.02] dark:bg-white/[0.03] rounded-[12px] border border-dashed border-black/10 dark:border-white/10 space-y-1.5">
                                    <p class="text-[13px] font-semibold text-black dark:text-white">Belum ada item layanan di katalog</p>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Klik "Tambah Layanan" di atas atau pilih template industri untuk mengisi katalog secara otomatis.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 5: PRODUCTS / INTEGRASI PRODUK KASIR POS                 -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'products'" x-cloak class="space-y-5">
                        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">
                                        5. Sinkronisasi Produk Kasir POS
                                    </h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Tampilkan katalog produk master POS langsung di landing page lengkap dengan tombol pesan instan via WhatsApp.</p>
                                </div>
                                <label class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                    <input type="checkbox" x-model="sectionVisibility.products"
                                        class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span>Tampilkan Section Produk</span>
                                </label>
                            </div>

                            <!-- Toggle Sinkron POS -->
                            <div class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 flex items-center justify-between">
                                <div>
                                    <div class="text-[14px] font-semibold text-black dark:text-white">Tampilkan Produk Aktif dari Kasir POS</div>
                                    <div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Produk aktif di POS akan otomatis tampil dengan harga terkini dan tombol pesan via WhatsApp.</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="show_pos_products" value="1"
                                        {{ $landingPage->show_pos_products ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#007AFF]">
                                    </div>
                                </label>
                            </div>

                            <!-- POS Products List Preview -->
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-2">
                                    Pratinjau Produk POS Terhubung (Maks. 8 Item Pertama)
                                </label>
                                @if ($posProducts->count() > 0)
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                        @foreach ($posProducts as $prod)
                                            <div class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3 space-y-1">
                                                <div class="flex items-center gap-1.5 text-[13px] font-medium text-black dark:text-white truncate">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0"></span>
                                                    <span class="truncate">{{ $prod->name }}</span>
                                                </div>
                                                <div class="text-[13px] font-bold tabular-nums text-black dark:text-white">
                                                    Rp {{ number_format((float) ($prod->selling_price ?? 0), 0, ',', '.') }}
                                                </div>
                                                <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums truncate">
                                                    Stok: {{ (int) ($prod->current_stock ?? 0) }} {{ $prod->unit ?? 'unit' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-6 text-center bg-black/[0.02] dark:bg-white/[0.03] rounded-[10px] border border-dashed border-black/10 dark:border-white/10">
                                        <p class="text-[12px] text-black/50 dark:text-white/50">Belum ada produk POS yang aktif. Kelola produk di menu <a href="{{ route('products.index') }}" class="text-[#007AFF] font-medium hover:underline">Master Data &gt; Produk</a>.</p>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 6: GALLERY / GALERI FOTO & SUASANA BISNIS                -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'gallery'" x-cloak class="space-y-5">
                        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-[16px] font-semibold text-black dark:text-white">
                                            6. Galeri &amp; Suasana Tempat Usaha
                                        </h2>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold tabular-nums bg-black/[0.06] dark:bg-white/[0.08] text-black/70 dark:text-white/70"
                                            x-text="(galleryItems.length + newGalleryUploads.length) + ' / 20 Foto'"></span>
                                    </div>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Tampilkan suasana tempat, hasil karya, foto produk, dan kegiatan bisnis dalam grid modern.</p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <label class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                        <input type="checkbox" x-model="sectionVisibility.gallery"
                                            class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                        <span>Tampilkan di Web</span>
                                    </label>
                                    <button type="button" @click="showAddUrlModal = true"
                                        class="h-8 px-3 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] text-black/80 dark:text-white/80 text-[12px] font-medium flex items-center gap-1.5 transition cursor-pointer">
                                        <span>+ URL</span>
                                    </button>
                                    <button type="button" @click="clearAllGallery()"
                                        x-show="galleryItems.length > 0 || newGalleryUploads.length > 0"
                                        class="h-8 px-3 rounded-[8px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 text-[#FF3B30] text-[12px] font-semibold flex items-center gap-1.5 transition cursor-pointer">
                                        <span>Kosongkan</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Gallery Title & Subtitle -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Judul Bagian Galeri</label>
                                    <input type="text" name="gallery_title" x-model="form.gallery_title"
                                        maxlength="120" placeholder="Galeri &amp; Suasana Toko Kami"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Deskripsi / Subtitle Galeri</label>
                                    <textarea name="gallery_subtitle" x-model="form.gallery_subtitle" maxlength="500" rows="2"
                                        placeholder="Dokumentasi aktivitas, produk unggulan, dan kehangatan pelayanan kami."
                                        class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 py-2 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                                </div>
                            </div>

                            <!-- Upload Dropzone -->
                            <div class="p-6 border-2 border-dashed border-black/10 dark:border-white/10 hover:border-[#007AFF]/50 bg-black/[0.02] dark:bg-white/[0.02] rounded-[14px] text-center transition-all group relative">
                                <input type="file" multiple accept="image/jpeg,image/png,image/webp"
                                    @change="handleGalleryFiles($event)"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="flex flex-col items-center justify-center space-y-1.5 pointer-events-none">
                                    <div class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                                        </svg>
                                    </div>
                                    <div class="text-[13px] font-semibold text-black dark:text-white">
                                        <span class="text-[#007AFF]">Klik untuk memilih foto</span> atau seret file ke area ini
                                    </div>
                                    <p class="text-[11px] text-black/45 dark:text-white/45 max-w-md">
                                        Format: JPG, PNG, WebP (maks. 4 MB per foto). Foto otomatis tampil dalam rasio persegi rapi di website publik.
                                    </p>
                                </div>
                            </div>

                            <!-- Gallery Photos Grid -->
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <div class="text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">
                                        Foto Galeri Aktif &amp; Antrean Upload:
                                    </div>
                                </div>

                                <!-- Empty State -->
                                <div x-show="galleryItems.length === 0 && newGalleryUploads.length === 0"
                                    class="p-8 text-center bg-black/[0.02] dark:bg-white/[0.03] rounded-[12px] border border-dashed border-black/10 dark:border-white/10 space-y-1.5">
                                    <div class="text-[13px] font-semibold text-black dark:text-white">Belum ada foto di galeri</div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 max-w-md mx-auto">Unggah foto dokumentasi suasana toko atau terapkan template industri.</p>
                                </div>

                                <!-- Grid of Photos -->
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3"
                                    x-show="galleryItems.length > 0 || newGalleryUploads.length > 0">

                                    <!-- Existing Saved Photos -->
                                    <template x-for="(item, idx) in galleryItems" :key="'saved-' + idx">
                                        <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-2 space-y-2 flex flex-col justify-between group transition-all">
                                            <div class="aspect-square rounded-[8px] overflow-hidden bg-black/[0.04] dark:bg-white/[0.06] relative group/img flex items-center justify-center">
                                                <img :src="item.url" :alt="item.caption || 'Galeri Foto'"
                                                    class="w-full h-full object-cover">

                                                <!-- Top Badges & Controls -->
                                                <div class="absolute top-1.5 inset-x-1.5 flex items-center justify-between pointer-events-none">
                                                    <span class="px-1.5 py-0.5 rounded-[4px] bg-white/90 dark:bg-black/90 text-[10px] font-mono font-bold text-black dark:text-white shadow-xs"
                                                        x-text="'#' + (idx + 1)"></span>
                                                    <div class="flex items-center gap-1 pointer-events-auto">
                                                        <button type="button" @click="moveGalleryItem(idx, -1)"
                                                            :disabled="idx === 0" title="Geser ke kiri"
                                                            class="w-6 h-6 rounded-[4px] bg-white/90 dark:bg-[#2C2C2E]/90 text-black/80 dark:text-white/80 flex items-center justify-center disabled:opacity-30 disabled:cursor-not-allowed transition">
                                                            ‹
                                                        </button>
                                                        <button type="button" @click="moveGalleryItem(idx, 1)"
                                                            :disabled="idx === galleryItems.length - 1"
                                                            title="Geser ke kanan"
                                                            class="w-6 h-6 rounded-[4px] bg-white/90 dark:bg-[#2C2C2E]/90 text-black/80 dark:text-white/80 flex items-center justify-center disabled:opacity-30 disabled:cursor-not-allowed transition">
                                                            ›
                                                        </button>
                                                        <button type="button" @click="removeGalleryItem(idx)"
                                                            title="Hapus foto ini"
                                                            class="w-6 h-6 rounded-[4px] bg-[#FF3B30] text-white flex items-center justify-center shadow-xs transition">
                                                            ×
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Caption Input -->
                                            <div>
                                                <input type="text" x-model="item.caption"
                                                    placeholder="Caption foto..."
                                                    class="w-full h-7 px-2 bg-white dark:bg-[#2C2C2E] border-none rounded-[6px] text-[11px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Staged New Uploads -->
                                    <template x-for="(nItem, nIdx) in newGalleryUploads" :key="'new-' + nIdx">
                                        <div class="rounded-[12px] bg-[#007AFF]/5 border border-[#007AFF]/30 p-2 space-y-2 flex flex-col justify-between group transition-all">
                                            <div class="aspect-square rounded-[8px] overflow-hidden bg-black/[0.04] dark:bg-white/[0.06] relative group/img flex items-center justify-center">
                                                <img :src="nItem.preview" :alt="nItem.caption || 'Foto Baru'"
                                                    class="w-full h-full object-cover">

                                                <div class="absolute top-1.5 inset-x-1.5 flex items-center justify-between pointer-events-none">
                                                    <span class="px-1.5 py-0.5 rounded-[4px] bg-[#007AFF] text-[10px] font-semibold text-white shadow-xs">Baru</span>
                                                    <div class="flex items-center gap-1 pointer-events-auto">
                                                        <button type="button" @click="removeNewUpload(nIdx)"
                                                            title="Batal upload"
                                                            class="w-6 h-6 rounded-[4px] bg-[#FF3B30] text-white flex items-center justify-center shadow transition cursor-pointer">
                                                            ×
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <input type="text" x-model="nItem.caption"
                                                    placeholder="Caption baru..."
                                                    class="w-full h-7 px-2 bg-white dark:bg-[#2C2C2E] border-none rounded-[6px] text-[11px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                                            </div>
                                        </div>
                                    </template>

                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 7: TESTIMONIALS / ULASAN & TESTIMONI                     -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'testimonials'" x-cloak class="space-y-5">
                        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">
                                        7. Ulasan &amp; Testimoni Pelanggan
                                    </h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Bukti kepuasan nyata dari pelanggan yang meningkatkan konversi dan rasa percaya calon pembeli baru.</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <label class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                        <input type="checkbox" x-model="sectionVisibility.testimonials"
                                            class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                        <span>Tampilkan di Web</span>
                                    </label>
                                    <button type="button" @click="addTestimonial"
                                        class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        <span>Tambah Ulasan</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Testimonials Repeater -->
                            <div class="space-y-3">
                                <template x-for="(testi, tIdx) in testimonials" :key="tIdx">
                                    <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 space-y-2.5">
                                        <div class="flex items-center gap-2">
                                            <input type="text" x-model="testi.name" placeholder="Nama Pelanggan"
                                                class="flex-1 h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[12px] font-semibold text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                            <input type="text" x-model="testi.role" placeholder="Kota / Profesi"
                                                class="flex-1 h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[12px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                            <span class="text-[#FF9500] text-[12px] font-bold font-mono whitespace-nowrap">★ 5.0</span>
                                            <button type="button" @click="removeTestimonial(tIdx)"
                                                class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 flex items-center justify-center transition cursor-pointer"
                                                title="Hapus Ulasan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <textarea x-model="testi.comment" rows="2"
                                            placeholder="Ulasan pengalaman pelanggan..."
                                            class="w-full bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] p-2.5 text-[12px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-1 focus:ring-[#007AFF] resize-none"></textarea>
                                    </div>
                                </template>

                                <div x-show="testimonials.length === 0"
                                    class="p-8 text-center bg-black/[0.02] dark:bg-white/[0.03] rounded-[12px] border border-dashed border-black/10 dark:border-white/10 space-y-1.5">
                                    <p class="text-[13px] font-semibold text-black dark:text-white">Belum ada testimoni pelanggan</p>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Tambahkan testimoni positif dari pelanggan setia Anda untuk memperkuat reputasi toko.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 8: FAQ / TANYA JAWAB POPULER                             -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'faq'" x-cloak class="space-y-5">
                        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">
                                        8. Tanya Jawab Populer (FAQ)
                                    </h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Jawab pertanyaan yang paling sering ditanyakan untuk mempercepat keputusan pembelian.</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <label class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                        <input type="checkbox" x-model="sectionVisibility.faq"
                                            class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                        <span>Tampilkan di Web</span>
                                    </label>
                                    <button type="button" @click="addFaq"
                                        class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        <span>Tambah FAQ</span>
                                    </button>
                                </div>
                            </div>

                            <!-- FAQ Repeater -->
                            <div class="space-y-3">
                                <template x-for="(faq, fIdx) in faqs" :key="fIdx">
                                    <div class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 space-y-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[12px] font-bold text-[#007AFF] font-mono shrink-0">Q:</span>
                                            <input type="text" x-model="faq.q"
                                                placeholder="Pertanyaan (misal: Apakah melayani pesan antar?)"
                                                class="flex-1 h-8 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] px-2.5 text-[12px] font-semibold text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                            <button type="button" @click="removeFaq(fIdx)"
                                                class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 flex items-center justify-center transition cursor-pointer"
                                                title="Hapus FAQ">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <span class="text-[12px] font-bold text-black/35 dark:text-white/35 font-mono shrink-0 mt-1.5">A:</span>
                                            <textarea x-model="faq.a" rows="2" placeholder="Jawaban yang jelas dan ramah..."
                                                class="flex-1 bg-white dark:bg-[#2C2C2E] border-none rounded-[8px] p-2.5 text-[12px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-1 focus:ring-[#007AFF] resize-none"></textarea>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="faqs.length === 0"
                                    class="p-8 text-center bg-black/[0.02] dark:bg-white/[0.03] rounded-[12px] border border-dashed border-black/10 dark:border-white/10 space-y-1.5">
                                    <p class="text-[13px] font-semibold text-black dark:text-white">Belum ada pertanyaan FAQ</p>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Tambahkan daftar tanya jawab untuk mempermudah calon pelanggan.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 9: CONTACT / KONTAK, WA, ALAMAT & SOSMED                 -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'contact'" x-cloak class="space-y-5">
                        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">
                                        9. Kontak, WhatsApp &amp; Peta Lokasi
                                    </h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Hubungkan pelanggan langsung ke nomor WhatsApp, telepon, email, dan rute navigasi Google Maps.</p>
                                </div>
                                <label class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                    <input type="checkbox" x-model="sectionVisibility.contact"
                                        class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span>Tampilkan Section Kontak</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- WhatsApp & Phone -->
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        Nomor WhatsApp Bisnis <span class="text-[#007AFF] font-mono">(Format: 628xxx)</span>
                                    </label>
                                    <input type="text" name="whatsapp_number" x-model="form.whatsapp_number"
                                        placeholder="6281234567890"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] font-mono text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition mb-3">

                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        Nomor Telepon Kantor / Toko (Opsional)
                                    </label>
                                    <input type="text" name="custom_phone" x-model="form.custom_phone"
                                        placeholder="021-1234567 atau 081234..."
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] font-mono text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>

                                <!-- Email & Alamat -->
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        Email Resmi Usaha (Opsional)
                                    </label>
                                    <input type="email" name="custom_email" x-model="form.custom_email"
                                        placeholder="kontak@bisnisanda.com"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition mb-3">

                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                        Google Maps Embed URL
                                    </label>
                                    <input type="text" name="google_maps_embed_url"
                                        x-model="form.google_maps_embed_url"
                                        placeholder="https://www.google.com/maps/embed?pb=..."
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] font-mono text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Google Maps &gt; Bagikan &gt; Sematkan peta &gt; salin URL di <code class="text-[#007AFF]">src="..."</code></p>
                                </div>
                            </div>

                            <!-- Pesan Sambutan WhatsApp -->
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Pesan Sambutan Otomatis WhatsApp
                                </label>
                                <textarea name="whatsapp_welcome_message" x-model="form.whatsapp_welcome_message" rows="2"
                                    placeholder="Halo, saya melihat website Anda dan ingin bertanya lebih lanjut..."
                                    class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 py-2.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                            </div>

                            <!-- Alamat Fisik Lengkap -->
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Alamat Fisik Toko / Kantor / Workshop
                                </label>
                                <textarea name="custom_address" x-model="form.custom_address" rows="2"
                                    placeholder="Jl. Sudirman No. 123, Kelurahan, Kecamatan, Kota/Kabupaten, Kode Pos"
                                    class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 py-2.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                            </div>

                            <!-- SOCIAL MEDIA & MARKETPLACE LINKS -->
                            <div class="border-t border-black/5 dark:border-white/5 pt-4 space-y-3">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">
                                    Tautan Media Sosial &amp; Marketplace Toko
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    @foreach ([['key' => 'instagram', 'label' => '📸 Instagram', 'placeholder' => 'https://instagram.com/...'], ['key' => 'tiktok', 'label' => '🎵 TikTok', 'placeholder' => 'https://tiktok.com/@...'], ['key' => 'facebook', 'label' => '📘 Facebook', 'placeholder' => 'https://facebook.com/...'], ['key' => 'youtube', 'label' => '▶️ YouTube', 'placeholder' => 'https://youtube.com/...'], ['key' => 'tokopedia', 'label' => '🛒 Tokopedia', 'placeholder' => 'https://tokopedia.com/...'], ['key' => 'shopee', 'label' => '🛍️ Shopee', 'placeholder' => 'https://shopee.co.id/...']] as $social)
                                        <div>
                                            <label class="text-[10px] font-semibold text-black/60 dark:text-white/60 block mb-1 uppercase tracking-wider">{{ $social['label'] }}</label>
                                            <input type="text" name="social_links[{{ $social['key'] }}]"
                                                value="{{ $landingPage->social_links[$social['key']] ?? '' }}"
                                                placeholder="{{ $social['placeholder'] }}"
                                                class="w-full h-8 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#007AFF] transition">
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 10: FOOTER / TATA LETAK & KONTEN FOOTER                  -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'footer'" x-cloak class="space-y-5">
                        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                                <div>
                                    <h2 class="text-[16px] font-semibold text-black dark:text-white">
                                        10. Tata Letak &amp; Konten Footer
                                    </h2>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Atur kolom navigasi, ringkasan brand, dan hak cipta di bagian bawah website.</p>
                                </div>
                                <label class="flex items-center gap-2 text-[12px] font-semibold text-black/80 dark:text-white/80 cursor-pointer bg-black/[0.04] dark:bg-white/[0.06] px-3 py-1.5 rounded-[8px]">
                                    <input type="checkbox" x-model="sectionVisibility.footer"
                                        class="rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span>Tampilkan Section Footer</span>
                                </label>
                            </div>

                            <!-- Grid Columns Visibility -->
                            <div class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 space-y-2.5">
                                <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Pilihan Kolom Footer</div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <label class="flex items-center gap-2 p-2 rounded-[6px] bg-white dark:bg-[#2C2C2E] text-[12px] text-black dark:text-white cursor-pointer">
                                        <input type="checkbox" x-model="sectionVisibility.footer_brand"
                                            class="rounded-[4px] border-black/20 text-[#007AFF]">
                                        <span>Brand &amp; Sosmed</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-[6px] bg-white dark:bg-[#2C2C2E] text-[12px] text-black dark:text-white cursor-pointer">
                                        <input type="checkbox" x-model="sectionVisibility.footer_navigation"
                                            class="rounded-[4px] border-black/20 text-[#007AFF]">
                                        <span>Navigasi</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-[6px] bg-white dark:bg-[#2C2C2E] text-[12px] text-black dark:text-white cursor-pointer">
                                        <input type="checkbox" x-model="sectionVisibility.footer_services"
                                            class="rounded-[4px] border-black/20 text-[#007AFF]">
                                        <span>Layanan</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-[6px] bg-white dark:bg-[#2C2C2E] text-[12px] text-black dark:text-white cursor-pointer">
                                        <input type="checkbox" x-model="sectionVisibility.footer_contact"
                                            class="rounded-[4px] border-black/20 text-[#007AFF]">
                                        <span>Kontak</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">Deskripsi Singkat Footer</label>
                                <textarea name="footer_description" x-model="form.footer_description" maxlength="1000" rows="3"
                                    placeholder="Deskripsi ringkas profil usaha untuk ditampilkan di bagian footer..."
                                    class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 py-2.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Judul Kolom Navigasi</label>
                                    <input type="text" name="footer_navigation_title"
                                        x-model="form.footer_navigation_title" maxlength="80" placeholder="Navigasi"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Judul Kolom Layanan</label>
                                    <input type="text" name="footer_services_title"
                                        x-model="form.footer_services_title" maxlength="80" placeholder="Layanan Kami"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Judul Kolom Kontak</label>
                                    <input type="text" name="footer_contact_title" x-model="form.footer_contact_title"
                                        maxlength="80" placeholder="Hubungi Kami"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Teks Tombol Aksi Footer</label>
                                    <input type="text" name="footer_cta_text" x-model="form.footer_cta_text"
                                        maxlength="100" placeholder="Hubungi via WhatsApp"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">Teks Hak Cipta (Copyright)</label>
                                <input type="text" name="footer_copyright" x-model="form.footer_copyright"
                                    maxlength="255"
                                    placeholder="© {{ date('Y') }} {{ $business->name }}. All rights reserved."
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 11: SEO / OPTIMASI GOOGLE & META TAG                     -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'seo'" x-cloak class="space-y-5">
                        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 transition-colors">

                            <div class="border-b border-black/5 dark:border-white/5 pb-3">
                                <h2 class="text-[16px] font-semibold text-black dark:text-white">
                                    11. Optimasi Google SEO &amp; Pratinjau Pencarian
                                </h2>
                                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Tingkatkan peringkat halaman bisnis Anda di Google Search agar calon pembeli di sekitar Anda mudah menemukan toko.</p>
                            </div>

                            <!-- Meta Title -->
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Judul Halaman Google (Meta Title)
                                </label>
                                <input type="text" name="meta_title" x-model="form.meta_title"
                                    placeholder="{{ $business->name }} — Layanan &amp; Produk Terpercaya"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <div class="flex justify-between text-[11px] text-black/45 dark:text-white/45 mt-1">
                                    <span>Panjang ideal: 50–60 karakter</span>
                                    <span :class="(form.meta_title || '').length > 60 ? 'text-[#FF3B30] font-semibold' : ''"
                                        x-text="(form.meta_title || '').length + ' / 60'"></span>
                                </div>
                            </div>

                            <!-- Meta Description -->
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Deskripsi Google (Meta Description)
                                </label>
                                <textarea name="meta_description" x-model="form.meta_description" rows="3"
                                    placeholder="Deskripsi ringkas bisnis Anda yang tampil di bawah judul pada hasil pencarian Google..."
                                    class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 py-2.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-none"></textarea>
                                <div class="flex justify-between text-[11px] text-black/45 dark:text-white/45 mt-1">
                                    <span>Panjang ideal: 120–160 karakter</span>
                                    <span :class="(form.meta_description || '').length > 160 ? 'text-[#FF3B30] font-semibold' : ''"
                                        x-text="(form.meta_description || '').length + ' / 160'"></span>
                                </div>
                            </div>

                            <!-- Keywords -->
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Kata Kunci Relevan (Meta Keywords)
                                </label>
                                <input type="text" name="meta_keywords" x-model="form.meta_keywords" maxlength="500"
                                    placeholder="kuliner jakarta, servis motor, katering enak, toko grosir"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Pisahkan tiap kata kunci dengan tanda koma.</p>
                            </div>

                            <!-- OG Image -->
                            <div>
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Upload Gambar Pratinjau Sosial (Open Graph / WhatsApp Share)
                                </label>
                                <div x-show="previews.og || form.og_image_url" class="mb-2 flex items-center gap-3">
                                    <img :src="previews.og || form.og_image_url" alt="OG Preview"
                                        class="w-32 h-16 rounded-[10px] object-cover border border-black/5 dark:border-white/10">
                                    <span class="text-[11px] text-[#007AFF] font-medium"
                                        x-text="previews.og ? 'Pratinjau OG baru dipilih' : 'Gambar OG tersimpan aktif'"></span>
                                </div>
                                <input type="file" name="og_image" accept="image/jpeg,image/png,image/webp"
                                    @change="handleImagePreview($event, 'og')"
                                    class="w-full text-[12px] text-black/50 dark:text-white/50 file:mr-3 file:rounded-[8px] file:border-0 file:bg-[#007AFF]/10 file:px-3 file:py-1.5 file:text-[#007AFF] file:font-semibold file:cursor-pointer">
                                <label class="mt-2 flex items-center gap-2 text-[11px] text-black/50 dark:text-white/50">
                                    <input type="checkbox" name="remove_og_image" value="1"
                                        class="rounded-[4px] border-black/20 text-[#FF3B30]">
                                    <span>Hapus gambar OG tersimpan</span>
                                </label>
                            </div>

                            <!-- LIVE GOOGLE SEARCH SERP PREVIEW -->
                            <div class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-1">
                                <div class="text-[10px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                    </svg>
                                    <span>Pratinjau Google Search</span>
                                </div>
                                <div class="text-[11px] text-black/50 dark:text-white/50 font-mono truncate">{{ $publicUrl }}</div>
                                <div class="text-[14px] font-medium text-[#007AFF] hover:underline cursor-pointer truncate"
                                    x-text="form.meta_title || '{{ addslashes($business->name) }} — Layanan & Produk Terpercaya'">
                                </div>
                                <div class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed"
                                    style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"
                                    x-text="form.meta_description || 'Kunjungi halaman profil dan katalog layanan resmi kami. Hubungi langsung melalui WhatsApp.'">
                                </div>
                            </div>

                            <!-- Schema Markup Info Banner -->
                            <div class="p-3.5 bg-[#34C759]/10 border border-[#34C759]/20 rounded-[10px] flex items-start gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0 mt-1.5"></span>
                                <div class="text-[12px] text-[#248A3D] dark:text-[#30D158] leading-relaxed">
                                    <strong class="font-semibold">Schema Markup Otomatis:</strong> Cooca secara otomatis menyematkan data terstruktur JSON-LD <code class="font-mono bg-black/5 dark:bg-white/10 px-1 py-0.5 rounded">LocalBusiness</code> agar Google Maps dan pencarian lokal mengenali bisnis Anda secara akurat.
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <!-- ========================================== -->
            <!-- 4. STICKY ACTION BAR (macOS Sonoma Floating Toolbar) -->
            <!-- ========================================== -->
            <div class="fixed bottom-0 inset-x-0 lg:left-64 z-40 backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border-t border-black/5 dark:border-white/10 px-4 sm:px-8 py-3 flex flex-col sm:flex-row items-center justify-between gap-3 transition-colors">
                <div class="flex items-center gap-2 text-[12px]">
                    <span class="w-2 h-2 rounded-full"
                        :class="saving ? 'bg-[#FF9500] animate-ping' : 'bg-[#34C759]'"></span>
                    <span class="font-medium text-black/80 dark:text-white/80"
                        x-text="saving ? 'Menyimpan perubahan ke server...' : (saveMessage || 'Siap menyimpan perubahan.')"></span>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="button" @click="openPreview()" :disabled="saving"
                        class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                        <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                        </svg>
                        <span>Preview Live</span>
                    </button>

                    <button type="submit" :disabled="saving"
                        class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] flex-1 sm:flex-none disabled:opacity-50">
                        <svg x-show="saving" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="saving ? 'Menyimpan...' : 'Simpan Semua Perubahan'">Simpan Semua Perubahan</span>
                    </button>
                </div>
            </div>

        </form>

        <!-- ============================================================ -->
        <!-- MODAL: LIVE PREVIEW DRAWER (Apple Sheet Style)               -->
        <!-- ============================================================ -->
        <div x-show="showPreview" x-transition.opacity
            class="fixed inset-0 z-[60] bg-black/30 backdrop-blur-sm p-3 sm:p-6 flex items-center justify-center" style="display: none;" x-cloak>
            <div class="h-full max-w-6xl w-full mx-auto bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl rounded-[16px] overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.25)] flex flex-col border border-black/5 dark:border-white/10">
                <div class="shrink-0 px-4 sm:px-6 py-3 border-b border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#34C759]"></span>
                        <span class="text-[14px] font-semibold text-black dark:text-white">Pratinjau Langsung (Live Preview)</span>
                        <span class="text-[11px] text-black/45 dark:text-white/45 font-mono hidden sm:inline">{{ $publicUrl }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="previewUrl" target="_blank"
                            class="text-[12px] font-medium text-[#007AFF] hover:underline flex items-center gap-1">
                            <span>Buka Tab Baru</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </a>
                        <button type="button" @click="showPreview = false"
                            class="w-7 h-7 rounded-[6px] text-black/50 dark:text-white/50 hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center transition"
                            title="Tutup preview">
                            ✕
                        </button>
                    </div>
                </div>
                <iframe :src="previewUrl" title="Preview halaman publik bisnis"
                    class="w-full h-full border-0 bg-white"></iframe>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: 20 INDUSTRY PRESETS SELECTOR (Apple Sheet Style)      -->
        <!-- ============================================================ -->
        <div x-show="showPresetModal" x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm"
            style="display:none;" x-cloak>
            <div @click.away="showPresetModal = false"
                class="bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 rounded-[16px] max-w-5xl w-full max-h-[88vh] flex flex-col shadow-[0_20px_50px_rgba(0,0,0,0.25)] overflow-hidden">

                <!-- Modal Header -->
                <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                    <div>
                        <h3 class="text-[16px] font-semibold text-black dark:text-white">Template 20 Sektor Industri Bisnis Indonesia</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Pilih sektor usaha Anda untuk mengisi konten, headline, layanan, FAQ, dan ulasan dalam 1 klik.</p>
                    </div>
                    <button type="button" @click="showPresetModal = false"
                        class="w-7 h-7 rounded-[6px] text-black/50 dark:text-white/50 hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center transition">
                        ✕
                    </button>
                </div>

                <!-- Modal Body: 20 Industry Cards -->
                <div class="p-4 sm:p-5 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
                        @foreach ($industries as $ind)
                            <div class="p-3.5 rounded-[12px] border border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.03] hover:border-[#007AFF]/50 transition-all flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-2xl">{{ $ind['icon'] }}</span>
                                        <span class="w-3.5 h-3.5 rounded-full border border-black/10 dark:border-white/10 shadow-2xs"
                                            style="background-color: {{ $ind['theme_color'] }}"></span>
                                    </div>
                                    <h4 class="text-[13px] font-semibold text-black dark:text-white leading-tight mb-1">
                                        {{ $ind['name'] }}
                                    </h4>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 leading-relaxed line-clamp-2">
                                        {{ $ind['description'] }}
                                    </p>
                                </div>
                                <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5">
                                    <button type="button"
                                        @click="applyPreset('{{ $ind['id'] }}', $event.currentTarget)"
                                        class="w-full h-7 rounded-[6px] bg-[#007AFF]/10 hover:bg-[#007AFF] text-[#007AFF] hover:text-white text-[11px] font-semibold active:scale-[0.97] transition-all flex items-center justify-center gap-1 cursor-pointer">
                                        <span>Terapkan</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-4 py-3 border-t border-black/5 dark:border-white/10 flex flex-col sm:flex-row justify-between items-center gap-2 text-[12px] text-black/50 dark:text-white/50">
                    <p>⚡ Penerapan template menyelaraskan teks pengantar, 4 pilar nilai, layanan, FAQ, dan ulasan.</p>
                    <button type="button" @click="showPresetModal = false"
                        class="h-8 px-4 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] text-black/80 dark:text-white/80 font-medium transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: ADD PHOTO BY URL (Apple Alert/Sheet Style)            -->
        <!-- ============================================================ -->
        <div x-show="showAddUrlModal" x-cloak
            class="fixed inset-0 z-50 bg-black/30 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 rounded-[14px] p-5 shadow-[0_20px_50px_rgba(0,0,0,0.25)] space-y-4"
                @click.outside="showAddUrlModal = false">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-3">
                    <h3 class="font-semibold text-[15px] text-black dark:text-white">
                        Tambah Foto Galeri via URL
                    </h3>
                    <button type="button" @click="showAddUrlModal = false"
                        class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">
                        ✕
                    </button>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-black/50 dark:text-white/50 mb-1.5 uppercase tracking-wider">
                            URL Gambar <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" x-model="newUrlInput"
                            placeholder="https://example.com/foto.jpg atau /storage/..."
                            class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-black/50 dark:text-white/50 mb-1.5 uppercase tracking-wider">
                            Caption Foto (Opsional)
                        </label>
                        <input type="text" x-model="newCaptionInput"
                            placeholder="Contoh: Suasana Ruang Tunggu dan Kasir"
                            class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-black/5 dark:border-white/5">
                    <button type="button" @click="showAddUrlModal = false"
                        class="h-8 px-3.5 rounded-[8px] text-[12px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">Batal</button>
                    <button type="button" @click="addGalleryByUrl()" :disabled="!newUrlInput.trim()"
                        class="h-8 px-4 rounded-[8px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12px] font-semibold disabled:opacity-50 transition cursor-pointer">Tambahkan</button>
                </div>
            </div>
        </div>

    </div>

    @php
        $sectionVisibilityDefaults = array_merge(
            [
                'hero' => true,
                'about' => true,
                'products' => true,
                'services' => true,
                'gallery' => true,
                'testimonials' => true,
                'faq' => true,
                'contact' => true,
                'footer' => true,
                'footer_brand' => true,
                'footer_navigation' => true,
                'footer_services' => true,
                'footer_contact' => true,
            ],
            $landingPage->section_visibility ?? [],
        );
    @endphp

    <script>
        function landingPageEditor() {
            return {
                activeTab: 'general',
                showPresetModal: false,
                showPreview: false,
                previewUrl: @json($publicUrl),
                copied: false,
                saving: false,
                galleryFileCount: 0,
                saveMessage: '',
                saveError: '',
                isPublished: @json((bool) $landingPage->is_published),

                tabs: [
                    { id: 'general', label: '1. Identitas & Tema' },
                    { id: 'hero', label: '2. Banner Hero' },
                    { id: 'about', label: '3. Profil & Nilai' },
                    { id: 'services', label: '4. Katalog Layanan' },
                    { id: 'products', label: '5. Produk POS' },
                    { id: 'gallery', label: '6. Galeri Suasana' },
                    { id: 'testimonials', label: '7. Ulasan Pelanggan' },
                    { id: 'faq', label: '8. Tanya Jawab (FAQ)' },
                    { id: 'contact', label: '9. Kontak & Lokasi' },
                    { id: 'footer', label: '10. Konten Footer' },
                    { id: 'seo', label: '11. Optimasi Google SEO' },
                ],

                themePresets: [
                    { hex: '#007AFF', label: 'System Blue' },
                    { hex: '#34C759', label: 'System Green' },
                    { hex: '#0EA5E9', label: 'Sky' },
                    { hex: '#5856D6', label: 'System Indigo' },
                    { hex: '#AF52DE', label: 'System Purple' },
                    { hex: '#FF2D55', label: 'System Pink' },
                    { hex: '#FF9500', label: 'System Orange' },
                    { hex: '#FF3B30', label: 'System Red' },
                    { hex: '#30B0C7', label: 'System Teal' },
                    { hex: '#8E8E93', label: 'System Gray' },
                ],

                sectionVisibility: @json($sectionVisibilityDefaults),

                form: {
                    industry_preset: @json($landingPage->industry_preset ?? 'retail'),
                    theme_color: @json($landingPage->theme_color ?? '#007AFF'),
                    announcement_badge: @json($landingPage->announcement_badge ?? ''),
                    headline: @json($landingPage->headline ?? ''),
                    subheadline: @json($landingPage->subheadline ?? ''),
                    cta_primary_text: @json($landingPage->cta_primary_text ?? ''),
                    cta_primary_url: @json($landingPage->cta_primary_url ?? ''),
                    cta_secondary_text: @json($landingPage->cta_secondary_text ?? ''),
                    cta_secondary_url: @json($landingPage->cta_secondary_url ?? ''),
                    hero_image_url: @json($landingPage->hero_image_url ?? ''),
                    logo_url: @json($landingPage->logo_url ?? ''),
                    about_title: @json($landingPage->about_title ?? ''),
                    about_story: @json($landingPage->about_story ?? ''),
                    services_title: @json($landingPage->services_title ?? ''),
                    services_subtitle: @json($landingPage->services_subtitle ?? ''),
                    whatsapp_number: @json($landingPage->whatsapp_number ?? ''),
                    custom_phone: @json($landingPage->custom_phone ?? ''),
                    whatsapp_welcome_message: @json($landingPage->whatsapp_welcome_message ?? ''),
                    custom_email: @json($landingPage->custom_email ?? ''),
                    custom_address: @json($landingPage->custom_address ?? ''),
                    google_maps_embed_url: @json($landingPage->google_maps_embed_url ?? ''),
                    gallery_title: @json($landingPage->gallery_title ?? ''),
                    gallery_subtitle: @json($landingPage->gallery_subtitle ?? ''),
                    meta_title: @json($landingPage->meta_title ?? ''),
                    meta_description: @json($landingPage->meta_description ?? ''),
                    meta_keywords: @json($landingPage->meta_keywords ?? ''),
                    footer_description: @json($landingPage->footer_description ?? ''),
                    footer_navigation_title: @json($landingPage->footer_navigation_title ?? ''),
                    footer_services_title: @json($landingPage->footer_services_title ?? ''),
                    footer_contact_title: @json($landingPage->footer_contact_title ?? ''),
                    footer_cta_text: @json($landingPage->footer_cta_text ?? ''),
                    footer_copyright: @json($landingPage->footer_copyright ?? ''),
                },

                previews: {
                    logo: '',
                    hero: '',
                    about: '',
                    og: ''
                },

                @php
                    $initialGallery = collect($landingPage->gallery_images ?? [])
                        ->map(function ($img) {
                            if (is_array($img)) {
                                return [
                                    'url' => (string) ($img['url'] ?? ''),
                                    'caption' => (string) ($img['caption'] ?? ''),
                                ];
                            }
                            return [
                                'url' => (string) $img,
                                'caption' => '',
                            ];
                        })
                        ->filter(fn($item) => filled($item['url']))
                        ->values();
                @endphp
                galleryItems: {!! json_encode($initialGallery, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!},
                newGalleryUploads: [],
                showAddUrlModal: false,
                newUrlInput: '',
                newCaptionInput: '',

                values: @json($landingPage->values ?? []),
                services: @json($landingPage->custom_services ?? []),
                faqs: @json($landingPage->faqs ?? []),
                testimonials: @json($landingPage->testimonials ?? []),
                operationalHours: @json($landingPage->operational_hours ?? []),

                init() {
                    if (!this.values || this.values.length === 0) {
                        this.values = [
                            {
                                title: 'Kualitas Terjamin',
                                description: 'Standar mutu dan pengerjaan terbaik dengan garansi kepuasan.',
                                icon: 'shield-check'
                            },
                            {
                                title: 'Pelayanan Cepat',
                                description: 'Responsif dan tepat waktu untuk setiap kebutuhan pelanggan.',
                                icon: 'zap'
                            },
                            {
                                title: 'Harga Transparan',
                                description: 'Biaya jelas tanpa ada pungutan tersembunyi.',
                                icon: 'award'
                            },
                            {
                                title: 'Konsultasi Ramah',
                                description: 'Dapatkan rekomendasi terbaik dari tim berpengalaman kami.',
                                icon: 'star'
                            }
                        ];
                    }
                    if (!this.operationalHours || this.operationalHours.length === 0) {
                        this.operationalHours = [
                            { day: 'Senin', hours: '08:00 - 17:00 WIB', is_open: true },
                            { day: 'Selasa', hours: '08:00 - 17:00 WIB', is_open: true },
                            { day: 'Rabu', hours: '08:00 - 17:00 WIB', is_open: true },
                            { day: 'Kamis', hours: '08:00 - 17:00 WIB', is_open: true },
                            { day: 'Jumat', hours: '08:00 - 17:00 WIB', is_open: true },
                            { day: 'Sabtu', hours: '08:00 - 15:00 WIB', is_open: true },
                            { day: 'Minggu', hours: 'Tutup', is_open: false },
                        ];
                    }
                },

                handleImagePreview(event, type) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    if (!validTypes.includes(file.type)) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Format Tidak Didukung',
                                text: 'Hanya format JPG, PNG, atau WebP yang diperbolehkan.',
                                confirmButtonColor: '#007AFF'
                            });
                        }
                        event.target.value = '';
                        return;
                    }
                    if (file.size > 4 * 1024 * 1024) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Ukuran Terlalu Besar',
                                text: 'Ukuran file gambar maksimal 4 MB.',
                                confirmButtonColor: '#007AFF'
                            });
                        }
                        event.target.value = '';
                        return;
                    }
                    this.previews[type] = URL.createObjectURL(file);
                },

                handleServiceImagePreview(event, sIdx) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    if (!validTypes.includes(file.type)) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Format Tidak Didukung',
                                text: 'Hanya format JPG, PNG, atau WebP yang diperbolehkan.',
                                confirmButtonColor: '#007AFF'
                            });
                        }
                        event.target.value = '';
                        return;
                    }
                    if (file.size > 4 * 1024 * 1024) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Ukuran Terlalu Besar',
                                text: 'Ukuran file gambar maksimal 4 MB.',
                                confirmButtonColor: '#007AFF'
                            });
                        }
                        event.target.value = '';
                        return;
                    }
                    this.services[sIdx].preview_url = URL.createObjectURL(file);
                },

                handleGalleryFiles(event) {
                    const files = Array.from(event.target.files || []);
                    if (!files.length) return;
                    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    for (let file of files) {
                        if (!validTypes.includes(file.type)) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Format Tidak Didukung',
                                    text: `File "${file.name}" bukan format JPG, PNG, atau WebP.`,
                                    confirmButtonColor: '#007AFF'
                                });
                            }
                            continue;
                        }
                        if (file.size > 4 * 1024 * 1024) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Ukuran Terlalu Besar',
                                    text: `File "${file.name}" melebihi batas 4 MB.`,
                                    confirmButtonColor: '#007AFF'
                                });
                            }
                            continue;
                        }
                        this.newGalleryUploads.push({
                            file: file,
                            preview: URL.createObjectURL(file),
                            caption: ''
                        });
                    }
                    event.target.value = '';
                },

                addGalleryByUrl() {
                    const url = this.newUrlInput.trim();
                    if (!url) return;
                    this.galleryItems.push({
                        url: url,
                        caption: this.newCaptionInput.trim()
                    });
                    this.newUrlInput = '';
                    this.newCaptionInput = '';
                    this.showAddUrlModal = false;
                },

                removeGalleryItem(index) {
                    this.galleryItems.splice(index, 1);
                },

                removeNewUpload(index) {
                    this.newGalleryUploads.splice(index, 1);
                },

                moveGalleryItem(index, direction) {
                    const targetIndex = index + direction;
                    if (targetIndex < 0 || targetIndex >= this.galleryItems.length) return;
                    const item = this.galleryItems.splice(index, 1)[0];
                    this.galleryItems.splice(targetIndex, 0, item);
                },

                clearAllGallery() {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Kosongkan Semua Galeri?',
                            text: 'Semua foto aktif dan antrean foto baru akan dihapus dari galeri.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Kosongkan',
                            cancelButtonText: 'Batal',
                            confirmButtonColor: '#FF3B30',
                            cancelButtonColor: '#007AFF'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.galleryItems = [];
                                this.newGalleryUploads = [];
                            }
                        });
                    } else {
                        this.galleryItems = [];
                        this.newGalleryUploads = [];
                    }
                },

                addService() {
                    this.services.push({
                        title: '',
                        price: '',
                        description: '',
                        badge: '',
                        preview_url: ''
                    });
                },

                removeService(index) {
                    this.services.splice(index, 1);
                },

                addTestimonial() {
                    this.testimonials.push({
                        name: '',
                        role: 'Pelanggan',
                        comment: '',
                        rating: 5
                    });
                },

                removeTestimonial(index) {
                    this.testimonials.splice(index, 1);
                },

                addFaq() {
                    this.faqs.push({
                        q: '',
                        a: ''
                    });
                },

                removeFaq(index) {
                    this.faqs.splice(index, 1);
                },

                copyLink(url) {
                    navigator.clipboard.writeText(url).then(() => {
                        this.copied = true;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Link website publik berhasil disalin!',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                        setTimeout(() => {
                            this.copied = false;
                        }, 2500);
                    });
                },

                applyPreset(presetKey, button) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Terapkan Template Industri?',
                            text: 'Konten draft Anda akan diselaraskan sesuai standar sektor industri yang dipilih.',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Terapkan',
                            cancelButtonText: 'Batal',
                            confirmButtonColor: '#007AFF',
                            cancelButtonColor: '#8E8E93'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.executeApplyPreset(presetKey, button);
                            }
                        });
                    } else {
                        this.executeApplyPreset(presetKey, button);
                    }
                },

                executeApplyPreset(presetKey, button) {
                    const btn = button;
                    if (btn) {
                        btn.textContent = 'Memuat...';
                        btn.disabled = true;
                    }

                    fetch('{{ route('landing-page.preset') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                preset: presetKey
                            })
                        })
                        .then(async res => {
                            const data = await res.json();
                            if (!res.ok) throw new Error(data.message || 'Gagal memuat template.');
                            return data;
                        })
                        .then(data => {
                            if (data.preset) {
                                const p = data.preset;
                                this.form.industry_preset = presetKey;
                                if (p.theme_color) this.form.theme_color = p.theme_color;
                                if (p.headline) this.form.headline = p.headline;
                                if (p.subheadline) this.form.subheadline = p.subheadline;
                                if (p.announcement_badge) this.form.announcement_badge = p.announcement_badge;
                                if (p.cta_primary_text) this.form.cta_primary_text = p.cta_primary_text;
                                if (p.cta_secondary_text) this.form.cta_secondary_text = p.cta_secondary_text;
                                if (p.about_title) this.form.about_title = p.about_title;
                                if (p.about_story) this.form.about_story = p.about_story;
                                if (p.values) this.values = p.values;
                                if (p.services) this.services = p.services;
                                if (p.faqs) this.faqs = p.faqs;
                                if (p.testimonials) this.testimonials = p.testimonials;
                                this.showPresetModal = false;
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Template Berhasil Diterapkan!',
                                        text: 'Seluruh teks profil, layanan, FAQ, dan ulasan telah diperbarui.',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                }
                            }
                        })
                        .catch(error => {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: error.message || 'Gagal menerapkan template. Silakan coba kembali.',
                                    confirmButtonColor: '#FF3B30'
                                });
                            }
                        })
                        .finally(() => {
                            if (btn) {
                                btn.textContent = 'Terapkan';
                                btn.disabled = false;
                            }
                        });
                },

                async openPreview() {
                    const saved = await this.saveAll();
                    if (!saved) return;

                    this.previewUrl = @json($publicUrl) + '?preview=' + Date.now();
                    this.showPreview = true;
                },

                async saveAll() {
                    if (this.saving) return;
                    this.saving = true;
                    this.saveMessage = '';
                    this.saveError = '';

                    try {
                        const formEl = document.getElementById('landingPageForm');
                        const formData = new FormData(formEl);

                        // Sinkronkan galleryItems JSON dan unggahan foto baru beserta captionnya
                        formData.set('gallery_images_json', JSON.stringify(this.galleryItems));
                        formData.delete('gallery_images[]');
                        formData.delete('new_gallery_captions[]');
                        this.newGalleryUploads.forEach((item) => {
                            if (item.file) {
                                formData.append('gallery_images[]', item.file);
                                formData.append('new_gallery_captions[]', item.caption || '');
                            }
                        });

                        const response = await fetch('{{ route('landing-page.update') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });
                        const data = await response.json();
                        if (!response.ok) {
                            const messages = data.errors ? Object.values(data.errors).flat() : [];
                            const errorMsg = messages[0] || data.message || 'Perubahan gagal disimpan.';
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Periksa kembali input data!',
                                    text: errorMsg,
                                    confirmButtonColor: '#FF9500'
                                });
                            }
                            throw new Error(errorMsg);
                        }
                        this.saveMessage = data.message || 'Perubahan berhasil disimpan.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Disimpan!',
                                text: data.message || 'Website bisnis Anda berhasil diperbarui.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                        if (data.landing_page) {
                            if (data.landing_page.hero_image_url) this.form.hero_image_url = data.landing_page.hero_image_url;
                            if (data.landing_page.logo_url) this.form.logo_url = data.landing_page.logo_url;
                            if (data.landing_page.about_image_url) this.form.about_image_url = data.landing_page.about_image_url;
                            if (data.landing_page.og_image_url) this.form.og_image_url = data.landing_page.og_image_url;
                            if (data.landing_page.gallery_images) {
                                this.galleryItems = (data.landing_page.gallery_images || []).map(img => {
                                    if (typeof img === 'object' && img !== null) {
                                        return {
                                            url: img.url || '',
                                            caption: img.caption || ''
                                        };
                                    }
                                    return {
                                        url: String(img),
                                        caption: ''
                                    };
                                }).filter(item => item.url);
                                this.newGalleryUploads = [];
                            }
                        }
                        return true;
                    } catch (error) {
                        this.saveError = error.message || 'Perubahan gagal disimpan.';
                        return false;
                    } finally {
                        this.saving = false;
                    }
                },

                async togglePublish() {
                    if (this.saving) return;
                    this.saving = true;
                    this.saveError = '';
                    try {
                        const response = await fetch('{{ route('landing-page.toggle-publish') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Status publikasi gagal diubah.');
                        this.isPublished = Boolean(data.is_published);
                        this.saveMessage = data.message || 'Status publikasi diperbarui.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Status Diperbarui!',
                                text: data.message || 'Status publikasi website berhasil diubah.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    } catch (error) {
                        this.saveError = error.message || 'Status publikasi gagal diubah.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: error.message || 'Status publikasi gagal diubah.',
                                confirmButtonColor: '#FF3B30'
                            });
                        }
                    } finally {
                        this.saving = false;
                    }
                }
            };
        }
    </script>
@endsection
