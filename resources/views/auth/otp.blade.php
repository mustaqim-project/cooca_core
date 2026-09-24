@extends('layouts.public_marketing', ['title' => 'Verifikasi Keamanan - COOCA', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-14rem)] flex flex-col justify-center py-12 sm:py-16 px-4 sm:px-6 lg:px-8"
        x-data="coocaOtp.boxes({{ (int) ($expiresAt ?? 0) }}, {{ (int) ($resendIn ?? 0) }})">
        <div class="w-full max-w-md mx-auto">

            <!-- Official COOCA Branding & Header -->
            <div class="text-center mb-8">
                <a href="{{ route('landing') }}" class="inline-block transition-transform hover:scale-105 mb-5 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 rounded-xl" aria-label="COOCA Beranda">
                    <img src="{{ asset('assets/image/cooca-logo-landscape.png') }}" alt="COOCA" class="h-9 sm:h-10 w-auto object-contain mx-auto">
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Verifikasi Keamanan
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
                    Masukkan kode OTP yang dikirim ke WhatsApp <span class="font-mono font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded bg-slate-100 dark:bg-white/10">{{ $phone }}</span>
                </p>
            </div>

            <!-- Structured Auth Card -->
            <div class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-black/40 transition-colors">

                @if (session('status'))
                    <div class="mb-5 p-3.5 rounded-xl bg-[#34C759]/10 border border-[#34C759]/25 text-[#34C759] dark:text-[#30D158] text-xs sm:text-sm flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if (isset($activeRecovery) && $activeRecovery && $activeRecovery->status === \App\Models\AccountRecoveryRequest::STATUS_APPROVED)
                    <div class="mb-5 p-3.5 rounded-xl bg-[#34C759]/10 border border-[#34C759]/25 text-slate-900 dark:text-white text-xs sm:text-sm flex items-start gap-3">
                        <i data-lucide="check-circle-2" class="w-4 h-4 mt-0.5 text-[#34C759] dark:text-[#30D158] shrink-0"></i>
                        <div>
                            <strong class="font-bold block text-[#34C759] dark:text-[#30D158]">Pemulihan Akun Berhasil Disetujui</strong>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                                Nomor kontak pemulihan akun Anda telah dialihkan ke <span class="font-mono font-semibold">{{ $activeRecovery->new_phone }}</span>.
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Dev Testing Bypass Alert -->
                <div class="mb-5 p-3.5 rounded-xl bg-[#007AFF]/10 border border-[#007AFF]/20 text-[#007AFF] dark:text-[#0A84FF] text-xs sm:text-sm flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                    <span><strong>Bypass / Pengujian:</strong> Gunakan kode OTP <code class="font-mono font-bold bg-[#007AFF]/15 px-1.5 py-0.5 rounded">123456</code> untuk verifikasi instan.</span>
                </div>

                @if ($deliveryError && !$errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-[#FF9500]/10 border border-[#FF9500]/25 text-[#FF9500] dark:text-[#FF9F0A] text-xs sm:text-sm flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                        <span>{{ $deliveryError }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs">
                        <div class="font-semibold mb-1 flex items-center gap-1.5 text-sm">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                            <span>Verifikasi Belum Berhasil:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs opacity-90 pl-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('auth.otp.verify') }}" class="space-y-5" x-data="{ submitting: false }"
                    @submit="submitting = true; if ($el.querySelector('input[name=otp]')) $el.querySelector('input[name=otp]').value = otp()">
                    @csrf
                    <div>
                        <label for="otp" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-3 text-center">
                            Masukkan 6-Digit Kode OTP
                        </label>
                        <input type="hidden" name="otp" :value="otp()" required>
                        <div class="flex justify-between gap-1.5 sm:gap-2.5">
                            <template x-for="(_, i) in [0, 1, 2, 3, 4, 5]" :key="i">
                                <input :id="'otp-box-' + i" :value="parts[i]" @input="handleInput(i, $event)"
                                    @keydown="handleKeydown(i, $event)" @paste="paste($event)" type="text"
                                    inputmode="numeric" maxlength="1" autocapitalize="off" spellcheck="false"
                                    :autocomplete="i === 0 ? 'one-time-code' : 'off'" :aria-label="'Digit ke-' + (i + 1)"
                                    class="w-full h-12 sm:h-14 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-center text-xl sm:text-2xl font-mono font-bold text-slate-900 dark:text-white transition-all outline-none">
                            </template>
                        </div>
                        <div class="flex items-center justify-between mt-3 text-xs text-slate-500 dark:text-slate-400">
                            <span x-show="available()" class="inline-flex items-center gap-1.5">
                                <i data-lucide="timer" class="w-4 h-4 shrink-0 text-[#FF9500] dark:text-[#FF9F0A]"></i>
                                <span>Berlaku <span class="font-mono font-bold text-[#FF9500] dark:text-[#FF9F0A]" x-text="clockLabel()"></span> lagi</span>
                            </span>
                            <span x-show="!available()" class="text-[#FF3B30] dark:text-[#FF453A] font-semibold">
                                Kode OTP kedaluwarsa - silakan kirim ulang.
                            </span>
                        </div>
                    </div>

                    <button type="submit" :disabled="submitting" :class="submitting ? 'opacity-60 cursor-not-allowed' : ''"
                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] hover:from-[#1cd0ff] hover:to-[#006fe6] text-white font-bold text-sm sm:text-[15px] shadow-[0_2px_12px_rgba(0,194,255,0.3)] hover:shadow-[0_4px_20px_rgba(0,194,255,0.5)] active:scale-[0.99] min-h-[50px] transition-all focus:outline-none focus:ring-4 focus:ring-[#007AFF]/25 cursor-pointer">
                        <span>Verifikasi &amp; Masuk ke Dashboard</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>

                <form method="POST" action="{{ route('auth.otp.resend') }}" class="mt-4 text-center">
                    @csrf
                    <button type="submit" :disabled="cooldown() > 0"
                        :class="cooldown() > 0 ? 'opacity-40 cursor-not-allowed' : ''"
                        class="min-h-[44px] px-3 py-2 text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors inline-flex items-center justify-center gap-1.5 cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="cooldown() > 0 ? 'animate-spin' : ''"></i>
                        <span x-show="cooldown() <= 0">Kirim Ulang Kode OTP</span>
                        <span x-show="cooldown() > 0">Kirim ulang dalam <span class="font-mono font-bold" x-text="cooldown()"></span> detik</span>
                    </button>
                </form>

                <!-- Status / Pengajuan Pemulihan Akses Akun -->
                @if (isset($activeRecovery) && $activeRecovery)
                    @if ($activeRecovery->status === \App\Models\AccountRecoveryRequest::STATUS_PENDING)
                        <div class="mt-5 p-4 rounded-xl bg-[#007AFF]/10 border border-[#007AFF]/20 text-xs text-left">
                            <div class="flex items-start gap-3">
                                <i data-lucide="clock" class="w-5 h-5 text-[#007AFF] dark:text-[#0A84FF] shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm block">Permohonan Pemulihan Akun Sedang Ditinjau</span>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 mb-2 leading-relaxed">
                                        Tiket <strong class="text-[#007AFF] dark:text-[#0A84FF] font-mono">{{ $activeRecovery->ticket_number }}</strong> sedang diproses Administrator. WhatsApp baru (<span class="font-mono font-semibold text-slate-900 dark:text-white">{{ $activeRecovery->new_phone }}</span>) akan aktif otomatis setelah disetujui.
                                    </p>
                                    <a href="{{ route('account-recovery.status', $activeRecovery->ticket_number) }}"
                                        class="inline-flex items-center gap-1 font-bold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors text-xs">
                                        <span>Cek Status Tiket &rarr;</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @elseif ($activeRecovery->status === \App\Models\AccountRecoveryRequest::STATUS_APPROVED)
                        <div class="mt-5 p-4 rounded-xl bg-[#34C759]/10 border border-[#34C759]/20 text-xs text-left">
                            <div class="flex items-start gap-3">
                                <i data-lucide="check-circle" class="w-5 h-5 text-[#34C759] dark:text-[#30D158] shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm block">Pemulihan Akun Telah Disetujui</span>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                                        Nomor WhatsApp telah dialihkan ke nomor baru Anda (<strong class="text-[#34C759] dark:text-[#30D158] font-mono">{{ $activeRecovery->new_phone }}</strong>). Kode OTP di atas telah dikirimkan ke nomor tersebut.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-5 p-4 rounded-xl bg-[#FF9500]/10 border border-[#FF9500]/20 text-xs text-left">
                            <div class="flex items-start gap-3">
                                <i data-lucide="help-circle" class="w-5 h-5 text-[#FF9500] dark:text-[#FF9F0A] shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm block">HP hilang atau nomor WhatsApp hangus?</span>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 mb-2 leading-relaxed">
                                        Owner bisnis dapat mengajukan pemulihan akses dan ganti nomor WhatsApp/email dengan melampirkan berkas identitas &amp; dokumen usaha.
                                    </p>
                                    <a href="{{ route('account-recovery.create') }}"
                                        class="inline-flex items-center gap-1 font-bold text-[#FF9500] dark:text-[#FF9F0A] hover:underline transition-colors text-xs">
                                        <span>Ajukan Pemulihan Akses &rarr;</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="mt-5 p-4 rounded-xl bg-[#FF9500]/10 border border-[#FF9500]/20 text-xs text-left">
                        <div class="flex items-start gap-3">
                            <i data-lucide="help-circle" class="w-5 h-5 text-[#FF9500] dark:text-[#FF9F0A] shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm block">HP hilang atau nomor WhatsApp hangus?</span>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 mb-2 leading-relaxed">
                                    Owner bisnis dapat mengajukan pemulihan akses dan ganti nomor WhatsApp/email dengan melampirkan berkas identitas &amp; dokumen usaha.
                                </p>
                                <a href="{{ route('account-recovery.create') }}"
                                    class="inline-flex items-center gap-1 font-bold text-[#FF9500] dark:text-[#FF9F0A] hover:underline transition-colors text-xs">
                                    <span>Ajukan Pemulihan Akses &rarr;</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('logout') }}" class="mt-5 pt-4 border-t border-slate-200/80 dark:border-white/10 text-center">
                    @csrf
                    <button type="submit"
                        class="min-h-[40px] px-3 text-xs sm:text-sm text-slate-500 dark:text-slate-400 hover:text-[#FF3B30] dark:hover:text-[#FF453A] transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/20 rounded">
                        Keluar dari sesi akun
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/otp-widget.js') }}"></script>
@endsection
