@extends('layouts.app', ['title' => 'Detail Hutang Supplier'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <a href="{{ route('purchasing.bills.index') }}" class="text-xs text-slate-400 hover:text-white">&larr; Kembali ke daftar hutang</a>
            <h1 class="text-2xl font-black text-white mt-2">{{ $invoice->invoice_number }}</h1>
            <p class="text-sm text-slate-400">{{ $invoice->supplier->name }} &middot; GR {{ $invoice->goodsReceipt->receipt_number }}</p>
        </div>
        <span class="rounded-full bg-slate-800 px-3 py-1 text-xs font-bold text-amber-300">{{ strtoupper($invoice->status) }}</span>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach([
            'Total' => $invoice->total_amount,
            'Terbayar' => $invoice->paid_amount,
            'Sisa' => $invoice->balance_due,
        ] as $label => $amount)
            <div class="glass-card rounded-xl border border-slate-800 p-5">
                <p class="text-xs uppercase text-slate-500">{{ $label }}</p>
                <p class="text-xl font-black text-white mt-2">Rp {{ number_format($amount, 0, ',', '.') }}</p>
            </div>
        @endforeach
    </div>

    @if($invoice->balance_due > 0)
        <form method="POST" action="{{ route('purchasing.bills.payments.store', $invoice) }}" class="glass-card rounded-xl border border-slate-800 p-5 space-y-4">
            @csrf
            <h2 class="text-sm font-black uppercase tracking-wider text-emerald-400">Catat Pembayaran</h2>
            @error('amount')<p class="text-sm text-rose-400">{{ $message }}</p>@enderror
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <input name="amount" type="number" min="0.01" max="{{ $invoice->balance_due }}" step="0.01" placeholder="Nominal" required value="{{ old('amount') }}" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
                <input name="payment_date" type="date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
                <select name="payment_method" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
                    <option value="bank_transfer">Transfer Bank</option>
                    <option value="cash">Kas</option>
                    <option value="qris">QRIS</option>
                </select>
                <button class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-bold text-slate-950 hover:bg-emerald-400">Simpan Pembayaran</button>
            </div>
            <input name="reference_number" placeholder="Nomor referensi (opsional)" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
        </form>
    @endif

    <div class="glass-card rounded-xl border border-slate-800 overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-800 text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Nomor</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Metode</th><th class="px-4 py-3 text-right">Nominal</th></tr></thead>
            <tbody class="divide-y divide-slate-800/70 text-slate-300">
                @forelse($invoice->payments as $payment)
                    <tr><td class="px-4 py-3">{{ $payment->payment_number }}</td><td class="px-4 py-3">{{ $payment->payment_date->format('d M Y') }}</td><td class="px-4 py-3">{{ strtoupper($payment->payment_method) }}</td><td class="px-4 py-3 text-right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td></tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada pembayaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
