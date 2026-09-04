<!DOCTYPE html>
<html lang="id" class="dark h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email Anda — Cooca UMKM</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: { 500: '#10b981', 600: '#059669' }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-card {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex items-center justify-center p-4 sm:p-6 font-sans">

    <div class="w-full max-w-md space-y-6">

        <!-- Logo & Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 via-teal-400 to-cyan-400 p-0.5 shadow-xl shadow-emerald-500/20">
                <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center">
                    <i data-lucide="mail-check" class="w-7 h-7 text-emerald-400"></i>
                </div>
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Verifikasi Email Anda</h2>
            <p class="text-xs text-slate-400">Terima kasih telah mendaftar di Cooca UMKM!</p>
        </div>

        <!-- Card Content -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-5">

            <p class="text-xs text-slate-300 leading-relaxed">
                Sebelum memulai, silakan periksa kotak masuk (atau folder spam) pada email <strong class="text-white font-mono">{{ auth()->user()->email }}</strong> dan klik tautan verifikasi yang kami kirimkan.
            </p>

            @if(session('status') === 'verification-link-sent')
                <div class="p-3.5 rounded-xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-300 text-xs flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                    <span>Tautan verifikasi baru telah dikirimkan ke alamat email Anda.</span>
                </div>
            @endif

            <div class="space-y-3 pt-2">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit"
                            class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-500/20 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Kirim Ulang Email Verifikasi</span>
                    </button>
                </form>

                <div class="flex items-center justify-between pt-3 border-t border-slate-800/80 text-xs">
                    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white font-semibold">
                        Lanjut ke Dashboard &rarr;
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-rose-400 hover:text-rose-300 hover:underline font-semibold flex items-center gap-1">
                            <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>
