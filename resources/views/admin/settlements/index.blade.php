@extends('layouts.admin', ['title' => 'Pencairan Saldo Merchant & Upload Bukti Bayar'])

@section('content')
<div class="space-y-6 pb-16" x-data="adminSettlementHub()">

    {{-- ========================================================== --}}
    {{-- TOOLBAR / HEADER                                           --}}
    {{-- ========================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 sm:p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.04] dark:border-white/[0.06] shadow-sm">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <span>Platform Operations</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Pencairan Merchant</span>
            </nav>
            <h1 class="text-[22px] font-bold tracking-tight text-black dark:text-white">Pencairan Saldo &amp; Bukti Transfer</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Kelola pengajuan pencairan saldo gateway non-tunai (QRIS &amp; VA TriPay) seluruh tenant merchant COOCA</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-[#007AFF]/10 text-[#007AFF] flex items-center gap-1.5">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                <span>TriPay Central Escrow</span>
            </span>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- BENTO KPI METRIC CARDS                                     --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Pending Payout Requests --}}
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.04] dark:border-white/[0.06] shadow-sm space-y-2 relative overflow-hidden">
            @if($stats['pending_count'] > 0)
                <div class="absolute top-0 right-0 w-2 h-full bg-[#FF9500]"></div>
            @endif
            <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                <span class="font-medium">Menunggu Transfer Admin</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/10 text-[#D97706] dark:text-[#FBBF24]">Pending</span>
            </div>
            <div class="text-[24px] font-extrabold text-black dark:text-white tabular-nums tracking-tight">
                Rp {{ number_format($stats['pending_amount'], 0, ',', '.') }}
            </div>
            <div class="text-[11.5px] text-black/50 dark:text-white/50 flex items-center justify-between">
                <span>{{ $stats['pending_count'] }} pengajuan siap dicairkan</span>
                @if($stats['pending_count'] > 0)
                    <span class="font-bold text-[#FF9500]">Wajib Diproses</span>
                @endif
            </div>
        </div>

        {{-- Card 2: Completed Payouts This Month --}}
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.04] dark:border-white/[0.06] shadow-sm space-y-2">
            <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                <span class="font-medium">Telah Ditransfer Bulan Ini</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">Bulan Ini</span>
            </div>
            <div class="text-[24px] font-extrabold text-[#34C759] dark:text-[#30D158] tabular-nums tracking-tight">
                Rp {{ number_format($stats['completed_amount_this_month'], 0, ',', '.') }}
            </div>
            <div class="text-[11.5px] text-black/50 dark:text-white/50">
                {{ $stats['completed_count_this_month'] }} pencairan berhasil diselesaikan
            </div>
        </div>

        {{-- Card 3: Total Fee MDR Collected --}}
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.04] dark:border-white/[0.06] shadow-sm space-y-2">
            <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                <span class="font-medium">Total Fee MDR Terpotong</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/10 text-[#FF3B30]">Biaya Gateway</span>
            </div>
            <div class="text-[24px] font-extrabold text-[#FF3B30] tabular-nums tracking-tight">
                Rp {{ number_format($stats['total_fee_mdr'], 0, ',', '.') }}
            </div>
            <div class="text-[11.5px] text-black/50 dark:text-white/50">
                Fee resmi TriPay (0,7% + Rp 750)
            </div>
        </div>

        {{-- Card 4: Total Gross All Time --}}
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.04] dark:border-white/[0.06] shadow-sm space-y-2">
            <div class="flex items-center justify-between text-xs text-black/50 dark:text-white/50">
                <span class="font-medium">Akumulasi Gross Cair</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]">All Time</span>
            </div>
            <div class="text-[24px] font-extrabold text-[#007AFF] tabular-nums tracking-tight">
                Rp {{ number_format($stats['total_gross_all_time'], 0, ',', '.') }}
            </div>
            <div class="text-[11.5px] text-black/50 dark:text-white/50">
                Total omzet non-tunai dicairkan
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- FILTER TABS & SEARCH BAR                                   --}}
    {{-- ========================================================== --}}
    <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.04] dark:border-white/[0.06] flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
        {{-- Status Filter Tabs --}}
        <div class="flex items-center gap-1.5 p-1 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] overflow-x-auto">
            @foreach(['all' => 'Semua', 'pending' => 'Menunggu Transfer', 'completed' => 'Selesai Ditransfer', 'rejected' => 'Ditolak'] as $key => $label)
                <a href="{{ request()->fullUrlWithQuery(['status' => $key]) }}"
                    class="px-3.5 py-1.5 rounded-[9px] text-[12px] font-semibold transition whitespace-nowrap {{ $status === $key ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white' }}">
                    <span>{{ $label }}</span>
                    @if($key === 'pending' && $stats['pending_count'] > 0)
                        <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-[#FF9500] text-white">
                            {{ $stats['pending_count'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Search & Date Filters --}}
        <form method="GET" action="{{ route('admin.settlements.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari no. settlement / toko / bank..."
                    class="w-56 sm:w-64 h-9 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] px-3 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
            </div>
            <button type="submit"
                class="h-9 px-3.5 rounded-[10px] bg-[#007AFF] text-white text-xs font-semibold hover:bg-[#0071E3] transition">
                Filter
            </button>
            @if($search || request('date_from'))
                <a href="{{ route('admin.settlements.index') }}"
                    class="h-9 px-2.5 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 text-xs font-medium hover:text-black dark:hover:text-white flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- ========================================================== --}}
    {{-- SETTLEMENT TABLE LIST                                      --}}
    {{-- ========================================================== --}}
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.04] dark:border-white/[0.06] overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.03] border-b border-black/[0.04] dark:border-white/[0.06] text-black/60 dark:text-white/60 font-semibold">
                    <tr>
                        <th class="py-3 px-4">Toko / Merchant</th>
                        <th class="py-3 px-4">No. Settlement</th>
                        <th class="py-3 px-4">Tanggal Diajukan</th>
                        <th class="py-3 px-4 text-right">Nominal Bruto</th>
                        <th class="py-3 px-4 text-right">Fee MDR</th>
                        <th class="py-3 px-4 text-right">Wajib Transfer (Net)</th>
                        <th class="py-3 px-4">Rekening Tujuan Bank</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Bukti Bayar &amp; Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.05] text-black/80 dark:text-white/80">
                    @forelse($settlements as $settlement)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition">
                            {{-- Merchant Business --}}
                            <td class="py-3.5 px-4 font-medium">
                                <div class="font-bold text-black dark:text-white text-[13px]">
                                    {{ $settlement->business?->name ?? 'Toko Tidak Dikenal' }}
                                </div>
                                <div class="text-[11px] text-black/45 dark:text-white/45">
                                    Owner: {{ $settlement->reconciler?->name ?? ($settlement->business?->owner?->name ?? '-') }}
                                </div>
                            </td>

                            {{-- Settlement Number --}}
                            <td class="py-3.5 px-4 font-mono font-medium text-black dark:text-white">
                                #{{ $settlement->settlement_number }}
                                <div class="text-[10px] text-black/45 dark:text-white/45 font-sans">
                                    {{ $settlement->allocations->count() }} transaksi pesanan
                                </div>
                            </td>

                            {{-- Date --}}
                            <td class="py-3.5 px-4 text-black/60 dark:text-white/60">
                                {{ \Carbon\Carbon::parse($settlement->settlement_date)->format('d M Y') }}
                            </td>

                            {{-- Gross --}}
                            <td class="py-3.5 px-4 text-right tabular-nums font-medium text-black/70 dark:text-white/70">
                                Rp {{ number_format($settlement->gross_amount, 0, ',', '.') }}
                            </td>

                            {{-- Fee MDR --}}
                            <td class="py-3.5 px-4 text-right tabular-nums text-[#FF3B30] font-medium">
                                -Rp {{ number_format($settlement->fee_amount, 0, ',', '.') }}
                            </td>

                            {{-- Net Payout Amount --}}
                            <td class="py-3.5 px-4 text-right tabular-nums font-extrabold text-[13px] text-[#34C759] dark:text-[#30D158]">
                                Rp {{ number_format($settlement->net_amount, 0, ',', '.') }}
                            </td>

                            {{-- Destination Bank --}}
                            <td class="py-3.5 px-4 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-black dark:text-white">{{ $settlement->destination_bank ?: 'Belum ditentukan' }}</span>
                                    @if($settlement->destination_bank)
                                        <button type="button" @click="copyToClipboard('{{ $settlement->destination_bank }}')" title="Salin rekening"
                                            class="p-1 rounded hover:bg-black/5 dark:hover:bg-white/10 text-black/40 dark:text-white/40">
                                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>

                            {{-- Status Badge --}}
                            <td class="py-3.5 px-4 text-center">
                                @if($settlement->status === 'completed')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                        Ditransfer
                                    </span>
                                @elseif($settlement->status === 'pending')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF9500]/15 text-[#D97706] dark:text-[#FBBF24]">
                                        Menunggu Transfer
                                    </span>
                                @elseif($settlement->status === 'rejected')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-black/10 text-black/70">
                                        {{ ucfirst($settlement->status) }}
                                    </span>
                                @endif
                            </td>

                            {{-- Action & Proof Button --}}
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($settlement->status === 'completed' && $settlement->proof_image_path)
                                        <button type="button"
                                            @click="openViewProofModal('{{ $settlement->proof_image_url }}', '{{ $settlement->settlement_number }}', '{{ $settlement->business?->name }}', '{{ number_format($settlement->net_amount, 0, ',', '.') }}', '{{ $settlement->destination_bank }}', '{{ $settlement->transferred_at ? $settlement->transferred_at->format('d M Y H:i') : '-' }}', '{{ addslashes($settlement->admin_notes ?? '') }}')"
                                            class="h-8 px-3 rounded-[9px] text-[11.5px] font-semibold bg-[#34C759]/10 hover:bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] transition flex items-center gap-1.5 shadow-xs">
                                            <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                            <span>Lihat Bukti</span>
                                        </button>
                                    @elseif($settlement->status === 'pending')
                                        <button type="button"
                                            @click="openUploadModal('{{ $settlement->id }}', '{{ $settlement->settlement_number }}', '{{ $settlement->business?->name }}', '{{ number_format($settlement->net_amount, 0, ',', '.') }}', '{{ $settlement->destination_bank }}')"
                                            class="h-8 px-3 rounded-[9px] text-[11.5px] font-semibold bg-[#007AFF] hover:bg-[#0071E3] text-white transition flex items-center gap-1.5 shadow-xs">
                                            <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                                            <span>Transfer &amp; Upload Bukti</span>
                                        </button>

                                        <button type="button"
                                            @click="openRejectModal('{{ $settlement->id }}', '{{ $settlement->settlement_number }}')"
                                            title="Tolak Pengajuan"
                                            class="h-8 w-8 rounded-[9px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] transition flex items-center justify-center">
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                        </button>
                                    @elseif($settlement->status === 'rejected')
                                        <span class="text-[11px] text-[#FF3B30] font-medium" title="{{ $settlement->rejection_reason }}">
                                            Ditolak: {{ \Illuminate\Support\Str::limit($settlement->rejection_reason ?? '-', 20) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-black/40 dark:text-white/40">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="inbox" class="w-8 h-8 opacity-40"></i>
                                    <span>Tidak ada data pengajuan pencairan saldo yang sesuai filter.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($settlements->hasPages())
            <div class="p-4 border-t border-black/[0.04] dark:border-white/[0.06]">
                {{ $settlements->links() }}
            </div>
        @endif
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL 1: UPLOAD BUKTI BAYAR & APPROVE SETTLEMENT           --}}
    {{-- ========================================================== --}}
    <div x-show="showUploadModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="showUploadModal = false">
        <div class="w-full max-w-lg rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden"
            @click.outside="showUploadModal = false">
            
            <div class="px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-[15px] font-bold text-black dark:text-white">Upload Bukti Transfer Bank</h3>
                        <p class="text-[11.5px] text-black/50 dark:text-white/50" x-text="'Settlement #' + currentSettlementNumber + ' • ' + currentBusinessName"></p>
                    </div>
                </div>
                <button type="button" @click="showUploadModal = false" class="p-1.5 rounded-lg hover:bg-black/5 dark:hover:bg-white/10 text-black/40 dark:text-white/40">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="'/admin/settlements/' + currentSettlementId + '/approve'" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf

                {{-- Summary Payout Target --}}
                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.05] space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-black/50 dark:text-white/50 font-medium">Nominal Wajib Ditransfer:</span>
                        <span class="text-[16px] font-extrabold text-[#34C759] dark:text-[#30D158] tabular-nums" x-text="'Rp ' + currentNetAmount"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs pt-1 border-t border-black/5 dark:border-white/5">
                        <span class="text-black/50 dark:text-white/50 font-medium">Rekening Tujuan Merchant:</span>
                        <span class="font-bold text-black dark:text-white" x-text="currentDestinationBank"></span>
                    </div>
                </div>

                {{-- Image File Input & Preview --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-black dark:text-white">Foto / Struk Bukti Transfer (JPG, PNG, WEBP, Maks. 5MB) *</label>
                    <div class="relative border-2 border-dashed border-black/15 dark:border-white/15 rounded-[16px] p-4 text-center hover:border-[#007AFF] transition bg-black/[0.01] dark:bg-white/[0.01]">
                        <input type="file" name="proof_image" accept="image/jpeg,image/png,image/jpg,image/webp" required
                            @change="handleFilePreview($event)"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        
                        <template x-if="!imagePreview">
                            <div class="flex flex-col items-center justify-center gap-2 py-3">
                                <div class="w-10 h-10 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                    <i data-lucide="image-plus" class="w-5 h-5"></i>
                                </div>
                                <div class="text-xs font-semibold text-black dark:text-white">Pilih file gambar atau seret ke sini</div>
                                <p class="text-[11px] text-black/40 dark:text-white/40">Bukti transfer dari m-banking, internet banking, atau ATM</p>
                            </div>
                        </template>

                        <template x-if="imagePreview">
                            <div class="space-y-2">
                                <img :src="imagePreview" alt="Preview Bukti Bayar" class="max-h-48 mx-auto rounded-[12px] object-contain shadow-xs border border-black/10">
                                <div class="text-[11.5px] font-medium text-[#34C759] flex items-center justify-center gap-1">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                    <span>Gambar bukti transfer siap diunggah</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Admin Notes / Reference --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-black dark:text-white">Catatan Transfer / No. Referensi Bank (Opsional)</label>
                    <input type="text" name="admin_notes" placeholder="Contoh: No. Ref BCA 981249812 - Ditransfer via Finance COOCA"
                        class="w-full h-10 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 px-3 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                {{-- Submit Action Buttons --}}
                <div class="pt-2 flex items-center justify-end gap-2.5">
                    <button type="button" @click="showUploadModal = false"
                        class="h-10 px-4 rounded-[12px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/10 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-10 px-5 rounded-[12px] bg-[#34C759] hover:bg-[#2FB350] text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm active:scale-[0.98]">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Konfirmasi Selesai &amp; Kirim Bukti</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL 2: LIHAT BUKTI TRANSFER (FULL PREVIEW)               --}}
    {{-- ========================================================== --}}
    <div x-show="showViewProofModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-md"
        @keydown.escape.window="showViewProofModal = false">
        <div class="w-full max-w-xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden"
            @click.outside="showViewProofModal = false">
            
            <div class="px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Bukti Transfer Pencairan</h3>
                    <p class="text-[11.5px] text-black/50 dark:text-white/50" x-text="'Settlement #' + viewSettlementNumber + ' • ' + viewBusinessName"></p>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="viewImageUrl" target="_blank" download title="Unduh Gambar"
                        class="p-1.5 rounded-lg hover:bg-black/5 dark:hover:bg-white/10 text-black/60 dark:text-white/60">
                        <i data-lucide="download" class="w-4 h-4"></i>
                    </a>
                    <button type="button" @click="showViewProofModal = false" class="p-1.5 rounded-lg hover:bg-black/5 dark:hover:bg-white/10 text-black/40 dark:text-white/40">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <div class="p-6 space-y-4">
                {{-- Image Display Container --}}
                <div class="rounded-[18px] bg-black/[0.03] dark:bg-black/40 border border-black/[0.05] dark:border-white/5 p-2 flex items-center justify-center overflow-hidden">
                    <img :src="viewImageUrl" alt="Bukti Transfer Struk Bank" class="max-h-[380px] w-auto rounded-[12px] object-contain">
                </div>

                {{-- Transfer Details Bar --}}
                <div class="grid grid-cols-2 gap-3 text-xs p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <div>
                        <span class="text-black/45 dark:text-white/45 block text-[11px]">Nominal Ditransfer:</span>
                        <span class="font-bold text-[#34C759] text-[13px] tabular-nums" x-text="'Rp ' + viewNetAmount"></span>
                    </div>
                    <div>
                        <span class="text-black/45 dark:text-white/45 block text-[11px]">Rekening Tujuan:</span>
                        <span class="font-semibold text-black dark:text-white" x-text="viewDestinationBank"></span>
                    </div>
                    <div>
                        <span class="text-black/45 dark:text-white/45 block text-[11px]">Waktu Transfer:</span>
                        <span class="font-medium text-black dark:text-white" x-text="viewTransferredAt"></span>
                    </div>
                    <div x-show="viewAdminNotes">
                        <span class="text-black/45 dark:text-white/45 block text-[11px]">Catatan Admin:</span>
                        <span class="font-medium text-black dark:text-white" x-text="viewAdminNotes"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL 3: TOLAK PENGAJUAN SETTLEMENT                        --}}
    {{-- ========================================================== --}}
    <div x-show="showRejectModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="showRejectModal = false">
        <div class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 space-y-4"
            @click.outside="showRejectModal = false">
            
            <div class="flex items-center gap-2.5 text-[#FF3B30]">
                <div class="w-9 h-9 rounded-full bg-[#FF3B30]/10 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Tolak Pengajuan Pencairan</h3>
                    <p class="text-[11.5px] text-black/50 dark:text-white/50" x-text="'Settlement #' + rejectSettlementNumber"></p>
                </div>
            </div>

            <form :action="'/admin/settlements/' + rejectSettlementId + '/reject'" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">Alasan Penolakan *</label>
                    <textarea name="rejection_reason" rows="3" required placeholder="Contoh: Nomor rekening tidak valid atau nama pemilik tidak sesuai..."
                        class="w-full rounded-[12px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 p-3 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showRejectModal = false"
                        class="h-9 px-4 rounded-[10px] text-xs font-medium text-black/70 dark:text-white/70 hover:bg-black/5">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-9 px-4 rounded-[10px] bg-[#FF3B30] text-white text-xs font-bold hover:bg-[#E03429] transition">
                        Konfirmasi Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function adminSettlementHub() {
    return {
        showUploadModal: false,
        currentSettlementId: '',
        currentSettlementNumber: '',
        currentBusinessName: '',
        currentNetAmount: '',
        currentDestinationBank: '',
        imagePreview: null,

        showViewProofModal: false,
        viewImageUrl: '',
        viewSettlementNumber: '',
        viewBusinessName: '',
        viewNetAmount: '',
        viewDestinationBank: '',
        viewTransferredAt: '',
        viewAdminNotes: '',

        showRejectModal: false,
        rejectSettlementId: '',
        rejectSettlementNumber: '',

        openUploadModal(id, number, business, net, bank) {
            this.currentSettlementId = id;
            this.currentSettlementNumber = number;
            this.currentBusinessName = business;
            this.currentNetAmount = net;
            this.currentDestinationBank = bank;
            this.imagePreview = null;
            this.showUploadModal = true;
            this.$nextTick(() => { lucide.createIcons(); });
        },

        openViewProofModal(url, number, business, net, bank, time, notes) {
            this.viewImageUrl = url;
            this.viewSettlementNumber = number;
            this.viewBusinessName = business;
            this.viewNetAmount = net;
            this.viewDestinationBank = bank;
            this.viewTransferredAt = time;
            this.viewAdminNotes = notes;
            this.showViewProofModal = true;
            this.$nextTick(() => { lucide.createIcons(); });
        },

        openRejectModal(id, number) {
            this.rejectSettlementId = id;
            this.rejectSettlementNumber = number;
            this.showRejectModal = true;
            this.$nextTick(() => { lucide.createIcons(); });
        },

        handleFilePreview(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                    this.$nextTick(() => { lucide.createIcons(); });
                };
                reader.readAsDataURL(file);
            }
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Rekening bank disalin ke clipboard: ' + text);
            });
        }
    };
}
</script>
@endsection
