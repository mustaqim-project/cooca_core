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

    <!-- Top Header / Toolbar -->
    <div class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
                <a href="{{ route('pos.terminal') }}" class="hover:text-[#007AFF] transition-colors">POS</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-black/30 dark:text-white/30"></i>
                <span class="text-black dark:text-white font-medium">Manajemen Meja</span>
            </nav>
            <h1 class="text-[20px] font-semibold tracking-tight text-black dark:text-white">Manajemen Meja &amp; QR Restoran</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Kelola tata letak meja, cetak kartu QR akrilik meja, dan pantau sesi pesanan tamu aktif</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('pos.kitchen'))
            <a href="{{ route('pos.kitchen.index') }}" class="min-h-[44px] sm:min-h-0 sm:h-9 px-3.5 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] text-black dark:text-white text-[13px] font-semibold flex items-center justify-center gap-1.5 transition">
                <i data-lucide="utensils-crossed" class="w-4 h-4 text-[#FF9500]"></i>
                <span>Kitchen Display</span>
            </a>
            @endif

            @if(\App\Support\Context::hasPermission('storefront.reservations.manage') || \App\Support\Context::isOwner())
            <a href="{{ route('storefront.reservations.index') }}" class="min-h-[44px] sm:min-h-0 sm:h-9 px-3.5 rounded-[10px] bg-[#34C759]/10 hover:bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] text-[13px] font-semibold flex items-center justify-center gap-1.5 transition active:scale-[0.97]">
                <i data-lucide="calendar" class="w-4 h-4"></i>
                <span>Buku Reservasi</span>
                @if(($stats['today_reservations'] ?? 0) > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759] text-white tabular-nums">
                    {{ $stats['today_reservations'] }}
                </span>
                @endif
            </a>
            @endif

            @if(\App\Support\Context::hasPermission('pos.tables'))
            <a href="{{ route('pos.tables.qr-cards') }}" target="_blank" class="min-h-[44px] sm:min-h-0 sm:h-9 px-3.5 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] text-black dark:text-white text-[13px] font-semibold flex items-center justify-center gap-1.5 transition">
                <i data-lucide="qr-code" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Cetak Kartu QR</span>
            </a>

            <button type="button" @click="showAddModal = true" class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white text-[13px] font-semibold flex items-center justify-center gap-1.5 transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Meja Baru</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Stats Overview Cards (Apple HIG Bento) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-3.5">
        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs">
            <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Total Meja</div>
            <div class="text-2xl font-bold tracking-tight text-black dark:text-white mt-1 tabular-nums">{{ $stats['total_tables'] }}</div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Seluruh unit terdaftar</div>
        </div>

        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs">
            <div class="text-[11px] font-semibold text-[#34C759] uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                Meja Tersedia
            </div>
            <div class="text-2xl font-bold tracking-tight text-black dark:text-white mt-1 tabular-nums">{{ $stats['available_tables'] }}</div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Siap menerima tamu</div>
        </div>

        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs">
            <div class="text-[11px] font-semibold text-[#007AFF] uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-[#007AFF] animate-pulse"></span>
                Meja Terisi
            </div>
            <div class="text-2xl font-bold tracking-tight text-black dark:text-white mt-1 tabular-nums">{{ $stats['occupied_tables'] }}</div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Tamu aktif bersantap</div>
        </div>

        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs">
            <div class="text-[11px] font-semibold text-[#248A3D] dark:text-[#30D158] uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                Reservasi Hari Ini
            </div>
            <div class="text-2xl font-bold tracking-tight text-black dark:text-white mt-1 tabular-nums">{{ $stats['today_reservations'] ?? 0 }}</div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Jadwal booking aktif</div>
        </div>

        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs col-span-2 sm:col-span-1">
            <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Kapasitas</div>
            <div class="text-2xl font-bold tracking-tight text-black dark:text-white mt-1 tabular-nums">{{ $stats['total_capacity'] }} <span class="text-xs font-normal text-black/40 dark:text-white/40">kursi</span></div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">Daya tampung simultan</div>
        </div>
    </div>

    {{-- Today's Reservations Bento Strip --}}
    @if(isset($todayReservations) && $todayReservations->isNotEmpty())
    <div class="p-4 sm:p-5 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center font-bold">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-[14px] font-bold text-black dark:text-white">Jadwal Reservasi Hari Ini ({{ $todayReservations->count() }})</h3>
                    <p class="text-[11px] text-black/50 dark:text-white/50">Tamu yang memiliki jadwal reservasi meja restoran hari ini</p>
                </div>
            </div>
            <a href="{{ route('storefront.reservations.index') }}" class="text-[12px] text-[#007AFF] hover:underline font-semibold flex items-center gap-1">
                <span>Buka Modul Reservasi</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($todayReservations as $rsv)
            <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 flex items-center justify-between text-xs gap-2">
                <div class="min-w-0">
                    <div class="font-bold text-black dark:text-white truncate">{{ $rsv->customer_name }}</div>
                    <div class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-1.5 mt-0.5">
                        <span class="font-semibold text-[#007AFF]">{{ $rsv->time_slot }}</span>
                        <span>•</span>
                        <span>{{ $rsv->guest_count }} Tamu</span>
                    </div>
                </div>
                <div class="shrink-0 text-right">
                    @if($rsv->posTable)
                        <span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-[#5856D6]/10 text-[#5856D6]">
                            Meja #{{ $rsv->posTable->table_number }}
                        </span>
                    @else
                        <a href="{{ route('storefront.reservations.index') }}" class="px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-[#FF9500]/10 text-[#D97706] hover:underline">
                            Plot Meja
                        </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Filter Bar -->
    @if($locations->count() > 1)
    <div class="flex items-center gap-3">
        <label class="text-[12px] font-medium text-black/60 dark:text-white/60">Filter Lokasi / Outlet:</label>
        <form method="GET" action="{{ route('pos.tables.index') }}" class="flex items-center gap-2">
            <select name="location_id" onchange="this.form.submit()" class="h-9 text-[13px] rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
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
    <div class="p-12 text-center rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-dashed border-black/15 dark:border-white/15 space-y-3">
        <div class="w-14 h-14 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto">
            <i data-lucide="layout-grid" class="w-7 h-7"></i>
        </div>
        <div class="font-semibold text-[16px] text-black dark:text-white">Belum Ada Meja Terdaftar</div>
        <p class="text-[13px] text-black/50 dark:text-white/50 max-w-sm mx-auto">Tambahkan unit meja restoran Anda untuk mulai mencetak kartu QR meja dan melayani pemesanan mandiri oleh pelanggan.</p>
        @if(\App\Support\Context::hasPermission('pos.tables'))
        <button type="button" @click="showAddModal = true" class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] bg-[#007AFF] text-white text-[13px] font-semibold inline-flex items-center justify-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
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
        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-xs flex flex-col justify-between transition hover:border-[#007AFF]/40">
            <div>
                <!-- Card Header -->
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="text-[11px] font-medium text-black/40 dark:text-white/40 flex items-center gap-1.5">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                            <span>{{ $table->capacity }} Kursi</span>
                            @if($table->location)
                                <span>• {{ $table->location->name }}</span>
                            @endif
                        </div>
                        <h3 class="text-[17px] font-bold text-black dark:text-white mt-0.5">{{ $table->table_number }}</h3>
                        @if($table->name)
                            <div class="text-[12px] text-black/60 dark:text-white/60 truncate">{{ $table->name }}</div>
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
                        <div class="flex items-center justify-between text-[12px]">
                            <span class="text-black/50 dark:text-white/50">Pelanggan:</span>
                            <span class="font-semibold text-black dark:text-white truncate max-w-[130px]">{{ $session->customer_name }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[12px]">
                            <span class="text-black/50 dark:text-white/50">WhatsApp:</span>
                            <span class="font-medium text-black/70 dark:text-white/70 tabular-nums">{{ $session->customer_phone }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[12px]">
                            <span class="text-black/50 dark:text-white/50">Pesanan / Total:</span>
                            <span class="font-bold text-[#34C759] tabular-nums">
                                {{ $session->orders->count() }} pesanan • Rp {{ number_format($session->total_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    @else
                    <div class="py-2 text-center text-[12px] text-black/40 dark:text-white/40">
                        Meja kosong. Siap menerima tamu.
                    </div>
                    @endif

                    @php
                        $tableBooking = isset($todayReservations) ? $todayReservations->where('pos_table_id', $table->id)->first() : null;
                    @endphp
                    @if($tableBooking)
                    <div class="mt-2.5 p-2 rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 flex items-center justify-between text-xs">
                        <div class="min-w-0 pr-1">
                            <span class="text-[9.5px] font-bold uppercase tracking-wider text-[#248A3D] dark:text-[#30D158] block">Booking Hari Ini</span>
                            <div class="font-bold text-black dark:text-white truncate text-[11.5px]">{{ $tableBooking->customer_name }}</div>
                            <div class="text-[10.5px] text-black/60 dark:text-white/60">{{ $tableBooking->time_slot }} • {{ $tableBooking->guest_count }} Orang</div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-[#34C759] text-white shrink-0 uppercase tracking-wide">
                            Booked
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Card Actions -->
            <div class="mt-4 pt-3 border-t border-black/5 dark:border-white/5 space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('pos.tables.qr-card', $table->id) }}" target="_blank" class="min-h-[36px] px-2 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center justify-center gap-1.5 transition">
                        <i data-lucide="qr-code" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span>Lihat QR Card</span>
                    </a>

                    <a href="{{ route('pos.tables.qr-svg', $table->id) }}" class="min-h-[36px] px-2 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center justify-center gap-1.5 transition">
                        <i data-lucide="download" class="w-3.5 h-3.5 text-[#34C759]"></i>
                        <span>Unduh SVG</span>
                    </a>
                </div>

                @if(\App\Support\Context::hasPermission('pos.tables'))
                <div class="flex items-center justify-between gap-1 pt-1">
                    <button type="button" @click="openEdit({{ json_encode($table) }})" class="text-[12px] text-[#007AFF] hover:underline font-medium p-1">
                        Edit Meja
                    </button>

                    <button type="button" @click="confirmRegen({{ json_encode($table) }})" class="text-[12px] text-[#FF9500] hover:underline font-medium p-1">
                        Regenerate QR
                    </button>

                    @if($session && $session->canBeClosed())
                    <form method="POST" action="{{ route('pos.sessions.close', $session->id) }}" onsubmit="return confirm('Tutup sesi meja ini dan jadikan meja kembali tersedia?')">
                        @csrf
                        <button type="submit" class="text-[12px] text-[#34C759] hover:underline font-bold p-1">
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
    <!-- MODAL: TAMBAH MEJA BARU (Apple Modal Sheet)               -->
    <!-- ========================================================= -->
    @if(\App\Support\Context::hasPermission('pos.tables'))
    <div x-show="showAddModal" 
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4 overflow-y-auto"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="w-full max-w-md bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl rounded-[16px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)] space-y-4 text-black dark:text-white my-8" 
            @click.outside="showAddModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div>
                    <h3 class="font-bold text-[17px]">Tambah Meja Baru</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Daftarkan nomor meja dan kapasitas kursi</p>
                </div>
                <button type="button" @click="showAddModal = false" class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('pos.tables.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[12px] font-semibold mb-1 text-black/70 dark:text-white/70">Nomor / Kode Meja *</label>
                    <input type="text" name="table_number" required placeholder="Contoh: Meja 01, VIP-A" class="w-full h-11 sm:h-10 px-3.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold mb-1 text-black/70 dark:text-white/70">Keterangan / Nama Area (Opsional)</label>
                    <input type="text" name="name" placeholder="Contoh: Area Outdoor Lantai 2" class="w-full h-11 sm:h-10 px-3.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-semibold mb-1 text-black/70 dark:text-white/70">Kapasitas Kursi *</label>
                        <input type="number" name="capacity" min="1" max="100" value="4" required class="w-full h-11 sm:h-10 px-3.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[14px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold mb-1 text-black/70 dark:text-white/70">Outlet / Lokasi</label>
                        <select name="location_id" class="w-full h-11 sm:h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="">Semua Lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold mb-1 text-black/70 dark:text-white/70">Catatan Khusus</label>
                    <textarea name="notes" rows="2" placeholder="Catatan internal meja..." class="w-full p-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showAddModal = false" class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">Batal</button>
                    <button type="submit" class="min-h-[44px] sm:min-h-0 sm:h-9 px-5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white text-[13px] font-semibold shadow-[0_1px_2px_rgba(0,122,255,0.25)] transition-all">Simpan Meja</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ========================================================= -->
    <!-- MODAL: EDIT MEJA (Apple Modal Sheet)                      -->
    <!-- ========================================================= -->
    @if(\App\Support\Context::hasPermission('pos.tables'))
    <div x-show="showEditModal" 
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4 overflow-y-auto"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="w-full max-w-md bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl rounded-[16px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)] space-y-4 text-black dark:text-white my-8" 
            @click.outside="showEditModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div>
                    <h3 class="font-bold text-[17px]">Edit Meja</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Perbarui konfigurasi atau status unit meja</p>
                </div>
                <button type="button" @click="showEditModal = false" class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" :action="'{{ url('pos/tables') }}/' + editForm.id" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[12px] font-semibold mb-1 text-black/70 dark:text-white/70">Nomor / Kode Meja *</label>
                    <input type="text" name="table_number" x-model="editForm.table_number" required class="w-full h-11 sm:h-10 px-3.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div>
                    <label class="block text-[12px] font-semibold mb-1 text-black/70 dark:text-white/70">Keterangan / Area</label>
                    <input type="text" name="name" x-model="editForm.name" class="w-full h-11 sm:h-10 px-3.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-semibold mb-1 text-black/70 dark:text-white/70">Kapasitas Kursi *</label>
                        <input type="number" name="capacity" x-model.number="editForm.capacity" min="1" max="100" required class="w-full h-11 sm:h-10 px-3.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[14px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold mb-1 text-black/70 dark:text-white/70">Status Meja</label>
                        <select name="is_active" x-model="editForm.is_active" class="w-full h-11 sm:h-10 px-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            <option :value="true">Aktif</option>
                            <option :value="false">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold mb-1 text-black/70 dark:text-white/70">Catatan</label>
                    <textarea name="notes" x-model="editForm.notes" rows="2" class="w-full p-3 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                </div>

                <div class="flex justify-between items-center pt-3 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="
                        if(confirm('Yakin ingin menghapus meja ini?')) {
                            const f = document.createElement('form');
                            f.method = 'POST';
                            f.action = '{{ url('pos/tables') }}/' + editForm.id;
                            f.innerHTML = '<input type=\'hidden\' name=\'_token\' value=\'{{ csrf_token() }}\'><input type=\'hidden\' name=\'_method\' value=\'DELETE\'>';
                            document.body.appendChild(f);
                            f.submit();
                        }
                    " class="min-h-[44px] sm:min-h-0 text-[13px] text-[#FF3B30] hover:underline font-semibold flex items-center gap-1">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        <span>Hapus</span>
                    </button>

                    <div class="flex gap-2">
                        <button type="button" @click="showEditModal = false" class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">Batal</button>
                        <button type="submit" class="min-h-[44px] sm:min-h-0 sm:h-9 px-5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white text-[13px] font-semibold shadow-[0_1px_2px_rgba(0,122,255,0.25)] transition-all">Simpan Perubahan</button>
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
    <div x-show="showRegenModal" 
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="w-full max-w-sm bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl rounded-[16px] border border-black/5 dark:border-white/10 p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-center space-y-4 text-black dark:text-white" 
            @click.outside="showRegenModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <div class="w-12 h-12 rounded-full bg-[#FF9500]/15 text-[#FF9500] flex items-center justify-center mx-auto">
                <i data-lucide="refresh-cw" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="font-bold text-[17px]">Regenerate QR Code?</h3>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1">
                    Token QR Meja <strong x-text="regenTable ? regenTable.table_number : ''"></strong> akan diperbarui. Stiker atau kartu QR fisik yang lama tidak akan bisa digunakan lagi.
                </p>
            </div>

            <form method="POST" :action="'{{ url('pos/tables') }}/' + (regenTable ? regenTable.id : '') + '/regenerate-qr'" class="flex justify-center gap-2 pt-2">
                @csrf
                <button type="button" @click="showRegenModal = false" class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">Batal</button>
                <button type="submit" class="min-h-[44px] sm:min-h-0 sm:h-9 px-5 rounded-[10px] bg-[#FF9500] hover:bg-[#E08500] text-white text-[13px] font-semibold shadow-[0_1px_2px_rgba(255,149,0,0.25)] transition-all">Ya, Perbarui QR</button>
            </form>
        </div>
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endsection
