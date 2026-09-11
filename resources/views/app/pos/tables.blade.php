@extends('layouts.app', ['title' => 'Manajemen Meja & QR Restoran'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showAddModal: false,
    showEditModal: false,
    showRegenModal: false,
    selectedTable: null,
    editForm: { id: '', table_number: '', name: '', capacity: 4, location_id: '', is_active: true, notes: '' },
    regenTable: null,
    isSubmitting: false,

    openEdit(table) {
        this.selectedTable = table;
        this.editForm = {
            id: table.id,
            table_number: table.table_number,
            name: table.name || '',
            capacity: table.capacity,
            location_id: table.location_id || '',
            is_active: Boolean(table.is_active),
            notes: table.notes || ''
        };
        this.showEditModal = true;
    },

    confirmRegen(table) {
        this.regenTable = table;
        this.showRegenModal = true;
    }
}">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pos.terminal') }}" class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    Terminal Kasir POS
                </a>
                <span class="text-xs text-black/30 dark:text-white/30">•</span>
                <span class="text-xs text-black/50 dark:text-white/50">F&amp;B Management</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-black dark:text-white mt-1">Manajemen Meja &amp; QR Restoran</h1>
            <p class="text-xs text-black/60 dark:text-white/60 mt-0.5">Kelola tata letak meja, cetak kartu QR akrilik meja, dan pantau sesi pesanan pelanggan aktif.</p>
        </div>

        <div class="flex items-center gap-2">
            @if(\App\Support\Context::hasPermission('pos.kitchen'))
            <a href="{{ route('pos.kitchen.index') }}" class="h-9 px-3.5 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-xs font-semibold flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-[#FF9500]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Kitchen Display</span>
            </a>
            @endif

            @if(\App\Support\Context::hasPermission('pos.tables'))
            <a href="{{ route('pos.tables.qr-cards') }}" target="_blank" class="h-9 px-3.5 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-xs font-semibold flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                <span>Cetak Kartu QR</span>
            </a>

            <button type="button" @click="showAddModal = true" class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white text-xs font-semibold flex items-center gap-2 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Meja Baru</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Stats Overview Cards (Apple HIG Bento) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-sm">
            <div class="text-[11px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wider">Total Meja</div>
            <div class="text-2xl font-bold tracking-tight text-black dark:text-white mt-1 tabular-nums">{{ $stats['total_tables'] }}</div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Seluruh unit meja terdaftar</div>
        </div>

        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-sm">
            <div class="text-[11px] font-medium text-[#34C759] uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                Meja Tersedia
            </div>
            <div class="text-2xl font-bold tracking-tight text-black dark:text-white mt-1 tabular-nums">{{ $stats['available_tables'] }}</div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Siap menerima tamu</div>
        </div>

        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-sm">
            <div class="text-[11px] font-medium text-[#007AFF] uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-pulse"></span>
                Meja Terisi (Aktif)
            </div>
            <div class="text-2xl font-bold tracking-tight text-black dark:text-white mt-1 tabular-nums">{{ $stats['occupied_tables'] }}</div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Tamu sedang bersantap/pesan</div>
        </div>

        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-sm">
            <div class="text-[11px] font-medium text-black/50 dark:text-white/50 uppercase tracking-wider">Total Kapasitas</div>
            <div class="text-2xl font-bold tracking-tight text-black dark:text-white mt-1 tabular-nums">{{ $stats['total_capacity'] }} <span class="text-sm font-normal text-black/40 dark:text-white/40">kursi</span></div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Daya tampung simultan</div>
        </div>
    </div>

    <!-- Filter Bar -->
    @if($locations->count() > 1)
    <div class="flex items-center gap-3">
        <label class="text-xs font-medium text-black/60 dark:text-white/60">Filter Lokasi / Outlet:</label>
        <form method="GET" action="{{ route('pos.tables.index') }}" class="flex items-center gap-2">
            <select name="location_id" onchange="this.form.submit()" class="h-8 text-xs rounded-[8px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                <option value="">Semua Lokasi / Outlet</option>
                @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ $selectedLocationId === $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                @endforeach
            </select>
        </form>
    </div>
    @endif

    <!-- Tables Grid -->
    @if($tables->isEmpty())
    <div class="p-12 text-center rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-dashed border-black/15 dark:border-white/15 space-y-3">
        <div class="w-14 h-14 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
        </div>
        <div class="font-semibold text-base text-black dark:text-white">Belum Ada Meja Terdaftar</div>
        <p class="text-xs text-black/50 dark:text-white/50 max-w-sm mx-auto">Tambahkan unit meja restoran Anda untuk mulai mencetak kartu QR meja dan melayani pemesanan mandiri oleh pelanggan.</p>
        @if(\App\Support\Context::hasPermission('pos.tables'))
        <button type="button" @click="showAddModal = true" class="h-9 px-4 rounded-[10px] bg-[#007AFF] text-white text-xs font-semibold inline-flex items-center gap-2">
            <span>Tambah Meja Pertama</span>
        </button>
        @endif
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($tables as $table)
        @php
            $isOccupied = in_array($table->status, [
                \App\Models\PosTable::STATUS_OCCUPIED,
                \App\Models\PosTable::STATUS_ORDERING,
                \App\Models\PosTable::STATUS_PREPARING,
                \App\Models\PosTable::STATUS_SERVING,
                \App\Models\PosTable::STATUS_WAITING_PAYMENT,
            ]);
            $session = $table->activeSession;
        @endphp
        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-sm flex flex-col justify-between transition hover:border-[#007AFF]/40">
            <div>
                <!-- Card Header -->
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="text-[11px] font-medium text-black/40 dark:text-white/40 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            <span>{{ $table->capacity }} Kursi</span>
                            @if($table->location)
                                <span>• {{ $table->location->name }}</span>
                            @endif
                        </div>
                        <h3 class="text-[17px] font-bold text-black dark:text-white mt-0.5">{{ $table->table_number }}</h3>
                        @if($table->name)
                            <div class="text-xs text-black/60 dark:text-white/60 truncate">{{ $table->name }}</div>
                        @endif
                    </div>

                    <!-- Status Pill -->
                    <div>
                        @if($table->status === \App\Models\PosTable::STATUS_AVAILABLE)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                Tersedia
                            </span>
                        @elseif($table->status === \App\Models\PosTable::STATUS_ORDERING)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#FF9500]/10 text-[#FF9500] border border-[#FF9500]/20 animate-pulse">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                Memilih Menu
                            </span>
                        @elseif($table->status === \App\Models\PosTable::STATUS_PREPARING)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#AF52DE]/10 text-[#AF52DE] border border-[#AF52DE]/20 animate-pulse">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#AF52DE]"></span>
                                Menyiapkan
                            </span>
                        @elseif($table->status === \App\Models\PosTable::STATUS_SERVING)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#30B0C7]/10 text-[#30B0C7] border border-[#30B0C7]/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#30B0C7]"></span>
                                Disajikan
                            </span>
                        @elseif($table->status === \App\Models\PosTable::STATUS_WAITING_PAYMENT)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#FFCC00]/15 text-[#D48800] dark:text-[#FFD60A] border border-[#FFCC00]/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FFCC00]"></span>
                                Tagihan
                            </span>
                        @elseif($table->status === \App\Models\PosTable::STATUS_INACTIVE)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black/50 dark:text-white/50 border border-black/10 dark:border-white/10">
                                Nonaktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>
                                Terisi
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Session Info -->
                <div class="mt-3.5 pt-3 border-t border-black/5 dark:border-white/5">
                    @if($session)
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-black/50 dark:text-white/50">Pelanggan:</span>
                            <span class="font-semibold text-black dark:text-white truncate max-w-[130px]">{{ $session->customer_name }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-black/50 dark:text-white/50">WhatsApp:</span>
                            <span class="font-medium text-black/70 dark:text-white/70 tabular-nums">{{ $session->customer_phone }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-black/50 dark:text-white/50">Pesanan / Total:</span>
                            <span class="font-bold text-[#34C759] tabular-nums">
                                {{ $session->orders->count() }} pesanan • Rp {{ number_format($session->total_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    @else
                    <div class="py-2 text-center text-xs text-black/40 dark:text-white/40">
                        Meja kosong. Siap menerima tamu.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Card Actions -->
            <div class="mt-4 pt-3 border-t border-black/5 dark:border-white/5 space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('pos.tables.qr-card', $table->id) }}" target="_blank" class="h-8 px-2 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[11px] font-semibold flex items-center justify-center gap-1.5 transition">
                        <svg class="w-3.5 h-3.5 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                        <span>Lihat QR Card</span>
                    </a>

                    <a href="{{ route('pos.tables.qr-svg', $table->id) }}" class="h-8 px-2 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[11px] font-semibold flex items-center justify-center gap-1.5 transition">
                        <svg class="w-3.5 h-3.5 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        <span>Unduh SVG</span>
                    </a>
                </div>

                @if(\App\Support\Context::hasPermission('pos.tables'))
                <div class="flex items-center justify-between gap-1 pt-1">
                    <button type="button" @click="openEdit({{ json_encode($table) }})" class="text-[11px] text-[#007AFF] hover:underline font-medium">
                        Edit Meja
                    </button>

                    <button type="button" @click="confirmRegen({{ json_encode($table) }})" class="text-[11px] text-[#FF9500] hover:underline font-medium">
                        Regenerate QR
                    </button>

                    @if($session && $session->canBeClosed())
                    <form method="POST" action="{{ route('pos.sessions.close', $session->id) }}" onsubmit="return confirm('Tutup sesi meja ini dan jadikan meja kembali tersedia?')">
                        @csrf
                        <button type="submit" class="text-[11px] text-[#34C759] hover:underline font-bold">
                            Tutup Sesi
                        </button>
                    </form>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- ========================================================= -->
    <!-- MODAL: TAMBAH MEJA BARU                                   -->
    <!-- ========================================================= -->
    @if(\App\Support\Context::hasPermission('pos.tables'))
    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4" style="display: none;">
        <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[18px] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4 text-black dark:text-white" @click.outside="showAddModal = false">
            <div class="flex items-center justify-between pb-2 border-b border-black/10 dark:border-white/10">
                <h3 class="font-bold text-base">Tambah Meja Baru</h3>
                <button type="button" @click="showAddModal = false" class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white">✕</button>
            </div>

            <form method="POST" action="{{ route('pos.tables.store') }}" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Nomor / Kode Meja *</label>
                    <input type="text" name="table_number" required placeholder="Contoh: Meja 01, VIP-A" class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Keterangan / Nama Area (Opsional)</label>
                    <input type="text" name="name" placeholder="Contoh: Area Outdoor Lantai 2" class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Kapasitas Kursi *</label>
                        <input type="number" name="capacity" min="1" max="100" value="4" required class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Outlet / Lokasi</label>
                        <select name="location_id" class="w-full h-10 px-2.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="">Semua Lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Catatan Khusus</label>
                    <textarea name="notes" rows="2" placeholder="Catatan internal meja..." class="w-full p-2.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showAddModal = false" class="h-9 px-4 rounded-[10px] text-xs font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5">Batal</button>
                    <button type="submit" class="h-9 px-5 rounded-[10px] bg-[#007AFF] text-white text-xs font-semibold shadow-sm">Simpan Meja</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ========================================================= -->
    <!-- MODAL: EDIT MEJA                                          -->
    <!-- ========================================================= -->
    @if(\App\Support\Context::hasPermission('pos.tables'))
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4" style="display: none;">
        <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[18px] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4 text-black dark:text-white" @click.outside="showEditModal = false">
            <div class="flex items-center justify-between pb-2 border-b border-black/10 dark:border-white/10">
                <h3 class="font-bold text-base">Edit Meja</h3>
                <button type="button" @click="showEditModal = false" class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white">✕</button>
            </div>

            <form method="POST" :action="'{{ url('pos/tables') }}/' + editForm.id" class="space-y-3.5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Nomor / Kode Meja *</label>
                    <input type="text" name="table_number" x-model="editForm.table_number" required class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Keterangan / Area</label>
                    <input type="text" name="name" x-model="editForm.name" class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Kapasitas Kursi *</label>
                        <input type="number" name="capacity" x-model.number="editForm.capacity" min="1" max="100" required class="w-full h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Status Meja</label>
                        <select name="is_active" x-model="editForm.is_active" class="w-full h-10 px-2.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option :value="true">Aktif</option>
                            <option :value="false">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1 text-black/70 dark:text-white/70">Catatan</label>
                    <textarea name="notes" x-model="editForm.notes" rows="2" class="w-full p-2.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                </div>

                <div class="flex justify-between items-center pt-2 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="
                        if(confirm('Yakin ingin menghapus meja ini?')) {
                            const f = document.createElement('form');
                            f.method = 'POST';
                            f.action = '{{ url('pos/tables') }}/' + editForm.id;
                            f.innerHTML = '<input type=\'hidden\' name=\'_token\' value=\'{{ csrf_token() }}\'><input type=\'hidden\' name=\'_method\' value=\'DELETE\'>';
                            document.body.appendChild(f);
                            f.submit();
                        }
                    " class="text-xs text-[#FF3B30] hover:underline font-semibold">
                        Hapus Meja
                    </button>

                    <div class="flex gap-2">
                        <button type="button" @click="showEditModal = false" class="h-9 px-4 rounded-[10px] text-xs font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5">Batal</button>
                        <button type="submit" class="h-9 px-5 rounded-[10px] bg-[#007AFF] text-white text-xs font-semibold shadow-sm">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ========================================================= -->
    <!-- MODAL: REGENERATE QR CONFIRMATION                         -->
    <!-- ========================================================= -->
    @if(\App\Support\Context::hasPermission('pos.tables'))
    <div x-show="showRegenModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4" style="display: none;">
        <div class="w-full max-w-sm bg-white dark:bg-[#1C1C1E] rounded-[18px] border border-black/10 dark:border-white/10 p-6 shadow-2xl text-center space-y-4 text-black dark:text-white" @click.outside="showRegenModal = false">
            <div class="w-12 h-12 rounded-full bg-[#FF9500]/15 text-[#FF9500] flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-base">Regenerate QR Code?</h3>
                <p class="text-xs text-black/60 dark:text-white/60 mt-1">
                    Token QR Meja <strong x-text="regenTable ? regenTable.table_number : ''"></strong> akan diperbarui. Stiker atau kartu QR fisik yang lama tidak akan bisa digunakan lagi.
                </p>
            </div>

            <form method="POST" :action="'{{ url('pos/tables') }}/' + (regenTable ? regenTable.id : '') + '/regenerate-qr'" class="flex justify-center gap-2 pt-2">
                @csrf
                <button type="button" @click="showRegenModal = false" class="h-9 px-4 rounded-[10px] text-xs font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5">Batal</button>
                <button type="submit" class="h-9 px-5 rounded-[10px] bg-[#FF9500] hover:bg-[#E08500] text-white text-xs font-semibold shadow-sm">Ya, Perbarui QR</button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
