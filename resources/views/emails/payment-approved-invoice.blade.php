<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice & Bukti Pembayaran Cooca</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #090d16; color: #e2e8f0; margin: 0; padding: 24px; }
        .container { max-width: 620px; margin: 0 auto; background-color: #0f172a; border-radius: 16px; border: 1px solid #1e293b; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.6); }
        .header { background: linear-gradient(135deg, #059669, #0d9488); padding: 32px 32px 24px 32px; text-align: center; }
        .header .logo { height: 32px; margin-bottom: 12px; }
        .header h1 { margin: 0; color: #ffffff; font-size: 22px; font-weight: 800; }
        .header p { margin: 4px 0 0 0; color: #d1fae5; font-size: 13px; }
        .content { padding: 32px; }
        .success-box { background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; text-align: center; }
        .success-box h3 { margin: 0 0 4px 0; color: #34d399; font-size: 16px; font-weight: 700; }
        .success-box p { margin: 0; color: #94a3b8; font-size: 13px; }
        .invoice-card { background-color: #1e293b; border-radius: 12px; border: 1px solid #334155; padding: 20px 24px; margin-bottom: 24px; }
        .invoice-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #334155; font-size: 13px; }
        .invoice-row:last-child { border-bottom: none; }
        .invoice-label { color: #94a3b8; }
        .invoice-value { color: #f8fafc; font-weight: 600; text-align: right; }
        .total-row { padding-top: 12px; margin-top: 8px; border-top: 2px solid #334155; display: flex; justify-content: space-between; font-size: 16px; font-weight: 700; color: #ffffff; }
        .total-amount { color: #34d399; font-size: 18px; }
        .btn { display: block; width: 100%; box-sizing: border-box; text-align: center; background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; padding: 14px 24px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 15px; margin-top: 16px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
        .footer { padding: 24px 32px; background-color: #090d16; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #1e293b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://cooca.id/assets/image/1785229034_logo_dark.png" alt="Cooca Logo" class="logo">
            <h1>Pembayaran Berhasil Diverifikasi</h1>
            <p>Kwitansi & Faktur Resmi Langganan Cooca</p>
        </div>
        <div class="content">
            <div class="success-box">
                <h3>Terima Kasih atas Kepercayaan Anda!</h3>
                <p>Paket langganan bisnis Anda telah aktif dan siap digunakan secara penuh.</p>
            </div>

            <div class="invoice-card">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="border-bottom: 1px solid #334155;">
                        <td style="padding: 8px 0; color: #94a3b8; font-size: 13px;">Nomor Invoice</td>
                        <td style="padding: 8px 0; color: #f8fafc; font-weight: 700; text-align: right; font-size: 13px;">
                            INV-{{ strtoupper(substr($payment->id, 0, 8)) }}
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #334155;">
                        <td style="padding: 8px 0; color: #94a3b8; font-size: 13px;">Nama Bisnis</td>
                        <td style="padding: 8px 0; color: #f8fafc; font-weight: 600; text-align: right; font-size: 13px;">
                            {{ $payment->business?->name }}
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #334155;">
                        <td style="padding: 8px 0; color: #94a3b8; font-size: 13px;">Layanan / Paket</td>
                        <td style="padding: 8px 0; color: #34d399; font-weight: 700; text-align: right; font-size: 13px;">
                            {{ $payment->billingPackage?->name ?? strtoupper($payment->payment_type) }}
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #334155;">
                        <td style="padding: 8px 0; color: #94a3b8; font-size: 13px;">Tanggal Pembayaran</td>
                        <td style="padding: 8px 0; color: #f8fafc; font-weight: 600; text-align: right; font-size: 13px;">
                            {{ $payment->approved_at ? $payment->approved_at->translatedFormat('d F Y, H:i') : now()->translatedFormat('d F Y, H:i') }} WIB
                        </td>
                    </tr>
                    @php
                        $sub = $payment->business?->subscription;
                    @endphp
                    @if ($sub && $sub->ends_at)
                    <tr style="border-bottom: 1px solid #334155;">
                        <td style="padding: 8px 0; color: #94a3b8; font-size: 13px;">Masa Berlaku Hingga</td>
                        <td style="padding: 8px 0; color: #fbbf24; font-weight: 700; text-align: right; font-size: 13px;">
                            {{ $sub->ends_at->translatedFormat('d F Y') }}
                        </td>
                    </tr>
                    @endif
                    <tr style="border-top: 2px solid #475569;">
                        <td style="padding: 14px 0 6px 0; color: #ffffff; font-size: 15px; font-weight: 700;">Total Dibayar</td>
                        <td style="padding: 14px 0 6px 0; color: #34d399; font-size: 18px; font-weight: 800; text-align: right;">
                            Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </div>

            <a href="{{ route('dashboard') }}" class="btn">
                Masuk ke Workspace Bisnis Anda
            </a>
        </div>
        <div class="footer">
            <p style="margin: 0 0 6px 0;">Cooca Engine — Platform Manajemen & Keuangan UMKM Indonesia</p>
            <p style="margin: 0;">Jika ada pertanyaan seputar tagihan, silakan hubungi tim support kami.</p>
        </div>
    </div>
</body>
</html>
