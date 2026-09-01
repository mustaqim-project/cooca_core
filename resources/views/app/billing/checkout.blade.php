@extends('layouts.app', [
    'title' => 'Checkout Langganan — Cooca Core',
    'headerTitle' => 'Pilih Metode Pembayaran',
    'headerSubtitle' => 'Tingkatkan bisnis Anda ke Cooca Core dengan akses tanpa batas dan 10 Juta Token AI'
])

@section('content')
<div class="max-w-5xl mx-auto space-y-8" x-data="{
    cycle: '{{ $cycle }}',
    paymentMethod: 'bca',
    monthlyPrice: 129000,
    annualPrice: 1290000,
    get currentPrice() {
        return this.cycle === 'annual' ? this.annualPrice : this.monthlyPrice;
    },
    formatRupiah(val) {
        return new Intl.NumberFormat('id-ID').format(val);
    }
}">

    <!-- Top Navigation Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('billing.limits') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Paket & Kuota</span>
        </a>
        <a href="{{ route('billing.history') }}" class="text-xs text-emerald-400 hover:underline flex items-center gap-1">
            <i data-lucide="history" class="w-3.5 h-3.5"></i>
            <span>Riwayat Pembayaran</span>
        </a>
    </div>

    <!-- Main Checkout Grid -->
    <form method="POST" action="{{ route('billing.order.store') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        @csrf
        <input type="hidden" name="cycle" :value="cycle">
        <input type="hidden" name="payment_method" :value="paymentMethod">

        <!-- Left Column: Plan Selector & Payment Method (2 cols) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- 1. Billing Cycle Selector Card -->
            <div class="glass-card rounded-3xl p-6 border border-slate-800 space-y-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-black text-white flex items-center gap-2">
                        <i data-lucide="calendar" class="w-5 h-5 text-emerald-400"></i>
                        <span>Pilih Periode Langganan</span>
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-500/20 text-purple-300 border border-purple-500/30">
                        Hemat Biaya
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Bulanan Option -->
                    <div @click="cycle = 'monthly'"
                         class="cursor-pointer rounded-2xl p-5 border transition-all relative overflow-hidden"
                         :class="cycle === 'monthly' ? 'bg-purple-950/40 border-purple-500 shadow-lg shadow-purple-500/10' : 'bg-slate-900/60 border-slate-800 hover:border-slate-700'">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-black text-white uppercase tracking-wider">Bulanan</span>
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                 :class="cycle === 'monthly' ? 'border-purple-400 bg-purple-500' : 'border-slate-600'">
                                <div x-show="cycle === 'monthly'" class="w-1.5 h-1.5 rounded-full bg-white"></div>
                            </div>
                        </div>
                        <div class="text-2xl font-black text-white font-mono">Rp 129.000</div>
                        <p class="text-[11px] text-slate-400 mt-1">Ditagih setiap bulan, fleksibel dibatalkan kapan saja.</p>
                    </div>

                    <!-- Tahunan Option -->
                    <div @click="cycle = 'annual'"
                         class="cursor-pointer rounded-2xl p-5 border transition-all relative overflow-hidden"
                         :class="cycle === 'annual' ? 'bg-emerald-950/40 border-emerald-500 shadow-lg shadow-emerald-500/10' : 'bg-slate-900/60 border-slate-800 hover:border-slate-700'">
                        <div class="absolute top-2 right-2 px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-emerald-500 text-slate-950">
                            Hemat 2 Bulan
                        </div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-black text-white uppercase tracking-wider">Tahunan</span>
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center mr-16"
                                 :class="cycle === 'annual' ? 'border-emerald-400 bg-emerald-500' : 'border-slate-600'">
                                <div x-show="cycle === 'annual'" class="w-1.5 h-1.5 rounded-full bg-white"></div>
                            </div>
                        </div>
                        <div class="text-2xl font-black text-emerald-400 font-mono">Rp 1.290.000</div>
                        <p class="text-[11px] text-slate-400 mt-1">Setara Rp 107.500/bln. Hemat Rp 258.000 per tahun.</p>
                    </div>
                </div>
            </div>

            <!-- 2. Payment Method Selector -->
            <div class="glass-card rounded-3xl p-6 border border-slate-800 space-y-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-black text-white flex items-center gap-2">
                        <i data-lucide="wallet" class="w-5 h-5 text-cyan-400"></i>
                        <span>Metode Pembayaran Resmi</span>
                    </h3>
                    <span class="text-xs text-slate-400 font-mono">Verifikasi Manual Cepat</span>
                </div>

                <div class="space-y-3">
                    @foreach($paymentMethods as $code => $m)
                    <div @click="paymentMethod = '{{ $code }}'"
                         class="cursor-pointer rounded-2xl p-4 border transition-all flex items-center justify-between"
                         :class="paymentMethod === '{{ $code }}' ? 'bg-slate-900 border-indigo-500 shadow-md' : 'bg-slate-950/60 border-slate-800/80 hover:border-slate-700'">
                        
                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center shrink-0">
                                @if($code === 'qris')
                                    <i data-lucide="qr-code" class="w-6 h-6 text-emerald-400"></i>
                                @else
                                    <i data-lucide="credit-card" class="w-6 h-6 text-indigo-400"></i>
                                @endif
                            </div>
                            <div>
                                <div class="font-bold text-sm text-white flex items-center gap-2">
                                    <span>{{ $m['name'] }}</span>
                                    @if($code === 'qris')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                            Instant / e-Wallet
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $m['bank_name'] }} • {{ $m['account_number'] }}</div>
                            </div>
                        </div>

                        <div class="w-5 h-5 rounded-full border flex items-center justify-center shrink-0"
                             :class="paymentMethod === '{{ $code }}' ? 'border-indigo-400 bg-indigo-500' : 'border-slate-700'">
                            <div x-show="paymentMethod === '{{ $code }}'" class="w-2 h-2 rounded-full bg-white"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- Right Column: Order Summary & Action (1 col) -->
        <div class="space-y-6">
            <div class="glass-card rounded-3xl p-6 border border-slate-800 space-y-6 sticky top-24">
                <div class="border-b border-slate-800 pb-4">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Ringkasan Pesanan</span>
                    <h4 class="text-lg font-black text-white mt-1">Cooca Core License</h4>
                    <p class="text-xs text-emerald-400 font-medium">Bisnis: {{ $business->name }}</p>
                </div>

                <!-- Features bullet list -->
                <div class="space-y-2.5 text-xs text-slate-300">
                    <div class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Katalog Produk & Resep Tanpa Batas</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Multi-Gudang & Multi-Outlet POS</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>10.000.000 Token AI / Bulan</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Komitmen <em>No Data Punishment</em></span>
                    </div>
                </div>

                <!-- Cost Summary -->
                <div class="border-t border-slate-800 pt-4 space-y-2 text-xs">
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Biaya Langganan:</span>
                        <span class="font-mono text-white" x-text="'Rp ' + formatRupiah(currentPrice)"></span>
                    </div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Kode Verifikasi Unik:</span>
                        <span class="font-mono text-purple-300">Dihasilkan di invoice</span>
                    </div>
                    <div class="border-t border-slate-800/80 pt-3 flex items-baseline justify-between">
                        <span class="font-bold text-white text-sm">Estimasi Total:</span>
                        <span class="font-black text-xl text-emerald-400 font-mono" x-text="'Rp ' + formatRupiah(currentPrice)"></span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-black text-sm shadow-xl shadow-emerald-500/20 transition flex items-center justify-center gap-2 group">
                    <span>Lanjutkan Pembayaran</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </button>

                <p class="text-[10px] text-center text-slate-500 leading-relaxed">
                    Setelah klik tombol di atas, Anda akan mendapatkan nomor rekening resmi dan form upload bukti transfer.
                </p>
            </div>
        </div>

    </form>
</div>
@endsection
