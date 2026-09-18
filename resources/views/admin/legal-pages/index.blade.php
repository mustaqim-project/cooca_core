@extends('layouts.admin')

@section('title', 'CMS Kebijakan & Dokumen Legalitas | Cooca Admin')

@section('content')
<div class="space-y-6">

    {{-- Breadcrumb & Header Bento --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md shadow-xs">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-[12px] font-medium text-black/50 dark:text-white/50">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-black dark:hover:text-white transition-colors">Admin</a>
                <span>/</span>
                <span class="text-black dark:text-white font-semibold">Kebijakan &amp; Legalitas</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-black dark:text-white flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="scale" class="w-4.5 h-4.5" stroke-width="2"></i>
                </div>
                <span>Pusat Tata Kelola Dokumen Legalitas &amp; Privasi</span>
            </h1>
            <p class="text-[13px] text-black/60 dark:text-white/60">
                Kelola naskah resmi Kebijakan Privasi (UU PDP No. 27/2022) dan Syarat &amp; Ketentuan Layanan untuk Pemilik Usaha (Owner) dan Pembeli (Customer).
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="https://cooca.id/privacy" target="_blank"
                class="h-10 px-4 rounded-[12px] text-[12px] font-bold text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/[0.06] dark:border-white/[0.08] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 cursor-pointer">
                <span>Preview Privasi</span>
                <i data-lucide="external-link" class="w-3.5 h-3.5" stroke-width="2"></i>
            </a>
            <a href="https://cooca.id/terms" target="_blank"
                class="h-10 px-4 rounded-[12px] text-[12px] font-bold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 border border-[#007AFF]/20 active:scale-[0.98] transition-all inline-flex items-center gap-1.5 cursor-pointer">
                <span>Preview Syarat Layanan</span>
                <i data-lucide="external-link" class="w-3.5 h-3.5" stroke-width="2"></i>
            </a>
        </div>
    </div>

    {{-- Bento Comparison Card: Privacy Policy vs Terms & Conditions --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-5 rounded-[22px] bg-gradient-to-br from-[#007AFF]/10 via-[#007AFF]/5 to-transparent border border-[#007AFF]/20 backdrop-blur-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 text-[12px] font-bold text-[#007AFF] dark:text-[#0A84FF]">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span>Kebijakan Privasi (Privacy Policy)</span>
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-[6px] bg-[#007AFF]/15 text-[#007AFF]">UU PDP 27/2022</span>
            </div>
            <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                Fokus pada <strong>Pelindungan Data Pribadi &amp; Bisnis</strong>: Pengumpulan data, enkripsi AES-256 simetris, integritas data multi-tenant, pemrosesan pihak ketiga (TriPay, Biteship, WhatsApp, Meta, TikTok), serta hak subjek data (penghapusan, salinan data, penarikan izin).
            </p>
        </div>

        <div class="p-5 rounded-[22px] bg-gradient-to-br from-[#FF9500]/10 via-[#FF9500]/5 to-transparent border border-[#FF9500]/20 backdrop-blur-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 text-[12px] font-bold text-[#B25E00] dark:text-[#FF9F0A]">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>Syarat &amp; Ketentuan (Terms of Service)</span>
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-[6px] bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]">KUHPerdata &amp; ITE</span>
            </div>
            <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                Fokus pada <strong>Perjanjian Kontrak Layanan</strong>: Paket langganan SaaS, sistem penampungan dana (escrow settlement), larangan barang terlarang, kewajiban pengemasan kurir, retur unboxing video, batasan tanggung jawab ganti rugi, dan penyelesaian sengketa.
            </p>
        </div>
    </div>

    {{-- Legal Pages Cards Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($pages as $page)
            <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 space-y-5 shadow-xs flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-3 pb-4 border-b border-black/[0.04] dark:border-white/[0.06]">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-[6px] bg-black/[0.05] dark:bg-white/[0.08] font-mono text-[11px] text-black/70 dark:text-white/70 font-semibold">
                                    /{{ $page->slug }}
                                </span>
                                <span class="px-2 py-0.5 rounded-[6px] bg-[#007AFF]/10 text-[#007AFF] font-mono text-[11px] font-bold">
                                    v{{ $page->version }}
                                </span>
                                @if($page->is_published)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                                        <span>Aktif Publik</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/15 text-[#FF3B30]">
                                        <i data-lucide="eye-off" class="w-3 h-3"></i>
                                        <span>Nonaktif</span>
                                    </span>
                                @endif
                            </div>
                            <h2 class="text-[17px] sm:text-[18px] font-bold text-black dark:text-white pt-1">
                                {{ $page->title }}
                            </h2>
                        </div>
                        <div class="w-10 h-10 rounded-[14px] {{ $page->slug === 'privacy-policy' ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-[#FF9500]/10 text-[#FF9500]' }} flex items-center justify-center shrink-0">
                            <i data-lucide="{{ $page->slug === 'privacy-policy' ? 'shield' : 'file-check' }}" class="w-5 h-5"></i>
                        </div>
                    </div>

                    <p class="text-[13px] text-black/65 dark:text-white/65 leading-relaxed">
                        {{ $page->subtitle ?? 'Tidak ada ringkasan.' }}
                    </p>

                    {{-- Audience Content Status Badges --}}
                    <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.04] space-y-2">
                        <span class="text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider block">Kesiapan Naskah Spesifik:</span>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="p-2 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.06]">
                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block">Umum</span>
                                <span class="text-[11px] font-bold {{ !empty($page->content_general) ? 'text-[#34C759]' : 'text-[#FF3B30]' }}">
                                    {{ !empty($page->content_general) ? 'Lengkap' : 'Kosong' }}
                                </span>
                            </div>
                            <div class="p-2 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.06]">
                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block">Owner UMKM</span>
                                <span class="text-[11px] font-bold {{ !empty($page->content_owner) ? 'text-[#007AFF]' : 'text-[#FF3B30]' }}">
                                    {{ !empty($page->content_owner) ? 'Lengkap' : 'Kosong' }}
                                </span>
                            </div>
                            <div class="p-2 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/[0.04] dark:border-white/[0.06]">
                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block">Customer</span>
                                <span class="text-[11px] font-bold {{ !empty($page->content_customer) ? 'text-[#FF9500]' : 'text-[#FF3B30]' }}">
                                    {{ !empty($page->content_customer) ? 'Lengkap' : 'Kosong' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between gap-3">
                    <div class="text-[11px] text-black/45 dark:text-white/45">
                        Efektif: <strong>{{ $page->effective_date ? $page->effective_date->format('d M Y') : '-' }}</strong>
                    </div>

                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('admin.legal-pages.toggle', $page) }}">
                            @csrf
                            <button type="submit"
                                class="h-9 px-3 rounded-[10px] text-[11.5px] font-bold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/10 transition-colors cursor-pointer"
                                title="Klik untuk mengaktifkan / menonaktifkan tayangan">
                                {{ $page->is_published ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>

                        <a href="{{ route('admin.legal-pages.edit', $page) }}"
                            class="h-9 px-4 rounded-[10px] text-[12px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-95 transition-all inline-flex items-center gap-1.5 shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5" stroke-width="2"></i>
                            <span>Edit Dokumen</span>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2 p-12 text-center rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08]">
                <p class="text-[14px] text-black/50 dark:text-white/50">Belum ada dokumen legal yang terdaftar. Jalankan seeder LegalPagesSeeder.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
