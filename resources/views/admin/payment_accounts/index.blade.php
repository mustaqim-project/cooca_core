@extends('layouts.admin', [
    'title' => 'CMS Rekening & Pembayaran - Admin Console',
    'headerTitle' => 'Kelola Rekening & Pembayaran',
    'headerSubtitle' => 'Kelola rekening bank tujuan transfer dan QRIS resmi yang tampil pada alur checkout langganan tenant Cooca',
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

        <!-- Bento KPI Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-4">
            <!-- Card 1: Total Rekening -->
            <div class="rounded-[20px] backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[12px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">Total Rekening</span>
                    <div class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                        <i data-lucide="credit-card" class="w-4 h-4" stroke-width="2"></i>
                    </div>
                </div>
                <div class="text-[28px] font-bold tabular-nums text-black dark:text-white tracking-tight">
                    {{ number_format($totalCount) }}
                </div>
                <div class="text-[12px] text-black/50 dark:text-white/50 mt-1.5 flex items-center gap-1.5">
                    <i data-lucide="layers" class="w-3.5 h-3.5 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="2"></i>
                    <span>Metode pembayaran terdaftar di platform</span>
                </div>
            </div>

            <!-- Card 2: Aktif di Checkout -->
            <div class="rounded-[20px] backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[12px] font-semibold uppercase tracking-wider text-[#34C759] dark:text-[#30D158]">Aktif di Checkout</span>
                    <div class="w-9 h-9 rounded-[12px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                        <i data-lucide="check-circle" class="w-4 h-4" stroke-width="2"></i>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <div class="text-[28px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight">
                        {{ number_format($activeCount) }}
                    </div>
                    @if($totalCount > 0)
                        <span class="text-[12px] font-semibold text-[#34C759] dark:text-[#30D158] bg-[#34C759]/10 px-2 py-0.5 rounded-full">
                            {{ round(($activeCount / $totalCount) * 100) }}% aktif
                        </span>
                    @endif
                </div>
                <div class="text-[12px] text-black/50 dark:text-white/50 mt-1.5 flex items-center gap-1.5">
                    <i data-lucide="eye" class="w-3.5 h-3.5 text-[#34C759] dark:text-[#30D158]" stroke-width="2"></i>
                    <span>Tampil sebagai opsi bayar bagi tenant</span>
                </div>
            </div>

            <!-- Card 3: QRIS & Instant -->
            <div class="rounded-[20px] backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[12px] font-semibold uppercase tracking-wider text-[#30B0C7] dark:text-[#40C8E0]">QRIS & Saluran Instant</span>
                    <div class="w-9 h-9 rounded-[12px] bg-[#30B0C7]/10 text-[#30B0C7] dark:text-[#40C8E0] flex items-center justify-center shrink-0">
                        <i data-lucide="qr-code" class="w-4 h-4" stroke-width="2"></i>
                    </div>
                </div>
                <div class="text-[28px] font-bold tabular-nums text-[#30B0C7] dark:text-[#40C8E0] tracking-tight">
                    {{ number_format($qrisCount) }}
                </div>
                <div class="text-[12px] text-black/50 dark:text-white/50 mt-1.5 flex items-center gap-1.5">
                    <i data-lucide="zap" class="w-3.5 h-3.5 text-[#30B0C7] dark:text-[#40C8E0]" stroke-width="2"></i>
                    <span>Mendukung scan barcode & e-Wallet</span>
                </div>
            </div>
        </div>

        <!-- Reassuring Note for Boomer / Admins -->
        <div class="rounded-[18px] p-4 bg-[#007AFF]/6 dark:bg-[#007AFF]/10 border border-[#007AFF]/15 flex items-start gap-3">
            <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="shield-check" class="w-4 h-4" stroke-width="2"></i>
            </div>
            <div class="text-[13px] leading-relaxed text-black/75 dark:text-white/75">
                <strong class="text-black dark:text-white font-semibold">💡 Petunjuk Operasional:</strong>
                Rekening dengan status <strong>Aktif</strong> akan otomatis ditampilkan pada tagihan langganan bisnis tenant. Anda dapat mengatur urutan prioritas melalui kolom <em>Nomor Urutan Tampil</em> di setiap rekening.
            </div>
        </div>

        <!-- Bento Search & Filter Toolbar -->
        <div class="rounded-[20px] backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] p-4 shadow-sm flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.payment-accounts.index') }}"
                class="flex flex-col sm:flex-row items-stretch gap-2.5 flex-1 max-w-2xl">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <i data-lucide="search"
                        class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"
                        stroke-width="1.8"></i>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Cari nama bank, kode, nomor rekening, atas nama..."
                        class="w-full h-10 pl-10 pr-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.08] rounded-[12px] text-[16px] md:text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 focus:border-[#007AFF] transition">
                </div>

                <!-- Type Filter -->
                <div class="relative shrink-0 sm:w-52">
                    <select name="type" onchange="this.form.submit()"
                        class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.08] rounded-[12px] text-[16px] md:text-[13px] font-medium text-black/75 dark:text-white/75 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 focus:border-[#007AFF] transition">
                        <option value="">Semua Tipe Pembayaran</option>
                        <option value="bank_transfer" {{ $type === 'bank_transfer' ? 'selected' : '' }}>Transfer Bank Manual</option>
                        <option value="qris" {{ $type === 'qris' ? 'selected' : '' }}>QRIS (Scan Barcode)</option>
                        <option value="e_wallet" {{ $type === 'e_wallet' ? 'selected' : '' }}>e-Wallet</option>
                    </select>
                </div>

                @if ($search !== '' || $type !== '')
                    <a href="{{ route('admin.payment-accounts.index') }}"
                        class="h-10 px-3 rounded-[12px] text-[13px] font-medium text-[#FF3B30] dark:text-[#FF453A] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.97] transition-all inline-flex items-center justify-center gap-1.5 shrink-0">
                        <i data-lucide="x" class="w-3.5 h-3.5" stroke-width="2"></i>
                        <span>Reset Filter</span>
                    </a>
                @endif
            </form>

            <!-- Primary Action Button -->
            <a href="{{ route('admin.payment-accounts.create') }}"
                class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.25)] shrink-0">
                <i data-lucide="plus-circle" class="w-4 h-4" stroke-width="2"></i>
                <span>Tambah Rekening Baru</span>
            </a>
        </div>

        <!-- Bento Accounts Table Container -->
        <div class="rounded-[22px] backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider text-center w-16">
                                Urutan
                            </th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                Bank / Saluran Pembayaran
                            </th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                Nomor Rekening / Kode
                            </th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                Atas Nama (A/N)
                            </th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                Tipe
                            </th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider text-center">
                                Status Checkout
                            </th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider text-right">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($accounts as $account)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <!-- Urutan -->
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] font-mono font-bold text-[12px] text-black/60 dark:text-white/60 tabular-nums">
                                        #{{ $account->sort_order }}
                                    </span>
                                </td>

                                <!-- Bank / Saluran -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-[12px] flex items-center justify-center shrink-0
                                            @if ($account->color === 'blue') bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]
                                            @elseif($account->color === 'amber') bg-[#FF9500]/10 text-[#FF9500] dark:text-[#FF9F0A]
                                            @elseif($account->color === 'cyan') bg-[#30B0C7]/10 text-[#30B0C7] dark:text-[#40C8E0]
                                            @elseif($account->color === 'emerald') bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158]
                                            @elseif($account->color === 'purple') bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2]
                                            @else bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] @endif">
                                            <i data-lucide="{{ $account->icon ?: 'credit-card' }}" class="w-5 h-5" stroke-width="1.8"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-black dark:text-white text-[14px]">
                                                {{ $account->bank_name }}
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="px-1.5 py-0.2 rounded font-mono text-[10px] font-bold uppercase bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60">
                                                    {{ $account->bank_code }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Nomor Rekening -->
                                <td class="px-5 py-3.5">
                                    <div class="space-y-1">
                                        <span class="font-mono font-bold text-black dark:text-white text-[13px] tabular-nums tracking-wide">
                                            {{ $account->account_number }}
                                        </span>
                                        @if ($account->qr_image_path)
                                            <div>
                                                <button type="button"
                                                    @click="openQris('{{ $account->qr_image_url }}', '{{ addslashes($account->bank_name) }}')"
                                                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#34C759] dark:text-[#30D158] bg-[#34C759]/10 hover:bg-[#34C759]/18 px-2 py-0.5 rounded-[6px] transition">
                                                    <i data-lucide="qr-code" class="w-3 h-3" stroke-width="2"></i>
                                                    <span>Lihat QR Code</span>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Atas Nama (A/N) -->
                                <td class="px-5 py-3.5 font-medium text-black/80 dark:text-white/80 text-[13px]">
                                    {{ $account->account_name }}
                                </td>

                                <!-- Tipe Saluran -->
                                <td class="px-5 py-3.5">
                                    @if ($account->type === \App\Models\PaymentAccount::TYPE_QRIS)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#30B0C7]/12 text-[#1F7A89] dark:text-[#40C8E0] border border-[#30B0C7]/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#30B0C7]"></span>QRIS Instant
                                        </span>
                                    @elseif($account->type === \App\Models\PaymentAccount::TYPE_E_WALLET)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2] border border-[#AF52DE]/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#AF52DE]"></span>e-Wallet
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#0062D1] dark:text-[#0A84FF] border border-[#007AFF]/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span>Transfer Bank
                                        </span>
                                    @endif
                                </td>

                                <!-- Status Toggle -->
                                <td class="px-5 py-3.5 text-center">
                                    <form method="POST" action="{{ route('admin.payment-accounts.toggle-status', $account) }}">
                                        @csrf
                                        <button type="submit"
                                            title="Klik untuk mengubah status aktif/nonaktif rekening"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold border transition-all active:scale-[0.97] {{ $account->is_active ? 'bg-[#34C759]/12 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/20' : 'bg-black/[0.04] dark:bg-white/[0.06] border-black/[0.08] dark:border-white/[0.1] text-black/50 dark:text-white/50 hover:bg-black/[0.08] dark:hover:bg-white/[0.1]' }}">
                                            <span class="w-2 h-2 rounded-full {{ $account->is_active ? 'bg-[#34C759]' : 'bg-black/35 dark:bg-white/35' }}"></span>
                                            <span>{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        </button>
                                    </form>
                                </td>

                                <!-- Aksi -->
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.payment-accounts.edit', $account) }}"
                                            title="Edit Rekening"
                                            class="h-8 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.97] transition-all inline-flex items-center gap-1 text-[12px] font-semibold">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5" stroke-width="2"></i>
                                            <span class="hidden sm:inline">Edit</span>
                                        </a>

                                        <form method="POST"
                                            action="{{ route('admin.payment-accounts.destroy', $account) }}"
                                            onsubmit="return AppAlert.confirmSubmit(event, this, 'Apakah Anda yakin ingin menghapus rekening {{ addslashes($account->bank_name) }}? Tindakan ini tidak dapat dibatalkan.', 'Hapus Rekening Pembayaran?', 'danger')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Rekening"
                                                class="h-8 w-8 rounded-[8px] bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] hover:bg-[#FF3B30]/18 active:scale-[0.97] transition-all inline-flex items-center justify-center">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5" stroke-width="2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-14 text-center">
                                    <div class="w-14 h-14 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.05] text-black/30 dark:text-white/30 flex items-center justify-center mx-auto mb-3">
                                        <i data-lucide="wallet" class="w-7 h-7" stroke-width="1.5"></i>
                                    </div>
                                    @if ($search !== '' || $type !== '')
                                        <p class="text-[16px] font-bold text-black dark:text-white">Tidak Ditemukan Rekening</p>
                                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-1 max-w-md mx-auto">
                                            Tidak ada rekening pembayaran yang sesuai dengan kata kunci atau filter tipe yang dipilih.
                                        </p>
                                        <div class="mt-4">
                                            <a href="{{ route('admin.payment-accounts.index') }}"
                                                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition inline-flex items-center gap-1.5">
                                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5" stroke-width="2"></i>
                                                <span>Tampilkan Semua Rekening</span>
                                            </a>
                                        </div>
                                    @else
                                        <p class="text-[16px] font-bold text-black dark:text-white">Belum Ada Rekening Terdaftar</p>
                                        <p class="text-[13px] text-black/50 dark:text-white/50 mt-1 max-w-md mx-auto">
                                            Daftarkan rekening bank tujuan transfer atau kode QRIS pertama Anda agar tenant UMKM dapat membayar langganan.
                                        </p>
                                        <div class="mt-4">
                                            <a href="{{ route('admin.payment-accounts.create') }}"
                                                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] transition inline-flex items-center gap-1.5 shadow-sm">
                                                <i data-lucide="plus-circle" class="w-4 h-4" stroke-width="2"></i>
                                                <span>Tambah Rekening Baru</span>
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($accounts instanceof \Illuminate\Pagination\LengthAwarePaginator && $accounts->hasPages())
                <div class="px-5 py-4 border-t border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.01]">
                    {{ $accounts->links() }}
                </div>
            @endif
        </div>

        <!-- Apple HIG QRIS Preview Modal Sheet -->
        <div x-show="qrisModalUrl" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-md transition-all"
            @click.self="qrisModalUrl = null"
            @keydown.escape.window="qrisModalUrl = null">
            <div
                class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-6 max-w-sm w-full space-y-4 text-center shadow-[0_25px_60px_rgba(0,0,0,0.3)] animate-in fade-in zoom-in-95 duration-200">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                    <div class="flex items-center gap-2 text-left">
                        <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                            <i data-lucide="qr-code" class="w-4 h-4" stroke-width="2"></i>
                        </div>
                        <div>
                            <h4 class="text-[15px] font-bold text-black dark:text-white" x-text="qrisModalTitle"></h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Barcode QRIS Resmi</p>
                        </div>
                    </div>
                    <button type="button" @click="qrisModalUrl = null"
                        class="p-2 rounded-[10px] text-black/40 dark:text-white/40 hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition">
                        <i data-lucide="x" class="w-4 h-4" stroke-width="2"></i>
                    </button>
                </div>

                <!-- QR Image Frame -->
                <div class="p-3 bg-white rounded-[16px] border border-black/[0.06] flex items-center justify-center shadow-inner">
                    <img :src="qrisModalUrl" alt="QRIS Code" class="max-h-72 object-contain rounded-[12px]">
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-between gap-2 pt-1">
                    <a :href="qrisModalUrl" target="_blank"
                        class="h-9 px-3 rounded-[10px] text-[12px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition inline-flex items-center gap-1.5">
                        <i data-lucide="external-link" class="w-3.5 h-3.5" stroke-width="2"></i>
                        <span>Buka Penuh</span>
                    </a>
                    <button type="button" @click="qrisModalUrl = null"
                        class="h-9 px-4 rounded-[10px] text-[12px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection
