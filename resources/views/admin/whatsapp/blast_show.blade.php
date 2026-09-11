@extends('layouts.admin')

@section('title', 'Detail Blast: ' . $blast->title)

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.whatsapp.index') }}?tab=blast" class="h-9 px-2.5 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition inline-flex items-center justify-center"><i data-lucide="arrow-left" class="w-4 h-4" stroke-width="1.5"></i></a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-[22px] font-bold text-black dark:text-white tracking-tight">{{ $blast->title }}</h1>
                    @if($blast->status === 'completed')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]"><span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>{{ ucfirst($blast->status) }}</span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]"><span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>{{ ucfirst($blast->status) }}</span>
                    @endif
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Dikirim pada {{ $blast->created_at->format('d M Y, H:i') }} WIB • Target: {{ str_replace('_', ' ', ucfirst($blast->target_filter)) }}</p>
            </div>
        </div>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 text-center"><div class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ number_format($blast->total_recipients) }}</div><div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Total Bisnis Owner</div></div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 text-center"><div class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ number_format($blast->total_sent) }}</div><div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Berhasil Terkirim</div></div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 text-center"><div class="text-[24px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">{{ number_format($blast->total_failed) }}</div><div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Gagal</div></div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 text-center">
            @php $rate = $blast->total_recipients > 0 ? round(($blast->total_sent / $blast->total_recipients) * 100, 1) : 0; @endphp
            <div class="text-[24px] font-bold tabular-nums text-[#30B0C7] dark:text-[#40C8E0]">{{ $rate }}%</div>
            <div class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Tingkat Keberhasilan</div>
        </div>
    </div>

    {{-- MESSAGE PREVIEW --}}
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-3">
        <h2 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2"><i data-lucide="message-square" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i>Isi Pesan Broadcast yang Dikirim</h2>
        @if($blast->media_url)
            <div class="p-2 bg-black/[0.03] dark:bg-white/[0.05] rounded-[10px] border border-black/5 dark:border-white/10 max-w-xs"><img src="{{ $blast->media_url }}" alt="Banner" class="w-full h-auto rounded-[8px] object-cover"></div>
        @endif
        <div class="p-4 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] text-[13px] text-black/70 dark:text-white/70 whitespace-pre-wrap border border-black/5 dark:border-white/10">{{ $blast->message }}</div>
    </div>

    {{-- RECIPIENT TABLE --}}
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <h2 class="text-[15px] font-semibold text-black dark:text-white">Status Penerima Per Bisnis Owner</h2>
            <span class="text-[12px] text-black/50 dark:text-white/50 tabular-nums">Halaman {{ $recipients->currentPage() }} dari {{ $recipients->lastPage() }}</span>
        </div>
        @if($recipients->isEmpty())
            <div class="p-8 text-center text-[13px] text-black/45 dark:text-white/45">Belum ada data penerima.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40">
                            <th class="text-left px-5 py-2.5 text-[11px] font-semibold">Bisnis</th>
                            <th class="text-left px-3 py-2.5 text-[11px] font-semibold">Owner</th>
                            <th class="text-left px-3 py-2.5 text-[11px] font-semibold">No. WhatsApp</th>
                            <th class="text-center px-3 py-2.5 text-[11px] font-semibold">Status</th>
                            <th class="text-left px-3 py-2.5 text-[11px] font-semibold">Waktu Terkirim</th>
                            <th class="text-left px-3 py-2.5 text-[11px] font-semibold">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @foreach($recipients as $item)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-5 py-3 font-medium text-black dark:text-white">{{ $item->business_name }}</td>
                                <td class="px-3 py-3 text-black/70 dark:text-white/70">{{ $item->owner_name }}</td>
                                <td class="px-3 py-3 tabular-nums text-black/60 dark:text-white/60">{{ $item->phone_number }}</td>
                                <td class="px-3 py-3 text-center">
                                    @if($item->status === 'sent')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]"><span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>Terkirim</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]"><span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>Gagal</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-black/50 dark:text-white/50 tabular-nums">{{ $item->sent_at ? $item->sent_at->format('d/m/Y H:i:s') : '-' }}</td>
                                <td class="px-3 py-3 text-black/45 dark:text-white/45">{{ $item->error_message ?: 'Terkirim sukses via WhatsApp Bot' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($recipients->hasPages())
                <div class="px-4 py-3 border-t border-black/5 dark:border-white/10">{{ $recipients->links() }}</div>
            @endif
        @endif
    </div>
</div>
@endsection