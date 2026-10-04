<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Tagihan Faktur</title>
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
            max-width: 600px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border-radius: 20px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            overflow: hidden;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.06);
        }

        .header {
            background: linear-gradient(135deg, #007AFF, #5856D6);
            padding: 36px 32px 28px 32px;
            text-align: center;
            color: #FFFFFF;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
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
            padding: 32px 28px;
        }

        .greeting {
            font-size: 15px;
            line-height: 1.5;
            color: #333336;
            margin-bottom: 24px;
        }

        .hero-bento {
            background-color: #F8F9FA;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 24px;
            text-align: center;
        }

        .hero-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #86868B;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .hero-amount {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #1D1D1F;
            font-variant-numeric: tabular-nums;
            margin: 4px 0 10px 0;
        }

        .status-badge {
            display: inline-block;
            font-size: 11.5px;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 100px;
            letter-spacing: -0.01em;
        }

        .badge-upcoming {
            background-color: #FFF3E0;
            color: #E65100;
        }

        .badge-today {
            background-color: #E3F2FD;
            color: #0D47A1;
        }

        .badge-overdue {
            background-color: #FFEBEE;
            color: #C62828;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 13.5px;
        }

        .detail-table tr {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .detail-table tr:last-child {
            border-bottom: none;
        }

        .detail-table td {
            padding: 11px 0;
        }

        .detail-table .label {
            color: #86868B;
            font-weight: 500;
            width: 42%;
        }

        .detail-table .value {
            color: #1D1D1F;
            font-weight: 600;
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .bank-box {
            background: #F2F7FF;
            border: 1px solid #D0E2FF;
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 28px;
            font-size: 13px;
        }

        .bank-box-title {
            font-weight: 700;
            color: #0043CE;
            margin-bottom: 6px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .action-container {
            text-align: center;
            margin: 32px 0 16px 0;
        }

        .btn-primary {
            display: inline-block;
            background-color: #007AFF;
            color: #FFFFFF !important;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            padding: 13px 28px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 122, 255, 0.25);
        }

        .footer {
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            padding: 24px 28px;
            text-align: center;
            font-size: 11.5px;
            color: #86868B;
            line-height: 1.6;
            background-color: #FAFAFA;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $business->name }}</h1>
            <p>Pemberitahuan Status Tagihan Faktur Penjualan</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Yth. <strong>{{ $customer->name }}</strong>{{ $customer->company_name ? " ({$customer->company_name})" : '' }},
                <br><br>
                Kami menginformasikan status tagihan faktur pembelian/layanan Anda pada <strong>{{ $business->name }}</strong> sebagai berikut:
            </div>

            <!-- Bento Highlight -->
            <div class="hero-bento">
                <div class="hero-label">Sisa Tagihan Belum Lunas</div>
                <div class="hero-amount">Rp {{ number_format((float) $invoice->balance_due, 0, ',', '.') }}</div>

                @if ($reminderType === 'upcoming_h3')
                    <span class="status-badge badge-upcoming">Jatuh Tempo dalam 3 Hari ({{ $invoice->due_date?->format('d M Y') }})</span>
                @elseif ($reminderType === 'due_date')
                    <span class="status-badge badge-today">Jatuh Tempo Hari Ini ({{ $invoice->due_date?->format('d M Y') }})</span>
                @elseif ($reminderType === 'overdue')
                    <span class="status-badge badge-overdue">Lewat Jatuh Tempo ({{ $invoice->due_date?->format('d M Y') }})</span>
                @else
                    <span class="status-badge badge-today">Tagihan Aktif ({{ $invoice->due_date?->format('d M Y') }})</span>
                @endif
            </div>

            <!-- Table Details -->
            <table class="detail-table">
                <tr>
                    <td class="label">Nomor Faktur</td>
                    <td class="value">#{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Faktur</td>
                    <td class="value">{{ $invoice->invoice_date?->format('d M Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Jatuh Tempo</td>
                    <td class="value">{{ $invoice->due_date?->format('d M Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Termin Pembayaran</td>
                    <td class="value">{{ $invoice->payment_terms ?? "Net {$customer->payment_terms_days} Hari" }}</td>
                </tr>
                <tr>
                    <td class="label">Total Nilai Tagihan</td>
                    <td class="value">Rp {{ number_format((float) $invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Sudah Dibayar</td>
                    <td class="value">Rp {{ number_format((float) $invoice->paid_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Sisa Pembayaran</td>
                    <td class="value" style="color: #FF3B30; font-weight: 700;">
                        Rp {{ number_format((float) $invoice->balance_due, 0, ',', '.') }}
                    </td>
                </tr>
            </table>

            <!-- Rekening Pembayaran Resmi -->
            @php
                $bankDetails = $invoice->bank_details_snapshot;
                if (! is_array($bankDetails)) {
                    $bankDetails = json_decode($bankDetails ?: '[]', true) ?: [];
                }
            @endphp

            @if (! empty($bankDetails))
                <div class="bank-box">
                    <div class="bank-box-title">Rekening Resmi Pembayaran Toko</div>
                    @foreach ($bankDetails as $bank)
                        <div>
                            • <strong>{{ $bank['bank_name'] ?? 'Bank' }}</strong>:
                            <span style="font-family: monospace; font-weight: bold; letter-spacing: 0.05em;">{{ $bank['account_number'] ?? '' }}</span>
                            a.n {{ $bank['account_name'] ?? $business->name }}
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($customNotes)
                <div style="background-color: #F8F9FA; border-left: 3px solid #007AFF; padding: 12px 16px; border-radius: 8px; font-size: 13px; color: #555; margin-bottom: 24px;">
                    <strong>Catatan:</strong> {{ $customNotes }}
                </div>
            @endif

            <!-- Button CTA -->
            <div class="action-container">
                <a href="{{ $invoiceUrl }}" class="btn-primary" target="_blank">
                    Lihat Dokumen Faktur Resmi
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div><strong>{{ $business->name }}</strong></div>
            @if ($business->address)
                <div>{{ $business->address }}</div>
            @endif
            @if ($business->phone)
                <div>Hubungi Kami / WhatsApp: {{ $business->phone }}</div>
            @endif
            <div style="margin-top: 10px; font-size: 10.5px; color: #A1A1A6;">
                Pesan ini dikirim secara otomatis oleh sistem pencatatan Cooca atas nama {{ $business->name }}. Jika Anda telah melakukan pembayaran, mohon abaikan pesan ini atau kirimkan bukti transfer kepada kami.
            </div>
        </div>
    </div>
</body>

</html>
