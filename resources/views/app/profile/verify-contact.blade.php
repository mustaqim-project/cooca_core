@extends('layouts.app', ['title' => 'Verifikasi Perubahan Kontak', 'headerTitle' => 'Verifikasi Perubahan Kontak', 'headerSubtitle' => 'Masukkan OTP WhatsApp untuk menyelesaikan perubahan email dan nomor WhatsApp'])

@section('content')
<div class="max-w-[520px] mx-auto py-8">
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
        @if(session('status'))
            <div class="mb-5 rounded-[10px] bg-[#34C759]/10 border border-[#34C759]/20 p-3 text-[13px] text-[#248A3D]">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-5 rounded-[10px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 p-3 text-[13px] text-[#FF3B30]">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('profile.contact.verify.submit') }}" class="space-y-5">
            @csrf
            <div>
                <label for="otp" class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Kode OTP WhatsApp</label>
                <input id="otp" name="otp" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus class="w-full h-12 bg-black/[0.04] dark:bg-white/[0.06] rounded-[10px] px-3.5 text-center text-xl tracking-[0.35em] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]/50" placeholder="000000">
            </div>
            <button type="submit" class="w-full h-10 rounded-[10px] bg-[#34C759] hover:bg-[#248A3D] text-white text-[13px] font-semibold transition">Verifikasi Perubahan</button>
        </form>
        <form method="POST" action="{{ route('profile.contact.verify.resend') }}" class="mt-4 text-center">
            @csrf
            <button type="submit" class="text-[12px] font-semibold text-[#007AFF] hover:underline">Kirim ulang OTP</button>
        </form>
        <a href="{{ route('profile.edit') }}" class="block mt-4 text-center text-[12px] text-black/50 dark:text-white/50 hover:text-[#007AFF]">Kembali ke profil</a>
    </div>
</div>
@endsection