@extends('layouts.admin', [
    'title' => 'Profil & Ganti Kata Sandi Administrator - Admin Console',
    'headerTitle' => 'Profil & Keamanan Akun Admin',
    'headerSubtitle' => 'Kelola identitas akun superadministrator dan perbarui kata sandi akses sistem',
])

@section('content')
<div class="max-w-5xl space-y-6" x-data="{
    showCurrent: false,
    showNew: false,
    showConfirm: false,
}">

    <!-- Bento Hero Card: Superadmin Identity Overview -->
    <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4 min-w-0">
                <div class="w-14 h-14 rounded-[18px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6] flex items-center justify-center text-white text-[20px] font-extrabold shadow-md shadow-[#007AFF]/25 shrink-0">
                    {{ strtoupper(substr($admin->name ?? 'A', 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white truncate">{{ $admin->name }}</h2>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                            <span>{{ strtoupper($admin->role ?? 'SUPER_ADMIN') }}</span>
                        </span>
                    </div>
                    <div class="text-[13px] text-black/55 dark:text-white/55 mt-0.5 flex items-center gap-2 flex-wrap">
                        <span>{{ $admin->email }}</span>
                        <span>•</span>
                        <span>Sesi Aktif Konsol</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                <div class="px-3.5 py-2 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] text-right">
                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Status Keamanan</div>
                    <div class="text-[12px] font-bold text-[#34C759] dark:text-[#30D158] flex items-center gap-1.5 justify-end mt-0.5">
                        <i data-lucide="shield-check" class="w-4 h-4" stroke-width="2"></i>
                        <span>Terlindungi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2-Column Bento Grid: Edit Profile & Change Password -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Form Update Profil Admin -->
        <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm flex flex-col justify-between space-y-5">
            <div>
                <div class="flex items-center gap-3 pb-4 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="w-10 h-10 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                        <i data-lucide="user" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Profil Administrator</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Perbarui nama dan email login admin</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4 pt-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Nama Lengkap <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $admin->name) }}" required
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Alamat Email <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $admin->email) }}" required
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Role Administrator
                        </label>
                        <input type="text" value="{{ ucfirst(str_replace('_', ' ', $admin->role)) }}" disabled
                            class="w-full h-11 px-4 bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.04] rounded-[12px] text-[16px] sm:text-[14px] text-black/50 dark:text-white/50 font-semibold cursor-not-allowed">
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                            class="w-full sm:w-auto h-12 sm:h-10 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[13px] font-bold transition-all flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25 cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4" stroke-width="2"></i>
                            <span>Simpan Profil</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] text-[11px] text-black/45 dark:text-white/45 flex items-center gap-1.5">
                <i data-lucide="info" class="w-3.5 h-3.5 text-[#007AFF] shrink-0" stroke-width="2"></i>
                <span>Email digunakan untuk menerima reset password &amp; notifikasi sistem</span>
            </div>
        </div>

        <!-- Form Ganti Password Admin -->
        <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm flex flex-col justify-between space-y-5">
            <div>
                <div class="flex items-center gap-3 pb-4 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="w-10 h-10 rounded-[14px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center shrink-0">
                        <i data-lucide="key-round" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Ganti Kata Sandi</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Perbarui kata sandi untuk keamanan akses admin</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-4 pt-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75">
                                Kata Sandi Saat Ini <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                            </label>
                            <button type="button" @click="showCurrent = !showCurrent"
                                class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline cursor-pointer">
                                <span x-text="showCurrent ? 'Sembunyikan' : 'Lihat'"></span>
                            </button>
                        </div>
                        <input :type="showCurrent ? 'text' : 'password'" name="current_password" required placeholder="••••••••"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] font-mono text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75">
                                Kata Sandi Baru <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                            </label>
                            <button type="button" @click="showNew = !showNew"
                                class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline cursor-pointer">
                                <span x-text="showNew ? 'Sembunyikan' : 'Lihat'"></span>
                            </button>
                        </div>
                        <input :type="showNew ? 'text' : 'password'" name="password" required placeholder="Minimal 8 karakter"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] font-mono text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75">
                                Konfirmasi Kata Sandi Baru <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                            </label>
                            <button type="button" @click="showConfirm = !showConfirm"
                                class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline cursor-pointer">
                                <span x-text="showConfirm ? 'Sembunyikan' : 'Lihat'"></span>
                            </button>
                        </div>
                        <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required placeholder="Ulangi kata sandi baru"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] font-mono text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                            class="w-full sm:w-auto h-12 sm:h-10 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[13px] font-bold transition-all flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25 cursor-pointer">
                            <i data-lucide="shield-check" class="w-4 h-4" stroke-width="2"></i>
                            <span>Ubah Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] text-[11px] text-black/45 dark:text-white/45 flex items-center gap-1.5">
                <i data-lucide="lock" class="w-3.5 h-3.5 text-[#34C759] shrink-0" stroke-width="2"></i>
                <span>Gunakan kombinasi huruf, angka, dan simbol untuk keamanan maksimal</span>
            </div>
        </div>

    </div>

    <!-- Reassuring Microcopy Banner -->
    <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.04] text-[12px] text-black/55 dark:text-white/55 flex items-center gap-2.5">
        <i data-lucide="sparkles" class="w-4 h-4 text-[#007AFF] shrink-0" stroke-width="2"></i>
        <span>💡 Tenang: Riwayat audit trail dan hak akses administrator Anda tetap terlindungi saat memperbarui informasi profil.</span>
    </div>

</div>
@endsection
