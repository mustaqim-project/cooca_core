@extends('layouts.public_marketing', ['title' => 'Reset Kata Sandi - COOCA', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-14rem)] flex flex-col justify-center py-12 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md mx-auto">

            <!-- Official COOCA Branding & Header -->
            <div class="text-center mb-8">
                @include('auth.partials.brand-logo', ['class' => 'mb-5'])
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Buat Kata Sandi Baru
                </h1>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
                    Tentukan kata sandi baru yang kuat dan mudah Anda ingat untuk akun bisnis Anda
                </p>
            </div>

            <!-- Structured Auth Card -->
            <div class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-black/40 transition-colors">

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs">
                        <div class="font-semibold mb-1 flex items-center gap-1.5 text-sm">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                            <span>Mohon Periksa Kembali:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs opacity-90 pl-1">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block font-semibold text-slate-700 dark:text-slate-200 text-xs sm:text-sm mb-1.5">
                            Alamat Email Akun <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </div>
                            <input type="email" name="email" id="email" value="{{ $email ?? old('email') }}" required autofocus
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all text-sm sm:text-base outline-none">
                        </div>
                    </div>

                    <!-- New Password Input -->
                    <div>
                        <label for="password" class="block font-semibold text-slate-700 dark:text-slate-200 text-xs sm:text-sm mb-1.5">
                            Kata Sandi Baru <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input :type="show ? 'text' : 'password'" name="password" id="password" required
                                class="w-full pl-10 pr-11 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all text-sm sm:text-base outline-none"
                                placeholder="Minimal 8 karakter">
                            <button type="button" @click="show = !show"
                                :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                class="absolute inset-y-0 right-0 w-11 flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 transition-colors focus:outline-none cursor-pointer">
                                <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <p class="mt-1.5 text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">
                            Gunakan kombinasi minimal 8 karakter huruf dan angka.
                        </p>
                    </div>

                    <!-- Password Confirmation Input -->
                    <div>
                        <label for="password_confirmation" class="block font-semibold text-slate-700 dark:text-slate-200 text-xs sm:text-sm mb-1.5">
                            Konfirmasi Kata Sandi Baru <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input :type="show ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required
                                class="w-full pl-10 pr-11 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all text-sm sm:text-base outline-none"
                                placeholder="Ulangi kata sandi baru">
                            <button type="button" @click="show = !show"
                                :aria-label="show ? 'Sembunyikan konfirmasi sandi' : 'Tampilkan konfirmasi sandi'"
                                class="absolute inset-y-0 right-0 w-11 flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 transition-colors focus:outline-none cursor-pointer">
                                <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] hover:from-[#1cd0ff] hover:to-[#006fe6] text-white font-bold text-sm sm:text-[15px] shadow-[0_2px_12px_rgba(0,194,255,0.3)] hover:shadow-[0_4px_20px_rgba(0,194,255,0.5)] active:scale-[0.99] min-h-[46px] transition-all focus:outline-none focus:ring-4 focus:ring-[#007AFF]/25 cursor-pointer">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <span>Simpan Kata Sandi Baru &amp; Masuk</span>
                        </button>
                    </div>
                </form>

                <div class="mt-6 pt-5 border-t border-slate-200/80 dark:border-white/10 text-center">
                    <a href="{{ route('login') }}"
                        class="text-xs sm:text-sm font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors inline-flex items-center gap-1.5 py-1 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span>Batal &amp; Kembali ke Halaman Masuk</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
