<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ringkasan Harian Penjualan POS - {{ $business->name }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #F5F5F7;
            color: #1D1D1F;
            margin: 0;
            padding: 24px 12px;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 620px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border-radius: 20px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            overflow: hidden;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.06);
        }

        .header {
            background: linear-gradient(135deg, #007AFF, #5856D6);
            padding: 32px 28px 24px 28px;
            text-align: center;
            color: #FFFFFF;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .header p {
            margin: 6px 0 0 0;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        .content {
            padding: 28px 24px;
        }

        .greeting {
            font-size: 14px;
            line-height: 1.5;
            color: #333336;
            margin-bottom: 20px;
        }

        .bento-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }

        .bento-card {
            background-color: #F8F9FA;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 14px;
            padding: 16px;
        }

        .bento-card.highlight {
            background-color: #EFF6FF;
            border-color: #BFDBFE;
        }

        .bento-card.success {
            background-color: #ECFDF5;
            border-color: #A7F3D0;
        }

        .bento-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6E6E73;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .bento-val {
            font-size: 18px;
            font-weight: 800;
            color: #1D1D1F;
            font-feature-settings: "tnum";
        }

        .bento-sub {
            font-size: 11px;
            color: #86868B;
            margin-top: 2px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6E6E73;
            margin: 24px 0 12px 0;
            border-bottom: 1px solid #E5E5EA;
            padding-bottom: 6px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .data-table th {
            text-align: left;
            padding: 8px 10px;
            background-color: #F8F9FA;
            color: #6E6E73;
            font-weight: 600;
            border-bottom: 1px solid #E5E5EA;
        }

        .data-table td {
            padding: 10px;
            border-bottom: 1px solid #F2F2F7;
            color: #1D1D1F;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: 700;
        }

        .text-emerald {
            color: #059669;
        }

        .text-blue {
            color: #007AFF;
        }

        .btn-cta {
            display: block;
            background-color: #007AFF;
            color: #FFFFFF !important;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            text-align: center;
            padding: 14px 24px;
            border-radius: 12px;
            margin: 28px 0 12px 0;
            box-shadow: 0 4px 14px rgba(0, 122, 255, 0.3);
        }

        .footer {
            background-color: #F8F9FA;
            padding: 20px 24px;
            text-align: center;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            font-size: 11px;
            color: #86868B;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>{{ $business->name }}</h1>
            <p>Ringkasan Eksekutif Penjualan POS & Saluran Digital</p>
        </div>

        <div class="content">
            <div class="greeting">
                Yth. Bapak/Ibu <strong>{{ $recipientName ?? 'Pemilik Usaha' }}</strong>,<br>
                Berikut adalah rekapitulasi performa penjualan kasir POS dan saluran online pada tanggal <strong>{{ $dateFormatted }}</strong>:
            </div>

            <!-- 4-Card Bento Grid -->
            <div class="bento-grid">
                <div class="bento-card highlight">
                    <div class="bento-label">Omzet Bersih</div>
                    <div class="bento-val text-blue">Rp {{ number_format($kpi->netSales, 0, ',', '.') }}</div>
                    <div class="bento-sub">Bruto: Rp {{ number_format($kpi->grossSales, 0, ',', '.') }}</div>
                </div>
                <div class="bento-card">
                    <div class="bento-label">Total Pesanan</div>
                    <div class="bento-val">{{ number_format($kpi->totalOrders, 0, ',', '.') }}</div>
                    <div class="bento-sub">AOV: Rp {{ number_format($kpi->averageOrderValue, 0, ',', '.') }}</div>
                </div>
                <div class="bento-card">
                    <div class="bento-label">Beban Pokok (HPP)</div>
                    <div class="bento-val">Rp {{ number_format($kpi->totalHpp, 0, ',', '.') }}</div>
                    <div class="bento-sub">Modal Produk Terjual</div>
                </div>
                <div class="bento-card success">
                    <div class="bento-label">Laba Kotor & Margin</div>
                    <div class="bento-val text-emerald">Rp {{ number_format($kpi->grossProfit, 0, ',', '.') }}</div>
                    <div class="bento-sub" style="font-weight: 700; color: #059669;">Margin: {{ number_format($kpi->grossMarginPercent, 1) }}%</div>
                </div>
            </div>

            <!-- Saluran Penjualan & Ojol Breakdown -->
            @if(isset($channels) && $channels->isNotEmpty())
                <div class="section-title">Performa Saluran Jual & Ojol Settlement</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Saluran</th>
                            <th class="text-right">Order</th>
                            <th class="text-right">Omzet</th>
                            <th class="text-right">Net Payout</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($channels as $chan)
                            <tr>
                                <td class="font-bold">{{ $chan->channel_label ?? $chan->channel }}</td>
                                <td class="text-right">{{ $chan->order_count }}</td>
                                <td class="text-right">Rp {{ number_format($chan->gross_sales, 0, ',', '.') }}</td>
                                <td class="text-right font-bold text-blue">Rp {{ number_format($chan->net_merchant_payout, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <!-- Top 5 Produk Terlaris -->
            @if(isset($topProducts) && $topProducts->isNotEmpty())
                <div class="section-title">Top Produk Terlaris</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Menu / Produk</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProducts->take(5) as $prod)
                            <tr>
                                <td>{{ $prod->product_name }}</td>
                                <td class="text-right">{{ number_format($prod->total_qty, 0, ',', '.') }}</td>
                                <td class="text-right font-bold">Rp {{ number_format($prod->total_sales, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <a href="{{ $dashboardUrl }}" class="btn-cta">
                Buka Laporan POS Lengkap &rarr;
            </a>
        </div>

        <div class="footer">
            Email ini dihasilkan otomatis oleh sistem <strong>COOCA ID Platform</strong>.<br>
            Multi-Tenant Business Operating System &bull; &copy; {{ date('Y') }} COOCA Indonesia.
        </div>
    </div>
</body>

</html>
