@extends('layouts.public_marketing')

@section('title', 'Hubungi Tim Dukungan Cooca UMKM | WhatsApp Resmi 0823 3749 9577')
@section('description', 'Hubungi tim konsultan dan customer service Cooca UMKM. Dapatkan bantuan teknis seputar aplikasi kasir, kalkulator HPP, atau pertanyaan kemitraan.')
@section('keywords', 'kontak cooca umkm, whatsapp cooca, customer service software kasir, support cooca id, bantuan teknis aplikasi kasir')

@section('content')
<div class="pt-8 pb-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto space-y-3">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs">
                <i data-lucide="message-square" class="w-4 h-4"></i>
                <span>Customer Support 24/7</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-[#1D1D1F] dark:text-[#F5F5F7] tracking-tight">Hubungi Tim Kami</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] dark:text-[#86868B] max-w-lg mx-auto leading-relaxed">
                Punya pertanyaan seputar cara penggunaan aplikasi kasir atau ingin berkonsultasi seputar pembukuan bisnis? Tim COOCA siap membantu Anda.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left: Contact Channels (5 Cols) -->
            <div class="lg:col-span-5 space-y-5">

                <!-- WhatsApp Card (Primary Bento Widget) -->
                <div class="p-6 rounded-[24px] border border-[#34C759]/25 bg-[#34C759]/[0.05] dark:bg-[#30D158]/[0.08] space-y-4 shadow-sm">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-[16px] bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                            <i data-lucide="phone-call" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase tracking-wider font-bold text-[#34C759] dark:text-[#30D158] block">WhatsApp Resmi</span>
                            <h3 class="text-lg font-bold text-[#1D1D1F] dark:text-[#F5F5F7] font-mono">{{ $officialWhatsapp }}</h3>
                        </div>
                    </div>
                    <p class="text-xs text-[#1D1D1F]/75 dark:text-[#F5F5F7]/75 leading-relaxed">
                        Layanan konsultasi cepat via pesan WhatsApp. Respon cepat oleh tim support teknis pada jam kerja (08.00 - 20.00 WIB).
                    </p>
                    <a href="https://wa.me/{{ $officialWhatsappRaw }}?text=Halo%20Tim%20Cooca%20UMKM,%20saya%20butuh%20bantuan%20seputar%20aplikasi" target="_blank" rel="noopener" class="w-full py-3.5 rounded-[14px] bg-[#34C759] hover:bg-[#2DB84D] text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>Chat WhatsApp Sekarang</span>
                    </a>
                </div>

                <!-- Email Card -->
                <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-6 rounded-[24px] space-y-2.5 shadow-sm">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase tracking-wider font-bold text-[#6E6E73] dark:text-[#86868B] block">Email Support</span>
                            <a href="mailto:{{ $officialEmail }}" class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors">{{ $officialEmail }}</a>
                        </div>
                    </div>
                    <p class="text-xs text-[#6E6E73] dark:text-[#86868B] leading-relaxed">
                        Untuk keperluan administrasi resmi, kemitraan, atau pelaporan kendala sistem data.
                    </p>
                </div>

                <!-- Office Location -->
                <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-6 rounded-[24px] space-y-2.5 shadow-sm">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-[#1D1D1F] dark:text-[#F5F5F7] flex items-center justify-center shrink-0">
                            <i data-lucide="map-pin" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase tracking-wider font-bold text-[#6E6E73] dark:text-[#86868B] block">Kantor Operasional</span>
                            <span class="text-sm font-bold text-[#1D1D1F] dark:text-[#F5F5F7] block">{{ $officeLocation }}</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right: Contact Form (7 Cols) -->
            <div class="lg:col-span-7 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-6 sm:p-8 rounded-[28px] shadow-sm">
                <div class="space-y-1 mb-6">
                    <h3 class="text-xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kirim Pesan Langsung</h3>
                    <p class="text-xs text-[#6E6E73] dark:text-[#86868B]">Isi formulir di bawah ini dan kami akan segera membalas via WhatsApp atau Email.</p>
                </div>

                @if(session('success_message'))
                <div class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#34C759] dark:text-[#30D158] text-xs mb-6 flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                    <span class="font-medium">{{ session('success_message') }}</span>
                </div>
                @endif

                <form action="{{ route('contact.submit') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] mb-1.5">Nama Lengkap *</label>
                            <input type="text" name="name" required placeholder="Contoh: Budi Santoso" value="{{ old('name') }}" class="w-full h-11 px-3.5 bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.1] rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-xs sm:text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-[#6E6E73]/50">
                            @error('name') <span class="text-[11px] text-[#FF3B30] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] mb-1.5">Email *</label>
                            <input type="email" name="email" required placeholder="email@domain.com" value="{{ old('email') }}" class="w-full h-11 px-3.5 bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.1] rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-xs sm:text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-[#6E6E73]/50">
                            @error('email') <span class="text-[11px] text-[#FF3B30] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] mb-1.5">Nomor WhatsApp</label>
                            <input type="tel" name="phone" placeholder="081234567890" value="{{ old('phone') }}" class="w-full h-11 px-3.5 bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.1] rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-xs sm:text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-[#6E6E73]/50">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] mb-1.5">Subjek Pertanyaan *</label>
                            <input type="text" name="subject" required placeholder="Contoh: Bantuan setting printer struk" value="{{ old('subject') }}" class="w-full h-11 px-3.5 bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.1] rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-xs sm:text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-[#6E6E73]/50">
                            @error('subject') <span class="text-[11px] text-[#FF3B30] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#1D1D1F] dark:text-[#F5F5F7] mb-1.5">Pesan / Kendala Anda *</label>
                        <textarea name="message" rows="5" required placeholder="Tuliskan pertanyaan atau kendala yang Anda alami secara detail..." class="w-full p-3.5 bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.1] rounded-[14px] text-[#1D1D1F] dark:text-[#F5F5F7] text-xs sm:text-sm focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-[#6E6E73]/50">{{ old('message') }}</textarea>
                        @error('message') <span class="text-[11px] text-[#FF3B30] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="w-full py-3.5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                        <span>Kirim Pesan Sekarang</span>
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>

        </div>

    </div>
</div>
@endsection
