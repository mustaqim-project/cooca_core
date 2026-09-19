@extends('layouts.public_marketing', ['title' => 'Verifikasi WhatsApp - Cooca', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-16rem)] flex flex-col justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8"
        x-data="coocaOtp.boxes({{ (int) ($expiresAt ?? 0) }}, {{ (int) ($resendIn ?? 0) }})">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Apple HIG Header -->
            <div class="text-center mb-7">
                <div
                    class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#34C759]/10 text-[#34C759] dark:bg-[#30D158]/15 dark:text-[#30D158] mb-3 shadow-sm transition-transform hover:scale-105">
                    <i data-lucide="message-square" class="w-7 h-7"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white tracking-tight">Verifikasi WhatsApp
                </h1>
                <p class="mt-1.5 text-xs sm:text-sm text-black/60 dark:text-white/60">
                    Masukkan kode 6 digit yang dikirim ke <span
                        class="font-mono font-bold text-black dark:text-white px-1.5 py-0.5 rounded bg-black/[0.04] dark:bg-white/[0.08]">{{ $phone }}</span>
                </p>
            </div>

            <div
                class="glass-card bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] rounded-[28px] p-6 sm:p-8 shadow-2xl shadow-black/5 dark:shadow-black/60 transition-colors">
                @if (session('status'))
                    <div
                        class="mb-5 p-3.5 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/25 text-[#34C759] dark:text-[#30D158] text-xs sm:text-sm flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <div
                    class="mb-5 p-3.5 rounded-[16px] bg-[#007AFF]/10 border border-[#007AFF]/20 text-[#007AFF] dark:text-[#0A84FF] text-xs sm:text-sm flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                    <span><strong>Bypass / Pengujian:</strong> Gunakan kode OTP <code
                            class="font-mono font-bold bg-[#007AFF]/15 px-1.5 py-0.5 rounded">123456</code> untuk
                        verifikasi instan.</span>
                </div>

                @if ($deliveryError)
                    <div
                        class="mb-5 p-3.5 rounded-[16px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-[#FF9500] dark:text-[#FF9F0A] text-xs sm:text-sm flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                        <span>{{ $deliveryError }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="mb-5 p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs">
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

                <form method="POST" action="{{ route('register.verify.submit') }}" class="space-y-5"
                    x-data="{ submitting: false }"
                    @submit="submitting = true; if ($el.querySelector('input[name=otp]')) $el.querySelector('input[name=otp]').value = otp()">
                    @csrf
                    <div>
                        <label for="otp"
                            class="block text-xs sm:text-sm font-semibold text-black/80 dark:text-white/85 uppercase tracking-wider mb-2.5 text-center">
                            Masukkan 6-Digit Kode OTP
                        </label>
                        <input type="hidden" name="otp" :value="otp()" required>
                        <div class="flex justify-between gap-1.5 sm:gap-2.5">
                            <template x-for="(_, i) in [0, 1, 2, 3, 4, 5]" :key="i">
                                <input :id="'otp-box-' + i" :value="parts[i]" @input="handleInput(i, $event)"
                                    @keydown="handleKeydown(i, $event)" @paste="paste($event)" type="text"
                                    inputmode="numeric" maxlength="1" autocapitalize="off" spellcheck="false"
                                    :autocomplete="i === 0 ? 'one-time-code' : 'off'" :aria-label="'Digit ke-' + (i + 1)"
                                    class="w-full h-12 sm:h-14 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[14px] text-center text-xl sm:text-2xl font-mono font-bold text-black dark:text-white transition-all">
                            </template>
                        </div>
                        <div class="flex items-center justify-between mt-3 text-xs text-black/55 dark:text-white/55">
                            <span x-show="available()" class="inline-flex items-center gap-1.5">
                                <i data-lucide="timer" class="w-4 h-4 shrink-0 text-[#FF9500] dark:text-[#FF9F0A]"></i>
                                <span>Berlaku <span class="font-mono font-bold text-[#FF9500] dark:text-[#FF9F0A]"
                                        x-text="clockLabel()"></span> lagi</span>
                            </span>
                            <span x-show="!available()" class="text-[#FF3B30] dark:text-[#FF453A] font-semibold">Kode OTP
                                kedaluwarsa - silakan kirim ulang.</span>
                        </div>
                    </div>

                    <button type="submit" :disabled="submitting" :class="submitting ? 'opacity-60 cursor-not-allowed' : ''"
                        class="w-full min-h-[50px] py-3.5 px-4 rounded-[14px] glow-btn bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm sm:text-base shadow-lg shadow-[#007AFF]/25 transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                        <span>Verifikasi &amp; Buka Akun</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>

                <form method="POST" action="{{ route('register.verify.resend') }}" class="mt-4 text-center">
                    @csrf
                    <button type="submit" :disabled="cooldown() > 0"
                        :class="cooldown() > 0 ? 'opacity-40 cursor-not-allowed' : ''"
                        class="min-h-[44px] px-3 py-2 text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors inline-flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="cooldown() > 0 ? 'animate-spin' : ''"></i>
                        <span x-show="cooldown() <= 0">Kirim Ulang Kode OTP</span>
                        <span x-show="cooldown() > 0">Kirim ulang dalam <span class="font-mono font-bold"
                                x-text="cooldown()"></span> detik</span>
                    </button>
                </form>

                <!-- Ganti Nomor WhatsApp Form -->
                <div class="mt-5 pt-4 border-t border-black/5 dark:border-white/10">
                    <form method="POST" action="{{ route('register.verify.change') }}" x-data="{ open: false }"
                        class="text-center">
                        @csrf
                        <button type="button" @click="open = !open"
                            class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors cursor-pointer min-h-[40px] px-2">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                            <span>Nomor keliru? Ganti Nomor WhatsApp</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform"
                                :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="open" x-transition
                            class="mt-3 text-left p-3.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-2"
                            style="display: none;">
                            <label for="new-phone"
                                class="block text-xs font-semibold text-black/80 dark:text-white/85">Nomor WhatsApp
                                Baru</label>
                            <div class="flex gap-2">
                                <input type="tel" id="new-phone" name="phone" inputmode="tel" required maxlength="20"
                                    placeholder="081234567890"
                                    class="min-w-0 flex-1 px-3.5 py-2.5 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[12px] text-[16px] sm:text-sm text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all">
                                <button type="submit"
                                    class="shrink-0 min-h-[42px] px-4 py-2 rounded-[12px] glow-btn bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs sm:text-sm shadow-md shadow-[#007AFF]/20 transition-all flex items-center gap-1">
                                    <span>Kirim OTP</span>
                                </button>
                            </div>
                            <p class="text-[11px] text-black/50 dark:text-white/50">OTP baru akan dialihkan ke nomor
                                tersebut tanpa mengubah data registrasi lainnya.</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/otp-widget.js') }}"></script>
@endsection
