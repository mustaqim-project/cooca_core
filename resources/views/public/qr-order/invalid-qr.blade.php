<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>QR Code Tidak Valid — COOCA POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', -apple-system, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[#F2F2F7] flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-white rounded-[24px] border border-black/10 p-7 text-center shadow-[0_12px_40px_rgba(0,0,0,0.06)] space-y-4">
        <div class="w-14 h-14 rounded-full bg-[#FF9500]/15 text-[#FF9500] flex items-center justify-center mx-auto">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
        </div>

        <h1 class="text-lg font-bold text-black tracking-tight">QR Meja Tidak Valid</h1>
        <p class="text-xs text-black/60 leading-relaxed">
            {{ $message ?? 'Kode QR meja tidak ditemukan, sudah kedaluwarsa, atau meja sedang dinonaktifkan.' }}
        </p>

        <div class="p-3.5 rounded-[12px] bg-black/[0.03] border border-black/5 text-[11px] text-black/60 leading-relaxed">
            Silakan panggil staff atau kasir restoran untuk mendapatkan QR Code meja terbaru atau memesan langsung di kasir.
        </div>

        <div class="pt-2 text-[10px] text-black/35 font-medium tracking-wide">
            COOCA POS • Self-Ordering System
        </div>
    </div>
</body>
</html>
