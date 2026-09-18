@extends('layouts.admin')

@section('title', 'Edit ' . $legalPage->title . ' | Cooca Admin CMS')

@section('content')
<div x-data="{ activeTab: 'general' }" class="space-y-6">

    {{-- Breadcrumb & Top Bar Bento --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md shadow-xs">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-[12px] font-medium text-black/50 dark:text-white/50">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-black dark:hover:text-white transition-colors">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.legal-pages.index') }}" class="hover:text-black dark:hover:text-white transition-colors">Kebijakan &amp; Legalitas</a>
                <span>/</span>
                <span class="text-black dark:text-white font-semibold truncate max-w-xs">{{ $legalPage->slug }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-black dark:text-white flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-[10px] {{ $legalPage->slug === 'privacy-policy' ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-[#FF9500]/10 text-[#FF9500]' }} flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $legalPage->slug === 'privacy-policy' ? 'shield' : 'file-check' }}" class="w-4.5 h-4.5" stroke-width="2"></i>
                </div>
                <span>Edit {{ $legalPage->title }}</span>
            </h1>
            <p class="text-[13px] text-black/60 dark:text-white/60">
                Perbarui isi naskah klausul hukum, rincian pasal khusus owner vs customer, dan metadata publikasi.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('admin.legal-pages.index') }}"
                class="h-10 px-4 rounded-[12px] text-[12px] font-bold text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/[0.06] dark:border-white/[0.08] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5" stroke-width="2"></i>
                <span>Kembali</span>
            </a>
            <a href="https://cooca.id/{{ $legalPage->slug === 'privacy-policy' ? 'privacy' : 'terms' }}" target="_blank"
                class="h-10 px-4 rounded-[12px] text-[12px] font-bold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 border border-[#007AFF]/20 active:scale-[0.98] transition-all inline-flex items-center gap-1.5 cursor-pointer">
                <span>Buka Tayangan Publik</span>
                <i data-lucide="external-link" class="w-3.5 h-3.5" stroke-width="2"></i>
            </a>
        </div>
    </div>

    {{-- Main Edit Form --}}
    <form method="POST" action="{{ route('admin.legal-pages.update', $legalPage) }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Segmented Controls for Tabs (Apple HIG Style) --}}
        <div class="p-1.5 rounded-[18px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] flex items-center gap-1 overflow-x-auto">
            <button type="button" @click="activeTab = 'general'"
                :class="activeTab === 'general' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="h-10 px-4 sm:px-5 rounded-[13px] text-[12.5px] sm:text-[13px] transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i data-lucide="layers" class="w-4 h-4"></i>
                <span>1. Ketentuan Umum Platform</span>
            </button>

            <button type="button" @click="activeTab = 'owner'"
                :class="activeTab === 'owner' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] dark:text-[#0A84FF] shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="h-10 px-4 sm:px-5 rounded-[13px] text-[12.5px] sm:text-[13px] transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i data-lucide="briefcase" class="w-4 h-4"></i>
                <span>2. Khusus Pemilik Usaha (Owner UMKM)</span>
            </button>

            <button type="button" @click="activeTab = 'customer'"
                :class="activeTab === 'customer' ? 'bg-white dark:bg-[#2C2C2E] text-[#34C759] dark:text-[#30D158] shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="h-10 px-4 sm:px-5 rounded-[13px] text-[12.5px] sm:text-[13px] transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                <span>3. Khusus Pelanggan Toko (Customer)</span>
            </button>

            <button type="button" @click="activeTab = 'seo'"
                :class="activeTab === 'seo' ? 'bg-white dark:bg-[#2C2C2E] text-[#AF52DE] dark:text-[#BF5AF2] shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="h-10 px-4 sm:px-5 rounded-[13px] text-[12.5px] sm:text-[13px] transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <i data-lucide="settings" class="w-4 h-4"></i>
                <span>4. Metadata &amp; SEO</span>
            </button>
        </div>

        {{-- TAB 1: KETENTUAN UMUM --}}
        <div x-show="activeTab === 'general'" x-transition class="space-y-4">
            <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 space-y-4 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Naskah Ketentuan Umum &amp; Kerangka Hukum Platform</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Memuat pendahuluan, landasan hukum UU PDP / KUHPerdata, integrasi pihak ketiga, dan batasan tanggung jawab.</p>
                    </div>
                    <span class="text-[11px] font-mono px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60">HTML Editor</span>
                </div>

                <div>
                    <textarea name="content_general" id="editor_content_general" class="tinymce-editor w-full h-96 p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.08] font-mono text-[13px]">{{ old('content_general', $legalPage->content_general) }}</textarea>
                </div>
            </div>
        </div>

        {{-- TAB 2: KHUSUS OWNER --}}
        <div x-show="activeTab === 'owner'" x-transition class="space-y-4">
            <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 space-y-4 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div>
                        <h3 class="text-[16px] font-bold text-[#007AFF] dark:text-[#0A84FF]">Ketentuan Khusus Pemilik Usaha &amp; Mitra UMKM (Owner / Merchant)</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Aturan operasional SaaS, sistem penampungan dana escrow settlement TriPay, etika produk toko, HPP, kuota AI/storage, dan logistik Biteship.</p>
                    </div>
                    <span class="text-[11px] font-mono px-2.5 py-1 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] font-bold">Mitra Merchant</span>
                </div>

                <div>
                    <textarea name="content_owner" id="editor_content_owner" class="tinymce-editor w-full h-96 p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.08] font-mono text-[13px]">{{ old('content_owner', $legalPage->content_owner) }}</textarea>
                </div>
            </div>
        </div>

        {{-- TAB 3: KHUSUS CUSTOMER --}}
        <div x-show="activeTab === 'customer'" x-transition class="space-y-4">
            <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 space-y-4 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div>
                        <h3 class="text-[16px] font-bold text-[#34C759] dark:text-[#30D158]">Ketentuan Khusus Pelanggan Toko &amp; Pembeli (Customer / End-User)</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Aturan pembayaran pesanan online/QR dining, alamat kirim, kebijakan retur dengan video unboxing, kuitansi digital WhatsApp, dan larangan order fiktif.</p>
                    </div>
                    <span class="text-[11px] font-mono px-2.5 py-1 rounded-[8px] bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] font-bold">Pelanggan Toko</span>
                </div>

                <div>
                    <textarea name="content_customer" id="editor_content_customer" class="tinymce-editor w-full h-96 p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.08] font-mono text-[13px]">{{ old('content_customer', $legalPage->content_customer) }}</textarea>
                </div>
            </div>
        </div>

        {{-- TAB 4: METADATA & SEO --}}
        <div x-show="activeTab === 'seo'" x-transition class="space-y-4">
            <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 space-y-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Informasi Dokumen &amp; Pengaturan SEO</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Judul utama, nomor versi, tanggal berlaku, dan meta tag untuk mesin pencari.</p>
                    </div>
                    <span class="text-[11px] font-mono px-2.5 py-1 rounded-[8px] bg-[#AF52DE]/10 text-[#AF52DE] font-bold">Meta Tag</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Judul Dokumen Resmi <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title', $legalPage->title) }}" required
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Nomor Versi Dokumen <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="version" value="{{ old('version', $legalPage->version) }}" required
                            placeholder="Contoh: 2.1"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Tanggal Efektif Berlaku <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="date" name="effective_date" value="{{ old('effective_date', $legalPage->effective_date ? $legalPage->effective_date->format('Y-m-d') : date('Y-m-d')) }}" required
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Meta Title SEO
                        </label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $legalPage->meta_title) }}"
                            placeholder="Judul yang tampil pada tab browser & hasil pencarian Google"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                        Subjudul / Ringkasan Dokumen
                    </label>
                    <textarea name="subtitle" rows="2"
                        class="w-full p-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">{{ old('subtitle', $legalPage->subtitle) }}</textarea>
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                        Meta Description SEO
                    </label>
                    <textarea name="meta_description" rows="2"
                        class="w-full p-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">{{ old('meta_description', $legalPage->meta_description) }}</textarea>
                </div>

                <div class="pt-2">
                    <label class="flex items-start gap-3 cursor-pointer p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors border border-black/[0.04] dark:border-white/[0.04]">
                        <input type="checkbox" name="is_published" value="1"
                            {{ $legalPage->is_published ? 'checked' : '' }}
                            class="w-5 h-5 rounded-[6px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]/30 mt-0.5 cursor-pointer">
                        <div>
                            <div class="text-[13px] font-bold text-black dark:text-white">Publikasikan Halaman Ini</div>
                            <div class="text-[11px] text-black/50 dark:text-white/50 leading-relaxed mt-0.5">Jika dicentang, naskah ini akan dapat diakses secara publik oleh seluruh pengunjung dan mesin pencari.</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Floating / Sticky Action Bar --}}
        <div class="sticky bottom-6 z-30 flex flex-col sm:flex-row items-center justify-between gap-4 p-5 rounded-[22px] bg-white/90 dark:bg-[#1C1C1E]/90 border border-black/[0.08] dark:border-white/[0.1] backdrop-blur-xl shadow-lg shadow-black/5">
            <div class="flex items-center gap-2 text-[12px] text-black/60 dark:text-white/60">
                <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0" stroke-width="2"></i>
                <span>Perubahan naskah legalitas akan langsung tersimpan ke basis data dan ditampilkan di portal publik.</span>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button type="submit"
                    class="w-full sm:w-auto h-12 sm:h-11 px-7 rounded-[14px] text-[13px] sm:text-[14px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25 cursor-pointer">
                    <i data-lucide="save" class="w-4.5 h-4.5" stroke-width="2"></i>
                    <span>Simpan Dokumen</span>
                </button>
            </div>
        </div>

    </form>

</div>

{{-- TinyMCE Free CDN Script --}}
<script src="https://cdn.tiny.cloud/1/2a7oruubgvqukc1gach4pq8j3pm4q12ooot480ieevdxu15m/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: 'textarea.tinymce-editor',
                plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
                min_height: 400,
                menubar: false,
                branding: false,
                promotion: false,
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", Inter, sans-serif; font-size: 14px; line-height: 1.6; color: #1C1C1E; }',
            });
        }
    });
</script>
@endsection
