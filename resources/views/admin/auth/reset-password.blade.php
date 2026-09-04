<!DOCTYPE html>
<html lang="id" class="dark h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Kata Sandi Baru Admin — Cooca UMKM Platform</title>

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
                        brand: { 500: '#6366f1', 600: '#4f46e5' }
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
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-cyan-400 p-0.5 shadow-xl shadow-indigo-500/20">
                <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center">
                    <i data-lucide="key-round" class="w-7 h-7 text-indigo-400"></i>
                </div>
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Atur Sandi Baru Admin</h2>
            <p class="text-xs text-slate-400">Buat kata sandi baru yang kuat untuk akun administrator Anda</p>
        </div>

        <!-- Form Card -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-5">

            @if($errors->any())
                <div class="p-3.5 rounded-xl bg-red-950/60 border border-red-500/40 text-red-300 text-xs flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-400 shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.password.update') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Alamat Email Administrator</label>
                    <input type="email" name="email" value="{{ old('email', $email) }}" required readonly
                           class="w-full px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-slate-400 font-mono text-xs cursor-not-allowed">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Kata Sandi Baru</label>
                    <input type="password" name="password" required autofocus placeholder="Minimal 8 karakter"
                           class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" name="password_confirmation" required placeholder="Ulangi kata sandi baru"
                           class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
                </div>

                <button type="submit"
                        class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-600 hover:from-indigo-500 hover:to-cyan-500 text-white font-bold shadow-lg shadow-indigo-500/25 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Simpan Kata Sandi & Login</span>
                </button>
            </form>

            <div class="text-center pt-2 border-t border-slate-800/80">
                <a href="{{ route('admin.login') }}" class="text-xs text-indigo-400 hover:underline font-semibold inline-flex items-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Kembali ke Halaman Login</span>
                </a>
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
