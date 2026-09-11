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

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Rekening</span>
                <i data-lucide="credit-card" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]" stroke-width="1.5"></i>
            </div>
            <div class="text-[22px] font-bold tabular-nums text-black dark:text-white">{{ $totalCount }}</div>
            <div class="text-[11px] text-black/45 dark:text-white/45 mt-1">Metode pembayaran terdaftar</div>
        </div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Aktif Checkout</span>
                <i data-lucide="check-circle" class="w-4 h-4 text-[#5856D6] dark:text-[#5E5CE6]" stroke-width="1.5"></i>
            </div>
            <div class="text-[22px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6]">{{ $activeCount }}</div>
            <div class="text-[11px] text-black/45 dark:text-white/45 mt-1">Muncul sebagai opsi pembayaran</div>
        </div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">QRIS / Instant</span>
                <i data-lucide="qr-code" class="w-4 h-4 text-[#30B0C7] dark:text-[#40C8E0]" stroke-width="1.5"></i>
            </div>
            <div class="text-[22px] font-bold tabular-nums text-[#30B0C7] dark:text-[#40C8E0]">{{ $qrisCount }}</div>
            <div class="text-[11px] text-black/45 dark:text-white/45 mt-1">Saluran QRIS / e-Wallet</div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.payment-accounts.index') }}" class="flex flex-col sm:flex-row items-stretch gap-2 flex-1 max-w-2xl">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2" stroke-width="1.5"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama bank, kode, nomor rekening, A/N..." class="w-full h-9 pl-9 pr-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>
            <select name="type" onchange="this.form.submit()" class="h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black/70 dark:text-white/70 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                <option value="">Semua Tipe</option>
                <option value="bank_transfer" {{ $type === 'bank_transfer' ? 'selected' : '' }}>Transfer Bank</option>
                <option value="qris" {{ $type === 'qris' ? 'selected' : '' }}>QRIS</option>
                <option value="e_wallet" {{ $type === 'e_wallet' ? 'selected' : '' }}>e-Wallet</option>
            </select>
            @if($search !== '' || $type !== '')
            <a href="{{ route('admin.payment-accounts.index') }}" class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#FF3B30] dark:text-[#FF453A] hover:bg-[#FF3B30]/8 transition-colors inline-flex items-center gap-1 self-end sm:self-auto"><i data-lucide="x" class="w-3.5 h-3.5" stroke-width="1.5"></i>Reset</a>
            @endif
        </form>
        <a href="{{ route('admin.payment-accounts.create') }}" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)] shrink-0"><i data-lucide="plus-circle" class="w-4 h-4" stroke-width="1.5"></i><span>Tambah Rekening Baru</span></a>
    </div>

    <!-- Table -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Urutan</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Bank / Saluran Pembayaran</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Nomor Rekening / Kode</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Atas Nama (A/N)</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">Tipe</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-center">Status</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($accounts as $account)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3 font-mono font-bold text-black/50 dark:text-white/50">#{{ $account->sort_order }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[10px] flex items-center justify-center shrink-0
                                    @if($account->color === 'blue') bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]
                                    @elseif($account->color === 'amber') bg-[#FF9500]/10 text-[#FF9500] dark:text-[#FF9F0A]
                                    @elseif($account->color === 'cyan') bg-[#30B0C7]/10 text-[#30B0C7] dark:text-[#40C8E0]
                                    @elseif($account->color === 'emerald') bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158]
                                    @else bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] @endif">
                                    <i data-lucide="{{ $account->icon ?: 'credit-card' }}" class="w-4 h-4" stroke-width="1.5"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-black dark:text-white text-[13px]">{{ $account->bank_name }}</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45 font-mono uppercase">Code: {{ $account->bank_code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="space-y-1">
                                <span class="font-mono font-semibold text-black dark:text-white text-[13px] tabular-nums">{{ $account->account_number }}</span>
                                @if($account->qr_image_path)
                                <div>
                                    <button type="button" @click="openQris('{{ $account->qr_image_url }}', '{{ $account->bank_name }}')" class="inline-flex items-center gap-1 text-[11px] font-medium text-[#34C759] dark:text-[#30D158] hover:underline"><i data-lucide="qr-code" class="w-3 h-3" stroke-width="1.5"></i><span>Lihat QR Code</span></button>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 font-medium text-black/80 dark:text-white/80">{{ $account->account_name }}</td>
                        <td class="px-4 py-3">
                            @if($account->type === \App\Models\PaymentAccount::TYPE_QRIS)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#30B0C7]/12 text-[#1F7A89] dark:text-[#40C8E0]"><span class="w-1.5 h-1.5 rounded-full bg-[#30B0C7]"></span>QRIS Instant</span>
                            @elseif($account->type === \App\Models\PaymentAccount::TYPE_E_WALLET)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]"><span class="w-1.5 h-1.5 rounded-full bg-[#AF52DE]"></span>e-Wallet</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#0062D1] dark:text-[#0A84FF]"><span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>Transfer Bank</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <form method="POST" action="{{ route('admin.payment-accounts.toggle-status', $account) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border transition-all {{ $account->is_active ? 'bg-[#34C759]/12 border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/18' : 'bg-black/6 dark:bg-white/8 border-black/10 dark:border-white/15 text-black/55 dark:text-white/55 hover:bg-black/10 dark:hover:bg-white/12' }} active:scale-[0.97] active:opacity-80">
                                    <span class="w-2 h-2 rounded-full {{ $account->is_active ? 'bg-[#34C759]' : 'bg-black/40 dark:bg-white/40' }}"></span>
                                    {{ $account->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.payment-accounts.edit', $account) }}" title="Edit Rekening" class="h-7 w-7 rounded-[6px] bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:bg-black/[0.08] dark:hover:bg-white/[0.1] transition-colors inline-flex items-center justify-center"><i data-lucide="edit-3" class="w-4 h-4" stroke-width="1.5"></i></a>
                                <form method="POST" action="{{ route('admin.payment-accounts.destroy', $account) }}" onsubmit="return AppAlert.confirmSubmit(event, this, 'Apakah Anda yakin ingin menghapus rekening {{ addslashes($account->bank_name) }}?', 'Hapus Rekening?', 'danger')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus Rekening" class="h-7 w-7 rounded-[6px] bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] hover:bg-[#FF3B30]/15 transition-colors inline-flex items-center justify-center"><i data-lucide="trash-2" class="w-4 h-4" stroke-width="1.5"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center">
                        <i data-lucide="wallet" class="w-12 h-12 mx-auto text-black/20 dark:text-white/20" stroke-width="1.5"></i>
                        @if($search !== '' || $type !== '')
                        <p class="text-[15px] font-semibold text-black dark:text-white mt-3">Tidak ditemukan rekening</p>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-1">Coba ubah kata kunci pencarian atau pilih tipe lain.</p>
                        @else
                        <p class="text-[15px] font-semibold text-black dark:text-white mt-3">Belum ada rekening pembayaran</p>
                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-1">Klik tombol "Tambah Rekening Baru" untuk membuat metode pembayaran pertama.</p>
                        @endif
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($accounts->hasPages())
        <div class="px-4 py-3 border-t border-black/5 dark:border-white/10">{{ $accounts->links() }}</div>
        @endif
    </div>

    <!-- QRIS Image Modal (Sheet) -->
    <div x-show="qrisModalUrl" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]" @click.self="qrisModalUrl = null">
        <div class="sheet-material rounded-[20px] p-6 border border-black/5 dark:border-white/10 max-w-sm w-full space-y-4 text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <h4 class="text-[15px] font-semibold text-black dark:text-white" x-text="qrisModalTitle"></h4>
                <button type="button" @click="qrisModalUrl = null" class="p-1.5 rounded-[6px] text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/10"><i data-lucide="x" class="w-4 h-4" stroke-width="1.5"></i></button>
            </div>
            <div class="p-2 bg-white rounded-[12px] flex items-center justify-center overflow-hidden">
                <img :src="qrisModalUrl" alt="QRIS Code" class="max-h-72 object-contain rounded-[10px]">
            </div>
            <div class="flex justify-center">
                <a :href="qrisModalUrl" target="_blank" class="text-[13px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1"><i data-lucide="external-link" class="w-3.5 h-3.5" stroke-width="1.5"></i><span>Buka Ukuran Penuh</span></a>
            </div>
        </div>
    </div>
</div>
@endsection