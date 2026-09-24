@extends('layouts.public_marketing')

@section('title', 'Hubungi Tim Dukungan Cooca | WhatsApp Resmi ' . $officialWhatsapp)
@section('description', 'Hubungi tim konsultan dan customer service Cooca. Dapatkan bantuan teknis seputar aplikasi kasir, kalkulator HPP, atau pertanyaan kemitraan.')
@section('og_title', 'Hubungi Tim Dukungan Cooca | WhatsApp Resmi ' . $officialWhatsapp)
@section('og_description', 'Hubungi tim konsultan dan customer service Cooca. Dapatkan bantuan teknis seputar aplikasi kasir atau kemitraan.')
@section('canonical', route('contact'))
@section('og_type', 'website')
@section('keywords', 'kontak Cooca, whatsapp cooca, customer service software kasir, support cooca id, bantuan teknis aplikasi kasir')

    @push('seo')
        <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "ContactPage",
      "name": "Kontak Dukungan Cooca",
      "url": "{{ route('contact') }}",
      "telephone": "{{ $officialWhatsapp }}"
    }
    </script>
    @endpush

@section('content')
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- HERO SECTION (Midnight #060B1E Full-Bleed - Type A Full Viewport) --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <section
            class="relative bg-[#060B1E] text-white min-h-[calc(100svh-84px)] lg:flex lg:items-center py-12 lg:py-16 overflow-hidden border-b border-white/10 w-full min-w-full">
            {{-- Dual Ambient Glows --}}
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>
            <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-[#00C4D8]/10 rounded-full blur-[120px] pointer-events-none">
            </div>

            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-8 w-full">
                {{-- Breadcrumb --}}
                <nav class="flex items-center gap-2 text-xs text-slate-400" aria-label="Breadcrumb">
                    <a href="{{ route('landing') }}" class="hover:text-white transition-colors">Beranda</a>
                    <span aria-hidden="true" class="text-white/20">/</span>
                    <span class="text-[#00C4D8] font-semibold" aria-current="page">Hubungi Kami</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-8 items-center">
                    {{-- Left Column: Copy & Core Actions (Mobile Center, Desktop Left ~ 5 Cols) --}}
                    <div class="lg:col-span-5 space-y-6 text-center mx-auto flex flex-col items-center lg:text-left lg:items-start lg:mx-0">
                        <div class="space-y-3 w-full">
                            <!-- Pure Typographic Overline Kicker (Zero Pill Abuse) -->
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Hubungi Tim Kami — Pusat Bantuan &amp; Konsultasi Resmi
                            </p>

                            <h1
                                class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-extrabold text-white tracking-tight leading-[1.15] text-balance break-words max-w-[22rem] sm:max-w-xl lg:max-w-none mx-auto lg:mx-0">
                                Tim Kami Siap Mendampingi <span class="text-[#00C4D8]">Operasional Bisnis Anda.</span>
                            </h1>
                        </div>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-[32rem] lg:max-w-xl mx-auto lg:mx-0">
                            Punya kendala teknis printer struk, sinkronisasi stok toko, atau ingin berkonsultasi mengenai
                            perhitungan pembukuan? Kami mengutamakan pendampingan ramah dan mudah dipahami tanpa bahasa
                            teknis yang rumit.
                        </p>

                        {{-- Trust indicators --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 text-left w-full">
                            <div class="p-3.5 rounded-[16px] bg-[#0E1E45]/60 border border-white/10 backdrop-blur-sm">
                                <div class="text-xs font-bold text-white flex items-center gap-2">
                                    <i data-lucide="clock" class="w-4 h-4 text-[#00C4D8] shrink-0"></i>
                                    <span class="min-w-0 flex-1 leading-snug">Respon Cepat</span>
                                </div>
                                <div class="text-[12px] text-slate-300 mt-1 leading-normal text-pretty">Rata-rata &lt; 15
                                    menit pada jam operasional
                                </div>
                            </div>

                            <div class="p-3.5 rounded-[16px] bg-[#0E1E45]/60 border border-white/10 backdrop-blur-sm">
                                <div class="text-xs font-bold text-white flex items-center gap-2">
                                    <i data-lucide="video" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                    <span class="min-w-0 flex-1 leading-snug">Panduan Langsung</span>
                                </div>
                                <div class="text-[12px] text-slate-300 mt-1 leading-normal text-pretty">Bimbingan foto atau
                                    video call praktis</div>
                            </div>

                            <div class="p-3.5 rounded-[16px] bg-[#0E1E45]/60 border border-white/10 backdrop-blur-sm">
                                <div class="text-xs font-bold text-white flex items-center gap-2">
                                    <i data-lucide="shield-check" class="w-4 h-4 text-[#FF9500] shrink-0"></i>
                                    <span class="min-w-0 flex-1 leading-snug">Data Terjamin Aman</span>
                                </div>
                                <div class="text-[12px] text-slate-300 mt-1 leading-normal text-pretty">Privasi dan
                                    pembukuan terenkripsi penuh</div>
                            </div>
                        </div>

                        {{-- Direct Primary Actions (Centered on Mobile, Row on Desktop) --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-center lg:justify-start gap-3 pt-2 w-full sm:w-auto">
                            <a href="https://wa.me/{{ $officialWhatsappRaw }}?text=Halo%20Tim%20Cooca%20UMKM,%20saya%20butuh%20bantuan%20seputar%20aplikasi"
                                target="_blank" rel="noopener"
                                class="h-12 px-6 rounded-[14px] bg-[#34C759] hover:bg-[#2DB84D] text-white font-semibold text-sm flex items-center justify-center gap-2.5 shadow-lg shadow-emerald-500/25 active:scale-[0.98] transition-all min-h-[48px]">
                                <i data-lucide="message-circle" class="w-5 h-5 shrink-0"></i>
                                <span>Chat Langsung WhatsApp</span>
                            </a>
                            <a href="#form-pesan"
                                class="h-12 px-6 rounded-[14px] bg-white/10 hover:bg-white/15 text-white border border-white/15 font-semibold text-sm flex items-center justify-center gap-2 transition-all backdrop-blur-sm min-h-[48px]">
                                <i data-lucide="mail" class="w-5 h-5 text-slate-300 shrink-0"></i>
                                <span>Kirim Formulir Pesan</span>
                            </a>
                        </div>
                    </div>

                    {{-- Right Column: Support Operation Bento Card (7 Cols ~ 58%) --}}
                    <div class="lg:col-span-7 space-y-4">
                        <div
                            class="rounded-2xl bg-[#0E1E45]/80 p-6 shadow-2xl border border-white/10 ring-1 ring-white/10 backdrop-blur-md text-white space-y-5">
                            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="relative flex h-3 w-3">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                    </span>
                                    <span class="text-xs font-bold uppercase tracking-wider text-white">Status Operasional
                                        Support</span>
                                </div>
                                <span
                                    class="text-xs text-emerald-400 font-medium bg-emerald-500/15 border border-emerald-500/30 px-2.5 py-1 rounded-full">
                                    Siaga Melayani
                                </span>
                            </div>

                            {{-- Channel 1: WhatsApp --}}
                            <div
                                class="p-4 rounded-[16px] bg-[#060B1E]/60 border border-white/10 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div
                                        class="w-10 h-10 rounded-[12px] bg-[#34C759] text-white flex items-center justify-center shrink-0 shadow-md">
                                        <i data-lucide="phone-call" class="w-5 h-5"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="text-[11px] font-semibold text-emerald-400 uppercase tracking-wider truncate">
                                            WhatsApp Hotline</div>
                                        <div class="text-base font-bold text-white font-mono truncate">
                                            {{ $officialWhatsapp }}</div>
                                    </div>
                                </div>
                                <a href="https://wa.me/{{ $officialWhatsappRaw }}?text=Halo%20Tim%20Cooca%20UMKM"
                                    target="_blank" rel="noopener"
                                    class="text-xs font-bold text-emerald-400 hover:underline flex items-center gap-1 shrink-0">
                                    Chat <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>

                            {{-- Channel 2: Email --}}
                            <div
                                class="p-4 rounded-[16px] bg-[#060B1E]/60 border border-white/10 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div
                                        class="w-10 h-10 rounded-[12px] bg-[#007AFF]/20 text-[#00C4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="mail" class="w-5 h-5"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider truncate">
                                            Email
                                            Surat Resmi</div>
                                        <a href="mailto:{{ $officialEmail }}"
                                            class="text-sm font-bold text-white hover:underline truncate block">{{ $officialEmail }}</a>
                                    </div>
                                </div>
                                <a href="mailto:{{ $officialEmail }}"
                                    class="text-xs font-bold text-[#00C4D8] hover:underline shrink-0">Kirim</a>
                            </div>

                            {{-- Channel 3: Office --}}
                            <div class="p-4 rounded-[16px] bg-[#060B1E]/60 border border-white/10 flex items-start gap-3">
                                <div
                                    class="w-10 h-10 rounded-[12px] bg-white/10 text-slate-200 flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="map-pin" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Kantor
                                        Operasional</div>
                                    <div class="text-sm font-semibold text-white mt-0.5 leading-snug break-words">
                                        {{ $officeLocation }}</div>
                                    <div class="text-xs text-slate-400 mt-1 leading-normal">Senin - Sabtu: 08.00 - 20.00
                                        WIB</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Main Content: Form Section --}}
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <section id="form-pesan" class="pt-2">
                <div
                    class="max-w-3xl mx-auto bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 sm:p-10 rounded-[24px] shadow-sm">
                    <div class="border-b border-slate-200/80 dark:border-white/10 pb-6 mb-8">
                        <div
                            class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] mb-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>Formulir Pesan Dukungan</span>
                        </div>
                        <h2
                            class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight text-balance break-words">
                            Tuliskan Pertanyaan atau Kendala Anda
                        </h2>
                        <p
                            class="text-sm text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed text-pretty break-words">
                            Isi data di bawah ini secara ringkas. Tim spesialis kami akan meninjau dan menghubungi Anda
                            kembali.
                        </p>
                    </div>

                    @if (session('success_message'))
                        <div
                            class="p-4 rounded-[16px] bg-emerald-500/10 border border-emerald-500/25 text-slate-900 dark:text-white text-sm mb-8 flex items-start gap-3.5">
                            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                            <div>
                                <div class="font-bold text-emerald-600 dark:text-emerald-400">Pesan Berhasil Terkirim</div>
                                <div class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                                    {{ session('success_message') }}</div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
                                    Nama Lengkap Pemilik / Pengelola <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="name" required placeholder="Contoh: Hendra Wijaya"
                                    value="{{ old('name') }}"
                                    class="w-full h-12 px-4 bg-slate-50 dark:bg-[#070A14] border border-slate-200 dark:border-white/10 rounded-[14px] text-slate-900 dark:text-white text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-slate-400">
                                @error('name')
                                    <span class="text-xs text-[#FF3B30] mt-1.5 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
                                    Alamat Email Aktif <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="email" name="email" required placeholder="email@contohtoko.com"
                                    value="{{ old('email') }}"
                                    class="w-full h-12 px-4 bg-slate-50 dark:bg-[#070A14] border border-slate-200 dark:border-white/10 rounded-[14px] text-slate-900 dark:text-white text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-slate-400">
                                @error('email')
                                    <span class="text-xs text-[#FF3B30] mt-1.5 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
                                    Nomor WhatsApp (Untuk Balasan Cepat)
                                </label>
                                <input type="tel" name="phone" placeholder="081234567890"
                                    value="{{ old('phone') }}"
                                    class="w-full h-12 px-4 bg-slate-50 dark:bg-[#070A14] border border-slate-200 dark:border-white/10 rounded-[14px] text-slate-900 dark:text-white text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-slate-400">
                                <span class="text-[12px] text-slate-500 dark:text-slate-400 mt-1 block">Sangat disarankan
                                    agar tim dapat merespon via WhatsApp</span>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
                                    Topik Permintaan <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="subject" required
                                    placeholder="Contoh: Konsultasi paket software kasir" value="{{ old('subject') }}"
                                    class="w-full h-12 px-4 bg-slate-50 dark:bg-[#070A14] border border-slate-200 dark:border-white/10 rounded-[14px] text-slate-900 dark:text-white text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-slate-400">
                                @error('subject')
                                    <span class="text-xs text-[#FF3B30] mt-1.5 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
                                Detail Pertanyaan / Kebutuhan Usaha Anda <span class="text-[#FF3B30]">*</span>
                            </label>
                            <textarea name="message" rows="5" required
                                placeholder="Jelaskan jenis usaha Anda dan apa yang ingin Anda tanyakan atau butuhkan bantuan..."
                                class="w-full p-4 bg-slate-50 dark:bg-[#070A14] border border-slate-200 dark:border-white/10 rounded-[14px] text-slate-900 dark:text-white text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-slate-400">{{ old('message') }}</textarea>
                            @error('message')
                                <span class="text-xs text-[#FF3B30] mt-1.5 block font-medium">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                class="w-full h-13 py-3.5 rounded-[16px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm sm:text-base flex items-center justify-center gap-2.5 shadow-lg shadow-[#007AFF]/25 active:scale-[0.99] transition-all">
                                <span>Kirimkan Pesan Sekarang</span>
                                <i data-lucide="send" class="w-4 h-4"></i>
                            </button>
                            <p class="text-center text-xs text-slate-500 dark:text-slate-400 mt-3">
                                Data kontak Anda aman dan hanya digunakan oleh tim COOCA untuk membalas pertanyaan ini.
                            </p>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection
