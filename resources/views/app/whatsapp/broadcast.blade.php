@extends('layouts.app')

@section('title', 'Blast Promosi WhatsApp — ' . $business->name)

@section('content')
<div class="p-4 sm:p-6 max-w-5xl mx-auto space-y-6">

    {{-- PAGE HEADER --}}
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#25D366] to-[#128C7E] flex items-center justify-center shadow-lg shadow-emerald-500/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-white">Blast Promosi WhatsApp</h1>
                <p class="text-sm text-slate-400">Kirim pesan promosi massal ke pelanggan Anda</p>
            </div>
        </div>
        <a href="{{ route('whatsapp.broadcast.create') }}"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#25D366] hover:bg-[#22c55e] text-white font-bold text-sm transition shadow-lg shadow-[#25D366]/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Blast Baru
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- STATS GRID --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 text-center">
            <div class="text-2xl font-extrabold text-white">{{ number_format($stats['total_campaigns']) }}</div>
            <div class="text-xs text-slate-500 mt-0.5">Total Kampanye</div>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 text-center">
            <div class="text-2xl font-extrabold text-[#25D366]">{{ number_format($stats['total_sent']) }}</div>
            <div class="text-xs text-slate-500 mt-0.5">Pesan Terkirim</div>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 text-center">
            <div class="text-2xl font-extrabold text-slate-300">{{ number_format($stats['total_recipients']) }}</div>
            <div class="text-xs text-slate-500 mt-0.5">Total Penerima</div>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 text-center">
            <div class="text-2xl font-extrabold text-emerald-400">{{ $stats['success_rate'] }}%</div>
            <div class="text-xs text-slate-500 mt-0.5">Keberhasilan</div>
        </div>
    </div>

    {{-- CAMPAIGNS TABLE --}}
    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-800">
            <h2 class="text-sm font-bold text-white">Riwayat Kampanye</h2>
        </div>

        @if($campaigns->isEmpty())
            <div class="text-center py-16 space-y-3">
                <div class="w-14 h-14 rounded-2xl bg-slate-800 flex items-center justify-center mx-auto">
                    <svg class="w-7 h-7 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                </div>
                <p class="text-slate-400 font-semibold text-sm">Belum ada blast promosi</p>
                <p class="text-slate-600 text-xs">Buat kampanye pertama Anda untuk mulai menjangkau pelanggan</p>
                <a href="{{ route('whatsapp.broadcast.create') }}"
                    class="inline-flex items-center gap-2 mt-3 px-5 py-2.5 rounded-xl bg-[#25D366]/15 hover:bg-[#25D366]/25 text-[#25D366] font-bold text-xs border border-[#25D366]/30 transition">
                    Buat Blast Pertama
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="w-full text-xs min-w-[620px]">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-500 uppercase tracking-wide whitespace-nowrap">
                            <th class="text-left px-5 py-3 font-semibold">Judul Kampanye</th>
                            <th class="text-left px-3 py-3 font-semibold">Target</th>
                            <th class="text-center px-3 py-3 font-semibold">Penerima</th>
                            <th class="text-center px-3 py-3 font-semibold">Terkirim</th>
                            <th class="text-center px-3 py-3 font-semibold">Status</th>
                            <th class="text-left px-3 py-3 font-semibold">Tanggal</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($campaigns as $campaign)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3.5">
                                    <div class="font-semibold text-white">{{ $campaign->title }}</div>
                                    <div class="text-slate-500 truncate max-w-xs mt-0.5">{{ Str::limit($campaign->message, 60) }}</div>
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        {{ $campaign->target_filter === 'all' ? 'bg-slate-700 text-slate-300' : 'bg-[#25D366]/10 text-[#25D366] border border-[#25D366]/20' }}">
                                        {{ $campaign->target_filter === 'all' ? 'Semua' : ucfirst($campaign->target_filter) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 text-center text-slate-300 font-semibold">{{ number_format($campaign->total_recipients) }}</td>
                                <td class="px-3 py-3.5 text-center">
                                    <span class="text-emerald-400 font-bold">{{ number_format($campaign->total_sent) }}</span>
                                    @if($campaign->total_failed > 0)
                                        <span class="text-rose-400 font-semibold"> / {{ $campaign->total_failed }} ❌</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    @php
                                        $statusBadge = match($campaign->status) {
                                            'completed'  => ['bg-emerald-500/15 text-emerald-400 border-emerald-500/30', '✓ Selesai'],
                                            'processing' => ['bg-amber-500/15 text-amber-400 border-amber-500/30', '⏳ Proses'],
                                            'failed'     => ['bg-rose-500/15 text-rose-400 border-rose-500/30', '✗ Gagal'],
                                            default      => ['bg-slate-700/50 text-slate-400 border-slate-700', 'Draft'],
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge[0] }}">
                                        {{ $statusBadge[1] }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 text-slate-500">{{ $campaign->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('whatsapp.broadcast.show', $campaign) }}"
                                        class="text-xs font-semibold text-slate-400 hover:text-white transition">Detail →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($campaigns->hasPages())
                <div class="px-5 py-4 border-t border-slate-800">
                    {{ $campaigns->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
