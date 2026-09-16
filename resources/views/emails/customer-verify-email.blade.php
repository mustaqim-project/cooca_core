<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Alamat Email Anda</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #F2F2F7;
            color: #1C1C1E;
            margin: 0;
            padding: 24px;
        }

        .container {
            max-width: 540px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border-radius: 20px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .header {
            background: linear-gradient(135deg, #007AFF, #5856D6);
            padding: 32px 28px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            color: #FFFFFF;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .header p {
            margin: 6px 0 0 0;
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
        }

        .body {
            padding: 32px 28px;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            background-color: #007AFF;
            color: #FFFFFF !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 15px;
            text-align: center;
            margin: 20px 0;
        }

        .footer {
            padding: 20px 28px;
            background-color: #F8F9FA;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            text-align: center;
            font-size: 12px;
            color: #8E8E93;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Cooca</h1>
            <p>Verifikasi Alamat Email Akun Pelanggan</p>
        </div>
        <div class="body">
            <p>Halo <strong>{{ $customer->name }}</strong>,</p>
            <p>Terima kasih telah mendaftarkan akun di platform belanja Cooca. Untuk memastikan keamanan akun Anda dan
                menerima informasi status pesanan, silakan konfirmasi alamat email Anda dengan menekan tombol di bawah
                ini:</p>

            <div style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="btn">Verifikasi Alamat Email</a>
            </div>

            <p style="font-size: 13px; color: #636366;">Tautan verifikasi ini berlaku selama 60 menit. Jika Anda tidak
                merasa mendaftar di COOCA, abaikan email ini.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Cooca Ecosystem. Hak Cipta Dilindungi.
        </div>
    </div>
</body>

</html>
