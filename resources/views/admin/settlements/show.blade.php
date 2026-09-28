@extends('layouts.admin', ['title' => 'Detail Pencairan Settlement #' . $settlement->settlement_number])

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-16">

    {{-- Header --}}
    <div class="flex items-center justify-between p-5 sm:p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.04] dark:border-white/[0.06] shadow-sm">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('admin.settlements.index') }}" class="hover:underline">Pencairan Merchant</a>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Detail #{{ $settlement->settlement_number }}</span>
            </nav>
            <h1 class="text-[20px] font-bold text-black dark:text-white flex items-center gap-2.5">
                <span>Settlement #{{ $settlement->settlement_number }}</span>
                @if($settlement->status === 'completed')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">Ditransfer</span>
                @elseif($settlement->status === 'pending')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#FF9500]/15 text-[#D97706] dark:text-[#FBBF24]">Menunggu Transfer</span>
                @elseif($settlement->status === 'rejected')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#FF3B30]/15 text-[#FF3B30]">Ditolak</span>
                @endif
                @if($settlement->payout_mode === 'auto_h1')
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]">Auto H+1</span>
                @endif
            </h1>
            <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Toko: <strong>{{ $settlement->business?->name }}</strong> • Tanggal: {{ \Carbon\Carbon::parse($settlement->settlement_date)->format('d F Y') }}</p>
        </div>
        <a href="{{ route('admin.settlements.index') }}"
            class="h-9 px-4 rounded-[10px] text-xs font-medium text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/10 hover:bg-black/10 transition flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali</span>
        </a>
    </div>

    {{-- ========================================================== --}}
    {{-- OWNER IDENTITY VERIFICATION CARD (ANTI-FRAUD)              --}}
    {{-- ========================================================== --}}
    <div class="p-5 sm:p-6 rounded-[22px] border shadow-sm space-y-4
        {{ $identityMatch
            ? 'bg-[#34C759]/[0.03] dark:bg-[#34C759]/[0.05] border-[#34C759]/20'
            : 'bg-[#FF3B30]/[0.03] dark:bg-[#FF3B30]/[0.05] border-[#FF3B30]/20' }}">
        <div class="flex items-center gap-3 pb-3 border-b {{ $identityMatch ? 'border-[#34C759]/15' : 'border-[#FF3B30]/15' }}">
            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0
                {{ $identityMatch ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#FF3B30]/15 text-[#FF3B30]' }}">
                <i data-lucide="{{ $identityMatch ? 'shield-check' : 'shield-alert' }}" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-[15px] font-bold {{ $identityMatch ? 'text-[#248A3D] dark:text-[#30D158]' : 'text-[#FF3B30]' }}">
                    {{ $identityMatch ? 'Identitas Pemilik Terverifikasi ✓' : '⚠️ Identitas Pemilik TIDAK COCOK' }}
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50">
                    {{ $identityMatch
                        ? 'Nama pemilik bisnis cocok dengan nama pemegang rekening bank tujuan.'
                        : 'PERHATIAN: Nama pemilik bisnis TIDAK sama dengan pemegang rekening bank. Periksa manual sebelum menyetujui.' }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-3.5 rounded-[14px] bg-white/80 dark:bg-black/20 border border-black/5 dark:border-white/5">
                <span class="text-[10px] text-black/45 dark:text-white/45 block font-medium uppercase tracking-wider mb-1">Nama Pemilik Bisnis (Owner)</span>
                <span class="text-[14px] font-bold text-black dark:text-white">{{ $ownerName }}</span>
            </div>
            <div class="p-3.5 rounded-[14px] bg-white/80 dark:bg-black/20 border border-black/5 dark:border-white/5">
                <span class="text-[10px] text-black/45 dark:text-white/45 block font-medium uppercase tracking-wider mb-1">Nama Pemegang Rekening Bank</span>
                <span class="text-[14px] font-bold {{ $identityMatch ? 'text-[#34C759]' : 'text-[#FF3B30]' }}">{{ $accountHolderName }}</span>
            </div>
        </div>

        @if($settlement->payoutBankAccount)
            <div class="p-3.5 rounded-[14px] bg-white/80 dark:bg-black/20 border border-black/5 dark:border-white/5 text-xs text-black/70 dark:text-white/70 space-y-1">
                <div class="flex items-center gap-2">
                    <span class="font-medium text-black/45 dark:text-white/45">Bank:</span>
                    <span class="font-bold">{{ $settlement->payoutBankAccount->bank_name }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-medium text-black/45 dark:text-white/45">No. Rekening:</span>
                    <span class="font-mono font-bold">{{ $settlement->payoutBankAccount->account_number }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-medium text-black/45 dark:text-white/45">Status Verifikasi:</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $settlement->payoutBankAccount->is_verified ? 'bg-[#34C759]/15 text-[#248A3D]' : 'bg-[#FF9500]/15 text-[#D97706]' }}">
                        {{ $settlement->payoutBankAccount->is_verified ? 'Terverifikasi ✓' : 'Belum Verifikasi' }}
                    </span>
                </div>
            </div>
        @endif
    </div>

    {{-- 3 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4.5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-1">
            <span class="text-xs text-black/50 dark:text-white/50 block font-medium">Nominal Bruto</span>
            <span class="text-xl font-extrabold text-black dark:text-white tabular-nums">
                Rp {{ number_format($settlement->gross_amount, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-black/45 dark:text-white/45 block">Akun Kliring: 1-1005 (Escrow)</span>
        </div>

        <div class="p-4.5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-1">
            <span class="text-xs text-black/50 dark:text-white/50 block font-medium">Fee MDR Gateway</span>
            <span class="text-xl font-extrabold text-[#FF3B30] tabular-nums">
                -Rp {{ number_format($settlement->fee_amount, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-black/45 dark:text-white/45 block">Biaya resmi Cooca Pay</span>
        </div>

        <div class="p-4.5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-1">
            <span class="text-xs text-black/50 dark:text-white/50 block font-medium">Nominal Bersih Ditransfer</span>
            <span class="text-xl font-extrabold text-[#34C759] dark:text-[#30D158] tabular-nums">
                Rp {{ number_format($settlement->net_amount, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-black/45 dark:text-white/45 block">{{ $settlement->destination_bank ?? 'Rekening Toko' }}</span>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- APPROVE / REJECT ACTION SECTION (only for pending)         --}}
    {{-- ========================================================== --}}
    @if($settlement->status === 'pending')
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden" x-data="{ activeTab: 'approve' }">
            <div class="px-5 py-4 border-b border-black/5 dark:border-white/10 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="banknote" class="w-4 h-4"></i>
                </div>
                <div>
                    <h2 class="text-[15px] font-bold text-black dark:text-white">Aksi Admin: Setujui atau Tolak Pencairan</h2>
                    <p class="text-xs text-black/50 dark:text-white/50">Transfer manual ke rekening merchant, upload bukti, lalu setujui. Notifikasi WhatsApp akan terkirim otomatis.</p>
                </div>
            </div>

            {{-- Tab switcher --}}
            <div class="flex border-b border-black/5 dark:border-white/10">
                <button @click="activeTab = 'approve'"
                    :class="activeTab === 'approve' ? 'text-[#34C759] border-[#34C759]' : 'text-black/50 dark:text-white/50 border-transparent'"
                    class="flex-1 py-3 text-xs font-bold text-center border-b-2 transition">
                    ✅ Setujui &amp; Upload Bukti
                </button>
                <button @click="activeTab = 'reject'"
                    :class="activeTab === 'reject' ? 'text-[#FF3B30] border-[#FF3B30]' : 'text-black/50 dark:text-white/50 border-transparent'"
                    class="flex-1 py-3 text-xs font-bold text-center border-b-2 transition">
                    ❌ Tolak Pencairan
                </button>
            </div>

            {{-- Approve Form --}}
            <div x-show="activeTab === 'approve'" class="p-5 space-y-4">
                <form action="{{ route('admin.settlements.approve', $settlement) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">Upload Bukti Transfer Bank *</label>
                        <input type="file" name="proof_image" accept="image/jpeg,image/png,image/webp" required
                            class="w-full text-xs file:mr-3 file:py-2 file:px-4 file:rounded-[10px] file:border-0 file:text-xs file:font-semibold file:bg-[#34C759]/10 file:text-[#34C759] hover:file:bg-[#34C759]/20 text-black/60 dark:text-white/60">
                        <p class="text-[10px] text-black/40 dark:text-white/40 mt-1">Foto/screenshot struk transfer bank ke rekening merchant. Maks. 5MB, format JPG/PNG/WebP.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">Catatan Admin (Opsional)</label>
                        <input type="text" name="admin_notes" placeholder="Contoh: Transfer BCA #01234 jam 14:30"
                            class="w-full h-10 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 text-xs text-black dark:text-white focus:ring-2 focus:ring-[#34C759] focus:outline-none">
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-black/5 dark:border-white/10">
                        <span class="text-xs text-black/50 dark:text-white/50">
                            Notifikasi WhatsApp akan otomatis terkirim ke pemilik bisnis.
                        </span>
                        <button type="submit"
                            class="h-10 px-6 rounded-[10px] bg-[#34C759] hover:bg-[#28A745] text-white text-xs font-bold transition active:scale-[0.98] flex items-center gap-2 shadow-sm">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            Setujui &amp; Kirim Bukti
                        </button>
                    </div>
                </form>
            </div>

            {{-- Reject Form --}}
            <div x-show="activeTab === 'reject'" x-cloak class="p-5 space-y-4">
                <form action="{{ route('admin.settlements.reject', $settlement) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1.5">Alasan Penolakan *</label>
                        <textarea name="rejection_reason" rows="3" required placeholder="Jelaskan alasan penolakan pencairan saldo..."
                            class="w-full rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 px-3 py-2.5 text-xs text-black dark:text-white focus:ring-2 focus:ring-[#FF3B30] focus:outline-none resize-none"></textarea>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-black/5 dark:border-white/10">
                        <span class="text-xs text-[#FF3B30]">
                            Transaksi yang teralokasi akan dikembalikan ke saldo mengendap merchant.
                        </span>
                        <button type="submit"
                            class="h-10 px-6 rounded-[10px] bg-[#FF3B30] hover:bg-[#D70015] text-white text-xs font-bold transition active:scale-[0.98] flex items-center gap-2 shadow-sm">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            Tolak Pencairan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Rejection reason card --}}
    @if($settlement->status === 'rejected' && $settlement->rejection_reason)
        <div class="p-5 rounded-[22px] bg-[#FF3B30]/5 dark:bg-[#FF3B30]/10 border border-[#FF3B30]/20 space-y-2">
            <div class="flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-[#FF3B30]"></i>
                <h3 class="text-[13px] font-bold text-[#FF3B30]">Alasan Penolakan</h3>
            </div>
            <p class="text-xs text-black/70 dark:text-white/70">{{ $settlement->rejection_reason }}</p>
        </div>
    @endif

    {{-- Bukti Transfer Card --}}
    @if($settlement->proof_image_path)
        <div class="p-6 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                        <i data-lucide="image" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-[15px] font-bold text-black dark:text-white">Bukti Transfer Bank Resmi</h3>
                        <p class="text-xs text-black/50 dark:text-white/50">Ditransfer pada {{ $settlement->transferred_at ? $settlement->transferred_at->format('d M Y H:i') : '-' }} oleh {{ $settlement->admin?->name ?? 'Admin COOCA' }}</p>
                    </div>
                </div>
                <a href="{{ $settlement->proof_image_url }}" target="_blank" download
                    class="h-8 px-3 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] text-xs font-semibold hover:bg-[#007AFF]/20 transition flex items-center gap-1.5">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    <span>Unduh Bukti</span>
                </a>
            </div>

            <div class="flex justify-center p-2 rounded-[16px] bg-black/[0.02] dark:bg-black/30 border border-black/5 dark:border-white/5">
                <img src="{{ $settlement->proof_image_url }}" alt="Struk Transfer" class="max-h-96 rounded-[12px] object-contain shadow-xs">
            </div>

            @if($settlement->admin_notes)
                <div class="p-3 rounded-[12px] bg-blue-50/50 dark:bg-blue-950/20 text-xs text-blue-900 dark:text-blue-200">
                    <span class="font-bold">Catatan Admin:</span> {{ $settlement->admin_notes }}
                </div>
            @endif
        </div>
    @endif

    {{-- Allocations Table --}}
    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-black/5 dark:border-white/10">
            <h2 class="text-[15px] font-bold text-black dark:text-white">Transaksi Pesanan yang Teralokasi ({{ $settlement->allocations->count() }} Item)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.03] border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-semibold">
                    <tr>
                        <th class="py-2.5 px-4">Tipe Transaksi</th>
                        <th class="py-2.5 px-4">ID Referensi</th>
                        <th class="py-2.5 px-4 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 text-black/80 dark:text-white/80">
                    @foreach($settlement->allocations as $a)
                        <tr>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-md font-semibold text-[10px] bg-[#007AFF]/10 text-[#007AFF]">
                                    {{ $a->payment_type === 'commerce_order' ? 'Toko Online Storefront' : ($a->payment_type === 'pos_order_payment' ? 'Kasir POS / Meja' : 'Invoice') }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono">{{ $a->payment_id }}</td>
                            <td class="py-3 px-4 text-right font-bold tabular-nums">Rp {{ number_format($a->amount, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
