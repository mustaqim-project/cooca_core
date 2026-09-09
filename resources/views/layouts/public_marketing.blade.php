<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth" data-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Cooca UMKM — Business Operating System 100% Gratis untuk UMKM')</title>
    <meta name="description" content="@yield('description', 'Cooca UMKM: Software kasir POS, pembukuan otomatis, kalkulator bisnis & AI Assistant gratis selamanya untuk UMKM Indonesia.')">
    <meta name="keywords" content="@yield('keywords', 'software kasir gratis, erp umkm, pos kasir toko, aplikasi pembukuan gratis, kalkulator hpp, kalkulator bep, template pembukuan excel, cooca umkm')">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="COOCA.ID">
    <meta property="og:locale" content="id_ID">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Cooca UMKM — Business Operating System')">
    <meta property="og:description" content="@yield('description', 'Software kasir, pembukuan, kalkulator bisnis & AI Assistant gratis selamanya.')">
    <meta property="og:image" content="@yield('og_image', 'https://cooca.id/assets/image/cooca.png')">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="https://cooca.id/assets/image/1785229034_favicon.png">
    <link rel="alternate icon" type="image/png" href="https://cooca.id/favicon.png">

    <!-- SEO Canonical & Robots -->
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="index, follow">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
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

    <!-- Lucide Icons & Alpine.js -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --bg: #030712;
            --surface: #0B0F19;
            --surface-hover: #111827;
            --primary: #6366F1;
            --primary-glow: rgba(99, 102, 241, 0.25);
            --accent: #22D3EE;
            --accent-glow: rgba(34, 211, 238, 0.25);
            --border: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(255, 255, 255, 0.16);
            --text: #F8FAFC;
            --text-muted: #94A3B8;
            --glass-bg: rgba(11, 15, 25, 0.7);
            --glass-blur: blur(20px);
        }
        html { scroll-behavior: smooth; }
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
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            border-color: rgba(99, 102, 241, 0.35);
            transform: translateY(-2px);
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
        [x-cloak] { display: none !important; }
    </style>

    @stack('seo')
</head>

