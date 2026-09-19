@extends('layouts.customer', ['title' => 'Daftar Akun Pelanggan Baru'])

@section('content')
<div class="max-w-lg mx-auto py-6 sm:py-10">
    <div class="bento-card p-6 sm:p-8 space-y-6">

        {{-- Header --}}
        <div class="text-center space-y-1.5">
            @if(isset($store) && $store)
                <div class="w-12 h-12 rounded-[16px] bg-black/5 dark:bg-white/10 mx-auto flex items-center justify-center mb-2 overflow-hidden">
                    @if($store->logo_url)
                        <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="w-full h-full object-contain p-1">
                    @else
                        <i data-lucide="store" class="w-6 h-6 text-[#007AFF]"></i>
                    @endif
                </div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#007AFF]">Registrasi Pembeli</span>
                <h1 class="text-2xl font-bold text-black dark:text-white tracking-tight">Daftar di {{ $store->name }}</h1>
                <p class="text-[13px] text-black/60 dark:text-white/60">Nikmati kemudahan pelacakan pesanan dan poin loyalitas.</p>
            @else
                <div class="w-12 h-12 rounded-[16px] bg-[#007AFF]/10 text-[#007AFF] mx-auto flex items-center justify-center mb-2">
                    <i data-lucide="user-plus" class="w-6 h-6"></i>
                </div>
                <h1 class="text-2xl font-bold text-black dark:text-white tracking-tight">Daftar Akun Pelanggan</h1>
                <p class="text-[13px] text-black/60 dark:text-white/60">Satu akun untuk melacak seluruh transaksi belanja dan promo UMKM.</p>
            @endif
        </div>

        @if($errors->any())
            <div class="p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-[12.5px] font-medium flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Form Registrasi --}}
        <form action="{{ route('customer.register.submit') }}" method="POST" class="space-y-4">
            @csrf
            @if(isset($store) && $store)
                <input type="hidden" name="store_id" value="{{ $store->id }}">
            @endif
            @if(!empty($redirectTo))
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
            @endif

            {{-- Nama Lengkap --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <i data-lucide="user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-black/40 dark:text-white/40"></i>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                           placeholder="Contoh: Budi Santoso"
                           class="w-full h-11 pl-10 pr-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                </div>
            </div>

            {{-- WhatsApp & Email --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                        Nomor WhatsApp <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <i data-lucide="phone" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-black/40 dark:text-white/40"></i>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required
                               placeholder="08123456789"
                               class="w-full h-11 pl-10 pr-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                        Email (Opsional)
                    </label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-black/40 dark:text-white/40"></i>
                        <input type="email" name="email" value="{{ old('email') }}"
                               placeholder="budi@example.com"
                               class="w-full h-11 pl-10 pr-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>
                </div>
            </div>

            {{-- Kata Sandi & Konfirmasi --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                        Kata Sandi <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-black/40 dark:text-white/40"></i>
                        <input type="password" name="password" required minlength="6"
                               placeholder="Min. 6 karakter"
                               class="w-full h-11 pl-10 pr-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                        Ulangi Kata Sandi <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <i data-lucide="shield-check" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-black/40 dark:text-white/40"></i>
                        <input type="password" name="password_confirmation" required minlength="6"
                               placeholder="Ulangi kata sandi"
                               class="w-full h-11 pl-10 pr-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition">
                    </div>
                </div>
            </div>

            {{-- Alamat Pengiriman Default --}}
            <div>
                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1">
                    Alamat Pengiriman Utama (Opsional)
                </label>
                <div class="relative">
                    <textarea name="shipping_address" rows="2"
                              placeholder="Nama jalan, nomor rumah/gedung, kelurahan, kota..."
                              class="w-full p-3 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition resize-none">{{ old('shipping_address') }}</textarea>
                </div>
                <span class="text-[11px] text-black/45 dark:text-white/45 block mt-0.5">Alamat ini akan otomatis digunakan saat Anda checkout.</span>
            </div>

            <button type="submit"
                    class="w-full h-11 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-[14px] transition flex items-center justify-center gap-2 shadow-sm active:scale-[0.98]">
                <span>Daftar &amp; Masuk Otomatis</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>

        {{-- Footer --}}
        <div class="pt-4 border-t border-black/5 dark:border-white/5 text-center text-[12.5px] text-black/60 dark:text-white/60 space-y-2">
            <p>
                Sudah memiliki akun?
                <a href="{{ route('customer.login', ['store' => isset($store) && $store ? $store->slug : null, 'redirect' => $redirectTo]) }}"
                   class="font-semibold text-[#007AFF] hover:underline">
                    Masuk Sekarang
                </a>
            </p>
            @if(isset($store) && $store)
                <p>
                    <a href="{{ url('/' . $store->slug) }}" class="text-black/50 dark:text-white/50 hover:underline">
                        &larr; Kembali ke Etalase Toko {{ $store->name }}
                    </a>
                </p>
            @endif
        </div>

    </div>
</div>
@endsection
