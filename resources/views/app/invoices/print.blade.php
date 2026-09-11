<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur {{ $invoice->invoice_number }} — {{ $business->name }}</title>

    <!-- Google Fonts / SF Pro Fallback -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- html2pdf.js for Direct Client-Side PDF Download -->
    <script src="{{ asset('vendor/html2pdf.bundle.min.js') }}"></script>
    <script>
        if (typeof html2pdf === 'undefined') {
            document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"><\/script>');
        }
    </script>

    <style>
        body {
            font-family: -apple-system, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
            background-color: #F2F2F7;
            color: #1C1C1E;
        }

        @page {
            size: A4 portrait;
            margin: 15mm 16mm 15mm 16mm;
        }

        .print-sheet {
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
            background-color: #ffffff !important;
        }

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
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
            }

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

            thead {
                display: table-header-group !important;
            }

            tfoot {
                display: table-footer-group !important;
            }

            .keep-together {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>

<body class="py-6 px-4 sm:px-6 bg-[#F2F2F7]">

    <!-- Floating Top Print Action Bar (Apple HIG macOS Frosted Toolbar) -->
    <div class="no-print max-w-4xl mx-auto mb-6 backdrop-blur-md bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/10 dark:border-white/10 p-4 rounded-[14px] shadow-[0_8px_24px_rgba(0,0,0,0.06)] space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                <span class="text-[13px] font-semibold text-black dark:text-white tracking-tight">Dokumen Faktur Penjualan (A4 Ready)</span>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.history.back()"
                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all">
                    Kembali
                </button>
                <button onclick="window.print()"
                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] flex items-center gap-1.5 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-black/60 dark:text-white/60" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6" />
                        <rect x="6" y="14" width="12" height="8" rx="1" />
                    </svg>
                    <span>Cetak Printer</span>
                </button>
                <button id="btnDownloadPdf" onclick="downloadPDF()"
                    class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold shadow-[0_1px_2px_rgba(0,122,255,0.25)] flex items-center gap-1.5 active:scale-[0.97] transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    <span>Download PDF Langsung</span>
                </button>
            </div>
        </div>
        <div class="pt-2 border-t border-black/5 dark:border-white/10 flex items-center gap-2 text-[12px] text-black/50 dark:text-white/50">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#007AFF] shrink-0" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
            <span>Klik <strong>"Download PDF Langsung"</strong> untuk mengunduh dokumen secara instan tanpa dialog print peramban dan bebas header URL/tanggal.</span>
        </div>
    </div>

    <!-- Paper Sheet (Standard A4 Flat Container) -->
    <div
        class="print-sheet max-w-4xl mx-auto bg-white p-8 sm:p-12 border-0 shadow-none rounded-none text-slate-900 space-y-6">

        <!-- Header / Kop Surat -->
        <div class="flex justify-between items-start border-b-2 border-slate-900 pb-5">
            <div class="space-y-2 max-w-md">
                @if ($business->logo_url)
                    <div class="mb-1">
                        <img src="{{ $business->logo_url }}" alt="{{ $business->name }}"
                            class="max-h-12 max-w-[140px] object-contain">
                    </div>
                @endif
                <div class="space-y-0.5">
                    <h1 class="text-base font-bold text-slate-950 uppercase tracking-wide leading-tight">
                        {{ $business->name }}</h1>
                    <p class="text-xs text-slate-600">
                        {{ $business->address ?? 'Alamat Kantor Operasional' }}</p>
                    <p class="text-xs text-slate-600">
                        @if ($business->email)
                            Email: {{ $business->email }}
                        @endif
                        @if ($business->phone)
                            | Telp: {{ $business->phone }}
                        @endif
                    </p>
                    @if ($business->tax_identification_number)
                        <p class="text-xs text-slate-600 tabular-nums">NPWP: {{ $business->tax_identification_number }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="text-right space-y-0.5">
                <div class="text-lg font-bold tracking-wider text-slate-950 uppercase">FAKTUR PENJUALAN</div>
                <div class="text-sm tabular-nums font-bold text-slate-800">{{ $invoice->invoice_number }}</div>
                @if ($invoice->purchaseOrder)
                    <div class="text-xs text-slate-500 tabular-nums">No. PO: {{ $invoice->purchaseOrder->po_number }}
                    </div>
                @endif
                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-600 mt-1">
                    STATUS: <span class="font-bold {{ $invoice->status === 'paid' ? 'text-emerald-700' : ($invoice->status === 'overdue' ? 'text-rose-700' : 'text-slate-900') }}">{{ strtoupper(str_replace('_', ' ', $invoice->status)) }}</span>
                </div>
            </div>
        </div>

        <!-- Detail Tagihan & Pihak Pembeli -->
        <div class="grid grid-cols-2 gap-8 text-xs">
            <div class="space-y-1.5">
                <div class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Ditagihkan Kepada:</div>
                <div class="text-sm font-bold text-slate-950">{{ $invoice->customer?->name ?? 'Pelanggan' }}</div>
                @if ($invoice->customer?->company_name)
                    <div class="font-semibold text-slate-800">{{ $invoice->customer->company_name }}</div>
                @endif
                <div class="text-slate-600 whitespace-pre-line">{{ $invoice->customer?->billing_address ?? '-' }}
                </div>
                <div class="text-slate-600 tabular-nums">Kontak:
                    {{ $invoice->customer?->phone ?? ($invoice->customer?->email ?? '-') }}</div>
                @if ($invoice->customer?->tax_identification_number)
                    <div class="text-slate-600 tabular-nums">NPWP: {{ $invoice->customer->tax_identification_number }}
                    </div>
                @endif
            </div>

            <div class="space-y-2 text-right">
                <div class="flex justify-end gap-4">
                    <span class="text-slate-500">Tanggal Faktur:</span>
                    <span
                        class="tabular-nums font-bold text-slate-900">{{ $invoice->invoice_date?->translatedFormat('d F Y') }}</span>
                </div>
                <div class="flex justify-end gap-4">
                    <span class="text-slate-500">Tanggal Jatuh Tempo:</span>
                    <span
                        class="tabular-nums font-bold text-slate-900">{{ $invoice->due_date?->translatedFormat('d F Y') }}</span>
                </div>
                <div class="flex justify-end gap-4">
                    <span class="text-slate-500">Termin Pembayaran:</span>
                    <span class="font-semibold text-slate-800">{{ $invoice->payment_terms ?? 'Net 30' }}</span>
                </div>
            </div>
        </div>

        <!-- Tabel Rincian Barang -->
        <div class="overflow-visible">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-y-2 border-slate-900 bg-slate-100 text-slate-900">
                        <th class="py-2 px-2.5 font-bold w-10 text-center">No</th>
                        <th class="py-2 px-2.5 font-bold">Deskripsi Produk / Jasa</th>
                        <th class="py-2 px-2.5 font-bold text-center w-16">Satuan</th>
                        <th class="py-2 px-2.5 font-bold text-right w-14">Qty</th>
                        <th class="py-2 px-2.5 font-bold text-right w-28 whitespace-nowrap">Harga Satuan</th>
                        <th class="py-2 px-2.5 font-bold text-right w-32 whitespace-nowrap">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($invoice->items as $idx => $item)
                        <tr class="align-top">
                            <td class="py-2 px-2.5 text-center tabular-nums text-slate-600">{{ $idx + 1 }}</td>
                            <td class="py-2 px-2.5">
                                <div class="font-bold text-slate-950 leading-tight">{{ $item->item_name }}</div>
                                @if ($item->sku)
                                    <div class="text-[10px] text-slate-500 tabular-nums mt-0.5">Kode: {{ $item->sku }}</div>
                                @endif
                                @if ($item->description)
                                    <div class="text-[10px] text-slate-600 mt-0.5 leading-snug">{{ $item->description }}</div>
                                @endif
                            </td>
                            <td class="py-2 px-2.5 text-center tabular-nums text-slate-700 uppercase">
                                {{ $item->unit?->code ?? ($item->unit?->symbol ?? 'pcs') }}
                            </td>
                            <td class="py-2 px-2.5 text-right tabular-nums font-semibold text-slate-900">
                                {{ (float) $item->quantity == (int) $item->quantity ? number_format((float) $item->quantity, 0, ',', '.') : rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }}
                            </td>
                            <td class="py-2 px-2.5 text-right tabular-nums text-slate-800 whitespace-nowrap">
                                {{ $business->currency_symbol }} {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-2.5 text-right tabular-nums font-bold text-slate-950 whitespace-nowrap">
                                {{ $business->currency_symbol }} {{ number_format((float) $item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer: Kalkulasi Finansial & Tanda Tangan (Keep Together) -->
        <div class="keep-together space-y-6 pt-2">
            <!-- Kalkulasi Finansial -->
            <div class="flex justify-between items-start border-t-2 border-slate-900 pt-2 gap-6 text-xs">
                <div class="space-y-4 w-1/2">
                    @if ($invoice->notes)
                        <div>
                            <div class="font-bold text-slate-900 mb-0.5">Catatan Pembayaran:</div>
                            <div class="text-slate-600 whitespace-pre-line">{{ $invoice->notes }}</div>
                        </div>
                    @endif

                    <!-- Instruksi Transfer Bank Resmi -->
                    <div class="p-3 border-l-2 border-slate-400 bg-slate-50/70 space-y-1.5 text-slate-700">
                        <div class="font-bold text-slate-950 text-[11px] uppercase tracking-wider">Instruksi Pembayaran Transfer:</div>
                        <div class="text-xs text-slate-600">Silakan lakukan transfer ke rekening resmi berikut:</div>
                        <div class="pt-1 space-y-1 text-xs">
                            <div class="flex items-baseline gap-2">
                                <span class="w-24 text-slate-500 shrink-0 font-medium">Bank</span>
                                <span class="text-slate-400">:</span>
                                <span class="font-bold text-slate-900">{{ str_starts_with(strtolower($business->bank_name ?? ''), 'bank') ? $business->bank_name : 'Bank ' . ($business->bank_name ?? 'BCA') }}</span>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span class="w-24 text-slate-500 shrink-0 font-medium">No. Rekening</span>
                                <span class="text-slate-400">:</span>
                                <span class="tabular-nums font-bold text-slate-950 text-sm tracking-wider">{{ $business->bank_account_number ?? '123-456-7890' }}</span>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span class="w-24 text-slate-500 shrink-0 font-medium">Atas Nama</span>
                                <span class="text-slate-400">:</span>
                                <span class="font-bold text-slate-900">{{ $business->bank_account_holder ?? $business->name }}</span>
                            </div>
                        </div>
                        <div class="text-[10px] text-slate-500 italic pt-0.5">* Cantumkan nomor faktur pada berita transfer.</div>
                    </div>
                </div>

                <div class="w-72 space-y-2">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal:</span>
                        <span class="tabular-nums font-semibold text-slate-900">{{ $business->currency_symbol }}
                            {{ number_format((float) $invoice->subtotal, 0, ',', '.') }}</span>
                    </div>

                    @if ($invoice->discount_amount > 0)
                        <div class="flex justify-between text-slate-600">
                            <span>Potongan Diskon:</span>
                            <span class="tabular-nums font-semibold text-rose-600">- {{ $business->currency_symbol }}
                                {{ number_format((float) $invoice->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if ($invoice->tax_amount > 0)
                        <div class="flex justify-between text-slate-600">
                            <span>PPN ({{ $invoice->tax_percentage }}%):</span>
                            <span class="tabular-nums font-semibold text-slate-900">+ {{ $business->currency_symbol }}
                                {{ number_format((float) $invoice->tax_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if ($invoice->shipping_cost > 0)
                        <div class="flex justify-between text-slate-600">
                            <span>Ongkos Kirim:</span>
                            <span class="tabular-nums font-semibold text-slate-900">+ {{ $business->currency_symbol }}
                                {{ number_format((float) $invoice->shipping_cost, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div
                        class="pt-2 border-t-2 border-slate-900 flex justify-between items-center text-sm font-bold text-slate-950">
                        <span>Total Tagihan:</span>
                        <span class="tabular-nums text-base">{{ $business->currency_symbol }}
                            {{ number_format((float) $invoice->total_amount, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-slate-600 pt-1">
                        <span>Sudah Dibayar:</span>
                        <span class="tabular-nums font-semibold text-emerald-700">{{ $business->currency_symbol }}
                            {{ number_format((float) $invoice->paid_amount, 0, ',', '.') }}</span>
                    </div>

                    <div
                        class="flex justify-between items-center pt-2 border-t border-slate-300 font-bold {{ $invoice->balance_due > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                        <span>Sisa Tagihan:</span>
                        <span class="tabular-nums text-base">{{ $business->currency_symbol }}
                            {{ number_format((float) $invoice->balance_due, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Kolom Tanda Tangan Otorisasi -->
            <div class="pt-8 grid grid-cols-2 gap-12 text-center text-xs">
                <div class="space-y-16">
                    <div>
                        <div class="text-slate-500">Diterima dan Disetujui Oleh,</div>
                        <div class="font-bold text-slate-950 mt-1">
                            {{ $invoice->customer?->company_name ?? ($invoice->customer?->name ?? 'Pelanggan') }}</div>
                    </div>
                    <div class="border-t border-slate-400 w-48 mx-auto pt-1 font-semibold text-slate-700">( Tanda
                        Tangan &amp; Cap )</div>
                </div>

                <div class="space-y-16">
                    <div>
                        <div class="text-slate-500">{{ $business->name }},</div>
                        <div class="font-bold text-slate-950 mt-1">Hormat Kami,</div>
                    </div>
                    <div class="border-t border-slate-400 w-48 mx-auto pt-1 font-semibold text-slate-700">( Bagian
                        Keuangan )</div>
                </div>
            </div>
        </div>

    </div>

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
                <span>Membuat PDF...</span>
            `;

            const element = document.querySelector('.print-sheet');
            const prevPadding = element.style.padding;
            element.style.padding = '0px';

            const filename = 'Faktur-{{ $invoice->invoice_number }}.pdf';

            const opt = {
                margin:       [15, 16, 15, 16],
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false, scrollY: 0 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
            };

            if (typeof html2pdf !== 'undefined') {
                html2pdf().set(opt).from(element).save().then(() => {
                    element.style.padding = prevPadding;
                    btn.disabled = false;
                    btn.classList.remove('opacity-75', 'cursor-wait');
                    btn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>✓ Berhasil Diunduh!</span>
                    `;
                    setTimeout(() => {
                        btn.innerHTML = originalHtml;
                    }, 3500);
                }).catch(err => {
                    element.style.padding = prevPadding;
                    console.error('html2pdf error:', err);
                    btn.disabled = false;
                    btn.classList.remove('opacity-75', 'cursor-wait');
                    btn.innerHTML = originalHtml;
                    window.print();
                });
            } else {
                element.style.padding = prevPadding;
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