<body class="min-h-screen text-slate-100 flex flex-col justify-between" x-data="{ mobileMenu: false, calcDropdown: false, solutionDropdown: false, templateDropdown: false }" x-init="lucide.createIcons()">

    <!-- ═══ MEGA HEADER ═══ -->
    <header class="sticky top-0 z-50 backdrop-blur-2xl bg-slate-950/80 border-b border-white/[0.06]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">

            <!-- Logo Cooca UMKM -->
            <a href="{{ route('landing') }}" class="flex items-center gap-3 group shrink-0">
                <img src="https://cooca.id/assets/image/1785229034_logo_dark.png" alt="COOCA UMKM" class="h-7 sm:h-8 w-auto object-contain transition-transform group-hover:scale-105">
                <div class="border-l border-white/10 pl-3 hidden xs:block">
                    <span class="font-extrabold text-sm sm:text-base tracking-tight text-white block leading-tight">Cooca UMKM</span>
                    <span class="text-[8px] sm:text-[10px] uppercase font-bold text-emerald-400 tracking-[0.15em]">100% Gratis Selamanya</span>
                </div>
            </a>

            <!-- Desktop Nav Menu -->
            <nav class="hidden lg:flex items-center gap-5 xl:gap-7 text-xs font-semibold text-slate-300">
                <a href="{{ route('landing') }}" class="hover:text-white transition-colors {{ request()->routeIs('landing') ? 'text-indigo-400 font-bold' : '' }}">Beranda</a>

                <!-- Dropdown Kalkulator -->
                <div class="relative" @click.outside="calcDropdown = false">
                    <button @click="calcDropdown = !calcDropdown" class="flex items-center gap-1.5 hover:text-white transition-colors py-2 focus:outline-none">
                        <span>Kalkulator</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform" :class="calcDropdown ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="calcDropdown" x-cloak x-transition class="absolute top-full left-0 w-64 p-2 bg-slate-950/95 border border-slate-800 rounded-2xl shadow-2xl backdrop-blur-xl space-y-1">
                        <a href="{{ route('kalkulator.hpp') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Kalkulator HPP</span>
                            <span class="text-[10px] text-slate-400">Hitung biaya & harga jual</span>
                        </a>
                        <a href="{{ route('kalkulator.bep') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Kalkulator BEP</span>
                            <span class="text-[10px] text-slate-400">Titik impas rupiah & unit</span>
                        </a>
                        <a href="{{ route('kalkulator.harga-jual') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Kalkulator Harga Jual</span>
                            <span class="text-[10px] text-slate-400">Markup vs margin</span>
                        </a>
                        <a href="{{ route('kalkulator.laba-bersih') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Kalkulator Laba Bersih</span>
                            <span class="text-[10px] text-slate-400">Simulasi net profit usaha</span>
                        </a>
                        <a href="{{ route('kalkulator.gaji-karyawan') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Kalkulator Gaji Karyawan</span>
                            <span class="text-[10px] text-slate-400">Take home pay & lembur</span>
                        </a>
                        <a href="{{ route('kalkulator.pph-final') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Kalkulator PPh Final 0.5%</span>
                            <span class="text-[10px] text-slate-400">Pajak UMKM PP 55/2022</span>
                        </a>
                        <a href="{{ route('kalkulator.omzet-harian') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Kalkulator Omzet Harian</span>
                            <span class="text-[10px] text-slate-400">Target sales harian & tiket</span>
                        </a>
                        <a href="{{ route('kalkulator.simulasi-what-if') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs border-t border-slate-800/80 mt-1">
                            <span class="font-bold block text-cyan-400">Simulasi What-If</span>
                            <span class="text-[10px] text-slate-400">Sensitivitas kenaikan biaya</span>
                        </a>
                    </div>
                </div>

                <!-- Dropdown Solusi Niche -->
                <div class="relative" @click.outside="solutionDropdown = false">
                    <button @click="solutionDropdown = !solutionDropdown" class="flex items-center gap-1.5 hover:text-white transition-colors py-2 focus:outline-none">
                        <span>Solusi Industri</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform" :class="solutionDropdown ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="solutionDropdown" x-cloak x-transition class="absolute top-full left-0 w-64 p-2 bg-slate-950/95 border border-slate-800 rounded-2xl shadow-2xl backdrop-blur-xl space-y-1">
                        <a href="{{ route('solusi.show', 'kasir-warung') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Warung & Sembako</span>
                            <span class="text-[10px] text-slate-400">Grosir, eceran, bon hutang</span>
                        </a>
                        <a href="{{ route('solusi.show', 'kasir-cafe-kecil') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Kafe & Kedai Kopi</span>
                            <span class="text-[10px] text-slate-400">Resep cup, split bill, QRIS</span>
                        </a>
                        <a href="{{ route('solusi.show', 'kasir-kios') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Kios & Konter HP</span>
                            <span class="text-[10px] text-slate-400">Aksesoris & kasir cepat</span>
                        </a>
                        <a href="{{ route('solusi.show', 'kasir-laundry') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Laundry Kiloan</span>
                            <span class="text-[10px] text-slate-400">Nota WA & status cuci</span>
                        </a>
                        <a href="{{ route('solusi.show', 'kasir-salon') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Salon Kecantikan</span>
                            <span class="text-[10px] text-slate-400">Treatment & komisi terapis</span>
                        </a>
                        <a href="{{ route('solusi.show', 'kasir-barbershop') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Barbershop</span>
                            <span class="text-[10px] text-slate-400">Antrean & komisi kapster</span>
                        </a>
                        <a href="{{ route('solusi.show', 'kasir-bengkel-kecil') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Bengkel Motor Kecil</span>
                            <span class="text-[10px] text-slate-400">Servis + stok sparepart</span>
                        </a>
                        <a href="{{ route('solusi.show', 'kasir-irt') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Industri Rumah Tangga (IRT)</span>
                            <span class="text-[10px] text-slate-400">Produksi snack & katering</span>
                        </a>
                    </div>
                </div>

                <!-- Dropdown Template Gratis -->
                <div class="relative" @click.outside="templateDropdown = false">
                    <button @click="templateDropdown = !templateDropdown" class="flex items-center gap-1.5 hover:text-white transition-colors py-2 focus:outline-none">
                        <span>Template Gratis</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform" :class="templateDropdown ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="templateDropdown" x-cloak x-transition class="absolute top-full left-0 w-64 p-2 bg-slate-950/95 border border-slate-800 rounded-2xl shadow-2xl backdrop-blur-xl space-y-1">
                        <a href="{{ route('template.index') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs font-bold text-indigo-400">
                            Semua Template Gratis →
                        </a>
                        <a href="{{ route('template.show', 'pembukuan-warung-excel') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Buku Kas Warung (Excel)</span>
                            <span class="text-[10px] text-slate-400">Arus kas harian warung</span>
                        </a>
                        <a href="{{ route('template.show', 'laporan-keuangan-sederhana') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Laporan Keuangan Sederhana</span>
                            <span class="text-[10px] text-slate-400">Laba rugi & neraca mini</span>
                        </a>
                        <a href="{{ route('template.show', 'stok-opname-excel') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Template Stok Opname</span>
                            <span class="text-[10px] text-slate-400">Cek fisik selisih barang</span>
                        </a>
                        <a href="{{ route('template.show', 'invoice-sederhana') }}" class="block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-indigo-600/20 text-xs">
                            <span class="font-bold block text-white">Pembuat Invoice Instan</span>
                            <span class="text-[10px] text-slate-400">Format nota & tagihan</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('blog.index') }}" class="hover:text-white transition-colors {{ request()->routeIs('blog.*') ? 'text-indigo-400 font-bold' : '' }}">Blog & Edukasi</a>
                <a href="{{ route('contact') }}" class="hover:text-white transition-colors {{ request()->routeIs('contact') ? 'text-indigo-400 font-bold' : '' }}">Kontak</a>
            </nav>

            <!-- Action Buttons -->
            <div class="hidden sm:flex items-center gap-2">
                <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors rounded-xl hover:bg-slate-900/50">Masuk</a>
                <a href="{{ route('register') }}" class="glow-btn px-5 py-2.5 rounded-xl text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-indigo-500/20">
                    <span>Mulai Sekarang - Gratis</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <!-- Mobile Hamburger -->
            <button @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 rounded-xl bg-slate-900/80 border border-slate-800 text-slate-400 hover:text-white transition-colors">
                <i :data-lucide="mobileMenu ? 'x' : 'menu'" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Mobile Drawer Menu -->
        <div x-show="mobileMenu" x-cloak x-transition class="lg:hidden p-5 bg-slate-950/98 backdrop-blur-2xl border-b border-slate-800/80 space-y-2 text-sm font-semibold max-h-[85vh] overflow-y-auto">
            <a href="{{ route('landing') }}" @click="mobileMenu = false" class="block py-2.5 px-3 rounded-xl text-slate-300 hover:bg-slate-900">Beranda</a>

            <div class="py-2 border-t border-slate-800/60">
                <div class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider px-3 mb-1">Kalkulator Interaktif</div>
                <div class="grid grid-cols-2 gap-1 text-xs">
                    <a href="{{ route('kalkulator.hpp') }}" @click="mobileMenu = false" class="p-2 rounded-lg text-slate-300 hover:bg-slate-900">HPP & Harga</a>
                    <a href="{{ route('kalkulator.bep') }}" @click="mobileMenu = false" class="p-2 rounded-lg text-slate-300 hover:bg-slate-900">BEP Impas</a>
                    <a href="{{ route('kalkulator.harga-jual') }}" @click="mobileMenu = false" class="p-2 rounded-lg text-slate-300 hover:bg-slate-900">Markup Margin</a>
                    <a href="{{ route('kalkulator.laba-bersih') }}" @click="mobileMenu = false" class="p-2 rounded-lg text-slate-300 hover:bg-slate-900">Laba Bersih</a>
                    <a href="{{ route('kalkulator.gaji-karyawan') }}" @click="mobileMenu = false" class="p-2 rounded-lg text-slate-300 hover:bg-slate-900">Gaji Karyawan</a>
                    <a href="{{ route('kalkulator.pph-final') }}" @click="mobileMenu = false" class="p-2 rounded-lg text-slate-300 hover:bg-slate-900">PPh Final 0.5%</a>
                    <a href="{{ route('kalkulator.omzet-harian') }}" @click="mobileMenu = false" class="p-2 rounded-lg text-slate-300 hover:bg-slate-900">Omzet Harian</a>
                    <a href="{{ route('kalkulator.simulasi-what-if') }}" @click="mobileMenu = false" class="p-2 rounded-lg text-cyan-400 hover:bg-slate-900">Simulasi What-If</a>
                </div>
            </div>

            <div class="py-2 border-t border-slate-800/60">
                <div class="text-[10px] font-bold text-cyan-400 uppercase tracking-wider px-3 mb-1">Solusi Niche</div>
                <div class="grid grid-cols-2 gap-1 text-xs">
                    <a href="{{ route('solusi.show', 'kasir-warung') }}" @click="mobileMenu = false" class="p-2 rounded-lg text-slate-300 hover:bg-slate-900">Warung & Toko</a>
                    <a href="{{ route('solusi.show', 'kasir-cafe-kecil') }}" @click="mobileMenu = false" class="p-2 rounded-lg text-slate-300 hover:bg-slate-900">Kafe & Kedai</a>
                    <a href="{{ route('solusi.show', 'kasir-kios') }}" @click="mobileMenu = false" class="p-2 rounded-lg text-slate-300 hover:bg-slate-900">Kios Pulsa</a>
                    <a href="{{ route('solusi.show', 'kasir-laundry') }}" @click="mobileMenu = false" class="p-2 rounded-lg text-slate-300 hover:bg-slate-900">Laundry</a>
                    <a href="{{ route('solusi.show', 'kasir-salon') }}" @click="mobileMenu = false" class="p-2 rounded-lg text-slate-300 hover:bg-slate-900">Salon</a>
                    <a href="{{ route('solusi.show', 'kasir-barbershop') }}" @click="mobileMenu = false" class="p-2 rounded-lg text-slate-300 hover:bg-slate-900">Barbershop</a>
                    <a href="{{ route('solusi.show', 'kasir-bengkel-kecil') }}" @click="mobileMenu = false" class="p-2 rounded-lg text-slate-300 hover:bg-slate-900">Bengkel Kecil</a>
                    <a href="{{ route('solusi.show', 'kasir-irt') }}" @click="mobileMenu = false" class="p-2 rounded-lg text-slate-300 hover:bg-slate-900">Industri IRT</a>
                </div>
            </div>

            <div class="py-2 border-t border-slate-800/60">
                <a href="{{ route('template.index') }}" @click="mobileMenu = false" class="block py-2 px-3 rounded-lg text-slate-300 hover:bg-slate-900">Template Pembukuan Excel</a>
                <a href="{{ route('blog.index') }}" @click="mobileMenu = false" class="block py-2 px-3 rounded-lg text-slate-300 hover:bg-slate-900">Blog & Edukasi UMKM</a>
                <a href="{{ route('contact') }}" @click="mobileMenu = false" class="block py-2 px-3 rounded-lg text-slate-300 hover:bg-slate-900">Kontak Kami</a>
            </div>

            <div class="pt-4 border-t border-slate-800/80 grid grid-cols-2 gap-2 mt-2">
                <a href="{{ route('login') }}" class="py-3 rounded-xl bg-slate-900/80 border border-slate-800 text-center text-white font-semibold">Masuk</a>
                <a href="{{ route('register') }}" class="py-3 rounded-xl glow-btn text-center text-white font-bold shadow-lg shadow-indigo-500/20">Mulai Sekarang - Gratis</a>
            </div>
        </div>
    </header>

    <!-- ═══ MAIN CONTENT ═══ -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- ═══ FOOTER ═══ -->
    <footer class="border-t border-slate-900 bg-slate-950/80 pt-16 pb-12 mt-20 text-xs text-slate-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 pb-12">

                <!-- Col 1: Brand & Contact -->
                <div class="space-y-4 lg:col-span-2">
                    <div class="flex items-center gap-3">
                        <img src="https://cooca.id/assets/image/1785229034_logo_dark.png" alt="COOCA UMKM" class="h-8 w-auto">
                        <div class="border-l border-white/10 pl-3">
                            <span class="font-extrabold text-base tracking-tight text-white block">Cooca UMKM</span>
                            <span class="text-[10px] uppercase font-bold text-emerald-400 tracking-wider">Business Operating System</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                        Platform operasional bisnis 100% gratis untuk UMKM Indonesia. Dilengkapi kalkulator HPP presisi, pembukuan kas, kasir POS, dan asisten kecerdasan buatan (AI).
                    </p>
                    <div class="pt-2 text-xs space-y-1">
                        <div class="flex items-center gap-2 text-slate-300">
                            <i data-lucide="phone" class="w-4 h-4 text-emerald-400"></i>
                            <span>WhatsApp Resmi: <a href="https://wa.me/6282337499577" target="_blank" class="font-bold text-white hover:text-emerald-400">0823 3749 9577</a></span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-300">
                            <i data-lucide="mail" class="w-4 h-4 text-indigo-400"></i>
                            <span>Email: <a href="mailto:support@cooca.id" class="hover:text-white">support@cooca.id</a></span>
                        </div>
                    </div>
                </div>

                <!-- Col 2: Kalkulator Bisnis -->
                <div class="space-y-3">
                    <p class="font-bold text-white uppercase text-[11px] tracking-wider">Kalkulator Bisnis</p>
                    <nav class="flex flex-col space-y-2">
                        <a href="{{ route('kalkulator.hpp') }}" class="hover:text-white transition-colors">Kalkulator HPP</a>
                        <a href="{{ route('kalkulator.bep') }}" class="hover:text-white transition-colors">Kalkulator BEP</a>
                        <a href="{{ route('kalkulator.harga-jual') }}" class="hover:text-white transition-colors">Kalkulator Harga Jual</a>
                        <a href="{{ route('kalkulator.laba-bersih') }}" class="hover:text-white transition-colors">Kalkulator Laba Bersih</a>
                        <a href="{{ route('kalkulator.pph-final') }}" class="hover:text-white transition-colors">PPh Final UMKM 0.5%</a>
                        <a href="{{ route('kalkulator.simulasi-what-if') }}" class="text-cyan-400 hover:text-cyan-300 transition-colors">Simulasi What-If</a>
                    </nav>
                </div>

                <!-- Col 3: Solusi Vertikal & Template -->
                <div class="space-y-3">
                    <p class="font-bold text-white uppercase text-[11px] tracking-wider">Solusi &amp; Template</p>
                    <nav class="flex flex-col space-y-2">
                        <a href="{{ route('solusi.show', 'kasir-warung') }}" class="hover:text-white transition-colors">Kasir Toko &amp; Warung</a>
                        <a href="{{ route('solusi.show', 'kasir-cafe-kecil') }}" class="hover:text-white transition-colors">Kasir Kafe &amp; Kopi</a>
                        <a href="{{ route('solusi.show', 'kasir-laundry') }}" class="hover:text-white transition-colors">Kasir Laundry</a>
                        <a href="{{ route('solusi.show', 'kasir-irt') }}" class="hover:text-white transition-colors">Kasir Industri Rumah Tangga</a>
                        <a href="{{ route('template.index') }}" class="text-indigo-400 hover:text-indigo-300 font-bold transition-colors">Download Template Excel →</a>
                    </nav>
                </div>

                <!-- Col 4: Ekosistem COOCA.ID (Cross-Link) -->
                <div class="space-y-3">
                    <p class="font-bold text-white uppercase text-[11px] tracking-wider">Ekosistem Enterprise</p>
                    <nav class="flex flex-col space-y-2">
                        <a href="https://cooca.id" target="_blank" rel="noopener" class="hover:text-white transition-colors flex items-center gap-1">
                            <span>COOCA.ID Premium ERP</span>
                            <i data-lucide="external-link" class="w-3 h-3 text-slate-500"></i>
                        </a>
                        <a href="https://cooca.id/products" target="_blank" rel="noopener" class="hover:text-white transition-colors">ERP Bengkel &amp; Toko Sparepart</a>
                        <a href="https://cooca.id/products" target="_blank" rel="noopener" class="hover:text-white transition-colors">ERP Klinik &amp; Apotek</a>
                        <a href="https://cooca.id/products" target="_blank" rel="noopener" class="hover:text-white transition-colors">ERP Restoran Multi-Cabang</a>
                        <a href="https://cooca.id/affiliate" target="_blank" rel="noopener" class="text-emerald-400 hover:text-emerald-300">Program Partner &amp; Afiliasi (25%)</a>
                    </nav>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="border-t border-slate-900 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
                <p>© 2026 COOCA.ID. Platform Bisnis UMKM Indonesia. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('contact') }}" class="hover:text-slate-400">Kontak</a>
                    <a href="{{ route('sitemap.html') }}" class="hover:text-slate-400">Peta Situs</a>
                    <a href="{{ route('sitemap.xml') }}" target="_blank" class="hover:text-slate-400">Sitemap XML</a>
                    <a href="https://cooca.id/privacy" target="_blank" class="hover:text-slate-400">Privasi</a>
                    <a href="https://cooca.id/terms" target="_blank" class="hover:text-slate-400">Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
    <!-- AppAlert (Centralized Alert & Confirm System) -->
    <script src="{{ asset('js/app-alert.js') }}"></script>

    @stack('scripts')
</body>
</html>
