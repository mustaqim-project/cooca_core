<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order {{ $purchaseOrder->po_number }} — {{ $business->name }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap"
        rel="stylesheet">

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
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
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

<body class="py-6 px-4 sm:px-6 bg-slate-100">

    <!-- Floating Top Print Action Bar -->
    <div class="no-print max-w-4xl mx-auto mb-6 bg-slate-900 text-white p-4 rounded-2xl shadow-xl space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                <span class="text-xs font-bold tracking-tight">Dokumen Purchase Order (A4 Ready)</span>
            </div>
            <div class="flex items-center gap-2.5">
                <button onclick="window.history.back()"
                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-medium transition-colors">
                    Kembali
                </button>
                <button onclick="window.print()"
                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium flex items-center gap-1.5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6" />
                        <rect x="6" y="14" width="12" height="8" rx="1" />
                    </svg>
                    <span>Cetak Printer</span>
                </button>
                <button id="btnDownloadPdf" onclick="downloadPDF()"
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition-all">
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
        <div class="pt-2 border-t border-slate-800 flex items-center gap-2 text-[11px] text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-400 shrink-0" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
            <span>Klik <strong>"Download PDF Langsung"</strong> untuk mengunduh file <code>.pdf</code> secara instan tanpa dialog print peramban dan bebas header tanggal/URL.</span>
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
                    <h1 class="text-base font-bold text-slate-950 uppercase tracking-wide leading-tight">{{ $business->name }}</h1>
                    <p class="text-xs text-slate-600">{{ $business->address ?? 'Alamat Kantor Operasional' }}</p>
                    <p class="text-xs text-slate-600">
                        @if ($business->email)
                            Email: {{ $business->email }}
                        @endif
                        @if ($business->phone)
                            | Telp: {{ $business->phone }}
                        @endif
                    </p>
                    @if ($business->tax_identification_number)
                        <p class="text-xs text-slate-600 font-mono">NPWP: {{ $business->tax_identification_number }}</p>
                    @endif
                </div>
            </div>

            <div class="text-right space-y-0.5">
                <div class="text-lg font-bold tracking-wider text-slate-950 uppercase">
                    {{ $purchaseOrder->po_type === 'customer' ? 'CUSTOMER PURCHASE ORDER' : 'VENDOR PURCHASE ORDER' }}
                </div>
                <div class="text-sm font-mono font-bold text-slate-800">{{ $purchaseOrder->po_number }}</div>
                @if ($purchaseOrder->reference_number)
                    <div class="text-xs text-slate-500 font-mono">No. Ref: {{ $purchaseOrder->reference_number }}</div>
                @endif
                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-600 mt-1">
                    STATUS: <span class="font-bold text-slate-900">{{ strtoupper(str_replace('_', ' ', $purchaseOrder->status)) }}</span>
                </div>
            </div>
        </div>

        <!-- Detail Dokumen & Pihak Terkait -->
        <div class="grid grid-cols-2 gap-8 text-xs">
            <div class="space-y-1.5">
                <div class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">
                    {{ $purchaseOrder->po_type === 'customer' ? 'Pelanggan / Pemesan:' : 'Pemasok / Vendor Tujuan:' }}
                </div>
                @if ($purchaseOrder->customer)
                    <div class="text-sm font-bold text-slate-950">{{ $purchaseOrder->customer->name }}</div>
                    @if ($purchaseOrder->customer->company_name)
                        <div class="font-semibold text-slate-800">{{ $purchaseOrder->customer->company_name }}</div>
                    @endif
                    <div class="text-slate-600 whitespace-pre-line">
                        {{ $purchaseOrder->customer->billing_address ?? '-' }}</div>
                    <div class="text-slate-600 font-mono">Kontak:
                        {{ $purchaseOrder->customer->phone ?? ($purchaseOrder->customer->email ?? '-') }}</div>
                    @if ($purchaseOrder->customer->tax_identification_number)
                        <div class="text-slate-600 font-mono">NPWP:
                            {{ $purchaseOrder->customer->tax_identification_number }}</div>
                    @endif
                @elseif($purchaseOrder->supplier)
                    <div class="text-sm font-bold text-slate-950">{{ $purchaseOrder->supplier->name }}</div>
                    <div class="text-slate-600">PIC: {{ $purchaseOrder->supplier->contact_person ?? '-' }}</div>
                    <div class="text-slate-600 whitespace-pre-line">{{ $purchaseOrder->supplier->address ?? '-' }}
                    </div>
                    <div class="text-slate-600 font-mono">Kontak:
                        {{ $purchaseOrder->supplier->phone ?? ($purchaseOrder->supplier->email ?? '-') }}</div>
                @else
                    <span class="text-slate-500">-</span>
                @endif
            </div>

            <div class="space-y-2 text-right">
                <div class="flex justify-end gap-4">
                    <span class="text-slate-500">Tanggal Order:</span>
                    <span
                        class="font-mono font-bold text-slate-900">{{ $purchaseOrder->order_date?->translatedFormat('d F Y') }}</span>
                </div>
                @if ($purchaseOrder->expected_delivery_date)
                    <div class="flex justify-end gap-4">
                        <span class="text-slate-500">Target Pengiriman:</span>
                        <span
                            class="font-mono font-bold text-slate-900">{{ $purchaseOrder->expected_delivery_date?->translatedFormat('d F Y') }}</span>
                    </div>
                @endif
                <div class="flex justify-end gap-4">
                    <span class="text-slate-500">Mata Uang:</span>
                    <span class="font-semibold text-slate-800">{{ $business->currency_code }}
                        ({{ $business->currency_symbol }})</span>
                </div>
            </div>
        </div>

        <!-- Tabel Rincian Item PO -->
        <div class="overflow-visible">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-y-2 border-slate-900 bg-slate-100 text-slate-900">
                        <th class="py-2 px-2.5 font-bold w-10 text-center">No</th>
                        <th class="py-2 px-2.5 font-bold">Deskripsi Item / Barang</th>
                        <th class="py-2 px-2.5 font-bold text-center w-16">Satuan</th>
                        <th class="py-2 px-2.5 font-bold text-right w-14">Qty</th>
                        <th class="py-2 px-2.5 font-bold text-right w-28 whitespace-nowrap">Harga Satuan</th>
                        <th class="py-2 px-2.5 font-bold text-right w-32 whitespace-nowrap">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($purchaseOrder->items as $idx => $item)
                        <tr class="align-top">
                            <td class="py-2 px-2.5 text-center font-mono text-slate-600">{{ $idx + 1 }}</td>
                            <td class="py-2 px-2.5">
                                <div class="font-bold text-slate-950 leading-tight">{{ $item->item_name }}</div>
                                @if ($item->sku)
                                    <div class="text-[10px] text-slate-500 font-mono mt-0.5">Kode: {{ $item->sku }}</div>
                                @endif
                                @if ($item->notes)
                                    <div class="text-[10px] text-slate-600 mt-0.5 leading-snug">{{ $item->notes }}</div>
                                @endif
                            </td>
                            <td class="py-2 px-2.5 text-center font-mono text-slate-700 uppercase">
                                {{ $item->unit?->code ?? ($item->unit?->symbol ?? 'pcs') }}
                            </td>
                            <td class="py-2 px-2.5 text-right font-mono font-semibold text-slate-900">
                                {{ (float) $item->quantity == (int) $item->quantity ? number_format((float) $item->quantity, 0, ',', '.') : rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }}
                            </td>
                            <td class="py-2 px-2.5 text-right font-mono text-slate-800 whitespace-nowrap">
                                {{ $business->currency_symbol }} {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-2.5 text-right font-mono font-bold text-slate-950 whitespace-nowrap">
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
            <div class="flex justify-between items-start border-t-2 border-slate-900 pt-2 gap-6 text-xs">
                <div class="space-y-4 w-1/2">
                    @if ($purchaseOrder->terms_and_conditions)
                        <div>
                            <div class="font-bold text-slate-900 mb-0.5">Syarat & Ketentuan Pengiriman:</div>
                            <div class="text-slate-600 whitespace-pre-line">{{ $purchaseOrder->terms_and_conditions }}
                            </div>
                        </div>
                    @endif

                    @if ($purchaseOrder->notes)
                        <div>
                            <div class="font-bold text-slate-900 mb-0.5">Catatan:</div>
                            <div class="text-slate-600 whitespace-pre-line">{{ $purchaseOrder->notes }}</div>
                        </div>
                    @endif
                </div>

                <div class="w-72 space-y-2">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal Item:</span>
                        <span class="font-mono font-semibold text-slate-900">{{ $business->currency_symbol }}
                            {{ number_format((float) $purchaseOrder->subtotal, 0, ',', '.') }}</span>
                    </div>

                    @if ($purchaseOrder->discount_amount > 0)
                        <div class="flex justify-between text-slate-600">
                            <span>Potongan Diskon:</span>
                            <span class="font-mono font-semibold text-rose-600">- {{ $business->currency_symbol }}
                                {{ number_format((float) $purchaseOrder->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if ($purchaseOrder->tax_amount > 0)
                        <div class="flex justify-between text-slate-600">
                            <span>PPN ({{ $purchaseOrder->tax_percentage }}%):</span>
                            <span class="font-mono font-semibold text-slate-900">+ {{ $business->currency_symbol }}
                                {{ number_format((float) $purchaseOrder->tax_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div
                        class="pt-2 border-t-2 border-slate-900 flex justify-between items-center text-sm font-bold text-slate-950">
                        <span>Total Nilai PO:</span>
                        <span class="font-mono text-base">{{ $business->currency_symbol }}
                            {{ number_format((float) $purchaseOrder->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Kolom Tanda Tangan Otorisasi -->
            <div class="pt-8 grid grid-cols-2 gap-12 text-center text-xs">
                <div class="space-y-16">
                    <div>
                        <div class="text-slate-500">Dibuat Oleh,</div>
                        <div class="font-bold text-slate-950 mt-1">{{ $business->name }}</div>
                    </div>
                    <div class="border-t border-slate-400 w-48 mx-auto pt-1 font-semibold text-slate-700">( Bagian
                        Purchasing )</div>
                </div>

                <div class="space-y-16">
                    <div>
                        <div class="text-slate-500">Disetujui & Dikonfirmasi Oleh,</div>
                        <div class="font-bold text-slate-950 mt-1">
                            {{ $purchaseOrder->customer?->company_name ?? ($purchaseOrder->customer?->name ?? ($purchaseOrder->supplier?->name ?? 'Pihak Terkait')) }}
                        </div>
                    </div>
                    <div class="border-t border-slate-400 w-48 mx-auto pt-1 font-semibold text-slate-700">( Tanda Tangan &
                        Cap )</div>
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
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
