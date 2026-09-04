@extends('layouts.app', ['title' => 'Paket Langganan & Kuota Penggunaan'])

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span
                        class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-500/20 text-purple-400 border border-purple-500/30">
                        SAAS SUBSCRIPTION & ENTITLEMENT
                    </span>
                    <span class="text-xs text-slate-400 font-mono">cooca.id Billing</span>
                </div>
                <h1 class="text-2xl font-black text-white tracking-tight mt-1">Paket Langganan & Kuota Bisnis</h1>
                <p class="text-xs text-slate-400 mt-0.5">Pantau kapasitas sumber daya bisnis Anda dan nikmati fitur tanpa
                    batas dengan Cooca UMKM.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('billing.history') }}"
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                    <i data-lucide="receipt" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span>Riwayat Tagihan</span>
                </a>
                @if ($usage['is_core'])
                    <span
                        class="px-3.5 py-1.5 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-black flex items-center gap-1.5">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span>{{ $usage['plan_label'] }} (Aktif)</span>
                    </span>
                @else
                    <span
                        class="px-3.5 py-1.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-xs font-bold flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
                        <span>Paket Free (Solo UMKM)</span>
                    </span>
                @endif
            </div>
        </div>

        <!-- Alert / No Data Punishment Banner -->
        <div class="glass-card rounded-2xl p-4 border border-emerald-500/30 bg-emerald-950/20 flex items-start gap-3">
            <div class="p-2 rounded-xl bg-emerald-500/20 text-emerald-400 shrink-0">
                <i data-lucide="heart-handshake" class="w-5 h-5"></i>
            </div>
            <div class="text-xs space-y-1">
                <h4 class="font-bold text-white">Komitmen Privasi: No Data Punishment</h4>
                <p class="text-slate-300">Data bisnis Anda adalah hak milik Anda sepenuhnya. Jika langganan berakhir atau
                    mencapai kuota, Cooca UMKM <strong class="text-emerald-400">tidak akan pernah menghapus atau mengunci
                        data lama Anda</strong>. Seluruh data historis tetap dapat dilihat dan dibaca kapan saja.</p>
            </div>
        </div>

        <!-- Pricing Upgrade Card -->
        @if (!$usage['is_core'])
            <div
                class="glass-card rounded-3xl p-8 border border-emerald-500/30 bg-gradient-to-br from-emerald-950/30 via-slate-900 to-slate-950 space-y-6">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div class="space-y-2">
                        <span
                            class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            PROGRAM PATUNGAN COOCA UMKM
                        </span>
                        <h3 class="text-2xl font-black text-white">Buka Seluruh Potensi Bisnis Anda Tanpa Batas</h3>
                        <p class="text-xs text-slate-400 max-w-xl">
                            Dapatkan katalog produk unlimited, multi-gudang, transaksi tanpa batas, integrasi WhatsApp,
                            import/export data Excel lengkap mulai dari Rp
                            {{ number_format($monthlyPrice, 0, ',', '.') }}/bulan.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 shrink-0">
                        <a href="{{ route('billing.checkout', ['cycle' => 'monthly']) }}"
                            class="px-5 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 text-xs font-black shadow-xl shadow-emerald-500/20 transition flex items-center gap-2">
                            <i data-lucide="zap" class="w-4 h-4"></i>
                            <span>Ikut Patungan Bulanan (Rp {{ number_format($monthlyPrice, 0, ',', '.') }}/bln)</span>
                        </a>

                        <a href="{{ route('billing.checkout', ['cycle' => 'annual']) }}"
                            class="px-5 py-3 rounded-2xl bg-teal-400 hover:bg-teal-300 text-slate-950 text-xs font-black shadow-xl shadow-teal-500/20 transition flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            <span>Patungan Tahunan {{ $annualDiscountBadge }} (Rp
                                {{ number_format($annualPrice, 0, ',', '.') }}/thn)</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Storage & AI Hero Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Storage Card -->
            <div
                class="glass-card rounded-2xl p-5 border border-cyan-500/30 bg-cyan-950/10 flex flex-col justify-between gap-4">
                <div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-cyan-300 text-xs font-bold uppercase tracking-wider">
                            <i data-lucide="hard-drive" class="w-4 h-4"></i> Storage Cloud Owner
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300">Pool
                            Gabungan</span>
                    </div>
                    <div class="flex items-baseline gap-2 mt-3">
                        <span
                            class="text-2xl font-black font-mono text-white">{{ number_format($usage['storage']['used_mb'] ?? 0, 1, ',', '.') }}
                            MB</span>
                        <span class="text-xs text-slate-400 font-mono">/
                            {{ number_format($usage['storage']['limit_gb'] ?? 1, 1, ',', '.') }} GB</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden mt-2">
                        <div class="bg-cyan-400 h-full rounded-full transition-all"
                            style="width: {{ $usage['storage']['percentage'] ?? 0 }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2">Kapasitas penyimpanan lampiran, struk, dan foto untuk seluruh
                        bisnis milik Anda.</p>
                </div>
                <div class="flex justify-end">
                    <a href="{{ route('billing.checkout', ['type' => 'storage']) }}"
                        class="px-3.5 py-1.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-black flex items-center gap-1.5 shadow-lg shadow-cyan-500/20 transition">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Top Up Storage
                    </a>
                </div>
            </div>

            <!-- AI Tokens Card -->
            <div
                class="glass-card rounded-2xl p-5 border border-amber-500/30 bg-amber-950/10 flex flex-col justify-between gap-4">
                <div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-amber-300 text-xs font-bold uppercase tracking-wider">
                            <i data-lucide="bot" class="w-4 h-4"></i> Token Asisten AI (Gemini 2.5)
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300">Top-up
                            Token</span>
                    </div>
                    <div class="flex items-baseline gap-2 mt-3">
                        <span
                            class="text-2xl font-black font-mono text-white">{{ number_format($usage['ai_tokens']['remaining'] ?? 0, 0, ',', '.') }}</span>
                        <span class="text-xs text-slate-400 font-mono">token tersisa</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden mt-2">
                        <div class="bg-amber-400 h-full rounded-full transition-all"
                            style="width: {{ $usage['ai_tokens']['percent'] ?? 0 }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2">Digunakan untuk peramalan tren penjualan kasir, AI kalkulasi
                        HPP, dan asisten pintar.</p>
                </div>
                <div class="flex justify-end">
                    <a href="{{ route('billing.checkout', ['type' => 'ai_token']) }}"
                        class="px-3.5 py-1.5 rounded-xl border border-amber-500/40 text-amber-300 hover:bg-amber-500/10 text-xs font-bold flex items-center gap-1.5 transition">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Top Up Token AI
                    </a>
                </div>
            </div>
        </div>

        <!-- Section 1: Transaksi Bulanan (PO, Invoices, POS) -->
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <i data-lucide="calendar" class="w-4 h-4 text-purple-400"></i>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-300">Kuota Transaksi Bulanan (Reset
                    Otomatis)</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- POS Orders -->
                <div class="glass-card rounded-2xl p-5 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Transaksi Kasir POS</span>
                        <i data-lucide="shopping-cart" class="w-4 h-4 text-emerald-400"></i>
                    </div>
                    <div>
                        <div class="flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono text-white">{{ $usage['pos_this_month']['used'] ?? 0 }}</span>
                            <span class="text-xs text-slate-400 font-mono">/
                                {{ $usage['pos_this_month']['limit'] ? $usage['pos_this_month']['limit'] . ' trx' : '∞ Unlimited' }}</span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-emerald-500 h-full rounded-full transition-all"
                            style="width: {{ $usage['pos_this_month']['percent'] ?? 0 }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500">Maks. 100 struk per bulan pada paket Free.</p>
                </div>

                <!-- Invoices -->
                <div class="glass-card rounded-2xl p-5 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Faktur Penjualan (B2B)</span>
                        <i data-lucide="receipt" class="w-4 h-4 text-purple-400"></i>
                    </div>
                    <div>
                        <div class="flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono text-white">{{ $usage['invoices_this_month']['used'] ?? 0 }}</span>
                            <span class="text-xs text-slate-400 font-mono">/
                                {{ $usage['invoices_this_month']['limit'] ? $usage['invoices_this_month']['limit'] . ' faktur' : '∞ Unlimited' }}</span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-purple-500 h-full rounded-full transition-all"
                            style="width: {{ $usage['invoices_this_month']['percent'] ?? 0 }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500">Maks. 10 faktur per bulan pada paket Free.</p>
                </div>

                <!-- Purchase Orders -->
                <div class="glass-card rounded-2xl p-5 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Purchase Order (PO)</span>
                        <i data-lucide="clipboard-list" class="w-4 h-4 text-cyan-400"></i>
                    </div>
                    <div>
                        <div class="flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono text-white">{{ $usage['po_this_month']['used'] ?? 0 }}</span>
                            <span class="text-xs text-slate-400 font-mono">/
                                {{ $usage['po_this_month']['limit'] ? $usage['po_this_month']['limit'] . ' PO' : '∞ Unlimited' }}</span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-cyan-500 h-full rounded-full transition-all"
                            style="width: {{ $usage['po_this_month']['percent'] ?? 0 }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500">Maks. 10 PO pembelian per bulan pada paket Free.</p>
                </div>
            </div>
        </div>

        <!-- Section 2: Master Data & Katalog -->
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <i data-lucide="database" class="w-4 h-4 text-cyan-400"></i>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-300">Master Data & Kapasitas Katalog</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- 1. Produk -->
                <div class="glass-card rounded-2xl p-4 border border-slate-800 space-y-2.5">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Katalog Produk</span>
                        <i data-lucide="box" class="w-4 h-4 text-slate-500"></i>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span
                            class="text-2xl font-black font-mono text-white">{{ $usage['products']['used'] ?? 0 }}</span>
                        <span class="text-xs text-slate-400 font-mono">/
                            {{ $usage['products']['limit'] ? $usage['products']['limit'] . ' item' : '∞ Unlimited' }}</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-emerald-500 h-full rounded-full transition-all"
                            style="width: {{ $usage['products']['percent'] ?? 0 }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500">Maks. 50 produk pada paket Free.</p>
                </div>

                <!-- 2. Bahan Baku -->
                <div class="glass-card rounded-2xl p-4 border border-slate-800 space-y-2.5">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Bahan Baku</span>
                        <i data-lucide="layers" class="w-4 h-4 text-slate-500"></i>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span
                            class="text-2xl font-black font-mono text-white">{{ $usage['materials']['used'] ?? 0 }}</span>
                        <span class="text-xs text-slate-400 font-mono">/
                            {{ $usage['materials']['limit'] ? $usage['materials']['limit'] . ' item' : '∞ Unlimited' }}</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-cyan-500 h-full rounded-full transition-all"
                            style="width: {{ $usage['materials']['percent'] ?? 0 }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500">Maks. 20 bahan baku pada paket Free.</p>
                </div>

                <!-- 3. Resep HPP (BOM) -->
                <div class="glass-card rounded-2xl p-4 border border-slate-800 space-y-2.5">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Resep HPP (BOM)</span>
                        <i data-lucide="chef-hat" class="w-4 h-4 text-slate-500"></i>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-black font-mono text-white">{{ $usage['recipes']['used'] ?? 0 }}</span>
                        <span class="text-xs text-slate-400 font-mono">/
                            {{ $usage['recipes']['limit'] ? $usage['recipes']['limit'] . ' resep' : '∞ Unlimited' }}</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-amber-500 h-full rounded-full transition-all"
                            style="width: {{ $usage['recipes']['percent'] ?? 0 }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500">Maks. 20 resep HPP pada paket Free.</p>
                </div>

                <!-- 4. Pelanggan CRM -->
                <div class="glass-card rounded-2xl p-4 border border-slate-800 space-y-2.5">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Pelanggan CRM</span>
                        <i data-lucide="users" class="w-4 h-4 text-slate-500"></i>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span
                            class="text-2xl font-black font-mono text-white">{{ $usage['customers']['used'] ?? 0 }}</span>
                        <span class="text-xs text-slate-400 font-mono">/
                            {{ $usage['customers']['limit'] ? $usage['customers']['limit'] . ' kontak' : '∞ Unlimited' }}</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-indigo-500 h-full rounded-full transition-all"
                            style="width: {{ $usage['customers']['percent'] ?? 0 }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500">Maks. 30 pelanggan pada paket Free.</p>
                </div>

                <!-- 5. Supplier -->
                <div class="glass-card rounded-2xl p-4 border border-slate-800 space-y-2.5">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Pemasok / Supplier</span>
                        <i data-lucide="truck" class="w-4 h-4 text-slate-500"></i>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span
                            class="text-2xl font-black font-mono text-white">{{ $usage['suppliers']['used'] ?? 0 }}</span>
                        <span class="text-xs text-slate-400 font-mono">/
                            {{ $usage['suppliers']['limit'] ? $usage['suppliers']['limit'] . ' vendor' : '∞ Unlimited' }}</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-rose-500 h-full rounded-full transition-all"
                            style="width: {{ $usage['suppliers']['percent'] ?? 0 }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500">Maks. 20 vendor pada paket Free.</p>
                </div>

                <!-- 6. Outlet & Gudang -->
                <div class="glass-card rounded-2xl p-4 border border-slate-800 space-y-2.5">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Outlet & Gudang</span>
                        <i data-lucide="store" class="w-4 h-4 text-slate-500"></i>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span
                            class="text-2xl font-black font-mono text-white">{{ ($usage['outlets']['used'] ?? 0) + ($usage['warehouses']['used'] ?? 0) }}</span>
                        <span class="text-xs text-slate-400 font-mono">/
                            {{ $usage['is_core'] ? '∞ Unlimited' : '1 Outlet + 1 Gudang' }}</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-teal-500 h-full rounded-full transition-all"
                            style="width: {{ $usage['outlets']['percent'] ?? 0 }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500">Maks. 1 outlet + 1 gudang pada paket Free.</p>
                </div>

                <!-- 7. Pengguna / Karyawan -->
                <div class="glass-card rounded-2xl p-4 border border-slate-800 space-y-2.5">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Karyawan / Akun</span>
                        <i data-lucide="user-check" class="w-4 h-4 text-slate-500"></i>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-black font-mono text-white">{{ $usage['users']['used'] ?? 0 }}</span>
                        <span class="text-xs text-slate-400 font-mono">/
                            {{ $usage['users']['limit'] ? $usage['users']['limit'] . ' user' : '∞ Unlimited' }}</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-blue-500 h-full rounded-full transition-all"
                            style="width: {{ $usage['users']['percent'] ?? 0 }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500">Solo Owner pada paket Free (unlimited di Core).</p>
                </div>

                <!-- 8. Bisnis / Cabang -->
                <div class="glass-card rounded-2xl p-4 border border-slate-800 space-y-2.5">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Entitas Bisnis</span>
                        <i data-lucide="building-2" class="w-4 h-4 text-slate-500"></i>
                    </div>
                    <div class="flex items-baseline gap-1.5">
                        <span
                            class="text-2xl font-black font-mono text-white">{{ $usage['businesses']['used'] ?? 1 }}</span>
                        <span class="text-xs text-slate-400 font-mono">/
                            {{ $usage['businesses']['limit'] ? $usage['businesses']['limit'] . ' entitas' : '∞ Unlimited' }}</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-violet-500 h-full rounded-full transition-all"
                            style="width: {{ $usage['businesses']['percent'] ?? 0 }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500">1 bisnis pada paket Free (multi-tenant di Core).</p>
                </div>
            </div>
        </div>


    </div>
@endsection
