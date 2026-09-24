@extends('layouts.public_marketing', ['title' => 'Lupa Kata Sandi - COOCA', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-14rem)] flex flex-col justify-center py-12 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md mx-auto">

            <!-- Official COOCA Branding & Header -->
            <div class="text-center mb-8">
                <a href="{{ route('landing') }}" class="inline-block transition-transform hover:scale-105 mb-5 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 rounded-xl" aria-label="COOCA Beranda">
                    <img src="{{ asset('assets/image/cooca-logo-landscape.png') }}" alt="COOCA" class="h-9 sm:h-10 w-auto object-contain mx-auto">
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Atur Ulang Kata Sandi
                </h1>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
                    Masukkan email bisnis Anda untuk menerima tautan pemulihan kata sandi instan
                </p>
            </div>

            <!-- Structured Auth Card -->
            <div class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-black/40 transition-colors">

                <!-- Success Status -->
                @if (session('status'))
                    <div class="mb-5 p-4 rounded-xl bg-[#34C759]/10 border border-[#34C759]/25 text-[#34C759] dark:text-[#30D158] text-sm flex items-start gap-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0 mt-0.5"></i>
                        <div class="flex-1 leading-relaxed">
                            <span class="font-semibold block">Tautan Terkirim!</span>
                            <span class="text-xs sm:text-sm opacity-90">{{ session('status') }}</span>
                        </div>
                    </div>
                @endif

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

                <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block font-semibold text-slate-700 dark:text-slate-200 text-xs sm:text-sm mb-1.5">
                            Alamat Email Terdaftar <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all text-sm sm:text-base outline-none"
                                placeholder="nama@perusahaan.com">
                        </div>
                        <p class="mt-1.5 text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">
                            Kami akan mengirimkan surat elektronik dengan petunjuk pengaturan ulang sandi.
                        </p>
                    </div>

                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] hover:from-[#1cd0ff] hover:to-[#006fe6] text-white font-bold text-sm sm:text-[15px] shadow-[0_2px_12px_rgba(0,194,255,0.3)] hover:shadow-[0_4px_20px_rgba(0,194,255,0.5)] active:scale-[0.99] min-h-[46px] transition-all focus:outline-none focus:ring-4 focus:ring-[#007AFF]/25 cursor-pointer">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Kirim Tautan Pemulihan Kata Sandi</span>
                    </button>
                </form>

                <!-- Reassurance Note -->
                <div class="mt-6 p-4 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 text-xs leading-relaxed text-slate-600 dark:text-slate-400 space-y-2">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="help-circle" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                        <p>
                            <strong>Tidak menerima email?</strong> Periksa folder <em>Spam</em> atau <em>Promosi</em>. Tautan pemulihan berlaku selama 60 menit demi keamanan akun bisnis Anda.
                        </p>
                    </div>
                </div>

                <div class="mt-6 pt-5 border-t border-slate-200/80 dark:border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <a href="{{ route('login') }}"
                        class="font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors inline-flex items-center gap-1.5 py-1 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span>Kembali Masuk</span>
                    </a>
                    <a href="{{ route('account-recovery.create') }}"
                        class="text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded">
                        Kendala email/nomor hilang?
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
