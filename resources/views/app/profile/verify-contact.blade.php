@extends('layouts.app', ['title' => 'Verifikasi Nomor WhatsApp', 'headerTitle' => 'Verifikasi Nomor WhatsApp', 'headerSubtitle' => 'Masukkan OTP WhatsApp untuk mengonfirmasi pembaruan nomor'])

@section('content')
    <div class="max-w-[520px] mx-auto py-8" x-data="coocaOtp.boxes({{ (int) ($expiresAt ?? 0) }}, {{ (int) ($resendIn ?? 0) }})">
        <div
            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
            @if (session('status'))
                <div class="mb-5 rounded-[10px] bg-[#34C759]/10 border border-[#34C759]/20 p-3 text-[13px] text-[#248A3D]">
                    {{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-[10px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 p-3 text-[13px] text-[#FF3B30]">
                    {{ $errors->first() }}</div>
            @endif

            <p class="mb-5 text-[13px] text-black/55 dark:text-white/55 leading-relaxed">
                Masukkan OTP yang dikirim ke WhatsApp <strong
                    class="font-mono text-black dark:text-white">{{ $phone }}</strong>.
            </p>

            <form method="POST" action="{{ route('profile.contact.verify.submit') }}" class="space-y-5"
                x-data="{ submitting: false }"
                @submit="submitting = true; if ($el.querySelector('input[name=otp]')) $el.querySelector('input[name=otp]').value = otp()">
                @csrf
                <div>
                    <label for="otp"
                        class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Kode OTP
                        WhatsApp</label>
                    <input type="hidden" name="otp" :value="otp()" required>
                    <div class="flex justify-between gap-2">
                        <template x-for="(_, i) in [0, 1, 2, 3, 4, 5]" :key="i">
                            <input :id="'otp-box-' + i" :value="parts[i]" @input="handleInput(i, $event)"
                                @keydown="handleKeydown(i, $event)" @paste="paste($event)" type="text"
                                inputmode="numeric" maxlength="1" autocapitalize="off" spellcheck="false"
                                :autocomplete="i === 0 ? 'one-time-code' : 'off'" :aria-label="'Digit ke-' + (i + 1)"
                                class="w-full h-12 bg-black/[0.04] dark:bg-white/[0.06] rounded-[10px] text-center text-xl text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]/50 transition">
                        </template>
                    </div>
                    <div class="flex items-center justify-between mt-2 text-[11px] text-black/45 dark:text-white/45">
                        <span x-show="available()" class="inline-flex items-center gap-1">
                            <i data-lucide="timer" class="w-3 h-3 shrink-0"></i>
                            <span>Berlaku <span class="font-mono font-semibold text-black/60 dark:text-white/60"
                                    x-text="clockLabel()"></span> lagi.</span>
                        </span>
                        <span x-show="!available()" class="text-[#FF3B30]">Kode OTP kedaluwarsa - kirim ulang.</span>
                    </div>
                </div>
                <button type="submit" :disabled="submitting" :class="submitting ? 'opacity-60 cursor-not-allowed' : ''"
                    class="w-full h-10 rounded-[10px] bg-[#34C759] hover:bg-[#248A3D] text-white text-[13px] font-semibold transition">Verifikasi
                    Nomor WhatsApp</button>
            </form>
            <form method="POST" action="{{ route('profile.contact.verify.resend') }}" class="mt-4 text-center">
                @csrf
                <button type="submit" :disabled="cooldown() > 0"
                    :class="cooldown() > 0 ? 'opacity-40 cursor-not-allowed' : ''"
                    class="text-[12px] font-semibold text-[#007AFF] hover:underline">
                    <span x-show="cooldown() <= 0">Kirim ulang OTP</span>
                    <span x-show="cooldown() > 0">Kirim ulang dalam <span x-text="cooldown()"></span> dtk</span>
                </button>
            </form>
            <a href="{{ route('profile.edit') }}"
                class="block mt-4 text-center text-[12px] text-black/50 dark:text-white/50 hover:text-[#007AFF]">Kembali ke
                profil</a>
        </div>
    </div>

    <script src="{{ asset('js/otp-widget.js') }}"></script>
@endsection
