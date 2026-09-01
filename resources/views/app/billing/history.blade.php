@extends('layouts.app', [
    'title' => 'Riwayat Pembayaran Langganan — Cooca Core',
    'headerTitle' => 'Riwayat Pembayaran & Tagihan',
    'headerSubtitle' => 'Daftar transaksi langganan dan status persetujuan paket Cooca Core bisnis Anda'
])

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <a href="{{ route('billing.limits') }}" class="text-xs text-slate-400 hover:text-white flex items-center gap-1.5 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Paket & Kuota</span>
        </a>
        <a href="{{ route('billing.checkout') }}" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow transition flex items-center gap-1.5">
            <i data-lucide="zap" class="w-3.5 h-3.5"></i>
            <span>Upgrade / Perpanjang Paket</span>
        </a>
    </div>

    <!-- Payments Table Card -->
    <div class="glass-card rounded-3xl border border-slate-800 overflow-hidden">
        <div class="p-6 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-black text-white flex items-center gap-2">
                <i data-lucide="receipt" class="w-5 h-5 text-emerald-400"></i>
                <span>Daftar Tagihan Langganan</span>
            </h3>
            <span class="text-xs text-slate-400 font-mono">Total: {{ $payments->total() }} transaksi</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/80 text-slate-400 font-mono uppercase text-[10px] border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-5">No. Pesanan</th>
                        <th class="py-3.5 px-4">Paket & Periode</th>
                        <th class="py-3.5 px-4">Metode Bayar</th>
                        <th class="py-3.5 px-4">Total Bayar</th>
                        <th class="py-3.5 px-4">Bukti Transfer</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans">
                    @forelse($payments as $p)
                    @php
                        $badge = $p->getStatusBadge();
                        $method = $p->getPaymentMethodDetails();
                    @endphp
                    <tr class="hover:bg-slate-900/40 transition">
                        <td class="py-4 px-5">
                            <span class="font-mono font-bold text-white">{{ $p->order_number }}</span>
                        </td>
                        <td class="py-4 px-4">
                            <div class="font-bold text-white">{{ $p->cycle === 'annual' ? 'Core Tahunan' : 'Core Bulanan' }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $p->plan_code }}</div>
                        </td>
                        <td class="py-4 px-4">
                            <div class="font-semibold text-slate-200">{{ $method['name'] }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $method['bank_name'] }}</div>
                        </td>
                        <td class="py-4 px-4">
                            <div class="font-mono font-black text-sm text-emerald-400">Rp {{ number_format($p->total_payable, 0, ',', '.') }}</div>
                            @if($p->unique_code > 0)
                                <div class="text-[10px] text-slate-500 font-mono">Kode unik: {{ $p->unique_code }}</div>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            @if($p->payment_proof_path)
                                <a href="{{ $p->getProofUrl() }}" target="_blank" class="text-xs text-cyan-400 hover:underline flex items-center gap-1 font-semibold">
                                    <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                    <span>Lihat Bukti</span>
                                </a>
                            @else
                                <span class="text-slate-500 text-[11px] italic">Belum diunggah</span>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border inline-flex items-center gap-1 {{ $badge['class'] }}">
                                <i data-lucide="{{ $badge['icon'] }}" class="w-3 h-3"></i>
                                <span>{{ $badge['label'] }}</span>
                            </span>
                        </td>
                        <td class="py-4 px-4 font-mono text-slate-400 text-[11px]">
                            {{ $p->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="py-4 px-5 text-right">
                            <a href="{{ route('billing.payment.show', $p) }}"
                               class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition inline-flex items-center gap-1">
                                <span>Rincian</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-slate-500 text-xs">
                            Belum ada riwayat transaksi tagihan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
