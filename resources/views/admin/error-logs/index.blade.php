@extends('layouts.admin', [
    'title' => 'Log Error & Diagnostik Sistem - Admin Console',
    'headerTitle' => 'Log Error & Diagnostik Sistem',
    'headerSubtitle' => 'Pemantauan runtime exception, failure stack trace, dan riwayat log aplikasi secara real-time',
])

@section('content')
<div class="max-w-[1440px] mx-auto space-y-6 pb-28 lg:pb-12"
    x-data="{
        showClearModal: false,
        copiedId: null,
        autoRefresh: false,
        refreshTimer: null,
        openTraces: {},
        toggleTrace(id) {
            this.openTraces[id] = !this.openTraces[id];
        },
        copyTrace(text, id) {
            navigator.clipboard.writeText(text).then(() => {
                this.copiedId = id;
                setTimeout(() => {
                    if (this.copiedId === id) this.copiedId = null;
                }, 2000);
            }).catch(() => {
                alert('Stack trace berhasil disalin ke clipboard!');
            });
        },
        toggleAutoRefresh() {
            this.autoRefresh = !this.autoRefresh;
            if (this.autoRefresh) {
                localStorage.setItem('cooca_error_log_auto_refresh', '1');
                this.refreshTimer = setInterval(() => {
                    window.location.reload();
                }, 10000);
            } else {
                localStorage.removeItem('cooca_error_log_auto_refresh');
                if (this.refreshTimer) {
                    clearInterval(this.refreshTimer);
                    this.refreshTimer = null;
                }
            }
        },
        init() {
            if (localStorage.getItem('cooca_error_log_auto_refresh') === '1') {
                this.autoRefresh = true;
                this.refreshTimer = setInterval(() => {
                    window.location.reload();
                }, 10000);
            }
        }
    }">

    <!-- Flash Notifications -->
    @if (session('success'))
        <div class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 flex items-center justify-between gap-3 text-[13px] text-[#248A3D] dark:text-[#30D158]">
            <div class="flex items-center gap-2.5">
                <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0" stroke-width="2"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 flex items-center justify-between gap-3 text-[13px] text-[#C41E17] dark:text-[#FF453A]">
            <div class="flex items-center gap-2.5">
                <i data-lucide="alert-octagon" class="w-5 h-5 shrink-0" stroke-width="2"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- Top Action & Diagnostic Header Banner -->
    <div class="rounded-[20px] p-5 sm:p-6 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-12 h-12 rounded-[14px] bg-gradient-to-tr from-[#FF3B30] to-[#FF9500] text-white flex items-center justify-center shadow-md shadow-[#FF3B30]/25 shrink-0">
                <i data-lucide="terminal" class="w-6 h-6" stroke-width="2"></i>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-[17px] font-extrabold text-black dark:text-white tracking-tight">Pusat Diagnostik &amp; Log Sistem</h2>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-mono font-bold bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70">
                        {{ $selectedFile }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">
                        Rotasi Harian (30 Hari)
                    </span>
                    <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">
                        ({{ $fileSize > 1024 ? round($fileSize / 1024, 2) . ' MB' : $fileSize . ' KB' }})
                    </span>
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5 truncate">
                    Streaming memory-safe untuk pemeriksaan kegagalan runtime, SQL exceptions, dan stack trace aplikasi.
                </p>
            </div>
        </div>

        <!-- Action Buttons Tray -->
        <div class="flex items-center gap-2 flex-wrap w-full md:w-auto justify-start md:justify-end">
            <!-- Auto-Refresh Toggle -->
            <button type="button" @click="toggleAutoRefresh()"
                :class="autoRefresh ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/30' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 border-black/[0.06] dark:border-white/[0.08]'"
                class="h-10 px-3.5 rounded-[12px] border text-[12.5px] font-semibold flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer"
                title="Refresh halaman otomatis setiap 10 detik">
                <span class="w-2 h-2 rounded-full" :class="autoRefresh ? 'bg-[#34C759] animate-ping' : 'bg-black/30 dark:bg-white/30'"></span>
                <i data-lucide="zap" class="w-4 h-4" stroke-width="2"></i>
                <span x-text="autoRefresh ? 'Live (10s Aktif)' : 'Live Refresh'"></span>
            </button>

            <!-- Refresh Button -->
            <button type="button" onclick="window.location.reload();"
                class="h-10 px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-black/70 dark:text-white/70 text-[12.5px] font-semibold flex items-center gap-1.5 transition-all active:scale-[0.98] cursor-pointer"
                title="Muat ulang halaman">
                <i data-lucide="refresh-cw" class="w-4 h-4" stroke-width="1.8"></i>
                <span class="hidden sm:inline">Refresh</span>
            </button>

            <!-- Download Raw Log Button -->
            <a href="{{ route('admin.error-logs.download', ['file' => $selectedFile]) }}"
                class="h-10 px-3.5 rounded-[12px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] dark:text-[#0A84FF] text-[12.5px] font-bold flex items-center gap-1.5 transition-all active:scale-[0.98]"
                title="Unduh berkas mentah {{ $selectedFile }}">
                <i data-lucide="download" class="w-4 h-4" stroke-width="2"></i>
                <span>Unduh Log</span>
            </a>

            <!-- Clear Log Button (Triggers Apple Modal Sheet) -->
            <button type="button" @click="showClearModal = true"
                class="h-10 px-3.5 rounded-[12px] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] dark:text-[#FF453A] text-[12.5px] font-bold flex items-center gap-1.5 transition-all active:scale-[0.98] cursor-pointer"
                title="Kosongkan isi berkas log ini">
                <i data-lucide="trash-2" class="w-4 h-4" stroke-width="2"></i>
                <span>Bersihkan</span>
            </button>
        </div>
    </div>

    <!-- Bento KPI Stats Grid (Adaptive 2-col mobile, 4-col desktop) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Log -->
        <div class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Total Baris Log</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="file-text" class="w-4 h-4" stroke-width="2"></i>
                </div>
            </div>
            <div class="text-[24px] sm:text-[28px] font-black text-black dark:text-white tabular-nums tracking-tight">
                {{ number_format($stats['total'] ?? 0) }}
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45 truncate">
                File: <code class="font-mono text-black/70 dark:text-white/70">{{ $selectedFile }}</code>
            </div>
        </div>

        <!-- Errors & Critical -->
        <div class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#FF3B30] dark:text-[#FF453A]">Errors &amp; Critical</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center">
                    <i data-lucide="alert-circle" class="w-4 h-4" stroke-width="2"></i>
                </div>
            </div>
            <div class="text-[24px] sm:text-[28px] font-black text-[#FF3B30] dark:text-[#FF453A] tabular-nums tracking-tight">
                {{ number_format($stats['errors'] ?? 0) }}
            </div>
            <div class="text-[11px] text-[#FF3B30]/80 dark:text-[#FF453A]/80 font-medium">
                Exception &amp; Fatal Failure
            </div>
        </div>

        <!-- Warnings -->
        <div class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#FF9500] dark:text-[#FF9F0A]">Warnings</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4" stroke-width="2"></i>
                </div>
            </div>
            <div class="text-[24px] sm:text-[28px] font-black text-[#FF9500] dark:text-[#FF9F0A] tabular-nums tracking-tight">
                {{ number_format($stats['warnings'] ?? 0) }}
            </div>
            <div class="text-[11px] text-[#FF9500]/80 dark:text-[#FF9F0A]/80 font-medium">
                Peringatan Operasional
            </div>
        </div>

        <!-- Info & Debug -->
        <div class="p-4 sm:p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#30B0C7] dark:text-[#40C8E0]">Info &amp; Debug</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#30B0C7]/10 text-[#30B0C7] flex items-center justify-center">
                    <i data-lucide="info" class="w-4 h-4" stroke-width="2"></i>
                </div>
            </div>
            <div class="text-[24px] sm:text-[28px] font-black text-[#30B0C7] dark:text-[#40C8E0] tabular-nums tracking-tight">
                {{ number_format($stats['info'] ?? 0) }}
            </div>
            <div class="text-[11px] text-black/45 dark:text-white/45">
                Audit Trail &amp; Telemetri
            </div>
        </div>
    </div>

    <!-- Filter & File Switcher Tray -->
    <div class="rounded-[20px] p-4 sm:p-5 bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
        <form method="GET" action="{{ route('admin.error-logs.index') }}" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1">
                <!-- Search Input -->
                <div class="relative flex-1 min-w-[240px]">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari pesan error, exception class, file, atau stack trace..."
                        class="w-full h-11 pl-10 pr-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-black dark:text-white text-[16px] sm:text-[13px] placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                </div>

                <!-- Select File Log (Rotasi Harian) -->
                <div class="w-full sm:w-72 md:w-80">
                    <select name="file" onchange="this.form.submit()"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-black dark:text-white text-[16px] sm:text-[13px] font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all cursor-pointer">
                        @if(!empty($filesMetadata))
                            @foreach($filesMetadata as $fKey => $fMeta)
                                <option value="{{ $fKey }}" {{ $selectedFile === $fKey ? 'selected' : '' }}>
                                    {{ $fMeta['label'] }}
                                </option>
                            @endforeach
                        @else
                            @foreach($files as $f)
                                <option value="{{ $f }}" {{ $selectedFile === $f ? 'selected' : '' }}>
                                    📁 {{ $f }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Select Level Log -->
                <div class="w-full sm:w-48">
                    <select name="level" onchange="this.form.submit()"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-black dark:text-white text-[16px] sm:text-[13px] focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all cursor-pointer">
                        <option value="all">Semua Level Log</option>
                        <option value="error" {{ request('level') === 'error' ? 'selected' : '' }}>🔴 ERROR &amp; CRITICAL</option>
                        <option value="warning" {{ request('level') === 'warning' ? 'selected' : '' }}>🟡 WARNING</option>
                        <option value="info" {{ request('level') === 'info' ? 'selected' : '' }}>🔵 INFO</option>
                        <option value="debug" {{ request('level') === 'debug' ? 'selected' : '' }}>🟣 DEBUG</option>
                    </select>
                </div>
            </div>

            <!-- Submit & Reset CTAs -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="submit"
                    class="h-11 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white font-bold text-[13px] flex items-center justify-center gap-1.5 shadow-sm shadow-[#007AFF]/25 transition-all cursor-pointer">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span>Terapkan Filter</span>
                </button>

                @if(request()->hasAny(['search', 'level']))
                    <a href="{{ route('admin.error-logs.index', ['file' => $selectedFile]) }}"
                        class="h-11 px-3.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] text-black/70 dark:text-white/70 text-[13px] font-semibold flex items-center justify-center gap-1 transition-all">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Log Entries Feed (Bento Cards List) -->
    <div class="space-y-3">
        @forelse($logs as $index => $log)
            @php
                $isErr = in_array($log['level'], ['ERROR', 'CRITICAL', 'EMERGENCY', 'ALERT'], true);
                $isWarn = $log['level'] === 'WARNING';
                $accentColor = $isErr ? '#FF3B30' : ($isWarn ? '#FF9500' : '#007AFF');
                $logKey = $log['id'] ?? $index;
            @endphp
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs overflow-hidden transition-all hover:border-black/[0.12] dark:hover:border-white/[0.15]"
                style="border-left: 4px solid {{ $accentColor }};">
                <div class="p-4 sm:p-5 space-y-3">
                    <!-- Top Meta Header -->
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-2">
                            @if($isErr)
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-extrabold bg-[#FF3B30]/12 text-[#FF3B30] dark:text-[#FF453A] flex items-center gap-1.5">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                    <span>{{ $log['level'] }}</span>
                                </span>
                            @elseif($isWarn)
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-extrabold bg-[#FF9500]/12 text-[#FF9500] dark:text-[#FF9F0A] flex items-center gap-1.5">
                                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                                    <span>{{ $log['level'] }}</span>
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-extrabold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center gap-1.5">
                                    <i data-lucide="info" class="w-3.5 h-3.5"></i>
                                    <span>{{ $log['level'] }}</span>
                                </span>
                            @endif

                            <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-mono font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60">
                                {{ $log['env'] }}
                            </span>

                            <span class="text-[12px] font-mono text-black/50 dark:text-white/50 tabular-nums flex items-center gap-1">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                <span>{{ $log['timestamp'] }}</span>
                            </span>
                        </div>

                        <!-- Stack Trace Toggle Trigger -->
                        @if(!empty($log['stack']))
                            <button type="button" @click="toggleTrace('{{ $logKey }}')"
                                class="h-8 px-3 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 text-[12px] font-bold flex items-center gap-1.5 transition-all cursor-pointer">
                                <i data-lucide="code" class="w-3.5 h-3.5"></i>
                                <span x-text="openTraces['{{ $logKey }}'] ? 'Sembunyikan Trace' : 'Lihat Stack Trace'"></span>
                            </button>
                        @endif
                    </div>

                    <!-- Exception / Error Message -->
                    <div class="font-mono text-[13.5px] font-bold text-black dark:text-white break-words leading-relaxed">
                        {{ $log['message'] }}
                    </div>

                    <!-- Diagnostic Stack Trace Collapsible Block -->
                    @if(!empty($log['stack']))
                        <div x-show="openTraces['{{ $logKey }}']" x-cloak
                            x-transition:enter="ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="pt-2 space-y-2">
                            <div class="flex items-center justify-between text-[11px] font-mono text-black/50 dark:text-white/50">
                                <span class="font-bold text-black/70 dark:text-white/70 flex items-center gap-1.5">
                                    <i data-lucide="terminal" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                    <span>Diagnostic Stack Trace:</span>
                                </span>
                                <button type="button"
                                    @click="copyTrace(@js($log['stack']), '{{ $logKey }}')"
                                    class="h-7 px-2.5 rounded-[7px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black/70 dark:text-white/70 text-[11px] font-bold flex items-center gap-1 transition-all cursor-pointer">
                                    <template x-if="copiedId === '{{ $logKey }}'">
                                        <span class="text-[#34C759] flex items-center gap-1">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            <span>Tersalin!</span>
                                        </span>
                                    </template>
                                    <template x-if="copiedId !== '{{ $logKey }}'">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="copy" class="w-3 h-3"></i>
                                            <span>Salin Trace</span>
                                        </span>
                                    </template>
                                </button>
                            </div>

                            <pre class="font-mono text-[11.5px] text-[#E5E5EA] bg-[#0A0A0C] border border-white/10 rounded-[12px] p-4 overflow-x-auto max-h-[380px] leading-relaxed whitespace-pre-wrap break-all select-text shadow-inner">{{ $log['stack'] }}</pre>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-10 sm:p-14 text-center space-y-3">
                <div class="w-16 h-16 rounded-full bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] flex items-center justify-center mx-auto shadow-md shadow-[#34C759]/20">
                    <i data-lucide="shield-check" class="w-8 h-8" stroke-width="2"></i>
                </div>
                <h3 class="text-[17px] font-extrabold text-black dark:text-white">Semua Berjalan Normal</h3>
                <p class="text-[13px] text-black/50 dark:text-white/50 max-w-md mx-auto">
                    Tidak ditemukan catatan exception atau log error pada berkas <code class="font-mono font-bold text-black/70 dark:text-white/70">{{ $selectedFile }}</code> yang sesuai dengan kriteria filter saat ini.
                </p>
            </div>
        @endforelse
    </div>

    <!-- MODAL SHEET: Konfirmasi Bersihkan Log (Pop-Up First Apple HIG) -->
    <div x-show="showClearModal" x-cloak class="relative z-50" aria-labelledby="clear-modal-title" role="dialog" aria-modal="true">
        <div x-show="showClearModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showClearModal = false"
            class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div x-show="showClearModal"
                x-transition:enter="ease-out duration-300 transform"
                x-transition:enter-start="translate-y-full sm:scale-95 opacity-0"
                x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100"
                x-transition:leave="ease-in duration-200 transform"
                x-transition:leave-start="translate-y-0 sm:scale-100 opacity-100"
                x-transition:leave-end="translate-y-full sm:scale-95 opacity-0"
                @click.outside="showClearModal = false"
                class="w-full max-w-md rounded-t-[28px] sm:rounded-[24px] sheet-material p-6 border-t sm:border border-black/[0.08] dark:border-white/[0.12] shadow-2xl space-y-5">
                
                <!-- Mobile Grabber Handle -->
                <div class="w-12 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto -mt-2 mb-3 sm:hidden"></div>

                <!-- Header -->
                <div class="flex items-start justify-between pb-3 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#FF3B30]/15 text-[#FF3B30] dark:text-[#FF453A] flex items-center justify-center font-bold">
                            <i data-lucide="trash-2" class="w-5 h-5" stroke-width="2"></i>
                        </div>
                        <div>
                            <h3 id="clear-modal-title" class="text-[17px] font-extrabold text-black dark:text-white">Kosongkan Berkas Log?</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Tindakan ini tidak dapat dibatalkan</p>
                        </div>
                    </div>
                    <button type="button" @click="showClearModal = false"
                        class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center active:scale-95 transition-all">
                        <i data-lucide="x" class="w-4 h-4" stroke-width="2"></i>
                    </button>
                </div>

                <!-- Content & Warning -->
                <div class="space-y-3 text-[13px]">
                    <div class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] space-y-2">
                        <div class="flex justify-between items-center text-[12px]">
                            <span class="text-black/50 dark:text-white/50">Berkas Target</span>
                            <span class="font-mono font-bold text-black dark:text-white">{{ $selectedFile }}</span>
                        </div>
                        <div class="flex justify-between items-center text-[12px]">
                            <span class="text-black/50 dark:text-white/50">Ukuran Saat Ini</span>
                            <span class="font-bold text-black dark:text-white tabular-nums">{{ $fileSize > 1024 ? round($fileSize / 1024, 2) . ' MB' : $fileSize . ' KB' }}</span>
                        </div>
                    </div>

                    <div class="p-3 rounded-[12px] bg-[#FF9500]/10 text-[#C97800] dark:text-[#FF9F0A] text-[11px] flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0" stroke-width="2"></i>
                        <span>💡 Disarankan mengunduh salinan berkas log terlebih dahulu sebelum mengosongkan.</span>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="pt-2 flex flex-col gap-2.5">
                    <form method="POST" action="{{ route('admin.error-logs.clear') }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="file" value="{{ $selectedFile }}">
                        <button type="submit"
                            class="w-full h-12 rounded-[14px] bg-[#FF3B30] hover:bg-[#E02D23] active:scale-[0.98] text-white font-bold text-[13px] flex items-center justify-center gap-2 shadow-md shadow-[#FF3B30]/25 transition-all cursor-pointer">
                            <i data-lucide="trash-2" class="w-4.5 h-4.5" stroke-width="2"></i>
                            <span>Ya, Kosongkan Berkas Sekarang</span>
                        </button>
                    </form>

                    <a href="{{ route('admin.error-logs.download', ['file' => $selectedFile]) }}"
                        class="w-full h-11 rounded-[14px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 active:scale-[0.98] text-[#007AFF] dark:text-[#0A84FF] font-bold text-[13px] flex items-center justify-center gap-2 transition-all">
                        <i data-lucide="download" class="w-4 h-4" stroke-width="2"></i>
                        <span>Unduh Salinan Cadangan</span>
                    </a>

                    <button type="button" @click="showClearModal = false"
                        class="w-full h-11 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-semibold text-[13px] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all cursor-pointer">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
