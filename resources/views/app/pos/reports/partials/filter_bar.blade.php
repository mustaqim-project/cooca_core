{{-- BENTO APPLE HIG MULTI-DIMENSIONAL FILTER BAR --}}
<div x-data="{ expanded: false }" class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 shadow-xs space-y-4">
    <form id="posFilterForm" method="GET" action="{{ route('pos.reports.index') }}" class="space-y-4">
        {{-- Preserve Active Tab --}}
        <input type="hidden" name="tab" value="{{ $activeTab }}">

        {{-- Row 1: Quick Preset Chips & Primary Actions --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            {{-- Preset Chips --}}
            <div class="flex flex-wrap items-center gap-1.5 overflow-x-auto py-0.5 no-scrollbar">
                @php
                    $currentPreset = request('preset', 'last_30_days');
                    $presets = [
                        'today' => __('pos.preset_today', ['default' => 'Hari Ini']),
                        'yesterday' => __('pos.preset_yesterday', ['default' => 'Kemarin']),
                        'this_week' => __('pos.preset_this_week', ['default' => 'Minggu Ini']),
                        'this_month' => __('pos.preset_this_month', ['default' => 'Bulan Ini']),
                        'last_month' => __('pos.preset_last_month', ['default' => 'Bulan Lalu']),
                        'this_year' => __('pos.preset_this_year', ['default' => 'Tahun Ini']),
                        'custom' => __('pos.preset_custom', ['default' => 'Custom']),
                    ];
                @endphp
                @foreach ($presets as $pKey => $pLabel)
                    <button type="button"
                        onclick="applyPreset('{{ $pKey }}')"
                        class="px-3 py-1.5 rounded-[10px] text-[12px] font-semibold transition-all cursor-pointer {{ $currentPreset === $pKey ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08] dark:hover:bg-white/[0.10]' }}">
                        {{ $pLabel }}
                    </button>
                @endforeach
                <input type="hidden" id="filterPresetInput" name="preset" value="{{ $currentPreset }}">
            </div>

            {{-- Filter Expand Toggle & Apply Button --}}
            <div class="flex items-center gap-2 ml-auto">
                <button type="button" @click="expanded = !expanded"
                    class="h-9 px-3 rounded-[10px] text-[12px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.10] flex items-center gap-1.5 transition-all">
                    <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
                    <span>Filter Lanjutan</span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': expanded }"></i>
                </button>

                <a href="{{ route('pos.reports.index', ['tab' => $activeTab]) }}"
                    class="h-9 px-3 rounded-[10px] text-[12px] font-semibold text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06] flex items-center gap-1 transition-all">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Reset</span>
                </a>

                <button type="submit"
                    class="h-9 px-4 rounded-[10px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center gap-1.5 shadow-xs">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
            </div>
        </div>

        {{-- Row 2: Standard Date Range Picker --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 pt-2 border-t border-black/5 dark:border-white/5">
            <div>
                <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Dari Tanggal</label>
                <input type="date" id="filterStartDate" name="start_date" value="{{ $startDate->toDateString() }}"
                    onchange="document.getElementById('filterPresetInput').value = 'custom'"
                    class="w-full h-10 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
            </div>

            <div>
                <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Sampai Tanggal</label>
                <input type="date" id="filterEndDate" name="end_date" value="{{ $endDate->toDateString() }}"
                    onchange="document.getElementById('filterPresetInput').value = 'custom'"
                    class="w-full h-10 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
            </div>

            <div>
                <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Cabang / Outlet</label>
                <select name="location_id" class="w-full h-10 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">Semua Outlet</option>
                    @foreach ($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Kasir / Operator</label>
                <select name="user_id" class="w-full h-10 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">Semua Kasir</option>
                    @foreach ($cashiers as $cUser)
                        <option value="{{ $cUser->id }}" {{ request('user_id') == $cUser->id ? 'selected' : '' }}>{{ $cUser->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Row 3: Advanced Collapsible Filters --}}
        <div x-show="expanded" x-transition.opacity.duration.200ms class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 pt-3 border-t border-black/5 dark:border-white/5">
            <div>
                <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Kategori Produk</label>
                <select name="category_id" class="w-full h-10 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Metode Pembayaran</label>
                <select name="payment_method" class="w-full h-10 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">Semua Metode</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                    <option value="qris" {{ request('payment_method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="edc_debit" {{ request('payment_method') === 'edc_debit' ? 'selected' : '' }}>Debit Card</option>
                    <option value="edc_credit" {{ request('payment_method') === 'edc_credit' ? 'selected' : '' }}>Kartu Kredit</option>
                    <option value="transfer" {{ request('payment_method') === 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                    <option value="customer_credit" {{ request('payment_method') === 'customer_credit' ? 'selected' : '' }}>Kasbon (Piutang)</option>
                    <option value="loyalty_points" {{ request('payment_method') === 'loyalty_points' ? 'selected' : '' }}>Poin Loyalitas</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Kanal Penjualan</label>
                <select name="sales_channel" class="w-full h-10 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">Semua Kanal</option>
                    <option value="pos" {{ request('sales_channel') === 'pos' ? 'selected' : '' }}>Kasir POS Langsung</option>
                    <option value="qr_table" {{ request('sales_channel') === 'qr_table' ? 'selected' : '' }}>QR Order Meja</option>
                    <option value="grabfood" {{ request('sales_channel') === 'grabfood' ? 'selected' : '' }}>GrabFood</option>
                    <option value="gofood" {{ request('sales_channel') === 'gofood' ? 'selected' : '' }}>GoFood</option>
                    <option value="shopeefood" {{ request('sales_channel') === 'shopeefood' ? 'selected' : '' }}>ShopeeFood</option>
                    <option value="online" {{ request('sales_channel') === 'online' ? 'selected' : '' }}>Website Storefront</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-medium text-black/50 dark:text-white/50 mb-1">Status Transaksi</label>
                <select name="status" class="w-full h-10 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">Selesai (Completed & Partial)</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hanya Selesai (Completed)</option>
                    <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>Dibatalkan (Voided)</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Direfund (Refunded)</option>
                    <option value="draft_held" {{ request('status') === 'draft_held' ? 'selected' : '' }}>Tertahan (Held)</option>
                </select>
            </div>
        </div>
    </form>
</div>

<script>
function applyPreset(preset) {
    const today = new Date();
    let startDate = new Date();
    let endDate = new Date();

    const formatDate = (d) => {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    if (preset === 'today') {
        startDate = today;
        endDate = today;
    } else if (preset === 'yesterday') {
        startDate = new Date(today);
        startDate.setDate(today.getDate() - 1);
        endDate = new Date(startDate);
    } else if (preset === 'this_week') {
        const dayOfWeek = today.getDay(); // 0 is Sunday
        const diffToMonday = today.getDate() - (dayOfWeek === 0 ? 6 : dayOfWeek - 1);
        startDate = new Date(today.setDate(diffToMonday));
        endDate = new Date();
    } else if (preset === 'this_month') {
        startDate = new Date(today.getFullYear(), today.getMonth(), 1);
        endDate = new Date();
    } else if (preset === 'last_month') {
        startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        endDate = new Date(today.getFullYear(), today.getMonth(), 0);
    } else if (preset === 'this_year') {
        startDate = new Date(today.getFullYear(), 0, 1);
        endDate = new Date();
    }

    if (preset !== 'custom') {
        document.getElementById('filterStartDate').value = formatDate(startDate);
        document.getElementById('filterEndDate').value = formatDate(endDate);
    }
    document.getElementById('filterPresetInput').value = preset;
    document.getElementById('posFilterForm').submit();
}
</script>
