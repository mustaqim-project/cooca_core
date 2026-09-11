@extends('layouts.admin', [
    'title' => 'Detail Workspace: ' . $business->name . ' — Admin Console',
    'headerTitle' => 'Workspace: ' . $business->name,
    'headerSubtitle' => 'Detail lengkap profil tenant, kuota sumber daya, pengguna, dan status langganan'
])

@section('content')
<div class="space-y-6">

    <!-- Top Navigation & Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <a href="{{ route('admin.businesses.index') }}" class="text-[13px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1.5 transition">
            <i data-lucide="arrow-left" class="w-4 h-4" stroke-width="1.5"></i><span>Kembali ke Daftar Bisnis</span>
        </a>
        <form method="POST" action="{{ route('admin.businesses.toggle-status', $business->id) }}" onsubmit="return AppAlert.confirmSubmit(event, this, 'Apakah Anda yakin ingin mengubah status aktif bisnis ini?', 'Ubah Status Bisnis?', 'warning')">
            @csrf
            @if($business->is_active)
                <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5"><i data-lucide="ban" class="w-4 h-4" stroke-width="1.5"></i><span>Tangguhkan Workspace (Suspend)</span></button>
            @else
                <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]"><i data-lucide="check-circle" class="w-4 h-4" stroke-width="1.5"></i><span>Aktifkan Kembali Workspace</span></button>
            @endif
        </form>
    </div>

    <!-- Workspace Identity Card -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-2">
                @if($business->is_active)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]"><span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>Status: Aktif</span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]"><span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>Status: Ditangguhkan</span>
                @endif
                @if($business->subscription?->isCorePlan())
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]"><i data-lucide="shield-check" class="w-3 h-3" stroke-width="1.5"></i>PAKET CORE ({{ strtoupper($business->subscription->plan_code) }})</span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55"><i data-lucide="gift" class="w-3 h-3" stroke-width="1.5"></i>FREE PLAN</span>
                @endif
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]"><i data-lucide="users" class="w-3 h-3" stroke-width="1.5"></i>{{ $isSoloMode ? 'Mode Solo Owner (1 User)' : 'Mode Tim Delegasi' }}</span>
            </div>

            <h2 class="text-[24px] font-bold text-black dark:text-white tracking-tight">{{ $business->name }}</h2>

            <div class="flex flex-wrap items-center gap-3 text-[12px] text-black/50 dark:text-white/50 tabular-nums">
                <span>Slug: {{ $business->slug }}</span>
                <span class="text-black/30 dark:text-white/30">·</span>
                <span>Mata Uang: {{ $business->currency ?? 'IDR' }} ({{ $business->currency_symbol }})</span>
                <span class="text-black/30 dark:text-white/30">·</span>
                <span>Dibuat: {{ $business->created_at?->format('d M Y') }}</span>
            </div>

            @if(!$business->is_active && $business->suspended_reason)
                <div class="p-2.5 rounded-[10px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[12px] text-[#C41E17] dark:text-[#FF453A] mt-2">
                    <strong>Alasan Penangguhan:</strong> {{ $business->suspended_reason }} (sejak {{ $business->suspended_at?->format('d M Y H:i') }})
                </div>
            @endif
        </div>
    </div>

    <!-- 4 Resources KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50 block">Total Produk</span>
            <div class="text-[24px] font-bold tabular-nums text-black dark:text-white mt-1">{{ $productCount }}</div>
            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">Katalog item toko</div>
        </div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50 block">Resep / BOM</span>
            <div class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] mt-1">{{ $bomCount }}</div>
            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">Struktur biaya HPP</div>
        </div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50 block">Transaksi Toko</span>
            <div class="text-[24px] font-bold tabular-nums text-black dark:text-white mt-1">{{ $invoiceCount + $posOrderCount }}</div>
            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">{{ $posOrderCount }} POS · {{ $invoiceCount }} Faktur</div>
        </div>
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50 block">Token AI Terpakai</span>
            <div class="text-[24px] font-bold tabular-nums text-[#AF52DE] dark:text-[#BF5AF2] mt-1">{{ number_format($totalAiTokensUsed, 0, ',', '.') }}</div>
            <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">Konsumsi Google Gemini</div>
        </div>
    </div>

    <!-- Two-Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="space-y-6 lg:col-span-7">

            <!-- Commercial Profile -->
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6">
                <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2 border-b border-black/5 dark:border-white/10 pb-3">
                    <i data-lucide="store" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i>
                    <span>Informasi Komersial &amp; Finansial</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 text-[13px]">
                    <div>
                        <div class="text-[12px] font-medium text-black/50 dark:text-white/50">Email Bisnis</div>
                        <div class="text-black/80 dark:text-white/80 font-medium mt-0.5">{{ $business->email ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-[12px] font-medium text-black/50 dark:text-white/50">Nomor Telepon</div>
                        <div class="text-black/80 dark:text-white/80 font-medium mt-0.5">{{ $business->phone ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-[12px] font-medium text-black/50 dark:text-white/50">Bank &amp; No Rekening</div>
                        <div class="text-black/80 dark:text-white/80 tabular-nums mt-0.5">{{ $business->bank_name ? $business->bank_name . ' - ' . $business->bank_account_number : '-' }}</div>
                    </div>
                    <div>
                        <div class="text-[12px] font-medium text-black/50 dark:text-white/50">NPWP</div>
                        <div class="text-black/80 dark:text-white/80 tabular-nums mt-0.5">{{ $business->tax_identification_number ?? '-' }}</div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="text-[12px] font-medium text-black/50 dark:text-white/50">Alamat Fisik</div>
                        <div class="text-black/70 dark:text-white/70 mt-0.5">{{ $business->address ?? 'Belum dicantumkan' }}</div>
                    </div>
                </div>
            </div>

            <!-- Subscription Status Card -->
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                    <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]" stroke-width="1.5"></i>
                        <span>Status Langganan SaaS</span>
                    </h3>
                    <span class="text-[12px] text-black/50 dark:text-white/50 tabular-nums">Sisa Token AI: <strong class="text-[#AF52DE] dark:text-[#BF5AF2]">{{ number_format($business->subscription?->ai_tokens_remaining ?? 0) }}</strong></span>
                </div>
                @if($business->subscription)
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 text-[13px]">
                    <div>
                        <div class="text-[12px] font-medium text-black/50 dark:text-white/50">Paket</div>
                        <div class="font-bold text-black dark:text-white uppercase mt-0.5">{{ $business->subscription->plan_code }}</div>
                    </div>
                    <div>
                        <div class="text-[12px] font-medium text-black/50 dark:text-white/50">Mulai Aktif</div>
                        <div class="text-black/70 dark:text-white/70 tabular-nums mt-0.5">{{ $business->subscription->starts_at?->format('d M Y') ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-[12px] font-medium text-black/50 dark:text-white/50">Berakhir Pada</div>
                        <div class="text-[#34C759] dark:text-[#30D158] font-bold tabular-nums mt-0.5">{{ $business->subscription->ends_at?->format('d M Y') ?? 'Tanpa Batas' }}</div>
                    </div>
                </div>
                @else
                <div class="text-[13px] text-black/60 dark:text-white/60 mt-4">Bisnis ini saat ini berada pada <strong>Free Plan</strong> (kuota gratis bawaan).</div>
                @endif
            </div>

            <!-- Recent Subscription Payments -->
            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6">
                <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2 border-b border-black/5 dark:border-white/10 pb-3">
                    <i data-lucide="receipt" class="w-4 h-4 text-[#FF9500] dark:text-[#FF9F0A]" stroke-width="1.5"></i>
                    <span>Riwayat Tagihan &amp; Pembayaran Langganan</span>
                </h3>
                @if($business->subscriptionPayments->isEmpty())
                <div class="text-[13px] text-black/45 dark:text-white/45 py-4 text-center">Belum ada riwayat transaksi pembayaran paket.</div>
                @else
                <div class="space-y-2 mt-4 text-[13px]">
                    @foreach($business->subscriptionPayments as $pay)
                    <div class="p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-black dark:text-white tabular-nums">{{ $pay->order_number }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">{{ $pay->plan_code }} · {{ $pay->created_at?->format('d M Y H:i') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-black dark:text-white tabular-nums">Rp {{ number_format((float) $pay->total_amount, 0, ',', '.') }}</div>
                            @php $stClass = $pay->status === 'approved' ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : ($pay->status === 'awaiting_approval' ? 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]' : 'bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55'); @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $stClass }}"><span class="w-1.5 h-1.5 rounded-full"></span>{{ $pay->status }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- Right Col: Users & Team -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 space-y-4 lg:col-span-5 h-fit">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <h3 class="text-[15px] font-semibold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-[#5856D6] dark:text-[#5E5CE6]" stroke-width="1.5"></i>
                    <span>Pengguna Terdaftar ({{ $business->users->count() }})</span>
                </h3>
            </div>
            <div class="space-y-3">
                @foreach($business->users as $u)
                <div class="p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] flex items-center justify-between text-[13px]">
                    <div>
                        <div class="font-semibold text-black dark:text-white">{{ $u->name }}</div>
                        <div class="text-[11px] text-black/45 dark:text-white/45">{{ $u->email }}</div>
                    </div>
                    <div class="text-right">
                        @if(($u->pivot->role ?? 'member') === 'owner')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]"><i data-lucide="crown" class="w-3 h-3" stroke-width="1.5"></i>Owner</span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55">Staf</span>
                        @endif
                        <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">{{ $u->google_id ? 'Google' : 'Password' }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection