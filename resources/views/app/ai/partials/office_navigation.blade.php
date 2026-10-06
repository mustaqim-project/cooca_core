{{-- Unified Apple HIG Navigation Hub for COOCA AI (Integrated with Owner Panel) --}}
@props([
    'activeOffice' => 'lobby', // lobby, executive, operations, growth, pos, actions, history, providers
    'pendingCount' => 0,
])

@php
    $biz = \App\Support\Context::business();
    $currUser = \App\Support\Context::user();
@endphp

<div class="space-y-3">
    <!-- Top Segmented Control Navigation & Quick Actions -->
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3 p-1.5 sm:p-2 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-white/10 shadow-sm">
        
        <!-- Segmented Control Tabs (Apple macOS / iOS Style) -->
        <div class="flex items-center gap-1 overflow-x-auto no-scrollbar scroll-smooth p-1 rounded-xl bg-slate-100 dark:bg-zinc-800/80 border border-slate-200/60 dark:border-white/5 text-xs font-semibold">
            
            <!-- 1. AI Studio (Unified Virtual Office HQ) -->
            <a href="{{ route('cooca-ai.index') }}"
               class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5 shrink-0 select-none {{ in_array($activeOffice, ['lobby', 'executive', 'operations', 'growth', 'studio'], true) ? 'bg-white dark:bg-zinc-700 text-indigo-700 dark:text-indigo-300 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-white/5' }}">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 {{ in_array($activeOffice, ['lobby', 'executive', 'operations', 'growth', 'studio'], true) ? 'text-indigo-600 dark:text-indigo-400' : 'opacity-70' }}"></i>
                <span>AI Studio</span>
            </a>

            <!-- 5. AI POS Intelligence -->
            <a href="{{ route('pos.ai.index') }}"
               class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 shrink-0 select-none {{ $activeOffice === 'pos' ? 'bg-white dark:bg-zinc-700 text-emerald-700 dark:text-emerald-400 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-white/5' }}">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5 {{ $activeOffice === 'pos' ? 'text-emerald-500' : 'opacity-70' }}"></i>
                <span>AI POS</span>
            </a>

            <!-- 6. Pusat Aksi & Approval -->
            <a href="{{ route('cooca-ai.actions') }}"
               class="relative px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 shrink-0 select-none {{ $activeOffice === 'actions' ? 'bg-white dark:bg-zinc-700 text-amber-700 dark:text-amber-400 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-white/5' }}">
                <i data-lucide="check-square" class="w-3.5 h-3.5 {{ $activeOffice === 'actions' ? 'text-amber-500' : 'opacity-70' }}"></i>
                <span>Action Center</span>
                @if($pendingCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-amber-500 text-white tabular-nums">{{ $pendingCount }}</span>
                @endif
            </a>

            <!-- 7. Riwayat Sesi -->
            <a href="{{ route('cooca-ai.history') }}"
               class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 shrink-0 select-none {{ $activeOffice === 'history' ? 'bg-white dark:bg-zinc-700 text-sky-700 dark:text-sky-400 shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-white/5' }}">
                <i data-lucide="history" class="w-3.5 h-3.5 {{ $activeOffice === 'history' ? 'text-sky-500' : 'opacity-70' }}"></i>
                <span>Riwayat Sesi</span>
            </a>

            <!-- 8. Pengaturan Provider -->
            <a href="{{ route('cooca-ai.providers') }}"
               class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 shrink-0 select-none {{ $activeOffice === 'providers' ? 'bg-white dark:bg-zinc-700 text-slate-900 dark:text-white shadow-sm font-bold' : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-white/5' }}">
                <i data-lucide="settings" class="w-3.5 h-3.5 {{ $activeOffice === 'providers' ? 'text-indigo-500' : 'opacity-70' }}"></i>
                <span>Pengaturan AI</span>
            </a>

        </div>

        <!-- Quick AI Consultation & Diagnosis Buttons -->
        <div class="flex items-center gap-2 px-1 shrink-0">
            @if(in_array($activeOffice, ['lobby', 'executive', 'operations', 'growth'], true))
                <button type="button" @click="openConsultationModal()"
                        class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5 cursor-pointer active:scale-95">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>Tanya AI</span>
                </button>
                <button type="button" @click="triggerDailyDiagnosis()"
                        class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 text-xs font-semibold transition flex items-center gap-1.5 border border-slate-200/80 dark:border-white/10 cursor-pointer active:scale-95">
                    <i data-lucide="activity" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span class="hidden sm:inline">Evaluasi Harian</span>
                </button>
            @else
                @php
                    $navBusiness = \App\Support\Context::business();
                    $hasConfiguredNavProvider = $navBusiness ? $navBusiness->aiProviderConfigs()
                        ->where('is_active', true)
                        ->whereNotNull('api_key')
                        ->where('api_key', '!=', '')
                        ->exists() : false;
                @endphp
                <a href="{{ $hasConfiguredNavProvider ? route('cooca-ai.index') : route('cooca-ai.providers') }}"
                   class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5 cursor-pointer active:scale-95">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>Tanya AI</span>
                </a>
            @endif
        </div>

    </div>

    <!-- Active Human Approval Alert Banner (Shown when pending decisions exist) -->
    @if($pendingCount > 0 && $activeOffice !== 'actions')
    <div class="rounded-2xl p-3.5 bg-amber-500/[0.08] dark:bg-amber-500/15 border border-amber-500/25 flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Perhatian: {{ $pendingCount }} Usulan Aksi Menunggu Keputusan Anda</h4>
                <p class="text-xs text-slate-600 dark:text-zinc-300 mt-0.5">Digital workforce AI telah menyiapkan rekomendasi aksi yang memerlukan persetujuan pemilik bisnis.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('cooca-ai.actions', ['status' => 'pending']) }}" class="px-3.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold transition shadow-sm flex items-center gap-1.5">
                <span>Tinjau Proposal</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>
    @endif
</div>
