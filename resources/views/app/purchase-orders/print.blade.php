<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order {{ $purchaseOrder->po_number }} — {{ $business->name }}</title>

    <!-- System Font & Fallback -->
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
            background-color: #f2f2f7;
            color: #000000;
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

<body class="py-6 px-4 sm:px-6 bg-[#F2F2F7] dark:bg-[#000000]">

    <!-- Floating Top Print Action Bar (macOS Sonoma Floating Toolbar) -->
    <div class="no-print max-w-4xl mx-auto mb-6 backdrop-blur-md bg-white/90 dark:bg-[#1C1C1E]/90 border border-black/10 dark:border-white/10 p-4 rounded-[14px] shadow-[0_4px_24px_rgba(0,0,0,0.06)] space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#34C759]"></span>
                <span class="text-[13px] font-semibold text-black dark:text-white tracking-tight">Dokumen Purchase Order (Format A4 Resmi)</span>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.history.back()"
                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all">
                    Kembali
                </button>
                <button onclick="window.print()"
                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-black/60 dark:text-white/60" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6" />
                        <rect x="6" y="14" width="12" height="8" rx="1" />
                    </svg>
                    <span>Cetak Printer</span>
                </button>
                <button id="btnDownloadPdf" onclick="downloadPDF()"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)] flex items-center gap-1.5">
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
            <span>Klik <strong>"Download PDF Langsung"</strong> untuk mengunduh berkas <code>.pdf</code> resmi tanpa dialog cetak peramban.</span>
        </div>
    </div>

    <!-- Paper Sheet (Standard A4 Flat Container) -->
    <div class="print-sheet max-w-4xl mx-auto bg-white p-8 sm:p-12 border border-black/10 shadow-sm rounded-[14px] text-black space-y-6">

        <!-- Header / Kop Surat -->
        <div class="flex justify-between items-start border-b-2 border-black pb-5">
            <div class="space-y-1.5 max-w-md">
                @if ($business->logo_url)
                    <div class="mb-1">
                        <img src="{{ $business->logo_url }}" alt="{{ $business->name }}"
                            class="max-h-12 max-w-[140px] object-contain">
                    </div>
                @endif
                <div class="space-y-0.5">
                    <h1 class="text-[16px] font-bold text-black uppercase tracking-wide leading-tight">{{ $business->name }}</h1>
                    <p class="text-[12px] text-black/70">{{ $business->address ?? 'Alamat Kantor Operasional' }}</p>
                    <p class="text-[12px] text-black/70">
                        @if ($business->email)
                            Email: {{ $business->email }}
                        @endif
                        @if ($business->phone)
                            | Telp: {{ $business->phone }}
                        @endif
                    </p>
                    @if ($business->tax_identification_number)
                        <p class="text-[12px] text-black/70 tabular-nums">NPWP: {{ $business->tax_identification_number }}</p>
                    @endif
                </div>
            </div>

            <div class="text-right space-y-0.5">
                <div class="text-[16px] font-bold tracking-wider text-black uppercase">
                    {{ $purchaseOrder->po_type === 'customer' ? 'CUSTOMER PURCHASE ORDER' : 'VENDOR PURCHASE ORDER' }}
                </div>
                <div class="text-[14px] font-bold tabular-nums text-black/80">{{ $purchaseOrder->po_number }}</div>
                @if ($purchaseOrder->reference_number)
                    <div class="text-[12px] text-black/60 tabular-nums">No. Ref: {{ $purchaseOrder->reference_number }}</div>
                @endif
                <div class="text-[11px] font-semibold uppercase tracking-wider text-black/60 mt-1">
                    STATUS: <span class="font-bold text-black">{{ strtoupper(str_replace('_', ' ', $purchaseOrder->status)) }}</span>
                </div>
            </div>
        </div>

        <!-- Detail Dokumen & Pihak Terkait -->
        <div class="grid grid-cols-2 gap-8 text-[12px]">
            <div class="space-y-1">
                <div class="font-bold text-black/50 uppercase tracking-wider text-[10px]">
                    {{ $purchaseOrder->po_type === 'customer' ? 'Pelanggan / Pemesan:' : 'Pemasok / Vendor Tujuan:' }}
                </div>
                @if ($purchaseOrder->customer)
                    <div class="text-[14px] font-bold text-black">{{ $purchaseOrder->customer->name }}</div>
                    @if ($purchaseOrder->customer->company_name)
                        <div class="font-semibold text-black/80">{{ $purchaseOrder->customer->company_name }}</div>
                    @endif
                    <div class="text-black/70 whitespace-pre-line leading-relaxed">
                        {{ $purchaseOrder->customer->billing_address ?? '-' }}</div>
                    <div class="text-black/70 tabular-nums">Kontak:
                        {{ $purchaseOrder->customer->phone ?? ($purchaseOrder->customer->email ?? '-') }}</div>
                    @if ($purchaseOrder->customer->tax_identification_number)
                        <div class="text-black/70 tabular-nums">NPWP:
                            {{ $purchaseOrder->customer->tax_identification_number }}</div>
                    @endif
                @elseif($purchaseOrder->supplier)
                    <div class="text-[14px] font-bold text-black">{{ $purchaseOrder->supplier->name }}</div>
                    <div class="text-black/70">PIC: {{ $purchaseOrder->supplier->contact_person ?? '-' }}</div>
                    <div class="text-black/70 whitespace-pre-line leading-relaxed">{{ $purchaseOrder->supplier->address ?? '-' }}
                    </div>
                    <div class="text-black/70 tabular-nums">Kontak:
                        {{ $purchaseOrder->supplier->phone ?? ($purchaseOrder->supplier->email ?? '-') }}</div>
                @else
                    <span class="text-black/50">-</span>
                @endif
            </div>

            <div class="space-y-1.5 text-right">
                <div class="flex justify-end gap-4">
                    <span class="text-black/50">Tanggal Order:</span>
                    <span class="tabular-nums font-semibold text-black">{{ $purchaseOrder->order_date?->translatedFormat('d F Y') }}</span>
                </div>
                @if ($purchaseOrder->expected_delivery_date)
                    <div class="flex justify-end gap-4">
                        <span class="text-black/50">Target Pengiriman:</span>
                        <span class="tabular-nums font-semibold text-black">{{ $purchaseOrder->expected_delivery_date?->translatedFormat('d F Y') }}</span>
                    </div>
                @endif
                <div class="flex justify-end gap-4">
                    <span class="text-black/50">Mata Uang:</span>
                    <span class="font-semibold text-black/80">{{ $business->currency_code }} ({{ $business->currency_symbol }})</span>
                </div>
            </div>
        </div>

        <!-- Tabel Rincian Item PO -->
        <div class="overflow-visible">
            <table class="w-full text-left text-[12px] border-collapse">
                <thead>
                    <tr class="border-y-2 border-black bg-black/[0.04] text-black">
                        <th class="py-2 px-2.5 font-bold w-10 text-center">No</th>
                        <th class="py-2 px-2.5 font-bold">Deskripsi Item / Barang</th>
                        <th class="py-2 px-2.5 font-bold text-center w-16">Satuan</th>
                        <th class="py-2 px-2.5 font-bold text-right w-14">Qty</th>
                        <th class="py-2 px-2.5 font-bold text-right w-28 whitespace-nowrap">Harga Satuan</th>
                        <th class="py-2 px-2.5 font-bold text-right w-32 whitespace-nowrap">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/10">
                    @foreach ($purchaseOrder->items as $idx => $item)
                        <tr class="align-top">
                            <td class="py-2 px-2.5 text-center tabular-nums text-black/60">{{ $idx + 1 }}</td>
                            <td class="py-2 px-2.5">
                                <div class="font-bold text-black leading-tight">{{ $item->item_name }}</div>
                                @if ($item->sku)
                                    <div class="text-[10px] text-black/50 tabular-nums mt-0.5">Kode: {{ $item->sku }}</div>
                                @endif
                                @if ($item->notes)
                                    <div class="text-[10px] text-black/60 mt-0.5 leading-snug">{{ $item->notes }}</div>
                                @endif
                            </td>
                            <td class="py-2 px-2.5 text-center tabular-nums text-black/70 uppercase">
                                {{ $item->unit?->code ?? ($item->unit?->symbol ?? 'pcs') }}
                            </td>
                            <td class="py-2 px-2.5 text-right tabular-nums font-semibold text-black">
                                {{ (float) $item->quantity == (int) $item->quantity ? number_format((float) $item->quantity, 0, ',', '.') : rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }}
                            </td>
                            <td class="py-2 px-2.5 text-right tabular-nums text-black/80 whitespace-nowrap">
                                {{ $business->currency_symbol }} {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-2.5 text-right tabular-nums font-bold text-black whitespace-nowrap">
                                {{ $business->currency_symbol }} {{ number_format((float) $item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer: Kalkulasi Finansial & Tanda Tangan (Keep Together) -->
        <div class="keep-together space-y-6 pt-2">
            <!-- Kalkulasi Finansial & Catatan -->
            <div class="flex justify-between items-start border-t-2 border-black pt-3 gap-6 text-[12px]">
                <div class="space-y-3 w-1/2">
                    @if ($purchaseOrder->terms_and_conditions)
                        <div>
                            <div class="font-bold text-black mb-0.5">Syarat & Ketentuan Pengiriman:</div>
                            <div class="text-black/70 whitespace-pre-line leading-relaxed">{{ $purchaseOrder->terms_and_conditions }}</div>
                        </div>
                    @endif

                    @if ($purchaseOrder->notes)
                        <div>
                            <div class="font-bold text-black mb-0.5">Catatan:</div>
                            <div class="text-black/70 whitespace-pre-line leading-relaxed">{{ $purchaseOrder->notes }}</div>
                        </div>
                    @endif
                </div>

                <div class="w-72 space-y-2">
                    <div class="flex justify-between text-black/70">
                        <span>Subtotal Item:</span>
                        <span class="tabular-nums font-semibold text-black">
                            {{ $business->currency_symbol }} {{ number_format((float) $purchaseOrder->subtotal, 0, ',', '.') }}
                        </span>
                    </div>

                    @if ($purchaseOrder->discount_amount > 0)
                        <div class="flex justify-between text-black/70">
                            <span>Potongan Diskon:</span>
                            <span class="tabular-nums font-semibold text-[#FF3B30]">
                                - {{ $business->currency_symbol }} {{ number_format((float) $purchaseOrder->discount_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif

                    @if ($purchaseOrder->tax_amount > 0)
                        <div class="flex justify-between text-black/70">
                            <span>PPN ({{ $purchaseOrder->tax_percentage }}%):</span>
                            <span class="tabular-nums font-semibold text-black">
                                + {{ $business->currency_symbol }} {{ number_format((float) $purchaseOrder->tax_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif

                    <div class="pt-2 border-t-2 border-black flex justify-between items-center text-[14px] font-bold text-black">
                        <span>Total Nilai PO:</span>
                        <span class="tabular-nums text-[16px]">
                            {{ $business->currency_symbol }} {{ number_format((float) $purchaseOrder->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Kolom Tanda Tangan Otorisasi -->
            <div class="pt-8 grid grid-cols-2 gap-12 text-center text-[12px]">
                <div class="space-y-16">
                    <div>
                        <div class="text-black/60">Dibuat Oleh,</div>
                        <div class="font-bold text-black mt-1">{{ $business->name }}</div>
                    </div>
                    <div class="border-t border-black/40 w-48 mx-auto pt-1 font-semibold text-black/80">
                        ( Bagian Purchasing )
                    </div>
                </div>

                <div class="space-y-16">
                    <div>
                        <div class="text-black/60">Disetujui & Dikonfirmasi Oleh,</div>
                        <div class="font-bold text-black mt-1">
                            {{ $purchaseOrder->customer?->company_name ?? ($purchaseOrder->customer?->name ?? ($purchaseOrder->supplier?->name ?? 'Pihak Terkait')) }}
                        </div>
                    </div>
                    <div class="border-t border-black/40 w-48 mx-auto pt-1 font-semibold text-black/80">
                        ( Tanda Tangan & Cap )
                    </div>
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

            const filename = 'PO-{{ $purchaseOrder->po_number }}.pdf';

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
