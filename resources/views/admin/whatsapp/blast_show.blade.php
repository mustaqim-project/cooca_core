@extends('layouts.admin')

@section('title', 'Detail Blast: ' . $blast->title)

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.whatsapp.index') }}?tab=blast" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-extrabold text-white">{{ $blast->title }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $blast->status === 'completed' ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/15 text-amber-400 border-amber-500/30' }}">
                        {{ ucfirst($blast->status) }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">Dikirim pada {{ $blast->created_at->format('d M Y, H:i') }} WIB • Target: {{ str_replace('_', ' ', ucfirst($blast->target_filter)) }}</p>
            </div>
        </div>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="p-4 rounded-2xl glass-card text-center">
            <div class="text-2xl font-black text-white">{{ number_format($blast->total_recipients) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Total Bisnis Owner</div>
        </div>
        <div class="p-4 rounded-2xl glass-card text-center">
            <div class="text-2xl font-black text-emerald-400">{{ number_format($blast->total_sent) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Berhasil Terkirim</div>
        </div>
        <div class="p-4 rounded-2xl glass-card text-center">
            <div class="text-2xl font-black text-rose-400">{{ number_format($blast->total_failed) }}</div>
            <div class="text-xs text-slate-400 mt-0.5">Gagal</div>
        </div>
        <div class="p-4 rounded-2xl glass-card text-center">
            @php $rate = $blast->total_recipients > 0 ? round(($blast->total_sent / $blast->total_recipients) * 100, 1) : 0; @endphp
            <div class="text-2xl font-black text-[#25D366]">{{ $rate }}%</div>
            <div class="text-xs text-slate-400 mt-0.5">Tingkat Keberhasilan</div>
        </div>
    </div>

    {{-- MESSAGE PREVIEW --}}
    <div class="rounded-2xl glass-card p-5 border border-slate-800 space-y-3">
        <h2 class="text-sm font-bold text-white flex items-center gap-2">
            <i data-lucide="message-square" class="w-4 h-4 text-indigo-400"></i>
            Isi Pesan Broadcast yang Dikirim
        </h2>
        @if($blast->media_url)
            <div class="p-2 bg-slate-950 rounded-xl border border-slate-800 max-w-xs">
                <img src="{{ $blast->media_url }}" alt="Banner" class="w-full h-auto rounded-lg object-cover">
            </div>
        @endif
        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 font-mono text-xs text-slate-300 whitespace-pre-wrap">{{ $blast->message }}</div>
    </div>

    {{-- RECIPIENT TABLE --}}
    <div class="rounded-2xl glass-card overflow-hidden border border-slate-800">
        <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
            <h2 class="text-sm font-bold text-white">Status Penerima Per Bisnis Owner</h2>
            <span class="text-xs text-slate-500">Halaman {{ $recipients->currentPage() }} dari {{ $recipients->lastPage() }}</span>
        </div>

        @if($recipients->isEmpty())
            <div class="p-8 text-center text-xs text-slate-500">Belum ada data penerima.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-500 uppercase tracking-wide">
                            <th class="text-left px-5 py-3 font-semibold">Bisnis</th>
                            <th class="text-left px-3 py-3 font-semibold">Owner</th>
                            <th class="text-left px-3 py-3 font-semibold">No. WhatsApp</th>
                            <th class="text-center px-3 py-3 font-semibold">Status</th>
                            <th class="text-left px-3 py-3 font-semibold">Waktu Terkirim</th>
                            <th class="text-left px-3 py-3 font-semibold">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($recipients as $item)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3.5 font-bold text-white">{{ $item->business_name }}</td>
                                <td class="px-3 py-3.5 text-slate-300">{{ $item->owner_name }}</td>
                                <td class="px-3 py-3.5 font-mono text-slate-300">{{ $item->phone_number }}</td>
                                <td class="px-3 py-3.5 text-center">
                                    @if($item->status === 'sent')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Terkirim</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">Gagal</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-slate-400">{{ $item->sent_at ? $item->sent_at->format('d/m/Y H:i:s') : '-' }}</td>
                                <td class="px-3 py-3.5 text-slate-500">{{ $item->error_message ?: 'Terkirim sukses via WhatsApp Bot' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($recipients->hasPages())
                <div class="p-4 border-t border-slate-800">
                    {{ $recipients->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
