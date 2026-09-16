@extends('layouts.public_marketing')

@section('title', 'Download Gratis: ' . $template['name'] . ' | Cooca')
@section('description', 'Download gratis ' . $template['name'] . '. ' . $template['description'])
@section('keywords', strtolower($template['name']) . ', download excel umkm, template gratis pembukuan toko, format
    laporan usaha excel')

@section('content')
    <div class="pt-8 pb-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- Breadcrumbs (Apple Inset Style) -->
            <nav class="flex items-center gap-2 text-xs text-[#6E6E73] dark:text-[#86868B]">
                <a href="{{ route('landing') }}"
                    class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Beranda</a>
                <span>/</span>
                <a href="{{ route('template.index') }}"
                    class="hover:text-[#1D1D1F] dark:hover:text-[#F5F5F7] transition-colors">Template Gratis</a>
                <span>/</span>
                <span class="text-[#007AFF] dark:text-[#0A84FF] font-semibold">{{ $template['name'] }}</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Left: Description & Highlights (7 Cols) -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="space-y-3">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158]">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                            <span>{{ $template['category'] }} • 100% Gratis</span>
                        </div>
                        <h1
                            class="text-2xl sm:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] leading-tight tracking-tight">
                            {{ $template['name'] }}
                        </h1>
                        <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                            {{ $template['description'] }}
                        </p>
                    </div>

                    <!-- Feature List (Apple Inset Box) -->
                    @if (!empty($template['highlights']))
                        <div
                            class="p-5 sm:p-6 rounded-[22px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] space-y-3.5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-[#1D1D1F] dark:text-[#F5F5F7]">Apa
                                yang Anda Dapatkan di Template Ini:</h3>
                            <div class="space-y-2.5">
                                @foreach ($template['highlights'] as $hl)
                                    <div class="flex items-start gap-2.5 text-xs text-[#1D1D1F]/85 dark:text-[#F5F5F7]/85">
                                        <i data-lucide="check"
                                            class="w-4 h-4 text-[#34C759] dark:text-[#30D158] shrink-0 mt-0.5"></i>
                                        <span>{{ $hl }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Trust Signal Inset Box -->
                    <div
                        class="p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] text-xs text-[#6E6E73] dark:text-[#86868B] flex items-center gap-3.5">
                        <div
                            class="w-10 h-10 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="font-bold text-[#1D1D1F] dark:text-[#F5F5F7] block">Aman &amp; Siap
                                Digunakan</span>
                            <span>Kompatibel dengan Microsoft Excel 2013+, Google Sheets, dan WPS Office tanpa macro VBA
                                berbahaya.</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Lead Capture Form Card (5 Cols Sticky) -->
                <div class="lg:col-span-5 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-6 sm:p-7 rounded-[26px] shadow-sm lg:sticky lg:top-24"
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
                                    // Otomatis buka unduhan
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
                        <div class="text-center mb-5 space-y-1.5">
                            <div
                                class="w-12 h-12 rounded-[16px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center mx-auto mb-2">
                                <i data-lucide="download" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Unduh Gratis Sekarang</h3>
                            <p class="text-xs text-[#6E6E73] dark:text-[#86868B]">Masukkan kontak Anda untuk mengunduh
                                spreadsheet resmi.</p>
                        </div>

                        <form @submit.prevent="submitLead" class="space-y-4">
                            <div x-show="errorMessage"
                                class="p-3 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-xs font-medium"
                                x-text="errorMessage"></div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] mb-1.5">Nama
                                    Lengkap *</label>
                                <input type="text" x-model="form.name" required placeholder="Contoh: Budi Santoso"
                                    class="w-full h-11 px-3.5 bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.1] rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-xs sm:text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-[#6E6E73]/50">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] mb-1.5">Nomor
                                    WhatsApp / HP *</label>
                                <input type="tel" x-model="form.phone" required placeholder="Contoh: 081234567890"
                                    class="w-full h-11 px-3.5 bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.1] rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-xs sm:text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-[#6E6E73]/50">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] mb-1.5">Nama
                                    Usaha / Toko</label>
                                <input type="text" x-model="form.business_name" placeholder="Contoh: Toko Sembako Berkah"
                                    class="w-full h-11 px-3.5 bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.1] rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-xs sm:text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-[#6E6E73]/50">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] mb-1.5">Email
                                    (Opsional)</label>
                                <input type="email" x-model="form.email" placeholder="email@domain.com"
                                    class="w-full h-11 px-3.5 bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.1] rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-xs sm:text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-[#6E6E73]/50">
                            </div>

                            <button type="submit" :disabled="loading"
                                class="w-full py-3.5 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                                <span x-text="loading ? 'Menyiapkan File...' : 'Download File Excel Sekarang'"></span>
                                <i data-lucide="arrow-down" class="w-4 h-4"></i>
                            </button>
                        </form>

                        <p class="text-[10px] text-[#6E6E73] dark:text-[#86868B] text-center mt-4">
                            Data Anda aman bersama kami. Bebas spam 100%.
                        </p>
                    </div>

                    <!-- State 2: Download Ready! -->
                    <div x-show="submitted" x-cloak class="text-center py-6 space-y-4">
                        <div
                            class="w-16 h-16 rounded-full bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center mx-auto">
                            <i data-lucide="check-circle" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">File Template Siap Diunduh!</h3>
                        <p class="text-xs text-[#6E6E73] dark:text-[#86868B]">
                            Unduhan dimulai secara otomatis. Jika file belum terunduh, silakan klik tombol di bawah ini:
                        </p>

                        <div
                            class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] text-left space-y-2">
                            <div class="flex items-center gap-2 text-xs font-mono text-[#34C759] dark:text-[#30D158]">
                                <i data-lucide="file-spreadsheet" class="w-4 h-4 shrink-0"></i>
                                <span class="truncate font-semibold" x-text="fileName"></span>
                            </div>
                            <div class="text-[11px] text-[#6E6E73] dark:text-[#86868B] flex items-center justify-between">
                                <span>Format: Excel Spreadsheet</span>
                                <span x-show="fileSize" x-text="'Ukuran: ' + fileSize"></span>
                            </div>
                        </div>

                        <div class="space-y-2.5 pt-1">
                            <a :href="downloadUrl" download
                                class="w-full py-3.5 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-sm transition-all active:scale-[0.98]">
                                <i data-lucide="download" class="w-4 h-4"></i>
                                <span>Klik di Sini untuk Mengunduh Ulang (.xlsx)</span>
                            </a>
                        </div>

                        <div class="pt-6 border-t border-black/[0.06] dark:border-white/[0.08] mt-4">
                            <p class="text-xs text-[#6E6E73] dark:text-[#86868B] mb-2">Ingin coba aplikasi kasir &amp;
                                pembukuan otomatis?</p>
                            <a href="{{ route('register') }}"
                                class="text-xs font-bold text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1">
                                <span>Daftar Cooca Gratis Selamanya</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Other Templates (Bento Row) -->
            <div class="border-t border-black/[0.06] dark:border-white/[0.08] pt-12 space-y-6">
                <h3 class="text-xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Template Bisnis Lainnya untuk Anda</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($otherTemplates as $ot)
                        <a href="{{ route('template.show', $ot['slug']) }}"
                            class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-5 rounded-[22px] group flex flex-col justify-between shadow-sm hover:shadow-md hover:border-[#007AFF]/30 hover:-translate-y-0.5 transition-all">
                            <div class="space-y-2">
                                <span
                                    class="text-[10px] uppercase font-bold text-[#007AFF] dark:text-[#0A84FF]">{{ $ot['category'] }}</span>
                                <h4
                                    class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors leading-snug">
                                    {{ $ot['name'] }}</h4>
                            </div>
                            <span
                                class="text-xs text-[#007AFF] dark:text-[#0A84FF] font-semibold mt-4 flex items-center gap-1.5">
                                <span>Download Gratis</span>
                                <i data-lucide="arrow-right"
                                    class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
@endsection
