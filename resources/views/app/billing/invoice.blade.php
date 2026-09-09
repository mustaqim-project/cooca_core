<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur Tagihan {{ $payment->order_number }} — Cooca UMKM</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
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
            background-color: #0f172a;
            color: #0f172a;
        }

        @page {
            size: A4 portrait;
            margin: 14mm 15mm 14mm 15mm;
        }

        .print-sheet {
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

<body class="py-4 sm:py-8 px-3 sm:px-6">

    @php
        $badge = $payment->getStatusBadge();
        $isApproved = $payment->isApproved();
        $uniqueStr = str_pad((string)$payment->unique_code, 3, '0', STR_PAD_LEFT);
        $durationText = $payment->package_duration_days
            ? $payment->package_duration_days . ' Hari'
            : ($payment->cycle === 'annual' ? '365 Hari (1 Tahun)' : '30 Hari (1 Bulan)');
        $methodDetails = $payment->getPaymentMethodDetails();
    @endphp

    <!-- Floating Top Action Bar -->
    <header class="no-print max-w-4xl mx-auto mb-6 bg-slate-900/95 text-white p-4 rounded-2xl shadow-2xl border border-slate-800 space-y-3 backdrop-blur-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ $isApproved ? 'bg-emerald-400' : ($payment->isRejected() ? 'bg-rose-400' : 'bg-amber-400') }}" aria-hidden="true"></span>
                <span class="text-xs font-bold tracking-tight">Dokumen Faktur Resmi Cooca UMKM (A4 Siap Cetak)</span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('billing.payment.show', $payment) }}"
                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold transition-colors text-slate-200 border border-slate-700">
                    Kembali
                </a>
                <button type="button" onclick="window.print()"
                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold flex items-center gap-1.5 transition-colors border border-slate-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6" />
                        <rect x="6" y="14" width="12" height="8" rx="1" />
                    </svg>
                    <span>Cetak Printer</span>
                </button>
                <button id="btnDownloadPdf" type="button" onclick="downloadPDF()"
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition-all cursor-pointer">
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
            <span>Klik <strong>"Download PDF Langsung"</strong> untuk mengunduh invoice <code>.pdf</code> resmi tanpa dialog printer browser, bebas watermark browser.</span>
        </div>
    </header>

    <!-- Outer responsive wrapper -->
    <div class="max-w-4xl mx-auto overflow-x-auto shadow-2xl rounded-2xl">
        <!-- Paper Sheet Container (Strict A4 Layout on Paper & Screen) -->
        <main class="print-sheet bg-white p-6 sm:p-10 md:p-12 text-slate-900 space-y-6 min-w-[620px] sm:min-w-0" aria-label="Faktur Tagihan Resmi">

            <!-- Header / Kop Surat Resmi PT Cooca Teknologi Indonesia -->
            <div class="flex justify-between items-start border-b-2 border-slate-900 pb-5">
                <div class="space-y-2 max-w-md">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-10 h-10 rounded-xl bg-slate-950 flex items-center justify-center text-emerald-400 font-black text-xl tracking-tighter shadow-md">
                            C
                        </div>
                        <div>
                            <div class="text-lg font-black text-slate-950 tracking-tight leading-none">COOCA.ID</div>
                            <div class="text-[10px] font-bold text-emerald-700 tracking-wider uppercase mt-0.5">PT Cooca Teknologi Indonesia</div>
                        </div>
                    </div>
                    <div class="space-y-0.5 text-xs text-slate-600 leading-relaxed">
                        <p class="font-medium">Penyedia Platform SaaS Enterprise &amp; Kasir POS Cloud UMKM Indonesia</p>
                        <p>Website: https://cooca.id | Email: billing@cooca.id | CS: +62 812-3456-7890</p>
                        <p class="font-mono text-[11px]">NPWP: 01.234.567.8-012.000 | SK Kemenkumham Terdaftar</p>
                    </div>
                </div>

                <div class="text-right space-y-0.5">
                    <div class="text-lg font-black tracking-wider text-slate-950 uppercase">INVOICE TAGIHAN</div>
                    <div class="text-sm font-mono font-black text-slate-800">{{ $payment->order_number }}</div>
                    <div class="text-xs text-slate-500 font-mono">
                        ID Transaksi: #{{ $payment->id }}
                    </div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-600 mt-1">
                        STATUS: <span class="font-black {{ $isApproved ? 'text-emerald-700' : ($payment->isRejected() ? 'text-rose-700' : 'text-amber-700') }}">{{ strtoupper(str_replace('_', ' ', $payment->status)) }}</span>
                    </div>
                </div>
            </div>

            <!-- Detail Pihak Tertagih & Tanggal Tagihan -->
            <div class="grid grid-cols-2 gap-8 text-xs pt-1">
                <div class="space-y-1.5">
                    <div class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Ditagihkan Kepada:</div>
                    <div class="text-sm font-black text-slate-950 font-mono">{{ $business->name }}</div>
                    <div class="font-bold text-slate-800">{{ $payment->user?->name ?? 'Pemilik Bisnis' }}</div>
                    <div class="text-slate-600 font-mono">Email: {{ $payment->user?->email ?? '-' }}</div>
                    <div class="text-slate-600 font-mono">ID Workspace: {{ $business->id }}</div>
                </div>

                <div class="space-y-1.5 text-right font-sans">
                    <div class="flex justify-end gap-3">
                        <span class="text-slate-500">Tanggal Faktur:</span>
                        <span class="font-mono font-bold text-slate-900">{{ $payment->created_at->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="flex justify-end gap-3">
                        <span class="text-slate-500">Jatuh Tempo:</span>
                        <span class="font-mono font-bold text-slate-900">{{ $payment->created_at->copy()->addDay()->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="flex justify-end gap-3">
                        <span class="text-slate-500">Metode Bayar:</span>
                        <span class="font-semibold text-slate-800">{{ $methodDetails['name'] ?? strtoupper($payment->payment_method) }}</span>
                    </div>
                    @if($payment->approved_at)
                    <div class="flex justify-end gap-3">
                        <span class="text-slate-500">Waktu Pembayaran:</span>
                        <span class="font-mono font-bold text-emerald-700">{{ $payment->approved_at->translatedFormat('d F Y, H:i') }} WIB</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Tabel Rincian Paket Layanan SaaS -->
            <div class="overflow-visible pt-2">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-y-2 border-slate-900 bg-slate-100 text-slate-900">
                            <th class="py-2.5 px-3 font-bold w-10 text-center">No</th>
                            <th class="py-2.5 px-3 font-bold">Deskripsi Layanan SaaS</th>
                            <th class="py-2.5 px-3 font-bold text-center w-28">Durasi / Unit</th>
                            <th class="py-2.5 px-3 font-bold text-right w-14">Qty</th>
                            <th class="py-2.5 px-3 font-bold text-right w-28 whitespace-nowrap">Tarif Satuan</th>
                            <th class="py-2.5 px-3 font-bold text-right w-32 whitespace-nowrap">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr class="align-top">
                            <td class="py-3 px-3 text-center font-mono text-slate-600">1</td>
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-950 leading-tight">
                                    {{ $payment->package_name ?? ($payment->cycle === 'annual' ? 'Paket Core Cooca UMKM (Tahunan)' : ($payment->cycle === 'monthly' ? 'Paket Core Cooca UMKM (Bulanan)' : 'Top Up Kuota Bisnis')) }}
                                </div>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                                    Kode: {{ $payment->plan_code ?: 'COOCA-SUB' }}
                                </div>
                                <div class="text-[10px] text-slate-600 mt-1 leading-relaxed">
                                    Lisensi resmi sistem Cloud POS Kasir, Analitik Laporan Akuntansi, Manajemen Resep HPP Real-time, Multi-Outlet, dan Integrasi Bot WhatsApp Struk.
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center font-mono text-slate-700 uppercase">
                                {{ $durationText }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-semibold text-slate-900">
                                1
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-slate-800 whitespace-nowrap">
                                Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-slate-950 whitespace-nowrap">
                                Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                            </td>
                        </tr>

                        @if ($payment->unique_code > 0)
                        <tr class="align-top">
                            <td class="py-3 px-3 text-center font-mono text-slate-600">2</td>
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-950 leading-tight">Kode Unik Verifikasi Rekening Bank</div>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">Kode: VERIF-AUTO</div>
                                <div class="text-[10px] text-slate-600 mt-1 leading-relaxed">
                                    3 digit identifikasi unik perbankan untuk percepatan verifikasi rekonsiliasi transfer otomatis.
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center font-mono text-slate-700 uppercase">
                                Trans
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-semibold text-slate-900">
                                1
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-amber-700 font-semibold whitespace-nowrap">
                                +Rp {{ $uniqueStr }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-amber-700 whitespace-nowrap">
                                +Rp {{ $uniqueStr }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Footer: Kalkulasi Finansial & Otorisasi Resmi -->
            <div class="keep-together space-y-6 pt-2">
                <div class="flex justify-between items-start border-t-2 border-slate-900 pt-3 gap-6 text-xs">
                    
                    <!-- Left: Bank Account Instructions & Transfer Data -->
                    <div class="space-y-4 w-1/2">
                        <div class="p-3.5 border-l-2 border-slate-400 bg-slate-50/80 space-y-1.5 text-slate-700 rounded-r-xl">
                            <div class="font-bold text-slate-950 text-[11px] uppercase tracking-wider">Rekening Tujuan Resmi Cooca:</div>
                            <div class="pt-1 space-y-1 text-xs">
                                <div class="flex items-baseline gap-2">
                                    <span class="w-24 text-slate-500 shrink-0 font-medium">Bank</span>
                                    <span class="text-slate-400">:</span>
                                    <span class="font-bold text-slate-900">{{ $methodDetails['bank_name'] }}</span>
                                </div>
                                <div class="flex items-baseline gap-2">
                                    <span class="w-24 text-slate-500 shrink-0 font-medium">No. Rekening</span>
                                    <span class="text-slate-400">:</span>
                                    <span class="font-mono font-bold text-slate-950 text-sm tracking-wider">{{ $methodDetails['account_number'] }}</span>
                                </div>
                                <div class="flex items-baseline gap-2">
                                    <span class="w-24 text-slate-500 shrink-0 font-medium">Atas Nama</span>
                                    <span class="text-slate-400">:</span>
                                    <span class="font-bold text-slate-900">{{ $methodDetails['account_name'] }}</span>
                                </div>
                            </div>
                            @if($payment->sender_account_name)
                            <div class="pt-2 mt-2 border-t border-slate-200 text-[11px] space-y-0.5">
                                <div><span class="text-slate-500">Rekening Pengirim:</span> <strong class="text-slate-900">{{ $payment->sender_account_name }} ({{ $payment->sender_bank ?? '-' }})</strong></div>
                                @if($payment->proof_uploaded_at)
                                <div><span class="text-slate-500">Waktu Kirim Struk:</span> <span class="font-mono text-slate-700">{{ $payment->proof_uploaded_at->translatedFormat('d F Y, H:i') }} WIB</span></div>
                                @endif
                            </div>
                            @endif
                        </div>

                        @if ($payment->notes)
                            <div>
                                <div class="font-bold text-slate-900 mb-0.5">Catatan Tambahan:</div>
                                <div class="text-slate-600 whitespace-pre-line bg-slate-50 p-2.5 rounded-lg border border-slate-200">{{ $payment->notes }}</div>
                            </div>
                        @endif
                    </div>

                    <!-- Right: Financial Ledger Calculation -->
                    <div class="w-72 space-y-2">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal Tagihan:</span>
                            <span class="font-mono font-semibold text-slate-900">
                                Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                            </span>
                        </div>

                        @if ($payment->unique_code > 0)
                            <div class="flex justify-between text-slate-600">
                                <span class="text-amber-700">Kode Unik Verifikasi:</span>
                                <span class="font-mono font-semibold text-amber-700">
                                    +Rp {{ $uniqueStr }}
                                </span>
                            </div>
                        @endif

                        <div class="pt-2 border-t-2 border-slate-900 flex justify-between items-center text-sm font-black text-slate-950">
                            <span>Total Tagihan:</span>
                            <span class="font-mono text-base text-slate-950 font-black">
                                Rp {{ number_format((float) $payment->total_payable, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex justify-between text-slate-600 pt-1">
                            <span>Sudah Dibayar:</span>
                            <span class="font-mono font-semibold {{ $isApproved ? 'text-emerald-700' : 'text-slate-500' }}">
                                Rp {{ $isApproved ? number_format((float) $payment->total_payable, 0, ',', '.') : '0' }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center pt-2 border-t border-slate-300 font-bold {{ $isApproved ? 'text-emerald-700' : 'text-amber-700' }}">
                            <span>Sisa Tagihan:</span>
                            <span class="font-mono text-base font-black">
                                {{ $isApproved ? 'Rp 0 (LUNAS)' : 'Rp ' . number_format((float) $payment->total_payable, 0, ',', '.') }}
                            </span>
                        </div>

                        <!-- Official Stamp Seal if Approved -->
                        @if($isApproved)
                        <div class="pt-3 text-center">
                            <div class="inline-block px-4 py-1.5 rounded-xl border-2 border-emerald-600 text-emerald-700 font-black text-xs font-mono uppercase tracking-widest rotate-[-3deg] shadow-sm">
                                ✓ LUNAS / PAID DIGITAL
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Digital Signatures & Authorization Blocks -->
                <div class="pt-8 grid grid-cols-2 gap-12 text-center text-xs">
                    <div class="space-y-16">
                        <div>
                            <div class="text-slate-500">Diterima dan Disetujui Oleh,</div>
                            <div class="font-bold text-slate-950 mt-1 font-mono">
                                {{ $business->name }}
                            </div>
                        </div>
                        <div class="border-t border-slate-400 w-48 mx-auto pt-1 font-semibold text-slate-700">
                            ( {{ $payment->user?->name ?? 'Pemilik Bisnis' }} )
                        </div>
                    </div>

                    <div class="space-y-16">
                        <div>
                            <div class="text-slate-500">PT Cooca Teknologi Indonesia,</div>
                            <div class="font-bold text-slate-950 mt-1">Bagian Billing &amp; Keuangan</div>
                        </div>
                        <div class="border-t border-slate-400 w-48 mx-auto pt-1 font-semibold text-slate-700">
                            ( Sistem Otorisasi Digital Cooca )
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
                <span>Membuat PDF...</span>
            `;

            const element = document.querySelector('.print-sheet');
            const prevPadding = element.style.padding;
            element.style.padding = '0px';

            const filename = 'Faktur-Cooca-{{ $payment->order_number }}.pdf';

            const opt = {
                margin:       [14, 15, 14, 15],
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
