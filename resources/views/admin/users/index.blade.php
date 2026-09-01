@extends('layouts.admin', [
    'title' => 'Kelola Pengguna — Admin Console',
    'headerTitle' => 'Manajemen Pengguna & Ekspor Data',
    'headerSubtitle' => 'Kelola seluruh pengguna terdaftar, pantau metode login, dan unduh data pengguna ke format CSV'
])

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="glass-card p-5 rounded-2xl flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        
        <!-- Search Input -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex-1 max-w-md">
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-3"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email pengguna..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>
        </form>

        <!-- Export CSV Button -->
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.export') }}" 
               class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition-all">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Ekspor CSV (Email & Nama)</span>
            </a>
        </div>
    </div>

    <!-- Users Table -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                        <th class="py-3.5 px-4 font-semibold">Nama Pengguna</th>
                        <th class="py-3.5 px-4 font-semibold">Alamat Email</th>
                        <th class="py-3.5 px-4 font-semibold">Bisnis Aktif</th>
                        <th class="py-3.5 px-4 font-semibold">Metode Login</th>
                        <th class="py-3.5 px-4 font-semibold">Tanggal Registrasi</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-white">
                            <div class="flex items-center gap-2.5">
                                @if($user->avatar)
                                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-7 h-7 rounded-full object-cover border border-slate-700">
                                @else
                                    <div class="w-7 h-7 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-[10px] text-slate-300">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                                <span>{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-slate-300 font-mono">
                            {{ $user->email }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-300">
                            @if($user->activeBusiness)
                                <span class="font-semibold text-white">{{ $user->activeBusiness->name }}</span>
                            @else
                                <span class="text-slate-500 italic">Belum Ada</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($user->google_id)
                                <span class="px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 font-semibold text-[10px] flex items-center gap-1 w-fit">
                                    <i data-lucide="chrome" class="w-3 h-3"></i>
                                    <span>Google</span>
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 font-semibold text-[10px] flex items-center gap-1 w-fit">
                                    <i data-lucide="key-round" class="w-3 h-3"></i>
                                    <span>Password</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-400 font-mono">
                            {{ $user->created_at?->format('d M Y, H:i') }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ $user->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors" title="Hapus User">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">
                            Tidak ditemukan data pengguna yang sesuai.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
