@extends('layouts.admin')

@section('title', 'Detail Broadcast: ' . $blast->title)

@section('content')
@php
    $rate = $blast->total_recipients > 0 ? round(($blast->total_sent / $blast->total_recipients) * 100, 1) : 0;
@endphp

<div class="space-y-5 sm:space-y-6 max-w-7xl w-full min-w-0 mx-auto pb-28 lg:pb-10" x-data="{ searchQuery: '', statusFilter: 'all' }">

    {{-- 1. HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 sm:gap-4 min-w-0">
        <div class="flex items-center gap-3 sm:gap-3.5 min-w-0 flex-1">
            <a href="{{ route('admin.whatsapp.index') }}?tab=blast"
                class="w-11 h-11 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition-all inline-flex items-center justify-center shrink-0 active:scale-[0.98]">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 mb-0.5">Detail Broadcast</div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight truncate">{{ $blast->title }}</h1>
                    @if ($blast->status === 'completed')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25 shrink-0">
                            <span>Selesai Dikirim</span>
                        </span>
                    @elseif ($blast->status === 'processing')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A] border border-[#FF9500]/25 shrink-0">
                            <span>Sedang Memproses</span>
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A] border border-[#FF3B30]/25 shrink-0">
                            <span>Gagal</span>
                        </span>
                    @endif
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-1 truncate">
                    Dikirim pada {{ optional($blast->created_at)->format('d M Y, H:i') }} WIB • Sasaran: <span class="font-medium text-black/75 dark:text-white/75">{{ str_replace('_', ' ', ucfirst($blast->target_filter)) }}</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.whatsapp.index') }}?tab=blast"
                class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold bg-black/[0.05] dark:bg-white/[0.08] text-black dark:text-white hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition-all inline-flex items-center gap-2 active:scale-[0.98]">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    {{-- 2. BENTO STATS TILES (4 TILES - APPLE HIG STYLE) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 w-full min-w-0">
        {{-- Tile 1: Total Sasaran --}}
        <div class="rounded-[18px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-2 min-w-0">
                <span class="text-[11px] sm:text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider truncate">Total Sasaran Bisnis</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[9px] sm:rounded-[10px] bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 flex items-center justify-center shrink-0">
                    <i data-lucide="users" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                </div>
            </div>
            <div class="mt-2.5 min-w-0">
                <div class="text-[18px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight tabular-nums truncate">
                    {{ number_format($blast->total_recipients) }}
                </div>
                <p class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 mt-0.5 truncate">Bisnis ditargetkan</p>
            </div>
        </div>

        {{-- Tile 2: Berhasil Terkirim --}}
        <div class="rounded-[18px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-2 min-w-0">
                <span class="text-[11px] sm:text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider truncate">Berhasil Terkirim</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[9px] sm:rounded-[10px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center shrink-0">
                    <i data-lucide="check-check" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                </div>
            </div>
            <div class="mt-2.5 min-w-0">
                <div class="text-[18px] sm:text-[24px] font-bold text-[#248A3D] dark:text-[#30D158] tracking-tight tabular-nums truncate">
                    {{ number_format($blast->total_sent) }}
                </div>
                <p class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 mt-0.5 truncate">Pesan sukses terkirim</p>
            </div>
        </div>

        {{-- Tile 3: Gagal Terkirim --}}
        <div class="rounded-[18px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-2 min-w-0">
                <span class="text-[11px] sm:text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider truncate">Gagal Terkirim</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[9px] sm:rounded-[10px] bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center shrink-0">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                </div>
            </div>
            <div class="mt-2.5 min-w-0">
                <div class="text-[18px] sm:text-[24px] font-bold text-[#FF3B30] dark:text-[#FF453A] tracking-tight tabular-nums truncate">
                    {{ number_format($blast->total_failed) }}
                </div>
                <p class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 mt-0.5 truncate">Pesan gagal / bouncing</p>
            </div>
        </div>

        {{-- Tile 4: Rasio Keterkiriman --}}
        <div class="rounded-[18px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-3.5 sm:p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-2 min-w-0">
                <span class="text-[11px] sm:text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider truncate">Rasio Keterkiriman</span>
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-[9px] sm:rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="activity" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                </div>
            </div>
            <div class="mt-2.5 min-w-0">
                <div class="text-[18px] sm:text-[24px] font-bold text-[#007AFF] dark:text-[#0A84FF] tracking-tight tabular-nums truncate">
                    {{ $rate }}%
                </div>
                <p class="text-[11px] sm:text-[11.5px] text-black/50 dark:text-white/50 mt-0.5 truncate">Tingkat sukses broadcast</p>
            </div>
        </div>
    </div>

    {{-- 3. CHAT BUBBLE PREVIEW & METADATA BENTO (2 KOLOM) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 w-full min-w-0">

        {{-- Mockup Balon Chat WhatsApp Asli (7 Kolom) --}}
        <div class="lg:col-span-7 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-4 sm:p-6 space-y-4 w-full min-w-0">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                <h2 class="text-[15px] font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="message-square" class="w-4 h-4 text-[#25D366]"></i>
                    <span>Tampilan Pesan WhatsApp (Chat Mockup)</span>
                </h2>
                <span class="text-[11px] font-medium text-black/40 dark:text-white/40">Pratinjau di HP Penerima</span>
            </div>

            {{-- WhatsApp Container Mockup --}}
            <div class="p-4 sm:p-6 rounded-[18px] bg-[#EFEAE2] dark:bg-[#0B141A] border border-black/[0.06] dark:border-white/[0.08] shadow-inner flex flex-col justify-end min-h-[220px]">
                <div class="max-w-md ml-auto bg-[#E7FFDB] dark:bg-[#005C4B] rounded-[16px] rounded-tr-[4px] p-3.5 shadow-sm text-black dark:text-white space-y-2.5">
                    @if ($blast->media_url)
                        <div class="rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10">
                            <img src="{{ $blast->media_url }}" alt="Media Banner" class="w-full h-auto max-h-56 object-cover block">
                        </div>
                    @endif

                    <div class="text-[13px] leading-relaxed whitespace-pre-wrap font-sans text-black/90 dark:text-white/95">
                        {{ $blast->message }}
                    </div>

                    <div class="flex items-center justify-end gap-1.5 text-[10px] text-black/50 dark:text-white/50 font-mono pt-1">
                        <span>{{ optional($blast->created_at)->format('H:i') }}</span>
                        <i data-lucide="check-check" class="w-3.5 h-3.5 text-[#53BDEB]"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ringkasan Parameter Broadcast (5 Kolom) --}}
        <div class="lg:col-span-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-4 sm:p-6 space-y-4 flex flex-col justify-between w-full min-w-0">
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white mb-3 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Informasi Teknis Siaran</span>
                </h3>

                <dl class="space-y-3 text-[13px]">
                    <div class="flex items-center justify-between py-2 border-b border-black/[0.04] dark:border-white/[0.06]">
                        <dt class="text-black/50 dark:text-white/50">ID Broadcast</dt>
                        <dd class="font-mono font-semibold text-black dark:text-white">#{{ $blast->id }}</dd>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-black/[0.04] dark:border-white/[0.06]">
                        <dt class="text-black/50 dark:text-white/50">Target Segmen</dt>
                        <dd class="font-semibold text-black dark:text-white">{{ str_replace('_', ' ', ucfirst($blast->target_filter)) }}</dd>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-black/[0.04] dark:border-white/[0.06]">
                        <dt class="text-black/50 dark:text-white/50">Lampiran Media</dt>
                        <dd class="text-black dark:text-white">
                            @if ($blast->media_url)
                                <a href="{{ $blast->media_url }}" target="_blank" rel="noopener noreferrer" class="text-[#007AFF] hover:underline font-mono text-[12px] inline-flex items-center gap-1">
                                    <span>Lihat Gambar</span>
                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            @else
                                <span class="text-black/40 dark:text-white/40">Tanpa Media</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-black/[0.04] dark:border-white/[0.06]">
                        <dt class="text-black/50 dark:text-white/50">Admin Operator</dt>
                        <dd class="font-semibold text-black dark:text-white">{{ $blast->admin?->name ?? 'Sistem Otomatis' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] text-[12px] text-black/60 dark:text-white/60 flex items-start gap-2.5 mt-4">
                <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                <span class="leading-relaxed">Data audit pengiriman ini bersifat tetap dan tersimpan aman untuk kepatuhan log platform.</span>
            </div>
        </div>

    </div>

    {{-- 4. TABEL STATUS PENERIMA PER BISNIS OWNER --}}
    <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-black/[0.06] dark:border-white/[0.08] shadow-sm overflow-hidden w-full min-w-0">
        <div class="p-4 sm:p-6 border-b border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-3 min-w-0">
            <div>
                <h2 class="text-[16px] font-bold text-black dark:text-white">Daftar Status Pengiriman Per Bisnis Owner</h2>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Audit log status penerimaan pesan broadcast untuk setiap nomor WhatsApp terdaftar</p>
            </div>
            <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">
                Halaman {{ $recipients->currentPage() }} dari {{ $recipients->lastPage() }}
            </span>
        </div>

        @if ($recipients->isEmpty())
            <div class="p-10 sm:p-12 text-center text-[13px] text-black/45 dark:text-white/45">
                Belum ada data rekaman penerima untuk broadcast ini.
            </div>
        @else
            {{-- Mobile Card List View (< md) --}}
            <div class="block md:hidden p-3.5 space-y-3 divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                @foreach ($recipients as $item)
                    <div class="pt-3 first:pt-0 space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-bold text-[14px] text-black dark:text-white truncate">{{ $item->business_name }}</div>
                                <div class="text-[12px] text-black/50 dark:text-white/50 truncate">{{ $item->owner_name }}</div>
                            </div>
                            <div class="shrink-0">
                                @if ($item->status === 'sent')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                        <span>Terkirim</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                        <span>Gagal</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[12px] text-black/55 dark:text-white/55">
                            <span class="font-mono tabular-nums">{{ $item->phone_number }}</span>
                            <span class="tabular-nums">{{ $item->sent_at ? $item->sent_at->format('d M Y, H:i') : '-' }} WIB</span>
                        </div>

                        @if ($item->error_message)
                            <div class="text-[11.5px] text-black/60 dark:text-white/60 bg-black/[0.03] dark:bg-white/[0.04] p-2.5 rounded-[10px] break-words">
                                {{ $item->error_message }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Desktop Table View (>= md) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] text-black/45 dark:text-white/45 bg-black/[0.01] dark:bg-white/[0.02]">
                            <th class="text-left px-5 sm:px-6 py-3 font-semibold text-[11px] uppercase tracking-wider">Nama Bisnis &amp; Owner</th>
                            <th class="text-left px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">No. WhatsApp</th>
                            <th class="text-center px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">Status</th>
                            <th class="text-left px-4 py-3 font-semibold text-[11px] uppercase tracking-wider">Waktu Terkirim</th>
                            <th class="text-left px-5 sm:px-6 py-3 font-semibold text-[11px] uppercase tracking-wider">Keterangan / Pesan Gateway</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @foreach ($recipients as $item)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.025] transition-colors">
                                <td class="px-5 sm:px-6 py-3.5">
                                    <div class="font-bold text-black dark:text-white">{{ $item->business_name }}</div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50 font-normal">{{ $item->owner_name }}</div>
                                </td>
                                <td class="px-4 py-3.5 font-mono text-black/70 dark:text-white/70 tabular-nums">
                                    {{ $item->phone_number }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if ($item->status === 'sent')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                            <span>Terkirim</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                            <span>Gagal</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-black/55 dark:text-white/55 tabular-nums text-[12px]">
                                    {{ $item->sent_at ? $item->sent_at->format('d M Y, H:i:s') : '-' }} WIB
                                </td>
                                <td class="px-5 sm:px-6 py-3.5 text-black/60 dark:text-white/60 text-[12px]">
                                    {{ $item->error_message ?: 'Terkirim sukses via WhatsApp Bot Gateway' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($recipients->hasPages())
                <div class="px-5 sm:px-6 py-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                    {{ $recipients->links() }}
                </div>
            @endif
        @endif
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endpush
@endsection