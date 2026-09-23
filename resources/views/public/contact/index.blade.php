@extends('layouts.public_marketing')

@section('title', 'Hubungi Tim Dukungan Cooca | WhatsApp Resmi ' . $officialWhatsapp)
@section('description', 'Hubungi tim konsultan dan customer service Cooca. Dapatkan bantuan teknis seputar aplikasi
    kasir, kalkulator HPP, atau pertanyaan kemitraan.')
@section('keywords', 'kontak Cooca, whatsapp cooca, customer service software kasir, support cooca id, bantuan teknis
    aplikasi kasir')

@section('content')
    <div class="pt-6 sm:pt-10 pb-24 bg-[#F5F5F7] dark:bg-[#000000] min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

            <!-- 2-Grid Hero Section -->
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start pt-4 sm:pt-8">
                <!-- Left Column: Copy & Core Actions -->
                <div class="lg:col-span-7 space-y-6">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7]">
                        <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                        <span>Hubungi Tim Kami — Pusat Bantuan & Konsultasi Resmi</span>
                    </div>

                    <h1
                        class="text-4xl sm:text-5xl lg:text-[3.25rem] xl:text-[3.75rem] font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight leading-[1.15]">
                        Tim Kami Siap Mendampingi Operasional Bisnis Anda.
                    </h1>

                    <p class="text-base sm:text-lg text-[#6E6E73] dark:text-[#86868B] leading-relaxed max-w-xl">
                        Punya kendala teknis printer struk, sinkronisasi stok toko, atau ingin berkonsultasi mengenai
                        perhitungan pembukuan? Kami mengutamakan pendampingan ramah dan mudah dipahami tanpa bahasa teknis
                        yang rumit.
                    </p>

                    <!-- Trust indicators for UMKM 40-65 -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                        <div
                            class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <div class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-1.5">
                                <i data-lucide="clock" class="w-4 h-4 text-[#007AFF]"></i>
                                <span>Respon Cepat</span>
                            </div>
                            <div class="text-[12px] text-[#6E6E73] dark:text-[#86868B] mt-1">Rata-rata &lt; 15 menit pada
                                jam operasional</div>
                        </div>

                        <div
                            class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <div class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-1.5">
                                <i data-lucide="video" class="w-4 h-4 text-[#34C759]"></i>
                                <span>Panduan Langsung</span>
                            </div>
                            <div class="text-[12px] text-[#6E6E73] dark:text-[#86868B] mt-1">Bimbingan foto atau video call
                                praktis</div>
                        </div>

                        <div
                            class="p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                            <div class="text-xs font-bold text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center gap-1.5">
                                <i data-lucide="shield-check" class="w-4 h-4 text-[#FF9500]"></i>
                                <span>Data Dijamin Aman</span>
                            </div>
                            <div class="text-[12px] text-[#6E6E73] dark:text-[#86868B] mt-1">Privasi dan pembukuan
                                terenkripsi penuh</div>
                        </div>
                    </div>

                    <!-- Direct Primary Actions -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                        <a href="https://wa.me/{{ $officialWhatsappRaw }}?text=Halo%20Tim%20Cooca%20UMKM,%20saya%20butuh%20bantuan%20seputar%20aplikasi"
                            target="_blank" rel="noopener"
                            class="h-12 px-6 rounded-[14px] bg-[#34C759] hover:bg-[#2DB84D] text-white font-semibold text-sm flex items-center justify-center gap-2.5 shadow-sm active:scale-[0.98] transition-all">
                            <i data-lucide="message-circle" class="w-5 h-5"></i>
                            <span>Chat Langsung WhatsApp</span>
                        </a>
                        <a href="#form-pesan"
                            class="h-12 px-6 rounded-[14px] bg-white dark:bg-[#1C1C1E] hover:bg-black/[0.04] dark:hover:bg-white/[0.08] text-[#1D1D1F] dark:text-[#F5F5F7] border border-black/[0.1] dark:border-white/[0.15] font-semibold text-sm flex items-center justify-center gap-2 transition-all">
                            <i data-lucide="mail" class="w-5 h-5 text-[#6E6E73] dark:text-[#86868B]"></i>
                            <span>Kirim Formulir Pesan</span>
                        </a>
                    </div>
                </div>

                <!-- Right Column: Support Operation Bento Card -->
                <div class="lg:col-span-5 space-y-4">
                    <!-- Live Support Status Window -->
                    <div
                        class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-6 shadow-sm space-y-5">
                        <div
                            class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                            <div class="flex items-center gap-2.5">
                                <span class="relative flex h-3 w-3">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#34C759] opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-[#34C759]"></span>
                                </span>
                                <span
                                    class="text-xs font-bold uppercase tracking-wider text-[#1D1D1F] dark:text-[#F5F5F7]">Status
                                    Operasional Support</span>
                            </div>
                            <span class="text-xs text-[#34C759] font-medium bg-[#34C759]/10 px-2.5 py-1 rounded-full">Siaga
                                Melayani</span>
                        </div>

                        <!-- Channel 1: WhatsApp -->
                        <div
                            class="p-4 rounded-[16px] bg-[#34C759]/[0.06] dark:bg-[#34C759]/[0.1] border border-[#34C759]/20 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-[12px] bg-[#34C759] text-white flex items-center justify-center shrink-0">
                                    <i data-lucide="phone-call" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <div class="text-[11px] font-semibold text-[#34C759] uppercase tracking-wider">WhatsApp
                                        Hotline</div>
                                    <div class="text-base font-bold text-[#1D1D1F] dark:text-[#F5F5F7] font-mono">
                                        {{ $officialWhatsapp }}</div>
                                </div>
                            </div>
                            <a href="https://wa.me/{{ $officialWhatsappRaw }}?text=Halo%20Tim%20Cooca%20UMKM"
                                target="_blank" rel="noopener"
                                class="text-xs font-bold text-[#34C759] hover:underline flex items-center gap-1">
                                Chat <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>

                        <!-- Channel 2: Email -->
                        <div
                            class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                                    <i data-lucide="mail" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <div
                                        class="text-[11px] font-semibold text-[#6E6E73] dark:text-[#86868B] uppercase tracking-wider">
                                        Email Surat Resmi</div>
                                    <a href="mailto:{{ $officialEmail }}"
                                        class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] hover:underline truncate block">{{ $officialEmail }}</a>
                                </div>
                            </div>
                            <a href="mailto:{{ $officialEmail }}"
                                class="text-xs font-bold text-[#007AFF] hover:underline">Kirim</a>
                        </div>

                        <!-- Channel 3: Office -->
                        <div
                            class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.08] flex items-start gap-3">
                            <div
                                class="w-10 h-10 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.08] text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="map-pin" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div
                                    class="text-[11px] font-semibold text-[#6E6E73] dark:text-[#86868B] uppercase tracking-wider">
                                    Kantor Operasional</div>
                                <div class="text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] mt-0.5">
                                    {{ $officeLocation }}</div>
                                <div class="text-xs text-[#6E6E73] dark:text-[#86868B] mt-1">Senin - Sabtu: 08.00 - 20.00
                                    WIB</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Form Section with Apple HIG Structure -->
            <section id="form-pesan" class="pt-6">
                <div
                    class="max-w-3xl mx-auto bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-6 sm:p-10 rounded-[24px] shadow-sm">
                    <div class="border-b border-black/[0.06] dark:border-white/[0.08] pb-6 mb-8">
                        <div
                            class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#007AFF] mb-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>Formulir Pesan Dukungan</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">
                            Tuliskan Pertanyaan atau Kendala Anda
                        </h2>
                        <p class="text-sm text-[#6E6E73] dark:text-[#86868B] mt-1.5">
                            Isi data di bawah ini secara ringkas. Tim spesialis kami akan meninjau dan menghubungi Anda
                            kembali.
                        </p>
                    </div>

                    @if (session('success_message'))
                        <div
                            class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/25 text-[#1D1D1F] dark:text-[#F5F5F7] text-sm mb-8 flex items-start gap-3.5">
                            <i data-lucide="check-circle" class="w-5 h-5 text-[#34C759] shrink-0 mt-0.5"></i>
                            <div>
                                <div class="font-bold text-[#34C759]">Pesan Berhasil Terkirim</div>
                                <div class="text-xs text-[#6E6E73] dark:text-[#86868B] mt-0.5">
                                    {{ session('success_message') }}</div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] mb-2">
                                    Nama Lengkap Pemilik / Pengelola <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="name" required placeholder="Contoh: Hendra Wijaya"
                                    value="{{ old('name') }}"
                                    class="w-full h-12 px-4 bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.1] dark:border-white/[0.12] rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-[#6E6E73]/50">
                                @error('name')
                                    <span class="text-xs text-[#FF3B30] mt-1.5 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] mb-2">
                                    Alamat Email Aktif <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="email" name="email" required placeholder="email@contohtoko.com"
                                    value="{{ old('email') }}"
                                    class="w-full h-12 px-4 bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.1] dark:border-white/[0.12] rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-[#6E6E73]/50">
                                @error('email')
                                    <span class="text-xs text-[#FF3B30] mt-1.5 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] mb-2">
                                    Nomor WhatsApp (Untuk Balasan Cepat)
                                </label>
                                <input type="tel" name="phone" placeholder="081234567890"
                                    value="{{ old('phone') }}"
                                    class="w-full h-12 px-4 bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.1] dark:border-white/[0.12] rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-[#6E6E73]/50">
                                <span class="text-[12px] text-[#6E6E73] dark:text-[#86868B] mt-1 block">Sangat disarankan
                                    agar tim dapat merespon via WhatsApp</span>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] mb-2">
                                    Topik Permintaan <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="subject" required
                                    placeholder="Contoh: Konsultasi paket software kasir" value="{{ old('subject') }}"
                                    class="w-full h-12 px-4 bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.1] dark:border-white/[0.12] rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-[#6E6E73]/50">
                                @error('subject')
                                    <span class="text-xs text-[#FF3B30] mt-1.5 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] mb-2">
                                Detail Pertanyaan / Kebutuhan Usaha Anda <span class="text-[#FF3B30]">*</span>
                            </label>
                            <textarea name="message" rows="5" required
                                placeholder="Jelaskan jenis usaha Anda dan apa yang ingin Anda tanyakan atau butuhkan bantuan..."
                                class="w-full p-4 bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.1] dark:border-white/[0.12] rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-[#6E6E73]/50">{{ old('message') }}</textarea>
                            @error('message')
                                <span class="text-xs text-[#FF3B30] mt-1.5 block font-medium">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                class="w-full h-13 py-3.5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm sm:text-base flex items-center justify-center gap-2.5 shadow-sm active:scale-[0.99] transition-all">
                                <span>Kirimkan Pesan Sekarang</span>
                                <i data-lucide="send" class="w-4 h-4"></i>
                            </button>
                            <p class="text-center text-xs text-[#6E6E73] dark:text-[#86868B] mt-3">
                                Data kontak Anda aman dan hanya digunakan oleh tim COOCA untuk membalas pertanyaan ini.
                            </p>
                        </div>
                    </form>
                </div>
            </section>

        </div>
    </div>
@endsection
