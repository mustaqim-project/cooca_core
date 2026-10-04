<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

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

    <!-- Multi-Language i18n & l10n Dictionary Injection -->
    <script>
        window.COOCA_LOCALE = '{{ app()->getLocale() }}';
        window.COOCA_I18N = {
            common: @json(__('common')),
            quick_actions: @json(__('quick_actions')),
            navigation: @json(__('navigation')),
        };

        // Global Reactive Event Bus (CoocaBus)
        window.CoocaBus = {
            emit(event, detail = {}) {
                window.dispatchEvent(new CustomEvent(event, { detail }));
            },
            on(event, handler) {
                window.addEventListener(event, handler);
                return () => window.removeEventListener(event, handler);
            },
            emitDataMutated(type, data = {}) {
                this.emit('cooca-data-mutated', { type, data, timestamp: Date.now() });
            }
        };

        // Smart AJAX Polling Engine (Page Visibility Aware)
        window.CoocaPoller = (function() {
            const subscribers = new Map();
            let isVisible = typeof document !== 'undefined' ? document.visibilityState === 'visible' : true;

            function executeSubscriber(sub) {
                if (typeof sub.callback === 'function') {
                    try {
                        sub.callback();
                    } catch (err) {
                        console.error('[CoocaPoller] Poller execution error for:', sub.key, err);
                    }
                }
            }

            function startTimer(sub) {
                stopTimer(sub);
                if (sub.isPaused) return;

                const interval = isVisible ? sub.activeInterval : sub.bgInterval;
                if (interval <= 0) return;

                sub.timerId = setTimeout(function tick() {
                    if (!sub.isPaused) {
                        executeSubscriber(sub);
                        const nextInterval = isVisible ? sub.activeInterval : sub.bgInterval;
                        if (nextInterval > 0) {
                            sub.timerId = setTimeout(tick, nextInterval);
                        }
                    }
                }, interval);
            }

            function stopTimer(sub) {
                if (sub.timerId) {
                    clearTimeout(sub.timerId);
                    sub.timerId = null;
                }
            }

            if (typeof document !== 'undefined') {
                document.addEventListener('visibilitychange', () => {
                    const wasVisible = isVisible;
                    isVisible = document.visibilityState === 'visible';

                    subscribers.forEach((sub) => {
                        if (sub.isPaused) return;

                        if (isVisible && !wasVisible) {
                            if (sub.immediateOnResume) {
                                executeSubscriber(sub);
                            }
                            startTimer(sub);
                        } else if (!isVisible && wasVisible) {
                            startTimer(sub);
                        }
                    });
                });

                window.addEventListener('cooca-data-mutated', (e) => {
                    subscribers.forEach((sub) => {
                        if (sub.autoSyncOnMutation && !sub.isPaused) {
                            executeSubscriber(sub);
                        }
                    });
                });
            }

            return {
                register(key, callback, options = {}) {
                    const config = typeof options === 'number' ? { activeInterval: options } : options;
                    const activeInterval = config.activeInterval || 5000;
                    const bgInterval = config.bgInterval !== undefined ? config.bgInterval : 30000;
                    const immediateOnResume = config.immediateOnResume !== false;
                    const autoSyncOnMutation = config.autoSyncOnMutation !== false;

                    const sub = {
                        key,
                        callback,
                        activeInterval,
                        bgInterval,
                        immediateOnResume,
                        autoSyncOnMutation,
                        isPaused: false,
                        timerId: null
                    };

                    this.unregister(key);
                    subscribers.set(key, sub);
                    startTimer(sub);
                    return sub;
                },
                unregister(key) {
                    if (subscribers.has(key)) {
                        stopTimer(subscribers.get(key));
                        subscribers.delete(key);
                    }
                },
                pause(key) {
                    const sub = subscribers.get(key);
                    if (sub) {
                        sub.isPaused = true;
                        stopTimer(sub);
                    }
                },
                resume(key) {
                    const sub = subscribers.get(key);
                    if (sub) {
                        sub.isPaused = false;
                        if (isVisible && sub.immediateOnResume) {
                            executeSubscriber(sub);
                        }
                        startTimer(sub);
                    }
                },
                trigger(key) {
                    const sub = subscribers.get(key);
                    if (sub) executeSubscriber(sub);
                },
                triggerAll() {
                    subscribers.forEach((sub) => {
                        if (!sub.isPaused) executeSubscriber(sub);
                    });
                },
                getSubscribers() {
                    return Array.from(subscribers.keys());
                },
                isDocumentVisible() {
                    return isVisible;
                }
            };
        })();
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Cooca' }} - cooca.id</title>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <!-- Google Fonts (Inter fallback, JetBrains Mono fallback) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script>
        (function() {
            const _origWarn = console.warn;
            console.warn = function(...args) {
                if (typeof args[0] === 'string' && args[0].indexOf('cdn.tailwindcss.com') !== -1) return;
                _origWarn.apply(console, args);
            };
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', '"SF Pro Text"', '"SF Pro Display"', 'Inter',
                            'system-ui', 'sans-serif'
                        ],
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
            font-size: 1.0625rem !important;
            /* 17px Headline */
            font-weight: 600 !important;
            margin: 0.25rem 0 !important;
            letter-spacing: -0.015em !important;
        }

        .swal2-html-container {
            color: rgba(60, 60, 67, 0.6) !important;
            font-size: 0.8125rem !important;
            /* 13px Footnote */
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
            font-size: 0.875rem !important;
            /* 14px */
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
            --bg: #F2F2F7;
            /* Secondary system background (grouped/macOS container) */
            --surface: #FFFFFF;
            /* Primary system background / cards */
            --surface-2: #F2F2F7;
            /* System Gray 6 */
            --surface-3: #E5E5EA;
            /* System Gray 5 (elevated hover surface) */

            /* Typography */
            --text-1: #000000;
            /* Primary label */
            --text-2: rgba(60, 60, 67, 0.6);
            /* Secondary label (60%) */
            --text-3: rgba(60, 60, 67, 0.3);
            /* Tertiary label (30%) */
            --text-dis: rgba(60, 60, 67, 0.18);
            /* Quaternary label (18%) */

            /* Borders (Hairline Separators) */
            --border: rgba(60, 60, 67, 0.08);
            /* Hairline border */
            --border-sub: rgba(60, 60, 67, 0.04);
            --border-str: rgba(60, 60, 67, 0.18);

            /* Brand Colors (System Blue Master Accent) */
            --brand: #007AFF;
            /* Primary System Blue */
            --brand-hover: #0071E3;
            --brand-light: rgba(0, 122, 255, 0.1);
            --brand-text: #007AFF;

            /* Apple Semantic Status Tokens */
            --color-accent: #007AFF;
            --color-success: #34C759;
            --color-warning: #FF9500;
            --color-danger: #FF3B30;
            --color-ai: #AF52DE;
            --color-info: #5856D6;
            --color-teal: #30B0C7;

            --success: #34C759;
            --success-bg: rgba(52, 199, 89, 0.12);
            --success-border: rgba(52, 199, 89, 0.25);
            --success-text: #248A3D;

            --warn: #FF9500;
            --warn-bg: rgba(255, 149, 0, 0.12);
            --warn-border: rgba(255, 149, 0, 0.25);
            --warn-text: #B25E00;

            --danger: #FF3B30;
            --danger-bg: rgba(255, 59, 48, 0.12);
            --danger-border: rgba(255, 59, 48, 0.25);
            --danger-text: #C41E17;

            --info: #5856D6;
            --info-bg: rgba(88, 86, 214, 0.12);
            --info-border: rgba(88, 86, 214, 0.25);
            --info-text: #413FA6;

            /* Input Controls */
            --input-bg: rgba(0, 0, 0, 0.04);
            --input-border: transparent;
            --input-focus: #007AFF;

            /* Navigation & Sidebar (macOS Source List Vibrancy) */
            --nav-bg: rgba(242, 242, 247, 0.8);
            --nav-border: rgba(60, 60, 67, 0.08);
            --nav-active: #007AFF;
            --nav-active-text: #FFFFFF;
            --nav-active-border: transparent;

            /* Overlays & Shadows (Subtle Diffused Elevation) */
            --overlay: rgba(0, 0, 0, 0.25);
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 2px 8px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.06);
            --shadow-xl: 0 20px 50px rgba(0, 0, 0, 0.15);

            /* Apple Squircle Radius Hierarchy */
            --r-sm: 8px;
            /* Small buttons, inline badges */
            --r-md: 10px;
            /* Regular buttons, form inputs */
            --r-lg: 14px;
            /* Data cards, KPI tiles */
            --r-xl: 16px;
            /* Modals, large panels */
            --r-2xl: 20px;
            /* Sheet headers, big containers */
        }

        .dark {
            /* Surface & Background */
            --bg: #1E1E1E;
            /* macOS desktop window background */
            --surface: #1C1C1E;
            /* Secondary system background (dark) */
            --surface-2: #2C2C2E;
            /* Tertiary system background (elevated card) */
            --surface-3: #3A3A3C;
            /* System Gray 4 */

            /* Typography */
            --text-1: #FFFFFF;
            --text-2: rgba(235, 235, 245, 0.6);
            --text-3: rgba(235, 235, 245, 0.3);
            --text-dis: rgba(235, 235, 245, 0.18);

            /* Borders */
            --border: rgba(255, 255, 255, 0.08);
            --border-sub: rgba(255, 255, 255, 0.04);
            --border-str: rgba(255, 255, 255, 0.15);

            /* Brand Colors (System Blue Dark) */
            --brand: #0A84FF;
            --brand-hover: #007AFF;
            --brand-light: rgba(10, 132, 255, 0.15);
            --brand-text: #0A84FF;

            /* Apple Semantic Status Tokens */
            --color-accent: #0A84FF;
            --color-success: #30D158;
            --color-warning: #FF9F0A;
            --color-danger: #FF453A;
            --color-ai: #BF5AF2;
            --color-info: #5E5CE6;
            --color-teal: #40C8E0;

            --success: #30D158;
            --success-bg: rgba(48, 209, 88, 0.14);
            --success-border: rgba(48, 209, 88, 0.28);
            --success-text: #30D158;

            --warn: #FF9F0A;
            --warn-bg: rgba(255, 159, 10, 0.14);
            --warn-border: rgba(255, 159, 10, 0.28);
            --warn-text: #FF9F0A;

            --danger: #FF453A;
            --danger-bg: rgba(255, 69, 58, 0.14);
            --danger-border: rgba(255, 69, 58, 0.28);
            --danger-text: #FF453A;

            --info: #5E5CE6;
            --info-bg: rgba(94, 92, 230, 0.14);
            --info-border: rgba(94, 92, 230, 0.28);
            --info-text: #5E5CE6;

            /* Input Controls */
            --input-bg: rgba(255, 255, 255, 0.06);
            --input-border: transparent;
            --input-focus: #0A84FF;

            /* Navigation & Sidebar (Dark Vibrancy) */
            --nav-bg: rgba(28, 28, 30, 0.8);
            --nav-border: rgba(255, 255, 255, 0.08);
            --nav-active: #0A84FF;
            --nav-active-text: #FFFFFF;
            --nav-active-border: transparent;

            /* Overlays & Shadows */
            --overlay: rgba(0, 0, 0, 0.65);
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 12px 30px rgba(0, 0, 0, 0.5);
            --shadow-xl: 0 20px 50px rgba(0, 0, 0, 0.6);
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

        *,
        *:before,
        *:after {
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

        /* Universal Adaptive Modal Dialog Engine (Bento Apple HIG v2.0) */
        .app-modal-dialog {
            width: 100%;
            max-width: min(calc(100vw - 1.5rem), var(--modal-max-width, 32rem));
            max-height: min(92dvh, calc(100vh - 2rem));
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }

        .app-modal-dialog-sm {
            --modal-max-width: 28rem; /* 448px */
        }

        .app-modal-dialog-md {
            --modal-max-width: 36rem; /* 576px */
        }

        .app-modal-dialog-lg {
            --modal-max-width: 48rem; /* 768px */
        }

        .app-modal-dialog-xl {
            --modal-max-width: 64rem; /* 1024px */
        }

        .app-modal-dialog-xxl,
        .app-modal-dialog-2xl {
            --modal-max-width: min(95vw, 84.375rem); /* 1350px / 95vw */
        }

        /* iOS Safari Input Auto-Zoom Prevention (Ensures 16px minimum font on mobile viewports) */
        @media screen and (max-width: 639px) {
            input[type="text"],
            input[type="number"],
            input[type="search"],
            input[type="tel"],
            input[type="email"],
            input[type="password"],
            select,
            textarea {
                font-size: 16px !important;
            }
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
        input,
        select,
        textarea,
        button {
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
            height: 2.25rem;
            /* 36px desktop */
            padding-left: 1rem;
            padding-right: 1rem;
            border-radius: var(--r-md);
            font-size: 0.8125rem;
            /* 13px */
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
                height: 3.5rem;
                /* 56px macOS standard toolbar height */
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

    {{-- Universal Typography Hierarchy (H1 - H6 & Typographic Roles) --}}
    @include('layouts.partials.typography')
</head>

<body
    class="h-full bg-[#F2F2F7] dark:bg-[#1E1E1E] text-black dark:text-white antialiased selection:bg-[#007AFF]/20 selection:text-[#007AFF]"
    x-data="{
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
        storagePruningOpen: false,
        init() {
            window.addEventListener('tour-open-sidebar', () => {
                this.sidebarOpen = true;
                this.sidebarCollapsed = false;
            });
            window.addEventListener('tour-close-sidebar', () => { this.sidebarOpen = false; });
            window.addEventListener('cooca-coming-soon', (e) => { this.openComingSoon(e.detail); });
            window.addEventListener('open-storage-pruning-modal', () => { this.storagePruningOpen = true; });
        }
    }">
    @php
        $activeBiz = \App\Support\Context::business();
        $navEntitlement = app(\App\Domain\Billing\EntitlementService::class);
        $navUsage = $activeBiz ? $navEntitlement->getUsageSummary($activeBiz) : null;
        $isCorePlan = $navUsage['is_core'] ?? false;
    @endphp
    <div class="min-h-full flex flex-col lg:flex-row">

        <!-- Mobile Sidebar Backdrop (Apple Frosted Dimmer) -->
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/30 backdrop-blur-[2px] z-40 lg:hidden"
            @click="sidebarOpen = false" style="display: none;"></div>

        <!-- Sidebar Navigation (Apple HIG / macOS Sonoma Edition) -->
        @include('layouts.partials.sidebar', compact('activeBiz', 'navEntitlement', 'navUsage', 'isCorePlan'))

        <!-- Main Content Area (macOS Window Canvas) -->
        <div :class="sidebarCollapsed ? 'lg:pl-[76px]' : 'lg:pl-[272px]'"
            class="flex-1 flex flex-col min-h-screen min-w-0 transition-all duration-250 ease-out bg-[#F2F2F7] dark:bg-[#1E1E1E]">

            <!-- Topbar Header (Apple macOS Toolbar Architecture) -->
            @include(
                'layouts.partials.topbar',
                compact('activeBiz', 'navEntitlement', 'navUsage', 'isCorePlan'))

            <!-- Main Page Content -->
            <main id="main-content"
                class="flex-1 min-w-0 pb-28 lg:pb-10 {{ (request()->routeIs('cooca-ai.*') || request()->routeIs('ai.*')) ? 'p-2 sm:p-4 lg:p-6 max-w-none w-full space-y-4' : 'p-3.5 sm:p-5 md:p-6 lg:p-7 space-y-5 sm:space-y-6 max-w-[1440px] w-full mx-auto' }}">
                {{-- Flash success & error notifications are handled by AppAlert floating toasts in footer scripts to avoid duplicate UI banners --}}
                @if (isset($errors) && $errors->any())
                    <div
                        class="p-3.5 rounded-[12px] bg-[#FF3B30]/10 dark:bg-[#FF453A]/15 border border-[#FF3B30]/20 text-[#C41E17] dark:text-[#FF453A] space-y-1">
                        <div class="flex items-center gap-2 font-semibold text-[13px]">
                            <i data-lucide="alert-circle"
                                class="w-4 h-4 text-[#FF3B30] dark:text-[#FF453A] shrink-0"></i>
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
            <div id="global-modals-container" x-data="{
                toastList: [],
                showExpenseModal: false,
                showStockInModal: false,
                showMaterialModal: false,
                showMobileActionSheet: false,
                isSubmitting: false,
                materialsList: [],
                isLoadingMaterials: false,
                requiresExpenseSupervisorPin: false,
                supervisorPin: '',
                displayExpenseAmount: '',
                displayStockInUnitCost: '',
                displayMaterialCost: '',
                expenseForm: { name: '', amount: '', category: 'Operasional Toko', payment_method: 'cash', notes: '' },
                stockInForm: { material_id: '', product_id: '', quantity: 1, unit_cost: '', supplier_name: '', notes: '' },
                materialForm: { name: '', cost_per_unit: '', unit_id: '', category_id: '', sku: '' },

                init() {
                    window.addEventListener('cooca-toast', (e) => {
                        this.addToast(e.detail.message, e.detail.type || 'success');
                    });
                    window.addEventListener('open-quick-expense', () => { 
                        this.showExpenseModal = true; 
                    });
                    window.addEventListener('open-quick-stockin', () => { 
                        this.openStockInModal(); 
                    });
                    window.addEventListener('open-quick-material', () => { 
                        this.showMaterialModal = true; 
                    });
                    window.addEventListener('open-mobile-actions', () => { 
                        this.showMobileActionSheet = true; 
                    });
                },

                formatRupiah(val) {
                    if (val === undefined || val === null || val === '') return '';
                    const loc = (window.COOCA_LOCALE === 'en') ? 'en-US' : 'id-ID';
                    return new Intl.NumberFormat(loc).format(val);
                },

                parseNumber(str) {
                    if (!str) return 0;
                    return parseFloat(String(str).replace(/[^0-9]/g, '')) || 0;
                },

                handleExpenseAmountInput(e) {
                    const raw = this.parseNumber(e.target.value);
                    this.expenseForm.amount = raw;
                    this.displayExpenseAmount = raw > 0 ? this.formatRupiah(raw) : '';
                    this.requiresExpenseSupervisorPin = raw >= 500000;
                },

                handleStockInCostInput(e) {
                    const raw = this.parseNumber(e.target.value);
                    this.stockInForm.unit_cost = raw;
                    this.displayStockInUnitCost = raw > 0 ? this.formatRupiah(raw) : '';
                },

                handleMaterialCostInput(e) {
                    const raw = this.parseNumber(e.target.value);
                    this.materialForm.cost_per_unit = raw;
                    this.displayMaterialCost = raw > 0 ? this.formatRupiah(raw) : '';
                },

                getIdempotencyKey() {
                    return typeof crypto !== 'undefined' && crypto.randomUUID 
                        ? crypto.randomUUID() 
                        : 'idemp-' + Date.now() + '-' + Math.random().toString(36).substring(2, 9);
                },

                async fetchMaterials() {
                    if (this.materialsList.length > 0) return;
                    this.isLoadingMaterials = true;
                    try {
                        const res = await fetch('{{ route('dashboard.quick-materials-list') }}', {
                            headers: { 
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.materialsList = data.materials || [];
                        }
                    } catch (e) {
                        console.error('Failed to load materials', e);
                    } finally {
                        this.isLoadingMaterials = false;
                    }
                },

                openStockInModal() {
                    this.showStockInModal = true;
                    this.fetchMaterials();
                },

                addToast(msg, type = 'success') {
                    const id = Date.now();
                    this.toastList.push({ id, msg, type });
                    setTimeout(() => {
                        this.toastList = this.toastList.filter(t => t.id !== id);
                    }, 4000);
                },

                async submitQuickExpense() {
                    if (this.isSubmitting) return;
                    if (!this.expenseForm.name || !this.expenseForm.amount) return;
                    this.isSubmitting = true;
                    const idempotencyKey = this.getIdempotencyKey();
                    try {
                        const payload = {
                            ...this.expenseForm,
                            supervisor_pin: this.supervisorPin
                        };
                        const res = await fetch('{{ route('dashboard.quick-expense') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Idempotency-Key': idempotencyKey,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.addToast(data.message || (window.COOCA_I18N?.quick_actions?.expense?.success_msg || 'Pengeluaran kas berhasil dicatat.'), 'success');
                            this.showExpenseModal = false;
                            this.expenseForm = { name: '', amount: '', category: 'Operasional Toko', payment_method: 'cash', notes: '' };
                            this.displayExpenseAmount = '';
                            this.supervisorPin = '';
                            this.requiresExpenseSupervisorPin = false;
                            window.dispatchEvent(new CustomEvent('expense-added', { detail: data }));
                            window.dispatchEvent(new CustomEvent('cooca-data-mutated', { detail: { type: 'expense', data } }));
                        } else {
                            if (data.requires_pin) {
                                this.requiresExpenseSupervisorPin = true;
                            }
                            this.addToast(data.message || (window.COOCA_I18N?.quick_actions?.expense?.error_msg || 'Gagal menyimpan pengeluaran'), 'error');
                        }
                    } catch (err) {
                        this.addToast(window.COOCA_I18N?.common?.connection_error || 'Terjadi kesalahan koneksi', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async submitQuickStockIn() {
                    if (this.isSubmitting) return;
                    if (!this.stockInForm.quantity || !this.stockInForm.unit_cost) return;
                    this.isSubmitting = true;
                    const idempotencyKey = this.getIdempotencyKey();
                    try {
                        const res = await fetch('{{ route('dashboard.quick-stock-in') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Idempotency-Key': idempotencyKey,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.stockInForm)
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.addToast(data.message || (window.COOCA_I18N?.quick_actions?.stock_in?.success_msg || 'Stok masuk berhasil dicatat.'), 'success');
                            this.showStockInModal = false;
                            this.stockInForm = { material_id: '', product_id: '', quantity: 1, unit_cost: '', supplier_name: '', notes: '' };
                            this.displayStockInUnitCost = '';
                            window.dispatchEvent(new CustomEvent('stock-in-added', { detail: data }));
                            window.dispatchEvent(new CustomEvent('cooca-data-mutated', { detail: { type: 'stock', data } }));
                        } else {
                            this.addToast(data.message || (window.COOCA_I18N?.quick_actions?.stock_in?.error_msg || 'Gagal menambah stok'), 'error');
                        }
                    } catch (err) {
                        this.addToast(window.COOCA_I18N?.common?.connection_error || 'Terjadi kesalahan koneksi', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async submitQuickMaterial() {
                    if (this.isSubmitting) return;
                    if (!this.materialForm.name || !this.materialForm.cost_per_unit) return;
                    this.isSubmitting = true;
                    const idempotencyKey = this.getIdempotencyKey();
                    try {
                        const res = await fetch('{{ route('dashboard.quick-material') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Idempotency-Key': idempotencyKey,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.materialForm)
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.addToast(data.message || (window.COOCA_I18N?.quick_actions?.material?.success_msg || 'Bahan baku baru berhasil didaftarkan.'), 'success');
                            this.showMaterialModal = false;
                            this.materialForm = { name: '', cost_per_unit: '', unit_id: '', category_id: '', sku: '' };
                            this.displayMaterialCost = '';
                            this.materialsList = []; // Invalidate cached list
                            window.dispatchEvent(new CustomEvent('material-added', { detail: data.material }));
                            window.dispatchEvent(new CustomEvent('cooca-data-mutated', { detail: { type: 'material', data: data.material } }));
                        } else {
                            this.addToast(data.message || (window.COOCA_I18N?.quick_actions?.material?.error_msg || 'Gagal menambah bahan baku'), 'error');
                        }
                    } catch (err) {
                        this.addToast(window.COOCA_I18N?.common?.connection_error || 'Terjadi kesalahan koneksi', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }">

                <!-- Floating Toasts Container (Apple Centered Top Banner with Tap & Swipe-Up Dismiss) -->
                <div id="alpine-toast-container"
                    class="fixed top-4 left-1/2 -translate-x-1/2 z-50 flex flex-col items-center gap-2 pointer-events-none w-full max-w-sm px-4">
                    <template x-for="t in toastList" :key="t.id">
                        <div x-data="{ touchStartY: 0 }"
                            @touchstart="touchStartY = $event.touches[0].clientY"
                            @touchend="if (touchStartY - $event.changedTouches[0].clientY > 25) { toastList = toastList.filter(item => item.id !== t.id); }"
                            x-transition:enter="transition ease-out duration-250"
                            x-transition:enter-start="opacity-0 -translate-y-3 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 -translate-y-2 scale-90"
                            class="p-3 px-4 rounded-[14px] border border-black/5 dark:border-white/10 shadow-[0_8px_30px_rgba(0,0,0,0.12)] backdrop-blur-xl pointer-events-auto flex items-center gap-2.5 text-[13px] font-medium bg-white/95 dark:bg-[#2C2C2E]/95 text-black dark:text-white transition-all select-none">
                            <span class="w-2 h-2 rounded-full shrink-0"
                                :class="t.type === 'error' ? 'bg-[#FF3B30]' : 'bg-[#34C759]'"></span>
                            <span x-text="t.msg" class="flex-1 leading-snug"></span>
                            <button type="button" @click="toastList = toastList.filter(item => item.id !== t.id)"
                                class="p-1 -mr-1 rounded-md text-black/40 hover:text-black/70 dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                                aria-label="{{ __('common.close') }}">
                                <i data-lucide="x" class="w-3.5 h-3.5" x-init="$nextTick(() => { if (window.lucide) lucide.createIcons(); })"></i>
                            </button>
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
                        @click.outside="if (!isSubmitting) showExpenseModal = false">
                        <div
                            class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-8 h-8 rounded-[8px] bg-[#FF9500]/12 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center">
                                    <i data-lucide="receipt" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">
                                        {{ __('quick_actions.expense.title') }}</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('quick_actions.expense.subtitle') }}</p>
                                </div>
                            </div>
                            <button type="button" @click="showExpenseModal = false" :disabled="isSubmitting"
                                class="w-7 h-7 rounded-full flex items-center justify-center text-black/40 hover:text-black/70 dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors disabled:opacity-40"
                                aria-label="{{ __('common.close') }}">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <form @submit.prevent="submitQuickExpense" class="space-y-3 text-[13px]">
                            <div>
                                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('quick_actions.expense.name_label') }}</label>
                                <input type="text" x-model="expenseForm.name" required :disabled="isSubmitting"
                                    placeholder="{{ __('quick_actions.expense.name_placeholder') }}"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-base sm:text-sm text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('quick_actions.expense.amount_label') }}</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[13px] font-bold text-black/40 dark:text-white/40">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            x-model="displayExpenseAmount"
                                            @input="handleExpenseAmountInput($event)"
                                            required :disabled="isSubmitting"
                                            placeholder="{{ __('quick_actions.expense.amount_placeholder') }}"
                                            class="w-full h-10 pl-9 pr-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-base sm:text-sm font-semibold text-black dark:text-white tabular-nums placeholder:text-black/30 dark:placeholder:text-white/30 focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('quick_actions.expense.payment_method_label') }}</label>
                                    <select x-model="expenseForm.payment_method" :disabled="isSubmitting"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-base sm:text-sm text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                                        <option value="cash">{{ __('quick_actions.expense.method_cash') }}</option>
                                        <option value="bank">{{ __('quick_actions.expense.method_bank') }}</option>
                                        <option value="qris">{{ __('quick_actions.expense.method_qris') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('quick_actions.expense.category_label') }}</label>
                                <select x-model="expenseForm.category" :disabled="isSubmitting"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-base sm:text-sm text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                                    <option value="Operasional Toko">{{ __('quick_actions.expense.cat_operational') }}</option>
                                    <option value="Bahan Habis Pakai">{{ __('quick_actions.expense.cat_consumables') }}</option>
                                    <option value="Listrik, Air & Gas">{{ __('quick_actions.expense.cat_utilities') }}</option>
                                    <option value="Transportasi & Logistik">{{ __('quick_actions.expense.cat_logistics') }}</option>
                                    <option value="Lainnya">{{ __('quick_actions.expense.cat_other') }}</option>
                                </select>
                            </div>

                            <!-- Supervisor PIN Guard (Maker-Checker Threshold >= Rp 500.000) -->
                            <div x-show="requiresExpenseSupervisorPin || (expenseForm.amount >= 500000)" x-transition class="p-3 bg-amber-500/10 dark:bg-amber-500/15 border border-amber-500/20 rounded-[12px] space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[11px] font-bold text-amber-700 dark:text-amber-300">{{ __('quick_actions.expense.supervisor_pin_label') }}</label>
                                    <span class="text-[10px] text-amber-600 dark:text-amber-400 font-medium">{{ __('quick_actions.expense.supervisor_pin_hint') }}</span>
                                </div>
                                <input type="password" maxlength="6" x-model="supervisorPin" :disabled="isSubmitting" placeholder="{{ __('quick_actions.expense.supervisor_pin_placeholder') }}"
                                    class="w-full h-9 bg-white dark:bg-[#1C1C1E] border border-amber-500/30 rounded-[8px] px-3 text-center tracking-widest text-base sm:text-sm font-bold text-black dark:text-white outline-none focus:ring-2 focus:ring-amber-500/50 disabled:opacity-50">
                            </div>

                            <div class="flex justify-end gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                                <button type="button" @click="showExpenseModal = false" :disabled="isSubmitting" class="btn-apple-gray disabled:opacity-40">
                                    {{ __('common.cancel') }}
                                </button>
                                <button type="submit" :disabled="isSubmitting" class="btn-apple-filled flex items-center gap-2">
                                    <svg x-show="isSubmitting" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span x-text="isSubmitting ? '{{ __('common.saving') }}' : '{{ __('quick_actions.expense.submit_btn') }}'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal 2: Quick Instant Stock-In (Apple Sheet with Lazy-Loaded Materials) -->
                <div x-show="showStockInModal" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]"
                    style="display: none;">
                    <div class="w-full max-w-md rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/5 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.2)] p-5 space-y-4"
                        @click.outside="if (!isSubmitting) showStockInModal = false">
                        <div
                            class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-8 h-8 rounded-[8px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center">
                                    <i data-lucide="package-plus" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">
                                        {{ __('quick_actions.stock_in.title') }}</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('quick_actions.stock_in.subtitle') }}</p>
                                </div>
                            </div>
                            <button type="button" @click="showStockInModal = false" :disabled="isSubmitting"
                                class="w-7 h-7 rounded-full flex items-center justify-center text-black/40 hover:text-black/70 dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors disabled:opacity-40"
                                aria-label="{{ __('common.close') }}">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <form @submit.prevent="submitQuickStockIn" class="space-y-3 text-[13px]">
                            <div>
                                <label
                                    class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('quick_actions.stock_in.material_label') }}</label>
                                <select x-model="stockInForm.material_id" required :disabled="isSubmitting || isLoadingMaterials"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-base sm:text-sm text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                                    <option value="">{{ __('quick_actions.stock_in.select_material') }}</option>
                                    <option value="" disabled x-show="isLoadingMaterials">{{ __('quick_actions.stock_in.loading_materials') }}</option>
                                    <template x-for="m in materialsList" :key="m.id">
                                        <option :value="m.id" x-text="`${m.name} (HPP: Rp ${formatRupiah(m.price)})`"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('quick_actions.stock_in.qty_label') }}</label>
                                    <input type="number" x-model.number="stockInForm.quantity" required :disabled="isSubmitting"
                                        min="0.01" step="any" placeholder="{{ __('quick_actions.stock_in.qty_placeholder') }}"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-base sm:text-sm text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                                </div>
                                <div>
                                    <label
                                        class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('quick_actions.stock_in.unit_cost_label') }}</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[13px] font-bold text-black/40 dark:text-white/40">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            x-model="displayStockInUnitCost"
                                            @input="handleStockInCostInput($event)"
                                            required :disabled="isSubmitting"
                                            placeholder="{{ __('quick_actions.stock_in.unit_cost_placeholder') }}"
                                            class="w-full h-10 pl-9 pr-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-base sm:text-sm font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('quick_actions.stock_in.supplier_label') }}</label>
                                <input type="text" x-model="stockInForm.supplier_name" :disabled="isSubmitting"
                                    placeholder="{{ __('quick_actions.stock_in.supplier_placeholder') }}"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-base sm:text-sm text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                            </div>

                            <div class="flex justify-end gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                                <button type="button" @click="showStockInModal = false" :disabled="isSubmitting" class="btn-apple-gray disabled:opacity-40">
                                    {{ __('common.cancel') }}
                                </button>
                                <button type="submit" :disabled="isSubmitting" class="btn-apple-filled flex items-center gap-2">
                                    <svg x-show="isSubmitting" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span x-text="isSubmitting ? '{{ __('common.processing') }}' : '{{ __('quick_actions.stock_in.submit_btn') }}'"></span>
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
                        @click.outside="if (!isSubmitting) showMaterialModal = false">
                        <div
                            class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-8 h-8 rounded-[8px] bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                                    <i data-lucide="boxes" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">
                                        {{ __('quick_actions.material.title') }}</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('quick_actions.material.subtitle') }}</p>
                                </div>
                            </div>
                            <button type="button" @click="showMaterialModal = false" :disabled="isSubmitting"
                                class="w-7 h-7 rounded-full flex items-center justify-center text-black/40 hover:text-black/70 dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors disabled:opacity-40"
                                aria-label="{{ __('common.close') }}">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <form @submit.prevent="submitQuickMaterial" class="space-y-3 text-[13px]">
                            <div>
                                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('quick_actions.material.name_label') }}</label>
                                <input type="text" x-model="materialForm.name" required :disabled="isSubmitting"
                                    placeholder="{{ __('quick_actions.material.name_placeholder') }}"
                                    class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-base sm:text-sm text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('quick_actions.material.cost_label') }}</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[13px] font-bold text-black/40 dark:text-white/40">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            x-model="displayMaterialCost"
                                            @input="handleMaterialCostInput($event)"
                                            required :disabled="isSubmitting"
                                            placeholder="{{ __('quick_actions.material.cost_placeholder') }}"
                                            class="w-full h-10 pl-9 pr-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-base sm:text-sm font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('quick_actions.material.unit_label') }}</label>
                                    <select x-model="materialForm.unit_id" :disabled="isSubmitting"
                                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-base sm:text-sm text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                                        <option value="">{{ __('quick_actions.material.select_unit') }}</option>
                                        @php
                                            $modalUnits = $activeBiz
                                                ? \Illuminate\Support\Facades\Cache::remember(
                                                    "layout_modal_units_{$activeBiz->id}",
                                                    300,
                                                    function () use ($activeBiz) {
                                                        return \App\Models\Unit::where('business_id', $activeBiz->id)
                                                            ->orWhereNull('business_id')
                                                            ->orderBy('name')
                                                            ->get()
                                                            ->map(
                                                                fn($u) => [
                                                                    'id' => (string) $u->id,
                                                                    'name' => (string) $u->name,
                                                                    'code' => (string) $u->code,
                                                                ],
                                                            )
                                                            ->all();
                                                    },
                                                )
                                                : [];
                                        @endphp
                                        @foreach ($modalUnits as $u)
                                            <option value="{{ $u['id'] }}">{{ $u['name'] }}
                                                ({{ $u['code'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                                <button type="button" @click="showMaterialModal = false" :disabled="isSubmitting" class="btn-apple-gray disabled:opacity-40">
                                    {{ __('common.cancel') }}
                                </button>
                                <button type="submit" :disabled="isSubmitting" class="btn-apple-filled flex items-center gap-2">
                                    <svg x-show="isSubmitting" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span x-text="isSubmitting ? '{{ __('common.saving') }}' : '{{ __('quick_actions.material.submit_btn') }}'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal 4: Mobile Action Sheet Bottom Modal (iOS 18 Sheet) -->
                <div x-show="showMobileActionSheet" x-transition:enter="transition-opacity ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-end justify-center bg-black/30 backdrop-blur-[2px] lg:hidden"
                    style="display: none;">
                    <div class="rounded-t-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl w-full border-t border-black/5 dark:border-white/10 shadow-[0_-10px_40px_rgba(0,0,0,0.15)] p-5 pt-3 space-y-3 max-h-[85vh] overflow-y-auto"
                        @click.outside="showMobileActionSheet = false"
                        x-transition:enter="transition ease-out duration-250"
                        x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0"
                        x-transition:leave-end="translate-y-full">

                        <!-- Grabber Bar -->
                        <div class="w-9 h-1 rounded-full bg-black/20 dark:bg-white/20 mx-auto"></div>

                        <div
                            class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-2">
                            <h3 class="text-[15px] font-semibold text-black dark:text-white">{{ __('quick_actions.sheet.title') }}</h3>
                            <button type="button" @click="showMobileActionSheet = false"
                                class="text-black/40 dark:text-white/40 p-1"
                                aria-label="{{ __('common.close') }}">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 text-[13px]">
                            @if (\App\Support\Context::hasPermission('expenses.manage') || \App\Support\Context::hasPermission('expenses.view') || \App\Support\Context::isOwner())
                                <button type="button" @click="showMobileActionSheet = false; showExpenseModal = true"
                                    class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition active:scale-[0.97]">
                                    <div
                                        class="w-8 h-8 rounded-[8px] bg-[#FF9500]/12 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center">
                                        <i data-lucide="receipt" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-black dark:text-white">{{ __('quick_actions.sheet.expense_title') }}</div>
                                        <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('quick_actions.sheet.expense_desc') }}</div>
                                    </div>
                                </button>
                            @endif

                            @if (\App\Support\Context::hasPermission('inventory.manage') || \App\Support\Context::hasPermission('inventory.view') || \App\Support\Context::isOwner())
                                <button type="button" @click="showMobileActionSheet = false; showStockInModal = true"
                                    class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition active:scale-[0.97]">
                                    <div
                                        class="w-8 h-8 rounded-[8px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center">
                                        <i data-lucide="package-plus" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-black dark:text-white">{{ __('quick_actions.sheet.stock_title') }}</div>
                                        <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('quick_actions.sheet.stock_desc') }}</div>
                                    </div>
                                </button>
                            @endif

                            @if ((\App\Support\Context::hasPermission('materials.create') || \App\Support\Context::hasPermission('materials.view') || \App\Support\Context::isOwner()) && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM) ?? true))
                                <button type="button" @click="showMobileActionSheet = false; showMaterialModal = true"
                                    class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition active:scale-[0.97]">
                                    <div
                                        class="w-8 h-8 rounded-[8px] bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                                        <i data-lucide="boxes" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-black dark:text-white">{{ __('quick_actions.sheet.material_title') }}</div>
                                        <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('quick_actions.sheet.material_desc') }}</div>
                                    </div>
                                </button>
                            @endif

                            @if (\App\Support\Context::hasPermission('costing.view_margin') || \App\Support\Context::hasPermission('costing.manage') || \App\Support\Context::isOwner())
                                <a href="{{ route('calculator.index') }}"
                                    class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition block active:scale-[0.97]">
                                    <div
                                        class="w-8 h-8 rounded-[8px] bg-[#AF52DE]/12 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center">
                                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-black dark:text-white">{{ __('quick_actions.sheet.hpp_title') }}</div>
                                        <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('quick_actions.sheet.hpp_desc') }}</div>
                                    </div>
                                </a>
                            @endif

                            @if ($activeBiz && $activeBiz->hasDineInFeature() && $activeBiz->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_POS_DINEIN) && (\App\Support\Context::hasPermission('pos.kitchen') || \App\Support\Context::isOwner()))
                                <a href="{{ route('pos.kitchen.index') }}"
                                    class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition block active:scale-[0.97]">
                                    <div
                                        class="w-8 h-8 rounded-[8px] bg-[#FF9500]/12 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center">
                                        <i data-lucide="chef-hat" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-black dark:text-white">{{ __('quick_actions.sheet.kds_title') }}</div>
                                        <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('quick_actions.sheet.kds_desc') }}</div>
                                    </div>
                                </a>
                            @endif

                            @if ($activeBiz && $activeBiz->hasDineInFeature() && $activeBiz->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_POS_DINEIN) && (\App\Support\Context::hasPermission('pos.tables') || \App\Support\Context::isOwner()))
                                <a href="{{ route('pos.tables.index') }}"
                                    class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition block active:scale-[0.97]">
                                    <div
                                        class="w-8 h-8 rounded-[8px] bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                                        <i data-lucide="layout-grid" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-black dark:text-white">{{ __('quick_actions.sheet.tables_title') }}</div>
                                        <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('quick_actions.sheet.tables_desc') }}</div>
                                    </div>
                                </a>
                            @endif

                            @if ($activeBiz && ($activeBiz->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_SERVICE_WORKSHOP) || $activeBiz->isServiceSector() || $activeBiz->isWorkshop()) && (\App\Support\Context::hasPermission('products.view') || \App\Support\Context::hasPermission('products.create') || \App\Support\Context::isOwner()))
                                <a href="{{ route('services.index') }}"
                                    class="p-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition block active:scale-[0.97]">
                                    <div
                                        class="w-8 h-8 rounded-[8px] bg-[#34C759]/12 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                                        <i data-lucide="wrench" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-black dark:text-white">{{ __('quick_actions.sheet.services_title') }}</div>
                                        <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('quick_actions.sheet.services_desc') }}</div>
                                    </div>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            <!-- Mobile Bottom App Bar (iOS 18 Frosted Tab Bar) -->
            <div
                class="fixed inset-x-0 bottom-0 z-40 bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border-t border-black/5 dark:border-white/10 lg:hidden px-4 py-1.5 shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
                <div class="flex items-center justify-around">
                    <!-- 1. Home / Dashboard / Portal -->
                    @php
                        $canSeeDashboard = \App\Support\Context::isOwner() || \App\Support\Context::hasPermission('dashboard.view');
                        $homeRoute = $canSeeDashboard ? route('dashboard') : route('portal');
                        $isHomeActive = $canSeeDashboard ? request()->routeIs('dashboard') : request()->routeIs('portal');
                    @endphp
                    <a href="{{ $homeRoute }}"
                        {{ $isHomeActive ? 'aria-current="page"' : '' }}
                        class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-[8px] transition active:scale-[0.97] {{ $isHomeActive ? 'text-[#007AFF] font-semibold' : 'text-black/45 dark:text-white/45 hover:text-black/70 dark:hover:text-white/70' }}">
                        <i data-lucide="{{ $canSeeDashboard ? 'layout-dashboard' : 'clock' }}" class="w-5 h-5"></i>
                        <span class="text-[10px]">{{ $canSeeDashboard ? __('navigation.home_short') : __('navigation.portal_short') }}</span>
                    </a>

                    <!-- 2. Kasir POS -->
                    <a href="{{ route('pos.terminal') }}"
                        {{ request()->routeIs('pos.terminal') ? 'aria-current="page"' : '' }}
                        class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-[8px] transition active:scale-[0.97] {{ request()->routeIs('pos.terminal') ? 'text-[#007AFF] font-semibold' : 'text-black/45 dark:text-white/45 hover:text-black/70 dark:hover:text-white/70' }}">
                        <i data-lucide="calculator" class="w-5 h-5"></i>
                        <span class="text-[10px]">{{ __('navigation.pos_terminal_short') }}</span>
                    </a>

                    <!-- 3. Center Action Trigger (iOS Action Capsule - 48x48px Touch Target) -->
                    <button type="button" @click="$dispatch('open-mobile-actions')"
                        class="w-12 h-12 -mt-5 rounded-full bg-[#007AFF] hover:bg-[#0071E3] text-white flex items-center justify-center shadow-[0_6px_20px_rgba(0,122,255,0.4)] ring-4 ring-white dark:ring-[#1C1C1E] active:scale-[0.93] transition-all shrink-0 cursor-pointer"
                        aria-label="{{ __('quick_actions.sheet.title') }}">
                        <i data-lucide="plus" class="w-6 h-6 stroke-[2.5]"></i>
                    </button>

                    <!-- 4. Katalog Produk & Stok -->
                    @if (\App\Support\Context::hasPermission('products.view') || \App\Support\Context::hasPermission('inventory.view'))
                        <a href="{{ route('products.index') }}"
                            {{ request()->routeIs('products.*') || request()->routeIs('inventory.*') ? 'aria-current="page"' : '' }}
                            class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-[8px] transition active:scale-[0.97] {{ request()->routeIs('products.*') || request()->routeIs('inventory.*') ? 'text-[#007AFF] font-semibold' : 'text-black/45 dark:text-white/45 hover:text-black/70 dark:hover:text-white/70' }}">
                            <i data-lucide="package" class="w-5 h-5"></i>
                            <span class="text-[10px]">{{ __('navigation.products_short') }}</span>
                        </a>
                    @endif

                    <!-- 5. Menu Drawer Trigger -->
                    <button type="button" @click="sidebarOpen = true"
                        class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-[8px] text-black/45 dark:text-white/45 hover:text-black/70 dark:hover:text-white/70 transition active:scale-[0.97]"
                        aria-label="{{ __('navigation.menu_short') }}">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                        <span class="text-[10px]">{{ __('navigation.menu_short') }}</span>
                    </button>
                </div>
            </div>

            <!-- Footer (macOS Minimalist Footnote) -->
            <footer
                class="px-6 lg:px-10 py-4 border-t border-black/5 dark:border-white/5 text-black/45 dark:text-white/45 text-[12px] flex flex-col sm:flex-row items-center justify-between gap-2 mb-16 lg:mb-0">
                <div>&copy; {{ date('Y') }} Cooca (cooca.id). {{ __('common.copyright_footer') ?? 'Business Operating System.' }}</div>
                <div class="flex items-center gap-3">
                    <a href="{{ url('/api/v1/docs') }}" target="_blank"
                        class="hover:text-[#007AFF] transition-colors">{{ __('common.api_docs') }}</a>
                    <span>•</span>
                    <a href="{{ route('settings.index') }}" class="hover:text-[#007AFF] transition-colors">{{ __('navigation.templates_business') }}</a>
                </div>
            </footer>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        // Targeted Lucide icon creator to avoid runaway MutationObserver CPU throttling
        let _lucideDebounce = null;
        window.createCoocaIcons = function() {
            if (_lucideDebounce) clearTimeout(_lucideDebounce);
            _lucideDebounce = setTimeout(() => {
                if (typeof lucide !== 'undefined' && document.querySelector('i[data-lucide]')) {
                    lucide.createIcons();
                }
            }, 50);
        };

        // Scoped MutationObserver targeting dynamic container nodes instead of whole document.body
        if (typeof window !== 'undefined' && window.MutationObserver) {
            const observeTarget = function() {
                const targetNodes = [
                    document.getElementById('main-content'),
                    document.querySelector('main'),
                    document.getElementById('global-modals-container'),
                    document.getElementById('alpine-toast-container')
                ].filter(Boolean);

                const observer = new MutationObserver((mutations) => {
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
                });

                if (targetNodes.length > 0) {
                    targetNodes.forEach(node => observer.observe(node, { childList: true, subtree: true }));
                } else {
                    const fallbackRoot = document.getElementById('main-content') || document.querySelector('main') || document.body;
                    observer.observe(fallbackRoot, { childList: true, subtree: true });
                }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', observeTarget);
            } else {
                observeTarget();
            }
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

    <!-- Coming Soon Modal (Bento Apple HIG v2.0 - Authentic Roadmap Preview) -->
    <div x-show="comingSoonOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-[200] flex items-center justify-center p-4"
        style="display: none;" @click.self="comingSoonOpen = false" @keydown.escape.window="comingSoonOpen = false">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

        <!-- Modal Panel -->
        <div x-show="comingSoonOpen" x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-3"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="app-modal-dialog app-modal-dialog-md relative w-full mx-auto bg-white dark:bg-[#1C1C1E] border border-slate-200/80 dark:border-white/10 rounded-[24px] shadow-2xl overflow-hidden z-10"
            style="display: none;">

            <!-- Subtle Apple Accent Header Glow -->
            <div class="h-1.5 w-full transition-colors duration-300"
                :class="{
                    'bg-gradient-to-r from-purple-500 via-indigo-500 to-purple-600': comingSoonFeature.color === 'purple',
                    'bg-gradient-to-r from-amber-400 via-orange-400 to-amber-500': comingSoonFeature.color === 'amber',
                    'bg-gradient-to-r from-emerald-400 via-teal-400 to-emerald-500': comingSoonFeature.color === 'emerald',
                    'bg-gradient-to-r from-blue-500 via-indigo-500 to-blue-600': !comingSoonFeature.color || comingSoonFeature.color === 'blue'
                }">
            </div>

            <div class="p-6 sm:p-7 space-y-6">
                <!-- Header with Close Button -->
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-[16px] flex items-center justify-center border shadow-sm transition-all duration-300 shrink-0"
                            :class="{
                                'bg-purple-500/10 border-purple-500/20 text-purple-600 dark:text-purple-400': comingSoonFeature.color === 'purple',
                                'bg-amber-500/10 border-amber-500/20 text-amber-600 dark:text-amber-400': comingSoonFeature.color === 'amber',
                                'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400': comingSoonFeature.color === 'emerald',
                                'bg-blue-500/10 border-blue-500/20 text-blue-600 dark:text-blue-400': !comingSoonFeature.color || comingSoonFeature.color === 'blue'
                            }">
                            <i :data-lucide="comingSoonFeature.icon || 'sparkles'" class="w-6 h-6"
                                x-init="$watch('comingSoonOpen', v => { if (v) { $nextTick(() => lucide.createIcons()); } })"></i>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold tracking-wide"
                                :class="{
                                    'bg-purple-500/15 text-purple-700 dark:text-purple-300 border border-purple-500/20': comingSoonFeature.color === 'purple',
                                    'bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/20': comingSoonFeature.color === 'amber',
                                    'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20': comingSoonFeature.color === 'emerald',
                                    'bg-blue-500/15 text-blue-700 dark:text-blue-300 border border-blue-500/20': !comingSoonFeature.color || comingSoonFeature.color === 'blue'
                                }">
                                <span class="w-1.5 h-1.5 rounded-full animate-pulse"
                                    :class="{
                                        'bg-purple-500': comingSoonFeature.color === 'purple',
                                        'bg-amber-500': comingSoonFeature.color === 'amber',
                                        'bg-emerald-500': comingSoonFeature.color === 'emerald',
                                        'bg-blue-500': !comingSoonFeature.color || comingSoonFeature.color === 'blue'
                                    }"></span>
                                <span>{{ __('common.in_development') }}</span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mt-1 leading-snug" x-text="comingSoonFeature.title"></h3>
                        </div>
                    </div>
                    <button @click="comingSoonOpen = false" type="button"
                        class="p-2 rounded-[10px] text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Description & Value Highlight Bento Card -->
                <div class="p-4 rounded-[16px] bg-slate-50 dark:bg-white/[0.03] border border-slate-200/70 dark:border-white/5 space-y-3">
                    <p class="text-[13px] text-slate-600 dark:text-slate-300 leading-relaxed" x-text="comingSoonFeature.desc || '{{ __('common.in_development_desc') }}'"></p>
                    
                    <div class="pt-2 border-t border-slate-200/60 dark:border-white/5 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                        <span class="inline-flex items-center gap-1.5 font-medium">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-500 shrink-0"></i>
                            <span>Enterprise Quality & Safety</span>
                        </span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Cooca Roadmap</span>
                    </div>
                </div>

                <!-- Action CTAs -->
                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 pt-2">
                    <button @click="comingSoonOpen = false" type="button"
                        class="w-full sm:w-auto px-4 py-2.5 rounded-[12px] text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white text-[13px] font-semibold transition-colors hover:bg-slate-100 dark:hover:bg-white/5">
                        {{ __('common.close') }}
                    </button>
                    <a href="{{ route('billing.limits') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-[12px] bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-[13px] font-semibold shadow-sm transition-all active:scale-95">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400 dark:text-amber-500"></i>
                        <span>{{ __('common.view_plans_quota') }}</span>
                    </a>
                </div>
            </div>
    <!-- Storage & Audit Pruning Preview Modal (Bento Apple HIG v2.0) -->
    <div x-show="storagePruningOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-[200] flex items-center justify-center p-4"
        style="display: none;" @click.self="storagePruningOpen = false" @keydown.escape.window="storagePruningOpen = false">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

        <!-- Modal Panel -->
        <div x-show="storagePruningOpen" x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-3"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="app-modal-dialog app-modal-dialog-md relative w-full mx-auto bg-white dark:bg-[#1C1C1E] border border-slate-200/80 dark:border-white/10 rounded-[24px] shadow-2xl overflow-hidden z-10"
            style="display: none;">

            <!-- Apple Blue Accent Header Glow -->
            <div class="h-1.5 w-full bg-gradient-to-r from-[#007AFF] via-[#5856D6] to-[#007AFF]"></div>

            <div class="p-6 sm:p-7 space-y-5">
                <!-- Header with Close Button -->
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-[16px] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 border border-[#007AFF]/20 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shadow-sm shrink-0">
                            <i data-lucide="hard-drive" class="w-6 h-6" x-init="$watch('storagePruningOpen', v => { if (v) { $nextTick(() => { if (window.createCoocaIcons) window.createCoocaIcons(); else if (window.lucide) lucide.createIcons(); }); } })"></i>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold tracking-wide bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] border border-[#007AFF]/20">
                                <i data-lucide="database" class="w-3 h-3"></i>
                                <span>{{ __('common.storage_pruning_title') }}</span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mt-1 leading-snug">{{ __('common.storage_pruning_title') }}</h3>
                        </div>
                    </div>
                    <button @click="storagePruningOpen = false" type="button"
                        class="p-2 rounded-[10px] text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition-colors"
                        aria-label="{{ __('common.close') }}">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <p class="text-[13px] text-slate-600 dark:text-slate-300 leading-relaxed">
                    {{ __('common.storage_pruning_subtitle') }}
                </p>

                <!-- 3 Bento Cards for Pruning Categories -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Bento Card 1: Historical Audit Logs (>90 Days) -->
                    <div class="p-3.5 rounded-[16px] bg-slate-50 dark:bg-white/[0.03] border border-slate-200/70 dark:border-white/5 space-y-1.5 flex flex-col justify-between">
                        <div class="space-y-1">
                            <div class="w-7 h-7 rounded-[8px] bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <i data-lucide="history" class="w-4 h-4"></i>
                            </div>
                            <div class="font-bold text-[12px] text-slate-900 dark:text-white">
                                {{ __('common.storage_audit_logs_title') }}
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                                {{ __('common.storage_audit_logs_desc') }}
                            </div>
                        </div>
                        <div class="pt-2 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                            <i data-lucide="check-circle" class="w-3 h-3"></i>
                            <span>Aman Dipangkas</span>
                        </div>
                    </div>

                    <!-- Bento Card 2: Sync & Webhook Logs -->
                    <div class="p-3.5 rounded-[16px] bg-slate-50 dark:bg-white/[0.03] border border-slate-200/70 dark:border-white/5 space-y-1.5 flex flex-col justify-between">
                        <div class="space-y-1">
                            <div class="w-7 h-7 rounded-[8px] bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                            </div>
                            <div class="font-bold text-[12px] text-slate-900 dark:text-white">
                                {{ __('common.storage_sync_logs_title') }}
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                                {{ __('common.storage_sync_logs_desc') }}
                            </div>
                        </div>
                        <div class="pt-2 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                            <i data-lucide="check-circle" class="w-3 h-3"></i>
                            <span>Aman Dipangkas</span>
                        </div>
                    </div>

                    <!-- Bento Card 3: Temporary Cache & Indexes -->
                    <div class="p-3.5 rounded-[16px] bg-slate-50 dark:bg-white/[0.03] border border-slate-200/70 dark:border-white/5 space-y-1.5 flex flex-col justify-between">
                        <div class="space-y-1">
                            <div class="w-7 h-7 rounded-[8px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                                <i data-lucide="cpu" class="w-4 h-4"></i>
                            </div>
                            <div class="font-bold text-[12px] text-slate-900 dark:text-white">
                                {{ __('common.storage_cache_index_title') }}
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                                {{ __('common.storage_cache_index_desc') }}
                            </div>
                        </div>
                        <div class="pt-2 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                            <i data-lucide="check-circle" class="w-3 h-3"></i>
                            <span>Auto Rebuilt</span>
                        </div>
                    </div>
                </div>

                <!-- Safety Protection Guarantee Banner -->
                <div class="p-3.5 rounded-[14px] bg-emerald-500/10 border border-emerald-500/20 flex items-start gap-2.5 text-[12px] text-emerald-800 dark:text-emerald-300">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5"></i>
                    <span class="leading-relaxed">{{ __('common.storage_safe_guarantee') }}</span>
                </div>

                <!-- Modal Actions -->
                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 pt-2">
                    <button @click="storagePruningOpen = false" type="button"
                        class="w-full sm:w-auto px-4 py-2.5 rounded-[12px] text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white text-[13px] font-semibold transition-colors hover:bg-slate-100 dark:hover:bg-white/5">
                        {{ __('common.close') }}
                    </button>
                    <a href="{{ route('settings.index') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-[12px] bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-[13px] font-semibold shadow-sm transition-all active:scale-95">
                        <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                        <span>{{ __('common.storage_open_settings') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Coming Soon: intercept development feature links globally -->
    <script>
        (function() {
            // Intercept all anchor clicks that go to ai_token checkout, pos/ai, or preview routes
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

    <!-- AppAlert Session Flash Notifications (Deduplicated) -->
    @php
        $flashSuccess = session('success');
        $flashError = session('error');
        $flashWarning = session('warning');
        $flashInfo = session('info');
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.__coocaFlashHandled) return;
            window.__coocaFlashHandled = true;
            @if ($flashSuccess)
                if (typeof AppAlert !== 'undefined') AppAlert.success(@json($flashSuccess));
            @endif
            @if ($flashError)
                if (typeof AppAlert !== 'undefined') AppAlert.error(@json($flashError));
            @endif
            @if ($flashWarning)
                if (typeof AppAlert !== 'undefined') AppAlert.warning(@json($flashWarning));
            @endif
            @if ($flashInfo)
                if (typeof AppAlert !== 'undefined') AppAlert.info(@json($flashInfo));
            @endif
        });
    </script>

    {{-- Reusable Quota & Lock Modal (Bento Apple HIG) --}}
    <x-quota-modal />

    @stack('scripts')
</body>

</html>
