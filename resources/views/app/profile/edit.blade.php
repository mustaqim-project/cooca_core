@extends('layouts.app', [
    'title' => 'Profil & Ganti Kata Sandi',
    'headerTitle' => 'Pengaturan Akun & Keamanan',
    'headerSubtitle' => 'Kelola informasi profil pengguna dan perbarui kata sandi akun Anda'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Pengaturan</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Akun &amp; Profil</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Akun Pengguna &amp; Keamanan</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Kelola identitas akun Anda dan proteksi keamanan autentikasi</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70">
                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                <span>Aktif · {{ auth()->user()->role ?? 'User' }}</span>
            </span>
        </div>
    </header>

    <!-- Feedback Alerts (Apple HIG Banner Style) -->
    @if(session('success'))
    <div class="rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 p-4 flex items-center gap-3">
        <svg class="w-5 h-5 text-[#248A3D] dark:text-[#30D158] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-[13px] font-medium text-[#248A3D] dark:text-[#30D158]">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 p-4 flex items-center gap-3">
        <svg class="w-5 h-5 text-[#FF3B30] dark:text-[#FF453A] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
        </svg>
        <span class="text-[13px] font-medium text-[#FF3B30] dark:text-[#FF453A]">{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 p-4 space-y-1">
        <div class="flex items-center gap-2 text-[13px] font-semibold text-[#FF3B30] dark:text-[#FF453A]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <span>Terdapat beberapa kesalahan validasi:</span>
        </div>
        <ul class="list-disc list-inside text-[12px] text-[#FF3B30] dark:text-[#FF453A] pl-5 space-y-0.5">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 2. SETTINGS GRID: PROFIL & GANTI KATA SANDI           -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
        
        <!-- Form Update Profil -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 shadow-[0_1px_2px_rgba(0,0,0,0.04)] flex flex-col justify-between">
            <div class="space-y-4">
                <div class="flex items-center gap-3 border-b border-black/5 dark:border-white/10 pb-4">
                    <div class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-[16px] font-semibold text-black dark:text-white">Profil Pengguna</h2>
                        <p class="text-[12px] text-black/50 dark:text-white/50">Ubah nama identitas dan kontak akun Anda</p>
                    </div>
                </div>

                <form id="form-update-profile" method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Nama Lengkap <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Alamat Surel (Email) <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="email" value="{{ $user->email }}" disabled
                               class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black/50 dark:text-white/50 focus:outline-none transition">
                        @if($user->hasVerifiedEmail())
                            <p class="mt-1.5 text-[11px] text-[#248A3D]">Email terverifikasi</p>
                        @else
                            <p class="mt-1.5 text-[11px] text-[#FF9500]">Email belum terverifikasi</p>
                        @endif
                    </div>
                </form>
            </div>

            <div class="pt-4 border-t border-black/5 dark:border-white/10 flex justify-end">
                <button type="submit" form="form-update-profile"
                        class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)] flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span>Simpan Profil</span>
                </button>
            </div>
        </div>

        <!-- Form Ganti Password -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 space-y-5 shadow-[0_1px_2px_rgba(0,0,0,0.04)] flex flex-col justify-between">
            <div class="space-y-4">
                <div class="flex items-center gap-3 border-b border-black/5 dark:border-white/10 pb-4">
                    <div class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-[16px] font-semibold text-black dark:text-white">Ganti Kata Sandi</h2>
                        <p class="text-[12px] text-black/50 dark:text-white/50">Perbarui kata sandi berkala untuk mengamankan akun</p>
                    </div>
                </div>

                <form id="form-update-password" method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Kata Sandi Saat Ini <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="password" name="current_password" required placeholder="••••••••"
                               class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Kata Sandi Baru <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="password" name="password" required placeholder="Minimal 8 karakter"
                               class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">
                            Konfirmasi Kata Sandi Baru <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="password" name="password_confirmation" required placeholder="Ketik ulang kata sandi baru"
                               class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </form>
            </div>

            <div class="pt-4 border-t border-black/5 dark:border-white/10 flex justify-end">
                <button type="submit" form="form-update-password"
                        class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)] flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                    </svg>
                    <span>Ubah Kata Sandi</span>
                </button>
            </div>
        </div>

        @if(\App\Support\Context::isOwner())
        <div class="md:col-span-2 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-6 shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
            <div class="flex items-center gap-3 border-b border-black/5 dark:border-white/10 pb-4 mb-5">
                <div class="w-10 h-10 rounded-[10px] bg-[#34C759]/10 text-[#248A3D] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75A2.25 2.25 0 014.5 4.5h15a2.25 2.25 0 012.25 2.25v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75zM2.25 7.5l9.75 6 9.75-6" /></svg>
                </div>
                <div>
                    <h2 class="text-[16px] font-semibold text-black dark:text-white">Ganti Email &amp; Nomor WhatsApp</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Perubahan dikonfirmasi dengan OTP WhatsApp dan verifikasi email baru</p>
                </div>
            </div>
            <form method="POST" action="{{ route('profile.contact.request') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">Email Baru <span class="text-[#FF3B30]">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]/50 transition">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">Nomor WhatsApp Baru <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" inputmode="tel" required placeholder="081234567890" class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#34C759]/50 transition">
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#34C759] hover:bg-[#248A3D] active:scale-[0.97] transition-all flex items-center gap-1.5">
                        <span>Kirim OTP &amp; Minta Verifikasi</span>
                    </button>
                </div>
            </form>
        </div>
        @endif

    </div>
</div>
@endsection
