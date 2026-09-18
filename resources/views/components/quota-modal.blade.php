{{-- Bento Apple HIG Reusable Quota & Lock Modal Component --}}
<div x-data="{
        showQuotaModal: false,
        quotaTitle: 'Batas Kuota Tercapai',
        quotaDesc: 'Kuota gratis untuk fitur ini telah habis bulan ini.',
        quotaUsed: 0,
        quotaLimit: 0,
        quotaUnit: 'Data',
        quotaResetDate: '1 {{ now()->addMonth()->translatedFormat('F Y') }}',
        upgradeUrl: '{{ route('billing.patungan') }}',
        upgradeFee: 'Rp 49.000/bln',
        isAddon: false
    }"
    @open-quota-modal.window="
        showQuotaModal = true;
        quotaTitle = $event.detail.title || 'Batas Kuota Tercapai';
        quotaDesc = $event.detail.desc || 'Kuota gratis untuk fitur ini telah habis bulan ini.';
        quotaUsed = $event.detail.used ?? 0;
        quotaLimit = $event.detail.limit ?? 0;
        quotaUnit = $event.detail.unit || 'Data';
        quotaResetDate = $event.detail.resetDate || '1 {{ now()->addMonth()->translatedFormat('F Y') }}';
        upgradeUrl = $event.detail.upgradeUrl || '{{ route('billing.patungan') }}';
        upgradeFee = $event.detail.upgradeFee || 'Rp 49.000/bln';
        isAddon = $event.detail.isAddon || false;
    "
    @keydown.escape.window="showQuotaModal = false"
    class="relative z-[9999]"
    style="display: none;"
    x-show="showQuotaModal">

    {{-- Backdrop Glassmorphism --}}
    <div x-show="showQuotaModal"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/45 dark:bg-black/65 backdrop-blur-md"
        @click="showQuotaModal = false"
        aria-hidden="true"></div>

    {{-- Modal Sheet Window --}}
    <div class="fixed inset-0 z-10 flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
        <div x-show="showQuotaModal"
            x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_24px_60px_rgba(0,0,0,0.22)] overflow-hidden p-6 sm:p-7 space-y-5 text-left"
            @click.away="showQuotaModal = false">

            {{-- Header with Apple Squircle Icon --}}
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-[16px] bg-gradient-to-br from-[#FF9500] to-[#FF3B30] text-white flex items-center justify-center shadow-md shadow-[#FF9500]/25 shrink-0">
                        <i data-lucide="lock" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#FF9500] dark:text-[#FF9F0A] block">
                            Paket Free Solo
                        </span>
                        <h3 class="text-[17px] font-bold text-black dark:text-white tracking-tight leading-snug" x-text="quotaTitle">
                            Batas Kuota Tercapai
                        </h3>
                    </div>
                </div>
                <button type="button" @click="showQuotaModal = false"
                    class="p-1.5 rounded-full text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-all cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Description & Friendly Reassurance --}}
            <p class="text-[13px] text-black/65 dark:text-white/65 leading-relaxed" x-text="quotaDesc">
                Kuota gratis untuk fitur ini telah habis bulan ini.
            </p>

            {{-- Bento Meter Box --}}
            <div class="p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-2.5">
                <div class="flex items-center justify-between text-[12.5px]">
                    <span class="text-black/60 dark:text-white/60 font-medium">Status Pemakaian:</span>
                    <span class="font-mono font-bold text-black dark:text-white">
                        <span x-text="quotaUsed" class="text-[#FF3B30]"></span> / <span x-text="quotaLimit"></span> <span x-text="quotaUnit" class="text-xs text-black/50 dark:text-white/50"></span>
                    </span>
                </div>
                {{-- Progress Bar --}}
                <div class="w-full h-2 bg-black/10 dark:bg-white/10 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-[#FF9500] to-[#FF3B30] rounded-full w-full"></div>
                </div>
                {{-- Auto Reset Notice --}}
                <div class="flex items-center gap-1.5 text-[11.5px] text-black/50 dark:text-white/50 pt-1 border-t border-black/5 dark:border-white/5">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                    <span>Reset otomatis pada: <strong class="text-black/75 dark:text-white/75" x-text="quotaResetDate"></strong></span>
                </div>
            </div>

            {{-- No Data Punishment Guarantee --}}
            <div class="flex items-start gap-2.5 text-[11.5px] text-[#34C759] dark:text-[#30D158] bg-[#34C759]/10 p-3 rounded-[12px] border border-[#34C759]/20">
                <i data-lucide="shield-check" class="w-4 h-4 shrink-0 mt-0.5"></i>
                <span><strong>Data Anda 100% Aman:</strong> Seluruh data yang sudah Anda catat sebelumnya tetap tersimpan rapi dan tidak akan pernah dihapus.</span>
            </div>

            {{-- Action Buttons --}}
            <div class="space-y-2 pt-1">
                <a :href="upgradeUrl"
                    class="w-full min-h-[44px] px-4 rounded-[12px] bg-gradient-to-r from-[#007AFF] via-[#5856D6] to-[#AF52DE] hover:opacity-95 text-white text-[13.5px] font-bold flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25 active:scale-[0.98] transition-all cursor-pointer">
                    <i data-lucide="sparkles" class="w-4 h-4 text-white"></i>
                    <span>Buka Tanpa Batas (<span x-text="upgradeFee"></span>)</span>
                </a>
                <button type="button" @click="showQuotaModal = false"
                    class="w-full min-h-[38px] px-4 rounded-[12px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white text-[12.5px] font-medium transition-all text-center cursor-pointer">
                    Nanti Saja (Tunggu Reset Bulan Depan)
                </button>
            </div>
        </div>
    </div>
</div>
