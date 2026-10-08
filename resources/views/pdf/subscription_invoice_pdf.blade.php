<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Kwitansi & Invoice {{ $invoiceNo }} - Cooca</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }
        * {
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        body {
            font-size: 11px;
            color: #1e293b;
            line-height: 1.45;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table {
            margin-bottom: 16px;
            border-bottom: 2px solid #059669;
            padding-bottom: 14px;
        }
        .brand-logo {
            font-size: 24px;
            font-weight: 900;
            color: #059669;
            letter-spacing: -0.5px;
            margin: 0;
        }
        .brand-sub {
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
            line-height: 1.3;
        }
        .doc-title-main {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            text-align: right;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title-sub {
            font-size: 10px;
            color: #64748b;
            text-align: right;
            margin-top: 2px;
            font-weight: 600;
        }
        .paid-stamp {
            display: inline-block;
            margin-top: 6px;
            padding: 3px 12px;
            border: 1.5px solid #059669;
            background-color: #ecfdf5;
            color: #059669;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-radius: 4px;
        }
        .parties-table {
            margin-bottom: 18px;
        }
        .party-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            vertical-align: top;
        }
        .party-title {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 800;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 3px;
        }
        .party-name {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .party-meta {
            font-size: 10px;
            color: #475569;
            line-height: 1.35;
        }
        .items-table {
            width: 100%;
            margin-bottom: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }
        .items-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 8px 10px;
            border-bottom: 1px solid #cbd5e1;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
            color: #334155;
            vertical-align: top;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .item-main-title {
            font-weight: 700;
            color: #0f172a;
            font-size: 12px;
            margin-bottom: 3px;
        }
        .item-desc {
            font-size: 10px;
            color: #64748b;
            line-height: 1.3;
        }
        .summary-wrapper {
            margin-bottom: 16px;
        }
        .terbilang-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 10px 12px;
            color: #166534;
            font-size: 10px;
            line-height: 1.4;
        }
        .terbilang-label {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            color: #15803d;
            margin-bottom: 2px;
        }
        .calculation-table td {
            padding: 4px 6px;
            font-size: 11px;
        }
        .calc-label {
            color: #64748b;
            text-align: right;
        }
        .calc-val {
            color: #0f172a;
            font-weight: 600;
            text-align: right;
            width: 110px;
        }
        .calc-total td {
            padding-top: 8px;
            border-top: 2px solid #0f172a;
            font-size: 13px;
            font-weight: 800;
        }
        .calc-total .calc-total-val {
            color: #059669;
            font-size: 14px;
        }
        .payment-meta-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 20px;
        }
        .payment-meta-table td {
            padding: 3px 6px;
            font-size: 10px;
        }
        .guarantee-box {
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            margin-top: 10px;
        }
        .seal-table td {
            vertical-align: middle;
        }
        .legal-note {
            font-size: 9px;
            color: #64748b;
            line-height: 1.4;
        }
        .signature-box {
            text-align: right;
        }
        .signature-title {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .signature-name {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            border-top: 1px solid #0f172a;
            display: inline-block;
            padding-top: 3px;
            margin-top: 36px;
        }
        .footer-note {
            margin-top: 16px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px dashed #cbd5e1;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="brand-logo">COOCA</div>
                <div class="brand-sub">
                    <strong>PT Cooca Digital Solusindo</strong><br>
                    Platform Manajemen & Sistem Operasi Bisnis UMKM Indonesia<br>
                    Website: <a href="https://cooca.id" style="color: #059669; text-decoration: none;">cooca.id</a> &bull; Email: billing@cooca.id
                </div>
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <div class="doc-title-main">Kwitansi & Faktur Resmi</div>
                <div class="doc-title-sub">BUKTI PEMBAYARAN SAH ELEKTRONIK</div>
                <div>
                    <span class="paid-stamp">&bull; LUNAS / TERVERIFIKASI &bull;</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Parties & Metadata -->
    <table class="parties-table">
        <tr>
            <td style="width: 48%; vertical-align: top;">
                <div class="party-card">
                    <div class="party-title">DITAGIHKAN KEPADA (PELANGGAN)</div>
                    <div class="party-name">{{ $payment->business?->name ?? 'Mitra Usaha Cooca' }}</div>
                    <div class="party-meta">
                        @php
                            $ownerUser = $payment->user ?? $payment->business?->users()->first();
                        @endphp
                        <strong>PIC:</strong> {{ $ownerUser?->name ?? 'Pemilik Bisnis' }}<br>
                        <strong>Email:</strong> {{ $ownerUser?->email ?? ($payment->business?->email ?? '-') }}<br>
                        @if($payment->business?->phone)
                            <strong>No. Telepon:</strong> {{ $payment->business->phone }}<br>
                        @endif
                        @if($payment->business?->address)
                            <strong>Alamat:</strong> {{ $payment->business->address }}
                        @endif
                    </div>
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; vertical-align: top;">
                <div class="party-card">
                    <div class="party-title">INFORMASI TAGIHAN & TRANSAKSI</div>
                    <div class="party-meta" style="line-height: 1.55;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 42%; color: #64748b; font-weight: 600;">No. Invoice:</td>
                                <td style="color: #0f172a; font-weight: 800;">{{ $invoiceNo }}</td>
                            </tr>
                            <tr>
                                <td style="color: #64748b; font-weight: 600;">No. Pesanan:</td>
                                <td style="color: #0f172a; font-weight: 700;">{{ $payment->order_number }}</td>
                            </tr>
                            <tr>
                                <td style="color: #64748b; font-weight: 600;">Tgl. Terbit:</td>
                                <td style="color: #0f172a;">{{ ($payment->created_at ?? now())->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td style="color: #64748b; font-weight: 600;">Tgl. Lunas:</td>
                                <td style="color: #059669; font-weight: 700;">{{ ($payment->approved_at ?? ($payment->updated_at ?? now()))->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 55%; text-align: left;">Deskripsi Layanan & Fasilitas</th>
                <th style="width: 18%; text-align: center;">Periode / Siklus</th>
                <th style="width: 22%; text-align: right;">Jumlah Tagihan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: 700;">1</td>
                <td>
                    <div class="item-main-title">
                        Langganan {{ $payment->billingPackage?->name ?? ($payment->package_name ?? 'Layanan Cooca Pro') }}
                    </div>
                    <div class="item-desc">
                        Akses modul Point of Sale (POS), Manajemen Inventori & Gudang Terpadu, Modul Pembelian (PO), Penjualan (SO), Faktur/Invoice, Laporan Keuangan, dan Pengelolaan Hak Akses Multi-User.
                        @php
                            $sub = $payment->business?->subscription;
                        @endphp
                        @if($sub && $sub->ends_at)
                            <br><span style="color: #059669; font-weight: 600;">Masa Aktif: s/d {{ $sub->ends_at->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}</span>
                        @endif
                    </div>
                </td>
                <td style="text-align: center; color: #475569;">
                    {{ $payment->package_duration_days ? $payment->package_duration_days . ' Hari' : ($payment->billing_cycle === 'annual' ? '1 Tahun' : '1 Bulan') }}
                </td>
                <td style="text-align: right; font-weight: 700; color: #0f172a;">
                    Rp {{ number_format((float) ($payment->package_price ?? $payment->amount), 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Calculation & Terbilang -->
    <table class="summary-wrapper">
        <tr>
            <td style="width: 52%; vertical-align: top;">
                <div class="terbilang-box">
                    <div class="terbilang-label">Terbilang:</div>
                    <div style="font-style: italic; font-weight: 600;">
                        # {{ $terbilang }} Rupiah #
                    </div>
                </div>
                
                <div style="margin-top: 10px; font-size: 10px; color: #64748b; line-height: 1.4;">
                    <strong>Metode Pembayaran:</strong> 
                    <span style="color: #0f172a; font-weight: 600;">{{ $paymentMethodName }}</span><br>
                    @if($payment->gateway_reference)
                        <strong>ID Referensi Transaksi:</strong> <code style="font-size: 9px; color: #334155;">{{ $payment->gateway_reference }}</code><br>
                    @endif
                    <strong>Status Verifikasi:</strong> <span style="color: #059669; font-weight: 700;">OTOMATIS / SISTEM VERIFIED</span>
                </div>
            </td>
            <td style="width: 6%;"></td>
            <td style="width: 42%; vertical-align: top;">
                <table class="calculation-table">
                    <tr>
                        <td class="calc-label">Subtotal:</td>
                        <td class="calc-val">Rp {{ number_format((float) ($payment->package_price ?? $payment->amount), 0, ',', '.') }}</td>
                    </tr>
                    @if(($payment->discount_amount ?? 0) > 0)
                        <tr>
                            <td class="calc-label" style="color: #dc2626;">Diskon Promo ({{ $payment->promo_code ?? 'VOUCHER' }}):</td>
                            <td class="calc-val" style="color: #dc2626;">- Rp {{ number_format((float) $payment->discount_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if(($payment->gateway_fee ?? 0) > 0)
                        <tr>
                            <td class="calc-label">Biaya Transaksi / Gateway:</td>
                            <td class="calc-val">Rp {{ number_format((float) $payment->gateway_fee, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr class="calc-total">
                        <td class="calc-label" style="color: #0f172a; font-size: 12px;">Total Pembayaran:</td>
                        <td class="calc-total-val">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Guarantee & Legal Disclaimer -->
    <div class="guarantee-box">
        <table class="seal-table">
            <tr>
                <td style="width: 65%; vertical-align: top;">
                    <div class="legal-note">
                        <strong>PERNYATAAN KEABSAHAN HUKUM:</strong><br>
                        Dokumen ini adalah bukti pembayaran dan faktur resmi (Electronic Invoice & Official Receipt) yang diterbitkan secara sah oleh sistem Cooca Platform. Sesuai dengan ketentuan Undang-Undang Republik Indonesia No. 11 Tahun 2008 tentang Informasi dan Transaksi Elektronik (UU ITE) Pasal 5 Ayat 1, dokumen elektronik ini memiliki kekuatan hukum yang sah dan tidak memerlukan tanda tangan basah.
                    </div>
                </td>
                <td style="width: 35%; vertical-align: top; text-align: right;">
                    <div class="signature-box">
                        <div class="signature-title">Diterbitkan Secara Digital Oleh:</div>
                        <div style="margin: 4px 0;">
                            <span style="font-weight: 800; font-size: 11px; color: #059669; letter-spacing: 0.5px;">[ COOCA AUTOMATED BILLING ]</span>
                        </div>
                        <div class="signature-name">Finance & Billing Department</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer Note -->
    <div class="footer-note">
        Dicetak otomatis pada {{ now()->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s') }} WIB &bull; Cooca Engine Cloud SaaS &bull; Hubungi kami di support@cooca.id untuk informasi billing lebih lanjut.
    </div>

</body>
</html>
