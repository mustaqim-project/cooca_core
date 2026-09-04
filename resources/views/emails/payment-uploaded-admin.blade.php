<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Pembayaran Masuk</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f172a; color: #e2e8f0; margin: 0; padding: 24px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #1e293b; border-radius: 16px; border: 1px solid #334155; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        .header { background: linear-gradient(135deg, #059669, #0d9488); padding: 24px 32px; text-align: center; }
        .header h1 { margin: 0; color: #ffffff; font-size: 20px; font-weight: 700; }
        .content { padding: 32px; }
        .badge { display: inline-block; background-color: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.4); padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: 600; margin-bottom: 16px; }
        .table-info { width: 100%; border-collapse: collapse; margin-top: 16px; margin-bottom: 24px; }
        .table-info td { padding: 10px 0; border-bottom: 1px solid #334155; font-size: 14px; }
        .table-info td.label { color: #94a3b8; width: 40%; }
        .table-info td.val { color: #f8fafc; font-weight: 600; }
        .btn { display: inline-block; width: 100%; box-sizing: border-box; text-align: center; background-color: #10b981; color: #ffffff; padding: 14px 24px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 14px; margin-top: 12px; }
        .footer { padding: 20px 32px; background-color: #0f172a; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #334155; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Notifikasi Pembayaran Masuk</h1>
        </div>
        <div class="content">
            <div class="badge">Menunggu Verifikasi Admin</div>
            <p style="font-size: 15px; margin-top: 0; line-height: 1.5;">
                Halo <strong>Administrator</strong>,<br>
                Bisnis <strong>{{ $payment->business?->name }}</strong> baru saja mengunggah bukti pembayaran untuk paket langganan / top-up.
            </p>

            <table class="table-info">
                <tr>
                    <td class="label">ID Pembayaran</td>
                    <td class="val">#{{ substr($payment->id, 0, 8) }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Bisnis</td>
                    <td class="val">{{ $payment->business?->name }}</td>
                </tr>
                <tr>
                    <td class="label">Pemilik / Kontak</td>
                    <td class="val">{{ $payment->business?->users()->first()?->name ?? '-' }} ({{ $payment->business?->phone ?? $payment->business?->users()->first()?->phone ?? '-' }})</td>
                </tr>
                <tr>
                    <td class="label">Paket / Layanan</td>
                    <td class="val">{{ $payment->billingPackage?->name ?? strtoupper($payment->payment_type) }}</td>
                </tr>
                <tr>
                    <td class="label">Total Nominal</td>
                    <td class="val" style="color: #34d399; font-size: 16px;">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Bank Pengirim</td>
                    <td class="val">{{ $payment->sender_bank ?? '-' }} a.n {{ $payment->sender_account_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Waktu Upload</td>
                    <td class="val">{{ $payment->proof_uploaded_at ? $payment->proof_uploaded_at->translatedFormat('d F Y, H:i') : now()->translatedFormat('d F Y, H:i') }} WIB</td>
                </tr>
            </table>

            <a href="{{ url('/admin/billing-packages/payments') }}" class="btn">
                Buka Panel Admin & Review Bukti Transfer
            </a>
        </div>
        <div class="footer">
            Email ini dikirim otomatis oleh Sistem Cooca Core Engine.
        </div>
    </div>
</body>
</html>
