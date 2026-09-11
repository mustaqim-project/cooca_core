<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Terjadi Kesalahan' }} - Cooca</title>
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('cooca-theme') || 'light';
                var dark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', dark);
            } catch (error) {}
        }());
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full bg-[#F2F2F7] text-[#1C1C1E] dark:bg-[#111111] dark:text-white">
    <main class="min-h-screen flex items-center justify-center p-5 sm:p-8">
        <section class="w-full max-w-xl text-center">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF]">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 8v4"></path>
                    <path d="M12 16h.01"></path>
                </svg>
            </div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#007AFF]">Cooca</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">{{ $code }}</h1>
            <h2 class="mt-2 text-xl font-semibold sm:text-2xl">{{ $title }}</h2>
            <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-black/55 dark:text-white/55">{{ $message }}</p>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <button type="button" onclick="history.back()" class="h-10 rounded-[10px] bg-black/[0.06] px-5 text-sm font-semibold text-black/75 transition hover:bg-black/[0.1] dark:bg-white/[0.1] dark:text-white/80 dark:hover:bg-white/[0.15]">
                    Kembali
                </button>
                <a href="{{ $homeUrl }}" class="inline-flex h-10 items-center justify-center rounded-[10px] bg-[#007AFF] px-5 text-sm font-semibold text-white transition hover:bg-[#0071E3]">
                    {{ $homeLabel }}
                </a>
            </div>

            <p class="mt-8 text-xs text-black/35 dark:text-white/35">Kode referensi: {{ $reference }}</p>
        </section>
    </main>
</body>
</html>
