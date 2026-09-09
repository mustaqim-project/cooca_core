@extends('layouts.app', [
    'title' => 'Detail Blast: ' . $campaign->title . ' — ' . $business->name,
    'headerTitle' => 'Detail Kampanye Broadcast',
    'headerSubtitle' => 'Audit status pengiriman dan daftar penerima pesan promosi'
])

@section('content')
<div class="space-y-6">

    <!-- Top Navigation Bar & Action Hub -->
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3">
            <a href="{{ route('whatsapp.broadcast.index') }}"
                class="p-2.5 rounded-xl bg-white hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 transition-colors shadow-xs"
                title="Kembali ke Daftar Blast">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ $campaign->title }}</h1>
                    @php
                        $statusBadge = match($campaign->status) {
                            'completed'  => ['bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/20', 'Selesai'],
                            'processing' => ['bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/20', 'Memproses'],
                            'failed'     => ['bg-rose-500/10 text-rose-700 dark:text-rose-300 border-rose-500/20', 'Gagal'],
                            default      => ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700', 'Draft'],
                        };
                    @endphp
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusBadge[0] }}">
                        {{ $statusBadge[1] }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Dibuat pada {{ $campaign->created_at->format('d M Y, H:i') }} WIB &bull; Filter: <span class="capitalize font-semibold text-slate-700 dark:text-slate-300">{{ $campaign->target_filter }}</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('whatsapp.broadcast.create') }}"
                class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs shadow-md shadow-emerald-500/20 flex items-center gap-2 transition-all active:scale-98">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Buat Blast Baru</span>
            </a>
        </div>
    </div>

    <!-- STATS KPI GRID (4 METRICS) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-xs transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Target</span>
                <div class="p-2 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                {{ number_format($campaign->total_recipients) }}
            </div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Nomor tujuan terdaftar</div>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-xs transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Berhasil Terkirim</span>
                <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400">
                {{ number_format($campaign->total_sent) }}
            </div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Sukses terkirim via bot</div>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-xs transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gagal Terkirim</span>
                <div class="p-2 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-rose-600 dark:text-rose-400">
                {{ number_format($campaign->total_failed) }}
            </div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Nomor salah / session mati</div>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800/90 shadow-xs transition-colors">
            @php
                $rate = $campaign->total_recipients > 0 ? round(($campaign->total_sent / $campaign->total_recipients) * 100, 1) : 0;
            @endphp
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tingkat Sukses</span>
                <div class="p-2 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400">
                    <i data-lucide="percent" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-teal-600 dark:text-teal-400">
                {{ $rate }}%
            </div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Persentase delivery rate</div>
        </div>
    </div>

    <!-- MESSAGE CONTENT PREVIEW CARD -->
    <div class="bg-white dark:bg-slate-900/90 rounded-3xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-6 space-y-3 transition-colors">
        <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                <i data-lucide="message-square" class="w-4 h-4"></i>
            </div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Template Pesan Promosi Yang Dikirim</h2>
        </div>

        @if($campaign->media_url)
            <div class="p-2 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800 max-w-sm">
                <img src="{{ $campaign->media_url }}" alt="Banner Promosi" class="w-full h-auto rounded-xl object-cover">
            </div>
        @endif

        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 font-mono text-xs text-slate-800 dark:text-slate-200 whitespace-pre-wrap leading-relaxed">
{{ $campaign->message }}
        </div>
    </div>

    <!-- RECIPIENTS DELIVERY AUDIT TABLE -->
    <div class="bg-white dark:bg-slate-900/90 rounded-3xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs overflow-hidden transition-colors">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Audit Log Penerima Pesan</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Status pengiriman riil per kontak pelanggan</p>
            </div>
            <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">Halaman {{ $recipients->currentPage() }} dari {{ $recipients->lastPage() }}</span>
        </div>

        @if($recipients->isEmpty())
            <div class="text-center py-12 text-slate-400 dark:text-slate-500 text-xs">
                Belum ada data penerima yang tercatat dalam kampanye ini.
            </div>
        @else
            <div class="table-responsive overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[600px]">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 uppercase tracking-wider text-[11px]">
                            <th class="px-6 py-3.5 font-semibold">Nama Pelanggan</th>
                            <th class="px-4 py-3.5 font-semibold">Nomor WhatsApp</th>
                            <th class="px-4 py-3.5 font-semibold text-center">Status</th>
                            <th class="px-4 py-3.5 font-semibold">Waktu Pengiriman</th>
                            <th class="px-6 py-3.5 font-semibold">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-sans">
                        @foreach($recipients as $item)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="px-6 py-3.5 font-semibold text-slate-900 dark:text-white">
                                    {{ $item->customer_name ?? ($item->customer?->name ?? 'Pelanggan') }}
                                </td>
                                <td class="px-4 py-3.5 font-mono text-slate-700 dark:text-slate-300">
                                    {{ $item->phone_number }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($item->status === 'sent')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            <span>Terkirim</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-700 dark:text-rose-300 border border-rose-500/20">
                                            <i data-lucide="x" class="w-3 h-3"></i>
                                            <span>Gagal</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                                    {{ $item->sent_at ? $item->sent_at->format('d/m/Y H:i:s') : '-' }}
                                </td>
                                <td class="px-6 py-3.5 text-slate-600 dark:text-slate-300 text-[11px]">
                                    {{ $item->error_message ?: 'Terkirim sukses via WhatsApp bot' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($recipients->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $recipients->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
