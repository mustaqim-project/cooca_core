@extends('layouts.app', [
    'title' => 'Log Pesan WhatsApp - ' . $business->name,
    'headerTitle' => 'Log Komunikasi WhatsApp',
    'headerSubtitle' => 'Audit trail seluruh pesan otomatis, struk kasir POS, dan blast promosi terkirim',
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="whatsappLogs()">

        <!-- ========================================== -->
        <!-- 0. BREADCRUMB BAR (APPLE MINIMALIST)       -->
        <!-- ========================================== -->
        <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 py-0.5 whitespace-nowrap print:hidden"
            aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
            <a href="{{ route('whatsapp.index') }}" class="hover:text-[#007AFF] transition-colors font-medium">WhatsApp Gateway</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
            <span class="text-black/80 dark:text-white/80 font-medium">Log Pesan</span>
        </nav>

        <!-- ===================================================== -->
        <!-- 1. TOOLBAR & SUB-TABS (macOS Sonoma Toolbar Style)     -->
        <!-- ===================================================== -->
        <header
            class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
            <div>
                <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight">
                    Log Komunikasi WhatsApp
                </h1>
                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">
                    Audit trail seluruh pesan otomatis, struk kasir POS, dan blast promosi terkirim
                </p>
            </div>

            <!-- Apple-Style Segmented Quick Filter -->
            <div
                class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/5 dark:border-white/5 overflow-x-auto text-[12px] font-medium w-full sm:w-auto">
                <button type="button" @click="filterType = 'all'"
                    :class="filterType === 'all'
                        ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-bold'
                        : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                    class="h-8 px-3.5 rounded-[9px] transition-all whitespace-nowrap">
                    Semua Log
                </button>
                <button type="button" @click="filterType = 'receipt'"
                    :class="filterType === 'receipt'
                        ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-bold'
                        : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                    class="h-8 px-3.5 rounded-[9px] transition-all whitespace-nowrap flex items-center gap-1.5">
                    <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                    <span>Struk POS</span>
                </button>
                <button type="button" @click="filterType = 'broadcast'"
                    :class="filterType === 'broadcast'
                        ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-bold'
                        : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                    class="h-8 px-3.5 rounded-[9px] transition-all whitespace-nowrap flex items-center gap-1.5">
                    <i data-lucide="megaphone" class="w-3.5 h-3.5"></i>
                    <span>Blast Promosi</span>
                </button>
                <button type="button" @click="filterType = 'test'"
                    :class="filterType === 'test'
                        ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-bold'
                        : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                    class="h-8 px-3.5 rounded-[9px] transition-all whitespace-nowrap flex items-center gap-1.5">
                    <i data-lucide="wrench" class="w-3.5 h-3.5"></i>
                    <span>Uji Coba</span>
                </button>
            </div>
        </header>

        <!-- Sub-Tabs Segmented Bar -->
        <div
            class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-2 sm:p-2.5 flex items-center justify-between shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
            <div
                class="inline-flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] border border-black/5 dark:border-white/5 w-full sm:w-auto overflow-x-auto text-[13px] font-medium">
                <a href="{{ route('whatsapp.index') }}"
                    class="h-9 px-4 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="smartphone" class="w-4 h-4"></i>
                    <span>Koneksi Gateway</span>
                </a>
                <a href="{{ route('whatsapp.broadcast.index') }}"
                    class="h-9 px-4 rounded-[10px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 whitespace-nowrap transition-colors">
                    <i data-lucide="megaphone" class="w-4 h-4"></i>
                    <span>Blast Promosi</span>
                </a>
                <a href="{{ route('whatsapp.logs.index') }}"
                    class="h-9 px-4 rounded-[10px] bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] flex items-center gap-2 whitespace-nowrap font-semibold">
                    <i data-lucide="history" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Log Pesan</span>
                </a>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- 2. LOGS DATA TABLE (Apple Dense Table)                -->
        <!-- ===================================================== -->
        <div
            class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-[0_1px_2px_rgba(0,0,0,0.02)] overflow-hidden transition-colors">
            <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h2 class="text-[15px] font-bold text-black dark:text-white">Riwayat Komunikasi Keluar</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Daftar transaksi pesan WhatsApp bot yang dikirim ke pelanggan</p>
                </div>
                <span class="text-[12px] text-black/40 dark:text-white/40 font-semibold tabular-nums">
                    Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}
                </span>
            </div>

            @if ($logs->isEmpty())
                <div class="text-center py-16 px-4 space-y-3">
                    <div
                        class="w-16 h-16 rounded-[18px] bg-black/[0.04] dark:bg-white/[0.06] text-black/30 dark:text-white/30 flex items-center justify-center mx-auto border border-black/5 dark:border-white/10">
                        <i data-lucide="inbox" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <h3 class="text-black dark:text-white font-bold text-[16px]">Belum Ada Riwayat Pesan</h3>
                        <p class="text-black/50 dark:text-white/50 text-[13px] mt-1 max-w-sm mx-auto leading-relaxed">
                            Log pengiriman pesan akan otomatis tercatat setiap kali kasir mengirim struk POS atau menjalankan blast promosi.
                        </p>
                    </div>
                    <a href="{{ route('whatsapp.index') }}"
                        class="inline-flex items-center gap-1.5 mt-2 text-[13px] font-bold text-[#007AFF] hover:underline">
                        <span>Lihat Status Gateway</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            @else
                {{-- Desktop Logs Table --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-[13.5px]">
                        <thead>
                            <tr
                                class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 uppercase tracking-wide text-[11px] font-semibold">
                                <th class="px-5 py-3">Penerima Pesan</th>
                                <th class="px-4 py-3">Tipe Komunikasi</th>
                                <th class="px-4 py-3">Isi Pesan</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3">Waktu Terkirim</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @foreach ($logs as $log)
                                @php
                                    $logJson = [
                                        'id' => $log->id,
                                        'recipient_name' => $log->recipient_name,
                                        'recipient_phone' => $log->recipient_phone,
                                        'type' => $log->type,
                                        'message' => $log->message,
                                        'status' => $log->status,
                                        'error_message' => $log->error_message,
                                        'sent_at' => $log->created_at->format('d F Y, H:i:s') . ' WIB',
                                    ];
                                @endphp
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors cursor-pointer"
                                    x-show="filterType === 'all' || filterType === '{{ $log->type }}'"
                                    @click="inspectLog(@json($logJson))">
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-black dark:text-white">{{ $log->recipient_name }}</div>
                                        <div class="text-black/50 dark:text-white/50 tabular-nums text-[12px]">{{ $log->recipient_phone }}</div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @php
                                            $typeBadge = match ($log->type) {
                                                'receipt' => 'bg-[#30B0C7]/12 text-[#227D8E] dark:text-[#40C8E0]',
                                                'broadcast' => 'bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]',
                                                'test' => 'bg-[#007AFF]/12 text-[#0062CC] dark:text-[#0A84FF]',
                                                default => 'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60',
                                            };
                                            $typeLabel = match ($log->type) {
                                                'receipt' => 'Struk POS',
                                                'broadcast' => 'Blast Promosi',
                                                'test' => 'Uji Tes',
                                                default => ucfirst($log->type),
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $typeBadge }}">
                                            {{ $typeLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 max-w-xs">
                                        <div class="text-black/80 dark:text-white/80 truncate text-[12.5px]" title="{{ $log->message }}">
                                            {{ Str::limit($log->message, 65) }}
                                        </div>
                                        @if ($log->error_message)
                                            <div class="text-[#FF3B30] dark:text-[#FF453A] text-[11px] mt-0.5 truncate font-medium flex items-center gap-1">
                                                <i data-lucide="alert-circle" class="w-3 h-3 shrink-0"></i>
                                                <span class="truncate">{{ $log->error_message }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if ($log->status === 'sent')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                <i data-lucide="check" class="w-3 h-3"></i>
                                                <span>Terkirim</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                                <i data-lucide="x" class="w-3 h-3"></i>
                                                <span>Gagal</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-black/50 dark:text-white/50 tabular-nums text-[12px] whitespace-nowrap">
                                        {{ $log->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <button type="button" @click.stop="inspectLog(@json($logJson))"
                                            class="min-h-[30px] px-2.5 rounded-[8px] text-[12px] font-semibold text-[#007AFF] hover:bg-[#007AFF]/10 transition-colors inline-flex items-center gap-1">
                                            <span>Inspeksi</span>
                                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Logs List --}}
                <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @foreach ($logs as $log)
                        @php
                            $typeBadge = match ($log->type) {
                                'receipt' => 'bg-[#30B0C7]/12 text-[#227D8E] dark:text-[#40C8E0]',
                                'broadcast' => 'bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]',
                                'test' => 'bg-[#007AFF]/12 text-[#0062CC] dark:text-[#0A84FF]',
                                default => 'bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60',
                            };
                            $typeLabel = match ($log->type) {
                                'receipt' => 'Struk POS',
                                'broadcast' => 'Blast Promosi',
                                'test' => 'Uji Tes',
                                default => ucfirst($log->type),
                            };
                            $logJsonMobile = [
                                'id' => $log->id,
                                'recipient_name' => $log->recipient_name,
                                'recipient_phone' => $log->recipient_phone,
                                'type' => $log->type,
                                'message' => $log->message,
                                'status' => $log->status,
                                'error_message' => $log->error_message,
                                'sent_at' => $log->created_at->format('d F Y, H:i:s') . ' WIB',
                            ];
                        @endphp
                        <div x-show="filterType === 'all' || filterType === '{{ $log->type }}'"
                            @click="inspectLog(@json($logJsonMobile))"
                            class="p-4 space-y-2.5 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors cursor-pointer">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="font-bold text-[14.5px] text-black dark:text-white">{{ $log->recipient_name }}</div>
                                    <div class="text-black/50 dark:text-white/50 font-mono tabular-nums text-[11.5px] mt-0.5">{{ $log->recipient_phone }}</div>
                                </div>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold {{ $typeBadge }} shrink-0">
                                    {{ $typeLabel }}
                                </span>
                            </div>

                            <div class="text-black/75 dark:text-white/75 text-[12.5px] bg-black/[0.02] dark:bg-white/[0.02] p-3 rounded-[10px] leading-relaxed">
                                {{ $log->message }}
                                @if ($log->error_message)
                                    <div class="text-[#FF3B30] dark:text-[#FF453A] text-[11px] mt-1 font-medium flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3 h-3 shrink-0"></i>
                                        <span class="truncate">{{ $log->error_message }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center justify-between text-[12px] pt-1">
                                <div>
                                    @if ($log->status === 'sent')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            <span>Terkirim</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]">
                                            <i data-lucide="x" class="w-3 h-3"></i>
                                            <span>Gagal</span>
                                        </span>
                                    @endif
                                </div>
                                <span class="text-black/45 dark:text-white/45 text-[11.5px] tabular-nums">
                                    {{ $log->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($logs->hasPages())
                    <div class="p-4 border-t border-black/5 dark:border-white/10">
                        {{ $logs->links() }}
                    </div>
                @endif
            @endif
        </div>

        <!-- =================================================================== -->
        <!-- 3. LOG INSPECTOR MODAL SHEET (Apple HIG Detail Inspector Sheet)     -->
        <!-- =================================================================== -->
        <div x-show="inspectorOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4 lg:p-6 overflow-hidden"
            @keydown.escape.window="closeInspector()">

            <!-- Backdrop -->
            <div x-show="inspectorOpen" x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" @click="closeInspector()"
                class="fixed inset-0 bg-black/40 dark:bg-black/60 backdrop-blur-sm"></div>

            <!-- Inspector Dialog Canvas -->
            <div x-show="inspectorOpen" x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
                class="relative w-full max-w-[95vw] md:max-w-2xl bg-white dark:bg-[#1C1C1E] sm:rounded-[24px] rounded-t-[28px] max-h-[95vh] sm:max-h-[90vh] flex flex-col overflow-hidden shadow-2xl border border-black/10 dark:border-white/10 z-10">

                <!-- Mobile Grab Bar -->
                <div class="sm:hidden w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto my-2.5 shrink-0"></div>

                <!-- Header -->
                <header class="px-5 sm:px-6 py-4 bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border-b border-black/5 dark:border-white/10 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-[16px] font-bold text-black dark:text-white">Rincian Log Pesan WhatsApp</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50" x-text="activeLog?.sent_at"></p>
                        </div>
                    </div>
                    <button type="button" @click="closeInspector()"
                        class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black/60 dark:text-white/60 flex items-center justify-center transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </header>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-5">
                    <!-- Recipient & Meta Grid -->
                    <div class="grid grid-cols-2 gap-3 p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 text-[12.5px]">
                        <div>
                            <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase">Penerima</span>
                            <p class="font-bold text-black dark:text-white text-[13.5px] mt-0.5" x-text="activeLog?.recipient_name || '-'"></p>
                        </div>
                        <div>
                            <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase">Nomor WhatsApp</span>
                            <p class="font-mono font-bold text-[#007AFF] text-[13.5px] mt-0.5 tabular-nums" x-text="activeLog?.recipient_phone || '-'"></p>
                        </div>
                        <div>
                            <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase">Tipe Komunikasi</span>
                            <p class="font-bold text-black dark:text-white mt-0.5 capitalize" x-text="activeLog?.type || '-'"></p>
                        </div>
                        <div>
                            <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase">Status Pengiriman</span>
                            <p class="font-bold mt-0.5" :class="activeLog?.status === 'sent' ? 'text-[#34C759]' : 'text-[#FF3B30]'" x-text="activeLog?.status === 'sent' ? 'Terkirim Sukses' : 'Gagal Terkirim'"></p>
                        </div>
                    </div>

                    <!-- Full Message Bubble -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-[12px] font-bold text-black/70 dark:text-white/70">Isi Pesan Utuh</label>
                            <button type="button" @click="copyMessage()"
                                class="text-[11.5px] text-[#007AFF] hover:underline font-semibold flex items-center gap-1">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                <span x-text="copied ? 'Tersalin!' : 'Salin Teks'"></span>
                            </button>
                        </div>
                        <div class="p-4 rounded-[14px] bg-[#ECE5DD]/40 dark:bg-[#0b141a]/60 border border-black/5 dark:border-white/10 font-mono text-[12.5px] text-black/90 dark:text-white/90 whitespace-pre-wrap leading-relaxed max-h-60 overflow-y-auto"
                            x-text="activeLog?.message"></div>
                    </div>

                    <!-- Error Callout if Failed -->
                    <template x-if="activeLog?.error_message">
                        <div class="p-3.5 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[12px] text-[#C41E17] dark:text-[#FF453A] space-y-1">
                            <span class="font-bold block">Pesan Kendala Sistem:</span>
                            <p class="font-mono" x-text="activeLog?.error_message"></p>
                        </div>
                    </template>
                </div>

                <!-- Footer Action -->
                <footer class="px-5 sm:px-6 py-4 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-md border-t border-black/5 dark:border-white/10 flex items-center justify-between gap-3 shrink-0">
                    <a :href="'https://wa.me/' + (activeLog?.recipient_phone ? activeLog.recipient_phone.replace(/[^0-9]/g, '') : '') + '?text=' + encodeURIComponent(activeLog?.message || '')"
                        target="_blank" rel="noopener noreferrer"
                        class="min-h-[44px] px-4 rounded-[12px] bg-[#34C759] hover:bg-[#2FB350] text-white font-bold text-[13px] shadow-sm flex items-center gap-2 transition active:scale-[0.98]">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        <span>Buka di WhatsApp Web / HP</span>
                    </a>

                    <button type="button" @click="closeInspector()"
                        class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition active:scale-[0.98]">
                        Tutup
                    </button>
                </footer>

            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function whatsappLogs() {
            return {
                filterType: 'all',
                inspectorOpen: false,
                activeLog: null,
                copied: false,

                init() {
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                inspectLog(log) {
                    this.activeLog = log;
                    this.inspectorOpen = true;
                    this.copied = false;
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                closeInspector() {
                    this.inspectorOpen = false;
                    this.activeLog = null;
                },

                async copyMessage() {
                    if (!this.activeLog?.message) return;
                    try {
                        await navigator.clipboard.writeText(this.activeLog.message);
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    } catch (e) {}
                }
            };
        }
    </script>
@endpush
