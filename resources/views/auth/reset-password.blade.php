@extends('layouts.public_marketing', ['title' => 'Reset Kata Sandi - Cooca UMKM', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-16rem)] flex flex-col justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Apple HIG Header -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-14 h-14 rounded-[20px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] mb-3.5 shadow-sm">
                    <i data-lucide="lock" class="w-7 h-7"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white tracking-tight">Buat Kata Sandi
                    Baru</h1>
                <p class="mt-2 text-sm text-black/60 dark:text-white/60">Tentukan kata sandi baru yang kuat dan mudah Anda
                    ingat</p>
            </div>

            <!-- Apple HIG Card -->
            <div
                class="glass-card bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] rounded-[28px] p-6 sm:p-8 shadow-2xl shadow-black/5 dark:shadow-black/50 relative overflow-hidden transition-all">

                @if ($errors->any())
                    <div
                        class="mb-6 p-4 rounded-[18px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-sm animate-shake">
                        <div class="font-semibold mb-1.5 flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                            <span>Mohon Periksa Kembali:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs opacity-90 pl-1">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label for="email"
                            class="block font-semibold text-black/80 dark:text-white/85 text-xs sm:text-sm mb-2">
                            Alamat Email Akun <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                            </div>
                            <input type="email" name="email" id="email" value="{{ $email ?? old('email') }}"
                                required autofocus
                                class="w-full pl-11 pr-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[16px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="password"
                            class="block font-semibold text-black/80 dark:text-white/85 text-xs sm:text-sm mb-2">
                            Kata Sandi Baru <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative" x-data="{ show: false }">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                <i data-lucide="lock" class="w-5 h-5"></i>
                            </div>
                            <input :type="show ? 'text' : 'password'" name="password" id="password" required
                                class="w-full pl-11 pr-12 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[16px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none"
                                placeholder="Minimal 8 karakter">
                            <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition-colors"
                                tabindex="-1">
                                <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <p class="mt-1.5 text-[11px] sm:text-xs text-black/50 dark:text-white/50">
                            Gunakan kombinasi minimal 8 karakter huruf dan angka.
                        </p>
                    </div>

                    <div>
                        <label for="password_confirmation"
                            class="block font-semibold text-black/80 dark:text-white/85 text-xs sm:text-sm mb-2">
                            Konfirmasi Kata Sandi Baru <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative" x-data="{ show: false }">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                <i data-lucide="lock-check" class="w-5 h-5"></i>
                            </div>
                            <input :type="show ? 'text' : 'password'" name="password_confirmation"
                                id="password_confirmation" required
                                class="w-full pl-11 pr-12 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[16px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none"
                                placeholder="Ulangi kata sandi baru">
                            <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition-colors"
                                tabindex="-1">
                                <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full min-h-[50px] py-3.5 px-5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm sm:text-base shadow-lg shadow-[#007AFF]/25 flex items-center justify-center gap-2.5 transition-all active:scale-[0.98]">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                        <span>Simpan Kata Sandi Baru & Masuk</span>
                    </button>
                </form>

                <div class="mt-6 pt-5 border-t border-black/[0.06] dark:border-white/[0.08] text-center">
                    <a href="{{ route('login') }}"
                        class="text-xs sm:text-sm font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors inline-flex items-center gap-1.5 py-1">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span>Batal & Kembali ke Halaman Masuk</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
