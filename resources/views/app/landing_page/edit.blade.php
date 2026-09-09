@extends('layouts.app', [
    'title' => 'Website & Landing Page Bisnis — Cooca UMKM',
    'headerTitle' => 'Website & Landing Page Bisnis',
    'headerSubtitle' => 'Kelola profil digital, katalog produk & layanan, galeri, serta kontak online ' . $business->name,
])

@section('content')
    <div class="space-y-6 pb-16" x-data="landingPageEditor()">

        <!-- ========================================== -->
        <!-- 0. BREADCRUMB BAR                         -->
        <!-- ========================================== -->
        <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 print:hidden" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}"
                class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 font-bold text-slate-900 dark:text-white">
                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                <span>Dashboard</span>
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
            <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                <span>Pemasaran &amp; Profil</span>
            </span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
            <span class="text-slate-900 dark:text-white font-bold flex items-center gap-1.5">
                <span>Website &amp; Landing Page Bisnis</span>
            </span>
        </nav>

        <!-- ========================================== -->
        <!-- 1. TOP HEADER BANNER & ACTION COCKPIT      -->
        <!-- ========================================== -->
        <div
            class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative overflow-hidden transition-colors">

            <!-- Subtle decorative glow -->
            <div
                class="absolute -right-16 -top-16 w-64 h-64 bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full blur-3xl pointer-events-none">
            </div>

            <div class="space-y-2 z-10 max-w-3xl">


                <h1 class="text-xl sm:text-2l font-black text-slate-900 dark:text-white tracking-tight">
                    Website &amp; Profil Digital: {{ $business->name }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Kelola halaman landing page profesional, katalog layanan, galeri foto suasana usaha, kontak WhatsApp
                    1-klik, dan optimasi SEO Google agar bisnis Anda semakin terpercaya.
                </p>

                <!-- Public URL bar preview inside top banner -->
                <div class="pt-2 flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-slate-500 dark:text-slate-400 font-semibold">Tautan Publik:</span>
                    <a href="{{ $publicUrl }}" target="_blank"
                        class="font-mono text-emerald-600 dark:text-emerald-400 hover:underline font-bold inline-flex items-center gap-1">
                        <span>{{ $publicUrl }}</span>
                        <i data-lucide="external-link" class="w-3 h-3"></i>
                    </a>
                    <button type="button" @click="copyLink('{{ $publicUrl }}')"
                        class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium transition inline-flex items-center gap-1 text-[11px]">
                        <i data-lucide="copy" class="w-3 h-3"></i>
                        <span x-text="copied ? 'Tersalin!' : 'Salin'">Salin</span>
                    </button>
                    <a href="https://api.whatsapp.com/send?text={{ urlencode('Halo, kunjungi profil dan katalog layanan kami di: ' . $publicUrl) }}"
                        target="_blank"
                        class="px-2 py-0.5 rounded-md bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 font-medium transition inline-flex items-center gap-1 text-[11px]">
                        <i data-lucide="share-2" class="w-3 h-3"></i>
                        <span>Share ke WA</span>
                    </a>
                </div>
            </div>

            <!-- Quick Action Cockpit -->
            <div class="flex flex-wrap items-center gap-2.5 z-10 w-full lg:w-auto">
                <button type="button" @click="showPresetModal = true"
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold text-amber-800 dark:text-amber-300 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/60 dark:hover:bg-amber-900/60 border border-amber-200 dark:border-amber-800/80 transition cursor-pointer shadow-2xs flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                    <i data-lucide="sparkles" class="w-4 h-4 text-amber-500"></i>
                    <span>Template Industri (20)</span>
                </button>

                <button type="button" @click="togglePublish()" :disabled="saving"
                    :class="isPublished
                        ?
                        'text-rose-700 dark:text-rose-400 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 border-rose-200 dark:border-rose-800' :
                        'text-emerald-700 dark:text-emerald-400 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/60 border-emerald-200 dark:border-emerald-800'"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold border transition cursor-pointer shadow-2xs flex items-center justify-center gap-1.5 flex-1 sm:flex-none disabled:opacity-50">
                    <i :data-lucide="isPublished ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                    <span x-text="isPublished ? 'Arsipkan Publik' : 'Terbitkan Sekarang'"></span>
                </button>

                <button type="button" @click="openPreview()" :disabled="saving"
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                    <i data-lucide="monitor-play" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                    <span x-text="saving ? 'Menyiapkan...' : 'Preview Live'">Preview Live</span>
                </button>

                <a href="{{ $publicUrl }}" target="_blank"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                    <span>Buka Website</span>
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 2. 4 COMMAND PILLARS KPI CARDS (GRID 4 KOLOM) -->
        <!-- ========================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

            <!-- Pillar 1: Status Publikasi -->
            <div
                class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Status
                            Website</span>
                        <div
                            class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/80 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <i data-lucide="globe" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-xl sm:text-2xl font-black font-mono tracking-tight truncate"
                        :class="isPublished ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400'"
                        x-text="isPublished ? 'LIVE PUBLIK' : 'DRAFT OFFLINE'">
                        {{ $landingPage->is_published ? 'LIVE PUBLIK' : 'DRAFT OFFLINE' }}
                    </div>
                </div>
                <div
                    class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                    <span>Visibilitas Publik</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300"
                        x-text="isPublished ? 'Terbuka Untuk Umum' : 'Hanya Pemilik'">
                        {{ $landingPage->is_published ? 'Terbuka Untuk Umum' : 'Hanya Pemilik' }}
                    </span>
                </div>
            </div>

            <!-- Pillar 2: Layanan & Produk Aktif -->
            <div
                class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Katalog
                            Layanan &amp; Menu</span>
                        <div
                            class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200/80 dark:border-blue-800/80 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i data-lucide="package" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div
                        class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                        <span x-text="services.length">0</span> Layanan
                    </div>
                </div>
                <div
                    class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                    <span>Sinkronisasi POS</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">
                        {{ $landingPage->show_pos_products ? $posProducts->count() . ' Produk Terhubung' : 'Non-Aktif' }}
                    </span>
                </div>
            </div>

            <!-- Pillar 3: Kredibilitas & Keunggulan -->
            <div
                class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Pilar
                            Nilai &amp; Kredibilitas</span>
                        <div
                            class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/80 flex items-center justify-center text-purple-600 dark:text-purple-400">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div
                        class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                        <span x-text="values.length">4</span> Pilar Nilai
                    </div>
                </div>
                <div
                    class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                    <span>Testimoni &amp; FAQ</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">
                        <span x-text="testimonials.length">0</span> Ulasan • <span x-text="faqs.length">0</span> FAQ
                    </span>
                </div>
            </div>

            <!-- Pillar 4: Dokumentasi Galeri -->
            <div
                class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Galeri
                            Foto Bisnis</span>
                        <div
                            class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/80 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <i data-lucide="images" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div
                        class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                        <span x-text="galleryItems.length + newGalleryUploads.length">0</span> / 20 Foto
                    </div>
                </div>
                <div
                    class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                    <span>Status Galeri</span>
                    <span class="font-bold"
                        :class="sectionVisibility.gallery ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'">
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

                <!-- SIDEBAR TABS NAVIGATION (3 KOLOM DESKTOP) -->
                <div class="lg:col-span-3 space-y-3">
                    <div
                        class="bg-white dark:bg-slate-900/90 rounded-2xl p-3 sm:p-4 border border-slate-200/90 dark:border-slate-800/90 shadow-xs lg:sticky lg:top-24 transition-colors">

                        <div
                            class="px-2 pb-2.5 mb-1 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                            <span
                                class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Navigasi
                                Studio CMS</span>
                            <span class="text-[10px] font-mono font-bold text-emerald-600 dark:text-emerald-400">11
                                Bagian</span>
                        </div>

                        <!-- Horizontal scroll on mobile / vertical list on desktop -->
                        <div class="flex lg:block gap-1.5 overflow-x-auto pb-2 lg:pb-0 snap-x snap-mandatory">
                            <template x-for="tab in tabs" :key="tab.id">
                                <button type="button" @click="activeTab = tab.id"
                                    class="w-auto lg:w-full shrink-0 snap-start flex items-center justify-between gap-2.5 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all text-left whitespace-nowrap mb-1 cursor-pointer"
                                    :class="activeTab === tab.id ?
                                        'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-200/90 dark:border-emerald-800/90 shadow-2xs' :
                                        'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white border border-transparent'">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <i :data-lucide="tab.icon" class="w-4 h-4 shrink-0"
                                            :class="activeTab === tab.id ? 'text-emerald-600 dark:text-emerald-400' :
                                                'text-slate-400 dark:text-slate-500'"></i>
                                        <span class="truncate" x-text="tab.label"></span>
                                    </div>
                                    <i x-show="activeTab === tab.id" data-lucide="chevron-right"
                                        class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0 hidden lg:block"></i>
                                </button>
                            </template>
                        </div>

                        <!-- Quick Direct Save in Sidebar -->
                        <div class="pt-3 mt-2 border-t border-slate-100 dark:border-slate-800/80 space-y-2">
                            <button type="submit" :disabled="saving"
                                class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50">
                                <i :data-lucide="saving ? 'loader-2' : 'save'" :class="saving ? 'animate-spin' : ''"
                                    class="w-4 h-4"></i>
                                <span x-text="saving ? 'Menyimpan...' : 'Simpan Perubahan'">Simpan Perubahan</span>
                            </button>
                            <p class="text-[10px] text-center"
                                :class="saveError ? 'text-rose-500 dark:text-rose-400 font-bold' :
                                    'text-slate-500 dark:text-slate-400'"
                                x-text="saveError || saveMessage || 'Perubahan disimpan ke sistem secara aman.'"></p>
                        </div>
                    </div>
                </div>

                <!-- STUDIO CONTENT AREA (9 KOLOM DESKTOP) -->
                <div class="lg:col-span-9 space-y-6">

                    <!-- ============================================================ -->
                    <!-- TAB 1: GENERAL / IDENTITAS & WARNA TEMA                       -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'general'" x-cloak class="space-y-6">
                        <div
                            class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-3">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <i data-lucide="palette"
                                            class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                        <span>1. Identitas Visual &amp; Warna Tema</span>
                                    </h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tentukan warna dominan dan
                                        karakter visual yang merepresentasikan identitas brand bisnis Anda.</p>
                                </div>
                                <span
                                    class="text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-xl border border-slate-200 dark:border-slate-700">
                                    Preset: <strong class="text-emerald-600 dark:text-emerald-400 uppercase font-mono"
                                        x-text="form.industry_preset"></strong>
                                </span>
                            </div>

                            <!-- Theme Color Picker -->
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">
                                    Palet Warna Aksen Utama
                                </label>
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <template x-for="color in themePresets" :key="color.hex">
                                        <button type="button" @click="form.theme_color = color.hex"
                                            :style="`background-color: ${color.hex}`"
                                            :class="form.theme_color === color.hex ?
                                                'ring-3 ring-emerald-500 ring-offset-2 ring-offset-white dark:ring-offset-slate-900 scale-110 shadow-md' :
                                                'opacity-80 hover:opacity-100 hover:scale-105'"
                                            class="w-8 h-8 rounded-full transition-all flex items-center justify-center text-white shadow-xs cursor-pointer"
                                            :title="color.label">
                                            <i x-show="form.theme_color === color.hex" data-lucide="check"
                                                class="w-3.5 h-3.5 font-black"></i>
                                        </button>
                                    </template>
                                    <div
                                        class="flex items-center gap-2 ml-2 px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl">
                                        <input type="color" x-model="form.theme_color"
                                            class="w-6 h-6 rounded-md cursor-pointer bg-transparent border-0 p-0"
                                            title="Pilih warna kustom">
                                        <code
                                            class="text-xs text-emerald-600 dark:text-emerald-400 font-mono font-bold uppercase"
                                            x-text="form.theme_color"></code>
                                    </div>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                                    Warna aksen ini akan diterapkan pada tombol utama, badge produk, ikon pilar, serta aksen
                                    teks judul pada website publik.
                                </p>
                            </div>

                            <!-- Dark Mode Toggle for Landing Page -->
                            <div
                                class="bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200/80 dark:border-slate-700/60 p-4 flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white">Tampilan Elegan Gelap
                                        (Dark Mode Publik)</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Aktifkan untuk
                                        memberikan nuansa mewah bertema gelap pada landing page publik bisnis Anda.</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="dark_mode" value="1"
                                        {{ $landingPage->dark_mode ? 'checked' : '' }} class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600">
                                    </div>
                                </label>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 2: HERO / BANNER UTAMA                                   -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'hero'" x-cloak class="space-y-6">
                        <div
                            class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-3">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <i data-lucide="sparkles"
                                            class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                        <span>2. Banner Utama (Hero Section)</span>
                                    </h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Bagian teratas yang
                                        pertama kali dilihat calon pelanggan saat membuka website.</p>
                                </div>
                                <label
                                    class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer bg-slate-50 dark:bg-slate-800/60 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <input type="checkbox" x-model="sectionVisibility.hero"
                                        class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500">
                                    <span>Tampilkan Section Hero</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Badge Pengumuman -->
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                        Badge Pengumuman Singkat
                                    </label>
                                    <input type="text" name="announcement_badge" x-model="form.announcement_badge"
                                        placeholder="🔥 Solusi Terpercaya Sejak 2018"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Muncul di atas judul
                                        besar sebagai pemikat perhatian pertama.</p>
                                </div>

                                <!-- Upload Logo -->
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                        Upload Logo Bisnis
                                    </label>
                                    <div x-show="previews.logo || form.logo_url" class="mb-2 flex items-center gap-3">
                                        <img :src="previews.logo || form.logo_url" alt="Logo Preview"
                                            class="w-12 h-12 rounded-xl object-contain bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 p-1">
                                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold"
                                            x-text="previews.logo ? 'Pratinjau logo baru dipilih' : 'Logo tersimpan aktif'"></span>
                                    </div>
                                    <input type="file" name="logo_image" accept="image/jpeg,image/png,image/webp"
                                        @change="handleImagePreview($event, 'logo')"
                                        class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:rounded-xl file:border-0 file:bg-emerald-50 dark:file:bg-emerald-950/60 file:px-3 file:py-2 file:text-emerald-700 dark:file:text-emerald-300 file:font-bold file:cursor-pointer">
                                    <label
                                        class="mt-2 flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                                        <input type="checkbox" name="remove_logo_image" value="1"
                                            class="rounded border-slate-300 dark:border-slate-700 text-rose-600">
                                        <span>Hapus logo tersimpan</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Headline Utama -->
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                    Headline Utama (Judul Besar) <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="headline" x-model="form.headline"
                                    placeholder="Solusi Layanan & Produk Terpercaya untuk Kebutuhan Anda"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-sm font-bold text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                            </div>

                            <!-- Subheadline -->
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                    Subheadline / Deskripsi Pengantar
                                </label>
                                <textarea name="subheadline" x-model="form.subheadline" rows="3"
                                    placeholder="Jelaskan proposisi nilai, keunikan, dan jaminan kepuasan pelanggan secara ringkas..."
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition resize-none"></textarea>
                            </div>

                            <!-- CTA Buttons Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                        Teks Tombol Utama (Panggilan Aksi)
                                    </label>
                                    <input type="text" name="cta_primary_text" x-model="form.cta_primary_text"
                                        placeholder="Pesan via WhatsApp Sekarang"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                        URL Tujuan Tombol Utama
                                    </label>
                                    <input type="text" name="cta_primary_url" x-model="form.cta_primary_url"
                                        placeholder="Kosongkan untuk otomatis ke WhatsApp"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                        Teks Tombol Sekunder (Katalog)
                                    </label>
                                    <input type="text" name="cta_secondary_text" x-model="form.cta_secondary_text"
                                        placeholder="Lihat Daftar Layanan &amp; Harga"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                        URL Tujuan Tombol Sekunder
                                    </label>
                                    <input type="text" name="cta_secondary_url" x-model="form.cta_secondary_url"
                                        placeholder="#layanan atau URL tujuan"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                                </div>
                            </div>

                            <!-- Upload Hero Image -->
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                    Upload Foto Banner Hero (Opsional)
                                </label>
                                <div x-show="previews.hero || form.hero_image_url" class="mb-2 flex items-center gap-3">
                                    <img :src="previews.hero || form.hero_image_url" alt="Hero Preview"
                                        class="w-32 h-20 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-xs">
                                    <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold"
                                        x-text="previews.hero ? 'Pratinjau foto hero baru dipilih' : 'Foto hero aktif tersimpan'"></span>
                                </div>
                                <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp"
                                    @change="handleImagePreview($event, 'hero')"
                                    class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:rounded-xl file:border-0 file:bg-emerald-50 dark:file:bg-emerald-950/60 file:px-3 file:py-2 file:text-emerald-700 dark:file:text-emerald-300 file:font-bold file:cursor-pointer">
                                <label class="mt-2 flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                                    <input type="checkbox" name="remove_hero_image" value="1"
                                        class="rounded border-slate-300 dark:border-slate-700 text-rose-600">
                                    <span>Hapus foto hero tersimpan</span>
                                </label>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Maks. 4MB. Format JPG, PNG,
                                    atau WebP. Resolusi ideal 1200x800px.</p>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 3: ABOUT / PROFIL, KEUNGGULAN & JAM BUKA                 -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'about'" x-cloak class="space-y-6">

                        <!-- Profil Bisnis & Story Card -->
                        <div
                            class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-5 transition-colors">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-3">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <i data-lucide="book-open"
                                            class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                        <span>3. Profil, Cerita &amp; Kredibilitas Bisnis</span>
                                    </h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Bangun kepercayaan
                                        pelanggan melalui transparansi sejarah, reputasi, dan standar kualitas.</p>
                                </div>
                                <label
                                    class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer bg-slate-50 dark:bg-slate-800/60 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <input type="checkbox" x-model="sectionVisibility.about"
                                        class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500">
                                    <span>Tampilkan Section Profil</span>
                                </label>
                            </div>

                            <!-- Gambar About -->
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                    Upload Foto Profil / Tempat Usaha (Opsional)
                                </label>
                                <div x-show="previews.about || form.about_image_url" class="mb-2 flex items-center gap-3">
                                    <img :src="previews.about || form.about_image_url" alt="About Preview"
                                        class="w-32 h-20 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-xs">
                                    <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold"
                                        x-text="previews.about ? 'Pratinjau foto profil baru dipilih' : 'Foto profil aktif tersimpan'"></span>
                                </div>
                                <input type="file" name="about_image" accept="image/jpeg,image/png,image/webp"
                                    @change="handleImagePreview($event, 'about')"
                                    class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:rounded-xl file:border-0 file:bg-emerald-50 dark:file:bg-emerald-950/60 file:px-3 file:py-2 file:text-emerald-700 dark:file:text-emerald-300 file:font-bold file:cursor-pointer">
                                <label class="mt-2 flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                                    <input type="checkbox" name="remove_about_image" value="1"
                                        class="rounded border-slate-300 dark:border-slate-700 text-rose-600">
                                    <span>Hapus foto profil tersimpan</span>
                                </label>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                    Judul Bagian Profil / About
                                </label>
                                <input type="text" x-model="form.about_title"
                                    placeholder="Dedikasi Kami untuk Kualitas &amp; Kepuasan Pelanggan"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                    Cerita / Sejarah / Filosofi Usaha
                                </label>
                                <textarea x-model="form.about_story" rows="4"
                                    placeholder="Ceritakan bagaimana usaha Anda beroperasi, komitmen kualitas, keahlian tim, serta garansi yang diberikan..."
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition resize-none"></textarea>
                            </div>

                            <!-- 3 STATS KREDIBILITAS -->
                            <div class="border-t border-slate-100 dark:border-slate-800/80 pt-4">
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-3 uppercase tracking-wider">
                                    3 Angka Metrik Kredibilitas Usaha
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div
                                        class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 rounded-xl p-3.5 space-y-2">
                                        <label
                                            class="text-[10px] text-slate-500 dark:text-slate-400 uppercase font-bold tracking-wider">Metrik
                                            1 (Pelanggan / Volume)</label>
                                        <input type="text" name="stats[clients]"
                                            value="{{ data_get($landingPage->values, 'stats.clients', '5.000+') }}"
                                            placeholder="5.000+"
                                            class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-base font-black font-mono text-emerald-600 dark:text-emerald-400 focus:outline-hidden text-center">
                                        <input type="text" name="stats[clients_label]" value="Pelanggan Puas"
                                            placeholder="Label Pelanggan"
                                            class="w-full px-3 py-1 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-700 dark:text-slate-300 focus:outline-hidden text-center">
                                    </div>
                                    <div
                                        class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 rounded-xl p-3.5 space-y-2">
                                        <label
                                            class="text-[10px] text-slate-500 dark:text-slate-400 uppercase font-bold tracking-wider">Metrik
                                            2 (Jam Terbang / Pengalaman)</label>
                                        <input type="text" name="stats[experience]" value="8+ Tahun"
                                            placeholder="8+ Tahun"
                                            class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-base font-black font-mono text-teal-600 dark:text-teal-400 focus:outline-hidden text-center">
                                        <input type="text" name="stats[experience_label]"
                                            value="Pengalaman Profesional" placeholder="Label Pengalaman"
                                            class="w-full px-3 py-1 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-700 dark:text-slate-300 focus:outline-hidden text-center">
                                    </div>
                                    <div
                                        class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 rounded-xl p-3.5 space-y-2">
                                        <label
                                            class="text-[10px] text-slate-500 dark:text-slate-400 uppercase font-bold tracking-wider">Metrik
                                            3 (Kepuasan / Review)</label>
                                        <input type="text" name="stats[rating]" value="4.9 / 5.0"
                                            placeholder="4.9 / 5.0"
                                            class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-base font-black font-mono text-amber-500 focus:outline-hidden text-center">
                                        <input type="text" name="stats[rating_label]" value="Rating Kepuasan Ulasan"
                                            placeholder="Label Rating"
                                            class="w-full px-3 py-1 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-700 dark:text-slate-300 focus:outline-hidden text-center">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- 4 VALUE PILLARS CARD -->
                        <div
                            class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-4 transition-colors">
                            <div class="border-b border-slate-100 dark:border-slate-800/80 pb-3">
                                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <i data-lucide="award" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
                                    <span>4 Pilar Keunggulan Utama Bisnis</span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Alasan kuat mengapa calon
                                    pelanggan harus memilih produk atau layanan Anda dibanding kompetitor.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <template x-for="(val, index) in values" :key="index">
                                    <div
                                        class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 rounded-xl p-4 space-y-2.5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center font-black text-xs shrink-0 font-mono"
                                                x-text="index + 1"></div>
                                            <input type="text" x-model="val.title"
                                                placeholder="Judul Pilar Keunggulan"
                                                class="flex-1 px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs font-bold text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-hidden">
                                            <select x-model="val.icon"
                                                class="px-2 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-emerald-600 dark:text-emerald-400 font-semibold focus:border-emerald-500 focus:outline-hidden">
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
                                            class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-700 dark:text-slate-300 focus:border-emerald-500 focus:outline-hidden resize-none"></textarea>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- OPERATIONAL HOURS CARD -->
                        <div
                            class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-4 transition-colors">
                            <div class="border-b border-slate-100 dark:border-slate-800/80 pb-3">
                                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <i data-lucide="clock" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                    <span>Jadwal &amp; Jam Operasional Outlet</span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Informasikan jam buka harian
                                    agar calon pelanggan tahu waktu terbaik untuk datang atau memesan.</p>
                            </div>

                            <div class="space-y-2">
                                <template x-for="(day, dIdx) in operationalHours" :key="dIdx">
                                    <div
                                        class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 rounded-xl">
                                        <div class="w-20 sm:w-24 text-xs font-bold text-slate-900 dark:text-white shrink-0"
                                            x-text="day.day"></div>
                                        <input type="text" x-model="day.hours" :disabled="!day.is_open"
                                            placeholder="08:00 - 17:00 WIB"
                                            :class="day.is_open ?
                                                'text-slate-800 dark:text-slate-200 bg-white dark:bg-slate-950 border-slate-200 dark:border-slate-800' :
                                                'text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-900/50 border-slate-200 dark:border-slate-800 italic'"
                                            class="flex-1 px-3 py-1.5 border rounded-lg text-xs focus:border-emerald-500 focus:outline-hidden transition">
                                        <label
                                            class="flex items-center gap-1.5 cursor-pointer text-xs shrink-0 select-none">
                                            <input type="checkbox" x-model="day.is_open"
                                                class="rounded border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500">
                                            <span
                                                :class="day.is_open ? 'text-emerald-600 dark:text-emerald-400 font-bold' :
                                                    'text-slate-400 dark:text-slate-500'"
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
                    <div x-show="activeTab === 'services'" x-cloak class="space-y-6">
                        <div
                            class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-3">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <i data-lucide="package"
                                            class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                        <span>4. Katalog Layanan, Menu &amp; Paket Produk</span>
                                    </h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tampilkan daftar layanan,
                                        menu unggulan, atau paket komersial dengan foto dan rincian harga transparan.</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <label
                                        class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer bg-slate-50 dark:bg-slate-800/60 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                        <input type="checkbox" x-model="sectionVisibility.services"
                                            class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500">
                                        <span>Tampilkan di Web</span>
                                    </label>
                                    <button type="button" @click="addService"
                                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition shadow-2xs flex items-center gap-1.5 shrink-0 cursor-pointer">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                        <span>Tambah Layanan</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Services Title & Subtitle -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Judul
                                        Section Layanan</label>
                                    <input type="text" name="services_title" x-model="form.services_title"
                                        placeholder="Layanan &amp; Produk Pilihan"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Subjudul
                                        / Deskripsi Pengantar</label>
                                    <input type="text" name="services_subtitle" x-model="form.services_subtitle"
                                        placeholder="Kualitas terbaik dan pelayanan prima untuk setiap pelanggan."
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                                </div>
                            </div>

                            <!-- Services Repeater List -->
                            <div class="space-y-3.5">
                                <template x-for="(service, sIdx) in services" :key="sIdx">
                                    <div
                                        class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 rounded-xl p-4 space-y-3">

                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-2 flex-1">
                                                <span
                                                    class="w-6 h-6 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center text-xs font-bold font-mono shrink-0"
                                                    x-text="sIdx + 1"></span>
                                                <input type="text" x-model="service.title"
                                                    placeholder="Nama Layanan / Menu / Paket"
                                                    class="flex-1 px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs font-bold text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-hidden">
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input type="text" x-model="service.price" placeholder="Rp 150.000"
                                                    class="w-full sm:w-36 px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs font-bold font-mono text-emerald-600 dark:text-emerald-400 focus:border-emerald-500 focus:outline-hidden text-right">
                                                <button type="button" @click="removeService(sIdx)"
                                                    class="text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer"
                                                    title="Hapus Layanan">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            <div class="md:col-span-2">
                                                <input type="text" x-model="service.description"
                                                    placeholder="Deskripsi ringkas, rincian termasuk, garansi pengerjaan..."
                                                    class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-700 dark:text-slate-300 focus:border-emerald-500 focus:outline-hidden">
                                            </div>
                                            <div>
                                                <input type="text" x-model="service.badge"
                                                    placeholder="Badge: Terpopuler / Promo"
                                                    class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs font-semibold text-amber-600 dark:text-amber-400 focus:border-emerald-500 focus:outline-hidden">
                                            </div>
                                        </div>

                                        <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/40">
                                            <label
                                                class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-1 uppercase tracking-wider">Foto
                                                Layanan</label>
                                            <div x-show="service.preview_url || service.image_url"
                                                class="mb-2 flex items-center gap-2">
                                                <img :src="service.preview_url || service.image_url" alt="Preview Layanan"
                                                    class="w-14 h-10 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shadow-2xs">
                                                <span
                                                    class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold"
                                                    x-text="service.preview_url ? 'Foto baru dipilih' : 'Foto tersimpan aktif'"></span>
                                            </div>
                                            <input type="file" :name="'service_images[' + sIdx + ']'"
                                                accept="image/jpeg,image/png,image/webp"
                                                @change="handleServiceImagePreview($event, sIdx)"
                                                class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 dark:file:bg-emerald-950/60 file:px-2.5 file:py-1.5 file:text-emerald-700 dark:file:text-emerald-300 file:font-semibold file:cursor-pointer">
                                            <label
                                                class="mt-1 flex items-center gap-2 text-[10px] text-slate-500 dark:text-slate-400">
                                                <input type="checkbox" :name="'remove_service_images[' + sIdx + ']'"
                                                    value="1"
                                                    class="rounded border-slate-300 dark:border-slate-700 text-rose-600">
                                                <span>Hapus foto layanan</span>
                                            </label>
                                        </div>

                                    </div>
                                </template>

                                <!-- Empty State Layanan -->
                                <div x-show="services.length === 0"
                                    class="p-8 text-center bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-dashed border-slate-200 dark:border-slate-800 space-y-2">
                                    <i data-lucide="package-open"
                                        class="w-8 h-8 text-slate-400 dark:text-slate-600 mx-auto"></i>
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Belum ada item layanan
                                        di katalog</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Klik "Tambah Layanan" di atas
                                        atau pilih template industri untuk mengisi katalog secara otomatis.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 5: PRODUCTS / INTEGRASI PRODUK KASIR POS                 -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'products'" x-cloak class="space-y-6">
                        <div
                            class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-3">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <i data-lucide="shopping-bag"
                                            class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                        <span>5. Sinkronisasi Produk Kasir POS</span>
                                    </h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tampilkan katalog produk
                                        master POS langsung di landing page lengkap dengan tombol pesan instan via WhatsApp.
                                    </p>
                                </div>
                                <label
                                    class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer bg-slate-50 dark:bg-slate-800/60 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <input type="checkbox" x-model="sectionVisibility.products"
                                        class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500">
                                    <span>Tampilkan Section Produk</span>
                                </label>
                            </div>

                            <!-- Toggle Sinkron POS -->
                            <div
                                class="bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200/80 dark:border-slate-700/60 p-4 flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <i data-lucide="refresh-cw"
                                            class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                        <span>Tampilkan Produk Aktif dari Kasir POS</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Produk aktif di POS
                                        akan otomatis tampil dengan harga terkini dan tombol pesan langsung via WhatsApp.
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="show_pos_products" value="1"
                                        {{ $landingPage->show_pos_products ? 'checked' : '' }} class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600">
                                    </div>
                                </label>
                            </div>

                            <!-- POS Products List Preview -->
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">
                                    Pratinjau Produk POS Terhubung (Maks. 8 Item Pertama)
                                </label>
                                @if ($posProducts->count() > 0)
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                        @foreach ($posProducts as $prod)
                                            <div
                                                class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 space-y-1 shadow-2xs">
                                                <div
                                                    class="flex items-center gap-1.5 text-xs font-bold text-slate-900 dark:text-white truncate">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                                    <span class="truncate">{{ $prod->name }}</span>
                                                </div>
                                                <div
                                                    class="text-xs font-black font-mono text-emerald-600 dark:text-emerald-400">
                                                    Rp
                                                    {{ number_format((float) ($prod->selling_price ?? 0), 0, ',', '.') }}
                                                </div>
                                                <div class="text-[10px] text-slate-400 truncate">
                                                    Stok: {{ (int) ($prod->current_stock ?? 0) }}
                                                    {{ $prod->unit ?? 'unit' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div
                                        class="p-6 text-center bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Belum ada produk POS yang
                                            aktif. Kelola produk di menu <a href="{{ route('products.index') }}"
                                                class="text-emerald-600 dark:text-emerald-400 font-bold underline">Master
                                                Data &gt; Produk</a>.</p>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 6: GALLERY / GALERI FOTO & SUASANA BISNIS                -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'gallery'" x-cloak class="space-y-6">
                        <div
                            class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-6 transition-colors">

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2
                                            class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                            <i data-lucide="images"
                                                class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                            <span>6. Galeri &amp; Suasana Tempat Usaha</span>
                                        </h2>
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800"
                                            x-text="(galleryItems.length + newGalleryUploads.length) + ' / 20 Foto'"></span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tampilkan suasana tempat,
                                        hasil karya, foto produk, dan kegiatan bisnis dalam grid modern dengan efek zoom dan
                                        caption.</p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <label
                                        class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer bg-slate-50 dark:bg-slate-800/60 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                        <input type="checkbox" x-model="sectionVisibility.gallery"
                                            class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500">
                                        <span>Tampilkan di Web</span>
                                    </label>
                                    <button type="button" @click="showAddUrlModal = true"
                                        class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer">
                                        <i data-lucide="link"
                                            class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                                        <span>URL</span>
                                    </button>
                                    <button type="button" @click="clearAllGallery()"
                                        x-show="galleryItems.length > 0 || newGalleryUploads.length > 0"
                                        class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span>Kosongkan</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Gallery Title & Subtitle -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Judul
                                        Bagian Galeri</label>
                                    <input type="text" name="gallery_title" x-model="form.gallery_title"
                                        maxlength="120" placeholder="Galeri &amp; Suasana Toko Kami"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Deskripsi
                                        / Subtitle Galeri</label>
                                    <textarea name="gallery_subtitle" x-model="form.gallery_subtitle" maxlength="500" rows="2"
                                        placeholder="Dokumentasi aktivitas, produk unggulan, dan kehangatan pelayanan kami."
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition resize-none"></textarea>
                                </div>
                            </div>

                            <!-- Upload Dropzone -->
                            <div
                                class="p-6 border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-emerald-500/60 dark:hover:border-emerald-500/60 bg-slate-50/60 dark:bg-slate-900/40 rounded-2xl text-center transition-all group relative">
                                <input type="file" multiple accept="image/jpeg,image/png,image/webp"
                                    @change="handleGalleryFiles($event)"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="flex flex-col items-center justify-center space-y-2 pointer-events-none">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                                    </div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white">
                                        <span
                                            class="text-emerald-600 dark:text-emerald-400 underline decoration-emerald-500/30 underline-offset-4">Klik
                                            untuk memilih foto</span> atau seret file ke area ini
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 max-w-md">
                                        Format didukung: JPG, PNG, atau WebP (maks. 4 MB per foto). Foto otomatis dipotong
                                        rasio persegi (*aspect-square*) yang rapi di website publik.
                                    </p>
                                </div>
                            </div>

                            <!-- Gallery Photos Grid -->
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <div
                                        class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                        Foto Galeri Aktif &amp; Antrean Upload:</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400"
                                        x-show="galleryItems.length + newGalleryUploads.length > 0">
                                        Arahkan kursor ke foto untuk melihat preview teks caption.
                                    </div>
                                </div>

                                <!-- Empty State -->
                                <div x-show="galleryItems.length === 0 && newGalleryUploads.length === 0"
                                    class="p-10 text-center bg-slate-50 dark:bg-slate-800/30 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 space-y-2">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center mx-auto">
                                        <i data-lucide="image-off" class="w-6 h-6"></i>
                                    </div>
                                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300">Belum ada foto di
                                        galeri</div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 max-w-md mx-auto">Unggah foto
                                        dokumentasi suasana toko atau terapkan template industri untuk menampilkan galeri
                                        menarik.</p>
                                </div>

                                <!-- Grid of Photos -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
                                    x-show="galleryItems.length > 0 || newGalleryUploads.length > 0">

                                    <!-- Existing Saved Photos -->
                                    <template x-for="(item, idx) in galleryItems" :key="'saved-' + idx">
                                        <div
                                            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-2.5 space-y-2 flex flex-col justify-between group shadow-2xs hover:border-emerald-500/50 transition-all">
                                            <div
                                                class="aspect-square rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 relative group/img flex items-center justify-center">
                                                <img :src="item.url" :alt="item.caption || 'Galeri Foto'"
                                                    class="w-full h-full object-cover group-hover/img:scale-105 transition-transform duration-500">

                                                <!-- Top Badges & Controls -->
                                                <div
                                                    class="absolute top-2 inset-x-2 flex items-center justify-between pointer-events-none">
                                                    <span
                                                        class="px-2 py-0.5 rounded-md bg-white/90 dark:bg-slate-950/80 text-[10px] font-mono font-bold text-slate-900 dark:text-emerald-400 border border-slate-200 dark:border-slate-700 shadow-xs"
                                                        x-text="'#' + (idx + 1)"></span>
                                                    <div class="flex items-center gap-1 pointer-events-auto">
                                                        <button type="button" @click="moveGalleryItem(idx, -1)"
                                                            :disabled="idx === 0" title="Geser ke kiri"
                                                            class="w-6 h-6 rounded-md bg-white/90 dark:bg-slate-950/80 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center border border-slate-200 dark:border-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition">
                                                            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                        <button type="button" @click="moveGalleryItem(idx, 1)"
                                                            :disabled="idx === galleryItems.length - 1"
                                                            title="Geser ke kanan"
                                                            class="w-6 h-6 rounded-md bg-white/90 dark:bg-slate-950/80 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center border border-slate-200 dark:border-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition">
                                                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                        <button type="button" @click="removeGalleryItem(idx)"
                                                            title="Hapus foto ini"
                                                            class="w-6 h-6 rounded-md bg-rose-600 hover:bg-rose-500 text-white flex items-center justify-center shadow-xs transition">
                                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Hover Caption Overlay -->
                                                <div
                                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover/img:opacity-100 transition-opacity duration-300 flex items-end p-3 pointer-events-none">
                                                    <p class="text-white text-[11px] font-semibold leading-snug drop-shadow"
                                                        x-text="item.caption || '(Tanpa caption)'"></p>
                                                </div>
                                            </div>

                                            <!-- Caption Input -->
                                            <div>
                                                <label
                                                    class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase tracking-wider">Caption
                                                    Foto</label>
                                                <input type="text" x-model="item.caption"
                                                    placeholder="Tulis caption foto..."
                                                    class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-emerald-500 focus:outline-hidden transition">
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Staged New Uploads -->
                                    <template x-for="(nItem, nIdx) in newGalleryUploads" :key="'new-' + nIdx">
                                        <div
                                            class="bg-white dark:bg-slate-900 border-2 border-emerald-500/50 rounded-2xl p-2.5 space-y-2 flex flex-col justify-between group shadow-sm transition-all">
                                            <div
                                                class="aspect-square rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-950 border border-emerald-500/30 relative group/img flex items-center justify-center">
                                                <img :src="nItem.preview" :alt="nItem.caption || 'Foto Baru'"
                                                    class="w-full h-full object-cover">

                                                <!-- Top Badges & Controls -->
                                                <div
                                                    class="absolute top-2 inset-x-2 flex items-center justify-between pointer-events-none">
                                                    <span
                                                        class="px-2 py-0.5 rounded-md bg-emerald-600 text-[10px] font-bold text-white shadow-xs">Foto
                                                        Baru</span>
                                                    <div class="flex items-center gap-1 pointer-events-auto">
                                                        <button type="button" @click="removeNewUpload(nIdx)"
                                                            title="Batal upload foto ini"
                                                            class="w-6 h-6 rounded-md bg-rose-600 hover:bg-rose-500 text-white flex items-center justify-center shadow transition cursor-pointer">
                                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div
                                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover/img:opacity-100 transition-opacity duration-300 flex items-end p-3 pointer-events-none">
                                                    <p class="text-white text-[11px] font-semibold leading-snug drop-shadow"
                                                        x-text="nItem.caption || '(Tanpa caption)'"></p>
                                                </div>
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-[10px] font-bold text-emerald-600 dark:text-emerald-400 mb-1 uppercase tracking-wider">Caption
                                                    Foto Baru</label>
                                                <input type="text" x-model="nItem.caption"
                                                    placeholder="Tulis caption foto..."
                                                    class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-emerald-500 focus:outline-hidden transition">
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
                    <div x-show="activeTab === 'testimonials'" x-cloak class="space-y-6">
                        <div
                            class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-3">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <i data-lucide="star" class="w-4 h-4 text-amber-500"></i>
                                        <span>7. Ulasan &amp; Testimoni Pelanggan</span>
                                    </h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Bukti kepuasan nyata dari
                                        pelanggan yang meningkatkan konversi dan rasa percaya calon pembeli baru.</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <label
                                        class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer bg-slate-50 dark:bg-slate-800/60 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                        <input type="checkbox" x-model="sectionVisibility.testimonials"
                                            class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500">
                                        <span>Tampilkan di Web</span>
                                    </label>
                                    <button type="button" @click="addTestimonial"
                                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition shadow-2xs flex items-center gap-1.5 shrink-0 cursor-pointer">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                        <span>Tambah Ulasan</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Testimonials Repeater -->
                            <div class="space-y-3.5">
                                <template x-for="(testi, tIdx) in testimonials" :key="tIdx">
                                    <div
                                        class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 rounded-xl p-4 space-y-3">
                                        <div class="flex items-center gap-3">
                                            <input type="text" x-model="testi.name" placeholder="Nama Pelanggan"
                                                class="flex-1 px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs font-bold text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-hidden">
                                            <input type="text" x-model="testi.role"
                                                placeholder="Kota / Profesi Pelanggan"
                                                class="flex-1 px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-600 dark:text-slate-400 focus:border-emerald-500 focus:outline-hidden">
                                            <span class="text-amber-500 text-xs font-bold font-mono whitespace-nowrap">★
                                                5.0</span>
                                            <button type="button" @click="removeTestimonial(tIdx)"
                                                class="text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer"
                                                title="Hapus Ulasan">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                        <textarea x-model="testi.comment" rows="2"
                                            placeholder="Ulasan pengalaman pelanggan yang autentik dan meyakinkan..."
                                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-700 dark:text-slate-300 focus:border-emerald-500 focus:outline-hidden resize-none"></textarea>
                                    </div>
                                </template>

                                <div x-show="testimonials.length === 0"
                                    class="p-8 text-center bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-dashed border-slate-200 dark:border-slate-800 space-y-2">
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Belum ada testimoni
                                        pelanggan</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Tambahkan testimoni positif
                                        dari pelanggan setia Anda untuk memperkuat reputasi toko.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 8: FAQ / TANYA JAWAB POPULER                             -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'faq'" x-cloak class="space-y-6">
                        <div
                            class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-3">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <i data-lucide="help-circle"
                                            class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                        <span>8. Tanya Jawab Populer (FAQ)</span>
                                    </h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Jawab pertanyaan yang
                                        paling sering ditanyakan untuk mempercepat keputusan pembelian calon pembeli.</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <label
                                        class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer bg-slate-50 dark:bg-slate-800/60 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                        <input type="checkbox" x-model="sectionVisibility.faq"
                                            class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500">
                                        <span>Tampilkan di Web</span>
                                    </label>
                                    <button type="button" @click="addFaq"
                                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition shadow-2xs flex items-center gap-1.5 shrink-0 cursor-pointer">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                        <span>Tambah FAQ</span>
                                    </button>
                                </div>
                            </div>

                            <!-- FAQ Repeater -->
                            <div class="space-y-3.5">
                                <template x-for="(faq, fIdx) in faqs" :key="fIdx">
                                    <div
                                        class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 rounded-xl p-4 space-y-2.5">
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-mono shrink-0">Q:</span>
                                            <input type="text" x-model="faq.q"
                                                placeholder="Pertanyaan (misal: Apakah melayani pengantaran atau booking jadwal?)"
                                                class="flex-1 px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs font-bold text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-hidden">
                                            <button type="button" @click="removeFaq(fIdx)"
                                                class="text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer"
                                                title="Hapus FAQ">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                        <div class="flex items-start gap-3">
                                            <span
                                                class="text-xs font-bold text-slate-400 dark:text-slate-500 font-mono shrink-0 mt-2">A:</span>
                                            <textarea x-model="faq.a" rows="2" placeholder="Jawaban yang jelas, ramah, dan solutif..."
                                                class="flex-1 px-3 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-700 dark:text-slate-300 focus:border-emerald-500 focus:outline-hidden resize-none"></textarea>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="faqs.length === 0"
                                    class="p-8 text-center bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-dashed border-slate-200 dark:border-slate-800 space-y-2">
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Belum ada pertanyaan
                                        FAQ</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Tambahkan daftar tanya jawab
                                        untuk menghemat waktu menjawab chat berulang dari pelanggan.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 9: CONTACT / KONTAK, WA, ALAMAT & SOSMED                 -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'contact'" x-cloak class="space-y-6">
                        <div
                            class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-3">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <i data-lucide="phone-call"
                                            class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                        <span>9. Kontak, WhatsApp &amp; Peta Lokasi</span>
                                    </h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Hubungkan pelanggan
                                        langsung ke nomor WhatsApp, telepon, email, dan rute navigasi Google Maps toko Anda.
                                    </p>
                                </div>
                                <label
                                    class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer bg-slate-50 dark:bg-slate-800/60 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <input type="checkbox" x-model="sectionVisibility.contact"
                                        class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500">
                                    <span>Tampilkan Section Kontak</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- WhatsApp & Phone -->
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                        Nomor WhatsApp Bisnis <span
                                            class="text-emerald-600 dark:text-emerald-400 font-mono">(Format:
                                            628xxx)</span>
                                    </label>
                                    <input type="text" name="whatsapp_number" x-model="form.whatsapp_number"
                                        placeholder="6281234567890"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition mb-3">

                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                        Nomor Telepon Toko / Kantor (Opsional)
                                    </label>
                                    <input type="text" name="custom_phone" x-model="form.custom_phone"
                                        placeholder="021-1234567 atau 081234..."
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                                </div>

                                <!-- Email & Alamat -->
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                        Email Resmi Usaha (Opsional)
                                    </label>
                                    <input type="email" name="custom_email" x-model="form.custom_email"
                                        placeholder="kontak@bisnisanda.com"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition mb-3">

                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                        Google Maps Embed URL
                                    </label>
                                    <input type="text" name="google_maps_embed_url"
                                        x-model="form.google_maps_embed_url"
                                        placeholder="https://www.google.com/maps/embed?pb=..."
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white font-mono placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Buka Google Maps &gt;
                                        Bagikan &gt; Sematkan peta &gt; salin tautan di dalam <code
                                            class="text-emerald-600 dark:text-emerald-400 font-bold">src="..."</code></p>
                                </div>
                            </div>

                            <!-- Pesan Sambutan WhatsApp -->
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                    Pesan Sambutan Otomatis WhatsApp
                                </label>
                                <textarea name="whatsapp_welcome_message" x-model="form.whatsapp_welcome_message" rows="2"
                                    placeholder="Halo, saya melihat halaman website bisnis Anda dan tertarik untuk bertanya lebih lanjut..."
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition resize-none"></textarea>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Teks ini otomatis terisi di
                                    kolom chat WhatsApp pembeli saat mengklik tombol pesan.</p>
                            </div>

                            <!-- Alamat Fisik Lengkap -->
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                    Alamat Fisik Toko / Kantor / Workshop
                                </label>
                                <textarea name="custom_address" x-model="form.custom_address" rows="2"
                                    placeholder="Jl. Sudirman No. 123, Kelurahan, Kecamatan, Kota/Kabupaten, Kode Pos"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition resize-none"></textarea>
                            </div>

                            <!-- SOCIAL MEDIA & MARKETPLACE LINKS -->
                            <div class="border-t border-slate-100 dark:border-slate-800/80 pt-4 space-y-3">
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                    Tautan Media Sosial &amp; Marketplace Toko
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    @foreach ([['key' => 'instagram', 'label' => '📸 Instagram', 'placeholder' => 'https://instagram.com/...'], ['key' => 'tiktok', 'label' => '🎵 TikTok', 'placeholder' => 'https://tiktok.com/@...'], ['key' => 'facebook', 'label' => '📘 Facebook', 'placeholder' => 'https://facebook.com/...'], ['key' => 'youtube', 'label' => '▶️ YouTube', 'placeholder' => 'https://youtube.com/...'], ['key' => 'tokopedia', 'label' => '🛒 Tokopedia', 'placeholder' => 'https://tokopedia.com/...'], ['key' => 'shopee', 'label' => '🛍️ Shopee', 'placeholder' => 'https://shopee.co.id/...']] as $social)
                                        <div>
                                            <label
                                                class="text-[10px] font-bold text-slate-600 dark:text-slate-400 block mb-1 uppercase tracking-wider">{{ $social['label'] }}</label>
                                            <input type="text" name="social_links[{{ $social['key'] }}]"
                                                value="{{ $landingPage->social_links[$social['key']] ?? '' }}"
                                                placeholder="{{ $social['placeholder'] }}"
                                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-emerald-500 focus:outline-hidden transition">
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 10: FOOTER / TATA LETAK & KONTEN FOOTER                  -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'footer'" x-cloak class="space-y-6">
                        <div
                            class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-5 transition-colors">

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-3">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <i data-lucide="panels-top-left"
                                            class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                        <span>10. Tata Letak &amp; Konten Footer</span>
                                    </h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Atur kolom navigasi,
                                        ringkasan brand, dan hak cipta di bagian bawah website.</p>
                                </div>
                                <label
                                    class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer bg-slate-50 dark:bg-slate-800/60 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <input type="checkbox" x-model="sectionVisibility.footer"
                                        class="rounded bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500">
                                    <span>Tampilkan Section Footer</span>
                                </label>
                            </div>

                            <!-- Grid Columns Visibility -->
                            <div
                                class="bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200/80 dark:border-slate-700/60 p-4 space-y-3">
                                <div class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                    Pilihan Kolom Footer</div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                                    <label
                                        class="flex items-center gap-2 p-2.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                        <input type="checkbox" x-model="sectionVisibility.footer_brand"
                                            class="rounded border-slate-300 dark:border-slate-700 text-emerald-600">
                                        <span>Kolom Brand &amp; Sosmed</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-2 p-2.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                        <input type="checkbox" x-model="sectionVisibility.footer_navigation"
                                            class="rounded border-slate-300 dark:border-slate-700 text-emerald-600">
                                        <span>Kolom Navigasi</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-2 p-2.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                        <input type="checkbox" x-model="sectionVisibility.footer_services"
                                            class="rounded border-slate-300 dark:border-slate-700 text-emerald-600">
                                        <span>Kolom Layanan</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-2 p-2.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                        <input type="checkbox" x-model="sectionVisibility.footer_contact"
                                            class="rounded border-slate-300 dark:border-slate-700 text-emerald-600">
                                        <span>Kolom Kontak</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Deskripsi
                                    Singkat Footer</label>
                                <textarea name="footer_description" x-model="form.footer_description" maxlength="1000" rows="3"
                                    placeholder="Deskripsi ringkas profil usaha untuk ditampilkan di bagian footer..."
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition resize-none"></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Judul
                                        Kolom Navigasi</label>
                                    <input type="text" name="footer_navigation_title"
                                        x-model="form.footer_navigation_title" maxlength="80" placeholder="Navigasi"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 transition">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Judul
                                        Kolom Layanan</label>
                                    <input type="text" name="footer_services_title"
                                        x-model="form.footer_services_title" maxlength="80" placeholder="Layanan Kami"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 transition">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Judul
                                        Kolom Kontak</label>
                                    <input type="text" name="footer_contact_title" x-model="form.footer_contact_title"
                                        maxlength="80" placeholder="Hubungi Kami"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 transition">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Teks
                                        Tombol Aksi Footer</label>
                                    <input type="text" name="footer_cta_text" x-model="form.footer_cta_text"
                                        maxlength="100" placeholder="Hubungi via WhatsApp"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 transition">
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Teks
                                    Copyright Footer</label>
                                <input type="text" name="footer_copyright" x-model="form.footer_copyright"
                                    maxlength="255"
                                    placeholder="© {{ date('Y') }} {{ $business->name }}. All rights reserved."
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 transition">
                            </div>

                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAB 11: SEO / OPTIMASI GOOGLE & META TAG                     -->
                    <!-- ============================================================ -->
                    <div x-show="activeTab === 'seo'" x-cloak class="space-y-6">
                        <div
                            class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6 space-y-5 transition-colors">

                            <div class="border-b border-slate-100 dark:border-slate-800/80 pb-3">
                                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <i data-lucide="search" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                    <span>11. Optimasi Google SEO &amp; Pratinjau Pencarian</span>
                                </h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tingkatkan peringkat halaman
                                    bisnis Anda di Google Search agar calon pembeli di sekitar Anda mudah menemukan toko.
                                </p>
                            </div>

                            <!-- Meta Title -->
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                    Judul Halaman Google (Meta Title)
                                </label>
                                <input type="text" name="meta_title" x-model="form.meta_title"
                                    placeholder="{{ $business->name }} — Layanan &amp; Produk Terpercaya"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                                <div class="flex justify-between text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                    <span>Panjang ideal: 50–60 karakter</span>
                                    <span
                                        :class="(form.meta_title || '').length > 60 ? 'text-rose-500 font-bold' :
                                            'text-slate-500'"
                                        x-text="(form.meta_title || '').length + ' / 60'"></span>
                                </div>
                            </div>

                            <!-- Meta Description -->
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                    Deskripsi Google (Meta Description)
                                </label>
                                <textarea name="meta_description" x-model="form.meta_description" rows="3"
                                    placeholder="Deskripsi ringkas bisnis Anda yang tampil di bawah judul pada hasil pencarian Google..."
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition resize-none"></textarea>
                                <div class="flex justify-between text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                    <span>Panjang ideal: 120–160 karakter</span>
                                    <span
                                        :class="(form.meta_description || '').length > 160 ? 'text-rose-500 font-bold' :
                                            'text-slate-500'"
                                        x-text="(form.meta_description || '').length + ' / 160'"></span>
                                </div>
                            </div>

                            <!-- Keywords -->
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                    Kata Kunci Relevan (Meta Keywords)
                                </label>
                                <input type="text" name="meta_keywords" x-model="form.meta_keywords" maxlength="500"
                                    placeholder="kuliner jakarta, servis motor, katering enak, toko grosir"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-emerald-500 transition">
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Pisahkan tiap kata kunci
                                    dengan tanda koma.</p>
                            </div>

                            <!-- OG Image -->
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                                    Upload Gambar Pratinjau Sosial (Open Graph / WhatsApp Share)
                                </label>
                                <div x-show="previews.og || form.og_image_url" class="mb-2 flex items-center gap-3">
                                    <img :src="previews.og || form.og_image_url" alt="OG Preview"
                                        class="w-32 h-16 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-2xs">
                                    <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold"
                                        x-text="previews.og ? 'Pratinjau OG baru dipilih' : 'Gambar OG tersimpan aktif'"></span>
                                </div>
                                <input type="file" name="og_image" accept="image/jpeg,image/png,image/webp"
                                    @change="handleImagePreview($event, 'og')"
                                    class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:rounded-xl file:border-0 file:bg-emerald-50 dark:file:bg-emerald-950/60 file:px-3 file:py-2 file:text-emerald-700 dark:file:text-emerald-300 file:font-bold file:cursor-pointer">
                                <label class="mt-2 flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                                    <input type="checkbox" name="remove_og_image" value="1"
                                        class="rounded border-slate-300 dark:border-slate-700 text-rose-600">
                                    <span>Hapus gambar OG tersimpan</span>
                                </label>
                            </div>

                            <!-- LIVE GOOGLE SEARCH SERP PREVIEW -->
                            <div
                                class="bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800 p-4 space-y-1.5 shadow-2xs">
                                <div
                                    class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-2 flex items-center gap-1.5">
                                    <i data-lucide="search" class="w-3.5 h-3.5 text-blue-600"></i>
                                    <span>Pratinjau Tampilan di Google Search</span>
                                </div>
                                <div class="text-xs text-slate-500 font-mono truncate">{{ $publicUrl }}</div>
                                <div class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer truncate leading-snug"
                                    x-text="form.meta_title || '{{ addslashes($business->name) }} — Layanan & Produk Terpercaya'">
                                </div>
                                <div class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed"
                                    style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"
                                    x-text="form.meta_description || 'Kunjungi halaman profil dan katalog layanan resmi kami. Hubungi langsung melalui WhatsApp.'">
                                </div>
                            </div>

                            <!-- Schema Markup Info Banner -->
                            <div
                                class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl flex items-start gap-2.5">
                                <i data-lucide="check-circle-2"
                                    class="w-4 h-4 text-emerald-600 dark:text-emerald-400 mt-0.5 shrink-0"></i>
                                <div class="text-xs text-emerald-800 dark:text-emerald-300 leading-relaxed">
                                    <strong class="text-emerald-950 dark:text-white">Schema Markup Otomatis:</strong> Cooca
                                    secara otomatis menyematkan data terstruktur JSON-LD <code
                                        class="font-mono bg-emerald-100 dark:bg-emerald-900/60 px-1 py-0.5 rounded text-emerald-900 dark:text-emerald-200">LocalBusiness</code>
                                    agar Google Maps dan pencarian lokal mengenali bisnis Anda secara akurat.
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <!-- ========================================== -->
            <!-- 4. STICKY ACTION BAR (FORM FOOTER DUAL-THEME) -->
            <!-- ========================================== -->
            <div
                class="fixed bottom-0 inset-x-0 lg:left-72 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200/90 dark:border-slate-800/90 px-4 sm:px-8 py-3 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-3 transition-colors">
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-2 h-2 rounded-full"
                        :class="saving ? 'bg-amber-500 animate-ping' : 'bg-emerald-500'"></span>
                    <span class="font-bold text-slate-800 dark:text-slate-200"
                        x-text="saving ? 'Menyimpan perubahan ke server...' : (saveMessage || 'Siap menyimpan perubahan.')"></span>
                </div>

                <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                    <button type="button" @click="openPreview()" :disabled="saving"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition cursor-pointer flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                        <i data-lucide="monitor-play" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                        <span>Preview Live</span>
                    </button>

                    <button type="submit" :disabled="saving"
                        class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2 flex-1 sm:flex-none disabled:opacity-50">
                        <i :data-lucide="saving ? 'loader-2' : 'save'" :class="saving ? 'animate-spin' : ''"
                            class="w-4 h-4"></i>
                        <span x-text="saving ? 'Menyimpan...' : 'Simpan Semua Perubahan'">Simpan Semua Perubahan</span>
                    </button>
                </div>
            </div>

        </form>

        <!-- ============================================================ -->
        <!-- MODAL: LIVE PREVIEW DRAWER                                   -->
        <!-- ============================================================ -->
        <div x-show="showPreview" x-transition.opacity
            class="fixed inset-0 z-[60] bg-slate-950/80 backdrop-blur-sm p-3 sm:p-6" style="display: none;" x-cloak>
            <div
                class="h-full max-w-6xl mx-auto bg-white dark:bg-slate-900 rounded-2xl overflow-hidden shadow-2xl flex flex-col border border-slate-200 dark:border-slate-800">
                <div
                    class="shrink-0 px-4 sm:px-6 py-3 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="monitor-play" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                        <span class="text-xs font-bold text-slate-900 dark:text-white">Pratinjau Langsung (Live
                            Preview)</span>
                        <span class="text-[10px] text-slate-500 font-mono hidden sm:inline">{{ $publicUrl }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="previewUrl" target="_blank"
                            class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                            <span>Buka Tab Baru</span>
                            <i data-lucide="external-link" class="w-3 h-3"></i>
                        </a>
                        <button type="button" @click="showPreview = false"
                            class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 transition"
                            title="Tutup preview">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <iframe :src="previewUrl" title="Preview halaman publik bisnis"
                    class="w-full h-full border-0 bg-white"></iframe>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: 20 INDUSTRY PRESETS SELECTOR                          -->
        <!-- ============================================================ -->
        <div x-show="showPresetModal" x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
            style="display:none;" x-cloak>
            <div @click.away="showPresetModal = false"
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-5xl w-full max-h-[88vh] flex flex-col shadow-2xl overflow-hidden">

                <!-- Modal Header -->
                <div
                    class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-950/50">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 flex items-center justify-center text-amber-600 dark:text-amber-400 font-black shadow-xs">
                            <i data-lucide="sparkles" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">Template 20 Sektor Industri
                                Bisnis Indonesia</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Pilih sektor usaha Anda untuk mengisi
                                konten, headline, layanan, FAQ, dan ulasan dalam 1 klik.</p>
                        </div>
                    </div>
                    <button type="button" @click="showPresetModal = false"
                        class="p-1.5 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Modal Body: 20 Industry Cards -->
                <div class="p-5 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
                        @foreach ($industries as $ind)
                            <div
                                class="p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/60 hover:border-emerald-500/50 hover:bg-emerald-50/30 dark:hover:bg-slate-800/50 transition-all group flex flex-col justify-between shadow-2xs">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-2xl">{{ $ind['icon'] }}</span>
                                        <span
                                            class="w-3.5 h-3.5 rounded-full border-2 border-white dark:border-slate-900 shadow-xs"
                                            style="background-color: {{ $ind['theme_color'] }}"></span>
                                    </div>
                                    <h4
                                        class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition leading-tight mb-1">
                                        {{ $ind['name'] }}</h4>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed"
                                        style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $ind['description'] }}</p>
                                </div>
                                <div class="mt-3 pt-2.5 border-t border-slate-200/80 dark:border-slate-800/80">
                                    <button type="button"
                                        @click="applyPreset('{{ $ind['id'] }}', $event.currentTarget)"
                                        class="w-full py-1.5 rounded-xl bg-slate-200/70 dark:bg-slate-800 hover:bg-emerald-600 dark:hover:bg-emerald-500 text-slate-800 dark:text-slate-200 hover:text-white dark:hover:text-slate-950 text-[11px] font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                        <i data-lucide="zap" class="w-3 h-3"></i>
                                        <span>Terapkan</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 flex flex-col sm:flex-row justify-between items-center gap-2">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">⚡ Penerapan template akan menyelaraskan teks
                        pengantar, 4 pilar nilai, layanan, FAQ, dan ulasan sesuai industri pilihan.</p>
                    <button type="button" @click="showPresetModal = false"
                        class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: ADD PHOTO BY URL                                      -->
        <!-- ============================================================ -->
        <div x-show="showAddUrlModal" x-cloak
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-2xl space-y-4"
                @click.outside="showAddUrlModal = false">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="link" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                        <span>Tambah Foto Galeri via URL</span>
                    </h3>
                    <button type="button" @click="showAddUrlModal = false"
                        class="text-slate-400 hover:text-slate-700 dark:hover:text-white p-1">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="space-y-3">
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">URL
                            Gambar <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="newUrlInput"
                            placeholder="https://example.com/foto.jpg atau /storage/..."
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-emerald-500 focus:outline-hidden">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Caption
                            Foto (Opsional)</label>
                        <input type="text" x-model="newCaptionInput"
                            placeholder="Contoh: Suasana Ruang Tunggu dan Kasir"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-emerald-500 focus:outline-hidden">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showAddUrlModal = false"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Batal</button>
                    <button type="button" @click="addGalleryByUrl()" :disabled="!newUrlInput.trim()"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold disabled:opacity-50 transition cursor-pointer">Tambahkan</button>
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

                tabs: [{
                        id: 'general',
                        label: '1. Identitas & Tema',
                        icon: 'palette'
                    },
                    {
                        id: 'hero',
                        label: '2. Banner Hero',
                        icon: 'sparkles'
                    },
                    {
                        id: 'about',
                        label: '3. Profil & Nilai',
                        icon: 'book-open'
                    },
                    {
                        id: 'services',
                        label: '4. Katalog Layanan',
                        icon: 'package'
                    },
                    {
                        id: 'products',
                        label: '5. Produk POS',
                        icon: 'shopping-bag'
                    },
                    {
                        id: 'gallery',
                        label: '6. Galeri Suasana',
                        icon: 'images'
                    },
                    {
                        id: 'testimonials',
                        label: '7. Ulasan Pelanggan',
                        icon: 'star'
                    },
                    {
                        id: 'faq',
                        label: '8. Tanya Jawab (FAQ)',
                        icon: 'help-circle'
                    },
                    {
                        id: 'contact',
                        label: '9. Kontak & Lokasi',
                        icon: 'phone-call'
                    },
                    {
                        id: 'footer',
                        label: '10. Konten Footer',
                        icon: 'panels-top-left'
                    },
                    {
                        id: 'seo',
                        label: '11. Optimasi Google SEO',
                        icon: 'search'
                    },
                ],

                themePresets: [{
                        hex: '#10B981',
                        label: 'Emerald'
                    },
                    {
                        hex: '#0EA5E9',
                        label: 'Sky'
                    },
                    {
                        hex: '#6366F1',
                        label: 'Indigo'
                    },
                    {
                        hex: '#8B5CF6',
                        label: 'Violet'
                    },
                    {
                        hex: '#EC4899',
                        label: 'Pink'
                    },
                    {
                        hex: '#F59E0B',
                        label: 'Amber'
                    },
                    {
                        hex: '#EF4444',
                        label: 'Red'
                    },
                    {
                        hex: '#14B8A6',
                        label: 'Teal'
                    },
                    {
                        hex: '#64748B',
                        label: 'Slate'
                    },
                ],

                sectionVisibility: @json($sectionVisibilityDefaults),

                form: {
                    industry_preset: @json($landingPage->industry_preset ?? 'retail'),
                    theme_color: @json($landingPage->theme_color ?? '#10B981'),
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
                        this.values = [{
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
                                title: 'Konsultasi Gratis',
                                description: 'Dapatkan rekomendasi terbaik dari tim ahli kami.',
                                icon: 'star'
                            }
                        ];
                    }
                    if (!this.operationalHours || this.operationalHours.length === 0) {
                        this.operationalHours = [{
                                day: 'Senin',
                                hours: '08:00 - 17:00 WIB',
                                is_open: true
                            },
                            {
                                day: 'Selasa',
                                hours: '08:00 - 17:00 WIB',
                                is_open: true
                            },
                            {
                                day: 'Rabu',
                                hours: '08:00 - 17:00 WIB',
                                is_open: true
                            },
                            {
                                day: 'Kamis',
                                hours: '08:00 - 17:00 WIB',
                                is_open: true
                            },
                            {
                                day: 'Jumat',
                                hours: '08:00 - 17:00 WIB',
                                is_open: true
                            },
                            {
                                day: 'Sabtu',
                                hours: '08:00 - 15:00 WIB',
                                is_open: true
                            },
                            {
                                day: 'Minggu',
                                hours: 'Tutup',
                                is_open: false
                            },
                        ];
                    }
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
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
                                confirmButtonColor: '#ef4444'
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
                                confirmButtonColor: '#ef4444'
                            });
                        }
                        event.target.value = '';
                        return;
                    }
                    this.previews[type] = URL.createObjectURL(file);
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
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
                                confirmButtonColor: '#ef4444'
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
                                confirmButtonColor: '#ef4444'
                            });
                        }
                        event.target.value = '';
                        return;
                    }
                    this.services[sIdx].preview_url = URL.createObjectURL(file);
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
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
                                    confirmButtonColor: '#ef4444'
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
                                    confirmButtonColor: '#ef4444'
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
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
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
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                removeGalleryItem(index) {
                    this.galleryItems.splice(index, 1);
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                removeNewUpload(index) {
                    this.newGalleryUploads.splice(index, 1);
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                moveGalleryItem(index, direction) {
                    const targetIndex = index + direction;
                    if (targetIndex < 0 || targetIndex >= this.galleryItems.length) return;
                    const item = this.galleryItems.splice(index, 1)[0];
                    this.galleryItems.splice(targetIndex, 0, item);
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
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
                            confirmButtonColor: '#ef4444',
                            cancelButtonColor: '#64748b'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.galleryItems = [];
                                this.newGalleryUploads = [];
                                this.$nextTick(() => {
                                    if (typeof lucide !== 'undefined') lucide.createIcons();
                                });
                            }
                        });
                    } else {
                        this.galleryItems = [];
                        this.newGalleryUploads = [];
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
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
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                removeService(index) {
                    this.services.splice(index, 1);
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                addTestimonial() {
                    this.testimonials.push({
                        name: '',
                        role: 'Pelanggan',
                        comment: '',
                        rating: 5
                    });
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                removeTestimonial(index) {
                    this.testimonials.splice(index, 1);
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                addFaq() {
                    this.faqs.push({
                        q: '',
                        a: ''
                    });
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                removeFaq(index) {
                    this.faqs.splice(index, 1);
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
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
                            confirmButtonColor: '#10b981',
                            cancelButtonColor: '#64748b'
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
                                this.$nextTick(() => {
                                    if (typeof lucide !== 'undefined') lucide.createIcons();
                                });
                            }
                        })
                        .catch(error => {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: error.message || 'Gagal menerapkan template. Silakan coba kembali.',
                                    confirmButtonColor: '#ef4444'
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
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
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
                                    confirmButtonColor: '#f59e0b'
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
                            if (data.landing_page.hero_image_url) this.form.hero_image_url = data.landing_page
                                .hero_image_url;
                            if (data.landing_page.logo_url) this.form.logo_url = data.landing_page.logo_url;
                            if (data.landing_page.about_image_url) this.form.about_image_url = data.landing_page
                                .about_image_url;
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
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
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
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    } finally {
                        this.saving = false;
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                    }
                }
            };
        }
    </script>
@endsection
