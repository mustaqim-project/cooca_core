@extends('layouts.app', [
    'title' => 'Blast Promosi WhatsApp — ' . $business->name,
    'headerTitle' => 'Blast Promosi WhatsApp',
    'headerSubtitle' => 'Kirim promosi massal & notifikasi spesial ke pelanggan terdaftar'
])

@section('content')
<div class="space-y-6">

    <!-- Module Navigation Sub-Tabs & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-1.5 p-1 rounded-2xl bg-slate-100 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800/80 w-full sm:w-auto overflow-x-auto text-xs font-bold">
            <a href="{{ route('whatsapp.index') }}"
                class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                <i data-lucide="smartphone" class="w-4 h-4"></i>
                <span>Koneksi Gateway</span>
            </a>
            <a href="{{ route('whatsapp.broadcast.index') }}"
                class="px-4 py-2 rounded-xl bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-xs border border-slate-200/60 dark:border-slate-800 flex items-center gap-2 whitespace-nowrap">
                <i data-lucide="megaphone" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                <span>Blast Promosi</span>
            </a>
            <a href="{{ route('whatsapp.logs.index') }}"
                class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                <i data-lucide="history" class="w-4 h-4"></i>
                <span>Log Pesan</span>
            </a>
        </div>

        <a href="{{ route('whatsapp.broadcast.create') }}"
            class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs shadow-md shadow-emerald-500/20 flex items-center justify-center gap-2 transition-all active:scale-98">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Buat Blast Promosi Baru</span>
        </a>
    </div>

    <!-- STATS KPI GRID (4 METRICS) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-xs transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Kampanye</span>
                <div class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                {{ number_format($stats['total_campaigns']) }}
            </div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Riwayat broadcast</div>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-xs transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pesan Terkirim</span>
                <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="send" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400">
                {{ number_format($stats['total_sent']) }}
            </div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Berhasil masuk ke WA</div>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-xs transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Target</span>
                <div class="p-2 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                {{ number_format($stats['total_recipients']) }}
            </div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Penerima ditargetkan</div>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-xs transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Keberhasilan</span>
                <div class="p-2 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-teal-600 dark:text-teal-400">
                {{ $stats['success_rate'] }}%
            </div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Tingkat delivery sukses</div>
        </div>
    </div>

    <!-- CAMPAIGNS DATA TABLE -->
    <div class="bg-white dark:bg-slate-900/90 rounded-3xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs overflow-hidden transition-colors">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Riwayat Kampanye Broadcast</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Daftar semua pesan blast yang pernah dijadwalkan dan dikirim</p>
            </div>
        </div>

        @if($campaigns->isEmpty())
            <div class="text-center py-16 px-4 space-y-3">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center mx-auto border border-slate-200 dark:border-slate-700">
                    <i data-lucide="megaphone-off" class="w-7 h-7"></i>
                </div>
                <div>
                    <h3 class="text-slate-900 dark:text-white font-bold text-sm">Belum Ada Kampanye Blast Promosi</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 max-w-sm mx-auto">
                        Buat pesan promosi massal pertama Anda untuk mengabarkan diskon atau info menu terbaru ke pelanggan setia.
                    </p>
                </div>
                <a href="{{ route('whatsapp.broadcast.create') }}"
                    class="inline-flex items-center gap-2 mt-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all active:scale-98">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Buat Blast Pertama Sekarang</span>
                </a>
            </div>
        @else
            <div class="table-responsive overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[640px]">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 uppercase tracking-wider text-[11px]">
                            <th class="px-6 py-3.5 font-semibold">Judul Kampanye</th>
                            <th class="px-4 py-3.5 font-semibold">Target Audiens</th>
                            <th class="px-4 py-3.5 font-semibold text-center">Penerima</th>
                            <th class="px-4 py-3.5 font-semibold text-center">Terkirim</th>
                            <th class="px-4 py-3.5 font-semibold text-center">Status</th>
                            <th class="px-4 py-3.5 font-semibold">Waktu Kirim</th>
                            <th class="px-6 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @foreach($campaigns as $campaign)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="px-6 py-3.5">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $campaign->title }}</div>
                                    <div class="text-slate-500 dark:text-slate-400 truncate max-w-xs mt-0.5 text-[11px]">
                                        {{ Str::limit($campaign->message, 55) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        {{ $campaign->target_filter === 'all' 
                                            ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' 
                                            : 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20' }}">
                                        {{ $campaign->target_filter === 'all' ? 'Semua Pelanggan' : ucfirst($campaign->target_filter) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center font-mono font-semibold text-slate-700 dark:text-slate-300">
                                    {{ number_format($campaign->total_recipients) }}
                                </td>
                                <td class="px-4 py-3.5 text-center font-mono">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ number_format($campaign->total_sent) }}</span>
                                    @if($campaign->total_failed > 0)
                                        <span class="text-rose-600 dark:text-rose-400 font-semibold text-[11px]"> ({{ $campaign->total_failed }} gagal)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @php
                                        $statusBadge = match($campaign->status) {
                                            'completed'  => ['bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/20', 'Selesai'],
                                            'processing' => ['bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/20', 'Memproses'],
                                            'failed'     => ['bg-rose-500/10 text-rose-700 dark:text-rose-300 border-rose-500/20', 'Gagal'],
                                            default      => ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700', 'Draft'],
                                        };
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge[0] }}">
                                        {{ $statusBadge[1] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 text-[11px] font-mono">
                                    {{ $campaign->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    <a href="{{ route('whatsapp.broadcast.show', $campaign) }}"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline transition-colors">
                                        <span>Detail</span>
                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($campaigns->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $campaigns->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
