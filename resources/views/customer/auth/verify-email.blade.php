@extends('layouts.customer', ['title' => 'Verifikasi Email Pelanggan - COOCA'])

@section('content')
<div class="max-w-md mx-auto py-8 sm:py-16">
    <div class="bento-card p-6 sm:p-8 space-y-6">
        <!-- Apple HIG Header -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-[20px] bg-[#007AFF]/10 text-[#007AFF] mx-auto flex items-center justify-center mb-3 shadow-sm">
                <i data-lucide="mail-check" class="w-7 h-7"></i>
            </div>
            <h1 class="text-2xl font-bold text-black dark:text-white tracking-tight">Verifikasi Email Anda</h1>
            <p class="text-[13px] text-black/60 dark:text-white/60">Satu langkah lagi untuk menyelesaikan pendaftaran akun belanja Anda</p>
        </div>

        <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] text-center space-y-2">
            <p class="text-xs text-black/55 dark:text-white/55">Surat verifikasi dikirimkan ke:</p>
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] font-mono text-xs sm:text-sm font-semibold max-w-full truncate">
                <i data-lucide="mail" class="w-4 h-4 shrink-0"></i>
                <span class="truncate">{{ auth('customer')->user()?->email }}</span>
            </div>
            <p class="text-xs text-black/65 dark:text-white/65 leading-relaxed pt-1">
                Silakan periksa kotak masuk atau folder spam email Anda, lalu klik tombol verifikasi di dalamnya.
            </p>
        </div>

        @if (session('status'))
            <div class="p-3.5 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/25 text-[#34C759] text-xs sm:text-sm flex items-center gap-2.5">
                <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('status') === 'verification-link-sent' ? 'Tautan verifikasi baru telah dikirimkan ke email Anda.' : session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('customer.verification.send') }}" class="space-y-3">
            @csrf
            <button type="submit"
                class="w-full min-h-[48px] py-3 px-5 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm shadow-md shadow-[#007AFF]/20 transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                <i data-lucide="send" class="w-4 h-4"></i>
                <span>Kirim Ulang Tautan Verifikasi</span>
            </button>
        </form>

        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex justify-between items-center text-xs">
            <a href="{{ route('customer.dashboard') }}" class="text-[#007AFF] hover:underline">
                Ke Dashboard
            </a>
            <form action="{{ route('customer.logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-black/50 dark:text-white/50 hover:text-red-500 transition">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
