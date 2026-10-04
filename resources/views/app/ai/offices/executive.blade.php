@extends('layouts.ai', ['title' => 'COOCA AI — Executive Office'])

@section('content')
<div class="space-y-6 w-full" x-data="aiOfficeBase()">

    <!-- INTERACTIVE VIRTUAL OFFICE FLOOR (Watch AI Agents Work Live) -->
    @include('app.ai.partials.virtual_office_canvas', [
        'mode' => 'executive',
        'agentStatuses' => $officeStats['agent_statuses'] ?? [],
        'recentTasks' => $recentTasks,
        'pendingProposals' => $pendingProposals,
        'recentHistories' => $recentHistories,
    ])

    <!-- AI CEO BUSINESS COMMAND SUITE (TOP BENTO) -->
    <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-500/20 shrink-0">
                    <i data-lucide="crown" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-black dark:text-white">AI CEO</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Aktif & Siaga</span>
                    </div>
                    <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Chief Executive Officer &bull; Business Command & Strategy</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="triggerDailyDiagnosis()" :disabled="isEvaluating" class="px-3.5 py-2 rounded-xl bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-xs font-semibold text-black dark:text-white transition flex items-center gap-2 border border-black/10 dark:border-white/10">
                    <i data-lucide="activity" class="w-4 h-4 text-amber-500"></i>
                    <span x-text="isEvaluating ? 'Mengevaluasi Bisnis...' : 'Evaluasi Harian AI CEO'"></span>
                </button>
            </div>
        </div>

        <!-- Strategic Priorities & Opportunities Bento Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 border-t border-black/5 dark:border-white/5 pt-4">
            <div class="p-3.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                <div class="text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                    <span>Pertumbuhan Omzet</span>
                </div>
                <p class="text-xs text-black/70 dark:text-white/70">Memantau pergerakan omzet harian dan deviasi target mingguan dari kanal kasir & digital.</p>
            </div>

            <div class="p-3.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                <div class="text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                    <span>Mitigasi Risiko Keuangan</span>
                </div>
                <p class="text-xs text-black/70 dark:text-white/70">Mencegah pembengkakan beban kas, memantau piutang jatuh tempo & pengeluaran kas abnormal.</p>
            </div>

            <div class="p-3.5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                <div class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>Koordinasi Antar Divisi</span>
                </div>
                <p class="text-xs text-black/70 dark:text-white/70">Mendelegasikan analisis ke AI CFO, COO, dan CMO sebelum mengajukan proposal ke pemilik.</p>
            </div>
        </div>
    </div>

    <!-- FINANCIAL & EXECUTIVE KPI STRIP (Apple HIG Tabular Nums) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm">
            <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Omzet Bulan Ini</div>
            <div class="text-lg sm:text-xl font-bold text-black dark:text-white tabular-nums tracking-tight mt-1">
                Rp {{ number_format($financialHealth['monthly_revenue'] ?? $salesSummary['total_sales'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-black/40 dark:text-white/40 mt-1">Berdasarkan transaksi POS & Invoice</div>
        </div>

        <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm">
            <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Pengeluaran Kas</div>
            <div class="text-lg sm:text-xl font-bold text-black dark:text-white tabular-nums tracking-tight mt-1">
                Rp {{ number_format($financialHealth['monthly_expense'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-black/40 dark:text-white/40 mt-1">Beban operasional & logistik</div>
        </div>

        <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm">
            <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Estimasi Laba Kotor</div>
            <div class="text-lg sm:text-xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums tracking-tight mt-1">
                Rp {{ number_format($financialHealth['net_margin_estimate'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-black/40 dark:text-white/40 mt-1">Margin sehat di atas 20%</div>
        </div>

        <div class="rounded-2xl p-4 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm">
            <div class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Piutang Belum Terbayar</div>
            <div class="text-lg sm:text-xl font-bold text-amber-600 dark:text-amber-400 tabular-nums tracking-tight mt-1">
                Rp {{ number_format($financialHealth['unpaid_invoices_amount'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-black/40 dark:text-white/40 mt-1">Faktur & kasbon pelanggan aktif</div>
        </div>
    </div>

    <!-- EXECUTIVE SQUAD WORKSTATIONS (AI CFO, FINANCE, REPORTING, BUSINESS AGENT) -->
    <div class="space-y-4">
        <div>
            <h3 class="text-sm font-bold text-black dark:text-white">Meja Kerja Tim Eksekutif & Keuangan</h3>
            <p class="text-xs text-black/50 dark:text-white/50">Agen spesialis yang bertanggung jawab langsung kepada AI CEO dan AI CFO.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <!-- Workstation 1: Business Agent -->
            @php $bizAgent = $officeStats['agent_statuses']['business'] ?? null; @endphp
            <div class="rounded-2xl p-5 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm flex flex-col justify-between space-y-3">
                <div class="space-y-3">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-black/5 dark:bg-white/10 text-black dark:text-white flex items-center justify-center font-bold">
                                <i data-lucide="line-chart" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-black dark:text-white">Business Agent</h4>
                                <div class="text-[10px] text-black/40 dark:text-white/40">Laporan ke: AI CEO</div>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $bizAgent['status'] === 'WORKING' ? 'bg-blue-500/10 text-blue-600' : ($bizAgent['status'] === 'WAITING_APPROVAL' ? 'bg-amber-500/10 text-amber-600' : 'bg-emerald-500/10 text-emerald-600') }}">
                            {{ $bizAgent['status_label'] ?? 'Tersedia' }}
                        </span>
                    </div>

                    <p class="text-xs text-black/60 dark:text-white/60">Analisis menyeluruh kesehatan bisnis, identifikasi masalah operasional, dan peluang antar modul.</p>
                    
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 text-[11px] text-black/70 dark:text-white/70">
                        <span class="text-black/40 dark:text-white/40 font-medium">Tugas Saat Ini:</span>
                        <div class="font-semibold text-black dark:text-white truncate mt-0.5">{{ $bizAgent['current_work'] ?? 'Tidak ada tugas aktif' }}</div>
                    </div>
                </div>

                <div class="text-[10px] text-black/40 dark:text-white/40 pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                    <span>Aktivitas Terakhir:</span>
                    <span class="font-medium">{{ $bizAgent['last_active'] ?? '-' }}</span>
                </div>
            </div>

            <!-- Workstation 2: Finance Agent (Under AI CFO) -->
            @php $finAgent = $officeStats['agent_statuses']['finance'] ?? null; @endphp
            <div class="rounded-2xl p-5 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm flex flex-col justify-between space-y-3">
                <div class="space-y-3">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-black/5 dark:bg-white/10 text-black dark:text-white flex items-center justify-center font-bold">
                                <i data-lucide="coins" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-black dark:text-white">Finance Agent</h4>
                                <div class="text-[10px] text-black/40 dark:text-white/40">Laporan ke: AI CFO</div>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $finAgent['status'] === 'WORKING' ? 'bg-blue-500/10 text-blue-600' : ($finAgent['status'] === 'WAITING_APPROVAL' ? 'bg-amber-500/10 text-amber-600' : 'bg-emerald-500/10 text-emerald-600') }}">
                            {{ $finAgent['status_label'] ?? 'Tersedia' }}
                        </span>
                    </div>

                    <p class="text-xs text-black/60 dark:text-white/60">Analisis pendapatan, pengeluaran kas, saldo kasbank, piutang, dan deteksi anomali finansial.</p>
                    
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 text-[11px] text-black/70 dark:text-white/70">
                        <span class="text-black/40 dark:text-white/40 font-medium">Tugas Saat Ini:</span>
                        <div class="font-semibold text-black dark:text-white truncate mt-0.5">{{ $finAgent['current_work'] ?? 'Tidak ada tugas aktif' }}</div>
                    </div>
                </div>

                <div class="text-[10px] text-black/40 dark:text-white/40 pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                    <span>Aktivitas Terakhir:</span>
                    <span class="font-medium">{{ $finAgent['last_active'] ?? '-' }}</span>
                </div>
            </div>

            <!-- Workstation 3: Reporting Agent (Under AI CFO) -->
            @php $repAgent = $officeStats['agent_statuses']['reporting'] ?? null; @endphp
            <div class="rounded-2xl p-5 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm flex flex-col justify-between space-y-3">
                <div class="space-y-3">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-black/5 dark:bg-white/10 text-black dark:text-white flex items-center justify-center font-bold">
                                <i data-lucide="file-text" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-black dark:text-white">Reporting Agent</h4>
                                <div class="text-[10px] text-black/40 dark:text-white/40">Laporan ke: AI CFO</div>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $repAgent['status'] === 'WORKING' ? 'bg-blue-500/10 text-blue-600' : ($repAgent['status'] === 'WAITING_APPROVAL' ? 'bg-amber-500/10 text-amber-600' : 'bg-emerald-500/10 text-emerald-600') }}">
                            {{ $repAgent['status_label'] ?? 'Tersedia' }}
                        </span>
                    </div>

                    <p class="text-xs text-black/60 dark:text-white/60">Penyusunan rekapitulasi performa harian, mingguan, bulanan, dan ringkasan eksekutif untuk manajemen.</p>
                    
                    <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 text-[11px] text-black/70 dark:text-white/70">
                        <span class="text-black/40 dark:text-white/40 font-medium">Tugas Saat Ini:</span>
                        <div class="font-semibold text-black dark:text-white truncate mt-0.5">{{ $repAgent['current_work'] ?? 'Tidak ada tugas aktif' }}</div>
                    </div>
                </div>

                <div class="text-[10px] text-black/40 dark:text-white/40 pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                    <span>Aktivitas Terakhir:</span>
                    <span class="font-medium">{{ $repAgent['last_active'] ?? '-' }}</span>
                </div>
            </div>

        </div>
    </div>

    <!-- CURRENT WORK & ACTIVE TASKS IN EXECUTIVE OFFICE -->
    <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div>
                <h3 class="text-sm font-bold text-black dark:text-white">Pekerjaan Berjalan di Executive Office</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Daftar tugas analitis yang sedang atau baru saja diproses oleh agen eksekutif.</p>
            </div>
            <span class="px-2.5 py-1 rounded-md text-[11px] font-mono font-semibold bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">
                {{ $recentTasks->count() }} Tugas Tercatat
            </span>
        </div>

        <div class="space-y-2">
            @forelse($recentTasks as $task)
                <div class="p-3.5 rounded-2xl bg-black/[0.01] dark:bg-white/[0.01] border border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="activity" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80 uppercase">
                                    {{ $task->agent }}
                                </span>
                                <span class="text-xs font-semibold text-black dark:text-white">{{ $task->input }}</span>
                            </div>
                            <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">
                                {{ $task->created_at->diffForHumans() }} &bull; Durasi: {{ $task->duration_ms ?? 0 }}ms
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0">
                        @if($task->isCompleted())
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">COMPLETED</span>
                        @elseif($task->isRunning())
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400">ANALYZING</span>
                        @elseif($task->isWaitingApproval())
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400">WAITING APPROVAL</span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-zinc-500/10 text-zinc-600 dark:text-zinc-400">{{ $task->status }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6 rounded-2xl bg-black/[0.01] dark:bg-white/[0.01] border border-black/5 dark:border-white/5 text-center text-xs text-black/50 dark:text-white/50">
                    Tidak ada tugas aktif di Executive Office. Seluruh agen siaga.
                </div>
            @endforelse
        </div>
    </div>

    <!-- ACTIONS REQUIRING OWNER APPROVAL (MAKER-CHECKER) -->
    @if($pendingProposals->count() > 0)
    <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-amber-500/20 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div>
                <h3 class="text-sm font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
                    <span>Usulan Keputusan Eksekutif Menunggu Persetujuan Anda</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">AI CEO & AI CFO dilarang melakukan aksi finansial sepihak. Persetujuan pemilik usaha wajib.</p>
            </div>
            <a href="{{ route('cooca-ai.actions', ['status' => 'pending']) }}" class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline">
                Buka Action Center &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($pendingProposals as $proposal)
                <div class="p-4 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/10 dark:border-white/10 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80 uppercase">
                                    {{ $proposal->agent }}
                                </span>
                                <span class="px-2 py-0.2 rounded text-[10px] font-bold uppercase {{ $proposal->risk_level === 'high' || $proposal->risk_level === 'critical' ? 'bg-red-500/15 text-red-600 dark:text-red-400' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400' }}">
                                    {{ $proposal->risk_level }}
                                </span>
                            </div>
                            <h4 class="text-xs font-bold text-black dark:text-white">{{ $proposal->title }}</h4>
                            <p class="text-xs text-black/60 dark:text-white/60 line-clamp-2">{{ $proposal->description }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-black/5 dark:border-white/5 text-xs">
                        <span class="text-black/40 dark:text-white/40 font-mono text-[11px]">{{ $proposal->created_at->diffForHumans() }}</span>
                        <div class="flex items-center gap-2">
                            <form action="{{ route('cooca-ai.actions.reject', $proposal) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 font-semibold text-black/80 dark:text-white/80 transition">Tolak</button>
                            </form>
                            <form action="{{ route('cooca-ai.actions.approve', $proposal) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-black hover:bg-black/90 dark:bg-white dark:hover:bg-white/90 text-white dark:text-black font-semibold transition">Setujui</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- RECENT EXECUTIVE WORK HISTORY -->
    <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div>
                <h3 class="text-sm font-bold text-black dark:text-white">Arsip Rekam Kerja Eksekutif</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Jejak audit pekerjaan yang telah diselesaikan oleh tim Executive Office.</p>
            </div>
            <a href="{{ route('cooca-ai.history') }}" class="text-xs font-semibold text-black/70 dark:text-white/70 hover:underline">
                Lihat Seluruh Riwayat &rarr;
            </a>
        </div>

        <div class="space-y-2.5">
            @forelse($recentHistories as $hist)
                <div class="p-3.5 rounded-2xl bg-black/[0.01] dark:bg-white/[0.01] border border-black/5 dark:border-white/5 flex items-start justify-between gap-3 text-xs">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            @if(is_array($hist->participating_agents) && count($hist->participating_agents) > 0)
                                @foreach(array_slice($hist->participating_agents, 0, 2) as $ag)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80 uppercase">{{ $ag }}</span>
                                @endforeach
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80 uppercase">{{ $hist->agent }}</span>
                            @endif
                            <span class="font-bold text-black dark:text-white">{{ $hist->session_title ?? $hist->action }}</span>
                        </div>
                        <p class="text-black/60 dark:text-white/60 line-clamp-1">{{ $hist->executive_summary ?? $hist->summary }}</p>
                    </div>
                    <span class="text-[11px] text-black/40 dark:text-white/40 font-mono shrink-0">{{ $hist->recorded_at->diffForHumans() }}</span>
                </div>
            @empty
                <div class="p-6 rounded-2xl bg-black/[0.01] dark:bg-white/[0.01] border border-black/5 dark:border-white/5 text-center text-xs text-black/50 dark:text-white/50">
                    Belum ada riwayat rekam kerja di Executive Office.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Shared Consultation Modal -->
    @include('app.ai.partials.consultation_modal')

</div>

<!-- Shared Alpine Script -->
@include('app.ai.partials.office_script')
@endsection
