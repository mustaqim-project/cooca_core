@extends('layouts.admin', [
    'title' => 'Profil & Ganti Kata Sandi Administrator — Admin Console',
    'headerTitle' => 'Profil & Keamanan Akun Admin',
    'headerSubtitle' => 'Kelola informasi identitas administrator dan perbarui kata sandi akun Anda'
])

@section('content')
<div class="max-w-4xl space-y-6">

    <!-- Grid: Profil & Ganti Password -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Form Update Profil Admin -->
        <div class="glass-card rounded-3xl overflow-hidden p-6 space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-800 pb-4">
                <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <i data-lucide="shield" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Profil Administrator</h3>
                    <p class="text-xs text-slate-400">Perbarui nama dan email login admin</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Role Administrator</label>
                    <input type="text" value="{{ ucfirst($admin->role) }}" disabled
                           class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-400 uppercase font-mono font-bold">
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold flex items-center gap-1.5 shadow-lg shadow-indigo-500/20 transition-all">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Profil</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Form Ganti Password Admin -->
        <div class="glass-card rounded-3xl overflow-hidden p-6 space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-800 pb-4">
                <div class="w-10 h-10 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400">
                    <i data-lucide="key-round" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Ganti Kata Sandi</h3>
                    <p class="text-xs text-slate-400">Perbarui kata sandi untuk keamanan akses admin</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Kata Sandi Saat Ini</label>
                    <input type="password" name="current_password" required placeholder="••••••••"
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-cyan-500 rounded-xl text-white font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Kata Sandi Baru</label>
                    <input type="password" name="password" required placeholder="Minimal 8 karakter"
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-cyan-500 rounded-xl text-white font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" name="password_confirmation" required placeholder="Ulangi kata sandi baru"
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-cyan-500 rounded-xl text-white font-mono">
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold flex items-center gap-1.5 shadow-lg shadow-cyan-500/20 transition-all">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span>Ubah Kata Sandi</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
