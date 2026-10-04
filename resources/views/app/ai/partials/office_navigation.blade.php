{{-- Shared Persistent Navigation Bar for COOCA AI Office (Image 2 Alignment) --}}
@props([
    'activeOffice' => 'lobby', // lobby, executive, operations, growth
    'pendingCount' => 0,
])

@php
    $biz = \App\Support\Context::business();
    $currUser = \App\Support\Context::user();
@endphp

<div class="space-y-4">
    <!-- Top Modern COOCA AI Header Bar (Matching Image 2 Reference) -->
    <div class="px-4 py-3 rounded-2xl bg-[#0c192c] border border-white/10 text-white shadow-xl flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        
        <!-- Left: COOCA AI Brand & Digital Workforce Tag -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/25 shrink-0 ring-2 ring-cyan-400/30">
                <i data-lucide="bot" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-lg font-black tracking-tight text-white">COOCA AI</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">v2.5</span>
                </div>
                <div class="text-[11px] text-cyan-200/70 font-medium">Your Digital Workforce</div>
            </div>
        </div>

        <!-- Center: Primary System Navigation Tabs (Matching Image 2) -->
        <div class="flex flex-wrap items-center gap-1.5 p-1 rounded-xl bg-slate-900/80 border border-white/10">
            <!-- AI Office (Primary active tab) -->
            <a href="{{ route('cooca-ai.index') }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-2 {{ request()->routeIs('cooca-ai.index') || request()->routeIs('cooca-ai.office.*') ? 'bg-[#1e3557] text-cyan-300 shadow-sm border border-cyan-500/30 font-bold' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <i data-lucide="building-2" class="w-3.5 h-3.5 text-cyan-400"></i>
                <span>AI Office</span>
            </a>

            <!-- Chat / Tanya AI -->
            <button type="button" @click="openConsultationModal()"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/5 transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="message-square" class="w-3.5 h-3.5 text-emerald-400"></i>
                <span>Chat</span>
            </button>

            <!-- Actions Center -->
            <a href="{{ route('cooca-ai.actions') }}"
               class="relative px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-2 {{ request()->routeIs('cooca-ai.actions*') ? 'bg-[#1e3557] text-cyan-300 shadow-sm border border-cyan-500/30 font-bold' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <i data-lucide="check-square" class="w-3.5 h-3.5 text-amber-400"></i>
                <span>Actions</span>
                @if($pendingCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-amber-500 text-white tabular-nums">{{ $pendingCount }}</span>
                @endif
            </a>

            <!-- Work History -->
            <a href="{{ route('cooca-ai.history') }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-2 {{ request()->routeIs('cooca-ai.history*') ? 'bg-[#1e3557] text-cyan-300 shadow-sm border border-cyan-500/30 font-bold' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <i data-lucide="history" class="w-3.5 h-3.5 text-sky-400"></i>
                <span>History</span>
            </a>

            <!-- AI Settings & Providers -->
            <a href="{{ route('cooca-ai.providers') }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-2 {{ request()->routeIs('cooca-ai.providers*') ? 'bg-[#1e3557] text-cyan-300 shadow-sm border border-cyan-500/30 font-bold' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <i data-lucide="settings" class="w-3.5 h-3.5 text-purple-400"></i>
                <span>Settings</span>
            </a>
        </div>

        <!-- Right: Active Tenant & User Avatar Profile (Matching Image 2) -->
        <div class="flex items-center gap-3">
            <!-- Tenant Switcher Dropdown Pill -->
            <div class="px-3 py-1.5 rounded-xl bg-slate-900/90 border border-white/10 text-xs flex items-center gap-2">
                <i data-lucide="store" class="w-4 h-4 text-cyan-400 shrink-0"></i>
                <div class="text-left">
                    <div class="font-bold text-white leading-tight truncate max-w-[120px]">{{ $biz->name ?? 'Toko Sejahtera' }}</div>
                    <div class="text-[10px] text-slate-400">Business Owner</div>
                </div>
            </div>

            <!-- User Profile Avatar Pill -->
            <div class="flex items-center gap-2 pl-2 border-l border-white/10">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-amber-400 to-rose-500 text-white font-bold text-xs flex items-center justify-center ring-2 ring-white/20">
                    {{ strtoupper(substr($currUser->name ?? 'B', 0, 1)) }}
                </div>
                <div class="text-left hidden sm:block">
                    <div class="text-xs font-bold text-white leading-tight">Hi, {{ explode(' ', $currUser->name ?? 'Budi')[0] }}</div>
                    <div class="text-[10px] text-slate-400">Owner</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3 OFFICE PERSISTENT NAVIGATION (Segmented Control ala Apple macOS / iOS) -->
    <div class="p-1 rounded-2xl bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 flex items-center overflow-x-auto no-scrollbar scroll-smooth">
        
        <!-- Lobby / Overview Tab -->
        <a href="{{ route('cooca-ai.index') }}"
           class="flex-1 min-w-[130px] px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center justify-center gap-2 text-center select-none {{ $activeOffice === 'lobby' ? 'bg-white dark:bg-zinc-800 text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
            <i data-lucide="building-2" class="w-4 h-4 {{ $activeOffice === 'lobby' ? 'text-black dark:text-white' : 'text-black/40 dark:text-white/40' }}"></i>
            <span>Lobi Utama</span>
        </a>

        <!-- Executive Office Tab -->
        <a href="{{ route('cooca-ai.office.executive') }}"
           class="flex-1 min-w-[150px] px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center justify-center gap-2 text-center select-none {{ $activeOffice === 'executive' ? 'bg-white dark:bg-zinc-800 text-amber-700 dark:text-amber-400 shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
            <i data-lucide="crown" class="w-4 h-4 {{ $activeOffice === 'executive' ? 'text-amber-500' : 'text-black/40 dark:text-white/40' }}"></i>
            <span>Executive Office</span>
        </a>

        <!-- Operations Office Tab -->
        <a href="{{ route('cooca-ai.office.operations') }}"
           class="flex-1 min-w-[150px] px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center justify-center gap-2 text-center select-none {{ $activeOffice === 'operations' ? 'bg-white dark:bg-zinc-800 text-blue-700 dark:text-blue-400 shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
            <i data-lucide="cpu" class="w-4 h-4 {{ $activeOffice === 'operations' ? 'text-blue-500' : 'text-black/40 dark:text-white/40' }}"></i>
            <span>Operations Office</span>
        </a>

        <!-- Growth Office Tab -->
        <a href="{{ route('cooca-ai.office.growth') }}"
           class="flex-1 min-w-[150px] px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center justify-center gap-2 text-center select-none {{ $activeOffice === 'growth' ? 'bg-white dark:bg-zinc-800 text-purple-700 dark:text-purple-400 shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
            <i data-lucide="sparkles" class="w-4 h-4 {{ $activeOffice === 'growth' ? 'text-purple-500' : 'text-black/40 dark:text-white/40' }}"></i>
            <span>Growth Office</span>
        </a>

    </div>

    <!-- Active Human Approval Alert Banner (Shown across all offices when pending decisions exist) -->
    @if($pendingCount > 0)
    <div class="rounded-2xl p-4 bg-amber-500/10 dark:bg-amber-500/15 border border-amber-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-black dark:text-white uppercase tracking-wider">Perhatian: {{ $pendingCount }} Usulan Aksi Menunggu Keputusan Pemilik</h4>
                <p class="text-xs text-black/70 dark:text-white/70 mt-0.5">Digital workforce telah menyiapkan rekomendasi aksi yang memerlukan persetujuan eksplisit Anda.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('cooca-ai.actions', ['status' => 'pending']) }}" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold transition shadow-sm flex items-center gap-1.5">
                <span>Tinjau Proposal</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>
    @endif
</div>
