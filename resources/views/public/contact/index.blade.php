@extends('layouts.public_marketing')

@section('title', 'Hubungi Tim Dukungan Cooca UMKM | WhatsApp Resmi 0823 3749 9577')
@section('description', 'Hubungi tim konsultan dan customer service Cooca UMKM. Dapatkan bantuan teknis seputar aplikasi kasir, kalkulator HPP, atau pertanyaan kemitraan.')
@section('keywords', 'kontak cooca umkm, whatsapp cooca, customer service software kasir, support cooca id, bantuan teknis aplikasi kasir')

@section('content')
<div class="pt-12 pb-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto mb-14">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 font-bold text-xs mb-3">
                <i data-lucide="message-square" class="w-4 h-4"></i>
                <span>Customer Support 24/7</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Hubungi Kami</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-2">
                Punya pertanyaan seputar cara penggunaan aplikasi kasir atau ingin berkonsultasi seputar bisnis? Tim COOCA siap membantu Anda.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left: Contact Channels -->
            <div class="lg:col-span-5 space-y-6">

                <!-- WhatsApp Card (Primary) -->
                <div class="glass-card p-6 rounded-3xl border-emerald-500/30 bg-emerald-950/20 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center">
                            <i data-lucide="phone-call" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase tracking-wider font-bold text-emerald-400 block">WhatsApp Resmi</span>
                            <h3 class="text-lg font-black text-white font-mono">{{ $officialWhatsapp }}</h3>
                        </div>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Layanan konsultasi cepat via pesan WhatsApp. Respon cepat pada jam kerja (08.00 - 20.00 WIB).
                    </p>
                    <a href="https://wa.me/{{ $officialWhatsappRaw }}?text=Halo%20Tim%20Cooca%20UMKM,%20saya%20butuh%20bantuan%20seputar%20aplikasi" target="_blank" rel="noopener" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/30 transition-all">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>Chat WhatsApp Sekarang</span>
                    </a>
                </div>

                <!-- Email Card -->
                <div class="glass-card p-6 rounded-3xl space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400 block">Email Support</span>
                            <a href="mailto:{{ $officialEmail }}" class="text-sm font-bold text-white hover:text-indigo-400 transition-colors">{{ $officialEmail }}</a>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Untuk keperluan administrasi, kemitraan, atau laporan kendala sistem.
                    </p>
                </div>

                <!-- Office Location -->
                <div class="glass-card p-6 rounded-3xl space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center">
                            <i data-lucide="map-pin" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400 block">Kantor Operasional</span>
                            <span class="text-sm font-bold text-white block">{{ $officeLocation }}</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right: Contact Form -->
            <div class="lg:col-span-7 glass-card p-6 sm:p-8 rounded-3xl">
                <h3 class="text-xl font-bold text-white mb-2">Kirim Pesan Langsung</h3>
                <p class="text-xs text-slate-400 mb-6">Isi formulir di bawah ini dan kami akan membalas via WhatsApp atau Email.</p>

                @if(session('success_message'))
                <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs mb-6 flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                    <span>{{ session('success_message') }}</span>
                </div>
                @endif

                <form action="{{ route('contact.submit') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Nama Lengkap *</label>
                            <input type="text" name="name" required placeholder="Budi Santoso" value="{{ old('name') }}" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs focus:border-indigo-500 focus:outline-none">
                            @error('name') <span class="text-[11px] text-rose-400">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Email *</label>
                            <input type="email" name="email" required placeholder="budi@gmail.com" value="{{ old('email') }}" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs focus:border-indigo-500 focus:outline-none">
                            @error('email') <span class="text-[11px] text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Nomor WhatsApp</label>
                            <input type="tel" name="phone" placeholder="081234567890" value="{{ old('phone') }}" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Subjek Pertanyaan *</label>
                            <input type="text" name="subject" required placeholder="Contoh: Bantuan setting printer struk" value="{{ old('subject') }}" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs focus:border-indigo-500 focus:outline-none">
                            @error('subject') <span class="text-[11px] text-rose-400">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Pesan / Kendala Anda *</label>
                        <textarea name="message" rows="5" required placeholder="Tuliskan pertanyaan atau kendala yang Anda alami secara detail..." class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs focus:border-indigo-500 focus:outline-none">{{ old('message') }}</textarea>
                        @error('message') <span class="text-[11px] text-rose-400">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="w-full glow-btn py-3.5 rounded-xl text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg">
                        <span>Kirim Pesan Sekarang</span>
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>

        </div>

    </div>
</div>
@endsection
