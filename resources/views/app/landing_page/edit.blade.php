@extends('layouts.app')

@section('title', 'Website & Landing Page Bisnis — Cooca')

@section('content')
<div x-data="landingPageEditor()" class="space-y-6 max-w-7xl mx-auto pb-20">

    <!-- TOP BAR / HEADER -->
    <div class="glass-card rounded-2xl p-5 md:p-6 border border-slate-800 relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 font-black shadow-lg shadow-emerald-500/20">
                        <i data-lucide="globe" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-black text-white tracking-tight">Website & Landing Page Bisnis</h1>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $landingPage->is_published ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                                <span class="w-2 h-2 rounded-full {{ $landingPage->is_published ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500' }}"></span>
                                {{ $landingPage->is_published ? 'Publik Aktif' : 'Draft / Disembunyikan' }}
                            </span>
                        </div>
                        <p class="text-xs md:text-sm text-slate-400 mt-0.5">Kelola halaman profil, katalog produk, dan kontak online bisnis Anda untuk 20+ sektor industri.</p>
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" @click="showPresetModal = true"
                    class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-amber-500/20 to-orange-500/20 hover:from-amber-500/30 hover:to-orange-500/30 text-amber-300 border border-amber-500/30 text-xs font-bold flex items-center gap-2 transition-all shadow-sm">
                    <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
                    <span>Pilih Template Industri (20)</span>
                </button>

                <form action="{{ route('landing-page.toggle-publish') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold border flex items-center gap-2 transition-all {{ $landingPage->is_published ? 'bg-rose-500/15 hover:bg-rose-500/25 text-rose-300 border-rose-500/30' : 'bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-300 border-emerald-500/30' }}">
                        <i data-lucide="{{ $landingPage->is_published ? 'eye-off' : 'eye' }}" class="w-4 h-4"></i>
                        <span>{{ $landingPage->is_published ? 'Nonaktifkan Publik' : 'Terbitkan Sekarang' }}</span>
                    </button>
                </form>

                <a href="{{ $publicUrl }}" target="_blank"
                    class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-extrabold flex items-center gap-2 transition-all shadow-lg shadow-emerald-500/20">
                    <span>Lihat Halaman Publik</span>
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- PUBLIC URL BANNER -->
        <div class="mt-4 pt-4 border-t border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2 overflow-hidden">
                <span class="text-slate-400 shrink-0 font-medium">Link Publik Anda:</span>
                <a href="{{ $publicUrl }}" target="_blank" class="text-emerald-400 hover:underline font-mono truncate max-w-md">
                    {{ $publicUrl }}
                </a>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="copyLink('{{ $publicUrl }}')"
                    class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold flex items-center gap-1.5 transition">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                    <span x-text="copied ? 'Tersalin!' : 'Salin Link'">Salin Link</span>
                </button>
                <a href="https://api.whatsapp.com/send?text={{ urlencode('Halo, kunjungi profil dan katalog layanan kami di: ' . $publicUrl) }}" target="_blank"
                    class="px-2.5 py-1 rounded-lg bg-[#25D366]/20 hover:bg-[#25D366]/30 text-[#25D366] text-xs font-semibold flex items-center gap-1.5 transition">
                    <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                    <span>Bagi ke WA</span>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 px-5 py-3.5 bg-emerald-500/15 border border-emerald-500/30 rounded-xl text-sm text-emerald-300 font-medium">
        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400 shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- MAIN FORM -->
    <form action="{{ route('landing-page.update') }}" method="POST" id="landingPageForm" @submit="prepareSubmission">
        @csrf
        @method('PUT')

        <!-- Hidden JSON inputs synced via Alpine -->
        <input type="hidden" name="values_json" :value="JSON.stringify(values)">
        <input type="hidden" name="custom_services_json" :value="JSON.stringify(services)">
        <input type="hidden" name="faqs_json" :value="JSON.stringify(faqs)">
        <input type="hidden" name="testimonials_json" :value="JSON.stringify(testimonials)">
        <input type="hidden" name="operational_hours_json" :value="JSON.stringify(operationalHours)">
        <input type="hidden" name="industry_preset" x-model="form.industry_preset">
        <input type="hidden" name="theme_color" x-model="form.theme_color">
        <input type="hidden" name="announcement_badge" x-model="form.announcement_badge">
        <input type="hidden" name="headline" x-model="form.headline">
        <input type="hidden" name="subheadline" x-model="form.subheadline">
        <input type="hidden" name="cta_primary_text" x-model="form.cta_primary_text">
        <input type="hidden" name="cta_secondary_text" x-model="form.cta_secondary_text">
        <input type="hidden" name="hero_image_url" x-model="form.hero_image_url">
        <input type="hidden" name="logo_url" x-model="form.logo_url">
        <input type="hidden" name="about_title" x-model="form.about_title">
        <input type="hidden" name="about_story" x-model="form.about_story">
        <input type="hidden" name="whatsapp_number" x-model="form.whatsapp_number">
        <input type="hidden" name="whatsapp_default_message" x-model="form.whatsapp_default_message">
        <input type="hidden" name="custom_email" x-model="form.custom_email">
        <input type="hidden" name="custom_address" x-model="form.custom_address">
        <input type="hidden" name="google_maps_embed" x-model="form.google_maps_embed">
        <input type="hidden" name="meta_title" x-model="form.meta_title">
        <input type="hidden" name="meta_description" x-model="form.meta_description">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- SIDEBAR TABS NAVIGATION -->
            <div class="lg:col-span-3 space-y-2">
                <div class="glass-card rounded-2xl p-3 border border-slate-800 space-y-1 sticky top-6">
                    <p class="text-[10px] uppercase font-bold text-slate-500 tracking-wider px-2 pb-1">Editor Bagian</p>

                    <template x-for="tab in tabs" :key="tab.id">
                        <button type="button" @click="activeTab = tab.id"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all text-left"
                            :class="activeTab === tab.id ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white'">
                            <i :data-lucide="tab.icon" class="w-4 h-4" :class="activeTab === tab.id ? 'text-emerald-400' : 'text-slate-500'"></i>
                            <span x-text="tab.label"></span>
                        </button>
                    </template>

                    <div class="pt-3 mt-1 border-t border-slate-800 space-y-2">
                        <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black flex items-center justify-center gap-2 transition-all shadow-lg shadow-emerald-500/20">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Simpan Semua Perubahan</span>
                        </button>
                        <p class="text-[10px] text-slate-500 text-center">Perubahan tersimpan tidak otomatis dipublikasikan.</p>
                    </div>
                </div>
            </div>

            <!-- TAB CONTENTS -->
            <div class="lg:col-span-9 space-y-6">

                <!-- ============================================================ -->
                <!-- TAB 1: HERO & BRANDING -->
                <!-- ============================================================ -->
                <div x-show="activeTab === 'hero'" class="space-y-5">
                    <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-5">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                            <div>
                                <h2 class="text-base font-bold text-white">🎨 Identitas & Warna Tema</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Tentukan tampilan visual utama halaman bisnis Anda.</p>
                            </div>
                            <span class="text-xs font-semibold text-slate-400 bg-slate-900 px-3 py-1 rounded-lg border border-slate-800">
                                Preset: <strong class="text-emerald-400 uppercase" x-text="form.industry_preset"></strong>
                            </span>
                        </div>

                        <!-- THEME COLOR PICKER -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-2">Warna Utama Tema Halaman</label>
                            <div class="flex flex-wrap items-center gap-3">
                                <template x-for="color in themePresets" :key="color.hex">
                                    <button type="button" @click="form.theme_color = color.hex"
                                        :style="`background-color: ${color.hex}`"
                                        :class="form.theme_color === color.hex ? 'ring-2 ring-white ring-offset-2 ring-offset-slate-900 scale-110' : 'opacity-70 hover:opacity-100 hover:scale-105'"
                                        class="w-9 h-9 rounded-full transition-all flex items-center justify-center text-white shadow-md" :title="color.label">
                                        <i x-show="form.theme_color === color.hex" data-lucide="check" class="w-4 h-4 font-black"></i>
                                    </button>
                                </template>
                                <div class="flex items-center gap-2 ml-2 px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-xl">
                                    <input type="color" x-model="form.theme_color" class="w-7 h-7 rounded-md cursor-pointer bg-transparent border-0 p-0" title="Pilih warna kustom">
                                    <code class="text-xs text-emerald-400 font-mono uppercase" x-text="form.theme_color"></code>
                                </div>
                            </div>
                        </div>

                        <!-- DARK THEME TOGGLE -->
                        <div class="flex items-center justify-between p-4 bg-slate-900/60 rounded-xl border border-slate-800">
                            <div>
                                <div class="text-xs font-bold text-white">Tampilan Elegan Gelap (Dark Mode)</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">Latar gelap premium dengan glassmorphism & efek cahaya tema.</div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_dark_theme" value="1" {{ $landingPage->is_dark_theme ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Badge Pengumuman Singkat</label>
                                <input type="text" x-model="form.announcement_badge" placeholder="🔥 Solusi Terpercaya Sejak 2018"
                                    class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30 focus:outline-none transition">
                                <p class="text-[10px] text-slate-500 mt-1">Muncul di atas headline utama sebagai magnet perhatian pertama.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">URL Logo Bisnis</label>
                                <input type="url" x-model="form.logo_url" placeholder="https://contoh.com/logo.png"
                                    class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                                <p class="text-[10px] text-slate-500 mt-1">Kosongkan = memakai inisial nama bisnis otomatis.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Headline Utama (Judul Besar) <span class="text-rose-400">*</span></label>
                            <input type="text" x-model="form.headline" required placeholder="Bengkel Motor Terlengkap & Terpercaya di Jakarta"
                                class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-sm font-semibold text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Subheadline / Deskripsi Pengantar</label>
                            <textarea x-model="form.subheadline" rows="3" placeholder="Jelaskan keunikan dan keunggulan utama bisnis Anda untuk calon pelanggan..."
                                class="w-full px-3.5 py-2 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Teks Tombol Utama (Order/Pesan)</label>
                                <input type="text" x-model="form.cta_primary_text" placeholder="Pesan via WhatsApp Sekarang"
                                    class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Teks Tombol Sekunder (Katalog)</label>
                                <input type="text" x-model="form.cta_secondary_text" placeholder="Lihat Daftar Layanan & Harga"
                                    class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">URL Gambar Hero Banner (Opsional)</label>
                            <input type="url" x-model="form.hero_image_url" placeholder="https://images.unsplash.com/..."
                                class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                            <p class="text-[10px] text-slate-500 mt-1">Kosongkan = pakai ilustrasi abstrak otomatis dengan animasi partikel premium.</p>
                        </div>
                    </div>

                    <!-- 4 VALUE PILLARS -->
                    <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <div>
                                <h2 class="text-base font-bold text-white">🏆 4 Pilar Keunggulan Bisnis</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Alasan kuat mengapa pelanggan harus memilih Anda.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-for="(val, index) in values" :key="index">
                                <div class="p-4 bg-slate-900/70 border border-slate-800 rounded-xl space-y-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-black text-xs shrink-0" x-text="index + 1"></div>
                                        <input type="text" x-model="val.title" placeholder="Judul Keunggulan"
                                            class="flex-1 px-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs font-bold text-white focus:border-emerald-500 focus:outline-none">
                                    </div>
                                    <textarea x-model="val.description" rows="2" placeholder="Uraian singkat..."
                                        class="w-full px-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-300 focus:border-emerald-500 focus:outline-none resize-none"></textarea>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- TAB 2: LAYANAN & PRODUK -->
                <!-- ============================================================ -->
                <div x-show="activeTab === 'services'" class="space-y-5">
                    <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                            <div>
                                <h2 class="text-base font-bold text-white">📦 Katalog Layanan & Paket Produk</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Tampilkan jasa, menu, atau paket bisnis dengan harga dan deskripsi yang menarik.</p>
                            </div>
                            <button type="button" @click="addService"
                                class="px-4 py-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 text-xs font-bold flex items-center gap-2 transition shrink-0">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Tambah Layanan</span>
                            </button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(service, sIdx) in services" :key="sIdx">
                                <div class="p-4 bg-slate-900/80 border border-slate-800 rounded-xl space-y-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center gap-2 flex-1">
                                            <span class="w-6 h-6 rounded-md bg-slate-800 text-slate-400 flex items-center justify-center text-xs font-bold shrink-0" x-text="sIdx + 1"></span>
                                            <input type="text" x-model="service.title" placeholder="Nama Layanan / Menu / Paket"
                                                class="flex-1 px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs font-bold text-white focus:border-emerald-500 focus:outline-none">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="text" x-model="service.price" placeholder="Rp 150.000"
                                                class="w-36 px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs font-semibold text-emerald-400 focus:border-emerald-500 focus:outline-none text-right">
                                            <button type="button" @click="removeService(sIdx)"
                                                class="text-slate-500 hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-500/10 transition">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div class="md:col-span-2">
                                            <input type="text" x-model="service.description" placeholder="Deskripsi singkat, rincian termasuk, dll."
                                                class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-300 focus:border-emerald-500 focus:outline-none">
                                        </div>
                                        <div>
                                            <input type="text" x-model="service.badge" placeholder="Badge: Terpopuler / Baru"
                                                class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-amber-400 focus:border-emerald-500 focus:outline-none">
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div x-show="services.length === 0" class="p-8 text-center bg-slate-900/30 rounded-xl border border-dashed border-slate-800">
                                <i data-lucide="package-open" class="w-8 h-8 text-slate-600 mx-auto mb-2"></i>
                                <p class="text-xs text-slate-500">Belum ada layanan. Klik "Tambah Layanan" atau terapkan template industri.</p>
                            </div>
                        </div>

                        <!-- POS PRODUCTS INTEGRATION -->
                        <div class="mt-2 p-4 bg-slate-900/60 rounded-xl border border-emerald-900/40 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-white flex items-center gap-2">
                                        <i data-lucide="shopping-bag" class="w-4 h-4 text-emerald-400"></i>
                                        <span>Tampilkan Produk dari Kasir POS</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-1">Produk aktif dari POS ditampilkan otomatis dengan tombol pesan via WhatsApp 1-klik.</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="show_products" value="1" {{ $landingPage->show_products ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                            @if($posProducts->count() > 0)
                            <div class="flex flex-wrap gap-2 pt-1">
                                @foreach($posProducts as $prod)
                                <span class="px-2.5 py-1 rounded-lg bg-slate-950/70 border border-slate-800 text-[11px] text-slate-300 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                                    <strong>{{ $prod->name }}</strong>
                                    <span class="text-emerald-400 font-mono">Rp {{ number_format((float)($prod->selling_price ?? 0), 0, ',', '.') }}</span>
                                </span>
                                @endforeach
                            </div>
                            @else
                            <p class="text-[11px] text-slate-500">Belum ada produk POS aktif. Tambahkan produk di menu Master Data > Produk.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- TAB 3: PROFIL, STORY & JAM BUKA -->
                <!-- ============================================================ -->
                <div x-show="activeTab === 'story'" class="space-y-5">
                    <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-5">
                        <div class="border-b border-slate-800 pb-4">
                            <h2 class="text-base font-bold text-white">📖 Profil & Cerita Bisnis</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Bangun kepercayaan melalui transparansi sejarah dan standar kualitas bisnis.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Judul Bagian Profil / About</label>
                            <input type="text" x-model="form.about_title" placeholder="Dedikasi Kami untuk Kepuasan & Kualitas Terbaik"
                                class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Cerita / Sejarah / Filosofi Bisnis</label>
                            <textarea x-model="form.about_story" rows="5" placeholder="Ceritakan bagaimana bisnis Anda berdiri, proses pengerjaan, garansi layanan, dan mengapa pelanggan dapat mempercayai Anda..."
                                class="w-full px-3.5 py-2 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition resize-none"></textarea>
                        </div>

                        <!-- STATS ROW -->
                        <div class="border-t border-slate-800 pt-4">
                            <label class="block text-xs font-semibold text-slate-300 mb-3">Statistik Kredibilitas (3 Angka Utama)</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="p-3 bg-slate-900/80 border border-slate-800 rounded-xl space-y-2">
                                    <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Stat 1</label>
                                    <input type="text" name="stats[clients]" value="{{ $landingPage->stats['clients'] ?? '5.000+' }}" placeholder="5.000+"
                                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-base font-black text-emerald-400 focus:outline-none text-center">
                                    <input type="text" name="stats[clients_label]" value="{{ $landingPage->stats['clients_label'] ?? 'Pelanggan Puas' }}" placeholder="Pelanggan Puas"
                                        class="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-400 focus:outline-none text-center">
                                </div>
                                <div class="p-3 bg-slate-900/80 border border-slate-800 rounded-xl space-y-2">
                                    <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Stat 2</label>
                                    <input type="text" name="stats[experience]" value="{{ $landingPage->stats['experience'] ?? '8+ Tahun' }}" placeholder="8+ Tahun"
                                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-base font-black text-teal-400 focus:outline-none text-center">
                                    <input type="text" name="stats[experience_label]" value="{{ $landingPage->stats['experience_label'] ?? 'Pengalaman Profesional' }}" placeholder="Pengalaman"
                                        class="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-400 focus:outline-none text-center">
                                </div>
                                <div class="p-3 bg-slate-900/80 border border-slate-800 rounded-xl space-y-2">
                                    <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Stat 3</label>
                                    <input type="text" name="stats[rating]" value="{{ $landingPage->stats['rating'] ?? '4.9 / 5.0' }}" placeholder="4.9 / 5.0"
                                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-base font-black text-amber-400 focus:outline-none text-center">
                                    <input type="text" name="stats[rating_label]" value="{{ $landingPage->stats['rating_label'] ?? 'Rating Kepuasan Ulasan' }}" placeholder="Rating Ulasan"
                                        class="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-400 focus:outline-none text-center">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- OPERATIONAL HOURS -->
                    <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-4">
                        <div class="border-b border-slate-800 pb-4">
                            <h2 class="text-base font-bold text-white">🕐 Jadwal & Jam Operasional</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Atur jam kerja agar pelanggan tahu waktu terbaik untuk berkunjung.</p>
                        </div>
                        <div class="space-y-2">
                            <template x-for="(day, dIdx) in operationalHours" :key="dIdx">
                                <div class="flex items-center gap-3 p-3 bg-slate-900/70 border border-slate-800 rounded-xl">
                                    <div class="w-24 text-xs font-bold text-white shrink-0" x-text="day.day"></div>
                                    <input type="text" x-model="day.hours" :disabled="!day.is_open" placeholder="08:00 - 17:00 WIB"
                                        :class="day.is_open ? 'text-slate-200 border-slate-800' : 'text-slate-600 border-slate-900 bg-slate-950/50 italic'"
                                        class="flex-1 px-3 py-1.5 bg-slate-950 border rounded-lg text-xs focus:border-emerald-500 focus:outline-none transition">
                                    <label class="flex items-center gap-1.5 cursor-pointer text-xs shrink-0 select-none">
                                        <input type="checkbox" x-model="day.is_open" class="rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-0 focus:ring-offset-0">
                                        <span :class="day.is_open ? 'text-emerald-400 font-bold' : 'text-slate-500'" x-text="day.is_open ? 'Buka' : 'Tutup'"></span>
                                    </label>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- TAB 4: KONTAK, WA & MAPS -->
                <!-- ============================================================ -->
                <div x-show="activeTab === 'contact'" class="space-y-5">
                    <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-5">
                        <div class="border-b border-slate-800 pb-4">
                            <h2 class="text-base font-bold text-white">📞 WhatsApp, Kontak & Lokasi</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Hubungkan pelanggan langsung ke WhatsApp, email, dan peta lokasi fisik bisnis.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">
                                    <span class="text-[#25D366]">Nomor WhatsApp Bisnis</span>
                                    <span class="text-slate-500 font-normal ml-1">(Format: 628xxx)</span>
                                </label>
                                <input type="text" x-model="form.whatsapp_number" placeholder="6281234567890"
                                    class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-[#25D366] focus:outline-none font-mono transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Email Resmi (Opsional)</label>
                                <input type="email" x-model="form.custom_email" placeholder="kontak@bisnisanda.com"
                                    class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Pesan Sambutan Otomatis WhatsApp</label>
                            <textarea x-model="form.whatsapp_default_message" rows="2" placeholder="Halo, saya melihat halaman Anda dan ingin bertanya lebih lanjut..."
                                class="w-full px-3.5 py-2 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none resize-none transition"></textarea>
                            <p class="text-[10px] text-slate-500 mt-1">Teks ini otomatis terisi di chat WA pembeli saat mengklik tombol WhatsApp.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Alamat Fisik Toko / Kantor</label>
                            <textarea x-model="form.custom_address" rows="2" placeholder="Jl. Sudirman No. 123, Kelurahan, Kecamatan, Kota, Kode Pos"
                                class="w-full px-3.5 py-2 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none resize-none transition"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Google Maps Embed URL</label>
                            <input type="text" x-model="form.google_maps_embed" placeholder="https://www.google.com/maps/embed?pb=..."
                                class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none font-mono transition">
                            <p class="text-[10px] text-slate-500 mt-1">Google Maps → Share → Embed a map → salin URL di dalam <code class="text-emerald-400">src="..."</code></p>
                        </div>

                        <!-- SOCIAL LINKS -->
                        <div class="border-t border-slate-800 pt-4 space-y-3">
                            <label class="block text-xs font-semibold text-slate-300">Media Sosial & Marketplace</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                @foreach([
                                    ['key' => 'instagram', 'label' => '📸 Instagram', 'placeholder' => 'https://instagram.com/...'],
                                    ['key' => 'tiktok', 'label' => '🎵 TikTok', 'placeholder' => 'https://tiktok.com/@...'],
                                    ['key' => 'facebook', 'label' => '📘 Facebook', 'placeholder' => 'https://facebook.com/...'],
                                    ['key' => 'youtube', 'label' => '▶️ YouTube', 'placeholder' => 'https://youtube.com/...'],
                                    ['key' => 'tokopedia', 'label' => '🛒 Tokopedia', 'placeholder' => 'https://tokopedia.com/...'],
                                    ['key' => 'shopee', 'label' => '🛍️ Shopee', 'placeholder' => 'https://shopee.co.id/...'],
                                ] as $social)
                                <div>
                                    <label class="text-[10px] text-slate-400 block mb-1">{{ $social['label'] }}</label>
                                    <input type="text" name="social_links[{{ $social['key'] }}]" value="{{ $landingPage->social_links[$social['key']] ?? '' }}" placeholder="{{ $social['placeholder'] }}"
                                        class="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white focus:border-emerald-500 focus:outline-none transition">
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- TAB 5: ULASAN & FAQ -->
                <!-- ============================================================ -->
                <div x-show="activeTab === 'social_proof'" class="space-y-5">

                    <!-- TESTIMONIALS -->
                    <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                            <div>
                                <h2 class="text-base font-bold text-white">⭐ Testimonial Pelanggan</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Bukti kepuasan nyata yang meningkatkan konversi penjualan signifikan.</p>
                            </div>
                            <button type="button" @click="addTestimonial"
                                class="px-4 py-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 text-xs font-bold flex items-center gap-2 transition">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Tambah Ulasan</span>
                            </button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(testi, tIdx) in testimonials" :key="tIdx">
                                <div class="p-4 bg-slate-900/80 border border-slate-800 rounded-xl space-y-3">
                                    <div class="flex items-center gap-3">
                                        <input type="text" x-model="testi.name" placeholder="Nama Pelanggan"
                                            class="flex-1 px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs font-bold text-white focus:border-emerald-500 focus:outline-none">
                                        <input type="text" x-model="testi.role" placeholder="Kota / Profesi"
                                            class="flex-1 px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-400 focus:border-emerald-500 focus:outline-none">
                                        <span class="text-amber-400 text-xs font-bold whitespace-nowrap">★ 5.0</span>
                                        <button type="button" @click="removeTestimonial(tIdx)"
                                            class="text-slate-500 hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-500/10 transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                    <textarea x-model="testi.comment" rows="2" placeholder="Ulasan pengalaman pelanggan yang autentik dan meyakinkan..."
                                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-300 focus:border-emerald-500 focus:outline-none resize-none"></textarea>
                                </div>
                            </template>
                            <div x-show="testimonials.length === 0" class="p-8 text-center bg-slate-900/30 rounded-xl border border-dashed border-slate-800">
                                <p class="text-xs text-slate-500">Belum ada ulasan. Tambahkan testimoni yang meyakinkan calon pembeli.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQs -->
                    <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                            <div>
                                <h2 class="text-base font-bold text-white">❓ Tanya Jawab Populer (FAQ)</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Jawab pertanyaan umum yang mengurangi hambatan pembelian.</p>
                            </div>
                            <button type="button" @click="addFaq"
                                class="px-4 py-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 text-xs font-bold flex items-center gap-2 transition">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Tambah FAQ</span>
                            </button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(faq, fIdx) in faqs" :key="fIdx">
                                <div class="p-4 bg-slate-900/80 border border-slate-800 rounded-xl space-y-2">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-bold text-emerald-400 shrink-0">Q:</span>
                                        <input type="text" x-model="faq.q" placeholder="Pertanyaan (misal: Apakah ada layanan antar ke rumah?)"
                                            class="flex-1 px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs font-bold text-white focus:border-emerald-500 focus:outline-none">
                                        <button type="button" @click="removeFaq(fIdx)"
                                            class="text-slate-500 hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-500/10 transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <span class="text-xs font-bold text-slate-500 shrink-0 mt-2">A:</span>
                                        <textarea x-model="faq.a" rows="2" placeholder="Jawaban yang informatif dan membangun kepercayaan..."
                                            class="flex-1 px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-300 focus:border-emerald-500 focus:outline-none resize-none"></textarea>
                                    </div>
                                </div>
                            </template>
                            <div x-show="faqs.length === 0" class="p-8 text-center bg-slate-900/30 rounded-xl border border-dashed border-slate-800">
                                <p class="text-xs text-slate-500">Belum ada FAQ. Tambahkan pertanyaan yang sering ditanyakan calon pembeli.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- TAB 6: SEO & META -->
                <!-- ============================================================ -->
                <div x-show="activeTab === 'seo'" class="space-y-5">
                    <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-5">
                        <div class="border-b border-slate-800 pb-4">
                            <h2 class="text-base font-bold text-white">🔍 Optimasi Google SEO</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Bantu halaman bisnis Anda mudah ditemukan calon pelanggan di Google Search.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Judul Halaman (Meta Title)</label>
                            <input type="text" x-model="form.meta_title" placeholder="Bengkel Motor Jaya — Servis & Sparepart Terpercaya Jakarta Selatan"
                                class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                            <div class="flex justify-between text-[10px] text-slate-500 mt-1">
                                <span>Ideal: 50–60 karakter</span>
                                <span :class="(form.meta_title || '').length > 60 ? 'text-rose-400' : 'text-slate-500'" x-text="(form.meta_title || '').length + ' / 60'"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Deskripsi Google (Meta Description)</label>
                            <textarea x-model="form.meta_description" rows="3" placeholder="Deskripsi ringkas bisnis Anda yang muncul di bawah judul di hasil pencarian Google..."
                                class="w-full px-3.5 py-2 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none resize-none transition"></textarea>
                            <div class="flex justify-between text-[10px] text-slate-500 mt-1">
                                <span>Ideal: 120–160 karakter</span>
                                <span :class="(form.meta_description || '').length > 160 ? 'text-rose-400' : 'text-slate-500'" x-text="(form.meta_description || '').length + ' / 160'"></span>
                            </div>
                        </div>

                        <!-- LIVE GOOGLE PREVIEW -->
                        <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800/80 space-y-1.5">
                            <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-3">⚡ Pratinjau Hasil Pencarian Google</div>
                            <div class="text-xs text-slate-500 font-mono truncate">{{ $publicUrl }}</div>
                            <div class="text-sm font-semibold text-blue-400 hover:underline cursor-pointer truncate leading-snug"
                                x-text="form.meta_title || '{{ addslashes($business->name) }}'"></div>
                            <div class="text-xs text-slate-400 leading-relaxed" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"
                                x-text="form.meta_description || 'Kunjungi halaman profil dan katalog layanan lengkap kami.'"></div>
                        </div>

                        <!-- LocalBusiness JSON-LD Info -->
                        <div class="p-4 bg-emerald-500/5 border border-emerald-500/20 rounded-xl">
                            <div class="flex items-start gap-2.5">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 mt-0.5 shrink-0"></i>
                                <div class="text-xs text-slate-300">
                                    <strong class="text-emerald-400">Schema Markup Otomatis:</strong> Cooca secara otomatis menyisipkan kode JSON-LD <code class="text-emerald-300">LocalBusiness</code> — data terstruktur ini membantu Google mengenali bisnis Anda sebagai entitas lokal dan meningkatkan visibilitas di Google Maps serta pencarian lokal.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>

    <!-- ============================================================ -->
    <!-- MODAL: 20 INDUSTRY PRESETS SELECTOR -->
    <!-- ============================================================ -->
    <div x-show="showPresetModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-sm" style="display:none;">
        <div @click.away="showPresetModal = false" class="bg-slate-900 border border-slate-700 rounded-2xl max-w-5xl w-full max-h-[88vh] flex flex-col shadow-2xl overflow-hidden">
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-400 flex items-center justify-center text-slate-950 font-black shadow-lg">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white">Template 20 Sektor Industri UMKM Indonesia</h3>
                        <p class="text-xs text-slate-400">Pilih industri untuk mengisi konten, layanan, FAQ & ulasan autentik dalam 1 klik.</p>
                    </div>
                </div>
                <button type="button" @click="showPresetModal = false" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-5 overflow-y-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
                    @foreach($industries as $ind)
                    <div class="p-3.5 rounded-2xl border border-slate-800 bg-slate-950/60 hover:border-slate-600 hover:bg-slate-800/50 transition-all group flex flex-col">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl">{{ $ind['icon'] }}</span>
                            <span class="w-3 h-3 rounded-full border-2 border-white/20" style="background-color: {{ $ind['theme_color'] }}"></span>
                        </div>
                        <h4 class="text-xs font-bold text-white group-hover:text-emerald-300 transition leading-tight mb-1">{{ $ind['name'] }}</h4>
                        <p class="text-[10px] text-slate-500 leading-relaxed flex-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $ind['description'] }}</p>
                        <div class="mt-3 pt-2.5 border-t border-slate-800">
                            <button type="button" @click="applyPreset('{{ $ind['id'] }}')"
                                class="w-full py-2 rounded-xl bg-slate-800 hover:bg-emerald-500 text-slate-300 hover:text-slate-950 text-[11px] font-extrabold transition-all flex items-center justify-center gap-1.5">
                                <i data-lucide="zap" class="w-3 h-3"></i>
                                <span>Terapkan</span>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="p-4 border-t border-slate-800 bg-slate-950/60 flex justify-between items-center">
                <p class="text-[11px] text-slate-500">⚡ Penerapan template akan memperbarui teks, layanan, FAQ, dan ulasan sesuai industri yang dipilih.</p>
                <button type="button" @click="showPresetModal = false" class="px-5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function landingPageEditor() {
    return {
        activeTab: 'hero',
        showPresetModal: false,
        copied: false,

        tabs: [
            { id: 'hero', label: '1. Hero & Branding', icon: 'sparkles' },
            { id: 'services', label: '2. Layanan & Produk', icon: 'package' },
            { id: 'story', label: '3. Profil & Jam Buka', icon: 'book-open' },
            { id: 'contact', label: '4. Kontak & Peta', icon: 'phone-call' },
            { id: 'social_proof', label: '5. Ulasan & FAQ', icon: 'message-square' },
            { id: 'seo', label: '6. SEO Google', icon: 'search' },
        ],

        themePresets: [
            { hex: '#10B981', label: 'Emerald' }, { hex: '#0EA5E9', label: 'Sky' },
            { hex: '#6366F1', label: 'Indigo' }, { hex: '#8B5CF6', label: 'Violet' },
            { hex: '#EC4899', label: 'Pink' }, { hex: '#F59E0B', label: 'Amber' },
            { hex: '#EF4444', label: 'Red' }, { hex: '#14B8A6', label: 'Teal' },
            { hex: '#64748B', label: 'Slate' },
        ],

        form: {
            industry_preset: @json($landingPage->industry_preset ?? 'retail'),
            theme_color: @json($landingPage->theme_color ?? '#10B981'),
            announcement_badge: @json($landingPage->announcement_badge ?? ''),
            headline: @json($landingPage->headline ?? ''),
            subheadline: @json($landingPage->subheadline ?? ''),
            cta_primary_text: @json($landingPage->cta_primary_text ?? ''),
            cta_secondary_text: @json($landingPage->cta_secondary_text ?? ''),
            hero_image_url: @json($landingPage->hero_image_url ?? ''),
            logo_url: @json($landingPage->logo_url ?? ''),
            about_title: @json($landingPage->about_title ?? ''),
            about_story: @json($landingPage->about_story ?? ''),
            whatsapp_number: @json($landingPage->whatsapp_number ?? ''),
            whatsapp_default_message: @json($landingPage->whatsapp_default_message ?? ''),
            custom_email: @json($landingPage->custom_email ?? ''),
            custom_address: @json($landingPage->custom_address ?? ''),
            google_maps_embed: @json($landingPage->google_maps_embed ?? ''),
            meta_title: @json($landingPage->meta_title ?? ''),
            meta_description: @json($landingPage->meta_description ?? ''),
        },

        values: @json($landingPage->values ?? []),
        services: @json($landingPage->custom_services ?? []),
        faqs: @json($landingPage->faqs ?? []),
        testimonials: @json($landingPage->testimonials ?? []),
        operationalHours: @json($landingPage->operational_hours ?? [
            ['day' => 'Senin', 'hours' => '08:00 - 17:00 WIB', 'is_open' => true],
            ['day' => 'Selasa', 'hours' => '08:00 - 17:00 WIB', 'is_open' => true],
            ['day' => 'Rabu', 'hours' => '08:00 - 17:00 WIB', 'is_open' => true],
            ['day' => 'Kamis', 'hours' => '08:00 - 17:00 WIB', 'is_open' => true],
            ['day' => 'Jumat', 'hours' => '08:00 - 17:00 WIB', 'is_open' => true],
            ['day' => 'Sabtu', 'hours' => '08:00 - 15:00 WIB', 'is_open' => true],
            ['day' => 'Minggu', 'hours' => 'Tutup', 'is_open' => false],
        ]),

        init() {
            if (!this.values || this.values.length === 0) {
                this.values = [
                    { title: 'Kualitas Terjamin', description: 'Standar mutu dan pengerjaan terbaik dengan garansi kepuasan.' },
                    { title: 'Pelayanan Cepat', description: 'Responsif dan tepat waktu untuk setiap kebutuhan pelanggan.' },
                    { title: 'Harga Transparan', description: 'Biaya jelas tanpa ada pungutan tersembunyi.' },
                    { title: 'Konsultasi Gratis', description: 'Dapatkan rekomendasi terbaik dari tim ahli kami.' }
                ];
            }
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        addService() {
            this.services.push({ title: '', price: '', description: '', badge: '' });
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        removeService(index) { this.services.splice(index, 1); },

        addTestimonial() {
            this.testimonials.push({ name: '', role: 'Pelanggan', comment: '', rating: 5 });
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        removeTestimonial(index) { this.testimonials.splice(index, 1); },

        addFaq() {
            this.faqs.push({ q: '', a: '' });
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        removeFaq(index) { this.faqs.splice(index, 1); },

        copyLink(url) {
            navigator.clipboard.writeText(url).then(() => {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2500);
            });
        },

        applyPreset(presetKey) {
            if (!confirm('Terapkan template industri ini? Konten draft Anda akan diperbarui sesuai industri yang dipilih.')) return;

            const btn = event.target;
            btn.textContent = 'Memuat...';
            btn.disabled = true;

            fetch('{{ route("landing-page.preset") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ preset: presetKey })
            })
            .then(res => res.json())
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
                    this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                }
            })
            .catch(() => alert('Gagal menerapkan preset. Silakan coba kembali.'))
            .finally(() => {
                btn.textContent = 'Terapkan';
                btn.disabled = false;
            });
        },

        prepareSubmission() {
            return true;
        }
    };
}
</script>
@endsection
