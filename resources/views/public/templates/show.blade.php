@extends('layouts.public_marketing')

@section('title', 'Download Gratis: ' . $template['name'] . ' | Cooca UMKM')
@section('description', 'Download gratis ' . $template['name'] . '. ' . $template['description'])
@section('keywords', strtolower($template['name']) . ', download excel umkm, template gratis pembukuan toko, format laporan usaha excel')

@section('content')
<div class="pt-10 pb-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6">
            <a href="{{ route('landing') }}" class="hover:text-white">Beranda</a>
            <span>/</span>
            <a href="{{ route('template.index') }}" class="hover:text-white">Template Gratis</a>
            <span>/</span>
            <span class="text-indigo-400 font-semibold">{{ $template['name'] }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left: Description & Highlights -->
            <div class="lg:col-span-7 space-y-6">
                <div>
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        {{ $template['category'] }} • 100% Gratis
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-white mt-3 leading-tight">
                        {{ $template['name'] }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-300 mt-3 leading-relaxed">
                        {{ $template['description'] }}
                    </p>
                </div>

                <!-- Feature List -->
                <div class="p-5 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Apa yang Anda Dapatkan di Template Ini:</h3>
                    <div class="space-y-2.5">
                        @foreach($template['highlights'] as $hl)
                        <div class="flex items-start gap-2.5 text-xs text-slate-200">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5"></i>
                            <span>{{ $hl }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-indigo-950/30 border border-indigo-500/20 text-xs text-slate-300 flex items-center gap-3">
                    <i data-lucide="shield-check" class="w-8 h-8 text-indigo-400 shrink-0"></i>
                    <div>
                        <span class="font-bold text-white block">Aman &amp; Siap Digunakan</span>
                        <span>Dapat dibuka di Microsoft Excel 2013+, Google Sheets, dan WPS Office tanpa macro berbahaya.</span>
                    </div>
                </div>
            </div>

            <!-- Right: Lead Capture Form Card -->
            <div class="lg:col-span-5 glass-card p-6 sm:p-7 rounded-3xl" x-data="{
                submitted: false,
                loading: false,
                errorMessage: '',
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
                            this.submitted = true;
                        } else {
                            this.errorMessage = data.message || 'Terjadi kesalahan, silakan coba lagi.';
                        }
                    } catch (e) {
                        this.submitted = true; // Fallback to success state
                    } finally {
                        this.loading = false;
                    }
                }
            }">
                <!-- State 1: Form Input -->
                <div x-show="!submitted">
                    <div class="text-center mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="download" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white">Unduh Gratis Sekarang</h3>
                        <p class="text-xs text-slate-400 mt-1">Masukkan kontak untuk membuka link download spreadsheet resmi.</p>
                    </div>

                    <form @submit.prevent="submitLead" class="space-y-4">
                        <div x-show="errorMessage" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs" x-text="errorMessage"></div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Nama Lengkap *</label>
                            <input type="text" x-model="form.name" required placeholder="Contoh: Budi Santoso" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Nomor WhatsApp / HP *</label>
                            <input type="tel" x-model="form.phone" required placeholder="Contoh: 081234567890" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Nama Usaha / Toko</label>
                            <input type="text" x-model="form.business_name" placeholder="Contoh: Toko Sembako Berkah" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Email (Opsional)</label>
                            <input type="email" x-model="form.email" placeholder="email@domain.com" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>

                        <button type="submit" :disabled="loading" class="w-full glow-btn py-3 rounded-xl text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg">
                            <span x-text="loading ? 'Memproses...' : 'Dapatkan Link Download Gratis'"></span>
                            <i data-lucide="arrow-down" class="w-4 h-4"></i>
                        </button>
                    </form>

                    <p class="text-[10px] text-slate-500 text-center mt-4">
                        Data Anda aman bersama kami. Bebas spam 100%.
                    </p>
                </div>

                <!-- State 2: Download Ready! -->
                <div x-show="submitted" x-cloak class="text-center py-6 space-y-4">
                    <div class="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto">
                        <i data-lucide="check-circle" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white">File Template Siap Diunduh!</h3>
                    <p class="text-xs text-slate-300">
                        Terima kasih telah mengunduh <strong>{{ $template['name'] }}</strong>. Klik tombol di bawah untuk membuka template di Google Sheets / Excel:
                    </p>

                    <a href="https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/copy" target="_blank" class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xl shadow-emerald-600/30 transition-all">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        <span>Buka / Salin Template di Google Sheets</span>
                    </a>

                    <div class="pt-6 border-t border-slate-800 mt-4">
                        <p class="text-xs text-slate-400 mb-2">Ingin coba aplikasi kasir &amp; pembukuan otomatis?</p>
                        <a href="{{ route('register') }}" class="text-xs font-bold text-indigo-400 hover:underline">
                            Daftar Cooca UMKM Gratis Selamanya →
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Other Templates -->
        <div class="mt-20 border-t border-slate-800 pt-12">
            <h3 class="text-lg font-bold text-white mb-6">Template Bisnis Lainnya untuk Anda:</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($otherTemplates as $ot)
                <a href="{{ route('template.show', $ot['slug']) }}" class="glass-card p-5 rounded-2xl group flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-indigo-400">{{ $ot['category'] }}</span>
                        <h4 class="text-sm font-bold text-white mt-1 group-hover:text-indigo-400 transition-colors">{{ $ot['name'] }}</h4>
                    </div>
                    <span class="text-xs text-indigo-400 font-bold mt-4 flex items-center gap-1">
                        <span>Download Gratis</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </span>
                </a>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection
