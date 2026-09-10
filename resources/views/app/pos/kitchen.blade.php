@extends('layouts.app', ['title' => 'Kitchen & Bar Display System'])

@section('content')
<div class="max-w-[1600px] mx-auto space-y-5 pb-12" x-data="kitchenDisplay()">

    <!-- Top Navigation & Controls -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-[#1C1C1E] p-4 rounded-[16px] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#FF9500]/10 dark:bg-[#FF9500]/20 flex items-center justify-center text-[#FF9500]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1A3.75 3.75 0 0012 18z"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold tracking-tight text-black dark:text-white">Kitchen &amp; Bar Display</h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse mr-1"></span>
                        LIVE
                    </span>
                </div>
                <p class="text-xs text-black/60 dark:text-white/60 mt-0.5">Pantau antrean pesanan dapur &amp; bar secara real-time dengan modifier &amp; catatan pelanggan.</p>
            </div>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <!-- Audio chime toggle -->
            <button type="button" @click="toggleSound()" class="h-9 px-3 rounded-[10px] text-xs font-semibold flex items-center gap-1.5 transition" :class="soundEnabled ? 'bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/30' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60'">
                <svg x-show="soundEnabled" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.757 3.63 8.25 4.51 8.25H6.75z"/></svg>
                <svg x-show="!soundEnabled" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75L19.5 12m0 0l2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-3.75l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.757 3.63 8.25 4.51 8.25H6.75z"/></svg>
                <span x-text="soundEnabled ? 'Suara Aktif' : 'Suara Mati'"></span>
            </button>

            <!-- Refresh button -->
            <button type="button" @click="fetchOrders(true)" class="h-9 px-3 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-xs font-semibold flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" :class="isLoading ? 'animate-spin' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                <span>Segarkan</span>
            </button>

            <!-- Fullscreen toggle -->
            <button type="button" @click="toggleFullscreen()" class="h-9 px-3 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-xs font-semibold flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/></svg>
                <span>Layar Penuh</span>
            </button>

            <a href="{{ route('pos.terminal') }}" class="h-9 px-3 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold flex items-center gap-1.5 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3"/></svg>
                <span>Terminal Kasir</span>
            </a>
        </div>
    </div>

    <!-- Kanban Grid (3 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

        <!-- COLUMN 1: Pesanan Masuk (Confirmed) -->
        <div class="flex flex-col bg-white/70 dark:bg-[#1C1C1E]/70 backdrop-blur-md rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] p-4 shadow-sm min-h-[500px]">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08] mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#007AFF]"></span>
                    <h2 class="text-sm font-bold tracking-tight text-black dark:text-white">Pesanan Baru Masuk</h2>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF]" x-text="confirmedOrders.length"></span>
            </div>

            <div class="space-y-3 flex-1 overflow-y-auto max-h-[calc(100vh-250px)] pr-1">
                <template x-for="order in confirmedOrders" :key="order.id">
                    <div class="p-4 rounded-[16px] bg-white dark:bg-[#2C2C2E] border-2 border-[#007AFF]/30 shadow-md space-y-3 transition hover:border-[#007AFF]">
                        <!-- Card Header -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-black/[0.05] dark:bg-white/[0.1] text-black dark:text-white" x-text="order.pos_table ? ('Meja ' + order.pos_table.table_number) : 'Kasir / Takeaway'"></span>
                                    <span class="text-[11px] font-mono text-black/50 dark:text-white/50" x-text="'#' + (order.order_number || order.id)"></span>
                                </div>
                                <div class="text-xs font-semibold text-black dark:text-white mt-1" x-text="(order.customer_name_guest || (order.customer ? order.customer.name : 'Pelanggan')) + (order.customer_phone_guest ? ' (' + order.customer_phone_guest + ')' : '')"></div>
                            </div>
                            <span class="text-[11px] font-medium px-2 py-0.5 rounded-md" :class="getTimeUrgencyClass(order.created_at)" x-text="formatTimeAgo(order.created_at)"></span>
                        </div>

                        <!-- General Order Note -->
                        <div x-show="order.notes" class="p-2 rounded-[10px] bg-[#FF9500]/10 border border-[#FF9500]/30 text-[11px] text-[#FF9500] font-medium flex items-start gap-1.5">
                            <span class="font-bold">💬 Pesan:</span>
                            <span x-text="order.notes"></span>
                        </div>

                        <!-- Items List -->
                        <div class="space-y-2 border-t border-b border-black/[0.06] dark:border-white/[0.08] py-2.5">
                            <template x-for="item in order.items" :key="item.id">
                                <div class="space-y-1">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-sm font-bold text-[#007AFF]" x-text="item.quantity + 'x'"></span>
                                            <span class="text-xs font-bold text-black dark:text-white" x-text="item.product_name"></span>
                                        </div>
                                    </div>

                                    <!-- Modifiers / Variants -->
                                    <div x-show="item.modifiers && item.modifiers.length > 0" class="pl-5 space-y-0.5">
                                        <template x-for="mod in item.modifiers" :key="mod.id">
                                            <div class="text-[11px] text-black/70 dark:text-white/70 flex items-center gap-1">
                                                <span class="w-1 h-1 rounded-full bg-[#007AFF]"></span>
                                                <span class="font-medium" x-text="mod.modifier_group_name + ': ' + mod.modifier_option_name"></span>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Item Note -->
                                    <div x-show="item.notes" class="pl-5">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30" x-text="'Catatan: ' + item.notes"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Action Button -->
                        <button type="button" @click="updateStatus(order.id, 'preparing')" class="w-full h-9 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold flex items-center justify-center gap-2 transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z"/></svg>
                            <span>Mulai Siapkan / Masak</span>
                        </button>
                    </div>
                </template>

                <div x-show="confirmedOrders.length === 0" class="text-center py-16 text-black/40 dark:text-white/40 text-xs">
                    <svg class="w-8 h-8 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Tidak ada pesanan antre</span>
                </div>
            </div>
        </div>

        <!-- COLUMN 2: Sedang Disiapkan (Preparing) -->
        <div class="flex flex-col bg-white/70 dark:bg-[#1C1C1E]/70 backdrop-blur-md rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] p-4 shadow-sm min-h-[500px]">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08] mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#FF9500]"></span>
                    <h2 class="text-sm font-bold tracking-tight text-black dark:text-white">Sedang Disiapkan / Dimasak</h2>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-[#FF9500]/10 text-[#FF9500]" x-text="preparingOrders.length"></span>
            </div>

            <div class="space-y-3 flex-1 overflow-y-auto max-h-[calc(100vh-250px)] pr-1">
                <template x-for="order in preparingOrders" :key="order.id">
                    <div class="p-4 rounded-[16px] bg-white dark:bg-[#2C2C2E] border-2 border-[#FF9500]/30 shadow-md space-y-3 transition hover:border-[#FF9500]">
                        <!-- Card Header -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#FF9500]/15 text-[#FF9500]" x-text="order.pos_table ? ('Meja ' + order.pos_table.table_number) : 'Kasir / Takeaway'"></span>
                                    <span class="text-[11px] font-mono text-black/50 dark:text-white/50" x-text="'#' + (order.order_number || order.id)"></span>
                                </div>
                                <div class="text-xs font-semibold text-black dark:text-white mt-1" x-text="(order.customer_name_guest || (order.customer ? order.customer.name : 'Pelanggan'))"></div>
                            </div>
                            <span class="text-[11px] font-medium px-2 py-0.5 rounded-md" :class="getTimeUrgencyClass(order.created_at)" x-text="formatTimeAgo(order.created_at)"></span>
                        </div>

                        <!-- General Order Note -->
                        <div x-show="order.notes" class="p-2 rounded-[10px] bg-[#FF9500]/10 border border-[#FF9500]/30 text-[11px] text-[#FF9500] font-medium flex items-start gap-1.5">
                            <span class="font-bold">💬 Pesan:</span>
                            <span x-text="order.notes"></span>
                        </div>

                        <!-- Items List -->
                        <div class="space-y-2 border-t border-b border-black/[0.06] dark:border-white/[0.08] py-2.5">
                            <template x-for="item in order.items" :key="item.id">
                                <div class="space-y-1">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-sm font-bold text-[#FF9500]" x-text="item.quantity + 'x'"></span>
                                            <span class="text-xs font-bold text-black dark:text-white" x-text="item.product_name"></span>
                                        </div>
                                    </div>

                                    <!-- Modifiers / Variants -->
                                    <div x-show="item.modifiers && item.modifiers.length > 0" class="pl-5 space-y-0.5">
                                        <template x-for="mod in item.modifiers" :key="mod.id">
                                            <div class="text-[11px] text-black/70 dark:text-white/70 flex items-center gap-1">
                                                <span class="w-1 h-1 rounded-full bg-[#FF9500]"></span>
                                                <span class="font-medium" x-text="mod.modifier_group_name + ': ' + mod.modifier_option_name"></span>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Item Note -->
                                    <div x-show="item.notes" class="pl-5">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30" x-text="'Catatan: ' + item.notes"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Action Button -->
                        <button type="button" @click="updateStatus(order.id, 'ready')" class="w-full h-9 rounded-[10px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-xs font-bold flex items-center justify-center gap-2 transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Pesanan Siap Saji</span>
                        </button>
                    </div>
                </template>

                <div x-show="preparingOrders.length === 0" class="text-center py-16 text-black/40 dark:text-white/40 text-xs">
                    <span>Tidak ada yang sedang disiapkan</span>
                </div>
            </div>
        </div>

        <!-- COLUMN 3: Siap Disajikan (Ready) -->
        <div class="flex flex-col bg-white/70 dark:bg-[#1C1C1E]/70 backdrop-blur-md rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] p-4 shadow-sm min-h-[500px]">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08] mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#34C759]"></span>
                    <h2 class="text-sm font-bold tracking-tight text-black dark:text-white">Siap Disajikan ke Meja</h2>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-[#34C759]/10 text-[#34C759]" x-text="readyOrders.length"></span>
            </div>

            <div class="space-y-3 flex-1 overflow-y-auto max-h-[calc(100vh-250px)] pr-1">
                <template x-for="order in readyOrders" :key="order.id">
                    <div class="p-4 rounded-[16px] bg-white dark:bg-[#2C2C2E] border-2 border-[#34C759]/30 shadow-md space-y-3 transition hover:border-[#34C759]">
                        <!-- Card Header -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#34C759]/15 text-[#34C759]" x-text="order.pos_table ? ('Meja ' + order.pos_table.table_number) : 'Kasir / Takeaway'"></span>
                                    <span class="text-[11px] font-mono text-black/50 dark:text-white/50" x-text="'#' + (order.order_number || order.id)"></span>
                                </div>
                                <div class="text-xs font-semibold text-black dark:text-white mt-1" x-text="(order.customer_name_guest || (order.customer ? order.customer.name : 'Pelanggan'))"></div>
                            </div>
                            <span class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-[#34C759]/10 text-[#34C759]" x-text="formatTimeAgo(order.created_at)"></span>
                        </div>

                        <!-- Items summary -->
                        <div class="space-y-1.5 border-t border-b border-black/[0.06] dark:border-white/[0.08] py-2">
                            <template x-for="item in order.items" :key="item.id">
                                <div class="flex items-center justify-between text-xs text-black/80 dark:text-white/80">
                                    <span class="font-medium" x-text="item.product_name"></span>
                                    <span class="font-bold text-[#34C759]" x-text="item.quantity + 'x'"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Action Button -->
                        <button type="button" @click="updateStatus(order.id, 'served')" class="w-full h-9 rounded-[10px] bg-black/[0.08] dark:bg-white/[0.12] hover:bg-black/[0.15] dark:hover:bg-white/[0.2] active:scale-[0.98] text-black dark:text-white text-xs font-bold flex items-center justify-center gap-2 transition">
                            <svg class="w-4 h-4 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>Tandai Sudah Disajikan</span>
                        </button>
                    </div>
                </template>

                <div x-show="readyOrders.length === 0" class="text-center py-16 text-black/40 dark:text-white/40 text-xs">
                    <span>Belum ada pesanan siap saji</span>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function kitchenDisplay() {
    return {
        orders: @json($activeOrders ?? []),
        isLoading: false,
        soundEnabled: false,
        audioCtx: null,
        pollInterval: null,
        previousConfirmedIds: new Set(),

        init() {
            // Pre-seed known confirmed IDs
            this.orders.filter(o => o.status === 'confirmed').forEach(o => this.previousConfirmedIds.add(o.id));

            // Setup polling every 5 seconds
            this.pollInterval = setInterval(() => {
                this.fetchOrders(false);
            }, 5000);
        },

        get confirmedOrders() {
            return this.orders.filter(o => o.status === 'confirmed');
        },

        get preparingOrders() {
            return this.orders.filter(o => o.status === 'preparing');
        },

        get readyOrders() {
            return this.orders.filter(o => o.status === 'ready');
        },

        toggleSound() {
            this.soundEnabled = !this.soundEnabled;
            if (this.soundEnabled) {
                this.playChime();
            }
        },

        playChime() {
            try {
                if (!this.audioCtx) {
                    this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (this.audioCtx.state === 'suspended') {
                    this.audioCtx.resume();
                }
                const now = this.audioCtx.currentTime;
                const osc = this.audioCtx.createOscillator();
                const gain = this.audioCtx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, now); // D5
                osc.frequency.exponentialRampToValueAtTime(880.00, now + 0.15); // A5

                gain.gain.setValueAtTime(0.3, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.5);

                osc.connect(gain);
                gain.connect(this.audioCtx.destination);

                osc.start(now);
                osc.stop(now + 0.5);
            } catch (e) {
                console.warn('Audio chime error:', e);
            }
        },

        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.error('Fullscreen error: ', err);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        },

        async fetchOrders(showSpinner = false) {
            if (showSpinner) this.isLoading = true;
            try {
                const res = await fetch("{{ route('pos.kitchen.active') }}", {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    // Check for newly incoming confirmed orders
                    const currentConfirmed = data.orders.filter(o => o.status === 'confirmed');
                    const hasNew = currentConfirmed.some(o => !this.previousConfirmedIds.has(o.id));
                    if (hasNew && this.soundEnabled) {
                        this.playChime();
                    }

                    // Update tracked IDs
                    this.previousConfirmedIds.clear();
                    currentConfirmed.forEach(o => this.previousConfirmedIds.add(o.id));

                    this.orders = data.orders;
                }
            } catch (e) {
                console.error('Failed to fetch kitchen orders:', e);
            } finally {
                if (showSpinner) this.isLoading = false;
            }
        },

        async updateStatus(orderId, newStatus) {
            try {
                const res = await fetch(`/pos/kitchen/${orderId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                const data = await res.json();
                if (data.success) {
                    if (newStatus === 'served') {
                        // Remove from active list
                        this.orders = this.orders.filter(o => o.id !== orderId);
                    } else {
                        // Update order object
                        const idx = this.orders.findIndex(o => o.id === orderId);
                        if (idx !== -1) {
                            this.orders[idx] = data.order;
                        }
                    }
                } else {
                    alert(data.message || 'Gagal memperbarui status');
                }
            } catch (e) {
                console.error('Update status error:', e);
            }
        },

        formatTimeAgo(dateStr) {
            if (!dateStr) return '';
            const diffMs = new Date() - new Date(dateStr);
            const diffMins = Math.floor(diffMs / 60000);
            if (diffMins < 1) return 'Baru saja';
            if (diffMins < 60) return `${diffMins} m lalu`;
            const hours = Math.floor(diffMins / 60);
            return `${hours} jam lalu`;
        },

        getTimeUrgencyClass(dateStr) {
            if (!dateStr) return 'bg-black/[0.05] dark:bg-white/[0.1] text-black/60 dark:text-white/60';
            const diffMins = Math.floor((new Date() - new Date(dateStr)) / 60000);
            if (diffMins > 20) {
                return 'bg-[#FF3B30]/10 text-[#FF3B30] font-bold';
            }
            if (diffMins > 10) {
                return 'bg-[#FF9500]/10 text-[#FF9500] font-bold';
            }
            return 'bg-black/[0.05] dark:bg-white/[0.1] text-black/60 dark:text-white/60';
        }
    };
}
</script>
@endpush
@endsection
