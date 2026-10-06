@extends('layouts.app', ['title' => 'COOCA AI Digital Company'])

@section('content')
<div class="space-y-6 w-full" x-data="aiOfficeBase()">

    <!-- Unified Apple HIG Navigation Hub -->
    @include('app.ai.partials.office_navigation', ['activeOffice' => 'lobby', 'pendingCount' => $pendingCount])

    @php
        $allAgentStatuses = array_merge(
            $officesStats['executive']['agent_statuses'] ?? [],
            $officesStats['operations']['agent_statuses'] ?? [],
            $officesStats['growth']['agent_statuses'] ?? []
        );
    @endphp

    <!-- INTERACTIVE VIRTUAL HEADQUARTERS CAMPUS (Watch AI Agents Work Live) -->
    @include('app.ai.partials.virtual_office_canvas', [
        'mode' => 'lobby',
        'agentStatuses' => $allAgentStatuses,
        'recentTasks' => $recentTasks,
        'pendingProposals' => $pendingProposals,
        'recentHistories' => $recentHistories,
        'companyTotals' => $companyTotals,
    ])

    <!-- COMPANY OVERVIEW & 3 OFFICES BENTO GRID -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-black dark:text-white tracking-tight">Tiga Kantor Digital Terintegrasi</h2>
                <p class="text-xs text-black/50 dark:text-white/50">Tiga lingkungan kerja terpisah yang saling terhubung dalam satu sistem organisasi COOCA AI terpadu.</p>
            </div>
            <div class="text-right hidden sm:block">
                <span class="text-xs text-black/40 dark:text-white/40">Status Mesin:</span>
                <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400 uppercase ml-1">
                    {{ $resolvedProvider['provider']->getProviderName() }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            
            <!-- OFFICE 01: EXECUTIVE OFFICE CARD -->
            <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm flex flex-col justify-between hover:border-amber-500/40 dark:hover:border-amber-500/40 transition-all group">
                <div class="space-y-4">
                    <div class="flex items-start justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-500/20 group-hover:scale-105 transition-transform">
                            <i data-lucide="crown" class="w-6 h-6"></i>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $officesStats['executive']['health_status'] === 'optimal' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400' }}">
                            {{ $officesStats['executive']['health_label'] }}
                        </span>
                    </div>

                    <div>
                        <div class="text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Office 01</div>
                        <h3 class="text-lg font-bold text-black dark:text-white tracking-tight mt-0.5">Executive Office</h3>
                        <p class="text-xs text-black/60 dark:text-white/60 mt-1 line-clamp-2">Pusat komando strategi bisnis, visibilitas keuangan, pemantauan KPI, risiko dan rekomendasi tingkat eksekutif.</p>
                    </div>

                    <!-- Executive Squad Hierarchy Overview -->
                    <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-2">
                        <div class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider">Pimpinan & Tim:</div>
                        <div class="flex flex-wrap items-center gap-1.5 text-xs text-black/80 dark:text-white/80">
                            <span class="px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-700 dark:text-amber-300 font-semibold text-[11px]">AI CEO</span>
                            <span class="text-black/30 dark:text-white/30">&bull;</span>
                            <span class="px-2 py-0.5 rounded-md bg-black/5 dark:bg-white/10 text-[11px]">AI CFO</span>
                            <span class="text-black/30 dark:text-white/30">&bull;</span>
                            <span class="text-[11px] text-black/60 dark:text-white/60">Business, Finance, Reporting</span>
                        </div>
                    </div>

                    <!-- Live Backend Metrics -->
                    <div class="grid grid-cols-3 gap-2 pt-1 border-t border-black/5 dark:border-white/5 text-center">
                        <div class="p-2 rounded-xl bg-black/[0.01] dark:bg-white/[0.01]">
                            <div class="text-[10px] text-black/50 dark:text-white/50 uppercase">Agen Aktif</div>
                            <div class="text-sm font-bold text-black dark:text-white tabular-nums mt-0.5">{{ $officesStats['executive']['active_agents_count'] }} / {{ $officesStats['executive']['total_agents'] }}</div>
                        </div>
                        <div class="p-2 rounded-xl bg-black/[0.01] dark:bg-white/[0.01]">
                            <div class="text-[10px] text-black/50 dark:text-white/50 uppercase">Tugas Berjalan</div>
                            <div class="text-sm font-bold text-black dark:text-white tabular-nums mt-0.5">{{ $officesStats['executive']['active_tasks_count'] }}</div>
                        </div>
                        <div class="p-2 rounded-xl bg-black/[0.01] dark:bg-white/[0.01]">
                            <div class="text-[10px] text-black/50 dark:text-white/50 uppercase">Keputusan</div>
                            <div class="text-sm font-bold text-amber-600 dark:text-amber-400 tabular-nums mt-0.5">{{ $officesStats['executive']['pending_approvals_count'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="pt-5">
                    <a href="{{ route('cooca-ai.office.executive') }}" class="w-full py-2.5 px-4 rounded-2xl bg-black hover:bg-black/90 dark:bg-white dark:hover:bg-white/90 text-white dark:text-black font-semibold text-xs transition shadow-sm flex items-center justify-center gap-2">
                        <span>Masuk Executive Office</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>

            <!-- OFFICE 02: OPERATIONS OFFICE CARD -->
            <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm flex flex-col justify-between hover:border-blue-500/40 dark:hover:border-blue-500/40 transition-all group">
                <div class="space-y-4">
                    <div class="flex items-start justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-500/20 group-hover:scale-105 transition-transform">
                            <i data-lucide="cpu" class="w-6 h-6"></i>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $officesStats['operations']['health_status'] === 'optimal' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-blue-500/10 text-blue-600 dark:text-blue-400' }}">
                            {{ $officesStats['operations']['health_label'] }}
                        </span>
                    </div>

                    <div>
                        <div class="text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Office 02</div>
                        <h3 class="text-lg font-bold text-black dark:text-white tracking-tight mt-0.5">Operations Office</h3>
                        <p class="text-xs text-black/60 dark:text-white/60 mt-1 line-clamp-2">Pusat kendali operasional harian: ketersediaan inventori, pengadaan bahan baku, dan omnichannel marketplace.</p>
                    </div>

                    <!-- Operations Squad Hierarchy Overview -->
                    <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-2">
                        <div class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider">Pimpinan & Tim:</div>
                        <div class="flex flex-wrap items-center gap-1.5 text-xs text-black/80 dark:text-white/80">
                            <span class="px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-700 dark:text-blue-300 font-semibold text-[11px]">AI COO</span>
                            <span class="text-black/30 dark:text-white/30">&bull;</span>
                            <span class="text-[11px] text-black/60 dark:text-white/60">Inventory, Purchasing, Marketplace</span>
                        </div>
                    </div>

                    <!-- Live Backend Metrics -->
                    <div class="grid grid-cols-3 gap-2 pt-1 border-t border-black/5 dark:border-white/5 text-center">
                        <div class="p-2 rounded-xl bg-black/[0.01] dark:bg-white/[0.01]">
                            <div class="text-[10px] text-black/50 dark:text-white/50 uppercase">Agen Aktif</div>
                            <div class="text-sm font-bold text-black dark:text-white tabular-nums mt-0.5">{{ $officesStats['operations']['active_agents_count'] }} / {{ $officesStats['operations']['total_agents'] }}</div>
                        </div>
                        <div class="p-2 rounded-xl bg-black/[0.01] dark:bg-white/[0.01]">
                            <div class="text-[10px] text-black/50 dark:text-white/50 uppercase">Tugas Operasional</div>
                            <div class="text-sm font-bold text-black dark:text-white tabular-nums mt-0.5">{{ $officesStats['operations']['active_tasks_count'] }}</div>
                        </div>
                        <div class="p-2 rounded-xl bg-black/[0.01] dark:bg-white/[0.01]">
                            <div class="text-[10px] text-black/50 dark:text-white/50 uppercase">Usulan PO/Stok</div>
                            <div class="text-sm font-bold text-blue-600 dark:text-blue-400 tabular-nums mt-0.5">{{ $officesStats['operations']['pending_approvals_count'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="pt-5">
                    <a href="{{ route('cooca-ai.office.operations') }}" class="w-full py-2.5 px-4 rounded-2xl bg-black hover:bg-black/90 dark:bg-white dark:hover:bg-white/90 text-white dark:text-black font-semibold text-xs transition shadow-sm flex items-center justify-center gap-2">
                        <span>Masuk Operations Office</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>

            <!-- OFFICE 03: GROWTH OFFICE CARD -->
            <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm flex flex-col justify-between hover:border-purple-500/40 dark:hover:border-purple-500/40 transition-all group">
                <div class="space-y-4">
                    <div class="flex items-start justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-500/20 group-hover:scale-105 transition-transform">
                            <i data-lucide="sparkles" class="w-6 h-6"></i>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $officesStats['growth']['health_status'] === 'optimal' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-purple-500/10 text-purple-600 dark:text-purple-400' }}">
                            {{ $officesStats['growth']['health_label'] }}
                        </span>
                    </div>

                    <div>
                        <div class="text-[11px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Office 03</div>
                        <h3 class="text-lg font-bold text-black dark:text-white tracking-tight mt-0.5">Growth Office</h3>
                        <p class="text-xs text-black/60 dark:text-white/60 mt-1 line-clamp-2">Pusat pertumbuhan komersial: kampanye pemasaran, produksi naskah konten, media sosial dan retensi pelanggan.</p>
                    </div>

                    <!-- Growth Squad Hierarchy Overview -->
                    <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-2">
                        <div class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider">Pimpinan & Tim:</div>
                        <div class="flex flex-wrap items-center gap-1.5 text-xs text-black/80 dark:text-white/80">
                            <span class="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-700 dark:text-purple-300 font-semibold text-[11px]">AI CMO</span>
                            <span class="text-black/30 dark:text-white/30">&bull;</span>
                            <span class="px-2 py-0.5 rounded-md bg-black/5 dark:bg-white/10 text-[11px]">Sales Director</span>
                            <span class="text-black/30 dark:text-white/30">&bull;</span>
                            <span class="text-[11px] text-black/60 dark:text-white/60">Marketing, Content, Social, Sales, Customer</span>
                        </div>
                    </div>

                    <!-- Live Backend Metrics -->
                    <div class="grid grid-cols-3 gap-2 pt-1 border-t border-black/5 dark:border-white/5 text-center">
                        <div class="p-2 rounded-xl bg-black/[0.01] dark:bg-white/[0.01]">
                            <div class="text-[10px] text-black/50 dark:text-white/50 uppercase">Agen Aktif</div>
                            <div class="text-sm font-bold text-black dark:text-white tabular-nums mt-0.5">{{ $officesStats['growth']['active_agents_count'] }} / {{ $officesStats['growth']['total_agents'] }}</div>
                        </div>
                        <div class="p-2 rounded-xl bg-black/[0.01] dark:bg-white/[0.01]">
                            <div class="text-[10px] text-black/50 dark:text-white/50 uppercase">Tugas Kampanye</div>
                            <div class="text-sm font-bold text-black dark:text-white tabular-nums mt-0.5">{{ $officesStats['growth']['active_tasks_count'] }}</div>
                        </div>
                        <div class="p-2 rounded-xl bg-black/[0.01] dark:bg-white/[0.01]">
                            <div class="text-[10px] text-black/50 dark:text-white/50 uppercase">Usulan Promo</div>
                            <div class="text-sm font-bold text-purple-600 dark:text-purple-400 tabular-nums mt-0.5">{{ $officesStats['growth']['pending_approvals_count'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="pt-5">
                    <a href="{{ route('cooca-ai.office.growth') }}" class="w-full py-2.5 px-4 rounded-2xl bg-black hover:bg-black/90 dark:bg-white dark:hover:bg-white/90 text-white dark:text-black font-semibold text-xs transition shadow-sm flex items-center justify-center gap-2">
                        <span>Masuk Growth Office</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>

    <!-- CROSS-OFFICE COLLABORATION TIMELINE WIDGET -->
    <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-black/10 dark:border-white/10 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div>
                <h3 class="text-sm font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="git-merge" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                    <span>Kolaborasi Lintas Kantor (Cross-Office Workflow)</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Tiga kantor saling bertukar data secara berjenjang untuk merumuskan usulan aksi bisnis terpadu.</p>
            </div>
            <div class="hidden sm:flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Alur Otomatis Aktif</span>
            </div>
        </div>

        <!-- Workflow Visual Concept Diagram -->
        <div class="p-4 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
            <div class="flex flex-col md:flex-row items-center justify-between gap-3 text-xs">
                
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold shrink-0">1</div>
                    <div>
                        <div class="font-bold text-black dark:text-white">Growth / Sales</div>
                        <div class="text-[11px] text-black/50 dark:text-white/50">Mendeteksi fluktuasi</div>
                    </div>
                </div>

                <i data-lucide="arrow-right" class="w-4 h-4 text-black/30 dark:text-white/30 rotate-90 md:rotate-0"></i>

                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold shrink-0">2</div>
                    <div>
                        <div class="font-bold text-black dark:text-white">Executive / CEO</div>
                        <div class="text-[11px] text-black/50 dark:text-white/50">Diagnosa & prioritas</div>
                    </div>
                </div>

                <i data-lucide="arrow-right" class="w-4 h-4 text-black/30 dark:text-white/30 rotate-90 md:rotate-0"></i>

                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold shrink-0">3</div>
                    <div>
                        <div class="font-bold text-black dark:text-white">Operations / COO</div>
                        <div class="text-[11px] text-black/50 dark:text-white/50">Validasi stok & logistik</div>
                    </div>
                </div>

                <i data-lucide="arrow-right" class="w-4 h-4 text-black/30 dark:text-white/30 rotate-90 md:rotate-0"></i>

                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold shrink-0">4</div>
                    <div>
                        <div class="font-bold text-black dark:text-white">Action Proposal</div>
                        <div class="text-[11px] text-black/50 dark:text-white/50">Keputusan Pemilik</div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Real Task Timeline Feed -->
        <div class="space-y-2.5 pt-1">
            <div class="text-xs font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider">Aktivitas Koordinasi Nyata:</div>
            
            @forelse($recentTasks as $task)
                <div class="p-3.5 rounded-2xl bg-black/[0.01] dark:bg-white/[0.01] border border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/70 dark:text-white/70 shrink-0 mt-0.5">
                            <i data-lucide="workflow" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-black/5 dark:bg-white/10 text-black/80 dark:text-white/80 uppercase">
                                    {{ $task->agent }}
                                </span>
                                <span class="text-xs font-semibold text-black dark:text-white truncate max-w-[320px] sm:max-w-md">
                                    {{ $task->input }}
                                </span>
                            </div>
                            <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5 flex items-center gap-2">
                                <span>{{ $task->created_at->diffForHumans() }}</span>
                                <span>&bull;</span>
                                <span class="font-mono text-[10px]">{{ $task->duration_ms ?? 0 }}ms</span>
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        @if($task->isCompleted())
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Selesai</span>
                        @elseif($task->isRunning())
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 animate-pulse">Bekerja</span>
                        @elseif($task->isWaitingApproval())
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400">Menunggu Approval</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-zinc-500/10 text-zinc-600 dark:text-zinc-400">{{ $task->status }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6 rounded-2xl bg-black/[0.01] dark:bg-white/[0.01] border border-black/5 dark:border-white/5 text-center text-xs text-black/50 dark:text-white/50">
                    Belum ada riwayat koordinasi tugas. Klik "Simulasi Kolaborasi Harian" atau "Konsultasi AI" untuk memulai.
                </div>
            @endforelse
        </div>
    </div>

    <!-- PENDING APPROVALS REQUIRING OWNER DECISION (MAKER-CHECKER) -->
    @if($pendingProposals->count() > 0)
    <div class="rounded-3xl p-6 bg-white dark:bg-zinc-900 border border-amber-500/30 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-black dark:text-white">Ada {{ $pendingCount }} Usulan Aksi Menunggu Keputusan Anda</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-700 dark:text-amber-300">Maker-Checker</span>
                </div>
                <p class="text-xs text-black/60 dark:text-white/60 mt-1">Tim AI telah menyusun rekomendasi bisnis. Sebagai pemilik usaha, eksekusi baru berjalan setelah persetujuan Anda di Action Center.</p>
            </div>
        </div>
        <a href="{{ route('cooca-ai.actions', ['status' => 'pending']) }}" class="px-5 py-3 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2 shrink-0">
            <span>Tinjau di Action Center ({{ $pendingCount }})</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
    @endif

    <!-- Shared Consultation Modal -->
    @include('app.ai.partials.consultation_modal')

</div>

<!-- Shared Alpine Script -->
@include('app.ai.partials.office_script')
@endsection
