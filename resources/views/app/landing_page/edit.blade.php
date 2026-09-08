@extends('layouts.app')

@section('title', 'Website & Landing Page Bisnis — Cooca')

@section('content')
<div x-data="landingPageEditor()" class="space-y-4 sm:space-y-6 max-w-7xl mx-auto px-0 sm:px-2 pb-20">

    <!-- TOP BAR / HEADER -->
    <div class="glass-card rounded-2xl p-4 sm:p-5 md:p-6 border border-slate-800 relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 font-black shadow-lg shadow-emerald-500/20">
                        <i data-lucide="globe" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-lg sm:text-xl font-black text-white tracking-tight leading-tight">Website & Landing Page Bisnis</h1>
                            <span :class="isPublished ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700'"
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold">
                                <span :class="isPublished ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500'" class="w-2 h-2 rounded-full"></span>
                                <span x-text="isPublished ? 'Publik Aktif' : 'Draft / Disembunyikan'"></span>
                            </span>
                        </div>
                        <p class="text-xs md:text-sm text-slate-400 mt-0.5">Kelola halaman profil, katalog produk, dan kontak online bisnis Anda untuk 20+ sektor industri.</p>
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="grid grid-cols-1 sm:flex sm:flex-wrap items-stretch sm:items-center gap-2.5 w-full lg:w-auto">
                <button type="button" @click="showPresetModal = true"
                    class="w-full sm:w-auto justify-center px-3.5 py-2 rounded-xl bg-gradient-to-r from-amber-500/20 to-orange-500/20 hover:from-amber-500/30 hover:to-orange-500/30 text-amber-300 border border-amber-500/30 text-xs font-bold flex items-center gap-2 transition-all shadow-sm">
                    <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
                    <span>Pilih Template Industri (20)</span>
                </button>

                <button type="button" @click="togglePublish()" :disabled="saving"
                    :class="isPublished ? 'bg-rose-500/15 hover:bg-rose-500/25 text-rose-300 border-rose-500/30' : 'bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-300 border-emerald-500/30'"
                    class="w-full sm:w-auto justify-center px-3.5 py-2 rounded-xl text-xs font-bold border flex items-center gap-2 transition-all disabled:opacity-50">
                    <i :data-lucide="isPublished ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                    <span x-text="isPublished ? 'Nonaktifkan Publik' : 'Terbitkan Sekarang'"></span>
                </button>

                <button type="button" @click="openPreview()" :disabled="saving"
                    class="w-full sm:w-auto justify-center px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold flex items-center gap-2 transition-all">
                    <i data-lucide="monitor-play" class="w-4 h-4 text-emerald-400"></i>
                    <span x-text="saving ? 'Menyiapkan Preview...' : 'Preview Live'"></span>
                </button>

                <a href="{{ $publicUrl }}" target="_blank"
                    class="w-full sm:w-auto justify-center px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-extrabold flex items-center gap-2 transition-all shadow-lg shadow-emerald-500/20">
                    <span>Lihat Halaman Publik</span>
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- PUBLIC URL BANNER -->
        <div class="mt-4 pt-4 border-t border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2 min-w-0 overflow-hidden">
                <span class="text-slate-400 shrink-0 font-medium">Link Publik Anda:</span>
                <a href="{{ $publicUrl }}" target="_blank" class="text-emerald-400 hover:underline font-mono truncate max-w-md">
                    {{ $publicUrl }}
                </a>
            </div>
            <div class="grid grid-cols-2 sm:flex items-center gap-2 w-full sm:w-auto">
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
    <form action="{{ route('landing-page.update') }}" method="POST" id="landingPageForm" enctype="multipart/form-data" @submit.prevent="saveAll">
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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-6">

            <!-- SIDEBAR TABS NAVIGATION -->
            <div class="lg:col-span-3 space-y-2">
                <div class="glass-card rounded-2xl p-2 sm:p-3 border border-slate-800 lg:sticky lg:top-6">
                    <p class="text-[10px] uppercase font-bold text-slate-500 tracking-wider px-2 pb-1">Editor Bagian</p>

                    <div class="flex lg:block gap-1 overflow-x-auto pb-1 lg:pb-0 snap-x snap-mandatory">
                    <template x-for="tab in tabs" :key="tab.id">
                        <button type="button" @click="activeTab = tab.id"
                            class="w-auto lg:w-full shrink-0 snap-start flex items-center gap-2 lg:gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all text-left whitespace-nowrap"
                            :class="activeTab === tab.id ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 shadow-sm' : 'text-slate-400 hover:bg-slate-900 hover:text-white'">
                            <i :data-lucide="tab.icon" class="w-4 h-4" :class="activeTab === tab.id ? 'text-emerald-400' : 'text-slate-500'"></i>
                            <span x-text="tab.label"></span>
                        </button>
                    </template>
                    </div>

                    <div class="pt-3 mt-2 border-t border-slate-800 space-y-2">
                        <button type="submit"
                            :disabled="saving"
                            class="w-full py-2.5 px-4 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black flex items-center justify-center gap-2 transition-all shadow-lg shadow-emerald-500/20 disabled:opacity-50">
                            <i :data-lucide="saving ? 'loader-circle' : 'save'" :class="saving ? 'animate-spin' : ''" class="w-4 h-4"></i>
                            <span x-text="saving ? 'Menyimpan...' : 'Simpan Semua Perubahan'"></span>
                        </button>
                        <p class="text-[10px] text-center" :class="saveError ? 'text-rose-400' : 'text-slate-500'" x-text="saveError || saveMessage || 'Perubahan tersimpan tidak otomatis dipublikasikan.'"></p>
                    </div>
                </div>
            </div>

            <!-- TAB CONTENTS -->
            <div class="lg:col-span-9 space-y-6">

                <!-- ============================================================ -->
                <!-- TAB 1: GENERAL -->
                <!-- ============================================================ -->
                <div x-show="['general', 'hero', 'about'].includes(activeTab)" class="space-y-5">
                    <div class="glass-card rounded-2xl p-4 sm:p-6 border border-slate-800 space-y-5">
                        <div x-show="activeTab === 'general'" class="flex items-center justify-between border-b border-slate-800 pb-4">
                            <div>
                                <h2 class="text-base font-bold text-white">🎨 Identitas & Warna Tema</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Tentukan tampilan visual utama halaman bisnis Anda.</p>
                            </div>
                            <span class="text-xs font-semibold text-slate-400 bg-slate-900 px-3 py-1 rounded-lg border border-slate-800">
                                Preset: <strong class="text-emerald-400 uppercase" x-text="form.industry_preset"></strong>
                            </span>
                        </div>

                        <!-- THEME COLOR PICKER -->
                        <div x-show="activeTab === 'general'">
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
                        <div x-show="activeTab === 'general'" class="flex items-center justify-between p-4 bg-slate-900/60 rounded-xl border border-slate-800">
                            <div>
                                <div class="text-xs font-bold text-white">Tampilan Elegan Gelap (Dark Mode)</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">Latar gelap premium dengan glassmorphism & efek cahaya tema.</div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="dark_mode" value="1" {{ $landingPage->dark_mode ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>

                        <div x-show="activeTab === 'hero'" class="space-y-5">
                        <div class="p-4 bg-slate-900/60 rounded-xl border border-slate-800">
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-300 cursor-pointer">
                                <input type="checkbox" x-model="sectionVisibility.hero" class="rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-emerald-500">
                                <span>Tampilkan section Hero</span>
                            </label>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Badge Pengumuman Singkat</label>
                                <input type="text" name="announcement_badge" x-model="form.announcement_badge" placeholder="🔥 Solusi Terpercaya Sejak 2018"
                                    class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30 focus:outline-none transition">
                                <p class="text-[10px] text-slate-500 mt-1">Muncul di atas headline utama sebagai magnet perhatian pertama.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Upload Logo Bisnis</label>
                                <div x-show="previews.logo || form.logo_url" class="mb-2 flex items-center gap-3">
                                    <img :src="previews.logo || form.logo_url" alt="Logo Preview" class="w-14 h-14 rounded-xl object-contain bg-slate-950 border border-slate-700 p-1">
                                    <span class="text-[10px] text-emerald-400 font-semibold" x-text="previews.logo ? 'Preview file baru dipilih' : 'Logo tersimpan'"></span>
                                </div>
                                <input type="file" name="logo_image" accept="image/jpeg,image/png,image/webp"
                                    @change="handleImagePreview($event, 'logo')"
                                    class="w-full text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-500/15 file:px-3 file:py-2 file:text-emerald-300">
                                <label class="mt-2 flex items-center gap-2 text-[10px] text-slate-400"><input type="checkbox" name="remove_logo_image" value="1" class="rounded bg-slate-900 border-slate-700 text-rose-500"> Hapus logo tersimpan</label>
                                <p class="text-[10px] text-slate-500 mt-1">JPG, PNG, atau WebP maksimal 4 MB. Logo lama tetap digunakan jika tidak memilih file baru.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Headline Utama (Judul Besar) <span class="text-rose-400">*</span></label>
                            <input type="text" name="headline" x-model="form.headline" placeholder="Bengkel Motor Terlengkap & Terpercaya di Jakarta"
                                class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-sm font-semibold text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Subheadline / Deskripsi Pengantar</label>
                            <textarea name="subheadline" x-model="form.subheadline" rows="3" placeholder="Jelaskan keunikan dan keunggulan utama bisnis Anda untuk calon pelanggan..."
                                class="w-full px-3.5 py-2 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Teks Tombol Utama (Order/Pesan)</label>
                                <input type="text" name="cta_primary_text" x-model="form.cta_primary_text" placeholder="Pesan via WhatsApp Sekarang"
                                    class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">URL Tombol Utama</label>
                                <input type="text" name="cta_primary_url" x-model="form.cta_primary_url" placeholder="Kosongkan untuk WhatsApp"
                                    class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Teks Tombol Sekunder (Katalog)</label>
                                <input type="text" name="cta_secondary_text" x-model="form.cta_secondary_text" placeholder="Lihat Daftar Layanan & Harga"
                                    class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">URL Tombol Sekunder</label>
                                <input type="text" name="cta_secondary_url" x-model="form.cta_secondary_url" placeholder="#layanan atau URL tujuan"
                                    class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Upload Gambar Hero (Opsional)</label>
                            <div x-show="previews.hero || form.hero_image_url" class="mb-2 flex items-center gap-3">
                                <img :src="previews.hero || form.hero_image_url" alt="Hero Preview" class="w-32 h-20 rounded-xl object-cover border border-slate-700">
                                <span class="text-[10px] text-emerald-400 font-semibold" x-text="previews.hero ? 'Preview file baru dipilih' : 'Hero tersimpan'"></span>
                            </div>
                            <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp"
                                @change="handleImagePreview($event, 'hero')"
                                class="w-full text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-500/15 file:px-3 file:py-2 file:text-emerald-300">
                            <label class="mt-2 flex items-center gap-2 text-[10px] text-slate-400"><input type="checkbox" name="remove_hero_image" value="1" class="rounded bg-slate-900 border-slate-700 text-rose-500"> Hapus hero tersimpan</label>
                            <p class="text-[10px] text-slate-500 mt-1">Maks. 4MB. Format JPG, PNG, atau WebP. Gambar baru otomatis menggantikan hero lama saat disimpan.</p>
                        </div>

                        </div>
                    </div>

                    <!-- 4 VALUE PILLARS -->
                    <div x-show="activeTab === 'about'" class="glass-card rounded-2xl p-4 sm:p-6 border border-slate-800 space-y-4">
                        <label class="flex items-center gap-2 p-4 bg-slate-900/60 rounded-xl border border-slate-800 text-xs font-bold text-slate-300 cursor-pointer">
                            <input type="checkbox" x-model="sectionVisibility.about" class="rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-emerald-500">
                            <span>Tampilkan section Tentang</span>
                        </label>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Upload Gambar About (Opsional)</label>
                            <div x-show="previews.about || form.about_image_url" class="mb-2 flex items-center gap-3">
                                <img :src="previews.about || form.about_image_url" alt="About Preview" class="w-32 h-20 rounded-xl object-cover border border-slate-700">
                                <span class="text-[10px] text-emerald-400 font-semibold" x-text="previews.about ? 'Preview file baru dipilih' : 'Gambar tersimpan'"></span>
                            </div>
                            <input type="file" name="about_image" accept="image/jpeg,image/png,image/webp"
                                @change="handleImagePreview($event, 'about')"
                                class="w-full text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-500/15 file:px-3 file:py-2 file:text-emerald-300">
                            <label class="mt-2 flex items-center gap-2 text-[10px] text-slate-400"><input type="checkbox" name="remove_about_image" value="1" class="rounded bg-slate-900 border-slate-700 text-rose-500"> Hapus gambar about tersimpan</label>
                            <p class="text-[10px] text-slate-500 mt-1">Gambar profil bisnis untuk section Tentang Kami. Maks. 4MB.</p>
                        </div>
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <div>
                                <h2 class="text-base font-bold text-white">🏆 4 Pilar Keunggulan Bisnis</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Alasan kuat mengapa pelanggan harus memilih Anda (ikon dapat dikonfigurasi).</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-for="(val, index) in values" :key="index">
                                <div class="p-4 bg-slate-900/70 border border-slate-800 rounded-xl space-y-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-black text-xs shrink-0" x-text="index + 1"></div>
                                        <input type="text" x-model="val.title" placeholder="Judul Keunggulan"
                                            class="flex-1 px-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs font-bold text-white focus:border-emerald-500 focus:outline-none">
                                        <select x-model="val.icon" class="px-2 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-emerald-400 font-semibold focus:border-emerald-500 focus:outline-none">
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
                <div x-show="['products', 'services'].includes(activeTab)" class="space-y-5">
                    <div class="glass-card rounded-2xl p-4 sm:p-6 border border-slate-800 space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-slate-900/60 rounded-xl border border-slate-800">
                            <label x-show="activeTab === 'products'" class="flex items-center gap-2 text-xs font-bold text-slate-300 cursor-pointer">
                                <input type="checkbox" x-model="sectionVisibility.products" class="rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-emerald-500">
                                <span>Tampilkan section Produk</span>
                            </label>
                            <label x-show="activeTab === 'services'" class="flex items-center gap-2 text-xs font-bold text-slate-300 cursor-pointer">
                                <input type="checkbox" x-model="sectionVisibility.services" class="rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-emerald-500">
                                <span>Tampilkan section Layanan</span>
                            </label>
                        </div>
                        <div x-show="activeTab === 'services'" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
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

                        <div x-show="activeTab === 'services'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <input type="text" name="services_title" x-model="form.services_title" placeholder="Judul layanan dan produk"
                                class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none">
                            <input type="text" name="services_subtitle" x-model="form.services_subtitle" placeholder="Deskripsi singkat katalog"
                                class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-300 placeholder-slate-500 focus:border-emerald-500 focus:outline-none">
                        </div>

                        <div x-show="activeTab === 'services'" class="space-y-3">
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
                                                class="w-full sm:w-36 px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs font-semibold text-emerald-400 focus:border-emerald-500 focus:outline-none text-right">
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
                                    <div>
                                        <label class="block text-[10px] text-slate-400 mb-1">Gambar Layanan</label>
                                        <div x-show="service.preview_url || service.image_url" class="mb-2 flex items-center gap-2">
                                            <img :src="service.preview_url || service.image_url" alt="Preview Layanan" class="w-16 h-12 rounded-lg object-cover border border-slate-700">
                                            <span class="text-[10px] text-emerald-400 font-semibold" x-text="service.preview_url ? 'Preview dipilih' : 'Gambar tersimpan'"></span>
                                        </div>
                                        <input type="file" :name="'service_images[' + sIdx + ']'" accept="image/jpeg,image/png,image/webp"
                                            @change="handleServiceImagePreview($event, sIdx)"
                                            class="w-full text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-500/15 file:px-3 file:py-2 file:text-emerald-300">
                                        <label class="mt-2 flex items-center gap-2 text-[10px] text-slate-400"><input type="checkbox" :name="'remove_service_images[' + sIdx + ']'" value="1" class="rounded bg-slate-900 border-slate-700 text-rose-500"> Hapus gambar layanan</label>
                                    </div>
                                </div>
                            </template>

                            <div x-show="services.length === 0" class="p-8 text-center bg-slate-900/30 rounded-xl border border-dashed border-slate-800">
                                <i data-lucide="package-open" class="w-8 h-8 text-slate-600 mx-auto mb-2"></i>
                                <p class="text-xs text-slate-500">Belum ada layanan. Klik "Tambah Layanan" atau terapkan template industri.</p>
                            </div>
                        </div>

                        <!-- POS PRODUCTS INTEGRATION -->
                        <div x-show="activeTab === 'products'" class="mt-2 p-4 bg-slate-900/60 rounded-xl border border-emerald-900/40 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-white flex items-center gap-2">
                                        <i data-lucide="shopping-bag" class="w-4 h-4 text-emerald-400"></i>
                                        <span>Tampilkan Produk dari Kasir POS</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-1">Produk aktif dari POS ditampilkan otomatis dengan tombol pesan via WhatsApp 1-klik.</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="show_pos_products" value="1" {{ $landingPage->show_pos_products ? 'checked' : '' }} class="sr-only peer">
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
                <!-- TAB 6: GALERI -->
                <!-- ============================================================ -->
                <div x-show="activeTab === 'gallery'" class="space-y-5">
                    <div class="glass-card rounded-2xl p-4 sm:p-6 border border-slate-800 space-y-6">

                        <!-- Section Visibility Banner -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 bg-slate-900/60 rounded-xl border border-slate-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="images" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-white">Tampilkan Section Galeri di Website</div>
                                    <div class="text-[11px] text-slate-400">Section galeri akan tampil di landing page jika opsi ini aktif dan ada minimal 1 foto.</div>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" x-model="sectionVisibility.gallery" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>

                        <!-- Section Header -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-base font-bold text-white">🖼️ Galeri &amp; Suasana Bisnis</h2>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30"
                                        x-text="(galleryItems.length + newGalleryUploads.length) + ' / 20 Foto'"></span>
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">Tampilkan suasana tempat, hasil karya, foto produk, dan kegiatan bisnis dalam grid 4 kolom modern dengan efek hover zoom dan caption.</p>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button" @click="showAddUrlModal = true"
                                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold flex items-center gap-1.5 transition shadow-sm">
                                    <i data-lucide="link" class="w-3.5 h-3.5 text-emerald-400"></i>
                                    <span>Tambah via URL</span>
                                </button>
                                <button type="button" @click="clearAllGallery()" x-show="galleryItems.length > 0 || newGalleryUploads.length > 0"
                                    class="px-3 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/30 text-xs font-bold flex items-center gap-1.5 transition">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    <span>Kosongkan</span>
                                </button>
                            </div>
                        </div>

                        <!-- Gallery Title & Subtitle -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Judul Bagian Galeri</label>
                                <input type="text" name="gallery_title" x-model="form.gallery_title" maxlength="120" placeholder="Galeri & Suasana Toko"
                                    class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                                <p class="text-[10px] text-slate-500 mt-1">Muncul sebagai tajuk utama di atas baris foto galeri.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Deskripsi / Subtitle Galeri</label>
                                <textarea name="gallery_subtitle" x-model="form.gallery_subtitle" maxlength="500" rows="2" placeholder="Dokumentasi aktivitas, produk unggulan, dan kehangatan pelayanan kami."
                                    class="w-full px-3.5 py-2 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-slate-300 placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition resize-none"></textarea>
                            </div>
                        </div>

                        <!-- Upload Dropzone -->
                        <div class="p-6 border-2 border-dashed border-slate-700 hover:border-emerald-500/60 bg-slate-900/40 rounded-2xl text-center transition-all group relative">
                            <input type="file" multiple accept="image/jpeg,image/png,image/webp"
                                @change="handleGalleryFiles($event)"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <div class="flex flex-col items-center justify-center space-y-2 pointer-events-none">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                                </div>
                                <div class="text-xs font-bold text-white">
                                    <span class="text-emerald-400 underline decoration-emerald-400/30 underline-offset-4">Klik untuk memilih foto</span> atau seret file ke sini
                                </div>
                                <p class="text-[11px] text-slate-400 max-w-md">
                                    Mendukung format JPG, PNG, atau WebP (maks. 4 MB per foto). Foto yang diunggah akan otomatis disesuaikan dengan rasio aspek persegi (*aspect-square*) dan standar tampilan website.
                                </p>
                            </div>
                        </div>

                        <!-- Modal Tambah via URL -->
                        <div x-show="showAddUrlModal" x-cloak class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
                            <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-2xl space-y-4" @click.outside="showAddUrlModal = false">
                                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                                    <h3 class="font-bold text-sm text-white flex items-center gap-2">
                                        <i data-lucide="link" class="w-4 h-4 text-emerald-400"></i>
                                        Tambah Foto via URL
                                    </h3>
                                    <button type="button" @click="showAddUrlModal = false" class="text-slate-400 hover:text-white p-1">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 mb-1">URL Gambar <span class="text-rose-400">*</span></label>
                                        <input type="text" x-model="newUrlInput" placeholder="https://example.com/foto.jpg atau /storage/..."
                                            class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-300 mb-1">Caption Foto (Opsional)</label>
                                        <input type="text" x-model="newCaptionInput" placeholder="Contoh: Suasana Ruang Makan Tradisional"
                                            class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none">
                                    </div>
                                </div>
                                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                                    <button type="button" @click="showAddUrlModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:bg-slate-800 transition">Batal</button>
                                    <button type="button" @click="addGalleryByUrl()" :disabled="!newUrlInput.trim()" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold disabled:opacity-50 transition">Tambahkan</button>
                                </div>
                            </div>
                        </div>

                        <!-- Gallery Cards Grid -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs font-bold text-slate-300">Daftar Foto Galeri Aktif &amp; Preview:</div>
                                <div class="text-[11px] text-slate-400" x-show="galleryItems.length + newGalleryUploads.length > 0">
                                    Arahkan kursor ke foto untuk melihat preview overlay caption seperti pada landing page.
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div x-show="galleryItems.length === 0 && newGalleryUploads.length === 0" class="p-12 text-center bg-slate-900/30 rounded-2xl border border-dashed border-slate-800 space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-slate-800 text-slate-500 flex items-center justify-center mx-auto">
                                    <i data-lucide="image-off" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-300">Belum ada foto di galeri</div>
                                    <p class="text-[11px] text-slate-500 mt-1">Unggah beberapa foto atau gunakan template industri untuk menampilkan galeri menarik pada landing page bisnis Anda.</p>
                                </div>
                            </div>

                            <!-- Grid of Photos -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" x-show="galleryItems.length > 0 || newGalleryUploads.length > 0">

                                <!-- Existing Saved Photos -->
                                <template x-for="(item, idx) in galleryItems" :key="'saved-' + idx">
                                    <div class="glass-card rounded-2xl p-2.5 border border-slate-800/90 bg-slate-900/80 space-y-2.5 flex flex-col justify-between group shadow-md hover:border-emerald-500/40 transition-all">
                                        <!-- Photo Container with Hover Overlay -->
                                        <div class="aspect-square rounded-xl overflow-hidden bg-slate-950 border border-slate-800 relative group/img flex items-center justify-center">
                                            <img :src="item.url" :alt="item.caption || 'Galeri Foto'" class="w-full h-full object-cover group-hover/img:scale-105 transition-transform duration-500" x-on:error="$event.target.style.display = 'none'; $event.target.nextElementSibling.classList.remove('hidden')">
                                            <i data-lucide="image" class="w-8 h-8 text-slate-700 hidden"></i>

                                            <!-- Top Badges & Controls -->
                                            <div class="absolute top-2 inset-x-2 flex items-center justify-between pointer-events-none">
                                                <span class="px-2 py-0.5 rounded-md bg-slate-950/80 text-[10px] font-mono font-bold text-emerald-400 border border-slate-700 backdrop-blur" x-text="'#' + (idx + 1)"></span>
                                                <div class="flex items-center gap-1 pointer-events-auto">
                                                    <button type="button" @click="moveGalleryItem(idx, -1)" :disabled="idx === 0" title="Geser ke kiri" class="w-6 h-6 rounded-md bg-slate-950/80 hover:bg-slate-800 text-slate-300 flex items-center justify-center border border-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition">
                                                        <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                    <button type="button" @click="moveGalleryItem(idx, 1)" :disabled="idx === galleryItems.length - 1" title="Geser ke kanan" class="w-6 h-6 rounded-md bg-slate-950/80 hover:bg-slate-800 text-slate-300 flex items-center justify-center border border-slate-700 disabled:opacity-30 disabled:cursor-not-allowed transition">
                                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                    <button type="button" @click="removeGalleryItem(idx)" title="Hapus foto ini" class="w-6 h-6 rounded-md bg-rose-500/80 hover:bg-rose-500 text-white flex items-center justify-center shadow transition">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Public Landing Page Hover Caption Preview -->
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover/img:opacity-100 transition-opacity duration-300 flex items-end p-3 pointer-events-none">
                                                <p class="text-white text-[11px] font-semibold leading-snug drop-shadow" x-text="item.caption || '(Tanpa caption)'"></p>
                                            </div>
                                        </div>

                                        <!-- Caption Input -->
                                        <div>
                                            <label class="block text-[10px] font-semibold text-slate-400 mb-1">Caption Foto</label>
                                            <input type="text" x-model="item.caption" placeholder="Tulis caption foto..."
                                                class="w-full px-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white placeholder-slate-600 focus:border-emerald-500 focus:outline-none transition">
                                        </div>
                                    </div>
                                </template>

                                <!-- Staged New Uploads -->
                                <template x-for="(nItem, nIdx) in newGalleryUploads" :key="'new-' + nIdx">
                                    <div class="glass-card rounded-2xl p-2.5 border border-emerald-500/40 bg-slate-900/90 space-y-2.5 flex flex-col justify-between group shadow-md shadow-emerald-500/5 transition-all">
                                        <!-- Photo Container -->
                                        <div class="aspect-square rounded-xl overflow-hidden bg-slate-950 border border-emerald-500/30 relative group/img flex items-center justify-center">
                                            <img :src="nItem.preview" :alt="nItem.caption || 'Foto Baru'" class="w-full h-full object-cover">

                                            <!-- Top Badges & Controls -->
                                            <div class="absolute top-2 inset-x-2 flex items-center justify-between pointer-events-none">
                                                <span class="px-2 py-0.5 rounded-md bg-emerald-500 text-[10px] font-bold text-slate-950 shadow">Foto Baru</span>
                                                <div class="flex items-center gap-1 pointer-events-auto">
                                                    <button type="button" @click="removeNewUpload(nIdx)" title="Batal upload foto ini" class="w-6 h-6 rounded-md bg-rose-500/80 hover:bg-rose-500 text-white flex items-center justify-center shadow transition">
                                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Hover Caption Preview -->
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover/img:opacity-100 transition-opacity duration-300 flex items-end p-3 pointer-events-none">
                                                <p class="text-white text-[11px] font-semibold leading-snug drop-shadow" x-text="nItem.caption || '(Tanpa caption)'"></p>
                                            </div>
                                        </div>

                                        <!-- Caption Input -->
                                        <div>
                                            <label class="block text-[10px] font-semibold text-emerald-400 mb-1">Caption Foto Baru</label>
                                            <input type="text" x-model="nItem.caption" placeholder="Tulis caption foto..."
                                                class="w-full px-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white placeholder-slate-600 focus:border-emerald-500 focus:outline-none transition">
                                        </div>
                                    </div>
                                </template>

                            </div>
                        </div>

                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- TAB 3: PROFIL, STORY & JAM BUKA -->
                <!-- ============================================================ -->
                <div x-show="activeTab === 'about'" class="space-y-5">
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
                                    <input type="text" name="stats[clients]" value="{{ data_get($landingPage->values, 'stats.clients', '5.000+') }}" placeholder="5.000+"
                                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-base font-black text-emerald-400 focus:outline-none text-center">
                                    <input type="text" name="stats[clients_label]" value="Pelanggan Puas" placeholder="Pelanggan Puas"
                                        class="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-400 focus:outline-none text-center">
                                </div>
                                <div class="p-3 bg-slate-900/80 border border-slate-800 rounded-xl space-y-2">
                                    <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Stat 2</label>
                                    <input type="text" name="stats[experience]" value="8+ Tahun" placeholder="8+ Tahun"
                                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-base font-black text-teal-400 focus:outline-none text-center">
                                    <input type="text" name="stats[experience_label]" value="Pengalaman Profesional" placeholder="Pengalaman"
                                        class="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-400 focus:outline-none text-center">
                                </div>
                                <div class="p-3 bg-slate-900/80 border border-slate-800 rounded-xl space-y-2">
                                    <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Stat 3</label>
                                    <input type="text" name="stats[rating]" value="4.9 / 5.0" placeholder="4.9 / 5.0"
                                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-base font-black text-amber-400 focus:outline-none text-center">
                                    <input type="text" name="stats[rating_label]" value="Rating Kepuasan Ulasan" placeholder="Rating Ulasan"
                                        class="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-400 focus:outline-none text-center">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- OPERATIONAL HOURS -->
                    <div class="glass-card rounded-2xl p-4 sm:p-6 border border-slate-800 space-y-4">
                        <div class="border-b border-slate-800 pb-4">
                            <h2 class="text-base font-bold text-white">🕐 Jadwal & Jam Operasional</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Atur jam kerja agar pelanggan tahu waktu terbaik untuk berkunjung.</p>
                        </div>
                        <div class="space-y-2">
                            <template x-for="(day, dIdx) in operationalHours" :key="dIdx">
                                <div class="flex items-center gap-3 p-3 bg-slate-900/70 border border-slate-800 rounded-xl">
                                    <div class="w-20 sm:w-24 text-xs font-bold text-white shrink-0" x-text="day.day"></div>
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
                        <label class="flex items-center gap-2 p-4 bg-slate-900/60 rounded-xl border border-slate-800 text-xs font-bold text-slate-300 cursor-pointer">
                            <input type="checkbox" x-model="sectionVisibility.contact" class="rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-emerald-500">
                            <span>Tampilkan section Kontak</span>
                        </label>
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
                                <input type="text" name="custom_phone" x-model="form.custom_phone" placeholder="Nomor telepon publik (opsional)"
                                    class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none font-mono transition mb-3">
                                <input type="text" name="whatsapp_number" x-model="form.whatsapp_number" placeholder="6281234567890"
                                    class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-[#25D366] focus:outline-none font-mono transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Email Resmi (Opsional)</label>
                                <input type="email" name="custom_email" x-model="form.custom_email" placeholder="kontak@bisnisanda.com"
                                    class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Pesan Sambutan Otomatis WhatsApp</label>
                            <textarea name="whatsapp_welcome_message" x-model="form.whatsapp_welcome_message" rows="2" placeholder="Halo, saya melihat halaman Anda dan ingin bertanya lebih lanjut..."
                                class="w-full px-3.5 py-2 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none resize-none transition"></textarea>
                            <p class="text-[10px] text-slate-500 mt-1">Teks ini otomatis terisi di chat WA pembeli saat mengklik tombol WhatsApp.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Alamat Fisik Toko / Kantor</label>
                            <textarea name="custom_address" x-model="form.custom_address" rows="2" placeholder="Jl. Sudirman No. 123, Kelurahan, Kecamatan, Kota, Kode Pos"
                                class="w-full px-3.5 py-2 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none resize-none transition"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Google Maps Embed URL</label>
                            <input type="text" name="google_maps_embed_url" x-model="form.google_maps_embed_url" placeholder="https://www.google.com/maps/embed?pb=..."
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
                <!-- TAB 7: FOOTER -->
                <!-- ============================================================ -->
                <div x-show="activeTab === 'footer'" class="space-y-5">
                    <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-5">
                        <label class="flex items-center gap-2 p-4 bg-slate-900/60 rounded-xl border border-slate-800 text-xs font-bold text-slate-300 cursor-pointer">
                            <input type="checkbox" x-model="sectionVisibility.footer" class="rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-emerald-500">
                            <span>Tampilkan section Footer</span>
                        </label>
                        <div class="border-t border-slate-800 pt-4 space-y-3">
                            <div>
                                <div class="text-xs font-bold text-white">Grid Footer</div>
                                <p class="text-[11px] text-slate-400 mt-1">Pilih grid yang ingin ditampilkan. Grid aktif akan otomatis membagi lebar footer secara rata.</p>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300 cursor-pointer">
                                    <input type="checkbox" x-model="sectionVisibility.footer_brand" class="rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-emerald-500">
                                    <span>Grid Brand & Sosial</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300 cursor-pointer">
                                    <input type="checkbox" x-model="sectionVisibility.footer_navigation" class="rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-emerald-500">
                                    <span>Grid Navigasi</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300 cursor-pointer">
                                    <input type="checkbox" x-model="sectionVisibility.footer_services" class="rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-emerald-500">
                                    <span>Grid Layanan</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300 cursor-pointer">
                                    <input type="checkbox" x-model="sectionVisibility.footer_contact" class="rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-emerald-500">
                                    <span>Grid Kontak</span>
                                </label>
                            </div>
                        </div>
                        <div class="border-b border-slate-800 pb-4">
                            <h2 class="text-base font-bold text-white">Footer</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Atur semua konten footer yang tampil pada halaman publik.</p>
                        </div>
                        <textarea name="footer_description" x-model="form.footer_description" maxlength="1000" rows="3" placeholder="Deskripsi singkat bisnis untuk footer"
                            class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-slate-300 placeholder-slate-500 focus:border-emerald-500 focus:outline-none resize-none"></textarea>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <input type="text" name="footer_navigation_title" x-model="form.footer_navigation_title" maxlength="80" placeholder="Judul navigasi"
                                class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none">
                            <input type="text" name="footer_services_title" x-model="form.footer_services_title" maxlength="80" placeholder="Judul layanan"
                                class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none">
                            <input type="text" name="footer_contact_title" x-model="form.footer_contact_title" maxlength="80" placeholder="Judul kontak"
                                class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none">
                            <input type="text" name="footer_cta_text" x-model="form.footer_cta_text" maxlength="100" placeholder="Teks tombol footer"
                                class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none">
                        </div>
                        <input type="text" name="footer_copyright" x-model="form.footer_copyright" maxlength="255" placeholder="Copyright footer"
                            class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none">
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- TAB 5: ULASAN & FAQ -->
                <!-- ============================================================ -->
                <div x-show="['testimonials', 'faq'].includes(activeTab)" class="space-y-5">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-slate-900/60 rounded-xl border border-slate-800">
                        <label x-show="activeTab === 'testimonials'" class="flex items-center gap-2 text-xs font-bold text-slate-300 cursor-pointer">
                            <input type="checkbox" x-model="sectionVisibility.testimonials" class="rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-emerald-500">
                            <span>Tampilkan section Ulasan</span>
                        </label>
                        <label x-show="activeTab === 'faq'" class="flex items-center gap-2 text-xs font-bold text-slate-300 cursor-pointer">
                            <input type="checkbox" x-model="sectionVisibility.faq" class="rounded bg-slate-800 border-slate-700 text-emerald-500 focus:ring-emerald-500">
                            <span>Tampilkan section FAQ</span>
                        </label>
                    </div>

                    <!-- TESTIMONIALS -->
                    <div x-show="activeTab === 'testimonials'" class="glass-card rounded-2xl p-6 border border-slate-800 space-y-4">
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
                    <div x-show="activeTab === 'faq'" class="glass-card rounded-2xl p-6 border border-slate-800 space-y-4">
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
                            <input type="text" name="meta_title" x-model="form.meta_title" placeholder="Bengkel Motor Jaya — Servis & Sparepart Terpercaya Jakarta Selatan"
                                class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                            <div class="flex justify-between text-[10px] text-slate-500 mt-1">
                                <span>Ideal: 50–60 karakter</span>
                                <span :class="(form.meta_title || '').length > 60 ? 'text-rose-400' : 'text-slate-500'" x-text="(form.meta_title || '').length + ' / 60'"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Deskripsi Google (Meta Description)</label>
                            <textarea name="meta_description" x-model="form.meta_description" rows="3" placeholder="Deskripsi ringkas bisnis Anda yang muncul di bawah judul di hasil pencarian Google..."
                                class="w-full px-3.5 py-2 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none resize-none transition"></textarea>
                            <div class="flex justify-between text-[10px] text-slate-500 mt-1">
                                <span>Ideal: 120–160 karakter</span>
                                <span :class="(form.meta_description || '').length > 160 ? 'text-rose-400' : 'text-slate-500'" x-text="(form.meta_description || '').length + ' / 160'"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Kata Kunci SEO</label>
                            <input type="text" name="meta_keywords" x-model="form.meta_keywords" maxlength="500" placeholder="restoran jakarta, makanan nusantara, nasi goreng, reservasi restoran"
                                class="w-full px-3.5 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:outline-none transition">
                            <p class="text-[10px] text-slate-500 mt-1">Pisahkan kata kunci dengan koma. Gunakan istilah yang benar-benar relevan dengan bisnis.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Upload Gambar Social Share (Open Graph)</label>
                            <div x-show="previews.og || form.og_image_url" class="mb-2 flex items-center gap-3">
                                <img :src="previews.og || form.og_image_url" alt="OG Preview" class="w-32 h-16 rounded-xl object-cover border border-slate-700">
                                <span class="text-[10px] text-emerald-400 font-semibold" x-text="previews.og ? 'Preview file baru dipilih' : 'Gambar OG tersimpan'"></span>
                            </div>
                            <input type="file" name="og_image" accept="image/jpeg,image/png,image/webp"
                                @change="handleImagePreview($event, 'og')"
                                class="w-full text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-500/15 file:px-3 file:py-2 file:text-emerald-300">
                            <label class="mt-2 flex items-center gap-2 text-[10px] text-slate-400"><input type="checkbox" name="remove_og_image" value="1" class="rounded bg-slate-900 border-slate-700 text-rose-500"> Hapus gambar OG tersimpan</label>
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

    <!-- LIVE LANDING PAGE PREVIEW -->
    <div x-show="showPreview" x-transition.opacity class="fixed inset-0 z-[60] bg-slate-950/90 backdrop-blur-sm p-3 sm:p-6" style="display: none;">
        <div class="h-full max-w-6xl mx-auto bg-white rounded-2xl overflow-hidden shadow-2xl flex flex-col">
            <div class="shrink-0 px-4 sm:px-6 py-3 bg-slate-950 border-b border-slate-800 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="monitor-play" class="w-4 h-4 text-emerald-400"></i>
                    <span class="text-xs font-bold text-white">Preview Live</span>
                    <span class="text-[10px] text-slate-500">Perubahan tampil otomatis</span>
                </div>
                <button type="button" @click="showPreview = false" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800" title="Tutup preview" aria-label="Tutup preview">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <iframe :src="previewUrl" title="Preview halaman publik bisnis" class="w-full h-full border-0 bg-white"></iframe>
            <div x-show="false" class="flex-1 overflow-y-auto" :style="`--preview-color: ${form.theme_color || 'transparent'}`">
                <section class="relative overflow-hidden px-6 py-16 sm:px-14 sm:py-24 text-white" :style="`background: linear-gradient(135deg, ${form.theme_color || 'transparent'}, #0f172a 72%)`">
                    <div class="relative z-10 max-w-3xl">
                        <div x-show="form.announcement_badge" class="inline-flex px-3 py-1 rounded-full bg-white/15 border border-white/20 text-xs font-bold mb-5" x-text="form.announcement_badge"></div>
                        <h1 class="text-3xl sm:text-5xl font-black leading-tight" x-text="form.headline || 'Headline bisnis Anda'"></h1>
                        <p class="mt-4 max-w-2xl text-sm sm:text-base text-white/80 leading-relaxed" x-text="form.subheadline || 'Deskripsi bisnis Anda akan tampil di sini.'"></p>
                        <div class="mt-7 flex flex-wrap gap-3">
                            <span class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-950 bg-white" x-text="form.cta_primary_text || 'Hubungi Kami'"></span>
                            <span class="px-4 py-2.5 rounded-xl text-xs font-bold border border-white/30" x-text="form.cta_secondary_text || 'Lihat Katalog'"></span>
                        </div>
                    </div>
                    <div x-show="form.hero_image_url" class="absolute inset-y-0 right-0 w-2/5 opacity-40 bg-cover bg-center" :style="`background-image: url('${form.hero_image_url}')`"></div>
                </section>

                <section class="px-6 py-10 sm:px-14 bg-slate-50">
                    <div class="max-w-5xl mx-auto">
                        <div class="text-center max-w-2xl mx-auto">
                            <h2 class="text-2xl font-black text-slate-900" x-text="form.about_title || 'Tentang Bisnis Kami'"></h2>
                            <p class="mt-3 text-sm text-slate-600 leading-relaxed" x-text="form.about_story || 'Cerita bisnis Anda akan tampil di sini.'"></p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-8">
                            <template x-for="(value, index) in values.slice(0, 4)" :key="index">
                                <div class="p-4 bg-white border border-slate-200 rounded-xl">
                                    <div class="text-xs font-black" :style="`color: ${form.theme_color || 'transparent'}`" x-text="'0' + (index + 1)"></div>
                                    <h3 class="mt-2 text-sm font-bold text-slate-900" x-text="value.title || 'Keunggulan bisnis'"></h3>
                                    <p class="mt-1 text-xs text-slate-500" x-text="value.description || ''"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </section>

                <section class="px-6 py-10 sm:px-14 bg-white">
                    <div class="max-w-5xl mx-auto">
                        <div class="flex items-end justify-between gap-3 mb-5">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest" :style="`color: ${form.theme_color || 'transparent'}`">Katalog</p>
                                <h2 class="text-2xl font-black text-slate-900">Layanan & Produk</h2>
                            </div>
                            <span class="text-xs text-slate-500" x-text="services.length + ' item'" ></span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <template x-for="(service, index) in services.slice(0, 6)" :key="index">
                                <div class="p-5 border border-slate-200 rounded-xl">
                                    <span x-show="service.badge" class="text-[10px] font-bold uppercase" :style="`color: ${form.theme_color || 'transparent'}`" x-text="service.badge"></span>
                                    <h3 class="mt-1 text-sm font-bold text-slate-900" x-text="service.title || 'Nama layanan'"></h3>
                                    <p class="mt-2 text-xs text-slate-500" x-text="service.description || 'Deskripsi layanan akan tampil di sini.'"></p>
                                    <p class="mt-4 text-sm font-black" :style="`color: ${form.theme_color || 'transparent'}`" x-text="service.price || 'Hubungi kami'"></p>
                                </div>
                            </template>
                        </div>
                        <div x-show="services.length === 0" class="py-10 text-center text-sm text-slate-400">Belum ada layanan di katalog.</div>
                    </div>
                </section>

                <footer class="px-6 py-8 sm:px-14 text-white" :style="`background: ${form.theme_color || 'transparent'}`">
                    <div class="max-w-5xl mx-auto flex flex-col sm:flex-row justify-between gap-4 text-xs">
                        <div><div class="font-black text-base">{{ $business->name }}</div><div class="mt-1 text-white/75" x-text="form.custom_address || 'Alamat bisnis'" ></div></div>
                        <div class="text-left sm:text-right"><div x-text="form.whatsapp_number || 'WhatsApp belum diatur'"></div><div class="mt-1 text-white/75" x-text="form.custom_email || 'Email belum diatur'"></div></div>
                    </div>
                </footer>
            </div>
        </div>
    </div>

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
                            <button type="button" @click="applyPreset('{{ $ind['id'] }}', $event.currentTarget)"
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

@php
    $sectionVisibilityDefaults = array_merge([
        'hero' => false,
        'about' => false,
        'products' => false,
        'services' => false,
        'gallery' => false,
        'testimonials' => false,
        'faq' => false,
        'contact' => false,
        'footer' => false,
        'footer_brand' => false,
        'footer_navigation' => false,
        'footer_services' => false,
        'footer_contact' => false,
    ], $landingPage->section_visibility ?? []);
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
            { id: 'general', label: '1. General', icon: 'settings-2' },
            { id: 'hero', label: '2. Hero', icon: 'sparkles' },
            { id: 'about', label: '3. Tentang', icon: 'book-open' },
            { id: 'products', label: '4. Produk', icon: 'shopping-bag' },
            { id: 'services', label: '5. Layanan', icon: 'package' },
            { id: 'gallery', label: '6. Galeri', icon: 'images' },
            { id: 'testimonials', label: '7. Ulasan', icon: 'star' },
            { id: 'faq', label: '8. FAQ', icon: 'help-circle' },
            { id: 'contact', label: '9. Kontak', icon: 'phone-call' },
            { id: 'footer', label: '10. Footer', icon: 'panels-top-left' },
            { id: 'seo', label: '11. SEO', icon: 'search' },
        ],

        themePresets: [
            { hex: '#10B981', label: 'Emerald' }, { hex: '#0EA5E9', label: 'Sky' },
            { hex: '#6366F1', label: 'Indigo' }, { hex: '#8B5CF6', label: 'Violet' },
            { hex: '#EC4899', label: 'Pink' }, { hex: '#F59E0B', label: 'Amber' },
            { hex: '#EF4444', label: 'Red' }, { hex: '#14B8A6', label: 'Teal' },
            { hex: '#64748B', label: 'Slate' },
        ],

        sectionVisibility: @json($sectionVisibilityDefaults),

        form: {
            industry_preset: @json($landingPage->industry_preset),
            theme_color: @json($landingPage->theme_color),
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
            $initialGallery = collect($landingPage->gallery_images ?? [])->map(function ($img) {
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
            })->filter(fn ($item) => filled($item['url']))->values();
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
                    { title: 'Kualitas Terjamin', description: 'Standar mutu dan pengerjaan terbaik dengan garansi kepuasan.', icon: 'shield-check' },
                    { title: 'Pelayanan Cepat', description: 'Responsif dan tepat waktu untuk setiap kebutuhan pelanggan.', icon: 'zap' },
                    { title: 'Harga Transparan', description: 'Biaya jelas tanpa ada pungutan tersembunyi.', icon: 'award' },
                    { title: 'Konsultasi Gratis', description: 'Dapatkan rekomendasi terbaik dari tim ahli kami.', icon: 'star' }
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
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
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
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
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
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        removeGalleryItem(index) {
            this.galleryItems.splice(index, 1);
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        removeNewUpload(index) {
            this.newGalleryUploads.splice(index, 1);
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        moveGalleryItem(index, direction) {
            const targetIndex = index + direction;
            if (targetIndex < 0 || targetIndex >= this.galleryItems.length) return;
            const item = this.galleryItems.splice(index, 1)[0];
            this.galleryItems.splice(targetIndex, 0, item);
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
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
                        this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                    }
                });
            } else {
                this.galleryItems = [];
                this.newGalleryUploads = [];
                this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
            }
        },

        addService() {
            this.services.push({ title: '', price: '', description: '', badge: '', preview_url: '' });
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
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Link publik berhasil disalin!',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
                setTimeout(() => { this.copied = false; }, 2500);
            });
        },

        applyPreset(presetKey, button) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Terapkan Template Industri?',
                    text: 'Konten draft Anda akan diperbarui sesuai template industri yang dipilih.',
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

            fetch('{{ route("landing-page.preset") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ preset: presetKey })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal memuat preset.');
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
                            title: 'Berhasil!',
                            text: 'Template industri berhasil diterapkan.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                    this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                }
            })
            .catch(error => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: error.message || 'Gagal menerapkan preset. Silakan coba kembali.',
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
                            title: 'Periksa kembali data!',
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
                        title: 'Berhasil!',
                        text: data.message || 'Landing page berhasil diperbarui.',
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
                this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
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
                        title: 'Berhasil!',
                        text: data.message || 'Status publikasi diperbarui.',
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
                this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
            }
        }
    };
}
</script>
@endsection
