<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Resmi POS & Rekonsiliasi - {{ $business->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
            font-size: 11px;
            line-height: 1.4;
            padding: 24px;
        }
        .print-container {
            max-width: 1024px;
            margin: 0 auto;
            background: #FFFFFF;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #E2E8F0;
        }
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #E2E8F0;
        }
        .btn-print {
            background-color: #007AFF;
            color: #FFFFFF;
            padding: 8px 18px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-print:hover {
            background-color: #0062CC;
        }
        .btn-close {
            background-color: #F1F5F9;
            color: #475569;
            padding: 8px 16px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid #CBD5E1;
        }

        /* Header Document */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 2px solid #0F172A;
        }
        .doc-title h1 {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #0F172A;
            text-transform: uppercase;
        }
        .doc-title h2 {
            font-size: 13px;
            font-weight: 600;
            color: #007AFF;
            margin-top: 2px;
        }
        .doc-meta {
            text-align: right;
            font-size: 10px;
            color: #64748B;
        }
        .doc-meta strong {
            color: #0F172A;
        }

        /* Bento KPI Grid */
        .bento-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        .bento-card {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 12px 14px;
        }
        .bento-card.emerald {
            background-color: #ECFDF5;
            border-color: #A7F3D0;
        }
        .bento-card.blue {
            background-color: #EFF6FF;
            border-color: #BFDBFE;
        }
        .bento-card.purple {
            background-color: #FAF5FF;
            border-color: #E9D5FF;
        }
        .bento-label {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748B;
            margin-bottom: 4px;
        }
        .bento-value {
            font-size: 16px;
            font-weight: 800;
            color: #0F172A;
            font-feature-settings: "tnum";
            font-variant-numeric: tabular-nums;
        }
        .bento-sub {
            font-size: 9px;
            color: #64748B;
            margin-top: 2px;
        }

        /* Section Headings */
        .section-heading {
            font-size: 12px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 8px;
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-heading::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 14px;
            background-color: #007AFF;
            border-radius: 2px;
        }

        /* Clean Data Tables */
        table.print-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 10px;
        }
        table.print-table th {
            background-color: #1C1C1E;
            color: #FFFFFF;
            font-weight: 700;
            text-align: left;
            padding: 6px 8px;
            font-size: 9.5px;
            border: 1px solid #1C1C1E;
        }
        table.print-table th.num, table.print-table td.num {
            text-align: right;
            font-feature-settings: "tnum";
            font-variant-numeric: tabular-nums;
        }
        table.print-table td {
            padding: 6px 8px;
            border: 1px solid #E2E8F0;
            color: #1E293B;
        }
        table.print-table tbody tr:nth-child(even) {
            background-color: #F8FAFC;
        }
        table.print-table tfoot td {
            background-color: #F1F5F9;
            font-weight: 700;
            border-top: 2px solid #0F172A;
            color: #0F172A;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-shopee { background: #EE4D2D; color: #FFF; }
        .badge-gofood { background: #EE2724; color: #FFF; }
        .badge-grab { background: #00B14F; color: #FFF; }
        .badge-pos { background: #34C759; color: #FFF; }

        /* Two Columns Grid */
        .two-cols {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        /* Signatures Block */
        .signature-block {
            margin-top: 32px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            text-align: center;
            page-break-inside: avoid;
        }
        .sig-box {
            border: 1px dashed #CBD5E1;
            padding: 16px 8px;
            border-radius: 8px;
            background: #FAFAFA;
        }
        .sig-title {
            font-size: 10px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 40px;
        }
        .sig-name {
            font-size: 11px;
            font-weight: 700;
            color: #0F172A;
            text-decoration: underline;
        }
        .sig-role {
            font-size: 9px;
            color: #64748B;
        }

        /* Print Media Query */
        @media print {
            body {
                background: #FFFFFF;
                padding: 0;
            }
            .print-container {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
            }
            .action-bar {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>

<div class="print-container">
    <!-- Action Bar (Hidden on Print) -->
    <div class="action-bar">
        <div style="display: flex; align-items: center; gap: 8px;">
            <a href="{{ route('pos.reports.index', ['tab' => $filter->activeTab ?? 'overview']) }}" class="btn-close">
                &larr; Kembali ke Dashboard
            </a>
            <span style="font-size: 11px; color: #64748B;">Pratinjau Cetak Siap Dokumen A4 / Ekspor PDF</span>
        </div>
        <button onclick="window.print()" class="btn-print">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Cetak Laporan / Simpan PDF
        </button>
    </div>

    <!-- Header Dokumen -->
    <div class="doc-header">
        <div class="doc-title">
            <h1>{{ $business->name }}</h1>
            <h2>MASTER LAPORAN PENJUALAN KASIR POS & REKONSILIASI FISKAL</h2>
            <div style="font-size: 10px; color: #475569; margin-top: 4px;">
                Lokasi: <strong>{{ $locationName }}</strong> | Industri: <strong>{{ ucfirst($business->industry_type ?? 'Multi-Industry') }}</strong>
            </div>
        </div>
        <div class="doc-meta">
            <div>Periode: <strong>{{ $filter->startDate->translatedFormat('d M Y') }} - {{ $filter->endDate->translatedFormat('d M Y') }}</strong></div>
            <div>Waktu Cetak: <strong>{{ now()->format('d/m/Y H:i:s') }} WIB</strong></div>
            <div>Dokumen ID: <strong>POS-REP-{{ strtoupper(substr(md5($business->id . now()->toDateString()), 0, 8)) }}</strong></div>
            <div style="color: #059669; font-weight: 700; margin-top: 2px;">COOCA SINGLE-SOURCE-OF-TRUTH</div>
        </div>
    </div>

    <!-- Bento Top KPI Cards -->
    <div class="bento-kpi-grid">
        <div class="bento-card blue">
            <div class="bento-label">Omzet Bersih Penjualan</div>
            <div class="bento-value">Rp {{ number_format($kpi->netSales, 0, ',', '.') }}</div>
            <div class="bento-sub">Bruto: Rp {{ number_format($kpi->grossSales, 0, ',', '.') }}</div>
        </div>
        <div class="bento-card">
            <div class="bento-label">Transaksi Selesai</div>
            <div class="bento-value">{{ number_format($kpi->totalOrders, 0, ',', '.') }} Pesanan</div>
            <div class="bento-sub">AOV: Rp {{ number_format($kpi->averageOrderValue, 0, ',', '.') }}</div>
        </div>
        <div class="bento-card">
            <div class="bento-label">Total Beban Pokok (HPP)</div>
            <div class="bento-value">Rp {{ number_format($kpi->totalHpp, 0, ',', '.') }}</div>
            <div class="bento-sub">BOM & Pembelian Riil</div>
        </div>
        <div class="bento-card emerald">
            <div class="bento-label">Laba Kotor & Margin</div>
            <div class="bento-value">Rp {{ number_format($kpi->grossProfit, 0, ',', '.') }}</div>
            <div class="bento-sub" style="color: #065F46; font-weight: 700;">Margin: {{ number_format($kpi->grossMarginPercent, 1) }}%</div>
        </div>
    </div>

    <!-- TABEL 1: Performa Saluran Penjualan & Ojol Delivery -->
    <div class="section-heading">1. Performa Saluran Penjualan & Online Food Delivery (Ojol Settlement)</div>
    <table class="print-table">
        <thead>
            <tr>
                <th>Saluran Penjualan</th>
                <th class="num">Pesanan</th>
                <th class="num">Omzet Bruto</th>
                <th class="num">Diskon</th>
                <th class="num">Omzet Kasir</th>
                <th class="num">MDR %</th>
                <th class="num">Komisi Ojol</th>
                <th class="num">Net Payout Resto</th>
                <th class="num">Modal HPP</th>
                <th class="num">Laba Riil</th>
                <th class="num">Margin %</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totOrders = 0; $totGross = 0; $totDisc = 0; $totNet = 0; $totFee = 0; $totPayout = 0; $totHpp = 0; $totProfit = 0;
            @endphp
            @forelse($channels as $ch)
                @php
                    $chName = is_array($ch) ? ($ch['channel_name'] ?? $ch['channel_label'] ?? 'Saluran POS') : ($ch->channel_name ?? $ch->channel_label ?? 'Saluran POS');
                    $ordCount = is_array($ch) ? ($ch['orders_count'] ?? $ch['total_orders'] ?? 0) : ($ch->orders_count ?? $ch->total_orders ?? 0);
                    $gSales = is_array($ch) ? ($ch['gross_sales'] ?? 0.0) : ($ch->gross_sales ?? 0.0);
                    $disc = is_array($ch) ? ($ch['total_discount'] ?? 0.0) : ($ch->total_discount ?? 0.0);
                    $nSales = is_array($ch) ? ($ch['net_sales'] ?? ($gSales - $disc)) : ($ch->net_sales ?? ($gSales - $disc));
                    $fPercent = is_array($ch) ? ($ch['platform_fee_percent'] ?? 0.0) : ($ch->platform_fee_percent ?? 0.0);
                    $fAmount = is_array($ch) ? ($ch['platform_fee_amount'] ?? 0.0) : ($ch->platform_fee_amount ?? 0.0);
                    $nPayout = is_array($ch) ? ($ch['net_merchant_payout'] ?? ($nSales - $fAmount)) : ($ch->net_merchant_payout ?? ($nSales - $fAmount));
                    $hpp = is_array($ch) ? ($ch['total_hpp'] ?? 0.0) : ($ch->total_hpp ?? 0.0);
                    $rProfit = is_array($ch) ? ($ch['real_gross_profit'] ?? ($nPayout - $hpp)) : ($ch->real_gross_profit ?? ($nPayout - $hpp));
                    $rMargin = is_array($ch) ? ($ch['real_margin_percent'] ?? 0.0) : ($ch->real_margin_percent ?? 0.0);

                    $totOrders += $ordCount; $totGross += $gSales; $totDisc += $disc; $totNet += $nSales;
                    $totFee += $fAmount; $totPayout += $nPayout; $totHpp += $hpp; $totProfit += $rProfit;
                @endphp
                <tr>
                    <td><strong>{{ $chName }}</strong></td>
                    <td class="num">{{ number_format($ordCount, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($gSales, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($disc, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($nSales, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($fPercent, 1) }}%</td>
                    <td class="num" style="color: #DC2626;">-Rp {{ number_format($fAmount, 0, ',', '.') }}</td>
                    <td class="num" style="font-weight: 700; color: #007AFF;">Rp {{ number_format($nPayout, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($hpp, 0, ',', '.') }}</td>
                    <td class="num" style="font-weight: 700; color: #059669;">Rp {{ number_format($rProfit, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($rMargin, 1) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center; color: #94A3B8;">Tidak ada data saluran penjualan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>TOTAL KESELURUHAN</td>
                <td class="num">{{ number_format($totOrders, 0, ',', '.') }}</td>
                <td class="num">Rp {{ number_format($totGross, 0, ',', '.') }}</td>
                <td class="num">Rp {{ number_format($totDisc, 0, ',', '.') }}</td>
                <td class="num">Rp {{ number_format($totNet, 0, ',', '.') }}</td>
                <td class="num">-</td>
                <td class="num" style="color: #DC2626;">-Rp {{ number_format($totFee, 0, ',', '.') }}</td>
                <td class="num" style="color: #007AFF;">Rp {{ number_format($totPayout, 0, ',', '.') }}</td>
                <td class="num">Rp {{ number_format($totHpp, 0, ',', '.') }}</td>
                <td class="num" style="color: #059669;">Rp {{ number_format($totProfit, 0, ',', '.') }}</td>
                <td class="num">{{ $totNet > 0 ? number_format(($totProfit / $totNet) * 100, 1) : '0.0' }}%</td>
            </tr>
        </tfoot>
    </table>

    <!-- Two Column Section: Top Products & 3-Way Reconciliation -->
    <div class="two-cols">
        <div>
            <div class="section-heading">2. Top 10 Produk & Menu Terlaris</div>
            <table class="print-table">
                <thead>
                    <tr>
                        <th>Nama Produk / Menu</th>
                        <th class="num">Qty</th>
                        <th class="num">Total Omzet</th>
                        <th class="num">Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProducts as $p)
                        @php
                            $pName = is_array($p) ? ($p['product_name'] ?? 'Produk') : ($p->product_name ?? 'Produk');
                            $pQty = is_array($p) ? ($p['total_quantity'] ?? 0) : ($p->total_qty ?? $p->totalQuantity ?? 0);
                            $pSales = is_array($p) ? ($p['total_sales'] ?? 0) : ($p->net_sales ?? $p->netSales ?? 0);
                            $pMargin = is_array($p) ? ($p['margin_percent'] ?? 0) : ($p->grossMarginPercent ?? $p->margin_percent ?? 0);
                        @endphp
                        <tr>
                            <td>{{ Str::limit((string) $pName, 26) }}</td>
                            <td class="num">{{ number_format((float) $pQty, 0, ',', '.') }}</td>
                            <td class="num">Rp {{ number_format((float) $pSales, 0, ',', '.') }}</td>
                            <td class="num" style="color: #059669; font-weight: 600;">{{ number_format((float) $pMargin, 1) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align: center; color: #94A3B8;">Tidak ada data produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            <div class="section-heading">3. Rekonsiliasi Kas 3-Arah & Mutasi Pembayaran</div>
            <table class="print-table">
                <thead>
                    <tr>
                        <th>Metode Pembayaran</th>
                        <th class="num">Frekuensi</th>
                        <th class="num">Nominal Kas Masuk</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paymentMethods as $pm)
                        <tr>
                            <td>{{ $pm['method_label'] }}</td>
                            <td class="num">{{ number_format($pm['count'], 0, ',', '.') }}x</td>
                            <td class="num" style="font-weight: 600;">Rp {{ number_format($pm['amount'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align: center; color: #94A3B8;">Tidak ada mutasi kas.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td>Status Integritas Kas</td>
                        <td colspan="2" style="text-align: right; color: {{ $reconciliation->isBalanced ? '#059669' : '#DC2626' }};">
                            {{ $reconciliation->isBalanced ? 'BALANCE / COCOK (0 Selisih)' : 'SELISIH: Rp ' . number_format($reconciliation->orderPaymentDiscrepancy, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- TABEL 4: 20 Transaksi Terbaru dengan Info Kontekstual 20 Industri -->
    <div class="section-heading">4. Buku Besar Transaksi Terbaru (Sample Audit)</div>
    <table class="print-table">
        <thead>
            <tr>
                <th>No. Order</th>
                <th>Waktu</th>
                <th>Saluran</th>
                <th>Ref / Meja</th>
                <th>Metadata 20 Industri</th>
                <th>Pelanggan</th>
                <th class="num">Omzet</th>
                <th>Metode</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentTransactions as $tx)
                @php
                    $indTexts = [];
                    if (!empty($tx->vehicle_license_plate)) {
                        $indTexts[] = '🚗 ' . $tx->vehicle_license_plate . ($tx->vehicle_model ? ' (' . $tx->vehicle_model . ')' : '');
                    }
                    if ($tx->laundry_weight_kg > 0) {
                        $indTexts[] = '🧺 ' . $tx->laundry_weight_kg . ' kg' . ($tx->rack_location ? ' [' . $tx->rack_location . ']' : '');
                    }
                    if (empty($indTexts) && !empty($tx->notes)) {
                        $indTexts[] = '📝 ' . Str::limit($tx->notes, 30);
                    }
                    $indSummary = !empty($indTexts) ? implode(' | ', $indTexts) : '-';
                @endphp
                <tr>
                    <td style="font-weight: 700;">{{ $tx->order_number }}</td>
                    <td>{{ $tx->created_at?->format('d/m H:i') }}</td>
                    <td><span class="badge badge-{{ $tx->sales_channel === 'shopeefood' ? 'shopee' : ($tx->sales_channel === 'gofood' ? 'gofood' : ($tx->sales_channel === 'grabfood' ? 'grab' : 'pos')) }}">{{ strtoupper($tx->sales_channel ?? 'POS') }}</span></td>
                    <td>{{ $tx->external_order_ref ?: ($tx->table_or_reference ?: '-') }}</td>
                    <td>{{ $indSummary }}</td>
                    <td>{{ Str::limit($tx->customer?->name ?? ($tx->customer_name_guest ?: 'Umum'), 18) }}</td>
                    <td class="num" style="font-weight: 700;">Rp {{ number_format((float) $tx->total_amount, 0, ',', '.') }}</td>
                    <td>{{ $tx->payments->pluck('payment_method')->unique()->implode(', ') ?: '-' }}</td>
                    <td>{{ strtoupper($tx->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align: center; color: #94A3B8;">Tidak ada riwayat transaksi.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan Resmi -->
    <div class="signature-block">
        <div class="sig-box">
            <div class="sig-title">Kasir / Petugas Pelapor</div>
            <div class="sig-name">{{ auth()->user()?->name ?? 'Kasir Bertugas' }}</div>
            <div class="sig-role">Staf Operasional POS</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Supervisor / Audit Keuangan</div>
            <div class="sig-name">( ........................................ )</div>
            <div class="sig-role">Finance & Accounting Lead</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Pemilik Usaha / Direktur</div>
            <div class="sig-name">{{ $business->name }}</div>
            <div class="sig-role">Managing Director / Owner</div>
        </div>
    </div>
</div>

</body>
</html>
