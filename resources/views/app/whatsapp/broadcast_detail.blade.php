@extends('layouts.app')

@section('title', 'Detail Blast: ' . $campaign->title . ' — ' . $business->name)

@section('content')
<div class="p-4 sm:p-6 max-w-5xl mx-auto space-y-6">

    {{-- PAGE HEADER --}}
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-4">
            <a href="{{ route('whatsapp.broadcast.index') }}" class="p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-extrabold text-white">{{ $campaign->title }}</h1>
                    @php
                        $statusBadge = match($campaign->status) {
                            'completed'  => ['bg-emerald-500/15 text-emerald-400 border-emerald-500/30', '✓ Selesai'],
                            'processing' => ['bg-amber-500/15 text-amber-400 border-amber-500/30', '⏳ Memproses'],
                            'failed'     => ['bg-rose-500/15 text-rose-400 border-rose-500/30', '✗ Gagal'],
                            default      => ['bg-slate-700/50 text-slate-400 border-slate-700', 'Draft'],
                        };
                    @endphp
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusBadge[0] }}">
                        {{ $statusBadge[1] }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-1">Dibuat pada {{ $campaign->created_at->format('d M Y, H:i') }} WIB</p>
            </div>
        </div>
        <a href="{{ route('whatsapp.broadcast.create') }}"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#25D366] hover:bg-[#22c55e] text-white font-bold text-xs transition shadow-lg shadow-[#25D366]/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Blast Baru
        </a>
    </div>

    {{-- STATS GRID --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 text-center">
            <div class="text-2xl font-extrabold text-slate-300">{{ number_format($campaign->total_recipients) }}</div>
            <div class="text-xs text-slate-500 mt-0.5">Total Target</div>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 text-center">
            <div class="text-2xl font-extrabold text-emerald-400">{{ number_format($campaign->total_sent) }}</div>
            <div class="text-xs text-slate-500 mt-0.5">Berhasil Terkirim</div>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 text-center">
            <div class="text-2xl font-extrabold text-rose-400">{{ number_format($campaign->total_failed) }}</div>
            <div class="text-xs text-slate-500 mt-0.5">Gagal Terkirim</div>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 text-center">
            @php
                $rate = $campaign->total_recipients > 0 ? round(($campaign->total_sent / $campaign->total_recipients) * 100, 1) : 0;
            @endphp
            <div class="text-2xl font-extrabold text-[#25D366]">{{ $rate }}%</div>
            <div class="text-xs text-slate-500 mt-0.5">Tingkat Keberhasilan</div>
        </div>
    </div>

    {{-- MESSAGE CONTENT PREVIEW --}}
    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5 space-y-3">
        <h2 class="text-sm font-bold text-white flex items-center gap-2">
            <svg class="w-4 h-4 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413A11.824 11.824 0 0 0 12.05 0z"/></svg>
            Template Pesan yang Dikirim
        </h2>
        @if($campaign->media_url)
            <div class="p-2 bg-slate-950 rounded-xl border border-slate-800 max-w-xs">
                <img src="{{ $campaign->media_url }}" alt="Media" class="w-full h-auto rounded-lg object-cover">
            </div>
        @endif
        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 font-mono text-xs text-slate-300 whitespace-pre-wrap">
{{ $campaign->message }}
        </div>
    </div>

    {{-- RECIPIENTS STATUS TABLE --}}
    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
            <h2 class="text-sm font-bold text-white">Daftar Penerima Kampanye</h2>
            <span class="text-xs text-slate-500">Halaman {{ $recipients->currentPage() }} dari {{ $recipients->lastPage() }}</span>
        </div>

        @if($recipients->isEmpty())
            <div class="text-center py-12 text-slate-500 text-xs">
                Belum ada data penerima yang dicatat.
            </div>
        @else
            <div class="table-responsive">
                <table class="w-full text-xs min-w-[560px]">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-500 uppercase tracking-wide whitespace-nowrap">
                            <th class="text-left px-5 py-3 font-semibold">Pelanggan</th>
                            <th class="text-left px-3 py-3 font-semibold">Nomor WhatsApp</th>
                            <th class="text-center px-3 py-3 font-semibold">Status</th>
                            <th class="text-left px-3 py-3 font-semibold">Waktu Kirim</th>
                            <th class="text-left px-3 py-3 font-semibold">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($recipients as $item)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3.5 font-semibold text-white">
                                    {{ $item->customer_name ?? ($item->customer?->name ?? 'Pelanggan') }}
                                </td>
                                <td class="px-3 py-3.5 font-mono text-slate-300">
                                    {{ $item->phone_number }}
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    @if($item->status === 'sent')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Terkirim
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Gagal
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-slate-500">
                                    {{ $item->sent_at ? $item->sent_at->format('d/m/Y H:i:s') : '-' }}
                                </td>
                                <td class="px-3 py-3.5 text-slate-400">
                                    {{ $item->error_message ?: 'Terkirim sukses via WhatsApp bot' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($recipients->hasPages())
                <div class="px-5 py-4 border-t border-slate-800">
                    {{ $recipients->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
