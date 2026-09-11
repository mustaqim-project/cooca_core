<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <!-- No-Flash Theme Bootstrap (Eliminates FOUC) -->
    <script>
        (function() {
            try {
                var theme = localStorage.getItem('cooca-theme') || 'light';
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (theme === 'dark' || (theme === 'system' && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {}
        })();
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Cooca UMKM' }} — cooca.id</title>

    <style>[x-cloak] { display: none !important; }</style>

    <!-- Google Fonts (Inter fallback, JetBrains Mono fallback) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', '"SF Pro Text"', '"SF Pro Display"', 'Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        apple: {
                            blue: '#007AFF',
                            'blue-dark': '#0A84FF',
                            green: '#34C759',
                            'green-dark': '#30D158',
                            orange: '#FF9500',
                            'orange-dark': '#FF9F0A',
                            red: '#FF3B30',
                            'red-dark': '#FF453A',
                            purple: '#AF52DE',
                            'purple-dark': '#BF5AF2',
                            teal: '#30B0C7',
                            'teal-dark': '#40C8E0',
                            indigo: '#5856D6',
                            'indigo-dark': '#5E5CE6',
                            yellow: '#FFCC00',
                            'yellow-dark': '#FFD60A',
                            gray: '#8E8E93',
                            gray2: '#AEAEB2',
                            gray3: '#C7C7CC',
                            gray4: '#D1D1D6',
                            gray5: '#E5E5EA',
                            gray6: '#F2F2F7',
                        },
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#bae0fd',
                            300: '#7cc5fb',
                            400: '#36a6f7',
                            500: '#007AFF',
                            600: '#0071E3',
                            700: '#0056b3',
                            800: '#00448f',
                            900: '#00336d',
                            950: '#001e44',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- AppAlert (Centralized Alert & Confirm System) -->
    <script src="{{ asset('js/app-alert.js') }}"></script>
    <style>
        /* Apple Alert Dialog Styling for SweetAlert2 */
        .swal2-popup {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            border-radius: 14px !important;
            color: #000000 !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2) !important;
            width: 290px !important;
            padding: 1.25rem !important;
        }
        .dark .swal2-popup {
            background: rgba(44, 44, 46, 0.95) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #FFFFFF !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5) !important;
        }
        .swal2-title {
            color: inherit !important;
            font-size: 1.0625rem !important; /* 17px Headline */
            font-weight: 600 !important;
            margin: 0.25rem 0 !important;
            letter-spacing: -0.015em !important;
        }
        .swal2-html-container {
            color: rgba(60, 60, 67, 0.6) !important;
            font-size: 0.8125rem !important; /* 13px Footnote */
            line-height: 1.35 !important;
            margin: 0.25rem 0 1rem 0 !important;
        }
        .dark .swal2-html-container {
            color: rgba(235, 235, 245, 0.6) !important;
        }
        .swal2-actions {
            width: 100% !important;
            margin: 0 !important;
            padding-top: 0.75rem !important;
            border-top: 1px solid rgba(60, 60, 67, 0.12) !important;
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 0.5rem !important;
        }
        .dark .swal2-actions {
            border-top-color: rgba(255, 255, 255, 0.1) !important;
        }
        .swal2-confirm {
            background-color: #007AFF !important;
            color: #ffffff !important;
            font-size: 0.875rem !important; /* 14px */
            font-weight: 600 !important;
            border-radius: 8px !important;
            padding: 0.5rem 0.75rem !important;
            min-height: 36px !important;
            box-shadow: none !important;
            transition: opacity 0.15s ease !important;
        }
        .swal2-confirm:active {
            opacity: 0.8 !important;
            transform: scale(0.97) !important;
        }
        .swal2-cancel {
            background-color: rgba(0, 0, 0, 0.05) !important;
            color: #007AFF !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            border-radius: 8px !important;
            padding: 0.5rem 0.75rem !important;
            min-height: 36px !important;
            transition: opacity 0.15s ease !important;
        }
        .dark .swal2-cancel {
            background-color: rgba(255, 255, 255, 0.08) !important;
            color: #0A84FF !important;
        }
        .swal2-cancel:active {
            opacity: 0.8 !important;
            transform: scale(0.97) !important;
        }
    </style>

    <style>
        /* === COOCA DESIGN SYSTEM v2.0 TOKENS (APPLE HIG STANDARD) === */
        :root {
            /* Surface & Background */
            --bg:          #F2F2F7;   /* Secondary system background (grouped/macOS container) */
            --surface:     #FFFFFF;   /* Primary system background / cards */
            --surface-2:   #F2F2F7;   /* System Gray 6 */
            --surface-3:   #E5E5EA;   /* System Gray 5 (elevated hover surface) */

            /* Typography */
            --text-1:      #000000;             /* Primary label */
            --text-2:      rgba(60, 60, 67, 0.6);   /* Secondary label (60%) */
            --text-3:      rgba(60, 60, 67, 0.3);   /* Tertiary label (30%) */
            --text-dis:    rgba(60, 60, 67, 0.18);  /* Quaternary label (18%) */

            /* Borders (Hairline Separators) */
            --border:      rgba(60, 60, 67, 0.08);  /* Hairline border */
            --border-sub:  rgba(60, 60, 67, 0.04);
            --border-str:  rgba(60, 60, 67, 0.18);

            /* Brand Colors (System Blue Master Accent) */
            --brand:       #007AFF;   /* Primary System Blue */
            --brand-hover: #0071E3;
            --brand-light: rgba(0, 122, 255, 0.1);
            --brand-text:  #007AFF;

            /* Apple Semantic Status Tokens */
            --color-accent:    #007AFF;
            --color-success:   #34C759;
            --color-warning:   #FF9500;
            --color-danger:    #FF3B30;
            --color-ai:        #AF52DE;
            --color-info:      #5856D6;
            --color-teal:      #30B0C7;

            --success:        #34C759;
            --success-bg:     rgba(52, 199, 89, 0.12);
            --success-border: rgba(52, 199, 89, 0.25);
            --success-text:   #248A3D;

            --warn:           #FF9500;
            --warn-bg:        rgba(255, 149, 0, 0.12);
            --warn-border:    rgba(255, 149, 0, 0.25);
            --warn-text:      #B25E00;

            --danger:         #FF3B30;
            --danger-bg:      rgba(255, 59, 48, 0.12);
            --danger-border:  rgba(255, 59, 48, 0.25);
            --danger-text:    #C41E17;

            --info:           #5856D6;
            --info-bg:        rgba(88, 86, 214, 0.12);
            --info-border:    rgba(88, 86, 214, 0.25);
            --info-text:      #413FA6;

            /* Input Controls */
            --input-bg:       rgba(0, 0, 0, 0.04);
            --input-border:   transparent;
            --input-focus:    #007AFF;

            /* Navigation & Sidebar (macOS Source List Vibrancy) */
            --nav-bg:             rgba(242, 242, 247, 0.8);
            --nav-border:         rgba(60, 60, 67, 0.08);
            --nav-active:         #007AFF;
            --nav-active-text:    #FFFFFF;
            --nav-active-border:  transparent;

            /* Overlays & Shadows (Subtle Diffused Elevation) */
            --overlay:    rgba(0, 0, 0, 0.25);
            --shadow-sm:  0 1px 2px rgba(0, 0, 0, 0.04);
            --shadow-md:  0 2px 8px rgba(0, 0, 0, 0.05);
            --shadow-lg:  0 8px 24px rgba(0, 0, 0, 0.06);
            --shadow-xl:  0 20px 50px rgba(0, 0, 0, 0.15);

            /* Apple Squircle Radius Hierarchy */
            --r-sm:  8px;    /* Small buttons, inline badges */
            --r-md:  10px;   /* Regular buttons, form inputs */
            --r-lg:  14px;   /* Data cards, KPI tiles */
            --r-xl:  16px;   /* Modals, large panels */
            --r-2xl: 20px;   /* Sheet headers, big containers */
        }

        .dark {
            /* Surface & Background */
            --bg:          #1E1E1E;   /* macOS desktop window background */
            --surface:     #1C1C1E;   /* Secondary system background (dark) */
            --surface-2:   #2C2C2E;   /* Tertiary system background (elevated card) */
            --surface-3:   #3A3A3C;   /* System Gray 4 */

            /* Typography */
            --text-1:      #FFFFFF;
            --text-2:      rgba(235, 235, 245, 0.6);
            --text-3:      rgba(235, 235, 245, 0.3);
            --text-dis:    rgba(235, 235, 245, 0.18);

            /* Borders */
            --border:      rgba(255, 255, 255, 0.08);
            --border-sub:  rgba(255, 255, 255, 0.04);
            --border-str:  rgba(255, 255, 255, 0.15);

            /* Brand Colors (System Blue Dark) */
            --brand:       #0A84FF;
            --brand-hover: #007AFF;
            --brand-light: rgba(10, 132, 255, 0.15);
            --brand-text:  #0A84FF;

            /* Apple Semantic Status Tokens */
            --color-accent:    #0A84FF;
            --color-success:   #30D158;
            --color-warning:   #FF9F0A;
            --color-danger:    #FF453A;
            --color-ai:        #BF5AF2;
            --color-info:      #5E5CE6;
            --color-teal:      #40C8E0;

            --success:        #30D158;
            --success-bg:     rgba(48, 209, 88, 0.14);
            --success-border: rgba(48, 209, 88, 0.28);
            --success-text:   #30D158;

            --warn:           #FF9F0A;
            --warn-bg:        rgba(255, 159, 10, 0.14);
            --warn-border:    rgba(255, 159, 10, 0.28);
            --warn-text:      #FF9F0A;

            --danger:         #FF453A;
            --danger-bg:      rgba(255, 69, 58, 0.14);
            --danger-border:  rgba(255, 69, 58, 0.28);
            --danger-text:    #FF453A;

            --info:           #5E5CE6;
            --info-bg:        rgba(94, 92, 230, 0.14);
            --info-border:    rgba(94, 92, 230, 0.28);
            --info-text:      #5E5CE6;

            /* Input Controls */
            --input-bg:       rgba(255, 255, 255, 0.06);
            --input-border:   transparent;
            --input-focus:    #0A84FF;

            /* Navigation & Sidebar (Dark Vibrancy) */
            --nav-bg:             rgba(28, 28, 30, 0.8);
            --nav-border:         rgba(255, 255, 255, 0.08);
            --nav-active:         #0A84FF;
            --nav-active-text:    #FFFFFF;
            --nav-active-border:  transparent;

            /* Overlays & Shadows */
            --overlay:    rgba(0, 0, 0, 0.65);
            --shadow-sm:  0 1px 2px rgba(0, 0, 0, 0.3);
            --shadow-md:  0 4px 12px rgba(0, 0, 0, 0.4);
            --shadow-lg:  0 12px 30px rgba(0, 0, 0, 0.5);
            --shadow-xl:  0 20px 50px rgba(0, 0, 0, 0.6);
        }

        /* Responsive root foundation */
        html {
            box-sizing: border-box;
            -webkit-text-size-adjust: 100%;
            scroll-behavior: smooth;
            overflow-x: hidden;
            background-color: var(--bg);
            color: var(--text-1);
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
        }

        *, *:before, *:after {
            box-sizing: inherit;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
            min-height: 100vh;
            min-height: 100dvh;
            overflow-x: hidden;
            background-color: var(--bg);
            color: var(--text-1);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Universal Adaptive Modal Dialogs */
        .fixed.inset-0 .glass-card,
        .fixed.inset-0 .glass-panel,
        .app-modal-dialog {
            width: 100% !important;
            max-width: min(calc(100vw - 1.5rem), var(--modal-max-width, 32rem)) !important;
            max-height: min(92dvh, calc(100vh - 2rem)) !important;
            overflow-y: auto !important;
            overscroll-behavior: contain !important;
            -webkit-overflow-scrolling: touch !important;
        }

        /* Universal Responsive Table Utilities */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-x: contain;
        }
        .table-responsive table {
            min-width: 580px;
            width: 100%;
        }
        .table-responsive-wide table {
            min-width: 760px;
            width: 100%;
        }
        .table-responsive th,
        .table-responsive td.cell-nowrap {
            white-space: nowrap;
        }

        /* Touch & form controls optimization */
        input, select, textarea, button {
            touch-action: manipulation;
        }

        /* Standard Glassmorphic Surfaces (Apple HIG Translucent Materials) */
        .glass-nav {
            background: var(--nav-bg);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-right: 1px solid var(--nav-border);
        }

        .glass-header {
            background: var(--surface);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid var(--nav-border);
        }

        .glass-card {
            background: var(--surface);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
        }

        .glass-card-interactive {
            background: var(--surface);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            transition: all 0.15s ease-out;
        }

        .glass-card-interactive:hover {
            border-color: rgba(0, 122, 255, 0.3);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        /* Standardized Apple Button Utility Classes (§6.3 & §23) */
        .btn-apple-filled {
            height: 2.25rem; /* 36px desktop */
            padding-left: 1rem;
            padding-right: 1rem;
            border-radius: var(--r-md);
            font-size: 0.8125rem; /* 13px */
            font-weight: 600;
            color: #ffffff;
            background-color: #007AFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            box-shadow: 0 1px 2px rgba(0, 122, 255, 0.25);
            transition: all 0.15s ease-out;
            cursor: pointer;
        }
        .btn-apple-filled:hover {
            background-color: #0071E3;
        }
        .btn-apple-filled:active {
            transform: scale(0.97);
            opacity: 0.8;
        }

        .btn-apple-tinted {
            height: 2.25rem;
            padding-left: 1rem;
            padding-right: 1rem;
            border-radius: var(--r-md);
            font-size: 0.8125rem;
            font-weight: 600;
            color: #007AFF;
            background-color: rgba(0, 122, 255, 0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            transition: all 0.15s ease-out;
            cursor: pointer;
        }
        .btn-apple-tinted:hover {
            background-color: rgba(0, 122, 255, 0.15);
        }
        .btn-apple-tinted:active {
            transform: scale(0.97);
            opacity: 0.7;
        }
        .dark .btn-apple-tinted {
            color: #0A84FF;
            background-color: rgba(10, 132, 255, 0.15);
        }
        .dark .btn-apple-tinted:hover {
            background-color: rgba(10, 132, 255, 0.22);
        }

        .btn-apple-gray {
            height: 2.25rem;
            padding-left: 1rem;
            padding-right: 1rem;
            border-radius: var(--r-md);
            font-size: 0.8125rem;
            font-weight: 500;
            color: rgba(0, 0, 0, 0.8);
            background-color: rgba(0, 0, 0, 0.06);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            transition: all 0.15s ease-out;
            cursor: pointer;
        }
        .btn-apple-gray:hover {
            background-color: rgba(0, 0, 0, 0.09);
        }
        .btn-apple-gray:active {
            transform: scale(0.97);
            opacity: 0.8;
        }
        .dark .btn-apple-gray {
            color: rgba(255, 255, 255, 0.85);
            background-color: rgba(255, 255, 255, 0.08);
        }
        .dark .btn-apple-gray:hover {
            background-color: rgba(255, 255, 255, 0.12);
        }

        .btn-apple-plain {
            height: 2.25rem;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            border-radius: var(--r-sm);
            font-size: 0.8125rem;
            font-weight: 500;
            color: #007AFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            transition: all 0.15s ease-out;
            cursor: pointer;
        }
        .btn-apple-plain:hover {
            background-color: rgba(0, 122, 255, 0.08);
        }
        .btn-apple-plain:active {
            transform: scale(0.97);
            opacity: 0.8;
        }
        .dark .btn-apple-plain {
            color: #0A84FF;
        }

        .btn-apple-danger {
            height: 2.25rem;
            padding-left: 1rem;
            padding-right: 1rem;
            border-radius: var(--r-md);
            font-size: 0.8125rem;
            font-weight: 600;
            color: #ffffff;
            background-color: #FF3B30;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            box-shadow: 0 1px 2px rgba(255, 59, 48, 0.25);
            transition: all 0.15s ease-out;
            cursor: pointer;
        }
        .btn-apple-danger:hover {
            background-color: #E0352B;
        }
        .btn-apple-danger:active {
            transform: scale(0.97);
            opacity: 0.8;
        }

        /* Backward compatibility mappings for legacy classes */
        .btn-brand-primary {
            background: #007AFF;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.8125rem;
            padding: 0.5rem 1rem;
            border-radius: var(--r-md);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            box-shadow: 0 1px 2px rgba(0, 122, 255, 0.25);
            transition: all 0.15s ease-out;
        }
        .btn-brand-primary:hover {
            background: #0071E3;
        }
        .btn-brand-primary:active {
            transform: scale(0.97);
            opacity: 0.8;
        }

        .btn-brand-secondary {
            background: rgba(0, 0, 0, 0.06);
            color: rgba(0, 0, 0, 0.8);
            font-weight: 500;
            font-size: 0.8125rem;
            padding: 0.5rem 1rem;
            border-radius: var(--r-md);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            transition: all 0.15s ease-out;
        }
        .dark .btn-brand-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.85);
        }
        .btn-brand-secondary:hover {
            background: rgba(0, 0, 0, 0.09);
        }
        .dark .btn-brand-secondary:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(60, 60, 67, 0.2);
            border-radius: 999px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: rgba(235, 235, 245, 0.2);
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(60, 60, 67, 0.35);
        }
        .dark ::-webkit-scrollbar-thumb:hover {
            background: rgba(235, 235, 245, 0.35);
        }

        /* Global truncation for topbar headers */
        .app-topbar-title h1,
        .app-topbar-title p {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Topbar Header Responsive Base (Apple macOS Toolbar Architecture) */
        @media (min-width: 1024px) {
            .app-topbar {
                height: 3.5rem; /* 56px macOS standard toolbar height */
            }
        }

        @media (max-width: 1023px) {
            .app-sidebar {
                width: 280px !important;
                max-width: 86vw;
            }

            .app-topbar {
                min-height: 3.25rem;
                height: auto;
                padding: 0.5rem 0.875rem;
                gap: 0.5rem;
            }

            .app-topbar-title {
                flex: 1 1 auto;
                min-width: 0;
            }

            .app-topbar-actions {
                flex: 0 0 auto;
                min-width: 0;
                display: flex;
                align-items: center;
                gap: 0.375rem;
            }
        }

        @media (max-width: 639px) {
            .app-topbar {
                padding: 0.5rem 0.75rem;
                gap: 0.375rem;
            }

            .app-topbar-title h1 {
                font-size: 0.875rem;
                line-height: 1.25rem;
            }

            .app-topbar-actions {
                gap: 0.25rem;
            }
        }

        @media (max-width: 380px) {
            .app-topbar {
                padding: 0.375rem 0.5rem;
                gap: 0.25rem;
            }

            .app-topbar-title h1 {
                font-size: 0.8125rem;
            }

            .app-topbar-actions {
                gap: 0.2rem;
            }
        }
    </style>
</head>

<body class="h-full bg-[#F2F2F7] dark:bg-[#1E1E1E] text-black dark:text-white antialiased selection:bg-[#007AFF]/20 selection:text-[#007AFF]" x-data="{
    sidebarOpen: false,
    sidebarCollapsed: localStorage.getItem('cooca-sidebar-collapsed') === 'true',
    toggleSidebarCollapse() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        localStorage.setItem('cooca-sidebar-collapsed', this.sidebarCollapsed);
        window.dispatchEvent(new CustomEvent('sidebar-collapsed-changed', { detail: { collapsed: this.sidebarCollapsed } }));
        this.$nextTick(() => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    },
    comingSoonOpen: false,
    comingSoonFeature: { title: '', icon: 'sparkles', desc: '', color: 'purple' },
    openComingSoon(feature) {
        this.comingSoonFeature = {
            title: feature?.title || 'Fitur Baru',
            icon: feature?.icon || 'sparkles',
            desc: feature?.desc || '',
            color: feature?.color || 'purple'
        };
        this.comingSoonOpen = true;
    },
    init() {
        window.addEventListener('tour-open-sidebar', () => {
            this.sidebarOpen = true;
            this.sidebarCollapsed = false;
        });
        window.addEventListener('tour-close-sidebar', () => { this.sidebarOpen = false; });
        window.addEventListener('cooca-coming-soon', (e) => { this.openComingSoon(e.detail); });
    }
}">
    @php
        $activeBiz = \App\Support\Context::business();
        $navEntitlement = app(\App\Domain\Billing\EntitlementService::class);
        $navUsage = $activeBiz ? $navEntitlement->getUsageSummary($activeBiz) : null;
        $isCorePlan = $navUsage['is_core'] ?? false;

        $canAccessSales =
            \App\Support\Context::hasPermission('pos.terminal') ||
            \App\Support\Context::hasPermission('invoices.view') ||
            \App\Support\Context::hasPermission('sales.view') ||
            \App\Support\Context::hasPermission('customers.view');
        $canAccessPurchasing =
            \App\Support\Context::hasPermission('purchasing.view') ||
            \App\Support\Context::hasPermission('receiving.manage');
        $canAccessInventory =
            \App\Support\Context::hasPermission('products.view') ||
            \App\Support\Context::hasPermission('materials.view') ||
            \App\Support\Context::hasPermission('inventory.view') ||
            \App\Support\Context::hasPermission('inventory.manage');
        $canAccessCosting =
            \App\Support\Context::hasPermission('costing.view_margin') ||
            \App\Support\Context::hasPermission('costing.manage') ||
            \App\Support\Context::hasPermission('labor_machines.view');
        $canAccessFinance =
            \App\Support\Context::hasPermission('accounting.view') ||
            \App\Support\Context::hasPermission('expenses.view');
        $canAccessReports =
            \App\Support\Context::hasPermission('reports.view') ||
            \App\Support\Context::hasPermission('pos.reports');
        $canAccessSettings =
            \App\Support\Context::hasPermission('settings.view') ||
            \App\Support\Context::hasPermission('roles.view') ||
            \App\Support\Context::hasPermission('billing.view') ||
            \App\Support\Context::isOwner();
        $canAccessRoles =
            \App\Support\Context::hasPermission('roles.view') ||
            \App\Support\Context::isOwner();
        $canAccessBilling =
            \App\Support\Context::hasPermission('billing.view') ||
            \App\Support\Context::isOwner();
        $canAccessMasterData =
            \App\Support\Context::hasPermission('master_data.suppliers.view') ||
            \App\Support\Context::hasPermission('master_data.material_categories.view') ||
            \App\Support\Context::hasPermission('master_data.product_categories.view') ||
            \App\Support\Context::hasPermission('master_data.units.view');
    @endphp
    <div class="min-h-full flex flex-col lg:flex-row">

        <!-- Mobile Sidebar Backdrop (Apple Frosted Dimmer) -->
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/30 backdrop-blur-[2px] z-40 lg:hidden"
            @click="sidebarOpen = false" style="display: none;"></div>

        <!-- Sidebar Navigation (Apple HIG / macOS Sonoma Edition) -->
        @include('layouts.partials.sidebar', compact('activeBiz', 'navEntitlement', 'navUsage', 'isCorePlan', 'canAccessSales', 'canAccessPurchasing', 'canAccessInventory', 'canAccessCosting', 'canAccessFinance', 'canAccessReports', 'canAccessSettings', 'canAccessRoles', 'canAccessBilling', 'canAccessMasterData'))

        <!-- Main Content Area (macOS Window Canvas) -->
        <div :class="sidebarCollapsed ? 'lg:pl-[76px]' : 'lg:pl-[268px]'"
            class="flex-1 flex flex-col min-h-screen min-w-0 transition-all duration-250 ease-out bg-[#F2F2F7] dark:bg-[#1E1E1E]">

            <!-- Topbar Header (Apple macOS Toolbar Architecture) -->
            @include('layouts.partials.topbar', compact('activeBiz', 'navEntitlement', 'navUsage', 'isCorePlan'))

            <!-- Main Page Content -->
            <main class="flex-1 p-3.5 sm:p-5 md:p-6 lg:p-7 space-y-5 sm:space-y-6 min-w-0 pb-28 lg:pb-10 max-w-[1400px] w-full mx-auto">
                {{-- Flash success & error notifications are handled by AppAlert floating toasts in footer scripts to avoid duplicate UI banners --}}
                @if (isset($errors) && $errors->any())
                    <div class="p-3.5 rounded-[12px] bg-[#FF3B30]/10 dark:bg-[#FF453A]/15 border border-[#FF3B30]/20 text-[#C41E17] dark:text-[#FF453A] space-y-1">
                        <div class="flex items-center gap-2 font-semibold text-[13px]">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-[#FF3B30] dark:text-[#FF453A] shrink-0"></i>
                            <span>Terdapat kesalahan input:</span>
                        </div>
                        <ul class="list-disc list-inside text-[12px] space-y-0.5 pl-2 opacity-90">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- ========================================== -->
            <!-- GLOBAL ZERO-NAVIGATION AJAX MODALS & TOASTS -->
            <!-- ========================================== -->
            <div x-data="{
                toastList: [],
                showExpenseModal: false,
                showStockInModal: false,
                showMaterialModal: false,
                showMobileActionSheet: false,
                expenseForm: { name: '', amount: '', category: 'Operasional Toko', payment_method: 'cash', notes: '' },
                stockInForm: { material_id: '', product_id: '', quantity: 1, unit_cost: '', supplier_name: '', notes: '' },
                materialForm: { name: '', cost_per_unit: '', unit_id: '', category_id: '', sku: '' },
                isSubmitting: false,

                init() {
                    window.addEventListener('cooca-toast', (e) => {
                        this.addToast(e.detail.message, e.detail.type || 'success');
                    });
                    window.addEventListener('open-quick-expense', () => { this.showExpenseModal = true; });
                    window.addEventListener('open-quick-stockin', () => { this.showStockInModal = true; });
                    window.addEventListener('open-quick-material', () => { this.showMaterialModal = true; });
                    window.addEventListener('open-mobile-actions', () => { this.showMobileActionSheet = true; });
                },

                addToast(msg, type = 'success') {
                    const id = Date.now();
                    this.toastList.push({ id, msg, type });
                    setTimeout(() => {
                        this.toastList = this.toastList.filter(t => t.id !== id);
                    }, 4000);
                },

                async submitQuickExpense() {
                    if (!this.expenseForm.name || !this.expenseForm.amount) return;
                    this.isSubmitting = true;
                    try {
                        const res = await fetch('{{ route('dashboard.quick-expense') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.expenseForm)
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.addToast(data.message, 'success');
                            this.showExpenseModal = false;
                            this.expenseForm = { name: '', amount: '', category: 'Operasional Toko', payment_method: 'cash', notes: '' };
                            window.dispatchEvent(new CustomEvent('expense-added', { detail: data }));
                        } else {
                            this.addToast(data.message || 'Gagal menyimpan pengeluaran', 'error');
                        }
                    } catch (err) {
                        this.addToast('Terjadi kesalahan koneksi', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async submitQuickStockIn() {
                    if (!this.stockInForm.quantity || !this.stockInForm.unit_cost) return;
                    this.isSubmitting = true;
                    try {
                        const res = await fetch('{{ route('dashboard.quick-stock-in') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.stockInForm)
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.addToast(data.message, 'success');
                            this.showStockInModal = false;
                            this.stockInForm = { material_id: '', product_id: '', quantity: 1, unit_cost: '', supplier_name: '', notes: '' };
                            window.dispatchEvent(new CustomEvent('stock-in-added', { detail: data }));
                        } else {
                            this.addToast(data.message || 'Gagal menambah stok', 'error');
                        }
                    } catch (err) {
                        this.addToast('Terjadi kesalahan koneksi', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async submitQuickMaterial() {
                    if (!this.materialForm.name || !this.materialForm.cost_per_unit) return;
                    this.isSubmitting = true;
                    try {
                        const res = await fetch('{{ route('dashboard.quick-material') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.materialForm)
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.addToast(data.message, 'success');
                            this.showMaterialModal = false;
                            this.materialForm = { name: '', cost_per_unit: '', unit_id: '', category_id: '', sku: '' };
                            window.dispatchEvent(new CustomEvent('material-added', { detail: data.material }));
                        } else {
                            this.addToast(data.message || 'Gagal menambah bahan baku', 'error');
                        }
                    } catch (err) {
                        this.addToast('Terjadi kesalahan koneksi', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }">

                <!-- Floating Toasts Container (Apple Centered Top Banner) -->
                <div class="fixed top-4 left-1/2 -translate-x-1/2 z-50 flex flex-col items-center gap-2 pointer-events-none w-full max-w-sm px-4">
                    <template x-for="t in toastList" :key="t.id">
                        <div x-transition:enter="transition ease-out duration-250"
                            x-transition:enter-start="opacity-0 -translate-y-3 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 -translate-y-2 scale-90"
                            class="p-3 px-4 rounded-[14px] border border-black/5 dark:border-white/10 shadow-[0_8px_30px_rgba(0,0,0,0.12)] backdrop-blur-xl pointer-events-auto flex items-center gap-2.5 text-[13px] font-medium bg-white/95 dark:bg-[#2C2C2E]/95 text-black dark:text-white">
                            <span class="w-2 h-2 rounded-full shrink-0"
                                :class="t.type === 'error' ? 'bg-[#FF3B30]' : 'bg-[#34C759]'"></span>
                            <span x-text="t.msg" class="flex-1 leading-snug"></span>
                        </div>
                    </template>
                </div>

                <!-- Modal 1: Quick Expense (Apple Centered Floating Sheet) -->
                <div x-show="showExpenseModal" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]"
                    style="display: none;">
                    <div class="w-full max-w-md rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.2)] p-5 space-y-4"
                        @click.outside="showExpenseModal = false">
                        <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-[8px] bg-[#FF9500]/12 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center">
                                    <i data-lucide="receipt" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">Catat Pengeluaran Cepat</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Jurnal otomatis operasional bisnis</p>
                                </div>
                            </div>
                            <button type="button" @click="showExpenseModal = false"
                                class="w-7 h-7 rounded-full flex items-center justify-center text-black/40 hover:text-black/70 dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <form @submit.prevent="submitQuickExpense" class="space-y-3 text-[13px]">
                            <div>
                                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Nama / Keterangan Biaya *</label>
                                <input type="text" x-model="expenseForm.name" required
                                    placeholder="Contoh: Gas Elpiji 3kg, Plastik Kresek"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[14px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Nominal (Rp) *</label>
                                    <input type="number" x-model.number="expenseForm.amount" required min="100"
                                        placeholder="25000"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[14px] text-black dark:text-white tabular-nums placeholder:text-black/30 dark:placeholder:text-white/30 focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition">
                                </div>
                                <div>
                                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Metode Bayar</label>
                                    <select x-model="expenseForm.payment_method"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition">
                                        <option value="cash">Kas Tunai (Laci)</option>
                                        <option value="bank">Transfer Bank</option>
                                        <option value="qris">QRIS / e-Wallet</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Kategori Biaya</label>
                                <select x-model="expenseForm.category"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition">
                                    <option value="Operasional Toko">Operasional Toko</option>
                                    <option value="Bahan Habis Pakai">Bahan Habis Pakai (Plastik/Kemasan)</option>
                                    <option value="Listrik, Air & Gas">Listrik, Air & Gas</option>
                                    <option value="Transportasi & Logistik">Transportasi & Logistik</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div class="flex justify-end gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                                <button type="button" @click="showExpenseModal = false" class="btn-apple-gray">
                                    Batal
                                </button>
                                <button type="submit" :disabled="isSubmitting" class="btn-apple-filled">
                                    <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Pengeluaran'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal 2: Quick Instant Stock-In (Apple Sheet) -->
                <div x-show="showStockInModal" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]"
                    style="display: none;">
                    <div class="w-full max-w-md rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.2)] p-5 space-y-4"
                        @click.outside="showStockInModal = false">
                        <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-[8px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center">
                                    <i data-lucide="package-plus" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">Beli Stok Masuk Cepat</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Tambah persediaan &amp; valuasi aset</p>
                                </div>
                            </div>
                            <button type="button" @click="showStockInModal = false"
                                class="w-7 h-7 rounded-full flex items-center justify-center text-black/40 hover:text-black/70 dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <form @submit.prevent="submitQuickStockIn" class="space-y-3 text-[13px]">
                            <div>
                                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Bahan Baku / Produk *</label>
                                <select x-model="stockInForm.material_id" required
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition">
                                    <option value="">-- Pilih Bahan Baku --</option>
                                    @php
                                        $modalMaterials = $activeBiz ? \Illuminate\Support\Facades\Cache::remember("layout_modal_mat_{$activeBiz->id}", 60, function () use ($activeBiz) {
                                            return \App\Models\Material::where('business_id', $activeBiz->id)
                                                ->with('latestPrice')
                                                ->orderBy('name')
                                                ->get()
                                                ->map(fn ($m) => [
                                                    'id' => (string) $m->id,
                                                    'name' => (string) $m->name,
                                                    'price' => (float) ($m->latestPrice?->purchase_price ?? 0),
                                                ])
                                                ->all();
                                        }) : [];
                                    @endphp
                                    @foreach ($modalMaterials as $m)
                                        <option value="{{ $m['id'] }}">{{ $m['name'] }} (HPP: Rp
                                            {{ number_format((float) $m['price'], 0, ',', '.') }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Jumlah Masuk *</label>
                                    <input type="number" x-model.number="stockInForm.quantity" required
                                        min="0.01" step="any" placeholder="10"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[14px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition">
                                </div>
                                <div>
                                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Harga Beli / Satuan (Rp) *</label>
                                    <input type="number" x-model.number="stockInForm.unit_cost" required
                                        min="0" placeholder="15000"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[14px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Nama Pemasok / Toko Beli</label>
                                <input type="text" x-model="stockInForm.supplier_name"
                                    placeholder="Contoh: Pasar Induk, Toko Bahan Kue Maju"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[14px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition">
                            </div>

                            <div class="flex justify-end gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                                <button type="button" @click="showStockInModal = false" class="btn-apple-gray">
                                    Batal
                                </button>
                                <button type="submit" :disabled="isSubmitting" class="btn-apple-filled">
                                    <span x-text="isSubmitting ? 'Memproses...' : 'Tambah Stok Masuk'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal 3: Quick Create Material (Apple Sheet) -->
                <div x-show="showMaterialModal" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]"
                    style="display: none;">
                    <div class="w-full max-w-md rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.2)] p-5 space-y-4"
                        @click.outside="showMaterialModal = false">
                        <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-[8px] bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                                    <i data-lucide="boxes" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">Tambah Bahan Baku Cepat</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Daftarkan bahan baku baru tanpa pindah layar</p>
                                </div>
                            </div>
                            <button type="button" @click="showMaterialModal = false"
                                class="w-7 h-7 rounded-full flex items-center justify-center text-black/40 hover:text-black/70 dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <form @submit.prevent="submitQuickMaterial" class="space-y-3 text-[13px]">
                            <div>
                                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Nama Bahan Baku *</label>
                                <input type="text" x-model="materialForm.name" required
                                    placeholder="Contoh: Tepung Terigu Segitiga Biru"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[14px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Harga Beli Dasar (Rp) *</label>
                                    <input type="number" x-model.number="materialForm.cost_per_unit" required
                                        min="0" placeholder="12000"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[14px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition">
                                </div>
                                <div>
                                    <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">Satuan Ukur</label>
                                    <select x-model="materialForm.unit_id"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition">
                                        <option value="">Pilih Satuan</option>
                                        @php
                                            $modalUnits = $activeBiz ? \Illuminate\Support\Facades\Cache::remember("layout_modal_units_{$activeBiz->id}", 300, function () use ($activeBiz) {
                                                return \App\Models\Unit::where('business_id', $activeBiz->id)
                                                    ->orWhereNull('business_id')
                                                    ->orderBy('name')
                                                    ->get()
                                                    ->map(fn ($u) => [
                                                        'id' => (string) $u->id,
                                                        'name' => (string) $u->name,
                                                        'code' => (string) $u->code,
                                                    ])
                                                    ->all();
                                            }) : [];
                                        @endphp
                                        @foreach ($modalUnits as $u)
                                            <option value="{{ $u['id'] }}">{{ $u['name'] }}
                                                ({{ $u['code'] }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                                <button type="button" @click="showMaterialModal = false" class="btn-apple-gray">
                                    Batal
                                </button>
                                <button type="submit" :disabled="isSubmitting" class="btn-apple-filled">
                                    <span x-text="isSubmitting ? 'Menyimpan...' : 'Tambah Bahan'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal 4: Mobile Action Sheet Bottom Modal (iOS 18 Sheet) -->
                <div x-show="showMobileActionSheet" x-transition:enter="transition-opacity ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-end justify-center bg-black/30 backdrop-blur-[2px] lg:hidden"
                    style="display: none;">
                    <div class="rounded-t-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl w-full border-t border-black/5 dark:border-white/10 shadow-[0_-10px_40px_rgba(0,0,0,0.15)] p-5 pt-3 space-y-3 max-h-[85vh] overflow-y-auto"
                        @click.outside="showMobileActionSheet = false"
                        x-transition:enter="transition ease-out duration-250"
                        x-transition:enter-start="translate-y-full"
                        x-transition:enter-end="translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="translate-y-0"
                        x-transition:leave-end="translate-y-full">

                        <!-- Grabber Bar -->
                        <div class="w-9 h-1 rounded-full bg-black/20 dark:bg-white/20 mx-auto"></div>

                        <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-2">
                            <h3 class="text-[15px] font-semibold text-black dark:text-white">Aksi Cepat Instan</h3>
                            <button type="button" @click="showMobileActionSheet = false"
                                class="text-black/40 dark:text-white/40 p-1">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 text-[13px]">
                            <button type="button" @click="showMobileActionSheet = false; showExpenseModal = true"
                                class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition active:scale-[0.97]">
                                <div class="w-8 h-8 rounded-[8px] bg-[#FF9500]/12 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center">
                                    <i data-lucide="receipt" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-black dark:text-white">Catat Beban</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">Biaya operasional</div>
                                </div>
                            </button>

                            <button type="button" @click="showMobileActionSheet = false; showStockInModal = true"
                                class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition active:scale-[0.97]">
                                <div class="w-8 h-8 rounded-[8px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center">
                                    <i data-lucide="package-plus" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-black dark:text-white">Beli Stok</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">Tambah persediaan</div>
                                </div>
                            </button>

                            <button type="button" @click="showMobileActionSheet = false; showMaterialModal = true"
                                class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition active:scale-[0.97]">
                                <div class="w-8 h-8 rounded-[8px] bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                                    <i data-lucide="boxes" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-black dark:text-white">Bahan Baku</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">Master bahan resep</div>
                                </div>
                            </button>

                            <a href="{{ route('calculator.index') }}"
                                class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition block active:scale-[0.97]">
                                <div class="w-8 h-8 rounded-[8px] bg-[#AF52DE]/12 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center">
                                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-black dark:text-white">Hitung HPP</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">3-Pilar harga jual</div>
                                </div>
                            </a>

                            <a href="{{ route('pos.kitchen.index') }}"
                                class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition block active:scale-[0.97]">
                                <div class="w-8 h-8 rounded-[8px] bg-[#FF9500]/12 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center">
                                    <i data-lucide="chef-hat" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-black dark:text-white">Kitchen (KDS)</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">Pesanan dapur live</div>
                                </div>
                            </a>

                            <a href="{{ route('pos.tables.index') }}"
                                class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition block active:scale-[0.97]">
                                <div class="w-8 h-8 rounded-[8px] bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                                    <i data-lucide="layout-grid" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-black dark:text-white">Meja &amp; QR</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45">Dine-in self order</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Mobile Bottom App Bar (iOS 18 Frosted Tab Bar) -->
            <div
                class="fixed inset-x-0 bottom-0 z-40 bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border-t border-black/5 dark:border-white/10 lg:hidden px-4 py-1.5 shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
                <div class="flex items-center justify-around">
                    <!-- 1. Home / Dashboard -->
                    <a href="{{ route('dashboard') }}"
                        {{ request()->routeIs('dashboard') ? 'aria-current="page"' : '' }}
                        class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-[8px] transition active:scale-[0.97] {{ request()->routeIs('dashboard') ? 'text-[#007AFF] font-semibold' : 'text-black/45 dark:text-white/45 hover:text-black/70 dark:hover:text-white/70' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        <span class="text-[10px]">Home</span>
                    </a>

                    <!-- 2. Kasir POS -->
                    <a href="{{ route('pos.terminal') }}"
                        {{ request()->routeIs('pos.terminal') ? 'aria-current="page"' : '' }}
                        class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-[8px] transition active:scale-[0.97] {{ request()->routeIs('pos.terminal') ? 'text-[#007AFF] font-semibold' : 'text-black/45 dark:text-white/45 hover:text-black/70 dark:hover:text-white/70' }}">
                        <i data-lucide="calculator" class="w-5 h-5"></i>
                        <span class="text-[10px]">Kasir</span>
                    </a>

                    <!-- 3. Center Action Trigger (iOS Action Capsule) -->
                    <button type="button" @click="$dispatch('open-mobile-actions')"
                        class="w-10 h-10 -mt-4 rounded-full bg-[#007AFF] hover:bg-[#0071E3] text-white flex items-center justify-center shadow-[0_4px_14px_rgba(0,122,255,0.35)] ring-4 ring-white dark:ring-[#1C1C1E] active:scale-[0.93] transition-all"
                        aria-label="Aksi Cepat">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                    </button>

                    <!-- 4. Katalog Produk & Stok -->
                    @if ($canAccessInventory)
                        <a href="{{ route('products.index') }}"
                            {{ request()->routeIs('products.*') || request()->routeIs('inventory.*') ? 'aria-current="page"' : '' }}
                            class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-[8px] transition active:scale-[0.97] {{ request()->routeIs('products.*') || request()->routeIs('inventory.*') ? 'text-[#007AFF] font-semibold' : 'text-black/45 dark:text-white/45 hover:text-black/70 dark:hover:text-white/70' }}">
                            <i data-lucide="package" class="w-5 h-5"></i>
                            <span class="text-[10px]">Produk</span>
                        </a>
                    @endif

                    <!-- 5. Menu Drawer Trigger -->
                    <button type="button" @click="sidebarOpen = true"
                        class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-[8px] text-black/45 dark:text-white/45 hover:text-black/70 dark:hover:text-white/70 transition active:scale-[0.97]"
                        aria-label="Buka Menu">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                        <span class="text-[10px]">Menu</span>
                    </button>
                </div>
            </div>

            <!-- Footer (macOS Minimalist Footnote) -->
            <footer
                class="px-6 lg:px-10 py-4 border-t border-black/5 dark:border-white/5 text-black/45 dark:text-white/45 text-[12px] flex flex-col sm:flex-row items-center justify-between gap-2 mb-16 lg:mb-0">
                <div>&copy; {{ date('Y') }} Cooca UMKM (cooca.id). Business Operating System.</div>
                <div class="flex items-center gap-3">
                    <a href="{{ url('/api/v1/docs') }}" target="_blank"
                        class="hover:text-[#007AFF] transition-colors">API Docs</a>
                    <span>•</span>
                    <a href="{{ route('settings.index') }}" class="hover:text-[#007AFF] transition-colors">20
                        Template Bisnis</a>
                </div>
            </footer>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
        // Targeted Lucide icon creator to avoid runaway MutationObserver CPU throttling
        let _lucideDebounce = null;
        window.createCoocaIcons = function() {
            if (_lucideDebounce) clearTimeout(_lucideDebounce);
            _lucideDebounce = setTimeout(() => {
                if (document.querySelector('i[data-lucide]')) {
                    lucide.createIcons();
                }
            }, 60);
        };
        if (window.MutationObserver) {
            new MutationObserver((mutations) => {
                let hasNewIcons = false;
                for (const m of mutations) {
                    if (m.addedNodes && m.addedNodes.length > 0) {
                        for (const node of m.addedNodes) {
                            if (node.nodeType === 1 && (node.matches?.('i[data-lucide]') || node.querySelector?.('i[data-lucide]'))) {
                                hasNewIcons = true;
                                break;
                            }
                        }
                    }
                    if (hasNewIcons) break;
                }
                if (hasNewIcons) {
                    window.createCoocaIcons();
                }
            }).observe(document.body, { childList: true, subtree: true });
        }
        window.coocaToast = function(msg, type = 'success') {
            window.dispatchEvent(new CustomEvent('cooca-toast', {
                detail: {
                    message: msg,
                    type: type
                }
            }));
        };
    </script>

    <!-- Coming Soon Modal -->
    <div x-show="comingSoonOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[200] flex items-center justify-center p-4"
         style="display: none;"
         @click.self="comingSoonOpen = false"
         @keydown.escape.window="comingSoonOpen = false">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

        <!-- Modal Panel -->
        <div x-show="comingSoonOpen"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-4"
             class="relative w-full max-w-md mx-auto rounded-3xl overflow-hidden shadow-2xl"
             style="display: none;">

            <!-- Gradient top strip -->
            <div class="h-1.5 w-full"
                 :class="{
                     'bg-gradient-to-r from-purple-500 via-indigo-500 to-purple-600': comingSoonFeature.color === 'purple',
                     'bg-gradient-to-r from-amber-400 via-orange-400 to-amber-500':  comingSoonFeature.color === 'amber',
                     'bg-gradient-to-r from-emerald-400 via-teal-400 to-emerald-500': comingSoonFeature.color === 'emerald'
                 }"></div>

            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 border-t-0 rounded-b-3xl p-8 space-y-6">

                <!-- Close button -->
                <button @click="comingSoonOpen = false" class="absolute top-5 right-5 p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>

                <!-- Icon + Title -->
                <div class="flex flex-col items-center text-center space-y-3">
                    <!-- Animated icon ring -->
                    <div class="relative">
                        <div class="absolute inset-0 rounded-full animate-ping opacity-20"
                             :class="{
                                 'bg-purple-500': comingSoonFeature.color === 'purple',
                                 'bg-amber-400':  comingSoonFeature.color === 'amber',
                                 'bg-emerald-500': comingSoonFeature.color === 'emerald'
                             }"></div>
                        <div class="relative w-16 h-16 rounded-2xl flex items-center justify-center shadow-lg"
                             :class="{
                                 'bg-purple-500/20 border border-purple-500/40 shadow-purple-500/20': comingSoonFeature.color === 'purple',
                                 'bg-amber-500/20 border border-amber-500/40 shadow-amber-500/20':   comingSoonFeature.color === 'amber',
                                 'bg-emerald-500/20 border border-emerald-500/40 shadow-emerald-500/20': comingSoonFeature.color === 'emerald'
                             }">
                            <i :data-lucide="comingSoonFeature.icon"
                               class="w-7 h-7"
                               :class="{
                                   'text-purple-600 dark:text-purple-400': comingSoonFeature.color === 'purple',
                                   'text-amber-600 dark:text-amber-400':  comingSoonFeature.color === 'amber',
                                   'text-emerald-600 dark:text-emerald-400': comingSoonFeature.color === 'emerald'
                               }"
                               x-init="$watch('comingSoonOpen', v => { if(v) { $nextTick(() => lucide.createIcons()); } })"></i>
                        </div>
                    </div>

                    <!-- Badge -->
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"
                          :class="{
                              'bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-500/30': comingSoonFeature.color === 'purple',
                              'bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/30':   comingSoonFeature.color === 'amber',
                              'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30': comingSoonFeature.color === 'emerald'
                          }">
                        🚀 Segera Hadir
                    </span>

                    <h2 class="text-xl font-black text-slate-900 dark:text-white" x-text="comingSoonFeature.title"></h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed" x-text="comingSoonFeature.desc"></p>
                </div>

                <!-- Countdown Timer -->
                <div x-data="coocaCountdown()" x-init="start()" class="space-y-3">
                    <p class="text-center text-[11px] text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider">Hitung Mundur Peluncuran</p>
                    <div class="grid grid-cols-4 gap-2">
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-full py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center">
                                <span class="text-2xl font-black font-mono text-slate-900 dark:text-white" x-text="String(days).padStart(2,'0')">00</span>
                            </div>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold uppercase">Hari</span>
                        </div>
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-full py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center">
                                <span class="text-2xl font-black font-mono text-slate-900 dark:text-white" x-text="String(hours).padStart(2,'0')">00</span>
                            </div>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold uppercase">Jam</span>
                        </div>
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-full py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center">
                                <span class="text-2xl font-black font-mono text-slate-900 dark:text-white" x-text="String(minutes).padStart(2,'0')">00</span>
                            </div>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold uppercase">Menit</span>
                        </div>
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-full py-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center">
                                <span class="text-2xl font-black font-mono text-slate-900 dark:text-white" x-text="String(seconds).padStart(2,'0')">00</span>
                            </div>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold uppercase">Detik</span>
                        </div>
                    </div>
                    <!-- Progress bar -->
                    <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-1000 bg-gradient-to-r from-emerald-500 to-teal-400"
                             :style="'width:' + progress + '%'"
                                ></div>
                    </div>
                    <p class="text-center text-[10px] text-slate-500 dark:text-slate-400" x-text="launchDate"></p>
                </div>

                <!-- CTA -->
                <div class="flex flex-col gap-2">
                    <a href="{{ route('billing.limits') }}"
                       class="flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 text-sm font-black shadow-lg shadow-emerald-500/20 transition-all hover:scale-[1.02] active:scale-95">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                        <span>Lihat Paket & Kuota Saya</span>
                    </a>
                    <button @click="comingSoonOpen = false"
                            class="px-5 py-2.5 rounded-2xl text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white text-sm font-semibold transition hover:bg-slate-100 dark:hover:bg-slate-800">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Coming Soon: intercept ai_token checkout links globally -->
    <script>
        (function() {
            // Countdown component
            window.coocaCountdown = function() {
                // Launch date: 1 month from now, stored in localStorage so it's stable per browser
                const KEY = 'cooca_coming_soon_launch';
                let launch = localStorage.getItem(KEY);
                if (!launch) {
                    const d = new Date();
                    d.setMonth(d.getMonth() + 1);
                    launch = d.toISOString();
                    localStorage.setItem(KEY, launch);
                }
                const launchTime = new Date(launch).getTime();
                const totalDuration = launchTime - (launchTime - 30 * 24 * 60 * 60 * 1000); // 30 days in ms

                return {
                    days: 0, hours: 0, minutes: 0, seconds: 0,
                    progress: 0,
                    launchDate: '',
                    _timer: null,
                    start() {
                        const ldate = new Date(launchTime);
                        this.launchDate = 'Target peluncuran: ' + ldate.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                        this.tick();
                        this._timer = setInterval(() => this.tick(), 1000);
                    },
                    tick() {
                        const now = Date.now();
                        const diff = Math.max(0, launchTime - now);
                        this.days    = Math.floor(diff / 86400000);
                        this.hours   = Math.floor((diff % 86400000) / 3600000);
                        this.minutes = Math.floor((diff % 3600000)  / 60000);
                        this.seconds = Math.floor((diff % 60000)    / 1000);
                        const elapsed = totalDuration - diff;
                        this.progress = Math.min(100, Math.round((elapsed / totalDuration) * 100));
                    }
                };
            };

            // Intercept all anchor clicks that go to ai_token checkout, pos/ai, or community
            document.addEventListener('click', function(e) {
                const a = e.target.closest('a');
                if (!a) return;
                const href = a.getAttribute('href') || '';
                const fullHref = a.href || '';

                // Match /billing/checkout?type=ai_token
                if (fullHref.includes('billing/checkout') && fullHref.includes('ai_token')) {
                    e.preventDefault();
                    window.dispatchEvent(new CustomEvent('cooca-coming-soon', {
                        detail: {
                            title: 'Top Up Token AI',
                            icon: 'bot',
                            desc: 'Fitur pembelian token AI untuk mengaktifkan AI Assistant, analisis penjualan, dan prediksi tren kasir POS. Segera tersedia!',
                            color: 'amber'
                        }
                    }));
                    return;
                }

                // Match /pos/ai
                if (fullHref.includes('/pos/ai') || href.includes('/pos/ai')) {
                    e.preventDefault();
                    window.dispatchEvent(new CustomEvent('cooca-coming-soon', {
                        detail: {
                            title: 'AI Assistant',
                            icon: 'bot',
                            desc: 'Fitur AI Cockpit untuk analisis penjualan, prediksi tren, dan asisten pintar kasir POS berbasis Gemini AI.',
                            color: 'purple'
                        }
                    }));
                    return;
                }
            }, true);
        })();
    </script>

    <!-- Guided Product Tour Engine -->
    <script src="{{ asset('js/onboarding/tour-config.js') }}"></script>
    <script src="{{ asset('js/onboarding/product-tour.js') }}"></script>

    <!-- AppAlert Session Flash Notifications -->
    @php
        $flashSuccess = session()->pull('success');
        $flashError = session()->pull('error');
        $flashWarning = session()->pull('warning');
        $flashInfo = session()->pull('info');
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if ($flashSuccess)
                AppAlert.success(@json($flashSuccess));
            @endif
            @if ($flashError)
                AppAlert.error(@json($flashError));
            @endif
            @if ($flashWarning)
                AppAlert.warning(@json($flashWarning));
            @endif
            @if ($flashInfo)
                AppAlert.info(@json($flashInfo));
            @endif
        });
    </script>

    @stack('scripts')
</body>

</html>
