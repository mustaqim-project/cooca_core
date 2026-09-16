@extends('layouts.admin', [
    'title' => 'Pemulihan Akun - Admin Console',
    'headerTitle' => 'Pusat Verifikasi Pemulihan Akun',
    'headerSubtitle' => 'Verifikasi bukti identitas (KTP) dan dokumen usaha untuk permohonan ganti kontak WhatsApp & email pemilik bisnis'
])

@section('content')
<div class="space-y-6" x-data="{ copiedTicket: null }">

    <!-- ═══ BENTO STAT COUNTER HERO ═══ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- 1. Menunggu Review (Hero Focus Card) -->
        <a href="{{ route('admin.account-recoveries.index', ['status' => 'pending']) }}"
           class="p-4 sm:p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border {{ $status === 'pending' ? 'border-[#FF9500] ring-2 ring-[#FF9500]/20' : 'border-black/[0.06] dark:border-white/[0.08]' }} hover:border-[#FF9500]/40 transition-all duration-200 block relative overflow-hidden group shadow-sm hover:shadow-md">
            @if($counts['pending'] > 0)
                <div class="absolute top-0 right-0 w-24 h-24 bg-[#FF9500]/10 rounded-full blur-2xl pointer-events-none"></div>
            @endif
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-black/55 dark:text-white/55">Menunggu Review</span>
                <div class="w-8 h-8 rounded-full bg-[#FF9500]/10 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center shrink-0">
                    <i data-lucide="clock" class="w-4 h-4" stroke-width="2"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">{{ number_format($counts['pending'], 0, ',', '.') }}</span>
                @if($counts['pending'] > 0)
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#FF9500] uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] animate-ping"></span>
                        Perlu Audit
                    </span>
                @endif
            </div>
            <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Permohonan perlu verifikasi berkas</p>
        </a>

        <!-- 2. Total Pengajuan -->
        <a href="{{ route('admin.account-recoveries.index', ['status' => 'all']) }}"
           class="p-4 sm:p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border {{ $status === 'all' ? 'border-[#007AFF] ring-2 ring-[#007AFF]/20' : 'border-black/[0.06] dark:border-white/[0.08]' }} hover:border-[#007AFF]/40 transition-all duration-200 block shadow-sm hover:shadow-md">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-black/55 dark:text-white/55">Total Pengajuan</span>
                <div class="w-8 h-8 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                    <i data-lucide="inbox" class="w-4 h-4" stroke-width="2"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold tabular-nums text-black dark:text-white">{{ number_format($counts['all'], 0, ',', '.') }}</div>
            <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">Seluruh tiket historis tercatat</p>
        </a>

        <!-- 3. Disetujui Resmi -->
        <a href="{{ route('admin.account-recoveries.index', ['status' => 'approved']) }}"
           class="p-4 sm:p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border {{ $status === 'approved' ? 'border-[#34C759] ring-2 ring-[#34C759]/20' : 'border-black/[0.06] dark:border-white/[0.08]' }} hover:border-[#34C759]/40 transition-all duration-200 block shadow-sm hover:shadow-md">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-black/55 dark:text-white/55">Disetujui Resmi</span>
                <div class="w-8 h-8 rounded-full bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle-2" class="w-4 h-4" stroke-width="2"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ number_format($counts['approved'], 0, ',', '.') }}</div>
            <p class="text-[11px] text-[#34C759]/80 mt-1">Akses akun berhasil dipulihkan</p>
        </a>

        <!-- 4. Permohonan Ditolak -->
        <a href="{{ route('admin.account-recoveries.index', ['status' => 'rejected']) }}"
           class="p-4 sm:p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border {{ $status === 'rejected' ? 'border-[#FF3B30] ring-2 ring-[#FF3B30]/20' : 'border-black/[0.06] dark:border-white/[0.08]' }} hover:border-[#FF3B30]/40 transition-all duration-200 block shadow-sm hover:shadow-md">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-black/55 dark:text-white/55">Permohonan Ditolak</span>
                <div class="w-8 h-8 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] flex items-center justify-center shrink-0">
                    <i data-lucide="x-circle" class="w-4 h-4" stroke-width="2"></i>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">{{ number_format($counts['rejected'], 0, ',', '.') }}</div>
            <p class="text-[11px] text-[#FF3B30]/80 mt-1">Dokumen tidak memenuhi syarat</p>
        </a>
    </div>

    <!-- ═══ BENTO TOOLBAR: SEGMENTED CONTROLS & SEARCH ═══ -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-3 sm:p-3.5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
        <!-- Segmented Filter Status -->
        <div class="inline-flex p-1 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-xs font-medium overflow-x-auto">
            <a href="{{ route('admin.account-recoveries.index', ['status' => 'all', 'search' => $search]) }}"
               class="px-3.5 py-1.5 rounded-[10px] whitespace-nowrap transition-all {{ $status === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-semibold shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                Semua ({{ $counts['all'] }})
            </a>
            <a href="{{ route('admin.account-recoveries.index', ['status' => 'pending', 'search' => $search]) }}"
               class="px-3.5 py-1.5 rounded-[10px] whitespace-nowrap flex items-center gap-1.5 transition-all {{ $status === 'pending' ? 'bg-white dark:bg-[#2C2C2E] text-[#FF9500] dark:text-[#FF9F0A] font-semibold shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                <span>Menunggu Review</span>
                @if($counts['pending'] > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500] dark:text-[#FF9F0A]">{{ $counts['pending'] }}</span>
                @endif
            </a>
            <a href="{{ route('admin.account-recoveries.index', ['status' => 'approved', 'search' => $search]) }}"
               class="px-3.5 py-1.5 rounded-[10px] whitespace-nowrap transition-all {{ $status === 'approved' ? 'bg-white dark:bg-[#2C2C2E] text-[#34C759] dark:text-[#30D158] font-semibold shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                Disetujui ({{ $counts['approved'] }})
            </a>
            <a href="{{ route('admin.account-recoveries.index', ['status' => 'rejected', 'search' => $search]) }}"
               class="px-3.5 py-1.5 rounded-[10px] whitespace-nowrap transition-all {{ $status === 'rejected' ? 'bg-white dark:bg-[#2C2C2E] text-[#FF3B30] dark:text-[#FF453A] font-semibold shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                Ditolak ({{ $counts['rejected'] }})
            </a>
        </div>

        <!-- Search Bar with Anti-Zoom input text-[16px] sm:text-xs -->
        <form method="GET" action="{{ route('admin.account-recoveries.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative w-full sm:w-72">
                <i data-lucide="search" class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari tiket, pemohon, email, WA..."
                       class="w-full pl-9 pr-3.5 py-2 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:border-[#007AFF] focus:ring-1 focus:ring-[#007AFF] transition-all">
            </div>
            @if($search)
                <a href="{{ route('admin.account-recoveries.index', ['status' => $status]) }}"
                   class="px-2.5 py-2 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white text-xs font-medium transition-colors">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- ═══ DATA LIST CONTAINER ═══ -->
    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-sm">
        <!-- DESKTOP TABLE (hidden sm:block) -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/[0.06] dark:border-white/[0.08] text-[11px] font-semibold text-black/45 dark:text-white/45">
                    <tr>
                        <th class="py-3.5 px-4">Nomor Tiket &amp; Waktu</th>
                        <th class="py-3.5 px-4">Pemohon &amp; Bisnis</th>
                        <th class="py-3.5 px-4">Jenis Kendala</th>
                        <th class="py-3.5 px-4">Kontak Lama</th>
                        <th class="py-3.5 px-4">Kontak Baru</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.05] text-black/80 dark:text-white/80">
                    @forelse ($recoveries as $rec)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                            <!-- Ticket Number & Clipboard Copy -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-[#007AFF] dark:text-[#0A84FF] text-xs sm:text-sm">{{ $rec->ticket_number }}</span>
                                    <button type="button"
                                            @click="navigator.clipboard.writeText('{{ $rec->ticket_number }}'); copiedTicket = '{{ $rec->id }}'; setTimeout(() => copiedTicket = null, 2000)"
                                            class="p-1 rounded-[6px] text-black/35 dark:text-white/35 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                                            title="Salin nomor tiket">
                                        <i data-lucide="copy" class="w-3.5 h-3.5" x-show="copiedTicket !== '{{ $rec->id }}'"></i>
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759]" x-show="copiedTicket === '{{ $rec->id }}'" style="display: none;"></i>
                                    </button>
                                </div>
                                <span class="text-[10px] text-black/40 dark:text-white/40 block mt-0.5">{{ $rec->created_at->format('d M Y, H:i') }} WIB</span>
                            </td>

                            <!-- Applicant & Business -->
                            <td class="py-4 px-4">
                                <div class="font-bold text-black dark:text-white text-xs sm:text-sm">{{ $rec->applicant_name }}</div>
                                <div class="text-[11px] text-black/55 dark:text-white/55 flex items-center gap-1 mt-0.5">
                                    <i data-lucide="store" class="w-3 h-3 shrink-0"></i>
                                    <span>{{ $rec->business_name }}</span>
                                </div>
                            </td>

                            <!-- Issue Type Badge -->
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[8px] text-[11px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/75 dark:text-white/75">
                                    {{ $rec->getIssueTypeLabel() }}
                                </span>
                            </td>

                            <!-- Old Contact -->
                            <td class="py-4 px-4 font-mono text-[11px]">
                                <span class="text-black/60 dark:text-white/60 block">{{ $rec->old_email }}</span>
                                <span class="text-black/40 dark:text-white/40 text-[10px]">{{ $rec->old_phone ?? '-' }}</span>
                            </td>

                            <!-- New Contact -->
                            <td class="py-4 px-4 font-mono text-[11px]">
                                <span class="text-[#34C759] dark:text-[#30D158] font-bold block">{{ $rec->new_email }}</span>
                                <span class="text-black/70 dark:text-white/70 text-[10px]">{{ $rec->new_phone }}</span>
                            </td>

                            <!-- Status Badge -->
                            <td class="py-4 px-4 text-center">
                                @if ($rec->status === \App\Models\AccountRecoveryRequest::STATUS_PENDING)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500] dark:text-[#FF9F0A]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] animate-ping"></span>
                                        <span>Menunggu Review</span>
                                    </span>
                                @elseif ($rec->status === \App\Models\AccountRecoveryRequest::STATUS_APPROVED)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158]">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        <span>Disetujui</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30] dark:text-[#FF453A]">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        <span>Ditolak</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Action CTA -->
                            <td class="py-4 px-4 text-right">
                                <a href="{{ route('admin.account-recoveries.show', $rec) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] dark:text-[#0A84FF] font-semibold text-xs transition-colors">
                                    <span>Periksa Berkas</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-14 text-center text-black/40 dark:text-white/40">
                                <div class="w-12 h-12 rounded-2xl bg-black/[0.03] dark:bg-white/[0.05] flex items-center justify-center mx-auto mb-3 text-black/30 dark:text-white/30">
                                    <i data-lucide="inbox" class="w-6 h-6"></i>
                                </div>
                                <span class="font-medium text-xs sm:text-sm">Tidak ada permohonan pemulihan akun pada filter ini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- MOBILE CARDS (sm:hidden) -->
        <div class="sm:hidden divide-y divide-black/[0.06] dark:divide-white/[0.08]">
            @forelse ($recoveries as $rec)
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-mono font-bold text-sm text-[#007AFF] dark:text-[#0A84FF]">{{ $rec->ticket_number }}</span>
                            <span class="text-[10px] text-black/40 dark:text-white/40 block mt-0.5">{{ $rec->created_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                        <div>
                            @if ($rec->status === \App\Models\AccountRecoveryRequest::STATUS_PENDING)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500] dark:text-[#FF9F0A] inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] animate-pulse"></span>
                                    Menunggu
                                </span>
                            @elseif ($rec->status === \App\Models\AccountRecoveryRequest::STATUS_APPROVED)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158]">Disetujui</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#FF3B30]/15 text-[#FF3B30] dark:text-[#FF453A]">Ditolak</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="font-bold text-sm text-black dark:text-white">{{ $rec->applicant_name }}</div>
                        <div class="text-xs text-black/55 dark:text-white/55 flex items-center gap-1 mt-0.5">
                            <i data-lucide="store" class="w-3 h-3"></i>
                            <span>{{ $rec->business_name }}</span>
                        </div>
                    </div>

                    <!-- Contact Details Box -->
                    <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] text-xs space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-black/45 dark:text-white/45 text-[11px]">Kendala:</span>
                            <span class="font-medium text-black/80 dark:text-white/80 text-[11px]">{{ $rec->getIssueTypeLabel() }}</span>
                        </div>
                        <div class="flex justify-between font-mono">
                            <span class="text-black/45 dark:text-white/45 text-[11px]">Email Baru:</span>
                            <span class="font-bold text-[#34C759] dark:text-[#30D158] text-[11px]">{{ $rec->new_email }}</span>
                        </div>
                        <div class="flex justify-between font-mono">
                            <span class="text-black/45 dark:text-white/45 text-[11px]">WA Baru:</span>
                            <span class="text-black/75 dark:text-white/75 text-[11px]">{{ $rec->new_phone }}</span>
                        </div>
                    </div>

                    <!-- Primary Action Button (48px Touch Target) -->
                    <div class="pt-1">
                        <a href="{{ route('admin.account-recoveries.show', $rec) }}"
                           class="w-full min-h-[48px] py-3 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25 transition-all active:scale-[0.98]">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <span>Periksa Berkas &amp; Tindakan</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-black/40 dark:text-white/40 text-xs">
                    Tidak ada pengajuan pemulihan akun.
                </div>
            @endforelse
        </div>

        @if ($recoveries->hasPages())
            <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                {{ $recoveries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
