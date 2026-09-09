@extends('layouts.app', [
    'title' => 'Pengaturan Bisnis & Template',
    'headerTitle' => 'Pengaturan Bisnis & Template Industri',
    'headerSubtitle' => 'Konfigurasi profil usaha, logo resmi, instruksi transfer faktur, strategi pembulatan HPP, dan 20 preset industri (§35 Blueprint)'
])

@section('content')
<div class="space-y-6" x-data="{
    activeTab: 'general',
    templateSearch: '',
    selectedCategory: 'all',
    logoPreview: '{{ $business->logo_url }}',
    roundingStrategy: '{{ $business->rounding_strategy ?? 'ROUND_100' }}',

    handleLogoChange(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => { this.logoPreview = ev.target.result; };
            reader.readAsDataURL(file);
        }
    },

    getRoundingExample(strategy) {
        const samples = {
            'ROUND': 'Rp 14.234 → Rp 14.234 (Presisi desimal normal)',
            'CEIL': 'Rp 14.234 → Rp 15.000 (Plafon ke atas)',
            'FLOOR': 'Rp 14.234 → Rp 14.000 (Pangkas ke bawah)',
            'ROUND_50': 'Rp 14.234 → Rp 14.250 (Kelipatan 50 terdekat)',
            'ROUND_100': 'Rp 14.234 → Rp 14.200 (Kelipatan 100 terdekat)',
            'ROUND_500': 'Rp 14.234 → Rp 14.500 (Kelipatan 500 terdekat)',
            'ROUND_1000': 'Rp 14.234 → Rp 14.000 (Kelipatan 1.000 terdekat)'
        };
        return samples[strategy] || 'Standar pembulatan sistem';
    }
}">

    {{-- ===== SUB-NAVIGATION TABS ===== --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200/80 dark:border-slate-800/80 scrollbar-none">
        <button type="button" @click="activeTab = 'general'"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap"
                :class="activeTab === 'general' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shadow-xs' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'">
            <i data-lucide="building-2" class="w-4 h-4"></i>
            <span>Profil Usaha & Pembulatan</span>
        </button>

        <button type="button" @click="activeTab = 'templates'"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap"
                :class="activeTab === 'templates' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shadow-xs' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'">
            <i data-lucide="layers" class="w-4 h-4"></i>
            <span>20 Template Industri (§35)</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] font-mono font-bold bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ $templates->count() }}</span>
        </button>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm font-medium flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-500/20 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <div class="flex-1">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-800 dark:text-rose-300 text-xs sm:text-sm font-medium flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-rose-500/20 flex items-center justify-center shrink-0">
                <i data-lucide="alert-octagon" class="w-4 h-4 text-rose-600 dark:text-rose-400"></i>
            </div>
            <div class="flex-1">{{ session('error') }}</div>
        </div>
    @endif

    {{-- ===== TAB 1: PROFIL BISNIS, LOGO & PEMBULATAN ===== --}}
    <div x-show="activeTab === 'general'" class="space-y-6">
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs max-w-4xl space-y-6">
            <div class="flex items-start justify-between flex-wrap gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center shrink-0">
                        <i data-lucide="building-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">
                            Profil Bisnis, Logo Resmi & Pembulatan
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Data ini otomatis tampil sebagai kop surat dan instruksi pembayaran pada Faktur Penjualan (Invoice) dan Purchase Order (PO).
                        </p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-5 text-xs sm:text-sm">
                @csrf
                @method('PUT')

                {{-- Logo Bisnis Section --}}
                <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80 space-y-3">
                    <label class="block font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">
                        Logo Resmi Usaha / Perusahaan
                    </label>
                    <div class="flex flex-col sm:flex-row items-center gap-5">
                        <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" alt="Logo Bisnis" class="w-full h-full object-contain p-1.5">
                            </template>
                            <template x-if="!logoPreview">
                                <div class="text-center p-2 text-slate-400">
                                    <i data-lucide="image" class="w-8 h-8 mx-auto mb-1 opacity-60"></i>
                                    <span class="text-[9px] font-semibold block">Belum ada logo</span>
                                </div>
                            </template>
                        </div>

                        <div class="flex-1 space-y-2 text-left w-full">
                            <input type="file" name="logo" id="biz_logo_input" accept="image/*" @change="handleLogoChange($event)"
                                   class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500 file:cursor-pointer cursor-pointer">
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Format didukung: PNG, JPG, WEBP, SVG. Maksimal 2MB. Format transparan direkomendasikan.
                            </p>

                            @if($business->logo_path)
                            <label class="inline-flex items-center gap-2 mt-1 cursor-pointer select-none">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 dark:border-slate-700 text-rose-600 focus:ring-rose-500/20">
                                <span class="text-xs text-rose-600 dark:text-rose-400 font-bold">Hapus logo saat ini</span>
                            </label>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Informasi Dasar & Kontak --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 text-xs mb-1.5">
                            Nama Usaha / Perusahaan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ $business->name }}" required
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 text-xs mb-1.5">
                            NPWP / Identitas Pajak
                        </label>
                        <input type="text" name="tax_identification_number" value="{{ $business->tax_identification_number }}" placeholder="01.234.567.8-901.000"
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 text-xs mb-1.5">
                            No. Telepon / WhatsApp Resmi
                        </label>
                        <input type="text" name="phone" value="{{ $business->phone }}" placeholder="0812-3456-7890"
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 text-xs mb-1.5">
                            Email Penagihan & Operasional
                        </label>
                        <input type="email" name="email" value="{{ $business->email }}" placeholder="finance@perusahaan.com"
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 text-xs mb-1.5">
                        Alamat Kantor / Workshop (Kop Surat)
                    </label>
                    <textarea name="address" rows="2" placeholder="Jl. Sudirman No. 45, Gedung Cyber Lt. 5, Jakarta..."
                              class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition resize-none">{{ $business->address }}</textarea>
                </div>

                {{-- Pengaturan Pajak & Tampilan POS --}}
                <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80 space-y-4">
                    <div>
                        <label class="block font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider text-[11px]">
                            Konfigurasi Kasir (Point of Sale)
                        </label>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Atur pemungutan pajak penjualan dan tampilan visual kartu menu pada terminal kasir.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 cursor-pointer shadow-2xs">
                            <input type="hidden" name="pos_enable_tax" value="0">
                            <input type="checkbox" name="pos_enable_tax" value="1" {{ $business->pos_enable_tax ? 'checked' : '' }}
                                   class="rounded border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500/20 w-4 h-4">
                            <span>Gunakan Pajak Penjualan (PPN/PB1) di POS</span>
                        </label>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 text-xs mb-1.5">
                                Persentase Pajak (%)
                            </label>
                            <input type="number" name="pos_tax_percent" value="{{ $business->pos_tax_percent ?? 0 }}" min="0" max="100" step="0.01"
                                   class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-3.5 py-2 text-slate-900 dark:text-white text-xs sm:text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                        </div>
                    </div>

                    <label class="flex items-center gap-2.5 p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 cursor-pointer shadow-2xs">
                        <input type="hidden" name="pos_show_product_images" value="0">
                        <input type="checkbox" name="pos_show_product_images" value="1" {{ $business->pos_show_product_images ? 'checked' : '' }}
                               class="rounded border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500/20 w-4 h-4">
                        <span>Tampilkan Gambar / Foto Produk pada Kartu Menu POS</span>
                    </label>
                </div>

                {{-- Informasi Rekening Bank Resmi --}}
                <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80 space-y-3">
                    <label class="block font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                        <span>Instruksi Transfer Faktur (Rekening Bank Resmi)</span>
                    </label>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Rincian rekening ini otomatis dicetak pada bagian bawah Faktur Penjualan resmi untuk memudahkan pelanggan mentransfer pembayaran.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 text-xs mb-1.5">Nama Bank</label>
                            <input type="text" name="bank_name" value="{{ $business->bank_name }}" placeholder="BCA / Mandiri / BNI / BRI"
                                   class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-3.5 py-2 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 text-xs mb-1.5">Nomor Rekening</label>
                            <input type="text" name="bank_account_number" value="{{ $business->bank_account_number }}" placeholder="123-456-7890"
                                   class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-3.5 py-2 text-slate-900 dark:text-white text-xs sm:text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 text-xs mb-1.5">Atas Nama Pemilik</label>
                            <input type="text" name="bank_account_holder" value="{{ $business->bank_account_holder }}" placeholder="PT Usaha Bersama"
                                   class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-3.5 py-2 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                        </div>
                    </div>
                </div>

                {{-- Mata Uang & Pembulatan --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 text-xs mb-1.5">Kode Mata Uang</label>
                        <input type="text" name="currency_code" value="{{ $business->currency_code }}" required
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 text-xs mb-1.5">Simbol Mata Uang</label>
                        <input type="text" name="currency_symbol" value="{{ $business->currency_symbol }}" required
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3.5 py-2.5 text-slate-900 dark:text-white text-xs sm:text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                    </div>
                </div>

                {{-- Strategi Pembulatan Output Akhir --}}
                <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80 space-y-3">
                    <label class="block font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                        <i data-lucide="calculator" class="w-4 h-4"></i>
                        <span>Strategi Pembulatan Output Akhir (§37 Blueprint)</span>
                    </label>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                        Diterapkan khusus pada HPP per unit final dan Harga Jual akhir produk. Perhitungan kalkulasi internal tetap mempertahankan presisi desimal floating-point murni.
                    </p>

                    <select name="rounding_strategy" required x-model="roundingStrategy"
                            class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-mono text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition cursor-pointer">
                        <option value="ROUND">ROUND — Pembulatan Standar Terdekat</option>
                        <option value="CEIL">CEIL — Pembulatan Ke Atas (Plafon)</option>
                        <option value="FLOOR">FLOOR — Pembulatan Ke Bawah</option>
                        <option value="ROUND_50">ROUND_50 — Kelipatan 50 Terdekat</option>
                        <option value="ROUND_100">ROUND_100 — Kelipatan 100 Terdekat (Standar UMKM)</option>
                        <option value="ROUND_500">ROUND_500 — Kelipatan 500 Terdekat</option>
                        <option value="ROUND_1000">ROUND_1000 — Kelipatan 1.000 Terdekat</option>
                    </select>

                    <div class="p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-xs text-emerald-800 dark:text-emerald-300 font-mono">
                        <span class="font-bold">Simulasi Hasil:</span> <span x-text="getRoundingExample(roundingStrategy)"></span>
                    </div>
                </div>

                {{-- Action Submit --}}
                <div class="pt-3 flex items-center justify-end border-t border-slate-100 dark:border-slate-800">
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm hover:shadow transition inline-flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Profil & Pengaturan Bisnis</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== TAB 2: 20 PRESET TEMPLATE INDUSTRI (§35) ===== --}}
    <div x-show="activeTab === 'templates'" class="space-y-6" style="display: none;">
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-5 h-5 text-amber-500"></i>
                        <span>Preset Template Industri Siap Pakai</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Terapkan struktur kategori dan komponen biaya default secara instan tanpa mengunci kebebasan kustomisasi mandiri Anda.
                    </p>
                </div>

                {{-- Search Template --}}
                <div class="relative w-full md:w-64">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    <input type="text" x-model="templateSearch" placeholder="Cari nama template / industri..."
                           class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                </div>
            </div>

            {{-- Filter Category Pills --}}
            <div class="flex items-center gap-2 flex-wrap pt-1 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="selectedCategory = 'all'"
                        class="px-3 py-1 rounded-lg text-xs font-bold transition-all"
                        :class="selectedCategory === 'all' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                    Semua Kategori
                </button>
                <button type="button" @click="selectedCategory = 'fnb'"
                        class="px-3 py-1 rounded-lg text-xs font-bold transition-all"
                        :class="selectedCategory === 'fnb' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                    Kuliner & F&B
                </button>
                <button type="button" @click="selectedCategory = 'retail'"
                        class="px-3 py-1 rounded-lg text-xs font-bold transition-all"
                        :class="selectedCategory === 'retail' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                    Retail & Toko
                </button>
                <button type="button" @click="selectedCategory = 'manufacturing'"
                        class="px-3 py-1 rounded-lg text-xs font-bold transition-all"
                        :class="selectedCategory === 'manufacturing' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                    Manufaktur & Produksi
                </button>
                <button type="button" @click="selectedCategory = 'service'"
                        class="px-3 py-1 rounded-lg text-xs font-bold transition-all"
                        :class="selectedCategory === 'service' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                    Jasa & Kreatif
                </button>
                <button type="button" @click="selectedCategory = 'agriculture'"
                        class="px-3 py-1 rounded-lg text-xs font-bold transition-all"
                        :class="selectedCategory === 'agriculture' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                    Agribisnis & Pertanian
                </button>
            </div>
        </div>

        {{-- Templates Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($templates as $tmpl)
            <div x-show="(selectedCategory === 'all' || '{{ strtolower($tmpl->industry_category) }}'.includes(selectedCategory)) && (!templateSearch || '{{ strtolower($tmpl->name . ' ' . $tmpl->description . ' ' . $tmpl->industry_category) }}'.includes(templateSearch.toLowerCase()))"
                 class="p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-2xs hover:shadow-md transition-all flex flex-col justify-between space-y-4 group">
                <div>
                    <div class="flex items-center justify-between mb-2.5">
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30 font-mono font-bold text-[10px] uppercase">
                            {{ $tmpl->industry_category }}
                        </span>
                        <span class="text-[11px] text-slate-400 font-mono font-semibold">
                            {{ $tmpl->recommended_costing_method }}
                        </span>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                        {{ $tmpl->name }}
                    </h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 line-clamp-2 leading-relaxed">
                        {{ $tmpl->description }}
                    </p>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                    <div class="text-[11px] text-slate-400 font-medium">
                        {{ count($tmpl->default_cost_components ?? []) }} Komponen Biaya
                    </div>

                    <form method="POST" action="{{ route('settings.apply-template') }}"
                          onsubmit="return AppAlert.confirmSubmit(event, this, 'Terapkan template {{ addslashes($tmpl->name) }} ke bisnis Anda? Kategori dan akun biaya default akan ditambahkan.', 'Terapkan Template?', 'info')">
                        @csrf
                        <input type="hidden" name="template_code" value="{{ $tmpl->code }}">
                        <button type="submit"
                                class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/20 font-bold text-xs transition inline-flex items-center gap-1.5">
                            <i data-lucide="download-cloud" class="w-3.5 h-3.5"></i>
                            <span>Terapkan</span>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
