<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth" data-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cooca Core — Business Operating System untuk UMKM</title>
    <meta name="description" content="Cooca Core: HPP presisi, AI Assistant, POS, inventori real-time, akuntansi otomatis — gratis selamanya untuk UMKM Indonesia.">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="COOCA.ID">
    <meta property="og:locale" content="id_ID">
    <meta property="og:url" content="https://cooca.id">
    <meta property="og:title" content="Cooca Core — Business Operating System untuk UMKM">
    <meta property="og:description" content="HPP presisi, AI Assistant, POS, inventori real-time, akuntansi otomatis — gratis selamanya.">
    <meta property="og:image" content="https://cooca.id/assets/image/cooca.png">
    <meta property="og:image:secure_url" content="https://cooca.id/assets/image/cooca.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="https://cooca.id/assets/image/1785229034_favicon.png">
    <link rel="alternate icon" type="image/png" href="https://cooca.id/favicon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com">
    </script>
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
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366F1',
                            600: '#4F46E5',
                            700: '#4338CA',
                        },
                        cyan: {
                            400: '#22D3EE',
                            500: '#06B6D4',
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'shimmer': 'shimmer 3s linear infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-12px)' },
                        },
                        shimmer: {
                            '0%': { backgroundPosition: '-200% 0' },
                            '100%': { backgroundPosition: '200% 0' },
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js & Lucide -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js">
    </script>
    <script src="https://unpkg.com/lucide@latest">
    </script>

    <style>
        /* ── DARK TOKENS ── */
        :root,
        [data-theme="dark"] {
            --bg: #030712;
            --bg-section: #0F172A;
            --surface: #111827;
            --card: #1E293B;
            --primary: #6366F1;
            --primary-glow: rgba(99, 102, 241, .35);
            --accent: #22D3EE;
            --text: #F8FAFC;
            --border: rgba(255, 255, 255, .08);
            --glass-bg: rgba(17, 24, 39, .75);
            --glass-blur: blur(20px);
        }

        * {
            box-sizing: border-box;
        }
        html {
            scroll-behavior: smooth;
        }
        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image:
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.14) 0px, transparent 50%),
                radial-gradient(at 100% 20%, rgba(34, 211, 238, 0.10) 0px, transparent 50%),
                radial-gradient(at 50% 100%, rgba(99, 102, 241, 0.08) 0px, transparent 60%);
            background-attachment: fixed;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            border-color: rgba(99, 102, 241, 0.35);
            transform: translateY(-3px);
            box-shadow: 0 16px 40px -12px var(--primary-glow);
        }

        .glow-btn {
            background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
            box-shadow: 0 4px 24px -2px var(--primary-glow);
            transition: all 0.3s ease;
        }
        .glow-btn:hover {
            box-shadow: 0 8px 40px 0px var(--primary-glow);
            transform: translateY(-2px) scale(1.02);
        }

        .text-gradient-accent {
            background: linear-gradient(135deg, #22D3EE 0%, #818CF8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .text-gradient-warm {
            background: linear-gradient(135deg, #FCD34D 0%, #F59E0B 50%, #F97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .shimmer-badge {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.15) 25%, rgba(34, 211, 238, 0.25) 50%, rgba(99, 102, 241, 0.15) 75%);
            background-size: 200% 100%;
            animation: shimmer 3s linear infinite;
        }

        .dashboard-glow {
            box-shadow: 0 0 60px -10px rgba(99, 102, 241, 0.25);
        }

        input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            background: transparent;
            cursor: pointer;
        }
        input[type="range"]::-webkit-slider-runnable-track {
            height: 6px;
            background: #1E293B;
            border-radius: 999px;
        }
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            height: 18px;
            width: 18px;
            border-radius: 50%;
            margin-top: -6px;
            background: #6366F1;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.5);
            transition: all 0.2s;
            cursor: pointer;
        }
        input[type="range"]::-webkit-slider-thumb:hover {
            transform: scale(1.15);
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.7);
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0F172A;
        }
        ::-webkit-scrollbar-thumb {
            background: #6366F1;
            border-radius: 999px;
        }
        ::selection {
            background: #6366F1;
            color: #fff;
        }
    </style>
</head>

