@extends('layouts.public_marketing', ['title' => 'Lupa Kata Sandi - Cooca UMKM', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-16rem)] flex flex-col justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Apple HIG Header -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-14 h-14 rounded-[20px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] mb-3.5 shadow-sm">
                    <i data-lucide="key-round" class="w-7 h-7"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white tracking-tight">Atur Ulang Kata
                    Sandi</h1>
                <p class="mt-2 text-sm text-black/60 dark:text-white/60">Masukkan email bisnis Anda untuk menerima tautan
                    pemulihan kata sandi instan</p>
            </div>

            <!-- Apple HIG Inset Bento Card -->
            <div
                class="glass-card bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] rounded-[28px] p-6 sm:p-8 shadow-2xl shadow-black/5 dark:shadow-black/50 relative overflow-hidden transition-all">

                @if (session('status'))
                    <div
                        class="mb-6 p-4 rounded-[18px] bg-[#34C759]/10 border border-[#34C759]/25 text-[#34C759] dark:text-[#30D158] text-sm flex items-start gap-3 animate-fade-in">
                        <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0 mt-0.5"></i>
                        <div class="flex-1 leading-relaxed">
                            <span class="font-semibold block">Tautan Terkirim!</span>
                            <span class="text-xs sm:text-sm opacity-90">{{ session('status') }}</span>
                        </div>
                    </div>
                @endif

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

                <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email"
                            class="block font-semibold text-black/80 dark:text-white/85 text-xs sm:text-sm mb-2">
                            Alamat Email Terdaftar <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                autofocus
                                class="w-full pl-11 pr-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[16px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none"
                                placeholder="nama@perusahaan.com">
                        </div>
                        <p class="mt-1.5 text-[11px] sm:text-xs text-black/50 dark:text-white/50">
                            Kami akan mengirimkan surat elektronik dengan petunjuk pengaturan ulang sandi.
                        </p>
                    </div>

                    <button type="submit"
                        class="w-full min-h-[50px] py-3.5 px-5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm sm:text-base shadow-lg shadow-[#007AFF]/25 flex items-center justify-center gap-2.5 transition-all active:scale-[0.98]">
                        <i data-lucide="send" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        <span>Kirim Tautan Pemulihan Kata Sandi</span>
                    </button>
                </form>

                <!-- Boomer / Senior Reassurance Note -->
                <div
                    class="mt-6 p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] text-xs leading-relaxed text-black/65 dark:text-white/65 space-y-2">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="help-circle" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                        <p>
                            <strong>Tidak menerima email?</strong> Periksa folder <em>Spam</em> atau <em>Promosi</em>.
                            Tautan pemulihan berlaku selama 60 menit demi keamanan akun bisnis Anda.
                        </p>
                    </div>
                </div>

                <div
                    class="mt-6 pt-5 border-t border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <a href="{{ route('login') }}"
                        class="font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors inline-flex items-center gap-1.5 py-1">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span>Kembali Masuk</span>
                    </a>
                    <a href="{{ route('account-recovery.create') }}"
                        class="text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white transition-colors">
                        Kendala email/nomor hilang?
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
