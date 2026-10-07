<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('billing.invoice_title', ['number' => $payment->order_number]) }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>

    <!-- html2pdf.js for Direct Client-Side PDF Download -->
    <script src="{{ asset('vendor/html2pdf.bundle.min.js') }}"></script>
    <script>
        if (typeof html2pdf === 'undefined') {
            document.write(
                '<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"><\/script>'
            );
        }
    </script>

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Plus Jakarta Sans", sans-serif;
            overflow-x: hidden;
        }

        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }

        .print-sheet {
            background-color: #ffffff !important;
            color: #0f172a !important;
            width: 100% !important;
            max-width: 820px !important;
            min-width: 0 !important;
            margin: 0 auto !important;
            box-sizing: border-box !important;
            padding: 16px 14px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border-radius: 16px;
        }

        @media (min-width: 640px) {
            .print-sheet {
                padding: 24px 22px !important;
            }
        }

        @media (min-width: 840px) {
            .print-sheet {
                padding: 36px 40px !important;
            }
        }

        .invoice-header-grid {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #000000;
            padding-bottom: 16px;
            margin-bottom: 18px;
            gap: 16px;
        }

        .invoice-header-left {
            flex: 1;
            min-width: 0;
        }

        .invoice-header-right {
            flex-shrink: 0;
            text-align: right;
            max-width: 48%;
            word-break: break-word;
        }

        .invoice-party-grid {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 11px;
            margin-bottom: 20px;
            gap: 16px;
        }

        .invoice-party-col {
            flex: 1;
            min-width: 0;
            line-height: 1.5;
            word-break: break-word;
        }

        .invoice-party-col.party-right {
            flex-shrink: 0;
            text-align: right;
            max-width: 48%;
        }

        .party-meta-row {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .invoice-table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 11px;
            table-layout: auto;
        }

        .invoice-table th,
        .invoice-table td {
            padding: 8px 8px;
            vertical-align: top;
            box-sizing: border-box;
        }

        @media (min-width: 768px) {
            .invoice-table th,
            .invoice-table td {
                padding: 8px 10px;
            }
        }

        .col-no { width: 32px; text-align: center; }
        .col-desc { text-align: left; }
        .col-durasi { width: 95px; text-align: center; }
        .col-qty { width: 36px; text-align: right; }
        .col-tarif { width: 95px; text-align: right; }
        .col-jumlah { width: 105px; text-align: right; }

        .invoice-bottom-grid {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 24px;
        }

        .invoice-bottom-left {
            flex: 1;
            min-width: 0;
        }

        .invoice-bottom-right {
            width: 42%;
            max-width: 42%;
            flex-shrink: 0;
        }

        .invoice-signatures-grid {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            text-align: center;
            font-size: 11px;
            gap: 20px;
        }

        .invoice-signature-col {
            width: 46%;
        }

        @media (max-width: 767px) {
            .invoice-bottom-grid {
                flex-direction: column !important;
                gap: 16px !important;
            }
            .invoice-bottom-left,
            .invoice-bottom-right {
                width: 100% !important;
                max-width: 100% !important;
            }
        }

        @media (max-width: 639px) {
            .invoice-header-grid {
                flex-direction: column !important;
                gap: 12px !important;
            }
            .invoice-header-left,
            .invoice-header-right {
                width: 100% !important;
                max-width: 100% !important;
                text-align: left !important;
            }
            .invoice-header-right {
                border-top: 1px dashed #e2e8f0;
                padding-top: 10px;
            }
            .invoice-party-grid {
                flex-direction: column !important;
                gap: 12px !important;
            }
            .invoice-party-col,
            .invoice-party-col.party-right {
                width: 100% !important;
                max-width: 100% !important;
                text-align: left !important;
            }
            .invoice-party-col.party-right {
                border-top: 1px dashed #f1f5f9;
                padding-top: 8px;
            }
            .party-meta-row {
                justify-content: space-between !important;
            }
            .invoice-table {
                font-size: 10px;
            }
            .invoice-table th,
            .invoice-table td {
                padding: 6px 4px;
            }
            .col-no { width: 24px; }
            .col-durasi { width: 72px; }
            .col-qty { width: 26px; }
            .col-tarif { width: 76px; }
            .col-jumlah { width: 80px; }
        }

        @media (max-width: 539px) {
            .invoice-signatures-grid {
                flex-direction: column !important;
                gap: 24px !important;
            }
            .invoice-signature-col {
                width: 100% !important;
            }
        }

        /* Saat render PDF via html2pdf atau Print, kunci kembali ke standar layout A4 portrait desktop */
        .print-sheet.pdf-render-mode {
            width: 794px !important;
            max-width: 794px !important;
            min-width: 794px !important;
            padding: 36px 40px !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        .print-sheet.pdf-render-mode .invoice-header-grid {
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            gap: 16px !important;
        }

        .print-sheet.pdf-render-mode .invoice-header-left {
            width: 58% !important;
            max-width: 58% !important;
            flex: none !important;
        }

        .print-sheet.pdf-render-mode .invoice-header-right {
            width: 40% !important;
            max-width: 40% !important;
            text-align: right !important;
            border-top: none !important;
            padding-top: 0 !important;
            flex: none !important;
        }

        .print-sheet.pdf-render-mode .invoice-party-grid {
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            gap: 16px !important;
        }

        .print-sheet.pdf-render-mode .invoice-party-col {
            width: 48% !important;
            flex: none !important;
        }

        .print-sheet.pdf-render-mode .invoice-party-col.party-right {
            width: 48% !important;
            text-align: right !important;
            border-top: none !important;
            padding-top: 0 !important;
            flex: none !important;
        }

        .print-sheet.pdf-render-mode .party-meta-row {
            justify-content: flex-end !important;
        }

        .print-sheet.pdf-render-mode .invoice-bottom-grid {
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            gap: 20px !important;
        }

        .print-sheet.pdf-render-mode .invoice-bottom-left {
            width: 54% !important;
            max-width: 54% !important;
            flex: none !important;
        }

        .print-sheet.pdf-render-mode .invoice-bottom-right {
            width: 42% !important;
            max-width: 42% !important;
            flex: none !important;
        }

        .print-sheet.pdf-render-mode .invoice-signatures-grid {
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
            text-align: center !important;
        }

        .print-sheet.pdf-render-mode .invoice-signature-col {
            width: 46% !important;
        }

        .print-sheet.pdf-render-mode .invoice-table {
            font-size: 11px !important;
        }

        .print-sheet.pdf-render-mode .invoice-table th,
        .print-sheet.pdf-render-mode .invoice-table td {
            padding: 8px 10px !important;
        }

        .print-sheet.pdf-render-mode .col-no { width: 36px !important; }
        .print-sheet.pdf-render-mode .col-durasi { width: 110px !important; }
        .print-sheet.pdf-render-mode .col-qty { width: 44px !important; }
        .print-sheet.pdf-render-mode .col-tarif { width: 110px !important; }
        .print-sheet.pdf-render-mode .col-jumlah { width: 120px !important; }

        @media print {
            html,
            body {
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
                border: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
            }

            .invoice-header-grid {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: flex-start !important;
                gap: 16px !important;
            }

            .invoice-header-left {
                width: 58% !important;
                max-width: 58% !important;
                flex: none !important;
            }

            .invoice-header-right {
                width: 40% !important;
                max-width: 40% !important;
                text-align: right !important;
                border-top: none !important;
                padding-top: 0 !important;
                flex: none !important;
            }

            .invoice-party-grid {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: flex-start !important;
                gap: 16px !important;
            }

            .invoice-party-col {
                width: 48% !important;
                flex: none !important;
            }

            .invoice-party-col.party-right {
                width: 48% !important;
                text-align: right !important;
                border-top: none !important;
                padding-top: 0 !important;
                flex: none !important;
            }

            .party-meta-row {
                justify-content: flex-end !important;
            }

            .invoice-bottom-grid {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: flex-start !important;
                gap: 20px !important;
            }

            .invoice-bottom-left {
                width: 54% !important;
                max-width: 54% !important;
                flex: none !important;
            }

            .invoice-bottom-right {
                width: 42% !important;
                max-width: 42% !important;
                flex: none !important;
            }

            .invoice-signatures-grid {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                text-align: center !important;
            }

            .invoice-signature-col {
                width: 46% !important;
            }

            .invoice-table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 11px !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
            }

            .invoice-table th,
            .invoice-table td {
                padding: 8px 10px !important;
            }

            .col-no { width: 36px !important; }
            .col-durasi { width: 110px !important; }
            .col-qty { width: 44px !important; }
            .col-tarif { width: 110px !important; }
            .col-jumlah { width: 120px !important; }

            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                page-break-after: auto !important;
                break-after: auto !important;
            }

            td,
            th {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .keep-together {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>

<body
    class="bg-[#F2F2F7] dark:bg-[#1E1E1E] text-black dark:text-white py-3 sm:py-8 px-2 sm:px-6 transition-colors overflow-x-hidden min-h-screen">

    @php
        $badge = $payment->getStatusBadge();
        $isApproved = $payment->isApproved();
        $uniqueStr = str_pad((string) $payment->unique_code, 3, '0', STR_PAD_LEFT);
        $durationText = $payment->package_duration_days
            ? __('billing.active_duration_days', ['days' => $payment->package_duration_days])
            : ($payment->cycle === 'annual'
                ? '365 ' . (app()->getLocale() === 'id' ? 'Hari (1 Tahun)' : 'Days (1 Year)')
                : '30 ' . (app()->getLocale() === 'id' ? 'Hari (1 Bulan)' : 'Days (1 Month)'));
        $methodDetails = $payment->getPaymentMethodDetails();

        $createdAtWib = $payment->created_at?->copy()->timezone('Asia/Jakarta');
        $approvedAtWib = $payment->approved_at?->copy()->timezone('Asia/Jakarta');
        $dueDateWib = $createdAtWib?->copy()->addDay();
        $proofUploadedAtWib = $payment->proof_uploaded_at?->copy()->timezone('Asia/Jakarta');

        $siteLogoSetting = \App\Models\SystemSetting::get('site_logo_light')
            ?? \App\Models\SystemSetting::get('site_logo_dark');

        $coocaLogoBase64 = null;
        if ($siteLogoSetting && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogoSetting)) {
            try {
                $mime = \Illuminate\Support\Facades\Storage::disk('public')->mimeType($siteLogoSetting) ?? 'image/png';
                $raw = \Illuminate\Support\Facades\Storage::disk('public')->get($siteLogoSetting);
                $coocaLogoBase64 = 'data:' . $mime . ';base64,' . base64_encode($raw);
            } catch (\Throwable $e) {}
        }

        if (! $coocaLogoBase64) {
            $defaultLogoPath = public_path('assets/image/cooca-logo-landscape.png');
            if (file_exists($defaultLogoPath)) {
                $coocaLogoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($defaultLogoPath));
            } else {
                $coocaLogoBase64 = asset('assets/image/cooca-logo-landscape.png');
            }
        }
    @endphp

    <!-- Floating Top Action Bar -->
    <header
        class="no-print max-w-4xl mx-auto mb-5 sm:mb-6 bg-white dark:bg-[#1C1C1E] text-black dark:text-white p-3.5 sm:p-4 rounded-[18px] sm:rounded-[20px] shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-black/[0.06] dark:border-white/[0.08] space-y-3 backdrop-blur-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 shrink-0">
                <span
                    class="w-2.5 h-2.5 rounded-full {{ $isApproved ? 'bg-[#34C759]' : ($payment->isRejected() ? 'bg-[#FF3B30]' : 'bg-[#FF9500]') }}"
                    aria-hidden="true"></span>
                <span class="text-xs font-semibold tracking-tight text-gray-800 dark:text-gray-200">{{ __('billing.invoice_official_title') }}</span>
            </div>

            <div class="grid grid-cols-3 sm:flex sm:items-center gap-2 sm:gap-2.5 w-full sm:w-auto shrink-0">
                <a href="{{ route('billing.payment.show', $payment) }}"
                    class="h-9 sm:h-10 px-3 sm:px-4 rounded-[10px] sm:rounded-[12px] bg-black/[0.04] hover:bg-black/[0.08] dark:bg-white/[0.06] dark:hover:bg-white/[0.1] text-xs font-semibold active:scale-[0.98] transition-all text-gray-700 dark:text-gray-300 border border-black/[0.06] dark:border-white/[0.08] cursor-pointer flex items-center justify-center gap-1.5 whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    <span>{{ __('billing.back') }}</span>
                </a>
                <button type="button" onclick="window.print()"
                    class="h-9 sm:h-10 px-3 sm:px-4 rounded-[10px] sm:rounded-[12px] bg-black/[0.04] hover:bg-black/[0.08] dark:bg-white/[0.06] dark:hover:bg-white/[0.1] text-gray-700 dark:text-gray-300 text-xs font-semibold flex items-center justify-center gap-1.5 active:scale-[0.98] transition-all border border-black/[0.06] dark:border-white/[0.08] cursor-pointer whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-500 dark:text-gray-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6" />
                        <rect x="6" y="14" width="12" height="8" rx="1" />
                    </svg>
                    <span>{{ __('billing.action_print_printer') }}</span>
                </button>
                <button id="btnDownloadPdf" type="button" onclick="downloadPDF()"
                    class="h-9 sm:h-10 px-3 sm:px-4 rounded-[10px] sm:rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold shadow-xs flex items-center justify-center gap-1.5 active:scale-[0.98] transition-all cursor-pointer whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    <span>{{ __('billing.action_download_pdf') }}</span>
                </button>
            </div>
        </div>

        <div
            class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center gap-2 text-[11px] text-gray-500 dark:text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
            <span class="truncate sm:whitespace-normal">{!! __('billing.invoice_download_pdf_hint') !!}</span>
        </div>
    </header>

    <!-- Outer responsive wrapper -->
    <div class="w-full max-w-4xl mx-auto pb-6">
        <!-- Paper Sheet Container (Strict A4 Layout on Paper & Screen, Zero Overlap) -->
        <main class="print-sheet bg-white text-black"
            aria-label="{{ __('billing.invoice_official_aria_label') }}">

            <!-- Header / Kop Surat Resmi Cooca ID -->
            <div class="invoice-header-grid">
                <div class="invoice-header-left">
                    <div style="margin-bottom: 8px;">
                        <img src="{{ $coocaLogoBase64 }}" alt="COOCA" style="height: 38px; width: auto; max-width: 180px; object-fit: contain; display: block;">
                    </div>
                    <div style="font-size: 11px; color: #475569; line-height: 1.45;">
                        <p style="margin: 0; font-weight: 500;">{{ __('billing.company_tagline') }}</p>
                        <p style="margin: 2px 0 0 0;">Email: billing@cooca.id | CS: +62 852-8786-4176</p>
                    </div>
                </div>

                <div class="invoice-header-right">
                    <div style="font-size: 17px; font-weight: 900; letter-spacing: 0.05em; color: #000000; text-transform: uppercase;">
                        {{ __('billing.invoice_header_title') }}
                    </div>
                    <div style="font-size: 13px; font-family: monospace; font-weight: 800; color: #000000; margin-top: 2px;">
                        {{ $payment->order_number }}
                    </div>
                    <div style="font-size: 10.5px; color: #64748b; font-family: monospace; margin-top: 2px; word-break: break-all;">
                        {{ __('billing.transaction_id') }} #{{ $payment->id }}
                    </div>
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px; color: #475569;">
                        {{ __('billing.status_label') }} <span style="font-weight: 900; color: {{ $isApproved ? '#16a34a' : ($payment->isRejected() ? '#dc2626' : '#d97706') }};">
                            {{ strtoupper(str_replace('_', ' ', $payment->status)) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Detail Pihak Tertagih & Tanggal Tagihan -->
            <div class="invoice-party-grid">
                <div class="invoice-party-col">
                    <div style="font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-size: 10px; margin-bottom: 2px;">
                        {{ __('billing.bill_to') }}
                    </div>
                    <div style="font-size: 13px; font-weight: 800; color: #000000; font-family: monospace;">{{ $business->name }}</div>
                    <div style="font-weight: 700; color: #1e293b;">{{ $payment->user?->name ?? __('billing.business_owner') }}</div>
                    <div style="color: #475569; font-family: monospace; word-break: break-all;">Email: {{ $payment->user?->email ?? '-' }}</div>
                    <div style="color: #64748b; font-family: monospace; word-break: break-all;">{{ __('billing.workspace_id') }} {{ $business->id }}</div>
                </div>

                <div class="invoice-party-col party-right" style="line-height: 1.5;">
                    <div class="party-meta-row">
                        <span style="color: #64748b;">{{ __('billing.invoice_date') }}</span>
                        <span style="font-family: monospace; font-weight: 700; color: #000000;">{{ $createdAtWib?->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="party-meta-row" style="margin-top: 2px;">
                        <span style="color: #64748b;">{{ __('billing.due_date') }}</span>
                        <span style="font-family: monospace; font-weight: 700; color: #000000;">{{ $dueDateWib?->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="party-meta-row" style="margin-top: 2px;">
                        <span style="color: #64748b;">{{ __('billing.payment_method_label') }}</span>
                        <span style="font-weight: 700; color: #1e293b;">{{ $methodDetails['name'] ?? strtoupper($payment->payment_method) }}</span>
                    </div>
                    @if ($approvedAtWib)
                        <div class="party-meta-row" style="margin-top: 2px;">
                            <span style="color: #64748b;">{{ __('billing.payment_time') }}</span>
                            <span style="font-family: monospace; font-weight: 800; color: #16a34a;">
                                {{ $approvedAtWib->translatedFormat('d F Y, H:i') }} WIB
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tabel Rincian Paket Layanan SaaS -->
            <div style="margin-bottom: 20px;">
                <table class="invoice-table">
                    <thead>
                        <tr style="border-top: 2px solid #000000; border-bottom: 2px solid #000000; background-color: #f8fafc; color: #000000;">
                            <th class="col-no">{{ __('billing.table_no') }}</th>
                            <th class="col-desc">{{ __('billing.table_description') }}</th>
                            <th class="col-durasi">{{ __('billing.table_duration_unit') }}</th>
                            <th class="col-qty">{{ __('billing.table_qty') }}</th>
                            <th class="col-tarif">{{ __('billing.table_unit_price') }}</th>
                            <th class="col-jumlah">{{ __('billing.table_amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid #e2e8f0; vertical-align: top;">
                            <td class="col-no" style="font-family: monospace; color: #64748b;">1</td>
                            <td class="col-desc">
                                <div style="font-weight: 800; color: #000000; line-height: 1.25;">
                                    {{ $payment->package_name ?? ($payment->cycle === 'annual' ? __('billing.plan_core_annual') : ($payment->cycle === 'monthly' ? __('billing.plan_core_monthly') : __('billing.topup_business_quota'))) }}
                                </div>
                            </td>
                            <td class="col-durasi" style="font-family: monospace; color: #334155; text-transform: uppercase;">
                                {{ $durationText }}
                            </td>
                            <td class="col-qty" style="font-family: monospace; font-weight: 600; color: #000000;">
                                1
                            </td>
                            <td class="col-tarif" style="font-family: monospace; color: #334155; white-space: nowrap;">
                                Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                            </td>
                            <td class="col-jumlah" style="font-family: monospace; font-weight: 800; color: #000000; white-space: nowrap;">
                                Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                            </td>
                        </tr>

                        @if ($payment->hasDiscount())
                            <tr style="border-bottom: 1px solid #e2e8f0; vertical-align: top;">
                                <td class="col-no" style="font-family: monospace; color: #64748b;">2</td>
                                <td class="col-desc">
                                    <div style="font-weight: 800; color: #16a34a; line-height: 1.25;">Voucher Promo Diskon ({{ $payment->promo_code }})</div>
                                    <div style="font-size: 10px; color: #64748b; font-family: monospace; margin-top: 2px;">KODE: {{ $payment->promo_code }}</div>
                                    <div style="font-size: 10px; color: #475569; margin-top: 4px; line-height: 1.4;">
                                        Insentif subsidi promo langganan resmi Cooca.
                                    </div>
                                </td>
                                <td class="col-durasi" style="font-family: monospace; color: #334155; text-transform: uppercase;">
                                    Voucher
                                </td>
                                <td class="col-qty" style="font-family: monospace; font-weight: 600; color: #000000;">
                                    1
                                </td>
                                <td class="col-tarif" style="font-family: monospace; font-weight: 600; color: #16a34a; white-space: nowrap;">
                                    -Rp {{ number_format((float) $payment->discount_amount, 0, ',', '.') }}
                                </td>
                                <td class="col-jumlah" style="font-family: monospace; font-weight: 800; color: #16a34a; white-space: nowrap;">
                                    -Rp {{ number_format((float) $payment->discount_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif

                        @if ($payment->unique_code > 0)
                            <tr style="border-bottom: 1px solid #e2e8f0; vertical-align: top;">
                                <td class="col-no" style="font-family: monospace; color: #64748b;">{{ $payment->hasDiscount() ? '3' : '2' }}</td>
                                <td class="col-desc">
                                    <div style="font-weight: 800; color: #000000; line-height: 1.25;">{{ __('billing.unique_code_desc_title') }}</div>
                                    <div style="font-size: 10px; color: #64748b; font-family: monospace; margin-top: 2px;">{{ __('billing.code_label') }} VERIF-AUTO</div>
                                    <div style="font-size: 10px; color: #475569; margin-top: 4px; line-height: 1.4;">
                                        {{ __('billing.unique_code_explanation') }}
                                    </div>
                                </td>
                                <td class="col-durasi" style="font-family: monospace; color: #334155; text-transform: uppercase;">
                                    Trans
                                </td>
                                <td class="col-qty" style="font-family: monospace; font-weight: 600; color: #000000;">
                                    1
                                </td>
                                <td class="col-tarif" style="font-family: monospace; font-weight: 600; color: #d97706; white-space: nowrap;">
                                    +Rp {{ $uniqueStr }}
                                </td>
                                <td class="col-jumlah" style="font-family: monospace; font-weight: 800; color: #d97706; white-space: nowrap;">
                                    +Rp {{ $uniqueStr }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Footer: Informasi Pembayaran Sistem & Kalkulasi Finansial (Zero Overlap) -->
            <div class="keep-together" style="margin-top: 8px;">
                <div class="invoice-bottom-grid" style="border-top: 2px solid #000000; padding-top: 14px;">

                    <!-- Left: Informasi Transaksi & Pembayaran Sistem -->
                    <div class="invoice-bottom-left">
                        <div style="padding: 12px 14px; background-color: #f8fafc; border-radius: 0 10px 10px 0; border: 1px solid #e2e8f0; border-left: 3px solid #0f172a;">
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 8px;">
                                <div style="font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #0f172a;">
                                    Informasi Transaksi &amp; Pembayaran
                                </div>
                            </div>

                            <table style="width: 100%; border-collapse: collapse; font-size: 11px; line-height: 1.55;">
                                <tbody>
                                    <tr>
                                        <td style="width: 105px; color: #64748b; padding: 2px 0; font-weight: 500;">Metode Bayar</td>
                                        <td style="width: 10px; color: #94a3b8; padding: 2px 0;">:</td>
                                        <td style="color: #0f172a; padding: 2px 0; font-weight: 700;">
                                            {{ $methodDetails['name'] ?? strtoupper($payment->payment_method) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #64748b; padding: 2px 0; font-weight: 500;">No. Referensi</td>
                                        <td style="color: #94a3b8; padding: 2px 0;">:</td>
                                        <td style="color: #0f172a; padding: 2px 0; font-family: monospace; font-weight: 700; word-break: break-all;">
                                            {{ $payment->gateway_reference ?: ($payment->tripay_reference ?: $payment->order_number) }}
                                        </td>
                                    </tr>
                                    @if ($payment->gateway_pay_code)
                                        <tr>
                                            <td style="color: #64748b; padding: 2px 0; font-weight: 500;">Kode / No. VA</td>
                                            <td style="color: #94a3b8; padding: 2px 0;">:</td>
                                            <td style="color: #0f172a; padding: 2px 0; font-family: monospace; font-weight: 700; letter-spacing: 0.05em; word-break: break-all;">
                                                {{ $payment->gateway_pay_code }}
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td style="color: #64748b; padding: 2px 0; font-weight: 500;">Status Bayar</td>
                                        <td style="color: #94a3b8; padding: 2px 0;">:</td>
                                        <td style="padding: 2px 0; font-weight: 800; color: {{ $isApproved ? '#16a34a' : ($payment->isRejected() ? '#dc2626' : '#d97706') }};">
                                            @if ($isApproved)
                                                LUNAS (Terverifikasi Otomatis)
                                            @elseif ($payment->status === 'cancelled')
                                                DIBATALKAN / KEDALUWARSA
                                            @elseif ($payment->status === 'rejected')
                                                DITOLAK
                                            @else
                                                MENUNGGU PEMBAYARAN
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #64748b; padding: 2px 0; font-weight: 500;">Waktu Transaksi</td>
                                        <td style="color: #94a3b8; padding: 2px 0;">:</td>
                                        <td style="color: #334155; padding: 2px 0; font-family: monospace;">
                                            {{ $approvedAtWib ? $approvedAtWib->translatedFormat('d F Y, H:i') . ' WIB' : ($createdAtWib ? $createdAtWib->translatedFormat('d F Y, H:i') . ' WIB' : '-') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            @if ($payment->isManual() && $payment->sender_account_name)
                                <div style="margin-top: 8px; padding-top: 6px; border-top: 1px solid #e2e8f0; font-size: 11px; color: #475569;">
                                    <div><span style="color: #64748b;">Pengirim:</span> <strong>{{ $payment->sender_account_name }} ({{ $payment->sender_bank ?? '-' }})</strong></div>
                                    @if ($proofUploadedAtWib)
                                        <div style="font-size: 10px; color: #64748b; font-family: monospace; margin-top: 2px;">
                                            Struk dikirim: {{ $proofUploadedAtWib->translatedFormat('d F Y, H:i') }} WIB
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if ($payment->notes)
                            <div style="margin-top: 10px;">
                                <div style="font-weight: 700; color: #0f172a; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
                                    {{ __('billing.additional_notes') }}
                                </div>
                                <div style="color: #475569; white-space: pre-line; background-color: #f8fafc; padding: 8px 10px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 11px; line-height: 1.4;">
                                    {{ $payment->notes }}
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Right: Financial Ledger Calculation -->
                    <div class="invoice-bottom-right">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px; line-height: 1.6;">
                            <tbody>
                                <tr>
                                    <td style="color: #475569; padding: 3px 0;">{{ __('billing.subtotal_bill') }}</td>
                                    <td style="text-align: right; font-family: monospace; font-weight: 600; color: #0f172a; padding: 3px 0;">
                                        Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @if ($payment->hasDiscount())
                                    <tr>
                                        <td style="color: #16a34a; font-weight: 500; padding: 3px 0;">Diskon Promo ({{ $payment->promo_code }})</td>
                                        <td style="text-align: right; font-family: monospace; font-weight: 600; color: #16a34a; padding: 3px 0;">
                                            -Rp {{ number_format((float) $payment->discount_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif
                                @if ($payment->unique_code > 0)
                                    <tr>
                                        <td style="color: #d97706; padding: 3px 0;">{{ __('billing.unique_code_verification') }}</td>
                                        <td style="text-align: right; font-family: monospace; font-weight: 600; color: #d97706; padding: 3px 0;">
                                            +Rp {{ $uniqueStr }}
                                        </td>
                                    </tr>
                                @endif
                                <tr style="border-top: 2px solid #000000;">
                                    <td style="font-weight: 800; color: #000000; padding: 6px 0; font-size: 13px;">{{ __('billing.total_bill') }}</td>
                                    <td style="text-align: right; font-family: monospace; font-weight: 800; color: #000000; padding: 6px 0; font-size: 14px;">
                                        Rp {{ number_format((float) $payment->total_payable, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color: #475569; padding: 3px 0;">{{ __('billing.paid_amount') }}</td>
                                    <td style="text-align: right; font-family: monospace; font-weight: 600; padding: 3px 0; color: {{ $isApproved ? '#16a34a' : '#64748b' }};">
                                        Rp {{ $isApproved ? number_format((float) $payment->total_payable, 0, ',', '.') : '0' }}
                                    </td>
                                </tr>
                                <tr style="border-top: 1px solid #cbd5e1;">
                                    <td style="font-weight: 700; padding: 6px 0; color: {{ $isApproved ? '#16a34a' : '#d97706' }};">
                                        {{ __('billing.remaining_bill') }}
                                    </td>
                                    <td style="text-align: right; font-family: monospace; font-weight: 800; font-size: 14px; padding: 6px 0; color: {{ $isApproved ? '#16a34a' : '#d97706' }};">
                                        {{ $isApproved ? __('billing.paid_full') : 'Rp ' . number_format((float) $payment->total_payable, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Official Stamp Seal if Approved -->
                        @if ($isApproved)
                            <div style="margin-top: 12px; text-align: center;">
                                <div style="display: inline-block; padding: 5px 14px; border: 2px solid #16a34a; color: #16a34a; font-weight: 800; font-size: 11px; font-family: monospace; text-transform: uppercase; letter-spacing: 0.1em; border-radius: 8px; transform: rotate(-2deg);">
                                    ✓ {{ __('billing.stamp_paid_verified') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Digital Signatures & Authorization Blocks -->
                <div class="invoice-signatures-grid">
                    <div class="invoice-signature-col">
                        <div style="color: #64748b; margin-bottom: 4px;">{{ __('billing.accepted_and_approved_by') }}</div>
                        <div style="font-weight: 700; color: #000000; font-family: monospace; font-size: 12px;">
                            {{ $business->name }}
                        </div>
                        <div style="margin-top: 48px; border-top: 1px solid #94a3b8; width: 180px; max-width: 90%; margin-left: auto; margin-right: auto; padding-top: 4px; font-weight: 600; color: #334155;">
                            ( {{ $payment->user?->name ?? __('billing.business_owner') }} )
                        </div>
                    </div>

                    <div class="invoice-signature-col">
                        <div style="color: #64748b; margin-bottom: 4px;">{{ __('billing.cooca_company_name') }}</div>
                        <div style="font-weight: 700; color: #000000; font-size: 12px;">{{ __('billing.billing_finance_department') }}</div>
                        <div style="margin-top: 48px; border-top: 1px solid #94a3b8; width: 200px; max-width: 90%; margin-left: auto; margin-right: auto; padding-top: 4px; font-weight: 600; color: #334155;">
                            {{ __('billing.cooca_digital_auth_system') }}
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- Client-Side PDF Generation Script with html2pdf.js -->
    <script>
        function downloadPDF() {
            const btn = document.getElementById('btnDownloadPdf');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-wait');
            btn.innerHTML = `
                <svg class="animate-spin w-4 h-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ __('billing.generating_pdf') }}</span>
            `;

            const element = document.querySelector('.print-sheet');
            const filename = 'Faktur-Cooca-{{ $payment->order_number }}.pdf';

            // Kunci layout dokumen ke format A4 portrait presisi sebelum capture
            element.classList.add('pdf-render-mode');

            // Options calibrated for exact A4 output with zero overlap
            const opt = {
                margin: [8, 8, 8, 8],
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    scrollY: 0,
                    scrollX: 0,
                    backgroundColor: '#ffffff'
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                },
                pagebreak: {
                    mode: ['avoid-all', 'css', 'legacy']
                }
            };

            if (typeof html2pdf !== 'undefined') {
                html2pdf().set(opt).from(element).save().then(() => {
                    element.classList.remove('pdf-render-mode');
                    btn.disabled = false;
                    btn.classList.remove('opacity-75', 'cursor-wait');
                    btn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>{{ __('billing.download_success') }}</span>
                    `;
                    setTimeout(() => {
                        btn.innerHTML = originalHtml;
                    }, 3500);
                }).catch(err => {
                    console.error('html2pdf error:', err);
                    element.classList.remove('pdf-render-mode');
                    btn.disabled = false;
                    btn.classList.remove('opacity-75', 'cursor-wait');
                    btn.innerHTML = originalHtml;
                    window.print();
                });
            } else {
                element.classList.remove('pdf-render-mode');
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-wait');
                btn.innerHTML = originalHtml;
                window.print();
            }
        }

        window.addEventListener('load', () => {
            const params = new URLSearchParams(window.location.search);
            if (params.get('download') === '1' || params.get('pdf') === '1') {
                setTimeout(() => {
                    downloadPDF();
                }, 500);
            } else if (params.get('print') === '1') {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        });
    </script>
</body>

</html>
