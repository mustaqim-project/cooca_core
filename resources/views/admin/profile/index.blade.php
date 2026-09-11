@extends('layouts.admin', [
    'title' => 'Profil & Ganti Kata Sandi Administrator — Admin Console',
    'headerTitle' => 'Profil & Keamanan Akun Admin',
    'headerSubtitle' => 'Kelola informasi identitas administrator dan perbarui kata sandi akun Anda'
])

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Form Update Profil Admin -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="px-5 py-4 border-b border-black/5 dark:border-white/10 flex items-center gap-3">
                <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF]"><i data-lucide="shield" class="w-4.5 h-4.5" stroke-width="1.5"></i></div>
                <div>
                    <h3 class="text-[15px] font-semibold text-black dark:text-white">Profil Administrator</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Perbarui nama dan email login admin</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.profile.update') }}" class="p-5 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}" required class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" required class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Role Administrator</label>
                    <input type="text" value="{{ ucfirst($admin->role) }}" disabled class="w-full h-11 px-3.5 bg-black/[0.03] dark:bg-white/[0.04] border-none rounded-[10px] text-[15px] text-black/50 dark:text-white/50 uppercase font-semibold cursor-not-allowed">
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 text-white text-[13px] font-semibold transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]"><i data-lucide="save" class="w-4 h-4" stroke-width="1.5"></i><span>Simpan Profil</span></button>
                </div>
            </form>
        </div>

        <!-- Form Ganti Password Admin -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="px-5 py-4 border-b border-black/5 dark:border-white/10 flex items-center gap-3">
                <div class="w-9 h-9 rounded-[10px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6] dark:text-[#5E5CE6]"><i data-lucide="key-round" class="w-4.5 h-4.5" stroke-width="1.5"></i></div>
                <div>
                    <h3 class="text-[15px] font-semibold text-black dark:text-white">Ganti Kata Sandi</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Perbarui kata sandi untuk keamanan akses admin</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.profile.password') }}" class="p-5 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Kata Sandi Saat Ini</label>
                    <input type="password" name="current_password" required placeholder="••••••••" class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Kata Sandi Baru</label>
                    <input type="password" name="password" required placeholder="Minimal 8 karakter" class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" name="password_confirmation" required placeholder="Ulangi kata sandi baru" class="w-full h-11 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 text-white text-[13px] font-semibold transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]"><i data-lucide="shield-check" class="w-4 h-4" stroke-width="1.5"></i><span>Ubah Kata Sandi</span></button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection