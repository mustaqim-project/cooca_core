@extends('layouts.admin', [
    'title' => 'Kelola Pengguna — Admin Console',
    'headerTitle' => 'Manajemen Pengguna & Ekspor Data',
    'headerSubtitle' => 'Kelola seluruh pengguna terdaftar, pantau metode login, dan unduh data pengguna ke format CSV'
])

@section('content')
<div class="space-y-6">

    <!-- Toolbar: Search + Export -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex-1 max-w-md">
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2" stroke-width="1.5"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email pengguna..."
                       class="w-full h-9 pl-9 pr-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>
        </form>

        <a href="{{ route('admin.users.export') }}"
           class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shrink-0">
            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i>
            <span>Ekspor CSV (Email & Nama)</span>
        </a>
    </div>

    <!-- Users Table -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Nama Pengguna</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Alamat Email</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Bisnis Aktif</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Metode Login</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Tanggal Registrasi</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($users as $user)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3 font-medium text-black dark:text-white">
                            <div class="flex items-center gap-2.5">
                                @if($user->avatar)
                                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-7 h-7 rounded-full object-cover">
                                @else
                                    <div class="w-7 h-7 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center font-semibold text-[11px] shrink-0">{{ substr($user->name, 0, 1) }}</div>
                                @endif
                                <span>{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60">
                            @if($user->activeBusiness)
                                <span class="font-medium text-black dark:text-white">{{ $user->activeBusiness->name }}</span>
                            @else
                                <span class="text-black/40 dark:text-white/40 italic">Belum Ada</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($user->google_id)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]"><i data-lucide="chrome" class="w-3 h-3" stroke-width="1.5"></i><span>Google</span></span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55"><i data-lucide="key-round" class="w-3 h-3" stroke-width="1.5"></i><span>Password</span></span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-[12px] text-black/50 dark:text-white/50 tabular-nums">{{ $user->created_at?->format('d M Y, H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return AppAlert.confirmSubmit(event, this, 'Apakah Anda yakin ingin menghapus pengguna {{ addslashes($user->name) }}?', 'Hapus Pengguna?', 'danger')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 active:scale-[0.97] active:opacity-80 transition-all inline-flex items-center gap-1" title="Hapus User">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5" stroke-width="1.5"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center">
                            <i data-lucide="users" class="w-12 h-12 mx-auto text-black/20 dark:text-white/20" stroke-width="1.5"></i>
                            <p class="text-[15px] font-semibold text-black dark:text-white mt-3">Tidak ada data pengguna</p>
                            <p class="text-[13px] text-black/50 dark:text-white/50 mt-1">Tidak ditemukan data pengguna yang sesuai.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-4 py-3 border-t border-black/5 dark:border-white/10">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection