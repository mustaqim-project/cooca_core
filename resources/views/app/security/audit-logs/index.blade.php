@extends('layouts.app', [
    'title' => 'Jejak Audit & Anti-Fraud - Cooca',
    'headerTitle' => 'Jejak Audit & Forensik Keamanan',
    'headerSubtitle' => 'Deteksi kecurangan internal, pantau mutasi data sensitif, dan rekam jejak digital bisnis.'
])

@section('content')
<div class="max-w-[1400px] mx-auto space-y-6 pb-14" x-data="auditLogExplorer()">

    <!-- Top Bento Header -->
    <header class="rounded-[22px] backdrop-blur-md bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/5 dark:border-white/10 px-6 py-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-[16px] bg-[#FF3B30]/10 text-[#FF3B30] dark:bg-[#FF453A]/15 dark:text-[#FF453A] flex items-center justify-center shrink-0">
                <i data-lucide="shield-alert" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-black dark:text-white tracking-tight">
                    Jejak Audit & Anti-Fraud (Explorer)
                </h1>
                <p class="text-[13.5px] text-black/60 dark:text-white/60 mt-0.5">
                    Tabel log kepatuhan mutlak (*append-only*). Rekam jejak forensik digital perubahan data operasional.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('settings.audit-logs.export', request()->query()) }}"
               class="min-h-[40px] px-4 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.10] text-black/75 dark:text-white/75 font-semibold text-[13px] flex items-center gap-1.5 active:scale-95 transition-all">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Ekspor CSV</span>
            </a>
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-[13px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                <span>Tabel Terproteksi (Append-Only)</span>
            </span>
        </div>
    </header>

    <!-- 3-Column Bento Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <!-- Metric 1: Total Logs -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-[15px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                <i data-lucide="activity" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="text-[13px] font-semibold text-black/55 dark:text-white/55">
                    Aktivitas Tercatat (30 Hari)
                </div>
                <div class="text-2xl font-bold text-black dark:text-white tracking-tight mt-0.5">
                    {{ number_format($totalLogsCount, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Metric 2: High Risk -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-[15px] bg-[#FF3B30]/10 text-[#FF3B30] dark:bg-[#FF453A]/15 dark:text-[#FF453A] flex items-center justify-center shrink-0">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="text-[13px] font-semibold text-black/55 dark:text-white/55">
                    Aktivitas Risiko Tinggi
                </div>
                <div class="text-2xl font-bold text-[#FF3B30] dark:text-[#FF453A] tracking-tight mt-0.5 flex items-center gap-2">
                    <span>{{ number_format($highRiskCount, 0, ',', '.') }}</span>
                    @if($highRiskCount > 0)
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20 uppercase tracking-wider">
                        Perhatian
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Metric 3: WA Alerts Sent -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-[15px] bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="smartphone" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="text-[13px] font-semibold text-black/55 dark:text-white/55">
                    Peringatan WA Terkirim
                </div>
                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 tracking-tight mt-0.5">
                    {{ number_format($alertSentCount, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Cockpit -->
    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-sm space-y-4">
        <form method="GET" action="{{ route('settings.audit-logs.index') }}" class="space-y-4">
            <!-- Row 1: Risk Filter Chips -->
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[13.5px] font-bold text-black/70 dark:text-white/70 mr-1">
                        Tingkat Risiko:
                    </span>
                    <a href="{{ route('settings.audit-logs.index', array_merge(request()->except('risk_level', 'page'), [])) }}"
                       class="min-h-[40px] px-4 py-2 rounded-full text-[13px] font-semibold transition-all flex items-center gap-1.5 {{ empty($filters['risk_level']) ? 'bg-black text-white dark:bg-white dark:text-black shadow-sm' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 hover:bg-black/[0.08]' }}">
                        <span>Semua Risiko</span>
                    </a>
                    <a href="{{ route('settings.audit-logs.index', array_merge(request()->except('page'), ['risk_level' => 'high'])) }}"
                       class="min-h-[40px] px-4 py-2 rounded-full text-[13px] font-semibold transition-all flex items-center gap-1.5 {{ ($filters['risk_level'] ?? '') === 'high' ? 'bg-[#FF3B30] text-white shadow-sm' : 'bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-500/20' }}">
                        <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                        <span>Tinggi (High)</span>
                    </a>
                    <a href="{{ route('settings.audit-logs.index', array_merge(request()->except('page'), ['risk_level' => 'medium'])) }}"
                       class="min-h-[40px] px-4 py-2 rounded-full text-[13px] font-semibold transition-all flex items-center gap-1.5 {{ ($filters['risk_level'] ?? '') === 'medium' ? 'bg-amber-500 text-white shadow-sm' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20' }}">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        <span>Sedang (Medium)</span>
                    </a>
                    <a href="{{ route('settings.audit-logs.index', array_merge(request()->except('page'), ['risk_level' => 'low'])) }}"
                       class="min-h-[40px] px-4 py-2 rounded-full text-[13px] font-semibold transition-all flex items-center gap-1.5 {{ ($filters['risk_level'] ?? '') === 'low' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20' }}">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                        <span>Rendah (Low)</span>
                    </a>
                </div>

                @if(!empty(array_filter($filters)))
                <a href="{{ route('settings.audit-logs.index') }}" class="min-h-[40px] px-3.5 py-1.5 rounded-[12px] text-[13px] font-semibold text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center gap-1">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Reset Filter</span>
                </a>
                @endif
            </div>

            <!-- Row 2: Selects, Dates, and Search -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 pt-1">
                <!-- Modul -->
                <div>
                    <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">
                        Kategori Modul
                    </label>
                    <select name="module" class="w-full min-h-[44px] px-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        <option value="">Semua Modul</option>
                        <option value="pos" {{ ($filters['module'] ?? '') === 'pos' ? 'selected' : '' }}>Kasir POS (Void & Diskon)</option>
                        <option value="supplier" {{ ($filters['module'] ?? '') === 'supplier' ? 'selected' : '' }}>Pemasok / Rekening Bank</option>
                        <option value="finance" {{ ($filters['module'] ?? '') === 'finance' ? 'selected' : '' }}>Keuangan & Jurnal</option>
                        <option value="inventory" {{ ($filters['module'] ?? '') === 'inventory' ? 'selected' : '' }}>Stok & Produk</option>
                        <option value="users" {{ ($filters['module'] ?? '') === 'users' ? 'selected' : '' }}>Pengguna & Role</option>
                    </select>
                </div>

                <!-- Pelaku -->
                <div>
                    <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">
                        Pelaku (User)
                    </label>
                    <select name="user_id" class="w-full min-h-[44px] px-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        <option value="">Semua Staf / Pengguna</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ ($filters['user_id'] ?? '') === $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tanggal Mulai -->
                <div>
                    <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">
                        Dari Tanggal
                    </label>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}"
                           class="w-full min-h-[44px] px-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <!-- Tanggal Selesai -->
                <div>
                    <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">
                        Sampai Tanggal
                    </label>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}"
                           class="w-full min-h-[44px] px-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <!-- Kata Kunci / Tombol Cari -->
                <div>
                    <label class="block text-[12px] font-semibold text-black/60 dark:text-white/60 mb-1">
                        Cari Kata Kunci / IP
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Ref, alasan, IP..."
                               class="w-full min-h-[44px] px-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13.5px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        <button type="submit" class="min-h-[44px] px-4 rounded-[14px] bg-[#007AFF] text-white font-semibold hover:bg-[#007AFF]/90 active:scale-95 transition-all shrink-0 flex items-center justify-center">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Table of Audit Logs -->
    <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] text-[12.5px] font-bold text-black/60 dark:text-white/60 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Waktu & Forensik</th>
                        <th class="py-3.5 px-5">Pelaku</th>
                        <th class="py-3.5 px-5">Modul & Aksi</th>
                        <th class="py-3.5 px-5">Tingkat Risiko & Alasan</th>
                        <th class="py-3.5 px-5">Catatan Diinput</th>
                        <th class="py-3.5 px-5 text-center">Alert WA</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/10 text-[13.5px]">
                    @forelse($logs as $log)
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors {{ $log->isHighRisk() ? 'bg-red-500/[0.02]' : '' }}">
                        <!-- Waktu & IP -->
                        <td class="py-4 px-5 whitespace-nowrap">
                            <div class="font-bold text-black dark:text-white">
                                {{ $log->created_at ? $log->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y') : '-' }}
                            </div>
                            <div class="text-[12px] text-black/50 dark:text-white/50 flex items-center gap-1 mt-0.5 font-mono">
                                <i data-lucide="clock" class="w-3 h-3"></i>
                                <span>{{ $log->created_at ? $log->created_at->timezone('Asia/Jakarta')->format('H:i:s') : '-' }} WIB</span>
                                <span class="mx-0.5">•</span>
                                <span>{{ $log->ip_address ?: '127.0.0.1' }}</span>
                            </div>
                        </td>

                        <!-- Pelaku -->
                        <td class="py-4 px-5 whitespace-nowrap">
                            <div class="font-semibold text-black dark:text-white">
                                {{ $log->user?->name ?? 'Sistem / Otomatis' }}
                            </div>
                            <div class="text-[12px] text-black/50 dark:text-white/50 truncate max-w-[150px]">
                                {{ $log->user?->email ?? '-' }}
                            </div>
                        </td>

                        <!-- Modul & Aksi -->
                        <td class="py-4 px-5 whitespace-nowrap">
                            <div class="font-semibold text-black dark:text-white">
                                {{ $log->getModuleLabel() }}
                            </div>
                            <div class="mt-0.5">
                                @if($log->action === 'created')
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                    CREATED
                                </span>
                                @elseif($log->action === 'updated')
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400">
                                    UPDATED
                                </span>
                                @elseif($log->action === 'deleted')
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-red-500/10 text-red-600 dark:text-red-400">
                                    DELETED
                                </span>
                                @else
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-gray-500/10 text-gray-600 dark:text-gray-400">
                                    {{ strtoupper($log->action) }}
                                </span>
                                @endif
                            </div>
                        </td>

                        <!-- Risiko & Alasan -->
                        <td class="py-4 px-5">
                            <div class="flex items-center gap-1.5 mb-1">
                                @if($log->isHighRisk())
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11.5px] font-bold bg-[#FF3B30]/10 text-[#FF3B30] dark:bg-[#FF453A]/15 dark:text-[#FF453A] border border-[#FF3B30]/20">
                                    <i data-lucide="shield-alert" class="w-3 h-3"></i>
                                    <span>RISIKO TINGGI</span>
                                </span>
                                @elseif($log->isMediumRisk())
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11.5px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                    <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                    <span>SEDANG</span>
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11.5px] font-medium bg-black/[0.04] text-black/60 dark:bg-white/[0.06] dark:text-white/60">
                                    <span>RENDAH</span>
                                </span>
                                @endif
                            </div>
                            <div class="text-[12.5px] font-medium text-black/80 dark:text-white/85">
                                {{ $log->risk_reason ?: '-' }}
                            </div>
                        </td>

                        <!-- Catatan Diinput -->
                        <td class="py-4 px-5 max-w-[240px]">
                            <div class="text-[12.5px] text-black/70 dark:text-white/70 line-clamp-2" title="{{ $log->notes }}">
                                {{ $log->notes ?: '-' }}
                            </div>
                        </td>

                        <!-- Status Alert WhatsApp -->
                        <td class="py-4 px-5 text-center whitespace-nowrap">
                            @if($log->alert_sent_at)
                            <div class="inline-flex flex-col items-center">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11.5px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                    <span>Terkirim</span>
                                </span>
                                <span class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">
                                    {{ $log->alert_sent_at->timezone('Asia/Jakarta')->format('H:i') }} WIB
                                </span>
                            </div>
                            @elseif($log->isHighRisk())
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-500/10 text-red-500">
                                <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                <span>No Phone</span>
                            </span>
                            @else
                            <span class="text-[12px] text-black/30 dark:text-white/30">-</span>
                            @endif
                        </td>

                        <!-- Aksi: View Diff -->
                        <td class="py-4 px-5 text-right whitespace-nowrap">
                            <button type="button" @click="openDiffModal('{{ $log->id }}')"
                                    class="min-h-[40px] px-3.5 rounded-[12px] text-[13px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-95 transition-all inline-flex items-center gap-1.5">
                                <i data-lucide="git-compare" class="w-3.5 h-3.5"></i>
                                <span>Lihat Diff Data</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-black/40 dark:text-white/40">
                            <div class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 mx-auto flex items-center justify-center mb-3">
                                <i data-lucide="inbox" class="w-6 h-6"></i>
                            </div>
                            <p class="font-semibold text-sm">Tidak ada log aktivitas audit yang ditemukan.</p>
                            <p class="text-xs mt-1">Coba sesuaikan filter pencarian atau rentang tanggal Anda.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="p-4 border-t border-black/5 dark:border-white/10">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

    <!-- Apple HIG Bento Visual Diff Viewer Modal -->
    <div x-show="showModal"
         x-cloak
         @keydown.escape.window="showModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">

        <div @click.away="showModal = false"
             x-show="showModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-[850px] max-h-[90vh] flex flex-col rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden">

            <!-- Modal Header -->
            <div class="p-6 border-b border-black/5 dark:border-white/10 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="git-compare" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                            <span>Visual Diff Viewer (Komparasi Data)</span>
                            <template x-if="activeLog && activeLog.risk_level === 'high'">
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-red-500/15 text-red-600 dark:text-red-400 uppercase">
                                    Risiko Tinggi
                                </span>
                            </template>
                        </h3>
                        <p class="text-[12.5px] text-black/50 dark:text-white/50" x-text="activeLog ? activeLog.module_label + ' • ' + activeLog.created_at_formatted : 'Memuat data...'"></p>
                    </div>
                </div>

                <button type="button" @click="showModal = false"
                        class="w-9 h-9 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 flex items-center justify-center text-black/70 dark:text-white/70 transition-all">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Modal Content (Scrollable) -->
            <div class="p-6 overflow-y-auto space-y-6 flex-1">
                <template x-if="isLoading">
                    <div class="py-12 text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-[#007AFF] border-r-transparent"></div>
                        <p class="text-xs text-black/50 dark:text-white/50 mt-3 font-medium">Mengambil jejak perubahan data...</p>
                    </div>
                </template>

                <template x-if="!isLoading && activeLog">
                    <div class="space-y-6">
                        <!-- Bento Forensics Box -->
                        <div class="rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 p-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-[12.5px]">
                            <div>
                                <span class="font-bold text-black/50 dark:text-white/50 block">Pelaku (Performer)</span>
                                <span class="font-semibold text-black dark:text-white mt-0.5 block" x-text="activeLog.user_name"></span>
                                <span class="text-black/40 dark:text-white/40 text-[11px] block truncate" x-text="activeLog.user_email"></span>
                            </div>
                            <div>
                                <span class="font-bold text-black/50 dark:text-white/50 block">Identitas Forensik Digital</span>
                                <span class="font-mono text-black dark:text-white mt-0.5 block" x-text="'IP: ' + activeLog.ip_address"></span>
                                <span class="text-black/40 dark:text-white/40 text-[11px] block truncate" :title="activeLog.user_agent" x-text="activeLog.user_agent"></span>
                            </div>
                            <div>
                                <span class="font-bold text-black/50 dark:text-white/50 block">Status Peringatan WhatsApp</span>
                                <template x-if="activeLog.alert_sent_at">
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5 block" x-text="'Terkirim ke ' + activeLog.alert_recipient + ' (' + activeLog.alert_sent_at + ')'"></span>
                                </template>
                                <template x-if="!activeLog.alert_sent_at">
                                    <span class="text-black/40 dark:text-white/40 mt-0.5 block">Tidak memicu notifikasi darurat</span>
                                </template>
                            </div>
                        </div>

                        <!-- Risk Reason and User Note -->
                        <template x-if="activeLog.risk_reason || activeLog.notes">
                            <div class="rounded-[16px] p-4 border"
                                 :class="activeLog.risk_level === 'high' ? 'bg-red-500/[0.04] border-red-500/20 text-red-900 dark:text-red-200' : 'bg-amber-500/[0.04] border-amber-500/20 text-amber-900 dark:text-amber-200'">
                                <div class="font-bold text-[13px] flex items-center gap-1.5" x-text="activeLog.risk_reason"></div>
                                <div class="text-[12.5px] mt-1 text-black/75 dark:text-white/80" x-text="'Alasan/Catatan: ' + (activeLog.notes || '-')"></div>
                            </div>
                        </template>

                        <!-- Visual Diff Table -->
                        <div>
                            <h4 class="text-[13px] font-bold text-black/70 dark:text-white/70 uppercase tracking-wider mb-2.5">
                                Perbandingan Atribut Data (Old vs. New)
                            </h4>

                            <div class="rounded-[18px] border border-black/5 dark:border-white/10 overflow-hidden">
                                <table class="w-full text-left text-[13px]">
                                    <thead>
                                        <tr class="bg-black/[0.03] dark:bg-white/[0.04] border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-bold text-[11.5px] uppercase">
                                            <th class="py-2.5 px-4 w-1/3">Nama Atribut / Kolom</th>
                                            <th class="py-2.5 px-4 w-1/3">Nilai Lama (Sebelumnya)</th>
                                            <th class="py-2.5 px-4 w-1/3">Nilai Baru (Setelahnya)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-black/5 dark:divide-white/10">
                                        <template x-for="item in activeLog.diffs" :key="item.key">
                                            <tr :class="item.is_different ? 'bg-amber-500/[0.02]' : ''">
                                                <td class="py-3 px-4 font-mono font-medium text-black dark:text-white" x-text="item.label"></td>
                                                <td class="py-3 px-4">
                                                    <span :class="item.is_different ? 'line-through text-red-600 dark:text-red-400 bg-red-500/10 px-2 py-0.5 rounded font-mono text-[12px]' : 'text-black/50 dark:text-white/50 font-mono text-[12px]'"
                                                          x-text="item.old"></span>
                                                </td>
                                                <td class="py-3 px-4">
                                                    <span :class="item.is_different ? 'font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded font-mono text-[12px]' : 'text-black/75 dark:text-white/75 font-mono text-[12px]'"
                                                          x-text="item.new"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-black/5 dark:border-white/10 flex justify-end">
                <button type="button" @click="showModal = false"
                        class="min-h-[44px] px-6 rounded-[14px] bg-black dark:bg-white text-white dark:text-black font-semibold text-[13.5px] hover:opacity-90 active:scale-95 transition-all">
                    Tutup Tampilan Diff
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function auditLogExplorer() {
    return {
        showModal: false,
        isLoading: false,
        activeLog: null,

        async openDiffModal(logId) {
            this.showModal = true;
            this.isLoading = true;
            this.activeLog = null;

            try {
                const response = await fetch(`{{ url('/settings/audit-logs') }}/${logId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Gagal mengambil data log.');
                }

                this.activeLog = await response.json();
                this.$nextTick(() => {
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                });
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat memuat visual diff data.');
                this.showModal = false;
            } finally {
                this.isLoading = false;
            }
        }
    };
}
</script>
@endsection
