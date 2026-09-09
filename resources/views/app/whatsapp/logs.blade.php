@extends('layouts.app', [
    'title' => 'Log Pesan WhatsApp — ' . $business->name,
    'headerTitle' => 'Log Komunikasi WhatsApp',
    'headerSubtitle' => 'Audit trail seluruh pesan otomatis, struk kasir POS, dan blast promosi terkirim'
])

@section('content')
<div class="space-y-6" x-data="{ filterType: 'all' }">

    <!-- Module Navigation Sub-Tabs & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-1.5 p-1 rounded-2xl bg-slate-100 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800/80 w-full sm:w-auto overflow-x-auto text-xs font-bold">
            <a href="{{ route('whatsapp.index') }}"
                class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                <i data-lucide="smartphone" class="w-4 h-4"></i>
                <span>Koneksi Gateway</span>
            </a>
            <a href="{{ route('whatsapp.broadcast.index') }}"
                class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                <i data-lucide="megaphone" class="w-4 h-4"></i>
                <span>Blast Promosi</span>
            </a>
            <a href="{{ route('whatsapp.logs.index') }}"
                class="px-4 py-2 rounded-xl bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-xs border border-slate-200/60 dark:border-slate-800 flex items-center gap-2 whitespace-nowrap">
                <i data-lucide="history" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                <span>Log Pesan</span>
            </a>
        </div>

        <!-- Quick Filter Chips -->
        <div class="flex items-center gap-1.5 overflow-x-auto text-xs font-bold">
            <button type="button" @click="filterType = 'all'"
                :class="filterType === 'all' ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900 shadow-2xs' : 'bg-white hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700'"
                class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap">
                Semua Log
            </button>
            <button type="button" @click="filterType = 'receipt'"
                :class="filterType === 'receipt' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-white hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700'"
                class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-1">
                <span>🧾 Struk Kasir</span>
            </button>
            <button type="button" @click="filterType = 'broadcast'"
                :class="filterType === 'broadcast' ? 'bg-teal-600 text-white shadow-2xs' : 'bg-white hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700'"
                class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-1">
                <span>📢 Blast Promosi</span>
            </button>
            <button type="button" @click="filterType = 'test'"
                :class="filterType === 'test' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-white hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700'"
                class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-1">
                <span>🔧 Tes Pesan</span>
            </button>
        </div>
    </div>

    <!-- LOGS DATA TABLE CARD -->
    <div class="bg-white dark:bg-slate-900/90 rounded-3xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs overflow-hidden transition-colors">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Riwayat Komunikasi Keluar</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Daftar transaksi pesan WhatsApp bot yang dikirim ke pelanggan</p>
            </div>
            <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}</span>
        </div>

        @if($logs->isEmpty())
            <div class="text-center py-16 px-4 space-y-3">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center mx-auto border border-slate-200 dark:border-slate-700">
                    <i data-lucide="inbox" class="w-7 h-7"></i>
                </div>
                <div>
                    <h3 class="text-slate-900 dark:text-white font-bold text-sm">Belum Ada Riwayat Pesan</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 max-w-sm mx-auto">
                        Log pengiriman pesan akan otomatis tercatat setiap kali kasir mengirim struk POS atau menjalankan blast promosi.
                    </p>
                </div>
                <a href="{{ route('whatsapp.index') }}"
                    class="inline-flex items-center gap-1.5 mt-2 text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                    <span>Lihat Status Gateway &rarr;</span>
                </a>
            </div>
        @else
            <div class="table-responsive overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[620px]">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 uppercase tracking-wider text-[11px]">
                            <th class="px-6 py-3.5 font-semibold">Penerima Pesan</th>
                            <th class="px-4 py-3.5 font-semibold">Tipe Komunikasi</th>
                            <th class="px-4 py-3.5 font-semibold">Isi Pesan</th>
                            <th class="px-4 py-3.5 font-semibold text-center">Status</th>
                            <th class="px-6 py-3.5 font-semibold">Waktu Terkirim</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-sans">
                        @foreach($logs as $log)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition"
                                x-show="filterType === 'all' || filterType === '{{ $log->type }}'">
                                <td class="px-6 py-3.5">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $log->recipient_name }}</div>
                                    <div class="text-slate-500 dark:text-slate-400 font-mono text-[11px]">{{ $log->recipient_phone }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    @php
                                        $typeBadge = match($log->type) {
                                            'receipt'   => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/20',
                                            'broadcast' => 'bg-teal-500/10 text-teal-700 dark:text-teal-300 border-teal-500/20',
                                            'test'      => 'bg-blue-500/10 text-blue-700 dark:text-blue-300 border-blue-500/20',
                                            default     => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700',
                                        };
                                        $typeLabel = match($log->type) {
                                            'receipt'   => 'Struk POS',
                                            'broadcast' => 'Blast Promosi',
                                            'test'      => 'Uji Tes',
                                            default     => ucfirst($log->type),
                                        };
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $typeBadge }}">
                                        {{ $typeLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 max-w-xs">
                                    <div class="text-slate-700 dark:text-slate-300 truncate text-[11px]" title="{{ $log->message }}">
                                        {{ Str::limit($log->message, 60) }}
                                    </div>
                                    @if($log->error_message)
                                        <div class="text-rose-600 dark:text-rose-400 text-[10px] mt-0.5 truncate font-medium">
                                            ⚠ {{ $log->error_message }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($log->status === 'sent')
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
                                <td class="px-6 py-3.5 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                                    {{ $log->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $logs->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
