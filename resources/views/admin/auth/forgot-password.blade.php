<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Lupa Kata Sandi Admin - Cooca Console</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', '"SF Pro Text"', '"SF Pro Display"', 'Inter', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: -apple-system, "SF Pro Text", "SF Pro Display", Inter, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
        }

        .dark .glass-card {
            background: rgba(28, 28, 30, 0.88);
        }

        .glow-btn:hover {
            box-shadow: 0 8px 24px -4px rgba(0, 122, 255, 0.45);
        }
    </style>
</head>

<body
    class="min-h-screen bg-[#F2F2F7] dark:bg-[#121214] flex items-center justify-center p-4 sm:p-6 text-black dark:text-white antialiased selection:bg-[#007AFF]/20">

    <div class="w-full max-w-[430px] my-auto">
        <!-- Apple HIG Header -->
        <div class="text-center space-y-2.5 mb-7">
            <div
                class="inline-flex items-center justify-center w-16 h-16 rounded-[22px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6] shadow-[0_8px_24px_rgba(0,122,255,0.35)] mb-2 transition-transform hover:scale-105 duration-200">
                <i data-lucide="key-round" class="w-8 h-8 text-white" stroke-width="2"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-black dark:text-white">Atur Ulang Sandi
            </h1>
            <p class="text-xs sm:text-sm text-black/55 dark:text-white/55">Kirimkan tautan reset kata sandi ke email
                administrator</p>
        </div>

        <!-- Apple Inset Grouped Card -->
        <div
            class="glass-card rounded-[28px] p-6 sm:p-8 shadow-2xl shadow-black/10 dark:shadow-black/70 border border-black/[0.06] dark:border-white/[0.08] relative overflow-hidden transition-colors">

            <!-- Status Alert -->
            @if (session('status'))
                <div
                    class="mb-5 p-3.5 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/25 text-[#34C759] dark:text-[#30D158] text-xs sm:text-sm flex items-center gap-2.5">
                    <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0" stroke-width="2"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- Errors Alert -->
            @if ($errors->any())
                <div
                    class="mb-5 p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs sm:text-sm space-y-1">
                    <div class="font-semibold flex items-center gap-2 mb-0.5">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0" stroke-width="2"></i>
                        <span>Permintaan Gagal:</span>
                    </div>
                    @foreach ($errors->all() as $error)
                        <p class="pl-6 text-xs opacity-90 leading-relaxed">• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.password.email') }}" class="space-y-4">
                @csrf

                <!-- Email Input (Anti-Zoom text-[16px] sm:text-sm) -->
                <div>
                    <label for="email"
                        class="block text-xs sm:text-sm font-semibold text-black/80 dark:text-white/85 mb-1.5">
                        Alamat Email Administrator <span class="text-[#007AFF] dark:text-[#0A84FF]">*</span>
                    </label>
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            autofocus placeholder="admin@cooca.id"
                            class="w-full pl-10 pr-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm">
                    </div>
                </div>

                <!-- Apple System Blue CTA Button (50px Touch Target) -->
                <button type="submit"
                    class="w-full min-h-[50px] py-3.5 px-4 rounded-[14px] glow-btn bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm sm:text-base shadow-lg shadow-[#007AFF]/25 flex items-center justify-center gap-2 transition-all active:scale-[0.98]">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Kirim Tautan Pemulihan Kata Sandi</span>
                </button>
            </form>

            <!-- Back to Login Link -->
            <div class="mt-5 pt-4 border-t border-black/5 dark:border-white/10 text-center">
                <a href="{{ route('admin.login') }}"
                    class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Kembali ke Halaman Login Admin</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
</body>

</html>
