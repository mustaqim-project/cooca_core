@extends('layouts.public_marketing', ['title' => 'Verifikasi Email Anda - Cooca', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-16rem)] flex flex-col justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Apple HIG Header -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-14 h-14 rounded-[20px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] mb-3.5 shadow-sm">
                    <i data-lucide="mail-check" class="w-7 h-7"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white tracking-tight">Verifikasi Email
                    Anda</h1>
                <p class="mt-2 text-sm text-black/60 dark:text-white/60">Satu langkah lagi untuk mengaktifkan akun bisnis
                    Cooca Anda</p>
            </div>

            <div
                class="glass-card bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] rounded-[28px] p-6 sm:p-8 shadow-2xl shadow-black/5 dark:shadow-black/50 transition-all">
                <div
                    class="p-4 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] text-center space-y-2 mb-5">
                    <p class="text-xs text-black/55 dark:text-white/55">Surat verifikasi dikirimkan ke:</p>
                    <div
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] font-mono text-xs sm:text-sm font-semibold max-w-full truncate">
                        <i data-lucide="mail" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">{{ auth()->user()->email }}</span>
                    </div>
                    <p class="text-xs text-black/65 dark:text-white/65 leading-relaxed pt-1">
                        Silakan buka email Anda dan klik tombol atau tautan verifikasi di dalamnya untuk langsung masuk ke
                        dashboard.
                    </p>
                </div>

                @if (session('status'))
                    <div
                        class="mb-5 p-4 rounded-[18px] bg-[#34C759]/10 border border-[#34C759]/25 text-[#34C759] dark:text-[#30D158] text-xs sm:text-sm flex items-start gap-3 animate-fade-in">
                        <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0 mt-0.5"></i>
                        <div class="flex-1 leading-relaxed">
                            <span class="font-semibold block">Tautan Baru Terkirim!</span>
                            <span>{{ session('status') === 'verification-link-sent' ? 'Tautan verifikasi baru telah berhasil dikirimkan ke alamat email Anda.' : session('status') }}</span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
                    @csrf
                    <button type="submit"
                        class="w-full min-h-[50px] py-3.5 px-5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm sm:text-base shadow-lg shadow-[#007AFF]/25 transition-all flex items-center justify-center gap-2.5 active:scale-[0.98]">
                        <i data-lucide="send" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        <span>Kirim Ulang Tautan Verifikasi</span>
                    </button>
                </form>

                <!-- Emergency Recovery / Inaccessible Email Bento Box -->
                <div class="mt-5 p-4 rounded-[20px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-xs text-left">
                    <div class="flex items-start gap-3">
                        <i data-lucide="help-circle" class="w-5 h-5 text-[#FF9500] dark:text-[#FF9F0A] shrink-0 mt-0.5"></i>
                        <div>
                            <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">Email salah ketik
                                atau tidak bisa dibuka?</span>
                            <p class="text-xs text-black/60 dark:text-white/60 mt-1 mb-2 leading-relaxed">
                                Jika Anda salah memasukkan alamat email saat mendaftar, Anda dapat mengajukan pembaruan
                                email dengan melampirkan identitas resmi.
                            </p>
                            <a href="{{ route('account-recovery.create') }}"
                                class="inline-flex items-center gap-1.5 font-bold text-[#FF9500] dark:text-[#FF9F0A] hover:underline transition-colors text-xs py-0.5">
                                <span>Ajukan Pemulihan Akses Akun</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between pt-5 mt-5 border-t border-black/[0.06] dark:border-white/[0.08] text-xs">
                    @if (auth()->user()->hasVerifiedEmail())
                        <a href="{{ route('dashboard') }}"
                            class="text-[#007AFF] dark:text-[#0A84FF] hover:underline font-semibold flex items-center gap-1">
                            <span>Lanjut ke Dashboard</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    @else
                        <span class="text-black/45 dark:text-white/45">Menunggu konfirmasi email...</span>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="text-[#FF3B30] dark:text-[#FF453A] hover:underline font-semibold flex items-center gap-1.5 py-1">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span>Keluar Akun</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
