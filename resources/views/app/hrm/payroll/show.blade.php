@extends('layouts.app', [
    'title' => $payroll->title,
    'headerTitle' => $payroll->title,
    'headerSubtitle' => __('hrm.batch_period_detail', ['period' => $payroll->formatted_period, 'count' => $payroll->total_employees_count])
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32" x-data="{
    showPayModal: false,
    showDrillDownModal: false,
    selectedItem: null,
    paymentMethod: 'bank_transfer',
    isApproving: false,
    isDeleting: false,
    isPaying: false,
    formatRupiah(val) {
        if (val === null || val === undefined || isNaN(val)) return '0';
        return new Intl.NumberFormat('id-ID').format(Math.round(val));
    }
}">

    <!-- Top Navigation & Overline Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-[#AF52DE]">
                <a href="{{ route('hrm.index', ['tab' => 'payrolls']) }}" class="hover:underline">{{ __('hrm.title') }}</a>
                <span>/</span>
                <span>{{ __('hrm.batch_monthly') }}</span>
                <span>/</span>
                <span class="text-black/40 dark:text-white/40 font-mono">{{ $payroll->payroll_number ?: __('common.detail') }}</span>
            </div>
            <a href="{{ route('hrm.index', ['tab' => 'payrolls']) }}"
                class="inline-flex items-center gap-2 text-[13px] font-semibold text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white transition group">
                <div class="w-7 h-7 rounded-[9px] bg-black/5 dark:bg-white/10 flex items-center justify-center group-hover:bg-black/10 dark:group-hover:bg-white/15 transition">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-black/70 dark:text-white/70"></i>
                </div>
                <span>{{ __('hrm.back_to_hub') }}</span>
            </a>
        </div>

        <!-- Action Workflow Buttons & Export Hub -->
        <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 w-full sm:w-auto justify-start sm:justify-end">
            <!-- Segmented Ekspor Hub -->
            <div class="inline-flex p-1 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 gap-1 shrink-0">
                <a href="{{ route('hrm.payrolls.export-excel', $payroll->id) }}"
                    class="h-9 px-3 rounded-[10px] bg-[#34C759]/15 hover:bg-[#34C759]/25 text-[#34C759] text-[12px] font-bold border border-[#34C759]/30 transition flex items-center gap-1.5 cursor-pointer shadow-xs active:scale-[0.98]"
                    title="{{ __('hrm.tooltip_download_excel') }}">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                    <span>Excel</span>
                </a>

                <a href="{{ route('hrm.payrolls.export', $payroll->id) }}"
                    class="h-9 px-2.5 rounded-[10px] hover:bg-black/5 dark:hover:bg-white/10 text-black/75 dark:text-white/75 text-[12px] font-semibold transition flex items-center gap-1 cursor-pointer active:scale-[0.98]"
                    title="{{ __('hrm.tooltip_download_csv') }}">
                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-black/50 dark:text-white/50"></i>
                    <span>CSV</span>
                </a>

                <a href="{{ route('hrm.payrolls.export-bank', $payroll->id) }}"
                    class="h-9 px-2.5 rounded-[10px] hover:bg-black/5 dark:hover:bg-white/10 text-black/75 dark:text-white/75 text-[12px] font-semibold transition flex items-center gap-1 cursor-pointer active:scale-[0.98]"
                    title="{{ __('hrm.tooltip_download_bank') }}">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    <span>Bank</span>
                </a>
            </div>

            @if($payroll->status === 'draft')
                @if(\App\Support\Context::hasPermission('users.manage'))
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('hrm.payrolls.approve', $payroll->id) }}"
                            @submit="if(isApproving) { $event.preventDefault(); return false; } isApproving = true;">
                            @csrf
                            <button type="submit" :disabled="isApproving"
                                :class="isApproving ? 'opacity-60 cursor-not-allowed' : ''"
                                class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-4.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-bold shadow-xs transition active:scale-[0.98] flex items-center gap-2 cursor-pointer">
                                <template x-if="!isApproving">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                                        <span>{{ __('hrm.action_approve_payroll') }}</span>
                                    </div>
                                </template>
                                <template x-if="isApproving">
                                    <div class="flex items-center gap-2">
                                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>{{ __('hrm.approving_payroll') }}</span>
                                    </div>
                                </template>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('hrm.payrolls.destroy', $payroll->id) }}"
                            @submit="if(isDeleting) { $event.preventDefault(); return false; } if(!confirm(window.__('hrm.confirm_delete_payroll') || '{{ __('hrm.confirm_delete_payroll_title') }}')) { $event.preventDefault(); return false; } isDeleting = true;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" :disabled="isDeleting"
                                :class="isDeleting ? 'opacity-60 cursor-not-allowed' : ''"
                                class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-3.5 rounded-[12px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 text-[12.5px] font-bold transition active:scale-[0.98] flex items-center gap-1.5 cursor-pointer">
                                <template x-if="!isDeleting">
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        <span>{{ __('hrm.delete_draft') }}</span>
                                    </div>
                                </template>
                                <template x-if="isDeleting">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="animate-spin w-3.5 h-3.5 text-[#FF3B30]" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>{{ __('hrm.deleting_draft') }}</span>
                                    </div>
                                </template>
                            </button>
                        </form>
                    </div>
                @endif
            @elseif($payroll->status === 'approved')
                @if(\App\Support\Context::hasPermission('users.manage'))
                    <button type="button" @click="showPayModal = true"
                        class="h-11 sm:h-10 min-h-[44px] sm:min-h-0 px-5 rounded-[12px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[13px] font-bold shadow-[0_4px_16px_rgba(52,199,89,0.3)] transition active:scale-[0.98] flex items-center gap-2 cursor-pointer">
                        <i data-lucide="check-circle-2" class="w-4.5 h-4.5"></i>
                        <span>{{ __('hrm.action_pay_payroll') }}</span>
                    </button>
                @endif
            @else
                <div class="h-11 sm:h-10 px-4 rounded-[12px] bg-[#34C759]/15 text-[#34C759] text-[12.5px] font-bold flex items-center gap-2 border border-[#34C759]/30">
                    <i data-lucide="check-circle" class="w-4.5 h-4.5"></i>
                    <span>{{ $payroll->paid_at ? __('hrm.paid_at_time', ['time' => $payroll->paid_at->translatedFormat('d M Y H:i')]) : __('hrm.completed') }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Bento Financial Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <!-- Gaji Bersih (THP) -->
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.03)] space-y-2">
            <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                <span class="text-[12px] font-bold uppercase tracking-wider">{{ __('hrm.total_thp_label') }}</span>
                <div class="w-8 h-8 rounded-[9px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-[24px] sm:text-[26px] font-extrabold text-[#34C759] tracking-tight tabular-nums truncate">
                Rp {{ number_format((float)$payroll->total_take_home_pay, 0, ',', '.') }}
            </div>
            <p class="text-[11.5px] text-black/55 dark:text-white/55 font-medium">{{ __('hrm.transfer_to_account') }}</p>
        </div>

        <!-- Total Beban Usaha Perusahaan -->
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.03)] space-y-2">
            <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                <span class="text-[12px] font-bold uppercase tracking-wider">{{ __('hrm.total_company_burden_label') }}</span>
                <div class="w-8 h-8 rounded-[9px] bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 flex items-center justify-center">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-[24px] sm:text-[26px] font-extrabold text-black dark:text-white tracking-tight tabular-nums truncate">
                Rp {{ number_format((float)$payroll->total_company_cost, 0, ',', '.') }}
            </div>
            <p class="text-[11.5px] text-black/55 dark:text-white/55 font-medium">{{ __('hrm.company_cost_desc') }}</p>
        </div>

        <!-- Total Iuran BPJS -->
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.03)] space-y-2">
            <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                <span class="text-[12px] font-bold uppercase tracking-wider">{{ __('hrm.payroll_total_bpjs') }}</span>
                <div class="w-8 h-8 rounded-[9px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="shield" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-[22px] sm:text-[24px] font-extrabold text-[#007AFF] tracking-tight tabular-nums truncate">
                Rp {{ number_format((float)($payroll->total_bpjs_company + $payroll->total_bpjs_employee), 0, ',', '.') }}
            </div>
            <p class="text-[11.5px] text-black/55 dark:text-white/55 font-medium">
                {{ __('hrm.company_portion_label', ['amount' => 'Rp ' . number_format((float)$payroll->total_bpjs_company, 0, ',', '.')]) }}
            </p>
        </div>

        <!-- Setoran Pajak PPh 21 TER -->
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_10px_rgba(0,0,0,0.03)] space-y-2">
            <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                <span class="text-[12px] font-bold uppercase tracking-wider">{{ __('hrm.tax_calc_title') }}</span>
                <div class="w-8 h-8 rounded-[9px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                    <i data-lucide="scale" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-[22px] sm:text-[24px] font-extrabold text-[#AF52DE] tracking-tight tabular-nums truncate">
                Rp {{ number_format((float)$payroll->total_pph21, 0, ',', '.') }}
            </div>
            <p class="text-[11.5px] text-black/55 dark:text-white/55 font-medium">{{ __('hrm.tax_deposit_desc') }}</p>
        </div>
    </div>

    <!-- Tabel Daftar Slip Gaji Tiap Karyawan -->
    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden space-y-2">
        <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.payslip_breakdown_title') }}</h3>
                    <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.payslip_breakdown_subtitle') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-[12px] font-bold text-black/60 dark:text-white/60">{{ __('hrm.batch_status') }}:</span>
                @if($payroll->status === 'paid')
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/30">{{ __('hrm.status_paid_badge') }}</span>
                @elseif($payroll->status === 'approved')
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-[#007AFF]/15 text-[#007AFF] border border-[#007AFF]/30">{{ __('hrm.status_approved_badge') }}</span>
                @else
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30">{{ __('hrm.status_draft_badge') }}</span>
                @endif
            </div>
        </div>

        <!-- Desktop View (>= 768px) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-[13px] border-collapse min-w-[1020px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                        <th class="py-3.5 px-4 sm:px-5">{{ __('hrm.th_employee') }}</th>
                        <th class="py-3.5 px-3">{{ __('hrm.th_base_salary') }}</th>
                        <th class="py-3.5 px-3">{{ __('hrm.th_allowances_commissions') }}</th>
                        <th class="py-3.5 px-3">{{ __('hrm.th_gross') }}</th>
                        <th class="py-3.5 px-3">{{ __('hrm.th_bpjs_tax') }}</th>
                        <th class="py-3.5 px-3">{{ __('hrm.th_loan_deductions') }}</th>
                        <th class="py-3.5 px-3">{{ __('hrm.th_take_home_pay') }}</th>
                        <th class="py-3.5 px-4 text-right">{{ __('hrm.th_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @foreach($payroll->items as $item)
                        @php
                            $wa = preg_replace('/[^0-9]/', '', (string)$item->whatsapp_number);
                            if (str_starts_with($wa, '0')) {
                                $wa = '62' . substr($wa, 1);
                            }
                            $service = app(\App\Domain\HRM\PayrollRunService::class);
                            $waText = urlencode($service->buildWhatsAppSlipMessage($item));
                        @endphp
                        <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.015] transition">
                            <td class="py-3.5 px-4 sm:px-5">
                                <div class="font-bold text-black dark:text-white text-[13.5px]">{{ $item->employee_name }}</div>
                                <div class="text-[11.5px] text-black/60 dark:text-white/60 font-medium">{{ $item->job_title ?: __('hrm.staff_label') }}</div>
                            </td>
                            <td class="py-3.5 px-3 tabular-nums text-black dark:text-white">
                                @if($item->employment_type === 'daily_worker')
                                    <div class="font-semibold">Rp {{ number_format((float)$item->daily_rate, 0, ',', '.') }} x {{ $item->days_worked }} hr</div>
                                    <div class="text-[11px] text-[#FF9500] font-bold">{{ __('hrm.staff_daily') }}</div>
                                @else
                                    <div class="font-semibold">Rp {{ number_format((float)$item->base_salary, 0, ',', '.') }}</div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50 font-medium">{{ __('hrm.staff_monthly') }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 tabular-nums text-black/80 dark:text-white/80 font-medium">
                                Rp {{ number_format((float)($item->fixed_allowances + $item->variable_allowances + $item->commissions + $item->overtime_pay), 0, ',', '.') }}
                                @if($item->thr_amount > 0)
                                    <div class="text-[10.5px] text-[#34C759] font-bold">+ THR: Rp {{ number_format((float)$item->thr_amount, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 tabular-nums font-bold text-black dark:text-white">
                                Rp {{ number_format((float)$item->gross_pay, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-3 tabular-nums text-[#FF3B30] font-semibold">
                                -Rp {{ number_format((float)($item->bpjs_tk_employee + $item->bpjs_kes_employee + $item->pph21_amount), 0, ',', '.') }}
                                <div class="text-[10.5px] text-black/50 dark:text-white/50 font-normal">
                                    BPJS: {{ number_format((float)($item->bpjs_tk_employee + $item->bpjs_kes_employee), 0, ',', '.') }} | Pajak: {{ number_format((float)$item->pph21_amount, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="py-3.5 px-3 tabular-nums text-[#FF9500] font-semibold">
                                @if($item->loan_deduction > 0)
                                    -Rp {{ number_format((float)$item->loan_deduction, 0, ',', '.') }}
                                @else
                                    <span class="text-[11px] text-black/35 dark:text-white/35 font-normal">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 tabular-nums font-extrabold text-[#34C759] text-[14px]">
                                Rp {{ number_format((float)$item->take_home_pay, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Interactive Drill-Down Trigger -->
                                    <button type="button" @click="selectedItem = {{ Js::from($item) }}; showDrillDownModal = true"
                                        class="h-8 px-2.5 rounded-[9px] text-[11.5px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition flex items-center gap-1.5 cursor-pointer"
                                        title="{{ __('hrm.tooltip_drilldown') }}"
                                        aria-label="{{ __('hrm.tooltip_drilldown') }}">
                                        <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('hrm.detail_btn') }}</span>
                                    </button>

                                    <!-- Tombol Slip Digital -->
                                    <a href="{{ route('hrm.payslips.show', $item->id) }}"
                                        class="h-8 px-2.5 rounded-[9px] text-[11.5px] font-bold text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 active:scale-[0.97] transition flex items-center gap-1.5 cursor-pointer"
                                        title="{{ __('hrm.tooltip_view_digital_slip') }}"
                                        aria-label="{{ __('hrm.tooltip_view_digital_slip') }}">
                                        <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('hrm.slip_btn') }}</span>
                                    </a>

                                    <!-- Tombol WhatsApp -->
                                    @if($wa)
                                        <a href="https://wa.me/{{ $wa }}?text={{ $waText }}" target="_blank"
                                            class="h-8 px-2.5 rounded-[9px] text-[11.5px] font-bold text-[#25D366] bg-[#25D366]/10 hover:bg-[#25D366]/15 active:scale-[0.97] transition flex items-center gap-1.5 cursor-pointer"
                                            title="{{ __('hrm.tooltip_send_wa_slip') }}"
                                            aria-label="{{ __('hrm.tooltip_send_wa_slip') }}">
                                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('hrm.wa_btn') }}</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Bento Card View (< 768px) -->
        <div class="md:hidden divide-y divide-black/5 dark:divide-white/5">
            @foreach($payroll->items as $item)
                @php
                    $wa = preg_replace('/[^0-9]/', '', (string)$item->whatsapp_number);
                    if (str_starts_with($wa, '0')) {
                        $wa = '62' . substr($wa, 1);
                    }
                    $service = app(\App\Domain\HRM\PayrollRunService::class);
                    $waText = urlencode($service->buildWhatsAppSlipMessage($item));
                @endphp
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <div class="font-bold text-black dark:text-white text-[14px] truncate">{{ $item->employee_name }}</div>
                            <div class="text-[11.5px] text-black/60 dark:text-white/60 font-medium">{{ $item->job_title ?: __('hrm.staff_label') }}</div>
                        </div>
                        @if($item->employment_type === 'daily_worker')
                            <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF9500]/15 text-[#FF9500] shrink-0">
                                {{ __('hrm.staff_daily') }}
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-medium bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 shrink-0">
                                {{ __('hrm.staff_monthly') }}
                            </span>
                        @endif
                    </div>

                    <!-- Breakdown Bento Grid -->
                    <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-2 text-[12px]">
                        <div class="flex items-center justify-between">
                            <span class="text-black/60 dark:text-white/60">{{ __('hrm.th_base_salary') }}</span>
                            <span class="font-semibold text-black dark:text-white tabular-nums">
                                @if($item->employment_type === 'daily_worker')
                                    Rp {{ number_format((float)$item->daily_rate, 0, ',', '.') }} ({{ $item->days_worked }} hr)
                                @else
                                    Rp {{ number_format((float)$item->base_salary, 0, ',', '.') }}
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-black/60 dark:text-white/60">{{ __('hrm.th_allowances_commissions') }}</span>
                            <span class="font-semibold text-black/80 dark:text-white/80 tabular-nums">
                                Rp {{ number_format((float)($item->fixed_allowances + $item->variable_allowances + $item->commissions + $item->overtime_pay), 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-black/60 dark:text-white/60">{{ __('hrm.th_bpjs_tax') }}</span>
                            <span class="font-semibold text-[#FF3B30] tabular-nums">
                                -Rp {{ number_format((float)($item->bpjs_tk_employee + $item->bpjs_kes_employee + $item->pph21_amount), 0, ',', '.') }}
                            </span>
                        </div>
                        @if($item->loan_deduction > 0)
                            <div class="flex items-center justify-between">
                                <span class="text-black/60 dark:text-white/60">{{ __('hrm.th_loan_deductions') }}</span>
                                <span class="font-semibold text-[#FF9500] tabular-nums">
                                    -Rp {{ number_format((float)$item->loan_deduction, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                        <div class="pt-2 border-t border-black/5 dark:border-white/10 flex items-center justify-between">
                            <div>
                                <span class="text-[10.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 block">{{ __('hrm.th_take_home_pay') }}</span>
                                <span class="text-[15px] font-extrabold text-[#34C759] tabular-nums">
                                    Rp {{ number_format((float)$item->take_home_pay, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-black/40 dark:text-white/40 block">{{ __('hrm.th_gross') }}</span>
                                <span class="text-[12px] font-bold text-black/80 dark:text-white/80 tabular-nums">
                                    Rp {{ number_format((float)$item->gross_pay, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-1 flex flex-wrap items-center gap-2">
                        <button type="button" @click="selectedItem = {{ Js::from($item) }}; showDrillDownModal = true"
                            class="flex-1 h-11 px-3 rounded-[12px] text-[12.5px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="calculator" class="w-4 h-4"></i>
                            <span>{{ __('hrm.detail_btn') }}</span>
                        </button>

                        <a href="{{ route('hrm.payslips.show', $item->id) }}"
                            class="h-11 px-3.5 rounded-[12px] text-[12.5px] font-bold text-black/80 dark:text-white/80 bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                            <span>{{ __('hrm.slip_btn') }}</span>
                        </a>

                        @if($wa)
                            <a href="https://wa.me/{{ $wa }}?text={{ $waText }}" target="_blank"
                                class="h-11 px-3.5 rounded-[12px] text-[12.5px] font-bold text-[#25D366] bg-[#25D366]/10 hover:bg-[#25D366]/20 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer shrink-0"
                                title="{{ __('hrm.tooltip_send_wa_slip') }}"
                                aria-label="{{ __('hrm.tooltip_send_wa_slip') }}">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                <span>{{ __('hrm.wa_btn') }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- MODAL PEMBAYARAN GAJI (MARK AS PAID) - BENTO APPLE HIG -->
    <div x-show="showPayModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display: none;">
        <div @click.outside="showPayModal = false" class="w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/10 dark:border-white/10 shadow-[0_24px_48px_rgba(0,0,0,0.25)] overflow-hidden flex flex-col max-h-[88vh]">
            <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                        <i data-lucide="credit-card" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.confirm_payment_title') }}</h3>
                        <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.confirm_payment_sub') }}</p>
                    </div>
                </div>
                <button type="button" @click="showPayModal = false"
                    aria-label="{{ __('common.close') }}"
                    class="w-10 h-10 min-w-[40px] min-h-[40px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 active:scale-95 transition cursor-pointer">
                    <i data-lucide="x" class="w-4.5 h-4.5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('hrm.payrolls.pay', $payroll->id) }}"
                @submit="if(isPaying) { $event.preventDefault(); return false; } isPaying = true;"
                class="p-5 space-y-4 overflow-y-auto">
                @csrf
                <div class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 text-center space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#34C759] block">{{ __('hrm.total_transferred_funds') }}</span>
                    <div class="text-[26px] font-black text-[#34C759] tabular-nums tracking-tight">
                        Rp {{ number_format((float)$payroll->total_take_home_pay, 0, ',', '.') }}
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('hrm.payment_method_label') }} <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="payment_method" x-model="paymentMethod" required
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                        <option value="bank_transfer">{{ __('hrm.method_bank_transfer') }}</option>
                        <option value="cash">{{ __('hrm.method_cash') }}</option>
                        <option value="multi">{{ __('hrm.method_multi') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">
                        {{ __('hrm.payment_notes_label') }}
                    </label>
                    <textarea name="notes" rows="2" placeholder="{{ __('hrm.payment_notes_placeholder') }}"
                        class="w-full p-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50"></textarea>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showPayModal = false"
                        class="h-11 sm:h-10 px-4.5 rounded-[11px] text-[14px] sm:text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit" :disabled="isPaying"
                        :class="isPaying ? 'opacity-60 cursor-not-allowed' : ''"
                        class="h-11 sm:h-10 px-5 rounded-[11px] text-[14px] sm:text-[13px] font-bold text-white bg-[#34C759] hover:bg-[#2FB34F] shadow-[0_4px_14px_rgba(52,199,89,0.3)] transition active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2">
                        <template x-if="!isPaying">
                            <span>{{ __('hrm.confirm_paid_button') }}</span>
                        </template>
                        <template x-if="isPaying">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ __('hrm.processing_payment') }}</span>
                            </div>
                        </template>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL INTERAKTIF: WEB DRILL-DOWN KALKULASI GAJI & BEBAN PERUSAHAAN (APPLE HIG BENTO MODAL) -->
    <div x-show="showDrillDownModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-black/60 backdrop-blur-md" style="display: none;">
        <div @click.outside="showDrillDownModal = false" class="w-full max-w-4xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_24px_64px_rgba(0,0,0,0.3)] overflow-hidden flex flex-col max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="p-5 sm:p-6 border-b border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.015] dark:bg-white/[0.02]">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0 shadow-xs">
                        <i data-lucide="calculator" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-[17px] font-bold text-black dark:text-white" x-text="selectedItem?.employee_name || '{{ __('hrm.drilldown_modal_title') }}'"></h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                                :class="selectedItem?.employment_type === 'daily_worker' ? 'bg-[#FF9500]/15 text-[#FF9500]' : 'bg-[#007AFF]/15 text-[#007AFF]'"
                                x-text="selectedItem?.employment_type === 'daily_worker' ? (window.__('hrm.daily_worker_badge') || '{{ __('hrm.staff_daily') }}') : (window.__('hrm.permanent_contract_badge') || '{{ __('hrm.permanent_contract_badge') }}')">
                            </span>
                        </div>
                        <p class="text-[12.5px] text-black/60 dark:text-white/60">
                            <span x-text="selectedItem?.job_title || '{{ __('hrm.staff_label') }}'"></span>
                            <span x-show="selectedItem?.tenure_months"> &bull; {{ __('hrm.drilldown_tenure_months', ['months' => '']) }}<span x-text="selectedItem?.tenure_months"></span> bln</span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="showDrillDownModal = false"
                    aria-label="{{ __('common.close') }}"
                    class="w-10 h-10 min-w-[40px] min-h-[40px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 active:scale-95 transition cursor-pointer">
                    <i data-lucide="x" class="w-4.5 h-4.5"></i>
                </button>
            </div>

            <!-- Modal Body (Scrollable Bento Container) -->
            <div class="p-5 sm:p-6 overflow-y-auto space-y-5">
                
                <!-- 3 Bento KPI Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">{{ __('hrm.drilldown_gross_pay') }}</span>
                        <div class="text-[20px] font-extrabold text-black dark:text-white tabular-nums">
                            Rp <span x-text="formatRupiah(selectedItem?.gross_pay)"></span>
                        </div>
                        <p class="text-[10.5px] text-black/50 dark:text-white/50">{{ __('hrm.drilldown_gross_sub') }}</p>
                    </div>

                    <div class="p-4 rounded-[16px] bg-[#FF3B30]/5 dark:bg-[#FF3B30]/10 border border-[#FF3B30]/15 space-y-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#FF3B30]">{{ __('hrm.drilldown_total_deductions') }}</span>
                        <div class="text-[20px] font-extrabold text-[#FF3B30] tabular-nums">
                            -Rp <span x-text="formatRupiah(selectedItem?.total_deductions)"></span>
                        </div>
                        <p class="text-[10.5px] text-[#FF3B30]/70">{{ __('hrm.drilldown_deductions_sub') }}</p>
                    </div>

                    <div class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 space-y-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#34C759]">{{ __('hrm.drilldown_thp') }}</span>
                        <div class="text-[20px] font-black text-[#34C759] tabular-nums">
                            Rp <span x-text="formatRupiah(selectedItem?.take_home_pay)"></span>
                        </div>
                        <p class="text-[10.5px] text-[#34C759]/80 font-medium">{{ __('hrm.drilldown_thp_sub') }}</p>
                    </div>
                </div>

                <!-- 2-Column Bento Breakdown Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <!-- Left Column: Komponen Pendapatan -->
                    <div class="p-4.5 rounded-[18px] bg-white dark:bg-[#242426] border border-black/10 dark:border-white/10 shadow-xs space-y-3">
                        <div class="flex items-center gap-2 pb-2.5 border-b border-black/5 dark:border-white/10 text-black dark:text-white font-bold text-[14px]">
                            <i data-lucide="plus-circle" class="w-4 h-4 text-[#34C759]"></i>
                            <span>{{ __('hrm.drilldown_income_components') }}</span>
                        </div>

                        <div class="space-y-2.5 text-[12.5px]">
                            <div class="flex items-center justify-between">
                                <span class="text-black/70 dark:text-white/70"
                                    x-text="selectedItem?.employment_type === 'daily_worker' ? (window.__('hrm.staff_daily') || 'Upah Harian') + ' (' + (selectedItem?.days_worked || 0) + ' hr @ Rp ' + formatRupiah(selectedItem?.daily_rate) + ')' : (window.__('hrm.field_base_salary') || 'Gaji Pokok Bulanan')"></span>
                                <span class="font-bold text-black dark:text-white tabular-nums">
                                    Rp <span x-text="formatRupiah(selectedItem?.base_salary)"></span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-black/70 dark:text-white/70">{{ __('hrm.field_fixed_allowances') }}</span>
                                <span class="font-semibold text-black/80 dark:text-white/80 tabular-nums">
                                    Rp <span x-text="formatRupiah(selectedItem?.fixed_allowances)"></span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-black/70 dark:text-white/70">{{ __('hrm.field_variable_allowances') }}</span>
                                <span class="font-semibold text-black/80 dark:text-white/80 tabular-nums">
                                    Rp <span x-text="formatRupiah(selectedItem?.variable_allowances)"></span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-black/70 dark:text-white/70">{{ __('hrm.payroll_overtime_pay') }}</span>
                                <span class="font-semibold text-black/80 dark:text-white/80 tabular-nums">
                                    Rp <span x-text="formatRupiah(selectedItem?.overtime_pay)"></span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-black/70 dark:text-white/70">{{ __('hrm.payroll_commissions') }}</span>
                                <span class="font-semibold text-black/80 dark:text-white/80 tabular-nums">
                                    Rp <span x-text="formatRupiah(selectedItem?.commissions)"></span>
                                </span>
                            </div>

                            <template x-if="selectedItem?.thr_amount > 0">
                                <div class="flex items-center justify-between text-[#34C759]">
                                    <span class="font-medium">{{ __('hrm.payroll_thr_amount') }}</span>
                                    <span class="font-bold tabular-nums">
                                        +Rp <span x-text="formatRupiah(selectedItem?.thr_amount)"></span>
                                    </span>
                                </div>
                            </template>
                        </div>

                        <div class="pt-3 border-t border-black/5 dark:border-white/10 flex items-center justify-between">
                            <span class="text-[13px] font-bold text-black dark:text-white">{{ __('hrm.drilldown_gross_pay') }}</span>
                            <span class="text-[15px] font-extrabold text-black dark:text-white tabular-nums">
                                Rp <span x-text="formatRupiah(selectedItem?.gross_pay)"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Right Column: Komponen Potongan Gaji Karyawan -->
                    <div class="p-4.5 rounded-[18px] bg-white dark:bg-[#242426] border border-black/10 dark:border-white/10 shadow-xs space-y-3">
                        <div class="flex items-center gap-2 pb-2.5 border-b border-black/5 dark:border-white/10 text-black dark:text-white font-bold text-[14px]">
                            <i data-lucide="minus-circle" class="w-4 h-4 text-[#FF3B30]"></i>
                            <span>{{ __('hrm.drilldown_deductions_components') }}</span>
                        </div>

                        <div class="space-y-2.5 text-[12.5px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-black/70 dark:text-white/70 block">{{ __('hrm.tax_calc_title') }}</span>
                                    <span class="text-[10.5px] text-black/50 dark:text-white/50 block"
                                        x-text="'Kategori TER ' + (selectedItem?.pph21_ter_category || 'A') + ' (' + (selectedItem?.pph21_ter_rate || 0) + '%)'"></span>
                                </div>
                                <span class="font-bold text-[#FF3B30] tabular-nums">
                                    -Rp <span x-text="formatRupiah(selectedItem?.pph21_amount)"></span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-black/70 dark:text-white/70 block">{{ __('hrm.field_bpjs_tk') }}</span>
                                    <span class="text-[10.5px] text-black/50 dark:text-white/50 block">{{ __('hrm.bpjs_tk_deduction_label') }}</span>
                                </div>
                                <span class="font-semibold text-[#FF3B30] tabular-nums">
                                    -Rp <span x-text="formatRupiah(selectedItem?.bpjs_tk_employee)"></span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-black/70 dark:text-white/70 block">{{ __('hrm.field_bpjs_kes') }}</span>
                                    <span class="text-[10.5px] text-black/50 dark:text-white/50 block">{{ __('hrm.bpjs_kes_deduction_label') }}</span>
                                </div>
                                <span class="font-semibold text-[#FF3B30] tabular-nums">
                                    -Rp <span x-text="formatRupiah(selectedItem?.bpjs_kes_employee)"></span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between" x-show="selectedItem?.loan_deduction > 0">
                                <span class="text-black/70 dark:text-white/70">{{ __('hrm.loan_installment_deduction') }}</span>
                                <span class="font-semibold text-[#FF9500] tabular-nums">
                                    -Rp <span x-text="formatRupiah(selectedItem?.loan_deduction)"></span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between" x-show="selectedItem?.other_deductions > 0">
                                <span class="text-black/70 dark:text-white/70">{{ __('hrm.attendance_other_deduction') }}</span>
                                <span class="font-semibold text-[#FF3B30] tabular-nums">
                                    -Rp <span x-text="formatRupiah(selectedItem?.other_deductions)"></span>
                                </span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-black/5 dark:border-white/10 flex items-center justify-between">
                            <span class="text-[13px] font-bold text-black dark:text-white">{{ __('hrm.total_deduction_label') }}</span>
                            <span class="text-[15px] font-extrabold text-[#FF3B30] tabular-nums">
                                -Rp <span x-text="formatRupiah(selectedItem?.total_deductions)"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Bottom Bento Section: Beban Riil Perusahaan (Employer Contribution) -->
                <div class="p-4.5 rounded-[18px] bg-black/[0.025] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 pb-2 border-b border-black/5 dark:border-white/10">
                        <div class="flex items-center gap-2 text-black dark:text-white font-bold text-[13.5px]">
                            <i data-lucide="building" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>{{ __('hrm.employer_burden_breakdown') }}</span>
                        </div>
                        <span class="text-[11px] text-black/50 dark:text-white/50">{{ __('hrm.employer_burden_desc') }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-[12.5px]">
                        <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5">
                            <span class="text-black/60 dark:text-white/60 block text-[11px]">{{ __('hrm.office_bpjs_tk') }}</span>
                            <span class="font-bold text-black dark:text-white tabular-nums">
                                Rp <span x-text="formatRupiah(selectedItem?.bpjs_tk_company)"></span>
                            </span>
                            <span class="text-[10px] text-black/40 dark:text-white/40 block">JKK + JKM + JHT + JP</span>
                        </div>

                        <div class="p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5">
                            <span class="text-black/60 dark:text-white/60 block text-[11px]">{{ __('hrm.office_bpjs_kes') }}</span>
                            <span class="font-bold text-black dark:text-white tabular-nums">
                                Rp <span x-text="formatRupiah(selectedItem?.bpjs_kes_company)"></span>
                            </span>
                            <span class="text-[10px] text-black/40 dark:text-white/40 block">{{ __('hrm.fully_covered_business') }}</span>
                        </div>

                        <div class="p-3 rounded-[12px] bg-[#007AFF]/10 border border-[#007AFF]/20">
                            <span class="text-[#007AFF] font-bold block text-[11px] uppercase tracking-wider">{{ __('hrm.company_total_cost_label') }}</span>
                            <span class="text-[15px] font-black text-[#007AFF] tabular-nums">
                                Rp <span x-text="formatRupiah(selectedItem?.company_total_cost)"></span>
                            </span>
                            <span class="text-[10px] text-[#007AFF]/70 block">{{ __('hrm.gross_plus_company_bpjs') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Bento Card: Informasi Rekening Transfer & Kontak -->
                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-[12px]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[9px] bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 shrink-0">
                            <i data-lucide="credit-card" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="text-black/50 dark:text-white/50 block text-[11px]">{{ __('hrm.target_transfer_account') }}:</span>
                            <span class="font-bold text-black dark:text-white" x-text="(selectedItem?.bank_name || 'Bank') + ' - ' + (selectedItem?.bank_account_number || '-') + ' (a.n ' + (selectedItem?.bank_account_holder || selectedItem?.employee_name) + ')'"></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2" x-show="selectedItem?.whatsapp_number">
                        <i data-lucide="phone" class="w-3.5 h-3.5 text-[#25D366]"></i>
                        <span class="text-black/60 dark:text-white/60 font-mono" x-text="selectedItem?.whatsapp_number"></span>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="p-4 sm:p-5 border-t border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-black/[0.015] dark:bg-white/[0.02]">
                <a :href="'/hrm/payslips/' + selectedItem?.id"
                    class="h-10 px-4 rounded-[11px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12.5px] font-bold flex items-center justify-center gap-1.5 transition active:scale-[0.98] shadow-xs cursor-pointer">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    <span>{{ __('hrm.open_full_official_payslip') }}</span>
                </a>

                <button type="button" @click="showDrillDownModal = false"
                    class="h-10 px-5 rounded-[11px] bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 text-black/70 dark:text-white/70 text-[12.5px] font-semibold transition cursor-pointer">
                    {{ __('hrm.close_details') }}
                </button>
            </div>

        </div>
    </div>

</div>
@endsection
