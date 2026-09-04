<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengingat Masa Langganan Cooca</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #090d16; color: #e2e8f0; margin: 0; padding: 24px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #0f172a; border-radius: 16px; border: 1px solid #1e293b; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.6); }
        .header { background: linear-gradient(135deg, #d97706, #b45309); padding: 32px 32px 24px 32px; text-align: center; }
        .header .logo { height: 32px; margin-bottom: 12px; }
        .header h1 { margin: 0; color: #ffffff; font-size: 22px; font-weight: 800; }
        .header p { margin: 4px 0 0 0; color: #fef3c7; font-size: 13px; }
        .content { padding: 32px; }
        .warning-box { background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; text-align: center; }
        .warning-box h3 { margin: 0 0 4px 0; color: #fbbf24; font-size: 16px; font-weight: 700; }
        .warning-box p { margin: 0; color: #cbd5e1; font-size: 13px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; background-color: #1e293b; border-radius: 12px; border: 1px solid #334155; }
        .info-table td { padding: 12px 16px; font-size: 14px; border-bottom: 1px solid #334155; }
        .info-table td.label { color: #94a3b8; width: 45%; }
        .info-table td.val { color: #f8fafc; font-weight: 600; text-align: right; }
        .btn { display: block; width: 100%; box-sizing: border-box; text-align: center; background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; padding: 14px 24px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 15px; margin-top: 16px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
        .footer { padding: 24px 32px; background-color: #090d16; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #1e293b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://cooca.id/assets/image/1785229034_logo_dark.png" alt="Cooca Logo" class="logo">
            <h1>Pengingat Masa Langganan</h1>
            <p>Masa Aktif Paket Berakhir dalam {{ $daysRemaining }} Hari</p>
        </div>
        <div class="content">
            <div class="warning-box">
                <h3>Jangan Biarkan Operasional Bisnis Terhenti!</h3>
                <p>Masa aktif paket <strong>{{ $subscription->plan_code }}</strong> untuk usaha <strong>{{ $subscription->business?->name }}</strong> akan segera berakhir.</p>
            </div>

            <table class="info-table">
                <tr>
                    <td class="label">Nama Usaha</td>
                    <td class="val">{{ $subscription->business?->name }}</td>
                </tr>
                <tr>
                    <td class="label">Status Saat Ini</td>
                    <td class="val" style="color: #34d399;">Aktif (Cooca UMKM)</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Berakhir</td>
                    <td class="val" style="color: #fbbf24; font-weight: 700;">
                        {{ $subscription->ends_at ? $subscription->ends_at->translatedFormat('d F Y') : '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Sisa Waktu</td>
                    <td class="val" style="color: #f87171; font-weight: 700;">
                        {{ $daysRemaining }} Hari Lagi
                    </td>
                </tr>
            </table>

            <p style="font-size: 13px; color: #94a3b8; line-height: 1.5; margin-bottom: 20px;">
                Perpanjang paket langganan Anda sekarang untuk memastikan akses fitur tanpa batas katalog, transaksi POS tak terbatas, multi-gudang, dan sinkronisasi laporan keuangan tetap berjalan lancar.
            </p>

            <a href="{{ route('billing.packages') }}" class="btn">
                Perpanjang Langganan Sekarang
            </a>
        </div>
        <div class="footer">
            <p style="margin: 0 0 6px 0;">Cooca Engine — Platform Manajemen & Keuangan UMKM Indonesia</p>
            <p style="margin: 0;">Email ini dikirimkan otomatis sebagai pengingat layanan Anda.</p>
        </div>
    </div>
</body>
</html>
