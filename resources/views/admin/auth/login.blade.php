<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — Cooca Console</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { fontFamily: { sans: ['-apple-system', '"SF Pro Text"', 'Inter', 'system-ui', 'sans-serif'] } } } }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: -apple-system, "SF Pro Text", Inter, system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
        .sheet-material { background: rgba(255,255,255,0.96); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); }
        .dark .sheet-material { background: rgba(44,44,46,0.96); }
    </style>
</head>
<body class="min-h-screen bg-[#F2F2F7] dark:bg-[#1E1E1E] flex items-center justify-center p-4 text-black dark:text-white antialiased">

    <div class="w-full max-w-[400px]">
        <!-- Header -->
        <div class="text-center space-y-2 mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-[22%] bg-[#007AFF] shadow-[0_4px_16px_rgba(0,122,255,0.3)] mb-3">
                <i data-lucide="shield-check" class="w-8 h-8 text-white" stroke-width="1.5"></i>
            </div>
            <h1 class="text-[24px] font-bold tracking-tight">Admin Console Login</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Masuk ke pusat kontrol sistem dan konfigurasi API</p>
        </div>

        <!-- Card -->
        <div class="sheet-material rounded-[20px] p-7 shadow-[0_20px_50px_rgba(0,0,0,0.12)] border border-black/5 dark:border-white/10 space-y-5">

            @if(session('status'))
                <div class="p-3.5 rounded-[12px] bg-[#5856D6]/10 border border-[#5856D6]/20 text-[#413FA6] dark:text-[#5E5CE6] text-[13px] flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 shrink-0" stroke-width="1.5"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-3.5 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#C41E17] dark:text-[#FF453A] text-[13px] space-y-1">
                    @foreach($errors->all() as $error)
                        <p class="flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0" stroke-width="1.5"></i>
                            <span>{{ $error }}</span>
                        </p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Email Administrator</label>
                    <div class="relative">
                        <i data-lucide="mail" class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2" stroke-width="1.5"></i>
                        <input type="email" name="email" value="{{ old('email', 'admin@cooca.id') }}" required autofocus
                               placeholder="admin@cooca.id"
                               class="w-full h-11 pl-10 pr-4 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-black/70 dark:text-white/70 mb-1.5">Kata Sandi</label>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2" stroke-width="1.5"></i>
                        <input type="password" name="password" required placeholder="••••••••"
                               class="w-full h-11 pl-10 pr-4 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[15px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-[13px] text-black/60 dark:text-white/60">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]/30">
                        <span>Ingat Sesi Masuk</span>
                    </label>
                    <a href="{{ route('admin.password.request') }}" class="text-[13px] font-medium text-[#007AFF] dark:text-[#0A84FF] hover:underline">
                        Lupa kata sandi?
                    </a>
                </div>

                <button type="submit"
                        class="w-full h-11 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 text-white text-[15px] font-semibold transition-all flex items-center justify-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                    <span>Otentikasi Administrator</span>
                    <i data-lucide="arrow-right" class="w-4 h-4" stroke-width="1.5"></i>
                </button>
            </form>
        </div>

        <div class="text-center mt-6 text-[13px] text-black/50 dark:text-white/50">
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-1.5 text-[#007AFF] dark:text-[#0A84FF] font-medium hover:underline">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5" stroke-width="1.5"></i>
                <span>Kembali ke Landing Page</span>
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });
    </script>
</body>
</html>