<body class="min-h-screen text-slate-100" x-data="{ mobileMenu: false, faqOpen: null }" x-init="lucide.createIcons()">

    <!-- ═══ HEADER ═══ -->
    <header class="sticky top-0 z-50 backdrop-blur-2xl bg-slate-950/70 border-b border-white/[0.06]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">

            <!-- Logo dengan route landing -->
            <a href="{{ route('landing') }}" class="flex items-center gap-3 group shrink-0">
                <img src="https://cooca.id/assets/image/1785229034_logo_dark.png" alt="COOCA.ID" class="h-7 sm:h-8 w-auto object-contain transition-transform group-hover:scale-105">
                <div class="border-l border-white/10 pl-3 hidden xs:block">
                    <span class="font-extrabold text-sm sm:text-base tracking-tight text-white block leading-tight">Cooca Core</span>
                    <span class="text-[8px] sm:text-[10px] uppercase font-bold text-emerald-400 tracking-[0.15em]">Business Operating System</span>
                </div>
            </a>

            <nav class="hidden lg:flex items-center gap-6 xl:gap-8 text-xs font-semibold text-slate-400">
                <a href="#calculator" class="hover:text-white transition-colors">Kalkulator HPP</a>
                <a href="#modul" class="hover:text-white transition-colors">Modul</a>
                <a href="#bento" class="hover:text-white transition-colors">Fitur Unggulan</a>
                <a href="#faq" class="hover:text-white transition-colors">FAQ</a>
            </nav>

            <!-- Actions dengan route login & register -->
            <div class="hidden sm:flex items-center gap-2">
                <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors rounded-xl hover:bg-slate-900/50">Masuk</a>
                <a href="{{ route('register') }}" class="glow-btn px-5 py-2.5 rounded-xl text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-indigo-500/20">
                    <span>Mulai Gratis</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <button @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 rounded-xl bg-slate-900/80 border border-slate-800 text-slate-400 hover:text-white transition-colors">
                <i :data-lucide="mobileMenu ? 'x' : 'menu'" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Mobile menu dengan route -->
        <div x-show="mobileMenu" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="lg:hidden p-5 bg-slate-950/98 backdrop-blur-2xl border-b border-slate-800/80 space-y-2 text-sm font-semibold" style="display: none;">
            <a href="#calculator" @click="mobileMenu = false" class="block py-3 px-4 rounded-xl text-slate-300 hover:bg-slate-900/80 hover:text-white">Kalkulator HPP</a>
            <a href="#modul" @click="mobileMenu = false" class="block py-3 px-4 rounded-xl text-slate-300 hover:bg-slate-900/80 hover:text-white">Modul Bisnis</a>
            <a href="#bento" @click="mobileMenu = false" class="block py-3 px-4 rounded-xl text-slate-300 hover:bg-slate-900/80 hover:text-white">Fitur Unggulan</a>
            <a href="#faq" @click="mobileMenu = false" class="block py-3 px-4 rounded-xl text-slate-300 hover:bg-slate-900/80 hover:text-white">FAQ</a>
            <div class="pt-4 border-t border-slate-800/80 grid grid-cols-2 gap-2 mt-2">
                <a href="{{ route('login') }}" class="py-3 rounded-xl bg-slate-900/80 border border-slate-800 text-center text-white font-semibold">Masuk</a>
                <a href="{{ route('register') }}" class="py-3 rounded-xl glow-btn text-center text-white font-bold shadow-lg shadow-indigo-500/20">Daftar</a>
            </div>
            <a href="{{ route('auth.google') }}" class="w-full py-3 rounded-xl bg-slate-900/80 border border-slate-800 text-white font-semibold text-sm flex items-center justify-center gap-2.5 hover:bg-slate-800/80">
                <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z"/><path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z"/><path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15s.7 5.3 1.9 7.7l3.7-2.9z"/><path fill="#34A853" d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.4-6.4-5.2L1.9 16c1.8 3.7 5.6 7 10.1 7z"/></svg>
                <span>Masuk via Google</span>
            </a>
        </div>
    </header>

    <!-- ═══ HERO — 2 KOLOM ═══ -->
    <section class="relative pt-10 pb-14 md:pt-20 md:pb-24 overflow-hidden">
        <!-- Orbs -->
        <div class="absolute top-1/4 -left-32 w-96 h-96 bg-indigo-600/20 rounded-full blur-[120px] animate-float pointer-events-none"></div>
        <div class="absolute bottom-1/4 -right-32 w-96 h-96 bg-cyan-500/15 rounded-full blur-[120px] animate-float pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">

                <!-- Kiri: CTA -->
                <div class="space-y-6 text-center lg:text-left">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass-card border-indigo-500/30 text-indigo-300 text-[11px] sm:text-xs font-semibold shimmer-badge mx-auto lg:mx-0">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Gratis Selamanya • Untuk UMKM Indonesia</span>
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-indigo-400"></i>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-extrabold tracking-tight text-white leading-[1.1]">
                        <span class="block">Kelola Bisnis</span>
                        <span class="text-gradient-accent">Lebih Cerdas &amp; Profit</span>
                    </h1>

                    <p class="text-sm sm:text-base text-slate-400 max-w-xl leading-relaxed font-medium mx-auto lg:mx-0">
                        <strong class="text-white">Cooca Core</strong> — Sistem operasi bisnis all-in-one: <span class="text-cyan-400 font-semibold">HPP presisi</span>, <span class="text-indigo-400 font-semibold">AI Assistant</span>, <span class="text-emerald-400 font-semibold">POS</span>, inventori real-time, dan akuntansi otomatis. <strong class="text-white">100% gratis</strong> untuk UMKM.
                    </p>

                    <!-- CTAs dengan route -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3">
                        <a href="{{ route('register') }}" class="w-full sm:w-auto glow-btn px-8 py-3.5 rounded-2xl text-white font-bold text-sm flex items-center justify-center gap-2 shadow-xl shadow-indigo-500/30">
                            <span>Mulai Sekarang — Gratis</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('auth.google') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl glass-card hover:bg-slate-800/60 text-white font-semibold text-sm flex items-center justify-center gap-2.5 border-slate-700/50 transition-all">
                            <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z"/><path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z"/><path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15s.7 5.3 1.9 7.7l3.7-2.9z"/><path fill="#34A853" d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.4-6.4-5.2L1.9 16c1.8 3.7 5.6 7 10.1 7z"/></svg>
                            <span>Google</span>
                        </a>
                    </div>

                    <!-- Trust signals -->
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 sm:gap-6 text-[11px] font-semibold text-slate-500 pt-1">
                        <span class="flex items-center gap-1.5"><i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-400"></i> Tanpa kartu kredit</span>
                        <span class="flex items-center gap-1.5"><i data-lucide="zap" class="w-3.5 h-3.5 text-amber-400"></i> Setup 2 menit</span>
                        <span class="flex items-center gap-1.5"><i data-lucide="lock" class="w-3.5 h-3.5 text-indigo-400"></i> Data privat & aman</span>
                    </div>

                    <!-- Social proof -->
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-5 sm:gap-8 text-xs text-slate-400 pt-1">
                        <div class="flex items-center gap-2"><i data-lucide="users" class="w-4 h-4 text-cyan-400"></i> <span>1.200+ UMKM Pakai</span></div>
                        <div class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i> <span>Akurasi 99.8%</span></div>
                        <div class="flex items-center gap-2"><i data-lucide="clock" class="w-4 h-4 text-indigo-400"></i> <span>Hemat 3-5 jam/minggu</span></div>
                    </div>
                </div>

                <!-- Kanan: Ilustrasi Dashboard -->
                <div class="flex justify-center lg:justify-end">
                    <div class="relative w-full max-w-md lg:max-w-lg xl:max-w-xl">
                        <div class="absolute -inset-8 bg-indigo-600/20 rounded-3xl blur-3xl opacity-60 animate-float pointer-events-none"></div>
                        <div class="relative glass-card p-4 sm:p-6 rounded-2xl border border-indigo-500/20 dashboard-glow w-full bg-slate-950/90 backdrop-blur-xl">
                            <!-- Fake dashboard UI -->
                            <div class="flex items-center justify-between border-b border-slate-800/80 pb-3 mb-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                    </div>
                                    <span class="text-xs font-bold text-white">Dashboard</span>
                                </div>
                                <div class="flex gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                    <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mb-4">
                                <div class="bg-slate-950/80 p-2.5 rounded-xl border border-slate-800">
                                    <div class="text-[9px] text-slate-400 uppercase font-semibold">Omzet Hari Ini</div>
                                    <div class="text-sm font-extrabold text-emerald-400 font-mono">Rp 4.2Jt</div>
                                    <div class="text-[9px] text-emerald-400 flex items-center gap-0.5"><i data-lucide="trending-up" class="w-3 h-3"></i> +12%</div>
                                </div>
                                <div class="bg-slate-950/80 p-2.5 rounded-xl border border-slate-800">
                                    <div class="text-[9px] text-slate-400 uppercase font-semibold">Total Produk</div>
                                    <div class="text-sm font-extrabold text-white font-mono">247</div>
                                    <div class="text-[9px] text-slate-400">aktif 89%</div>
                                </div>
                                <div class="bg-slate-950/80 p-2.5 rounded-xl border border-slate-800">
                                    <div class="text-[9px] text-slate-400 uppercase font-semibold">Stok Menipis</div>
                                    <div class="text-sm font-extrabold text-amber-400 font-mono">6 item</div>
                                    <div class="text-[9px] text-amber-400 flex items-center gap-0.5"><i data-lucide="alert-triangle" class="w-3 h-3"></i> segera pesan</div>
                                </div>
                                <div class="bg-slate-950/80 p-2.5 rounded-xl border border-slate-800">
                                    <div class="text-[9px] text-slate-400 uppercase font-semibold">Laba Bulan Ini</div>
                                    <div class="text-sm font-extrabold text-cyan-400 font-mono">Rp 18.5Jt</div>
                                    <div class="text-[9px] text-cyan-400 flex items-center gap-0.5"><i data-lucide="arrow-up" class="w-3 h-3"></i> +8%</div>
                                </div>
                            </div>

                            <div class="bg-slate-950/80 p-3 rounded-xl border border-slate-800 mb-3">
                                <div class="flex justify-between text-[9px] text-slate-400 mb-1.5">
                                    <span>Penjualan 7 Hari Terakhir</span>
                                    <span class="text-emerald-400">+23%</span>
                                </div>
                                <div class="flex items-end gap-1 h-12">
                                    <div class="flex-1 bg-indigo-500/70 h-6 rounded-sm"></div>
                                    <div class="flex-1 bg-indigo-500/70 h-8 rounded-sm"></div>
                                    <div class="flex-1 bg-indigo-500/70 h-4 rounded-sm"></div>
                                    <div class="flex-1 bg-indigo-500/70 h-10 rounded-sm"></div>
                                    <div class="flex-1 bg-indigo-500/70 h-7 rounded-sm"></div>
                                    <div class="flex-1 bg-indigo-500/70 h-12 rounded-sm"></div>
                                    <div class="flex-1 bg-indigo-500/70 h-9 rounded-sm"></div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 bg-slate-950/80 p-2 rounded-xl border border-slate-800">
                                <i data-lucide="bot" class="w-4 h-4 text-cyan-400 shrink-0"></i>
                                <span class="text-[10px] text-slate-300 truncate flex-1">AI: “Stok tepung tersisa 20% — pesan 50 kg”</span>
                                <span class="text-[9px] text-cyan-400 font-bold uppercase">AI</span>
                            </div>
                        </div>

                        <div class="absolute -bottom-3 -right-3 bg-emerald-500/10 backdrop-blur-md border border-emerald-500/30 rounded-full px-3 py-1.5 text-[9px] font-bold text-emerald-400 shadow-lg shadow-emerald-500/20 animate-float-delay">
                            <i data-lucide="check-circle" class="w-3 h-3 inline mr-1"></i> 100% Gratis
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══ MODUL BISNIS ═══ -->
    <section id="modul" class="py-12 md:py-20 border-t border-slate-800/60 bg-slate-950/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

            <div class="text-center space-y-3 max-w-2xl mx-auto">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-400">Modul Terintegrasi</span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">Semua yang Anda Butuhkan dalam Satu Platform</h2>
                <p class="text-sm text-slate-400">Dari kalkulasi biaya hingga laporan keuangan — Cooca Core menyatukan seluruh operasi bisnis Anda.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                <div class="glass-card p-6 rounded-3xl space-y-3 border-blue-500/20 hover:border-blue-500/40 transition-all">
                    <div class="w-11 h-11 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Dashboard</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Lihat gambaran utuh bisnis Anda: omzet, laba, stok, piutang, dan KPI penting dalam satu layar.</p>
                </div>

                <div class="glass-card p-6 rounded-3xl space-y-3 border-purple-500/20 hover:border-purple-500/40 transition-all">
                    <div class="w-11 h-11 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-400">
                        <i data-lucide="calculator" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Kalkulator HPP &amp; 3-Pilar</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Hitung biaya pokok produksi dengan presisi tinggi. Dukung 3 pilar biaya: material, tenaga kerja, dan overhead.</p>
                </div>

                <div class="glass-card p-6 rounded-3xl space-y-3 border-cyan-500/20 hover:border-cyan-500/40 transition-all">
                    <div class="w-11 h-11 rounded-xl bg-cyan-500/10 flex items-center justify-center text-cyan-400">
                        <i data-lucide="bot" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">AI Assistant</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Tanya jawab bisnis dengan AI: analisis tren, rekomendasi harga, dan deteksi anomali secara otomatis.</p>
                </div>

                <div class="glass-card p-6 rounded-3xl space-y-3 border-emerald-500/20 hover:border-emerald-500/40 transition-all">
                    <div class="w-11 h-11 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Kasir &amp; Penjualan</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Terminal POS, faktur & piutang, riwayat transaksi, shift, pelanggan CRM, dan pesanan/penawaran.</p>
                </div>

                <div class="glass-card p-6 rounded-3xl space-y-3 border-amber-500/20 hover:border-amber-500/40 transition-all">
                    <div class="w-11 h-11 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400">
                        <i data-lucide="package" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Produk &amp; Inventori</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Katalog produk & resep, bahan baku & harga, stok real-time & mutasi, PO & pemasok, upah kerja & mesin.</p>
                </div>

                <div class="glass-card p-6 rounded-3xl space-y-3 border-rose-500/20 hover:border-rose-500/40 transition-all">
                    <div class="w-11 h-11 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-400">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Keuangan &amp; Analitik</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Laporan & analitik, BEP & profitabilitas, beban operasional, jurnal akuntansi otomatis, dan simulasi what-if.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- ═══ BENTO GRID — FITUR UNGGULAN ═══ -->
    <section id="bento" class="py-12 md:py-20 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

            <div class="text-center space-y-3 max-w-2xl mx-auto">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-cyan-400">Fitur Unggulan</span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">Fitur yang Membuat Bisnis Anda Tumbuh</h2>
                <p class="text-sm text-slate-400">Fitur-fitur andalan yang siap membantu Anda mengambil keputusan lebih cepat dan tepat.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5" id="calculator">

                <!-- Kalkulator HPP Interaktif -->
                <div class="glass-card p-5 sm:p-7 rounded-3xl lg:col-span-8 flex flex-col space-y-6 relative overflow-hidden" x-data="{
                        matCost: 25000,
                        labCost: 6500,
                        ovhCost: 8500,
                        markup: 40,
                        isMargin: false,
                        get hpp() { return this.matCost + this.labCost + this.ovhCost; },
                        get price() {
                            if (this.isMargin) return Math.round(this.hpp / (1 - (this.markup / 100)));
                            return Math.round(this.hpp * (1 + (this.markup / 100)));
                        },
                        get profit() { return this.price - this.hpp; },
                        get marginPct() { return this.price > 0 ? Math.round((this.profit / this.price) * 100) : 0; }
                    }">

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800/80 pb-4">
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-400 font-bold text-[10px] uppercase">
                                <i data-lucide="play" class="w-3 h-3"></i>
                                <span>Live Kalkulator HPP</span>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-white mt-1">Hitung HPP &amp; Harga Jual Instan</h3>
                        </div>
                        <div class="flex items-center p-1 bg-slate-950 rounded-xl border border-slate-800 text-xs shrink-0">
                            <button @click="isMargin = false" :class="!isMargin ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-400'" class="px-3 py-1 rounded-lg font-semibold transition-all duration-200">Markup</button>
                            <button @click="isMargin = true" :class="isMargin ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-400'" class="px-3 py-1 rounded-lg font-semibold transition-all duration-200">Margin</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800/80">
                            <span class="text-[10px] font-bold text-blue-400 uppercase">Material</span>
                            <div class="font-mono font-bold text-white text-sm mt-1">Rp <span x-text="matCost.toLocaleString('id-ID')"></span></div>
                            <input type="range" x-model.number="matCost" min="5000" max="60000" step="1000" class="w-full mt-2 accent-blue-500">
                        </div>
                        <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800/80">
                            <span class="text-[10px] font-bold text-purple-400 uppercase">Tenaga Kerja</span>
                            <div class="font-mono font-bold text-white text-sm mt-1">Rp <span x-text="labCost.toLocaleString('id-ID')"></span></div>
                            <input type="range" x-model.number="labCost" min="1000" max="30000" step="500" class="w-full mt-2 accent-purple-500">
                        </div>
                        <div class="p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800/80">
                            <span class="text-[10px] font-bold text-emerald-400 uppercase">Overhead</span>
                            <div class="font-mono font-bold text-white text-sm mt-1">Rp <span x-text="ovhCost.toLocaleString('id-ID')"></span></div>
                            <input type="range" x-model.number="ovhCost" min="1000" max="25000" step="500" class="w-full mt-2 accent-emerald-500">
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-950/90 border border-indigo-500/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="space-y-1 w-full sm:w-1/2">
                            <div class="flex justify-between text-xs font-semibold">
                                <span class="text-slate-300">Target <span x-text="isMargin ? 'Margin' : 'Markup'"></span>:</span>
                                <span class="font-mono font-bold text-indigo-400" x-text="markup + '%'"></span>
                            </div>
                            <input type="range" x-model.number="markup" min="10" max="150" step="5" class="w-full accent-indigo-500">
                        </div>
                        <div class="flex items-center gap-5 w-full sm:w-auto justify-between sm:justify-end border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-800">
                            <div>
                                <div class="text-[10px] font-bold uppercase text-slate-400">Total HPP</div>
                                <div class="text-base sm:text-lg font-extrabold text-white font-mono">Rp <span x-text="hpp.toLocaleString('id-ID')"></span></div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold uppercase text-emerald-400">Harga Jual Rekomendasi</div>
                                <div class="text-xl sm:text-2xl font-extrabold text-emerald-400 font-mono">Rp <span x-text="price.toLocaleString('id-ID')"></span></div>
                            </div>
                        </div>
                    </div>

                    <div class="text-[10px] text-slate-500 text-center border-t border-slate-800/60 pt-3 -mb-1">
                        <span x-show="isMargin" class="text-emerald-400">Margin: <span x-text="marginPct"></span>% • Laba: Rp <span x-text="profit.toLocaleString('id-ID')"></span></span>
                        <span x-show="!isMargin" class="text-indigo-400">Markup: <span x-text="markup"></span>% • Laba: Rp <span x-text="profit.toLocaleString('id-ID')"></span></span>
                    </div>
                </div>

                <!-- AI Assistant -->
                <div class="glass-card p-6 sm:p-7 rounded-3xl lg:col-span-4 flex flex-col justify-between space-y-4 border-cyan-500/20 hover:border-cyan-500/40 transition-all">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 mb-3">
                            <i data-lucide="bot" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-white">AI Assistant — Tanya Apa Saja</h3>
                        <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">Dapatkan wawasan bisnis instan: prediksi penjualan, analisis biaya, rekomendasi harga, dan deteksi anomali stok — semua melalui percakapan.</p>
                    </div>
                    <div class="space-y-2 text-xs">
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 flex items-start gap-2.5">
                            <i data-lucide="sparkles" class="w-4 h-4 text-cyan-400 shrink-0 mt-0.5"></i>
                            <span class="text-slate-300">“Prediksi penjualan bulan depan berdasarkan data historis?”</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 flex items-start gap-2.5">
                            <i data-lucide="sparkles" class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5"></i>
                            <span class="text-slate-300">“Produk mana yang paling profitabel?”</span>
                        </div>
                    </div>
                    <a href="#" class="text-[11px] text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1.5 transition-colors">
                        Coba AI Assistant <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <!-- POS Terminal -->
                <div class="glass-card p-6 sm:p-7 rounded-3xl lg:col-span-4 space-y-4 border-emerald-500/20 hover:border-emerald-500/40 transition-all">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Terminal Kasir POS</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Transaksi cepat dengan antarmuka yang intuitif. Dukung berbagai metode pembayaran, cetak struk, dan kelola shift kasir.</p>
                    <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 text-xs flex items-center justify-between">
                        <span class="text-slate-400">Transaksi hari ini</span>
                        <span class="font-mono font-bold text-emerald-400">Rp 2.450.000</span>
                    </div>
                </div>

                <!-- Stok Real-Time -->
                <div class="glass-card p-6 sm:p-7 rounded-3xl lg:col-span-4 space-y-4 border-amber-500/20 hover:border-amber-500/40 transition-all">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                        <i data-lucide="warehouse" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Stok Real-Time &amp; Mutasi</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Pantau stok barang dan bahan baku secara langsung. Ketahui mutasi masuk-keluar, nilai persediaan, dan notifikasi stok menipis.</p>
                    <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 text-xs flex items-center justify-between">
                        <span class="text-slate-400">Stok kritis</span>
                        <span class="font-mono font-bold text-rose-400">3 item perlu dipesan</span>
                    </div>
                </div>

                <!-- BEP & Profitabilitas -->
                <div class="glass-card p-6 sm:p-7 rounded-3xl lg:col-span-4 space-y-4 border-rose-500/20 hover:border-rose-500/40 transition-all">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400">
                        <i data-lucide="target" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">BEP &amp; Profitabilitas</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Hitung titik impas (BEP) dalam unit dan rupiah. Ketahui margin kontribusi dan proyeksi laba untuk berbagai skenario penjualan.</p>
                    <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 text-xs flex items-center justify-between">
                        <span class="text-slate-400">BEP bulan ini</span>
                        <span class="font-mono font-bold text-emerald-400">1.230 unit</span>
                    </div>
                </div>

                <!-- Jurnal Akuntansi Otomatis -->
                <div class="glass-card p-6 sm:p-7 rounded-3xl lg:col-span-4 space-y-4 border-indigo-500/20 hover:border-indigo-500/40 transition-all">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Jurnal Akuntansi Otomatis</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Setiap transaksi penjualan, pembelian, dan biaya langsung dicatat sebagai jurnal otomatis. Laporan keuangan siap kapan saja.</p>
                    <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 text-xs flex items-center justify-between">
                        <span class="text-slate-400">Saldo akhir</span>
                        <span class="font-mono font-bold text-cyan-400">Rp 87.500.000</span>
                    </div>
                </div>

                <!-- Simulasi What-If -->
                <div class="glass-card p-6 sm:p-7 rounded-3xl lg:col-span-4 space-y-4 border-blue-500/20 hover:border-blue-500/40 transition-all">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                        <i data-lucide="sliders" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Simulasi What-If</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Uji skenario perubahan harga bahan baku, upah, atau volume penjualan — lihat dampaknya pada laba dan arus kas tanpa merusak data asli.</p>
                    <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 text-xs flex items-center justify-between">
                        <span class="text-slate-400">Jika bahan naik 10%</span>
                        <span class="font-mono font-bold text-amber-400">Laba turun 4.2%</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ═══ 100% FREE CTA ═══ -->
    <section class="py-12 md:py-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-indigo-600/5 via-transparent to-transparent pointer-events-none"></div>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="glass-card p-6 sm:p-10 md:p-14 rounded-3xl text-center space-y-6 relative overflow-hidden border-indigo-500/30 shadow-2xl shadow-indigo-500/10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-600/10 rounded-full blur-[100px] pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-cyan-500/10 rounded-full blur-[100px] pointer-events-none"></div>

                <div class="relative z-10">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[11px] sm:text-xs font-bold uppercase tracking-wider">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                        <span>100% Gratis Selamanya</span>
                    </div>

                    <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight mt-4">Mulai Kelola Bisnis Anda Hari Ini</h2>
                    <p class="text-sm text-slate-400 max-w-2xl mx-auto leading-relaxed">Tanpa biaya langganan, tanpa batasan pengguna, tanpa trik. Cooca Core hadir untuk membantu UMKM Indonesia tumbuh.</p>

                    <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3.5 max-w-md mx-auto">
                        <a href="{{ route('register') }}" class="w-full sm:w-auto glow-btn px-8 py-3.5 rounded-2xl text-white font-bold text-sm flex items-center justify-center gap-2 shadow-xl shadow-indigo-500/30">
                            <span>Daftar Gratis</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('auth.google') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl glass-card hover:bg-slate-800/60 text-white font-semibold text-sm flex items-center justify-center gap-2 border-slate-700/50 transition-all">
                            <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z"/><path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z"/><path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15s.7 5.3 1.9 7.7l3.7-2.9z"/><path fill="#34A853" d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.4-6.4-5.2L1.9 16c1.8 3.7 5.6 7 10.1 7z"/></svg>
                            <span>Google</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══ FAQ ═══ -->
    <section id="faq" class="py-12 md:py-20 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="text-center space-y-2">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-cyan-400">FAQ</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Pertanyaan yang Sering Diajukan</h2>
            <p class="text-sm text-slate-400">Semua jawaban tentang Cooca Core, gratis, dan keamanan data.</p>
        </div>

        <div class="space-y-3 text-sm" x-data="{ open: null }">
            <div class="glass-card rounded-2xl overflow-hidden border-slate-800/60">
                <button @click="open = open === 1 ? null : 1" class="w-full p-4 sm:p-5 text-left font-bold text-white flex items-center justify-between gap-3 hover:bg-slate-900/30 transition-colors">
                    <span>Apakah Cooca Core benar-benar gratis?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-300" :class="open === 1 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-4 sm:p-5 pt-0 text-slate-400 leading-relaxed border-t border-slate-800/60">
                    Ya! Semua fitur — Kalkulator HPP, AI Assistant, POS, inventori, akuntansi, dan analitik — dapat digunakan <strong class="text-cyan-400">100% gratis</strong> tanpa batasan waktu atau jumlah transaksi. Kami berkomitmen untuk UMKM Indonesia.
                </div>
            </div>

            <div class="glass-card rounded-2xl overflow-hidden border-slate-800/60">
                <button @click="open = open === 2 ? null : 2" class="w-full p-4 sm:p-5 text-left font-bold text-white flex items-center justify-between gap-3 hover:bg-slate-900/30 transition-colors">
                    <span>Apakah data saya aman?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-300" :class="open === 2 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-4 sm:p-5 pt-0 text-slate-400 leading-relaxed border-t border-slate-800/60">
                    Data bisnis Anda dienkripsi dan hanya dapat diakses oleh akun Anda. Kami tidak menjual atau membagikan data ke pihak ketiga. Privasi adalah prioritas utama.
                </div>
            </div>

            <div class="glass-card rounded-2xl overflow-hidden border-slate-800/60">
                <button @click="open = open === 3 ? null : 3" class="w-full p-4 sm:p-5 text-left font-bold text-white flex items-center justify-between gap-3 hover:bg-slate-900/30 transition-colors">
                    <span>Apakah cocok untuk usaha F&B atau manufaktur?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-300" :class="open === 3 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open === 3" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-4 sm:p-5 pt-0 text-slate-400 leading-relaxed border-t border-slate-800/60">
                    Sangat cocok. Cooca Core mendukung resep/recipe BOM, konversi satuan otomatis, yield factor, dan penghitungan biaya tenaga kerja & mesin — ideal untuk F&B, konveksi, skincare, dan manufaktur lainnya.
                </div>
            </div>

            <div class="glass-card rounded-2xl overflow-hidden border-slate-800/60">
                <button @click="open = open === 4 ? null : 4" class="w-full p-4 sm:p-5 text-left font-bold text-white flex items-center justify-between gap-3 hover:bg-slate-900/30 transition-colors">
                    <span>Bagaimana cara menggunakan AI Assistant?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-300" :class="open === 4 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open === 4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-4 sm:p-5 pt-0 text-slate-400 leading-relaxed border-t border-slate-800/60">
                    Cukup tanyakan pertanyaan bisnis Anda dalam bahasa Indonesia. AI akan menganalisis data real-time Anda dan memberikan jawaban, rekomendasi harga, serta analisis tren secara otomatis.
                </div>
            </div>

            <div class="glass-card rounded-2xl overflow-hidden border-slate-800/60">
                <button @click="open = open === 5 ? null : 5" class="w-full p-4 sm:p-5 text-left font-bold text-white flex items-center justify-between gap-3 hover:bg-slate-900/30 transition-colors">
                    <span>Apakah bisa login dengan Google?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-300" :class="open === 5 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open === 5" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-4 sm:p-5 pt-0 text-slate-400 leading-relaxed border-t border-slate-800/60">
                    Ya, dukung Single Sign-On (SSO) Google. Anda dapat mendaftar atau masuk hanya dengan satu klik menggunakan akun Google Anda.
                </div>
            </div>
        </div>
    </section>

    <!-- ═══ FOOTER ═══ -->
    <footer class="border-t border-white/[0.06] bg-[#030712] py-12 sm:py-16 text-xs text-slate-400" role="contentinfo">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-8">

                <div class="sm:col-span-2 lg:col-span-1 space-y-4">
                    <img src="https://cooca.id/assets/image/1785229034_logo_dark.png" alt="COOCA.ID" class="h-8 w-auto object-contain">
                    <p class="text-slate-400 leading-relaxed text-[12px]">Platform ERP enterprise untuk UMKM, klinik, bengkel, restoran, retail, dan semua skala bisnis. Cloud native, modular, dan selalu siap.</p>
                    <div class="flex items-center gap-2 pt-1">
                        <a href="#" class="p-2 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:border-slate-700 transition-colors"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a>
                        <a href="#" class="p-2 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:border-slate-700 transition-colors"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg></a>
                        <a href="#" class="p-2 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:border-slate-700 transition-colors"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.95C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96C1 8.12 1 12 1 12s0 3.88.46 5.58a2.78 2.78 0 001.95 1.95C5.12 20 12 20 12 20s6.88 0 8.59-.47a2.78 2.78 0 001.95-1.95C23 15.88 23 12 23 12s0-3.88-.46-5.58zM9.75 15.02V8.98L15.5 12l-5.75 3.02z"/></svg></a>
                        <a href="#" class="p-2 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:border-slate-700 transition-colors"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg></a>
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="font-bold text-white uppercase text-[11px] tracking-wider">Navigasi</p>
                    <nav class="flex flex-col space-y-2 text-[12px]">
                        <a href="#" class="hover:text-white transition-colors">Beranda</a>
                        <a href="#" class="hover:text-white transition-colors">Tentang</a>
                        <a href="#" class="hover:text-white transition-colors">Produk ERP</a>
                        <a href="#" class="hover:text-white transition-colors">Kontak</a>
                    </nav>
                </div>

                <div class="space-y-3">
                    <p class="font-bold text-white uppercase text-[11px] tracking-wider">Layanan</p>
                    <nav class="flex flex-col space-y-2 text-[12px]">
                        <a href="#" class="hover:text-white transition-colors">POS Restoran</a>
                        <a href="#" class="hover:text-white transition-colors">Klinik ERP</a>
                        <a href="#" class="hover:text-white transition-colors">Bengkel ERP</a>
                        <a href="#" class="hover:text-white transition-colors">Retail POS</a>
                    </nav>
                </div>

                <div class="space-y-3">
                    <p class="font-bold text-white uppercase text-[11px] tracking-wider">Sumber Daya</p>
                    <nav class="flex flex-col space-y-2 text-[12px]">
                        <a href="#" class="hover:text-white transition-colors">Blog</a>
                        <a href="#" class="hover:text-white transition-colors">FAQ</a>
                        <a href="#" class="hover:text-white transition-colors">Syarat & Ketentuan</a>
                        <a href="#" class="hover:text-white transition-colors">Kebijakan Privasi</a>
                    </nav>
                </div>

                <div class="space-y-4 sm:col-span-2 lg:col-span-1">
                    <div>
                        <p class="font-bold text-white uppercase text-[11px] tracking-wider">Kontak</p>
                        <div class="space-y-1.5 text-[12px] mt-2">
                            <span class="block text-slate-400">Jakarta Selatan, DKI Jakarta</span>
                            <a href="#" class="block hover:text-white transition-colors">6282337499577</a>
                            <a href="#" class="block hover:text-white transition-colors">hello@cooca.id</a>
                        </div>
                    </div>
                    <div>
                        <p class="font-bold text-white uppercase text-[11px] tracking-wider">Newsletter</p>
                        <form class="space-y-2 mt-2" action="#" method="POST">
                            <div class="flex gap-2">
                                <input type="email" name="email" placeholder="Email kamu..." required class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition-colors">
                                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-xl text-white font-bold text-xs shrink-0 transition-colors shadow-lg shadow-indigo-500/20">→</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-900 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
                <p>© 2026 COOCA.ID. All rights reserved. Made with ❤️ in Indonesia.</p>
                <a href="#" title="DMCA Compliance">
                    <img src="https://images.dmca.com/Badges/dmca-badge-w100-2x1-03.png?ID=8a1fbcb5-5cdf-401c-8d19-df395c18212e" alt="DMCA compliant" width="100" height="25" loading="lazy" />
                </a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>

</body>

</html>