@php
    $periodMonthCarbon = \Carbon\Carbon::create()->month($item->payroll->period_month);
    $periodStr = $periodMonthCarbon->translatedFormat('F') . ' ' . $item->payroll->period_year;
    $empName = $item->user?->name ?? __('hrm.payslip_employee_name_label');
    $empPhone = $item->user?->phone ?? '';
    $cleanPhone = preg_replace('/[^0-9]/', '', $empPhone);
    if (str_starts_with($cleanPhone, '0')) {
        $cleanPhone = '62' . substr($cleanPhone, 1);
    }
    $waUrl = !empty($cleanPhone) 
        ? "https://wa.me/{$cleanPhone}?text=" . rawurlencode($whatsappMessage)
        : "https://wa.me/?text=" . rawurlencode($whatsappMessage);
    $isPublicMode = !empty($isPublic);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('hrm.payslip_title') }} - {{ $empName }} ({{ $periodStr }}) - {{ $business->name }}</title>

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>

    <!-- html2pdf.js for Direct Client-Side PDF Download -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .mono {
            font-family: 'JetBrains Mono', monospace;
        }
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }
        @media print {
            html, body {
                background-color: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .print-sheet {
                box-shadow: none !important;
                border: 1px solid #d1d5db !important;
                border-radius: 14px !important;
                padding: 24px !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .keep-together {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 py-6 sm:py-10 px-3 sm:px-6 transition-colors min-h-screen">

    <!-- Top Action Bar (Screen Only) -->
    <header class="no-print max-w-3xl mx-auto mb-6 bg-white dark:bg-slate-900/95 p-3.5 sm:p-4 rounded-[22px] shadow-sm border border-black/[0.08] dark:border-white/[0.1] backdrop-blur-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full {{ $item->payroll->status === 'paid' ? 'bg-[#34C759]' : ($item->payroll->status === 'approved' ? 'bg-[#007AFF]' : 'bg-[#FF9500]') }}"></span>
                <span class="text-[13px] font-bold tracking-tight text-slate-800 dark:text-slate-200">
                    {{ __('hrm.payslip_title') }} &bull; {{ $periodStr }}
                </span>
            </div>

            <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 w-full sm:w-auto">
                @if(!$isPublicMode)
                    <a href="{{ route('hrm.payrolls.show', $item->payroll_id) }}" class="h-10 sm:h-9 min-h-[40px] px-3.5 rounded-[11px] bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-[12.5px] sm:text-[12px] font-bold active:scale-[0.97] transition-all text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 inline-flex items-center justify-center gap-1.5 cursor-pointer" aria-label="{{ __('hrm.batch_monthly') }}">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                        <span>{{ __('hrm.batch_monthly') }}</span>
                    </a>
                @endif

                <button type="button" onclick="window.print()" class="h-10 sm:h-9 min-h-[40px] px-3.5 rounded-[11px] bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[12.5px] sm:text-[12px] font-bold flex items-center justify-center gap-1.5 active:scale-[0.97] transition-all border border-slate-200 dark:border-slate-700 cursor-pointer" aria-label="{{ __('hrm.payslip_print_a4') }}">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>
                    <span>{{ __('hrm.payslip_print_a4') }}</span>
                </button>

                <button id="btnPdf" type="button" onclick="downloadPayslipPDF()" class="h-10 sm:h-9 min-h-[40px] px-3.5 rounded-[11px] bg-black dark:bg-white text-white dark:text-black hover:bg-black/90 dark:hover:bg-white/90 text-[12.5px] sm:text-[12px] font-bold shadow-xs flex items-center justify-center gap-1.5 active:scale-[0.97] transition-all cursor-pointer" aria-label="{{ __('hrm.payslip_download_pdf') }}">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span>{{ __('hrm.payslip_download_pdf') }}</span>
                </button>

                <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="h-10 sm:h-9 min-h-[40px] px-3.5 rounded-[11px] bg-[#25D366] hover:bg-[#20bd5a] text-white text-[12.5px] sm:text-[12px] font-bold shadow-xs flex items-center justify-center gap-1.5 active:scale-[0.97] transition-all cursor-pointer" aria-label="{{ __('hrm.action_send_whatsapp') }}">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    <span>WhatsApp</span>
                </a>

                <button type="button" onclick="copySlipSummary()" class="col-span-2 sm:col-span-1 h-10 sm:h-9 min-h-[40px] px-3 rounded-[11px] bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[12.5px] sm:text-[12px] font-semibold flex items-center justify-center gap-1 active:scale-[0.97] transition-all border border-slate-200 dark:border-slate-700 cursor-pointer" title="{{ __('hrm.payslip_copy_summary') }}" aria-label="{{ __('hrm.payslip_copy_summary') }}">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    <span id="copyBtnText">{{ __('hrm.payslip_copy_summary') }}</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Payslip Sheet Container (A4 / Printable) -->
    <main id="payslipContent" class="print-sheet max-w-3xl mx-auto bg-white dark:bg-slate-900 rounded-[24px] shadow-sm border border-black/[0.08] dark:border-white/[0.1] p-6 sm:p-10 transition-colors">
        
        <!-- Header: Business & Document Info -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-6 border-b border-black/[0.08] dark:border-white/[0.1]">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] text-[11.5px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                    {{ $business->name }}
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    {{ __('hrm.payslip_header_main') }}
                </h1>
                <p class="text-[13px] text-slate-600 dark:text-slate-400">
                    {{ __('hrm.payslip_period_label') }}: <strong class="text-slate-900 dark:text-white font-bold">{{ $periodStr }}</strong>
                </p>
            </div>

            <div class="sm:text-right space-y-1">
                <div class="text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold">{{ __('hrm.payslip_document_number') }}</div>
                <div class="mono text-[13px] font-bold text-slate-900 dark:text-white">{{ $item->payroll->payroll_number }}</div>
                <div class="inline-block mt-1.5 px-2.5 py-0.5 rounded-[6px] text-[11px] font-bold uppercase tracking-wider {{ $item->payroll->status === 'paid' ? 'bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/30' : ($item->payroll->status === 'approved' ? 'bg-[#007AFF]/15 text-[#007AFF] border border-[#007AFF]/30' : 'bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30') }}">
                    {{ $item->payroll->status === 'paid' ? __('hrm.payroll_status_paid') : ($item->payroll->status === 'approved' ? __('hrm.payroll_status_approved') : __('hrm.payroll_status_draft')) }}
                </div>
                @if($item->payroll->payment_date)
                    <div class="text-[11px] text-slate-600 dark:text-slate-400 pt-0.5 font-medium">
                        {{ __('hrm.payslip_payment_date', ['date' => \Carbon\Carbon::parse($item->payroll->payment_date)->translatedFormat('d F Y')]) }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Employee Info Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 py-6 border-b border-black/[0.08] dark:border-white/[0.1] text-[12.5px]">
            <div class="space-y-2.5">
                <div class="flex justify-between sm:justify-start sm:gap-6">
                    <span class="text-slate-500 dark:text-slate-400 w-32 font-medium">{{ __('hrm.payslip_employee_name_label') }}</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $empName }}</span>
                </div>
                <div class="flex justify-between sm:justify-start sm:gap-6">
                    <span class="text-slate-500 dark:text-slate-400 w-32 font-medium">{{ __('hrm.payslip_employment_status_label') }}</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">
                        {{ $item->employment_type === 'permanent' ? __('hrm.payslip_emp_status_permanent') : ($item->employment_type === 'contract' ? __('hrm.payslip_emp_status_contract') : __('hrm.payslip_emp_status_daily')) }}
                    </span>
                </div>
                <div class="flex justify-between sm:justify-start sm:gap-6">
                    <span class="text-slate-500 dark:text-slate-400 w-32 font-medium">{{ __('hrm.payslip_ptkp_status_label') }}</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">
                        {{ $item->tax_ptkp_status ?? 'TK/0' }}
                        @if($item->tax_ter_category)
                            <span class="text-[11px] text-slate-600 dark:text-slate-400">(TER {{ $item->tax_ter_category }} - {{ number_format((float)$item->tax_ter_rate * 100, 2) }}%)</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between sm:justify-start sm:gap-6">
                    <span class="text-slate-500 dark:text-slate-400 w-32 font-medium">{{ __('hrm.col_work_days') }}</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $item->days_worked ?? 0 }}</span>
                </div>
            </div>

            <div class="space-y-2.5 sm:border-l sm:border-black/[0.08] sm:dark:border-white/[0.1] sm:pl-6">
                <div class="flex justify-between sm:justify-start sm:gap-6">
                    <span class="text-slate-500 dark:text-slate-400 w-32 font-medium">{{ __('hrm.field_bank_name') }}</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">
                        {{ !empty($item->bank_account_number) ? ($item->bank_name ?? 'Transfer') : 'Kas' }}
                    </span>
                </div>
                @if(!empty($item->bank_account_number))
                    <div class="flex justify-between sm:justify-start sm:gap-6">
                        <span class="text-slate-500 dark:text-slate-400 w-32 font-medium">{{ __('hrm.field_bank_account_number') }}</span>
                        <span class="mono font-semibold text-slate-800 dark:text-slate-200">{{ $item->bank_account_number }} ({{ $item->bank_account_holder ?? $empName }})</span>
                    </div>
                @endif
                <div class="flex justify-between sm:justify-start sm:gap-6">
                    <span class="text-slate-500 dark:text-slate-400 w-32 font-medium">{{ __('hrm.payroll_overtime_hours') }}</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ (float) $item->overtime_hours }}</span>
                </div>
                <div class="flex justify-between sm:justify-start sm:gap-6">
                    <span class="text-slate-500 dark:text-slate-400 w-32 font-medium">{{ __('hrm.payslip_token_label') }}</span>
                    <span class="mono text-[11px] text-slate-500 truncate max-w-[180px]" title="{{ $item->payslip_token }}">{{ substr($item->payslip_token, 0, 16) }}...</span>
                </div>
            </div>
        </div>

        <!-- Breakdown Grid: Earnings & Deductions -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-black/[0.08] dark:border-white/[0.1]">
            
            <!-- Earnings Panel -->
            <div class="space-y-3.5">
                <div class="flex items-center justify-between pb-2 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <span class="text-[12px] font-bold uppercase tracking-wider text-[#007AFF]">{{ __('hrm.payslip_gross_income_title') }}</span>
                    <span class="text-[11px] font-bold text-slate-500">Rp</span>
                </div>

                <div class="space-y-2.5 text-[12.5px]">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-700 dark:text-slate-300">{{ __('hrm.payslip_base_salary_label') }}</span>
                        <span class="mono font-semibold text-slate-900 dark:text-white tabular-nums">{{ number_format((float) $item->base_salary, 0, ',', '.') }}</span>
                    </div>

                    @if((float) $item->fixed_allowances > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-slate-700 dark:text-slate-300">{{ __('hrm.payslip_fixed_allowance_label') }}</span>
                            <span class="mono font-semibold text-slate-900 dark:text-white tabular-nums">{{ number_format((float) $item->fixed_allowances, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if((float) $item->variable_allowances > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-slate-700 dark:text-slate-300">{{ __('hrm.payslip_variable_allowance_label') }}</span>
                            <span class="mono font-semibold text-slate-900 dark:text-white tabular-nums">{{ number_format((float) $item->variable_allowances, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if((float) $item->overtime_pay > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-slate-700 dark:text-slate-300">{{ __('hrm.payslip_overtime_pay_label') }}</span>
                            <span class="mono font-semibold text-slate-900 dark:text-white tabular-nums">{{ number_format((float) $item->overtime_pay, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if((float) $item->commissions > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-slate-700 dark:text-slate-300">{{ __('hrm.payslip_commissions_label') }}</span>
                            <span class="mono font-semibold text-slate-900 dark:text-white tabular-nums">{{ number_format((float) $item->commissions, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if((float) $item->thr_bonus > 0)
                        <div class="flex justify-between items-center text-[#34C759] font-semibold">
                            <span>{{ __('hrm.payslip_thr_label') }}</span>
                            <span class="mono font-bold tabular-nums">{{ number_format((float) $item->thr_bonus, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-dashed border-black/[0.12] dark:border-white/[0.12] flex justify-between items-center">
                    <span class="text-[12.5px] font-bold text-slate-900 dark:text-white">{{ __('hrm.payslip_gross_total') }}</span>
                    <span class="mono text-[13.5px] font-extrabold text-slate-900 dark:text-white tabular-nums">{{ number_format((float) $item->gross_salary, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Deductions Panel -->
            <div class="space-y-3.5">
                <div class="flex items-center justify-between pb-2 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <span class="text-[12px] font-bold uppercase tracking-wider text-[#FF3B30]">{{ __('hrm.payslip_legal_deductions_title') }}</span>
                    <span class="text-[11px] font-bold text-slate-500">Rp</span>
                </div>

                <div class="space-y-2.5 text-[12.5px]">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-700 dark:text-slate-300">{{ __('hrm.payslip_pph21_label') }}</span>
                        <span class="mono font-semibold text-[#FF3B30] tabular-nums">
                            {{ (float) $item->tax_pph21 > 0 ? '-' . number_format((float) $item->tax_pph21, 0, ',', '.') : '0' }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-slate-700 dark:text-slate-300">{{ __('hrm.payslip_bpjs_tk_label') }}</span>
                        <span class="mono font-semibold text-[#FF3B30] tabular-nums">
                            {{ (float) $item->bpjs_tk_employee > 0 ? '-' . number_format((float) $item->bpjs_tk_employee, 0, ',', '.') : '0' }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-slate-700 dark:text-slate-300">{{ __('hrm.payslip_bpjs_kes_label') }}</span>
                        <span class="mono font-semibold text-[#FF3B30] tabular-nums">
                            {{ (float) $item->bpjs_kes_employee > 0 ? '-' . number_format((float) $item->bpjs_kes_employee, 0, ',', '.') : '0' }}
                        </span>
                    </div>

                    @if((float) $item->loan_deduction > 0)
                        <div class="flex justify-between items-center text-[#FF9500]">
                            <span class="font-medium">{{ __('hrm.payslip_loan_deduction_label') }}</span>
                            <span class="mono font-bold tabular-nums">-{{ number_format((float) $item->loan_deduction, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if((float) $item->other_deductions > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-slate-700 dark:text-slate-300">{{ __('hrm.payslip_other_deductions_label') }}</span>
                            <span class="mono font-semibold text-[#FF3B30] tabular-nums">-{{ number_format((float) $item->other_deductions, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-dashed border-black/[0.12] dark:border-white/[0.12] flex justify-between items-center">
                    <span class="text-[12.5px] font-bold text-[#FF3B30]">{{ __('hrm.payslip_deductions_total') }}</span>
                    <span class="mono text-[13.5px] font-extrabold text-[#FF3B30] tabular-nums">
                        {{ (float) $item->total_deductions > 0 ? '-' . number_format((float) $item->total_deductions, 0, ',', '.') : '0' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Net Take Home Pay Highlight Bento Card -->
        <div class="my-6 p-5 sm:p-6 rounded-[20px] bg-slate-50 dark:bg-slate-800/80 border border-black/[0.08] dark:border-white/[0.1] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block mb-0.5">{{ __('hrm.payslip_net_thp_title') }}</span>
                <div class="text-[15px] font-bold text-slate-800 dark:text-slate-200">{{ __('hrm.payslip_net_thp_label') }}</div>
            </div>
            <div class="sm:text-right">
                <div class="mono text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight tabular-nums">
                    Rp {{ number_format((float) $item->net_salary, 0, ',', '.') }}
                </div>
                <div class="text-[12px] text-[#34C759] font-bold mt-0.5">
                    {{ __('hrm.payslip_transferred_to_account') }}
                </div>
            </div>
        </div>

        <!-- Employer Contribution Section (Benefit Tambahan) -->
        <div class="py-4 px-5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.06] dark:border-white/[0.08] text-[12px] space-y-2 keep-together">
            <div class="flex items-center justify-between">
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ __('hrm.payslip_employer_contributions_title') }}</span>
                <span class="mono font-bold text-slate-900 dark:text-white tabular-nums">
                    Rp {{ number_format((float) ($item->bpjs_tk_employer + $item->bpjs_kes_employer), 0, ',', '.') }}
                </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11.5px] text-slate-600 dark:text-slate-400 pt-1.5 border-t border-black/[0.04] dark:border-white/[0.06]">
                <div>{{ __('hrm.payslip_employer_bpjs_tk') }} <strong class="mono text-slate-800 dark:text-slate-200 font-bold tabular-nums">Rp {{ number_format((float) $item->bpjs_tk_employer, 0, ',', '.') }}</strong></div>
                <div>{{ __('hrm.payslip_employer_bpjs_kes') }} <strong class="mono text-slate-800 dark:text-slate-200 font-bold tabular-nums">Rp {{ number_format((float) $item->bpjs_kes_employer, 0, ',', '.') }}</strong></div>
            </div>
        </div>

        @if(!empty($item->notes))
            <div class="mt-4 p-3.5 rounded-[14px] bg-[#FF9500]/[0.08] border border-[#FF9500]/25 text-[12px] text-slate-800 dark:text-slate-200">
                <strong class="text-[#FF9500]">{{ __('hrm.payroll_notes_label') }}:</strong> {{ $item->notes }}
            </div>
        @endif

        <!-- Footer / Legal & Signatures -->
        <div class="mt-8 pt-6 border-t border-black/[0.08] dark:border-white/[0.1] flex flex-col sm:flex-row justify-between items-end gap-6 keep-together">
            <div class="text-[11px] text-slate-500 dark:text-slate-400 space-y-1">
                <p>{{ __('hrm.payslip_legal_disclaimer') }}</p>
                <p>{{ __('hrm.payslip_authenticity_verify') }} <a href="{{ route('public.payslip', $item->payslip_token) }}" target="_blank" class="text-slate-600 dark:text-slate-300 hover:underline mono font-medium">{{ route('public.payslip', $item->payslip_token) }}</a></p>
                <p>{{ __('hrm.payslip_issued_at', ['time' => now()->translatedFormat('d F Y, H:i')]) }}</p>
            </div>

            <div class="text-right space-y-8 min-w-[160px]">
                <div class="text-[12px] font-bold text-slate-700 dark:text-slate-300">
                    {{ $business->name }}
                </div>
                <div class="border-b border-black/30 dark:border-white/30 w-36 ml-auto"></div>
                <div class="text-[12px] font-bold text-slate-900 dark:text-white">
                    {{ __('hrm.payslip_hr_authority') }}
                </div>
            </div>
        </div>

    </main>

    <!-- Hidden element containing raw text for clipboard copying -->
    <div id="rawSummaryText" class="hidden">{{ $whatsappMessage }}</div>

    <script>
        function downloadPayslipPDF() {
            const element = document.getElementById('payslipContent');
            const opt = {
                margin:       [10, 10, 10, 10],
                filename:     'Slip_Gaji_{{ Str::slug($empName) }}_{{ $item->payroll->period_year }}{{ str_pad((string)$item->payroll->period_month, 2, "0", STR_PAD_LEFT) }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }

        function copySlipSummary() {
            const text = document.getElementById('rawSummaryText').innerText;
            navigator.clipboard.writeText(text).then(() => {
                const btnText = document.getElementById('copyBtnText');
                const oldText = btnText.innerText;
                btnText.innerText = '{{ __('hrm.payslip_copied_toast') }}';
                setTimeout(() => {
                    btnText.innerText = oldText;
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
    </script>
</body>
</html>

