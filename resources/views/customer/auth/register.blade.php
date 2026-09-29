@extends('layouts.public_marketing', ['title' => 'Daftar Akun Pelanggan - COOCA', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-12rem)] py-8 sm:py-12 lg:py-16 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-xl mx-auto space-y-6 sm:space-y-8"
            x-data="{
                showPassword: false,
                showPasswordConfirm: false
            }">

            <!-- Customer & Store Header -->
            <div class="text-center space-y-3">
                @if (isset($store) && $store)
                    <div class="w-16 h-16 rounded-[22px] bg-gradient-to-br from-[#007AFF] to-[#5AC8FA] mx-auto flex items-center justify-center shadow-lg shadow-[#007AFF]/25 mb-3 overflow-hidden p-1">
                        @if ($store->logo_url)
                            <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="w-full h-full object-cover rounded-[18px]">
                        @else
                            <i data-lucide="store" class="w-8 h-8 text-white"></i>
                        @endif
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] text-xs font-bold uppercase tracking-wider mb-1">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                        <span>Registrasi Pembeli di {{ $store->name }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Daftar Akun Pelanggan
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
                        Nikmati kemudahan transaksi, pelacakan pesanan real-time, dan promo di <strong>{{ $store->name }}</strong>.
                    </p>
                @else
                    <div class="w-16 h-16 rounded-[22px] bg-gradient-to-br from-[#007AFF] to-[#5AC8FA] mx-auto flex items-center justify-center shadow-lg shadow-[#007AFF]/25 mb-3">
                        <i data-lucide="user-plus" class="w-8 h-8 text-white"></i>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Daftar Akun Pelanggan Baru
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
                        Satu akun terpadu untuk belanja, melacak riwayat transaksi, dan promo di seluruh merchant COOCA.
                    </p>
                @endif
            </div>

            <!-- Error Notification Banner -->
            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs sm:text-sm shadow-xs">
                    <div class="font-semibold mb-1.5 flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                        <span>Mohon periksa kembali formulir berikut:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs opacity-90 pl-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Main Single-Column Focused Register Card -->
            <div class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-black/40 transition-colors">
                <form method="POST" action="{{ route('customer.register.submit') }}" id="register-form" class="space-y-4 sm:space-y-5">
                    @csrf
                    @if (isset($store) && $store)
                        <input type="hidden" name="store_id" value="{{ $store->id }}">
                    @endif
                    @if (!empty($redirectTo))
                        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                    @endif

                    <!-- Google SSO Shortcut -->
                    <a href="{{ route('customer.auth.google') }}{{ $redirectTo ? '?redirect=' . urlencode($redirectTo) : '' }}"
                        class="w-full min-h-[46px] py-2.5 px-4 rounded-xl bg-white dark:bg-white/[0.06] border border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-white/[0.1] text-slate-700 dark:text-slate-200 font-semibold text-xs sm:text-sm flex items-center justify-center gap-3 transition-all shadow-sm active:scale-[0.99] group focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 cursor-pointer">
                        <svg class="w-5 h-5 shrink-0" width="20" height="20" viewBox="0 0 24 24">
                            <path fill="#EA4335"
                                d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z" />
                            <path fill="#4285F4"
                                d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z" />
                            <path fill="#FBBC05"
                                d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15s.7 5.3 1.9 7.7l3.7-2.9z" />
                            <path fill="#34A853"
                                d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.4-6.4-5.2L1.9 16c1.8 3.7 5.6 7 10.1 7z" />
                        </svg>
                        <span>Daftar Cepat dengan Google</span>
                    </a>

                    <!-- Divider -->
                    <div class="relative flex py-1 items-center">
                        <div class="flex-grow border-t border-slate-200 dark:border-white/10"></div>
                        <span class="flex-shrink mx-3 text-[10px] text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">
                            Atau lengkapi formulir pendaftaran
                        </span>
                        <div class="flex-grow border-t border-slate-200 dark:border-white/10"></div>
                    </div>

                    <!-- Input Nama Lengkap -->
                    <div>
                        <label for="name" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                            Nama Lengkap <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <i data-lucide="user" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-base text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                placeholder="Contoh: Budi Santoso">
                        </div>
                    </div>

                    <!-- WhatsApp & Email (Grid 2-Kolom) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Input WhatsApp -->
                        <div>
                            <label for="phone" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                Nomor WhatsApp <span class="text-[#FF3B30]">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                    <i data-lucide="phone" class="w-4 h-4"></i>
                                </div>
                                <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required inputmode="tel" autocomplete="tel"
                                    class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-base text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                    placeholder="081234567890">
                            </div>
                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                                Kode OTP aktivasi akun akan dikirim ke WhatsApp ini.
                            </p>
                        </div>

                        <!-- Input Email (Opsional) -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="email" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    Email Akun
                                </label>
                                <span class="text-[11px] text-slate-400 dark:text-slate-500">Opsional</span>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                    <i data-lucide="mail" class="w-4 h-4"></i>
                                </div>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                    class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-base text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                    placeholder="budi@example.com">
                            </div>
                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                                Untuk salinan nota &amp; bukti transaksi digital.
                            </p>
                        </div>
                    </div>

                    <!-- Input Kata Sandi & Konfirmasi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Kata Sandi -->
                        <div>
                            <label for="password" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                Kata Sandi <span class="text-[#FF3B30]">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                    <i data-lucide="lock" class="w-4 h-4"></i>
                                </div>
                                <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required minlength="6"
                                    class="w-full pl-10 pr-11 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-base text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                    placeholder="Min. 6 karakter">
                                <button type="button" @click="showPassword = !showPassword"
                                    :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                    class="absolute right-0 top-0 bottom-0 w-11 flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 focus:outline-none transition-colors cursor-pointer">
                                    <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Konfirmasi Sandi -->
                        <div>
                            <label for="password_confirmation" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                Konfirmasi Sandi <span class="text-[#FF3B30]">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                                </div>
                                <input :type="showPasswordConfirm ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required minlength="6"
                                    class="w-full pl-10 pr-11 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-base text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                    placeholder="Ulangi sandi">
                                <button type="button" @click="showPasswordConfirm = !showPasswordConfirm"
                                    :aria-label="showPasswordConfirm ? 'Sembunyikan konfirmasi sandi' : 'Tampilkan konfirmasi sandi'"
                                    class="absolute right-0 top-0 bottom-0 w-11 flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 focus:outline-none transition-colors cursor-pointer">
                                    <i :data-lucide="showPasswordConfirm ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Input Alamat Pengiriman Utama -->
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
                            <textarea name="shipping_address" id="shipping_address" rows="2"
                                placeholder="Nama jalan, nomor rumah/gedung, kelurahan, kecamatan, kota..."
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none resize-none">{{ old('shipping_address') }}</textarea>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                            Alamat ini akan otomatis digunakan sebagai default saat Anda checkout pesanan.
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] hover:from-[#1cd0ff] hover:to-[#006fe6] text-white font-bold text-sm sm:text-[15px] shadow-[0_2px_12px_rgba(0,194,255,0.3)] hover:shadow-[0_4px_20px_rgba(0,194,255,0.5)] active:scale-[0.99] min-h-[50px] transition-all focus:outline-none focus:ring-4 focus:ring-[#007AFF]/25 cursor-pointer">
                            <span>Daftar &amp; Masuk Otomatis</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Login Link -->
                    <div class="text-center pt-3 border-t border-slate-200/80 dark:border-white/10 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                        Sudah memiliki akun pelanggan?
                        <a href="{{ route('customer.login', ['store' => isset($store) && $store ? $store->slug : null, 'redirect' => $redirectTo]) }}"
                            class="font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded ml-1">
                            Masuk Sekarang
                        </a>
                    </div>

                    @if (isset($store) && $store)
                        <div class="text-center pt-1">
                            <a href="{{ url('/' . $store->slug) }}"
                                class="text-xs text-slate-500 dark:text-slate-400 hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition-colors inline-flex items-center gap-1">
                                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                                <span>Kembali ke Etalase Toko {{ $store->name }}</span>
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            <!-- Trust & Privacy Security Callout -->
            <div class="text-center space-y-1 text-slate-500 dark:text-slate-400 text-xs">
                <div class="inline-flex items-center gap-1.5 font-medium text-[#34C759]">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    <span>Data profil Anda terlindungi aman &amp; terisolasi privat</span>
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                    Dengan mendaftar, Anda menyetujui Ketentuan Layanan &amp; Kebijakan Privasi COOCA.
                </p>
            </div>

        </div>
    </div>
@endsection
