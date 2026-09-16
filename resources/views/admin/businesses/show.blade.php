@extends('layouts.admin', [
    'title' => 'Detail Workspace: ' . $business->name . ' - Admin Console',
    'headerTitle' => 'Workspace: ' . $business->name,
    'headerSubtitle' => 'Detail lengkap profil tenant, kuota sumber daya, pengguna, dan status langganan',
])

@section('content')
    <div class="space-y-6">

        <!-- Top Navigation & Actions Bar -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <a href="{{ route('admin.businesses.index') }}"
                class="text-[13px] font-bold text-[#007AFF] dark:text-[#0A84FF] hover:underline inline-flex items-center gap-1.5 transition">
                <i data-lucide="arrow-left" class="w-4 h-4" stroke-width="2"></i>
                <span>Kembali ke Daftar Bisnis</span>
            </a>
            <form method="POST" action="{{ route('admin.businesses.toggle-status', $business->id) }}"
                onsubmit="return AppAlert.confirmSubmit(event, this, 'Apakah Anda yakin ingin mengubah status aktif bisnis ini?', 'Ubah Status Bisnis?', 'warning')">
                @csrf
                @if ($business->is_active)
                    <button type="submit"
                        class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:scale-[0.98] transition-all flex items-center gap-2 shadow-sm">
                        <i data-lucide="ban" class="w-4 h-4" stroke-width="2"></i>
                        <span>Tangguhkan Workspace (Suspend)</span>
                    </button>
                @else
                    <button type="submit"
                        class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-2 shadow-md shadow-[#007AFF]/25">
                        <i data-lucide="check-circle" class="w-4 h-4" stroke-width="2"></i>
                        <span>Aktifkan Kembali Workspace</span>
                    </button>
                @endif
            </form>
        </div>

        <!-- Bento Hero Workspace Identity Card -->
        <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($business->is_active)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Status: Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span> Status: Ditangguhkan
                        </span>
                    @endif

                    @if ($business->subscription?->isCorePlan())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5" stroke-width="2"></i>
                            <span>PAKET CORE ({{ strtoupper($business->subscription->plan_code) }})</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/60 dark:text-white/60">
                            <i data-lucide="gift" class="w-3.5 h-3.5" stroke-width="1.5"></i>
                            <span>FREE PLAN</span>
                        </span>
                    @endif

                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]">
                        <i data-lucide="users" class="w-3.5 h-3.5" stroke-width="1.5"></i>
                        <span>{{ $isSoloMode ? 'Mode Solo Owner (1 User)' : 'Mode Tim Delegasi' }}</span>
                    </span>
                </div>

                <h1 class="text-[26px] sm:text-[30px] font-extrabold text-black dark:text-white tracking-tight">
                    {{ $business->name }}
                </h1>

                <div class="flex flex-wrap items-center gap-3 text-[12px] text-black/50 dark:text-white/50 font-mono">
                    <span>slug: <strong>{{ $business->slug }}</strong></span>
                    <span class="text-black/20 dark:text-white/20">·</span>
                    <span>Mata Uang: {{ $business->currency ?? 'IDR' }} ({{ $business->currency_symbol }})</span>
                    <span class="text-black/20 dark:text-white/20">·</span>
                    <span>Bergabung: {{ $business->created_at?->format('d M Y') }}</span>
                </div>

                @if (!$business->is_active && $business->suspended_reason)
                    <div class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[12px] text-[#C41E17] dark:text-[#FF453A] mt-2 leading-relaxed">
                        <strong>Alasan Penangguhan:</strong> {{ $business->suspended_reason }} (sejak {{ $business->suspended_at?->format('d M Y H:i') }})
                    </div>
                @endif
            </div>
        </div>

        <!-- 4 Resources Bento KPI Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5">
                <span class="text-[12px] font-bold text-black/50 dark:text-white/50 block">Total Produk</span>
                <div class="text-[26px] font-extrabold tabular-nums text-black dark:text-white mt-1">{{ $productCount }}</div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">Katalog item toko</div>
            </div>
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5">
                <span class="text-[12px] font-bold text-black/50 dark:text-white/50 block">Resep / BOM HPP</span>
                <div class="text-[26px] font-extrabold tabular-nums text-[#34C759] dark:text-[#30D158] mt-1">{{ $bomCount }}</div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">Struktur biaya produk</div>
            </div>
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5">
                <span class="text-[12px] font-bold text-black/50 dark:text-white/50 block">Transaksi Toko</span>
                <div class="text-[26px] font-extrabold tabular-nums text-black dark:text-white mt-1">{{ $invoiceCount + $posOrderCount }}</div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">{{ $posOrderCount }} POS · {{ $invoiceCount }} Faktur</div>
            </div>
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-5">
                <span class="text-[12px] font-bold text-black/50 dark:text-white/50 block">Token AI Terpakai</span>
                <div class="text-[26px] font-extrabold tabular-nums text-[#AF52DE] dark:text-[#BF5AF2] mt-1">{{ number_format($totalAiTokensUsed, 0, ',', '.') }}</div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">Konsumsi Google Gemini</div>
            </div>
        </div>

        <!-- Two-Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Col (7 cols) -->
            <div class="space-y-6 lg:col-span-7">

                <!-- Commercial Profile Bento Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 shadow-sm">
                    <div class="flex items-center gap-3 pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08]">
                        <div class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 flex items-center justify-center text-[#007AFF] dark:text-[#0A84FF]">
                            <i data-lucide="store" class="w-4.5 h-4.5" stroke-width="1.5"></i>
                        </div>
                        <h3 class="text-[15px] font-bold text-black dark:text-white">Informasi Komersial &amp; Finansial</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 text-[13px]">
                        <div class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.03] dark:border-white/[0.04]">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Email Bisnis</div>
                            <div class="text-black dark:text-white font-semibold mt-1">{{ $business->email ?? '-' }}</div>
                        </div>
                        <div class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.03] dark:border-white/[0.04]">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Nomor Telepon / WA</div>
                            <div class="text-black dark:text-white font-semibold mt-1">{{ $business->phone ?? '-' }}</div>
                        </div>
                        <div class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.03] dark:border-white/[0.04]">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Bank &amp; No Rekening</div>
                            <div class="text-black dark:text-white font-semibold tabular-nums mt-1">
                                {{ $business->bank_name ? $business->bank_name . ' - ' . $business->bank_account_number : '-' }}
                            </div>
                        </div>
                        <div class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.03] dark:border-white/[0.04]">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">NPWP</div>
                            <div class="text-black dark:text-white font-semibold tabular-nums mt-1">
                                {{ $business->tax_identification_number ?? '-' }}
                            </div>
                        </div>
                        <div class="sm:col-span-2 p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.03] dark:border-white/[0.04]">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Alamat Fisik</div>
                            <div class="text-black/80 dark:text-white/80 mt-1 leading-relaxed">
                                {{ $business->address ?? 'Belum dicantumkan' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscription Status Bento Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08]">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-[12px] bg-[#34C759]/10 flex items-center justify-center text-[#34C759] dark:text-[#30D158]">
                                <i data-lucide="shield-check" class="w-4.5 h-4.5" stroke-width="2"></i>
                            </div>
                            <h3 class="text-[15px] font-bold text-black dark:text-white">Status Langganan SaaS</h3>
                        </div>
                        <span class="text-[12px] text-black/55 dark:text-white/55 tabular-nums">
                            Sisa Token AI: <strong class="text-[#AF52DE] dark:text-[#BF5AF2]">{{ number_format($business->subscription?->ai_tokens_remaining ?? 0) }}</strong>
                        </span>
                    </div>

                    @if ($business->subscription)
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 text-[13px]">
                            <div class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.03] dark:border-white/[0.04]">
                                <div class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Paket Aktif</div>
                                <div class="font-extrabold text-black dark:text-white uppercase mt-1">
                                    {{ $business->subscription->plan_code }}
                                </div>
                            </div>
                            <div class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.03] dark:border-white/[0.04]">
                                <div class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Mulai Aktif</div>
                                <div class="text-black/80 dark:text-white/80 tabular-nums font-semibold mt-1">
                                    {{ $business->subscription->starts_at?->format('d M Y') ?? '-' }}
                                </div>
                            </div>
                            <div class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.03] dark:border-white/[0.04]">
                                <div class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Berakhir Pada</div>
                                <div class="text-[#34C759] dark:text-[#30D158] font-bold tabular-nums mt-1">
                                    {{ $business->subscription->ends_at?->format('d M Y') ?? 'Tanpa Batas' }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-[13px] text-black/60 dark:text-white/60 p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] mt-4">
                            Bisnis ini saat ini menggunakan <strong>Free Plan</strong> (kuota gratis bawaan platform).
                        </div>
                    @endif
                </div>

                <!-- Recent Subscription Payments Bento Card -->
                <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 shadow-sm">
                    <div class="flex items-center gap-3 pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08]">
                        <div class="w-9 h-9 rounded-[12px] bg-[#FF9500]/10 flex items-center justify-center text-[#FF9500] dark:text-[#FF9F0A]">
                            <i data-lucide="receipt" class="w-4.5 h-4.5" stroke-width="1.5"></i>
                        </div>
                        <h3 class="text-[15px] font-bold text-black dark:text-white">Riwayat Tagihan &amp; Pembayaran</h3>
                    </div>

                    @if ($business->subscriptionPayments->isEmpty())
                        <div class="text-[13px] text-black/45 dark:text-white/45 py-8 text-center">
                            Belum ada riwayat transaksi pembayaran paket untuk workspace ini.
                        </div>
                    @else
                        <div class="space-y-2.5 mt-4 text-[13px]">
                            @foreach ($business->subscriptionPayments as $pay)
                                <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.03] dark:border-white/[0.04] flex items-center justify-between">
                                    <div>
                                        <div class="font-bold text-black dark:text-white font-mono">{{ $pay->order_number }}</div>
                                        <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">
                                            {{ $pay->plan_code }} · {{ $pay->created_at?->format('d M Y H:i') }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-black dark:text-white tabular-nums">
                                            Rp {{ number_format((float) $pay->total_amount, 0, ',', '.') }}
                                        </div>
                                        @php
                                            $stClass = $pay->status === 'approved' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : ($pay->status === 'awaiting_approval' ? 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]' : 'bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55');
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $stClass }} mt-1">
                                            <span class="w-1.5 h-1.5 rounded-full"></span> {{ $pay->status }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            <!-- Right Col (5 cols): Users & Team -->
            <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 shadow-sm space-y-4 lg:col-span-5">
                <div class="flex items-center justify-between pb-3.5 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-[12px] bg-[#5856D6]/10 flex items-center justify-center text-[#5856D6] dark:text-[#5E5CE6]">
                            <i data-lucide="users" class="w-4.5 h-4.5" stroke-width="1.5"></i>
                        </div>
                        <h3 class="text-[15px] font-bold text-black dark:text-white">Pengguna Terdaftar ({{ $business->users->count() }})</h3>
                    </div>
                </div>

                <div class="space-y-2.5">
                    @foreach ($business->users as $u)
                        <div class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.03] dark:border-white/[0.04] flex items-center justify-between text-[13px]">
                            <div class="min-w-0 pr-3">
                                <div class="font-bold text-black dark:text-white truncate">{{ $u->name }}</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 truncate mt-0.5">{{ $u->email }}</div>
                            </div>
                            <div class="text-right shrink-0">
                                @if (($u->pivot->role ?? 'member') === 'owner')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#5856D6]/15 text-[#413FA6] dark:text-[#5E5CE6]">
                                        <i data-lucide="crown" class="w-3 h-3" stroke-width="2"></i>
                                        <span>Owner</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55">
                                        <span>Staf</span>
                                    </span>
                                @endif
                                <div class="text-[10px] font-medium text-black/45 dark:text-white/45 mt-1">
                                    {{ $u->google_id ? 'Google SSO' : 'Password' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
@endsection
