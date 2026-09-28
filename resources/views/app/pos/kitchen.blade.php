@extends('layouts.app', ['title' => 'Kitchen & Bar Display System'])

@section('content')
<div class="max-w-[1600px] mx-auto space-y-5 pb-16" x-data="kitchenDisplay()">

    <!-- Top Navigation & Controls -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md p-4 sm:p-5 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-[14px] bg-[#FF9500]/15 flex items-center justify-center text-[#FF9500] shrink-0">
                <i data-lucide="flame" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-[20px] font-bold tracking-tight text-black dark:text-white">Kitchen &amp; Bar Display</h1>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                        <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                        LIVE KDS
                    </span>
                </div>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-0.5">Pantau antrean pesanan dapur &amp; bar secara real-time dengan modifier &amp; catatan pelanggan.</p>
            </div>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <!-- Audio chime toggle -->
            <button type="button" @click="toggleSound()" class="min-h-[44px] h-11 px-3.5 rounded-[12px] text-[13px] font-semibold flex items-center gap-2 transition" :class="soundEnabled ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/30 shadow-xs' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.12]'">
                <i :data-lucide="soundEnabled ? 'volume-2' : 'volume-x'" class="w-4 h-4"></i>
                <span x-text="soundEnabled ? 'Suara Aktif' : 'Suara Mati'"></span>
            </button>

            <!-- Refresh button -->
            <button type="button" @click="fetchOrders(true)" class="min-h-[44px] h-11 px-3.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold flex items-center gap-2 transition active:scale-[0.98]">
                <i data-lucide="refresh-cw" class="w-4 h-4" :class="isLoading ? 'animate-spin' : ''"></i>
                <span>Segarkan</span>
            </button>

            <!-- Fullscreen toggle -->
            <button type="button" @click="toggleFullscreen()" class="min-h-[44px] h-11 px-3.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold flex items-center gap-2 transition active:scale-[0.98]">
                <i data-lucide="maximize" class="w-4 h-4"></i>
                <span>Layar Penuh</span>
            </button>

            <!-- Prep Sheet Link -->
            <a href="{{ route('pos.kitchen.prep_sheet') }}" class="min-h-[44px] h-11 px-3.5 rounded-[12px] bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white text-[13px] font-semibold flex items-center gap-2 transition shadow-sm">
                <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                <span>Lembar Prep</span>
            </a>

            @if(\App\Support\Context::hasPermission('pos.terminal') || \App\Support\Context::hasPermission('pos.orders'))
            <a href="{{ route('pos.terminal') }}" class="min-h-[44px] h-11 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[13px] font-semibold flex items-center gap-2 transition shadow-sm">
                <i data-lucide="layout-grid" class="w-4 h-4"></i>
                <span>Terminal Kasir</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Kanban Grid (3 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

        <!-- COLUMN 1: Pesanan Masuk (Confirmed) -->
        <div class="flex flex-col bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 shadow-sm min-h-[520px]">
            <div class="flex items-center justify-between pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08] mb-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-3.5 h-3.5 rounded-full bg-[#007AFF] shadow-xs"></span>
                    <h2 class="text-[15px] font-bold tracking-tight text-black dark:text-white">Pesanan Baru Masuk</h2>
                </div>
                <span class="text-[12px] font-bold px-2.5 py-0.5 rounded-full bg-[#007AFF]/15 text-[#007AFF]" x-text="confirmedOrders.length"></span>
            </div>

            <div class="space-y-3.5 flex-1 overflow-y-auto max-h-[calc(100vh-250px)] pr-1">
                <template x-for="order in confirmedOrders" :key="order.id">
                    <div class="p-4 rounded-[18px] bg-white dark:bg-[#2C2C2E] border-2 border-[#007AFF]/30 shadow-md space-y-3.5 transition hover:border-[#007AFF]">
                        <!-- Card Header -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-[8px] text-[12px] font-bold bg-[#007AFF]/15 text-[#007AFF]" x-text="order.pos_table ? ('Meja ' + order.pos_table.table_number) : 'Kasir / Takeaway'"></span>
                                    <template x-if="order.sales_channel && order.sales_channel !== 'dine_in'">
                                        <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-black uppercase tracking-wider text-white shadow-xs"
                                            :class="order.sales_channel === 'gofood' ? 'bg-[#00AA13]' : (order.sales_channel === 'grabfood' ? 'bg-[#00B14F]' : (order.sales_channel === 'shopeefood' ? 'bg-[#EE4D2D]' : 'bg-[#5856D6]'))"
                                            x-text="order.sales_channel + (order.external_order_ref ? ' #' + order.external_order_ref : '')"></span>
                                    </template>
                                    <span class="text-[12px] font-mono text-black/50 dark:text-white/50" x-text="'#' + (order.order_number || order.id)"></span>
                                </div>
                                <div class="text-[13px] font-bold text-black dark:text-white mt-1.5" x-text="(order.customer_name_guest || (order.customer ? order.customer.name : 'Pelanggan')) + (order.customer_phone_guest ? ' (' + order.customer_phone_guest + ')' : '')"></div>
                            </div>
                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-[8px]" :class="getTimeUrgencyClass(order.created_at)" x-text="formatTimeAgo(order.created_at)"></span>
                        </div>

                        <!-- General Order Note -->
                        <div x-show="order.notes" class="p-2.5 rounded-[12px] bg-[#FF9500]/10 border border-[#FF9500]/30 text-[12px] text-[#FF9500] font-medium flex items-start gap-2">
                            <i data-lucide="message-square" class="w-4 h-4 shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-bold">Catatan: </span>
                                <span x-text="order.notes"></span>
                            </div>
                        </div>

                        <!-- Items List -->
                        <div class="space-y-2 border-t border-b border-black/[0.06] dark:border-white/[0.08] py-3">
                            <template x-for="item in order.items" :key="item.id">
                                <div class="space-y-1">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-[15px] font-bold text-[#007AFF] tabular-nums" x-text="item.quantity + 'x'"></span>
                                            <span class="text-[13px] font-bold text-black dark:text-white" x-text="item.product_name"></span>
                                        </div>
                                    </div>

                                    <!-- Modifiers / Variants -->
                                    <div x-show="item.modifiers && item.modifiers.length > 0" class="pl-6 space-y-0.5">
                                        <template x-for="mod in item.modifiers" :key="mod.id">
                                            <div class="text-[12px] text-black/70 dark:text-white/70 flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>
                                                <span class="font-medium" x-text="mod.modifier_group_name + ': ' + mod.modifier_option_name"></span>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Item Note -->
                                    <div x-show="item.notes" class="pl-6">
                                        <span class="inline-block px-2 py-0.5 rounded-[6px] text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30" x-text="'Catatan: ' + item.notes"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Action Button -->
                        @if(\App\Support\Context::hasPermission('pos.kitchen'))
                        <button type="button" @click="updateStatus(order.id, 'preparing')" class="w-full min-h-[44px] h-11 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[13px] font-bold flex items-center justify-center gap-2 transition shadow-sm">
                            <i data-lucide="chef-hat" class="w-4 h-4"></i>
                            <span>Mulai Siapkan / Masak</span>
                        </button>
                        @endif
                    </div>
                </template>

                <div x-show="confirmedOrders.length === 0" class="text-center py-20 text-black/40 dark:text-white/40 text-[13px] space-y-2">
                    <i data-lucide="check-circle-2" class="w-8 h-8 mx-auto opacity-40"></i>
                    <div>Tidak ada antrean pesanan baru</div>
                </div>
            </div>
        </div>

        <!-- COLUMN 2: Sedang Disiapkan (Preparing) -->
        <div class="flex flex-col bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 shadow-sm min-h-[520px]">
            <div class="flex items-center justify-between pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08] mb-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-3.5 h-3.5 rounded-full bg-[#FF9500] shadow-xs"></span>
                    <h2 class="text-[15px] font-bold tracking-tight text-black dark:text-white">Sedang Dimasak / Diproses</h2>
                </div>
                <span class="text-[12px] font-bold px-2.5 py-0.5 rounded-full bg-[#FF9500]/15 text-[#FF9500]" x-text="preparingOrders.length"></span>
            </div>

            <div class="space-y-3.5 flex-1 overflow-y-auto max-h-[calc(100vh-250px)] pr-1">
                <template x-for="order in preparingOrders" :key="order.id">
                    <div class="p-4 rounded-[18px] bg-white dark:bg-[#2C2C2E] border-2 border-[#FF9500]/30 shadow-md space-y-3.5 transition hover:border-[#FF9500]">
                        <!-- Card Header -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-[8px] text-[12px] font-bold bg-[#FF9500]/15 text-[#FF9500]" x-text="order.pos_table ? ('Meja ' + order.pos_table.table_number) : 'Kasir / Takeaway'"></span>
                                    <template x-if="order.sales_channel && order.sales_channel !== 'dine_in'">
                                        <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-black uppercase tracking-wider text-white shadow-xs"
                                            :class="order.sales_channel === 'gofood' ? 'bg-[#00AA13]' : (order.sales_channel === 'grabfood' ? 'bg-[#00B14F]' : (order.sales_channel === 'shopeefood' ? 'bg-[#EE4D2D]' : 'bg-[#5856D6]'))"
                                            x-text="order.sales_channel + (order.external_order_ref ? ' #' + order.external_order_ref : '')"></span>
                                    </template>
                                    <span class="text-[12px] font-mono text-black/50 dark:text-white/50" x-text="'#' + (order.order_number || order.id)"></span>
                                </div>
                                <div class="text-[13px] font-bold text-black dark:text-white mt-1.5" x-text="(order.customer_name_guest || (order.customer ? order.customer.name : 'Pelanggan'))"></div>
                            </div>
                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-[8px]" :class="getTimeUrgencyClass(order.created_at)" x-text="formatTimeAgo(order.created_at)"></span>
                        </div>

                        <!-- General Order Note -->
                        <div x-show="order.notes" class="p-2.5 rounded-[12px] bg-[#FF9500]/10 border border-[#FF9500]/30 text-[12px] text-[#FF9500] font-medium flex items-start gap-2">
                            <i data-lucide="message-square" class="w-4 h-4 shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-bold">Catatan: </span>
                                <span x-text="order.notes"></span>
                            </div>
                        </div>

                        <!-- Items List -->
                        <div class="space-y-2 border-t border-b border-black/[0.06] dark:border-white/[0.08] py-3">
                            <template x-for="item in order.items" :key="item.id">
                                <div class="space-y-1">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-[15px] font-bold text-[#FF9500] tabular-nums" x-text="item.quantity + 'x'"></span>
                                            <span class="text-[13px] font-bold text-black dark:text-white" x-text="item.product_name"></span>
                                        </div>
                                    </div>

                                    <!-- Modifiers / Variants -->
                                    <div x-show="item.modifiers && item.modifiers.length > 0" class="pl-6 space-y-0.5">
                                        <template x-for="mod in item.modifiers" :key="mod.id">
                                            <div class="text-[12px] text-black/70 dark:text-white/70 flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                                <span class="font-medium" x-text="mod.modifier_group_name + ': ' + mod.modifier_option_name"></span>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Item Note -->
                                    <div x-show="item.notes" class="pl-6">
                                        <span class="inline-block px-2 py-0.5 rounded-[6px] text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30" x-text="'Catatan: ' + item.notes"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Action Button -->
                        @if(\App\Support\Context::hasPermission('pos.kitchen'))
                        <button type="button" @click="updateStatus(order.id, 'ready')" class="w-full min-h-[44px] h-11 rounded-[12px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-[13px] font-bold flex items-center justify-center gap-2 transition shadow-sm">
                            <i data-lucide="bell-ring" class="w-4 h-4"></i>
                            <span>Pesanan Siap Saji (Ready)</span>
                        </button>
                        @endif
                    </div>
                </template>

                <div x-show="preparingOrders.length === 0" class="text-center py-20 text-black/40 dark:text-white/40 text-[13px] space-y-2">
                    <i data-lucide="coffee" class="w-8 h-8 mx-auto opacity-40"></i>
                    <div>Tidak ada pesanan yang sedang disiapkan</div>
                </div>
            </div>
        </div>

        <!-- COLUMN 3: Siap Disajikan (Ready) -->
        <div class="flex flex-col bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 shadow-sm min-h-[520px]">
            <div class="flex items-center justify-between pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08] mb-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-3.5 h-3.5 rounded-full bg-[#34C759] shadow-xs"></span>
                    <h2 class="text-[15px] font-bold tracking-tight text-black dark:text-white">Siap Disajikan ke Meja</h2>
                </div>
                <span class="text-[12px] font-bold px-2.5 py-0.5 rounded-full bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]" x-text="readyOrders.length"></span>
            </div>

            <div class="space-y-3.5 flex-1 overflow-y-auto max-h-[calc(100vh-250px)] pr-1">
                <template x-for="order in readyOrders" :key="order.id">
                    <div class="p-4 rounded-[18px] bg-white dark:bg-[#2C2C2E] border-2 border-[#34C759]/30 shadow-md space-y-3.5 transition hover:border-[#34C759]">
                        <!-- Card Header -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-[8px] text-[12px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]" x-text="order.pos_table ? ('Meja ' + order.pos_table.table_number) : 'Kasir / Takeaway'"></span>
                                    <template x-if="order.sales_channel && order.sales_channel !== 'dine_in'">
                                        <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-black uppercase tracking-wider text-white shadow-xs"
                                            :class="order.sales_channel === 'gofood' ? 'bg-[#00AA13]' : (order.sales_channel === 'grabfood' ? 'bg-[#00B14F]' : (order.sales_channel === 'shopeefood' ? 'bg-[#EE4D2D]' : 'bg-[#5856D6]'))"
                                            x-text="order.sales_channel + (order.external_order_ref ? ' #' + order.external_order_ref : '')"></span>
                                    </template>
                                    <span class="text-[12px] font-mono text-black/50 dark:text-white/50" x-text="'#' + (order.order_number || order.id)"></span>
                                </div>
                                <div class="text-[13px] font-bold text-black dark:text-white mt-1.5" x-text="(order.customer_name_guest || (order.customer ? order.customer.name : 'Pelanggan'))"></div>
                            </div>
                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-[8px] bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]" x-text="formatTimeAgo(order.created_at)"></span>
                        </div>

                        <!-- Items summary -->
                        <div class="space-y-1.5 border-t border-b border-black/[0.06] dark:border-white/[0.08] py-3">
                            <template x-for="item in order.items" :key="item.id">
                                <div class="flex items-center justify-between text-[13px] text-black/80 dark:text-white/80">
                                    <span class="font-medium" x-text="item.product_name"></span>
                                    <span class="font-bold text-[#34C759] tabular-nums" x-text="item.quantity + 'x'"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Action Button -->
                        @if(\App\Support\Context::hasPermission('pos.kitchen'))
                        <button type="button" @click="updateStatus(order.id, 'served')" class="w-full min-h-[44px] h-11 rounded-[12px] bg-black/[0.08] dark:bg-white/[0.12] hover:bg-black/[0.15] dark:hover:bg-white/[0.2] active:scale-[0.98] text-black dark:text-white text-[13px] font-bold flex items-center justify-center gap-2 transition">
                            <i data-lucide="check-check" class="w-4 h-4 text-[#34C759]"></i>
                            <span>Tandai Sudah Disajikan (Served)</span>
                        </button>
                        @endif
                    </div>
                </template>

                <div x-show="readyOrders.length === 0" class="text-center py-20 text-black/40 dark:text-white/40 text-[13px] space-y-2">
                    <i data-lucide="sparkles" class="w-8 h-8 mx-auto opacity-40"></i>
                    <div>Belum ada pesanan siap saji</div>
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
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });

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
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
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
                const res = await fetch("{{ route('pos.kitchen.orders') }}", {
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
                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
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
                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                } else {
                    if (typeof AppAlert !== 'undefined') {
                        AppAlert.error(data.message || 'Gagal memperbarui status pesanan');
                    } else if (window.AppAlert) {
                        window.AppAlert.error(data.message || 'Gagal memperbarui status pesanan');
                    } else {
                        console.error('Kitchen update error:', data.message);
                    }
                }
            } catch (e) {
                console.error('Update status error:', e);
                if (typeof AppAlert !== 'undefined') {
                    AppAlert.error('Terjadi gangguan jaringan saat memperbarui status');
                }
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
