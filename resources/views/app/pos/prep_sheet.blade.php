@extends('layouts.app', ['title' => 'Lembar Prep Dapur (Kitchen Batch Prep Sheet)'])

@section('content')
<div class="max-w-[1600px] mx-auto space-y-6 pb-16">

    <!-- Top Action & Filter Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white dark:bg-[#1C1C1E] p-4 sm:p-5 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-sm print:hidden">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                <i data-lucide="clipboard-list" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-white">Lembar Prep Dapur &amp; Katering</h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        BOM AGGREGATION
                    </span>
                </div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                    Agregasi porsi menu &amp; kalkulasi kebutuhan bahan baku otomatis berdasarkan resep BOM untuk produksi harian.
                </p>
            </div>
        </div>

        <!-- Filter Controls & Actions -->
        <form method="GET" action="{{ route('pos.kitchen.prep_sheet') }}" class="flex flex-wrap items-center gap-2.5">
            <!-- Date Filter -->
            <div class="flex items-center gap-1.5 bg-neutral-100 dark:bg-neutral-800/80 p-1 rounded-xl border border-black/5 dark:border-white/5">
                <a href="{{ route('pos.kitchen.prep_sheet', ['date' => now()->toDateString(), 'location_id' => $locationId]) }}"
                   class="px-2.5 py-1 text-xs font-semibold rounded-lg transition {{ $targetDate === now()->toDateString() ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white shadow-xs' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900' }}">
                    Hari Ini
                </a>
                <a href="{{ route('pos.kitchen.prep_sheet', ['date' => now()->addDay()->toDateString(), 'location_id' => $locationId]) }}"
                   class="px-2.5 py-1 text-xs font-semibold rounded-lg transition {{ $targetDate === now()->addDay()->toDateString() ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white shadow-xs' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900' }}">
                    Besok
                </a>
                <a href="{{ route('pos.kitchen.prep_sheet', ['date' => now()->addDays(2)->toDateString(), 'location_id' => $locationId]) }}"
                   class="px-2.5 py-1 text-xs font-semibold rounded-lg transition {{ $targetDate === now()->addDays(2)->toDateString() ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white shadow-xs' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900' }}">
                    Lusa
                </a>
                <input type="date" name="date" value="{{ $targetDate }}" onchange="this.form.submit()"
                       class="px-2 py-1 text-xs bg-white dark:bg-neutral-700 rounded-lg border-none text-neutral-800 dark:text-neutral-200 focus:ring-1 focus:ring-emerald-500 font-medium">
            </div>

            <!-- Location Selector -->
            @if($locations->count() > 1)
                <select name="location_id" onchange="this.form.submit()"
                        class="h-9 px-3 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-xs font-semibold border-black/5 dark:border-white/10 text-neutral-800 dark:text-neutral-200 focus:ring-emerald-500">
                    <option value="">Semua Lokasi / Dapur</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ $locationId === $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }} ({{ $loc->type === 'central_kitchen' ? 'Dapur Pusat' : ($loc->type === 'outlet' ? 'Cabang' : 'Gudang') }})
                        </option>
                    @endforeach
                </select>
            @endif

            <!-- Print Button -->
            <button type="button" onclick="window.print()"
                    class="h-9 px-3.5 rounded-xl bg-neutral-900 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-100 text-white dark:text-neutral-900 text-xs font-semibold flex items-center gap-1.5 transition shadow-sm">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Lembar Prep</span>
            </button>

            <!-- Back to KDS Link -->
            <a href="{{ route('pos.kitchen.index') }}"
               class="h-9 px-3 rounded-xl bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-xs font-semibold text-neutral-700 dark:text-neutral-300 flex items-center gap-1.5 transition">
                <i data-lucide="tv" class="w-4 h-4"></i>
                <span>Monitor KDS</span>
            </a>
        </form>
    </div>

    <!-- Print Header (Only visible when printing) -->
    <div class="hidden print:block mb-6 pb-4 border-b border-neutral-300">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-black">{{ $business->name }}</h1>
                <p class="text-sm text-neutral-600">Lembar Kerja Persiapan Dapur (Daily Kitchen Batch Prep Sheet)</p>
            </div>
            <div class="text-right text-xs text-neutral-600">
                <p class="font-bold text-sm text-black">Tanggal Target: {{ \Carbon\Carbon::parse($targetDate)->translatedFormat('l, d F Y') }}</p>
                <p>Dicetak: {{ now()->translatedFormat('d/m/Y H:i') }} WIB</p>
            </div>
        </div>
    </div>

    <!-- 4 Bento Key Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Total Orders -->
        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Total Pesanan</span>
                <span class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-neutral-900 dark:text-white tabular-nums">{{ $totalOrdersCount }}</span>
                <span class="text-xs text-neutral-500">transaksi</span>
            </div>
            <p class="text-[11px] text-neutral-400 mt-1">Batch online &amp; POS pada tanggal ini</p>
        </div>

        <!-- Metric 2: Total Menu Portions -->
        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Total Porsi Menu</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="utensils" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-neutral-900 dark:text-white tabular-nums">{{ number_format($totalPortionsCount, 0, ',', '.') }}</span>
                <span class="text-xs text-neutral-500">porsi disiapkan</span>
            </div>
            <p class="text-[11px] text-neutral-400 mt-1">{{ count($menuPortions) }} varian menu berbeda</p>
        </div>

        <!-- Metric 3: Raw Materials Required -->
        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Bahan Baku (BOM)</span>
                <span class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center">
                    <i data-lucide="boxes" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-neutral-900 dark:text-white tabular-nums">{{ count($materialRequirements) }}</span>
                <span class="text-xs text-neutral-500">jenis bahan baku</span>
            </div>
            <p class="text-[11px] text-neutral-400 mt-1">Dihitung otomatis via resep</p>
        </div>

        <!-- Metric 4: Shortages Alert -->
        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Kesiapan Stok</span>
                <span class="w-8 h-8 rounded-xl {{ $shortageCount > 0 ? 'bg-red-500/10 text-red-600' : 'bg-emerald-500/10 text-emerald-600' }} flex items-center justify-center">
                    <i data-lucide="{{ $shortageCount > 0 ? 'alert-triangle' : 'check-circle' }}" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                @if($shortageCount > 0)
                    <span class="text-2xl font-bold text-red-600 dark:text-red-400 tabular-nums">{{ $shortageCount }}</span>
                    <span class="text-xs font-bold text-red-600 dark:text-red-400">Bahan Kurang!</span>
                @else
                    <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">Aman</span>
                    <span class="text-xs text-emerald-600 dark:text-emerald-400">100% Cukup</span>
                @endif
            </div>
            <p class="text-[11px] text-neutral-400 mt-1">
                {{ $shortageCount > 0 ? 'Segera lakukan PO bahan baku' : 'Stok gudang mencukupi seluruh porsi' }}
            </p>
        </div>
    </div>

    <!-- 2 Main Bento Panels -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Panel 1: Rencana Porsi Menu (Left: 5 Cols) -->
        <div class="lg:col-span-5 bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-orange-500/10 text-orange-600 flex items-center justify-center">
                        <i data-lucide="chef-hat" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-neutral-900 dark:text-white">Rencana Produksi Menu</h2>
                        <p class="text-[11px] text-neutral-500">Porsi makanan/minuman yang harus disiapkan</p>
                    </div>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-orange-100 text-orange-800 dark:bg-orange-950/60 dark:text-orange-300">
                    {{ count($menuPortions) }} Menu
                </span>
            </div>

            <div class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                @forelse($menuPortions as $portion)
                    <div class="p-4 hover:bg-neutral-50/60 dark:hover:bg-neutral-800/40 transition">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-sm text-neutral-900 dark:text-white truncate">{{ $portion['product_name'] }}</h3>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 font-medium">
                                        {{ $portion['category'] }}
                                    </span>
                                </div>
                                
                                <!-- Delivery/Serving Slots Breakdown -->
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    @foreach($portion['time_slots'] as $slotName => $qty)
                                        <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-md bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
                                            <i data-lucide="clock" class="w-3 h-3 text-neutral-400"></i>
                                            <span>{{ $slotName }}:</span>
                                            <strong class="font-bold">{{ number_format($qty, 0, ',', '.') }}</strong>
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <span class="text-lg font-extrabold text-neutral-900 dark:text-white tabular-nums">
                                    {{ number_format($portion['total_quantity'], 0, ',', '.') }}
                                </span>
                                <span class="block text-[11px] text-neutral-400">porsi</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center mx-auto text-neutral-400 mb-2">
                            <i data-lucide="calendar-x" class="w-6 h-6"></i>
                        </div>
                        <p class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">Tidak ada pesanan terjadwal</p>
                        <p class="text-xs text-neutral-400 mt-0.5">Pilih tanggal lain atau pastikan pesanan katering sudah berstatus aktif.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Panel 2: Kebutuhan Bahan Baku BOM (Right: 7 Cols) -->
        <div class="lg:col-span-7 bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="scale" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-neutral-900 dark:text-white">Lembar Pengambilan Bahan Baku</h2>
                        <p class="text-[11px] text-neutral-500">Breakdown kebutuhan bahan mentah dari resep BOM</p>
                    </div>
                </div>

                @if($shortageCount > 0)
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300">
                        {{ $shortageCount }} Bahan Kurang
                    </span>
                @else
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                        Stok Memadai
                    </span>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-neutral-50/80 dark:bg-neutral-800/50 text-neutral-500 dark:text-neutral-400 font-semibold border-b border-black/[0.04] dark:border-white/[0.04]">
                            <th class="px-4 py-3">Nama Bahan Baku</th>
                            <th class="px-3 py-3 text-right">Dibutuhkan</th>
                            <th class="px-3 py-3 text-right">Stok Fisik</th>
                            <th class="px-4 py-3 text-center">Status Kesiapan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                        @forelse($materialRequirements as $mat)
                            <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition {{ ! $mat['is_sufficient'] ? 'bg-red-50/40 dark:bg-red-950/10' : '' }}">
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-neutral-900 dark:text-white">{{ $mat['material_name'] }}</div>
                                    @if($mat['material_code'])
                                        <div class="text-[10px] text-neutral-400 font-mono">{{ $mat['material_code'] }}</div>
                                    @endif
                                    @if(!empty($mat['used_in_menus']))
                                        <div class="text-[10px] text-neutral-500 dark:text-neutral-400 mt-0.5 truncate max-w-xs">
                                            Menu: {{ implode(', ', array_slice($mat['used_in_menus'], 0, 2)) }}
                                            @if(count($mat['used_in_menus']) > 2)
                                                <span>+{{ count($mat['used_in_menus']) - 2 }} lainnya</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <td class="px-3 py-3.5 text-right whitespace-nowrap">
                                    <span class="font-bold text-neutral-900 dark:text-white tabular-nums">
                                        {{ number_format($mat['required_quantity'], 2, ',', '.') }}
                                    </span>
                                    <span class="text-neutral-500 text-[10px] ml-0.5">{{ $mat['unit'] }}</span>
                                </td>

                                <td class="px-3 py-3.5 text-right whitespace-nowrap">
                                    <span class="font-bold tabular-nums {{ $mat['is_sufficient'] ? 'text-neutral-700 dark:text-neutral-300' : 'text-red-600 dark:text-red-400' }}">
                                        {{ number_format($mat['stock_on_hand'], 2, ',', '.') }}
                                    </span>
                                    <span class="text-neutral-500 text-[10px] ml-0.5">{{ $mat['unit'] }}</span>
                                </td>

                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    @if($mat['is_sufficient'])
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            Cukup (+{{ number_format($mat['stock_on_hand'] - $mat['required_quantity'], 1, ',', '.') }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-950/80 dark:text-red-300 border border-red-200 dark:border-red-800">
                                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                            Kurang {{ number_format($mat['shortage'], 2, ',', '.') }} {{ $mat['unit'] }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-neutral-400">
                                    @if(empty($menuPortions))
                                        Tidak ada pesanan pada tanggal ini untuk dihitung kebutuhan bahannya.
                                    @else
                                        Menu pada pesanan ini belum memiliki konfigurasi resep BOM (Cost Model).
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Panel Footer Note -->
            <div class="p-3.5 bg-neutral-50 dark:bg-neutral-800/40 border-t border-black/[0.04] dark:border-white/[0.04] text-[11px] text-neutral-500 dark:text-neutral-400 flex items-center justify-between">
                <span>* Angka kebutuhan sudah memperhitungkan persentase toleransi waste pada resep BOM.</span>
                <span class="hidden sm:inline">Gunakan form penerimaan barang (PO) untuk mengisi kekurangan bahan.</span>
            </div>
        </div>

    </div>

    <!-- Scheduled Customer Orders List (Collapsible Table) -->
    @if($scheduledOrders->isNotEmpty())
        <div class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-5 print:mt-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="truck" class="w-4 h-4 text-neutral-500"></i>
                    <h2 class="text-sm font-bold text-neutral-900 dark:text-white">Daftar Pengiriman / Pengambilan Pesanan</h2>
                </div>
                <span class="text-xs text-neutral-500">{{ $scheduledOrders->count() }} pesanan terdaftar</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-neutral-50 dark:bg-neutral-800/50 text-neutral-500 font-semibold border-b border-black/[0.04] dark:border-white/[0.04]">
                            <th class="px-3 py-2">No. Order</th>
                            <th class="px-3 py-2">Pelanggan</th>
                            <th class="px-3 py-2">Slot Waktu</th>
                            <th class="px-3 py-2">Menu &amp; Porsi</th>
                            <th class="px-3 py-2">Metode</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                        @foreach($scheduledOrders as $ord)
                            <tr>
                                <td class="px-3 py-2.5 font-bold font-mono text-neutral-900 dark:text-white">
                                    #{{ $ord->order_number }}
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="font-semibold text-neutral-900 dark:text-white">{{ $ord->customer_name }}</div>
                                    <div class="text-[10px] text-neutral-400">{{ $ord->customer_phone }}</div>
                                </td>
                                <td class="px-3 py-2.5 font-medium text-neutral-700 dark:text-neutral-300">
                                    {{ $ord->scheduled_time_slot ?: 'Reguler' }}
                                </td>
                                <td class="px-3 py-2.5">
                                    @foreach($ord->items as $it)
                                        <div>{{ (int)$it->quantity }}x {{ $it->product_name }}</div>
                                    @endforeach
                                </td>
                                <td class="px-3 py-2.5 uppercase font-semibold text-[10px] text-neutral-600 dark:text-neutral-400">
                                    {{ $ord->fulfillment_type }}
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-300">
                                        {{ $ord->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
