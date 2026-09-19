@extends('layouts.customer', ['title' => 'Verifikasi WhatsApp'])

@section('content')
<div class="max-w-md mx-auto py-6 sm:py-16">
    <div class="bento-card p-8 sm:p-10 space-y-7">

        {{-- Icon + Header --}}
        <div class="text-center space-y-3">
            <div class="w-16 h-16 rounded-3xl bg-[#34C759]/10 border border-[#34C759]/20 mx-auto flex items-center justify-center text-3xl">
                📲
            </div>
            <h1 class="text-2xl font-extrabold text-black dark:text-white">Verifikasi WhatsApp</h1>
            <p class="text-sm text-black/50 dark:text-white/50 leading-relaxed">
                Masukkan kode 6 digit yang dikirim ke WhatsApp
                <span class="font-semibold text-black dark:text-white">{{ $customer->phone }}</span>.
                <br>Verifikasi ini hanya dilakukan sekali.
            </p>
        </div>

        {{-- Alerts --}}
        @if($errors->any())
            <div class="p-4 bg-[#FF3B30]/10 border border-[#FF3B30]/20 rounded-2xl text-sm text-[#FF3B30]">
                @foreach($errors->all() as $err)<p>{{ $err }}</p>@endforeach
            </div>
        @endif
        @if(session('status'))
            <div class="p-4 bg-[#34C759]/10 border border-[#34C759]/20 rounded-2xl text-sm text-[#34C759]">{{ session('status') }}</div>
        @endif

        @if(!$already_sent)
            {{-- Send OTP first --}}
            <form method="POST" action="{{ route('customer.otp.send') }}">
                @csrf
                <button type="submit"
                        class="w-full py-4 bg-[#34C759] text-white text-[15px] font-bold rounded-2xl hover:bg-[#28A745] active:scale-[0.98] transition-all shadow-lg shadow-[#34C759]/30">
                    Kirim Kode ke WhatsApp
                </button>
            </form>
        @else
            {{-- Enter OTP --}}
            <form method="POST" action="{{ route('customer.otp.verify') }}" class="space-y-5" id="otp-form">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50 mb-2">
                        Kode OTP (6 digit)
                    </label>
                    <input type="text" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                           autofocus autocomplete="one-time-code"
                           placeholder="_ _ _ _ _ _"
                           class="w-full text-center text-3xl font-black tracking-[0.5em] py-4 rounded-2xl border-2 border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] focus:outline-none focus:border-[#34C759]/60 focus:ring-2 focus:ring-[#34C759]/20 transition-all"
                           value="{{ old('otp') }}">
                    <p class="text-xs text-[#007AFF] dark:text-[#0A84FF] text-center mt-2">
                        Bypass / Uji Coba: gunakan kode <code class="font-mono font-bold bg-[#007AFF]/15 px-1.5 py-0.5 rounded">123456</code>
                    </p>
                    @error('otp')
                        <p class="text-[#FF3B30] text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full py-4 bg-[#007AFF] text-white text-[15px] font-bold rounded-2xl hover:bg-[#0062CC] active:scale-[0.98] transition-all shadow-lg shadow-[#007AFF]/30">
                    Verifikasi Sekarang
                </button>
            </form>

            {{-- Resend with timer --}}
            <div class="text-center">
                <form method="POST" action="{{ route('customer.otp.send') }}" id="resend-form">
                    @csrf
                    <button type="submit" id="resend-btn"
                            class="text-sm text-black/40 dark:text-white/40 disabled:opacity-40 disabled:cursor-not-allowed hover:text-[#007AFF] transition-colors">
                        Kirim ulang kode
                        <span id="resend-timer" class="font-semibold text-[#007AFF]"></span>
                    </button>
                </form>
            </div>
        @endif

        {{-- Change phone --}}
        <div class="text-center text-sm text-black/40 dark:text-white/40">
            Nomor salah?
            <a href="{{ route('customer.profile.complete') }}" class="text-[#007AFF] font-semibold hover:underline">
                Ubah nomor WhatsApp
            </a>
        </div>

    </div>
</div>

@push('scripts')
@if($already_sent)
<script>
(function() {
    const btn = document.getElementById('resend-btn');
    const timerEl = document.getElementById('resend-timer');
    let seconds = 60;
    btn.disabled = true;

    const iv = setInterval(() => {
        seconds--;
        if (seconds <= 0) {
            clearInterval(iv);
            btn.disabled = false;
            timerEl.textContent = '';
        } else {
            timerEl.textContent = '(' + seconds + 's)';
        }
    }, 1000);
    timerEl.textContent = '(' + seconds + 's)';

    // Auto-focus and auto-submit on 6 digits
    const otpInput = document.querySelector('input[name=otp]');
    if (otpInput) {
        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
            if (this.value.length === 6) {
                document.getElementById('otp-form').submit();
            }
        });
    }
})();
</script>
@endif
@endpush
@endsection