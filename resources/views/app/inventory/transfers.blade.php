@extends('layouts.app', [
    'title' => 'Transfer Stok Antar Outlet',
    'headerTitle' => 'Transfer Stok Antar Outlet',
    'headerSubtitle' => 'Kirim dan terima barang antar outlet atau gudang pusat dengan pencatatan mutasi otomatis'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showCreateModal: false,
    receiveModalOpen: false,
    receiveTarget: { id: null, number: '' },
    items: [
        @if($products->count() > 0)
            { product_id: '{{ $products->first()->id }}', quantity: 1 }
        @endif
    ],
    addItem() {
        this.items.push({ product_id: '{{ $products->first()?->id ?? '' }}', quantity: 1 });
    },
    removeItem(idx) {
        if (this.items.length > 1) {
            this.items.splice(idx, 1);
        }
    },
    openReceive(id, number) {
        this.receiveTarget = { id, number };
        this.receiveModalOpen = true;
    },
    closeReceive() {
        this.receiveModalOpen = false;
        this.receiveTarget = { id: null, number: '' };
    },
    submitReceive() {
        if (this.receiveTarget.id) {
            document.getElementById('form-receive-' + this.receiveTarget.id).submit();
        }
    }
}">
    
    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <a href="{{ route('inventory.stocks') }}" class="hover:text-[#007AFF] transition-colors">Inventori</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Transfer Stok</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Transfer Stok Antar Outlet</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Kirim dan terima barang antar cabang atau gudang pusat dengan mutasi otomatis</p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('inventory.stocks') }}" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Saldo Stok</span>
            </a>

            <button type="button" @click="showCreateModal = true" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Kirim Transfer Baru</span>
            </button>
        </div>
    </header>

    <!-- Session Banner -->
    @if(session('success'))
    <div class="rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2.5">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 2. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 whitespace-nowrap">
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">No. Transfer</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Tanggal</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Rute (Asal → Tujuan)</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Pengirim / Penerima</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Item</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Status</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($transfers as $tr)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-semibold text-black dark:text-white tabular-nums">#{{ $tr->transfer_number }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">{{ $tr->notes ?? 'Mutasi internal' }}</div>
                        </td>
                        <td class="py-3 px-4 tabular-nums text-black/60 dark:text-white/60">
                            {{ $tr->transfer_date->format('d/m/Y') }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-medium text-black dark:text-white flex items-center gap-1.5">
                                <span>{{ $tr->sourceLocation->name ?? 'Asal' }}</span>
                                <span class="text-[#007AFF] font-bold">→</span>
                                <span>{{ $tr->destinationLocation->name ?? 'Tujuan' }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-black/70 dark:text-white/70 text-[12px]">
                            <div>Kirim: <span class="font-medium text-black dark:text-white">{{ $tr->creator->name ?? '-' }}</span></div>
                            <div class="text-[11px] text-black/45 dark:text-white/45">Terima: {{ $tr->receiver->name ?? 'Belum' }}</div>
                        </td>
                        <td class="py-3 px-4 text-center tabular-nums font-semibold text-black dark:text-white">
                            {{ $tr->items->count() }} Produk
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($tr->status === 'received')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Diterima
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> Dalam Pengiriman
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            @if($tr->status !== 'received')
                                <button type="button" @click="openReceive('{{ $tr->id }}', '{{ $tr->transfer_number }}')" class="h-7 px-2.5 rounded-[6px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all">
                                    Konfirmasi Terima
                                </button>
                                <form id="form-receive-{{ $tr->id }}" action="{{ route('inventory.transfers.receive', $tr->id) }}" method="POST" class="hidden">
                                    @csrf
                                </form>
                            @else
                                <span class="text-black/40 dark:text-white/40 text-[12px]">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-black/40 dark:text-white/40">Belum ada transfer stok antar outlet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
        <div class="px-4 py-3 border-t border-black/5 dark:border-white/10">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 3. MOBILE GROUPED INSET LIST (Standar iOS List View)   -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
        @forelse($transfers as $tr)
        <div class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.02] dark:active:bg-white/[0.04] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                    <p class="text-[15px] font-semibold text-black dark:text-white tabular-nums">#{{ $tr->transfer_number }}</p>
                    <span class="w-1.5 h-1.5 rounded-full {{ $tr->status === 'received' ? 'bg-[#34C759]' : 'bg-[#FF9500]' }} shrink-0"></span>
                </div>
                <p class="text-[13px] text-black/70 dark:text-white/70 mt-0.5">
                    {{ $tr->sourceLocation->name ?? 'Asal' }} <span class="text-[#007AFF]">→</span> {{ $tr->destinationLocation->name ?? 'Tujuan' }}
                </p>
                <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5 tabular-nums">
                    {{ $tr->transfer_date->format('d/m/Y') }} · {{ $tr->items->count() }} produk ditransfer
                </p>
            </div>
            <div class="shrink-0">
                @if($tr->status !== 'received')
                    <button type="button" @click="openReceive('{{ $tr->id }}', '{{ $tr->transfer_number }}')" class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-white bg-[#007AFF] active:scale-[0.97] transition-all">
                        Terima
                    </button>
                    <form id="form-receive-{{ $tr->id }}" action="{{ route('inventory.transfers.receive', $tr->id) }}" method="POST" class="hidden">
                        @csrf
                    </form>
                @else
                    <span class="px-2 py-1 rounded-full text-[11px] font-medium bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                        Selesai
                    </span>
                @endif
            </div>
        </div>
        @empty
        <div class="py-8 text-center text-black/40 dark:text-white/40 text-[13px]">Belum ada transfer stok antar outlet.</div>
        @endforelse

        @if($transfers->hasPages())
        <div class="p-3 border-t border-black/5 dark:border-white/10">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 4. APPLE SHEET: KIRIM TRANSFER STOK BARU              -->
    <!-- ===================================================== -->
    <div x-show="showCreateModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/25 backdrop-blur-[2px] p-0 sm:p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-full sm:max-w-xl rounded-t-[20px] sm:rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] max-h-[90vh] overflow-y-auto"
            @click.away="showCreateModal = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
            x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
            x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0">

            <!-- Mobile Grabber Handle -->
            <div class="sm:hidden w-10 h-1 rounded-full bg-black/20 dark:bg-white/20 mx-auto mb-2"></div>

            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <h3 class="text-[17px] font-semibold text-black dark:text-white">Kirim Transfer Stok Baru</h3>
                <button type="button" @click="showCreateModal = false" class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('inventory.transfers.store') }}" method="POST" class="space-y-4 text-[13px]">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Lokasi Asal (Pengirim) *</label>
                        <select name="source_location_id" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Lokasi Tujuan (Penerima) *</label>
                        <select name="destination_location_id" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ $loop->last ? 'selected' : '' }}>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Tanggal Transfer *</label>
                        <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Catatan / No. Surat Jalan</label>
                        <input type="text" name="notes" placeholder="Misal: Surat Jalan #SJ-01..." class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <!-- Items Row -->
                <div class="space-y-2.5 pt-3 border-t border-black/5 dark:border-white/10">
                    <div class="flex items-center justify-between">
                        <label class="text-[12px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">Produk yang Ditransfer:</label>
                        <button type="button" @click="addItem()" class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Tambah Produk</span>
                        </button>
                    </div>

                    <template x-for="(item, idx) in items" :key="idx">
                        <div class="flex items-center gap-2">
                            <select :name="'items[' + idx + '][product_id]'" x-model="item.product_id" required class="flex-1 h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" step="any" min="0.0001" :name="'items[' + idx + '][quantity]'" x-model.number="item.quantity" required placeholder="Qty" class="h-9 w-24 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] px-2 text-[13px] text-center tabular-nums font-semibold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <button type="button" @click="removeItem(idx)" :disabled="items.length <= 1" class="p-1.5 text-black/30 hover:text-[#FF3B30] dark:text-white/30 dark:hover:text-[#FF453A] disabled:opacity-20 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showCreateModal = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] transition-all">
                        Batal
                    </button>
                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        Kirim Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 5. APPLE ALERT DIALOG: KONFIRMASI TERIMA TRANSFER     -->
    <!-- ===================================================== -->
    <div x-show="receiveModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="closeReceive()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Konfirmasi Terima?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    Konfirmasi bahwa barang transfer <span x-text="'#' + receiveTarget.number" class="font-medium text-black dark:text-white tabular-nums"></span> sudah sampai dan diterima di lokasi tujuan.
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="closeReceive()" class="py-3 text-black/70 dark:text-white/70 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitReceive()" class="py-3 text-[#007AFF] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Konfirmasi
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
