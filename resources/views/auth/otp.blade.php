@extends('layouts.guest', ['title' => 'Verifikasi Keamanan — Cooca UMKM'])

@section('content')
    <div class="sm:mx-auto sm:w-full sm:max-w-md px-4">
        <div class="text-center mb-8">
            <a href="{{ route('landing') }}" class="inline-flex items-center justify-center mb-4">
                <img src="https://cooca.id/assets/image/1785229034_logo_dark.png" alt="COOCA.ID" style="height: 38px; width: auto; object-fit: contain;">
            </a>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Verifikasi Keamanan</h2>
            <p class="mt-2 text-sm text-slate-400">Masukkan OTP yang dikirim ke WhatsApp {{ $phone }}.</p>
        </div>

        <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl shadow-black/60">
            @if (session('status'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs">{{ session('status') }}</div>
            @endif
            @if ($deliveryError)
                <div class="mb-5 p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs">{{ $deliveryError }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs">
                    <ul class="list-disc list-inside space-y-0.5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('auth.otp.verify') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="otp" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Kode OTP</label>
                    <input type="text" name="otp" id="otp" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus
                        class="w-full px-3.5 py-3 bg-slate-950/80 border border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl text-center text-2xl tracking-[0.45em] text-white transition-colors" placeholder="000000">
                </div>
                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-semibold text-sm shadow-lg shadow-emerald-500/25 transition-all">Verifikasi &amp; Lanjutkan</button>
            </form>

            <form method="POST" action="{{ route('auth.otp.resend') }}" class="mt-4 text-center">
                @csrf
                <button type="submit" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 transition-colors">Kirim ulang OTP</button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-6 pt-6 border-t border-slate-800 text-center">
                @csrf
                <button type="submit" class="text-xs text-slate-400 hover:text-white transition-colors">Keluar dari akun</button>
            </form>
        </div>
    </div>
@endsection
