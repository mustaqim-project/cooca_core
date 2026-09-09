@extends('layouts.app')

@section('title', 'Log Pesan WhatsApp — ' . $business->name)

@section('content')
<div class="p-4 sm:p-6 max-w-5xl mx-auto space-y-6">

    {{-- PAGE HEADER --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('whatsapp.index') }}" class="p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div class="w-11 h-11 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-white">Log Pesan WhatsApp</h1>
            <p class="text-sm text-slate-400">Riwayat semua pesan yang dikirim dari sistem</p>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 overflow-hidden">
        @if($logs->isEmpty())
            <div class="text-center py-16 space-y-3">
                <div class="w-14 h-14 rounded-2xl bg-slate-800 flex items-center justify-center mx-auto">
                    <svg class="w-7 h-7 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <p class="text-slate-400 font-semibold text-sm">Belum ada log pesan</p>
                <p class="text-slate-600 text-xs">Log akan muncul setelah pesan pertama dikirim</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="w-full text-xs min-w-[560px]">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-500 uppercase tracking-wide whitespace-nowrap">
                            <th class="text-left px-5 py-3 font-semibold">Penerima</th>
                            <th class="text-left px-3 py-3 font-semibold">Tipe</th>
                            <th class="text-left px-3 py-3 font-semibold">Pesan</th>
                            <th class="text-center px-3 py-3 font-semibold">Status</th>
                            <th class="text-left px-3 py-3 font-semibold">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($logs as $log)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3">
                                    <div class="font-semibold text-white text-xs">{{ $log->recipient_name }}</div>
                                    <div class="text-slate-500 font-mono text-[10px]">{{ $log->recipient_phone }}</div>
                                </td>
                                <td class="px-3 py-3">
                                    @php
                                        $typeBadge = match($log->type) {
                                            'receipt'   => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            'broadcast' => 'bg-[#25D366]/10 text-[#25D366] border-[#25D366]/20',
                                            'test'      => 'bg-slate-700/50 text-slate-400 border-slate-700',
                                            default     => 'bg-slate-700/50 text-slate-400 border-slate-700',
                                        };
                                        $typeLabel = match($log->type) {
                                            'receipt'   => '🧾 Struk',
                                            'broadcast' => '📢 Blast',
                                            'test'      => '🔧 Tes',
                                            default     => ucfirst($log->type),
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $typeBadge }}">{{ $typeLabel }}</span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="text-slate-300 truncate max-w-xs" title="{{ $log->message }}">
                                        {{ Str::limit($log->message, 55) }}
                                    </div>
                                    @if($log->error_message)
                                        <div class="text-rose-400 text-[10px] mt-0.5 truncate">⚠ {{ $log->error_message }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($log->status === 'sent')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Terkirim
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Gagal
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-slate-500">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="px-5 py-4 border-t border-slate-800">
                    {{ $logs->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
