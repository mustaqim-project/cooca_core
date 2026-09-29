@extends('layouts.public_marketing', ['title' => 'Lengkapi Profil Pelanggan - COOCA', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-14rem)] flex flex-col justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md mx-auto">

            <!-- Official Header -->
            <div class="text-center mb-6 sm:mb-8">
                <div class="w-16 h-16 rounded-[22px] bg-[#FF9500]/10 border border-[#FF9500]/20 text-[#FF9500] dark:text-[#FF9F0A] mx-auto flex items-center justify-center mb-4 shadow-sm">
                    <i data-lucide="user-check" class="w-8 h-8"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Lengkapi Profil
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
                    Tambahkan nomor WhatsApp untuk pengiriman OTP aktivasi, konfirmasi pesanan, dan informasi kurir.
                </p>
            </div>

            <!-- Structured Auth Card -->
            <div class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-black/40 transition-colors">

                <!-- Success / Info Alert -->
                @if (session('status') || session('success'))
                    <div class="mb-5 p-3.5 rounded-xl bg-[#34C759]/10 border border-[#34C759]/25 text-[#34C759] dark:text-[#30D158] text-xs sm:text-sm flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                        <span>{{ session('status') ?? session('success') }}</span>
                    </div>
                @endif

                @if (session('info'))
                    <div class="mb-5 p-3.5 rounded-xl bg-[#007AFF]/10 border border-[#007AFF]/25 text-[#007AFF] dark:text-[#0A84FF] text-xs sm:text-sm flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                <!-- Validation Errors Alert -->
                @if ($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs">
                        <div class="font-semibold mb-1 flex items-center gap-1.5 text-sm">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                            <span>Mohon lengkapi data berikut:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs opacity-90 pl-1">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Profile Info Chip (From Google OAuth) -->
                <div class="flex items-center gap-3.5 p-3.5 mb-5 rounded-2xl bg-slate-50 dark:bg-white/[0.04] border border-slate-200/80 dark:border-white/10">
                    @if ($customer->avatar_url)
                        <img src="{{ $customer->avatar_url }}" class="w-12 h-12 rounded-xl object-cover ring-2 ring-[#007AFF]/20" alt="{{ $customer->name }}">
                    @else
                        <div class="w-12 h-12 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold text-base">
                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-sm text-slate-900 dark:text-white truncate">{{ $customer->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $customer->email }}</p>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] text-[11px] font-bold rounded-full border border-[#34C759]/20 shrink-0">
                        <i data-lucide="check" class="w-3 h-3"></i>
                        <span>Google</span>
                    </span>
                </div>

                <!-- Form Profile Complete -->
                <form method="POST" action="{{ route('customer.profile.complete.save') }}" class="space-y-4 sm:space-y-5">
                    @csrf

                    <!-- WhatsApp Phone Input -->
                    <div>
                        <label for="customer_phone" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                            Nomor WhatsApp <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative flex">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 z-10">
                                <i data-lucide="phone" class="w-4 h-4"></i>
                            </div>
                            <input type="tel" name="phone" id="customer_phone"
                                value="{{ old('phone', $customer->phone ? (str_starts_with($customer->phone, '62') ? '0' . substr($customer->phone, 2) : $customer->phone) : '') }}"
                                required autofocus inputmode="tel" autocomplete="tel"
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-base text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                placeholder="081234567890">
                        </div>
                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                            Kode OTP verifikasi akan dikirimkan ke nomor WhatsApp ini.
                        </p>
                    </div>

                    <!-- Shipping Address Input (Optional) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="shipping_address" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200">
                                Alamat Pengiriman Utama
                            </label>
                            <span class="text-[11px] text-slate-400 dark:text-slate-500">Opsional</span>
                        </div>
                        <div class="relative">
                            <div class="absolute top-3 left-3.5 pointer-events-none text-slate-400 dark:text-slate-500">
                                <i data-lucide="map-pin" class="w-4 h-4"></i>
                            </div>
                            <textarea name="shipping_address" id="shipping_address" rows="3"
                                placeholder="Nama jalan, nomor rumah/gedung, RT/RW, kelurahan, kecamatan, kota..."
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none resize-none">{{ old('shipping_address', $customer->shipping_address) }}</textarea>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                            Alamat ini akan otomatis digunakan sebagai default saat Anda memesan produk di toko.
                        </p>
                    </div>

                    <!-- Primary CTA Button -->
                    <div class="pt-2">
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] hover:from-[#1cd0ff] hover:to-[#006fe6] text-white font-bold text-sm sm:text-[15px] shadow-[0_2px_12px_rgba(0,194,255,0.3)] hover:shadow-[0_4px_20px_rgba(0,194,255,0.5)] active:scale-[0.99] min-h-[50px] transition-all focus:outline-none focus:ring-4 focus:ring-[#007AFF]/25 cursor-pointer">
                            <span>Simpan &amp; Lanjutkan Verifikasi WhatsApp</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>

                <!-- Logout Option -->
                <form method="POST" action="{{ route('customer.logout') }}" class="mt-5 pt-4 border-t border-slate-200/80 dark:border-white/10 text-center">
                    @csrf
                    <button type="submit"
                        class="min-h-[40px] px-3 text-xs sm:text-sm text-slate-500 dark:text-slate-400 hover:text-[#FF3B30] dark:hover:text-[#FF453A] transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/20 rounded">
                        Gunakan akun lain / Keluar
                    </button>
                </form>
            </div>

            <!-- Trust Indicators -->
            <div class="mt-6 flex flex-wrap items-center justify-center gap-4 sm:gap-6 text-xs text-slate-500 dark:text-slate-400">
                <span class="inline-flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]"></i>
                    <span>Aman &amp; Terenkripsi</span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <i data-lucide="store" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Satu Akun Semua Toko</span>
                </span>
            </div>

        </div>
    </div>
@endsection