@extends('layouts.ai', ['title' => 'AI Work History — COOCA'])

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('ai.office') }}" class="text-xs text-black/50 hover:text-black dark:text-white/50 dark:hover:text-white flex items-center gap-1 transition">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    <span>Kembali ke AI Office</span>
                </a>
            </div>
            <h1 class="text-2xl font-bold text-black dark:text-white tracking-tight mt-1">AI Work History</h1>
            <p class="text-xs text-black/60 dark:text-white/60 mt-0.5">Rekam jejak historis seluruh sesi diagnosis, analisis operasional, dan usulan aksi yang dilakukan oleh tim digital.</p>
        </div>
    </div>

    <!-- History Table Container -->
    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm overflow-hidden">
        @if($histories->isEmpty())
            <div class="p-12 text-center space-y-2">
                <div class="w-10 h-10 rounded-xl bg-black/5 dark:bg-white/5 mx-auto flex items-center justify-center text-black/40 dark:text-white/40">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                </div>
                <div class="text-xs font-bold text-black dark:text-white">Belum ada riwayat sesi AI tercatat.</div>
                <p class="text-xs text-black/50 dark:text-white/50">Jalankan evaluasi harian atau lakukan konsultasi di AI Office untuk mulai mencatat riwayat pekerjaan digital.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-black/80 dark:text-white/80">
                    <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/10 dark:border-white/10 text-[10px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Waktu Sesi</th>
                            <th class="py-3 px-4">Judul / Topik Sesi</th>
                            <th class="py-3 px-4">Tim yang Terlibat</th>
                            <th class="py-3 px-4">Ringkasan Eksekutif</th>
                            <th class="py-3 px-4 text-center">Temuan</th>
                            <th class="py-3 px-4 text-center">Usulan Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @foreach($histories as $item)
                            <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition">
                                <td class="py-3.5 px-4 font-mono text-[11px] text-black/60 dark:text-white/60 whitespace-nowrap">
                                    {{ $item->recorded_at->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-black dark:text-white max-w-[200px] truncate">
                                    {{ $item->session_title }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-wrap gap-1 max-w-[220px]">
                                        @foreach($item->participating_agents as $ag)
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-mono bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 capitalize">{{ $ag }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 max-w-[320px] text-black/70 dark:text-white/70 line-clamp-2">
                                    {{ $item->executive_summary }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $item->insights_count }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-amber-600 dark:text-amber-400">
                                    {{ $item->actions_count }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-black/10 dark:border-white/10">
                {{ $histories->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
