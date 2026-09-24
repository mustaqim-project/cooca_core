@extends('layouts.public_marketing')

@section('title', 'Download Gratis: ' . $template['name'] . ' | COOCA')
@section('description', 'Download gratis ' . $template['name'] . '. ' . $template['description'])
@section('og_title', 'Download Gratis: ' . $template['name'] . ' | COOCA')
@section('og_description', 'Download gratis ' . $template['name'] . '. ' . $template['description'])
@section('canonical', route('template.show', $template['slug']))
@section('og_type', 'article')
@section('keywords', strtolower($template['name']) . ', download excel umkm, template gratis pembukuan toko, format laporan usaha excel')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "{{ addslashes($template['name']) }}",
  "operatingSystem": "Windows, macOS, Android, iOS (Excel / Google Sheets)",
  "applicationCategory": "BusinessApplication",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "IDR"
  },
  "description": "{{ addslashes($template['description']) }}"
}
</script>
@endpush

@section('content')
<div x-data="{
    refreshIcons() {
        this.$nextTick(() => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    }
}" x-init="refreshIcons()"
class="w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors pb-24">

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 1. HERO SECTION: 2-Grid Bento Apple HIG Canvas ══════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <section class="pt-10 sm:pt-14 pb-12 sm:pb-16 border-b border-black/[0.06] dark:border-white/[0.08]">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors">Beranda</a>
                <span aria-hidden="true" class="text-slate-300 dark:text-slate-700">/</span>
                <a href="{{ route('template.index') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors">Template Spreadsheet</a>
                <span aria-hidden="true" class="text-slate-300 dark:text-slate-700">/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold truncate max-w-[200px] sm:max-w-none" aria-current="page">{{ $template['name'] }}</span>
            </nav>

            <!-- 2-Grid: Details (Left 7 cols) + Lead Capture / Download (Right 5 cols) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">

                <!-- Left: Description & Highlights (Mobile Center, Desktop Left ~ 7 Cols) -->
                <div class="lg:col-span-7 space-y-6 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                    <div class="space-y-3 w-full">
                        <!-- Pure Typographic Overline Kicker (Zero Pill Abuse) -->
                        <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                            {{ $template['category'] }} • 100% Bebas Biaya
                        </p>

                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold text-slate-900 dark:text-white leading-[1.15] tracking-tight text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                            {{ $template['name'] }}
                        </h1>
                    </div>

                    <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 leading-relaxed font-normal text-pretty max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                        {{ $template['description'] }}
                    </p>

                    <!-- Feature Bento Card -->
                    @if (!empty($template['highlights']))
                        <div class="p-6 sm:p-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-4 shadow-sm w-full text-left">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF] flex items-center gap-2">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                <span>Keunggulan Formula Dalam Template Ini:</span>
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @foreach ($template['highlights'] as $hl)
                                    <div class="p-3 rounded-[12px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.03] dark:border-white/[0.04] flex items-start gap-2.5">
                                        <div class="w-5 h-5 rounded-[6px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                        </div>
                                        <span class="text-xs sm:text-sm text-slate-800 dark:text-slate-200 leading-snug font-medium text-pretty">{{ $hl }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Trust Signal Inset Box -->
                    <div class="p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] text-xs text-slate-600 dark:text-slate-400 flex items-center gap-3.5 shadow-sm w-full text-left">
                        <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                        <div class="space-y-0.5">
                            <span class="font-bold text-sm text-slate-900 dark:text-white block">Aman &amp; Kompatibel Universal</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">Kompatibel dengan Microsoft Excel 2013+, Google Sheets, dan WPS Office tanpa macro VBA berbahaya.</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Lead Capture Form Card (5 Cols Sticky) -->
                <div class="lg:col-span-5 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-6 sm:p-8 rounded-[24px] shadow-sm lg:sticky lg:top-24 text-slate-900 dark:text-white"
                    x-data="{
                        submitted: false,
                        loading: false,
                        errorMessage: '',
                        downloadUrl: '{{ route('template.file', $template['slug']) }}',
                        fileName: '{{ $template['file_name'] }}',
                        fileSize: '{{ $template['formatted_file_size'] ?? '' }}',
                        form: {
                            name: '',
                            phone: '',
                            email: '',
                            business_name: ''
                        },
                        async submitLead() {
                            if (!this.form.name || !this.form.phone) {
                                this.errorMessage = 'Nama dan Nomor WhatsApp wajib diisi.';
                                return;
                            }
                            this.loading = true;
                            this.errorMessage = '';
                            try {
                                let response = await fetch('{{ route('template.download', $template['slug']) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify(this.form)
                                });
                                let data = await response.json();
                                if (data.success) {
                                    if (data.download_url) {
                                        this.downloadUrl = data.download_url;
                                    }
                                    if (data.file_name) {
                                        this.fileName = data.file_name;
                                    }
                                    if (data.file_size) {
                                        this.fileSize = data.file_size;
                                    }
                                    this.submitted = true;
                                    window.location.href = this.downloadUrl;
                                } else {
                                    this.errorMessage = data.message || 'Terjadi kesalahan, silakan coba lagi.';
                                }
                            } catch (e) {
                                this.submitted = true;
                                window.location.href = this.downloadUrl;
                            } finally {
                                this.loading = false;
                            }
                        }
                    }">
                    <!-- State 1: Form Input -->
                    <div x-show="!submitted">
                        <div class="text-center mb-6 space-y-2">
                            <div class="w-12 h-12 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center mx-auto mb-2">
                                <i data-lucide="download" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Unduh Spreadsheet Gratis</h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                Masukkan kontak Anda agar kami dapat mengirimkan file serta pembaruan rumus terbaru.
                            </p>
                        </div>

                        <form @submit.prevent="submitLead" class="space-y-4">
                            <div x-show="errorMessage"
                                class="p-3.5 rounded-[12px] bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-semibold"
                                x-text="errorMessage"></div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Nama Lengkap *
                                </label>
                                <input type="text" x-model="form.name" required placeholder="Contoh: Budi Santoso"
                                    class="w-full h-11 px-4 bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[12px] text-slate-900 dark:text-white text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition placeholder-slate-400">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Nomor WhatsApp / HP *
                                </label>
                                <input type="tel" x-model="form.phone" required placeholder="Contoh: 081234567890"
                                    class="w-full h-11 px-4 bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[12px] text-slate-900 dark:text-white text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition placeholder-slate-400">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Nama Usaha / Toko (Opsional)
                                </label>
                                <input type="text" x-model="form.business_name"
                                    placeholder="Contoh: Toko Berkah Mandiri"
                                    class="w-full h-11 px-4 bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[12px] text-slate-900 dark:text-white text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition placeholder-slate-400">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Email (Opsional)
                                </label>
                                <input type="email" x-model="form.email" placeholder="email@contohtoko.com"
                                    class="w-full h-11 px-4 bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.12] rounded-[12px] text-slate-900 dark:text-white text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition placeholder-slate-400">
                            </div>

                            <button type="submit" :disabled="loading"
                                class="w-full h-12 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-xs active:scale-[0.98] transition">
                                <span x-text="loading ? 'Menyiapkan File...' : 'Download File Excel (.xlsx)'"></span>
                                <i data-lucide="arrow-down" class="w-4 h-4"></i>
                            </button>
                        </form>

                        <p class="text-[11px] text-slate-400 text-center mt-4">
                            Data Anda dijamin aman. Bebas spam 100%.
                        </p>
                    </div>

                    <!-- State 2: Download Ready! -->
                    <div x-show="submitted" x-cloak class="text-center py-6 space-y-5">
                        <div class="w-14 h-14 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto">
                            <i data-lucide="check-circle" class="w-7 h-7"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">File Template Siap Diunduh</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Unduhan sedang berjalan. Jika unduhan tidak otomatis dimulai, klik tombol di bawah ini:
                        </p>

                        <div class="p-4 rounded-[14px] bg-[#F2F2F7] dark:bg-[#2C2C2E] text-left space-y-2 border border-black/[0.04] dark:border-white/[0.04]">
                            <div class="flex items-center gap-2 text-xs font-mono text-emerald-600 dark:text-emerald-400">
                                <i data-lucide="file-spreadsheet" class="w-4 h-4 shrink-0"></i>
                                <span class="truncate font-semibold" x-text="fileName"></span>
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                                <span>Format: Excel Spreadsheet (.xlsx)</span>
                                <span x-show="fileSize" x-text="'Ukuran: ' + fileSize"></span>
                            </div>
                        </div>

                        <div class="space-y-3 pt-1">
                            <a :href="downloadUrl" download
                                class="w-full h-12 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-xs transition active:scale-[0.98]">
                                <i data-lucide="download" class="w-4 h-4"></i>
                                <span>Unduh Ulang Spreadsheet (.xlsx)</span>
                            </a>
                        </div>

                        <div class="pt-6 border-t border-black/[0.06] dark:border-white/[0.08] mt-4">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Ingin coba aplikasi kasir &amp; pembukuan otomatis?</p>
                            <a href="{{ route('register') }}"
                                class="text-sm font-bold text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1.5">
                                <span>Daftar COOCA Gratis Selamanya</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 2. OTHER TEMPLATES SECTION ═══════════════════════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-8">
        <section class="space-y-6">
            <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">
                Template Bisnis Lainnya untuk Anda
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach ($otherTemplates as $ot)
                    <a href="{{ route('template.show', $ot['slug']) }}"
                        class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-6 rounded-[22px] group flex flex-col justify-between shadow-sm hover:border-[#007AFF]/40 hover:shadow-lg transition-all">
                        <div class="space-y-2">
                            <span class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#0A84FF]">{{ $ot['category'] }}</span>
                            <h4 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug">
                                {{ $ot['name'] }}
                            </h4>
                        </div>
                        <span class="text-xs text-[#007AFF] dark:text-[#0A84FF] font-semibold mt-4 flex items-center gap-1.5">
                            <span>Download Gratis</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>

</div>
@endsection
