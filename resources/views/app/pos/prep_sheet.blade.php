@extends('layouts.app', ['title' => __('pos.prep_sheet_title')])

@section('content')
<div class="max-w-[1600px] mx-auto space-y-6 pb-16">

    {{-- MODULE HEADER & PERSISTENT POS TABS --}}
    <div class="space-y-4 print:hidden">
        <x-module-header
            module="pos"
            :title="__('pos.prep_sheet_title')"
            :subtitle="__('pos.prep_sheet_subtitle')">
            <x-slot:actions>
                <a href="{{ route('pos.kitchen.index') }}"
                   class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 w-full sm:w-auto">
                    <i data-lucide="tv" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                    <span>{{ __('pos.kitchen_title') }}</span>
                </a>
                <button type="button" onclick="window.print()"
                        class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-sm cursor-pointer w-full sm:w-auto">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>{{ __('pos.print_prep_sheet') }}</span>
                </button>
            </x-slot:actions>
        </x-module-header>

        <x-module-tabs module="pos" />
    </div>

    <!-- Filter Controls Bar -->
    <div class="bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md p-4 sm:p-5 rounded-[16px] border border-black/5 dark:border-white/10 shadow-xs print:hidden">
        <form method="GET" action="{{ route('pos.kitchen.prep_sheet') }}" class="flex flex-wrap items-center justify-between gap-3">
            <!-- Date Filter -->
            <div class="flex items-center gap-1.5 bg-black/[0.05] dark:bg-white/[0.06] p-1 rounded-[12px] border border-black/5 dark:border-white/5">
                <a href="{{ route('pos.kitchen.prep_sheet', ['date' => now()->toDateString(), 'location_id' => $locationId]) }}"
                   class="min-h-[36px] px-3 py-1.5 text-[12px] font-semibold rounded-[8px] flex items-center transition {{ $targetDate === now()->toDateString() ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    {{ __('pos.today') }}
                </a>
                <a href="{{ route('pos.kitchen.prep_sheet', ['date' => now()->addDay()->toDateString(), 'location_id' => $locationId]) }}"
                   class="min-h-[36px] px-3 py-1.5 text-[12px] font-semibold rounded-[8px] flex items-center transition {{ $targetDate === now()->addDay()->toDateString() ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    {{ __('pos.tomorrow') }}
                </a>
                <a href="{{ route('pos.kitchen.prep_sheet', ['date' => now()->addDays(2)->toDateString(), 'location_id' => $locationId]) }}"
                   class="min-h-[36px] px-3 py-1.5 text-[12px] font-semibold rounded-[8px] flex items-center transition {{ $targetDate === now()->addDays(2)->toDateString() ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    {{ __('pos.day_after_tomorrow') }}
                </a>
                <input type="date" name="date" value="{{ $targetDate }}" onchange="this.form.submit()"
                       class="min-h-[36px] px-2.5 py-1 text-[13px] bg-white dark:bg-[#2C2C2E] rounded-[8px] border-none text-black dark:text-white focus:ring-2 focus:ring-[#007AFF] font-medium">
            </div>

            <!-- Location Selector -->
            @if($locations->count() > 1)
                <select name="location_id" onchange="this.form.submit()"
                        class="min-h-[40px] h-10 px-3.5 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.06] text-[13px] font-semibold border border-black/5 dark:border-white/10 text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">{{ __('pos.all_locations') }}</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ $locationId === $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }} ({{ $loc->type === 'central_kitchen' ? __('pos.central_kitchen_label') : ($loc->type === 'outlet' ? __('pos.outlet_kitchen_label') : __('pos.warehouse_label')) }})
                        </option>
                    @endforeach
                </select>
            @endif
        </form>
    </div>

    <!-- Print Header (Only visible when printing) -->
    <div class="hidden print:block mb-6 pb-4 border-b border-neutral-300">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-black">{{ $business->name }}</h1>
                <p class="text-sm text-neutral-600">{{ __('pos.daily_prep_sheet_title') }}</p>
            </div>
            <div class="text-right text-xs text-neutral-600">
                <p class="font-bold text-sm text-black">{{ __('pos.target_date') }}: {{ \Carbon\Carbon::parse($targetDate)->translatedFormat('l, d F Y') }}</p>
                <p>{{ __('pos.printed_at') }}: {{ now()->translatedFormat('d/m/Y H:i') }} WIB</p>
            </div>
        </div>
    </div>

    <!-- 4 Bento Key Metrics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Total Orders -->
        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">{{ __('pos.prep_orders_metric') }}</span>
                <span class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-neutral-900 dark:text-white tabular-nums">{{ $totalOrdersCount }}</span>
                <span class="text-xs text-neutral-500">{{ __('pos.transactions_count_header') }}</span>
            </div>
            <p class="text-[11px] text-neutral-400 mt-1">{{ __('pos.prep_batch_online_pos_desc') }}</p>
        </div>

        <!-- Metric 2: Total Menu Portions -->
        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">{{ __('pos.prep_portions_metric') }}</span>
                <span class="w-8 h-8 rounded-xl bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                    <i data-lucide="utensils" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-neutral-900 dark:text-white tabular-nums">{{ number_format($totalPortionsCount, 0, ',', '.') }}</span>
                <span class="text-xs text-neutral-500">{{ __('pos.prep_portions_to_prepare') }}</span>
            </div>
            <p class="text-[11px] text-neutral-400 mt-1">{{ __('pos.prep_menu_variants_count', ['count' => count($menuPortions)]) }}</p>
        </div>

        <!-- Metric 3: Raw Materials Required -->
        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">{{ __('pos.prep_bom_materials_metric') }}</span>
                <span class="w-8 h-8 rounded-xl bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                    <i data-lucide="boxes" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-neutral-900 dark:text-white tabular-nums">{{ count($materialRequirements) }}</span>
                <span class="text-xs text-neutral-500">{{ __('pos.prep_raw_materials_count') }}</span>
            </div>
            <p class="text-[11px] text-neutral-400 mt-1">{{ __('pos.prep_bom_calculated_desc') }}</p>
        </div>

        <!-- Metric 4: Shortages Alert -->
        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">{{ __('pos.prep_stock_readiness_metric') }}</span>
                <span class="w-8 h-8 rounded-xl {{ $shortageCount > 0 ? 'bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A]' : 'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158]' }} flex items-center justify-center">
                    <i data-lucide="{{ $shortageCount > 0 ? 'alert-triangle' : 'check-circle' }}" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                @if($shortageCount > 0)
                    <span class="text-2xl font-bold text-[#FF3B30] dark:text-[#FF453A] tabular-nums">{{ $shortageCount }}</span>
                    <span class="text-xs font-bold text-[#FF3B30] dark:text-[#FF453A]">{{ __('pos.stock_deficit_alert') }}</span>
                @else
                    <span class="text-2xl font-bold text-[#34C759] dark:text-[#30D158]">{{ __('pos.stock_safe') }}</span>
                    <span class="text-xs text-[#34C759] dark:text-[#30D158]">{{ __('pos.prep_stock_100_percent') }}</span>
                @endif
            </div>
            <p class="text-[11px] text-neutral-400 mt-1">
                {{ $shortageCount > 0 ? __('pos.stock_deficit_action_hint') : __('pos.stock_sufficient_hint') }}
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
                        <h2 class="text-sm font-bold text-neutral-900 dark:text-white">{{ __('pos.prep_production_plan_title') }}</h2>
                        <p class="text-[11px] text-neutral-500">{{ __('pos.prep_production_plan_desc') }}</p>
                    </div>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-orange-100 text-orange-800 dark:bg-orange-950/60 dark:text-orange-300">
                    {{ __('pos.prep_menu_count_badge', ['count' => count($menuPortions)]) }}
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
                                <span class="block text-[11px] text-neutral-400">{{ __('pos.prep_portions_to_prepare') }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center mx-auto text-neutral-400 mb-2">
                            <i data-lucide="calendar-x" class="w-6 h-6"></i>
                        </div>
                        <p class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">{{ __('pos.prep_no_scheduled_orders') }}</p>
                        <p class="text-xs text-neutral-400 mt-0.5">{{ __('pos.prep_no_scheduled_orders_hint') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Panel 2: Kebutuhan Bahan Baku BOM (Right: 7 Cols) -->
        <div class="lg:col-span-7 bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                        <i data-lucide="scale" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-neutral-900 dark:text-white">{{ __('pos.prep_material_picking_title') }}</h2>
                        <p class="text-[11px] text-neutral-500">{{ __('pos.prep_material_picking_desc') }}</p>
                    </div>
                </div>

                @if($shortageCount > 0)
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]">
                        {{ __('pos.prep_materials_shortage_badge', ['count' => $shortageCount]) }}
                    </span>
                @else
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                        {{ __('pos.prep_stock_sufficient_badge') }}
                    </span>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-neutral-50/80 dark:bg-neutral-800/50 text-neutral-500 dark:text-neutral-400 font-semibold border-b border-black/[0.04] dark:border-white/[0.04]">
                            <th class="px-4 py-3">{{ __('pos.material_name') }}</th>
                            <th class="px-3 py-3 text-right">{{ __('pos.estimated_requirement') }}</th>
                            <th class="px-3 py-3 text-right">{{ __('pos.current_stock') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('pos.prep_stock_readiness_metric') }}</th>
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
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            {{ __('pos.prep_readiness_sufficient') }} (+{{ number_format($mat['stock_on_hand'] - $mat['required_quantity'], 1, ',', '.') }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#FF3B30]/10 text-[#C41E17] dark:text-[#FF453A] border border-[#FF3B30]/25">
                                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                            {{ __('pos.prep_readiness_shortage', ['amount' => number_format($mat['shortage'], 2, ',', '.'), 'unit' => $mat['unit']]) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-neutral-400">
                                    @if(empty($menuPortions))
                                        {{ __('pos.prep_no_materials_needed') }}
                                    @else
                                        {{ __('pos.prep_no_bom_configured') }}
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Panel Footer Note -->
            <div class="p-3.5 bg-neutral-50 dark:bg-neutral-800/40 border-t border-black/[0.04] dark:border-white/[0.04] text-[11px] text-neutral-500 dark:text-neutral-400 flex items-center justify-between">
                <span>{{ __('pos.prep_waste_tolerance_note') }}</span>
                <span class="hidden sm:inline">{{ __('pos.prep_po_advice_note') }}</span>
            </div>
        </div>

    </div>

    <!-- Scheduled Customer Orders List (Collapsible Table) -->
    @if($scheduledOrders->isNotEmpty())
        <div class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-5 print:mt-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="truck" class="w-4 h-4 text-neutral-500"></i>
                    <h2 class="text-sm font-bold text-neutral-900 dark:text-white">{{ __('pos.prep_deliveries_title') }}</h2>
                </div>
                <span class="text-xs text-neutral-500">{{ __('pos.prep_scheduled_orders_count', ['count' => $scheduledOrders->count()]) }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-neutral-50 dark:bg-neutral-800/50 text-neutral-500 font-semibold border-b border-black/[0.04] dark:border-white/[0.04]">
                            <th class="px-3 py-2">{{ __('pos.order_number') }}</th>
                            <th class="px-3 py-2">{{ __('pos.customer_label') }}</th>
                            <th class="px-3 py-2">{{ __('pos.prep_time_slot') }}</th>
                            <th class="px-3 py-2">{{ __('pos.prep_menu_and_portions') }}</th>
                            <th class="px-3 py-2">{{ __('pos.prep_fulfillment_method') }}</th>
                            <th class="px-3 py-2">{{ __('pos.status') }}</th>
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
                                    {{ $ord->scheduled_time_slot ?: __('pos.prep_regular_slot') }}
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
