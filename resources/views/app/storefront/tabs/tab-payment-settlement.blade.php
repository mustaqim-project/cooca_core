{{-- TAB 5: PEMBAYARAN, SALDO GATEWAY & SETTLEMENT DANA --}}
<div class="space-y-6">

    {{-- BENTO CARD 1: SALDO GATEWAY & PENARIKAN DANA (TRIPAY ESCROW CLEARING) --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-black/5 dark:border-white/10">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight">Saldo Gateway &amp; Penarikan Dana</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $isGatewayConfigured ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF9500]/10 text-[#D97706]' }}">
                            {{ $isGatewayConfigured ? 'Gateway Aktif' : 'Gateway Siap Konfigurasi' }}
                        </span>
                    </div>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Dana transaksi non-tunai otomatis (QRIS Dinamis &amp; Virtual Account) yang siap dicairkan ke bank Anda</p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <a href="{{ route('finance.settlements.index') }}"
                    class="h-9 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12.5px] font-semibold flex items-center gap-1.5 transition active:scale-[0.98] shadow-xs">
                    <i data-lucide="arrow-down-to-dot" class="w-3.5 h-3.5"></i>
                    <span>Tarik Saldo ke Bank</span>
                </a>
            </div>
        </div>

        {{-- 3 KPI METRICS --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Gross --}}
            <div class="p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                <span class="text-[11.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">
                    Saldo Gateway Mengendap
                </span>
                <div class="text-2xl font-extrabold text-black dark:text-white tabular-nums tracking-tight">
                    {{ $business->currency_symbol ?? 'Rp' }} {{ number_format($unsettledData['summary']['total_gross'] ?? 0, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-black/50 dark:text-white/50">
                    {{ $unsettledData['summary']['count'] ?? 0 }} transaksi non-tunai lunas
                </p>
            </div>

            {{-- Fee MDR --}}
            <div class="p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                <span class="text-[11.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">
                    Total Potongan Fee MDR
                </span>
                <div class="text-2xl font-extrabold text-[#FF3B30] tabular-nums tracking-tight">
                    {{ $business->currency_symbol ?? 'Rp' }} {{ number_format($unsettledData['summary']['total_fee'] ?? 0, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-black/50 dark:text-white/50">
                    Biaya pemrosesan resmi TriPay
                </p>
            </div>

            {{-- Net Payout --}}
            <div class="p-4.5 rounded-[18px] bg-[#34C759]/5 dark:bg-[#30D158]/5 border border-[#34C759]/20 space-y-1">
                <span class="text-[11.5px] font-bold uppercase tracking-wider text-[#248A3D] dark:text-[#30D158] block">
                    Saldo Bersih Siap Cair
                </span>
                <div class="text-2xl font-extrabold text-[#248A3D] dark:text-[#30D158] tabular-nums tracking-tight">
                    {{ $business->currency_symbol ?? 'Rp' }} {{ number_format($unsettledData['summary']['total_net'] ?? 0, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-black/50 dark:text-white/50">
                    Estimasi dana masuk ke rekening bank
                </p>
            </div>
        </div>

        {{-- Edukasi Penarikan & Riwayat --}}
        <div class="p-4 rounded-[16px] bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-start gap-2.5">
                <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                <p class="text-[12px] text-black/70 dark:text-white/70 leading-relaxed">
                    Setiap pembayaran via QRIS &amp; Virtual Account otomatis tercatat di saldo penampungan gateway. Merchant dapat mengajukan penarikan saldo ke rekening bank kapan saja — Admin COOCA akan memproses transfer dan melampirkan foto bukti bayar resmi yang dapat dilihat dan diunduh langsung di riwayat settlement.
                </p>
            </div>
            <a href="{{ route('finance.settlements.index') }}"
                class="text-[12px] font-bold text-[#007AFF] hover:underline shrink-0 inline-flex items-center gap-1 self-start sm:self-auto">
                <span>Buka Modul Settlement</span>
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>

    {{-- BENTO CARD 2: PANDUAN DUA METODE PEMBAYARAN TOKO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Saluran 1: Otomatis Gateway --}}
        <div class="p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs space-y-3">
            <div class="flex items-center gap-2 text-[#007AFF]">
                <i data-lucide="zap" class="w-5 h-5"></i>
                <h4 class="text-[14px] font-bold text-black dark:text-white">1. Pembayaran Otomatis (Payment Gateway)</h4>
            </div>
            <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                Pelanggan membayar menggunakan <strong>QRIS Dinamis 24 Jam</strong> atau <strong>Virtual Account Bank (BCA, Mandiri, BRI, BNI, Permata, BSI)</strong>.
            </p>
            <ul class="text-[11.5px] text-black/60 dark:text-white/60 space-y-1.5 list-disc list-inside">
                <li>Verifikasi otomatis detik itu juga tanpa perlu upload bukti transfer.</li>
                <li>Stok produk langsung terkunci secara aman.</li>
                <li>Dana terkumpul di <strong>Saldo Gateway</strong> di atas dan dapat dicairkan kapan saja.</li>
            </ul>
        </div>

        {{-- Saluran 2: Manual Transfer --}}
        <div class="p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs space-y-3">
            <div class="flex items-center gap-2 text-[#34C759]">
                <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                <h4 class="text-[14px] font-bold text-black dark:text-white">2. Transfer Manual &amp; QRIS Toko (Upload Bukti)</h4>
            </div>
            <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                Pelanggan mentransfer langsung ke rekening bank pribadi Anda dan mengunggah foto struk transfer di formulir pesanan etalase.
            </p>
            <ul class="text-[11.5px] text-black/60 dark:text-white/60 space-y-1.5 list-disc list-inside">
                <li>100% uang langsung masuk ke rekening Anda hari ini tanpa perantara.</li>
                <li>Bebas potongan biaya MDR gateway (0%).</li>
                <li>Merchant memverifikasi foto bukti bayar di menu <strong>Pesanan Masuk</strong>.</li>
            </ul>
        </div>
    </div>

    {{-- BENTO CARD 3: REKENING BANK & QRIS TOKO MANUAL --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-sm space-y-5">
        <div class="flex items-center justify-between pb-4 border-b border-black/5 dark:border-white/10">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">Daftar Rekening Transfer Manual Toko</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Rekening bank dan QRIS statis yang akan ditampilkan kepada pelanggan saat checkout</p>
                </div>
            </div>

            <button type="button" @click="addMethodModalOpen = true"
                class="h-9 px-4 rounded-[12px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] text-xs font-bold transition flex items-center gap-1.5 cursor-pointer active:scale-[0.98]">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Tambah Rekening / QRIS</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($paymentMethods as $method)
                <div class="p-4 rounded-[18px] border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.01] flex items-start justify-between gap-3">
                    <div class="space-y-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-[14px] font-bold text-black dark:text-white truncate">{{ $method->bank_name }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase shrink-0 {{ $method->type === 'qris' ? 'bg-[#5856D6]/10 text-[#5856D6]' : 'bg-[#007AFF]/10 text-[#007AFF]' }}">
                                {{ $method->type === 'qris' ? 'QRIS Statis' : 'Transfer Bank' }}
                            </span>
                        </div>
                        @if ($method->account_number)
                            <p class="text-[13px] font-mono tabular-nums text-black/80 dark:text-white/80 tracking-wide font-medium">
                                {{ $method->account_number }}
                            </p>
                            <p class="text-[11.5px] text-black/50 dark:text-white/50">a/n {{ $method->account_holder }}</p>
                        @endif
                        @if ($method->qris_image_path)
                            <a href="{{ $method->qris_image_url }}" target="_blank"
                                class="text-[11px] text-[#007AFF] hover:underline font-medium inline-flex items-center gap-1 mt-1">
                                <i data-lucide="image" class="w-3 h-3"></i> Lihat Gambar QRIS
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        {{-- TOGGLE ACTIVE --}}
                        <form action="{{ route('storefront.settings.payment_methods.toggle', $method->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                title="{{ $method->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                class="w-8 h-8 rounded-full flex items-center justify-center transition cursor-pointer {{ $method->is_active ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/25' : 'bg-black/10 text-black/40 hover:bg-black/20' }}">
                                <i data-lucide="{{ $method->is_active ? 'check' : 'power' }}" class="w-4 h-4"></i>
                            </button>
                        </form>

                        {{-- DELETE TRIGGER --}}
                        <button type="button"
                            @click="methodToDelete = {{ json_encode(['id' => $method->id, 'bank_name' => $method->bank_name, 'type' => $method->type, 'account_number' => $method->account_number]) }}; deleteModalOpen = true;"
                            title="Hapus Rekening"
                            class="w-8 h-8 rounded-full bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] flex items-center justify-center transition cursor-pointer">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-black/40 dark:text-white/40 space-y-1">
                    <i data-lucide="credit-card" class="w-10 h-10 mx-auto stroke-1 opacity-40 mb-2"></i>
                    <p class="text-[13px] font-medium">Belum ada rekening transfer atau QRIS manual.</p>
                    <p class="text-[11.5px]">Tambahkan minimal 1 rekening agar pembeli dapat membayar secara transfer bank langsung.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
