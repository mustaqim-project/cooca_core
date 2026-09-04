@extends('layouts.app', [
    'title' => 'Checkout Patungan — Cooca UMKM',
    'headerTitle' => 'Pilih Metode Pembayaran',
    'headerSubtitle' => 'Ikut program Patungan Cooca UMKM untuk akses fitur tanpa batas dan kolaborasi bisnis'
])

@section('content')
@php
    $defaultSelectedCode = $paymentAccounts->first()?->bank_code ?? 'bca';
@endphp
<div class="max-w-5xl mx-auto space-y-8" x-data="{
    cycle: '{{ $cycle }}',
    orderType: '{{ $type }}',
    packageId: '{{ $packages->first()?->id ?? '' }}',
    paymentMethod: '{{ $defaultSelectedCode }}',
    monthlyPrice: {{ (int)$monthlyPrice }},
    annualPrice: {{ (int)$annualPrice }},
    topupPrice: {{ (int)$topupPrice }},
    get currentPrice() {
        const selected = this.packageId ? this.packages.find(item => item.id === this.packageId) : null;
        if (selected) return Number(selected.price);
        return this.orderType === 'subscription' ? (this.cycle === 'annual' ? this.annualPrice : this.monthlyPrice) : this.topupPrice;
    },
    packages: {{ \Illuminate\Support\Js::from($packagesData) }},
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
        <input type="hidden" name="order_type" value="{{ $type }}">
        <input type="hidden" name="package_id" :value="packageId">
        <input type="hidden" name="payment_method" :value="paymentMethod">

        <!-- Left Column: Plan Selector & Payment Method (2 cols) -->
        <div class="lg:col-span-2 space-y-6">

            @if($packages->isNotEmpty())
            <div class="glass-card rounded-3xl p-6 border border-emerald-500/30 space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-white">{{ $type === 'subscription' ? 'Pilih Paket Patungan' : ($type === 'ai_token' ? 'Pilih Paket Top Up Token AI' : 'Pilih Paket Storage') }}</h3>
                        <p class="text-xs text-slate-400 mt-1">{{ $type === 'subscription' ? 'Harga resmi program patungan UMKM Cooca.' : 'Paket top up kuota tambahan.' }}</p>
                    </div>
                    @if($type === 'subscription')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        Patungan UMKM
                    </span>
                    @endif
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($packages as $package)
                    <button type="button" @click="packageId = '{{ $package->id }}'; cycle = '{{ $package->duration_days >= 360 ? 'annual' : 'monthly' }}'" :class="packageId === '{{ $package->id }}' ? 'border-emerald-400 bg-emerald-950/40 shadow-lg shadow-emerald-500/10' : 'border-slate-800 bg-slate-900/60'" class="text-left rounded-2xl border p-4 transition hover:border-emerald-400/60 relative overflow-hidden">
                        <div class="flex items-start justify-between gap-2">
                            <span class="font-bold text-white text-sm">{{ $package->name }}</span>
                            <span class="w-4 h-4 rounded-full border border-emerald-400 shrink-0 flex items-center justify-center" :class="packageId === '{{ $package->id }}' ? 'bg-emerald-400' : ''">
                                <span x-show="packageId === '{{ $package->id }}'" class="w-1.5 h-1.5 rounded-full bg-slate-950"></span>
                            </span>
                        </div>
                        <div class="text-2xl font-black text-emerald-400 font-mono mt-3">Rp {{ number_format($package->price, 0, ',', '.') }}</div>
                        <p class="text-[11px] text-slate-400 mt-1">{{ $package->description }}</p>
                        @if($type === 'subscription')
                            <p class="text-[11px] text-cyan-300 mt-2 font-medium">Akses Pro/Patungan {{ $package->duration_days }} hari</p>
                        @elseif($type === 'ai_token')
                            <p class="text-[11px] text-amber-300 mt-2">{{ number_format($package->token_quantity, 0, ',', '.') }} token · {{ $package->token_expiry_days ?? 30 }} hari</p>
                        @else
                            <p class="text-[11px] text-cyan-300 mt-2">{{ number_format(($package->storage_bytes ?? 0) / 1073741824, 2, ',', '.') }} GB permanen</p>
                        @endif
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

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
                    @forelse($paymentAccounts as $account)
                    <div @click="paymentMethod = '{{ $account->bank_code }}'"
                         class="cursor-pointer rounded-2xl p-4 border transition-all flex items-center justify-between"
                         :class="paymentMethod === '{{ $account->bank_code }}' ? 'bg-slate-900 border-indigo-500 shadow-md' : 'bg-slate-950/60 border-slate-800/80 hover:border-slate-700'">

                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center shrink-0">
                                @if($account->type === \App\Models\PaymentAccount::TYPE_QRIS)
                                    <i data-lucide="qr-code" class="w-6 h-6 text-emerald-400"></i>
                                @else
                                    <i data-lucide="{{ $account->icon ?: 'credit-card' }}" class="w-6 h-6 text-indigo-400"></i>
                                @endif
                            </div>
                            <div>
                                <div class="font-bold text-sm text-white flex items-center gap-2">
                                    <span>{{ $account->bank_name }}</span>
                                    @if($account->type === \App\Models\PaymentAccount::TYPE_QRIS)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                            Instant / e-Wallet
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $account->account_number }} • a.n. {{ $account->account_name }}</div>
                            </div>
                        </div>

                        <div class="w-5 h-5 rounded-full border flex items-center justify-center shrink-0"
                             :class="paymentMethod === '{{ $account->bank_code }}' ? 'border-indigo-400 bg-indigo-500' : 'border-slate-700'">
                            <div x-show="paymentMethod === '{{ $account->bank_code }}'" class="w-2 h-2 rounded-full bg-white"></div>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 rounded-xl bg-slate-900 text-slate-400 text-xs text-center">
                        Belum ada metode pembayaran yang aktif. Silakan hubungi admin.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Column: Order Summary & Action (1 col) -->
        <div class="space-y-6">
            <div class="glass-card rounded-3xl p-6 border border-slate-800 space-y-6 sticky top-24">
                <div class="border-b border-slate-800 pb-4">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Ringkasan Pesanan</span>
                    <h4 class="text-lg font-black text-white mt-1">Cooca UMKM — Patungan</h4>
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
                        <span>Import & Export Excel Tanpa Batas</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Akses Asisten Pintar AI (Top-Up)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Komitmen <em>No Data Punishment</em></span>
                    </div>
                </div>

                <!-- Cost Summary -->
                <div class="border-t border-slate-800 pt-4 space-y-2 text-xs">
                    <div class="flex items-center justify-between text-slate-400">
                        <span>{{ $type === 'subscription' ? 'Nominal Patungan:' : 'Harga Top Up:' }}</span>
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
