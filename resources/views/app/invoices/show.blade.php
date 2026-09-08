@extends('layouts.app', [
    'title' => "Faktur {$invoice->invoice_number}",
    'headerTitle' => "Faktur Penjualan: {$invoice->invoice_number}",
    'headerSubtitle' => "Detail penagihan komersial dan pencatatan pembayaran"
])

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ showPaymentModal: false }">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <a href="{{ route('invoices.index') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Daftar Faktur</span>
        </a>

        <div class="flex items-center gap-2 flex-wrap">
            {{-- Tombol Konfirmasi & Rilis Faktur (hanya tampil saat draft) --}}
            @if($invoice->status === 'draft')
            <form method="POST" action="{{ route('invoices.confirm', $invoice->id) }}" onsubmit="return AppAlert.confirmSubmit(event, this, 'Konfirmasi dan rilis faktur ini? Stok barang akan dipotong dari gudang yang dipilih.', 'Konfirmasi Faktur?', 'warning')">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-500/20 flex items-center gap-1.5 transition-all">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Konfirmasi &amp; Rilis Faktur</span>
                </button>
            </form>
            @endif

            {{-- Tombol Catat Pembayaran (hanya saat ada sisa tagihan dan bukan void) --}}
            @if($invoice->balance_due > 0 && $invoice->status !== 'void' && $invoice->status !== 'draft')
            <button @click="showPaymentModal = true" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold shadow-lg shadow-sky-500/20 flex items-center gap-1.5 transition-all">
                <i data-lucide="wallet" class="w-4 h-4"></i>
                <span>Catat Pembayaran</span>
            </button>
            @endif

            <a href="{{ route('invoices.print', $invoice->id) }}?download=1" target="_blank" class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold flex items-center gap-1.5 transition-all">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Download PDF</span>
            </a>

            <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i data-lucide="printer" class="w-4 h-4 text-slate-400"></i>
                <span>Cetak (A4)</span>
            </a>

            {{-- Tombol Void (hanya tampil saat bukan draft/void dan belum ada pembayaran) --}}
            @if(!in_array($invoice->status, ['draft','void']) && $invoice->paid_amount == 0)
            <form method="POST" action="{{ route('invoices.void', $invoice->id) }}" onsubmit="return AppAlert.confirmSubmit(event, this, 'Batalkan (void) faktur ini? Stok yang sudah dipotong akan dikembalikan ke gudang.', 'Void Faktur?', 'danger')">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-rose-900/50 hover:bg-rose-700/60 text-rose-400 text-xs font-semibold border border-rose-800/60 flex items-center gap-1.5 transition-colors">
                    <i data-lucide="ban" class="w-4 h-4"></i>
                    <span>Void / Batalkan</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Main Invoice Sheet -->
    <div class="glass-card p-8 rounded-2xl border border-slate-800 space-y-6 bg-slate-900/60">
        <!-- Sheet Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b border-slate-800">
            <div>
                <div class="text-xs font-bold uppercase tracking-widest text-emerald-400">FAKTUR PENJUALAN KOMERSIAL</div>
                <h1 class="text-2xl font-extrabold text-white font-mono mt-1">{{ $invoice->invoice_number }}</h1>
                @if($invoice->purchaseOrder)
                    <div class="text-xs text-slate-400 mt-0.5">Berdasarkan PO: <span class="font-mono text-emerald-400">{{ $invoice->purchaseOrder->po_number }}</span></div>
                @endif
            </div>

            <div class="text-right space-y-1 self-start sm:self-auto">
                @php
                    $badges = [
                        'draft' => 'bg-slate-800 text-slate-300 border-slate-700',
                        'sent' => 'bg-blue-500/15 text-blue-400 border-blue-500/30',
                        'unpaid' => 'bg-sky-500/15 text-sky-400 border-sky-500/30',
                        'partially_paid' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
                        'paid' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
                        'overdue' => 'bg-rose-500/15 text-rose-400 border-rose-500/30',
                        'void' => 'bg-slate-800 text-slate-500 border-slate-700 line-through',
                    ];
                    $badgeClass = $badges[$invoice->status] ?? 'bg-slate-800 text-slate-300 border-slate-700';
                @endphp
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $badgeClass }}">
                    {{ str_replace('_', ' ', $invoice->status) }}
                </span>
                <div class="text-xs text-slate-400 font-mono">Tgl Faktur: {{ $invoice->invoice_date?->translatedFormat('d F Y') }}</div>
                <div class="text-xs text-slate-400 font-mono">Jatuh Tempo: {{ $invoice->due_date?->translatedFormat('d F Y') }}</div>
                @if($invoice->payment_terms)
                    <div class="text-[11px] text-slate-500">Termin: {{ $invoice->payment_terms }}</div>
                @endif
            </div>
        </div>

        <!-- Party Information -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-1">
                <div class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Pihak Penjual:</div>
                <div class="text-sm font-bold text-white">{{ $business->name }}</div>
                <div class="text-slate-400">{{ $business->address ?? 'Alamat Kantor Pusat Bisnis' }}</div>
                <div class="text-slate-400">Mata Uang: {{ $business->currency_code }} ({{ $business->currency_symbol }})</div>
            </div>

            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-1">
                <div class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Ditagihkan Kepada:</div>
                <div class="text-sm font-bold text-white">{{ $invoice->customer?->name ?? 'Pelanggan' }}</div>
                @if($invoice->customer?->company_name)
                    <div class="text-emerald-400 font-semibold">{{ $invoice->customer->company_name }}</div>
                @endif
                <div class="text-slate-400">{{ $invoice->customer?->billing_address ?? '-' }}</div>
                <div class="text-slate-400 font-mono">Kontak: {{ $invoice->customer?->phone ?? $invoice->customer?->email ?? '-' }}</div>
                @if($invoice->customer?->tax_identification_number)
                    <div class="text-slate-400 font-mono">NPWP: {{ $invoice->customer->tax_identification_number }}</div>
                @endif
            </div>
        </div>

        {{-- Badge Gudang Asal Pengeluaran Barang --}}
        @if($invoice->location)
        <div class="flex items-center gap-2 text-xs text-slate-400 py-2 border-t border-slate-800/60">
            <i data-lucide="warehouse" class="w-4 h-4 text-emerald-400 shrink-0"></i>
            <span>Barang dikeluarkan dari gudang:</span>
            <span class="font-semibold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/25">{{ $invoice->location->name }}</span>
            @if($invoice->location->type)
                <span class="text-slate-500">({{ $invoice->location->type }})</span>
            @endif
        </div>
        @endif

        <!-- Items Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-950/80">
                        <th class="py-3 px-3 font-semibold w-12 text-center">No</th>
                        <th class="py-3 px-3 font-semibold">Nama Produk / Jasa</th>
                        <th class="py-3 px-3 font-semibold text-center w-24">Satuan</th>
                        <th class="py-3 px-3 font-semibold text-right w-20">Qty</th>
                        <th class="py-3 px-3 font-semibold text-right w-32">Harga Satuan</th>
                        <th class="py-3 px-3 font-semibold text-right w-28 text-emerald-400">HPP Modal</th>
                        <th class="py-3 px-3 font-semibold text-right w-36">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($invoice->items as $idx => $item)
                    <tr class="hover:bg-slate-800/20">
                        <td class="py-3 px-3 text-center font-mono text-slate-500">{{ $idx + 1 }}</td>
                        <td class="py-3 px-3">
                            <div class="font-bold text-white">{{ $item->item_name }}</div>
                            @if($item->sku)
                                <div class="text-[10px] text-slate-400 font-mono">SKU: {{ $item->sku }}</div>
                            @endif
                            @if($item->description)
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $item->description }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center text-slate-300 font-mono">
                            {{ $item->unit?->name ?? 'pcs' }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-semibold text-white">
                            {{ (float)$item->quantity == (int)$item->quantity ? number_format((float)$item->quantity, 0, ',', '.') : rtrim(rtrim(number_format((float)$item->quantity, 2, ',', '.'), '0'), ',') }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-slate-200">
                            {{ $business->currency_symbol }} {{ number_format((float)$item->unit_price, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-emerald-400">
                            {{ $business->currency_symbol }} {{ number_format((float)$item->unit_hpp, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-white">
                            {{ $business->currency_symbol }} {{ number_format((float)$item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Financial Summary Breakdown -->
        <div class="flex flex-col sm:flex-row justify-between items-start pt-4 border-t border-slate-800 gap-6">
            <div class="space-y-3 w-full sm:max-w-md text-xs">
                <!-- Profit Analysis Card -->
                <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 space-y-1">
                    <div class="text-[11px] text-emerald-400 font-bold uppercase tracking-wider flex items-center justify-between">
                        <span>Laba Kotor Riil Transaksi:</span>
                        <span>{{ $invoice->subtotal > 0 ? round(($invoice->total_gross_profit / $invoice->subtotal) * 100) : 0 }}% Margin</span>
                    </div>
                    <div class="text-lg font-extrabold text-emerald-400 font-mono">
                        {{ $business->currency_symbol }} {{ number_format((float)$invoice->total_gross_profit, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] text-slate-400">
                        Total Penjualan: {{ $business->currency_symbol }} {{ number_format((float)$invoice->subtotal, 0, ',', '.') }} — Total HPP Modal: {{ $business->currency_symbol }} {{ number_format((float)$invoice->total_hpp_cost, 0, ',', '.') }}
                    </div>
                </div>

                @if($invoice->notes)
                <div>
                    <div class="font-bold text-slate-300 mb-0.5">Catatan Faktur:</div>
                    <div class="text-slate-400">{{ $invoice->notes }}</div>
                </div>
                @endif

                @if($invoice->terms_conditions)
                <div>
                    <div class="font-bold text-slate-300 mb-0.5">Ketentuan Pembayaran:</div>
                    <div class="text-slate-400 whitespace-pre-line">{{ $invoice->terms_conditions }}</div>
                </div>
                @endif
            </div>

            <div class="w-full sm:w-80 space-y-2 text-xs">
                <div class="flex justify-between text-slate-400">
                    <span>Subtotal:</span>
                    <span class="font-mono font-semibold text-white">{{ $business->currency_symbol }} {{ number_format((float)$invoice->subtotal, 0, ',', '.') }}</span>
                </div>

                @if($invoice->discount_amount > 0)
                <div class="flex justify-between text-slate-400">
                    <span>Potongan Diskon:</span>
                    <span class="font-mono font-semibold text-rose-400">- {{ $business->currency_symbol }} {{ number_format((float)$invoice->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif

                @if($invoice->tax_amount > 0)
                <div class="flex justify-between text-slate-400">
                    <span>PPN ({{ $invoice->tax_percentage }}%):</span>
                    <span class="font-mono font-semibold text-slate-200">+ {{ $business->currency_symbol }} {{ number_format((float)$invoice->tax_amount, 0, ',', '.') }}</span>
                </div>
                @endif

                @if($invoice->shipping_cost > 0)
                <div class="flex justify-between text-slate-400">
                    <span>Ongkos Kirim:</span>
                    <span class="font-mono font-semibold text-slate-200">+ {{ $business->currency_symbol }} {{ number_format((float)$invoice->shipping_cost, 0, ',', '.') }}</span>
                </div>
                @endif

                <div class="pt-2 border-t border-slate-800 flex justify-between items-center text-sm font-bold text-white">
                    <span>Total Tagihan:</span>
                    <span class="font-mono text-emerald-400 text-lg">{{ $business->currency_symbol }} {{ number_format((float)$invoice->total_amount, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-between text-slate-400 pt-1">
                    <span>Sudah Dibayar:</span>
                    <span class="font-mono font-semibold text-emerald-400">{{ $business->currency_symbol }} {{ number_format((float)$invoice->paid_amount, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-between items-center pt-2 border-t border-slate-800/80 font-bold {{ $invoice->balance_due > 0 ? 'text-amber-400' : 'text-emerald-400' }}">
                    <span>Sisa Saldo Piutang:</span>
                    <span class="font-mono text-base">{{ $business->currency_symbol }} {{ number_format((float)$invoice->balance_due, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Payments History Table -->
        <div class="pt-6 border-t border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4 text-emerald-400"></i>
                    <span>Riwayat Pembayaran yang Diterima</span>
                </h3>
                @if($invoice->balance_due > 0 && $invoice->status !== 'void')
                <button @click="showPaymentModal = true" class="text-xs text-emerald-400 hover:underline font-semibold flex items-center gap-1">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Pembayaran</span>
                </button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-800 bg-slate-950/60">
                            <th class="py-2.5 px-3 font-semibold">No. Bukti Bayar</th>
                            <th class="py-2.5 px-3 font-semibold">Tanggal</th>
                            <th class="py-2.5 px-3 font-semibold">Metode Pembayaran</th>
                            <th class="py-2.5 px-3 font-semibold">No. Referensi / Bank</th>
                            <th class="py-2.5 px-3 font-semibold text-right">Jumlah Dibayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($invoice->payments as $payment)
                        <tr class="hover:bg-slate-800/20">
                            <td class="py-2.5 px-3 font-mono font-semibold text-white">{{ $payment->payment_number }}</td>
                            <td class="py-2.5 px-3 font-mono text-slate-300">{{ $payment->payment_date?->translatedFormat('d M Y') }}</td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-800 text-slate-300">
                                    {{ str_replace('_', ' ', $payment->payment_method) }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 font-mono text-slate-400">{{ $payment->reference_number ?? '-' }}</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-400">
                                {{ $business->currency_symbol }} {{ number_format((float)$payment->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-slate-500">
                                Belum ada riwayat pembayaran yang tercatat untuk faktur ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Catat Pembayaran -->
    <div x-show="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card w-full max-w-md rounded-2xl p-6 border border-slate-700 space-y-4" @click.outside="showPaymentModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="wallet" class="w-5 h-5 text-emerald-400"></i>
                    <span>Catat Penerimaan Pembayaran</span>
                </h3>
                <button @click="showPaymentModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('invoices.payments.store', $invoice->id) }}" class="space-y-4 text-xs">
                @csrf

                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex justify-between items-center">
                    <span class="text-slate-400">Sisa Tagihan Belum Dibayar:</span>
                    <span class="font-mono font-bold text-amber-400 text-sm">
                        {{ $business->currency_symbol }} {{ number_format((float)$invoice->balance_due, 0, ',', '.') }}
                    </span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nominal Pembayaran Diterima *</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 font-mono">{{ $business->currency_symbol }}</span>
                        <input type="number" step="any" min="1" max="{{ (float)$invoice->balance_due }}" name="amount" value="{{ (float)$invoice->balance_due }}" required class="w-full pl-12 pr-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono font-bold text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Tanggal Bayar *</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Metode Bayar *</label>
                        <select name="payment_method" required class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                            <option value="bank_transfer">Transfer Bank</option>
                            <option value="cash">Tunai (Cash)</option>
                            <option value="qris">QRIS / E-Wallet</option>
                            <option value="credit_card">Kartu Kredit</option>
                            <option value="cheque">Cek / Giro</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">No. Referensi / Bukti Transfer</label>
                    <input type="text" name="reference_number" placeholder="TRX-BCA-981203" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Catatan Pembayaran</label>
                    <input type="text" name="notes" placeholder="Pelunasan termin 1..." class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="showPaymentModal = false" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold shadow-lg shadow-emerald-500/20">Konfirmasi Bayar</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
