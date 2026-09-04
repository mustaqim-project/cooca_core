<!DOCTYPE html>
<html lang="id" class="dark h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi Admin — Cooca UMKM Platform</title>

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
                    <i data-lucide="shield-alert" class="w-7 h-7 text-indigo-400"></i>
                </div>
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Reset Sandi Admin</h2>
            <p class="text-xs text-slate-400">Masukkan email administrator untuk menerima link reset kata sandi</p>
        </div>

        <!-- Form Card -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-5">

            @if(session('status'))
                <div class="p-3.5 rounded-xl bg-indigo-950/60 border border-indigo-500/40 text-indigo-300 text-xs flex items-center gap-2">
                    <i data-lucide="mail" class="w-4 h-4 text-indigo-400 shrink-0"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-3.5 rounded-xl bg-red-950/60 border border-red-500/40 text-red-300 text-xs flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-400 shrink-0"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.password.email') }}" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-semibold text-slate-300 mb-1.5">Alamat Email Administrator</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="admin@cooca.id"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-950/80 border border-slate-800 focus:border-indigo-500 rounded-xl text-white font-mono text-xs">
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-600 hover:from-indigo-500 hover:to-cyan-500 text-white font-bold shadow-lg shadow-indigo-500/25 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Kirim Link Reset Kata Sandi</span>
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
