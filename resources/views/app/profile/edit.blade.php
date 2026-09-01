@extends('layouts.app', [
    'title' => 'Profil & Ganti Kata Sandi',
    'headerTitle' => 'Pengaturan Akun & Keamanan',
    'headerSubtitle' => 'Kelola informasi profil pengguna dan perbarui kata sandi akun Anda'
])

@section('content')
<div class="max-w-4xl space-y-8">
    
    <!-- Grid: Profil & Ganti Password -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Form Update Profil -->
        <div class="glass-card rounded-2xl overflow-hidden p-6 space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-800 pb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <i data-lucide="user" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Profil Pengguna</h3>
                    <p class="text-xs text-slate-400">Ubah nama dan alamat email login Anda</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold flex items-center gap-1.5 shadow-lg shadow-emerald-500/20">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Profil</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Form Ganti Password -->
        <div class="glass-card rounded-2xl overflow-hidden p-6 space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-800 pb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Ganti Kata Sandi</h3>
                    <p class="text-xs text-slate-400">Perbarui kata sandi untuk mengamankan akun</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.password') }}" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Kata Sandi Saat Ini</label>
                    <input type="password" name="current_password" required placeholder="••••••••"
                           class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 focus:border-blue-500 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Kata Sandi Baru</label>
                    <input type="password" name="password" required placeholder="Minimal 8 karakter"
                           class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 focus:border-blue-500 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" name="password_confirmation" required placeholder="Ulangi kata sandi baru"
                           class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 focus:border-blue-500 rounded-xl text-white">
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold flex items-center gap-1.5 shadow-lg shadow-blue-500/20">
                        <i data-lucide="key" class="w-4 h-4"></i>
                        <span>Ubah Kata Sandi</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
