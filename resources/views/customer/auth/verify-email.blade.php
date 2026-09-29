@extends('layouts.public_marketing', ['title' => 'Verifikasi Email Pelanggan - COOCA', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-14rem)] flex flex-col justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md mx-auto">

            <!-- Official COOCA Branding & Header -->
            <div class="text-center mb-6 sm:mb-8">
                <div class="w-16 h-16 rounded-[22px] bg-[#007AFF]/10 border border-[#007AFF]/20 text-[#007AFF] dark:text-[#0A84FF] mx-auto flex items-center justify-center mb-4 shadow-sm">
                    <i data-lucide="mail-check" class="w-8 h-8"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Verifikasi Email Pelanggan
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
                    Satu langkah lagi untuk mengaktifkan akun belanja pelanggan Anda di COOCA.
                </p>
            </div>

            <!-- Structured Auth Card -->
            <div class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-black/40 transition-colors">

                <!-- Email Target Info Box -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 text-center space-y-2 mb-5">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Surat verifikasi dikirimkan ke:</p>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-mono text-xs sm:text-sm font-semibold max-w-full truncate">
                        <i data-lucide="mail" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">{{ auth('customer')->user()?->email }}</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed pt-1">
                        Silakan periksa kotak masuk atau folder spam email Anda, lalu klik tombol verifikasi di dalamnya.
                    </p>
                </div>

                <!-- Status / Resend Alert -->
                @if (session('status'))
                    <div class="mb-5 p-4 rounded-xl bg-[#34C759]/10 border border-[#34C759]/25 text-[#34C759] dark:text-[#30D158] text-xs sm:text-sm flex items-start gap-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0 mt-0.5"></i>
                        <div class="flex-1 leading-relaxed">
                            <span class="font-semibold block">Tautan Baru Terkirim!</span>
                            <span class="text-xs sm:text-sm opacity-90">
                                {{ session('status') === 'verification-link-sent' ? 'Tautan verifikasi baru telah berhasil dikirimkan ke email Anda.' : session('status') }}
                            </span>
                        </div>
                    </div>
                @endif

                <!-- Resend Form -->
                <form method="POST" action="{{ route('customer.verification.send') }}" class="space-y-4">
                    @csrf
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] hover:from-[#1cd0ff] hover:to-[#006fe6] text-white font-bold text-sm sm:text-[15px] shadow-[0_2px_12px_rgba(0,194,255,0.3)] hover:shadow-[0_4px_20px_rgba(0,194,255,0.5)] active:scale-[0.99] min-h-[50px] transition-all focus:outline-none focus:ring-4 focus:ring-[#007AFF]/25 cursor-pointer">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Kirim Ulang Tautan Verifikasi</span>
                    </button>
                </form>

                <!-- Navigation & Logout Actions -->
                <div class="flex items-center justify-between pt-5 mt-5 border-t border-slate-200/80 dark:border-white/10 text-xs sm:text-sm">
                    @if (auth('customer')->user()?->hasVerifiedEmail())
                        <a href="{{ route('customer.dashboard') }}"
                            class="text-[#007AFF] dark:text-[#0A84FF] hover:underline font-semibold flex items-center gap-1 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded">
                            <span>Lanjut ke Dashboard</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    @else
                        <a href="{{ route('customer.dashboard') }}"
                            class="text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                            Lewati ke Dashboard
                        </a>
                    @endif

                    <form method="POST" action="{{ route('customer.logout') }}">
                        @csrf
                        <button type="submit"
                            class="text-[#FF3B30] dark:text-[#FF453A] hover:underline font-semibold flex items-center gap-1.5 py-1 focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/20 rounded cursor-pointer">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span>Keluar Akun</span>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
