@extends('layouts.guest', ['title' => 'Lengkapi Profil Usaha — Cooca UMKM'])

@section('content')
    <div class="sm:mx-auto sm:w-full sm:max-w-xl px-4">
        <!-- Brand Badge -->
        <div class="text-center mb-8">
            <a href="{{ route('landing') }}"
                class="inline-flex items-center justify-center mb-4 hover:scale-105 transition-transform">
                <img src="https://cooca.id/assets/image/1785229034_logo_dark.png" alt="COOCA.ID"
                    style="height: 38px; width: auto; object-fit: contain;">
            </a>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold mb-2">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                <span>Langkah Terakhir</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Lengkapi Data Profil Bisnis</h2>
            <p class="mt-2 text-sm text-slate-400">Mohon lengkapi nama, nomor WhatsApp/HP, dan nama usaha Anda untuk verifikasi & notifikasi invoice.</p>
        </div>

        <!-- Card -->
        <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl shadow-black/60 relative overflow-hidden">
            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs">
                    <div class="font-semibold mb-1">Ada data yang belum valid:</div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.complete.save') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Nama Lengkap Pemilik / Admin <span class="text-emerald-400">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl text-sm text-white placeholder-slate-500 transition-colors"
                            placeholder="Contoh: Budi Pratama">
                    </div>
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Nomor WhatsApp / HP <span class="text-emerald-400">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone ?? $business->phone ?? '') }}" required autofocus
                            class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl text-sm text-white placeholder-slate-500 transition-colors"
                            placeholder="Contoh: 081234567890">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Nomor ini digunakan untuk konfirmasi aktivasi paket & bantuan layanan darurat.</p>
                </div>

                <div>
                    <label for="business_name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Nama Usaha / Toko / Brand <span class="text-emerald-400">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" name="business_name" id="business_name"
                            value="{{ old('business_name', (str_starts_with($business->name ?? '', 'Usaha ') ? '' : ($business->name ?? ''))) }}" required
                            class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl text-sm text-white placeholder-slate-500 transition-colors"
                            placeholder="Contoh: Kopi Kenangan Manis / Dapur Ibu">
                    </div>
                </div>

                <div class="pt-3">
                    <button type="submit"
                        class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-semibold text-sm shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-2 transition-all transform active:scale-[0.99]">
                        <span>Simpan & Buka Workspace</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
