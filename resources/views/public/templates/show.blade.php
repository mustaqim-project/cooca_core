@extends('layouts.public_marketing')

@section('title', 'Download Gratis: ' . $template['name'] . ' | Cooca')
@section('description', 'Download gratis ' . $template['name'] . '. ' . $template['description'])
@section('keywords',
    strtolower($template['name']) .
    ', download excel umkm, template gratis pembukuan toko, format
    laporan usaha excel')

@section('content')
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300 min-h-screen">

        <!-- ═══ HERO SECTION: Midnight Dark with Ambient Glows ═══ -->
        <section
            class="relative bg-[#060B1E] text-white pt-8 sm:pt-12 pb-16 lg:pb-20 overflow-hidden border-b border-white/10 w-full min-w-full">
            <!-- Dual Ambient Glows -->
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div
                class="absolute -bottom-40 -left-40 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none">
            </div>

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-6">
                <!-- Breadcrumbs -->
                <nav class="flex items-center gap-2 text-xs text-slate-400">
                    <a href="{{ route('landing') }}" class="hover:text-white transition-colors">Beranda</a>
                    <span>/</span>
                    <a href="{{ route('template.index') }}" class="hover:text-white transition-colors">Template Gratis</a>
                    <span>/</span>
                    <span class="text-[#00C4D8] font-semibold">{{ $template['name'] }}</span>
                </nav>

                <!-- 2-Grid: Details (Left) + Lead Capture / Download (Right) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">

                    <!-- Left: Description & Highlights (7 Cols) -->
                    <div class="lg:col-span-7 space-y-6">
                        <div class="space-y-4">
                            <div
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#007AFF]/15 text-[#00C4D8] border border-[#00C4D8]/30 text-xs font-semibold backdrop-blur-md">
                                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                                <span>{{ $template['category'] }} • 100% Bebas Biaya</span>
                            </div>
                            <h1
                                class="text-4xl sm:text-5xl lg:text-[3.25rem] xl:text-[3.75rem] font-extrabold text-white leading-tight tracking-tight">
                                {{ $template['name'] }}
                            </h1>
                            <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal">
                                {{ $template['description'] }}
                            </p>
                        </div>

                        <!-- Feature Bento Card -->
                        @if (!empty($template['highlights']))
                            <div
                                class="p-6 sm:p-7 rounded-[22px] bg-[#0E1E45]/80 border border-white/10 ring-1 ring-white/10 backdrop-blur-md space-y-4 shadow-xl">
                                <h2 class="text-xs font-bold uppercase tracking-wider text-[#00C4D8] flex items-center gap-2">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#00C4D8]"></i>
                                    <span>Keunggulan Formula Dalam Template Ini:</span>
                                </h2>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    @foreach ($template['highlights'] as $hl)
                                        <div class="p-3 rounded-[14px] bg-white/[0.05] border border-white/10 flex items-start gap-2.5 hover:bg-white/[0.08] transition-colors">
                                            <div class="w-6 h-6 rounded-[8px] bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-500/20">
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            </div>
                                            <span class="text-xs sm:text-sm text-slate-200 leading-snug font-medium text-pretty">{{ $hl }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Trust Signal Inset Box -->
                        <div
                            class="p-5 rounded-xl bg-white/5 border border-white/10 text-xs text-slate-300 flex items-center gap-3.5 shadow-sm">
                            <div
                                class="w-10 h-10 rounded-xl bg-[#007AFF]/20 text-[#00C4D8] border border-[#007AFF]/30 flex items-center justify-center shrink-0">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </div>
                            <div class="space-y-0.5">
                                <span class="font-bold text-sm text-white block">Aman &amp; Kompatibel Penuh</span>
                                <span class="text-xs text-slate-300">Kompatibel dengan Microsoft Excel 2013+, Google Sheets,
                                    dan WPS Office tanpa macro VBA berbahaya.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Lead Capture Form Card (5 Cols Sticky) -->
                    <div class="lg:col-span-5 bg-[#0E1E45]/90 border border-white/10 ring-1 ring-white/10 p-6 sm:p-8 rounded-2xl shadow-2xl backdrop-blur-md lg:sticky lg:top-24 text-white"
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
                                <div
                                    class="w-12 h-12 rounded-xl bg-[#007AFF]/20 text-[#00C4D8] border border-[#007AFF]/30 flex items-center justify-center mx-auto mb-2 shadow-inner">
                                    <i data-lucide="download" class="w-6 h-6"></i>
                                </div>
                                <h3 class="text-xl font-bold text-white">Unduh Spreadsheet Gratis</h3>
                                <p class="text-xs sm:text-sm text-slate-300">
                                    Masukkan kontak Anda agar kami dapat mengirimkan file serta pembaruan rumus terbaru.
                                </p>
                            </div>

                            <form @submit.prevent="submitLead" class="space-y-4">
                                <div x-show="errorMessage"
                                    class="p-3.5 rounded-xl bg-rose-500/20 border border-rose-500/30 text-rose-300 text-xs font-semibold"
                                    x-text="errorMessage"></div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-200 mb-1.5">
                                        Nama Lengkap *
                                    </label>
                                    <input type="text" x-model="form.name" required placeholder="Contoh: Budi Santoso"
                                        class="w-full h-12 px-4 bg-white/10 border border-white/20 rounded-xl text-white text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/30 focus:outline-none transition-all placeholder-slate-400">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-200 mb-1.5">
                                        Nomor WhatsApp / HP *
                                    </label>
                                    <input type="tel" x-model="form.phone" required placeholder="Contoh: 081234567890"
                                        class="w-full h-12 px-4 bg-white/10 border border-white/20 rounded-xl text-white text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/30 focus:outline-none transition-all placeholder-slate-400">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-200 mb-1.5">
                                        Nama Usaha / Toko (Opsional)
                                    </label>
                                    <input type="text" x-model="form.business_name"
                                        placeholder="Contoh: Toko Berkah Mandiri"
                                        class="w-full h-12 px-4 bg-white/10 border border-white/20 rounded-xl text-white text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/30 focus:outline-none transition-all placeholder-slate-400">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-200 mb-1.5">
                                        Email (Opsional)
                                    </label>
                                    <input type="email" x-model="form.email" placeholder="email@contohtoko.com"
                                        class="w-full h-12 px-4 bg-white/10 border border-white/20 rounded-xl text-white text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/30 focus:outline-none transition-all placeholder-slate-400">
                                </div>

                                <button type="submit" :disabled="loading"
                                    class="w-full h-12 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-lg shadow-[#007AFF]/25 active:scale-[0.98] transition-all">
                                    <span x-text="loading ? 'Menyiapkan File...' : 'Download File Excel (.xlsx)'"></span>
                                    <i data-lucide="arrow-down" class="w-4 h-4"></i>
                                </button>
                            </form>

                            <p class="text-xs text-slate-400 text-center mt-4">
                                Data Anda dijamin aman. Bebas spam 100%.
                            </p>
                        </div>

                        <!-- State 2: Download Ready! -->
                        <div x-show="submitted" x-cloak class="text-center py-6 space-y-5">
                            <div
                                class="w-14 h-14 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center mx-auto">
                                <i data-lucide="check-circle" class="w-7 h-7"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white">File Template Siap Diunduh</h3>
                            <p class="text-sm text-slate-300">
                                Unduhan sedang berjalan. Jika unduhan tidak otomatis dimulai, klik tombol di bawah ini:
                            </p>

                            <div class="p-4 rounded-xl bg-white/5 border border-white/10 text-left space-y-2">
                                <div class="flex items-center gap-2 text-xs font-mono text-emerald-400">
                                    <i data-lucide="file-spreadsheet" class="w-4 h-4 shrink-0"></i>
                                    <span class="truncate font-semibold" x-text="fileName"></span>
                                </div>
                                <div class="text-xs text-slate-300 flex items-center justify-between">
                                    <span>Format: Excel Spreadsheet</span>
                                    <span x-show="fileSize" x-text="'Ukuran: ' + fileSize"></span>
                                </div>
                            </div>

                            <div class="space-y-3 pt-1">
                                <a :href="downloadUrl" download
                                    class="w-full h-12 rounded-xl bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-lg shadow-[#007AFF]/25 transition-all active:scale-[0.98]">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    <span>Unduh Ulang Spreadsheet (.xlsx)</span>
                                </a>
                            </div>

                            <div class="pt-6 border-t border-white/10 mt-4">
                                <p class="text-xs text-slate-400 mb-2">Ingin coba aplikasi kasir &amp; pembukuan otomatis?
                                </p>
                                <a href="{{ route('register') }}"
                                    class="text-sm font-bold text-[#00C4D8] hover:underline inline-flex items-center gap-1.5">
                                    <span>Daftar COOCA Gratis Selamanya</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ═══ OTHER TEMPLATES SECTION (Light / Dark Compatible) ═══ -->
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
            <section class="space-y-6">
                <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Template Bisnis Lainnya untuk Anda
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    @foreach ($otherTemplates as $ot)
                        <a href="{{ route('template.show', $ot['slug']) }}"
                            class="bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 rounded-2xl group flex flex-col justify-between shadow-sm hover:border-[#007AFF]/40 hover:shadow-xl hover:shadow-[#007AFF]/5 transition-all">
                            <div class="space-y-2">
                                <span
                                    class="text-xs uppercase font-bold text-[#007AFF] dark:text-[#00C4D8]">{{ $ot['category'] }}</span>
                                <h4
                                    class="text-base font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition-colors leading-snug">
                                    {{ $ot['name'] }}
                                </h4>
                            </div>
                            <span
                                class="text-xs text-[#007AFF] dark:text-[#00C4D8] font-semibold mt-4 flex items-center gap-1.5">
                                <span>Download Gratis</span>
                                <i data-lucide="arrow-right"
                                    class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>

    </div>
@endsection
