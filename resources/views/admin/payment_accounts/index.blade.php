@extends('layouts.admin', [
    'title' => 'CMS Rekening & Pembayaran — Admin Console',
    'headerTitle' => 'Kelola Rekening & Pembayaran (CMS Rekening)',
    'headerSubtitle' => 'Kelola rekening bank tujuan transfer dan QRIS yang tampil pada checkout pelanggan'
])

@section('content')
<div class="space-y-6" x-data="{
    qrisModalUrl: null,
    qrisModalTitle: '',
    openQris(url, title) {
        this.qrisModalUrl = url;
        this.qrisModalTitle = title;
    }
}">

    <!-- Stats and Action Header -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <!-- Metric Cards -->
        <div class="grid grid-cols-3 gap-3">
            <div class="glass-card px-4 py-3 rounded-2xl border-slate-800 flex items-center gap-3">
                <div class="p-2 rounded-xl bg-indigo-500/20 text-indigo-400">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                </div>
                <div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase">Total Rekening</div>
                    <div class="text-base font-black text-white font-mono">{{ $accounts->count() }}</div>
                </div>
            </div>

            <div class="glass-card px-4 py-3 rounded-2xl border-slate-800 flex items-center gap-3">
                <div class="p-2 rounded-xl bg-emerald-500/20 text-emerald-400">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
                <div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase">Aktif Checkout</div>
                    <div class="text-base font-black text-emerald-400 font-mono">{{ $activeCount }}</div>
                </div>
            </div>

            <div class="glass-card px-4 py-3 rounded-2xl border-slate-800 flex items-center gap-3">
                <div class="p-2 rounded-xl bg-teal-500/20 text-teal-400">
                    <i data-lucide="qr-code" class="w-4 h-4"></i>
                </div>
                <div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase">QRIS / Instant</div>
                    <div class="text-base font-black text-teal-400 font-mono">{{ $qrisCount }}</div>
                </div>
            </div>
        </div>

        <!-- Add Button -->
        <a href="{{ route('admin.payment-accounts.create') }}" 
           class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 transition flex items-center justify-center gap-2">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Rekening Baru</span>
        </a>
    </div>

    <!-- Payment Accounts Cards Grid & Table -->
    <div class="glass-card rounded-3xl border border-slate-800 overflow-hidden">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="wallet" class="w-4 h-4 text-cyan-400"></i>
                <h3 class="font-bold text-white text-sm">Daftar Metode Pembayaran Resmi Platform</h3>
            </div>
            <span class="text-xs text-slate-400">Rekening berstatus "Aktif" akan langsung muncul pada pilihan pembayaran tenant.</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 bg-slate-950/40">
                        <th class="py-3 px-4 font-bold">Urutan</th>
                        <th class="py-3 px-4 font-bold">Bank / Saluran Pembayaran</th>
                        <th class="py-3 px-4 font-bold">Nomor Rekening / Kode</th>
                        <th class="py-3 px-4 font-bold">Atas Nama (A/N)</th>
                        <th class="py-3 px-4 font-bold">Tipe</th>
                        <th class="py-3 px-4 font-bold text-center">Status</th>
                        <th class="py-3 px-4 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($accounts as $account)
                    <tr class="hover:bg-slate-800/30 transition">
                        <!-- Sort Order -->
                        <td class="py-4 px-4 font-mono font-bold text-slate-400">
                            #{{ $account->sort_order }}
                        </td>

                        <!-- Bank & Icon -->
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 
                                    @if($account->color === 'blue') bg-blue-500/20 text-blue-400 border border-blue-500/30
                                    @elseif($account->color === 'amber') bg-amber-500/20 text-amber-400 border border-amber-500/30
                                    @elseif($account->color === 'cyan') bg-cyan-500/20 text-cyan-400 border border-cyan-500/30
                                    @elseif($account->color === 'emerald') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                    @else bg-purple-500/20 text-purple-400 border border-purple-500/30 @endif">
                                    <i data-lucide="{{ $account->icon ?: 'credit-card' }}" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-white text-sm">{{ $account->bank_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono uppercase">Code: {{ $account->bank_code }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Account Number & QRIS Preview -->
                        <td class="py-4 px-4">
                            <div class="space-y-1">
                                <span class="font-mono font-bold text-white text-sm">{{ $account->account_number }}</span>
                                @if($account->qr_image_path)
                                <div>
                                    <button type="button" 
                                            @click="openQris('{{ $account->qr_image_url }}', '{{ $account->bank_name }}')"
                                            class="inline-flex items-center gap-1 text-[11px] text-emerald-400 hover:text-emerald-300 hover:underline font-semibold">
                                        <i data-lucide="qr-code" class="w-3 h-3"></i>
                                        <span>Lihat QR Code</span>
                                    </button>
                                </div>
                                @endif
                            </div>
                        </td>

                        <!-- Account Name -->
                        <td class="py-4 px-4 font-semibold text-slate-200">
                            {{ $account->account_name }}
                        </td>

                        <!-- Type Badge -->
                        <td class="py-4 px-4">
                            @if($account->type === \App\Models\PaymentAccount::TYPE_QRIS)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                                    QRIS Instant
                                </span>
                            @elseif($account->type === \App\Models\PaymentAccount::TYPE_E_WALLET)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                    e-Wallet
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                    Transfer Bank
                                </span>
                            @endif
                        </td>

                        <!-- Status Toggle -->
                        <td class="py-4 px-4 text-center">
                            <form method="POST" action="{{ route('admin.payment-accounts.toggle-status', $account) }}">
                                @csrf
                                <button type="submit" 
                                        class="px-3 py-1 rounded-full text-[11px] font-bold border transition-all inline-flex items-center gap-1.5
                                        {{ $account->is_active ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40 hover:bg-emerald-500/30' : 'bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700' }}">
                                    <span class="w-2 h-2 rounded-full {{ $account->is_active ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                                    <span>{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </button>
                            </form>
                        </td>

                        <!-- Actions -->
                        <td class="py-4 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.payment-accounts.edit', $account) }}" 
                                   title="Edit Rekening"
                                   class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </a>

                                <form method="POST" action="{{ route('admin.payment-accounts.destroy', $account) }}" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus rekening {{ $account->bank_name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            title="Hapus Rekening"
                                            class="p-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 transition">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500 text-xs">
                            Belum ada rekening pembayaran yang ditambahkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- QRIS Image Modal Popover -->
    <div x-show="qrisModalUrl" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         @click.self="qrisModalUrl = null">
        <div class="glass-card rounded-3xl p-6 border border-slate-700 max-w-sm w-full space-y-4 text-center">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h4 class="font-bold text-white text-sm" x-text="qrisModalTitle"></h4>
                <button type="button" @click="qrisModalUrl = null" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-2 bg-white rounded-2xl flex items-center justify-center overflow-hidden">
                <img :src="qrisModalUrl" alt="QRIS Code" class="max-h-72 object-contain">
            </div>
            <div class="flex justify-center">
                <a :href="qrisModalUrl" target="_blank" class="text-xs text-indigo-400 hover:underline inline-flex items-center gap-1 font-bold">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span>Buka Ukuran Penuh</span>
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
