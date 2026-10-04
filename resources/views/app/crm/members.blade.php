@extends('layouts.app', [
    'title' => __('crm.members_title') . ' - Cooca',
    'headerTitle' => __('crm.members_title'),
    'headerSubtitle' => __('crm.subtitle'),
])

@php
    $isWorkshop = $business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_SERVICE_WORKSHOP) || $business->isWorkshop();
    $canCrmLoyalty = $business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_CRM_LOYALTY);
@endphp

@section('content')
    <script>
        window.COOCA_MEMBERS = @json($customers->items());
        window.COOCA_LOCALE = '{{ app()->getLocale() }}';
        window.COOCA_I18N = {
            locale: '{{ app()->getLocale() }}',
            loading_points: @json(__('crm.points_history_loading')),
            empty_points: @json(__('crm.points_history_empty')),
            pos_cashier_tx: @json(__('crm.pos_cashier_tx')),
            points_unit: @json(__('crm.points_unit')),
            processing: @json(__('crm.processing')),
            save_payment: @json(__('crm.save_payment')),
            repay_notes_default: @json(__('crm.credit_repayment_title')),
        };
    </script>

    <div class="space-y-6 pb-24 lg:pb-16" x-data="{
        membersList: window.COOCA_MEMBERS || [],
        showCreditModal: false,
        showPointsModal: false,
        selectedCustomer: null,
        loadingPoints: false,
        pointHistories: [],
        creditRepayAmount: '',
        creditRawAmount: 0,
        creditNotes: '',

        openCreditModal(customer) {
            this.selectedCustomer = customer;
            const maxDebt = Number(customer.current_credit_balance || 0);
            this.creditRawAmount = maxDebt;
            this.creditRepayAmount = this.formatCurrency(maxDebt);
            this.creditNotes = window.COOCA_I18N.repay_notes_default;
            this.showCreditModal = true;
        },

        setQuickCredit(percent) {
            if (!this.selectedCustomer) return;
            const maxDebt = Number(this.selectedCustomer.current_credit_balance || 0);
            const amt = Math.round(maxDebt * (percent / 100));
            this.creditRawAmount = amt;
            this.creditRepayAmount = this.formatCurrency(amt);
        },

        onCreditInput(e) {
            let clean = e.target.value.replace(/[^0-9]/g, '');
            let num = parseInt(clean, 10) || 0;
            if (this.selectedCustomer) {
                const maxDebt = Number(this.selectedCustomer.current_credit_balance || 0);
                if (num > maxDebt) num = maxDebt;
            }
            this.creditRawAmount = num;
            this.creditRepayAmount = this.formatCurrency(num);
        },

        formatCurrency(val) {
            if (!val || isNaN(val)) return '0';
            return new Intl.NumberFormat(window.COOCA_LOCALE === 'en' ? 'en-US' : 'id-ID').format(val);
        },

        async openPointHistory(customer) {
            this.selectedCustomer = customer;
            this.showPointsModal = true;
            this.loadingPoints = true;
            this.pointHistories = [];

            try {
                const res = await fetch('/crm/customers/' + customer.id + '/points', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    this.pointHistories = data.histories || [];
                }
            } catch (err) {
                console.error('Failed to load point histories:', err);
            } finally {
                this.loadingPoints = false;
            }
        }
    }">

        <x-module-header
            module="crm"
            title="{{ __('crm.title') }}"
            subtitle="{{ __('crm.subtitle') }}" />

        <x-module-tabs module="crm" />

        <!-- ========================================================================= -->
        <!-- 1. BENTO HERO KPI TILES                                                   -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Tile 1: Total Members -->
            <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">{{ __('crm.total_members') }}</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl sm:text-3xl font-bold font-mono tracking-tight text-black dark:text-white tabular-nums">
                    {{ number_format($totalMembers, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-1">
                    <span class="font-semibold text-[#007AFF]">{{ $isWorkshop ? __('crm.workshop_customers') : __('crm.loyalty_database') }}</span>
                    <span>• {{ __('crm.pos_store') }}</span>
                </div>
            </div>

            <!-- Tile 2: Poin Beredar -->
            <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">{{ __('crm.total_points_issued') }}</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                        <i data-lucide="award" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl sm:text-3xl font-bold font-mono tracking-tight text-[#FF9500] tabular-nums">
                    {{ number_format($totalPointsIssued, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-1">
                    <span>{{ __('crm.points_circulating_desc') }}</span>
                </div>
            </div>

            <!-- Tile 3: Piutang Kasbon -->
            <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">{{ __('crm.total_credit_receivable') }}</span>
                    <div class="w-8 h-8 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl sm:text-3xl font-bold font-mono tracking-tight text-[#FF3B30] tabular-nums">
                    Rp {{ number_format($totalCreditReceivable, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-1">
                    <span>{{ __('crm.credit_receivable_desc') }}</span>
                </div>
            </div>

            <!-- Tile 4: Quick Action Tray -->
            <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] flex flex-col justify-between">
                <span class="text-[12px] font-semibold text-black/50 dark:text-white/50">{{ __('crm.quick_actions') }}</span>
                <div class="space-y-2 pt-2">
                    @if (\App\Support\Context::hasPermission('customers.create'))
                        <a href="{{ route('customers.index') }}"
                            class="w-full h-10 px-3 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold transition flex items-center justify-center gap-2 cursor-pointer shadow-[0_1px_4px_rgba(0,122,255,0.25)]">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            <span>{{ __('crm.add_customer') }}</span>
                        </a>
                    @endif
                    @if (\App\Support\Context::hasPermission('crm.manage') && $canCrmLoyalty)
                        <a href="{{ route('crm.vouchers.index') }}"
                            class="w-full h-9 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 text-[12px] font-semibold transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="ticket" class="w-3.5 h-3.5 text-[#34C759]"></i>
                            <span>{{ __('crm.manage_vouchers') }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 2. FILTER & SEARCH BAR                                                    -->
        <!-- ========================================================================= -->
        <div class="p-4 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
            <form method="GET" action="{{ route('crm.members.index') }}" class="flex flex-col lg:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ __('crm.search_member_placeholder') }}"
                        class="w-full h-11 pl-10 pr-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-hidden focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition">
                </div>

                <div class="flex items-center gap-2 w-full lg:w-auto">
                    <select name="tier"
                        class="h-11 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[13px] font-medium text-black dark:text-white focus:outline-hidden">
                        <option value="all">{{ __('crm.all_tiers') }}</option>
                        <option value="bronze" {{ strtolower((string) request('tier')) === 'bronze' ? 'selected' : '' }}>Bronze</option>
                        <option value="silver" {{ strtolower((string) request('tier')) === 'silver' ? 'selected' : '' }}>Silver</option>
                        <option value="gold" {{ strtolower((string) request('tier')) === 'gold' ? 'selected' : '' }}>Gold</option>
                        <option value="platinum" {{ strtolower((string) request('tier')) === 'platinum' ? 'selected' : '' }}>Platinum</option>
                    </select>

                    <select name="segment"
                        class="h-11 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[13px] font-medium text-black dark:text-white focus:outline-hidden">
                        <option value="all">{{ __('crm.all_segments') }}</option>
                        <option value="VIP" {{ request('segment') === 'VIP' ? 'selected' : '' }}>VIP</option>
                        <option value="Regular" {{ request('segment') === 'Regular' ? 'selected' : '' }}>Regular</option>
                        <option value="New" {{ request('segment') === 'New' ? 'selected' : '' }}>New</option>
                        <option value="At Risk" {{ request('segment') === 'At Risk' ? 'selected' : '' }}>At Risk</option>
                    </select>

                    <button type="submit"
                        class="h-11 px-5 rounded-[12px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition cursor-pointer flex items-center justify-center gap-1.5 shrink-0">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        <span>{{ __('crm.filter') }}</span>
                    </button>

                    @if (request('search') || request('tier') || request('segment'))
                        <a href="{{ route('crm.members.index') }}"
                            class="h-11 px-3.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white text-[13px] font-semibold transition flex items-center justify-center cursor-pointer shrink-0">
                            <span>{{ __('common.reset') }}</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- ========================================================================= -->
        <!-- 3. MEMBERS BENTO TABLE CONTAINER                                          -->
        <!-- ========================================================================= -->
        <div class="rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] overflow-hidden">
            <div class="px-5 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h2 class="text-[14px] font-bold text-black dark:text-white">{{ __('crm.title') }}</h2>
                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-[#FF9500]/10 text-[#FF9500]">{{ $customers->total() }} Member</span>
                </div>
            </div>

            @if ($customers->count() > 0)
                <!-- Desktop Table View (lg: screens and above) -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                <th class="py-3.5 px-4 sm:px-6">{{ __('crm.table_member') }}</th>
                                <th class="py-3.5 px-4">{{ __('crm.table_tier_segment') }}</th>
                                <th class="py-3.5 px-4 text-center">{{ __('crm.table_points_balance') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('crm.table_total_spent') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('crm.table_credit_debt') }}</th>
                                <th class="py-3.5 px-4 text-right pr-6">{{ __('crm.table_action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.06]">
                            @foreach ($customers as $m)
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                    <!-- Member Name -->
                                    <td class="py-4 px-4 sm:px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-[12px] bg-[#FF9500]/10 text-[#FF9500] font-bold flex items-center justify-center shrink-0 border border-[#FF9500]/20 text-[14px]">
                                                {{ strtoupper(substr($m->name, 0, 2)) }}
                                            </div>
                                            <div class="space-y-0.5">
                                                <div class="font-semibold text-black dark:text-white">{{ $m->name }}</div>
                                                @if ($m->phone)
                                                    @php
                                                        $cleanPhone = preg_replace('/[^0-9]/', '', $m->phone);
                                                        if (str_starts_with($cleanPhone, '0')) {
                                                            $cleanPhone = '62' . substr($cleanPhone, 1);
                                                        }
                                                    @endphp
                                                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank"
                                                        class="inline-flex items-center gap-1 font-mono text-[11px] text-[#34C759] hover:underline">
                                                        <i data-lucide="message-circle" class="w-3 h-3"></i>
                                                        <span>{{ $m->phone }}</span>
                                                    </a>
                                                @else
                                                    <span class="text-black/30 dark:text-white/30 text-[11px] font-mono">-</span>
                                                @endif

                                                @if ($isWorkshop && $m->vehicle_license_plate)
                                                    <div class="text-[11px] font-mono text-[#007AFF] flex items-center gap-1">
                                                        <i data-lucide="wrench" class="w-3 h-3"></i>
                                                        <span class="font-bold">{{ $m->vehicle_license_plate }}</span>
                                                        @if ($m->vehicle_model)
                                                            <span class="text-black/50 dark:text-white/50 font-sans">({{ $m->vehicle_model }})</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Tier & Segment Badges -->
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            @php
                                                $tierRaw = strtolower((string) ($m->membership_tier ?: 'bronze'));
                                                $tierMap = [
                                                    'platinum' => ['label' => 'Platinum', 'class' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20'],
                                                    'gold'     => ['label' => 'Gold',     'class' => 'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/20'],
                                                    'silver'   => ['label' => 'Silver',   'class' => 'bg-slate-500/10 text-slate-600 dark:text-slate-300 border-slate-500/20'],
                                                    'bronze'   => ['label' => 'Bronze',   'class' => 'bg-amber-600/10 text-amber-700 dark:text-amber-400 border-amber-600/20'],
                                                ];
                                                $tConfig = $tierMap[$tierRaw] ?? ['label' => ucfirst($tierRaw), 'class' => 'bg-black/5 text-black/60 border-black/10'];
                                            @endphp
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $tConfig['class'] }}">
                                                <i data-lucide="award" class="w-3 h-3"></i>
                                                <span>{{ $tConfig['label'] }}</span>
                                            </span>

                                            @if ($m->segment)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60">
                                                    {{ strtoupper($m->segment) === 'VIP' ? 'VIP' : ucfirst($m->segment) }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Points Balance & Action -->
                                    <td class="py-4 px-4 text-center">
                                        <div class="space-y-1">
                                            <div class="text-[15px] font-bold text-[#FF9500] tabular-nums font-mono">
                                                {{ number_format($m->points_balance, 0, ',', '.') }}
                                            </div>
                                            <button type="button" @click="openPointHistory(@js($m))"
                                                class="inline-flex items-center gap-1 text-[11px] text-[#007AFF] hover:underline font-semibold cursor-pointer">
                                                <i data-lucide="history" class="w-3 h-3"></i>
                                                <span>{{ __('crm.point_history') }}</span>
                                            </button>
                                        </div>
                                    </td>

                                    <!-- Total Spent -->
                                    <td class="py-4 px-4 text-right font-mono tabular-nums">
                                        <div class="font-semibold text-black dark:text-white">
                                            Rp {{ number_format($m->total_spent, 0, ',', '.') }}
                                        </div>
                                        <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('crm.pos_orders_count', ['count' => $m->pos_orders_count]) }}</span>
                                    </td>

                                    <!-- Credit Debt Balance -->
                                    <td class="py-4 px-4 text-right">
                                        @if ($m->current_credit_balance > 0)
                                            <div class="space-y-1">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/10 text-[#FF3B30] border border-[#FF3B30]/20 font-mono tabular-nums">
                                                    Rp {{ number_format($m->current_credit_balance, 0, ',', '.') }}
                                                </span>
                                                @if (\App\Support\Context::hasPermission('crm.manage'))
                                                    <div>
                                                        <button type="button" @click="openCreditModal(@js($m))"
                                                            class="inline-flex items-center gap-1 text-[11px] font-bold text-[#34C759] hover:underline cursor-pointer">
                                                            <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
                                                            <span>{{ __('crm.pay_credit') }}</span>
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#34C759]">
                                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                                <span>{{ __('crm.status_paid') }}</span>
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Action Buttons -->
                                    <td class="py-4 px-4 text-right pr-6">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if ($m->current_credit_balance > 0 && \App\Support\Context::hasPermission('crm.manage'))
                                                <button type="button" @click="openCreditModal(@js($m))"
                                                    class="h-8 px-2.5 rounded-[8px] text-[12px] font-semibold bg-[#34C759]/10 text-[#34C759] hover:bg-[#34C759]/20 transition cursor-pointer flex items-center gap-1">
                                                    <i data-lucide="hand-coins" class="w-3.5 h-3.5"></i>
                                                    <span>{{ __('crm.btn_settlement') }}</span>
                                                </button>
                                            @endif
                                            <a href="{{ route('customers.index', ['search' => $m->name]) }}"
                                                class="h-8 px-2.5 rounded-[8px] text-[12px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 transition cursor-pointer flex items-center gap-1"
                                                title="{{ __('crm.view_full_profile') }}">
                                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                <span>{{ __('crm.btn_profile') }}</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View (Thumb Zone Optimized for 320px–390px screens) -->
                <div class="block lg:hidden divide-y divide-black/[0.05] dark:divide-white/[0.06]">
                    @foreach ($customers as $m)
                        <div class="p-4 sm:p-5 space-y-3.5">
                            <!-- Card Header: Member Info, Tier & Segment -->
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-11 h-11 rounded-[14px] bg-[#FF9500]/10 text-[#FF9500] font-bold flex items-center justify-center shrink-0 border border-[#FF9500]/20 text-[14px]">
                                        {{ strtoupper(substr($m->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0 space-y-0.5">
                                        <h3 class="font-bold text-[15px] text-black dark:text-white truncate">
                                            {{ $m->name }}
                                        </h3>
                                        @if ($m->phone)
                                            @php
                                                $cleanPhone = preg_replace('/[^0-9]/', '', $m->phone);
                                                if (str_starts_with($cleanPhone, '0')) {
                                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                                }
                                            @endphp
                                            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank"
                                                class="inline-flex items-center gap-1 font-mono text-[11px] text-[#34C759] hover:underline">
                                                <i data-lucide="message-circle" class="w-3 h-3"></i>
                                                <span>{{ $m->phone }}</span>
                                            </a>
                                        @else
                                            <span class="text-black/30 dark:text-white/30 text-[11px] font-mono">-</span>
                                        @endif

                                        @if ($isWorkshop && $m->vehicle_license_plate)
                                            <div class="text-[11px] font-mono text-[#007AFF] flex items-center gap-1 font-bold">
                                                <i data-lucide="wrench" class="w-3 h-3"></i>
                                                <span>{{ $m->vehicle_license_plate }}</span>
                                                @if ($m->vehicle_model)
                                                    <span class="text-black/50 dark:text-white/50 font-sans font-normal">({{ $m->vehicle_model }})</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Tier & Segment Badges -->
                                <div class="shrink-0 flex flex-col items-end gap-1">
                                    @php
                                        $tierRaw = strtolower((string) ($m->membership_tier ?: 'bronze'));
                                        $tierMap = [
                                            'platinum' => ['label' => 'Platinum', 'class' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20'],
                                            'gold'     => ['label' => 'Gold',     'class' => 'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/20'],
                                            'silver'   => ['label' => 'Silver',   'class' => 'bg-slate-500/10 text-slate-600 dark:text-slate-300 border-slate-500/20'],
                                            'bronze'   => ['label' => 'Bronze',   'class' => 'bg-amber-600/10 text-amber-700 dark:text-amber-400 border-amber-600/20'],
                                        ];
                                        $tConfig = $tierMap[$tierRaw] ?? ['label' => ucfirst($tierRaw), 'class' => 'bg-black/5 text-black/60 border-black/10'];
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $tConfig['class'] }}">
                                        <i data-lucide="award" class="w-3 h-3"></i>
                                        <span>{{ $tConfig['label'] }}</span>
                                    </span>

                                    @if ($m->segment)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60">
                                            {{ strtoupper($m->segment) === 'VIP' ? 'VIP' : ucfirst($m->segment) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Key Metrics 3-Col Mini Bento Grid -->
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <!-- Poin Belanja -->
                                <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-0.5">
                                    <span class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">{{ __('crm.table_points_balance') }}</span>
                                    <div class="text-[14px] font-bold text-[#FF9500] font-mono tabular-nums">
                                        {{ number_format($m->points_balance, 0, ',', '.') }}
                                    </div>
                                </div>

                                <!-- Total Belanja -->
                                <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-0.5">
                                    <span class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">{{ __('crm.table_total_spent') }}</span>
                                    <div class="text-[12px] font-bold text-black dark:text-white font-mono tabular-nums truncate">
                                        Rp {{ number_format($m->total_spent, 0, ',', '.') }}
                                    </div>
                                </div>

                                <!-- Kasbon -->
                                <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-0.5">
                                    <span class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 block">{{ __('crm.table_credit_debt') }}</span>
                                    @if ($m->current_credit_balance > 0)
                                        <div class="text-[12px] font-bold text-[#FF3B30] font-mono tabular-nums truncate">
                                            Rp {{ number_format($m->current_credit_balance, 0, ',', '.') }}
                                        </div>
                                    @else
                                        <div class="text-[12px] font-medium text-[#34C759]">
                                            {{ __('crm.status_paid_clean') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Thumb Zone Quick Actions (Ergonomic 44px min-height targets) -->
                            <div class="flex items-center gap-2 pt-1">
                                @if ($m->current_credit_balance > 0 && \App\Support\Context::hasPermission('crm.manage'))
                                    <button type="button" @click="openCreditModal(@js($m))"
                                        class="flex-1 h-11 px-3 rounded-[12px] bg-[#34C759] hover:bg-[#2FB350] text-white font-bold text-[13px] flex items-center justify-center gap-1.5 shadow-[0_2px_8px_rgba(52,199,89,0.25)] active:scale-[0.98] transition cursor-pointer">
                                        <i data-lucide="hand-coins" class="w-4 h-4"></i>
                                        <span>{{ __('crm.btn_settlement') }}</span>
                                    </button>
                                @endif

                                <button type="button" @click="openPointHistory(@js($m))"
                                    class="flex-1 h-11 px-3 rounded-[12px] bg-[#FF9500]/10 hover:bg-[#FF9500]/20 text-[#FF9500] font-semibold text-[13px] flex items-center justify-center gap-1.5 active:scale-[0.98] transition cursor-pointer">
                                    <i data-lucide="history" class="w-4 h-4"></i>
                                    <span>{{ __('crm.point_history') }}</span>
                                </button>

                                <a href="{{ route('customers.index', ['search' => $m->name]) }}"
                                    class="h-11 px-3.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white font-semibold text-[13px] flex items-center justify-center gap-1.5 active:scale-[0.98] transition cursor-pointer shrink-0"
                                    title="{{ __('crm.view_full_profile') }}">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    <span>{{ __('crm.btn_profile') }}</span>
                                </a>

                                @if ($m->phone)
                                    @php
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $m->phone);
                                        if (str_starts_with($cleanPhone, '0')) {
                                            $cleanPhone = '62' . substr($cleanPhone, 1);
                                        }
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank"
                                        class="h-11 w-11 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center active:scale-[0.98] transition shrink-0"
                                        title="{{ __('crm.chat_whatsapp') }}">
                                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if ($customers->hasPages())
                    <div class="p-4 border-t border-black/[0.05] dark:border-white/[0.06]">
                        {{ $customers->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="py-16 px-6 text-center space-y-4">
                    <div class="w-14 h-14 rounded-[18px] bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                        <i data-lucide="users" class="w-7 h-7"></i>
                    </div>
                    <div class="space-y-1 max-w-sm mx-auto">
                        <h4 class="text-[15px] font-bold text-black dark:text-white">{{ __('crm.empty_members_title') }}</h4>
                        <p class="text-[13px] text-black/50 dark:text-white/50 leading-relaxed">
                            {{ __('crm.empty_members_desc') }}
                        </p>
                    </div>
                    @if (\App\Support\Context::hasPermission('customers.create'))
                        <div class="pt-2">
                            <a href="{{ route('customers.index') }}"
                                class="inline-flex items-center gap-2 h-11 px-5 rounded-[12px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition cursor-pointer">
                                <i data-lucide="user-plus" class="w-4 h-4"></i>
                                <span>{{ __('crm.add_new_customer') }}</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL: CATAT PELUNASAN KASBON PIUTANG TEMPO (MOBILE BOTTOM SHEET & DESKTOP) -->
        <!-- ========================================================================= -->
        @if (\App\Support\Context::hasPermission('crm.manage'))
            <div x-show="showCreditModal" x-cloak
                class="fixed inset-0 z-50 flex items-end lg:items-center justify-center p-0 lg:p-6 bg-black/50 backdrop-blur-md transition-all"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @keydown.escape.window="showCreditModal = false">
                
                <div class="w-full bg-white dark:bg-[#1C1C1E] border-t lg:border border-black/[0.08] dark:border-white/[0.1] shadow-2xl relative overflow-y-auto
                            rounded-t-[28px] lg:rounded-[28px] max-h-[92vh] lg:max-h-[88vh]
                            p-5 sm:p-6 lg:p-8 lg:max-w-xl space-y-6"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                    x-transition:enter-end="translate-y-0 lg:scale-100 lg:opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="translate-y-0 lg:scale-100 lg:opacity-100"
                    x-transition:leave-end="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                    @click.away="showCreditModal = false">
                    
                    <!-- Mobile Drag / Bottom Sheet Pill Indicator -->
                    <div class="w-12 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto mb-2 lg:hidden"></div>

                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-[14px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                <i data-lucide="banknote" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-black dark:text-white">{{ __('crm.credit_repayment_title') }}</h3>
                                <p class="text-[12px] text-black/50 dark:text-white/50" x-text="selectedCustomer ? selectedCustomer.name : ''"></p>
                            </div>
                        </div>
                        <button type="button" @click="showCreditModal = false"
                            class="text-black/40 hover:text-black dark:hover:text-white p-1.5 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Microcopy Keamanan Data -->
                    <div class="p-3.5 rounded-[16px] bg-[#007AFF]/5 border border-[#007AFF]/15 text-[12px] text-[#007AFF] flex items-start gap-2.5">
                        <i data-lucide="shield-check" class="w-4 h-4 shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed">{{ __('crm.credit_safety_guarantee') }}</span>
                    </div>

                    <form method="POST" :action="'/crm/customers/' + (selectedCustomer ? selectedCustomer.id : '') + '/credit-payment'"
                        x-data="{ submitting: false }" @submit="submitting = true" class="space-y-5">
                        @csrf
                        <div class="space-y-1.5">
                            <label class="block text-[13px] font-bold text-black dark:text-white">
                                {{ __('crm.repayment_amount') }} <span class="text-[#FF3B30]">*</span>
                            </label>
                            <input type="text" :value="creditRepayAmount" @input="onCreditInput($event)" required
                                class="w-full h-12 px-4 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] font-mono font-bold text-xl text-black dark:text-white focus:outline-hidden focus:border-[#34C759] focus:ring-2 focus:ring-[#34C759]/20 transition">
                            <input type="hidden" name="amount" :value="creditRawAmount">
                        </div>

                        <!-- Quick Percentage Fill Buttons -->
                        <div class="grid grid-cols-3 gap-2.5">
                            <button type="button" @click="setQuickCredit(25)"
                                class="h-10 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-[13px] font-semibold text-black dark:text-white transition cursor-pointer">
                                {{ __('crm.quick_25') }}
                            </button>
                            <button type="button" @click="setQuickCredit(50)"
                                class="h-10 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-[13px] font-semibold text-black dark:text-white transition cursor-pointer">
                                {{ __('crm.quick_50') }}
                            </button>
                            <button type="button" @click="setQuickCredit(100)"
                                class="h-10 rounded-[12px] bg-[#34C759]/15 hover:bg-[#34C759]/25 text-[13px] font-bold text-[#34C759] transition cursor-pointer">
                                {{ __('crm.quick_100') }}
                            </button>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[13px] font-semibold text-black/70 dark:text-white/70">{{ __('crm.payment_notes') }}</label>
                            <input type="text" name="notes" x-model="creditNotes" placeholder="{{ __('crm.payment_notes_placeholder') }}"
                                class="w-full h-12 px-4 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden focus:border-[#34C759] transition">
                        </div>

                        <!-- Footer Actions -->
                        <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3">
                            <button type="button" @click="showCreditModal = false" :disabled="submitting"
                                class="h-12 px-6 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-semibold text-[13px] hover:text-black dark:hover:text-white transition cursor-pointer disabled:opacity-50">
                                {{ __('common.cancel') }}
                            </button>
                            <button type="submit" :disabled="submitting || creditRawAmount <= 0"
                                class="flex-1 h-12 rounded-[14px] bg-[#34C759] hover:bg-[#2FB350] disabled:opacity-50 text-white font-bold text-[14px] shadow-[0_2px_8px_rgba(52,199,89,0.3)] transition cursor-pointer flex items-center justify-center gap-2">
                                <i data-lucide="check" class="w-4 h-4" x-show="!submitting"></i>
                                <span x-text="submitting ? window.COOCA_I18N.processing : window.COOCA_I18N.save_payment"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- ========================================================================= -->
        <!-- MODAL: RIWAYAT PEROLEHAN & PENUKARAN POIN (BOTTOM SHEET & DESKTOP MODAL)  -->
        <!-- ========================================================================= -->
        <div x-show="showPointsModal" x-cloak
            class="fixed inset-0 z-50 flex items-end lg:items-center justify-center p-0 lg:p-6 bg-black/50 backdrop-blur-md transition-all"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @keydown.escape.window="showPointsModal = false">
            
            <div class="w-full bg-white dark:bg-[#1C1C1E] border-t lg:border border-black/[0.08] dark:border-white/[0.1] shadow-2xl relative overflow-y-auto
                        rounded-t-[28px] lg:rounded-[28px] max-h-[92vh] lg:max-h-[88vh]
                        p-5 sm:p-6 lg:p-8 lg:max-w-2xl space-y-6"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                x-transition:enter-end="translate-y-0 lg:scale-100 lg:opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-y-0 lg:scale-100 lg:opacity-100"
                x-transition:leave-end="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                @click.away="showPointsModal = false">
                
                <!-- Mobile Drag / Bottom Sheet Pill Indicator -->
                <div class="w-12 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto mb-2 lg:hidden"></div>

                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-[14px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                            <i data-lucide="history" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-black dark:text-white">{{ __('crm.points_history_title') }}</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50" x-text="selectedCustomer ? selectedCustomer.name : ''"></p>
                        </div>
                    </div>
                    <button type="button" @click="showPointsModal = false"
                        class="text-black/40 hover:text-black dark:hover:text-white p-1.5 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Points List Container -->
                <div class="max-h-[380px] overflow-y-auto space-y-2 pr-1">
                    <template x-if="loadingPoints">
                        <div class="py-12 text-center text-[13px] text-black/40 dark:text-white/40" x-text="window.COOCA_I18N.loading_points">
                        </div>
                    </template>

                    <template x-if="!loadingPoints && pointHistories.length === 0">
                        <div class="py-12 text-center text-[13px] text-black/40 dark:text-white/40" x-text="window.COOCA_I18N.empty_points">
                        </div>
                    </template>

                    <template x-if="!loadingPoints && pointHistories.length > 0">
                        <div class="divide-y divide-black/[0.05] dark:divide-white/[0.06]">
                            <template x-for="h in pointHistories" :key="h.id">
                                <div class="py-3 flex items-center justify-between">
                                    <div class="space-y-0.5">
                                        <div class="font-semibold text-[13px] text-black dark:text-white" x-text="h.notes || window.COOCA_I18N.pos_cashier_tx"></div>
                                        <div class="text-[11px] text-black/40 dark:text-white/40 font-mono" x-text="h.created_at ? new Date(h.created_at).toLocaleString(window.COOCA_LOCALE === 'en' ? 'en-US' : 'id-ID') : '-'"></div>
                                    </div>
                                    <div class="font-mono font-bold text-[14px] tabular-nums"
                                        :class="h.points_change >= 0 ? 'text-[#34C759]' : 'text-[#FF3B30]'"
                                        x-text="(h.points_change >= 0 ? '+' : '') + h.points_change + ' ' + window.COOCA_I18N.points_unit">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Footer Actions -->
                <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end">
                    <button type="button" @click="showPointsModal = false"
                        class="h-12 px-6 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-semibold text-[13px] hover:text-black dark:hover:text-white transition cursor-pointer">
                        {{ __('common.close') }}
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection
