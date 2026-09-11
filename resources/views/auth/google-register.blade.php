@extends('layouts.guest', ['title' => 'Lengkapi Pendaftaran Google — Cooca UMKM'])

@section('content')
    <div class="sm:mx-auto sm:w-full sm:max-w-md px-4">
        <div class="text-center mb-8">
            <a href="{{ route('landing') }}" class="inline-flex items-center justify-center mb-4">
                <img src="https://cooca.id/assets/image/1785229034_logo_dark.png" alt="COOCA.ID" style="height: 38px; width: auto; object-fit: contain;">
            </a>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Lengkapi Pendaftaran</h2>
            <p class="mt-2 text-sm text-slate-400">Akun Google <strong class="text-slate-200">{{ $pending['email'] }}</strong> siap digunakan. Tambahkan WhatsApp owner untuk verifikasi.</p>
        </div>

        <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl shadow-black/60">
            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.google.submit') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="business_name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Nama Usaha / Perusahaan</label>
                    <input type="text" name="business_name" id="business_name" value="{{ old('business_name', 'Usaha ' . ($pending['name'] ?? 'Saya')) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl text-sm text-white transition-colors">
                </div>
                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Nomor WhatsApp Owner</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required inputmode="tel" autocomplete="tel"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl text-sm text-white placeholder-slate-500 transition-colors"
                        placeholder="081234567890">
                    <p class="mt-1 text-[11px] text-slate-500">Kode OTP akan dikirim melalui WhatsApp Admin Cooca.</p>
                </div>
                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-semibold text-sm shadow-lg shadow-emerald-500/25 transition-all">
                    Kirim OTP WhatsApp
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-800 text-center text-xs text-slate-400">
                <a href="{{ route('login') }}" class="font-semibold text-emerald-400 hover:text-emerald-300 transition-colors">Batalkan dan kembali ke login</a>
            </div>
        </div>
    </div>
@endsection
